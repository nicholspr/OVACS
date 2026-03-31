<?php
/**
 * PHP Configuration Info
 * This will show you where your php.ini file is located
 */
?>
<!DOCTYPE html>
<html>
<head>
    <title>PHP Configuration Info</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .important { background: #fffacd; padding: 10px; border-left: 4px solid #ffa500; margin: 10px 0; }
        table { border-collapse: collapse; width: 100%; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h1>🔧 PHP Configuration Information</h1>
    
    <div class="important">
        <strong>📍 PHP Configuration File (php.ini) Location:</strong><br>
        <code><?php echo php_ini_loaded_file() ?: 'No php.ini file loaded'; ?></code>
    </div>
    
    <div class="important">
        <strong>📂 Additional .ini files parsed:</strong><br>
        <code><?php echo php_ini_scanned_files() ?: 'None'; ?></code>
    </div>
    
    <h2>📊 Key Settings</h2>
    <table>
        <tr><th>Setting</th><th>Value</th></tr>
        <tr><td>PHP Version</td><td><?php echo PHP_VERSION; ?></td></tr>
        <tr><td>PHP SAPI</td><td><?php echo php_sapi_name(); ?></td></tr>
        <tr><td>Configuration File Path</td><td><?php echo PHP_CONFIG_FILE_PATH; ?></td></tr>
        <tr><td>Configuration File Scan Dir</td><td><?php echo PHP_CONFIG_FILE_SCAN_DIR ?: 'Not set'; ?></td></tr>
        <tr><td>PDO MySQL Extension</td><td><?php echo extension_loaded('pdo_mysql') ? '✅ Loaded' : '❌ Not Loaded'; ?></td></tr>
        <tr><td>MySQL Extension</td><td><?php echo extension_loaded('mysql') ? '✅ Loaded' : '❌ Not Loaded'; ?></td></tr>
        <tr><td>MySQLi Extension</td><td><?php echo extension_loaded('mysqli') ? '✅ Loaded' : '❌ Not Loaded'; ?></td></tr>
    </table>
    
    <?php if (!extension_loaded('pdo_mysql')): ?>
    <div class="important">
        <strong>⚠️ PDO MySQL Extension Not Loaded</strong><br>
        To enable it, edit your php.ini file and uncomment or add:<br>
        <code>extension=pdo_mysql</code><br>
        Then restart IIS.
    </div>
    <?php endif; ?>
    
    <h2>🛠️ How to Edit php.ini</h2>
    <ol>
        <li>Navigate to the php.ini file location shown above</li>
        <li>Make a backup copy first: <code>php.ini.backup</code></li>
        <li>Open php.ini in a text editor (as Administrator)</li>
        <li>Find the line <code>;extension=pdo_mysql</code></li>
        <li>Remove the semicolon (;) to uncomment it: <code>extension=pdo_mysql</code></li>
        <li>Save the file</li>
        <li>Restart IIS: Open Command Prompt as Administrator and run <code>iisreset</code></li>
        <li>Refresh this page to verify the change</li>
    </ol>
    
    <h2>📋 Full PHP Info</h2>
    <p><strong>Warning:</strong> This contains sensitive information. Remove this file after troubleshooting.</p>
    <details>
        <summary>Click to show full phpinfo() output</summary>
        <div style="margin-top: 20px;">
            <?php phpinfo(); ?>
        </div>
    </details>
    
    <p><a href="database-test.php">🔗 Test Database Connection</a> | <a href="index.php">← Back to OVACS</a></p>
</body>
</html>