<?php
/**
 * Vehicle Filter Test
 * Debug filtering functionality
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'includes/header.php'; 
include 'includes/database.php';

$vehicleManager = new VehicleManager();
$stationManager = new StationManager();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Filter Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .debug { background: #f0f0f0; padding: 15px; margin: 10px 0; border: 1px solid #ccc; }
        .filter-form { background: #e8f4f8; padding: 15px; margin: 10px 0; }
        table { border-collapse: collapse; width: 100%; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h1>🔧 Vehicle Filter Test</h1>
    
    <div class="debug">
        <h3>Current URL Parameters:</h3>
        <pre><?php print_r($_GET); ?></pre>
    </div>
    
    <?php
    // Get filters from URL
    $filters = [];
    if (!empty($_GET['type'])) $filters['type'] = $_GET['type'];
    if (!empty($_GET['status'])) $filters['status'] = $_GET['status'];  
    if (!empty($_GET['station'])) $filters['station'] = $_GET['station'];
    
    echo "<div class='debug'>";
    echo "<h3>Applied Filters:</h3>";
    echo "<pre>" . print_r($filters, true) . "</pre>";
    echo "</div>";
    
    try {
        $vehicles = $vehicleManager->getAllVehicles($filters);
        $stations = $stationManager->getAllStations();
        $allVehicles = $vehicleManager->getAllVehicles(); // No filters for comparison
        
        echo "<div class='debug'>";
        echo "<strong>Results:</strong><br>";
        echo "Filtered vehicles: " . count($vehicles) . "<br>";
        echo "Total vehicles: " . count($allVehicles) . "<br>";
        echo "Available stations: " . count($stations) . "<br>";
        echo "</div>";
        
    } catch (Exception $e) {
        echo "<div class='debug'><strong>Error:</strong> " . $e->getMessage() . "</div>";
        $vehicles = [];
        $stations = [];
    }
    ?>
    
    <div class="filter-form">
        <h3>🔍 Filter Controls (Enhanced)</h3>
        <form method="GET" action="vehicle-filter-test.php">
            <label>Vehicle Type:</label>
            <select name="type">
                <option value="">All Vehicle Types</option>
                <option value="ERU" <?php echo ($_GET['type'] ?? '') === 'ERU' ? 'selected' : ''; ?>>Emergency Response Units (ERU)</option>
                <option value="PTU" <?php echo ($_GET['type'] ?? '') === 'PTU' ? 'selected' : ''; ?>>Patient Transport Units (PTU)</option>
            </select>
            
            <label>Station:</label>
            <select name="station">
                <option value="">All Stations</option>
                <?php foreach ($stations as $station): ?>
                    <option value="<?php echo $station['id']; ?>" <?php echo ($_GET['station'] ?? '') == $station['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($station['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            
            <label>Status:</label>
            <select name="status">
                <option value="">All Status</option>
                <option value="Available" <?php echo ($_GET['status'] ?? '') === 'Available' ? 'selected' : ''; ?>>Available</option>
                <option value="In Service" <?php echo ($_GET['status'] ?? '') === 'In Service' ? 'selected' : ''; ?>>In Service</option>
                <option value="Out of Service" <?php echo ($_GET['status'] ?? '') === 'Out of Service' ? 'selected' : ''; ?>>Out of Service</option>
                <option value="Maintenance" <?php echo ($_GET['status'] ?? '') === 'Maintenance' ? 'selected' : ''; ?>>Maintenance</option>
            </select>
            
            <button type="submit">Apply Filters</button>
            <a href="vehicle-filter-test.php">Clear Filters</a>
        </form>
        
        <h4>Quick Test Links:</h4>
        <p>
            <a href="vehicle-filter-test.php?type=ERU">Filter by ERU</a> |
            <a href="vehicle-filter-test.php?type=PTU">Filter by PTU</a> |
            <a href="vehicle-filter-test.php?status=Available">Available Only</a> |
            <a href="vehicle-filter-test.php?status=In%20Service">In Service Only</a>
        </p>
    </div>
    
    <h3>📊 Vehicle Results (<?php echo count($vehicles); ?> vehicles)</h3>
    <table>
        <thead>
            <tr>
                <th>Vehicle ID</th>
                <th>Type</th>
                <th>Type Code</th>
                <th>Station</th>
                <th>Status</th>
                <th>Updated</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($vehicles)): ?>
                <tr>
                    <td colspan="6" style="text-align: center; padding: 2rem;">
                        No vehicles found matching your criteria.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($vehicles as $vehicle): ?>
                <tr>
                    <td><?php echo htmlspecialchars($vehicle['vehicle_id']); ?></td>
                    <td><?php echo htmlspecialchars($vehicle['type_name']); ?></td>
                    <td><?php echo htmlspecialchars($vehicle['type_code']); ?></td>
                    <td><?php echo htmlspecialchars($vehicle['station_name']); ?></td>
                    <td><?php echo htmlspecialchars($vehicle['status']); ?></td>
                    <td><?php echo htmlspecialchars($vehicle['updated_at']); ?></td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
    
    <p><a href="vehicles.php">← Back to Vehicles Page</a> | <a href="index.php">Home</a></p>
</body>
</html>