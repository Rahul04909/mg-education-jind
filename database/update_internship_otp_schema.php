<?php
require_once __DIR__ . '/db-config.php';

$conn = getDbConnection();
$show_output = (basename(__FILE__) == basename($_SERVER["SCRIPT_FILENAME"]));

// Check if otp column exists
$check_sql = "SHOW COLUMNS FROM internship_enrollments LIKE 'otp'";
$result = $conn->query($check_sql);

if ($result->num_rows == 0) {
    if ($show_output) echo "Column 'otp' not found. Adding otp and otp_expiry...<br>";
    $sql = "ALTER TABLE internship_enrollments 
            ADD COLUMN otp VARCHAR(10) DEFAULT NULL AFTER password,
            ADD COLUMN otp_expiry DATETIME DEFAULT NULL AFTER otp";
            
    if ($conn->query($sql) === TRUE) {
        if ($show_output) echo "Columns added successfully.<br>";
    } else {
        if ($show_output) echo "Error adding columns: " . $conn->error . "<br>";
    }
} else {
    if ($show_output) echo "Columns already exist.<br>";
}

$conn->close();
?>
