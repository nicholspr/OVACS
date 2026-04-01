<?php
require_once 'includes/common.php';
require_once 'includes/ui_components.php';
include 'includes/database.php';

// Get database connection
$pdo = DatabaseConfig::getConnection();

$success_message = '';
$error_message = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    if (isset($_POST['action'])) {
        
        if ($_POST['action'] === 'add_requirement') {
            try {
                // Add new requirement
                $stmt = $pdo->prepare("INSERT INTO station_requirements (station_id, vehicle_type_id, shift_pattern_id, required_count) VALUES (?, ?, ?, ?)");
                $stmt->execute([
                    $_POST['station_id'],
                    $_POST['vehicle_type_id'], 
                    $_POST['shift_pattern_id'],
                    $_POST['required_count']
                ]);
                $success_message = "✅ Station requirement added successfully!";
            } catch (PDOException $e) {
                if ($e->getCode() == 23000) {
                    $error_message = "⚠️ A requirement for this station, vehicle type, and shift already exists.";
                } else {
                    $error_message = "❌ Error adding requirement: " . $e->getMessage();
                }
            }
        }
        
        elseif ($_POST['action'] === 'update_requirement') {
            try {
                // Update existing requirement
                $stmt = $pdo->prepare("UPDATE station_requirements SET required_count = ? WHERE id = ?");
                $stmt->execute([$_POST['required_count'], $_POST['requirement_id']]);
                $success_message = "✅ Requirement updated successfully!";
            } catch (PDOException $e) {
                $error_message = "❌ Error updating requirement: " . $e->getMessage();
            }
        }
        
        elseif ($_POST['action'] === 'delete_requirement') {
            try {
                // Delete requirement  
                $stmt = $pdo->prepare("DELETE FROM station_requirements WHERE id = ?");
                $stmt->execute([$_POST['requirement_id']]);
                $success_message = "✅ Requirement deleted successfully!";
            } catch (PDOException $e) {
                $error_message = "❌ Error deleting requirement: " . $e->getMessage();
            }
        }
    }
}

// Get all data for display
$stations = $pdo->query("SELECT id, name FROM stations WHERE is_active = 1 ORDER BY name")->fetchAll();
$vehicle_types = $pdo->query("SELECT id, type_code, type_name FROM vehicle_types ORDER BY type_name")->fetchAll();
$shift_patterns = $pdo->query("SELECT id, pattern_name, start_time, end_time, pattern_type FROM shift_patterns WHERE is_active = 1 ORDER BY pattern_name")->fetchAll();

// Get current requirements with station, vehicle type, and shift pattern details
$requirements = $pdo->query("
    SELECT 
        sr.id,
        sr.station_id,
        sr.vehicle_type_id,
        sr.shift_pattern_id,
        sr.required_count,
        sr.effective_date,
        s.name as station_name,
        vt.type_code,
        vt.type_name,
        sp.pattern_name,
        sp.start_time,
        sp.end_time,
        sp.pattern_type
    FROM station_requirements sr
    JOIN stations s ON sr.station_id = s.id
    JOIN vehicle_types vt ON sr.vehicle_type_id = vt.id
    JOIN shift_patterns sp ON sr.shift_pattern_id = sp.id
    ORDER BY s.name, vt.type_name, sp.pattern_name
")->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OVACS - Station Requirements</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <section class="hero">
        <div class="container">
            <h1 class="hero-title">Station Requirements</h1>
            <p class="hero-subtitle">Manage vehicle requirements by station and shift type</p>
            
            <?php if ($success_message): ?>
                <div style="background-color: #d1e7dd; color: #0f5132; padding: 1rem; border-radius: 0.5rem; margin-top: 1rem; border: 1px solid #badbcc;">
                    <?php echo $success_message; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($error_message): ?>
                <div style="background-color: #f8d7da; color: #721c24; padding: 1rem; border-radius: 0.5rem; margin-top: 1rem; border: 1px solid #f5c6cb;">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <main class="container" style="padding: 2rem 0;">
        
        <div style="background-color: #f8f9fa; padding: 2rem; border-radius: 0.5rem; margin-bottom: 2rem;">
            <button onclick="showAddRequirementForm()" style="background-color: #2563eb; color: white; padding: 0.75rem 1.5rem; border: none; border-radius: 0.375rem; font-size: 0.875rem; font-weight: 500; cursor: pointer;">
                ➕ Add New Requirement
            </button>
        </div>

        <div style="background-color: white; border-radius: 0.5rem; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1); overflow: hidden;">
            <div style="padding: 1.5rem; border-bottom: 1px solid #e5e7eb;">
                <h2 style="margin: 0; color: #1f2937; font-size: 1.25rem; font-weight: 600;">Station Requirements</h2>
                <p style="margin: 0.5rem 0 0 0; color: #6b7280; font-size: 0.875rem;">Vehicle requirements for each station and shift pattern</p>
            </div>
                


            <?php if (empty($requirements)): ?>
                <div style="padding: 3rem; text-align: center; color: #6b7280;">
                    <p style="margin: 0;">No requirements found. Add some requirements using the button above.</p>
                </div>
            <?php else: ?>
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background-color: #f8f9fa; color: #495057; font-size: 0.875rem;">
                                <th style="padding: 0.75rem 1rem; text-align: left; font-weight: 600; border-bottom: 1px solid #dee2e6;">Station</th>
                                <th style="padding: 0.75rem 1rem; text-align: left; font-weight: 600; border-bottom: 1px solid #dee2e6;">Vehicle Type</th>
                                <th style="padding: 0.75rem 1rem; text-align: left; font-weight: 600; border-bottom: 1px solid #dee2e6;">Shift Pattern</th>
                                <th style="padding: 0.75rem 1rem; text-align: left; font-weight: 600; border-bottom: 1px solid #dee2e6;">Required Count</th>
                                <th style="padding: 0.75rem 1rem; text-align: left; font-weight: 600; border-bottom: 1px solid #dee2e6;">Effective Date</th>
                                <th style="padding: 0.75rem 1rem; text-align: left; font-weight: 600; border-bottom: 1px solid #dee2e6;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $currentStation = '';
                            foreach ($requirements as $req): 
                                $isNewStation = ($currentStation !== $req['station_name']);
                                if ($isNewStation) $currentStation = $req['station_name'];
                            ?>
                            <tr style="border-bottom: 1px solid #f3f4f6; font-size: 0.875rem; <?php echo $isNewStation ? 'border-top: 2px solid #e5e7eb;' : ''; ?>">
                                <td style="padding: 0.75rem 1rem; color: #374151;">
                                    <?php echo htmlspecialchars($req['station_name']); ?>
                                </td>
                                <td style="padding: 0.75rem 1rem; color: #374151;">
                                    <span style="background-color: #2563eb; color: white; padding: 0.25rem 0.5rem; border-radius: 0.25rem; font-size: 0.75rem; font-weight: 600; margin-right: 0.5rem;">
                                        <?php echo htmlspecialchars($req['type_code']); ?>
                                    </span>
                                    <?php echo htmlspecialchars($req['type_name']); ?>
                                </td>
                                <td style="padding: 0.75rem 1rem; color: #374151;">
                                    <strong><?php echo htmlspecialchars($req['pattern_name']); ?></strong><br>
                                    <small style="color: #6b7280;">
                                        <?php echo date('H:i', strtotime($req['start_time'])); ?> - <?php echo date('H:i', strtotime($req['end_time'])); ?> 
                                        (<?php echo htmlspecialchars($req['pattern_type']); ?>)
                                    </small>
                                </td>
                                <td style="padding: 0.75rem 1rem;">
                                    <strong style="color: #059669; font-size: 1.1rem;"><?php echo $req['required_count']; ?></strong>
                                </td>
                                <td style="padding: 0.75rem 1rem; color: #6b7280; font-size: 0.875rem;">
                                    <?php echo date('M j, Y', strtotime($req['effective_date'])); ?>
                                </td>
                                <td style="padding: 0.75rem 1rem;">
                                    <button class="edit-btn" data-id="<?php echo $req['id']; ?>" data-count="<?php echo $req['required_count']; ?>" 
                                            style="background-color: #10b981; color: white; border: none; padding: 0.375rem 0.75rem; border-radius: 0.25rem; cursor: pointer; margin-right: 0.5rem; font-size: 0.875rem; position: relative; z-index: 20; pointer-events: auto;">
                                        ✏️ Edit
                                    </button>
                                    <button class="delete-btn" data-id="<?php echo $req['id']; ?>" data-name="<?php echo htmlspecialchars($req['station_name']) . ' - ' . htmlspecialchars($req['type_name']) . ' (' . htmlspecialchars($req['pattern_name']) . ')'; ?>" 
                                            style="background-color: #dc2626; color: white; border: none; padding: 0.375rem 0.75rem; border-radius: 0.25rem; cursor: pointer; font-size: 0.875rem; position: relative; z-index: 20; pointer-events: auto;">
                                        🗑️ Delete
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- Add Requirement Modal -->
    <div id="addRequirementModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.5); z-index: 1000;">
        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 2rem; border-radius: 0.5rem; width: 90%; max-width: 500px;">
            <h3 style="margin-top: 0; color: #1f2937; font-size: 1.25rem; font-weight: 600;">Add New Requirement</h3>
            
            <form method="POST" style="display: grid; gap: 1rem;">
                <input type="hidden" name="action" value="add_requirement">
                
                <div>
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #374151;">Station</label>
                    <select name="station_id" required style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 0.375rem; font-size: 0.875rem;">
                        <option value="">Select Station</option>
                        <?php foreach ($stations as $station): ?>
                            <option value="<?php echo $station['id']; ?>"><?php echo htmlspecialchars($station['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div>
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #374151;">Vehicle Type</label>
                    <select name="vehicle_type_id" required style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 0.375rem; font-size: 0.875rem;">
                        <option value="">Select Vehicle Type</option>
                        <?php foreach ($vehicle_types as $vtype): ?>
                            <option value="<?php echo $vtype['id']; ?>"><?php echo htmlspecialchars($vtype['type_name']); ?> (<?php echo htmlspecialchars($vtype['type_code']); ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div>
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #374151;">Shift Pattern</label>
                    <select name="shift_pattern_id" required style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 0.375rem; font-size: 0.875rem;">
                        <option value="">Select Shift Pattern</option>
                        <?php foreach ($shift_patterns as $pattern): ?>
                            <option value="<?php echo $pattern['id']; ?>">
                                <?php echo htmlspecialchars($pattern['pattern_name']); ?> 
                                (<?php echo date('H:i', strtotime($pattern['start_time'])); ?>-<?php echo date('H:i', strtotime($pattern['end_time'])); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div>
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #374151;">Required Count</label>
                    <input type="number" name="required_count" min="0" max="20" required 
                           style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 0.375rem; font-size: 0.875rem;">
                </div>
                
                <div style="display: flex; gap: 1rem; margin-top: 1rem;">
                    <button type="submit" style="flex: 1; background-color: #2563eb; color: white; padding: 0.75rem; border: none; border-radius: 0.375rem; font-size: 0.875rem; font-weight: 500; cursor: pointer;">
                        ✅ Add Requirement
                    </button>
                    <button type="button" onclick="hideAddRequirementForm()" style="flex: 1; background-color: #6b7280; color: white; padding: 0.75rem; border: none; border-radius: 0.375rem; font-size: 0.875rem; font-weight: 500; cursor: pointer;">
                        ❌ Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Requirement Modal -->
    <div id="editRequirementModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.5); z-index: 1000;">
        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 2rem; border-radius: 0.5rem; width: 90%; max-width: 400px;">
            <h3 style="margin-top: 0; color: #1f2937; font-size: 1.25rem; font-weight: 600;">Edit Requirement Count</h3>
            
            <form method="POST" style="display: grid; gap: 1rem;">
                <input type="hidden" name="action" value="update_requirement">
                <input type="hidden" id="edit_requirement_id" name="requirement_id">
                
                <div>
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #374151;">Required Count</label>
                    <input type="number" id="edit_required_count" name="required_count" min="0" max="20" required 
                           style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 0.375rem; font-size: 0.875rem;">
                </div>
                
                <div style="display: flex; gap: 1rem; margin-top: 1rem;">
                    <button type="submit" style="flex: 1; background-color: #10b981; color: white; padding: 0.75rem; border: none; border-radius: 0.375rem; font-size: 0.875rem; font-weight: 500; cursor: pointer;">
                        💾 Update Count
                    </button>
                    <button type="button" onclick="hideEditRequirementForm()" style="flex: 1; background-color: #6b7280; color: white; padding: 0.75rem; border: none; border-radius: 0.375rem; font-size: 0.875rem; font-weight: 500; cursor: pointer;">
                        ❌ Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Modal management functions
        function showAddRequirementForm() {
            document.getElementById('addRequirementModal').style.display = 'block';
            document.body.style.overflow = 'hidden';
        }

        function hideAddRequirementForm() {
            document.getElementById('addRequirementModal').style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        function showEditRequirementForm() {
            document.getElementById('editRequirementModal').style.display = 'block';
            document.body.style.overflow = 'hidden';
        }

        function hideEditRequirementForm() {
            document.getElementById('editRequirementModal').style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        // Event delegation for edit and delete buttons
        document.addEventListener('DOMContentLoaded', function() {
            // Edit button handler
            document.addEventListener('click', function(event) {
                if (event.target.classList.contains('edit-btn')) {
                    event.preventDefault();
                    const requirementId = event.target.dataset.id;
                    const currentCount = event.target.dataset.count;
                    
                    document.getElementById('edit_requirement_id').value = requirementId;
                    document.getElementById('edit_required_count').value = currentCount;
                    showEditRequirementForm();
                }
            });

            // Delete button handler
            document.addEventListener('click', function(event) {
                if (event.target.classList.contains('delete-btn')) {
                    event.preventDefault();
                    const requirementId = event.target.dataset.id;
                    const requirementName = event.target.dataset.name;
                    
                    if (confirm(`Delete requirement for:\n${requirementName}\n\nAre you sure?`)) {
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.innerHTML = `
                            <input type="hidden" name="action" value="delete_requirement">
                            <input type="hidden" name="requirement_id" value="${requirementId}">
                        `;
                        document.body.appendChild(form);
                        form.submit();
                    }
                }
            });

            // Close modal when clicking outside
            window.onclick = function(event) {
                const addModal = document.getElementById('addRequirementModal');
                const editModal = document.getElementById('editRequirementModal');
                
                if (event.target === addModal) {
                    hideAddRequirementForm();
                }
                if (event.target === editModal) {
                    hideEditRequirementForm();
                }
            };
        });
    </script>

    <?php include 'includes/footer.php'; ?>
</body>
</html>