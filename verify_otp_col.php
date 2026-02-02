<?php
require_once 'database/db-config.php';
$conn = getDbConnection();
$res = $conn->query("SHOW COLUMNS FROM internship_enrollments LIKE 'otp'");
if($res->num_rows > 0) {
    echo "OTP Column Exists";
} else {
    echo "OTP Column MISSING";
    // Try adding it again if missing
    $conn->query("ALTER TABLE internship_enrollments ADD COLUMN otp VARCHAR(10) DEFAULT NULL AFTER password, ADD COLUMN otp_expiry DATETIME DEFAULT NULL AFTER otp");
    echo " - Attempted to Add";
}
?>
