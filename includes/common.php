<?php
/**
 * OVACS Common Functions and Utilities
 * Shared functionality across all OVACS pages
 */

// Include database connection at the top so classes can access it
require_once __DIR__ . '/database.php';

/**
 * Common page initialization
 * Includes database, initializes managers, handles filters
 */
function initializePage($managers = []) {
    // Initialize requested managers
    $instances = [];
    foreach ($managers as $manager) {
        switch ($manager) {
            case 'vehicle':
                $instances['vehicleManager'] = new VehicleManager();
                break;
            case 'station':
                $instances['stationManager'] = new StationManager();
                break;
            case 'status':
                $instances['statusManager'] = new StatusManager();
                break;
        }
    }
    
    return $instances;
}

/**
 * Process GET filters from URL parameters
 */
function processFilters($allowedFilters = []) {
    $filters = [];
    foreach ($allowedFilters as $filter) {
        if (!empty($_GET[$filter])) {
            $filters[$filter] = $_GET[$filter];
        }
    }
    return $filters;
}

/**
 * Get filters from URL parameters (alias for processFilters)
 */
function getFiltersFromUrl($allowedFilters = []) {
    return processFilters($allowedFilters);
}

/**
 * Get success message from query string
 */
function getSuccessMessage() {
    return isset($_GET['success']) ? htmlspecialchars($_GET['success']) : '';
}

/**
 * Get error message from query string
 */
function getErrorMessage() {
    return isset($_GET['error']) ? htmlspecialchars($_GET['error']) : '';
}

/**
 * Build redirect URL with preserved filters
 */
function buildRedirectUrl($baseUrl, $params = []) {
    if (empty($params)) {
        return $baseUrl;
    }
    return $baseUrl . '?' . http_build_query($params);
}

/**
 * Adjust color brightness for gradients
 */
function adjustBrightness($hexColor, $percent) {
    $hex = ltrim($hexColor, '#');
    $rgb = array_map('hexdec', str_split($hex, 2));
    
    for ($i = 0; $i < 3; $i++) {
        $rgb[$i] = max(0, min(255, $rgb[$i] + ($rgb[$i] * $percent / 100)));
    }
    
    return '#' . sprintf('%02x%02x%02x', $rgb[0], $rgb[1], $rgb[2]);
}

/**
 * Get all status types with vehicle counts
 */
function getStatusTypesWithCounts() {
    $pdo = DatabaseConfig::getConnection();
    $query = $pdo->prepare("
        SELECT 
            st.id,
            st.status_name,
            st.color_code,
            COUNT(v.id) as vehicle_count
        FROM status_types st
        LEFT JOIN vehicles v ON v.status_id = st.id
        GROUP BY st.id, st.status_name, st.color_code
        ORDER BY st.status_name
    ");
    $query->execute();
    return $query->fetchAll();
}

/**
 * Get the current timezone (BST/GMT aware)
 */
function getCurrentTimezone() {
    return new DateTimeZone('Europe/London');
}

/**
 * Get current DateTime with proper timezone
 */
function getCurrentDateTime() {
    return new DateTime('now', getCurrentTimezone());
}

/**
 * Safe HTML output with null checking
 */
function safeHtml($value, $default = '') {
    return htmlspecialchars($value ?? $default, ENT_QUOTES, 'UTF-8');
}

/**
 * Format timestamp for display
 */
function formatTimestamp($timestamp, $format = 'Y-m-d H:i:s') {
    if (!$timestamp) return 'N/A';
    
    $date = new DateTime($timestamp);
    $date->setTimezone(getCurrentTimezone());
    return $date->format($format);
}

/**
 * StatusManager class for handling vehicle status operations
 */
class StatusManager {
    private $pdo;
    
    public function __construct() {
        $this->pdo = DatabaseConfig::getConnection();
    }
    
    /**
     * Get all status types with vehicle counts
     */
    public function getStatusCounts() {
        $query = $this->pdo->prepare("
            SELECT 
                st.id,
                st.status_name,
                st.color_code,
                st.status_description,
                COUNT(v.id) as count
            FROM status_types st
            LEFT JOIN vehicles v ON v.status_id = st.id
            GROUP BY st.id, st.status_name, st.color_code, st.status_description
            ORDER BY st.status_name
        ");
        $query->execute();
        return $query->fetchAll();
    }
    
    /**
     * Get all status types
     */
    public function getAllStatusTypes() {
        $query = $this->pdo->prepare("SELECT * FROM status_types ORDER BY status_name");
        $query->execute();
        return $query->fetchAll();
    }
}