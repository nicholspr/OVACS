<?php
/**
 * Dropdown Test - No CSS/JS interference
 */
include 'includes/database.php';

$vehicleManager = new VehicleManager();
$stationManager = new StationManager();

$filters = [];
if (!empty($_GET['type'])) $filters['type'] = $_GET['type'];
if (!empty($_GET['status'])) $filters['status'] = $_GET['status'];
if (!empty($_GET['station'])) $filters['station'] = $_GET['station'];

try {
    $vehicles = $vehicleManager->getAllVehicles($filters);
    $stations = $stationManager->getAllStations();
} catch (Exception $e) {
    $vehicles = [];
    $stations = [];
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Dropdown Test - No Styling</title>
</head>
<body>
    <h1>Dropdown Test - Pure HTML (No CSS/JS)</h1>
    
    <div style="border: 2px solid blue; padding: 20px; margin: 20px 0;">
        <h2>Current URL Parameters:</h2>
        <pre><?php print_r($_GET); ?></pre>
        
        <h2>Applied Filters:</h2>
        <pre><?php print_r($filters); ?></pre>
        
        <h2>Results:</h2>
        <p>Found <?php echo count($vehicles); ?> vehicles</p>
    </div>
    
    <div style="border: 2px solid red; padding: 20px; margin: 20px 0;">
        <h2>Filter Form Test</h2>
        <form method="GET" action="" style="border: 1px solid green; padding: 15px;">
            <p>
                <label>Vehicle Type:</label><br>
                <select name="type">
                    <option value="">All Types</option>
                    <option value="ERU" <?php echo ($_GET['type'] ?? '') === 'ERU' ? 'selected' : ''; ?>>ERU</option>
                    <option value="PTU" <?php echo ($_GET['type'] ?? '') === 'PTU' ? 'selected' : ''; ?>>PTU</option>
                </select>
            </p>
            
            <p>
                <label>Station:</label><br>
                <select name="station">
                    <option value="">All Stations</option>
                    <?php foreach ($stations as $station): ?>
                        <option value="<?php echo $station['id']; ?>" <?php echo ($_GET['station'] ?? '') == $station['id'] ? 'selected' : ''; ?>>
                            Station <?php echo htmlspecialchars($station['station_code']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </p>
            
            <p>
                <label>Status:</label><br>
                <select name="status">
                    <option value="">All Status</option>
                    <option value="Available" <?php echo ($_GET['status'] ?? '') === 'Available' ? 'selected' : ''; ?>>Available</option>
                    <option value="In Service" <?php echo ($_GET['status'] ?? '') === 'In Service' ? 'selected' : ''; ?>>In Service</option>
                </select>
            </p>
            
            <p>
                <button type="submit">APPLY FILTERS</button>
                <a href="?" style="margin-left: 10px;">CLEAR</a>
            </p>
        </form>
    </div>
    
    <div style="border: 2px solid purple; padding: 20px; margin: 20px 0;">
        <h2>Quick Links Test</h2>
        <p>
            <a href="?type=ERU">Test ERU Filter</a> |
            <a href="?type=PTU">Test PTU Filter</a> |
            <a href="?status=Available">Test Available Filter</a> |
            <a href="?">Clear All</a>
        </p>
    </div>
    
    <h2>Results (<?php echo count($vehicles); ?> vehicles)</h2>
    <table border="1" style="border-collapse: collapse;">
        <tr>
            <th>Vehicle ID</th>
            <th>Type</th>
            <th>Station</th>
            <th>Status</th>
        </tr>
        <?php if (empty($vehicles)): ?>
            <tr>
                <td colspan="4">No vehicles found</td>
            </tr>
        <?php else: ?>
            <?php foreach (array_slice($vehicles, 0, 10) as $vehicle): ?>
            <tr>
                <td><?php echo htmlspecialchars($vehicle['vehicle_id']); ?></td>
                <td><?php echo htmlspecialchars($vehicle['type_name']); ?></td>
                <td><?php echo htmlspecialchars($vehicle['station_name']); ?></td>
                <td><?php echo htmlspecialchars($vehicle['status']); ?></td>
            </tr>
            <?php endforeach; ?>
            <?php if (count($vehicles) > 10): ?>
            <tr>
                <td colspan="4">... and <?php echo count($vehicles) - 10; ?> more</td>
            </tr>
            <?php endif; ?>
        <?php endif; ?>
    </table>
    
    <p><a href="vehicles.php">Back to Main Vehicles Page</a></p>
</body>
</html>