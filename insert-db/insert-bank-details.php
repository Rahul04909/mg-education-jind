<?php
// Enable Error Reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once '../database/db-config.php';

if (!isset($_SESSION['center_id'])) {
    header("Location: ../center/login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $conn = getDbConnection();
    $center_id = $_SESSION['center_id'];
    
    $bank_name = $_POST['bank_name'];
    $account_holder = $_POST['account_holder'];
    $account_no = $_POST['account_no'];
    $ifsc = $_POST['ifsc'];
    $branch = $_POST['branch'];
    $upi_id = $_POST['upi_id'] ?? '';

    // Handle File Uploads
    $upload_dir = '../assets/uploads/center-qr/';
    if (!file_exists($upload_dir)) {
        if (!mkdir($upload_dir, 0777, true)) {
            die("Failed to create upload directory");
        }
    }
    
    // Function to handle upload
    function handleUpload($fileInputName, $existingPath, $upload_dir) {
        if (isset($_FILES[$fileInputName]) && $_FILES[$fileInputName]['error'] == 0) {
            $ext = pathinfo($_FILES[$fileInputName]['name'], PATHINFO_EXTENSION);
            $new_name = uniqid('qr_') . '.' . $ext;
            $target_file = $upload_dir . $new_name;
            
            if (move_uploaded_file($_FILES[$fileInputName]['tmp_name'], $target_file)) {
                return 'assets/uploads/center-qr/' . $new_name;
            } else {
                // Log or handle upload error
                error_log("Failed to move uploaded file: " . $_FILES[$fileInputName]['name']);
            }
        }
        return $existingPath;
    }

    // Get existing paths if any
    $qr_1 = ''; 
    $qr_2 = '';
    $check_sql = "SELECT qr_1, qr_2 FROM center_bank_details WHERE center_id = $center_id";
    $result = $conn->query($check_sql);
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $qr_1 = $row['qr_1'];
        $qr_2 = $row['qr_2'];
    }

    // Process Uploads
    $qr_1 = handleUpload('qr_1', $qr_1, $upload_dir);
    $qr_2 = handleUpload('qr_2', $qr_2, $upload_dir);

    // Insert or Update
    // Check if record exists
    if ($result && $result->num_rows > 0) {
        // Update
        $stmt = $conn->prepare("UPDATE center_bank_details SET bank_name=?, account_holder=?, account_no=?, ifsc=?, branch=?, upi_id=?, qr_1=?, qr_2=? WHERE center_id=?");
        $stmt->bind_param("ssssssssi", $bank_name, $account_holder, $account_no, $ifsc, $branch, $upi_id, $qr_1, $qr_2, $center_id);
    } else {
        // Insert
        $stmt = $conn->prepare("INSERT INTO center_bank_details (center_id, bank_name, account_holder, account_no, ifsc, branch, upi_id, qr_1, qr_2) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issssssss", $center_id, $bank_name, $account_holder, $account_no, $ifsc, $branch, $upi_id, $qr_1, $qr_2);
    }

    if ($stmt->execute()) {
        header("Location: ../center/bank-details.php?status=success&msg=Bank details saved successfully");
    } else {
        header("Location: ../center/bank-details.php?status=error&msg=Database error: " . $stmt->error);
    }

    $conn->close();
} else {
    // Fallback for non-POST requests to prevent white screen
    header("Location: ../center/bank-details.php");
    exit;
}
?>
