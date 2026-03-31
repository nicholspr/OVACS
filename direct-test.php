<?php
/**
 * Simple Database Connection Test
 * Direct connection test without using classes
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Direct Database Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .success { color: green; background: #e8f5e8; padding: 10px; margin: 5px 0; }
        .error { color: red; background: #ffe8e8; padding: 10px; margin: 5px 0; }
        .info { background: #e8f4f8; padding: 10px; margin: 5px 0; }
    </style>
</head>
<body>
    <h1>🔧 Direct Database Connection Test</h1>
    
    <?php
    // Test 1: Basic PHP Info
    echo "<div class='info'><strong>PHP Version:</strong> " . PHP_VERSION . "</div>";
    echo "<div class='info'><strong>SAPI:</strong> " . php_sapi_name() . "</div>";
    
    // Test 2: PDO MySQL Extension
    if (extension_loaded('pdo_mysql')) {
        echo "<div class='success'>✅ PDO MySQL extension loaded</div>";
    } else {
        echo "<div class='error'>❌ PDO MySQL extension NOT loaded</div>";
        exit;
    }
    
    // Test 3: Direct PDO Connection
    $host = 'localhost';
    $dbname = 'ovacs_db';
    $username = 'root';
    $password = 'Jiffyor@nge999';
    
    try {
        echo "<div class='info'>🔄 Attempting direct PDO connection...</div>";
        echo "<div class='info'>Host: $host</div>";
        echo "<div class='info'>Database: $dbname</div>";
        echo "<div class='info'>Username: $username</div>";
        
        $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ];
        
        $pdo = new PDO($dsn, $username, $password, $options);
        echo "<div class='success'>✅ Direct PDO connection successful!</div>";
        
        // Test 4: Show all tables
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo "<div class='success'>✅ Found " . count($tables) . " tables</div>";
        echo "<div class='info'>Tables: " . implode(', ', $tables) . "</div>";
        
        // Test 5: Check specific tables we need
        $requiredTables = ['vehicles', 'stations', 'vehicle_types'];
        foreach ($requiredTables as $table) {
            if (in_array($table, $tables)) {
                // Get row count
                $countStmt = $pdo->query("SELECT COUNT(*) FROM `$table`");
                $count = $countStmt->fetchColumn();
                echo "<div class='success'>✅ Table '$table' exists with $count rows</div>";
            } else {
                echo "<div class='error'>❌ Table '$table' missing</div>";
            }
        }
        
        // Test 6: Try creating the classes
        echo "<h2>Testing Class Creation</h2>";
        
        // Include database.php
        try {
            include_once 'includes/database.php';
            echo "<div class='success'>✅ database.php included</div>";
            
            // Test DatabaseConfig
            $testConnection = DatabaseConfig::getConnection();
            echo "<div class='success'>✅ DatabaseConfig::getConnection() works</div>";
            
            // Test VehicleManager
            $vehicleManager = new VehicleManager();
            echo "<div class='success'>✅ VehicleManager created</div>";
            
            // Test getting vehicles
            $vehicles = $vehicleManager->getAllVehicles();
            echo "<div class='success'>✅ getAllVehicles() returned " . count($vehicles) . " vehicles</div>";
            
            if (count($vehicles) > 0) {
                echo "<div class='info'>First vehicle: " . json_encode($vehicles[0], JSON_PRETTY_PRINT) . "</div>";
            }
            
        } catch (Exception $e) {
            echo "<div class='error'>❌ Class test failed: " . $e->getMessage() . "</div>";
            echo "<div class='error'>Stack trace: " . $e->getTraceAsString() . "</div>";
        }
        
    } catch (PDOException $e) {
        echo "<div class='error'>❌ PDO Connection failed!</div>";
        echo "<div class='error'>Error: " . $e->getMessage() . "</div>";
        echo "<div class='error'>Error Code: " . $e->getCode() . "</div>";
        
        // Try different connection without database
        echo "<h2>Testing Connection Without Database</h2>";
        try {
            $simpleDsn = "mysql:host=$host;charset=utf8mb4";
            $simplePdo = new PDO($simpleDsn, $username, $password, $options);
            echo "<div class='success'>✅ Connection to MySQL server successful (without database)</div>";
            
            // Check if database exists
            $dbStmt = $simplePdo->query("SHOW DATABASES LIKE '$dbname'");
            if ($dbStmt->rowCount() > 0) {
                echo "<div class='success'>✅ Database '$dbname' exists</div>";
            } else {
                echo "<div class='error'>❌ Database '$dbname' does not exist</div>";
            }
            
        } catch (PDOException $e2) {
            echo "<div class='error'>❌ Even basic MySQL connection failed: " . $e2->getMessage() . "</div>";
        }
        
    } catch (Exception $e) {
        echo "<div class='error'>❌ General error: " . $e->getMessage() . "</div>";
    }
    ?>
    
    <h2>🔧 Troubleshooting</h2>
    <ul>
        <li>If PDO MySQL not loaded: Check php.ini and restart IIS</li>  
        <li>If connection failed: Check MySQL service is running</li>
        <li>If database doesn't exist: Run setup-database.bat</li>
        <li>If tables missing: Check database schema import</li>
    </ul>
    
    <p><a href="vehicles.php">Try Vehicles Page</a> | <a href="index.php">Home</a></p>
</body>
</html>