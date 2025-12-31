<?php
require_once __DIR__ . '/db-config.php';

function updateAdmissionsSchema() {
    $conn = getDbConnection();
    
    // Check if column exists
    $check_col = $conn->query("SHOW COLUMNS FROM admissions LIKE 'session_id'");
    if ($check_col->num_rows == 0) {
        $sql = "ALTER TABLE admissions ADD COLUMN session_id INT DEFAULT NULL";
        if ($conn->query($sql) === TRUE) {
             if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
                 echo "Success: 'session_id' column added to admissions table.<br>";
             }
        } else {
            echo "Error updating schema: " . $conn->error . "<br>";
        }
    } else {
         if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
             // echo "Info: 'session_id' column already exists.<br>";
         }
    }
    
    $conn->close();
}

// Enable error reporting only when running directly
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

updateAdmissionsSchema();
?>
