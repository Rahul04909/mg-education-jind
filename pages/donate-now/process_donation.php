<?php
require_once __DIR__ . '/../../database/db-config.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use Razorpay\Api\Api;

header('Content-Type: application/json');

$conn = getDbConnection();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    
    // Fetch Keys
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
    
    $success = true;
    try {
        $attributes = [
            'razorpay_order_id' => $order_id,
            'razorpay_payment_id' => $payment_id,
            'razorpay_signature' => $signature
        ];
        $api->utility->verifyPaymentSignature($attributes);
    } catch(Exception $e) {
        $success = false;
        echo json_encode(['status' => 'error', 'message' => 'Payment Verification Failed: ' . $e->getMessage()]); exit;
    }

    // Inputs
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $mobile = mysqli_real_escape_string($conn, $_POST['mobile']);
    $pan_card = isset($_POST['pan_card']) ? mysqli_real_escape_string($conn, $_POST['pan_card']) : '';
    $address = isset($_POST['address']) ? mysqli_real_escape_string($conn, $_POST['address']) : '';
    $purpose = isset($_POST['purpose']) ? mysqli_real_escape_string($conn, $_POST['purpose']) : 'General Donation';
    $amount = floatval($_POST['amount']);

    // Generate Receipt No
    $receipt_no = "DON" . date("Ymd") . rand(1000, 9999);

    $sql = "INSERT INTO donations (
        full_name, email, mobile, pan_card, address, amount, purpose, 
        payment_id, order_id, status, receipt_no
    ) VALUES (
        '$full_name', '$email', '$mobile', '$pan_card', '$address', $amount, '$purpose',
        '$payment_id', '$order_id', 'success', '$receipt_no'
    )";
    
    if ($conn->query($sql) === TRUE) {
        $donation_id = $conn->insert_id;
        echo json_encode(['status' => 'success', 'donation_id' => $donation_id, 'receipt_no' => $receipt_no]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database Error: ' . $conn->error]);
    }
}
$conn->close();
?>
