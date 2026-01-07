<?php
require_once __DIR__ . '/../database/db-config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $conn = getDbConnection();

    $full_name = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $purpose = trim($_POST['purpose'] ?? '');
    $message = trim($_POST['message'] ?? '');

    // Basic Validation
    if (empty($full_name) || empty($phone) || empty($email) || empty($purpose)) {
        header("Location: ../index.php?status=error&message=Please fill all required fields");
        exit();
    }

    $sql = "INSERT INTO donation_enquiries (full_name, phone, email, purpose, message) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    
    if ($stmt) {
        $stmt->bind_param("sssss", $full_name, $phone, $email, $purpose, $message);
        
        if ($stmt->execute()) {
            header("Location: ../index.php?status=success&message=Thank you for your enquiry. We will contact you soon.");
        } else {
            header("Location: ../index.php?status=error&message=Database error: " . $stmt->error);
        }
        $stmt->close();
    } else {
        header("Location: ../index.php?status=error&message=System error: " . $conn->error);
    }
    
    $conn->close();
} else {
    header("Location: ../index.php");
    exit();
}
?>
