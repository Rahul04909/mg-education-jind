<?php
// Database Schema Update for Online Exam Login
require_once __DIR__ . '/db-config.php';

$conn = getDbConnection();

// Add 'otp' and 'otp_expiry' to 'admissions' table
$sql_check = "SHOW COLUMNS FROM admissions LIKE 'otp'";
$result = $conn->query($sql_check);

if ($result->num_rows == 0) {
    // Columns don't exist, add them
    $sql_add = "ALTER TABLE admissions 
                ADD COLUMN otp VARCHAR(6) NULL AFTER password,
                ADD COLUMN otp_expiry DATETIME NULL AFTER otp";
    
    if ($conn->query($sql_add) === TRUE) {
        echo "Successfully added 'otp' and 'otp_expiry' columns to 'admissions' table.\n";
    } else {
        echo "Error adding columns: " . $conn->error . "\n";
    }
} else {
    echo "Columns 'otp' and 'otp_expiry' already exist.\n";
}

$conn->close();
?>
