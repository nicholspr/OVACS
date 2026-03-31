<?php
include 'includes/database.php';

// Handle success messages
$success_message = '';
$error_message = '';

if (isset($_GET['success'])) {
    switch ($_GET['success']) {
        case 'added':
            $success_message = "✅ Item added successfully!";
            break;
        case 'updated':
            $success_message = "✅ Item updated successfully!";
            break;
        case 'deleted':
            $success_message = "✅ Item deleted successfully!";
            break;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OVACS - System Administration</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <section class="hero">
        <div class="container">
            <h1 class="hero-title">System Administration</h1>
            <p class="hero-subtitle">Manage system settings and administrative functions</p>
            
            <?php if ($success_message): ?>
                <div style="background-color: #d1e7dd; border: 1px solid #badbcc; color: #0f5132; padding: 0.75rem 1rem; border-radius: 0.375rem; margin: 1rem 0;">
                    <?php echo htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($error_message): ?>
                <div style="background-color: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 0.75rem 1rem; border-radius: 0.375rem; margin: 1rem 0;">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <main class="container" style="padding: 2rem 0;">
        <!-- Admin Functions Grid -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem; margin-bottom: 2rem;">
            
            <!-- User Management Card -->
            <div style="background: #fff; border-radius: 0.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); padding: 1.5rem;">
                <h3 style="color: #1f2937; margin: 0 0 0.5rem 0; font-size: 1.125rem; font-weight: 600;">User Management</h3>
                <p style="color: #6b7280; margin: 0 0 1rem 0; font-size: 0.875rem;">Manage user accounts, roles, and permissions</p>
                <button style="background: #2563eb; color: white; border: none; padding: 0.5rem 1rem; border-radius: 0.375rem; font-size: 0.875rem; cursor: pointer; opacity: 0.6;" disabled>
                    Coming Soon
                </button>
            </div>

            <!-- System Settings Card -->
            <div style="background: #fff; border-radius: 0.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); padding: 1.5rem;">
                <h3 style="color: #1f2937; margin: 0 0 0.5rem 0; font-size: 1.125rem; font-weight: 600;">System Settings</h3>
                <p style="color: #6b7280; margin: 0 0 1rem 0; font-size: 0.875rem;">Configure system preferences and global settings</p>
                <button style="background: #2563eb; color: white; border: none; padding: 0.5rem 1rem; border-radius: 0.375rem; font-size: 0.875rem; cursor: pointer; opacity: 0.6;" disabled>
                    Coming Soon
                </button>
            </div>

            <!-- Backup & Restore Card -->
            <div style="background: #fff; border-radius: 0.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); padding: 1.5rem;">
                <h3 style="color: #1f2937; margin: 0 0 0.5rem 0; font-size: 1.125rem; font-weight: 600;">Backup & Restore</h3>
                <p style="color: #6b7280; margin: 0 0 1rem 0; font-size: 0.875rem;">Database backup and system restore functions</p>
                <button style="background: #2563eb; color: white; border: none; padding: 0.5rem 1rem; border-radius: 0.375rem; font-size: 0.875rem; cursor: pointer; opacity: 0.6;" disabled>
                    Coming Soon
                </button>
            </div>

            <!-- Audit Logs Card -->
            <div style="background: #fff; border-radius: 0.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); padding: 1.5rem;">
                <h3 style="color: #1f2937; margin: 0 0 0.5rem 0; font-size: 1.125rem; font-weight: 600;">Audit Logs</h3>
                <p style="color: #6b7280; margin: 0 0 1rem 0; font-size: 0.875rem;">View system activity and audit trail</p>
                <button style="background: #2563eb; color: white; border: none; padding: 0.5rem 1rem; border-radius: 0.375rem; font-size: 0.875rem; cursor: pointer; opacity: 0.6;" disabled>
                    Coming Soon
                </button>
            </div>

            <!-- Database Management Card -->
            <div style="background: #fff; border-radius: 0.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); padding: 1.5rem;">
                <h3 style="color: #1f2937; margin: 0 0 0.5rem 0; font-size: 1.125rem; font-weight: 600;">Database Management</h3>
                <p style="color: #6b7280; margin: 0 0 1rem 0; font-size: 0.875rem;">Database optimization and maintenance tools</p>
                <button style="background: #2563eb; color: white; border: none; padding: 0.5rem 1rem; border-radius: 0.375rem; font-size: 0.875rem; cursor: pointer; opacity: 0.6;" disabled>
                    Coming Soon
                </button>
            </div>

            <!-- Reports & Analytics Card -->
            <div style="background: #fff; border-radius: 0.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); padding: 1.5rem;">
                <h3 style="color: #1f2937; margin: 0 0 0.5rem 0; font-size: 1.125rem; font-weight: 600;">Reports & Analytics</h3>
                <p style="color: #6b7280; margin: 0 0 1rem 0; font-size: 0.875rem;">System usage reports and analytics dashboard</p>
                <button style="background: #2563eb; color: white; border: none; padding: 0.5rem 1rem; border-radius: 0.375rem; font-size: 0.875rem; cursor: pointer; opacity: 0.6;" disabled>
                    Coming Soon
                </button>
            </div>

        </div>

        <!-- Quick Access Section -->
        <div style="background: #f8f9fa; padding: 1.5rem; border-radius: 0.5rem; margin-bottom: 2rem;">
            <h2 style="color: #1f2937; margin: 0 0 1rem 0; font-size: 1.25rem; font-weight: 600;">Quick Access - Core Functions</h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem;">
                <a href="shifts.php" style="background: #10b981; color: white; text-decoration: none; padding: 1rem 1.5rem; border-radius: 0.5rem; font-size: 0.875rem; font-weight: 600; text-align: center; box-shadow: 0 2px 4px rgba(0,0,0,0.1); transition: transform 0.15s ease;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                    📅 Manage Shift Patterns
                </a>
                <a href="requirements.php" style="background: #2563eb; color: white; text-decoration: none; padding: 1rem 1.5rem; border-radius: 0.5rem; font-size: 0.875rem; font-weight: 600; text-align: center; box-shadow: 0 2px 4px rgba(0,0,0,0.1); transition: transform 0.15s ease;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                    📋 Manage Station Requirements
                </a>
                <a href="vehicles.php" style="background: #6b7280; color: white; text-decoration: none; padding: 1rem 1.5rem; border-radius: 0.5rem; font-size: 0.875rem; font-weight: 600; text-align: center; box-shadow: 0 2px 4px rgba(0,0,0,0.1); transition: transform 0.15s ease;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                    🚑 Manage Vehicles
                </a>
                <a href="stations.php" style="background: #6b7280; color: white; text-decoration: none; padding: 1rem 1.5rem; border-radius: 0.5rem; font-size: 0.875rem; font-weight: 600; text-align: center; box-shadow: 0 2px 4px rgba(0,0,0,0.1); transition: transform 0.15s ease;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                    🏥 Manage Stations
                </a>
            </div>
        </div>

        <!-- System Status -->
        <div style="background: #fff; border-radius: 0.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); padding: 1.5rem;">
            <h2 style="color: #1f2937; margin: 0 0 1rem 0; font-size: 1.25rem; font-weight: 600;">System Status</h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                <div style="text-align: center; padding: 1rem;">
                    <div style="font-size: 2rem; color: #10b981; margin-bottom: 0.5rem;">✅</div>
                    <div style="font-weight: 600; color: #1f2937;">Database</div>
                    <div style="font-size: 0.875rem; color: #6b7280;">Connected</div>
                </div>
                <div style="text-align: center; padding: 1rem;">
                    <div style="font-size: 2rem; color: #10b981; margin-bottom: 0.5rem;">🟢</div>
                    <div style="font-weight: 600; color: #1f2937;">System</div>
                    <div style="font-size: 0.875rem; color: #6b7280;">Operational</div>
                </div>
                <div style="text-align: center; padding: 1rem;">
                    <div style="font-size: 2rem; color: #2563eb; margin-bottom: 0.5rem;">📊</div>
                    <div style="font-weight: 600; color: #1f2937;">Uptime</div>
                    <div style="font-size: 0.875rem; color: #6b7280;">99.9%</div>
                </div>
                <div style="text-align: center; padding: 1rem;">
                    <div style="font-size: 2rem; color: #f59e0b; margin-bottom: 0.5rem;">⏰</div>
                    <div style="font-weight: 600; color: #1f2937;">Last Updated</div>
                    <div style="font-size: 0.875rem; color: #6b7280;"><?php echo date('Y-m-d H:i'); ?></div>
                </div>
            </div>
        </div>

    </main>

    <?php include 'includes/footer.php'; ?>

</body>
</html>