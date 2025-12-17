<?php
require_once __DIR__ . '/../database/db-config.php';
require_once __DIR__ . '/../vendor/autoload.php';
use Razorpay\Api\Api;

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid Request']);
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

// Get Course Fee from POST (Ideally fetch from DB using course_id for security)
$course_id = isset($_POST['course_id']) ? intval($_POST['course_id']) : 0;

if ($course_id == 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid Course']);
    exit;
}

$course_sql = "SELECT fees FROM courses WHERE id = $course_id";
$c_res = $conn->query($course_sql);
if ($c_res->num_rows == 0) {
    echo json_encode(['status' => 'error', 'message' => 'Course not found']);
    exit;
}

$course = $c_res->fetch_assoc();
$fees = json_decode($course['fees'], true);
$amount = floatval($fees['amount']);

if ($amount <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid Fee Amount']);
    exit;
}

$orderData = [
    'receipt'         => 'rcptid_' . time(),
    'amount'          => $amount * 100, // Amount in paise
    'currency'        => 'INR',
    'payment_capture' => 1 // Auto capture
];

try {
    $razorpayOrder = $api->order->create($orderData);
    echo json_encode([
        'status' => 'success',
        'order_id' => $razorpayOrder['id'],
        'amount' => $amount,
        'key_id' => $key_id
    ]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

$conn->close();
?>
