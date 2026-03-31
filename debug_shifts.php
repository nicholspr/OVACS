<?php
// Simple debug page to test shift editing
include 'includes/database.php';
$pdo = DatabaseConfig::getConnection();

// Get the first shift for testing
$stmt = $pdo->query("SELECT * FROM shift_patterns LIMIT 1");
$shift = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Debug Shifts</title>
</head>
<body>
    <h1>Debug Shift Edit</h1>
    
    <?php if ($shift): ?>
        <p>Test shift: <?php echo htmlspecialchars($shift['pattern_name']); ?></p>
        
        <button onclick="testEdit()" style="padding: 10px; background: blue; color: white; border: none; cursor: pointer;">
            Test Edit Function
        </button>
        
        <div id="result"></div>
        
        <script>
        function testEdit() {
            document.getElementById('result').innerHTML = '<p style="color: green;">Edit button clicked! Function is working.</p>';
            console.log('Edit function test successful');
            
            // Test if we can populate form fields
            try {
                const testData = {
                    id: <?php echo $shift['id']; ?>,
                    name: <?php echo json_encode($shift['pattern_name']); ?>,
                    start: <?php echo json_encode($shift['start_time']); ?>,
                    end: <?php echo json_encode($shift['end_time']); ?>,
                    type: <?php echo json_encode($shift['pattern_type']); ?>
                };
                document.getElementById('result').innerHTML += '<p>Test data: ' + JSON.stringify(testData) + '</p>';
            } catch (e) {
                document.getElementById('result').innerHTML += '<p style="color: red;">Error: ' + e.message + '</p>';
            }
        }
        </script>
    <?php else: ?>
        <p>No shift patterns found in database!</p>
    <?php endif; ?>
    
    <p><a href="shifts.php">Back to Shifts Page</a></p>
</body>
</html>