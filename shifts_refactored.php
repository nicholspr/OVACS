<?php
/**
 * OVACS - Shift Pattern Management (Refactored)
 */

require_once 'includes/base_page.php';

class ShiftPatternsPage extends CRUDPage {
    private $shifts = [];
    
    public function __construct() {
        parent::__construct('Shift Pattern Management', 'Shift Pattern', [], []);
        $this->loadShiftPatterns();
    }
    
    private function loadShiftPatterns() {
        try {
            $pdo = DatabaseConfig::getConnection();
            $stmt = $pdo->query("SELECT * FROM shift_patterns ORDER BY start_time");
            $this->shifts = $stmt->fetchAll();
        } catch (Exception $e) {
            $this->addMessage('error', 'Failed to load shift patterns: ' . $e->getMessage());
            $this->shifts = [];
        }
    }
    
    private function calculateDuration($start, $end) {
        $start_time = strtotime($start);
        $end_time = strtotime($end);
        if ($end_time < $start_time) {
            $end_time += 24 * 3600; // Add 24 hours for next day
        }
        return ($end_time - $start_time) / 3600; // Convert to hours
    }
    
    protected function handleAdd() {
        try {
            $pdo = DatabaseConfig::getConnection();
            $stmt = $pdo->prepare("INSERT INTO shift_patterns (pattern_name, start_time, end_time, duration_hours, pattern_type) VALUES (?, ?, ?, ?, ?)");
            $duration = $this->calculateDuration($_POST['start_time'], $_POST['end_time']);
            
            $stmt->execute([
                $_POST['shift_name'],
                $_POST['start_time'],
                $_POST['end_time'],
                $duration,
                $_POST['pattern_type']
            ]);
            
            $this->redirectWithMessage('shifts_refactored.php');
        } catch (Exception $e) {
            $this->addMessage('error', 'Failed to add shift pattern: ' . $e->getMessage());
        }
    }
    
    protected function handleEdit() {
        try {
            $pdo = DatabaseConfig::getConnection();
            $stmt = $pdo->prepare("UPDATE shift_patterns SET pattern_name = ?, start_time = ?, end_time = ?, duration_hours = ?, pattern_type = ? WHERE id = ?");
            $duration = $this->calculateDuration($_POST['start_time'], $_POST['end_time']);
            
            $result = $stmt->execute([
                $_POST['shift_name'],
                $_POST['start_time'],
                $_POST['end_time'],
                $duration,
                $_POST['pattern_type'],
                $_POST['shift_id']
            ]);
            
            if ($stmt->rowCount() > 0) {
                $this->redirectWithMessage('shifts_refactored.php');
            } else {
                $this->addMessage('warning', 'No changes were made or shift pattern not found.');
            }
        } catch (Exception $e) {
            $this->addMessage('error', 'Failed to update shift pattern: ' . $e->getMessage());
        }
    }
    
    protected function handleDelete() {
        try {
            $pdo = DatabaseConfig::getConnection();
            $stmt = $pdo->prepare("DELETE FROM shift_patterns WHERE id = ?");
            $stmt->execute([$_POST['shift_id']]);
            
            $this->redirectWithMessage('shifts_refactored.php');
        } catch (Exception $e) {
            $this->addMessage('error', 'Failed to delete shift pattern: ' . $e->getMessage());
        }
    }
    
    protected function getTableHeaders() {
        return ['Pattern Name', 'Start Time', 'End Time', 'Duration (hrs)', 'Type', 'Actions'];
    }
    
    protected function getTableData() {
        $rows = [];
        foreach ($this->shifts as $shift) {
            $editBtn = '<button data-id="' . safeHtml($shift['id']) . '" data-name="' . safeHtml($shift['pattern_name']) . '" data-start="' . safeHtml($shift['start_time']) . '" data-end="' . safeHtml($shift['end_time']) . '" data-type="' . safeHtml($shift['pattern_type']) . '" class="edit-shift-btn" style="background: #10b981; color: white; border: none; padding: 0.35rem 0.75rem; border-radius: 4px; font-size: 0.75rem; cursor: pointer; margin-right: 0.5rem;">✏️ Edit</button>';
            $deleteBtn = '<button data-id="' . safeHtml($shift['id']) . '" data-name="' . safeHtml($shift['pattern_name']) . '" class="delete-shift-btn" style="background: #dc2626; color: white; border: none; padding: 0.35rem 0.75rem; border-radius: 4px; font-size: 0.75rem; cursor: pointer;">🗑️ Delete</button>';
            
            $rows[] = [
                safeHtml($shift['pattern_name']),
                safeHtml($shift['start_time']),
                safeHtml($shift['end_time']),
                safeHtml($shift['duration_hours']),
                renderVehicleTypeBadge($shift['pattern_type'], '#6366f1'),
                $editBtn . $deleteBtn
            ];
        }
        return $rows;
    }
    
    protected function getSuccessMessage() {
        return 'Shift pattern operation completed successfully!';
    }
    
    protected function getErrorMessage() {
        return 'Failed to process shift pattern operation.';
    }
    
    protected function renderContent() {
        $this->renderHero('Create, edit, and manage shift patterns for your emergency services');
        $this->renderAddButton('addShiftModal');
        
        echo '<main style="padding: 2rem 0;">
            <div class="container">';
            
        $this->renderDataTable();
        
        echo '</div>
        </main>';
        
        $this->renderModals();
    }
    
    private function renderModals() {
        // Add Shift Modal
        $addFormContent = '
        <form id="addShiftModalForm" method="POST" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <input type="hidden" name="action" value="add">
            
            <div style="grid-column: 1 / -1;">
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #374151;">Pattern Name:</label>
                <input type="text" name="shift_name" required style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 6px;">
            </div>
            
            <div>
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #374151;">Start Time:</label>
                <input type="time" name="start_time" step="1" required style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 6px;">
            </div>
            
            <div>
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #374151;">End Time:</label>
                <input type="time" name="end_time" step="1" required style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 6px;">
            </div>
            
            <div style="grid-column: 1 / -1;">
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #374151;">Pattern Type:</label>
                <select name="pattern_type" required style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 6px;">
                    <option value="">Select pattern type...</option>
                    <option value="Day">Day</option>
                    <option value="Night">Night</option>
                    <option value="Split">Split</option>
                    <option value="24Hour">24 Hour</option>
                </select>
            </div>
        </form>';
        
        renderModal('addShiftModal', 'Add New Shift Pattern', $addFormContent);
        
        // Edit Shift Modal  
        $editFormContent = '
        <form id="editShiftModalForm" method="POST" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="shift_id" id="editShiftId">
            
            <div style="grid-column: 1 / -1;">
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #374151;">Pattern Name:</label>
                <input type="text" name="shift_name" id="editShiftName" required style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 6px;">
            </div>
            
            <div>
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #374151;">Start Time:</label>
                <input type="time" name="start_time" id="editStartTime" step="1" required style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 6px;">
            </div>
            
            <div>
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #374151;">End Time:</label>
                <input type="time" name="end_time" id="editEndTime" step="1" required style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 6px;">
            </div>
            
            <div style="grid-column: 1 / -1;">
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #374151;">Pattern Type:</label>
                <select name="pattern_type" id="editPatternType" required style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 6px;">
                    <option value="Day">Day</option>
                    <option value="Night">Night</option>
                    <option value="Split">Split</option>
                    <option value="24Hour">24 Hour</option>
                </select>
            </div>
        </form>';
        
        renderModal('editShiftModal', 'Edit Shift Pattern', $editFormContent, 'Update Shift Pattern');
        
        // JavaScript for modal handling
        echo '<script>
        // Edit shift function
        OVACSEvents.setupButtonDelegation(".edit-shift-btn", function(data) {
            document.getElementById("editShiftId").value = data.id;
            document.getElementById("editShiftName").value = data.name;
            document.getElementById("editStartTime").value = data.start;
            document.getElementById("editEndTime").value = data.end;
            document.getElementById("editPatternType").value = data.type;
            OVACSModal.show("editShiftModal");
        });
        
        // Delete shift function
        OVACSEvents.setupButtonDelegation(".delete-shift-btn", function(data) {
            OVACSUtils.confirm("Are you sure you want to delete the shift pattern: " + data.name + "?", function() {
                const form = document.createElement("form");
                form.method = "POST";
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="shift_id" value="${data.id}">
                `;
                document.body.appendChild(form);
                form.submit();
            });
        });
        </script>';
    }
}

// Initialize and render the page
$page = new ShiftPatternsPage();
$page->render();
?>