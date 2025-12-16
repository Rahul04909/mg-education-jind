<?php
require_once __DIR__ . '/../../database/db-config.php';

$conn = getDbConnection();

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id = intval($_GET['id']);
$sql = "SELECT * FROM centers WHERE id = $id";
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
    <title>View Center - MG Skills</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #4f46e5; --text: #0b1020; --muted: #64748b; --line: #e2e8f0; --bg: #f8fafc; }
        body { font-family: 'Outfit', sans-serif; background: var(--bg); color: var(--text); margin: 0; padding: 0; }
        
        .admin-content { margin-left: 260px; padding: 32px; min-height: 100vh; }
        body.sidebar-collapsed .admin-content { margin-left: 80px; }
        
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; }
        .back-btn { text-decoration: none; color: var(--muted); font-size: 14px; display: flex; align-items: center; gap: 6px; }
        .title { font-size: 24px; font-weight: 700; margin: 4px 0 0; }
        
        .grid { display: grid; grid-template-columns: 2fr 1fr; gap: 24px; }
        .card { background: #fff; border-radius: 16px; border: 1px solid var(--line); padding: 24px; margin-bottom: 24px; }
        
        .profile-header { display: flex; gap: 20px; align-items: center; }
        .profile-logo { width: 80px; height: 80px; border-radius: 12px; object-fit: cover; border: 1px solid var(--line); }
        .profile-info h2 { margin: 0; font-size: 20px; }
        .profile-info p { margin: 4px 0 0; color: var(--muted); font-size: 14px; }
        
        .section-title { font-size: 16px; font-weight: 700; color: var(--primary); margin-bottom: 16px; padding-bottom: 8px; border-bottom: 1px solid var(--line); }
        
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        .info-item label { display: block; font-size: 12px; color: var(--muted); font-weight: 600; text-transform: uppercase; margin-bottom: 4px; }
        .info-item div { font-size: 15px; font-weight: 500; }
        
        .badge { padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; background: #eef2ff; color: var(--primary); }
        
        .doc-list { list-style: none; padding: 0; }
        .doc-list li { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #f1f5f9; font-size: 14px; }
        .doc-list li:last-child { border-bottom: none; }
        .doc-link { color: var(--primary); text-decoration: none; font-weight: 600; }
        
        .media-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
        .media-item img { width: 100%; height: 100px; object-fit: cover; border-radius: 8px; border: 1px solid var(--line); }
        .media-label { font-size: 12px; color: var(--muted); margin-top: 4px; display: block; text-align: center; }
        
        @media (max-width: 900px) { .grid { grid-template-columns: 1fr; } .admin-content { margin-left: 80px; padding: 20px; } }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../sidebar.php'; ?>
    
    <main class="admin-content">
        <div class="header">
            <div>
                <a href="index.php" class="back-btn">← Back to Centers</a>
                <h1 class="title">Center Details</h1>
            </div>
            <a href="edit-center.php?id=<?php echo $id; ?>" class="badge" style="background:var(--primary);color:#fff;text-decoration:none;padding:10px 20px;font-size:14px">Edit Details</a>
        </div>
        
        <div class="grid">
            <!-- Left Column -->
            <div class="col-main">
                <!-- Basic Info -->
                <div class="card">
                    <div class="profile-header">
                        <img src="<?php echo !empty($center['center_logo']) ? '../../'.$center['center_logo'] : '../../assets/images/placeholder-logo.png'; ?>" class="profile-logo">
                        <div class="profile-info">
                            <h2><?php echo htmlspecialchars($center['center_name']); ?></h2>
                            <p><?php echo htmlspecialchars($center['city'] . ', ' . $center['state']); ?></p>
                            <div style="margin-top:8px">
                                <span class="badge"><?php echo $center['is_active'] ? 'Active' : 'Inactive'; ?></span>
                                <span class="badge" style="background:#f1f5f9;color:#64748b"><?php echo htmlspecialchars($center['lab_type']); ?> Lab</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Contact & Location -->
                <div class="card">
                    <h3 class="section-title">Contact Information</h3>
                    <div class="info-grid">
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
                         <div class="info-item">
                            <label>Country</label>
                            <div><?php echo htmlspecialchars($center['country']); ?></div>
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
                    <div style="margin-top:16px">
                        <label style="font-size:12px;color:var(--muted);font-weight:600;text-transform:uppercase">Working Hours</label>
                        <div style="font-weight:500">
                            <?php echo date('h:i A', strtotime($center['working_hours_from'])); ?> - 
                            <?php echo date('h:i A', strtotime($center['working_hours_to'])); ?>
                        </div>
                        <div style="margin-top:4px;font-size:13px;color:var(--muted)">
                            Closed on: <?php echo implode(', ', $weekend_off); ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Right Column -->
            <div class="col-side">
                <!-- Media -->
                <div class="card">
                    <h3 class="section-title">Key People</h3>
                    <div class="media-grid">
                         <div class="media-item">
                             <?php if($center['owner_image']): ?>
                                <img src="../../<?php echo $center['owner_image']; ?>">
                             <?php else: ?>
                                <div style="height:100px;background:#f1f5f9;display:flex;align-items:center;justify-content:center;border-radius:8px;font-size:12px;color:#94a3b8">No Image</div>
                             <?php endif; ?>
                            <span class="media-label">Owner</span>
                         </div>
                         <div class="media-item">
                             <?php if($center['authorized_signatory']): ?>
                                <img src="../../<?php echo $center['authorized_signatory']; ?>">
                             <?php else: ?>
                                <div style="height:100px;background:#f1f5f9;display:flex;align-items:center;justify-content:center;border-radius:8px;font-size:12px;color:#94a3b8">No Image</div>
                             <?php endif; ?>
                            <span class="media-label">Signatory</span>
                         </div>
                    </div>
                </div>
                
                 <!-- Financials -->
                <div class="card">
                    <h3 class="section-title">Franchise Details</h3>
                    <div class="info-item" style="margin-bottom:12px">
                        <label>Franchise Fee</label>
                        <div style="font-size:18px;color:#16a34a">₹ <?php echo number_format($center['franchise_fee'], 2); ?></div>
                    </div>
                    <div class="info-item">
                        <label>Royalty</label>
                        <div><?php echo $center['royalty_percentage']; ?>%</div>
                    </div>
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
                                    <a href="../../<?php echo $doc['file']; ?>" target="_blank" class="doc-link">View</a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>

                 <!-- Social -->
                 <div class="card">
                    <h3 class="section-title">Social Media</h3>
                    <ul class="doc-list">
                        <?php foreach($social as $platform => $link): ?>
                            <?php if(!empty($link)): ?>
                            <li>
                                <span style="text-transform:capitalize"><?php echo $platform; ?></span>
                                <a href="<?php echo htmlspecialchars($link); ?>" target="_blank" class="doc-link">Visit</a>
                            </li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
<?php $conn->close(); ?>
