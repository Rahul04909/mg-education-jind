<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/db-config.php';

$conn = getDbConnection();
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<h2>Database Schema Update</h2>";

// 1. Add center_code
$sql = "SHOW COLUMNS FROM centers LIKE 'center_code'";
$result = $conn->query($sql);
if ($result->num_rows == 0) {
    echo "Adding 'center_code' column... ";
    if ($conn->query("ALTER TABLE centers ADD COLUMN center_code VARCHAR(50) UNIQUE AFTER id") === TRUE) {
        echo "<span style='color:green'>Success</span><br>";
    } else {
        echo "<span style='color:red'>Error: " . $conn->error . "</span><br>";
    }
} else {
    echo "'center_code' column already exists.<br>";
}

// 2. Add password
$sql = "SHOW COLUMNS FROM centers LIKE 'password'";
$result = $conn->query($sql);
if ($result->num_rows == 0) {
    echo "Adding 'password' column... ";
    if ($conn->query("ALTER TABLE centers ADD COLUMN password VARCHAR(255) AFTER email") === TRUE) {
         echo "<span style='color:green'>Success</span><br>";
    } else {
        echo "<span style='color:red'>Error: " . $conn->error . "</span><br>";
    }
} else {
    echo "'password' column already exists.<br>";
}

echo "<br><strong>Update Complete. You can now delete this file.</strong>";
$conn->close();
?>
