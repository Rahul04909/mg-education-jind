<?php
session_start();
require_once __DIR__ . '/../database/db-config.php';

if (isset($_SESSION['reception_id'])) {
    header("Location: index.php");
    exit;
}

$conn = getDbConnection();
$error_message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    $sql = "SELECT * FROM receptions WHERE username = '$username' AND is_active = 1";
    $result = $conn->query($sql);

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        // Verify Password
        if (password_verify($password, $row['password'])) {
            $_SESSION['reception_id'] = $row['id'];
            $_SESSION['reception_name'] = $row['name'];
            $_SESSION['reception_username'] = $row['username'];
            header("Location: index.php");
            exit;
        } else {
            $error_message = "Invalid Password.";
        }
    } else {
        $error_message = "Invalid Username or Account Inactive.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reception Login - MG Skills</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Outfit', sans-serif; background-color: #fce7f3; /* Pink-100 */ }
        .login-wrapper { min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }
        
        .login-card { 
            width: 100%; max-width: 400px;
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 25px -5px rgba(236, 72, 153, 0.2);
            border: 1px solid #fbcfe8;
        }
        
        .logo-area { text-align: center; margin-bottom: 30px; }
        .logo-area img { height: 80px; margin: 0 auto 15px; display: block; border-radius: 10px; }
        .brand-title { color: #be185d; font-weight: 700; font-size: 20px; }
        
        .form-input-group { margin-bottom: 20px; }
        .form-input { 
            width: 100%; padding: 12px 16px; 
            border: 2px solid #e5e7eb; border-radius: 10px; 
            font-size: 15px; outline: none; transition: all 0.2s;
        }
        .form-input:focus { border-color: #ec4899; box-shadow: 0 0 0 3px rgba(236, 72, 153, 0.1); }
        
        .btn-primary {
            width: 100%; padding: 14px;
            background: linear-gradient(135deg, #ec4899 0%, #db2777 100%);
            color: white; border: none; border-radius: 10px;
            font-weight: 600; font-size: 16px; cursor: pointer;
            transition: transform 0.2s;
        }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(236, 72, 153, 0.3); }
    </style>
</head>
<body>

<div class="login-wrapper">
    <div class="login-card">
        <div class="logo-area">
            <img src="../assets/images/sidebar-logo.jpg" alt="Logo" onerror="this.src='https://via.placeholder.com/150x80?text=MG+Skills'">
            <div class="brand-title">Reception Portal</div>
            <p class="text-gray-500 text-sm mt-2">Sign in to manage visitors & enquiries</p>
        </div>

        <?php if ($error_message): ?>
            <div class="bg-red-50 text-red-600 p-3 rounded-lg mb-6 text-sm flex items-center gap-2 border border-red-100">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
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
                <input type="password" name="password" class="form-input" placeholder="Enter password" required>
            </div>

            <div class="flex justify-between items-center mb-6">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" class="w-4 h-4 text-pink-600 rounded border-gray-300 focus:ring-pink-500">
                    <span class="text-sm text-gray-500">Remember me</span>
                </label>
            </div>

            <button type="submit" class="btn-primary">Sign In</button>
        </form>
    </div>
</div>

</body>
</html>
<?php $conn->close(); ?>
