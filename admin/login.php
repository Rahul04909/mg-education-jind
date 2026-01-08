<?php
session_start();
require_once __DIR__ . '/../database/db-config.php';

if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: index.php");
    exit;
}

$conn = getDbConnection();
$error_message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = mysqli_real_escape_string($conn, trim($_POST['username']));
    $password = $_POST['password'];

    $sql = "SELECT * FROM admin_users WHERE username = '$username'";
    $result = $conn->query($sql);

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        // Verify Password
        if (password_verify($password, $row['password'])) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $row['id'];
            $_SESSION['admin_name'] = $row['full_name'];
            $_SESSION['admin_role'] = $row['role'];
            header("Location: index.php");
            exit;
        } else {
            $error_message = "Invalid Password.";
        }
    } else {
        $error_message = "Invalid Username.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - MG Skill</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Outfit', sans-serif; background-color: #f3f4f6; }
        .login-wrapper { min-height: 100vh; display: grid; grid-template-columns: 1fr; }
        @media (min-width: 1024px) {
            .login-wrapper { grid-template-columns: 1.2fr 1fr; }
        }
        .login-image { 
            background: linear-gradient(135deg, #4f46e5 0%, #3730a3 100%);
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px;
        }
        .login-image::after {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: radial-gradient(circle at 50% 50%, rgba(255,255,255,0.1) 0%, transparent 60%);
        }
        .login-form-container {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px;
            background: white;
        }
        .login-card { width: 100%; max-width: 450px; }
        .form-input-group { margin-bottom: 20px; }
        .password-input-wrapper { position: relative; }
        .form-input { 
            width: 100%; 
            padding: 14px 16px; 
            border: 2px solid #e5e7eb; 
            border-radius: 12px; 
            font-size: 15px; 
            transition: all 0.3s ease;
            outline: none;
            background: #f9fafb;
        }
        .form-input:focus {
            border-color: #4f46e5;
            background: #fff;
            box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
        }
        .input-icon {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            cursor: pointer;
            transition: color 0.2s;
        }
        .input-icon:hover { color: #4f46e5; }
        .btn-primary {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            font-size: 16px;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(79, 70, 229, 0.2);
        }
        .logo-area { text-align: center; margin-bottom: 40px; }
        .admin-badge {
            display: inline-block;
            padding: 6px 16px;
            background: #eef2ff;
            color: #4f46e5;
            border-radius: 99px;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 16px;
        }
    </style>
</head>
<body>

<div class="login-wrapper">
    <!-- Left Column: Branding -->
    <div class="login-image hidden lg:flex">
        <div class="text-white z-10 max-w-lg text-center">
            <div style="font-size:80px; margin-bottom:20px;">🛡️</div>
            <h1 class="text-4xl font-bold mb-6">MG Skill Administration</h1>
            <p class="text-lg opacity-80 leading-relaxed">Secure access for platform administrators. Manage students, centers, courses, and content from a centralized command center.</p>
        </div>
    </div>

    <!-- Right Column: Login Form -->
    <div class="login-form-container">
        <div class="login-card">
            <div class="logo-area">
                <div class="admin-badge">ADMINISTRATION PORTAL</div>
                <h3 class="text-3xl font-bold text-gray-900">Welcome Back</h3>
                <p class="text-gray-500 mt-2">Please sign in to access your dashboard.</p>
            </div>

            <?php if ($error_message): ?>
                <div class="bg-red-50 text-red-600 p-4 rounded-xl mb-6 flex items-center gap-3 border border-red-100 text-sm font-medium">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-input-group">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Username</label>
                    <input type="text" name="username" class="form-input" placeholder="Enter username" required>
                </div>

                <div class="form-input-group">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Password</label>
                    <div class="password-input-wrapper">
                        <input type="password" name="password" id="password" class="form-input" placeholder="Enter password" required>
                        <div class="input-icon" onclick="togglePassword()">
                            <svg id="eye-open" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display:none"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                            <svg id="eye-closed" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path></svg>
                        </div>
                    </div>
                </div>

                <div class="flex justify-between items-center mb-6">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" class="w-4 h-4 text-indigo-600 rounded border-gray-300 focus:ring-indigo-500">
                        <span class="text-sm text-gray-500">Keep me logged in</span>
                    </label>
                </div>

                <button type="submit" class="btn-primary">Secure Login</button>
            </form>
            
            <div class="mt-8 text-center text-sm text-gray-400">
                <p>&copy; <?php echo date('Y'); ?> MG Education & Social Development Organisation</p>
            </div>
        </div>
    </div>
</div>

<script>
    function togglePassword() {
        const passwordInput = document.getElementById('password');
        const eyeOpen = document.getElementById('eye-open');
        const eyeClosed = document.getElementById('eye-closed');
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            eyeOpen.style.display = 'block';
            eyeClosed.style.display = 'none';
        } else {
            passwordInput.type = 'password';
            eyeOpen.style.display = 'none';
            eyeClosed.style.display = 'block';
        }
    }
</script>

</body>
</html>
<?php $conn->close(); ?>
