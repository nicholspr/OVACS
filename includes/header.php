<?php
// Get current page for active navigation highlighting
$current_page = basename($_SERVER['PHP_SELF']);
?>
<header class="header">
    <nav class="nav container">
        <a href="index.php" class="logo">OVACS</a>
        <ul class="nav-links">
            <li><a href="index.php" <?php echo ($current_page == 'index.php') ? 'class="active"' : ''; ?>>Home</a></li>
            <li><a href="#about">About</a></li>
            <li><a href="#contact">Contact</a></li>
        </ul>
    </nav>
</header>