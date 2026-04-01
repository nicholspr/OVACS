<?php
require_once 'includes/common.php';
require_once 'includes/ui_components.php';
include 'includes/database.php';

$success_message = '';
$error_message = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo = DatabaseConfig::getConnection();
    
    try {
        if (isset($_POST['action'])) {
            if ($_POST['action'] === 'add_vehicle') {
                // Add new vehicle
                $stmt = $pdo->prepare("
                    INSERT INTO vehicles (vehicle_id, type_id, station_id, make, model, year, registration, mileage, status_id, last_service_date, next_service_date, service_interval_km, notes) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    strtoupper($_POST['vehicle_id']),
                    $_POST['type_id'],
                    $_POST['station_id'],
                    $_POST['make'],
                    $_POST['model'],
                    $_POST['year'],
                    strtoupper($_POST['registration']),
                    $_POST['mileage'],
                    $_POST['status_id'],
                    $_POST['last_service_date'] ?: null,
                    $_POST['next_service_date'] ?: null,
                    $_POST['service_interval_km'],
                    $_POST['notes']
                ]);
                $success_message = "✅ Vehicle added successfully!";
            }
            elseif ($_POST['action'] === 'edit_vehicle') {
                // Edit vehicle
                $stmt = $pdo->prepare("
                    UPDATE vehicles 
                    SET vehicle_id = ?, type_id = ?, station_id = ?, make = ?, model = ?, year = ?, 
                        registration = ?, mileage = ?, status_id = ?, last_service_date = ?, 
                        next_service_date = ?, service_interval_km = ?, notes = ?, updated_at = CURRENT_TIMESTAMP
                    WHERE id = ?
                ");
                $stmt->execute([
                    strtoupper($_POST['vehicle_id']),
                    $_POST['type_id'],
                    $_POST['station_id'],
                    $_POST['make'],
                    $_POST['model'],
                    $_POST['year'],
                    strtoupper($_POST['registration']),
                    $_POST['mileage'],
                    $_POST['status_id'],
                    $_POST['last_service_date'] ?: null,
                    $_POST['next_service_date'] ?: null,
                    $_POST['service_interval_km'],
                    $_POST['notes'],
                    $_POST['id']
                ]);
                $success_message = "✅ Vehicle updated successfully!";
            }
            elseif ($_POST['action'] === 'delete_vehicle') {
                // Delete vehicle
                $stmt = $pdo->prepare("DELETE FROM vehicles WHERE id = ?");
                $stmt->execute([$_POST['id']]);
                $success_message = "✅ Vehicle deleted successfully!";
            }
        }
    } catch (PDOException $e) {
        $error_message = "⚠️ Error: " . $e->getMessage();
    }
}

// Fetch all vehicles with related data
$pdo = DatabaseConfig::getConnection();
$stmt = $pdo->query("
    SELECT v.*, 
           vt.type_name, vt.type_code,
           s.name as station_name, s.station_code,
           st.status_name, st.color_code
    FROM vehicles v
    LEFT JOIN vehicle_types vt ON v.type_id = vt.id
    LEFT JOIN stations s ON v.station_id = s.id  
    LEFT JOIN status_types st ON v.status_id = st.id
    ORDER BY v.vehicle_id
");
$vehicles = $stmt->fetchAll();

// Fetch vehicle types for dropdown
$stmt = $pdo->query("SELECT * FROM vehicle_types ORDER BY type_name");
$vehicle_types = $stmt->fetchAll();

// Fetch stations for dropdown
$stmt = $pdo->query("SELECT * FROM stations WHERE is_active = 1 ORDER BY name");
$stations = $stmt->fetchAll();

// Fetch status types for dropdown
$stmt = $pdo->query("SELECT * FROM status_types ORDER BY status_name");
$status_types = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OVACS - Vehicle Management</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <section class="hero">
        <div class="container">
            <h1 class="hero-title">Vehicle Management</h1>
            <p class="hero-subtitle">Manage vehicle records and fleet information</p>
            
            <?php if ($success_message): ?>
                <div style="background-color: #d1e7dd; color: #0a3622; padding: 1rem; border-radius: 0.5rem; margin: 1rem 0;">
                    <?php echo $success_message; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($error_message): ?>
                <div style="background-color: #f8d7da; color: #722c24; padding: 1rem; border-radius: 0.5rem; margin: 1rem 0;">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <main class="container" style="padding: 2rem 0;">
        <!-- Action Buttons -->
        <div style="background: #f8f9fa; padding: 1.5rem; border-radius: 0.75rem; margin-bottom: 2rem;">
            <button onclick="showAddVehicleForm()" style="background: #059669; color: white; border: none; padding: 0.75rem 1.5rem; border-radius: 0.5rem; cursor: pointer; font-weight: 500;">
                ➕ Add New Vehicle
            </button>
        </div>

        <!-- Vehicles Table -->
        <div style="background: white; border-radius: 0.75rem; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); overflow: hidden;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #f8f9fa;">
                        <th style="padding: 0.75rem 1rem; text-align: left; font-weight: 600; color: #495057; font-size: 0.875rem;">Vehicle ID</th>
                        <th style="padding: 0.75rem 1rem; text-align: left; font-weight: 600; color: #495057; font-size: 0.875rem;">Type</th>
                        <th style="padding: 0.75rem 1rem; text-align: left; font-weight: 600; color: #495057; font-size: 0.875rem;">Station</th>
                        <th style="padding: 0.75rem 1rem; text-align: left; font-weight: 600; color: #495057; font-size: 0.875rem;">Make/Model</th>
                        <th style="padding: 0.75rem 1rem; text-align: left; font-weight: 600; color: #495057; font-size: 0.875rem;">Year</th>
                        <th style="padding: 0.75rem 1rem; text-align: left; font-weight: 600; color: #495057; font-size: 0.875rem;">Registration</th>
                        <th style="padding: 0.75rem 1rem; text-align: left; font-weight: 600; color: #495057; font-size: 0.875rem;">Status</th>
                        <th style="padding: 0.75rem 1rem; text-align: left; font-weight: 600; color: #495057; font-size: 0.875rem;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($vehicles as $vehicle): ?>
                    <tr style="border-bottom: 1px solid #e5e7eb;">
                        <td style="padding: 0.75rem 1rem; font-size: 0.875rem;">
                            <span style="background: #2563eb; color: white; padding: 0.25rem 0.5rem; border-radius: 0.25rem; font-size: 0.75rem; font-weight: 600;">
                                <?php echo htmlspecialchars($vehicle['vehicle_id']); ?>
                            </span>
                        </td>
                        <td style="padding: 0.75rem 1rem; font-size: 0.875rem;">
                            <span style="background: #059669; color: white; padding: 0.25rem 0.5rem; border-radius: 0.25rem; font-size: 0.75rem; font-weight: 600;">
                                <?php echo htmlspecialchars($vehicle['type_code']); ?>
                            </span>
                            <br><small style="color: #6b7280;"><?php echo htmlspecialchars($vehicle['type_name']); ?></small>
                        </td>
                        <td style="padding: 0.75rem 1rem; font-size: 0.875rem;">
                            <strong><?php echo htmlspecialchars($vehicle['station_code']); ?></strong>
                            <br><small style="color: #6b7280;"><?php echo htmlspecialchars($vehicle['station_name']); ?></small>
                        </td>
                        <td style="padding: 0.75rem 1rem; font-size: 0.875rem;">
                            <?php echo htmlspecialchars(($vehicle['make'] ?? '') . ' ' . ($vehicle['model'] ?? '')); ?>
                        </td>
                        <td style="padding: 0.75rem 1rem; font-size: 0.875rem;">
                            <?php echo htmlspecialchars($vehicle['year']); ?>
                        </td>
                        <td style="padding: 0.75rem 1rem; font-size: 0.875rem;">
                            <?php echo htmlspecialchars($vehicle['registration']); ?>
                        </td>
                        <td style="padding: 0.75rem 1rem; font-size: 0.875rem;">
                            <span style="background: <?php echo htmlspecialchars($vehicle['color_code']); ?>; color: white; padding: 0.25rem 0.5rem; border-radius: 0.25rem; font-size: 0.75rem; font-weight: 600;">
                                <?php echo htmlspecialchars($vehicle['status_name']); ?>
                            </span>
                        </td>
                        <td style="padding: 0.75rem 1rem; font-size: 0.875rem;">
                            <button class="edit-btn" 
                                    data-id="<?php echo $vehicle['id']; ?>"
                                    data-vehicle-id="<?php echo htmlspecialchars($vehicle['vehicle_id']); ?>"
                                    data-type-id="<?php echo $vehicle['type_id']; ?>"
                                    data-station-id="<?php echo $vehicle['station_id']; ?>"
                                    data-make="<?php echo htmlspecialchars($vehicle['make'] ?? ''); ?>"
                                    data-model="<?php echo htmlspecialchars($vehicle['model'] ?? ''); ?>"
                                    data-year="<?php echo $vehicle['year']; ?>"
                                    data-registration="<?php echo htmlspecialchars($vehicle['registration'] ?? ''); ?>"
                                    data-mileage="<?php echo $vehicle['mileage']; ?>"
                                    data-status-id="<?php echo $vehicle['status_id']; ?>"
                                    data-last-service="<?php echo $vehicle['last_service_date']; ?>"
                                    data-next-service="<?php echo $vehicle['next_service_date']; ?>"
                                    data-service-interval="<?php echo $vehicle['service_interval_km']; ?>"
                                    data-notes="<?php echo htmlspecialchars($vehicle['notes'] ?? ''); ?>"
                                    style="background: #10b981; color: white; border: none; padding: 0.5rem 0.75rem; border-radius: 0.25rem; cursor: pointer; margin-right: 0.5rem; position: relative; z-index: 20; pointer-events: auto;">
                                ✏️ Edit
                            </button>
                            <button class="delete-btn"
                                    data-id="<?php echo $vehicle['id']; ?>"
                                    data-vehicle-id="<?php echo htmlspecialchars($vehicle['vehicle_id']); ?>"
                                    style="background: #dc2626; color: white; border: none; padding: 0.5rem 0.75rem; border-radius: 0.25rem; cursor: pointer; position: relative; z-index: 20; pointer-events: auto;">
                                🗑️ Delete
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>

    <!-- Add Vehicle Modal -->
    <div id="addVehicleModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); backdrop-filter: blur(5px); z-index: 1000; align-items: center; justify-content: center;">
        <div style="background: white; padding: 2rem; border-radius: 0.75rem; width: 90%; max-width: 600px; max-height: 90vh; overflow-y: auto;">
            <h3 style="margin-top: 0; color: #1f2937; margin-bottom: 1.5rem;">Add New Vehicle</h3>
            <form method="POST">
                <input type="hidden" name="action" value="add_vehicle">
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #374151;">Vehicle ID:</label>
                        <input type="text" name="vehicle_id" required maxlength="10" 
                               style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.5rem; font-size: 0.875rem;"
                               onkeyup="this.value = this.value.toUpperCase()">
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #374151;">Vehicle Type:</label>
                        <select name="type_id" required style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.5rem; font-size: 0.875rem;">
                            <option value="">Select Type</option>
                            <?php foreach ($vehicle_types as $type): ?>
                                <option value="<?php echo $type['id']; ?>"><?php echo htmlspecialchars($type['type_code'] . ' - ' . $type['type_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div style="margin-bottom: 1rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #374151;">Station:</label>
                    <select name="station_id" required style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.5rem; font-size: 0.875rem;">
                        <option value="">Select Station</option>
                        <?php foreach ($stations as $station): ?>
                            <option value="<?php echo $station['id']; ?>"><?php echo htmlspecialchars($station['station_code'] . ' - ' . $station['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #374151;">Make:</label>
                        <input type="text" name="make" required maxlength="50" 
                               style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.5rem; font-size: 0.875rem;">
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #374151;">Model:</label>
                        <input type="text" name="model" required maxlength="50"
                               style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.5rem; font-size: 0.875rem;">
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #374151;">Year:</label>
                        <input type="number" name="year" required min="1990" max="2030"
                               style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.5rem; font-size: 0.875rem;">
                    </div>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #374151;">Registration:</label>
                        <input type="text" name="registration" required maxlength="15"
                               style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.5rem; font-size: 0.875rem;"
                               onkeyup="this.value = this.value.toUpperCase()">
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #374151;">Mileage:</label>
                        <input type="number" name="mileage" required min="0"
                               style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.5rem; font-size: 0.875rem;">
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #374151;">Status:</label>
                        <select name="status_id" required style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.5rem; font-size: 0.875rem;">
                            <option value="">Select Status</option>
                            <?php foreach ($status_types as $status): ?>
                                <option value="<?php echo $status['id']; ?>"><?php echo htmlspecialchars($status['status_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #374151;">Last Service Date:</label>
                        <input type="date" name="last_service_date" 
                               style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.5rem; font-size: 0.875rem;">
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #374151;">Next Service Date:</label>
                        <input type="date" name="next_service_date"
                               style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.5rem; font-size: 0.875rem;">
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #374151;">Service Interval (km):</label>
                        <input type="number" name="service_interval_km" min="1000" step="1000" value="10000"
                               style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.5rem; font-size: 0.875rem;">
                    </div>
                </div>
                
                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #374151;">Notes:</label>
                    <textarea name="notes" rows="3" 
                              style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.5rem; font-size: 0.875rem; resize: vertical;"></textarea>
                </div>
                
                <div style="text-align: right;">
                    <button type="button" onclick="hideAddVehicleForm()" 
                            style="background: #6b7280; color: white; border: none; padding: 0.75rem 1.5rem; border-radius: 0.5rem; cursor: pointer; margin-right: 0.5rem;">
                        Cancel
                    </button>
                    <button type="submit" 
                            style="background: #059669; color: white; border: none; padding: 0.75rem 1.5rem; border-radius: 0.5rem; cursor: pointer;">
                        Add Vehicle
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Vehicle Modal -->
    <div id="editVehicleModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); backdrop-filter: blur(5px); z-index: 1000; align-items: center; justify-content: center;">
        <div style="background: white; padding: 2rem; border-radius: 0.75rem; width: 90%; max-width: 600px; max-height: 90vh; overflow-y: auto;">
            <h3 style="margin-top: 0; color: #1f2937; margin-bottom: 1.5rem;">Edit Vehicle</h3>
            <form method="POST">
                <input type="hidden" name="action" value="edit_vehicle">
                <input type="hidden" name="id" id="editVehicleId">
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #374151;">Vehicle ID:</label>
                        <input type="text" name="vehicle_id" id="editVehicleIdField" required maxlength="10" 
                               style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.5rem; font-size: 0.875rem;"
                               onkeyup="this.value = this.value.toUpperCase()">
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #374151;">Vehicle Type:</label>
                        <select name="type_id" id="editTypeId" required style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.5rem; font-size: 0.875rem;">
                            <option value="">Select Type</option>
                            <?php foreach ($vehicle_types as $type): ?>
                                <option value="<?php echo $type['id']; ?>"><?php echo htmlspecialchars($type['type_code'] . ' - ' . $type['type_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div style="margin-bottom: 1rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #374151;">Station:</label>
                    <select name="station_id" id="editStationId" required style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.5rem; font-size: 0.875rem;">
                        <option value="">Select Station</option>
                        <?php foreach ($stations as $station): ?>
                            <option value="<?php echo $station['id']; ?>"><?php echo htmlspecialchars($station['station_code'] . ' - ' . $station['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #374151;">Make:</label>
                        <input type="text" name="make" id="editMake" required maxlength="50" 
                               style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.5rem; font-size: 0.875rem;">
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #374151;">Model:</label>
                        <input type="text" name="model" id="editModel" required maxlength="50"
                               style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.5rem; font-size: 0.875rem;">
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #374151;">Year:</label>
                        <input type="number" name="year" id="editYear" required min="1990" max="2030"
                               style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.5rem; font-size: 0.875rem;">
                    </div>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #374151;">Registration:</label>
                        <input type="text" name="registration" id="editRegistration" required maxlength="15"
                               style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.5rem; font-size: 0.875rem;"
                               onkeyup="this.value = this.value.toUpperCase()">
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #374151;">Mileage:</label>
                        <input type="number" name="mileage" id="editMileage" required min="0"
                               style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.5rem; font-size: 0.875rem;">
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #374151;">Status:</label>
                        <select name="status_id" id="editStatusId" required style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.5rem; font-size: 0.875rem;">
                            <option value="">Select Status</option>
                            <?php foreach ($status_types as $status): ?>
                                <option value="<?php echo $status['id']; ?>"><?php echo htmlspecialchars($status['status_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #374151;">Last Service Date:</label>
                        <input type="date" name="last_service_date" id="editLastService"
                               style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.5rem; font-size: 0.875rem;">
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #374151;">Next Service Date:</label>
                        <input type="date" name="next_service_date" id="editNextService"
                               style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.5rem; font-size: 0.875rem;">
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #374151;">Service Interval (km):</label>
                        <input type="number" name="service_interval_km" id="editServiceInterval" min="1000" step="1000"
                               style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.5rem; font-size: 0.875rem;">
                    </div>
                </div>
                
                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #374151;">Notes:</label>
                    <textarea name="notes" id="editNotes" rows="3" 
                              style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.5rem; font-size: 0.875rem; resize: vertical;"></textarea>
                </div>
                
                <div style="text-align: right;">
                    <button type="button" onclick="hideEditVehicleForm()" 
                            style="background: #6b7280; color: white; border: none; padding: 0.75rem 1.5rem; border-radius: 0.5rem; cursor: pointer; margin-right: 0.5rem;">
                        Cancel
                    </button>
                    <button type="submit" 
                            style="background: #059669; color: white; border: none; padding: 0.75rem 1.5rem; border-radius: 0.5rem; cursor: pointer;">
                        Update Vehicle
                    </button>
                </div>
            </form>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script>
    function showAddVehicleForm() {
        document.getElementById('addVehicleModal').style.display = 'flex';
    }

    function hideAddVehicleForm() {
        document.getElementById('addVehicleModal').style.display = 'none';
    }

    function hideEditVehicleForm() {
        document.getElementById('editVehicleModal').style.display = 'none';
    }

    // Event delegation for edit and delete buttons
    document.addEventListener('DOMContentLoaded', function() {
        // Edit button handling
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('edit-btn')) {
                const button = e.target;
                
                // Populate edit form with data
                document.getElementById('editVehicleId').value = button.dataset.id;
                document.getElementById('editVehicleIdField').value = button.dataset.vehicleId;
                document.getElementById('editTypeId').value = button.dataset.typeId;
                document.getElementById('editStationId').value = button.dataset.stationId;
                document.getElementById('editMake').value = button.dataset.make;
                document.getElementById('editModel').value = button.dataset.model;
                document.getElementById('editYear').value = button.dataset.year;
                document.getElementById('editRegistration').value = button.dataset.registration;
                document.getElementById('editMileage').value = button.dataset.mileage;
                document.getElementById('editStatusId').value = button.dataset.statusId;
                document.getElementById('editLastService').value = button.dataset.lastService || '';
                document.getElementById('editNextService').value = button.dataset.nextService || '';
                document.getElementById('editServiceInterval').value = button.dataset.serviceInterval;
                document.getElementById('editNotes').value = button.dataset.notes;
                
                document.getElementById('editVehicleModal').style.display = 'flex';
            }
        });

        // Delete button handling
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('delete-btn')) {
                const button = e.target;
                const vehicleId = button.dataset.vehicleId;
                const id = button.dataset.id;
                
                if (confirm('Are you sure you want to delete vehicle ' + vehicleId + '?\\n\\nThis action cannot be undone.')) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.innerHTML = `
                        <input type="hidden" name="action" value="delete_vehicle">
                        <input type="hidden" name="id" value="${id}">
                    `;
                    document.body.appendChild(form);
                    form.submit();
                }
            }
        });

        // Close modal when clicking outside
        window.addEventListener('click', function(e) {
            if (e.target === document.getElementById('addVehicleModal')) {
                hideAddVehicleForm();
            }
            if (e.target === document.getElementById('editVehicleModal')) {
                hideEditVehicleForm();
            }
        });
    });
    </script>
</body>
</html>