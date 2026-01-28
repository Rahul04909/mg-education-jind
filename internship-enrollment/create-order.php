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

// Get Internship Fee from POST
// Using 'course_id' parameter name to match frontend generic naming or switch to 'internship_id' if I change frontend.
// Let's use 'internship_id' in backend for clarity, but frontend generic form might send 'course_id' if copied directly.
// I will ensure frontend sends 'internship_id'.

$internship_id = isset($_POST['internship_id']) ? intval($_POST['internship_id']) : 0;

if ($internship_id == 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid Internship']);
    exit;
}

$i_sql = "SELECT fees FROM internships WHERE id = $internship_id";
$i_res = $conn->query($i_sql);
if ($i_res->num_rows == 0) {
    echo json_encode(['status' => 'error', 'message' => 'Internship not found']);
    exit;
}

$internship = $i_res->fetch_assoc();
$fees = json_decode($internship['fees'], true);
$amount = isset($fees['amount']) ? floatval($fees['amount']) : 0;

if ($amount <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Free Internship or Invalid Fee Amount']);
    exit;
}

$orderData = [
    'receipt'         => 'int_rcpt_' . time(),
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
