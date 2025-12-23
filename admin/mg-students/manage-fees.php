<?php
require_once __DIR__ . '/../../database/db-config.php';
$conn = getDbConnection();

$message = "";
$msg_type = "";
$student = null;
$transactions = [];
$total_fee = 0;
$total_paid = 0;
$pending_fee = 0;

// Handle New Transaction
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_transaction'])) {
    $enrollment_no = $_POST['enrollment_no'];
    $amount = floatval($_POST['amount']);
    $mode = $_POST['payment_mode'];
    $txn_id = $_POST['transaction_id'] ?: 'MANUAL_' . uniqid();
    $remarks = $_POST['remarks'];
    
    if ($amount > 0) {
        $stmt = $conn->prepare("INSERT INTO student_transactions (enrollment_no, transaction_id, amount, payment_mode, status, remarks) VALUES (?, ?, ?, ?, 'success', ?)");
        $stmt->bind_param("ssdss", $enrollment_no, $txn_id, $amount, $mode, $remarks);
        if ($stmt->execute()) {
            $message = "Transaction added successfully!";
            $msg_type = "success";
        } else {
            $message = "Error adding transaction: " . $conn->error;
            $msg_type = "error";
        }
    }
}

// Handle Search
if (isset($_REQUEST['enrollment_no']) && !empty($_REQUEST['enrollment_no'])) {
    $search_enroll = $conn->real_escape_string($_REQUEST['enrollment_no']);
    
    // Fetch Student & Course
    $sql = "SELECT a.*, c.title as course_title, c.fees as course_meta 
            FROM admissions a 
            LEFT JOIN courses c ON a.course_id = c.id 
            WHERE a.enrollment_no = '$search_enroll'";
    $res = $conn->query($sql);
    
    if ($res->num_rows > 0) {
        $student = $res->fetch_assoc();
        
        // Calculate Fees
        $course_meta = json_decode($student['course_meta'], true);
        $total_fee = isset($course_meta['amount']) ? floatval($course_meta['amount']) : 0;
        
        // Fetch Transactions
        $t_sql = "SELECT * FROM student_transactions WHERE enrollment_no = '$search_enroll' ORDER BY created_at DESC";
        $t_res = $conn->query($t_sql);
        while($row = $t_res->fetch_assoc()) {
            $transactions[] = $row;
            if ($row['status'] == 'success') {
                $total_paid += floatval($row['amount']);
            }
        }
        
        $pending_fee = $total_fee - $total_paid;
        
    } else {
        $message = "Student not found with Enrollment No: $search_enroll";
        $msg_type = "error";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Fees - MG Skills</title>
    <style>
        :root{--active:#22c55e;--indigo:#6f75ff;--line:#e6e8ee;--text:#0b1020;--muted:#6f7787;--error:#ef4444;--success:#22c55e}
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:system-ui,-apple-system,sans-serif;background:#f8fafc;color:var(--text)}
        .admin-content{margin-left:260px;padding:20px}
        .admin-wrap{max-width:1100px;margin:0 auto}
        .page-header{margin-bottom:20px;display:flex;justify-content:space-between;align-items:center}
        .page-title{font-size:24px;font-weight:700}
        
        .card{background:#fff;padding:25px;border-radius:12px;box-shadow:0 2px 10px rgba(0,0,0,0.05);margin-bottom:25px}
        .search-box{display:flex;gap:10px}
        .input-control{padding:10px;border:1px solid var(--line);border-radius:8px;font-size:14px;width:100%}
        .btn{padding:10px 20px;background:var(--indigo);color:#fff;border:none;border-radius:8px;cursor:pointer;font-weight:600}
        .btn-green{background:var(--success)}
        
        .stats-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:20px;margin-bottom:20px}
        .stat-box{background:#f8fafc;padding:20px;border-radius:10px;text-align:center;border:1px solid var(--line)}
        .stat-val{font-size:24px;font-weight:700;color:var(--indigo);margin-top:5px}
        .stat-label{font-size:14px;color:var(--muted)}
        .stat-box.pending .stat-val{color:var(--error)}
        
        .table-wrap{overflow-x:auto}
        table{width:100%;border-collapse:collapse;margin-top:15px}
        th,td{text-align:left;padding:12px;border-bottom:1px solid var(--line)}
        th{color:var(--muted);font-weight:600;font-size:13px}
        
        .info-grid{display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px}
        .info-item label{font-size:13px;color:var(--muted);display:block}
        .info-item div{font-weight:600}
        
        .modal {display: none;position: fixed;z-index: 1000;left: 0;top: 0;width: 100%;height: 100%;background-color: rgba(0,0,0,0.5);}
        .modal-content {background-color: #fff;margin: 10% auto;padding: 30px;border-radius: 12px;width: 400px;box-shadow: 0 4px 20px rgba(0,0,0,0.2);}
        .close {color: #aaa;float: right;font-size: 28px;font-weight: bold;cursor:pointer;}
        
        .alert{padding:15px;border-radius:8px;margin-bottom:20px}
        .alert-success{background:#dcfce7;color:#166534}
        .alert-error{background:#fee2e2;color:#991b1b}
    </style>
</head>
<body>
    <?php include __DIR__ . "/../sidebar.php"; ?>
    <main class="admin-content">
        <div class="admin-wrap">
            <div class="page-header">
                <h1 class="page-title">Manage Fees</h1>
            </div>
            
            <?php if($message): ?>
                <div class="alert alert-<?php echo $msg_type; ?>"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <!-- Search -->
            <div class="card">
                <form method="GET" class="search-box">
                    <input type="text" name="enrollment_no" class="input-control" placeholder="Enter Enrollment No (e.g. MG20240001)" value="<?php echo isset($_GET['enrollment_no']) ? htmlspecialchars($_GET['enrollment_no']) : ''; ?>" required>
                    <button type="submit" class="btn">Search Student</button>
                </form>
            </div>
            
            <?php if($student): ?>
            
            <div class="stats-grid">
                <div class="stat-box">
                    <div class="stat-label">Total Course Fee</div>
                    <div class="stat-val">₹<?php echo number_format($total_fee, 2); ?></div>
                </div>
                <div class="stat-box">
                    <div class="stat-label">Total Paid</div>
                    <div class="stat-val">₹<?php echo number_format($total_paid, 2); ?></div>
                </div>
                <div class="stat-box pending">
                    <div class="stat-label">Pending Amount</div>
                    <div class="stat-val">₹<?php echo number_format($pending_fee, 2); ?></div>
                </div>
            </div>
            
            <div class="card">
                <h3>Student Details</h3>
                <br>
                <div class="info-grid">
                    <div class="info-item"><label>Full Name</label><div><?php echo htmlspecialchars($student['full_name']); ?></div></div>
                    <div class="info-item"><label>Enrollment No</label><div><?php echo htmlspecialchars($student['enrollment_no']); ?></div></div>
                    <div class="info-item"><label>Course</label><div><?php echo htmlspecialchars($student['course_title']); ?></div></div>
                    <div class="info-item"><label>Mobile</label><div><?php echo htmlspecialchars($student['mobile']); ?></div></div>
                </div>
            </div>
            
            <div class="card">
                <div style="display:flex;justify-content:space-between;align-items:center;">
                    <h3>Transaction History</h3>
                    <button onclick="document.getElementById('txnModal').style.display='block'" class="btn btn-green">+ Add Payment</button>
                </div>
                
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Transaction ID</th>
                                <th>Mode</th>
                                <th>Remarks</th>
                                <th>Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($transactions as $t): ?>
                            <tr>
                                <td><?php echo date("d M Y, h:i A", strtotime($t['created_at'])); ?></td>
                                <td><?php echo htmlspecialchars($t['transaction_id']); ?></td>
                                <td><?php echo htmlspecialchars($t['payment_mode']); ?></td>
                                <td><?php echo htmlspecialchars($t['remarks']); ?></td>
                                <td style="font-weight:600">₹<?php echo number_format($t['amount'], 2); ?></td>
                                <td>
                                    <span style="padding:4px 8px;border-radius:4px;font-size:12px;background:<?php echo $t['status']=='success'?'#dcfce7':'#fee2e2'; ?>;color:<?php echo $t['status']=='success'?'#166534':'#991b1b'; ?>">
                                        <?php echo strtoupper($t['status']); ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if(empty($transactions)): ?>
                                <tr><td colspan="6" style="text-align:center;padding:20px;">No transactions found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Modal -->
            <div id="txnModal" class="modal">
                <div class="modal-content">
                    <span class="close" onclick="document.getElementById('txnModal').style.display='none'">&times;</span>
                    <h2 style="margin-bottom:20px;">Collect Fee</h2>
                    <form method="POST">
                        <input type="hidden" name="add_transaction" value="1">
                        <input type="hidden" name="enrollment_no" value="<?php echo $student['enrollment_no']; ?>">
                        
                        <div style="margin-bottom:15px;">
                            <label style="display:block;margin-bottom:5px;font-weight:600">Amount (₹)</label>
                            <input type="number" name="amount" class="input-control" required min="1" step="any" value="<?php echo $pending_fee > 0 ? $pending_fee : ''; ?>">
                        </div>
                        
                        <div style="margin-bottom:15px;">
                            <label style="display:block;margin-bottom:5px;font-weight:600">Payment Mode</label>
                            <select name="payment_mode" class="input-control" required>
                                <option value="Cash">Cash</option>
                                <option value="Cheque">Cheque</option>
                                <option value="UPI">UPI</option>
                                <option value="Bank Transfer">Bank Transfer</option>
                                <option value="Manual">Others</option>
                            </select>
                        </div>
                        
                        <div style="margin-bottom:15px;">
                            <label style="display:block;margin-bottom:5px;font-weight:600">Transaction/Ref ID (Optional)</label>
                            <input type="text" name="transaction_id" class="input-control" placeholder="Leave blank for auto-generated">
                        </div>
                        
                        <div style="margin-bottom:15px;">
                            <label style="display:block;margin-bottom:5px;font-weight:600">Remarks</label>
                            <textarea name="remarks" class="input-control" rows="3"></textarea>
                        </div>
                        
                        <button type="submit" class="btn" style="width:100%">Submit Payment</button>
                    </form>
                </div>
            </div>
            
            <?php endif; ?>
            
        </div>
    </main>
    <script>
        window.onclick = function(event) {
            if (event.target == document.getElementById('txnModal')) {
                document.getElementById('txnModal').style.display = "none";
            }
        }
    </script>
</body>
</html>
<?php $conn->close(); ?>
