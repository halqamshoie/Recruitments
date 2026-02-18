<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>System Diagnostic</h1>";

// 1. Check Database Connection & Write
echo "<h2>1. Database Check</h2>";
try {
    require_once __DIR__ . '/../src/Models/Database.php';
    $pdo = Database::connect();
    echo "<span style='color:green'>Database Connection: OK</span><br>";
    
    // Check if audit_logs exists
    $stmt = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='audit_logs'");
    if ($stmt->fetch()) {
        echo "<span style='color:green'>Table 'audit_logs': Exists</span><br>";
        
        // Try Writing
        try {
            $stmt = $pdo->prepare("INSERT INTO audit_logs (action, details, ip_address) VALUES ('debug', 'test', '127.0.0.1')");
            $stmt->execute();
            echo "<span style='color:green'>Write Permission: OK (Successfully inserted into audit_logs)</span><br>";
        } catch (Exception $e) {
            echo "<span style='color:red'>Write Permission: FAILED</span><br>";
            echo "Error: " . $e->getMessage() . "<br>";
        }
    } else {
        echo "<span style='color:red'>Table 'audit_logs': MISSING</span><br>";
    }

} catch (Exception $e) {
    echo "<span style='color:red'>Database Error: " . $e->getMessage() . "</span><br>";
}

// 2. Check Vendor / Dependencies
echo "<h2>2. Dependencies Check</h2>";
$vendorPath = __DIR__ . '/../vendor/autoload.php';
if (file_exists($vendorPath)) {
    echo "<span style='color:green'>Vendor Autoload: Found at $vendorPath</span><br>";
    try {
        require_once $vendorPath;
        echo "<span style='color:green'>Vendor Autoload: Loaded Successfully</span><br>";
    } catch (Exception $e) {
        echo "<span style='color:red'>Vendor Autoload: Failed to Load (" . $e->getMessage() . ")</span><br>";
    }
} else {
    echo "<span style='color:red'>Vendor Autoload: MISSING at $vendorPath</span><br>";
    echo "Hint: You need to run 'composer install' on the server.<br>";
}

// 3. PHP Extensions
echo "<h2>3. PHP Extensions</h2>";
$extensions = ['sqlite3', 'mbstring', 'xml', 'curl'];
foreach ($extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "<span style='color:green'>Ext '$ext': OK</span><br>";
    } else {
        echo "<span style='color:red'>Ext '$ext': MISSING</span><br>";
    }
}
