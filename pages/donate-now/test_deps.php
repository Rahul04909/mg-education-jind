<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>Environment Diagnostic</h1>";

// 1. Check PHP Version
echo "<h2>PHP Version</h2>";
echo "PHP version: " . phpversion() . "<br>";

// 2. Check Extensions
echo "<h2>Extensions</h2>";
$required_extensions = ['intl', 'mysqli', 'curl', 'json', 'gd'];
foreach ($required_extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "<span style='color:green'>✔ Extension '$ext' is loaded.</span><br>";
    } else {
        echo "<span style='color:red'>✖ Extension '$ext' is NOT loaded.</span><br>";
    }
}

// 3. Check Vendor Autoload
echo "<h2>Vendor Autoload</h2>";
$autoload_path = __DIR__ . '/../../vendor/autoload.php';
if (file_exists($autoload_path)) {
    echo "<span style='color:green'>✔ Autoload file found at: $autoload_path</span><br>";
    require_once $autoload_path;
} else {
    echo "<span style='color:red'>✖ Autoload file NOT found at: $autoload_path</span><br>";
}

// 4. Check Classes
echo "<h2>Classes</h2>";
$classes = [
    'Dompdf\Dompdf',
    'Razorpay\Api\Api',
    'NumberFormatter'
];

foreach ($classes as $class) {
    if (class_exists($class)) {
        echo "<span style='color:green'>✔ Class '$class' exists.</span><br>";
    } else {
        echo "<span style='color:red'>✖ Class '$class' does NOT exist.</span><br>";
    }
}

// 5. Check Database
echo "<h2>Database Connection</h2>";
require_once __DIR__ . '/../../database/db-config.php';
try {
    $conn = getDbConnection();
    if ($conn->connect_error) {
         echo "<span style='color:red'>✖ Database connection failed: " . $conn->connect_error . "</span><br>";
    } else {
        echo "<span style='color:green'>✔ Database connection successful.</span><br>";
        $conn->close();
    }
} catch (Exception $e) {
    echo "<span style='color:red'>✖ Database connection exception: " . $e->getMessage() . "</span><br>";
}

?>
