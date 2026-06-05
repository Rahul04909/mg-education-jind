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
$success_message = "";
$error_message = "";

// Handle Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_assignment'])) {
    $assignment_id = intval($_POST['assignment_id']);
    
    // Check if already submitted
    $chk = $conn->query("SELECT id FROM internship_submissions WHERE assignment_id = $assignment_id AND student_id = $student_id");
    if($chk->num_rows > 0) {
        $error_message = "You have already submitted this assignment.";
    } else {
        if(isset($_FILES['submission_file']) && $_FILES['submission_file']['error'] == 0) {
            $target_dir = "../assets/uploads/internship_submissions/";
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            $ext = pathinfo($_FILES['submission_file']['name'], PATHINFO_EXTENSION);
            $filename = "SUB_" . $student_id . "_" . $assignment_id . "_" . time() . "." . $ext;
            $target_file = $target_dir . $filename;
            
            if(move_uploaded_file($_FILES['submission_file']['tmp_name'], $target_file)) {
                $file_path = "assets/uploads/internship_submissions/" . $filename;
                $comments = mysqli_real_escape_string($conn, $_POST['comments']);
                
                $stmt = $conn->prepare("INSERT INTO internship_submissions (assignment_id, student_id, submission_file, comments) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("iiss", $assignment_id, $student_id, $file_path, $comments);
                
                if($stmt->execute()) {
                    $success_message = "Assignment submitted successfully!";
                } else {
                    $error_message = "Database error: " . $conn->error;
                }
            } else {
                $error_message = "Failed to upload file.";
            }
        } else {
            $error_message = "Please select a file to upload.";
        }
    }
}

// Fetch Student Info for Filtering and Header display
$stmt_s = $conn->prepare("SELECT * FROM internship_enrollments WHERE id = ?");
$stmt_s->bind_param("i", $student_id);
$stmt_s->execute();
$res_s = $stmt_s->get_result();
$student = $res_s->fetch_assoc();

$internship_id = $student['internship_id'];
$session_id = $student['session_id'];

// Fetch Assignments
// Logic: Assignments for this internship AND (session matches OR session is NULL)
// Also join with submissions to check status
$assignments = [];
$sql = "SELECT ia.*, s.id as submission_id, s.status, s.marks, s.submitted_at, s.submission_file as submitted_file 
        FROM internship_assignments ia 
        LEFT JOIN internship_submissions s ON ia.id = s.assignment_id AND s.student_id = $student_id
        WHERE ia.internship_id = $internship_id 
        AND (ia.session_id IS NULL OR ia.session_id = $session_id)
        AND ia.is_active = 1 
        ORDER BY ia.created_at DESC";

$result = $conn->query($sql);
if($result) {
    while($row = $result->fetch_assoc()) {
        $assignments[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Assignments - MG Skills</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        :root { --primary: #6366f1; --bg: #f8fafc; --text: #0f172a; --card-bg: #fff; --border: #e2e8f0; --success: #22c55e; --warning: #f59e0b; }
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family: 'Outfit', sans-serif; background: var(--bg); color: var(--text); padding-bottom: 50px; }
        
        .container { max-width: 1000px; margin: 30px auto; padding: 0 20px; }
        
        .page-header { margin-bottom: 30px; }
        .page-title { font-size: 28px; font-weight: 700; color: var(--text); }
        .page-subtitle { color: #64748b; font-size: 15px; margin-top: 5px; }

        .assignment-list { display: grid; gap: 20px; }
        
        .assign-card { background: var(--card-bg); border: 1px solid var(--border); border-radius: 16px; padding: 25px; transition: 0.2s; }
        .assign-card:hover { transform: translateY(-2px); box-shadow: 0 10px 25px -5px rgba(0,0,0,0.05); }
        
        .assign-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 15px; }
        .assign-title { font-size: 18px; font-weight: 700; color: var(--text); margin-bottom: 5px; }
        .assign-date { font-size: 13px; color: #94a3b8; display: flex; align-items: center; gap: 5px; }
        
        .status-badge { padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; text-transform: uppercase; }
        .status-pending { background: #fef3c7; color: #d97706; }
        .status-submitted { background: #dcfce7; color: #166534; }
        .status-graded { background: #e0e7ff; color: #4338ca; }
        
        .assign-body { color: #475569; font-size: 14px; line-height: 1.5; margin-bottom: 20px; }
        
        .assign-actions { display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #f1f5f9; padding-top: 20px; }
        
        .file-link { display: inline-flex; align-items: center; gap: 6px; color: var(--primary); font-weight: 500; text-decoration: none; font-size: 14px; background: #e0e7ff; padding: 6px 12px; border-radius: 8px; }
        .file-link:hover { background: #c7d2fe; }
        
        .btn-submit { background: var(--primary); color: white; padding: 8px 16px; border-radius: 8px; font-weight: 600; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; font-size: 14px; }
        .btn-submit:hover { background: #4f46e5; }
        
        /* Modal */
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center; }
        .modal.active { display: flex; }
        .modal-content { background: white; width: 100%; max-width: 500px; border-radius: 16px; padding: 30px; position: relative; animation: slideUp 0.3s ease; }
        .modal-close { position: absolute; top: 20px; right: 20px; cursor: pointer; color: #94a3b8; }
        
        @keyframes slideUp { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }

        .form-group { margin-bottom: 20px; }
        .form-label { display: block; font-weight: 600; margin-bottom: 8px; font-size: 14px; }
        .form-input, .form-textarea { width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px; font-family: inherit; }
        
        .alert { padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .alert-success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .alert-error { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
    </style>
</head>
<body>

    <?php include 'header.php'; ?>

    <div class="container">
        <div class="page-header">
            <h1 class="page-title">My Assignments</h1>
            <p class="page-subtitle">View and submit your internship tasks here.</p>
        </div>

        <?php if($success_message): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        <?php if($error_message): ?>
            <div class="alert alert-error"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <div class="assignment-list">
            <?php if(empty($assignments)): ?>
                <div style="text-align:center; padding:50px; background:white; border-radius:16px;">
                    <i data-lucide="folder-open" style="width:48px; height:48px; color:#cbd5e1; margin-bottom:10px;"></i>
                    <p style="color:#64748b;">No assignments found for you.</p>
                </div>
            <?php else: ?>
                <?php foreach($assignments as $assign): 
                    $is_submitted = !empty($assign['submission_id']);
                    $status = $assign['status'] ?? 'Pending';
                    $status_class = strtolower($status) == 'submitted' ? 'status-submitted' : (strtolower($status) == 'graded' ? 'status-graded' : 'status-pending');
                    if(!$is_submitted) { $status = 'Pending'; $status_class = 'status-pending'; }
                ?>
                <div class="assign-card">
                    <div class="assign-header">
                        <div>
                            <h3 class="assign-title"><?php echo htmlspecialchars($assign['title']); ?></h3>
                            <div class="assign-date">
                                <i data-lucide="calendar" width="14"></i> 
                                Posted: <?php echo date('d M, Y', strtotime($assign['created_at'])); ?>
                            </div>
                        </div>
                        <span class="status-badge <?php echo $status_class; ?>"><?php echo $status; ?></span>
                    </div>

                    <div class="assign-body">
                        <?php echo nl2br(htmlspecialchars($assign['description'])); ?>
                    </div>

                    <div class="assign-actions">
                        <a href="../<?php echo $assign['assignment_file']; ?>" target="_blank" class="file-link">
                            <i data-lucide="file-text" width="16"></i> View Assignment
                        </a>

                        <?php if($is_submitted): ?>
                             <div style="text-align:right;">
                                <div style="font-size:13px; color:#64748b;">Submitted on <?php echo date('d M, Y', strtotime($assign['submitted_at'])); ?></div>
                                <?php if($assign['marks']): ?>
                                    <div style="font-weight:700; color:var(--primary);">Marks: <?php echo $assign['marks']; ?></div>
                                <?php endif; ?>
                             </div>
                        <?php else: ?>
                            <button class="btn-submit" onclick="openModal(<?php echo $assign['id']; ?>, '<?php echo htmlspecialchars(addslashes($assign['title'])); ?>')">
                                <i data-lucide="upload-cloud" width="16"></i> Submit Work
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Submission Modal -->
    <div class="modal" id="submitModal">
        <div class="modal-content">
            <i data-lucide="x" class="modal-close" onclick="closeModal()"></i>
            <h3 style="margin-bottom:20px; font-size:20px;">Submit Assignment</h3>
            <p id="modalAssignTitle" style="color:#64748b; margin-bottom:20px; font-size:14px;"></p>
            
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="assignment_id" id="modalAssignId">
                <input type="hidden" name="submit_assignment" value="1">
                
                <div class="form-group">
                    <label class="form-label">Upload File</label>
                    <input type="file" name="submission_file" class="form-input" required>
                    <small style="color:#94a3b8; font-size:12px;">Allowed: PDF, Zip, Images.</small>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Comments (Optional)</label>
                    <textarea name="comments" class="form-textarea" rows="3" placeholder="Any notes for the instructor..."></textarea>
                </div>
                
                <button type="submit" class="btn-submit" style="width:100%; justify-content:center;">Submit Assignment</button>
            </form>
        </div>
    </div>

    <script>
        lucide.createIcons();

        function openModal(id, title) {
            document.getElementById('modalAssignId').value = id;
            document.getElementById('modalAssignTitle').innerText = title;
            document.getElementById('submitModal').classList.add('active');
        }

        function closeModal() {
            document.getElementById('submitModal').classList.remove('active');
        }

        // Close on outside click
        window.onclick = function(event) {
            let modal = document.getElementById('submitModal');
            if (event.target == modal) {
                closeModal();
            }
        }
    </script>
</body>
</html>
<?php $conn->close(); ?>
