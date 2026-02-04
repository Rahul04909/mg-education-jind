<?php
session_start();
require_once __DIR__ . '/../../database/db-config.php';

// Auth Check
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit;
}

$conn = getDbConnection();
$admin_id = $_SESSION['admin_id'];
$success_msg = "";
$error_msg = "";

// Fetch Current Admin Data
$stmt = $conn->prepare("SELECT username, email, full_name, role, password FROM admin_users WHERE id = ?");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    die("Admin user not found.");
}
$admin = $result->fetch_assoc();
$stmt->close();

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Update Profile Details
    if (isset($_POST['update_profile'])) {
        $full_name = trim($_POST['full_name']);
        $email = trim($_POST['email']);
        
        if (empty($full_name) || empty($email)) {
            $error_msg = "Name and Email are required.";
        } else {
            // Update
            $update_sql = "UPDATE admin_users SET full_name = ?, email = ? WHERE id = ?";
            $ustmt = $conn->prepare($update_sql);
            $ustmt->bind_param("ssi", $full_name, $email, $admin_id);
            if ($ustmt->execute()) {
                $success_msg = "Profile updated successfully.";
                // Refresh data
                $admin['full_name'] = $full_name;
                $admin['email'] = $email;
                // Update Session
                $_SESSION['admin_name'] = $full_name;
            } else {
                $error_msg = "Error updating profile: " . $conn->error;
            }
            $ustmt->close();
        }
    }

    // Change Password
    if (isset($_POST['change_password'])) {
        $current_pass = $_POST['current_password'];
        $new_pass = $_POST['new_password'];
        $confirm_pass = $_POST['confirm_password'];

        if (empty($current_pass) || empty($new_pass) || empty($confirm_pass)) {
            $error_msg = "All password fields are required.";
        } elseif ($new_pass !== $confirm_pass) {
            $error_msg = "New password and confirm password do not match.";
        } else {
            // Verify Old Password
            if (password_verify($current_pass, $admin['password'])) {
                // Hash New Password
                $new_hashed = password_hash($new_pass, PASSWORD_BCRYPT);
                
                $pwd_sql = "UPDATE admin_users SET password = ? WHERE id = ?";
                $pstmt = $conn->prepare($pwd_sql);
                $pstmt->bind_param("si", $new_hashed, $admin_id);
                if ($pstmt->execute()) {
                    $success_msg = "Password changed successfully.";
                    // Refresh current password hash in memory if needed, though page reload handles it
                    $admin['password'] = $new_hashed; 
                } else {
                    $error_msg = "Error changing password.";
                }
                $pstmt->close();
            } else {
                $error_msg = "Incorrect current password.";
            }
        }
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Admin Panel</title>
    <style>
        :root{--active:#22c55e;--indigo:#4f46e5;--indigo-hover:#4338ca; --line:#e2e8f0;--text:#0f172a;--muted:#64748b;--bg:#f8fafc;--white:#ffffff;--radius:12px;--shadow:0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);}
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:'Outfit', system-ui, -apple-system, sans-serif; background:var(--bg); color:var(--text);}
        .admin-content{margin-left:260px; padding:30px; transition: margin-left 0.3s ease;}
        body.sidebar-collapsed .admin-content{margin-left:80px;}
        .admin-wrap{max-width:1000px; margin:0 auto;}
        
        .page-header{margin-bottom:30px; display:flex; justify-content:space-between; align-items:center;}
        .page-title{font-size:24px; font-weight:700; color:var(--text);}
        .breadcrumb{font-size:14px; color:var(--muted);}
        
        .alert{padding:16px; border-radius:var(--radius); margin-bottom:20px; font-size:14px; font-weight:500;}
        .alert-success{background:#dcfce7; color:#166534; border:1px solid #bbf7d0;}
        .alert-error{background:#fee2e2; color:#991b1b; border:1px solid #fecaca;}

        .grid-layout { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; }
        
        .card { background:var(--white); padding:30px; border-radius:var(--radius); box-shadow:var(--shadow); border:1px solid var(--line); }
        .card-header { margin-bottom:24px; border-bottom:1px solid var(--line); padding-bottom:16px; }
        .card-title { font-size:18px; font-weight:600; color:var(--indigo); display:flex; align-items:center; gap:10px; }
        
        .form-group { margin-bottom:20px; }
        .form-label { display:block; font-size:14px; font-weight:500; color:var(--text); margin-bottom:8px; }
        .form-input { width:100%; padding:10px 14px; border:1px solid var(--line); border-radius:8px; font-size:14px; transition:all 0.2s; outline:none; color:var(--text); }
        .form-input:focus { border-color:var(--indigo); box-shadow:0 0 0 3px #eef2ff; }
        .form-input[readonly] { background:#f1f5f9; cursor:not-allowed; color:var(--muted); }
        
        .btn { display:inline-flex; align-items:center; justify-content:center; padding:10px 20px; background:var(--indigo); color:white; border:none; border-radius:8px; font-weight:500; cursor:pointer; font-size:14px; transition:all 0.2s; }
        .btn:hover { background:var(--indigo-hover); }

        .role-badge { display:inline-block; padding:4px 10px; background:#e0e7ff; color:#3730a3; border-radius:20px; font-size:12px; font-weight:600; text-transform:uppercase; margin-bottom:20px;}

        @media (max-width: 900px) {
            .grid-layout { grid-template-columns: 1fr; }
            .admin-content { margin-left:0; padding:20px; }
        }
    </style>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php include __DIR__ . '/../sidebar.php'; ?>
    
    <main class="admin-content">
        <div class="admin-wrap">
            <div class="page-header">
                <div>
                    <div class="breadcrumb">Dashboard / Settings</div>
                    <h1 class="page-title">My Profile</h1>
                </div>
            </div>

            <?php if ($success_msg): ?>
                <div class="alert alert-success"><?php echo $success_msg; ?></div>
            <?php endif; ?>
            <?php if ($error_msg): ?>
                <div class="alert alert-error"><?php echo $error_msg; ?></div>
            <?php endif; ?>

            <div class="grid-layout">
                <!-- Profile Details -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                            Profile Details
                        </h2>
                    </div>
                    
                    <div style="text-align:center;">
                        <span class="role-badge"><?php echo htmlspecialchars($admin['role']); ?></span>
                    </div>

                    <form method="POST">
                        <div class="form-group">
                            <label class="form-label">Username</label>
                            <input type="text" class="form-input" value="<?php echo htmlspecialchars($admin['username']); ?>" readonly>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Full Name</label>
                            <input type="text" name="full_name" class="form-input" value="<?php echo htmlspecialchars($admin['full_name']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Email Address</label>
                            <input type="email" name="email" class="form-input" value="<?php echo htmlspecialchars($admin['email']); ?>" required>
                        </div>
                        <div style="text-align:right;">
                            <button type="submit" name="update_profile" class="btn">Update Profile</button>
                        </div>
                    </form>
                </div>

                <!-- Security -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                            Security Settings
                        </h2>
                    </div>
                    <form method="POST">
                        <div class="form-group">
                            <label class="form-label">Current Password</label>
                            <input type="password" name="current_password" class="form-input" placeholder="Enter current password" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">New Password</label>
                            <input type="password" name="new_password" class="form-input" placeholder="Enter new password" required minlength="6">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Confirm New Password</label>
                            <input type="password" name="confirm_password" class="form-input" placeholder="Confirm new password" required>
                        </div>
                        <div style="text-align:right;">
                            <button type="submit" name="change_password" class="btn">Change Password</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
