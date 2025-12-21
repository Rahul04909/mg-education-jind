<?php
session_start();
require_once __DIR__ . '/../database/db-config.php';

if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit;
}

$student_id = $_SESSION['student_id'];
$student_name = $_SESSION['student_name'];
$enrollment_no = $_SESSION['enrollment_no'];

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
        :root{--primary:#6366f1;--secondary:#0f172a;--bg:#f8fafc;--white:#fff;--border:#e2e8f0;--success:#22c55e;}
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:'Outfit',sans-serif;background:var(--bg);color:var(--secondary);display:flex;min-height:100vh}
        
        /* Sidebar */
        .sidebar{width:260px;background:var(--white);border-right:1px solid var(--border);position:fixed;height:100vh;display:flex;flex-direction:column;z-index: 10;}
        .logo{padding:24px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:12px}
        .logo img{height:32px;}
        .logo span{font-weight:700;font-size:18px;color:var(--primary)}
        
        .nav-links{padding:20px;flex:1}
        .nav-item{display:flex;align-items:center;gap:12px;padding:12px 16px;color:#64748b;text-decoration:none;border-radius:8px;margin-bottom:4px;transition:0.2s}
        .nav-item:hover, .nav-item.active{background:#eff6ff;color:var(--primary)}
        .nav-item i{width:20px;height:20px}
        
        .user-profile{padding:20px;border-top:1px solid var(--border);display:flex;align-items:center;gap:12px}
        .user-avatar{width:40px;height:40px;border-radius:50%;background:var(--primary);color:var(--white);display:flex;align-items:center;justify-content:center;font-weight:700}
        .user-info{flex:1}
        .user-name{font-weight:600;font-size:14px}
        .user-role{font-size:12px;color:#64748b}
        
        /* Main Content */
        .main{margin-left:260px;flex:1;padding:30px}
        .header{display:flex;justify-content:space-between;align-items:center;margin-bottom:30px}
        .greeting h1{font-size:24px;font-weight:700;margin-bottom:4px}
        .greeting p{color:#64748b}
        
        .card-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:24px;margin-bottom:30px}
        .stat-card{background:var(--white);padding:24px;border-radius:16px;border:1px solid var(--border);display:flex;align-items:center;gap:20px}
        .stat-icon{width:50px;height:50px;border-radius:12px;background:#eff6ff;color:var(--primary);display:flex;align-items:center;justify-content:center}
        .stat-icon.green{background:#dcfce7;color:var(--success)}
        .stat-info h3{font-size:24px;font-weight:700;margin-bottom:4px}
        .stat-info p{color:#64748b;font-size:14px}
        
        .content-card{background:var(--white);border-radius:16px;border:1px solid var(--border);padding:24px;margin-bottom:24px}
        .card-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;padding-bottom:15px;border-bottom:1px solid var(--border)}
        .card-title{font-size:18px;font-weight:700}
        
        .detail-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:20px}
        .detail-item label{display:block;color:#64748b;font-size:13px;margin-bottom:4px}
        .detail-item div{font-weight:600}

        @media(max-width:1024px){
            .sidebar{display:none}
            .main{margin-left:0}
        }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="logo">
        <i data-lucide="graduation-cap" class="text-indigo-600"></i>
        <span>MG Student</span>
    </div>
    <div class="nav-links">
        <a href="index.php" class="nav-item active"><i data-lucide="layout-dashboard"></i> Dashboard</a>
        <a href="#" class="nav-item"><i data-lucide="book-open"></i> My Courses</a>
        <a href="#" class="nav-item"><i data-lucide="file-text"></i> Results</a>
        <a href="#" class="nav-item"><i data-lucide="award"></i> Certificates</a>
        <a href="logout.php" class="nav-item"><i data-lucide="log-out"></i> Logout</a>
    </div>
    <div class="user-profile">
        <div class="user-avatar"><?php echo strtoupper(substr($student_name, 0, 1)); ?></div>
        <div class="user-info">
            <div class="user-name"><?php echo htmlspecialchars($student_name); ?></div>
            <div class="user-role"><?php echo htmlspecialchars($enrollment_no); ?></div>
        </div>
    </div>
</div>

<main class="main">
    <div class="header">
        <div class="greeting">
            <h1>Welcome back, <?php echo htmlspecialchars($student_name); ?>! 👋</h1>
            <p>Here's what's happening with your learning journey.</p>
        </div>
        <button onclick="window.location.href='logout.php'" style="padding:10px 20px;background:var(--white);border:1px solid var(--border);border-radius:8px;cursor:pointer">Logout</button>
    </div>

    <div class="card-grid">
        <div class="stat-card">
            <div class="stat-icon"><i data-lucide="book"></i></div>
            <div class="stat-info"><h3><?php echo htmlspecialchars($course_name); ?></h3><p>Active Course</p></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon green"><i data-lucide="check-circle"></i></div>
            <div class="stat-info"><h3>Active</h3><p>Admission Status</p></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i data-lucide="calendar"></i></div>
            <div class="stat-info"><h3><?php echo $student['passing_year']; ?></h3><p>Batch Year</p></div>
        </div>
    </div>

    <div class="content-card">
        <div class="card-header"><h3 class="card-title">Student Profile</h3></div>
        <div class="detail-grid">
            <div class="detail-item"><label>Full Name</label><div><?php echo htmlspecialchars($student['full_name']); ?></div></div>
            <div class="detail-item"><label>Father's Name</label><div><?php echo htmlspecialchars($student['father_name']); ?></div></div>
            <div class="detail-item"><label>Mother's Name</label><div><?php echo htmlspecialchars($student['mother_name']); ?></div></div>
            <div class="detail-item"><label>Date of Birth</label><div><?php echo htmlspecialchars($student['dob']); ?></div></div>
            <div class="detail-item"><label>Category</label><div><?php echo htmlspecialchars($student['category']); ?></div></div>
            <div class="detail-item"><label>Mobile</label><div><?php echo htmlspecialchars($student['mobile']); ?></div></div>
            <div class="detail-item"><label>Email</label><div><?php echo htmlspecialchars($student['email']); ?></div></div>
            <div class="detail-item"><label>Address</label><div><?php echo htmlspecialchars($student['city'] . ', ' . $student['state']); ?></div></div>
        </div>
    </div>

</main>

<script>
    lucide.createIcons();
</script>
</body>
</html>
