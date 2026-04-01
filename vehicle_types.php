<?php
include 'includes/database.php';

$success_message = '';
$error_message = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo = DatabaseConfig::getConnection();
    
    if (isset($_POST['action'])) {
        try {
            if ($_POST['action'] === 'add_vehicle_type') {
                $stmt = $pdo->prepare("INSERT INTO vehicle_types (type_code, type_name, description) VALUES (?, ?, ?)");
                $stmt->execute([
                    strtoupper($_POST['type_code']),
                    $_POST['type_name'],
                    $_POST['description']
                ]);
                $success_message = "✅ Vehicle type added successfully!";
                
            } elseif ($_POST['action'] === 'edit_vehicle_type') {
                $stmt = $pdo->prepare("UPDATE vehicle_types SET type_code = ?, type_name = ?, description = ? WHERE id = ?");
                $stmt->execute([
                    strtoupper($_POST['type_code']),
                    $_POST['type_name'],
                    $_POST['description'],
                    $_POST['id']
                ]);
                $success_message = "✅ Vehicle type updated successfully!";
                
            } elseif ($_POST['action'] === 'delete_vehicle_type') {
                // Check if vehicle type is in use
                $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM vehicles WHERE type_id = ?");
                $stmt->execute([$_POST['id']]);
                $usage_count = $stmt->fetch()['count'];
                
                if ($usage_count > 0) {
                    $error_message = "⚠️ Cannot delete this vehicle type. It is currently assigned to {$usage_count} vehicle(s).";
                } else {
                    $stmt = $pdo->prepare("DELETE FROM vehicle_types WHERE id = ?");
                    $stmt->execute([$_POST['id']]);
                    $success_message = "✅ Vehicle type deleted successfully!";
                }
            }
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $error_message = "⚠️ A vehicle type with this code already exists.";
            } else {
                $error_message = "⚠️ Database error: " . $e->getMessage();
            }
        }
    }
}

// Fetch all vehicle types
$pdo = DatabaseConfig::getConnection();
$stmt = $pdo->query("SELECT vt.*, COUNT(v.id) as vehicle_count 
                     FROM vehicle_types vt 
                     LEFT JOIN vehicles v ON vt.id = v.type_id 
                     GROUP BY vt.id 
                     ORDER BY vt.type_code");
$vehicle_types = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OVACS - Vehicle Types Management</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <section class="hero">
        <div class="container">
            <h1 class="hero-title">Vehicle Types Management</h1>
            <p class="hero-subtitle">Manage vehicle types and their configurations</p>
            
            <?php if ($success_message): ?>
                <div style="background: #d1e7dd; color: #0f5132; padding: 1rem; border-radius: 0.375rem; margin: 1rem 0;">
                    <?php echo $success_message; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($error_message): ?>
                <div style="background: #f8d7da; color: #721c24; padding: 1rem; border-radius: 0.375rem; margin: 1rem 0;">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <main class="container" style="padding: 2rem 0;">
        <!-- Action Buttons -->
        <div style="background: #f8f9fa; padding: 1.5rem; border-radius: 0.5rem; margin-bottom: 2rem;">
            <button onclick="showAddVehicleTypeForm()" style="background: #2563eb; color: white; border: none; padding: 0.75rem 1.5rem; border-radius: 0.375rem; font-weight: 500; cursor: pointer; font-size: 0.875rem;">
                ➕ Add New Vehicle Type
            </button>
        </div>

        <!-- Vehicle Types Table -->
        <div style="background: white; border-radius: 0.5rem; box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06); overflow: hidden;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #f8f9fa; border-bottom: 1px solid #dee2e6;">
                        <th style="padding: 0.75rem 1rem; text-align: left; font-weight: 600; color: #495057; font-size: 0.875rem;">Type Code</th>
                        <th style="padding: 0.75rem 1rem; text-align: left; font-weight: 600; color: #495057; font-size: 0.875rem;">Type Name</th>
                        <th style="padding: 0.75rem 1rem; text-align: left; font-weight: 600; color: #495057; font-size: 0.875rem;">Description</th>
                        <th style="padding: 0.75rem 1rem; text-align: left; font-weight: 600; color: #495057; font-size: 0.875rem;">Vehicles</th>
                        <th style="padding: 0.75rem 1rem; text-align: left; font-weight: 600; color: #495057; font-size: 0.875rem;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($vehicle_types as $type): ?>
                    <tr style="border-bottom: 1px solid #f1f3f4;">
                        <td style="padding: 0.75rem 1rem; font-size: 0.875rem;">
                            <span style="background: #2563eb; color: white; padding: 0.25rem 0.5rem; border-radius: 0.25rem; font-weight: 500; font-size: 0.75rem;">
                                <?php echo htmlspecialchars($type['type_code']); ?>
                            </span>
                        </td>
                        <td style="padding: 0.75rem 1rem; font-size: 0.875rem; font-weight: 500;">
                            <?php echo htmlspecialchars($type['type_name']); ?>
                        </td>
                        <td style="padding: 0.75rem 1rem; font-size: 0.875rem; color: #6b7280;">
                            <?php echo htmlspecialchars($type['description']); ?>
                        </td>
                        <td style="padding: 0.75rem 1rem; font-size: 0.875rem;">
                            <span style="background: #f3f4f6; color: #374151; padding: 0.25rem 0.5rem; border-radius: 0.25rem; font-weight: 500; font-size: 0.75rem;">
                                <?php echo $type['vehicle_count']; ?> vehicles
                            </span>
                        </td>
                        <td style="padding: 0.75rem 1rem; font-size: 0.875rem;">
                            <button class="edit-btn" 
                                    data-id="<?php echo $type['id']; ?>"
                                    data-code="<?php echo htmlspecialchars($type['type_code']); ?>"
                                    data-name="<?php echo htmlspecialchars($type['type_name']); ?>"
                                    data-description="<?php echo htmlspecialchars($type['description']); ?>"
                                    style="background: #10b981; color: white; border: none; padding: 0.25rem 0.5rem; border-radius: 0.25rem; margin-right: 0.5rem; cursor: pointer; font-size: 0.75rem;">
                                ✏️ Edit
                            </button>
                            <button class="delete-btn"
                                    data-id="<?php echo $type['id']; ?>"
                                    data-name="<?php echo htmlspecialchars($type['type_name']); ?>"
                                    style="background: #dc2626; color: white; border: none; padding: 0.25rem 0.5rem; border-radius: 0.25rem; cursor: pointer; font-size: 0.75rem;">
                                🗑️ Delete
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>

    <!-- Add Vehicle Type Modal -->
    <div id="addModal" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.4); backdrop-filter: blur(5px);">
        <div style="background-color: #fefefe; margin: 10% auto; padding: 0; border-radius: 0.5rem; width: 90%; max-width: 500px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);">
            <div style="padding: 1.5rem; border-bottom: 1px solid #e5e7eb;">
                <h2 style="margin: 0; font-size: 1.25rem; font-weight: 600; color: #111827;">Add New Vehicle Type</h2>
            </div>
            <form method="POST" style="padding: 1.5rem;">
                <input type="hidden" name="action" value="add_vehicle_type">
                
                <div style="margin-bottom: 1rem;">
                    <label style="display: block; font-size: 0.875rem; font-weight: 500; color: #374151; margin-bottom: 0.5rem;">Type Code</label>
                    <input type="text" name="type_code" required maxlength="10" 
                           style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid #d1d5db; border-radius: 0.375rem; font-size: 0.875rem;"
                           placeholder="e.g., DCA, RRV">
                </div>
                
                <div style="margin-bottom: 1rem;">
                    <label style="display: block; font-size: 0.875rem; font-weight: 500; color: #374151; margin-bottom: 0.5rem;">Type Name</label>
                    <input type="text" name="type_name" required maxlength="100"
                           style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid #d1d5db; border-radius: 0.375rem; font-size: 0.875rem;"
                           placeholder="e.g., Double Crewed Ambulance">
                </div>
                
                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; font-size: 0.875rem; font-weight: 500; color: #374151; margin-bottom: 0.5rem;">Description</label>
                    <textarea name="description" rows="3"
                              style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid #d1d5db; border-radius: 0.375rem; font-size: 0.875rem; resize: vertical;"
                              placeholder="Brief description of this vehicle type"></textarea>
                </div>
                
                <div style="display: flex; gap: 0.75rem; justify-content: flex-end;">
                    <button type="button" onclick="hideAddVehicleTypeForm()"
                            style="background: #6b7280; color: white; border: none; padding: 0.5rem 1rem; border-radius: 0.375rem; font-size: 0.875rem; cursor: pointer;">
                        Cancel
                    </button>
                    <button type="submit"
                            style="background: #2563eb; color: white; border: none; padding: 0.5rem 1rem; border-radius: 0.375rem; font-size: 0.875rem; cursor: pointer;">
                        ✅ Add Type
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Vehicle Type Modal -->
    <div id="editModal" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.4); backdrop-filter: blur(5px);">
        <div style="background-color: #fefefe; margin: 10% auto; padding: 0; border-radius: 0.5rem; width: 90%; max-width: 500px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);">
            <div style="padding: 1.5rem; border-bottom: 1px solid #e5e7eb;">
                <h2 style="margin: 0; font-size: 1.25rem; font-weight: 600; color: #111827;">Edit Vehicle Type</h2>
            </div>
            <form method="POST" style="padding: 1.5rem;">
                <input type="hidden" name="action" value="edit_vehicle_type">
                <input type="hidden" name="id" id="edit_id">
                
                <div style="margin-bottom: 1rem;">
                    <label style="display: block; font-size: 0.875rem; font-weight: 500; color: #374151; margin-bottom: 0.5rem;">Type Code</label>
                    <input type="text" name="type_code" id="edit_type_code" required maxlength="10"
                           style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid #d1d5db; border-radius: 0.375rem; font-size: 0.875rem;">
                </div>
                
                <div style="margin-bottom: 1rem;">
                    <label style="display: block; font-size: 0.875rem; font-weight: 500; color: #374151; margin-bottom: 0.5rem;">Type Name</label>
                    <input type="text" name="type_name" id="edit_type_name" required maxlength="100"
                           style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid #d1d5db; border-radius: 0.375rem; font-size: 0.875rem;">
                </div>
                
                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; font-size: 0.875rem; font-weight: 500; color: #374151; margin-bottom: 0.5rem;">Description</label>
                    <textarea name="description" id="edit_description" rows="3"
                              style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid #d1d5db; border-radius: 0.375rem; font-size: 0.875rem; resize: vertical;"></textarea>
                </div>
                
                <div style="display: flex; gap: 0.75rem; justify-content: flex-end;">
                    <button type="button" onclick="hideEditVehicleTypeForm()"
                            style="background: #6b7280; color: white; border: none; padding: 0.5rem 1rem; border-radius: 0.375rem; font-size: 0.875rem; cursor: pointer;">
                        Cancel
                    </button>
                    <button type="submit"
                            style="background: #10b981; color: white; border: none; padding: 0.5rem 1rem; border-radius: 0.375rem; font-size: 0.875rem; cursor: pointer;">
                        💾 Update Type
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Auto-uppercase type codes
        document.addEventListener('DOMContentLoaded', function() {
            const typeCodeInputs = document.querySelectorAll('input[name="type_code"]');
            typeCodeInputs.forEach(input => {
                input.addEventListener('input', function() {
                    this.value = this.value.toUpperCase();
                });
            });
        });

        // Modal functions
        function showAddVehicleTypeForm() {
            document.getElementById('addModal').style.display = 'block';
            document.body.style.overflow = 'hidden';
        }

        function hideAddVehicleTypeForm() {
            document.getElementById('addModal').style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        function showEditVehicleTypeForm() {
            document.getElementById('editModal').style.display = 'block';
            document.body.style.overflow = 'hidden';
        }

        function hideEditVehicleTypeForm() {
            document.getElementById('editModal').style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        // Event delegation for edit and delete buttons
        document.addEventListener('DOMContentLoaded', function() {
            // Edit button clicks
            document.addEventListener('click', function(event) {
                if (event.target.classList.contains('edit-btn')) {
                    const id = event.target.getAttribute('data-id');
                    const code = event.target.getAttribute('data-code');
                    const name = event.target.getAttribute('data-name');
                    const description = event.target.getAttribute('data-description');
                    
                    document.getElementById('edit_id').value = id;
                    document.getElementById('edit_type_code').value = code;
                    document.getElementById('edit_type_name').value = name;
                    document.getElementById('edit_description').value = description;
                    
                    showEditVehicleTypeForm();
                }
            });

            // Delete button clicks
            document.addEventListener('click', function(event) {
                if (event.target.classList.contains('delete-btn')) {
                    const id = event.target.getAttribute('data-id');
                    const name = event.target.getAttribute('data-name');
                    
                    if (confirm(`Are you sure you want to delete the vehicle type "${name}"?`)) {
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.innerHTML = `
                            <input type="hidden" name="action" value="delete_vehicle_type">
                            <input type="hidden" name="id" value="${id}">
                        `;
                        document.body.appendChild(form);
                        form.submit();
                    }
                }
            });
        });

        // Close modal when clicking outside
        window.onclick = function(event) {
            const addModal = document.getElementById('addModal');
            const editModal = document.getElementById('editModal');
            
            if (event.target === addModal) {
                hideAddVehicleTypeForm();
            }
            if (event.target === editModal) {
                hideEditVehicleTypeForm();
            }
        };
    </script>

    <?php include 'includes/footer.php'; ?>
</body>
</html>