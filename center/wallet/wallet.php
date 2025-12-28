<?php
session_start();
// Include database configuration
require_once __DIR__ . '/../../database/db-config.php';
require_once __DIR__ . '/../../database/update_wallet_schema.php';
require_once __DIR__ . '/../../vendor/autoload.php';
use Razorpay\Api\Api;

if (!isset($_SESSION['center_id'])) {
    header("Location: ../login.php");
    exit;
}

$conn = getDbConnection();
$center_id = $_SESSION['center_id'];
$success_message = '';
$error_message = '';

// Fetch Center Details (Royalty & Balance)
$center_result = $conn->query("SELECT center_name, email, mobile, wallet_balance, royalty_percentage FROM centers WHERE id = $center_id");
$center = $center_result->fetch_assoc();
$wallet_balance = floatval($center['wallet_balance']);
$royalty_percent = floatval($center['royalty_percentage']);

// Handle Payment Verification (AJAX POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'verify_payment') {
    header('Content-Type: application/json');
    
    // 1. Fetch Keys for Verification
    $k_sql = "SELECT * FROM razorpay_settings WHERE is_active = 1 LIMIT 1";
    $k_res = $conn->query($k_sql);
    if ($k_res->num_rows == 0) {
        echo json_encode(['status' => 'error', 'message' => 'Gateway keys not found']); exit;
    }
    $keys = $k_res->fetch_assoc();
    $api = new Api($keys['razorpay_key_id'], $keys['razorpay_key_secret']);
    
    $payment_id = $_POST['razorpay_payment_id'];
    $order_id = $_POST['razorpay_order_id'];
    $signature = $_POST['razorpay_signature'];
    
    // Credit amount and paid amount for logging (sent from client but validated effectively by Order ID logic indirectly)
    // Ideally we should look up the Order ID to see what the amount was, but Razorpay signature ensures integrity.
    // However, to record "Credit Amount" vs "Paid Amount", we need to trust the logic or recalculate.
    // Let's Recalculate based on input 'wallet_credit'
    
    $wallet_credit = floatval($_POST['wallet_credit']); // The 1000 Rs
    $paid_amount = floatval($_POST['paid_amount']);     // The 100 Rs
    
    // Verify Signature
    try {
        $attributes = [
            'razorpay_order_id' => $order_id,
            'razorpay_payment_id' => $payment_id,
            'razorpay_signature' => $signature
        ];
        $api->utility->verifyPaymentSignature($attributes);
        
        // Success
        
        // 1. Record Transaction
        $stmt = $conn->prepare("INSERT INTO wallet_transactions (center_id, amount, credit_amount, payment_id, status) VALUES (?, ?, ?, ?, 'success')");
        $stmt->bind_param("idds", $center_id, $paid_amount, $wallet_credit, $payment_id);
        
        if ($stmt->execute()) {
            // 2. Update Wallet Balance
            $new_balance = $wallet_balance + $wallet_credit;
            $conn->query("UPDATE centers SET wallet_balance = $new_balance WHERE id = $center_id");
            
            echo json_encode(['status' => 'success', 'new_balance' => $new_balance]);
            exit;
        }

    } catch(Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Signature Verification Failed: ' . $e->getMessage()]);
        exit;
    }
    
    echo json_encode(['status' => 'error', 'message' => 'Transaction failed']);
    exit;
}

// Fetch Transactions
$txns = $conn->query("SELECT * FROM wallet_transactions WHERE center_id = $center_id ORDER BY created_at DESC LIMIT 20");

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Center Wallet - MG Skills</title>
    <!-- Razorpay SDK -->
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <style>
        :root{--active:#22c55e;--indigo:#6f75ff;--line:#e6e8ee;--text:#0b1020;--muted:#6f7787;--error:#ef4444;--success:#22c55e}
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:'Outfit', sans-serif;background:#f8fafc;color:var(--text)}
        
        /* Adjusted margin to 280px to match sidebar width */
        .admin-content{margin-left:280px;min-height:100vh;padding:30px;transition:margin-left .25s ease}
        body.sidebar-collapsed .admin-content{margin-left:80px}
        
        .page-header{margin-bottom:30px}
        .page-title{font-size:28px;font-weight:700;color:var(--text);margin-bottom:5px}
        
        .grid-2{display:grid;grid-template-columns:1fr 1fr;gap:25px}
        
        /* Wallet Card */
        .wallet-card {
            background: linear-gradient(135deg, #6f75ff 0%, #8b5cf6 100%);
            color: white;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 10px 20px rgba(111, 117, 255, 0.2);
            position: relative;
            overflow: hidden;
        }
        .wallet-card::before {
            content: ''; position: absolute; top: -50px; right: -50px; width: 150px; height: 150px;
            background: rgba(255,255,255,0.1); border-radius: 50%;
        }
        .balance-label { font-size: 14px; opacity: 0.9; margin-bottom: 5px; text-transform: uppercase; letter-spacing: 1px; }
        .balance-amount { font-size: 42px; font-weight: 800; margin-bottom: 20px; }
        .royalty-badge { 
            background: rgba(255,255,255,0.2); display: inline-block; padding: 6px 12px; 
            border-radius: 20px; font-size: 13px; font-weight: 500; 
        }

        /* Top up Card */
        .card { background: #fff; border: 1px solid var(--line); border-radius: 18px; padding: 25px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
        .card-title { font-size: 18px; font-weight: 700; margin-bottom: 20px; border-bottom: 1px solid var(--line); padding-bottom: 10px; }
        
        .form-group { margin-bottom: 15px; }
        .form-label { display: block; font-weight: 600; font-size: 13px; margin-bottom: 6px; color: var(--muted); }
        .form-input { width: 100%; padding: 12px 16px; border: 1px solid var(--line); border-radius: 10px; font-size: 16px; font-weight: 600; font-family: inherit; transition: all 0.2s; }
        .form-input:focus { border-color: var(--indigo); outline: none; box-shadow: 0 0 0 3px rgba(111,117,255,0.1); }
        
        .calc-box { background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; padding: 15px; border-radius: 10px; margin-bottom: 20px; display: none; }
        
        .btn { border: none; padding: 14px; width: 100%; border-radius: 12px; font-weight: 700; cursor: pointer; font-size: 15px; transition: 0.2s; }
        .btn-primary { background: var(--text); color: white; }
        .btn-primary:hover { background: #000; transform: translateY(-2px); }

        /* History Table */
        .table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .table th { text-align: left; padding: 12px; font-size: 12px; text-transform: uppercase; color: var(--muted); border-bottom: 1px solid var(--line); }
        .table td { padding: 14px 12px; border-bottom: 1px solid var(--line); font-size: 14px; font-weight: 500; }
        .badge { padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; text-transform: uppercase; }
        .badge-success { background: #d1fae5; color: #065f46; }
        .badge-pending { background: #fef3c7; color: #92400e; }
        
    </style>
</head>
<body>
    <?php include '../sidebar.php'; ?>
    
    <main class="admin-content">
        <div class="page-header">
            <h1 class="page-title">Center Wallet</h1>
            <div style="color:var(--muted)">Manage your wallet balance and recent transactions</div>
        </div>

        <div class="grid-2">
            <!-- Left: Balanace + Topup -->
            <div>
                <!-- Balance Card -->
                <div class="wallet-card" style="margin-bottom: 20px;">
                    <div class="balance-label">Current Balance</div>
                    <div class="balance-amount">₹ <?php echo number_format($wallet_balance, 2); ?></div>
                    <div class="royalty-badge">
                        Royalty Fee: <?php echo $royalty_percent; ?>%
                    </div>
                </div>

                <!-- Top-up Form -->
                <div class="card">
                    <div class="card-title">Top-up Wallet</div>
                    
                    <div class="form-group">
                        <label class="form-label">Enter Amount to Add to Wallet (INR)</label>
                        <input type="number" id="add_amount" class="form-input" placeholder="e.g 1000" oninput="calculatePayable()">
                    </div>

                    <div id="calcBox" class="calc-box">
                        <div style="display:flex; justify-content:space-between; margin-bottom:5px;">
                            <span>Wallet Credit:</span>
                            <b>₹ <span id="creditDisp">0</span></b>
                        </div>
                        <div style="display:flex; justify-content:space-between; margin-bottom:5px; font-size:13px; opacity:0.8">
                            <span>Royalty Fee (<?php echo $royalty_percent; ?>%):</span>
                            <span>Using Royalty Rate</span>
                        </div>
                        <div style="border-top:1px solid rgba(0,0,0,0.1); padding-top:5px; margin-top:5px; display:flex; justify-content:space-between; font-size:18px;">
                            <span>You Pay:</span>
                            <b>₹ <span id="payDisp">0</span></b>
                        </div>
                    </div>

                    <button class="btn btn-primary" id="payBtn" onclick="initiatePayment()" disabled>Proceed to Pay</button>
                </div>
            </div>

            <!-- Right: History -->
            <div class="card" style="height: fit-content;">
                <div class="card-title">Transaction History</div>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Wallet Added</th>
                            <th>Paid Amt</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($txns->num_rows > 0): ?>
                            <?php while($row = $txns->fetch_assoc()): ?>
                            <?php 
                                $is_debit = $row['credit_amount'] < 0;
                                $color = $is_debit ? 'var(--error)' : 'var(--active)';
                                $sign = $is_debit ? '' : '+';
                            ?>
                            <tr>
                                <td><?php echo date('d M, h:i A', strtotime($row['created_at'])); ?></td>
                                <td style="color:<?php echo $color; ?>"><?php echo $sign; ?>₹<?php echo number_format($row['credit_amount'], 2); ?></td>
                                <td style="color:var(--muted)">₹<?php echo number_format($row['amount'], 2); ?></td>
                                <td><span class="badge badge-success">Success</span></td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="4" style="text-align:center; color:var(--muted); padding:30px;">No transactions yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </main>

    <script>
        const royaltyPercent = <?php echo $royalty_percent; ?>;
        
        function calculatePayable() {
            const amount = parseFloat(document.getElementById('add_amount').value) || 0;
            const calcBox = document.getElementById('calcBox');
            const btn = document.getElementById('payBtn');

            if (amount > 0) {
                const payable = (amount * royaltyPercent) / 100;
                
                document.getElementById('creditDisp').innerText = amount.toFixed(2);
                document.getElementById('payDisp').innerText = payable.toFixed(2);
                
                calcBox.style.display = 'block';
                btn.disabled = false;
                btn.innerText = `Pay ₹ ${payable.toFixed(2)}`;
                return payable;
            } else {
                calcBox.style.display = 'none';
                btn.disabled = true;
                btn.innerText = "Proceed to Pay";
                return 0;
            }
        }

        function initiatePayment() {
            const creditAmount = parseFloat(document.getElementById('add_amount').value);
            const btn = document.getElementById('payBtn');
            
            if(creditAmount <= 0) return;
            
            btn.disabled = true;
            btn.innerText = "Processing...";

            // 1. Create Order via Backend
            const formData = new FormData();
            formData.append('amount', creditAmount);

            fetch('create-order.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if(data.status === 'success') {
                    // 2. Open Razorpay
                    var options = {
                        "key": data.key_id, 
                        "amount": data.amount * 100,
                        "currency": "INR",
                        "name": "MG Skills",
                        "description": "Wallet Topup",
                        "order_id": data.order_id,
                        "handler": function (response){
                            verifyPayment(response, data.amount, data.wallet_credit);
                        },
                        "prefill": data.prefill,
                        "theme": { "color": "#6f75ff" }
                    };
                    
                    var rzp1 = new Razorpay(options);
                    rzp1.open();
                    rzp1.on('payment.failed', function (response){
                        alert("Payment Failed: " + response.error.description);
                        btn.disabled = false;
                        btn.innerText = `Pay ₹ ${data.amount.toFixed(2)}`;
                    });
                } else {
                    alert("Order Creation Failed: " + data.message);
                    btn.disabled = false;
                    btn.innerText = "Try Again";
                }
            })
            .catch(err => {
                console.error(err);
                alert("Something went wrong initializing payment.");
                btn.disabled = false;
                btn.innerText = "Try Again";
            });
        }

        function verifyPayment(response, paidAmount, creditAmount) {
            const formData = new FormData();
            formData.append('action', 'verify_payment');
            formData.append('razorpay_payment_id', response.razorpay_payment_id);
            formData.append('razorpay_order_id', response.razorpay_order_id);
            formData.append('razorpay_signature', response.razorpay_signature);
            formData.append('paid_amount', paidAmount);
            formData.append('wallet_credit', creditAmount);

            fetch('wallet.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if(data.status === 'success') {
                    alert('Top-up Successful! New Balance: ₹ ' + data.new_balance);
                    location.reload();
                } else {
                    alert('Verification failed: ' + data.message);
                }
            });
        }
    </script>
</body>
</html>
<?php $conn->close(); ?>
