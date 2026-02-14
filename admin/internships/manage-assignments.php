<?php
// Include database configuration
require_once __DIR__ . '/../../database/db-config.php';
$conn = getDbConnection();

$success_message = "";
$error_message = "";

// Handle Delete
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    
    // First get the file path to delete it
    $stmt_get = $conn->prepare("SELECT assignment_file FROM internship_assignments WHERE id = ?");
    $stmt_get->bind_param("i", $delete_id);
    $stmt_get->execute();
    $res_get = $stmt_get->get_result();
    if ($row = $res_get->fetch_assoc()) {
        $file_path = __DIR__ . "/../../" . $row['assignment_file'];
        if (file_exists($file_path)) {
            unlink($file_path);
        }
    }
    
    $stmt_del = $conn->prepare("DELETE FROM internship_assignments WHERE id = ?");
    $stmt_del->bind_param("i", $delete_id);
    if ($stmt_del->execute()) {
        $success_message = "Assignment deleted successfully!";
    } else {
        $error_message = "Error deleting assignment: " . $conn->error;
    }
}

// Handle Add Assignment
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $internship_id = intval($_POST['internship_id']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    // File Upload
    $assignment_file = "";
    if (isset($_FILES['assignment_file']) && $_FILES['assignment_file']['error'] == 0) {
        $target_dir = "../../assets/uploads/internship_assignments/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $file_extension = pathinfo($_FILES["assignment_file"]["name"], PATHINFO_EXTENSION);
        $clean_title = preg_replace('/[^A-Za-z0-9\-]/', '', str_replace(' ', '-', $title));
        $new_filename = $clean_title . "_" . time() . "." . $file_extension;
        $target_file = $target_dir . $new_filename;
        
        // Allow only PDFs
        if (strtolower($file_extension) != "pdf") {
            $error_message = "Only PDF files are allowed.";
        } else {
            if (move_uploaded_file($_FILES["assignment_file"]["tmp_name"], $target_file)) {
                $assignment_file = "assets/uploads/internship_assignments/" . $new_filename;
            } else {
                $error_message = "Error uploading file.";
            }
        }
    } else {
        $error_message = "Please upload an assignment PDF file.";
    }

    if (empty($error_message)) {
        $stmt = $conn->prepare("INSERT INTO internship_assignments (internship_id, title, assignment_file, description, is_active) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("isssi", $internship_id, $title, $assignment_file, $description, $is_active);
        
        if ($stmt->execute()) {
            $success_message = "Assignment added successfully!";
        } else {
            $error_message = "Database Error: " . $conn->error;
        }
    }
}

// Fetch Internships for Dropdown
$internships = [];
$res_int = $conn->query("SELECT id, title FROM internships WHERE is_active = 1 ORDER BY title ASC");
if ($res_int) {
    while($row = $res_int->fetch_assoc()) {
        $internships[] = $row;
    }
}

// Fetch Assignments List
$assignments = [];
$sql_list = "SELECT ia.*, i.title as internship_title 
             FROM internship_assignments ia 
             JOIN internships i ON ia.internship_id = i.id 
             ORDER BY ia.created_at DESC";
$res_list = $conn->query($sql_list);
if ($res_list) {
    while($row = $res_list->fetch_assoc()) {
        $assignments[] = $row;
    }
}

include __DIR__ . "/../sidebar.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Assignments - MG Education</title>
    
    <style>
        :root{--active:#22c55e;--indigo:#6f75ff;--line:#e6e8ee;--text:#0b1020;--muted:#6f7787;--error:#ef4444;--success:#22c55e;}
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;background:linear-gradient(180deg,#f8fafc 0%,#ffffff 100%);color:var(--text)}
        .admin-content{margin-left:260px;min-height:100vh;padding:20px;transition:margin-left .25s ease}
        .admin-wrap{max-width:1200px;margin:0 auto}
        .page-header{margin-bottom:30px}
        .page-title{font-size:32px;font-weight:800;color:var(--text);margin-bottom:8px;}
        .breadcrumb{color:var(--muted);margin-bottom:10px;font-size:14px}
        .breadcrumb a{color:var(--indigo);text-decoration:none}
        
        .alert{padding:16px 20px;border-radius:12px;margin-bottom:20px;display:flex;align-items:center;gap:12px;border:1px solid;}
        .alert-success{background:#d1fae5;border-color:#86efac;color:#065f46}
        .alert-error{background:#fee2e2;border-color:#fca5a5;color:#991b1b}
        
        .card{background:#fff;border:1px solid var(--line);border-radius:18px;padding:30px;box-shadow:0 4px 12px rgba(0,0,0,.05);margin-bottom: 30px;}
        .form-group{margin-bottom:20px}
        .form-label{display:block;font-weight:700;color:var(--text);margin-bottom:8px;font-size:14px}
        .form-input, .form-textarea, .form-select{width:100%;padding:12px 16px;border:1px solid var(--line);border-radius:10px;font-size:15px;background:#fff}
        
        .btn{display:inline-flex;align-items:center;gap:8px;padding:12px 24px;border-radius:12px;font-weight:700;font-size:15px;border:none;cursor:pointer;background:var(--indigo);color:#fff;text-decoration:none;}
        .btn:hover{opacity:0.9;}
        .btn-danger{background:var(--error);padding:6px 12px;font-size:13px;border-radius:6px;}
        .btn-view{background:#3b82f6;padding:6px 12px;font-size:13px;border-radius:6px;}
        
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid var(--line); font-size: 14px; }
        th { font-weight: 600; color: var(--muted); background: #f8fafc; }
        
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    </style>
</head>
<body>
    <main class="admin-content">
        <div class="admin-wrap">
            <div class="page-header">
                <div class="breadcrumb">
                    <a href="../index.php">Dashboard</a> › Internships › Manage Assignments
                </div>
                <h1 class="page-title">Internship Assignments</h1>
            </div>

            <?php if (!empty($success_message)): ?>
            <div class="alert alert-success"><span>✓ <?php echo $success_message; ?></span></div>
            <?php endif; ?>

            <?php if (!empty($error_message)): ?>
            <div class="alert alert-error"><span>⚠ <?php echo $error_message; ?></span></div>
            <?php endif; ?>

            <!-- Add Assignment Form -->
            <div class="card">
                <h3 style="margin-bottom: 20px; font-size: 18px;">Add New Assignment</h3>
                <form method="POST" enctype="multipart/form-data">
                    <div class="grid-2">
                        <div class="form-group">
                            <label class="form-label">Select Internship</label>
                            <select name="internship_id" class="form-select" required>
                                <option value="">-- Select Internship --</option>
                                <?php foreach($internships as $int): ?>
                                    <option value="<?php echo $int['id']; ?>"><?php echo htmlspecialchars($int['title']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Assignment Title</label>
                            <input type="text" name="title" class="form-input" required placeholder="e.g. Month 1 - Basic Web Structure">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Description (Optional)</label>
                        <textarea name="description" class="form-textarea" rows="3"></textarea>
                    </div>

                    <div class="grid-2">
                        <div class="form-group">
                            <label class="form-label">Assignment File (PDF Only)</label>
                            <input type="file" name="assignment_file" class="form-input" accept="application/pdf" required>
                        </div>
                        <div class="form-group" style="display:flex; align-items:flex-end;">
                             <label class="form-label" style="margin-bottom:15px; display:block;">
                                <input type="checkbox" name="is_active" checked> Active
                            </label>
                        </div>
                    </div>

                    <button type="submit" class="btn">Upload Assignment</button>
                </form>
            </div>

            <!-- List Assignments -->
            <div class="card">
                <h3 style="margin-bottom: 20px; font-size: 18px;">Existing Assignments</h3>
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Internship</th>
                            <th>Title</th>
                            <th>File</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($assignments)): ?>
                            <tr><td colspan="6" style="text-align:center; padding:30px; color:#94a3b8;">No assignments found.</td></tr>
                        <?php else: ?>
                            <?php foreach($assignments as $assign): ?>
                            <tr>
                                <td><?php echo $assign['id']; ?></td>
                                <td style="font-weight:500; color:#3b82f6;"><?php echo htmlspecialchars($assign['internship_title']); ?></td>
                                <td><?php echo htmlspecialchars($assign['title']); ?></td>
                                <td>
                                    <a href="../../<?php echo $assign['assignment_file']; ?>" target="_blank" class="btn-view">View PDF</a>
                                </td>
                                <td><?php echo date('d M Y', strtotime($assign['created_at'])); ?></td>
                                <td>
                                    <a href="?delete_id=<?php echo $assign['id']; ?>" class="btn-danger" onclick="return confirm('Are you sure you want to delete this assignment?');">Delete</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </main>
</body>
</html>
<?php $conn->close(); ?>
