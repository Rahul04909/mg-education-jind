<?php
session_start();
require_once __DIR__ . '/../database/db-config.php';

if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit;
}

$student_id = $_SESSION['student_id'];
$conn = getDbConnection();

// Fetch student details
$sql = "SELECT * FROM admissions WHERE id = $student_id";
$result = $conn->query($sql);
$student = $result->fetch_assoc();

// Fetch Course Name
$course_id = $student['course_id'];
$c_sql = "SELECT title FROM courses WHERE id = $course_id";
$c_res = $conn->query($c_sql);
$course_name = ($c_res->num_rows > 0) ? $c_res->fetch_assoc()['title'] : "Unknown Course";

// Dates
$join_date = isset($student['created_at']) ? date('d/m/Y', strtotime($student['created_at'])) : date('d/m/Y'); 
$expire_date = date('d/m/Y', strtotime('+1 year', strtotime(str_replace('/', '-', $join_date))));
$dob = date('d/m/Y', strtotime($student['dob']));

// Handle Image Paths (Admin uploads might be relative to admin folder, need to adjust for student folder)
// DB stores: assets/uploads/students/...
// Adding ../ prefix since we are in student/ folder
$photo_path = !empty($student['student_photo']) ? '../' . $student['student_photo'] : 'https://i.pravatar.cc/300';
$logo_path = '../assets/images/logo.png'; // Assuming a logo exists, or use text

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My ID Card - MG Skills</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://html2canvas.hertzen.com/dist/html2canvas.min.js"></script>
    <style>
        :root{--primary:#6366f1;--secondary:#0f172a;--bg:#f8fafc;--white:#fff;--border:#e2e8f0;--success:#22c55e;}
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:'Outfit',sans-serif;background:var(--bg);color:var(--secondary);display:flex;min-height:100vh}
        
        /* Layout similar to index.php */
        .sidebar{width:260px;background:var(--white);border-right:1px solid var(--border);position:fixed;height:100vh;display:flex;flex-direction:column;z-index:10;}
        .main{margin-left:260px;flex:1;padding:30px; display: flex; flex-direction: column; align-items: center;}
        
        /* Mobile */
        @media(max-width:1024px){
            .sidebar{display:none}
            .main{margin-left:0}
        }

        .header-area { width: 100%; max-width: 800px; display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .page-title { font-size: 24px; font-weight: 700; }

        /* ID Card container */
        .id-card-wrapper {
            background: transparent;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            gap: 20px;
        }

        /* The Card ID Design */
        .id-card {
            width: 600px;
            height: 380px;
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            font-family: 'Outfit', sans-serif;
        }

        /* Abstract Top Background */
        .card-header-bg {
            height: 140px;
            background: linear-gradient(135deg, #0ea5e9 0%, #2563eb 100%);
            position: relative;
            z-index: 1;
        }
        
        /* Geometric lines overlay */
        .card-header-bg::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background-image: 
                linear-gradient(45deg, rgba(255,255,255,0.1) 25%, transparent 25%), 
                linear-gradient(-45deg, rgba(255,255,255,0.1) 25%, transparent 25%), 
                linear-gradient(45deg, transparent 75%, rgba(255,255,255,0.1) 75%), 
                linear-gradient(-45deg, transparent 75%, rgba(255,255,255,0.1) 75%);
            background-size: 40px 40px;
            opacity: 0.3;
        }
        
        .company-logo {
            position: absolute;
            top: 25px;
            right: 30px;
            text-align: right;
            color: #fff;
            z-index: 2;
        }
        .company-logo h2 { font-size: 24px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; margin: 0; line-height: 1; }
        .company-logo span { font-size: 12px; opacity: 0.9; letter-spacing: 2px; text-transform: uppercase; }

        /* Profile Image Area */
        .profile-container {
            position: absolute;
            top: 60px; /* Overlaps header */
            left: 40px;
            z-index: 5;
        }
        .profile-img-box {
            width: 160px;
            height: 160px;
            border-radius: 50%;
            background: #fff;
            padding: 5px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .profile-img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #f1f5f9;
        }

        /* Content Body */
        .card-body {
            margin-top: 60px; /* Space for profile overlap */
            padding: 0 40px 30px 40px;
            display: flex;
            flex-direction: column;
            flex: 1;
            position: relative;
        }

        .student-main-info {
            margin-left: 170px; /* push right of profile pic */
            margin-bottom: 25px;
        }
        .student-name {
            font-size: 28px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 4px;
        }
        .student-course {
            font-size: 16px;
            color: #64748b;
            font-weight: 500;
        }

        .details-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-top: 20px;
        }

        .detail-group label {
            display: block;
            font-size: 11px;
            text-transform: uppercase;
            color: #64748b;
            font-weight: 700;
            margin-bottom: 2px;
            letter-spacing: 0.5px;
        }
        .detail-group div {
            font-size: 15px;
            font-weight: 600;
            color: #334155;
        }

        /* Barcode / Footer Area */
        .card-footer {
            display: flex;
            justify-content: flex-end;
            align-items: flex-end;
            margin-top: auto;
            position: absolute;
            bottom: 25px;
            right: 30px;
            width: 100%;
        }
        
        .signature-area {
            text-align: center;
            margin-right: 40px;
        }
        .signature-img {
            height: 30px;
            display: block;
            margin: 0 auto 5px auto;
            opacity: 0.8;
        }
        .signature-text {
            font-size: 10px;
            color: #94a3b8;
            border-top: 1px solid #cbd5e1;
            padding-top: 2px;
            display: inline-block;
            min-width: 100px;
        }

        .barcode {
             height: 35px;
             /* Simple CSS barcode representation or image */
             background: repeating-linear-gradient(
                90deg,
                #333 0px,
                #333 2px,
                transparent 2px,
                transparent 4px,
                #333 4px,
                #333 8px,
                transparent 8px,
                transparent 9px
             );
             width: 120px;
             margin-left: auto;
             margin-right: 70px; /* From right edge */
        }
        
        .download-btn {
            background: var(--primary);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: 0.2s;
            box-shadow: 0 4px 6px -1px rgba(99, 102, 241, 0.4);
        }
        .download-btn:hover {
            background: #4f46e5;
            transform: translateY(-2px);
        }

    </style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<main class="main">
    <div class="header-area">
        <h1 class="page-title">Digital ID Card</h1>
        <button class="download-btn" id="downloadBtn">
            <i data-lucide="download"></i> Download ID Card
        </button>
    </div>

    <div class="id-card-wrapper">
        <div class="id-card" id="captureCard">
            <!-- Header Background -->
            <div class="card-header-bg">
                <div class="company-logo">
                    <span>MG Education</span>
                    <h2>SKILLS</h2>
                </div>
            </div>

            <!-- Profile Image -->
            <div class="profile-container">
                <div class="profile-img-box">
                    <img src="<?php echo htmlspecialchars($photo_path); ?>" alt="Student Photo" class="profile-img" crossorigin="anonymous">
                </div>
            </div>

            <div class="card-body">
                <!-- Name & Title -->
                <div class="student-main-info">
                    <div class="student-name"><?php echo htmlspecialchars($student['full_name']); ?></div>
                    <div class="student-course"><?php echo htmlspecialchars($course_name); ?></div>
                </div>

                <!-- Details Grid -->
                <div class="details-grid">
                    <div class="detail-group">
                        <label>ID No</label>
                        <div><?php echo htmlspecialchars($student['enrollment_no']); ?></div>
                    </div>
                    <div class="detail-group">
                        <label>Joined Date</label>
                        <div><?php echo $join_date; ?></div>
                    </div>
                    <div class="detail-group">
                        <label>D.O.B</label>
                        <div><?php echo $dob; ?></div>
                    </div>
                    <div class="detail-group">
                        <label>Expire Date</label>
                        <div><?php echo $expire_date; ?></div>
                    </div>
                </div>

                <!-- Footer with Signature/Barcode -->
                <div class="card-footer">
                     <!-- Fake Barcode for visual -->
                     <div class="barcode"></div>
                </div>
            </div>
            
            <!-- Signature Overlapping bottom right slightly -->
            <div style="position: absolute; bottom: 80px; right: 40px; text-align: center;">
                 <!-- If signature image exists -->
                 <?php if(!empty($student['student_sign'])): ?>
                    <img src="../<?php echo htmlspecialchars($student['student_sign']); ?>" style="height: 40px; display:block; margin: 0 auto;" crossorigin="anonymous">
                 <?php else: ?>
                    <div style="font-family: 'Brush Script MT', cursive; font-size: 20px; color: #333;">Digitally Signed</div>
                 <?php endif; ?>
                 <div style="font-size: 10px; color: #94a3b8; margin-top: 4px;">Authorized Signature</div>
            </div>

        </div>
        
        <p style="margin-top: 20px; color: #64748b; font-size: 14px;">
            <i data-lucide="info" style="width: 14px; height: 14px; vertical-align: middle;"></i> 
            This ID card is valid for online verification and campus access.
        </p>
    </div>

</main>

<script>
    lucide.createIcons();

    document.getElementById('downloadBtn').addEventListener('click', function() {
        const card = document.getElementById('captureCard');
        const btn = this;
        
        btn.innerHTML = '<i data-lucide="loader-2" class="animate-spin"></i> Generating...';
        lucide.createIcons();
        
        html2canvas(card, {
            scale: 3, // High resolution
            useCORS: true, // For images
            backgroundColor: null
        }).then(canvas => {
            const link = document.createElement('a');
            link.download = 'MG-Skill-ID-<?php echo $student["enrollment_no"]; ?>.png';
            link.href = canvas.toDataURL('image/png');
            link.click();
            
            btn.innerHTML = '<i data-lucide="check"></i> Downloaded!';
            lucide.createIcons();
            
            setTimeout(() => {
                btn.innerHTML = '<i data-lucide="download"></i> Download ID Card';
                lucide.createIcons();
            }, 3000);
        }).catch(err => {
            console.error(err);
            alert('Failed to generate image. Please try again.');
            btn.innerHTML = '<i data-lucide="download"></i> Download ID Card';
            lucide.createIcons();
        });
    });
</script>

</body>
</html>
