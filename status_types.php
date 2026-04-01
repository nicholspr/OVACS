<?php
require_once 'includes/common.php';
require_once 'includes/ui_components.php';
require_once 'includes/database.php';

$success_message = '';
$error_message = '';

// Handle form submissions
if ($_POST) {
    try {
        $pdo = DatabaseConfig::getConnection();
        
        if (isset($_POST['add_status'])) {
            // Add new status type
            $status_name = $_POST['status_name'];
            $status_description = $_POST['status_description'];
            $color_code = $_POST['color_code'];
            
            $stmt = $pdo->prepare("INSERT INTO status_types (status_name, status_description, color_code) VALUES (?, ?, ?)");
            $stmt->execute([$status_name, $status_description, $color_code]);
            
            $success_message = "✅ Status type '$status_name' added successfully!";
        }
        elseif (isset($_POST['edit_status'])) {
            // Edit existing status type
            $id = $_POST['status_id'];
            $status_name = $_POST['status_name'];
            $status_description = $_POST['status_description'];
            $color_code = $_POST['color_code'];
            
            $stmt = $pdo->prepare("UPDATE status_types SET status_name = ?, status_description = ?, color_code = ? WHERE id = ?");
            $result = $stmt->execute([$status_name, $status_description, $color_code, $id]);
            
            if ($result) {
                $success_message = "✅ Status type '$status_name' updated successfully!";
            } else {
                $error_message = "⚠️ Error updating status type.";
            }
        }
        elseif (isset($_POST['delete_status'])) {
            // Delete status type
            $id = $_POST['status_id'];
            
            // Check if status type is in use
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM vehicles WHERE status_id = ?");
            $stmt->execute([$id]);
            $usage = $stmt->fetch();
            
            if ($usage['count'] > 0) {
                $error_message = "⚠️ Cannot delete status type - it is currently being used by {$usage['count']} vehicle(s).";
            } else {
                $stmt = $pdo->prepare("DELETE FROM status_types WHERE id = ?");
                $result = $stmt->execute([$id]);
                
                if ($result) {
                    $success_message = "✅ Status type deleted successfully!";
                } else {
                    $error_message = "⚠️ Error deleting status type.";
                }
            }
        }
    } catch (PDOException $e) {
        $error_message = "⚠️ Database error: " . $e->getMessage();
    }
}

// Get all status types
try {
    $pdo = DatabaseConfig::getConnection();
    $stmt = $pdo->query("SELECT * FROM status_types ORDER BY status_name");
    $status_types = $stmt->fetchAll();
} catch (PDOException $e) {
    $error_message = "⚠️ Error fetching status types: " . $e->getMessage();
    $status_types = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OVACS - Status Types Management</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <section class="hero">
        <div class="container">
            <h1 class="hero-title">Status Types Management</h1>
            <p class="hero-subtitle">Manage vehicle status types and their display properties</p>
            
            <?php if ($success_message): ?>
                <div style="background-color: #d1e7dd; color: #0f5132; padding: 0.75rem 1rem; border-radius: 0.375rem; margin-top: 1rem;">
                    <?php echo $success_message; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($error_message): ?>
                <div style="background-color: #f8d7da; color: #721c24; padding: 0.75rem 1rem; border-radius: 0.375rem; margin-top: 1rem;">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <main class="container" style="padding: 2rem 0;">
        <!-- Action Buttons -->
        <div style="background-color: #f8f9fa; padding: 1rem; border-radius: 0.5rem; margin-bottom: 2rem;">
            <button onclick="showAddStatusForm()" style="background-color: #2563eb; color: white; padding: 0.5rem 1rem; border: none; border-radius: 0.375rem; font-weight: 500; cursor: pointer; font-size: 0.875rem;">
                ➕ Add New Status Type
            </button>
        </div>

        <!-- Status Types Table -->
        <div style="background-color: white; border-radius: 0.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow: hidden;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead style="background-color: #f8f9fa;">
                    <tr>
                        <th style="text-align: left; padding: 0.75rem 1rem; font-weight: 600; color: #495057; font-size: 0.875rem;">ID</th>
                        <th style="text-align: left; padding: 0.75rem 1rem; font-weight: 600; color: #495057; font-size: 0.875rem;">Status Name</th>
                        <th style="text-align: left; padding: 0.75rem 1rem; font-weight: 600; color: #495057; font-size: 0.875rem;">Description</th>
                        <th style="text-align: left; padding: 0.75rem 1rem; font-weight: 600; color: #495057; font-size: 0.875rem;">Color</th>
                        <th style="text-align: left; padding: 0.75rem 1rem; font-weight: 600; color: #495057; font-size: 0.875rem;">Usage</th>
                        <th style="text-align: left; padding: 0.75rem 1rem; font-weight: 600; color: #495057; font-size: 0.875rem;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($status_types as $status): ?>
                        <?php
                        // Get usage count
                        try {
                            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM vehicles WHERE status_id = ?");
                            $stmt->execute([$status['id']]);
                            $usage = $stmt->fetch();
                            $usage_count = $usage['count'];
                        } catch (PDOException $e) {
                            $usage_count = 0;
                        }
                        ?>
                        <tr style="border-top: 1px solid #e9ecef;">
                            <td style="padding: 0.75rem 1rem; font-size: 0.875rem;"><?php echo $status['id']; ?></td>
                            <td style="padding: 0.75rem 1rem; font-size: 0.875rem; font-weight: 500;"><?php echo htmlspecialchars($status['status_name']); ?></td>
                            <td style="padding: 0.75rem 1rem; font-size: 0.875rem;"><?php echo htmlspecialchars($status['status_description']); ?></td>
                            <td style="padding: 0.75rem 1rem; font-size: 0.875rem;">
                                <span style="display: inline-block; width: 20px; height: 20px; border-radius: 50%; background-color: <?php echo htmlspecialchars($status['color_code']); ?>; margin-right: 0.5rem; vertical-align: middle;"></span>
                                <?php echo htmlspecialchars($status['color_code']); ?>
                            </td>
                            <td style="padding: 0.75rem 1rem; font-size: 0.875rem;"><?php echo $usage_count; ?> vehicle(s)</td>
                            <td style="padding: 0.75rem 1rem; font-size: 0.875rem;">
                                <button class="edit-status-btn" 
                                        data-id="<?php echo $status['id']; ?>"
                                        data-name="<?php echo htmlspecialchars($status['status_name']); ?>"
                                        data-description="<?php echo htmlspecialchars($status['status_description']); ?>"
                                        data-color="<?php echo htmlspecialchars($status['color_code']); ?>"
                                        style="background-color: #10b981; color: white; border: none; padding: 0.25rem 0.5rem; border-radius: 0.25rem; cursor: pointer; margin-right: 0.5rem; font-size: 0.75rem; position: relative; z-index: 20; pointer-events: auto;">
                                    ✏️ Edit
                                </button>
                                <button class="delete-status-btn"
                                        data-id="<?php echo $status['id']; ?>"
                                        data-name="<?php echo htmlspecialchars($status['status_name']); ?>"
                                        style="background-color: #dc2626; color: white; border: none; padding: 0.25rem 0.5rem; border-radius: 0.25rem; cursor: pointer; font-size: 0.75rem; position: relative; z-index: 20; pointer-events: auto;">
                                    🗑️ Delete
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>

    <!-- Add Status Type Modal -->
    <div id="addStatusModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); z-index: 1000; backdrop-filter: blur(4px);">
        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 2rem; border-radius: 0.75rem; box-shadow: 0 10px 25px rgba(0,0,0,0.15); min-width: 400px; max-width: 90vw;">
            <h3 style="margin: 0 0 1.5rem 0; color: #1f2937; font-size: 1.25rem; font-weight: 600;">Add New Status Type</h3>
            <form method="POST" style="display: flex; flex-direction: column; gap: 1rem;">
                <input type="hidden" name="add_status" value="1">
                
                <div>
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #374151; font-size: 0.875rem;">Status Name:</label>
                    <input type="text" name="status_name" required style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 0.375rem; font-size: 0.875rem; box-sizing: border-box;">
                </div>
                
                <div>
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #374151; font-size: 0.875rem;">Description:</label>
                    <input type="text" name="status_description" required style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 0.375rem; font-size: 0.875rem; box-sizing: border-box;">
                </div>
                
                <div>
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #374151; font-size: 0.875rem;">Color Code:</label>
                    <div style="display: flex; gap: 0.5rem; align-items: center;">
                        <input type="color" name="color_code" value="#10b981" style="width: 50px; height: 40px; border: 1px solid #d1d5db; border-radius: 0.375rem; cursor: pointer;">
                        <input type="text" name="color_code_text" placeholder="#10b981" style="flex: 1; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 0.375rem; font-size: 0.875rem;">
                    </div>
                </div>
                
                <div style="display: flex; gap: 0.5rem; margin-top: 1rem;">
                    <button type="submit" style="flex: 1; background-color: #2563eb; color: white; padding: 0.75rem; border: none; border-radius: 0.375rem; font-weight: 500; cursor: pointer; font-size: 0.875rem;">
                        ✅ Add Status Type
                    </button>
                    <button type="button" onclick="hideAddStatusForm()" style="flex: 1; background-color: #6b7280; color: white; padding: 0.75rem; border: none; border-radius: 0.375rem; font-weight: 500; cursor: pointer; font-size: 0.875rem;">
                        ✖️ Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Status Type Modal -->
    <div id="editStatusModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); z-index: 1000; backdrop-filter: blur(4px);">
        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 2rem; border-radius: 0.75rem; box-shadow: 0 10px 25px rgba(0,0,0,0.15); min-width: 400px; max-width: 90vw;">
            <h3 style="margin: 0 0 1.5rem 0; color: #1f2937; font-size: 1.25rem; font-weight: 600;">Edit Status Type</h3>
            <form method="POST" style="display: flex; flex-direction: column; gap: 1rem;">
                <input type="hidden" name="edit_status" value="1">
                <input type="hidden" id="edit_status_id" name="status_id">
                
                <div>
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #374151; font-size: 0.875rem;">Status Name:</label>
                    <input type="text" id="edit_status_name" name="status_name" required style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 0.375rem; font-size: 0.875rem; box-sizing: border-box;">
                </div>
                
                <div>
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #374151; font-size: 0.875rem;">Description:</label>
                    <input type="text" id="edit_status_description" name="status_description" required style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 0.375rem; font-size: 0.875rem; box-sizing: border-box;">
                </div>
                
                <div>
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #374151; font-size: 0.875rem;">Color Code:</label>
                    <div style="display: flex; gap: 0.5rem; align-items: center;">
                        <input type="color" id="edit_color_code" name="color_code" style="width: 50px; height: 40px; border: 1px solid #d1d5db; border-radius: 0.375rem; cursor: pointer;">
                        <input type="text" id="edit_color_code_text" placeholder="#10b981" style="flex: 1; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 0.375rem; font-size: 0.875rem;">
                    </div>
                </div>
                
                <div style="display: flex; gap: 0.5rem; margin-top: 1rem;">
                    <button type="submit" style="flex: 1; background-color: #059669; color: white; padding: 0.75rem; border: none; border-radius: 0.375rem; font-weight: 500; cursor: pointer; font-size: 0.875rem;">
                        💾 Update Status Type
                    </button>
                    <button type="button" onclick="hideEditStatusForm()" style="flex: 1; background-color: #6b7280; color: white; padding: 0.75rem; border: none; border-radius: 0.375rem; font-weight: 500; cursor: pointer; font-size: 0.875rem;">
                        ✖️ Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function showAddStatusForm() {
            document.getElementById('addStatusModal').style.display = 'block';
            document.body.style.overflow = 'hidden';
        }

        function hideAddStatusForm() {
            document.getElementById('addStatusModal').style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        function showEditStatusForm() {
            document.getElementById('editStatusModal').style.display = 'block';
            document.body.style.overflow = 'hidden';
        }

        function hideEditStatusForm() {
            document.getElementById('editStatusModal').style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        // Color picker synchronization for add modal
        document.addEventListener('DOMContentLoaded', function() {
            const addColorPicker = document.querySelector('input[name="color_code"]');
            const addColorText = document.querySelector('input[name="color_code_text"]');
            
            addColorPicker.addEventListener('change', function() {
                addColorText.value = this.value;
            });
            
            addColorText.addEventListener('input', function() {
                if (this.value.match(/^#[0-9A-F]{6}$/i)) {
                    addColorPicker.value = this.value;
                }
            });

            // Color picker synchronization for edit modal
            const editColorPicker = document.getElementById('edit_color_code');
            const editColorText = document.getElementById('edit_color_code_text');
            
            editColorPicker.addEventListener('change', function() {
                editColorText.value = this.value;
            });
            
            editColorText.addEventListener('input', function() {
                if (this.value.match(/^#[0-9A-F]{6}$/i)) {
                    editColorPicker.value = this.value;
                }
            });
        });

        // Event delegation for edit buttons
        document.addEventListener('click', function(event) {
            if (event.target.classList.contains('edit-status-btn') || event.target.closest('.edit-status-btn')) {
                const button = event.target.classList.contains('edit-status-btn') ? event.target : event.target.closest('.edit-status-btn');
                const id = button.getAttribute('data-id');
                const name = button.getAttribute('data-name');
                const description = button.getAttribute('data-description');
                const color = button.getAttribute('data-color');
                
                document.getElementById('edit_status_id').value = id;
                document.getElementById('edit_status_name').value = name;
                document.getElementById('edit_status_description').value = description;
                document.getElementById('edit_color_code').value = color;
                document.getElementById('edit_color_code_text').value = color;
                
                showEditStatusForm();
            }
        });

        // Event delegation for delete buttons
        document.addEventListener('click', function(event) {
            if (event.target.classList.contains('delete-status-btn') || event.target.closest('.delete-status-btn')) {
                const button = event.target.classList.contains('delete-status-btn') ? event.target : event.target.closest('.delete-status-btn');
                const id = button.getAttribute('data-id');
                const name = button.getAttribute('data-name');
                
                if (confirm(`Are you sure you want to delete the status type "${name}"?\n\nThis action cannot be undone.`)) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.style.display = 'none';
                    
                    const deleteInput = document.createElement('input');
                    deleteInput.type = 'hidden';
                    deleteInput.name = 'delete_status';
                    deleteInput.value = '1';
                    
                    const idInput = document.createElement('input');
                    idInput.type = 'hidden';
                    idInput.name = 'status_id';
                    idInput.value = id;
                    
                    form.appendChild(deleteInput);
                    form.appendChild(idInput);
                    document.body.appendChild(form);
                    form.submit();
                }
            }
        });

        // Close modals when clicking outside
        window.onclick = function(event) {
            const addModal = document.getElementById('addStatusModal');
            const editModal = document.getElementById('editStatusModal');
            
            if (event.target === addModal) {
                hideAddStatusForm();
            }
            if (event.target === editModal) {
                hideEditStatusForm();
            }
        };
    </script>

    <?php include 'includes/footer.php'; ?>
</body>
</html>