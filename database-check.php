<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OVACS - Database Status</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <section class="hero" style="padding: 6rem 0 4rem;">
        <div class="container">
            <h1 class="hero-title">Database Status Check</h1>
            <p class="hero-subtitle">Verify your OVACS database connection and data</p>
        </div>
    </section>

    <section class="features" style="padding-top: 1rem;">
        <div class="container">
            <?php
            echo "<h2>Database Connection Test</h2>";
            
            // Test database connection
            try {
                include 'includes/database.php';
                
                $dbInfo = DatabaseConfig::getDatabaseInfo();
                $isConnected = DatabaseConfig::testConnection();
                
                if ($isConnected) {
                    echo "<div class='alert alert-success'>";
                    echo "<strong>✅ Database Connection: SUCCESS</strong><br>";
                    echo "Host: " . htmlspecialchars($dbInfo['host']) . "<br>";
                    echo "Database: " . htmlspecialchars($dbInfo['database']) . "<br>";
                    echo "User: " . htmlspecialchars($dbInfo['user']);
                    echo "</div>";
                    
                    // Test data retrieval
                    try {
                        $vehicleManager = new VehicleManager();
                        $stationManager = new StationManager();
                        
                        $fleetStats = $vehicleManager->getFleetSummary();
                        $vehicleCount = $vehicleManager->getAllVehicles();
                        $stations = $stationManager->getAllStations();
                        $recentActivity = $vehicleManager->getRecentActivity(5);
                        
                        echo "<div class='alert alert-success'>";
                        echo "<strong>✅ Data Retrieval: SUCCESS</strong><br>";
                        echo "Total Vehicles: " . count($vehicleCount) . "<br>";
                        echo "Available: " . $fleetStats['available'] . " | In Service: " . $fleetStats['in_service'] . " | Maintenance: " . $fleetStats['maintenance'] . "<br>";
                        echo "Active Stations: " . count($stations) . "<br>";
                        echo "Recent Activity Records: " . count($recentActivity);
                        echo "</div>";
                        
                        // Show sample data
                        echo "<h3 style='margin-top: 2rem;'>Sample Vehicle Data</h3>";
                        if (!empty($vehicleCount)) {
                            echo "<table style='width: 100%; border-collapse: collapse; margin-top: 1rem; background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1);'>";
                            echo "<thead style='background: #f8fafc; border-bottom: 2px solid #e5e7eb;'>";
                            echo "<tr>";
                            echo "<th style='padding: 1rem; text-align: left;'>Vehicle ID</th>";
                            echo "<th style='padding: 1rem; text-align: left;'>Type</th>";
                            echo "<th style='padding: 1rem; text-align: left;'>Station</th>";
                            echo "<th style='padding: 1rem; text-align: left;'>Status</th>";
                            echo "</tr>";
                            echo "</thead>";
                            echo "<tbody>";
                            
                            foreach (array_slice($vehicleCount, 0, 10) as $vehicle) {
                                $statusColor = match($vehicle['status']) {
                                    'Available' => '#10b981',
                                    'In Service' => '#ef4444',
                                    'Maintenance' => '#f59e0b',
                                    'Out of Service' => '#6b7280',
                                    default => '#6b7280'
                                };
                                
                                echo "<tr style='border-bottom: 1px solid #f3f4f6;'>";
                                echo "<td style='padding: 1rem; font-weight: 500;'>" . htmlspecialchars($vehicle['vehicle_id']) . "</td>";
                                echo "<td style='padding: 1rem;'>" . htmlspecialchars($vehicle['type_name']) . "</td>";
                                echo "<td style='padding: 1rem;'>" . htmlspecialchars($vehicle['station_name']) . "</td>";
                                echo "<td style='padding: 1rem;'>";
                                echo "<span style='background: $statusColor; color: white; padding: 0.25rem 0.75rem; border-radius: 15px; font-size: 0.85rem;'>";
                                echo htmlspecialchars($vehicle['status']);
                                echo "</span>";
                                echo "</td>";
                                echo "</tr>";
                            }
                            
                            echo "</tbody>";
                            echo "</table>";
                            
                            echo "<p style='margin-top: 1rem; color: #6b7280;'>Showing first 10 vehicles of " . count($vehicleCount) . " total</p>";
                        }
                        
                        echo "<div style='margin-top: 2rem;'>";
                        echo "<a href='index.php' class='btn btn-primary'>Go to Dashboard</a> ";
                        echo "<a href='vehicles.php' class='btn btn-secondary'>Manage Vehicles</a>";
                        echo "</div>";
                        
                    } catch (Exception $e) {
                        echo "<div class='alert alert-warning'>";
                        echo "<strong>⚠️ Data Retrieval: PARTIAL FAILURE</strong><br>";
                        echo "Connection successful but data access failed.<br>";
                        echo "Error: " . htmlspecialchars($e->getMessage()) . "<br>";
                        echo "This might indicate missing tables or incorrect permissions.";
                        echo "</div>";
                    }
                    
                } else {
                    echo "<div class='alert alert-warning'>";
                    echo "<strong>❌ Database Connection: FAILED</strong><br>";
                    echo "Cannot connect to the database.<br>";
                    echo "Please check your database configuration in <code>includes/database.php</code>";
                    echo "</div>";
                }
                
            } catch (Exception $e) {
                echo "<div class='alert alert-warning'>";
                echo "<strong>❌ Database Setup: NOT CONFIGURED</strong><br>";
                echo "Error: " . htmlspecialchars($e->getMessage()) . "<br><br>";
                echo "<strong>To set up the database:</strong><br>";
                echo "1. Install MySQL/MariaDB<br>";
                echo "2. Run <code>setup-database.bat</code> in the project folder<br>";
                echo "3. Update database credentials in <code>includes/database.php</code><br>";
                echo "4. Refresh this page to test again";
                echo "</div>";
            }
            ?>
            
            <div style="margin-top: 3rem; padding: 2rem; background: #f8fafc; border-radius: 10px;">
                <h3>Setup Instructions</h3>
                <ol style="margin-left: 2rem; line-height: 1.8;">
                    <li>Install MySQL or MariaDB server</li>
                    <li>Run <strong>setup-database.bat</strong> to create the database schema</li>
                    <li>Update database credentials in <strong>includes/database.php</strong></li>
                    <li>Ensure your web server has PHP PDO MySQL extension enabled</li>
                </ol>
                
                <h4 style="margin-top: 2rem;">System Requirements:</h4>
                <ul style="margin-left: 2rem; line-height: 1.8;">
                    <li>PHP 8.0+ with PDO MySQL extension</li>
                    <li>MySQL 5.7+ or MariaDB 10.3+</li>
                    <li>Web server (Apache, Nginx, IIS)</li>
                </ul>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>
</body>
</html>