<?php
/**
 * Database Connection Test for OVACS
 * Use this to diagnose database connection issues
 */
?>
<!DOCTYPE html>
<html>
<head>
    <title>OVACS Database Connection Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        .success { color: green; }
        .error { color: red; }
        .warning { color: orange; }
        .info { background: #f0f0f0; padding: 10px; margin: 10px 0; }
    </style>
</head>
<body>
    <h1>🔧 OVACS Database Connection Test</h1>
    
    <?php
    echo "<div class='info'>";
    echo "<strong>Current Configuration:</strong><br>";
    echo "Host: localhost<br>";
    echo "Database: ovacs_db<br>";
    echo "User: root<br>";
    echo "Password: [hidden]<br>";
    echo "</div>";

    // Test 1: PDO Extension
    echo "<h3>Test 1: PDO MySQL Extension</h3>";
    if (extension_loaded('pdo_mysql')) {
        echo "<p class='success'>✅ PDO MySQL extension is loaded</p>";
    } else {
        echo "<p class='error'>❌ PDO MySQL extension is NOT loaded</p>";
    }

    // Test 2: Database Connection with current config
    echo "<h3>Test 2: Database Connection (Current Config)</h3>";
    try {
        $dsn = "mysql:host=localhost;dbname=ovacs_db;charset=utf8mb4";
        $pdo = new PDO($dsn, 'root', 'Jiffyor@nge999', [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);
        echo "<p class='success'>✅ Connection successful with root user</p>";
        
        // Test query
        $stmt = $pdo->query("SELECT COUNT(*) as table_count FROM information_schema.tables WHERE table_schema = 'ovacs_db'");
        $result = $stmt->fetch();
        echo "<p class='success'>✅ Database contains {$result['table_count']} tables</p>";
        
    } catch (PDOException $e) {
        echo "<p class='error'>❌ Connection failed: " . htmlspecialchars($e->getMessage()) . "</p>";
        
        // Test 3: Try with root user
        echo "<h3>Test 3: Database Connection (Root User)</h3>";
        try {
            echo "<p class='warning'>⚠️ Trying connection with root user...</p>";
            $dsn = "mysql:host=localhost;dbname=ovacs_db;charset=utf8mb4";
            $pdo_root = new PDO($dsn, 'root', 'Jiffyor@nge999', [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]);
            echo "<p class='success'>✅ Connection successful with root user</p>";
            
            // Check if ovacs_user exists
            $stmt = $pdo_root->query("SELECT User FROM mysql.user WHERE User = 'ovacs_user'");
            if ($stmt->rowCount() > 0) {
                echo "<p class='warning'>⚠️ User 'ovacs_user' exists but not being used</p>";
            } else {
                echo "<p class='info'>ℹ️ User 'ovacs_user' does not exist (using root instead)</p>";
            }
            
        } catch (PDOException $e2) {
            echo "<p class='error'>❌ Root connection also failed: " . htmlspecialchars($e2->getMessage()) . "</p>";
            
            // Test fallback: Try different root password
            echo "<h4>Trying root with different passwords...</h4>";
            $common_passwords = ['', 'root', 'password', 'mysql', '123456'];
            $found_password = false;
            foreach ($common_passwords as $pwd) {
                try {
                    $pdo_test = new PDO($dsn, 'root', $pwd, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
                    echo "<p class='success'>✅ Root password found: '" . ($pwd ?: '[empty]') . "'</p>";
                    $found_password = true;
                    break;
                } catch (PDOException $e3) {
                    // Continue to next password
                }
            }
            if (!$found_password) {
                echo "<p class='error'>❌ Could not find working root password</p>";
            }
        }
    }

    // Test 4: Check if database exists
    echo "<h3>Test 4: Database Existence</h3>";
    try {
        $dsn_no_db = "mysql:host=localhost;charset=utf8mb4";
        $pdo_check = new PDO($dsn_no_db, 'root', 'Jiffyor@nge999', [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);
        $stmt = $pdo_check->query("SHOW DATABASES LIKE 'ovacs_db'");
        if ($stmt->rowCount() > 0) {
            echo "<p class='success'>✅ Database 'ovacs_db' exists</p>";
            
            // Also check tables in the database
            $pdo_check->exec("USE ovacs_db");
            $tables = $pdo_check->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
            echo "<p class='success'>✅ Database contains " . count($tables) . " tables: " . implode(', ', $tables) . "</p>";
        } else {
            echo "<p class='error'>❌ Database 'ovacs_db' does not exist</p>";
            echo "<p class='warning'>🔧 Run setup-database.bat to create the database</p>";
        }
    } catch (PDOException $e) {
        echo "<p class='error'>❌ Cannot check database existence: " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<p class='warning'>This usually means MySQL service is not running or wrong password</p>";
    }
    ?>
    
    <h3>💡 Next Steps</h3>
    <ul>
        <li>If PDO MySQL is not loaded, enable it in php.ini</li>
        <li>If ovacs_user doesn't exist, create it or update database.php to use root</li>
        <li>If database doesn't exist, run the setup-database.bat script</li>
        <li>Check MySQL service is running</li>
    </ul>
    
    <p><a href="index.php">← Back to OVACS</a></p>
</body>
</html>