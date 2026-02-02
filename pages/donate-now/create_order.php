<?php
require_once __DIR__ . '/../../database/db-config.php';
require_once __DIR__ . '/../../vendor/autoload.php';
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

try {
    $api = new Api($key_id, $key_secret);
    
    // Get Donation Amount
    $amount = isset($_POST['amount']) ? floatval($_POST['amount']) : 0;
    
    if ($amount < 1) { // Minimum 1 Rupee
        echo json_encode(['status' => 'error', 'message' => 'Invalid Donation Amount']);
        exit;
    }
    
    $orderData = [
        'receipt'         => 'don_' . time(),
        'amount'          => $amount * 100, // Amount in paise
        'currency'        => 'INR',
        'payment_capture' => 1 // Auto capture
    ];

    $razorpayOrder = $api->order->create($orderData);
    echo json_encode([
        'status' => 'success',
        'order_id' => $razorpayOrder['id'],
        'amount' => $amount,
        'key_id' => $key_id,
        'currency' => 'INR',
        'name' => 'MG Education & Social Development',
        'description' => 'Donation',
        'image' => 'https://svsws.in/assets/img/logo.png', // Replace with actual logo URL if available
        'prefill' => [
            'name' => '',
            'email' => '',
            'contact' => ''
        ],
        'theme' => [
            'color' => '#3399cc'
        ]
    ]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

$conn->close();
?>
