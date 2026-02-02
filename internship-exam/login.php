<?php
session_start();
require_once __DIR__ . '/../database/db-config.php';

$conn = getDbConnection();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = mysqli_real_escape_string($conn, $_POST['identifier']);
    $password = $_POST['password'];

    // Check in internship_enrollments
    $sql = "SELECT * FROM internship_enrollments WHERE enrollment_no = '$identifier' OR email = '$identifier' LIMIT 1";
    $res = $conn->query($sql);

    if ($res && $res->num_rows > 0) {
        $student = $res->fetch_assoc();
        // Verify Password
        if (password_verify($password, $student['password'])) {
            $_SESSION['student_id'] = $student['id'];
            $_SESSION['student_name'] = $student['full_name'];
            $_SESSION['enrollment_no'] = $student['enrollment_no'];
            $_SESSION['is_internship'] = true;
            
            header("Location: index.php");
            exit;
        } else {
            $error = "Invalid Password.";
        }
    } else {
        $error = "Student not found with this ID.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Internship Exam Login - MG Skills</title>
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
            background: linear-gradient(135deg, var(--primary) 0%, #4338ca 100%);
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

        .login-form-wrap {
            flex: 1;
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .brand { display: flex; align-items: center; justify-content: center; margin-bottom: 30px; }
        .brand img { height: 80px; border-radius: 8px; }

        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-size: 13px; font-weight: 600; color: #64748b; margin-bottom: 8px; }
        .inp-group { position: relative; }
        .inp-group svg { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8; width: 18px; height: 18px; }
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

        .alert { padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; }
        .error { background: #fee2e2; color: #991b1b; }
        
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
            <h2>Internship Exam Portal</h2>
            <p>Welcome, Future Professionals! Login to access your internship assessments and track your progress.</p>
        </div>

        <!-- Right Side -->
        <div class="login-form-wrap">
            <div class="brand">
                <img src="../assets/images/sidebar-logo.jpg" alt="Logo">
            </div>

            <?php if ($error): ?>
                <div class="alert error"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST">
                
                <div class="form-group">
                    <label>Enrollment No / Email</label>
                    <div class="inp-group">
                        <i data-lucide="user"></i>
                        <input type="text" name="identifier" placeholder="Enter Enrollment No or Email" required>
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
        </div>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
