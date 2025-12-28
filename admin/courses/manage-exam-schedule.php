<?php
// Include database configuration
require_once __DIR__ . '/../../database/db-config.php';
// Ensure schema is updated
require_once __DIR__ . '/../../database/update_subjects_schema.php';
require_once __DIR__ . '/../../database/update_exam_schema.php';

$conn = getDbConnection();
$success_message = '';
$error_message = '';

// Handle Actions
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $subject_id = intval($_POST['subject_id']);
        $exam_date = mysqli_real_escape_string($conn, $_POST['exam_date']);
        $start_time = mysqli_real_escape_string($conn, $_POST['start_time']);
        $duration_minutes = intval($_POST['duration_minutes']);
        
        $sql = "INSERT INTO exam_schedules (subject_id, exam_date, start_time, duration_minutes) 
                VALUES ($subject_id, '$exam_date', '$start_time', $duration_minutes)";
        
        if ($conn->query($sql) === TRUE) {
            $success_message = "Exam schedule added successfully!";
        } else {
            $error_message = "Error adding schedule: " . $conn->error;
        }

    } elseif ($action === 'edit') {
        $id = intval($_POST['schedule_id']);
        $subject_id = intval($_POST['subject_id']);
        $exam_date = mysqli_real_escape_string($conn, $_POST['exam_date']);
        $start_time = mysqli_real_escape_string($conn, $_POST['start_time']);
        $duration_minutes = intval($_POST['duration_minutes']);

        $sql = "UPDATE exam_schedules SET 
                subject_id=$subject_id, 
                exam_date='$exam_date', 
                start_time='$start_time', 
                duration_minutes=$duration_minutes 
                WHERE id=$id";

        if ($conn->query($sql) === TRUE) {
            $success_message = "Schedule updated successfully!";
        } else {
            $error_message = "Error updating schedule: " . $conn->error;
        }

    } elseif ($action === 'delete') {
        $id = intval($_POST['schedule_id']);
        $sql = "DELETE FROM exam_schedules WHERE id=$id";
        if ($conn->query($sql) === TRUE) {
            $success_message = "Schedule deleted successfully!";
        } else {
            $error_message = "Error deleting schedule: " . $conn->error;
        }
    }
}

// Fetch Subjects for Dropdown
$subjects_result = $conn->query("SELECT s.id, s.name, s.code, c.title as course_name, s.course_id 
                                 FROM subjects s 
                                 JOIN courses c ON s.course_id = c.id 
                                 ORDER BY c.title ASC, s.name ASC");
$subjects = [];
$courses_filter = [];
while ($row = $subjects_result->fetch_assoc()) {
    $subjects[] = $row;
    $courses_filter[$row['course_id']] = $row['course_name'];
}

// Filter Logic
$selected_course_id = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;

// Fetch Schedules
$sql_schedules = "SELECT es.*, s.name as subject_name, s.code, c.title as course_name 
                  FROM exam_schedules es 
                  JOIN subjects s ON es.subject_id = s.id 
                  JOIN courses c ON s.course_id = c.id";

if ($selected_course_id > 0) {
    $sql_schedules .= " WHERE c.id = $selected_course_id";
}

$sql_schedules .= " ORDER BY es.exam_date ASC, es.start_time ASC";

$result_schedules = $conn->query($sql_schedules);

include __DIR__ . "/../sidebar.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam Schedule - MG Education</title>
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
                    <a href="../index.php" style="text-decoration:none;color:var(--indigo)">Dashboard</a> › Courses › Exam Schedule
                </div>
                <h1 class="page-title">Exam Schedule</h1>
                <p style="color:var(--muted)">Manage dates and timings for subject exams</p>
            </div>
            <div>
                <button class="btn btn-primary" onclick="openAddModal()">+ Add Schedule</button>
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
                <div style="padding-top:20px;">
                    <a href="manage-exam-schedule.php" class="btn btn-sm" style="background:#f1f5f9; color:var(--text)">Reset</a>
                </div>
            </form>
        </div>

        <!-- Schedule List -->
        <div class="card">
            <h3 style="font-size:18px; font-weight:700; margin-bottom:15px;">Schedule List</h3>
            <table class="table">
                <thead>
                    <tr>
                        <th>Subject</th>
                        <th>Course</th>
                        <th>Exam Date</th>
                        <th>Exam Timing</th>
                        <th>Duration</th>
                        <th style="text-align:right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result_schedules->num_rows > 0): ?>
                        <?php while($row = $result_schedules->fetch_assoc()): 
                            $start_time = strtotime($row['start_time']);
                            $duration_mins = $row['duration_minutes'];
                            $end_time = $start_time + ($duration_mins * 60);
                        ?>
                        <tr>
                            <td>
                                <div style="font-weight:700"><?php echo htmlspecialchars($row['subject_name']); ?></div>
                                <div style="font-size:12px;color:var(--muted)"><?php echo htmlspecialchars($row['code']); ?></div>
                            </td>
                            <td><?php echo htmlspecialchars($row['course_name']); ?></td>
                            <td><?php echo date('M d, Y', strtotime($row['exam_date'])); ?></td>
                            <td>
                                <div style="font-weight:600; color:var(--text)">
                                    <?php echo date('h:i A', $start_time); ?> - <?php echo date('h:i A', $end_time); ?>
                                </div>
                            </td>
                            <td><?php echo $duration_mins; ?> Min</td>
                            <td style="text-align:right">
                                <button class="btn btn-sm btn-primary" style="background:none; color:var(--indigo); border:1px solid var(--line)" onclick='openEditModal(<?php echo json_encode($row); ?>)'>Edit</button>
                                <form method="POST" style="display:inline-block;" onsubmit="return confirm('Are you sure you want to delete this schedule?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="schedule_id" value="<?php echo $row['id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-danger" style="background:none; color:var(--error); border:none">Delete</button>
                                </form>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="6" style="text-align:center;padding:40px;color:var(--muted)">No exam schedules found. Add a schedule.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

    <!-- Add/Edit Modal -->
    <div id="scheduleModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2 id="modalTitle" style="margin-bottom:20px;">Add Exam Schedule</h2>
            <form method="POST" id="scheduleForm">
                <input type="hidden" name="action" id="formAction" value="add">
                <input type="hidden" name="schedule_id" id="scheduleId">

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
                    <label class="form-label">Exam Date *</label>
                    <input type="date" name="exam_date" id="exam_date" class="form-input" required>
                </div>

                <div class="grid-2">
                    <div class="form-group">
                        <label class="form-label">Start Time *</label>
                        <input type="time" name="start_time" id="start_time" class="form-input" required onchange="calculateEndTime()">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Duration (Minutes) *</label>
                        <input type="number" name="duration_minutes" id="duration_minutes" class="form-input" value="180" required oninput="calculateEndTime()">
                    </div>
                </div>

                <div class="form-group" style="background:#f8fafc; padding:15px; border-radius:10px; border:1px solid var(--line);">
                    <label class="form-label" style="font-size:12px; color:var(--muted); margin-bottom:0;">Exam Timing Preview:</label>
                    <div id="previewWait" style="font-weight:700; font-size:15px; color:var(--indigo);">-- : --</div>
                </div>

                <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center;">Save Schedule</button>
            </form>
        </div>
    </div>

    <script>
        const modal = document.getElementById("scheduleModal");
        
        function calculateEndTime() {
            const startTimeStr = document.getElementById('start_time').value;
            const duration = parseInt(document.getElementById('duration_minutes').value) || 0;
            const preview = document.getElementById('previewWait');

            if (!startTimeStr || duration <= 0) {
                preview.innerText = "-- : --";
                return;
            }

            // Parse time
            const [hours, minutes] = startTimeStr.split(':').map(Number);
            
            // Create Date object for calculation (using arbitrary date)
            const date = new Date();
            date.setHours(hours);
            date.setMinutes(minutes);

            // Start Time Format
            const startFormatted = date.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});

            // Add duration
            date.setMinutes(date.getMinutes() + duration);

            // End Time Format
            const endFormatted = date.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
            
            preview.innerText = `${startFormatted} - ${endFormatted}`;
        }

        function openAddModal() {
            document.getElementById('modalTitle').innerText = "Add Exam Schedule";
            document.getElementById('formAction').value = "add";
            document.getElementById('scheduleId').value = "";
            document.getElementById('scheduleForm').reset();
            document.getElementById('previewWait').innerText = "-- : --";
            
            modal.style.display = "block";
        }

        function openEditModal(data) {
            document.getElementById('modalTitle').innerText = "Edit Exam Schedule";
            document.getElementById('formAction').value = "edit";
            document.getElementById('scheduleId').value = data.id;
            
            document.getElementById('subject_id').value = data.subject_id;
            document.getElementById('exam_date').value = data.exam_date;
            document.getElementById('start_time').value = data.start_time; // Time input expects HH:MM format
            document.getElementById('duration_minutes').value = data.duration_minutes;

            calculateEndTime(); // Update preview
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
