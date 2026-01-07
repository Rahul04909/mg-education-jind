<?php
require_once __DIR__ . '/../../database/db-config.php';

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$conn = getDbConnection();
$id = intval($_GET['id']);
$sql = "SELECT * FROM volunteers WHERE id = $id";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    echo "Volunteer not found";
    exit;
}

$vol = $result->fetch_assoc();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Volunteer - MG Skills</title>
    <style>
        :root{--active:#22c55e;--indigo:#4f46e5;--line:#e2e8f0;--text:#1e293b;--muted:#64748b;--bg:#f8fafc;--white:#fff;}
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:'Outfit',system-ui,sans-serif;background:var(--bg);color:var(--text)}
        .admin-content{margin-left:260px;min-height:100vh;padding:30px;transition:margin-left .3s ease}
        body.sidebar-collapsed .admin-content{margin-left:80px}
        
        .page-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:24px}
        .page-title{font-size:24px;font-weight:700;color:var(--text)}
        
        .btn { padding: 10px 20px; border-radius: 8px; font-weight: 600; text-decoration: none; cursor: pointer; border: 1px solid var(--line); font-size: 14px; transition: all 0.2s; display: inline-flex; align-items: center; gap: 8px; background: white; color: var(--text); }
        .btn:hover { background: #f1f5f9; }
        
        .card { background: white; border-radius: 12px; border: 1px solid var(--line); overflow: hidden; margin-bottom: 24px; }
        .card-header { padding: 20px; border-bottom: 1px solid var(--line); font-weight: 600; font-size: 16px; background: #f8fafc; color: var(--muted); text-transform: uppercase; letter-spacing: 0.5px; }
        .card-body { padding: 30px; }
        
        .info-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 30px; }
        .info-item { margin-bottom: 10px; }
        .label { font-size: 12px; color: var(--muted); font-weight: 600; margin-bottom: 4px; display: block; text-transform: uppercase; }
        .value { font-size: 15px; color: var(--text); font-weight: 500; }
        
        .doc-preview { width: 100%; height: 200px; background: #f1f5f9; border-radius: 8px; display: flex; align-items: center; justify-content: center; overflow: hidden; border: 1px solid var(--line); }
        .doc-preview img { width: 100%; height: 100%; object-fit: contain; }
        .doc-link { display: block; margin-top: 10px; color: var(--indigo); font-size: 14px; text-decoration: none; font-weight: 500; }
        
        .badge { padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; background: #e0f2fe; color: #0284c7; }
    </style>
</head>
<body class="<?php echo isset($_COOKIE['sidebar_collapsed']) && $_COOKIE['sidebar_collapsed'] == 'true' ? 'sidebar-collapsed' : ''; ?>">

    <?php include '../../admin/sidebar.php'; ?>

    <div class="admin-content">
        <div class="page-header">
            <div>
                <h1 class="page-title">Volunteer Details</h1>
                <p style="color:var(--muted); font-size:14px; margin-top:4px;">ID: <span style="color:var(--indigo); font-weight:700"><?php echo $vol['volunteer_id']; ?></span></p>
            </div>
            <a href="index.php" class="btn">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5"/><path d="M12 19l-7-7 7-7"/></svg>
                Back to List
            </a>
        </div>

        <div class="card">
            <div class="card-header">Personal Information</div>
            <div class="card-body">
                <div class="info-grid">
                    <div class="info-item">
                        <span class="label">Full Name</span>
                        <div class="value"><?php echo htmlspecialchars($vol['full_name']); ?></div>
                    </div>
                    <div class="info-item">
                        <span class="label">Email Address</span>
                        <div class="value"><?php echo htmlspecialchars($vol['email']); ?></div>
                    </div>
                    <div class="info-item">
                        <span class="label">Mobile Number</span>
                        <div class="value"><?php echo htmlspecialchars($vol['mobile']); ?></div>
                    </div>
                    <div class="info-item">
                        <span class="label">Date of Birth</span>
                        <div class="value"><?php echo date("d M Y", strtotime($vol['dob'])); ?></div>
                    </div>
                    <div class="info-item">
                        <span class="label">Gender</span>
                        <div class="value"><?php echo htmlspecialchars($vol['gender']); ?></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">Location Details</div>
            <div class="card-body">
                <div class="info-grid">
                    <div class="info-item">
                        <span class="label">Address</span>
                        <div class="value"><?php echo nl2br(htmlspecialchars($vol['address'])); ?></div>
                    </div>
                    <div class="info-item">
                        <span class="label">City</span>
                        <div class="value"><?php echo htmlspecialchars($vol['city']); ?></div>
                    </div>
                    <div class="info-item">
                        <span class="label">State</span>
                        <div class="value"><?php echo htmlspecialchars($vol['state']); ?></div>
                    </div>
                    <div class="info-item">
                        <span class="label">Country</span>
                        <div class="value"><?php echo htmlspecialchars($vol['country']); ?></div>
                    </div>
                    <div class="info-item">
                        <span class="label">Pincode</span>
                        <div class="value"><?php echo htmlspecialchars($vol['pincode']); ?></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">Role & Availability</div>
            <div class="card-body">
                <div class="info-grid">
                    <div class="info-item">
                        <span class="label">Preferred Role</span>
                        <div class="value"><span class="badge"><?php echo htmlspecialchars($vol['role']); ?></span></div>
                    </div>
                    <div class="info-item">
                        <span class="label">Preferred Mode</span>
                        <div class="value"><?php echo htmlspecialchars($vol['preferred_mode']); ?></div>
                    </div>
                    <div class="info-item">
                        <span class="label">Hours Per Week</span>
                        <div class="value"><?php echo htmlspecialchars($vol['hours_per_week']); ?> Hours</div>
                    </div>
                    <div class="info-item">
                        <span class="label">Available Days</span>
                        <div class="value">
                            <?php 
                                $days = json_decode($vol['weekdays'], true);
                                echo $days ? implode(", ", $days) : 'None'; 
                            ?>
                        </div>
                    </div>
                </div>
                <div style="margin-top:20px">
                    <span class="label">Motivation / Message</span>
                    <div class="value" style="background:#f8fafc; padding:15px; border-radius:8px; border:1px solid var(--line); margin-top:5px; line-height:1.6">
                        <?php echo nl2br(htmlspecialchars($vol['message'])); ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">Documents & Uploads</div>
            <div class="card-body">
                <div class="info-grid">
                    <div class="info-item">
                        <span class="label">Profile Photo</span>
                        <?php if($vol['photo_file']): ?>
                            <div class="doc-preview" style="width:150px; height:150px">
                                <img src="../../<?php echo htmlspecialchars($vol['photo_file']); ?>" alt="Photo">
                            </div>
                        <?php else: ?>
                            <div class="value">No Photo</div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="info-item">
                        <span class="label">Aadhar Card</span>
                        <div class="value" style="margin-bottom:5px">No: <?php echo htmlspecialchars($vol['aadhar_no']); ?></div>
                        <?php if($vol['aadhar_file']): ?>
                            <a href="../../<?php echo htmlspecialchars($vol['aadhar_file']); ?>" target="_blank" class="doc-link">View Aadhar PDF</a>
                        <?php endif; ?>
                    </div>

                    <div class="info-item">
                        <span class="label">Resume</span>
                        <?php if($vol['resume_file']): ?>
                            <a href="../../<?php echo htmlspecialchars($vol['resume_file']); ?>" target="_blank" class="doc-link">Download Resume</a>
                        <?php else: ?>
                            <div class="value">Not Uploaded</div>
                        <?php endif; ?>
                    </div>

                    <?php if($vol['is_student']): ?>
                    <div class="info-item">
                        <span class="label">Student ID</span>
                        <div class="value" style="margin-bottom:5px">No: <?php echo htmlspecialchars($vol['student_id_no']); ?></div>
                        <?php if($vol['student_id_file']): ?>
                            <div class="doc-preview" style="width:200px; height:120px">
                                <img src="../../<?php echo htmlspecialchars($vol['student_id_file']); ?>" alt="ID Card">
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>

</body>
</html>
