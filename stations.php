<?php
/**
 * OVACS - Station Management
 * Online Vehicle Availability Control System - Station Management Page
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database first
include 'includes/database.php';

// Initialize managers
$stationManager = new StationManager();

// Get filters from URL
$filters = [];
if (!empty($_GET['division'])) $filters['division'] = $_GET['division'];
if (!empty($_GET['postcode'])) $filters['postcode'] = $_GET['postcode'];
if (!empty($_GET['capacity_min'])) $filters['capacity_min'] = $_GET['capacity_min'];

// Get data
try {
    $stations = $stationManager->getAllStations($filters);
    $allStations = $stationManager->getAllStations(); // For count comparison
    $summary = $stationManager->getStationsSummary();
    $divisions = $stationManager->getDivisions();
} catch (Exception $e) {
    $error = "Unable to load station data: " . $e->getMessage();
    $stations = [];
    $allStations = [];
    $summary = [];
    $divisions = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OVACS - Station Management</title>
    <meta name="description" content="OVACS - Station Management System for Emergency Services">
    <link rel="stylesheet" href="css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Override any CSS that might interfere with dropdowns */
        select {
            pointer-events: auto !important;
            z-index: 9999 !important;
            position: relative !important;
            background: white !important;
            border: 1px solid #ccc !important;
            padding: 8px 12px !important;
            border-radius: 4px !important;
            font-size: 14px !important;
            line-height: normal !important;
            cursor: pointer !important;
        }
        
        select option {
            background: white !important;
            color: black !important;
            padding: 4px 8px !important;
        }
        
        form {
            z-index: 999 !important;
            position: relative !important;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin: 2rem 0;
        }
        
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: #2563eb;
        }
        
        .stat-label {
            color: #6b7280;
            font-size: 0.9rem;
            margin-top: 0.5rem;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <!-- Station Management Header -->
    <section class="hero" style="padding: 6rem 0 3rem;">
        <div class="container">
            <h1 class="hero-title">Station Management</h1>
            <p class="hero-subtitle">Monitor and manage all <?php echo count($allStations); ?> emergency service stations</p>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-warning" style="margin-top: 1rem; background: #fff3cd; color: #6c4100; padding: 10px; border-radius: 5px; border: 1px solid #ffe69c;">
                    ⚠️ <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <!-- Statistics Cards -->
            <?php if (!empty($summary)): ?>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $summary['total_stations']; ?></div>
                    <div class="stat-label">Total Stations</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $summary['total_divisions']; ?></div>
                    <div class="stat-label">Divisions Covered</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $summary['total_capacity']; ?></div>
                    <div class="stat-label">Total Vehicle Capacity</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $summary['total_vehicles']; ?></div>
                    <div class="stat-label">Active Vehicles</div>
                </div>
                <div class="stat-card" style="background: linear-gradient(135deg, #10b981, #059669);">
                    <div class="stat-number" style="color: white;"><?php echo $summary['total_available']; ?></div>
                    <div class="stat-label" style="color: rgba(255,255,255,0.9);">Available</div>
                </div>
                <div class="stat-card" style="background: linear-gradient(135deg, #ef4444, #dc2626);">
                    <div class="stat-number" style="color: white;"><?php echo $summary['total_in_service']; ?></div>
                    <div class="stat-label" style="color: rgba(255,255,255,0.9);">In Service</div>
                </div>
                <div class="stat-card" style="background: linear-gradient(135deg, #f59e0b, #d97706);">
                    <div class="stat-number" style="color: white;"><?php echo $summary['total_maintenance']; ?></div>
                    <div class="stat-label" style="color: rgba(255,255,255,0.9);">Maintenance</div>
                </div>
                <div class="stat-card" style="background: linear-gradient(135deg, #6b7280, #4b5563);">
                    <div class="stat-number" style="color: white;"><?php echo $summary['total_out_of_service']; ?></div>
                    <div class="stat-label" style="color: rgba(255,255,255,0.9);">Out of Service</div>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($filters)): ?>
                <div class="alert alert-info" style="margin-top: 1rem; background: #cff4fc; color: #055160; padding: 10px; border-radius: 5px; border: 1px solid #9eeaf9;">
                    🔍 <strong>Active Filters:</strong>
                    <?php
                    $activeFilters = [];
                    if (!empty($filters['division'])) $activeFilters[] = "Division: " . $filters['division'];
                    if (!empty($filters['postcode'])) $activeFilters[] = "Postcode: " . $filters['postcode'];
                    if (!empty($filters['capacity_min'])) $activeFilters[] = "Min Capacity: " . $filters['capacity_min'];
                    echo htmlspecialchars(implode(', ', $activeFilters));
                    ?> | Showing <?php echo count($stations); ?> of <?php echo count($allStations); ?> stations
                </div>
            <?php endif; ?>
            
            <!-- Filter Controls -->
            <div style="background: #f8f9fa; padding: 20px; border-radius: 10px; margin-top: 2rem;">
                <form method="GET" action="stations.php" style="display: block !important;">
                    <div style="display: flex; gap: 15px; flex-wrap: wrap; align-items: end;">
                        <div>
                            <label style="display: block; margin-bottom: 5px; font-weight: 600;">Division:</label>
                            <select name="division" style="padding: 8px 12px !important; border: 1px solid #ced4da !important; border-radius: 5px !important; background: white !important; min-width: 150px !important; pointer-events: auto !important; z-index: 999 !important; position: relative !important;">
                                <option value="">All Divisions</option>
                                <?php foreach ($divisions as $division): ?>
                                    <option value="<?php echo htmlspecialchars($division); ?>" <?php echo ($_GET['division'] ?? '') === $division ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($division); ?> Division
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div>
                            <label style="display: block; margin-bottom: 5px; font-weight: 600;">Postcode:</label>
                            <input type="text" name="postcode" value="<?php echo htmlspecialchars($_GET['postcode'] ?? ''); ?>" 
                                   placeholder="e.g. SW1, M1" 
                                   style="padding: 8px 12px; border: 1px solid #ced4da; border-radius: 5px; min-width: 120px;">
                        </div>
                        
                        <div>
                            <label style="display: block; margin-bottom: 5px; font-weight: 600;">Min Capacity:</label>
                            <select name="capacity_min" style="padding: 8px 12px !important; border: 1px solid #ced4da !important; border-radius: 5px !important; background: white !important; min-width: 120px !important; pointer-events: auto !important; z-index: 999 !important; position: relative !important;">
                                <option value="">Any</option>
                                <option value="3" <?php echo ($_GET['capacity_min'] ?? '') === '3' ? 'selected' : ''; ?>>3+ vehicles</option>
                                <option value="5" <?php echo ($_GET['capacity_min'] ?? '') === '5' ? 'selected' : ''; ?>>5+ vehicles</option>
                                <option value="7" <?php echo ($_GET['capacity_min'] ?? '') === '7' ? 'selected' : ''; ?>>7+ vehicles</option>
                                <option value="10" <?php echo ($_GET['capacity_min'] ?? '') === '10' ? 'selected' : ''; ?>>10+ vehicles</option>
                            </select>
                        </div>
                        
                        <div>
                            <button type="submit" style="padding: 8px 16px; background: #0066cc; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: 600; z-index: 999; position: relative;">🔍 Apply Filters</button>
                            <a href="stations.php" style="display: inline-block; padding: 8px 16px; background: #6c757d; color: white; text-decoration: none; border-radius: 5px; margin-left: 10px; font-weight: 600;">🔄 Clear</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <!-- Stations List -->
    <section style="padding: 2rem 0;">
        <div class="container">
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; background: white; border-radius: 10px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
                    <thead>
                        <tr style="background: #f8fafc; border-bottom: 2px solid #e5e7eb;">
                            <th style="padding: 1rem; text-align: left; font-weight: 600;">Station Code</th>
                            <th style="padding: 1rem; text-align: left; font-weight: 600;">Name</th>
                            <th style="padding: 1rem; text-align: left; font-weight: 600;">Division</th>
                            <th style="padding: 1rem; text-align: left; font-weight: 600;">Capacity</th>
                            <th style="padding: 1rem; text-align: left; font-weight: 600;">Vehicles</th>
                            <th style="padding: 1rem; text-align: left; font-weight: 600;">Contact</th>
                            <th style="padding: 1rem; text-align: left; font-weight: 600;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($stations)): ?>
                            <tr>
                                <td colspan="7" style="padding: 2rem; text-align: center; color: #6b7280;">
                                    <?php if (isset($error)): ?>
                                        ❌ Unable to load stations: <?php echo htmlspecialchars($error); ?>
                                    <?php elseif (!empty($filters)): ?>
                                        🔍 No stations found matching your criteria. <a href="stations.php">Clear filters</a> to see all stations.
                                    <?php else: ?>
                                        📋 No stations found.
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($stations as $station): 
                                $totalCapacity = $station['capacity_eru'] + $station['capacity_ptu'];
                                $totalVehicles = $station['total_vehicles'];
                                $utilizationPercent = $totalCapacity > 0 ? round(($totalVehicles / $totalCapacity) * 100) : 0;
                                
                                $statusColor = match(true) {
                                    $utilizationPercent >= 90 => '#ef4444', // Red - overutilized
                                    $utilizationPercent >= 70 => '#f59e0b', // Orange - high utilization
                                    $utilizationPercent >= 40 => '#10b981', // Green - good utilization
                                    default => '#6b7280' // Gray - low utilization
                                };
                            ?>
                            <tr style="border-bottom: 1px solid #f3f4f6;">
                                <td style="padding: 1rem; font-weight: 500;">
                                    <div style="font-weight: 600;"><?php echo htmlspecialchars($station['station_code']); ?></div>
                                </td>
                                <td style="padding: 1rem;">
                                    <div style="font-weight: 600;"><?php echo htmlspecialchars($station['name']); ?></div>
                                    <div style="font-size: 0.85rem; color: #6b7280;">
                                        <?php echo htmlspecialchars($station['address'] ?? ''); ?>
                                    </div>
                                </td>
                                <td style="padding: 1rem;">
                                    <div style="font-weight: 600; color: #2563eb;"><?php echo htmlspecialchars($station['division']); ?> Division</div>
                                    <div style="font-size: 0.85rem; color: #6b7280;">
                                        <?php echo htmlspecialchars($station['city'] . ', ' . $station['postcode']); ?>
                                    </div>
                                </td>
                                <td style="padding: 1rem; text-align: center;">
                                    <div style="font-weight: 600;"><?php echo $totalCapacity; ?></div>
                                    <div style="font-size: 0.8rem; color: #6b7280;">
                                        ERU: <?php echo $station['capacity_eru']; ?> | PTU: <?php echo $station['capacity_ptu']; ?>
                                    </div>
                                </td>
                                <td style="padding: 1rem; text-align: center;">
                                    <div style="font-weight: 600; color: <?php echo $statusColor; ?>;">
                                        <?php echo $totalVehicles; ?>/<?php echo $totalCapacity; ?>
                                    </div>
                                    <div style="font-size: 0.75rem; line-height: 1.2; color: #6b7280;">
                                        <?php if ($station['available_vehicles'] > 0): ?>
                                            <div style="margin: 2px 0;"><span style="color: #10b981;">✅ <?php echo $station['available_vehicles']; ?> Available</span></div>
                                        <?php endif; ?>
                                        <?php if ($station['in_service_vehicles'] > 0): ?>
                                            <div style="margin: 2px 0;"><span style="color: #ef4444;">🚨 <?php echo $station['in_service_vehicles']; ?> In Service</span></div>
                                        <?php endif; ?>
                                        <?php if ($station['maintenance_vehicles'] > 0): ?>
                                            <div style="margin: 2px 0;"><span style="color: #f59e0b;">🔧 <?php echo $station['maintenance_vehicles']; ?> Maintenance</span></div>
                                        <?php endif; ?>
                                        <?php if ($station['out_of_service_vehicles'] > 0): ?>
                                            <div style="margin: 2px 0;"><span style="color: #6b7280;">❌ <?php echo $station['out_of_service_vehicles']; ?> Out of Service</span></div>
                                        <?php endif; ?>
                                        <?php if ($totalVehicles == 0): ?>
                                            <div style="color: #9ca3af; font-style: italic;">No vehicles assigned</div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td style="padding: 1rem; font-size: 0.85rem;">
                                    <?php if ($station['phone']): ?>
                                        <div>📞 <?php echo htmlspecialchars($station['phone']); ?></div>
                                    <?php endif; ?>
                                    <?php if ($station['email']): ?>
                                        <div>📧 <?php echo htmlspecialchars($station['email']); ?></div>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 1rem; text-align: center;">
                                    <span style="background: <?php echo $statusColor; ?>; color: white; padding: 0.25rem 0.75rem; border-radius: 15px; font-size: 0.8rem;">
                                        <?php echo $utilizationPercent; ?>% Used
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>
</body>
</html>