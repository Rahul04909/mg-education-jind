<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}

require_once __DIR__ . '/../database/db-config.php';
require_once __DIR__ . '/../database/update_student_assignment_schema.php';

$conn = getDbConnection();
$student_id = $_SESSION['student_id'];
$success_message = '';
$error_message = '';

// Get Student Details (Course & Session)
$stu_sql = "SELECT course_id, session_id FROM admissions WHERE id = $student_id";
$stu_res = $conn->query($stu_sql);
if ($stu_res->num_rows > 0) {
    $student_data = $stu_res->fetch_assoc();
    $course_id = $student_data['course_id'];
    $session_id = $student_data['session_id'];
} else {
    die("Student record not found.");
}

// Handle Submission
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action']) && $_POST['action'] === 'submit_assignment') {
    $assignment_id = intval($_POST['assignment_id']);
    
    // Validate Dates again
    $check_sql = "SELECT start_date, last_date FROM assignments WHERE id = $assignment_id";
    $check_res = $conn->query($check_sql);
    if($check_res->num_rows > 0) {
        $dates = $check_res->fetch_assoc();
        $today = date('Y-m-d');
        
        if ($today < $dates['start_date']) {
            $error_message = "Assignment submission has not started yet.";
        } elseif ($today > $dates['last_date']) {
            $error_message = "Assignment deadline has passed.";
        } else {
            // Process Upload
            $target_dir = "../uploads/student_assignments/";
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }

            $file_name = "stu_" . $student_id . "_assign_" . $assignment_id . "_" . time() . "_" . basename($_FILES["assignment_file"]["name"]);
            $target_file = $target_dir . $file_name;
            $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

            $allowed_types = ['pdf', 'doc', 'docx'];
            if (!in_array($file_type, $allowed_types)) {
                $error_message = "Only PDF, DOC, and DOCX files are allowed.";
            } else {
                if (move_uploaded_file($_FILES["assignment_file"]["tmp_name"], $target_file)) {
                    // Check if already submitted
                    $exist_sql = "SELECT id FROM student_assignments WHERE student_id = $student_id AND assignment_id = $assignment_id";
                    $exist_res = $conn->query($exist_sql);

                    if ($exist_res->num_rows > 0) {
                        // Update
                        $row = $exist_res->fetch_assoc();
                        $sub_id = $row['id'];
                        $sql = "UPDATE student_assignments SET submission_file = '$file_name', submitted_at = NOW(), status = 'SUBMITTED' WHERE id = $sub_id";
                    } else {
                        // Insert
                        $sql = "INSERT INTO student_assignments (assignment_id, student_id, submission_file, status) VALUES ($assignment_id, $student_id, '$file_name', 'SUBMITTED')";
                    }

                    if ($conn->query($sql) === TRUE) {
                        $success_message = "Assignment submitted successfully!";
                    } else {
                        $error_message = "Database error: " . $conn->error;
                    }
                } else {
                    $error_message = "Error uploading file.";
                }
            }
        }
    }
}

// Fetch Assignments for this student's course and session
$sql_assignments = "SELECT a.*, s.name as subject_name, sa.id as submission_id, sa.status as submission_status, sa.marks_obtained 
                    FROM assignments a
                    LEFT JOIN subjects s ON a.subject_id = s.id
                    LEFT JOIN student_assignments sa ON a.id = sa.assignment_id AND sa.student_id = $student_id
                    WHERE a.course_id = $course_id AND (a.session_id = $session_id OR a.session_id = 0)
                    ORDER BY a.last_date DESC";

$result_assignments = $conn->query($sql_assignments);

// Sidebar logic (simplified for student)
include __DIR__ . "/sidebar.php"; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Assignments - MG Education</title>
    <style>
        :root{--active:#22c55e;--indigo:#6f75ff;--line:#e6e8ee;--text:#0b1020;--muted:#6f7787;--error:#ef4444;--success:#22c55e}
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;background:linear-gradient(180deg,#f8fafc 0%,#ffffff 100%);color:var(--text)}
        .admin-content{margin-left:260px;min-height:100vh;padding:20px;transition:margin-left .25s ease}
        body.sidebar-collapsed .admin-content{margin-left:88px}
        .page-header{margin-bottom:30px}
        .page-title{font-size:32px;font-weight:800;color:var(--text);margin-bottom:8px}
        .card{background:#fff;border:1px solid var(--line);border-radius:18px;padding:30px;box-shadow:0 4px 12px rgba(0,0,0,.05);margin-bottom:20px}
        .btn{display:inline-flex;align-items:center;gap:8px;padding:10px 20px;border-radius:10px;font-weight:700;font-size:14px;border:none;cursor:pointer;transition:all .2s ease;text-decoration:none}
        .btn-primary{background:linear-gradient(135deg,var(--indigo) 0%,#5a5fff 100%);color:#fff}
        .btn-secondary{background:#f1f5f9;color:var(--text)}
        .status-badge{padding:6px 12px;border-radius:20px;font-size:12px;font-weight:700;display:inline-block}
        .status-submitted{background:#d1fae5;color:#065f46}
        .status-pending{background:#fef3c7;color:#92400e}
        .status-expired{background:#fee2e2;color:#991b1b}
        .status-graded{background:#e0e7ff;color:#3730a3}
        .table{width:100%;border-collapse:collapse;margin-top:20px}
        .table th{text-align:left;padding:16px;background:#f8fafc;font-size:13px;font-weight:700;border-bottom:1px solid var(--line)}
        .table td{padding:16px;border-bottom:1px solid var(--line);font-size:14px;vertical-align:middle}
        
        /* Modal */
        .modal {display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.5);}
        .modal-content {background-color: #fefefe; margin: 10% auto; padding: 20px; border: 1px solid #888; width: 500px; border-radius: 12px;}
        .close {color: #aaa; float: right; font-size: 28px; font-weight: bold; cursor: pointer;}
        .close:hover {color: black;}
    </style>
</head>
<body>
    <main class="admin-content">
        <div class="page-header">
            <h1 class="page-title">Assignments</h1>
            <p style="color:var(--muted)">View and submit your course assignments</p>
        </div>

        <?php if (!empty($success_message)): ?>
        <div class="alert" style="background:#d1fae5;padding:15px;border-radius:10px;margin-bottom:20px;color:#065f46">
            <?php echo $success_message; ?>
        </div>
        <?php endif; ?>

        <?php if (!empty($error_message)): ?>
        <div class="alert" style="background:#fee2e2;padding:15px;border-radius:10px;margin-bottom:20px;color:#991b1b">
            <?php echo $error_message; ?>
        </div>
        <?php endif; ?>

        <div class="card">
            <table class="table">
                <thead>
                    <tr>
                        <th>Details</th>
                        <th>Dates</th>
                        <th>Total Marks</th>
                        <th>Status</th>
                        <th>Obtained Marks</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result_assignments->num_rows > 0): ?>
                        <?php while($row = $result_assignments->fetch_assoc()): 
                            $today = date('Y-m-d');
                            $is_active = ($today >= $row['start_date'] && $today <= $row['last_date']);
                            $is_expired = ($today > $row['last_date']);
                            $status_label = "Pending";
                            $status_class = "status-pending";

                            if ($row['submission_status'] == 'GRADED') {
                                $status_label = "Graded";
                                $status_class = "status-graded";
                            } elseif ($row['submission_status'] == 'SUBMITTED') {
                                $status_label = "Submitted";
                                $status_class = "status-submitted";
                            } elseif ($is_expired) {
                                $status_label = "Expired";
                                $status_class = "status-expired";
                            } else {
                                $status_label = "Pending";
                                $status_class = "status-pending"; // Waiting submission
                            }
                        ?>
                        <tr>
                            <td>
                                <div style="font-weight:700"><?php echo htmlspecialchars($row['title']); ?></div>
                                <div style="font-size:12px;color:var(--muted)">Subject: <?php echo htmlspecialchars($row['subject_name']); ?></div>
                                <div style="margin-top:5px;">
                                    <a href="../uploads/assignments/<?php echo $row['pdf_file']; ?>" target="_blank" style="font-size:12px;color:var(--indigo);text-decoration:none;">Download Question PDF</a>
                                </div>
                            </td>
                            <td>
                                <div style="font-size:13px">Start: <?php echo date('M d, Y', strtotime($row['start_date'])); ?></div>
                                <div style="font-size:13px; color:<?php echo $is_expired ? 'var(--error)' : 'var(--text)'; ?>">End: <?php echo date('M d, Y', strtotime($row['last_date'])); ?></div>
                            </td>
                            <td><?php echo $row['total_marks']; ?></td>
                            <td><span class="status-badge <?php echo $status_class; ?>"><?php echo $status_label; ?></span></td>
                            <td style="font-weight:700;">
                                <?php echo ($row['marks_obtained'] !== NULL) ? $row['marks_obtained'] : '-'; ?>
                            </td>
                            <td>
                                <?php if ($is_active && $row['submission_status'] != 'GRADED'): ?>
                                    <button class="btn btn-primary" onclick="openUploadModal(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['title'], ENT_QUOTES); ?>')">
                                        <?php echo ($row['submission_status'] == 'SUBMITTED') ? 'Resubmit' : 'Submit'; ?>
                                    </button>
                                <?php else: ?>
                                    <button class="btn btn-secondary" disabled>
                                        <?php if ($is_expired) echo 'Expired'; elseif($today < $row['start_date']) echo 'Wait for Start'; else echo 'Closed'; ?>
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="6" style="text-align:center;padding:30px;color:var(--muted)">No assignments available.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

    <!-- Upload Modal -->
    <div id="uploadModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2 style="margin-bottom:15px;">Submit Assignment</h2>
            <p id="modalAssignTitle" style="margin-bottom:20px;color:var(--muted)"></p>
            
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="submit_assignment">
                <input type="hidden" name="assignment_id" id="assignment_id">
                
                <div style="margin-bottom:20px;">
                    <label style="display:block;font-weight:700;margin-bottom:8px">Upload Answer File (PDF/DOC)</label>
                    <input type="file" name="assignment_file" required style="width:100%;padding:10px;border:1px solid var(--line);border-radius:8px">
                </div>

                <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center">Upload Submission</button>
            </form>
        </div>
    </div>

    <script>
        const modal = document.getElementById("uploadModal");
        
        function openUploadModal(id, title) {
            document.getElementById('assignment_id').value = id;
            document.getElementById('modalAssignTitle').innerText = title;
            modal.style.display = "block";
        }

        function closeModal() {
            modal.style.display = "none";
        }

        window.onclick = function(event) {
            if (event.target == modal) {
                closeModal();
            }
        }
    </script>
</body>
</html>
<?php $conn->close(); ?>
