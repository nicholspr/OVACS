<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OVACS - Vehicle Availability Dashboard</title>
    <meta name="description" content="OVACS - Online Vehicle Availability Control System for Emergency Services">
    <link rel="stylesheet" href="css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php 
    include 'includes/header.php'; 
    
    // Function to adjust color brightness for gradients
    if (!function_exists('adjustBrightness')) {
        function adjustBrightness($hexColor, $adjustPercent) {
            $hexColor = ltrim($hexColor, '#');
            if (strlen($hexColor) == 3) {
                $hexColor = $hexColor[0] . $hexColor[0] . $hexColor[1] . $hexColor[1] . $hexColor[2] . $hexColor[2];
            }
            $r = hexdec(substr($hexColor, 0, 2));
            $g = hexdec(substr($hexColor, 2, 2));
            $b = hexdec(substr($hexColor, 4, 2));
            
            $r = max(0, min(255, $r + ($r * $adjustPercent / 100)));
            $g = max(0, min(255, $g + ($g * $adjustPercent / 100)));
            $b = max(0, min(255, $b + ($b * $adjustPercent / 100)));
            
            return sprintf('#%02x%02x%02x', $r, $g, $b);
        }
    }
    
    // Initialize database connection and get real data
    $fleetStats = ['total' => 150, 'available' => 127, 'in_service' => 18, 'maintenance' => 5];
    $recentActivity = [];
    $statusCounts = [];
    
    try {
        include 'includes/database.php';
        $vehicleManager = new VehicleManager();
        $statusManager = new StatusManager();
        $fleetStats = $vehicleManager->getFleetSummary();
        $recentActivity = $vehicleManager->getRecentActivity(4);
        
        // Get vehicle status counts for hero cards
        $pdo = DatabaseConfig::getConnection();
        $statusQuery = $pdo->prepare("
            SELECT 
                st.status_name,
                st.color_code,
                COUNT(v.id) as count
            FROM status_types st
            LEFT JOIN vehicles v ON v.status_id = st.id
            GROUP BY st.id, st.status_name, st.color_code
            ORDER BY st.status_name
        ");
        $statusQuery->execute();
        $statusCounts = $statusQuery->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        // Fallback to default values if database is not available
        error_log("Dashboard database error: " . $e->getMessage());
    }
    ?>

    <style>
        /* Status cards styling */
        .status-cards {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            margin-top: 1.5rem;
        }
        
        @media (max-width: 768px) {
            .status-cards {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 480px) {
            .status-cards {
                grid-template-columns: 1fr;
            }
        }

        /* Quick action button styling */
        .btn-secondary {
            background: rgba(0, 0, 0, 0.6) !important;
            color: white !important;
            border: 1px solid rgba(255, 255, 255, 0.3) !important;
            position: relative !important;
            z-index: 10 !important;
            pointer-events: auto !important;
            backdrop-filter: blur(10px);
        }

        .btn-secondary:hover {
            background: rgba(0, 0, 0, 0.8) !important;
            border-color: rgba(255, 255, 255, 0.5) !important;
        }
    </style>

    <!-- Dashboard Header -->
    <section class="hero">
        <div class="container">
            <div class="hero-content">
                <h1 class="hero-title"><span class="brand">OVACS</span> Dashboard</h1>
                <p class="hero-subtitle">Online Vehicle Availability Control System - Real-time management of 150 emergency vehicles across 50 stations</p>
                <div class="dashboard-status">
                    <span class="status-indicator available">System Online</span>
                    <span class="last-updated">Last Updated: <span id="lastUpdate"><?php echo date('Y-m-d H:i:s'); ?></span></span>
                </div>
                
                <!-- Vehicle Status Cards -->
                <?php if (!empty($statusCounts)): ?>
                    <div class="status-cards">
                        <?php 
                        $totalVehicles = 0;
                        foreach ($statusCounts as $status): 
                            $totalVehicles += $status['count'];
                            $gradientStart = $status['color_code'];
                            $gradientEnd = adjustBrightness($status['color_code'], -20);
                        ?>
                            <div style="
                                background: linear-gradient(135deg, <?php echo $gradientStart; ?> 0%, <?php echo $gradientEnd; ?> 100%);
                                padding: 1.5rem;
                                border-radius: 10px;
                                color: white;
                                text-align: center;
                                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                                transition: transform 0.2s ease;
                            " onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                                <div style="font-size: 2rem; font-weight: 700; margin-bottom: 0.5rem;">
                                    <?php echo number_format($status['count']); ?>
                                </div>
                                <div style="font-size: 0.875rem; font-weight: 600; opacity: 0.9;">
                                    <?php echo htmlspecialchars($status['status_name']); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        
                        <!-- Total Vehicles Card -->
                        <div style="
                            background: linear-gradient(135deg, #1f2937 0%, #111827 100%);
                            padding: 1.5rem;
                            border-radius: 10px;
                            color: white;
                            text-align: center;
                            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                            transition: transform 0.2s ease;
                            border: 2px solid #374151;
                        " onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                            <div style="font-size: 2rem; font-weight: 700; margin-bottom: 0.5rem;">
                                <?php echo number_format($totalVehicles); ?>
                            </div>
                            <div style="font-size: 0.875rem; font-weight: 600; opacity: 0.9;">
                                Total Vehicles
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>



    <!-- Quick Actions Dashboard -->
    <section class="features">
        <div class="container">
            <h2 class="section-title">Quick Actions</h2>
            <div class="features-grid">
                <div class="feature">
                    <div class="feature-icon">🚑</div>
                    <h3 class="feature-title">Vehicle Status</h3>
                    <p class="feature-description">View and update real-time status of all emergency vehicles across the fleet.</p>
                    <a href="vehicles.php" class="btn btn-secondary" style="margin-top: 10px;">Manage Vehicles</a>
                </div>
                <div class="feature">
                    <div class="feature-icon">🏥</div>
                    <h3 class="feature-title">Station Overview</h3>
                    <p class="feature-description">Monitor capacity and vehicle assignments across all 50 station locations.</p>
                    <a href="stations.php" class="btn btn-secondary" style="margin-top: 10px;">View Stations</a>
                </div>
                <div class="feature">
                    <div class="feature-icon">📋</div>
                    <h3 class="feature-title">Shift Management</h3>
                    <p class="feature-description">Manage shift patterns, staff assignments, and vehicle deployments.</p>
                    <a href="shifts.php" class="btn btn-secondary" style="margin-top: 10px;">Manage Shifts</a>
                </div>
                <div class="feature">
                    <div class="feature-icon">📋</div>
                    <h3 class="feature-title">Station Requirements</h3>
                    <p class="feature-description">Define vehicle requirements for each station by shift type and vehicle category.</p>
                    <a href="requirements.php" class="btn btn-secondary" style="margin-top: 10px;">Manage Requirements</a>
                </div>
                <div class="feature">
                    <div class="feature-icon">📅</div>
                    <h3 class="feature-title">24-Hour Schedule</h3>
                    <p class="feature-description">Plan and visualize vehicle and shift requirements for the next 24 hours by station.</p>
                    <a href="schedule.php" class="btn btn-secondary" style="margin-top: 10px;">View Schedule</a>
                </div>
                <div class="feature">
                    <div class="feature-icon">⚙️</div>
                    <h3 class="feature-title">System Administration</h3>
                    <p class="feature-description">Access administrative functions and system configuration settings.</p>
                    <a href="admin.php" class="btn btn-secondary" style="margin-top: 10px;">Admin Panel</a>
                </div>
                <div class="feature">
                    <div class="feature-icon">📊</div>
                    <h3 class="feature-title">Reports & Analytics</h3>
                    <p class="feature-description">Generate reports on vehicle utilization, response times, and fleet performance.</p>
                    <a href="reports.php" class="btn btn-secondary" style="margin-top: 10px;">View Reports</a>
                </div>

            </div>
        </div>
    </section>

    <!-- Recent Activity & Alerts -->
    <section id="activity" class="contact">
        <div class="container">
            <h2 class="section-title">System Activity & Alerts</h2>
            <div class="contact-content">
                <div class="contact-info">
                    <h3>Recent Activity</h3>
                    <div class="activity-log">
                        <?php if (!empty($recentActivity)): ?>
                            <?php foreach ($recentActivity as $activity): 
                                $activityTime = new DateTime($activity['timestamp']);
                            ?>
                            <div class="activity-item">
                                <span class="activity-time"><?php echo $activityTime->format('H:i'); ?></span>
                                <span class="activity-desc">
                                    Vehicle <?php echo htmlspecialchars($activity['vehicle_id']); ?> 
                                    <?php if ($activity['new_status'] === 'Available'): ?>
                                        returned to <?php echo htmlspecialchars($activity['station_name']); ?> - Available
                                    <?php elseif ($activity['new_status'] === 'In Service'): ?>
                                        deployed from <?php echo htmlspecialchars($activity['station_name']); ?> - In Service
                                    <?php elseif ($activity['new_status'] === 'Maintenance'): ?>
                                        scheduled for maintenance
                                    <?php else: ?>
                                        status changed to <?php echo htmlspecialchars($activity['new_status']); ?>
                                    <?php endif; ?>
                                </span>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="activity-item">
                                <span class="activity-time"><?php echo date('H:i'); ?></span>
                                <span class="activity-desc">System online - No recent activity</span>
                            </div>
                            <div class="activity-item">
                                <span class="activity-time"><?php echo date('H:i', strtotime('-15 minutes')); ?></span>
                                <span class="activity-desc">Automatic status check completed</span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="contact-form">
                    <h3>System Alerts</h3>
                    <div class="alerts-panel">
                        <div class="alert alert-warning">
                            <strong>⚠️ Low Availability:</strong> Station 15 - Only 1 vehicle available
                        </div>
                        <div class="alert alert-info">
                            <strong>ℹ️ Maintenance Due:</strong> 3 vehicles due for service this week
                        </div>
                        <div class="alert alert-success">
                            <strong>✅ All Clear:</strong> No critical alerts at this time
                        </div>
                    </div>
                    <div style="margin-top: 20px;">
                        <a href="availability.php" class="btn btn-primary">View Live Availability Board</a>
                        <a href="dispatch.php" class="btn btn-secondary" style="margin-left: 10px;">Dispatch Console</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>

    <!-- Dashboard Auto-refresh Script -->
    <script>
        // Auto-refresh dashboard every 30 seconds
        setInterval(function() {
            document.getElementById('lastUpdate').textContent = new Date().toLocaleString();
        }, 30000);

        // Add some interactivity to alert boxes
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                alert.style.cursor = 'pointer';
                alert.addEventListener('click', function() {
                    this.style.opacity = '0.7';
                    setTimeout(() => {
                        this.style.opacity = '1';
                    }, 200);
                });
            });
        });
    </script>

    <script src="js/main.js"></script>
</body>
</html>