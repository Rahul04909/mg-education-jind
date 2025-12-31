<?php
require_once __DIR__ . '/../database/db-config.php';

function updateAdmissionSchema() {
    $conn = getDbConnection();
    
    // Check if column exists
    $check = $conn->query("SHOW COLUMNS FROM admissions LIKE 'admission_mode'");
    if ($check->num_rows == 0) {
        $sql = "ALTER TABLE admissions ADD COLUMN admission_mode VARCHAR(20) DEFAULT 'Online' AFTER category";
        if ($conn->query($sql) === TRUE) {
             if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
                 echo "Success: 'admission_mode' column added.<br>";
             }
        } else {
            echo "Error updating schema: " . $conn->error . "<br>";
        }
    } else {
        if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
            echo "Info: 'admission_mode' column already exists.<br>";
        }
    }
    
    $conn->close();
}

updateAdmissionSchema();
?>
