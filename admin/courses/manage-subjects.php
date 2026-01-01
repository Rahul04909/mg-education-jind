<?php
// Include database configuration
require_once __DIR__ . '/../../database/db-config.php';
// Ensure schema is updated
require_once __DIR__ . '/../../database/update_subjects_schema.php';

$conn = getDbConnection();
$success_message = '';
$error_message = '';

// Handle Actions (Add, Edit, Delete)
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $course_id = intval($_POST['course_id']);
        $name = mysqli_real_escape_string($conn, $_POST['name']);
        $code = mysqli_real_escape_string($conn, $_POST['code']);
        $theory_marks = intval($_POST['theory_marks']);
        $assignment_marks = intval($_POST['assignment_marks']);
        $passing_marks = intval($_POST['passing_marks']);

        $sql = "INSERT INTO subjects (course_id, name, code, theory_marks, assignment_marks, passing_marks) 
                VALUES ($course_id, '$name', '$code', $theory_marks, $assignment_marks, $passing_marks)";
        
        if ($conn->query($sql) === TRUE) {
            $success_message = "Subject added successfully!";
        } else {
            $error_message = "Error adding subject: " . $conn->error;
        }

    } elseif ($action === 'edit') {
        $id = intval($_POST['subject_id']);
        $course_id = intval($_POST['course_id']);
        $name = mysqli_real_escape_string($conn, $_POST['name']);
        $code = mysqli_real_escape_string($conn, $_POST['code']);
        $theory_marks = intval($_POST['theory_marks']);
        $assignment_marks = intval($_POST['assignment_marks']);
        $passing_marks = intval($_POST['passing_marks']);

        $sql = "UPDATE subjects SET 
                course_id=$course_id, 
                name='$name', 
                code='$code', 
                theory_marks=$theory_marks, 
                assignment_marks=$assignment_marks,
                passing_marks=$passing_marks 
                WHERE id=$id";

        if ($conn->query($sql) === TRUE) {
            $success_message = "Subject updated successfully!";
        } else {
            $error_message = "Error updating subject: " . $conn->error;
        }

    } elseif ($action === 'delete') {
        $id = intval($_POST['subject_id']);
        $sql = "DELETE FROM subjects WHERE id=$id";
        if ($conn->query($sql) === TRUE) {
            $success_message = "Subject deleted successfully!";
        } else {
            $error_message = "Error deleting subject: " . $conn->error;
        }
    }
}

// Fetch Courses for Dropdown
$courses_result = $conn->query("SELECT id, title FROM courses ORDER BY title ASC");
$courses = [];
while ($row = $courses_result->fetch_assoc()) {
    $courses[] = $row;
}

// Filter Logic
$selected_course_id = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;

// Fetch Subjects
$sql_subjects = "SELECT s.*, c.title as course_name 
                 FROM subjects s 
                 LEFT JOIN courses c ON s.course_id = c.id";

if ($selected_course_id > 0) {
    $sql_subjects .= " WHERE s.course_id = $selected_course_id";
}
$sql_subjects .= " ORDER BY s.course_id ASC, s.name ASC";

$result_subjects = $conn->query($sql_subjects);

include __DIR__ . "/../sidebar.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Subjects - MG Education</title>
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
        .grid-4{display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:20px}
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
                    <a href="../index.php" style="text-decoration:none;color:var(--indigo)">Dashboard</a> › Courses › Manage Subjects
                </div>
                <h1 class="page-title">Manage Subjects</h1>
                <p style="color:var(--muted)">Add and manage subjects for your courses</p>
            </div>
            <div>
                <button class="btn btn-primary" onclick="openAddModal()">+ Add New Subject</button>
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
                    <a href="manage-subjects.php" class="btn btn-sm" style="background:#f1f5f9; color:var(--text)">Reset</a>
                </div>
            </form>
        </div>

        <!-- Subjects List -->
        <div class="card">
            <h3 style="font-size:18px; font-weight:700; margin-bottom:15px;">Subjects List</h3>
            <table class="table">
                <thead>
                    <tr>
                        <th>Subject Name</th>
                        <th>Code</th>
                        <th>Course</th>
                        <th>Marks Distribution</th>
                        <th>Total Marks</th>
                        <th style="text-align:right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result_subjects->num_rows > 0): ?>
                        <?php while($row = $result_subjects->fetch_assoc()): 
                            $total_marks = $row['theory_marks'] + $row['assignment_marks'];
                        ?>
                        <tr>
                            <td>
                                <div style="font-weight:700"><?php echo htmlspecialchars($row['name']); ?></div>
                            </td>
                            <td><span style="background:#f1f5f9; padding:4px 8px; border-radius:6px; font-size:12px; font-family:monospace;"><?php echo htmlspecialchars($row['code']); ?></span></td>
                            <td><?php echo htmlspecialchars($row['course_name']); ?></td>
                            <td>
                                <div style="font-size:12px; color:var(--muted)">
                                    Theory: <b><?php echo $row['theory_marks']; ?></b> | Assgn: <b><?php echo $row['assignment_marks']; ?></b> | Pass: <b><?php echo $row['passing_marks']; ?></b>
                                </div>
                            </td>
                            <td><span style="font-weight:700; color:var(--active)"><?php echo $total_marks; ?></span></td>
                            <td style="text-align:right">
                                <button class="btn btn-sm btn-primary" style="background:none; color:var(--indigo); border:1px solid var(--line)" onclick='openEditModal(<?php echo htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8'); ?>)'>Edit</button>
                                <form method="POST" style="display:inline-block;" onsubmit="return confirm('Are you sure you want to delete this subject?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="subject_id" value="<?php echo $row['id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-danger" style="background:none; color:var(--error); border:none">Delete</button>
                                </form>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="6" style="text-align:center;padding:40px;color:var(--muted)">No subjects found. Add a subject to get started.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

    <!-- Add/Edit Modal -->
    <div id="subjectModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2 id="modalTitle" style="margin-bottom:20px;">Add New Subject</h2>
            <form method="POST" id="subjectForm">
                <input type="hidden" name="action" id="formAction" value="add">
                <input type="hidden" name="subject_id" id="subjectId">

                <div class="form-group">
                    <label class="form-label">Select Course *</label>
                    <select name="course_id" id="course_id" class="form-select" required>
                        <option value="">-- Select Course --</option>
                        <?php foreach($courses as $c): ?>
                            <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['title']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="grid-2">
                    <div class="form-group">
                        <label class="form-label">Subject Name *</label>
                        <input type="text" name="name" id="name" class="form-input" required placeholder="e.g. Data Structures">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Subject Code</label>
                        <input type="text" name="code" id="code" class="form-input" placeholder="e.g. CS101">
                    </div>
                </div>

                <div class="grid-2">
                    <div class="form-group">
                        <label class="form-label">Theory Marks</label>
                        <input type="number" name="theory_marks" id="theory_marks" class="form-input" value="70" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Assignment Marks</label>
                        <input type="number" name="assignment_marks" id="assignment_marks" class="form-input" value="30" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Passing Marks</label>
                    <input type="number" name="passing_marks" id="passing_marks" class="form-input" value="33" required>
                </div>

                <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center;">Save Subject</button>
            </form>
        </div>
    </div>

    <script>
        const modal = document.getElementById("subjectModal");
        
        function openAddModal() {
            document.getElementById('modalTitle').innerText = "Add New Subject";
            document.getElementById('formAction').value = "add";
            document.getElementById('subjectId').value = "";
            document.getElementById('subjectForm').reset();
            
            // Pre-select course from filter if available
            const urlParams = new URLSearchParams(window.location.search);
            const courseId = urlParams.get('course_id');
            if(courseId) {
                document.getElementById('course_id').value = courseId;
            }
            
            modal.style.display = "block";
        }

        function openEditModal(data) {
            document.getElementById('modalTitle').innerText = "Edit Subject";
            document.getElementById('formAction').value = "edit";
            document.getElementById('subjectId').value = data.id;
            
            document.getElementById('course_id').value = data.course_id;
            document.getElementById('name').value = data.name;
            document.getElementById('code').value = data.code;
            document.getElementById('theory_marks').value = data.theory_marks;
            document.getElementById('assignment_marks').value = data.assignment_marks;
            document.getElementById('passing_marks').value = data.passing_marks || 33;

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
