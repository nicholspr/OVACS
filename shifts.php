<?php
// Database connection and error handling
$errorMessage = '';
$successMessage = '';
$shifts = [];

try {
    include 'includes/database.php';
    $pdo = DatabaseConfig::getConnection();
    
    // Handle form submissions
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['action'])) {
            if ($_POST['action'] === 'add') {
                $stmt = $pdo->prepare("INSERT INTO shift_patterns (pattern_name, start_time, end_time, duration_hours, pattern_type) VALUES (?, ?, ?, ?, ?)");
                $duration = calculateDuration($_POST['start_time'], $_POST['end_time']);
                $stmt->execute([
                    $_POST['shift_name'],
                    $_POST['start_time'],
                    $_POST['end_time'],
                    $duration,
                    $_POST['pattern_type']
                ]);
                $successMessage = "Shift pattern added successfully!";
            } elseif ($_POST['action'] === 'edit') {
                try {
                    $stmt = $pdo->prepare("UPDATE shift_patterns SET pattern_name = ?, start_time = ?, end_time = ?, duration_hours = ?, pattern_type = ? WHERE id = ?");
                    $duration = calculateDuration($_POST['start_time'], $_POST['end_time']);
                    $result = $stmt->execute([
                        $_POST['shift_name'],
                        $_POST['start_time'],
                        $_POST['end_time'],
                        $duration,
                        $_POST['pattern_type'],
                        $_POST['shift_id']
                    ]);
                    if ($stmt->rowCount() > 0) {
                        $successMessage = "Shift pattern updated successfully!";
                    } else {
                        $errorMessage = "No shift pattern was updated. Please check the ID.";
                    }
                } catch (Exception $e) {
                    $errorMessage = "Error updating shift pattern: " . $e->getMessage();
                }
            } elseif ($_POST['action'] === 'delete') {
                $stmt = $pdo->prepare("DELETE FROM shift_patterns WHERE id = ?");
                $stmt->execute([$_POST['shift_id']]);
                $successMessage = "Shift pattern deleted successfully!";
            }
        }
    }
    
    // Fetch shifts from database
    $stmt = $pdo->query("SELECT * FROM shift_patterns ORDER BY start_time");
    $shifts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    $errorMessage = "Database error: " . $e->getMessage();
}

function calculateDuration($start, $end) {
    $start_time = strtotime($start);
    $end_time = strtotime($end);
    if ($end_time < $start_time) {
        $end_time += 24 * 3600; // Add 24 hours for next day
    }
    return ($end_time - $start_time) / 3600; // Convert to hours
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shift Management - OVACS</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <section class="hero">
        <div class="container">
            <h1 class="hero-title">Shift Management</h1>
            <p class="hero-subtitle">Manage shift patterns and schedules</p>

            <?php if ($errorMessage): ?>
                <div style="background: #fee2e2; border: 1px solid #dc2626; color: #dc2626; padding: 0.75rem 1rem; border-radius: 0.375rem; margin-top: 1rem;">
                    ⚠️ <?php echo htmlspecialchars($errorMessage); ?>
                </div>
            <?php endif; ?>

            <?php if ($successMessage): ?>
                <div style="background: #d1e7dd; border: 1px solid #0f5132; color: #0f5132; padding: 0.75rem 1rem; border-radius: 0.375rem; margin-top: 1rem;">
                    ✅ <?php echo htmlspecialchars($successMessage); ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <main class="container" style="padding: 2rem 0;">

        <div style="background: #f8f9fa; padding: 1.5rem; border-radius: 0.5rem; margin-bottom: 2rem;">
            <button onclick="showAddShiftForm()" style="background: #10b981; color: white; padding: 0.75rem 1.5rem; border: none; border-radius: 0.375rem; font-weight: 500; cursor: pointer; transition: all 0.2s; font-size: 0.875rem;">✅ Add New Shift Pattern</button>
        </div>

        <div style="background: white; border-radius: 0.5rem; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1); overflow: hidden;">
            <div style="padding: 1rem 1.5rem; background: #f8f9fa; border-bottom: 1px solid #dee2e6;">
                <h2 style="margin: 0; color: #495057; font-weight: 600; font-size: 1.125rem;">Shift Patterns</h2>
            </div>
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr>
                        <th style="padding: 0.75rem 1rem; text-align: left; background: #f8f9fa; font-weight: 600; color: #495057; font-size: 0.875rem;">Pattern Name</th>
                        <th style="padding: 0.75rem 1rem; text-align: left; background: #f8f9fa; font-weight: 600; color: #495057; font-size: 0.875rem;">Start Time</th>
                        <th style="padding: 0.75rem 1rem; text-align: left; background: #f8f9fa; font-weight: 600; color: #495057; font-size: 0.875rem;">End Time</th>
                        <th style="padding: 0.75rem 1rem; text-align: left; background: #f8f9fa; font-weight: 600; color: #495057; font-size: 0.875rem;">Duration</th>
                        <th style="padding: 0.75rem 1rem; text-align: left; background: #f8f9fa; font-weight: 600; color: #495057; font-size: 0.875rem;">Type</th>
                        <th style="padding: 0.75rem 1rem; text-align: left; background: #f8f9fa; font-weight: 600; color: #495057; font-size: 0.875rem;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($shifts)): ?>
                        <tr>
                            <td colspan="6" style="padding: 2rem; text-align: center; color: #6b7280; font-size: 0.875rem;">
                                No shift patterns found. Add a shift pattern to get started.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($shifts as $shift): ?>
                            <tr>
                                <td style="padding: 0.75rem 1rem; border-bottom: 1px solid #dee2e6; font-size: 0.875rem; font-weight: 500;"><?php echo htmlspecialchars($shift['pattern_name']); ?></td>
                                <td style="padding: 0.75rem 1rem; border-bottom: 1px solid #dee2e6; font-size: 0.875rem;"><?php echo htmlspecialchars($shift['start_time']); ?></td>
                                <td style="padding: 0.75rem 1rem; border-bottom: 1px solid #dee2e6; font-size: 0.875rem;"><?php echo htmlspecialchars($shift['end_time']); ?></td>
                                <td style="padding: 0.75rem 1rem; border-bottom: 1px solid #dee2e6; font-size: 0.875rem;"><?php echo $shift['duration_hours']; ?> hours</td>
                                <td style="padding: 0.75rem 1rem; border-bottom: 1px solid #dee2e6; font-size: 0.875rem;">
                                    <span style="background: #2563eb; color: white; padding: 0.25rem 0.5rem; border-radius: 0.25rem; font-size: 0.75rem; font-weight: 500;"><?php echo htmlspecialchars($shift['pattern_type']); ?></span>
                                </td>
                                <td style="padding: 0.75rem 1rem; border-bottom: 1px solid #dee2e6; position: relative; z-index: 5;">
                                    <button class="edit-shift-btn" 
                                        data-id="<?php echo htmlspecialchars($shift['id']); ?>"
                                        data-name="<?php echo htmlspecialchars($shift['pattern_name']); ?>"
                                        data-start="<?php echo htmlspecialchars($shift['start_time']); ?>"
                                        data-end="<?php echo htmlspecialchars($shift['end_time']); ?>"
                                        data-type="<?php echo htmlspecialchars($shift['pattern_type']); ?>"
                                        style="background: #10b981; color: white; padding: 0.375rem 0.75rem; border: none; border-radius: 0.25rem; margin-right: 0.5rem; cursor: pointer; font-size: 0.75rem; transition: all 0.2s; position: relative !important; z-index: 20 !important; pointer-events: auto !important;">✏️ Edit</button>
                                    <button class="delete-shift-btn" 
                                        data-id="<?php echo htmlspecialchars($shift['id']); ?>"
                                        data-name="<?php echo htmlspecialchars($shift['pattern_name']); ?>"
                                        style="background: #dc2626; color: white; padding: 0.375rem 0.75rem; border: none; border-radius: 0.25rem; cursor: pointer; font-size: 0.75rem; transition: all 0.2s; position: relative !important; z-index: 20 !important; pointer-events: auto !important;">🗑️ Delete</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

    <!-- Add Shift Modal (hidden by default) -->
    <div id="addShiftModal" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); align-items: center; justify-content: center;">
        <div style="background-color: white; padding: 2rem; border-radius: 0.5rem; width: 90%; max-width: 500px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            <h3 style="margin: 0 0 1.5rem 0; font-weight: 600; color: #374151;">Add New Shift Pattern</h3>
            <form id="addShiftForm" method="POST">
                <input type="hidden" name="action" value="add">
                <div style="margin-bottom: 1rem;">
                    <label for="shift_name" style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #374151; font-size: 0.875rem;">Pattern Name:</label>
                    <input type="text" id="shift_name" name="shift_name" required style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 0.375rem; font-size: 0.875rem; box-sizing: border-box;">
                </div>
                <div style="margin-bottom: 1rem;">
                    <label for="start_time" style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #374151; font-size: 0.875rem;">Start Time:</label>
                    <input type="time" id="start_time" name="start_time" step="1" required style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 0.375rem; font-size: 0.875rem; box-sizing: border-box;">
                </div>
                <div style="margin-bottom: 1rem;">
                    <label for="end_time" style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #374151; font-size: 0.875rem;">End Time:</label>
                    <input type="time" id="end_time" name="end_time" step="1" required style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 0.375rem; font-size: 0.875rem; box-sizing: border-box;">
                </div>
                <div style="margin-bottom: 1rem;">
                    <label for="pattern_type" style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #374151; font-size: 0.875rem;">Pattern Type:</label>
                    <select id="pattern_type" name="pattern_type" required style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 0.375rem; font-size: 0.875rem; box-sizing: border-box;">
                        <option value="">Select Type</option>
                        <option value="Day">Day</option>
                        <option value="Night">Night</option>
                        <option value="Split">Split</option>
                        <option value="24Hour">24 Hour</option>
                    </select>
                </div>
                <div style="display: flex; gap: 0.75rem; margin-top: 1.5rem;">
                    <button type="submit" style="background: #10b981; color: white; padding: 0.75rem 1.5rem; border: none; border-radius: 0.375rem; cursor: pointer; font-size: 0.875rem; font-weight: 500;">Add Shift Pattern</button>
                    <button type="button" onclick="hideAddShiftForm()" style="background: #6b7280; color: white; padding: 0.75rem 1.5rem; border: none; border-radius: 0.375rem; cursor: pointer; font-size: 0.875rem; font-weight: 500;">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Shift Modal (hidden by default) -->
    <!-- Edit Shift Modal (hidden by default) -->
    <div id="editShiftModal" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); align-items: center; justify-content: center;">
        <div style="background-color: white; padding: 2rem; border-radius: 0.5rem; width: 90%; max-width: 500px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            <h3 style="margin: 0 0 1.5rem 0; font-weight: 600; color: #374151;">Edit Shift Pattern</h3>
            <form id="editShiftForm" method="POST">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" id="edit_shift_id" name="shift_id">
                <div style="margin-bottom: 1rem;">
                    <label for="edit_shift_name" style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #374151; font-size: 0.875rem;">Pattern Name:</label>
                    <input type="text" id="edit_shift_name" name="shift_name" required style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 0.375rem; font-size: 0.875rem; box-sizing: border-box;">
                </div>
                <div style="margin-bottom: 1rem;">
                    <label for="edit_start_time" style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #374151; font-size: 0.875rem;">Start Time:</label>
                    <input type="time" id="edit_start_time" name="start_time" step="1" required style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 0.375rem; font-size: 0.875rem; box-sizing: border-box;">
                </div>
                <div style="margin-bottom: 1rem;">
                    <label for="edit_end_time" style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #374151; font-size: 0.875rem;">End Time:</label>
                    <input type="time" id="edit_end_time" name="end_time" step="1" required style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 0.375rem; font-size: 0.875rem; box-sizing: border-box;">
                </div>
                <div style="margin-bottom: 1rem;">
                    <label for="edit_pattern_type" style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #374151; font-size: 0.875rem;">Pattern Type:</label>
                    <select id="edit_pattern_type" name="pattern_type" required style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 0.375rem; font-size: 0.875rem; box-sizing: border-box;">
                        <option value="Day">Day</option>
                        <option value="Night">Night</option>
                        <option value="Split">Split</option>
                        <option value="24Hour">24 Hour</option>
                    </select>
                </div>
                <div style="display: flex; gap: 0.75rem; margin-top: 1.5rem;">
                    <button type="submit" style="background: #10b981; color: white; padding: 0.75rem 1.5rem; border: none; border-radius: 0.375rem; cursor: pointer; font-size: 0.875rem; font-weight: 500;">Update Shift Pattern</button>
                    <button type="button" onclick="hideEditShiftForm()" style="background: #6b7280; color: white; padding: 0.75rem 1.5rem; border: none; border-radius: 0.375rem; cursor: pointer; font-size: 0.875rem; font-weight: 500;">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script>
        function showAddShiftForm() {
            document.getElementById('addShiftModal').style.display = 'flex';
        }

        function hideAddShiftForm() {
            document.getElementById('addShiftModal').style.display = 'none';
        }

        function showEditShiftForm() {
            document.getElementById('editShiftModal').style.display = 'flex';
        }

        function hideEditShiftForm() {
            document.getElementById('editShiftModal').style.display = 'none';
        }

        function editShift(id, name, startTime, endTime, patternType) {
            document.getElementById('edit_shift_id').value = id;
            document.getElementById('edit_shift_name').value = name;
            document.getElementById('edit_start_time').value = startTime;
            document.getElementById('edit_end_time').value = endTime;
            document.getElementById('edit_pattern_type').value = patternType;
            
            showEditShiftForm();
        }

        function deleteShift(id, name) {
            if (confirm('Are you sure you want to delete the shift pattern "' + name + '"?')) {
                var form = document.createElement('form');
                form.method = 'POST';
                form.style.display = 'none';
                
                var actionInput = document.createElement('input');
                actionInput.type = 'hidden';
                actionInput.name = 'action';
                actionInput.value = 'delete';
                
                var idInput = document.createElement('input');
                idInput.type = 'hidden';
                idInput.name = 'shift_id';
                idInput.value = id;
                
                form.appendChild(actionInput);
                form.appendChild(idInput);
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Event delegation for edit buttons
        document.addEventListener('click', function(event) {
            if (event.target.classList.contains('edit-shift-btn')) {
                const id = event.target.getAttribute('data-id');
                const name = event.target.getAttribute('data-name');
                const start = event.target.getAttribute('data-start');
                const end = event.target.getAttribute('data-end');
                const type = event.target.getAttribute('data-type');
                
                editShift(id, name, start, end, type);
            }
        });

        // Event delegation for delete buttons
        document.addEventListener('click', function(event) {
            if (event.target.classList.contains('delete-shift-btn')) {
                const id = event.target.getAttribute('data-id');
                const name = event.target.getAttribute('data-name');
                
                deleteShift(id, name);
            }
        });

        // Close modal when clicking outside
        window.onclick = function(event) {
            const addModal = document.getElementById('addShiftModal');
            const editModal = document.getElementById('editShiftModal');
            if (event.target == addModal) {
                addModal.style.display = 'none';
            }
            if (event.target == editModal) {
                editModal.style.display = 'none';
            }
        };
    </script>

    <?php include 'includes/footer.php'; ?>
    <script src="js/main.js"></script>
</body>
</html>