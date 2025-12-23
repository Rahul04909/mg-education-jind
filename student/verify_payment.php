<?php
session_start();
require_once __DIR__ . '/../database/db-config.php';
require_once __DIR__ . '/../vendor/autoload.php';
use Razorpay\Api\Api;

header('Content-Type: application/json');

if (!isset($_SESSION['student_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$conn = getDbConnection();

// Fetch Razorpay Keys
$k_sql = "SELECT * FROM razorpay_settings WHERE is_active = 1 LIMIT 1";
$k_res = $conn->query($k_sql);
if ($k_res->num_rows == 0) {
    echo json_encode(['status' => 'error', 'message' => 'Payment Gateway Error']); exit;
}
$keys = $k_res->fetch_assoc();
$api = new Api($keys['razorpay_key_id'], $keys['razorpay_key_secret']);

$payment_id = $_POST['razorpay_payment_id'];
$order_id = $_POST['razorpay_order_id'];
$signature = $_POST['razorpay_signature'];
$amount = floatval($_POST['amount']);
$enrollment_no = $_SESSION['enrollment_no'];

try {
    $attributes = [
        'razorpay_order_id' => $order_id,
        'razorpay_payment_id' => $payment_id,
        'razorpay_signature' => $signature
    ];
    $api->utility->verifyPaymentSignature($attributes);
} catch(Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Payment Verification Failed: ' . $e->getMessage()]); 
    exit;
}

// Log Transaction
$txn_sql = "INSERT INTO student_transactions (enrollment_no, transaction_id, amount, payment_mode, status, remarks) 
            VALUES ('$enrollment_no', '$payment_id', $amount, 'Razorpay', 'success', 'Student Online Payment')";

if ($conn->query($txn_sql) === TRUE) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Database Error: ' . $conn->error]);
}

$conn->close();
?>
