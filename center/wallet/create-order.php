<?php
session_start();
require_once __DIR__ . '/../../database/db-config.php';
require_once __DIR__ . '/../../vendor/autoload.php'; // Ensure this path matches online-admisson logic
use Razorpay\Api\Api;

header('Content-Type: application/json');

// Check Auth
if (!isset($_SESSION['center_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid Request']);
    exit;
}

$conn = getDbConnection();
$center_id = $_SESSION['center_id'];

// 1. Fetch Razorpay Keys from DB
$sql = "SELECT * FROM razorpay_settings WHERE is_active = 1 LIMIT 1";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    echo json_encode(['status' => 'error', 'message' => 'Payment gateway not configured']);
    exit;
}

$keys = $result->fetch_assoc();
$key_id = $keys['razorpay_key_id'];
$key_secret = $keys['razorpay_key_secret'];

// 2. Calculate Payable Amount Logic
// Security: Fetch Royalty % from SERVER, do not trust client
$c_res = $conn->query("SELECT royalty_percentage, center_name, email, mobile FROM centers WHERE id = $center_id");
if ($c_res->num_rows == 0) {
    echo json_encode(['status' => 'error', 'message' => 'Center not found']);
    exit;
}
$center = $c_res->fetch_assoc();
$royalty = floatval($center['royalty_percentage']);

$amount_to_add = isset($_POST['amount']) ? floatval($_POST['amount']) : 0; // Amount user wants to add to wallet

if ($amount_to_add <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid Amount']);
    exit;
}

// Payable = AmountToAdd * Royalty% / 100
$payable_amount = ($amount_to_add * $royalty) / 100;

if ($payable_amount <= 0) {
     // If royalty is 0, they pay 0? Need to handle. Assuming > 0 
     // Or perhaps min Rs 1?
     echo json_encode(['status' => 'error', 'message' => 'Payable amount is zero or invalid']);
     exit;
}

// 3. Create Order
$api = new Api($key_id, $key_secret);

$orderData = [
    'receipt'         => 'wlt_' . $center_id . '_' . time(),
    'amount'          => intval($payable_amount * 100), // Amount in paise (integer)
    'currency'        => 'INR',
    'payment_capture' => 1,
    'notes'           => [
        'center_id' => $center_id,
        'wallet_credit' => $amount_to_add,
        'royalty_pct' => $royalty
    ]
];

try {
    $razorpayOrder = $api->order->create($orderData);
    
    echo json_encode([
        'status' => 'success',
        'key_id' => $key_id,
        'order_id' => $razorpayOrder['id'],
        'amount' => $payable_amount,
        'wallet_credit' => $amount_to_add, // Send back for UI confirmation/Verify
        'prefill' => [
            'name' => $center['center_name'],
            'email' => $center['email'],
            'contact' => $center['mobile']
        ]
    ]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

$conn->close();
?>
