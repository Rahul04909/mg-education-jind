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

// Fetch Syllabus Topics
$syllabus = [];
$internship_id = $student['internship_id'] ?? 0;
if ($internship_id > 0) {
    $sql_syl = "SELECT * FROM internship_syllabus WHERE internship_id = $internship_id ORDER BY id ASC";
    $res_syl = $conn->query($sql_syl);
    if ($res_syl) {
        while($row = $res_syl->fetch_assoc()) {
            $syllabus[] = $row;
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
    <title>My Syllabus - MG Skills</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        :root { 
            --primary: #6366f1; 
            --bg: #f8fafc; 
            --text: #0f172a; 
            --card-bg: #fff; 
            --border: #e2e8f0; 
            --indigo-light: #e0e7ff; 
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

        /* Syllabus Section */
        .syllabus-header { 
            background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%); 
            color: white; 
            padding: 30px; 
            border-radius: 16px; 
            margin-bottom: 30px; 
            box-shadow: 0 4px 20px rgba(79, 70, 229, 0.15); 
        }
        .syllabus-header h2 { font-size: 26px; font-weight: 700; margin-bottom: 8px; }
        .syllabus-header p { font-size: 15px; opacity: 0.9; }

        .timeline { position: relative; padding-left: 20px; }
        .timeline::before { 
            content: ''; 
            position: absolute; 
            left: 0; 
            top: 10px; 
            bottom: 10px; 
            width: 2px; 
            background: #cbd5e1; 
        }

        .timeline-item { position: relative; margin-bottom: 30px; }
        .timeline-marker { 
            position: absolute; 
            left: -26px; 
            top: 4px; 
            width: 14px; 
            height: 14px; 
            border-radius: 50%; 
            background: var(--primary); 
            border: 3px solid white; 
            box-shadow: 0 0 0 2px var(--primary); 
        }

        .syllabus-card { 
            background: var(--card-bg); 
            border: 1px solid var(--border); 
            border-radius: 12px; 
            padding: 24px; 
            box-shadow: 0 2px 8px rgba(0,0,0,0.02);
            transition: 0.2s;
        }
        .syllabus-card:hover { 
            transform: translateY(-2px); 
            box-shadow: 0 10px 20px -10px rgba(79, 70, 229, 0.1); 
            border-color: var(--primary); 
        }

        .unit-title { font-size: 18px; font-weight: 700; color: #1e293b; margin-bottom: 12px; display: flex; align-items: center; gap: 8px; }

        /* Rich text contents rendering wrapper */
        .syllabus-desc { color: #475569; font-size: 15px; line-height: 1.6; }
        .syllabus-desc p { margin-bottom: 12px; }
        .syllabus-desc ul, .syllabus-desc ol { padding-left: 20px; margin-bottom: 12px; }
        .syllabus-desc ul { list-style-type: disc; }
        .syllabus-desc ol { list-style-type: decimal; }
        .syllabus-desc li { margin-bottom: 6px; }

        .no-syllabus-placeholder { 
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

        <!-- Main Content (Syllabus) -->
        <div class="syllabus-section-container">
            <div class="syllabus-header">
                <h2>Course Curriculum</h2>
                <p>Detailed syllabus breakdown for the <?php echo htmlspecialchars($student['internship_title'] ?? 'Internship Program'); ?></p>
            </div>

            <?php if (empty($syllabus)): ?>
                <div class="no-syllabus-placeholder">
                    <i data-lucide="book-open" style="width: 48px; height: 48px; stroke: #94a3b8; margin-bottom: 15px; display: inline-block;"></i>
                    <h3 style="font-size: 18px; font-weight: 600; color: #1e293b; margin-bottom: 5px;">Syllabus Not Available</h3>
                    <p style="font-size: 14px;">The curriculum details for this internship have not been uploaded yet. Please contact your coordinator.</p>
                </div>
            <?php else: ?>
                <div class="timeline">
                    <?php foreach ($syllabus as $unit): ?>
                        <div class="timeline-item">
                            <span class="timeline-marker"></span>
                            <div class="syllabus-card">
                                <h3 class="unit-title">
                                    <i data-lucide="bookmark" style="width: 18px; color: var(--primary);"></i>
                                    <?php echo htmlspecialchars($unit['unit_title']); ?>
                                </h3>
                                <div class="syllabus-desc">
                                    <?php echo $unit['description']; ?>
                                </div>
                            </div>
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
