<?php
// Database connection and error handling
$errorMessage = '';
$reportData = [];

try {
    include 'includes/database.php';
    $vehicleManager = new VehicleManager(); 
    // For now, we'll use placeholder data for reports
    $reportData = [
        'fleet_utilization' => 87.3,
        'average_response_time' => 4.2,
        'maintenance_cost' => 45620,
        'total_deployments' => 1247,
        'monthly_stats' => [
            ['month' => 'January', 'deployments' => 423, 'avg_response' => 4.1],
            ['month' => 'February', 'deployments' => 387, 'avg_response' => 4.3],
            ['month' => 'March', 'deployments' => 437, 'avg_response' => 4.0]
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
    <title>Reports & Analytics - OVACS</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container" style="margin-top: 100px; padding: 20px;">
        <h1>Reports & Analytics</h1>
        <p class="subtitle">Generate reports on vehicle utilization, response times, and fleet performance</p>

        <?php if ($errorMessage): ?>
            <div class="alert alert-error">
                <strong>Error:</strong> <?php echo htmlspecialchars($errorMessage); ?>
            </div>
        <?php endif; ?>

        <div class="action-buttons" style="margin: 20px 0;">
            <button onclick="generateReport('utilization')" class="btn btn-primary">Fleet Utilization Report</button>
            <button onclick="generateReport('response')" class="btn btn-primary">Response Time Report</button>
            <button onclick="generateReport('maintenance')" class="btn btn-primary">Maintenance Report</button>
            <a href="index.php" class="btn btn-secondary">Back to Dashboard</a>
        </div>

        <div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin: 30px 0;">
            <div class="stat-card">
                <h3>Fleet Utilization</h3>
                <div class="stat-number" style="color: #28a745; font-size: 2em;"><?php echo $reportData['fleet_utilization']; ?>%</div>
                <p>Current utilization rate</p>
            </div>
            <div class="stat-card">
                <h3>Avg Response Time</h3>
                <div class="stat-number" style="color: #007bff; font-size: 2em;"><?php echo $reportData['average_response_time']; ?> min</div>
                <p>Average emergency response</p>
            </div>
            <div class="stat-card">
                <h3>Maintenance Costs</h3>
                <div class="stat-number" style="color: #ffc107; font-size: 2em;">$<?php echo number_format($reportData['maintenance_cost']); ?></div>
                <p>Monthly maintenance budget</p>
            </div>
            <div class="stat-card">
                <h3>Total Deployments</h3>
                <div class="stat-number" style="color: #dc3545; font-size: 2em;"><?php echo number_format($reportData['total_deployments']); ?></div>
                <p>This quarter</p>
            </div>
        </div>

        <div class="data-table">
            <h2>Monthly Performance Summary</h2>
            <table>
                <thead>
                    <tr>
                        <th>Month</th>
                        <th>Total Deployments</th>
                        <th>Average Response Time</th>
                        <th>Performance Rating</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reportData['monthly_stats'] as $month): 
                        $rating = $month['avg_response'] <= 4.0 ? 'Excellent' : ($month['avg_response'] <= 4.5 ? 'Good' : 'Needs Improvement');
                        $ratingColor = $month['avg_response'] <= 4.0 ? '#28a745' : ($month['avg_response'] <= 4.5 ? '#ffc107' : '#dc3545');
                    ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($month['month']); ?></strong></td>
                            <td><?php echo number_format($month['deployments']); ?></td>
                            <td><?php echo $month['avg_response']; ?> minutes</td>
                            <td style="color: <?php echo $ratingColor; ?>; font-weight: 500;"><?php echo $rating; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="report-actions" style="margin: 30px 0; padding: 20px; background: #f8f9fa; border-radius: 8px;">
            <h3>Generate Custom Reports</h3>
            <div class="form-group" style="margin: 15px 0;">
                <label for="reportType">Report Type:</label>
                <select id="reportType" class="form-control">
                    <option value="utilization">Fleet Utilization</option>
                    <option value="response">Response Times</option>
                    <option value="maintenance">Maintenance Schedule</option>
                    <option value="deployment">Deployment History</option>
                </select>
            </div>
            <div class="form-group" style="margin: 15px 0;">
                <label for="dateRange">Date Range:</label>
                <select id="dateRange" class="form-control">
                    <option value="week">Last Week</option>
                    <option value="month">Last Month</option>
                    <option value="quarter">Last Quarter</option>
                    <option value="year">Last Year</option>
                </select>
            </div>
            <button onclick="downloadReport()" class="btn btn-success">Download Report (PDF)</button>
            <button onclick="emailReport()" class="btn btn-primary">Email Report</button>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script>
        function generateReport(type) {
            alert(`Generating ${type} report... This feature will be fully implemented in the next update.`);
        }

        function downloadReport() {
            const reportType = document.getElementById('reportType').value;
            const dateRange = document.getElementById('dateRange').value;
            alert(`Downloading ${reportType} report for ${dateRange}... PDF generation will be implemented in the next update.`);
        }

        function emailReport() {
            const reportType = document.getElementById('reportType').value;
            const dateRange = document.getElementById('dateRange').value;
            alert(`Emailing ${reportType} report for ${dateRange}... Email functionality will be implemented in the next update.`);
        }
    </script>

    <script src="js/main.js"></script>

    <style>
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .stat-card h3 {
            margin: 0 0 10px 0;
            color: #333;
            font-size: 1.1em;
        }
        
        .stat-card p {
            margin: 10px 0 0 0;
            color: #666;
            font-size: 0.9em;
        }
        
        .form-control {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: #333;
        }
    </style>
</body>
</html>