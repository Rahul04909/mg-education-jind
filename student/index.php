<?php
session_start();
require_once __DIR__ . '/../database/db-config.php';

if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit;
}

$student_id = $_SESSION['student_id'];
$enrollment_no = $_SESSION['enrollment_no'];
$conn = getDbConnection();

// 1. Fetch Student, Course, and Session details
$sql = "SELECT s.*, c.title as course_name, c.fees as course_meta, cs.session_name 
        FROM admissions s 
        LEFT JOIN courses c ON s.course_id = c.id 
        LEFT JOIN course_sessions cs ON s.session_id = cs.id
        WHERE s.id = $student_id";
$result = $conn->query($sql);
$student = $result->fetch_assoc();

// 2. Fetch Fee Statistics
$course_meta = json_decode($student['course_meta'], true);
$total_fee = isset($course_meta['amount']) ? floatval($course_meta['amount']) : 0;

$total_paid = 0;
$t_sql = "SELECT SUM(amount) as paid FROM student_transactions WHERE enrollment_no = '$enrollment_no' AND status = 'success'";
$t_res = $conn->query($t_sql);
if ($t_res && $row = $t_res->fetch_assoc()) {
    $total_paid = floatval($row['paid']);
}

$pending_fee = max(0, $total_fee - $total_paid);
$fee_percent = ($total_fee > 0) ? round(($total_paid / $total_fee) * 100) : 0;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - MG Skills</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        :root {
            --primary: #a855f7;
            --primary-light: #f3e8ff;
            --secondary: #0f172a;
            --bg: #f8fafc;
            --white: #ffffff;
            --border: #e2e8f0;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --success: #22c55e;
            --warning: #f59e0b;
            --danger: #ef4444;
            --sidebar-w: 260px;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Outfit', sans-serif; background: var(--bg); color: var(--text-main); line-height: 1.5; }

        .main-container { margin-left: var(--sidebar-w); min-height: 100vh; padding: 40px; transition: all 0.3s; }

        /* Welcome Header */
        .welcome-section {
            background: linear-gradient(135deg, #a855f7 0%, #7c3aed 100%);
            padding: 40px;
            border-radius: 24px;
            color: white;
            margin-bottom: 40px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 20px 40px -10px rgba(168, 85, 247, 0.3);
        }
        .welcome-section::after {
            content: '';
            position: absolute;
            top: -50px;
            right: -50px;
            width: 200px;
            height: 200px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
        }
        .welcome-text h1 { font-size: 32px; font-weight: 700; margin-bottom: 8px; }
        .welcome-text p { font-size: 16px; opacity: 0.9; }

        /* Stats Grid */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 24px; margin-bottom: 40px; }
        .stat-card {
            background: var(--white);
            padding: 24px;
            border-radius: 20px;
            border: 1px solid var(--border);
            display: flex;
            align-items: center;
            gap: 20px;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .stat-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.05); }
        .stat-icon {
            width: 56px;
            height: 56px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .icon-purple { background: #f3e8ff; color: #a855f7; }
        .icon-blue { background: #eff6ff; color: #3b82f6; }
        .icon-green { background: #f0fdf4; color: #22c55e; }
        
        .stat-info .label { font-size: 13px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600; }
        .stat-info .value { font-size: 20px; font-weight: 700; color: var(--secondary); margin-top: 2px; }

        /* Dashboard Content Layout */
        .dashboard-layout { display: grid; grid-template-columns: 350px 1fr; gap: 30px; align-items: start; }

        /* Profile/ID Card Section */
        .id-card-component {
            background: var(--white);
            border-radius: 24px;
            border: 1px solid var(--border);
            padding: 30px;
            text-align: center;
            position: sticky;
            top: 40px;
        }
        .v-photo-box {
            width: 160px;
            height: 160px;
            margin: 0 auto 20px;
            border-radius: 20px;
            border: 4px solid var(--primary-light);
            padding: 5px;
            background: white;
            overflow: hidden;
        }
        .v-photo-box img { width: 100%; height: 100%; object-fit: cover; border-radius: 12px; }
        
        .v-details h2 { font-size: 22px; font-weight: 700; color: var(--secondary); margin-bottom: 4px; }
        .v-details .id-no { font-size: 14px; color: var(--text-muted); font-weight: 500; margin-bottom: 20px; }
        
        .v-sign-box {
            background: #fdfaf5;
            border: 1px dashed #d1d5db;
            border-radius: 12px;
            padding: 15px;
            height: 80px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            overflow: hidden;
        }
        .v-sign-box img { max-height: 60px; filter: grayscale(1); mix-blend-mode: multiply; }
        .v-sign-label { font-size: 11px; text-transform: uppercase; color: var(--text-muted); font-weight: 700; }

        /* Info Hub */
        .info-hub { display: flex; flex-direction: column; gap: 25px; }
        .hub-card {
            background: var(--white);
            border-radius: 24px;
            border: 1px solid var(--border);
            padding: 30px;
        }
        .hub-card-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--border);
        }
        .hub-card-header h3 { font-size: 18px; font-weight: 700; }
        .hub-card-header i { color: var(--primary); }

        .info-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 24px; }
        .info-item label { display: block; font-size: 12px; color: var(--text-muted); text-transform: uppercase; font-weight: 700; margin-bottom: 6px; }
        .info-item div { font-size: 15px; font-weight: 600; color: var(--secondary); }

        /* Fee Progress Card */
        .fee-status-card {
            background: #fff;
            padding: 24px;
            border-radius: 20px;
            border: 1px solid var(--border);
            margin-bottom: 24px;
        }
        .fee-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; }
        .fee-header h4 { font-size: 16px; font-weight: 700; }
        .fee-percent { background: var(--primary-light); color: var(--primary); padding: 4px 12px; border-radius: 20px; font-size: 13px; font-weight: 700; }
        
        .progress-container { height: 10px; background: #f1f5f9; border-radius: 5px; overflow: hidden; margin-bottom: 15px; }
        .progress-fill { height: 100%; background: var(--primary); transition: width 1s cubic-bezier(0.4, 0, 0.2, 1); }
        
        .fee-summary { display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; }
        .summary-item .s-label { font-size: 11px; color: var(--text-muted); display: block; margin-bottom: 2px; }
        .summary-item .s-val { font-weight: 700; font-size: 14px; }

        @media (max-width: 1200px) {
            .dashboard-layout { grid-template-columns: 1fr; }
            .id-card-component { position: static; }
        }

        @media (max-width: 768px) {
            .main-container { margin-left: 0; padding: 20px; }
            .info-grid { grid-template-columns: 1fr; }
            .welcome-section { padding: 25px; }
            .welcome-text h1 { font-size: 24px; }
        }
    </style>
</head>
<body>

    <?php include 'sidebar.php'; ?>

    <div class="main-container">
        <!-- Header Section -->
        <div class="welcome-section">
            <div class="welcome-text">
                <h1>Welcome back, <?php echo htmlspecialchars($student['full_name']); ?>! 👋</h1>
                <p>Track your courses, fees, and identity documentation in one place.</p>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon icon-purple"><i data-lucide="book-open"></i></div>
                <div class="stat-info">
                    <span class="label">Course Title</span>
                    <div class="value"><?php echo htmlspecialchars($student['course_name'] ?? 'N/A'); ?></div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon icon-blue"><i data-lucide="calendar"></i></div>
                <div class="stat-info">
                    <span class="label">Course Session</span>
                    <div class="value"><?php echo htmlspecialchars($student['session_name'] ?? 'N/A'); ?></div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon icon-green"><i data-lucide="shield-check"></i></div>
                <div class="stat-info">
                    <span class="label">Admission Status</span>
                    <div class="value">Active</div>
                </div>
            </div>
        </div>

        <div class="dashboard-layout">
            <!-- Left Side: Interactive ID/Sign -->
            <div class="id-card-component">
                <div class="v-photo-box">
                    <?php 
                        $photo = (!empty($student['student_photo']) && file_exists("../" . $student['student_photo'])) ? "../" . $student['student_photo'] : "../assets/images/avatar-placeholder.png";
                    ?>
                    <img src="<?php echo $photo; ?>" alt="Profile Photo">
                </div>
                <div class="v-details">
                    <h2><?php echo htmlspecialchars($student['full_name']); ?></h2>
                    <div class="id-no">ROLL NO: <?php echo htmlspecialchars($student['enrollment_no']); ?></div>
                </div>
                
                <div class="v-sign-box">
                    <?php if(!empty($student['student_sign']) && file_exists("../" . $student['student_sign'])): ?>
                        <img src="../<?php echo $student['student_sign']; ?>" alt="Signature">
                    <?php else: ?>
                        <span style="color:#cbd5e1; font-style:italic; font-size:14px;">Signature not uploaded</span>
                    <?php endif; ?>
                </div>
                <div class="v-sign-label">Candidate Signature</div>
                
                <div style="margin-top: 30px;">
                    <a href="id-card/id-card.php" style="display:block; padding:12px; background:var(--primary); color:white; border-radius:12px; text-decoration:none; font-weight:600; font-size:14px;">Download Full ID Card</a>
                </div>
            </div>

            <!-- Right Side: Details & Fees -->
            <div class="info-hub">
                <!-- Fee Status Card -->
                <div class="fee-status-card">
                    <div class="fee-header">
                        <h4>Financial Overview</h4>
                        <span class="fee-percent"><?php echo $fee_percent; ?>% Paid</span>
                    </div>
                    <div class="progress-container">
                        <div class="progress-fill" style="width: <?php echo $fee_percent; ?>%"></div>
                    </div>
                    <div class="fee-summary">
                        <div class="summary-item">
                            <span class="s-label">Total Fee</span>
                            <div class="s-val">₹<?php echo number_format($total_fee); ?></div>
                        </div>
                        <div class="summary-item">
                            <span class="s-label">Amount Paid</span>
                            <div class="s-val" style="color:var(--success)">₹<?php echo number_format($total_paid); ?></div>
                        </div>
                        <div class="summary-item">
                            <span class="s-label">Balance Due</span>
                            <div class="s-val" style="color:var(--danger)">₹<?php echo number_format($pending_fee); ?></div>
                        </div>
                    </div>
                    <div style="margin-top:20px; text-align:right;">
                        <a href="fees.php" style="color:var(--primary); text-decoration:none; font-size:14px; font-weight:700;">View History & Pay →</a>
                    </div>
                </div>

                <!-- Profile Info -->
                <div class="hub-card">
                    <div class="hub-card-header">
                        <i data-lucide="user"></i>
                        <h3>Personal Details</h3>
                    </div>
                    <div class="info-grid">
                        <div class="info-item"><label>Enrollment Number</label><div><?php echo htmlspecialchars($student['enrollment_no']); ?></div></div>
                        <div class="info-item"><label>Father's Name</label><div><?php echo htmlspecialchars($student['father_name']); ?></div></div>
                        <div class="info-item"><label>Mother's Name</label><div><?php echo htmlspecialchars($student['mother_name']); ?></div></div>
                        <div class="info-item"><label>Date of Birth</label><div><?php echo (!empty($student['dob'])) ? date('d M, Y', strtotime($student['dob'])) : 'N/A'; ?></div></div>
                        <div class="info-item"><label>Category</label><div><?php echo htmlspecialchars($student['category']); ?></div></div>
                        <div class="info-item"><label>Mobile</label><div><?php echo htmlspecialchars($student['mobile']); ?></div></div>
                        <div class="info-item"><label>Email</label><div><?php echo htmlspecialchars($student['email']); ?></div></div>
                        <div class="info-item"><label>Address</label><div><?php echo htmlspecialchars($student['city'] . ', ' . $student['state']); ?></div></div>
                    </div>
                </div>

                <div class="hub-card">
                    <div class="hub-card-header">
                        <i data-lucide="graduation-cap"></i>
                        <h3>Academic Information</h3>
                    </div>
                    <div class="info-grid">
                        <div class="info-item"><label>Selected Course</label><div><?php echo htmlspecialchars($student['course_name'] ?? 'N/A'); ?></div></div>
                        <div class="info-item"><label>Academic Session</label><div><?php echo htmlspecialchars($student['session_name'] ?? 'N/A'); ?></div></div>
                        <div class="info-item"><label>Admission Year</label><div><?php echo htmlspecialchars($student['passing_year'] ?? 'N/A'); ?></div></div>
                        <div class="info-item"><label>Center ID</label><div><?php echo htmlspecialchars($student['center_id'] ?? 'N/A'); ?></div></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
