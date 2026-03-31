<?php
// Get current page for active navigation highlighting
$current_page = basename($_SERVER['PHP_SELF']);
?>
<header class="header">
    <nav class="nav container">
        <a href="index.php" class="logo">OVACS</a>
        <ul class="nav-links">
            <li><a href="index.php" <?php echo ($current_page == 'index.php') ? 'class="active"' : ''; ?>>Dashboard</a></li>
            <li><a href="vehicles.php" <?php echo ($current_page == 'vehicles.php') ? 'class="active"' : ''; ?>>Vehicles</a></li>
            <li><a href="stations.php" <?php echo ($current_page == 'stations.php') ? 'class="active"' : ''; ?>>Stations</a></li>
            <li><a href="shifts.php" <?php echo ($current_page == 'shifts.php') ? 'class="active"' : ''; ?>>Shifts</a></li>
            <li><a href="availability.php" <?php echo ($current_page == 'availability.php') ? 'class="active"' : ''; ?>>Live Board</a></li>
            <li><a href="reports.php" <?php echo ($current_page == 'reports.php') ? 'class="active"' : ''; ?>>Reports</a></li>
        </ul>
    </nav>
</header>