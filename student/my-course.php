<?php
session_start();
require_once __DIR__ . '/../database/db-config.php';

if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit;
}

$conn = getDbConnection();
$student_id = $_SESSION['student_id'];

// 1. Get Enrolled Course ID
$adm_sql = "SELECT course_id FROM admissions WHERE id = $student_id";
$adm_res = $conn->query($adm_sql);

if ($adm_res->num_rows == 0) {
    echo "Student record not found.";
    exit;
}

$course_id = $adm_res->fetch_assoc()['course_id'];

// 2. Get Course Details
$course_sql = "SELECT * FROM courses WHERE id = $course_id";
$course_res = $conn->query($course_sql);

if ($course_res->num_rows == 0) {
    // Handle case where course might have been deleted but admission exists?
    $course = null;
} else {
    $course = $course_res->fetch_assoc();
}

// 3. Get Subjects
$subjects = [];
if ($course) {
    $sub_sql = "SELECT * FROM subjects WHERE course_id = $course_id ORDER BY name ASC";
    $sub_res = $conn->query($sub_sql);
    while ($row = $sub_res->fetch_assoc()) {
        $subjects[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Course - MG Education</title>
    <style>
        :root {
            --primary: #a855f7;
            --primary-dark: #7e22ce;
            --secondary: #f472b6;
            --bg-body: #f8fafc;
            --text-main: #1e293b;
            --text-light: #64748b;
            --card-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
        }
        body { font-family: 'Outfit', sans-serif; background: var(--bg-body); color: var(--text-main); margin: 0; }
        .main-content { margin-left: 260px; padding: 30px 40px; min-height: 100vh; }
        
        /* Banner/Header */
        .page-header { margin-bottom: 30px; }
        .page-title { font-size: 28px; font-weight: 700; color: var(--text-main); margin-bottom: 5px; }
        .page-subtitle { color: var(--text-light); font-size: 15px; }

        /* Course Card */
        .course-card {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: var(--card-shadow);
            margin-bottom: 30px;
            position: relative;
            overflow: hidden;
            display: grid;
            grid-template-columns: 100px 1fr auto;
            gap: 30px;
            align-items: center;
        }
        
        .course-icon {
            width: 100px;
            height: 100px;
            border-radius: 16px;
            background: linear-gradient(135deg, #f0abfc 0%, #c026d3 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 40px;
            font-weight: 700;
        }

        .course-info h2 { font-size: 24px; font-weight: 700; margin: 0 0 10px 0; }
        .stats-row { display: flex; gap: 24px; }
        .stat-badge {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 6px 14px;
            border-radius: 30px;
            font-size: 13px;
            font-weight: 600;
        }
        .stat-badge.duration { background: #eff6ff; color: #2563eb; }
        .stat-badge.fees { background: #ecfdf5; color: #059669; }

        .btn-view-details {
            background: var(--primary);
            color: white;
            padding: 12px 24px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
            border:none;
            cursor: cursor;
        }
        .btn-view-details:hover { background: var(--primary-dark); transform: translateY(-2px); }

        /* Subjects Table */
        .subjects-section h3 { font-size: 20px; margin-bottom: 20px; font-weight: 700; }
        .subjects-card {
            background: white;
            border-radius: 20px;
            box-shadow: var(--card-shadow);
            overflow: hidden;
        }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 16px 24px; background: #f8fafc; color: var(--text-light); font-weight: 600; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 1px solid #e2e8f0; }
        td { padding: 18px 24px; border-bottom: 1px solid #f1f5f9; font-size: 15px; font-weight: 500; }
        tr:last-child td { border-bottom: none; }
        tr:hover td { background: #f8fafc; }

        .marks-tag {
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            background: #f3e8ff;
            color: var(--primary);
            display: inline-block;
            min-width: 40px;
            text-align: center;
        }

        @media (max-width: 768px) {
            .main-content { margin-left: 0; padding: 20px; }
            .course-card { grid-template-columns: 1fr; text-align: center; justify-items: center; padding: 20px; }
            .stats-row { justify-content: center; }
        }
    </style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<main class="main-content">
    <div class="page-header">
        <h1 class="page-title">My Course</h1>
        <div class="page-subtitle">Track your academic progress and course details</div>
    </div>

    <?php if ($course): ?>
        <?php 
            $fees_data = json_decode($course['fees'], true);
            $fees_amount = isset($fees_data['amount']) ? "₹" . number_format($fees_data['amount']) : "Free";
        ?>
        <div class="course-card">
            <div class="course-icon">
                <?php echo strtoupper(substr($course['title'], 0, 2)); ?>
            </div>
            <div class="course-info">
                <h2><?php echo htmlspecialchars($course['title']); ?></h2>
                <div class="stats-row">
                    <div class="stat-badge duration">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                        <?php echo $course['duration_value'] . ' ' . $course['duration_type']; ?>
                    </div>
                    <div class="stat-badge fees">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                        <?php echo $fees_amount; ?>
                    </div>
                </div>
            </div>
            <div>
                <!-- Assuming course-details.php exists or just a placeholder for now -->
                <a href="../course-details.php?id=<?php echo $course['id']; ?>" class="btn-view-details">
                    View Details
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                </a>
            </div>
        </div>

        <div class="subjects-section">
            <h3>Course Subjects & Marks</h3>
            <div class="subjects-card">
                <table>
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Subject Name</th>
                            <th>Theory Marks</th>
                            <th>Assignment Marks</th>
                            <th>Total Marks</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($subjects) > 0): ?>
                            <?php foreach ($subjects as $sub): ?>
                                <tr>
                                    <td style="color:var(--text-light); font-family:monospace;"><?php echo htmlspecialchars($sub['code']); ?></td>
                                    <td><?php echo htmlspecialchars($sub['name']); ?></td>
                                    <td><span class="marks-tag"><?php echo $sub['theory_marks']; ?></span></td>
                                    <td><span class="marks-tag"><?php echo $sub['assignment_marks']; ?></span></td>
                                    <td style="font-weight:700;"><?php echo ($sub['theory_marks'] + $sub['assignment_marks']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="5" style="text-align:center;">No subjects assigned to this course yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    <?php else: ?>
        <div style="text-align:center; padding:50px;">
            <h2>No Active Course Found</h2>
            <p style="color:var(--text-light);">You are not currently enrolled in any active course.</p>
        </div>
    <?php endif; ?>

</main>

<script>
    // Responsive sidebar function if needed (reused from dashboard)
</script>

</body>
</html>
