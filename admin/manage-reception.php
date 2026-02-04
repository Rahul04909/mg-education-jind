<?php
session_start();
require_once __DIR__ . '/../database/db-config.php';

// Auth check - assuming similar to other admin pages
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$conn = getDbConnection();
$message = "";
$error = "";

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'add') {
        $name = $_POST['name'];
        $username = $_POST['username'];
        $email = $_POST['email'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        
        // Check if username or email exists
        $check = $conn->query("SELECT id FROM receptions WHERE username = '$username' OR email = '$email'");
        if ($check->num_rows > 0) {
            $error = "Username or Email already exists.";
        } else {
            $stmt = $conn->prepare("INSERT INTO receptions (name, username, email, password) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $name, $username, $email, $password);
            if ($stmt->execute()) {
                $message = "Reception added successfully.";
            } else {
                $error = "Error adding reception: " . $conn->error;
            }
            $stmt->close();
        }
    } elseif (isset($_POST['action']) && $_POST['action'] === 'update_password') {
        $id = $_POST['id'];
        $password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
        
        $stmt = $conn->prepare("UPDATE receptions SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $password, $id);
        if ($stmt->execute()) {
            $message = "Password updated successfully.";
        } else {
            $error = "Error updating password.";
        }
        $stmt->close();
    } elseif (isset($_POST['action']) && $_POST['action'] === 'delete') {
        $id = $_POST['id'];
        $conn->query("DELETE FROM receptions WHERE id = $id");
        $message = "Reception deleted successfully.";
    }
}

// Fetch Receptions
$receptions = $conn->query("SELECT * FROM receptions ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Reception - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root{--primary:#4f46e5;--bg-body:#f1f5f9;--text-main:#0f172a;--text-light:#64748b;--border:#e2e8f0;--surface:#ffffff;}
        body{font-family:'Outfit',sans-serif;background-color:var(--bg-body);margin:0;padding:0;color:var(--text-main)}
        .admin-content{margin-left:260px;min-height:100vh;padding:32px;transition:margin-left .3s}
        body.sidebar-collapsed .admin-content{margin-left:80px}
        @media (max-width:900px){.admin-content{margin-left:80px;padding:20px}}
        
        .page-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:32px}
        .page-title h1{font-size:24px;font-weight:700;margin:0}
        
        .card{background:var(--surface);border-radius:16px;box-shadow:0 1px 3px rgba(0,0,0,0.1);padding:24px;margin-bottom:24px;}
        
        .form-grid{display:grid;grid-template-columns:repeat(auto-fit, minmax(200px, 1fr));gap:16px;margin-bottom:16px;}
        .form-group label{display:block;font-size:13px;font-weight:600;margin-bottom:6px;color:var(--text-light);}
        .form-control{width:100%;padding:10px;border:1px solid var(--border);border-radius:8px;font-family:inherit;box-sizing:border-box;}
        
        .btn{padding:10px 20px;border-radius:8px;font-weight:600;border:none;cursor:pointer;color:white;}
        .btn-primary{background:var(--primary);}
        .btn-danger{background:#ef4444;}
        
        table{width:100%;border-collapse:collapse;margin-top:16px;}
        th{text-align:left;padding:12px;background:#f8fafc;font-size:12px;text-transform:uppercase;color:var(--text-light);}
        td{padding:12px;border-bottom:1px solid var(--border);font-size:14px;}
        
        .alert{padding:12px;border-radius:8px;margin-bottom:16px;font-size:14px;}
        .alert-success{background:#dcfce7;color:#16a34a;}
        .alert-error{background:#fee2e2;color:#dc2626;}
        
        /* Modal for password reset */
        .modal{display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.5);align-items:center;justify-content:center;}
        .modal-content{background:white;padding:24px;border-radius:16px;width:100%;max-width:400px;}
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <main class="admin-content">
        <div class="page-header">
            <div>
                <div class="page-title"><h1>Manage Reception</h1></div>
                <div style="font-size:14px;color:var(--text-light)">Create and manage reception accounts</div>
            </div>
        </div>

        <?php if($message): ?> <div class="alert alert-success"><?php echo $message; ?></div> <?php endif; ?>
        <?php if($error): ?> <div class="alert alert-error"><?php echo $error; ?></div> <?php endif; ?>

        <div class="card">
            <h3>Add New Reception</h3>
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Create Account</button>
            </form>
        </div>

        <div class="card">
            <h3>Existing Accounts</h3>
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $receptions->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo htmlspecialchars($row['username']); ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                        <td>
                            <button class="btn btn-primary" style="padding:6px 12px;font-size:12px;" onclick="openPasswordModal(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['name']); ?>')">Change Password</button>
                            <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure?');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                <button type="submit" class="btn btn-danger" style="padding:6px 12px;font-size:12px;">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </main>
    
    <div id="passwordModal" class="modal">
        <div class="modal-content">
            <h3 style="margin-top:0">Change Password for <span id="modalName"></span></h3>
            <form method="POST">
                <input type="hidden" name="action" value="update_password">
                <input type="hidden" name="id" id="modalId">
                <div class="form-group" style="margin-bottom:16px;">
                    <label>New Password</label>
                    <input type="password" name="new_password" class="form-control" required>
                </div>
                <div style="display:flex;gap:8px;justify-content:flex-end">
                    <button type="button" class="btn" style="background:#e2e8f0;color:black" onclick="closePasswordModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        const modal = document.getElementById('passwordModal');
        function openPasswordModal(id, name) {
            document.getElementById('modalId').value = id;
            document.getElementById('modalName').innerText = name;
            modal.style.display = 'flex';
        }
        function closePasswordModal() {
            modal.style.display = 'none';
        }
        modal.addEventListener('click', (e) => {
            if(e.target === modal) closePasswordModal();
        })
    </script>
</body>
</html>
<?php $conn->close(); ?>
