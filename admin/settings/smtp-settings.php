<?php
// Include database configuration
require_once __DIR__ . '/../../database/db-config.php';

// Load PHPMailer
require_once __DIR__ . "/../../vendor/autoload.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Create database connection using our new function
$conn = getDbConnection();

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize variables
$success_message = "";
$error_message = "";
$test_email_result = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST["save_settings"])) {
        // Save SMTP settings
        $smtp_host = mysqli_real_escape_string($conn, $_POST["smtp_host"]);
        $smtp_port = intval($_POST["smtp_port"]);
        $smtp_username = mysqli_real_escape_string(
            $conn,
            $_POST["smtp_username"],
        );
        $smtp_password = mysqli_real_escape_string(
            $conn,
            $_POST["smtp_password"],
        );
        $smtp_encryption = mysqli_real_escape_string(
            $conn,
            $_POST["smtp_encryption"],
        );
        $from_email = mysqli_real_escape_string($conn, $_POST["from_email"]);
        $from_name = mysqli_real_escape_string($conn, $_POST["from_name"]);
        $is_active = isset($_POST["is_active"]) ? 1 : 0;

        // Check if settings exist
        $check_sql = "SELECT COUNT(*) as count FROM smtp_settings";
        $result = $conn->query($check_sql);
        $row = $result->fetch_assoc();

        if ($row["count"] > 0) {
            // Update existing settings
            $sql = "UPDATE smtp_settings SET
                    smtp_host = '$smtp_host',
                    smtp_port = $smtp_port,
                    smtp_username = '$smtp_username',
                    smtp_password = '$smtp_password',
                    smtp_encryption = '$smtp_encryption',
                    from_email = '$from_email',
                    from_name = '$from_name',
                    is_active = $is_active
                    WHERE id = 1";
        } else {
            // Insert new settings
            $sql = "INSERT INTO smtp_settings
                    (smtp_host, smtp_port, smtp_username, smtp_password, smtp_encryption, from_email, from_name, is_active)
                    VALUES ('$smtp_host', $smtp_port, '$smtp_username', '$smtp_password', '$smtp_encryption', '$from_email', '$from_name', $is_active)";
        }

        if ($conn->query($sql) === true) {
            $success_message = "SMTP settings saved successfully!";
        } else {
            $error_message = "Error saving settings: " . $conn->error;
        }
    } elseif (isset($_POST["send_test_email"])) {
        // Send test email
        // Get SMTP settings from database
        $settings_sql = "SELECT * FROM smtp_settings WHERE id = 1";
        $settings_result = $conn->query($settings_sql);

        if ($settings_result->num_rows > 0) {
            $settings = $settings_result->fetch_assoc();
            $test_email = mysqli_real_escape_string(
                $conn,
                $_POST["test_email"],
            );

            $mail = new PHPMailer(true);

            try {
                // Server settings
                $mail->isSMTP();
                $mail->Host = $settings["smtp_host"];
                $mail->SMTPAuth = true;
                $mail->Username = $settings["smtp_username"];
                $mail->Password = $settings["smtp_password"];
                $mail->SMTPSecure = $settings["smtp_encryption"];
                $mail->Port = $settings["smtp_port"];

                // Recipients
                $mail->setFrom($settings["from_email"], $settings["from_name"]);
                $mail->addAddress($test_email);

                // Content
                $mail->isHTML(true);
                $mail->Subject = "Test Email from MG Education Portal";
                $mail->Body =
                    '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background: #f8fafc;"><div style="background: #fff; border-radius: 12px; padding: 30px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);"><h2 style="color: #0b1020; margin-bottom: 20px;">SMTP Configuration Test</h2><p style="color: #4b5563; line-height: 1.6; margin-bottom: 15px;">Congratulations! Your SMTP configuration is working correctly.</p><p style="color: #4b5563; line-height: 1.6; margin-bottom: 15px;">This is a test email sent from your MG Education Portal to verify that your email settings are properly configured.</p><div style="background: #d1fae5; border-left: 4px solid #22c55e; padding: 15px; border-radius: 8px; margin: 20px 0;"><p style="color: #065f46; margin: 0; font-weight: 600;">✓ SMTP Connection Successful</p></div><p style="color: #6b7280; font-size: 13px; margin-top: 30px; padding-top: 20px; border-top: 1px solid #e5e7eb;">MG Education Portal | SMTP Configuration System</p></div></div>';
                $mail->AltBody =
                    "Congratulations! Your SMTP configuration is working correctly. This is a test email from MG Education Portal.";

                $mail->send();

                // Log successful email
                $log_sql = "INSERT INTO email_logs (to_email, subject, message, status) VALUES ('$test_email', 'Test Email from MG Education Portal', 'SMTP Configuration Test', 'success')";
                $conn->query($log_sql);

                $test_email_result = "success";
                $success_message =
                    "Test email sent successfully to " .
                    htmlspecialchars($test_email);
            } catch (Exception $e) {
                // Log failed email
                $error_msg = mysqli_real_escape_string($conn, $mail->ErrorInfo);
                $log_sql = "INSERT INTO email_logs (to_email, subject, message, status, error_message) VALUES ('$test_email', 'Test Email from MG Education Portal', 'SMTP Configuration Test', 'failed', '$error_msg')";
                $conn->query($log_sql);

                $test_email_result = "error";
                $error_message =
                    "Failed to send test email. Error: " . $mail->ErrorInfo;
            }
        } else {
            $error_message =
                "Please save SMTP settings before sending test email.";
        }
    }
}

// Fetch current SMTP settings
$settings_sql = "SELECT * FROM smtp_settings WHERE id = 1";
$settings_result = $conn->query($settings_sql);
$settings =
    $settings_result->num_rows > 0 ? $settings_result->fetch_assoc() : null;

// Fetch email logs
$logs_sql = "SELECT * FROM email_logs ORDER BY sent_at DESC LIMIT 10";
$logs_result = $conn->query($logs_sql);

include __DIR__ . "/../sidebar.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SMTP Settings - MG Education</title>
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
        .test-email-section{background:#f8fafc;border:2px dashed var(--line);border-radius:14px;padding:24px;margin-top:20px}
        .test-email-title{font-size:16px;font-weight:700;color:var(--text);margin-bottom:16px;display:flex;align-items:center;gap:8px}
        .logs-table{width:100%;border-collapse:collapse;margin-top:16px}
        .logs-table th{background:#f8fafc;padding:12px;text-align:left;font-weight:700;color:var(--text);font-size:13px;border-bottom:2px solid var(--line)}
        .logs-table td{padding:12px;border-bottom:1px solid var(--line);color:var(--muted);font-size:14px}
        .status-badge{display:inline-flex;align-items:center;gap:6px;padding:4px 12px;border-radius:999px;font-size:12px;font-weight:700}
        .status-success{background:#d1fae5;color:#065f46}
        .status-failed{background:#fee2e2;color:#991b1b}
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
                    <a href="../index.php">Dashboard</a> › <a href="#">Settings</a> › SMTP Configuration
                </div>
                <h1 class="page-title">
                    <svg width="32" height="32" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                    SMTP Settings
                </h1>
                <p class="page-subtitle">Configure your email server settings to enable email notifications</p>
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
                                SMTP Configuration
                            </h2>
                            <p class="card-subtitle">Configure your email server credentials and settings</p>
                        </div>

                        <div class="info-box">
                            <div class="info-box-title">💡 Popular SMTP Providers</div>
                            <div class="info-box-text">
                                <strong>Gmail:</strong> smtp.gmail.com | Port: 587 | Encryption: TLS<br>
                                <strong>Outlook:</strong> smtp-mail.outlook.com | Port: 587 | Encryption: TLS<br>
                                <strong>Yahoo:</strong> smtp.mail.yahoo.com | Port: 587 | Encryption: TLS<br>
                                <strong>SendGrid:</strong> smtp.sendgrid.net | Port: 587 | Encryption: TLS
                            </div>
                        </div>

                        <form method="POST" action="">
                            <div class="form-group">
                                <label class="form-label">SMTP Host *</label>
                                <input type="text" name="smtp_host" class="form-input"
                                       value="<?php echo $settings
                                           ? htmlspecialchars(
                                               $settings["smtp_host"],
                                           )
                                           : "smtp.gmail.com"; ?>"
                                       placeholder="smtp.gmail.com" required>
                                <div class="form-help">Your SMTP server hostname</div>
                            </div>

                            <div class="grid-2">
                                <div class="form-group">
                                    <label class="form-label">SMTP Port *</label>
                                    <input type="number" name="smtp_port" class="form-input"
                                           value="<?php echo $settings
                                               ? $settings["smtp_port"]
                                               : "587"; ?>"
                                           placeholder="587" required>
                                    <div class="form-help">Usually 587 (TLS) or 465 (SSL)</div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Encryption *</label>
                                    <select name="smtp_encryption" class="form-select" required>
                                        <option value="tls" <?php echo $settings &&
                                        $settings["smtp_encryption"] === "tls"
                                            ? "selected"
                                            : ""; ?>>TLS</option>
                                        <option value="ssl" <?php echo $settings &&
                                        $settings["smtp_encryption"] === "ssl"
                                            ? "selected"
                                            : ""; ?>>SSL</option>
                                        <option value="none" <?php echo $settings &&
                                        $settings["smtp_encryption"] === "none"
                                            ? "selected"
                                            : ""; ?>>None</option>
                                    </select>
                                    <div class="form-help">Security protocol</div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">SMTP Username *</label>
                                <input type="text" name="smtp_username" class="form-input"
                                       value="<?php echo $settings
                                           ? htmlspecialchars(
                                               $settings["smtp_username"],
                                           )
                                           : ""; ?>"
                                       placeholder="your-email@example.com" required>
                                <div class="form-help">Your SMTP account email address</div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">SMTP Password *</label>
                                <div class="password-toggle">
                                    <input type="password" name="smtp_password" id="smtpPassword" class="form-input"
                                           value="<?php echo $settings
                                               ? htmlspecialchars(
                                                   $settings["smtp_password"],
                                               )
                                               : ""; ?>"
                                           placeholder="••••••••••" required>
                                    <button type="button" class="password-toggle-btn" onclick="togglePassword('smtpPassword')">
                                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </button>
                                </div>
                                <div class="form-help">Your SMTP account password or app-specific password</div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">From Email *</label>
                                <input type="email" name="from_email" class="form-input"
                                       value="<?php echo $settings
                                           ? htmlspecialchars(
                                               $settings["from_email"],
                                           )
                                           : "noreply@mgedu.com"; ?>"
                                       placeholder="noreply@mgedu.com" required>
                                <div class="form-help">Email address that will appear in the "From" field</div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">From Name *</label>
                                <input type="text" name="from_name" class="form-input"
                                       value="<?php echo $settings
                                           ? htmlspecialchars(
                                               $settings["from_name"],
                                           )
                                           : "MG Education"; ?>"
                                       placeholder="MG Education" required>
                                <div class="form-help">Name that will appear in the "From" field</div>
                            </div>

                            <div class="form-group">
                                <div class="checkbox-wrapper">
                                    <input type="checkbox" name="is_active" id="is_active" class="checkbox-input"
                                           <?php echo $settings &&
                                           $settings["is_active"]
                                               ? "checked"
                                               : ""; ?>>
                                    <label for="is_active" class="form-label" style="margin: 0;">Enable SMTP</label>
                                </div>
                                <div class="form-help">Toggle to enable or disable email sending</div>
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

                        <div class="test-email-section">
                            <div class="test-email-title">
                                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                                Send Test Email
                            </div>
                            <form method="POST" action="">
                                <div class="form-group">
                                    <label class="form-label">Test Email Address *</label>
                                    <input type="email" name="test_email" class="form-input"
                                           placeholder="test@example.com" required>
                                    <div class="form-help">Enter an email address to send a test email</div>
                                </div>
                                <button type="submit" name="send_test_email" class="btn btn-success">
                                    <svg class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                                    </svg>
                                    Send Test Email
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <div>
                    <div class="stat-card">
                        <div class="stat-label">Email Configuration Status</div>
                        <div class="stat-value"><?php echo $settings &&
                        $settings["is_active"]
                            ? "Active"
                            : "Inactive"; ?></div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title">
                                <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                Email Logs
                            </h2>
                            <p class="card-subtitle">Recent email sending history</p>
                        </div>

                        <?php if ($logs_result->num_rows > 0): ?>
                        <table class="logs-table">
                            <thead>
                                <tr>
                                    <th>To</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while (
                                    $log = $logs_result->fetch_assoc()
                                ): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars(
                                        $log["to_email"],
                                    ); ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo $log[
                                            "status"
                                        ]; ?>">
                                            <?php echo ucfirst(
                                                $log["status"],
                                            ); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date(
                                        "M d, Y H:i",
                                        strtotime($log["sent_at"]),
                                    ); ?></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                        <?php else: ?>
                        <div class="empty-state">
                            <svg class="empty-state-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                            <p>No email logs yet</p>
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
<?php $conn->close();
?>
