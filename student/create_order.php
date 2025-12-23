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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid Request']);
    exit;
}

$amount = isset($_POST['amount']) ? floatval($_POST['amount']) : 0;
if ($amount <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid Amount']);
    exit;
}

$conn = getDbConnection();

// Fetch Razorpay Keys
$sql = "SELECT * FROM razorpay_settings WHERE is_active = 1 LIMIT 1";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    echo json_encode(['status' => 'error', 'message' => 'Payment gateway not configured']);
    exit;
}

$keys = $result->fetch_assoc();
$key_id = $keys['razorpay_key_id'];
$key_secret = $keys['razorpay_key_secret'];

$api = new Api($key_id, $key_secret);

$orderData = [
    'receipt'         => 'fee_' . $_SESSION['enrollment_no'] . '_' . time(),
    'amount'          => $amount * 100, // Amount in paise
    'currency'        => 'INR',
    'payment_capture' => 1
];

try {
    $razorpayOrder = $api->order->create($orderData);
    echo json_encode([
        'status' => 'success',
        'order_id' => $razorpayOrder['id'],
        'amount' => $amount * 100,
        'key_id' => $key_id
    ]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

$conn->close();
?>
