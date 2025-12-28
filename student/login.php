<?php
session_start();
require_once __DIR__ . '/../database/db-config.php';

if (isset($_SESSION['student_id'])) {
    header("Location: index.php");
    exit;
}

$conn = getDbConnection();
$error_message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $enrollment_no = mysqli_real_escape_string($conn, trim($_POST['enrollment_no']));
    $password = $_POST['password'];

    $sql = "SELECT * FROM admissions WHERE enrollment_no = '$enrollment_no'";
    $result = $conn->query($sql);

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        // Verify Password
        if (password_verify($password, $row['password'])) {
            $_SESSION['student_id'] = $row['id'];
            $_SESSION['student_name'] = $row['full_name'];
            $_SESSION['enrollment_no'] = $row['enrollment_no'];
            header("Location: index.php");
            exit;
        } else {
            $error_message = "Invalid Password.";
        }
    } else {
        $error_message = "Enrollment Number not found. Please contact Admin.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Login - MG Skills</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Outfit', sans-serif; background-color: #f3f4f6; }
        .login-wrapper { min-height: 100vh; display: grid; grid-template-columns: 1fr; }
        @media (min-width: 1024px) {
            .login-wrapper { grid-template-columns: 1.2fr 1fr; }
        }
        .login-image { 
            background: url('../assets/images/banner-1.png') no-repeat center center; 
            background-size: cover; 
            position: relative;
        }
        .login-image::after {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: linear-gradient(to right, rgba(0,0,0,0.3), rgba(0,0,0,0.6));
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
            border-color: #6366f1;
            background: #fff;
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
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
        .input-icon:hover { color: #6366f1; }
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
        .logo-area { text-align: center; margin-bottom: 30px; }
        .logo-area img { height: 100px; margin: 0 auto; display: block; }
    </style>
</head>
<body>

<div class="login-wrapper">
    <!-- Left Column: Image -->
    <div class="login-image hidden lg:block">
        <div class="absolute bottom-10 left-10 text-white z-10 max-w-lg">
            <h1 class="text-4xl font-bold mb-4">Student Portal - MG Skills</h1>
            <p class="text-lg opacity-90">Access your course materials, results, and certificates.</p>
        </div>
    </div>

    <!-- Right Column: Login Form -->
    <div class="login-form-container">
        <div class="login-card">
            <div class="logo-area">
                <img src="../../assets/images/sidebar-logo.jpg" alt="MG Skills Logo" onerror="this.style.display='none';">
                <h3 class="text-2xl font-bold text-gray-800 mt-6">Student Login</h3>
                <p class="text-gray-500 mt-2">Welcome! Enter your credentials.</p>
            </div>

            <?php if ($error_message): ?>
                <div class="bg-red-50 text-red-600 p-4 rounded-xl mb-6 flex items-center gap-3 border border-red-100">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-input-group">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Enrollment Number</label>
                    <input type="text" name="enrollment_no" class="form-input" placeholder="e.g. MG20250001" required>
                </div>

                <div class="form-input-group">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Password</label>
                    <div class="password-input-wrapper">
                        <input type="password" name="password" id="password" class="form-input" placeholder="Enter your password" required>
                        <div class="input-icon" onclick="togglePassword()">
                            <svg id="eye-open" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display:none"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                            <svg id="eye-closed" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path></svg>
                        </div>
                    </div>
                </div>

                <div class="flex justify-between items-center mb-6">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" class="w-4 h-4 text-indigo-600 rounded border-gray-300 focus:ring-indigo-500">
                        <span class="text-sm text-gray-500">Remember for 30 days</span>
                    </label>
                </div>

                <button type="submit" class="btn-primary">Login</button>
            </form>
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
