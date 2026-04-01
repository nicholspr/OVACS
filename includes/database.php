<?php
/**
 * OVACS Database Configuration
 * Online Vehicle Availability Control System
 */

// Database Configuration
class DatabaseConfig {
    // Database connection parameters
    private const DB_HOST = 'localhost';
    private const DB_NAME = 'ovacs_db';
    private const DB_USER = 'root'; // Using root for now
    private const DB_PASS = 'Jiffyor@nge999'; // Enter your MySQL root password here
    private const DB_CHARSET = 'utf8mb4';

    private static $connection = null;

    /**
     * Get database connection
     */
    public static function getConnection() {
        if (self::$connection === null) {
            try {
                $dsn = "mysql:host=" . self::DB_HOST . ";dbname=" . self::DB_NAME . ";charset=" . self::DB_CHARSET;
                self::$connection = new PDO($dsn, self::DB_USER, self::DB_PASS, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]);
            } catch (PDOException $e) {
                error_log("Database connection failed: " . $e->getMessage());
                throw new Exception("Database connection failed. Please check your configuration.");
            }
        }
        return self::$connection;
    }

    /**
     * Test database connection
     */
    public static function testConnection() {
        try {
            $pdo = self::getConnection();
            $stmt = $pdo->query("SELECT 1");
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Get database info
     */
    public static function getDatabaseInfo() {
        return [
            'host' => self::DB_HOST,
            'database' => self::DB_NAME,
            'user' => self::DB_USER
        ];
    }
}

/**
 * Vehicle Management Class
 */
class VehicleManager {
    private $pdo;

    public function __construct() {
        $this->pdo = DatabaseConfig::getConnection();
    }

    /**
     * Get all vehicles with station and type info
     */
    public function getAllVehicles($filters = []) {
        $sql = "SELECT v.id, v.vehicle_id, st.status_name as status, v.updated_at,
                       vt.type_name, vt.type_code,
                       s.station_code, s.name as station_name, st.color_code as status_color
                FROM vehicles v
                JOIN vehicle_types vt ON v.type_id = vt.id
                JOIN stations s ON v.station_id = s.id
                JOIN status_types st ON v.status_id = st.id
                WHERE v.is_active = TRUE";

        $params = [];

        // Apply filters
        if (!empty($filters['status'])) {
            $sql .= " AND st.status_name = :status";
            $params[':status'] = $filters['status'];
        }

        if (!empty($filters['type'])) {
            $sql .= " AND vt.type_code = :type";
            $params[':type'] = $filters['type'];
        }

        if (!empty($filters['station'])) {
            $sql .= " AND s.id = :station";
            $params[':station'] = $filters['station'];
        }

        $sql .= " ORDER BY v.vehicle_id";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Get vehicle availability summary
     */
    public function getFleetSummary() {
        $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN st.status_name = 'Available' THEN 1 ELSE 0 END) as available,
                    SUM(CASE WHEN st.status_name = 'In Service' THEN 1 ELSE 0 END) as in_service,
                    SUM(CASE WHEN st.status_name = 'Maintenance' THEN 1 ELSE 0 END) as maintenance,
                    SUM(CASE WHEN st.status_name = 'Out of Service' THEN 1 ELSE 0 END) as out_of_service
                FROM vehicles v
                JOIN status_types st ON v.status_id = st.id
                WHERE v.is_active = TRUE";

        $stmt = $this->pdo->query($sql);
        return $stmt->fetch();
    }

    /**
     * Update vehicle status
     */
    public function updateVehicleStatus($vehicleId, $newStatus, $changedBy, $reason = '') {
        try {
            $this->pdo->beginTransaction();

            // Get current status for logging
            $currentStatusStmt = $this->pdo->prepare("SELECT st.status_name FROM vehicles v JOIN status_types st ON v.status_id = st.id WHERE v.id = ?");
            $currentStatusStmt->execute([$vehicleId]);
            $oldStatus = $currentStatusStmt->fetchColumn();

            // Get status_id from status_name
            $statusStmt = $this->pdo->prepare("SELECT id FROM status_types WHERE status_name = ?");
            $statusStmt->execute([$newStatus]);
            $statusId = $statusStmt->fetchColumn();
            
            if (!$statusId) {
                throw new Exception("Invalid status: " . $newStatus);
            }

            // Update vehicle status_id
            $updateStmt = $this->pdo->prepare("UPDATE vehicles SET status_id = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
            $updateStmt->execute([$statusId, $vehicleId]);

            // Log the status change with the old status we captured before the update
            $logStmt = $this->pdo->prepare("INSERT INTO vehicle_status_log (vehicle_id, previous_status, new_status, changed_by, change_reason) VALUES (?, ?, ?, ?, ?)");
            $logStmt->execute([$vehicleId, $oldStatus, $newStatus, $changedBy, $reason]);

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log("Failed to update vehicle status: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get recent activity log
     */
    public function getRecentActivity($limit = 10) {
        $sql = "SELECT vsl.*, v.vehicle_id, s.name as station_name
                FROM vehicle_status_log vsl
                JOIN vehicles v ON vsl.vehicle_id = v.id
                LEFT JOIN stations s ON vsl.new_station_id = s.id
                ORDER BY vsl.timestamp DESC
                LIMIT :limit";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}

/**
 * Station Management Class
 */
class StationManager {
    private $pdo;

    public function __construct() {
        $this->pdo = DatabaseConfig::getConnection();
    }

    /**
     * Get all stations with optional filtering
     */
    public function getAllStations($filters = []) {
        $sql = "SELECT s.*, 
                       COUNT(v.id) as total_vehicles,
                       SUM(CASE WHEN st.status_name = 'Available' THEN 1 ELSE 0 END) as available_vehicles,
                       SUM(CASE WHEN st.status_name = 'In Service' THEN 1 ELSE 0 END) as in_service_vehicles,
                       SUM(CASE WHEN st.status_name = 'Maintenance' THEN 1 ELSE 0 END) as maintenance_vehicles,
                       SUM(CASE WHEN st.status_name = 'Out of Service' THEN 1 ELSE 0 END) as out_of_service_vehicles
                FROM stations s
                LEFT JOIN vehicles v ON s.id = v.station_id AND v.is_active = TRUE
                LEFT JOIN status_types st ON v.status_id = st.id
                WHERE s.is_active = TRUE";

        $params = [];

        // Apply filters
        if (!empty($filters['division'])) {
            $sql .= " AND s.division = :division";
            $params[':division'] = $filters['division'];
        }

        if (!empty($filters['postcode'])) {
            $sql .= " AND s.postcode LIKE :postcode";
            $params[':postcode'] = '%' . $filters['postcode'] . '%';
        }

        $sql .= " GROUP BY s.id ORDER BY s.station_code";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Get station by ID
     */
    public function getStationById($id) {
        $sql = "SELECT * FROM stations WHERE id = ? AND is_active = TRUE";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Get station availability view
     */
    public function getStationAvailability() {
        $sql = "SELECT * FROM vehicle_availability ORDER BY station_code, type_name";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Get stations summary statistics
     */
    public function getStationsSummary() {
        $sql = "SELECT 
                    COUNT(DISTINCT s.id) as total_stations,
                    COUNT(DISTINCT s.division) as total_divisions,
                    0 as total_capacity,
                    0 as avg_capacity,
                    COUNT(v.id) as total_vehicles,
                    SUM(CASE WHEN st.status_name = 'Available' THEN 1 ELSE 0 END) as total_available,
                    SUM(CASE WHEN st.status_name = 'In Service' THEN 1 ELSE 0 END) as total_in_service,
                    SUM(CASE WHEN st.status_name = 'Maintenance' THEN 1 ELSE 0 END) as total_maintenance,
                    SUM(CASE WHEN st.status_name = 'Out of Service' THEN 1 ELSE 0 END) as total_out_of_service
                FROM stations s
                LEFT JOIN vehicles v ON s.id = v.station_id AND v.is_active = TRUE
                LEFT JOIN status_types st ON v.status_id = st.id
                WHERE s.is_active = TRUE";

        $stmt = $this->pdo->query($sql);
        return $stmt->fetch();
    }

    /**
     * Get vehicles for a specific station
     */
    public function getStationVehicles($stationId) {
        $sql = "SELECT v.*, vt.type_name, vt.type_code
                FROM vehicles v
                JOIN vehicle_types vt ON v.type_id = vt.id
                WHERE v.station_id = ? AND v.is_active = TRUE
                ORDER BY v.vehicle_id";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$stationId]);
        return $stmt->fetchAll();
    }

    /**
     * Get all divisions for filtering
     */
    public function getDivisions() {
        $sql = "SELECT DISTINCT division FROM stations WHERE is_active = TRUE AND division IS NOT NULL ORDER BY division";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}

/**
 * Status Management Class
 */
class StatusManager {
    private $pdo;

    public function __construct() {
        $this->pdo = DatabaseConfig::getConnection();
    }

    /**
     * Get all status types
     */
    public function getAllStatusTypes() {
        $sql = "SELECT id, status_name, status_description, color_code 
                FROM status_types 
                ORDER BY status_name";
        
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Get status by name
     */
    public function getStatusByName($statusName) {
        $sql = "SELECT id, status_name, status_description, color_code 
                FROM status_types 
                WHERE status_name = ?";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$statusName]);
        return $stmt->fetch();
    }
}

// Error handling function
function handleDatabaseError($error) {
    error_log("OVACS Database Error: " . $error);
    echo "<div class='alert alert-warning'>A system error occurred. Please try again later.</div>";
}

// Initialize database connection on include
try {
    DatabaseConfig::getConnection();
} catch (Exception $e) {
    handleDatabaseError($e->getMessage());
}
?>