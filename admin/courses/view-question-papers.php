<?php
// Include database configuration
require_once __DIR__ . '/../../database/db-config.php';
// Ensure schema
require_once __DIR__ . '/../../database/update_paper_schema.php';

$conn = getDbConnection();
$success_message = '';
$error_message = '';

// Handle Delete
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action']) && $_POST['action'] == 'delete_paper') {
    $paper_id = intval($_POST['paper_id']);
    
    // Deleting the paper will cascade delete questions due to foreign key
    $sql = "DELETE FROM question_papers WHERE id = $paper_id";
    if ($conn->query($sql) === TRUE) {
        $success_message = "Question Paper deleted successfully.";
    } else {
        $error_message = "Error deleting paper: " . $conn->error;
    }
}

// Fetch Papers with Details
// Grouping logic isn't strictly needed as it's 1-to-1 with subject for now, but fetching nice join data
$sql_papers = "SELECT qp.*, s.name as subject_name, s.code, c.title as course_name 
               FROM question_papers qp 
               JOIN subjects s ON qp.subject_id = s.id 
               JOIN courses c ON s.course_id = c.id 
               ORDER BY qp.created_at DESC";

$result_papers = $conn->query($sql_papers);

include __DIR__ . "/../sidebar.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Question Papers - MG Education</title>
    <style>
        :root{--active:#22c55e;--indigo:#6f75ff;--line:#e6e8ee;--text:#0b1020;--muted:#6f7787;--error:#ef4444;--success:#22c55e}
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;background:linear-gradient(180deg,#f8fafc 0%,#ffffff 100%);color:var(--text)}
        .admin-content{margin-left:260px;min-height:100vh;padding:20px;transition:margin-left .25s ease}
        body.sidebar-collapsed .admin-content{margin-left:88px}
        .page-header{margin-bottom:30px; display:flex; justify-content:space-between; align-items:center}
        .page-title{font-size:32px;font-weight:800;color:var(--text);margin-bottom:8px}
        .card{background:#fff;border:1px solid var(--line);border-radius:18px;padding:30px;box-shadow:0 4px 12px rgba(0,0,0,.05);margin-bottom:20px}
        
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
    </style>
</head>
<body>
    <main class="admin-content">
        <div class="page-header">
            <div>
                <div style="font-size:14px;color:var(--muted);margin-bottom:10px">
                    <a href="../index.php" style="text-decoration:none;color:var(--indigo)">Dashboard</a> › Courses › Question Papers
                </div>
                <h1 class="page-title">View Question Papers</h1>
                <p style="color:var(--muted)">Manage all created exam papers</p>
            </div>
            <div>
                <a href="manage-question-paper.php" class="btn btn-primary">+ Create New Paper</a>
            </div>
        </div>

        <?php if (!empty($success_message)): ?>
        <div class="alert alert-success"><span>✓ <?php echo $success_message; ?></span></div>
        <?php endif; ?>

        <?php if (!empty($error_message)): ?>
        <div class="alert alert-error"><span>⚠ <?php echo $error_message; ?></span></div>
        <?php endif; ?>

        <div class="card">
            <h3 style="font-size:18px; font-weight:700; margin-bottom:15px;">All Question Papers</h3>
            <table class="table">
                <thead>
                    <tr>
                        <th>Subject</th>
                        <th>Course</th>
                        <th>Format</th>
                        <th>Total Marks</th>
                        <th style="text-align:right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result_papers->num_rows > 0): ?>
                        <?php while($row = $result_papers->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <div style="font-weight:700"><?php echo htmlspecialchars($row['subject_name']); ?></div>
                                <div style="font-size:12px;color:var(--muted)"><?php echo htmlspecialchars($row['code']); ?></div>
                            </td>
                            <td><?php echo htmlspecialchars($row['course_name']); ?></td>
                            <td>
                                <div><?php echo $row['total_questions']; ?> Questions</div>
                                <div style="font-size:12px;color:var(--muted)">@ <?php echo $row['marks_per_question']; ?> Marks/Q</div>
                            </td>
                            <td><span style="font-weight:700; color:var(--active)"><?php echo $row['total_marks']; ?></span></td>
                            <td style="text-align:right">
                                <!-- Link to Manage Page with Subject ID to trigger Edit Mode -->
                                <a href="manage-question-paper.php?paper_id=<?php echo $row['id']; ?>&subject_id=<?php echo $row['subject_id']; ?>" class="btn btn-sm btn-primary" style="background:none; color:var(--indigo); border:1px solid var(--line)">Edit</a>
                                
                                <form method="POST" style="display:inline-block;" onsubmit="return confirm('Type DELETE to confirm deletion logic... Just kidding. Are you SURE you want to delete this paper? All questions will be lost.');">
                                    <input type="hidden" name="action" value="delete_paper">
                                    <input type="hidden" name="paper_id" value="<?php echo $row['id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-danger" style="background:none; color:var(--error); border:none">Delete</button>
                                </form>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5" style="text-align:center;padding:40px;color:var(--muted)">No question papers found. Create one to get started.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>
<?php $conn->close(); ?>
