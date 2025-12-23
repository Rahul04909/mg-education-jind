<?php
session_start();
require_once __DIR__ . '/../database/db-config.php';

if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit;
}

$conn = getDbConnection();
$enrollment_no = $_SESSION['enrollment_no'];

// Fetch Razorpay Key
$key_sql = "SELECT razorpay_key_id FROM razorpay_settings WHERE is_active = 1 LIMIT 1";
$key_res = $conn->query($key_sql);
$razorpay_key = ($key_res->num_rows > 0) ? $key_res->fetch_assoc()['razorpay_key_id'] : '';

// Fetch Course Fee
$student_id = $_SESSION['student_id'];
$sql = "SELECT a.*, c.title as course_title, c.fees as course_meta 
        FROM admissions a 
        LEFT JOIN courses c ON a.course_id = c.id 
        WHERE a.id = $student_id";
$res = $conn->query($sql);
$student = $res->fetch_assoc();

$course_meta = json_decode($student['course_meta'], true);
$total_fee = isset($course_meta['amount']) ? floatval($course_meta['amount']) : 0;

// Fetch Transactions
$transactions = [];
$total_paid = 0;
$t_sql = "SELECT * FROM student_transactions WHERE enrollment_no = '$enrollment_no' ORDER BY created_at DESC";
$t_res = $conn->query($t_sql);
while($row = $t_res->fetch_assoc()) {
    $transactions[] = $row;
    if ($row['status'] == 'success') {
        $total_paid += floatval($row['amount']);
    }
}

$pending_fee = $total_fee - $total_paid;
if ($pending_fee < 0) $pending_fee = 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Fees - MG Skills</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <style>
        :root{--primary:#6366f1;--secondary:#0f172a;--bg:#f8fafc;--white:#fff;--border:#e2e8f0;--success:#22c55e;--error:#ef4444}
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:'Outfit',sans-serif;background:var(--bg);color:var(--secondary);display:flex;min-height:100vh}
        
        .sidebar{width:260px;background:var(--white);border-right:1px solid var(--border);position:fixed;height:100vh;display:flex;flex-direction:column;z-index: 10;}
        .main{margin-left:260px;flex:1;padding:30px}
        
        /* Reuse styles from index.php roughly, or include sidebar */
        /* Since sidebar code is not fully modular (HTML structure in file), I will include sidebar.php and adjust */
        /* If sidebar.php is just the menu, great. Let's check sidebar.php first. 
           Actually, checking previous output, sidebar.php was in the list.
           Assume sidebar.php contains the sidebar div. */
           
        .card{background:var(--white);border-radius:16px;border:1px solid var(--border);padding:24px;margin-bottom:24px}
        .stats-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:20px;margin-bottom:30px}
        .stat-card{background:#f8fafc;padding:20px;border-radius:12px;text-align:center;border:1px solid var(--border)}
        .stat-val{font-size:24px;font-weight:700;color:var(--primary);margin-top:5px}
        .stat-label{font-size:14px;color:#64748b}
        
        table{width:100%;border-collapse:collapse;margin-top:15px}
        th,td{text-align:left;padding:12px;border-bottom:1px solid var(--border)}
        th{color:#64748b;font-weight:600;font-size:13px}
        
        .pay-section{margin-top:20px;background:#eff6ff;padding:20px;border-radius:12px;display:flex;align-items:center;gap:20px}
        .pay-input{padding:10px;border:1px solid var(--primary);border-radius:6px;width:150px;font-size:16px;font-weight:600}
        .pay-btn{background:var(--primary);color:#fff;border:none;padding:10px 24px;border-radius:6px;cursor:pointer;font-weight:600}
        .pay-btn:disabled{opacity:0.5;cursor:not-allowed}
        
        @media(max-width:1024px){.sidebar{display:none}.main{margin-left:0}}
    </style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<main class="main">
    <div style="margin-bottom:30px">
        <h1 style="font-size:24px;font-weight:700">Fee Management</h1>
        <p style="color:#64748b">Manage your course fees and view transaction history.</p>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-label">Total Fee</div>
            <div class="stat-val">₹<?php echo number_format($total_fee, 2); ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Paid Amount</div>
            <div class="stat-val" style="color:var(--success)">₹<?php echo number_format($total_paid, 2); ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Pending Due</div>
            <div class="stat-val" style="color:var(--error)">₹<?php echo number_format($pending_fee, 2); ?></div>
        </div>
    </div>
    
    <?php if($pending_fee > 0): ?>
    <div class="card">
        <h3>Pay Pending Fees</h3>
        <div class="pay-section">
            <div>
                <label style="display:block;font-size:12px;margin-bottom:4px;color:var(--primary)">Amount to Pay (₹)</label>
                <input type="number" id="payAmount" class="pay-input" value="<?php echo $pending_fee; ?>" max="<?php echo $pending_fee; ?>" min="1">
            </div>
            <button id="payBtn" class="pay-btn">Pay Now with Razorpay</button>
        </div>
        <p id="msg" style="margin-top:10px;font-size:14px"></p>
    </div>
    <?php endif; ?>

    <div class="card">
        <h3>Transaction History</h3>
        <div style="overflow-x:auto">
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Transaction ID</th>
                        <th>Mode</th>
                        <th>Amount</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($transactions as $t): ?>
                    <tr>
                        <td><?php echo date("d M Y", strtotime($t['created_at'])); ?></td>
                        <td><?php echo htmlspecialchars($t['transaction_id']); ?></td>
                        <td><?php echo htmlspecialchars($t['payment_mode']); ?></td>
                        <td style="font-weight:600">₹<?php echo number_format($t['amount'], 2); ?></td>
                        <td>
                            <span style="color:<?php echo $t['status']=='success'?'var(--success)':'var(--error)'; ?>">
                                <?php echo strtoupper($t['status']); ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if(empty($transactions)): ?>
                        <tr><td colspan="5">No transactions found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</main>

<script>
    lucide.createIcons();
    
    const payBtn = document.getElementById('payBtn');
    const payInput = document.getElementById('payAmount');
    const msg = document.getElementById('msg');
    
    if(payBtn) {
        payBtn.onclick = function() {
            const amount = payInput.value;
            if(amount <= 0) {
                alert("Please enter a valid amount");
                return;
            }
            
            payBtn.disabled = true;
            payBtn.textContent = "Processing...";
            
            // 1. Create Order
            fetch('create_order.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'amount=' + amount
            })
            .then(res => res.json())
            .then(data => {
                if(data.status === 'success') {
                    // 2. Open Razorpay
                    var options = {
                        "key": "<?php echo $razorpay_key; ?>",
                        "amount": data.amount, // in paise
                        "currency": "INR",
                        "name": "MG Skills",
                        "description": "Fee Payment",
                        "order_id": data.order_id,
                        "handler": function (response){
                            verifyPayment(response, amount);
                        },
                        "modal": {
                            "ondismiss": function(){
                                payBtn.disabled = false;
                                payBtn.textContent = "Pay Now with Razorpay";
                            }
                        }
                    };
                    var rzp1 = new Razorpay(options);
                    rzp1.open();
                } else {
                    alert(data.message);
                    payBtn.disabled = false;
                    payBtn.textContent = "Pay Now with Razorpay";
                }
            })
            .catch(err => {
                console.error(err);
                alert("Something went wrong");
                payBtn.disabled = false;
                payBtn.textContent = "Pay Now with Razorpay";
            });
        };
    }
    
    function verifyPayment(response, amount) {
        msg.textContent = "Verifying Payment...";
        
        const formData = new FormData();
        formData.append('razorpay_payment_id', response.razorpay_payment_id);
        formData.append('razorpay_order_id', response.razorpay_order_id);
        formData.append('razorpay_signature', response.razorpay_signature);
        formData.append('amount', amount);
        
        fetch('verify_payment.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if(data.status === 'success') {
                msg.style.color = 'green';
                msg.textContent = "Payment Successful! Reloading...";
                setTimeout(() => location.reload(), 2000);
            } else {
                msg.style.color = 'red';
                msg.textContent = data.message;
                payBtn.disabled = false;
            }
        });
    }
</script>
</body>
</html>
<?php $conn->close(); ?>
