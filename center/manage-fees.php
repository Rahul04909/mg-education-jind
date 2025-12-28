<?php
session_start();
require_once __DIR__ . '/../database/db-config.php';

if (!isset($_SESSION['center_id'])) {
    header("Location: login.php");
    exit;
}

$center_id = $_SESSION['center_id'];
$conn = getDbConnection();

// Handle Collection
// Handle Collection
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['collect_fee'])) {
    $enrollment_no = $_POST['enrollment_no'];
    $amount = floatval($_POST['amount']);
    $mode = $_POST['mode'];
    $txn_id = "OFFLINE" . time() . rand(100,999);

    // 1. Check Wallet Balance
    $c_qm = "SELECT wallet_balance FROM centers WHERE id = $center_id";
    $c_res = $conn->query($c_qm);
    $c_row = $c_res->fetch_assoc();
    $current_balance = floatval($c_row['wallet_balance']);

    if ($current_balance < $amount) {
        $error_message = "Insufficient Wallet Balance (₹" . number_format($current_balance, 2) . "). Please Top-up your wallet.";
    } else {
        // 2. Deduct from Wallet
        $new_balance = $current_balance - $amount;
        $up_sql = "UPDATE centers SET wallet_balance = $new_balance WHERE id = $center_id";
        
        if ($conn->query($up_sql) === TRUE) {
            // 3. Log Wallet Transaction (Debit)
            // amount = 0 (No Real money paid now), credit_amount = -$amount (Balance reduced), payment_id = local ref
            $w_sql = "INSERT INTO wallet_transactions (center_id, amount, credit_amount, payment_id, status) 
                      VALUES ($center_id, 0, -$amount, 'Fee_$enrollment_no', 'success')";
            $conn->query($w_sql);

            // 4. Log Student Transaction (Original Logic)
            $sql = "INSERT INTO student_transactions (enrollment_no, transaction_id, payment_mode, amount, status) 
                    VALUES ('$enrollment_no', '$txn_id', '$mode', $amount, 'success')";
            
            if ($conn->query($sql) === TRUE) {
                $success_message = "Fee collected successfully! Wallet Deducted: ₹" . number_format($amount, 2);
            }
        } else {
             $error_message = "Database Error: Warning - Wallet could not be updated.";
        }
    }
}

// Fetch Students and Fee Stats
$sql = "SELECT a.full_name, a.enrollment_no, c.fees 
        FROM admissions a 
        LEFT JOIN courses c ON a.course_id = c.id 
        WHERE a.center_id = $center_id";
$result = $conn->query($sql);
$students = [];
while ($row = $result->fetch_assoc()) {
    $course_fees = json_decode($row['fees'], true);
    $total_fee = isset($course_fees['amount']) ? floatval($course_fees['amount']) : 0;
    
    // Paid
    $p_sql = "SELECT SUM(amount) as paid FROM student_transactions WHERE enrollment_no = '{$row['enrollment_no']}' AND status='success'";
    $p_res = $conn->query($p_sql);
    $paid = floatval($p_res->fetch_assoc()['paid']);
    
    $row['total_fee'] = $total_fee;
    $row['paid_fee'] = $paid;
    $row['pending_fee'] = $total_fee - $paid;
    
    $students[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Fees - Center</title>
    <style>
        :root{--active:#22c55e;--indigo:#6f75ff;--line:#e6e8ee;--text:#0b1020;--muted:#6f7787;}
        body{font-family:'Outfit',sans-serif;background:#f8fafc;color:var(--text)}
        .admin-content{margin-left:260px;padding:30px}
        table{width:100%;border-collapse:collapse;background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 2px 10px rgba(0,0,0,0.05)}
        th,td{padding:15px;text-align:left;border-bottom:1px solid var(--line)}
        th{background:#f1f5f9;font-weight:600}
        .modal{display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);align-items:center;justify-content:center}
        .modal-content{background:#fff;padding:30px;border-radius:12px;width:400px}
        .btn{padding:8px 16px;background:var(--indigo);color:#fff;border:none;border-radius:6px;cursor:pointer}
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <main class="admin-content">
        <h1>Fee Management</h1>
        
        <?php if(!empty($success_message)): ?>
        <div style="background:#d1fae5;color:#065f46;padding:15px;border-radius:8px;margin-bottom:20px;border:1px solid #a7f3d0">
            <?php echo $success_message; ?>
        </div>
        <?php endif; ?>

        <?php if(!empty($error_message)): ?>
        <div style="background:#fee2e2;color:#991b1b;padding:15px;border-radius:8px;margin-bottom:20px;border:1px solid #fecaca">
            <?php echo $error_message; ?>
        </div>
        <?php endif; ?>

        <table>
            <thead>
                <tr>
                    <th>Student</th>
                    <th>Total Fee</th>
                    <th>Paid</th>
                    <th>Pending</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($students as $s): ?>
                <tr>
                    <td>
                        <div><?php echo htmlspecialchars($s['full_name']); ?></div>
                        <small style="color:var(--muted)"><?php echo $s['enrollment_no']; ?></small>
                    </td>
                    <td>₹<?php echo number_format($s['total_fee'], 2); ?></td>
                    <td style="color:var(--active)">₹<?php echo number_format($s['paid_fee'], 2); ?></td>
                    <td style="color:#ef4444">₹<?php echo number_format($s['pending_fee'], 2); ?></td>
                    <td>
                        <?php if($s['pending_fee'] > 0): ?>
                        <button class="btn" onclick="collect('<?php echo $s['enrollment_no']; ?>', <?php echo $s['pending_fee']; ?>)">Collect Fee</button>
                        <?php else: ?>
                        <span style="color:var(--active);font-weight:600">Paid</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </main>
    
    <div id="collectModal" class="modal">
        <div class="modal-content">
            <h3>Collect Fee</h3>
            <form method="POST">
                <input type="hidden" name="collect_fee" value="1">
                <input type="hidden" name="enrollment_no" id="modalEnroll">
                <p>Pending: ₹<span id="modalPending"></span></p>
                <div style="margin-bottom:15px">
                    <label>Amount</label>
                    <input type="number" name="amount" id="modalAmount" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:6px" required>
                </div>
                <div style="margin-bottom:15px">
                    <label>Mode</label>
                    <select name="mode" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:6px">
                        <option value="Cash">Cash</option>
                        <option value="UPI">UPI</option>
                        <option value="Bank Transfer">Bank Transfer</option>
                    </select>
                </div>
                <button type="submit" class="btn" style="width:100%">Record Payment</button>
                <button type="button" onclick="document.getElementById('collectModal').style.display='none'" style="margin-top:10px;background:none;border:none;color:#666;cursor:pointer;width:100%">Cancel</button>
            </form>
        </div>
    </div>
    
    <script>
        function collect(enroll, pending) {
            document.getElementById('modalEnroll').value = enroll;
            document.getElementById('modalPending').textContent = pending;
            document.getElementById('modalAmount').value = pending;
            document.getElementById('modalAmount').max = pending;
            document.getElementById('collectModal').style.display = 'flex';
        }
    </script>
</body>
</html>
