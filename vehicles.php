<?php
/**
 * OVACS - Vehicles Management
 */

// Include database
include 'includes/database.php';

// Initialize managers
$vehicleManager = new VehicleManager();
$stationManager = new StationManager();
$statusManager = new StatusManager();

// Get filters from URL
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
    
    // Prepare redirect URL with preserved filters
    $redirectParams = [];
    if (!empty($_POST['type'])) $redirectParams['type'] = $_POST['type'];
    if (!empty($_POST['status'])) $redirectParams['status'] = $_POST['status'];
    if (!empty($_POST['station'])) $redirectParams['station'] = $_POST['station'];
    
    if ($success) {
        $redirectParams['msg'] = 'success';
    } else {
        $redirectParams['msg'] = 'error';
    }
    
    $redirectUrl = 'vehicles.php';
    if (!empty($redirectParams)) {
        $redirectUrl .= '?' . http_build_query($redirectParams);
    }
    
    header('Location: ' . $redirectUrl);
    exit;
}

// Handle success/error messages from redirects
if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'success') {
        $message = "Vehicle status updated successfully!";
    } elseif ($_GET['msg'] === 'error') {
        $error = "Failed to update vehicle status.";
    }
}

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

// Get data
try {
    $vehicles = $vehicleManager->getAllVehicles($filters);
    $stations = $stationManager->getAllStations();
    $statusTypes = $statusManager->getAllStatusTypes();
    $allVehicles = $vehicleManager->getAllVehicles(); // For count comparison
    
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
    $error = "Unable to load data: " . $e->getMessage();
    $vehicles = [];
    $stations = [];
    $statusTypes = [];
    $allVehicles = [];
    $statusCounts = [];
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
            
            <!-- Filter Controls -->
            <div style="background: #f8f9fa; padding: 20px; border-radius: 10px; margin-top: 2rem;">
                <form method="GET" action="vehicles.php" style="display: block !important;">
                    <div style="display: flex; gap: 15px; flex-wrap: wrap; align-items: end;">
                        <div>
                            <label style="display: block; margin-bottom: 5px; font-weight: 600;">Vehicle Type:</label>
                            <select name="type" style="padding: 8px 12px !important; border: 1px solid #ced4da !important; border-radius: 5px !important; background: white !important; min-width: 180px !important; pointer-events: auto !important; z-index: 999 !important; position: relative !important;">
                                <option value="">All Vehicle Types</option>
                                <option value="DCA" <?php echo ($_GET['type'] ?? '') === 'DCA' ? 'selected' : ''; ?>>Double Crewed Ambulance (DCA)</option>
                                <option value="RRV" <?php echo ($_GET['type'] ?? '') === 'RRV' ? 'selected' : ''; ?>>Rapid Response Car (RRV)</option>
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
                                <?php foreach ($statusTypes as $status): ?>
                                    <option value="<?php echo htmlspecialchars($status['status_name']); ?>" <?php echo ($_GET['status'] ?? '') === $status['status_name'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($status['status_name']); ?>
                                    </option>
                                <?php endforeach; ?>
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
                                $lastUpdated = new DateTime($vehicle['updated_at']);
                            ?>
                            <tr style="border-bottom: 1px solid #f3f4f6;">
                                <td style="padding: 1rem; font-weight: 500;"><?php echo htmlspecialchars($vehicle['vehicle_id']); ?></td>
                                <td style="padding: 1rem;"><?php echo htmlspecialchars($vehicle['type_name']); ?></td>
                                <td style="padding: 1rem;">
                                    <div style="font-weight: 500;"><?php echo htmlspecialchars($vehicle['station_name']); ?></div>
                                    <div style="font-size: 0.85rem; color: #6b7280; margin-top: 2px;">
                                        <?php 
                                        $address_parts = [];
                                        if (!empty($vehicle['station_address'])) $address_parts[] = $vehicle['station_address'];
                                        if (!empty($vehicle['station_city'])) $address_parts[] = $vehicle['station_city'];
                                        if (!empty($vehicle['station_province'])) $address_parts[] = $vehicle['station_province'];
                                        if (!empty($vehicle['station_postal_code'])) $address_parts[] = $vehicle['station_postal_code'];
                                        echo htmlspecialchars(implode(', ', $address_parts));
                                        ?>
                                    </div>
                                </td>
                                <td style="padding: 1rem;">
                                    <span style="background: <?php echo htmlspecialchars($vehicle['status_color']); ?>; color: white; padding: 0.25rem 0.75rem; border-radius: 15px; font-size: 0.85rem;">
                                        <?php echo htmlspecialchars($vehicle['status']); ?>
                                    </span>
                                </td>
                                <td style="padding: 1rem; color: #6b7280;"><?php echo $lastUpdated->format('H:i'); ?></td>
                                <td style="padding: 1rem;">
                                    <form method="POST" action="vehicles.php" style="display: inline-block;">
                                        <input type="hidden" name="action" value="update_status">
                                        <input type="hidden" name="vehicle_id" value="<?php echo htmlspecialchars($vehicle['id']); ?>">
                                        <select name="new_status" onchange="this.form.submit()" 
                                                style="padding: 0.4rem 0.8rem; border: 1px solid #ced4da; border-radius: 4px; font-size: 0.8rem; cursor: pointer;">
                                            <option value="">Update Status...</option>
                                            <?php foreach ($statusTypes as $status): ?>
                                                <option value="<?php echo htmlspecialchars($status['status_name']); ?>" 
                                                        <?php echo $vehicle['status'] === $status['status_name'] ? 'disabled' : ''; ?>>
                                                    <?php echo htmlspecialchars($status['status_name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <input type="hidden" name="reason" value="Status updated via web interface">
                                        <!-- Preserve current filters -->
                                        <?php if (!empty($_GET['type'])): ?>
                                            <input type="hidden" name="type" value="<?php echo htmlspecialchars($_GET['type']); ?>">
                                        <?php endif; ?>
                                        <?php if (!empty($_GET['status'])): ?>
                                            <input type="hidden" name="status" value="<?php echo htmlspecialchars($_GET['status']); ?>">
                                        <?php endif; ?>
                                        <?php if (!empty($_GET['station'])): ?>
                                            <input type="hidden" name="station" value="<?php echo htmlspecialchars($_GET['station']); ?>">
                                        <?php endif; ?>
                                    </form>
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