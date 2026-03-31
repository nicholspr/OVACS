<?php
// Database connection and error handling
$errorMessage = '';
$dispatchData = [];

try {
    include 'includes/database.php';
    $vehicleManager = new VehicleManager();
    // For now, we'll use placeholder data for dispatch console
    $dispatchData = [
        'available_vehicles' => [
            ['id' => 'A-101', 'type' => 'Ambulance', 'station' => 'Central Station', 'crew' => 'Team A'],
            ['id' => 'A-203', 'type' => 'Ambulance', 'station' => 'North Station', 'crew' => 'Team B'],
            ['id' => 'F-301', 'type' => 'Fire Truck', 'station' => 'East Station', 'crew' => 'Team C'],
            ['id' => 'P-401', 'type' => 'Police', 'station' => 'West Station', 'crew' => 'Officer D'],
        ],
        'recent_dispatches' => [
            ['time' => '14:23', 'vehicle' => 'A-105', 'location' => 'Main St & 5th Ave', 'type' => 'Medical Emergency'],
            ['time' => '14:18', 'vehicle' => 'F-302', 'location' => '123 Oak Street', 'type' => 'House Fire'],
            ['time' => '14:12', 'vehicle' => 'P-403', 'location' => 'Highway 101', 'type' => 'Traffic Accident']
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
    <title>Dispatch Console - OVACS</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container" style="margin-top: 100px; padding: 20px;">
        <div class="dispatch-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
            <div>
                <h1>🚨 Dispatch Console</h1>
                <p class="subtitle">Emergency dispatch and vehicle deployment management</p>
            </div>
            <div class="emergency-status">
                <span class="status-indicator available">OPERATIONAL</span>
                <span class="current-time" id="currentTime"><?php echo date('H:i:s'); ?></span>
            </div>
        </div>

        <?php if ($errorMessage): ?>
            <div class="alert alert-error">
                <strong>Error:</strong> <?php echo htmlspecialchars($errorMessage); ?>
            </div>
        <?php endif; ?>

        <div class="action-buttons" style="margin: 20px 0;">
            <button onclick="showEmergencyDispatch()" class="btn btn-danger">🚨 EMERGENCY DISPATCH</button>
            <button onclick="showRoutineDispatch()" class="btn btn-primary">📞 Routine Dispatch</button>
            <a href="availability.php" class="btn btn-secondary">📊 Availability Board</a>
            <a href="index.php" class="btn btn-secondary">← Dashboard</a>
        </div>

        <!-- Quick Dispatch Section -->
        <div class="dispatch-section" style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin: 30px 0;">
            <div class="available-vehicles">
                <h3>🚗 Available Vehicles</h3>
                <div class="vehicle-list">
                    <?php foreach ($dispatchData['available_vehicles'] as $vehicle): ?>
                        <div class="vehicle-card" onclick="selectVehicle('<?php echo $vehicle['id']; ?>')">
                            <div class="vehicle-header">
                                <strong><?php echo htmlspecialchars($vehicle['id']); ?></strong>
                                <span class="vehicle-type"><?php echo htmlspecialchars($vehicle['type']); ?></span>
                            </div>
                            <div class="vehicle-details">
                                <p>📍 <?php echo htmlspecialchars($vehicle['station']); ?></p>
                                <p>👥 <?php echo htmlspecialchars($vehicle['crew']); ?></p>
                            </div>
                            <button class="dispatch-btn" onclick="quickDispatch('<?php echo $vehicle['id']; ?>')">Dispatch</button>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="recent-dispatches">
                <h3>📻 Recent Dispatches</h3>
                <div class="dispatch-log">
                    <?php foreach ($dispatchData['recent_dispatches'] as $dispatch): ?>
                        <div class="dispatch-item">
                            <div class="dispatch-time"><?php echo htmlspecialchars($dispatch['time']); ?></div>
                            <div class="dispatch-details">
                                <strong><?php echo htmlspecialchars($dispatch['vehicle']); ?></strong> - 
                                <?php echo htmlspecialchars($dispatch['type']); ?>
                                <br>
                                <small>📍 <?php echo htmlspecialchars($dispatch['location']); ?></small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Emergency Dispatch Form (Hidden by default) -->
        <div id="emergencyDispatchModal" class="modal" style="display: none;">
            <div class="modal-content emergency-modal">
                <h3>🚨 EMERGENCY DISPATCH</h3>
                <form id="emergencyDispatchForm">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="emergency_type">Emergency Type:</label>
                            <select id="emergency_type" name="emergency_type" required>
                                <option value="">Select Emergency Type</option>
                                <option value="medical">Medical Emergency</option>
                                <option value="fire">Fire</option>
                                <option value="accident">Traffic Accident</option>
                                <option value="crime">Crime in Progress</option>
                                <option value="other">Other Emergency</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="priority_level">Priority Level:</label>
                            <select id="priority_level" name="priority_level" required>
                                <option value="critical">🔴 Critical</option>
                                <option value="high">🟠 High</option>
                                <option value="medium">🟡 Medium</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="incident_location">Incident Location:</label>
                        <input type="text" id="incident_location" name="incident_location" 
                               placeholder="Street address or intersection" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="caller_info">Caller Information:</label>
                        <input type="text" id="caller_info" name="caller_info" 
                               placeholder="Name and callback number">
                    </div>
                    
                    <div class="form-group">
                        <label for="incident_details">Incident Details:</label>
                        <textarea id="incident_details" name="incident_details" rows="3" 
                                  placeholder="Brief description of the emergency"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="assigned_vehicle">Assign Vehicle:</label>
                        <select id="assigned_vehicle" name="assigned_vehicle" required>
                            <option value="">Select Vehicle</option>
                            <?php foreach ($dispatchData['available_vehicles'] as $vehicle): ?>
                                <option value="<?php echo $vehicle['id']; ?>">
                                    <?php echo $vehicle['id']; ?> - <?php echo $vehicle['type']; ?> (<?php echo $vehicle['station']; ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-buttons">
                        <button type="submit" class="btn btn-danger">🚨 DISPATCH NOW</button>
                        <button type="button" onclick="hideEmergencyDispatch()" class="btn btn-secondary">Cancel</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Routine Dispatch Form (Hidden by default) -->
        <div id="routineDispatchModal" class="modal" style="display: none;">
            <div class="modal-content">
                <h3>📞 Routine Dispatch</h3>
                <form id="routineDispatchForm">
                    <div class="form-group">
                        <label for="routine_type">Service Type:</label>
                        <select id="routine_type" name="routine_type" required>
                            <option value="">Select Service Type</option>
                            <option value="transport">Patient Transport</option>
                            <option value="wellness">Wellness Check</option>
                            <option value="inspection">Safety Inspection</option>
                            <option value="training">Training Exercise</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="routine_location">Location:</label>
                        <input type="text" id="routine_location" name="routine_location" 
                               placeholder="Street address" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="scheduled_time">Scheduled Time:</label>
                        <input type="datetime-local" id="scheduled_time" name="scheduled_time">
                    </div>
                    
                    <div class="form-group">
                        <label for="routine_vehicle">Assign Vehicle:</label>
                        <select id="routine_vehicle" name="routine_vehicle" required>
                            <option value="">Select Vehicle</option>
                            <?php foreach ($dispatchData['available_vehicles'] as $vehicle): ?>
                                <option value="<?php echo $vehicle['id']; ?>">
                                    <?php echo $vehicle['id']; ?> - <?php echo $vehicle['type']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-buttons">
                        <button type="submit" class="btn btn-primary">Schedule Dispatch</button>
                        <button type="button" onclick="hideRoutineDispatch()" class="btn btn-secondary">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script>
        function showEmergencyDispatch() {
            document.getElementById('emergencyDispatchModal').style.display = 'flex';
        }

        function hideEmergencyDispatch() {
            document.getElementById('emergencyDispatchModal').style.display = 'none';
        }

        function showRoutineDispatch() {
            document.getElementById('routineDispatchModal').style.display = 'flex';
        }

        function hideRoutineDispatch() {
            document.getElementById('routineDispatchModal').style.display = 'none';
        }

        function selectVehicle(vehicleId) {
            document.querySelectorAll('.vehicle-card').forEach(card => {
                card.classList.remove('selected');
            });
            event.currentTarget.classList.add('selected');
        }

        function quickDispatch(vehicleId) {
            const location = prompt('Enter dispatch location:');
            if (location) {
                alert(`Dispatching vehicle ${vehicleId} to ${location}. Full dispatch functionality will be implemented in the next update.`);
            }
        }

        // Handle emergency dispatch form
        document.getElementById('emergencyDispatchForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            alert('Emergency dispatch sent! Full dispatch functionality will be implemented in the next update.');
            hideEmergencyDispatch();
        });

        // Handle routine dispatch form
        document.getElementById('routineDispatchForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            alert('Routine dispatch scheduled! Full dispatch functionality will be implemented in the next update.');
            hideRoutineDispatch();
        });

        // Update current time every second
        setInterval(function() {
            document.getElementById('currentTime').textContent = new Date().toLocaleTimeString();
        }, 1000);

        // Close modals when clicking outside
        window.onclick = function(event) {
            const emergencyModal = document.getElementById('emergencyDispatchModal');
            const routineModal = document.getElementById('routineDispatchModal');
            if (event.target == emergencyModal) {
                emergencyModal.style.display = 'none';
            }
            if (event.target == routineModal) {
                routineModal.style.display = 'none';
            }
        }
    </script>

    <script src="js/main.js"></script>

    <style>
        .vehicle-card {
            background: white;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 15px;
            margin: 10px 0;
            cursor: pointer;
            transition: all 0.3s;
            position: relative;
        }
        
        .vehicle-card:hover, .vehicle-card.selected {
            border-color: #007bff;
            box-shadow: 0 2px 8px rgba(0,123,255,0.1);
        }
        
        .vehicle-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .vehicle-type {
            background: #007bff;
            color: white;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.8em;
        }
        
        .vehicle-details p {
            margin: 5px 0;
            font-size: 0.9em;
            color: #666;
        }
        
        .dispatch-btn {
            background: #28a745;
            color: white;
            border: none;
            padding: 5px 15px;
            border-radius: 4px;
            cursor: pointer;
            position: absolute;
            top: 15px;
            right: 15px;
        }
        
        .dispatch-item {
            display: flex;
            padding: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .dispatch-time {
            font-weight: bold;
            margin-right: 15px;
            color: #007bff;
            min-width: 50px;
        }
        
        .emergency-modal {
            border-left: 4px solid #dc3545;
        }
        
        .emergency-modal h3 {
            color: #dc3545;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        
        .current-time {
            font-family: 'Courier New', monospace;
            background: #333;
            color: #0ff;
            padding: 5px 10px;
            border-radius: 4px;
        }
        
        .status-indicator.available {
            background: #28a745;
            color: white;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 0.8em;
            font-weight: bold;
            margin-right: 10px;
        }
    </style>
</body>
</html>