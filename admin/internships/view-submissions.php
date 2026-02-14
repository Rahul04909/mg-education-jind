<?php
// Include database configuration
require_once __DIR__ . '/../../database/db-config.php';
$conn = getDbConnection();

$success_message = "";
$error_message = "";

// Handle Grading Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_grade'])) {
    $submission_id = intval($_POST['submission_id']);
    $marks = !empty($_POST['marks']) ? intval($_POST['marks']) : NULL;
    $status = $_POST['status'];
    $feedback = mysqli_real_escape_string($conn, $_POST['feedback']);

    $stmt = $conn->prepare("UPDATE internship_submissions SET marks = ?, status = ?, feedback = ? WHERE id = ?");
    $stmt->bind_param("issi", $marks, $status, $feedback, $submission_id);
    
    if ($stmt->execute()) {
        $success_message = "Submission graded successfully!";
    } else {
        $error_message = "Error updating grade: " . $conn->error;
    }
}

// Pagination Setup
$limit = 20;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// Filters
$filter_internship = isset($_GET['internship_id']) ? intval($_GET['internship_id']) : 0;
$filter_session = isset($_GET['session_id']) ? intval($_GET['session_id']) : 0;
$filter_search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

// Build Query
$where_clauses = ["1=1"];
if ($filter_internship > 0) $where_clauses[] = "ia.internship_id = $filter_internship";
if ($filter_session > 0) $where_clauses[] = "ia.session_id = $filter_session";
if (!empty($filter_search)) {
    $where_clauses[] = "(st.full_name LIKE '%$filter_search%' OR st.enrollment_no LIKE '%$filter_search%' OR ia.title LIKE '%$filter_search%')";
}
$where_sql = implode(" AND ", $where_clauses);

// Count Total for Pagination
$count_sql = "SELECT COUNT(*) as total 
              FROM internship_submissions s
              JOIN internship_enrollments st ON s.student_id = st.id
              JOIN internship_assignments ia ON s.assignment_id = ia.id
              WHERE $where_sql";
$count_res = $conn->query($count_sql);
$total_rows = $count_res->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $limit);

// Fetch Data
$sql = "SELECT s.*, st.full_name, st.enrollment_no, 
               ia.title as assign_title, 
               i.title as internship_title, sess.session_name
        FROM internship_submissions s
        JOIN internship_enrollments st ON s.student_id = st.id
        JOIN internship_assignments ia ON s.assignment_id = ia.id
        JOIN internships i ON ia.internship_id = i.id
        LEFT JOIN internship_sessions sess ON ia.session_id = sess.id
        WHERE $where_sql
        ORDER BY s.submitted_at DESC
        LIMIT $offset, $limit";
$result = $conn->query($sql);
$submissions = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $submissions[] = $row;
    }
}

// Fetch Internships for Filter
$internships = [];
$res_int = $conn->query("SELECT id, title FROM internships ORDER BY title ASC");
if ($res_int) {
    while ($row = $res_int->fetch_assoc()) {
        $internships[] = $row;
    }
}

include __DIR__ . "/../sidebar.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assignment Submissions - MG Education</title>
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
        
        .card{background:#fff;border:1px solid var(--line);border-radius:18px;padding:30px;box-shadow:0 4px 12px rgba(0,0,0,.05);margin-bottom: 30px;}
        
        .filters { display: flex; gap: 15px; flex-wrap: wrap; align-items: end; margin-bottom: 20px; }
        .filter-group { flex: 1; min-width: 200px; }
        .form-label { display: block; font-weight: 700; color: var(--text); margin-bottom: 5px; font-size: 13px; }
        .form-select, .form-input { width: 100%; padding: 10px; border: 1px solid var(--line); border-radius: 8px; font-size: 14px; background: #fff; }
        .btn-filter { padding: 10px 20px; background: var(--indigo); color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; }
        .btn-reset { padding: 10px 20px; background: #94a3b8; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; text-decoration: none; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid var(--line); font-size: 14px; }
        th { font-weight: 600; color: var(--muted); background: #f8fafc; }
        
        .status-badge { padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; text-transform: uppercase; }
        .status-Submitted { background: #dcfce7; color: #166534; } /* Greenish/Fresh */
        .status-Graded { background: #e0e7ff; color: #4338ca; } /* Indigo */
        .status-Resubmit { background: #fee2e2; color: #991b1b; } /* Red */

        .btn-action { padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 600; text-decoration: none; display: inline-block; margin-right: 5px; cursor: pointer; border: none; }
        .btn-view { background: #eff6ff; color: #1d4ed8; }
        .btn-grade { background: #f0fdf4; color: #15803d; }
        
        .pagination { display: flex; justify-content: center; margin-top: 20px; gap: 5px; }
        .page-link { padding: 8px 12px; border: 1px solid var(--line); border-radius: 6px; text-decoration: none; color: var(--text); font-size: 14px; }
        .page-link.active { background: var(--indigo); color: white; border-color: var(--indigo); }
        
        /* Modal */
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center; }
        .modal.active { display: flex; }
        .modal-content { background: white; width: 100%; max-width: 450px; border-radius: 16px; padding: 25px; position: relative; }
        .modal-close { position: absolute; top: 15px; right: 15px; cursor: pointer; font-size: 20px; color: #94a3b8; }
        .modal h3 { margin-bottom: 15px; }
    </style>
</head>
<body>
    <main class="admin-content">
        <div class="admin-wrap">
            <div class="page-header">
                <div class="breadcrumb">
                    <a href="../index.php">Dashboard</a> › Internships › Submissions
                </div>
                <h1 class="page-title">Assignment Submissions</h1>
            </div>

            <?php if (!empty($success_message)): ?>
                <div style="background:#d1fae5; color:#065f46; padding:15px; border-radius:10px; margin-bottom:20px;">
                    <?php echo $success_message; ?>
                </div>
            <?php endif; ?>

            <div class="card">
                <form method="GET" class="filters">
                    <div class="filter-group">
                        <label class="form-label">Search Student</label>
                        <input type="text" name="search" class="form-input" placeholder="Name, Enrollment..." value="<?php echo htmlspecialchars($filter_search); ?>">
                    </div>
                    <div class="filter-group">
                        <label class="form-label">Internship</label>
                        <select name="internship_id" id="internship_select" class="form-select" onchange="loadSessions()">
                            <option value="">All Internships</option>
                            <?php foreach ($internships as $int): ?>
                                <option value="<?php echo $int['id']; ?>" <?php if ($filter_internship == $int['id']) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($int['title']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label class="form-label">Session</label>
                        <select name="session_id" id="session_select" class="form-select">
                            <option value="">All Sessions</option>
                        </select>
                    </div>
                    <button type="submit" class="btn-filter">Filter</button>
                    <a href="view-submissions.php" class="btn-reset">Reset</a>
                </form>

                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Student</th>
                            <th>Internship/Session</th>
                            <th>Assignment</th>
                            <th>Status</th>
                            <th>Marks</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($submissions)): ?>
                            <tr><td colspan="7" style="text-align:center; padding:30px; color:#94a3b8;">No submissions found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($submissions as $sub): ?>
                            <tr>
                                <td>#<?php echo $sub['id']; ?></td>
                                <td>
                                    <div style="font-weight:600;"><?php echo htmlspecialchars($sub['full_name']); ?></div>
                                    <div style="font-size:12px; color:var(--muted);"><?php echo htmlspecialchars($sub['enrollment_no']); ?></div>
                                </td>
                                <td>
                                    <div style="font-size:13px;"><?php echo htmlspecialchars($sub['internship_title']); ?></div>
                                    <div style="font-size:12px; color:var(--muted);"><?php echo htmlspecialchars($sub['session_name'] ?? 'All Sessions'); ?></div>
                                </td>
                                <td><?php echo htmlspecialchars($sub['assign_title']); ?></td>
                                <td><span class="status-badge status-<?php echo $sub['status']; ?>"><?php echo $sub['status']; ?></span></td>
                                <td><?php echo $sub['marks'] ? $sub['marks'] : '-'; ?></td>
                                <td>
                                    <a href="../../<?php echo $sub['submission_file']; ?>" target="_blank" class="btn-action btn-view">View File</a>
                                    <button class="btn-action btn-grade" onclick="openGradeModal(<?php echo $sub['id']; ?>, '<?php echo $sub['marks']; ?>', '<?php echo $sub['status']; ?>', '<?php echo htmlspecialchars(addslashes($sub['feedback'] ?? '')); ?>')">Grade</button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="?page=<?php echo $i; ?>&search=<?php echo $filter_search; ?>&internship_id=<?php echo $filter_internship; ?>&session_id=<?php echo $filter_session; ?>" 
                           class="page-link <?php if ($page == $i) echo 'active'; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- Grading Modal -->
    <div class="modal" id="gradeModal">
        <div class="modal-content">
            <span class="modal-close" onclick="closeModal()">×</span>
            <h3>Grade Submission</h3>
            <form method="POST">
                <input type="hidden" name="update_grade" value="1">
                <input type="hidden" name="submission_id" id="modalSubId">
                
                <div style="margin-bottom:15px;">
                    <label class="form-label">Marks</label>
                    <input type="number" name="marks" id="modalMarks" class="form-input" placeholder="Enter marks">
                </div>
                
                <div style="margin-bottom:15px;">
                    <label class="form-label">Status</label>
                    <select name="status" id="modalStatus" class="form-select">
                        <option value="Submitted">Submitted (Pending)</option>
                        <option value="Graded">Graded</option>
                        <option value="Resubmit">Resubmit Requested</option>
                    </select>
                </div>

                <div style="margin-bottom:20px;">
                    <label class="form-label">Feedback</label>
                    <textarea name="feedback" id="modalFeedback" class="form-input" rows="3"></textarea>
                </div>

                <button type="submit" class="btn-filter" style="width:100%;">Save Grade</button>
            </form>
        </div>
    </div>

    <script>
        function loadSessions() {
            let intId = document.getElementById('internship_select').value;
            let sessSelect = document.getElementById('session_select');
            
            // Allow selecting "All"
            sessSelect.innerHTML = '<option value="">All Sessions</option>';
            
            if(intId) {
                // Fetch sessions
                fetch('../../internship-enrollment/get-sessions.php?internship_id=' + intId)
                .then(res => res.json())
                .then(data => {
                    if(data.status === 'success' && data.data.length > 0) {
                        data.data.forEach(sess => {
                            let selected = (sess.id == "<?php echo $filter_session; ?>") ? 'selected' : '';
                            sessSelect.innerHTML += `<option value="${sess.id}" ${selected}>${sess.session_name}</option>`;
                        });
                    }
                })
                .catch(err => console.error(err));
            }
        }

        // Auto-load sessions if filter is active
        <?php if($filter_internship): ?>
        window.addEventListener('DOMContentLoaded', loadSessions);
        <?php endif; ?>

        function openGradeModal(id, marks, status, feedback) {
            document.getElementById('modalSubId').value = id;
            document.getElementById('modalMarks').value = marks;
            document.getElementById('modalStatus').value = status;
            document.getElementById('modalFeedback').value = feedback;
            document.getElementById('gradeModal').classList.add('active');
        }

        function closeModal() {
            document.getElementById('gradeModal').classList.remove('active');
        }
        
        // Close on outside click
        window.onclick = function(event) {
            let modal = document.getElementById('gradeModal');
            if (event.target == modal) {
                closeModal();
            }
        }
    </script>
</body>
</html>
<?php $conn->close(); ?>
