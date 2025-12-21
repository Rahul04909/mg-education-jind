<?php
session_start();
require_once '../database/db-config.php';

if (!isset($_SESSION['center_id'])) {
    header("Location: ../center/login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $conn = getDbConnection();
    
    $center_id = $_SESSION['center_id'];
    $current = $_POST['current_password'];
    $new = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];

    // Validation
    if ($new !== $confirm) {
        header("Location: ../center/profile.php?status=error&msg=New passwords do not match");
        exit;
    }

    if (strlen($new) < 6) {
        header("Location: ../center/profile.php?status=error&msg=Password must be at least 6 characters");
        exit;
    }

    // Verify current password
    $sql = "SELECT password FROM centers WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $center_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $center = $result->fetch_assoc();

    if (!password_verify($current, $center['password'])) {
        header("Location: ../center/profile.php?status=error&msg=Incorrect current password");
        exit;
    }

    // Hash new password
    $hashed_password = password_hash($new, PASSWORD_DEFAULT);

    // Update password
    $update_sql = "UPDATE centers SET password = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("si", $hashed_password, $center_id);
    
    if ($update_stmt->execute()) {
        header("Location: ../center/profile.php?status=success&msg=Password updated successfully");
    } else {
        header("Location: ../center/profile.php?status=error&msg=Database error");
    }

    $conn->close();
}
?>
