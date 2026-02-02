<?php
require_once __DIR__ . '/db-config.php';

$conn = getDbConnection();
$show_output = (basename(__FILE__) == basename($_SERVER["SCRIPT_FILENAME"]));

// Check if password column exists
$check_sql = "SHOW COLUMNS FROM internship_enrollments LIKE 'password'";
$result = $conn->query($check_sql);

if ($result->num_rows == 0) {
    if ($show_output) echo "Column 'password' not found. Adding it...<br>";
    $sql = "ALTER TABLE internship_enrollments ADD COLUMN password VARCHAR(255) DEFAULT NULL AFTER email";
    if ($conn->query($sql) === TRUE) {
        if ($show_output) echo "Column 'password' added successfully.<br>";
    } else {
        if ($show_output) echo "Error adding column: " . $conn->error . "<br>";
    }
} else {
    if ($show_output) echo "Column 'password' already exists.<br>";
}

$conn->close();
?>
