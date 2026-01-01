<?php
session_start();
require_once __DIR__ . '/../database/db-config.php';
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$conn = getDbConnection();
$error = '';
$success = '';

// Handle AJAX/Post Requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // 1. Send OTP
    if ($action === 'send_otp') {
        header('Content-Type: application/json');
        $identifier = mysqli_real_escape_string($conn, $_POST['identifier']); // Email or Enrollment

        // Find student
        $sql = "SELECT id, full_name, email FROM admissions WHERE email = '$identifier' OR enrollment_no = '$identifier' OR mobile = '$identifier' LIMIT 1";
        $res = $conn->query($sql);

        if ($res && $res->num_rows > 0) {
            $student = $res->fetch_assoc();
            $email = $student['email'];
            $full_name = $student['full_name'];
            
            // Generate OTP
            $otp = rand(100000, 999999);
            $expiry = date('Y-m-d H:i:s', strtotime('+10 minutes'));

            // Update DB
            $upd = "UPDATE admissions SET otp = '$otp', otp_expiry = '$expiry' WHERE id = " . $student['id'];
            $conn->query($upd);

            // Send Email
            // Fetch SMTP
            $smtp_sql = "SELECT * FROM smtp_settings WHERE id = 1 AND is_active = 1";
            $smtp_res = $conn->query($smtp_sql);
            
            if ($smtp_res->num_rows > 0) {
                $smtp = $smtp_res->fetch_assoc();
                $mail = new PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Host = $smtp['smtp_host'];
                    $mail->SMTPAuth = true;
                    $mail->Username = $smtp['smtp_username'];
                    $mail->Password = $smtp['smtp_password'];
                    $mail->SMTPSecure = $smtp['smtp_encryption'];
                    $mail->Port = $smtp['smtp_port'];

                    $mail->setFrom($smtp['from_email'], $smtp['from_name']);
                    $mail->addAddress($email, $full_name);
                    $mail->isHTML(true);
                    $mail->Subject = "Your Login OTP - MG Skills";
                    $mail->Body = "
                        <div style='font-family: sans-serif; padding: 20px; border: 1px solid #ddd; max-width: 500px;'>
                            <h2 style='color: #6366f1;'>Online Exam Login OTP</h2>
                            <p>Hello <strong>$full_name</strong>,</p>
                            <p>Your One Time Password (OTP) for login is:</p>
                            <h1 style='background: #f3f4f6; padding: 10px; text-align: center; letter-spacing: 5px; color: #333;'>$otp</h1>
                            <p>This OTP is valid for 10 minutes.</p>
                        </div>
                    ";
                    $mail->send();
                    echo json_encode(['status' => 'success', 'message' => 'OTP sent to your registered email.']);
                } catch (Exception $e) {
                    echo json_encode(['status' => 'error', 'message' => 'Failed to send OTP email.']);
                }
            } else {
                 echo json_encode(['status' => 'error', 'message' => 'SMTP settings not configured.']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Student not found with this ID.']);
        }
        exit;
    }

    // 2. Verify OTP Login
    if ($action === 'login_otp') {
        $identifier = mysqli_real_escape_string($conn, $_POST['identifier']);
        $otp_input = mysqli_real_escape_string($conn, $_POST['otp']);

        $sql = "SELECT * FROM admissions WHERE (email = '$identifier' OR enrollment_no = '$identifier' OR mobile = '$identifier') AND otp = '$otp_input' AND otp_expiry > NOW() LIMIT 1";
        $res = $conn->query($sql);

        if ($res && $res->num_rows > 0) {
            $student = $res->fetch_assoc();
            $_SESSION['student_id'] = $student['id'];
            $_SESSION['student_name'] = $student['full_name'];
            $_SESSION['enrollment_no'] = $student['enrollment_no'];
            
            // Clear OTP
            $conn->query("UPDATE admissions SET otp = NULL, otp_expiry = NULL WHERE id = " . $student['id']);
            
            header("Location: index.php");
            exit;
        } else {
            $error = "Invalid or Expired OTP.";
        }
    }

    // 3. Password Login
    if ($action === 'login_password') {
        $identifier = mysqli_real_escape_string($conn, $_POST['identifier']);
        $password = $_POST['password'];

        $sql = "SELECT * FROM admissions WHERE email = '$identifier' OR enrollment_no = '$identifier' OR mobile = '$identifier' LIMIT 1";
        $res = $conn->query($sql);

        if ($res && $res->num_rows > 0) {
            $student = $res->fetch_assoc();
            // Verify hash (or plain text if legacy, but ideally hash)
            if (password_verify($password, $student['password'])) {
                $_SESSION['student_id'] = $student['id'];
                $_SESSION['student_name'] = $student['full_name'];
                $_SESSION['enrollment_no'] = $student['enrollment_no'];
                header("Location: index.php");
                exit;
            } else {
                $error = "Invalid Password.";
            }
        } else {
            $error = "Student not found.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Login - Online Exam</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        :root { --primary: #6366f1; --primary-hover: #4f46e5; --bg: #f8fafc; --text: #0f172a; --border: #e2e8f0; }
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family: 'Outfit', sans-serif; background: var(--bg); color: var(--text); min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        
        .login-container {
            width: 100%;
            max-width: 900px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px -10px rgba(0,0,0,0.1);
            overflow: hidden;
            display: flex;
            min-height: 550px;
        }

        .login-visual {
            flex: 1;
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            color: white;
            position: relative;
        }
        .login-visual h2 { font-size: 32px; font-weight: 700; margin-bottom: 15px; }
        .login-visual p { font-size: 16px; opacity: 0.9; line-height: 1.6; }
        .circles { position: absolute; width: 200px; height: 200px; background: rgba(255,255,255,0.1); border-radius: 50%; top: -50px; left: -50px; }
        .circles::after { content:''; position: absolute; width: 100px; height: 100px; background: rgba(255,255,255,0.1); border-radius: 50%; bottom: -20px; right: -20px; }

        .login-form-wrap {
            flex: 1;
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .brand { display: flex; align-items: center; gap: 10px; margin-bottom: 30px; }
        .brand img { height: 40px; border-radius: 8px; }
        .brand span { font-size: 18px; font-weight: 700; color: var(--primary); }

        .auth-tabs {
            display: flex;
            background: #f1f5f9;
            padding: 4px;
            border-radius: 10px;
            margin-bottom: 25px;
        }
        .tab-btn {
            flex: 1;
            padding: 10px;
            border: none;
            background: transparent;
            font-weight: 600;
            color: #64748b;
            cursor: pointer;
            border-radius: 8px;
            transition: 0.3s;
        }
        .tab-btn.active {
            background: white;
            color: var(--primary);
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }

        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-size: 13px; font-weight: 600; color: #64748b; margin-bottom: 8px; }
        .inp-group { position: relative; }
        .inp-group i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8; width: 18px; }
        .inp-group input { 
            width: 100%; padding: 12px 12px 12px 40px; border: 1px solid var(--border); border-radius: 10px; font-size: 14px; transition: 0.2s;
            font-family: inherit;
        }
        .inp-group input:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1); }

        .btn-primary {
            width: 100%;
            padding: 12px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            font-size: 15px;
            cursor: pointer;
            transition: 0.2s;
            margin-top: 10px;
        }
        .btn-primary:hover { background: var(--primary-hover); transform: translateY(-1px); }
        .btn-primary:active { transform: translateY(0); }

        .otp-section { display: none; }
        .otp-section.active { display: block; }
        
        .send-otp-btn {
            color: var(--primary);
            background: none;
            border: none;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            float: right;
            margin-bottom: 5px;
        }
        .send-otp-btn:hover { text-decoration: underline; }

        .alert { padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; }
        .error { background: #fee2e2; color: #991b1b; }
        .success { background: #dcfce7; color: #166534; }
        
        @media (max-width: 768px) {
            .login-container { flex-direction: column; max-width: 400px; margin: 20px; min-height: auto; }
            .login-visual { padding: 30px; }
            .login-form-wrap { padding: 30px; }
        }
    </style>
</head>
<body>

    <div class="login-container">
        <!-- Left Side -->
        <div class="login-visual">
            <div class="circles"></div>
            <h2>Student Exam Portal</h2>
            <p>Access your scheduled exams, view results, and manage your academic performance securely.</p>
        </div>

        <!-- Right Side -->
        <div class="login-form-wrap">
            <div class="brand">
                <img src="../assets/images/logo.png" alt="Logo"> <!-- Placeholder logo path -->
                <span>MG Skills</span>
            </div>

            <?php if ($error): ?>
                <div class="alert error"><?php echo $error; ?></div>
            <?php endif; ?>

            <!-- Tabs -->
            <div class="auth-tabs">
                <button class="tab-btn active" onclick="switchTab('password')">Password Login</button>
                <button class="tab-btn" onclick="switchTab('otp')">OTP Login</button>
            </div>

            <!-- Password Form -->
            <form id="password-form" method="POST">
                <input type="hidden" name="action" value="login_password">
                
                <div class="form-group">
                    <label>Enrollment / Email / Mobile</label>
                    <div class="inp-group">
                        <i data-lucide="user"></i>
                        <input type="text" name="identifier" placeholder="Enter ID, Email or Mobile" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Password</label>
                    <div class="inp-group">
                        <i data-lucide="lock"></i>
                        <input type="password" name="password" placeholder="Enter your password" required>
                    </div>
                </div>

                <button type="submit" class="btn-primary">Login Now</button>
            </form>

            <!-- OTP Form -->
            <form id="otp-form" method="POST" style="display:none;">
                <input type="hidden" name="action" value="login_otp">
                
                <div class="form-group">
                    <label>Enrollment / Email / Mobile</label>
                    <div class="inp-group">
                        <i data-lucide="mail"></i>
                        <input type="text" id="otp-identifier" name="identifier" placeholder="Enter ID, Email or Mobile" required>
                    </div>
                </div>

                <div class="form-group otp-input-group" style="display: none;">
                     <div style="display:flex; justify-content:space-between; align-items:center;">
                        <label>Enter OTP</label>
                        <button type="button" class="send-otp-btn" onclick="sendOtp()">Resend OTP</button>
                    </div>
                    <div class="inp-group">
                        <i data-lucide="key"></i>
                        <input type="text" name="otp" placeholder="Enter 6-digit OTP">
                    </div>
                    <button type="submit" class="btn-primary" style="margin-top:20px;">Verify & Login</button>
                </div>
                
                <button type="button" class="btn-primary" id="get-otp-btn" onclick="sendOtp()">Get OTP</button>
            </form>
        </div>
    </div>

    <script>
        lucide.createIcons();

        function switchTab(mode) {
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            if(mode === 'password') {
                document.querySelector('.tab-btn:nth-child(1)').classList.add('active');
                document.getElementById('password-form').style.display = 'block';
                document.getElementById('otp-form').style.display = 'none';
            } else {
                document.querySelector('.tab-btn:nth-child(2)').classList.add('active');
                document.getElementById('password-form').style.display = 'none';
                document.getElementById('otp-form').style.display = 'block';
            }
        }

        function sendOtp() {
            const identifier = document.getElementById('otp-identifier').value;
            const btn = document.getElementById('get-otp-btn');
            
            if(!identifier) {
                alert("Please enter your Enrollment No, Email or Mobile.");
                return;
            }

            btn.innerText = "Sending...";
            btn.disabled = true;

            const formData = new FormData();
            formData.append('action', 'send_otp');
            formData.append('identifier', identifier);

            fetch('login.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if(data.status === 'success') {
                    alert(data.message);
                    document.querySelector('.otp-input-group').style.display = 'block';
                    btn.style.display = 'none'; // Hide Get OTP Button
                } else {
                    alert(data.message);
                    btn.innerText = "Get OTP";
                    btn.disabled = false;
                }
            })
            .catch(err => {
                console.error(err);
                alert("Something went wrong.");
                btn.innerText = "Get OTP";
                btn.disabled = false;
            });
        }
    </script>
</body>
</html>
