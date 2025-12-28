<?php
// Include database configuration
require_once __DIR__ . '/../../database/db-config.php';
// Ensure schema is updated (in case they land here first)
require_once __DIR__ . '/../../database/update_subjects_schema.php';

$conn = getDbConnection();
$success_message = '';
$error_message = '';

// Handle Actions
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $subject_id = intval($_POST['subject_id']);
        $unit_title = mysqli_real_escape_string($conn, $_POST['unit_title']);
        $description = mysqli_real_escape_string($conn, $_POST['description']);
        
        $sql = "INSERT INTO syllabus (subject_id, unit_title, description) 
                VALUES ($subject_id, '$unit_title', '$description')";
        
        if ($conn->query($sql) === TRUE) {
            $success_message = "Syllabus topic added successfully!";
        } else {
            $error_message = "Error adding topic: " . $conn->error;
        }

    } elseif ($action === 'edit') {
        $id = intval($_POST['syllabus_id']);
        $subject_id = intval($_POST['subject_id']);
        $unit_title = mysqli_real_escape_string($conn, $_POST['unit_title']);
        $description = mysqli_real_escape_string($conn, $_POST['description']);

        $sql = "UPDATE syllabus SET 
                subject_id=$subject_id, 
                unit_title='$unit_title', 
                description='$description' 
                WHERE id=$id";

        if ($conn->query($sql) === TRUE) {
            $success_message = "Syllabus updated successfully!";
        } else {
            $error_message = "Error updating syllabus: " . $conn->error;
        }

    } elseif ($action === 'delete') {
        $id = intval($_POST['syllabus_id']);
        $sql = "DELETE FROM syllabus WHERE id=$id";
        if ($conn->query($sql) === TRUE) {
            $success_message = "Topic deleted successfully!";
        } else {
            $error_message = "Error deleting topic: " . $conn->error;
        }
    }
}

// Fetch Subjects for Dropdown and Filters
// We join with courses to show "Subject (Course)"
$subjects_result = $conn->query("SELECT s.id, s.name, s.code, c.title as course_name, s.course_id 
                                 FROM subjects s 
                                 JOIN courses c ON s.course_id = c.id 
                                 ORDER BY c.title ASC, s.name ASC");
$subjects = [];
$courses_filter = []; // To build a unique list of courses for the filter dropdown
while ($row = $subjects_result->fetch_assoc()) {
    $subjects[] = $row;
    $courses_filter[$row['course_id']] = $row['course_name'];
}

// Filter Logic
$selected_course_id = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;
$selected_subject_id = isset($_GET['subject_id']) ? intval($_GET['subject_id']) : 0;

// Fetch Syllabus List
$sql_syllabus = "SELECT syl.*, s.name as subject_name, s.code, c.title as course_name 
                 FROM syllabus syl 
                 JOIN subjects s ON syl.subject_id = s.id 
                 JOIN courses c ON s.course_id = c.id";

$where_clauses = [];
if ($selected_course_id > 0) {
    $where_clauses[] = "c.id = $selected_course_id";
}
if ($selected_subject_id > 0) {
    $where_clauses[] = "s.id = $selected_subject_id";
}

if (!empty($where_clauses)) {
    $sql_syllabus .= " WHERE " . implode(' AND ', $where_clauses);
}

$sql_syllabus .= " ORDER BY c.title ASC, s.name ASC, syl.created_at ASC";

$result_syllabus = $conn->query($sql_syllabus);

include __DIR__ . "/../sidebar.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Syllabus - MG Education</title>
    <!-- TinyMCE -->
    <script src="https://cdn.tiny.cloud/1/zzf1ium270xgdjvayd6ocr6p0e7uej8ogum1kdm771lsz41d/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>

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
        .form-input, .form-select, .form-textarea{width:100%;padding:12px 16px;border:1px solid var(--line);border-radius:10px;font-size:15px;transition:all .2s ease;font-family:inherit;background:#fff}
        .form-input:focus, .form-select:focus, .form-textarea:focus{outline:none;border-color:var(--indigo);box-shadow:0 0 0 3px rgba(111,117,255,.1)}
        .btn{display:inline-flex;align-items:center;gap:8px;padding:12px 24px;border-radius:12px;font-weight:700;font-size:14px;border:none;cursor:pointer;transition:all .2s ease;text-decoration:none}
        .btn-primary{background:linear-gradient(135deg,var(--indigo) 0%,#5a5fff 100%);color:#fff}
        .btn-danger{background:var(--error);color:#fff}
        .btn-sm{padding:8px 16px; font-size:13px;}
        .alert{padding:16px 20px;border-radius:12px;margin-bottom:20px;display:flex;align-items:center;gap:12px;border:1px solid}
        .alert-success{background:#d1fae5;border-color:#86efac;color:#065f46}
        .alert-error{background:#fee2e2;border-color:#fca5a5;color:#991b1b}
        .table{width:100%;border-collapse:collapse;margin-top:20px}
        .table th{text-align:left;padding:16px;background:#f8fafc;font-size:13px;font-weight:700;border-bottom:1px solid var(--line)}
        .table td{padding:16px;border-bottom:1px solid var(--line);font-size:14px;vertical-align:middle}
        
        /* Modal Styles */
        .modal {display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.5);}
        .modal-content {background-color: #fefefe; margin: 5% auto; padding: 20px; border: 1px solid #888; width: 700px; border-radius: 12px; position: relative;}
        .close {color: #aaa; float: right; font-size: 28px; font-weight: bold; cursor: pointer;}
        .close:hover {color: black;}
    </style>
</head>
<body>
    <main class="admin-content">
        <div class="page-header">
            <div>
                <div style="font-size:14px;color:var(--muted);margin-bottom:10px">
                    <a href="../index.php" style="text-decoration:none;color:var(--indigo)">Dashboard</a> › Courses › Manage Syllabus
                </div>
                <h1 class="page-title">Manage Syllabus</h1>
                <p style="color:var(--muted)">Organize course curriculum and topics</p>
            </div>
            <div>
                <button class="btn btn-primary" onclick="openAddModal()">+ Add Syllabus Topic</button>
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
                        <?php foreach($courses_filter as $cid => $cname): ?>
                            <option value="<?php echo $cid; ?>" <?php echo $selected_course_id == $cid ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cname); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="flex:1">
                    <label class="form-label" style="margin-bottom: 5px;">Filter by Subject:</label>
                    <select name="subject_id" class="form-select" onchange="this.form.submit()">
                        <option value="">All Subjects</option>
                        <?php foreach($subjects as $s): ?>
                            <?php if ($selected_course_id == 0 || $s['course_id'] == $selected_course_id): ?>
                                <option value="<?php echo $s['id']; ?>" <?php echo $selected_subject_id == $s['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($s['name']); ?> (<?php echo htmlspecialchars($s['course_name']); ?>)
                                </option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="padding-top:20px;">
                    <a href="manage-syllabus.php" class="btn btn-sm" style="background:#f1f5f9; color:var(--text)">Reset</a>
                </div>
            </form>
        </div>

        <!-- Syllabus List -->
        <div class="card">
            <h3 style="font-size:18px; font-weight:700; margin-bottom:15px;">Syllabus / Units List</h3>
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 25%;">Unit / Topic</th>
                        <th style="width: 20%;">Subject</th>
                        <th style="width: 20%;">Course</th>
                        <th style="width: 20%;">Description / Details</th>
                        <th style="text-align:right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result_syllabus->num_rows > 0): ?>
                        <?php while($row = $result_syllabus->fetch_assoc()): ?>
                        <tr>
                            <td><div style="font-weight:700"><?php echo htmlspecialchars($row['unit_title']); ?></div></td>
                            <td><?php echo htmlspecialchars($row['subject_name']); ?></td>
                            <td><span style="font-size:13px; color:var(--muted)"><?php echo htmlspecialchars($row['course_name']); ?></span></td>
                            <td>
                                <!-- strip tags for preview -->
                                <?php echo mb_strimwidth(strip_tags($row['description']), 0, 50, "..."); ?>
                            </td>
                            <td style="text-align:right">
                                <button class="btn btn-sm btn-primary" style="background:none; color:var(--indigo); border:1px solid var(--line)" 
                                    onclick='openEditModal(<?php echo json_encode([
                                        "id" => $row["id"], 
                                        "subject_id" => $row["subject_id"], 
                                        "unit_title" => $row["unit_title"],
                                        "description" => $row["description"] 
                                    ]); ?>)'>Edit</button>
                                
                                <form method="POST" style="display:inline-block;" onsubmit="return confirm('Are you sure you want to delete this topic?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="syllabus_id" value="<?php echo $row['id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-danger" style="background:none; color:var(--error); border:none">Delete</button>
                                </form>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5" style="text-align:center;padding:40px;color:var(--muted)">No syllabus topics found. Select a subject and add topics.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

    <!-- Add/Edit Modal -->
    <div id="syllabusModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2 id="modalTitle" style="margin-bottom:20px;">Add Syllabus Topic</h2>
            <form method="POST" id="syllabusForm">
                <input type="hidden" name="action" id="formAction" value="add">
                <input type="hidden" name="syllabus_id" id="syllabusId">

                <div class="form-group">
                    <label class="form-label">Select Subject *</label>
                    <select name="subject_id" id="subject_id" class="form-select" required>
                        <option value="">-- Select Subject --</option>
                        <?php foreach($subjects as $s): ?>
                            <option value="<?php echo $s['id']; ?>">
                                <?php echo htmlspecialchars($s['name']); ?> (<?php echo htmlspecialchars($s['course_name']); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Unit Title / Topic Name *</label>
                    <input type="text" name="unit_title" id="unit_title" class="form-input" required placeholder="e.g. Unit 1: Introduction to Algorithms">
                </div>

                <div class="form-group">
                    <label class="form-label">Description / Contents</label>
                    <textarea name="description" id="description" class="form-textarea" rows="8"></textarea>
                </div>

                <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center;">Save Topic</button>
            </form>
        </div>
    </div>

    <script>
        const modal = document.getElementById("syllabusModal");
        
        // Init TinyMCE
        tinymce.init({
            selector: '#description',
            height: 300,
            plugins: 'advlist autolink lists link image charmap preview anchor searchreplace visualblocks code fullscreen insertdatetime media table help wordcount',
            toolbar: 'undo redo | blocks | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist | removeformat'
        });

        function openAddModal() {
            document.getElementById('modalTitle').innerText = "Add Syllabus Topic";
            document.getElementById('formAction').value = "add";
            document.getElementById('syllabusId').value = "";
            document.getElementById('unit_title').value = "";
            
            // Allow clearing TinyMCE safely
            if (tinymce.get('description')) {
                tinymce.get('description').setContent('');
            } else {
                document.getElementById('description').value = '';
            }

            // Pre-select subject from filter
            const urlParams = new URLSearchParams(window.location.search);
            const subId = urlParams.get('subject_id');
            if(subId) {
                document.getElementById('subject_id').value = subId;
            }
            
            modal.style.display = "block";
        }

        function openEditModal(data) {
            document.getElementById('modalTitle').innerText = "Edit Syllabus Topic";
            document.getElementById('formAction').value = "edit";
            document.getElementById('syllabusId').value = data.id;
            
            document.getElementById('subject_id').value = data.subject_id;
            document.getElementById('unit_title').value = data.unit_title;
            
            if (tinymce.get('description')) {
                tinymce.get('description').setContent(data.description);
            } else {
                document.getElementById('description').value = data.description;
            }

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
