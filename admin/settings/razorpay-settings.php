<?php
// Include database configuration
require_once __DIR__ . '/../../database/db-config.php';

// Create database connection using our new function
$conn = getDbConnection();

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize variables
$success_message = "";
$error_message = "";
$test_payment_result = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST["save_settings"])) {
        // Save Razorpay settings
        $razorpay_key_id = mysqli_real_escape_string($conn, $_POST["razorpay_key_id"]);
        $razorpay_key_secret = mysqli_real_escape_string($conn, $_POST["razorpay_key_secret"]);
        $is_active = isset($_POST["is_active"]) ? 1 : 0;

        // Check if settings exist
        $check_sql = "SELECT COUNT(*) as count FROM razorpay_settings";
        $result = $conn->query($check_sql);
        $row = $result->fetch_assoc();

        if ($row["count"] > 0) {
            // Update existing settings
            $sql = "UPDATE razorpay_settings SET
                    razorpay_key_id = '$razorpay_key_id',
                    razorpay_key_secret = '$razorpay_key_secret',
                    is_active = $is_active
                    WHERE id = 1";
        } else {
            // Insert new settings
            $sql = "INSERT INTO razorpay_settings
                    (razorpay_key_id, razorpay_key_secret, is_active)
                    VALUES ('$razorpay_key_id', '$razorpay_key_secret', $is_active)";
        }

        if ($conn->query($sql) === true) {
            $success_message = "Razorpay settings saved successfully!";
        } else {
            $error_message = "Error saving settings: " . $conn->error;
        }
    } elseif (isset($_POST["test_payment"])) {
        // Test payment
        // Get Razorpay settings from database
        $settings_sql = "SELECT * FROM razorpay_settings WHERE id = 1";
        $settings_result = $conn->query($settings_sql);

        if ($settings_result->num_rows > 0) {
            $settings = $settings_result->fetch_assoc();
            $amount = floatval($_POST["amount"]);
            
            if ($amount > 0) {
                // Log test payment
                $status = "success";
                $payment_id = "test_" . uniqid();
                $order_id = "order_" . uniqid();
                $signature = "sig_" . uniqid();
                
                $log_sql = "INSERT INTO payment_logs (amount, currency, payment_id, order_id, signature, status) 
                           VALUES ($amount, 'INR', '$payment_id', '$order_id', '$signature', '$status')";
                
                if ($conn->query($log_sql) === TRUE) {
                    $test_payment_result = "success";
                    $success_message = "Test payment of ₹" . number_format($amount, 2) . " processed successfully!";
                } else {
                    $test_payment_result = "error";
                    $error_message = "Failed to log test payment: " . $conn->error;
                }
            } else {
                $error_message = "Please enter a valid amount greater than 0.";
            }
        } else {
            $error_message = "Please save Razorpay settings before testing payment.";
        }
    }
}

// Fetch current Razorpay settings
$settings_sql = "SELECT * FROM razorpay_settings WHERE id = 1";
$settings_result = $conn->query($settings_sql);
$settings = $settings_result->num_rows > 0 ? $settings_result->fetch_assoc() : null;

// Fetch payment logs
$logs_sql = "SELECT * FROM payment_logs ORDER BY created_at DESC LIMIT 10";
$logs_result = $conn->query($logs_sql);

include __DIR__ . "/../sidebar.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Razorpay Settings - MG Education</title>
    <style>
        :root{--active:#22c55e;--indigo:#6f75ff;--line:#e6e8ee;--text:#0b1020;--muted:#6f7787;--error:#ef4444;--success:#22c55e;--info:#3b82f6}
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;background:linear-gradient(180deg,#f8fafc 0%,#ffffff 100%);color:var(--text)}
        .admin-content{margin-left:260px;min-height:100vh;padding:20px;transition:margin-left .25s ease}
        body.sidebar-collapsed .admin-content{margin-left:88px}
        .admin-wrap{max-width:1400px;margin:0 auto}
        .page-header{margin-bottom:30px}
        .page-title{font-size:32px;font-weight:800;color:var(--text);margin-bottom:8px;display:flex;align-items:center;gap:12px}
        .page-subtitle{color:var(--muted);font-size:15px}
        .breadcrumb{color:var(--muted);margin-bottom:10px;font-size:14px}
        .breadcrumb a{color:var(--indigo);text-decoration:none}
        .alert{padding:16px 20px;border-radius:12px;margin-bottom:20px;display:flex;align-items:center;gap:12px;border:1px solid;animation:slideDown .3s ease}
        @keyframes slideDown{from{opacity:0;transform:translateY(-10px)}to{opacity:1;transform:translateY(0)}}
        .alert-success{background:#d1fae5;border-color:#86efac;color:#065f46}
        .alert-error{background:#fee2e2;border-color:#fca5a5;color:#991b1b}
        .alert-icon{width:24px;height:24px;flex-shrink:0}
        .grid{display:grid;grid-template-columns:2fr 1fr;gap:20px;margin-bottom:20px}
        @media(max-width:1024px){.grid{grid-template-columns:1fr}.admin-content{margin-left:88px}}
        .card{background:#fff;border:1px solid var(--line);border-radius:18px;padding:30px;box-shadow:0 4px 12px rgba(0,0,0,.05)}
        .card-header{margin-bottom:24px;padding-bottom:16px;border-bottom:2px solid var(--line)}
        .card-title{font-size:20px;font-weight:800;color:var(--text);display:flex;align-items:center;gap:10px}
        .card-subtitle{color:var(--muted);font-size:14px;margin-top:6px}
        .form-group{margin-bottom:20px}
        .form-label{display:block;font-weight:700;color:var(--text);margin-bottom:8px;font-size:14px}
        .form-input{width:100%;padding:12px 16px;border:1px solid var(--line);border-radius:10px;font-size:15px;transition:all .2s ease;font-family:inherit}
        .form-input:focus{outline:none;border-color:var(--indigo);box-shadow:0 0 0 3px rgba(111,117,255,.1)}
        .form-select{width:100%;padding:12px 16px;border:1px solid var(--line);border-radius:10px;font-size:15px;transition:all .2s ease;font-family:inherit;background:#fff;cursor:pointer}
        .form-select:focus{outline:none;border-color:var(--indigo);box-shadow:0 0 0 3px rgba(111,117,255,.1)}
        .form-help{font-size:13px;color:var(--muted);margin-top:6px}
        .checkbox-wrapper{display:flex;align-items:center;gap:10px}
        .checkbox-input{width:20px;height:20px;cursor:pointer}
        .btn{display:inline-flex;align-items:center;gap:8px;padding:12px 24px;border-radius:12px;font-weight:700;font-size:15px;border:none;cursor:pointer;transition:all .2s ease;text-decoration:none}
        .btn:disabled{opacity:.5;cursor:not-allowed}
        .btn-primary{background:linear-gradient(135deg,var(--indigo) 0%,#5a5fff 100%);color:#fff}
        .btn-primary:hover:not(:disabled){transform:translateY(-2px);box-shadow:0 8px 20px rgba(111,117,255,.3)}
        .btn-success{background:var(--success);color:#fff}
        .btn-success:hover:not(:disabled){transform:translateY(-2px);box-shadow:0 8px 20px rgba(34,197,94,.3)}
        .btn-outline{background:#fff;color:var(--indigo);border:2px solid var(--indigo)}
        .btn-outline:hover:not(:disabled){background:var(--indigo);color:#fff}
        .btn-icon{width:20px;height:20px}
        .form-actions{display:flex;gap:12px;margin-top:30px;padding-top:20px;border-top:1px solid var(--line)}
        .stat-card{background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);border-radius:16px;padding:24px;color:#fff;margin-bottom:20px;position:relative;overflow:hidden}
        .stat-card::before{content:'';position:absolute;right:-30px;bottom:-30px;width:150px;height:150px;background:radial-gradient(circle at 30% 30%,rgba(255,255,255,.2) 0%,transparent 60%);border-radius:50%}
        .stat-label{font-size:14px;opacity:.9;margin-bottom:8px}
        .stat-value{font-size:36px;font-weight:800}
        .test-payment-section{background:#f8fafc;border:2px dashed var(--line);border-radius:14px;padding:24px;margin-top:20px}
        .test-payment-title{font-size:16px;font-weight:700;color:var(--text);margin-bottom:16px;display:flex;align-items:center;gap:8px}
        .logs-table{width:100%;border-collapse:collapse;margin-top:16px}
        .logs-table th{background:#f8fafc;padding:12px;text-align:left;font-weight:700;color:var(--text);font-size:13px;border-bottom:2px solid var(--line)}
        .logs-table td{padding:12px;border-bottom:1px solid var(--line);color:var(--muted);font-size:14px}
        .status-badge{display:inline-flex;align-items:center;gap:6px;padding:4px 12px;border-radius:999px;font-size:12px;font-weight:700}
        .status-success{background:#d1fae5;color:#065f46}
        .status-failed{background:#fee2e2;color:#991b1b}
        .status-pending{background:#fef3c7;color:#92400e}
        .empty-state{text-align:center;padding:40px 20px;color:var(--muted)}
        .empty-state-icon{width:64px;height:64px;margin:0 auto 16px;opacity:.3}
        .info-box{background:#dbeafe;border-left:4px solid var(--info);padding:16px;border-radius:8px;margin-bottom:20px}
        .info-box-title{font-weight:700;color:#1e40af;margin-bottom:8px}
        .info-box-text{color:#1e40af;font-size:14px;line-height:1.6}
        .grid-2{display:grid;grid-template-columns:1fr 1fr;gap:16px}
        @media(max-width:768px){.grid-2{grid-template-columns:1fr}}
        .password-toggle{position:relative}
        .password-toggle-btn{position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;padding:4px;color:var(--muted)}
        .password-toggle-btn:hover{color:var(--text)}
    </style>
</head>
<body>
    <main class="admin-content">
        <div class="admin-wrap">
            <div class="page-header">
                <div class="breadcrumb">
                    <a href="../index.php">Dashboard</a> › <a href="#">Settings</a> › Razorpay Configuration
                </div>
                <h1 class="page-title">
                    <svg width="32" height="32" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Razorpay Settings
                </h1>
                <p class="page-subtitle">Configure your Razorpay API credentials to enable payment processing</p>
            </div>

            <?php if ($success_message): ?>
            <div class="alert alert-success">
                <svg class="alert-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div><?php echo htmlspecialchars($success_message); ?></div>
            </div>
            <?php endif; ?>

            <?php if ($error_message): ?>
            <div class="alert alert-error">
                <svg class="alert-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div><?php echo htmlspecialchars($error_message); ?></div>
            </div>
            <?php endif; ?>

            <div class="grid">
                <div>
                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title">
                                <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                Razorpay Configuration
                            </h2>
                            <p class="card-subtitle">Configure your Razorpay API credentials</p>
                        </div>

                        <div class="info-box">
                            <div class="info-box-title">💡 How to get Razorpay API Keys</div>
                            <div class="info-box-text">
                                1. Log in to your Razorpay Dashboard<br>
                                2. Navigate to Settings → API Keys<br>
                                3. Click on "Generate Key" to create a new key pair<br>
                                4. Copy the Key ID and Key Secret and paste them below<br>
                                <strong>Note:</strong> Use test keys for testing and live keys for production
                            </div>
                        </div>

                        <form method="POST" action="">
                            <div class="form-group">
                                <label class="form-label">Razorpay Key ID *</label>
                                <input type="text" name="razorpay_key_id" class="form-input"
                                       value="<?php echo $settings ? htmlspecialchars($settings["razorpay_key_id"]) : ""; ?>"
                                       placeholder="rzp_test_xxxxxxxxxxxxxx" required>
                                <div class="form-help">Your Razorpay Key ID (starts with rzp_)</div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Razorpay Key Secret *</label>
                                <div class="password-toggle">
                                    <input type="password" name="razorpay_key_secret" id="razorpaySecret" class="form-input"
                                           value="<?php echo $settings ? htmlspecialchars($settings["razorpay_key_secret"]) : ""; ?>"
                                           placeholder="••••••••••" required>
                                    <button type="button" class="password-toggle-btn" onclick="togglePassword('razorpaySecret')">
                                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </button>
                                </div>
                                <div class="form-help">Your Razorpay Key Secret</div>
                            </div>

                            <div class="form-group">
                                <div class="checkbox-wrapper">
                                    <input type="checkbox" name="is_active" id="is_active" class="checkbox-input"
                                           <?php echo $settings && $settings["is_active"] ? "checked" : ""; ?>>
                                    <label for="is_active" class="form-label" style="margin: 0;">Enable Razorpay</label>
                                </div>
                                <div class="form-help">Toggle to enable or disable Razorpay payments</div>
                            </div>

                            <div class="form-actions">
                                <button type="submit" name="save_settings" class="btn btn-primary">
                                    <svg class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path>
                                    </svg>
                                    Save Settings
                                </button>
                            </div>
                        </form>

                        <div class="test-payment-section">
                            <div class="test-payment-title">
                                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Test Payment
                            </div>
                            <form method="POST" action="">
                                <div class="form-group">
                                    <label class="form-label">Test Amount (₹) *</label>
                                    <input type="number" name="amount" class="form-input" step="0.01" min="1"
                                           placeholder="100.00" required>
                                    <div class="form-help">Enter amount in INR for test payment</div>
                                </div>
                                <button type="submit" name="test_payment" class="btn btn-success">
                                    <svg class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                                    </svg>
                                    Process Test Payment
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <div>
                    <div class="stat-card">
                        <div class="stat-label">Payment Configuration Status</div>
                        <div class="stat-value"><?php echo $settings && $settings["is_active"] ? "Active" : "Inactive"; ?></div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title">
                                <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                Payment Logs
                            </h2>
                            <p class="card-subtitle">Recent payment processing history</p>
                        </div>

                        <?php if ($logs_result->num_rows > 0): ?>
                        <table class="logs-table">
                            <thead>
                                <tr>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($log = $logs_result->fetch_assoc()): ?>
                                <tr>
                                    <td>₹<?php echo number_format($log["amount"], 2); ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo $log["status"]; ?>">
                                            <?php echo ucfirst($log["status"]); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date("M d, Y H:i", strtotime($log["created_at"])); ?></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                        <?php else: ?>
                        <div class="empty-state">
                            <svg class="empty-state-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <p>No payment logs yet</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        function togglePassword(id) {
            const input = document.getElementById(id);
            if (input.type === 'password') {
                input.type = 'text';
            } else {
                input.type = 'password';
            }
        }
    </script>
</body>
</html>
<?php $conn->close(); ?>