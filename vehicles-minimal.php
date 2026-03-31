<?php
/**
 * Vehicles Page Debug - Minimal Version  
 * Test to isolate the dropdown/form issue
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start with basic includes
include 'includes/database.php';

$vehicleManager = new VehicleManager();
$stationManager = new StationManager();

// Debug: Show what we received
echo "<h2>🔍 Form Submission Debug</h2>";
echo "<div style='background: #f0f0f0; padding: 10px; margin: 10px 0;'>";
echo "<strong>GET Parameters:</strong><br>";
echo "<pre>" . print_r($_GET, true) . "</pre>";
echo "</div>";

// Get filters
$filters = [];
if (!empty($_GET['type'])) $filters['type'] = $_GET['type'];
if (!empty($_GET['status'])) $filters['status'] = $_GET['status'];  
if (!empty($_GET['station'])) $filters['station'] = $_GET['station'];

echo "<div style='background: #e8f5e8; padding: 10px; margin: 10px 0;'>";
echo "<strong>Applied Filters:</strong><br>";
echo "<pre>" . print_r($filters, true) . "</pre>";
echo "</div>";

try {
    $vehicles = $vehicleManager->getAllVehicles($filters);
    $stations = $stationManager->getAllStations();
    
    echo "<div style='background: #e8f4f8; padding: 10px; margin: 10px 0;'>";
    echo "<strong>Results:</strong> " . count($vehicles) . " vehicles found<br>";
    echo "<strong>Stations available:</strong> " . count($stations) . "<br>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background: #ffe8e8; padding: 10px; margin: 10px 0;'>";
    echo "<strong>Error:</strong> " . $e->getMessage();
    echo "</div>";
    $vehicles = [];
    $stations = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vehicles Debug - Minimal</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
        .form-container { background: #f9f9f9; padding: 20px; border-radius: 8px; margin: 20px 0; }
        select, button { padding: 8px 12px; margin: 5px; border: 1px solid #ccc; border-radius: 4px; font-size: 14px; }
        button { background: #007cba; color: white; cursor: pointer; }
        button:hover { background: #005a8a; }
        .clear-btn { background: #6c757d; text-decoration: none; display: inline-block; }
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h1>🚗 Vehicles Debug Page (No External Dependencies)</h1>
    
    <div class="form-container">
        <h3>Filter Form Test</h3>
        <form method="GET" action="" style="border: 2px solid #007cba; padding: 15px; border-radius: 5px;">
            <div style="margin-bottom: 10px;">
                <label for="type"><strong>Vehicle Type:</strong></label><br>
                <select name="type" id="type">
                    <option value="">-- Select Type --</option>
                    <option value="ERU" <?php echo ($_GET['type'] ?? '') === 'ERU' ? 'selected' : ''; ?>>Emergency Response Units (ERU)</option>
                    <option value="PTU" <?php echo ($_GET['type'] ?? '') === 'PTU' ? 'selected' : ''; ?>>Patient Transport Units (PTU)</option>
                </select>
            </div>
            
            <div style="margin-bottom: 10px;">
                <label for="station"><strong>Station:</strong></label><br>
                <select name="station" id="station">
                    <option value="">-- Select Station --</option>
                    <?php foreach ($stations as $station): ?>
                        <option value="<?php echo $station['id']; ?>" <?php echo ($_GET['station'] ?? '') == $station['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($station['station_code'] . ' - ' . $station['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div style="margin-bottom: 10px;">
                <label for="status"><strong>Status:</strong></label><br>
                <select name="status" id="status">
                    <option value="">-- Select Status --</option>
                    <option value="Available" <?php echo ($_GET['status'] ?? '') === 'Available' ? 'selected' : ''; ?>>Available</option>
                    <option value="In Service" <?php echo ($_GET['status'] ?? '') === 'In Service' ? 'selected' : ''; ?>>In Service</option>
                    <option value="Out of Service" <?php echo ($_GET['status'] ?? '') === 'Out of Service' ? 'selected' : ''; ?>>Out of Service</option>
                    <option value="Maintenance" <?php echo ($_GET['status'] ?? '') === 'Maintenance' ? 'selected' : ''; ?>>Maintenance</option>
                </select>
            </div>
            
            <div style="margin-top: 15px;">
                <button type="submit">🔍 Apply Filters</button>
                <a href="?" class="clear-btn">🔄 Clear All</a>
            </div>
        </form>
        
        <div style="margin-top: 15px; font-size: 12px; color: #666;">
            <strong>Current URL:</strong> <?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>
        </div>
    </div>
    
    <h3>📊 Results (<?php echo count($vehicles); ?> vehicles)</h3>
    
    <?php if (!empty($filters)): ?>
        <div style="background: #d4edda; padding: 10px; border: 1px solid #c3e6cb; border-radius: 4px; margin: 10px 0;">
            <strong>🔍 Active Filters:</strong>
            <?php
            $activeFilters = [];
            if (!empty($filters['type'])) $activeFilters[] = "Type: " . $filters['type'];
            if (!empty($filters['status'])) $activeFilters[] = "Status: " . $filters['status'];
            if (!empty($filters['station'])) {
                foreach ($stations as $station) {
                    if ($station['id'] == $filters['station']) {
                        $activeFilters[] = "Station: " . $station['name'];
                        break;
                    }
                }
            }
            echo htmlspecialchars(implode(' | ', $activeFilters));
            ?>
        </div>
    <?php endif; ?>
    
    <table>
        <thead>
            <tr>
                <th>Vehicle ID</th>
                <th>Type</th>
                <th>Status</th>
                <th>Station</th>
                <th>Last Updated</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($vehicles)): ?>
                <tr>
                    <td colspan="5" style="text-align: center; padding: 20px; color: #999;">
                        No vehicles match your criteria.
                    </td>
                </tr>
            <?php else: ?>
                <?php $count = 0; ?>
                <?php foreach ($vehicles as $vehicle): ?>
                    <?php if (++$count > 20) break; // Limit to first 20 for debugging ?>
                    <tr>
                        <td><?php echo htmlspecialchars($vehicle['vehicle_id']); ?></td>
                        <td><?php echo htmlspecialchars($vehicle['type_name']); ?></td>
                        <td><?php echo htmlspecialchars($vehicle['status']); ?></td>
                        <td><?php echo htmlspecialchars($vehicle['station_name']); ?></td>
                        <td><?php echo htmlspecialchars($vehicle['updated_at']); ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (count($vehicles) > 20): ?>
                    <tr>
                        <td colspan="5" style="text-align: center; font-style: italic; color: #666;">
                            ... and <?php echo count($vehicles) - 20; ?> more vehicles
                        </td>
                    </tr>
                <?php endif; ?>
            <?php endif; ?>
        </tbody>
    </table>
    
    <div style="margin-top: 30px; padding: 15px; background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 4px;">
        <h4>🔧 Troubleshooting</h4>
        <p><strong>If this page works but vehicles.php doesn't:</strong></p>
        <ul>
            <li>Check for JavaScript conflicts in main.js or other scripts</li>
            <li>Check CSS conflicts in style.css</li>
            <li>Compare included files between this and vehicles.php</li>
            <li>Check browser dev tools for console errors</li>
        </ul>
    </div>
    
    <p style="margin-top: 20px;">
        <a href="vehicles.php">← Back to Main Vehicles Page</a> | 
        <a href="vehicle-filter-test.php">Test Page</a> |
        <a href="index.php">Home</a>
    </p>
</body>
</html>