<?php
/**
 * OVACS - Vehicles Management (Fixed Version)
 * This version matches the working test page structure
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database first (like working test pages)
include 'includes/database.php';

// Initialize managers
$vehicleManager = new VehicleManager();
$stationManager = new StationManager();

// Get filters from URL (exactly like test pages)
$filters = [];
if (!empty($_GET['type'])) $filters['type'] = $_GET['type'];
if (!empty($_GET['status'])) $filters['status'] = $_GET['status'];
if (!empty($_GET['station'])) $filters['station'] = $_GET['station'];

// Handle status updates
if (isset($_POST['action']) && $_POST['action'] === 'update_status' && !empty($_POST['vehicle_id']) && !empty($_POST['new_status'])) {
    $success = $vehicleManager->updateVehicleStatus(
        $_POST['vehicle_id'],
        $_POST['new_status'],
        'Web User', // In real app, use logged-in user
        $_POST['reason'] ?? ''
    );
    
    if ($success) {
        $message = "Vehicle status updated successfully!";
    } else {
        $error = "Failed to update vehicle status.";
    }
}

// Get data
try {
    $vehicles = $vehicleManager->getAllVehicles($filters);
    $stations = $stationManager->getAllStations();
    $allVehicles = $vehicleManager->getAllVehicles(); // For count comparison
} catch (Exception $e) {
    $error = "Unable to load data: " . $e->getMessage();
    $vehicles = [];
    $stations = [];
    $allVehicles = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OVACS - Vehicle Management</title>
    <meta name="description" content="OVACS - Vehicle Management System for Emergency Services">
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
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <!-- Vehicle Management Header -->
    <section class="hero" style="padding: 6rem 0 3rem;">
        <div class="container">
            <h1 class="hero-title">Vehicle Management</h1>
            <p class="hero-subtitle">Monitor and manage all <?php echo count($allVehicles); ?> emergency vehicles across the fleet</p>
            
            <?php if (isset($message)): ?>
                <div class="alert alert-success" style="margin-top: 1rem; background: #d1e7dd; color: #0a3622; padding: 10px; border-radius: 5px; border: 1px solid #a3dcb0;">
                    ✅ <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-warning" style="margin-top: 1rem; background: #fff3cd; color: #6c4100; padding: 10px; border-radius: 5px; border: 1px solid #ffe69c;">
                    ⚠️ <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($filters)): ?>
                <div class="alert alert-info" style="margin-top: 1rem; background: #cff4fc; color: #055160; padding: 10px; border-radius: 5px; border: 1px solid #9eeaf9;">
                    🔍 <strong>Active Filters:</strong>
                    <?php
                    $activeFilters = [];
                    if (!empty($filters['type'])) $activeFilters[] = "Type: " . $filters['type'];
                    if (!empty($filters['status'])) $activeFilters[] = "Status: " . $filters['status'];
                    if (!empty($filters['station'])) {
                        $stationName = '';
                        foreach ($stations as $station) {
                            if ($station['id'] == $filters['station']) {
                                $stationName = $station['name'];
                                break;
                            }
                        }
                        $activeFilters[] = "Station: " . $stationName;
                    }
                    echo htmlspecialchars(implode(', ', $activeFilters));
                    ?> | Showing <?php echo count($vehicles); ?> of <?php echo count($allVehicles); ?> vehicles
                </div>
            <?php endif; ?>
            
            <!-- Filter Controls - Simplified Form (Like Test Pages) -->
            <div style="background: #f8f9fa; padding: 20px; border-radius: 10px; margin-top: 2rem;">
                <form method="GET" action="vehicles.php" style="display: block !important;">
                    <div style="display: flex; gap: 15px; flex-wrap: wrap; align-items: end;">
                        <div>
                            <label style="display: block; margin-bottom: 5px; font-weight: 600;">Vehicle Type:</label>
                            <select name="type" style="padding: 8px 12px !important; border: 1px solid #ced4da !important; border-radius: 5px !important; background: white !important; min-width: 180px !important; pointer-events: auto !important; z-index: 999 !important; position: relative !important;">
                                <option value="">All Vehicle Types</option>
                                <option value="ERU" <?php echo ($_GET['type'] ?? '') === 'ERU' ? 'selected' : ''; ?>>Emergency Response Units (ERU)</option>
                                <option value="PTU" <?php echo ($_GET['type'] ?? '') === 'PTU' ? 'selected' : ''; ?>>Patient Transport Units (PTU)</option>
                            </select>
                        </div>
                        
                        <div>
                            <label style="display: block; margin-bottom: 5px; font-weight: 600;">Station:</label>
                            <select name="station" style="padding: 8px 12px !important; border: 1px solid #ced4da !important; border-radius: 5px !important; background: white !important; min-width: 200px !important; pointer-events: auto !important; z-index: 999 !important; position: relative !important;">
                                <option value="">All Stations</option>
                                <?php foreach ($stations as $station): ?>
                                    <option value="<?php echo $station['id']; ?>" <?php echo ($_GET['station'] ?? '') == $station['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($station['station_code'] . ' - ' . $station['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div>
                            <label style="display: block; margin-bottom: 5px; font-weight: 600;">Status:</label>
                            <select name="status" style="padding: 8px 12px !important; border: 1px solid #ced4da !important; border-radius: 5px !important; background: white !important; min-width: 150px !important; pointer-events: auto !important; z-index: 999 !important; position: relative !important;">
                                <option value="">All Status</option>
                                <option value="Available" <?php echo ($_GET['status'] ?? '') === 'Available' ? 'selected' : ''; ?>>Available</option>
                                <option value="In Service" <?php echo ($_GET['status'] ?? '') === 'In Service' ? 'selected' : ''; ?>>In Service</option>
                                <option value="Out of Service" <?php echo ($_GET['status'] ?? '') === 'Out of Service' ? 'selected' : ''; ?>>Out of Service</option>
                                <option value="Maintenance" <?php echo ($_GET['status'] ?? '') === 'Maintenance' ? 'selected' : ''; ?>>Maintenance</option>
                            </select>
                        </div>
                        
                        <div>
                            <button type="submit" style="padding: 8px 16px; background: #0066cc; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: 600; z-index: 999; position: relative;">🔍 Apply Filters</button>
                            <a href="vehicles.php" style="display: inline-block; padding: 8px 16px; background: #6c757d; color: white; text-decoration: none; border-radius: 5px; margin-left: 10px; font-weight: 600;">🔄 Clear</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <!-- Vehicle List -->
    <section style="padding: 2rem 0;">
        <div class="container">
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; background: white; border-radius: 10px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
                    <thead>
                        <tr style="background: #f8fafc; border-bottom: 2px solid #e5e7eb;">
                            <th style="padding: 1rem; text-align: left; font-weight: 600;">Vehicle ID</th>
                            <th style="padding: 1rem; text-align: left; font-weight: 600;">Type</th>
                            <th style="padding: 1rem; text-align: left; font-weight: 600;">Station</th>
                            <th style="padding: 1rem; text-align: left; font-weight: 600;">Status</th>
                            <th style="padding: 1rem; text-align: left; font-weight: 600;">Last Updated</th>
                            <th style="padding: 1rem; text-align: left; font-weight: 600;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($vehicles)): ?>
                            <tr>
                                <td colspan="6" style="padding: 2rem; text-align: center; color: #6b7280;">
                                    <?php if (isset($error)): ?>
                                        ❌ Unable to load vehicles: <?php echo htmlspecialchars($error); ?>
                                    <?php elseif (!empty($filters)): ?>
                                        🔍 No vehicles found matching your criteria. <a href="vehicles.php">Clear filters</a> to see all vehicles.
                                    <?php else: ?>
                                        📋 No vehicles found.
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($vehicles as $vehicle): 
                                $statusColor = match($vehicle['status']) {
                                    'Available' => '#10b981',
                                    'In Service' => '#ef4444',
                                    'Maintenance' => '#f59e0b',
                                    'Out of Service' => '#6b7280',
                                    default => '#6b7280'
                                };
                                $lastUpdated = new DateTime($vehicle['updated_at']);
                            ?>
                            <tr style="border-bottom: 1px solid #f3f4f6;">
                                <td style="padding: 1rem; font-weight: 500;"><?php echo htmlspecialchars($vehicle['vehicle_id']); ?></td>
                                <td style="padding: 1rem;"><?php echo htmlspecialchars($vehicle['type_name']); ?></td>
                                <td style="padding: 1rem;"><?php echo htmlspecialchars($vehicle['station_name']); ?></td>
                                <td style="padding: 1rem;">
                                    <span style="background: <?php echo $statusColor; ?>; color: white; padding: 0.25rem 0.75rem; border-radius: 15px; font-size: 0.85rem;">
                                        <?php echo htmlspecialchars($vehicle['status']); ?>
                                    </span>
                                </td>
                                <td style="padding: 1rem; color: #6b7280;"><?php echo $lastUpdated->format('H:i'); ?></td>
                                <td style="padding: 1rem;">
                                    <button onclick="alert('Status update functionality temporarily disabled for debugging')" 
                                            style="padding: 0.4rem 0.8rem; background: #6c757d; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 0.8rem;">
                                        Update Status
                                    </button>
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

    <!-- JavaScript temporarily disabled for debugging -->
</body>
</html>