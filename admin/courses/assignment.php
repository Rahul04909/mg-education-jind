<?php
// Include database configuration
require_once __DIR__ . '/../../database/db-config.php';
// Ensure schema is updated
require_once __DIR__ . '/../../database/update_assignment_schema.php';

$conn = getDbConnection();
$success_message = '';
$error_message = '';

// Handle Actions
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $course_id = intval($_POST['course_id']);
        $session_id = intval($_POST['session_id']);
        $title = mysqli_real_escape_string($conn, $_POST['title']);
        $start_date = mysqli_real_escape_string($conn, $_POST['start_date']);
        $last_date = mysqli_real_escape_string($conn, $_POST['last_date']);
        
        // Handle File Upload
        $target_dir = "../../uploads/assignments/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $file_name = time() . '_' . basename($_FILES["pdf_file"]["name"]);
        $target_file = $target_dir . $file_name;
        $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        
        if ($file_type != "pdf") {
            $error_message = "Only PDF files are allowed.";
        } else {
            if (move_uploaded_file($_FILES["pdf_file"]["tmp_name"], $target_file)) {
                $sql = "INSERT INTO assignments (course_id, session_id, title, start_date, last_date, pdf_file) 
                        VALUES ($course_id, $session_id, '$title', '$start_date', '$last_date', '$file_name')";
                
                if ($conn->query($sql) === TRUE) {
                    $success_message = "Assignment created successfully!";
                } else {
                    $error_message = "Error creating assignment: " . $conn->error;
                    unlink($target_file); // Remove file if db insert fails
                }
            } else {
                $upload_error = $_FILES["pdf_file"]["error"];
                if ($upload_error == UPLOAD_ERR_INI_SIZE || $upload_error == UPLOAD_ERR_FORM_SIZE) {
                    $max_size = ini_get('upload_max_filesize');
                    $error_message = "File is too large. Maximum allowed size is $max_size.";
                } elseif ($upload_error == UPLOAD_ERR_NO_FILE) {
                    $error_message = "No file was uploaded.";
                } else {
                    $error_message = "Error uploading file. Error Code: $upload_error.";
                    // Log details for debugging
                    error_log("Upload Error: Code $upload_error, Path $target_file, Tmp " . $_FILES["pdf_file"]["tmp_name"]);
                }
            }
        }

    } elseif ($action === 'delete') {
        $id = intval($_POST['assignment_id']);
        
        // Get file name to delete
        $sql_file = "SELECT pdf_file FROM assignments WHERE id=$id";
        $res_file = $conn->query($sql_file);
        if ($res_file->num_rows > 0) {
            $row_file = $res_file->fetch_assoc();
            $file_path = "../../uploads/assignments/" . $row_file['pdf_file'];
            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }

        $sql = "DELETE FROM assignments WHERE id=$id";
        if ($conn->query($sql) === TRUE) {
            $success_message = "Assignment deleted successfully!";
        } else {
            $error_message = "Error deleting assignment: " . $conn->error;
        }
    }
}

// Fetch Courses for Dropdown
$courses_result = $conn->query("SELECT id, title FROM courses ORDER BY title ASC");
$courses = [];
while ($row = $courses_result->fetch_assoc()) {
    $courses[] = $row;
}

// Fetch Sessions
$sessions = [];
$s_sql = "SELECT id, course_id, session_name FROM course_sessions WHERE is_active = 1 ORDER BY id DESC";
$s_res = $conn->query($s_sql);
while($row = $s_res->fetch_assoc()) {
    $sessions[$row['course_id']][] = $row;
}

// Filter Logic
$selected_course_id = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;

// Fetch Assignments
$sql_assignments = "SELECT a.*, c.title as course_name, cs.session_name 
                    FROM assignments a 
                    JOIN courses c ON a.course_id = c.id 
                    LEFT JOIN course_sessions cs ON a.session_id = cs.id";

if ($selected_course_id > 0) {
    $sql_assignments .= " WHERE c.id = $selected_course_id";
}

$sql_assignments .= " ORDER BY a.created_at DESC";

$result_assignments = $conn->query($sql_assignments);

include __DIR__ . "/../sidebar.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assignments - MG Education</title>
    <style>
        :root{--active:#22c55e;--indigo:#6f75ff;--line:#e6e8ee;--text:#0b1020;--muted:#6f7787;--error:#ef4444;--success:#22c55e}
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;background:linear-gradient(180deg,#f8fafc 0%,#ffffff 100%);color:var(--text)}
        .admin-content{margin-left:260px;min-height:100vh;padding:20px;transition:margin-left .25s ease}
        body.sidebar-collapsed .admin-content{margin-left:88px}
        .page-header{margin-bottom:30px; display:flex; justify-content:space-between; align-items:center}
        .page-title{font-size:32px;font-weight:800;color:var(--text);margin-bottom:8px}
        .card{background:#fff;border:1px solid var(--line);border-radius:18px;padding:30px;box-shadow:0 4px 12px rgba(0,0,0,.05);margin-bottom:20px}
        .form-group{margin-bottom:20px}
        .form-label{display:block;font-weight:700;color:var(--text);margin-bottom:8px;font-size:14px}
        .form-input, .form-select{width:100%;padding:12px 16px;border:1px solid var(--line);border-radius:10px;font-size:15px;transition:all .2s ease;font-family:inherit;background:#fff}
        .form-input:focus, .form-select:focus{outline:none;border-color:var(--indigo);box-shadow:0 0 0 3px rgba(111,117,255,.1)}
        .btn{display:inline-flex;align-items:center;gap:8px;padding:12px 24px;border-radius:12px;font-weight:700;font-size:14px;border:none;cursor:pointer;transition:all .2s ease;text-decoration:none}
        .btn-primary{background:linear-gradient(135deg,var(--indigo) 0%,#5a5fff 100%);color:#fff}
        .btn-danger{background:var(--error);color:#fff}
        .btn-sm{padding:8px 16px; font-size:13px;}
        .grid-2{display:grid;grid-template-columns:1fr 1fr;gap:20px}
        .alert{padding:16px 20px;border-radius:12px;margin-bottom:20px;display:flex;align-items:center;gap:12px;border:1px solid}
        .alert-success{background:#d1fae5;border-color:#86efac;color:#065f46}
        .alert-error{background:#fee2e2;border-color:#fca5a5;color:#991b1b}
        .table{width:100%;border-collapse:collapse;margin-top:20px}
        .table th{text-align:left;padding:16px;background:#f8fafc;font-size:13px;font-weight:700;border-bottom:1px solid var(--line)}
        .table td{padding:16px;border-bottom:1px solid var(--line);font-size:14px;vertical-align:middle}
        
        /* Modal Styles */
        .modal {display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.5);}
        .modal-content {background-color: #fefefe; margin: 10% auto; padding: 20px; border: 1px solid #888; width: 500px; border-radius: 12px; position: relative;}
        .close {color: #aaa; float: right; font-size: 28px; font-weight: bold; cursor: pointer;}
        .close:hover {color: black;}
    </style>
</head>
<body>
    <main class="admin-content">
        <div class="page-header">
            <div>
                <div style="font-size:14px;color:var(--muted);margin-bottom:10px">
                    <a href="../index.php" style="text-decoration:none;color:var(--indigo)">Dashboard</a> › Courses › Assignments
                </div>
                <h1 class="page-title">Assignments</h1>
                <p style="color:var(--muted)">Create and manage course assignments</p>
            </div>
            <div>
                <button class="btn btn-primary" onclick="openAddModal()">+ Create Assignment</button>
            </div>
        </div>

        <?php if (!empty($success_message)): ?>
        <div class="alert alert-success"><span>✓ <?php echo $success_message; ?></span></div>
        <?php endif; ?>

        <?php if (!empty($error_message)): ?>
        <div class="alert alert-error"><span>⚠ <?php echo $error_message; ?></span></div>
        <?php endif; ?>

        <!-- Filter -->
        <div class="card" style="padding: 20px;">
            <form method="GET" style="display:flex; align-items:center; gap:20px;">
                <div style="flex:1">
                    <label class="form-label" style="margin-bottom: 5px;">Filter by Course:</label>
                    <select name="course_id" class="form-select" onchange="this.form.submit()">
                        <option value="">All Courses</option>
                        <?php foreach($courses as $c): ?>
                            <option value="<?php echo $c['id']; ?>" <?php echo $selected_course_id == $c['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($c['title']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="padding-top:20px;">
                    <a href="assignment.php" class="btn btn-sm" style="background:#f1f5f9; color:var(--text)">Reset</a>
                </div>
            </form>
        </div>

        <!-- Assignment List -->
        <div class="card">
            <h3 style="font-size:18px; font-weight:700; margin-bottom:15px;">Assignment List</h3>
            <table class="table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Course / Session</th>
                        <th>Start Date</th>
                        <th>Last Date</th>
                        <th>PDF</th>
                        <th style="text-align:right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result_assignments->num_rows > 0): ?>
                        <?php while($row = $result_assignments->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <div style="font-weight:700"><?php echo htmlspecialchars($row['title']); ?></div>
                            </td>
                            <td>
                                <div><?php echo htmlspecialchars($row['course_name']); ?></div>
                                <div style="font-size:12px;color:var(--muted)"><?php echo htmlspecialchars($row['session_name'] ?? 'N/A'); ?></div>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($row['start_date'])); ?></td>
                            <td><?php echo date('M d, Y', strtotime($row['last_date'])); ?></td>
                            <td>
                                <a href="../../uploads/assignments/<?php echo $row['pdf_file']; ?>" target="_blank" style="color:var(--indigo); text-decoration:none;">Download PDF</a>
                            </td>
                            <td style="text-align:right">
                                <form method="POST" style="display:inline-block;" onsubmit="return confirm('Are you sure you want to delete this assignment?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="assignment_id" value="<?php echo $row['id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-danger" style="background:none; color:var(--error); border:none">Delete</button>
                                </form>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="6" style="text-align:center;padding:40px;color:var(--muted)">No assignments found. Create an assignment.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

    <!-- Add Modal -->
    <div id="assignmentModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2 id="modalTitle" style="margin-bottom:20px;">Create Assignment</h2>
            <form method="POST" id="assignmentForm" enctype="multipart/form-data">
                <input type="hidden" name="action" value="add">

                <div class="form-group">
                    <label class="form-label">Select Course *</label>
                    <select name="course_id" id="course_id" class="form-select" required>
                        <option value="">-- Select Course --</option>
                        <?php foreach($courses as $c): ?>
                            <option value="<?php echo $c['id']; ?>">
                                <?php echo htmlspecialchars($c['title']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Select Session *</label>
                    <select name="session_id" id="session_id" class="form-select" required>
                        <option value="">-- Select Session --</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Assignment Title *</label>
                    <input type="text" name="title" class="form-input" required placeholder="Enter assignment title">
                </div>

                <div class="grid-2">
                    <div class="form-group">
                        <label class="form-label">Start Date *</label>
                        <input type="date" name="start_date" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Last Date of Submission *</label>
                        <input type="date" name="last_date" class="form-input" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Upload PDF *</label>
                    <input type="file" name="pdf_file" class="form-input" accept=".pdf" required>
                    <small style="color:var(--muted)">Only PDF files allowed</small>
                </div>

                <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center;">Create Assignment</button>
            </form>
        </div>
    </div>

    <script>
        const modal = document.getElementById("assignmentModal");
        const allSessions = <?php echo json_encode($sessions); ?>;

        document.getElementById('course_id').addEventListener('change', function() {
            const courseId = this.value;
            const sessionSelect = document.getElementById('session_id');
            sessionSelect.innerHTML = '<option value="">-- Select Session --</option>';

            if(courseId && allSessions[courseId]) {
                allSessions[courseId].forEach(sess => {
                    const opt = document.createElement('option');
                    opt.value = sess.id;
                    opt.textContent = sess.session_name;
                    sessionSelect.appendChild(opt);
                });
            }
        });

        function openAddModal() {
            document.getElementById('assignmentForm').reset();
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
