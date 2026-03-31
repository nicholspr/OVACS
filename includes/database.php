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
        $sql = "SELECT v.id, v.vehicle_id, v.status, v.updated_at,
                       vt.type_name, vt.type_code,
                       s.station_code, s.name as station_name
                FROM vehicles v
                JOIN vehicle_types vt ON v.type_id = vt.id
                JOIN stations s ON v.station_id = s.id
                WHERE v.is_active = TRUE";

        $params = [];

        // Apply filters
        if (!empty($filters['status'])) {
            $sql .= " AND v.status = :status";
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
                    SUM(CASE WHEN status = 'Available' THEN 1 ELSE 0 END) as available,
                    SUM(CASE WHEN status = 'In Service' THEN 1 ELSE 0 END) as in_service,
                    SUM(CASE WHEN status = 'Maintenance' THEN 1 ELSE 0 END) as maintenance,
                    SUM(CASE WHEN status = 'Out of Service' THEN 1 ELSE 0 END) as out_of_service
                FROM vehicles 
                WHERE is_active = TRUE";

        $stmt = $this->pdo->query($sql);
        return $stmt->fetch();
    }

    /**
     * Update vehicle status
     */
    public function updateVehicleStatus($vehicleId, $newStatus, $changedBy, $reason = '') {
        try {
            $this->pdo->beginTransaction();

            // Call stored procedure
            $stmt = $this->pdo->prepare("CALL UpdateVehicleStatus(?, ?, NULL, ?, ?)");
            $stmt->execute([$vehicleId, $newStatus, $changedBy, $reason]);

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
     * Get all stations
     */
    public function getAllStations() {
        $sql = "SELECT * FROM stations WHERE is_active = TRUE ORDER BY station_code";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Get station availability view
     */
    public function getStationAvailability() {
        $sql = "SELECT * FROM vehicle_availability ORDER BY station_code, type_name";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll();
    }
}

// Error handling function
function handleDatabaseError($error) {
    error_log("OVACS Database Error: " . $error);
    
    // In production, don't show detailed errors to users
    if (defined('DEBUG') && DEBUG) {
        echo "<div class='alert alert-danger'>Database Error: " . htmlspecialchars($error) . "</div>";
    } else {
        echo "<div class='alert alert-warning'>A system error occurred. Please try again later.</div>";
    }
}

// Initialize database connection on include
try {
    DatabaseConfig::getConnection();
} catch (Exception $e) {
    handleDatabaseError($e->getMessage());
}
?>