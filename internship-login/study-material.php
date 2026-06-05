<?php
session_start();
require_once __DIR__ . '/../database/db-config.php';
date_default_timezone_set('Asia/Kolkata');

if (!isset($_SESSION['student_id']) || !isset($_SESSION['is_internship'])) {
    header("Location: login.php");
    exit;
}

$conn = getDbConnection();
$student_id = $_SESSION['student_id'];
$root_path = rtrim(str_replace('\\', '/', dirname(dirname($_SERVER['SCRIPT_NAME']))), '/') . '/';

// Fetch Student Details
$sql_s = "SELECT s.*, i.title as internship_title, sess.session_name 
          FROM internship_enrollments s 
          LEFT JOIN internships i ON s.internship_id = i.id 
          LEFT JOIN internship_sessions sess ON s.session_id = sess.id
          WHERE s.id = $student_id";
$res_s = $conn->query($sql_s);
$student = $res_s->fetch_assoc();

// Fetch Study Materials
$materials = [];
$internship_id = $student['internship_id'] ?? 0;
if ($internship_id > 0) {
    $sql_mat = "SELECT * FROM internship_study_material WHERE internship_id = $internship_id ORDER BY id DESC";
    $res_mat = $conn->query($sql_mat);
    if ($res_mat) {
        while($row = $res_mat->fetch_assoc()) {
            $materials[] = $row;
        }
    }
}

// Profile Image URL
$photo_url = $root_path . "assets/images/avatar-placeholder.png";
if(isset($student['student_photo']) && !empty($student['student_photo'])) {
    $photo_url = $root_path . $student['student_photo'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Study Materials - MG Skills</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        :root { 
            --primary: #6366f1; 
            --primary-hover: #4f46e5;
            --bg: #f8fafc; 
            --text: #0f172a; 
            --card-bg: #fff; 
            --border: #e2e8f0; 
            --muted: #64748b;
        }
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family: 'Outfit', sans-serif; background: var(--bg); color: var(--text); padding-bottom: 50px; }

        .container { max-width: 1200px; margin: 30px auto; padding: 0 20px; display: grid; grid-template-columns: 320px 1fr; gap: 30px; }
        
        /* Profile Card */
        .profile-card { background: var(--card-bg); border-radius: 16px; padding: 30px; text-align: center; border: 1px solid var(--border); position: sticky; top: 100px; }
        .profile-img-box { width: 120px; height: 120px; border-radius: 50%; margin: 0 auto 15px; overflow: hidden; border: 4px solid #e0e7ff; }
        .profile-img { width: 100%; height: 100%; object-fit: cover; }
        .profile-name { font-size: 20px; font-weight: 700; margin-bottom: 5px; }
        .profile-id { color: #64748b; font-size: 14px; margin-bottom: 20px; display: inline-block; background: #f1f5f9; padding: 4px 12px; border-radius: 20px; }
        
        .profile-meta { text-align: left; margin-top: 20px; border-top: 1px solid var(--border); padding-top: 20px; }
        .meta-item { margin-bottom: 12px; display: flex; justify-content: space-between; font-size: 14px; gap: 20px; }
        .meta-label { color: #64748b; font-weight: 500; flex-shrink: 0; }
        .meta-val { font-weight: 600; text-align: right; flex: 1; }

        .sign-box { margin-top: 20px; border: 1px dashed var(--border); padding: 10px; border-radius: 8px; }
        .sign-box img { height: 40px; max-width: 100%; object-fit: contain; }
        .sign-label { font-size: 11px; color: #94a3b8; margin-top: 5px; text-transform: uppercase; letter-spacing: 0.5px; }

        /* Content Area */
        .section-header { 
            background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%); 
            color: white; 
            padding: 30px; 
            border-radius: 16px; 
            margin-bottom: 30px; 
            box-shadow: 0 4px 20px rgba(79, 70, 229, 0.15); 
        }
        .section-header h2 { font-size: 26px; font-weight: 700; margin-bottom: 8px; }
        .section-header p { font-size: 15px; opacity: 0.9; }

        /* Materials Grid */
        .materials-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 20px; }
        
        .material-card { 
            background: var(--card-bg); 
            border: 1px solid var(--border); 
            border-radius: 14px; 
            padding: 24px; 
            display: flex; 
            flex-direction: column; 
            transition: all 0.2s ease;
        }
        .material-card:hover { 
            transform: translateY(-3px); 
            box-shadow: 0 12px 24px rgba(0,0,0,0.05); 
            border-color: var(--primary); 
        }

        .material-icon-wrapper { 
            width: 48px; 
            height: 48px; 
            border-radius: 10px; 
            background: #fef2f2; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            color: #ef4444; 
            margin-bottom: 16px; 
        }
        
        .material-title { font-size: 18px; font-weight: 700; color: #1e293b; margin-bottom: 8px; line-height: 1.3; }
        
        .material-desc { 
            font-size: 14px; 
            color: var(--muted); 
            margin-bottom: 20px; 
            line-height: 1.5; 
            flex: 1; 
        }
        .material-desc p { margin-bottom: 8px; }
        .material-desc ul, .material-desc ol { padding-left: 15px; margin-bottom: 8px; }

        .btn-download { 
            display: inline-flex; 
            align-items: center; 
            justify-content: center; 
            gap: 8px; 
            padding: 12px; 
            border-radius: 8px; 
            background: var(--primary); 
            color: white; 
            font-weight: 600; 
            font-size: 14px; 
            text-decoration: none; 
            transition: background 0.2s; 
        }
        .btn-download:hover { background: var(--primary-hover); }

        .no-materials-placeholder { 
            background: white; 
            border: 1px solid var(--border); 
            border-radius: 16px; 
            padding: 60px; 
            text-align: center; 
            color: #64748b; 
        }

        @media (max-width: 1024px) {
            .container { grid-template-columns: 1fr; }
            .profile-card { position: static; display: flex; flex-direction: column; align-items: center; }
            .profile-meta { width: 100%; max-width: 500px; }
        }
    </style>
</head>
<body>

    <!-- Header -->
    <?php include 'header.php'; ?>

    <div class="container">
        <!-- Sidebar / Profile -->
        <aside>
            <div class="profile-card">
                <div class="profile-img-box">
                    <img src="<?php echo $photo_url; ?>" class="profile-img" alt="Student Photo">
                </div>
                <h3 class="profile-name"><?php echo htmlspecialchars($student['full_name']); ?></h3>
                <span class="profile-id"><?php echo htmlspecialchars($student['enrollment_no']); ?></span>
                
                <div class="profile-meta">
                    <div class="meta-item"><span class="meta-label">Internship</span><span class="meta-val"><?php echo htmlspecialchars($student['internship_title']); ?></span></div>
                    <div class="meta-item"><span class="meta-label">Session</span><span class="meta-val"><?php echo htmlspecialchars($student['session_name']); ?></span></div>
                    <div class="meta-item"><span class="meta-label">Category</span><span class="meta-val"><?php echo htmlspecialchars($student['category']); ?></span></div>
                </div>

                <div class="sign-box">
                    <?php if(!empty($student['student_sign'])): ?>
                        <img src="<?php echo $root_path . htmlspecialchars($student['student_sign']); ?>" alt="Signature">
                    <?php else: ?>
                        <span style="color:#ccc; font-size:12px;">No Signature Uploaded</span>
                    <?php endif; ?>
                    <div class="sign-label">Digitally Verified</div>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="content-section">
            <div class="section-header">
                <h2>Study Materials</h2>
                <p>Download reference PDF documents and guides for the <?php echo htmlspecialchars($student['internship_title'] ?? 'Internship Program'); ?></p>
            </div>

            <?php if (empty($materials)): ?>
                <div class="no-materials-placeholder">
                    <i data-lucide="folder-open" style="width: 48px; height: 48px; stroke: #94a3b8; margin-bottom: 15px; display: inline-block;"></i>
                    <h3 style="font-size: 18px; font-weight: 600; color: #1e293b; margin-bottom: 5px;">No Materials Available</h3>
                    <p style="font-size: 14px;">Reference study guides and PDFs have not been uploaded for your internship group yet.</p>
                </div>
            <?php else: ?>
                <div class="materials-grid">
                    <?php foreach ($materials as $mat): ?>
                        <div class="material-card">
                            <div class="material-icon-wrapper">
                                <i data-lucide="file-text" style="width: 24px; height: 24px;"></i>
                            </div>
                            <h3 class="material-title"><?php echo htmlspecialchars($mat['title']); ?></h3>
                            <div class="material-desc">
                                <?php echo $mat['description']; ?>
                            </div>
                            <a href="<?php echo $root_path . htmlspecialchars($mat['file_path']); ?>" target="_blank" download class="btn-download">
                                <i data-lucide="download" style="width: 16px;"></i>
                                Download PDF
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        if(typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    </script>
</body>
</html>
<?php $conn->close(); ?>
