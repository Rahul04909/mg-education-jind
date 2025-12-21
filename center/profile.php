<?php
require_once 'auth_check.php';
require_once '../database/db-config.php';

$conn = getDbConnection();
$center_id = $_SESSION['center_id'];

$sql = "SELECT * FROM centers WHERE id = $center_id";
$result = $conn->query($sql);

if ($result->num_rows === 0) {
    die("Center not found");
}

$center = $result->fetch_assoc();
$weekend_off = json_decode($center['weekend_off'], true) ?? [];
$social = json_decode($center['social_links'], true) ?? [];
$docs = json_decode($center['legal_documents'], true) ?? [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - MG Skills</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #059669; --text: #0b1020; --muted: #64748b; --line: #e2e8f0; --bg: #f1f5f9; }
        body { font-family: 'Outfit', sans-serif; background: var(--bg); color: var(--text); margin: 0; padding: 0; }
        
        .main-content { margin-left: 280px; padding: 32px; min-height: 100vh; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; }
        .title { font-size: 24px; font-weight: 700; margin: 0; color: #1e293b; }
        
        .grid { display: grid; grid-template-columns: 2fr 1fr; gap: 24px; }
        .card { background: #fff; border-radius: 20px; border: 1px solid var(--line); padding: 24px; margin-bottom: 24px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02); }
        
        .profile-header { display: flex; gap: 20px; align-items: center; }
        .profile-logo { width: 80px; height: 80px; border-radius: 12px; object-fit: cover; border: 1px solid var(--line); }
        .profile-info h2 { margin: 0; font-size: 20px; }
        .profile-info p { margin: 4px 0 0; color: var(--muted); font-size: 14px; }
        
        .section-title { font-size: 16px; font-weight: 700; color: var(--primary); margin-bottom: 16px; padding-bottom: 8px; border-bottom: 1px solid var(--line); display: flex; justify-content: space-between; align-items: center; }
        
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        .info-item label { display: block; font-size: 12px; color: var(--muted); font-weight: 600; text-transform: uppercase; margin-bottom: 4px; }
        .info-item div { font-size: 15px; font-weight: 500; color: #334155; }
        
        .badge { padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; background: #eef2ff; color: var(--primary); }
        
        .doc-list { list-style: none; padding: 0; }
        .doc-list li { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #f1f5f9; font-size: 14px; }
        .doc-list li:last-child { border-bottom: none; }
        .doc-link { color: var(--primary); text-decoration: none; font-weight: 600; }
        
        /* Form Styles */
        .form-group { margin-bottom: 16px; }
        .form-label { display: block; font-size: 13px; font-weight: 600; color: #475569; margin-bottom: 6px; }
        .form-input { 
            width: 100%; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-family: inherit; font-size: 14px; 
            transition: all 0.2s; box-sizing: border-box;
        }
        .form-input:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.1); }
        .btn-primary { 
            background: var(--primary); color: white; border: none; padding: 12px 24px; border-radius: 8px; font-weight: 600; cursor: pointer; width: 100%;
            font-size: 14px; transition: background 0.2s;
        }
        .btn-primary:hover { background: #047857; }
        
        @media (max-width: 900px) { .grid { grid-template-columns: 1fr; } .main-content { margin-left: 0; padding: 20px; } }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <main class="main-content">
        <div class="header">
            <h1 class="title">My Profile</h1>
            <div class="date-badge" style="background:white; padding:8px 16px; border-radius:8px; font-size:14px; font-weight:600; color:var(--muted)">
                <?php echo date('d M, Y'); ?>
            </div>
        </div>
        
        <div class="grid">
            <!-- Left Column: Details -->
            <div class="col-main">
                <!-- Basic Info -->
                <div class="card">
                    <div class="profile-header">
                        <img src="<?php echo !empty($center['center_logo']) ? '../'.$center['center_logo'] : '../assets/images/placeholder-logo.png'; ?>" class="profile-logo">
                        <div class="profile-info">
                            <h2><?php echo htmlspecialchars($center['center_name']); ?></h2>
                            <p><?php echo htmlspecialchars($center['city'] . ', ' . $center['state']); ?></p>
                            <div style="margin-top:8px">
                                <span class="badge" style="background:#ecfdf5; color:#059669"><?php echo $center['is_active'] ? 'Active Center' : 'Inactive'; ?></span>
                                <span class="badge" style="background:#f1f5f9;color:#64748b"><?php echo htmlspecialchars($center['lab_type']); ?> Lab</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Contact & Location -->
                <div class="card">
                    <h3 class="section-title">Center Information</h3>
                    <div class="info-grid">
                        <div class="info-item">
                            <label>Center Code</label>
                            <div><?php echo 'CEN-' . str_pad($center['id'], 4, '0', STR_PAD_LEFT); ?></div>
                        </div>
                        <div class="info-item">
                            <label>Owner Name</label>
                            <div><?php echo htmlspecialchars($center['owner_name']); ?></div>
                        </div>
                        <div class="info-item">
                            <label>Mobile</label>
                            <div><?php echo htmlspecialchars($center['mobile']); ?></div>
                        </div>
                        <div class="info-item">
                            <label>Email</label>
                            <div><?php echo htmlspecialchars($center['email']); ?></div>
                        </div>
                        <div class="info-item">
                            <label>Full Address</label>
                            <div><?php echo htmlspecialchars($center['address']); ?></div>
                        </div>
                         <div class="info-item">
                            <label>Pincode</label>
                            <div><?php echo htmlspecialchars($center['pincode']); ?></div>
                        </div>
                    </div>
                </div>
                
                <!-- Infrastructure -->
                <div class="card">
                    <h3 class="section-title">Infrastructure & Staff</h3>
                     <div class="info-grid" style="grid-template-columns: repeat(4, 1fr);">
                        <div class="info-item">
                            <label>Classrooms</label>
                            <div><?php echo $center['num_classrooms']; ?></div>
                        </div>
                        <div class="info-item">
                            <label>Computers</label>
                            <div><?php echo $center['num_computers']; ?></div>
                        </div>
                        <div class="info-item">
                            <label>Staff</label>
                            <div><?php echo $center['total_staff']; ?></div>
                        </div>
                        <div class="info-item">
                            <label>Power Backup</label>
                            <div><?php echo $center['has_power_backup'] ? 'Yes' : 'No'; ?></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Right Column: Actions & Docs -->
            <div class="col-side">
                
                <!-- Change Password -->
                <div class="card" style="border-color: #cbd5e1;">
                    <h3 class="section-title">Security Settings</h3>
                    <form action="../insert-db/update-center-password.php" method="POST">
                        <div class="form-group">
                            <label class="form-label">Current Password</label>
                            <input type="password" name="current_password" class="form-input" required placeholder="Enter current password">
                        </div>
                        <div class="form-group">
                            <label class="form-label">New Password</label>
                            <input type="password" name="new_password" class="form-input" required placeholder="Min 6 characters">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Confirm Password</label>
                            <input type="password" name="confirm_password" class="form-input" required placeholder="Re-enter new password">
                        </div>
                        <button type="submit" class="btn-primary">Update Password</button>
                    </form>
                </div>

                <!-- Documents -->
                <div class="card">
                    <h3 class="section-title">Legal Documents</h3>
                    <?php if(empty($docs)): ?>
                        <p style="color:var(--muted);font-size:13px">No documents uploaded.</p>
                    <?php else: ?>
                        <ul class="doc-list">
                            <?php foreach($docs as $doc): ?>
                                <li>
                                    <span><?php echo htmlspecialchars($doc['name']); ?></span>
                                    <a href="../<?php echo $doc['file']; ?>" target="_blank" class="doc-link">View</a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
                
            </div>
        </div>
    </main>
    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <?php if(isset($_GET['status'])): ?>
    <script>
        Swal.fire({
            icon: '<?php echo $_GET['status'] == 'success' ? 'success' : 'error'; ?>',
            title: '<?php echo $_GET['status'] == 'success' ? 'Success' : 'Error'; ?>',
            text: '<?php echo isset($_GET['msg']) ? htmlspecialchars($_GET['msg']) : ""; ?>',
            confirmButtonColor: '#059669'
        });
    </script>
    <?php endif; ?>
</body>
</html>
<?php $conn->close(); ?>
