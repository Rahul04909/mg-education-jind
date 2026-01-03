<?php
// Include database configuration
require_once __DIR__ . '/../database/db-config.php';
$conn = getDbConnection();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
if (!isset($_SESSION['center_id'])) {
    header("Location: login.php");
    exit();
}
$center_id = $_SESSION['center_id'];

$success_message = '';
$error_message = '';

// Handle Marking
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action']) && $_POST['action'] === 'grade_submission') {
    $submission_id = intval($_POST['submission_id']);
    $marks = intval($_POST['marks']);
    $max_marks = intval($_POST['max_marks']);

    if ($marks > $max_marks) {
        $error_message = "Obtained marks cannot be greater than Total Marks ($max_marks).";
    } else {
        $sql = "UPDATE student_assignments SET marks_obtained = $marks, status = 'GRADED' WHERE id = $submission_id";
        if ($conn->query($sql) === TRUE) {
            $success_message = "Marks assigned successfully!";
        } else {
            $error_message = "Error assigning marks: " . $conn->error;
        }
    }
}

// Fetch Courses for Filter
$courses = [];
$c_res = $conn->query("SELECT id, title FROM courses ORDER BY title ASC");
while ($r = $c_res->fetch_assoc()) $courses[] = $r;

// Filter Logic
$selected_course_id = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;
$selected_status = isset($_GET['status']) ? $_GET['status'] : '';

// Fetch Submissions for THIS CENTER only
$where_clause = "WHERE std.center_id = $center_id";

if ($selected_course_id > 0) {
    $where_clause .= " AND a.course_id = $selected_course_id";
}
if ($selected_status) {
    if ($selected_status == 'PENDING') {
         $where_clause .= " AND sa.status != 'GRADED'";
    } else {
         $where_clause .= " AND sa.status = '$selected_status'";
    }
}

$sql_submissions = "SELECT sa.*, a.title as assign_title, a.total_marks, s.name as subject_name, 
                           std.full_name as student_name, std.enrollment_no, c.title as course_name
                    FROM student_assignments sa
                    JOIN assignments a ON sa.assignment_id = a.id
                    JOIN subjects s ON a.subject_id = s.id
                    JOIN admissions std ON sa.student_id = std.id
                    JOIN courses c ON a.course_id = c.id
                    $where_clause
                    ORDER BY sa.submitted_at DESC";

$result_submissions = $conn->query($sql_submissions);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Assignments - MG Education</title>
    <style>
        :root{--active:#22c55e;--indigo:#6f75ff;--line:#e6e8ee;--text:#0b1020;--muted:#6f7787;--error:#ef4444;--success:#22c55e}
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;background:linear-gradient(180deg,#f8fafc 0%,#ffffff 100%);color:var(--text)}
        .admin-content{margin-left:280px;min-height:100vh;padding:20px;transition:margin-left .25s ease} /* Updated margin to match sidebar width 280px */
        body.sidebar-collapsed .admin-content{margin-left:88px}
        .page-header{margin-bottom:30px}
        .page-title{font-size:32px;font-weight:800;color:var(--text);margin-bottom:8px}
        .card{background:#fff;border:1px solid var(--line);border-radius:18px;padding:30px;box-shadow:0 4px 12px rgba(0,0,0,.05);margin-bottom:20px}
        .form-select, .form-input{padding:10px;border-radius:8px;border:1px solid var(--line)}
        .btn{display:inline-flex;align-items:center;gap:8px;padding:8px 16px;border-radius:8px;font-weight:700;font-size:13px;border:none;cursor:pointer;transition:all .2s ease;text-decoration:none}
        .btn-primary{background:linear-gradient(135deg,var(--indigo) 0%,#5a5fff 100%);color:#fff}
        .btn-success{background:var(--success);color:#fff}
        .table{width:100%;border-collapse:collapse;margin-top:20px}
        .table th{text-align:left;padding:16px;background:#f8fafc;font-size:13px;font-weight:700;border-bottom:1px solid var(--line)}
        .table td{padding:16px;border-bottom:1px solid var(--line);font-size:14px;vertical-align:middle}

        /* Modal */
        .modal {display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.5);}
        .modal-content {background-color: #fefefe; margin: 10% auto; padding: 20px; border: 1px solid #888; width: 400px; border-radius: 12px;}
        .close {color: #aaa; float: right; font-size: 28px; font-weight: bold; cursor: pointer;}
        .close:hover {color: black;}
    </style>
</head>
<body>
    <?php include __DIR__ . "/sidebar.php"; ?>
    <main class="admin-content">
        <div class="page-header">
            <div>
                <a href="../index.php" style="text-decoration:none;color:var(--indigo);font-size:14px">Dashboard</a> ›
                <h1 class="page-title">Review Assignments</h1>
                <p style="color:var(--muted)">Review and mark student assignments</p>
            </div>
        </div>

        <?php if ($success_message): ?><div style="padding:15px;background:#d1fae5;color:#065f46;border-radius:8px;margin-bottom:20px">✓ <?php echo $success_message; ?></div><?php endif; ?>
        <?php if ($error_message): ?><div style="padding:15px;background:#fee2e2;color:#991b1b;border-radius:8px;margin-bottom:20px">⚠ <?php echo $error_message; ?></div><?php endif; ?>

        <!-- Filter -->
        <div class="card" style="padding:20px; display:flex; gap:20px; align-items:flex-end">
            <form method="GET" style="display:contents">
                <div>
                    <label style="display:block;font-size:13px;font-weight:700;margin-bottom:5px">Course</label>
                    <select name="course_id" class="form-select">
                        <option value="">All Courses</option>
                        <?php foreach($courses as $c): ?>
                            <option value="<?php echo $c['id']; ?>" <?php echo $selected_course_id == $c['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($c['title']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label style="display:block;font-size:13px;font-weight:700;margin-bottom:5px">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="SUBMITTED" <?php echo $selected_status == 'SUBMITTED' ? 'selected' : ''; ?>>Submitted (Pending Grading)</option>
                        <option value="GRADED" <?php echo $selected_status == 'GRADED' ? 'selected' : ''; ?>>Graded</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary" style="height:38px">Filter</button>
                <a href="review-mark-assignment.php" class="btn" style="background:#f1f5f9;color:var(--text);height:38px">Reset</a>
            </form>
        </div>

        <div class="card">
            <table class="table">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Assignment</th>
                        <th>Submitted At</th>
                        <th>File</th>
                        <th>Marks</th>
                        <th>Status</th>
                        <th style="text-align:right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result_submissions->num_rows > 0): ?>
                        <?php while($row = $result_submissions->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <div style="font-weight:700"><?php echo htmlspecialchars($row['student_name']); ?></div>
                                <div style="font-size:12px;color:var(--muted)"><?php echo htmlspecialchars($row['enrollment_no']); ?></div>
                            </td>
                            <td>
                                <div style="font-weight:700"><?php echo htmlspecialchars($row['assign_title']); ?></div>
                                <div style="font-size:12px;color:var(--muted)"><?php echo htmlspecialchars($row['subject_name']); ?></div>
                            </td>
                            <td><?php echo date('M d, Y h:i A', strtotime($row['submitted_at'])); ?></td>
                            <td>
                                <a href="../../uploads/student_assignments/<?php echo $row['submission_file']; ?>" target="_blank" style="color:var(--indigo);">Download</a>
                            </td>
                            <td>
                                <span style="font-weight:700"><?php echo $row['marks_obtained'] !== NULL ? $row['marks_obtained'] : '-'; ?></span> / <?php echo $row['total_marks']; ?>
                            </td>
                            <td>
                                <?php if($row['status'] == 'GRADED'): ?>
                                    <span style="color:var(--success);font-weight:700">Graded</span>
                                <?php else: ?>
                                    <span style="color:#d97706;font-weight:700">Pending</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align:right">
                                <button class="btn btn-primary" onclick="openGradeModal(<?php echo $row['id']; ?>, <?php echo $row['total_marks']; ?>, '<?php echo htmlspecialchars($row['student_name'], ENT_QUOTES); ?>')">
                                    <?php echo $row['status'] == 'GRADED' ? 'Update Marks' : 'Grade'; ?>
                                </button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="7" style="text-align:center;padding:30px;color:var(--muted)">No submissions found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

    <!-- Grade Modal -->
    <div id="gradeModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2 style="margin-bottom:15px;">Assign Marks</h2>
            <p style="margin-bottom:15px;color:var(--muted)">Student: <span id="modalStudent" style="font-weight:700;color:var(--text)"></span></p>
            
            <form method="POST">
                <input type="hidden" name="action" value="grade_submission">
                <input type="hidden" name="submission_id" id="submission_id">
                <input type="hidden" name="max_marks" id="max_marks">

                <div style="margin-bottom:20px;">
                    <label style="display:block;font-weight:700;margin-bottom:8px">Marks Obtained (Max: <span id="displayMax"></span>)</label>
                    <input type="number" name="marks" id="marks_input" class="form-input" style="width:100%" required min="0">
                </div>

                <button type="submit" class="btn btn-success" style="width:100%;justify-content:center">Save Marks</button>
            </form>
        </div>
    </div>

    <script>
        const modal = document.getElementById("gradeModal");
        
        function openGradeModal(id, max, studentName) {
            document.getElementById('submission_id').value = id;
            document.getElementById('max_marks').value = max;
            document.getElementById('displayMax').innerText = max;
            document.getElementById('marks_input').max = max;
            document.getElementById('modalStudent').innerText = studentName;
            
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
