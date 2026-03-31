<?php
/**
 * Vehicles Page Error Diagnostic
 * This will show what specific errors are occurring on the vehicles page
 */

// Enable error display for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>🔧 Vehicles Page Error Diagnostic</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .step { background: #f0f0f0; padding: 15px; margin: 10px 0; border-left: 4px solid #3b82f6; }
    .error { color: red; background: #fee; padding: 10px; border: 1px solid red; margin: 10px 0; }
    .success { color: green; background: #efe; padding: 10px; border: 1px solid green; margin: 10px 0; }
    .warning { color: orange; background: #fff4e6; padding: 10px; border: 1px solid orange; margin: 10px 0; }
</style>";

echo "<div class='step'><strong>Step 1:</strong> Testing basic PHP</div>";
echo "<div class='success'>✅ PHP is working</div>";

echo "<div class='step'><strong>Step 2:</strong> Testing database.php include</div>";
try {
    include_once 'includes/database.php';
    echo "<div class='success'>✅ database.php included successfully</div>";
} catch (Exception $e) {
    echo "<div class='error'>❌ Failed to include database.php: " . $e->getMessage() . "</div>";
    exit;
}

echo "<div class='step'><strong>Step 3:</strong> Testing database connection</div>";
try {
    $connection = DatabaseConfig::getConnection();
    echo "<div class='success'>✅ Database connection successful</div>";
} catch (Exception $e) {
    echo "<div class='error'>❌ Database connection failed: " . $e->getMessage() . "</div>";
    echo "<div class='warning'>⚠️ Check your MySQL service and credentials</div>";
    exit;
}

echo "<div class='step'><strong>Step 4:</strong> Testing VehicleManager class</div>";
try {
    $vehicleManager = new VehicleManager();
    echo "<div class='success'>✅ VehicleManager instantiated successfully</div>";
} catch (Exception $e) {
    echo "<div class='error'>❌ Failed to create VehicleManager: " . $e->getMessage() . "</div>";
    exit;
}

echo "<div class='step'><strong>Step 5:</strong> Testing StationManager class</div>";
try {
    $stationManager = new StationManager();
    echo "<div class='success'>✅ StationManager instantiated successfully</div>";
} catch (Exception $e) {
    echo "<div class='error'>❌ Failed to create StationManager: " . $e->getMessage() . "</div>";
    exit;
}

echo "<div class='step'><strong>Step 6:</strong> Testing database tables</div>";
try {
    $pdo = DatabaseConfig::getConnection();
    
    // First, show all tables that exist
    echo "<div class='info'>📋 All tables in database:</div>";
    $allTablesStmt = $pdo->query("SHOW TABLES");
    $allTables = $allTablesStmt->fetchAll(PDO::FETCH_COLUMN);
    echo "<div class='info'>Found " . count($allTables) . " tables: " . implode(', ', $allTables) . "</div>";
    
    // Check if required tables exist
    $requiredTables = ['vehicles', 'stations', 'vehicle_types'];
    $existingTables = [];
    
    foreach ($requiredTables as $table) {
        echo "<div class='info'>🔍 Checking for table: $table</div>";
        
        // Try multiple methods to check if table exists
        $exists = false;
        
        // Method 1: SHOW TABLES LIKE
        try {
            $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
            $stmt->execute([$table]);
            $result = $stmt->fetch();
            if ($result) {
                $exists = true;
                echo "<div class='success'>  ✅ Method 1 (SHOW TABLES LIKE): found</div>";
            } else {
                echo "<div class='warning'>  ⚠️ Method 1 (SHOW TABLES LIKE): not found</div>";
            }
        } catch (Exception $e) {
            echo "<div class='error'>  ❌ Method 1 failed: " . $e->getMessage() . "</div>";
        }
        
        // Method 2: Check in array of all tables
        if (in_array($table, $allTables)) {
            $exists = true;
            echo "<div class='success'>  ✅ Method 2 (in array): found</div>";
        } else {
            echo "<div class='warning'>  ⚠️ Method 2 (in array): not found</div>";
        }
        
        // Method 3: Try to query the table
        try {
            $testStmt = $pdo->query("SELECT COUNT(*) FROM `$table`");
            $count = $testStmt->fetchColumn();
            $exists = true;
            echo "<div class='success'>  ✅ Method 3 (query table): found with $count records</div>";
        } catch (Exception $e) {
            echo "<div class='error'>  ❌ Method 3 failed: " . $e->getMessage() . "</div>";
        }
        
        if ($exists) {
            $existingTables[] = $table;
            echo "<div class='success'>✅ Table '$table' confirmed to exist</div>";
        } else {
            echo "<div class='error'>❌ Table '$table' not found by any method</div>";
        }
    }
    
    echo "<div class='info'>📊 Summary: Found " . count($existingTables) . " out of " . count($requiredTables) . " required tables</div>";
    
    if (count($existingTables) === count($requiredTables)) {
        echo "<div class='success'>🎉 All required tables exist!</div>";
    } else {
        echo "<div class='warning'>⚠️ Missing tables: " . implode(', ', array_diff($requiredTables, $existingTables)) . "</div>";
        echo "<div class='warning'>⚠️ Run setup-database.bat to create them.</div>";
    }
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Failed to check tables: " . $e->getMessage() . "</div>";
    echo "<div class='info'>🔍 Error details: " . get_class($e) . "</div>";
    echo "<div class='info'>🔍 Error trace: " . $e->getTraceAsString() . "</div>";
}

echo "<div class='step'><strong>Step 7:</strong> Testing data retrieval</div>";
try {
    $vehicles = $vehicleManager->getAllVehicles();
    echo "<div class='success'>✅ Retrieved " . count($vehicles) . " vehicles</div>";
    
    if (count($vehicles) > 0) {
        echo "<div class='success'>Sample vehicle: " . json_encode($vehicles[0]) . "</div>";
    }
} catch (Exception $e) {
    echo "<div class='error'>❌ Failed to get vehicles: " . $e->getMessage() . "</div>";
}

try {
    $stations = $stationManager->getAllStations();
    echo "<div class='success'>✅ Retrieved " . count($stations) . " stations</div>";
    
    if (count($stations) > 0) {
        echo "<div class='success'>Sample station: " . json_encode($stations[0]) . "</div>";
    }
} catch (Exception $e) {
    echo "<div class='error'>❌ Failed to get stations: " . $e->getMessage() . "</div>";
}

echo "<div class='step'><strong>Step 8:</strong> Testing header.php include</div>";
try {
    ob_start();
    include 'includes/header.php';
    $headerOutput = ob_get_clean();
    echo "<div class='success'>✅ header.php included successfully</div>";
} catch (Exception $e) {
    echo "<div class='error'>❌ Failed to include header.php: " . $e->getMessage() . "</div>";
    $headerOutput = '';
}

echo "<h2>📋 Summary</h2>";
echo "<p>If all tests above are successful, the vehicles page should work. If any tests failed, those are the issues to fix.</p>";

echo "<h2>🔧 Next Steps</h2>";
echo "<ul>";
echo "<li>If database connection failed: Check MySQL service and credentials</li>";
echo "<li>If tables are missing: Run <code>setup-database.bat</code></li>";
echo "<li>If classes failed: Check PHP error logs</li>";
echo "<li>If all tests pass: Try <a href='vehicles.php'>vehicles.php</a> again</li>";
echo "</ul>";

echo "<p><a href='index.php'>← Back to Home</a> | <a href='database-test.php'>🔗 Database Test</a></p>";
?>