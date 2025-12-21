<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'database/db-config.php';

$conn = getDbConnection();
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "Database: " . $conn->host_info . "\n";

echo "\n--- Schema ---\n";
$desc = $conn->query("DESCRIBE admissions");
if ($desc) {
    while($row = $desc->fetch_assoc()) {
        echo $row['Field'] . " | " . $row['Type'] . " | " . $row['Null'] . "\n";
    }
} else {
    echo "Error describing table: " . $conn->error . "\n";
}

echo "\n--- Data ---\n";
$sql = "SELECT * FROM admissions";
$result = $conn->query($sql);
if ($result) {
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            echo "Found Student: " . $row['enrollment_no'] . " | Payment: " . $row['payment_status'] . "\n";
        }
    } else {
        echo "No rows found in admissions table.\n";
    }
} else {
    echo "Error fetching data: " . $conn->error . "\n";
}
$conn->close();
?>
