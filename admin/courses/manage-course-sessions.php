<?php
// Include database configuration
require_once __DIR__ . '/../../database/db-config.php';
// Ensure schema is updated
require_once __DIR__ . '/../../database/update_session_schema.php';

$success_message = '';
$error_message = '';

// Handle Form Submission
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action']) && $_POST['action'] == 'add_session') {
    $course_id = intval($_POST['course_id']);
    $start_month = mysqli_real_escape_string($conn, $_POST['start_month']);
    $start_year = intval($_POST['start_year']);
    $end_month = mysqli_real_escape_string($conn, $_POST['end_month']);
    $end_year = intval($_POST['end_year']);
    
    // Create session name (e.g. "April 2024 - March 2025")
    $session_name = "$start_month $start_year - $end_month $end_year";
    
    // Validate
    if ($course_id > 0 && !empty($start_month) && !empty($end_month)) {
        $sql = "INSERT INTO course_sessions (course_id, session_name, start_month, start_year, end_month, end_year) 
                VALUES ($course_id, '$session_name', '$start_month', $start_year, '$end_month', $end_year)";
        
        if ($conn->query($sql) === TRUE) {
            $success_message = "Session added successfully!";
        } else {
            $error_message = "Error: " . $conn->error;
        }
    } else {
        $error_message = "Please fill all required fields.";
    }
}

// Handle Delete
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $conn->query("DELETE FROM course_sessions WHERE id = $delete_id");
    $success_message = "Session deleted successfully.";
}

// Fetch Courses for Dropdown
$courses_result = $conn->query("SELECT id, title FROM courses ORDER BY title ASC");
$courses = [];
if ($courses_result->num_rows > 0) {
    while($row = $courses_result->fetch_assoc()) {
        $courses[] = $row;
    }
}

// Fetch Sessions with Filter
$filter_course_id = isset($_GET['filter_course']) ? intval($_GET['filter_course']) : 0;
$query = "SELECT cs.*, c.title as course_title 
          FROM course_sessions cs 
          JOIN courses c ON cs.course_id = c.id";

if ($filter_course_id > 0) {
    $query .= " WHERE cs.course_id = $filter_course_id";
}

$query .= " ORDER BY cs.id DESC";
$sessions_result = $conn->query($query);

// Common Months Array
$months = [
    "January", "February", "March", "April", "May", "June", 
    "July", "August", "September", "October", "November", "December"
];

// Year Range
$current_year = date('Y');
$years = range($current_year - 1, $current_year + 3);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Course Sessions - MG Education</title>
    
    <style>
        :root{--active:#22c55e;--indigo:#6f75ff;--line:#e6e8ee;--text:#0b1020;--muted:#6f7787;--error:#ef4444;--success:#22c55e;--info:#3b82f6}
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;background:linear-gradient(180deg,#f8fafc 0%,#ffffff 100%);color:var(--text)}
        .admin-content{margin-left:260px;min-height:100vh;padding:20px;transition:margin-left .25s ease}
        body.sidebar-collapsed .admin-content{margin-left:88px}
        .admin-wrap{max-width:1200px;margin:0 auto}
        .page-header{margin-bottom:30px}
        .page-title{font-size:32px;font-weight:800;color:var(--text);margin-bottom:8px;display:flex;align-items:center;gap:12px}
        .page-subtitle{color:var(--muted);font-size:15px}
        .breadcrumb{color:var(--muted);margin-bottom:10px;font-size:14px}
        .breadcrumb a{color:var(--indigo);text-decoration:none}
        .alert{padding:16px 20px;border-radius:12px;margin-bottom:20px;display:flex;align-items:center;gap:12px;border:1px solid;animation:slideDown .3s ease}
        .alert-success{background:#d1fae5;border-color:#86efac;color:#065f46}
        .alert-error{background:#fee2e2;border-color:#fca5a5;color:#991b1b}
        .card{background:#fff;border:1px solid var(--line);border-radius:18px;padding:30px;box-shadow:0 4px 12px rgba(0,0,0,.05);margin-bottom: 20px;}
        .form-group{margin-bottom:20px}
        .form-label{display:block;font-weight:700;color:var(--text);margin-bottom:8px;font-size:14px}
        .form-input, .form-select{width:100%;padding:12px 16px;border:1px solid var(--line);border-radius:10px;font-size:15px;transition:all .2s ease;font-family:inherit;background:#fff}
        .form-input:focus, .form-select:focus{outline:none;border-color:var(--indigo);box-shadow:0 0 0 3px rgba(111,117,255,.1)}
        .btn{display:inline-flex;align-items:center;gap:8px;padding:12px 24px;border-radius:12px;font-weight:700;font-size:15px;border:none;cursor:pointer;transition:all .2s ease;text-decoration:none}
        .btn-primary{background:linear-gradient(135deg,var(--indigo) 0%,#5a5fff 100%);color:#fff}
        .btn-danger{background:#fee2e2;color:#ef4444;padding:8px 12px;border-radius:8px;font-size:13px;}
        .grid-2{display:grid;grid-template-columns:1fr 1fr;gap:20px}
        .grid-4{display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:15px}
        
        /* Table Styles */
        .table-container { overflow-x: auto; }
        .data-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .data-table th, .data-table td { padding: 16px; text-align: left; border-bottom: 1px solid var(--line); }
        .data-table th { background: #f8fafc; font-weight: 600; color: var(--muted); font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px; }
        .data-table td { font-size: 15px; }
        .data-table tr:last-child td { border-bottom: none; }
        .badge { padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; }
        .badge-active { background: #d1fae5; color: #065f46; }

        .filter-bar { display: flex; gap: 10px; margin-bottom: 20px; align-items: center; background: #f8fafc; padding: 15px; border-radius: 12px; border: 1px solid var(--line); }
    </style>
</head>
<body>
    <?php include __DIR__ . "/../sidebar.php"; ?>
    
    <main class="admin-content">
        <div class="admin-wrap">
            <div class="page-header">
                <div class="breadcrumb">
                    <a href="../index.php">Dashboard</a> › <a href="#">Courses</a> › Sessions
                </div>
                <h1 class="page-title">Manage Course Sessions</h1>
                <p class="page-subtitle">Add and manage academic sessions for courses</p>
            </div>

            <?php if (!empty($success_message)): ?>
            <div class="alert alert-success"><span>✓ <?php echo $success_message; ?></span></div>
            <?php endif; ?>

            <?php if (!empty($error_message)): ?>
            <div class="alert alert-error"><span>⚠ <?php echo $error_message; ?></span></div>
            <?php endif; ?>

            <!-- Add Session Form -->
            <div class="card">
                <h3 style="margin-bottom: 20px; font-size: 18px;">Add New Session</h3>
                <form method="POST">
                    <input type="hidden" name="action" value="add_session">
                    
                    <div class="form-group">
                        <label class="form-label">Select Course</label>
                        <select name="course_id" class="form-select" required>
                            <option value="">-- Choose Course --</option>
                            <?php foreach($courses as $c): ?>
                                <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['title']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="grid-2">
                        <!-- Start Date -->
                        <div>
                            <label class="form-label">Session Start</label>
                            <div class="grid-2" style="gap: 10px;">
                                <select name="start_month" class="form-select" required>
                                    <option value="">Month</option>
                                    <?php foreach($months as $m): ?>
                                        <option value="<?php echo $m; ?>"><?php echo $m; ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <select name="start_year" class="form-select" required>
                                    <?php foreach($years as $y): ?>
                                        <option value="<?php echo $y; ?>" <?php if($y == $current_year) echo 'selected'; ?>><?php echo $y; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <!-- End Date -->
                        <div>
                            <label class="form-label">Session End</label>
                            <div class="grid-2" style="gap: 10px;">
                                <select name="end_month" class="form-select" required>
                                    <option value="">Month</option>
                                    <?php foreach($months as $m): ?>
                                        <option value="<?php echo $m; ?>"><?php echo $m; ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <select name="end_year" class="form-select" required>
                                    <?php foreach($years as $y): ?>
                                        <option value="<?php echo $y; ?>" <?php if($y == $current_year + 1) echo 'selected'; ?>><?php echo $y; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div style="margin-top: 20px;">
                        <button type="submit" class="btn btn-primary">Save Session</button>
                    </div>
                </form>
            </div>

            <!-- List Sessions -->
            <div class="card">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h3 style="font-size: 18px;">All Sessions</h3>
                </div>

                <div class="filter-bar">
                    <span style="font-weight: 600; font-size: 14px; color: var(--muted);">Filter by Course:</span>
                    <form method="GET" style="display: flex; gap: 10px; flex: 1;">
                        <select name="filter_course" class="form-select" style="max-width: 300px;" onchange="this.form.submit()">
                            <option value="">All Courses</option>
                            <?php foreach($courses as $c): ?>
                                <option value="<?php echo $c['id']; ?>" <?php if($filter_course_id == $c['id']) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($c['title']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if($filter_course_id > 0): ?>
                            <a href="manage-course-sessions.php" style="display: flex; align-items: center; color: var(--error); text-decoration: none; font-size: 14px;">Clear Filter</a>
                        <?php endif; ?>
                    </form>
                </div>

                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Session Name</th>
                                <th>Associated Course</th>
                                <th>Duration</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($sessions_result->num_rows > 0): ?>
                                <?php while($row = $sessions_result->fetch_assoc()): ?>
                                    <tr>
                                        <td style="font-weight: 600; color: var(--indigo);">
                                            <?php echo htmlspecialchars($row['session_name']); ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($row['course_title']); ?></td>
                                        <td style="color: var(--muted);">
                                            <?php echo htmlspecialchars($row['start_month'] . ' ' . $row['start_year']); ?> 
                                            → 
                                            <?php echo htmlspecialchars($row['end_month'] . ' ' . $row['end_year']); ?>
                                        </td>
                                        <td>
                                            <span class="badge badge-active">Active</span>
                                        </td>
                                        <td>
                                            <a href="?delete_id=<?php echo $row['id']; ?>" class="btn-danger" onclick="return confirm('Are you sure you want to delete this session?');">Delete</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" style="text-align: center; color: var(--muted); padding: 30px;">
                                        No sessions found. Add a new session above.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </main>
</body>
<script>
    // Keep sidebar state similar to other pages
    if(localStorage.getItem('sidebar-collapsed') === 'true') {
        document.body.classList.add('sidebar-collapsed');
    }
</script>
</html>
<?php $conn->close(); ?>
