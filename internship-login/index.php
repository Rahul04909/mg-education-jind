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

// 1. Fetch Student Details
// Join with internships table to get internship title
$sql_s = "SELECT s.*, i.title as internship_title, sess.session_name 
          FROM internship_enrollments s 
          LEFT JOIN internships i ON s.internship_id = i.id 
          LEFT JOIN internship_sessions sess ON s.session_id = sess.id
          WHERE s.id = $student_id";
$res_s = $conn->query($sql_s);
$student = $res_s->fetch_assoc();

// 2. Fetch Exam Schedule (Question Papers)
$exams = [];
$internship_id = $student['internship_id'];
$session_id = $student['session_id'];

if ($internship_id > 0 && $session_id > 0) {
    // There is no separate 'exam_schedules' for internships, the 'internship_question_papers' acts as the schedule
    // because it has exam_date, start_time, etc.
    $sql_e = "SELECT iqp.*, i.title as internship_title 
              FROM internship_question_papers iqp 
              JOIN internships i ON iqp.internship_id = i.id 
              WHERE iqp.internship_id = $internship_id AND iqp.session_id = $session_id
              ORDER BY iqp.exam_date ASC, iqp.start_time ASC";
    $res_e = $conn->query($sql_e);
    if($res_e) {
        while($row = $res_e->fetch_assoc()) {
            $exams[] = $row;
        }
    }
}

// 3. Fetch Attempted Exams
$attempted_map = [];
$att_sql = "SELECT internship_paper_id, status, obtained_marks, total_marks FROM internship_results WHERE student_id = $student_id";
$att_res = $conn->query($att_sql);
if($att_res) {
    while($row = $att_res->fetch_assoc()) {
        $attempted_map[$row['internship_paper_id']] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Internship Dashboard - MG Skills</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        :root { --primary: #6366f1; --bg: #f8fafc; --text: #0f172a; --card-bg: #fff; --border: #e2e8f0; }
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family: 'Outfit', sans-serif; background: var(--bg); color: var(--text); padding-bottom: 50px; }

        /* Header */
        /* Header Styles moved to header.php */

        /* Dashboard Layout */
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

        /* Exam List */
        .dashboard-content h2 { font-size: 24px; font-weight: 700; margin-bottom: 20px; }
        .exam-grid { display: grid; gap: 20px; }

        .exam-card { background: var(--card-bg); border: 1px solid var(--border); border-radius: 12px; padding: 25px; transition: 0.2s; position: relative; overflow: hidden; }
        .exam-card:hover { transform: translateY(-3px); box-shadow: 0 10px 30px -10px rgba(0,0,0,0.08); border-color: var(--primary); }
        
        .exam-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 15px; }
        .subject-badge { background: #e0e7ff; color: var(--primary); font-size: 12px; font-weight: 700; padding: 5px 10px; border-radius: 6px; text-transform: uppercase; }
        
        .exam-title { font-size: 20px; font-weight: 700; margin-bottom: 10px; }
        .exam-meta { display: flex; gap: 20px; color: #64748b; font-size: 14px; margin-bottom: 20px; }
        .meta-icon { display: flex; align-items: center; gap: 6px; }

        .exam-actions { display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #f1f5f9; padding-top: 20px; }
        .countdown { font-family: monospace; font-size: 15px; font-weight: 600; color: #ef4444; background: #fef2f2; padding: 6px 12px; border-radius: 6px; display: inline-flex; align-items: center; gap: 6px; }
        
        .btn-exam { padding: 10px 24px; border-radius: 8px; font-weight: 600; text-decoration: none; font-size: 14px; transition: 0.2s; border: none; cursor: pointer; }
        .btn-start { background: var(--primary); color: white; box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3); }
        .btn-start:hover { background: #4f46e5; transform: translateY(-1px); }
        .btn-disabled { background: #e2e8f0; color: #94a3b8; cursor: not-allowed; box-shadow: none; pointer-events: none; }
        
        .status-badge { padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: 700; text-transform: uppercase; }
        .status-upcoming { background: #fef3c7; color: #d97706; }
        .status-live { background: #dcfce7; color: #166534; animation: pulse 2s infinite; }
        .status-completed { background: #f1f5f9; color: #64748b; }

        @keyframes pulse { 0% { opacity: 1; } 50% { opacity: 0.7; } 100% { opacity: 1; } }

        @media (max-width: 1024px) {
            .container { grid-template-columns: 1fr; }
            .profile-card { position: static; display: flex; flex-direction: column; align-items: center; }
            .profile-meta { width: 100%; max-width: 500px; }
        }
    </style>
</head>
<body>

    <!-- Header -->
    <!-- Header -->
    <?php include 'header.php'; ?>

    <!-- Content -->
    <div class="container">
        <!-- Sidebar / Profile -->
        <aside>
            <div class="profile-card">
                <div class="profile-img-box">
                    <img src="<?php echo $photo_url; ?>" class="profile-img">
                </div>
                <h3 class="profile-name"><?php echo htmlspecialchars($student['full_name']); ?></h3>
                <span class="profile-id"><?php echo htmlspecialchars($student['enrollment_no']); ?></span>
                
                <div class="profile-meta">
                    <div class="meta-item"><span class="meta-label">Internship</span><span class="meta-val"><?php echo htmlspecialchars($student['internship_title']); ?></span></div>
                    <div class="meta-item"><span class="meta-label">Session</span><span class="meta-val"><?php echo htmlspecialchars($student['session_name']); ?></span></div>
                    <div class="meta-item"><span class="meta-label">Category</span><span class="meta-val"><?php echo htmlspecialchars($student['category']); ?></span></div>
                </div>

                <div class="sign-box">
                    <?php if(!empty($student['student_sign']) && file_exists("../".$student['student_sign'])): ?>
                        <img src="../<?php echo $student['student_sign']; ?>" alt="Signature">
                    <?php else: ?>
                        <span style="color:#ccc; font-size:12px;">No Signature Uploaded</span>
                    <?php endif; ?>
                    <div class="sign-label">Digitally Verified</div>
                </div>
            </div>
        </aside>

        <!-- Main Dashboard -->
        <div class="dashboard-content">
            <h2>Your Internship Exams</h2>
            
            <div class="exam-grid">
                <?php if(empty($exams)): ?>
                    <div style="text-align:center; padding:50px; background:white; border-radius:12px; color:#64748b;">
                        <i data-lucide="calendar-off" style="width:40px; height:40px; margin-bottom:10px;"></i>
                        <p>No exams assigned for your session yet.</p>
                    </div>
                <?php else: ?>
                    <?php foreach($exams as $ex): 
                        $exam_dt = $ex['exam_date'] . ' ' . $ex['start_time'];
                        $exam_ts = strtotime($exam_dt);
                        $now_ts = time();
                        $end_ts = $exam_ts + ($ex['exam_duration'] * 60);

                        $status = 'upcoming';
                        $status_label = 'Upcoming';
                        if ($now_ts >= $exam_ts && $now_ts <= $end_ts) {
                            $status = 'live';
                            $status_label = 'Live Now';
                        } elseif ($now_ts > $end_ts) {
                            $status = 'completed';
                            $status_label = 'Completed';
                        }

                        // Check if already attempted/submitted
                        $is_attempted = isset($attempted_map[$ex['id']]);
                        if ($is_attempted) {
                            $status = 'completed';
                            $status_label = 'Submitted';
                        }

                        // Determine Button State
                        $btn_class = "btn-exam btn-disabled";
                        $btn_text = "Start Exam";
                        $btn_href = "#";

                        if ($status == 'live') {
                            $btn_class = "btn-exam btn-start";
                            // Distinguish params: using paper_id as the main ID because the paper itself is the schedule in internship model
                            $btn_href = "start-exam.php?paper_id=" . $ex['id'];
                        } elseif ($status == 'completed') {
                            $btn_class = "btn-exam";
                            $btn_text = "View Result";
                            $btn_href = "result.php?paper_id=" . $ex['id'];
                            
                            if (!$is_attempted && $now_ts > $end_ts) {
                                $btn_text = "Expired";
                                $btn_class = "btn-exam btn-disabled";
                                $btn_href = "#";
                            }
                        }
                    ?>
                    <div class="exam-card">
                        <div class="exam-header">
                            <span class="subject-badge"><?php echo htmlspecialchars($ex['internship_title']); ?></span>
                            <span class="status-badge status-<?php echo $status; ?>"><?php echo $status_label; ?></span>
                        </div>
                        
                        <h3 class="exam-title">Internship Assessment</h3>
                        
                        <div class="exam-meta">
                            <div class="meta-icon"><i data-lucide="calendar" width="16"></i> <?php echo date('d M, Y', strtotime($ex['exam_date'])); ?></div>
                            <div class="meta-icon"><i data-lucide="clock" width="16"></i> <?php echo date('h:i A', strtotime($ex['start_time'])); ?></div>
                            <div class="meta-icon"><i data-lucide="hourglass" width="16"></i> <?php echo $ex['exam_duration']; ?> Mins</div>
                        </div>

                        <div class="exam-actions">
                            <?php if($status == 'upcoming'): ?>
                                <div class="countdown" data-time="<?php echo $exam_ts; ?>">
                                    <i data-lucide="timer" width="14"></i> <span class="timer-display">Loading...</span>
                                </div>
                                <a href="#" class="btn-exam btn-disabled">Starts Soon</a>
                            <?php elseif($status == 'live'): ?>
                                <div style="color: #166534; font-weight:600; font-size:14px;">Exam is Live!</div>
                                <a href="<?php echo $btn_href; ?>" class="<?php echo $btn_class; ?>"><?php echo $btn_text; ?></a>
                            <?php else: ?>
                                <div style="color: #64748b; font-size:14px;">Total Marks: <?php echo $ex['total_marks']; ?></div>
                                <a href="<?php echo $btn_href; ?>" class="btn-exam" style="background:#f1f5f9; color:#0f172a;"><?php echo $btn_text; ?></a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        lucide.createIcons();

        // Countdown Timer Logic
        function updateTimers() {
            const now = Math.floor(Date.now() / 1000);
            
            document.querySelectorAll('.countdown').forEach(el => {
                const target = parseInt(el.getAttribute('data-time'));
                let diff = target - now;

                if (diff <= 0) {
                    el.innerHTML = "Do Refresh!";
                    return;
                }

                const d = Math.floor(diff / 86400);
                const h = Math.floor((diff % 86400) / 3600);
                const m = Math.floor((diff % 3600) / 60);
                const s = diff % 60;

                let str = "";
                if(d > 0) str += d + "d ";
                str += h + "h " + m + "m " + s + "s";
                
                el.querySelector('.timer-display').innerText = str;
            });
        }

        setInterval(updateTimers, 1000);
        updateTimers(); 
    </script>
</body>
</html>
