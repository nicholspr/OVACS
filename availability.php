<?php
// Database connection and error handling
$errorMessage = '';
$availabilityData = [];

try {
    include 'includes/database.php';
    $vehicleManager = new VehicleManager();
    // For now, we'll use placeholder data for availability
    $availabilityData = [
        'stations' => [
            ['id' => 1, 'name' => 'Central Station', 'available' => 8, 'in_service' => 2, 'maintenance' => 1, 'total' => 11],
            ['id' => 2, 'name' => 'North Station', 'available' => 6, 'in_service' => 3, 'maintenance' => 0, 'total' => 9],
            ['id' => 3, 'name' => 'South Station', 'available' => 5, 'in_service' => 4, 'maintenance' => 1, 'total' => 10],
            ['id' => 4, 'name' => 'East Station', 'available' => 7, 'in_service' => 1, 'maintenance' => 2, 'total' => 10],
            ['id' => 5, 'name' => 'West Station', 'available' => 4, 'in_service' => 5, 'maintenance' => 1, 'total' => 10]
        ],
        'critical_alerts' => [
            'Low availability at South Station (50%)',
            'Vehicle E-15 overdue for maintenance',
            'Heavy deployment in North sector'
        ]
    ];
} catch (Exception $e) {
    $errorMessage = "Database connection failed: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Live Availability Board - OVACS</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <meta http-equiv="refresh" content="30"> <!-- Auto refresh every 30 seconds -->
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container" style="margin-top: 100px; padding: 20px;">
        <div class="availability-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
            <div>
                <h1>Live Availability Board</h1>
                <p class="subtitle">Real-time vehicle availability across all stations - Auto-refreshes every 30 seconds</p>
            </div>
            <div class="status-info">
                <span class="status-indicator available">LIVE</span>
                <span class="last-updated">Last Updated: <?php echo date('H:i:s'); ?></span>
            </div>
        </div>

        <?php if ($errorMessage): ?>
            <div class="alert alert-error">
                <strong>Error:</strong> <?php echo htmlspecialchars($errorMessage); ?>
            </div>
        <?php endif; ?>

        <div class="action-buttons" style="margin: 20px 0;">
            <button onclick="refreshBoard()" class="btn btn-primary">🔄 Refresh Now</button>
            <button onclick="toggleFullscreen()" class="btn btn-secondary">📺 Fullscreen</button>
            <a href="dispatch.php" class="btn btn-success">🚨 Dispatch Console</a>
            <a href="index.php" class="btn btn-secondary">← Back to Dashboard</a>
        </div>

        <!-- Critical Alerts Section -->
        <div class="alerts-section" style="margin: 20px 0;">
            <h3>🚨 Critical Alerts</h3>
            <div class="alert-list">
                <?php foreach ($availabilityData['critical_alerts'] as $alert): ?>
                    <div class="alert alert-warning">
                        <strong>⚠️ Alert:</strong> <?php echo htmlspecialchars($alert); ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Station Availability Grid -->
        <div class="availability-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin: 30px 0;">
            <?php foreach ($availabilityData['stations'] as $station): 
                $availabilityPercent = round(($station['available'] / $station['total']) * 100);
                $statusClass = $availabilityPercent >= 70 ? 'good' : ($availabilityPercent >= 50 ? 'warning' : 'critical');
            ?>
                <div class="station-card <?php echo $statusClass; ?>">
                    <h3><?php echo htmlspecialchars($station['name']); ?></h3>
                    <div class="availability-percent">
                        <span class="percent-number"><?php echo $availabilityPercent; ?>%</span>
                        <span class="percent-label">Available</span>
                    </div>
                    <div class="vehicle-breakdown">
                        <div class="breakdown-item available">
                            <span class="count"><?php echo $station['available']; ?></span>
                            <span class="label">Available</span>
                        </div>
                        <div class="breakdown-item in-service">
                            <span class="count"><?php echo $station['in_service']; ?></span>
                            <span class="label">In Service</span>
                        </div>
                        <div class="breakdown-item maintenance">
                            <span class="count"><?php echo $station['maintenance']; ?></span>
                            <span class="label">Maintenance</span>
                        </div>
                    </div>
                    <div class="station-actions">
                        <button onclick="viewStationDetails(<?php echo $station['id']; ?>)" class="btn btn-small">View Details</button>
                        <button onclick="dispatchFromStation(<?php echo $station['id']; ?>)" class="btn btn-small btn-success">Quick Dispatch</button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Fleet Summary Bar -->
        <div class="fleet-summary" style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-top: 30px;">
            <h3>Fleet Overview</h3>
            <?php 
                $totalAvailable = array_sum(array_column($availabilityData['stations'], 'available'));
                $totalInService = array_sum(array_column($availabilityData['stations'], 'in_service'));
                $totalMaintenance = array_sum(array_column($availabilityData['stations'], 'maintenance'));
                $totalFleet = array_sum(array_column($availabilityData['stations'], 'total'));
            ?>
            <div class="summary-stats" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; text-align: center;">
                <div>
                    <div class="summary-number" style="font-size: 2em; color: #28a745;"><?php echo $totalAvailable; ?></div>
                    <div class="summary-label">Available</div>
                </div>
                <div>
                    <div class="summary-number" style="font-size: 2em; color: #dc3545;"><?php echo $totalInService; ?></div>
                    <div class="summary-label">In Service</div>
                </div>
                <div>
                    <div class="summary-number" style="font-size: 2em; color: #ffc107;"><?php echo $totalMaintenance; ?></div>
                    <div class="summary-label">Maintenance</div>
                </div>
                <div>
                    <div class="summary-number" style="font-size: 2em; color: #333;"><?php echo $totalFleet; ?></div>
                    <div class="summary-label">Total Fleet</div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script>
        function refreshBoard() {
            window.location.reload();
        }

        function toggleFullscreen() {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen();
            } else {
                document.exitFullscreen();
            }
        }

        function viewStationDetails(stationId) {
            window.location.href = `stations.php?id=${stationId}`;
        }

        function dispatchFromStation(stationId) {
            window.location.href = `dispatch.php?station=${stationId}`;
        }

        // Auto-update timestamp every second
        setInterval(function() {
            const now = new Date();
            document.querySelector('.last-updated').textContent = `Last Updated: ${now.toLocaleTimeString()}`;
        }, 1000);

        // Add visual indicators for status changes
        document.addEventListener('DOMContentLoaded', function() {
            const stationCards = document.querySelectorAll('.station-card');
            stationCards.forEach(card => {
                card.addEventListener('click', function() {
                    this.style.transform = 'scale(0.98)';
                    setTimeout(() => {
                        this.style.transform = 'scale(1)';
                    }, 100);
                });
            });
        });
    </script>

    <script src="js/main.js"></script>

    <style>
        .availability-grid .station-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.2s;
            border-left: 4px solid #28a745;
        }
        
        .station-card.warning {
            border-left-color: #ffc107;
        }
        
        .station-card.critical {
            border-left-color: #dc3545;
        }
        
        .availability-percent {
            text-align: center;
            margin: 15px 0;
        }
        
        .percent-number {
            display: block;
            font-size: 2.5em;
            font-weight: bold;
            color: #28a745;
        }
        
        .station-card.warning .percent-number {
            color: #ffc107;
        }
        
        .station-card.critical .percent-number {
            color: #dc3545;
        }
        
        .percent-label {
            font-size: 0.9em;
            color: #666;
        }
        
        .vehicle-breakdown {
            display: flex;
            justify-content: space-between;
            margin: 15px 0;
        }
        
        .breakdown-item {
            text-align: center;
        }
        
        .breakdown-item .count {
            display: block;
            font-weight: bold;
            font-size: 1.2em;
        }
        
        .breakdown-item.available .count {
            color: #28a745;
        }
        
        .breakdown-item.in-service .count {
            color: #dc3545;
        }
        
        .breakdown-item.maintenance .count {
            color: #ffc107;
        }
        
        .breakdown-item .label {
            font-size: 0.8em;
            color: #666;
        }
        
        .station-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        
        .status-indicator.available {
            background: #28a745;
            color: white;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 0.8em;
            font-weight: bold;
        }
        
        .last-updated {
            color: #666;
            font-size: 0.9em;
        }
    </style>
</body>
</html>