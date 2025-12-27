<?php
session_start();
require_once __DIR__ . '/../database/db-config.php';

if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit;
}

$conn = getDbConnection();
$student_id = $_SESSION['student_id'];

// Fetch Student Info (Center ID)
$s_sql = "SELECT center_id, enrollment_no FROM admissions WHERE id = $student_id";
$s_res = $conn->query($s_sql);
$student = $s_res->fetch_assoc();

if (!$student['center_id']) {
    // Should not happen if logic is correct, but redirect to admin fees just in case
    header("Location: fees.php");
    exit;
}

$center_id = $student['center_id'];

// Fetch Center Bank Details
$b_sql = "SELECT * FROM center_bank_details WHERE center_id = $center_id";
$b_res = $conn->query($b_sql);
$bank = ($b_res->num_rows > 0) ? $b_res->fetch_assoc() : null;

// Fetch Transaction History (Offline payments recorded by center)
$t_sql = "SELECT * FROM student_transactions WHERE enrollment_no = '{$student['enrollment_no']}' ORDER BY created_at DESC";
$t_res = $conn->query($t_sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Fees - Student Portal</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        :root{--primary:#8b5cf6;--bg:#f8fafc;--text:#1e293b;--white:#fff;}
        body{font-family:'Outfit',sans-serif;background:var(--bg);color:var(--text);display:flex}
        .main{margin-left:260px;flex:1;padding:30px}
        .card{background:var(--white);padding:24px;border-radius:16px;box-shadow:0 1px 3px rgba(0,0,0,0.1);margin-bottom:24px}
        .row{display:flex;gap:24px;flex-wrap:wrap}
        .col{flex:1;min-width:300px}
        h2{font-size:20px;font-weight:700;margin-bottom:16px}
        .bank-info p{margin-bottom:8px;font-size:15px}
        .qr-container{text-align:center;background:#f3f4f6;padding:20px;border-radius:12px}
        .qr-img{max-width:200px;border-radius:8px;margin-bottom:10px}
        .badge{display:inline-block;padding:4px 10px;border-radius:20px;font-size:12px;font-weight:600}
        .success{background:#dcfce7;color:#166534}
        .pending{background:#fef9c3;color:#854d0e}
        table{width:100%;border-collapse:collapse}
        th,td{padding:12px;text-align:left;border-bottom:1px solid #e2e8f0}
        @media(max-width:1024px){.main{margin-left:0}}
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <main class="main">
        <h1 style="font-size:24px;font-weight:700;margin-bottom:24px">Fee Management</h1>
        
        <?php if($bank): ?>
        <div class="row">
            <!-- Bank Details -->
            <div class="col card">
                <h2>Center Bank Details</h2>
                <div class="bank-info">
                    <p><strong>Bank Name:</strong> <?php echo htmlspecialchars($bank['bank_name']); ?></p>
                    <p><strong>Account Holder:</strong> <?php echo htmlspecialchars($bank['account_holder']); ?></p>
                    <p><strong>Account No:</strong> <?php echo htmlspecialchars($bank['account_no']); ?></p>
                    <p><strong>IFSC Code:</strong> <?php echo htmlspecialchars($bank['ifsc']); ?></p>
                    <p><strong>Branch:</strong> <?php echo htmlspecialchars($bank['branch']); ?></p>
                    <?php if(!empty($bank['upi_id'])): ?>
                    <p><strong>UPI ID:</strong> <?php echo htmlspecialchars($bank['upi_id']); ?></p>
                    <?php endif; ?>
                </div>
                <div style="margin-top:20px;padding:15px;background:#fff7ed;border-radius:8px;border:1px solid #ffedd5;font-size:14px;color:#9a3412">
                    <strong>Note:</strong> Please make payment to the above account or scan the QR code. After payment, contact your center administrator to update your fee record.
                </div>
            </div>

            <!-- QR Codes -->
            <div class="col card">
                <h2>Scan to Pay</h2>
                <div class="row" style="gap:10px">
                    <?php if(!empty($bank['qr_1'])): ?>
                    <div class="qr-container" style="flex:1">
                        <img src="../center/<?php echo $bank['qr_1']; ?>" class="qr-img" alt="QR Code 1">
                        <div>Primary QR</div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if(!empty($bank['qr_2'])): ?>
                    <div class="qr-container" style="flex:1">
                        <img src="../center/<?php echo $bank['qr_2']; ?>" class="qr-img" alt="QR Code 2">
                        <div>Secondary QR</div>
                    </div>
                    <?php endif; ?>
                </div>
                <?php if(empty($bank['qr_1']) && empty($bank['qr_2'])): ?>
                    <p style="text-align:center;color:#64748b">No QR Codes available.</p>
                <?php endif; ?>
            </div>
        </div>
        <?php else: ?>
        <div class="card">
            <p>No bank details available for your center. Please contact the center administrator.</p>
        </div>
        <?php endif; ?>

        <!-- Fee History -->
        <div class="card">
            <h2>Fee History</h2>
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
                        <?php if($t_res->num_rows > 0): ?>
                            <?php while($row = $t_res->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo date("d M Y", strtotime($row['created_at'])); ?></td>
                                <td><?php echo htmlspecialchars($row['transaction_id']); ?></td>
                                <td><?php echo htmlspecialchars($row['payment_mode']); ?></td>
                                <td>₹<?php echo number_format($row['amount'], 2); ?></td>
                                <td><span class="badge success">PAID</span></td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="5" style="text-align:center">No fee payments recorded yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
    <script>lucide.createIcons();</script>
</body>
</html>
