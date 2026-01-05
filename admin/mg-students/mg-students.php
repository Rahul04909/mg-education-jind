<?php
require_once __DIR__ . '/../../database/db-config.php';
require_once __DIR__ . '/../../database/update_admission_schema.php';

$conn = getDbConnection();

// Fetch Courses for Filter
$courses = [];
$c_sql = "SELECT id, title FROM courses WHERE is_active = 1";
$c_res = $conn->query($c_sql);
while($row = $c_res->fetch_assoc()) $courses[] = $row;

// Fetch Sessions for Filter
$sessions = [];
$s_sql = "SELECT id, session_name FROM course_sessions WHERE is_active = 1 ORDER BY id DESC";
$s_res = $conn->query($s_sql);
while($row = $s_res->fetch_assoc()) $sessions[] = $row;

// Filtration Logic
$where_clauses = ["(added_by IS NULL OR added_by != 'center')"];
$params = [];
$types = "";

// Filter by Course
if (isset($_GET['course_id']) && !empty($_GET['course_id'])) {
    $where_clauses[] = "a.course_id = ?";
    $params[] = intval($_GET['course_id']);
    $types .= "i";
}

// Filter by Session
if (isset($_GET['session_id']) && !empty($_GET['session_id'])) {
    $where_clauses[] = "a.session_id = ?";
    $params[] = intval($_GET['session_id']);
    $types .= "i";
}

// Filter by Search (Name, Enrollment, Mobile, Email)
$search_term = '';
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search_term = '%' . $_GET['search'] . '%';
    $where_clauses[] = "(a.full_name LIKE ? OR a.enrollment_no LIKE ? OR a.mobile LIKE ? OR a.email LIKE ?)";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
    $types .= "ssss";
}

$where_sql = implode(" AND ", $where_clauses);

// Pagination
$limit = 10;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// Fetch Students Query
$sql = "SELECT a.*, c.title as course_title, s.session_name 
        FROM admissions a 
        LEFT JOIN courses c ON a.course_id = c.id 
        LEFT JOIN course_sessions s ON a.session_id = s.id 
        WHERE $where_sql 
        ORDER BY a.created_at DESC 
        LIMIT ?, ?";

$types .= "ii";
$params[] = $offset;
$params[] = $limit;

$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Total Count for Pagination
$count_sql = "SELECT COUNT(*) as total FROM admissions a WHERE $where_sql";
$count_stmt = $conn->prepare($count_sql);
// Remove limit/offset params for count
$count_types = substr($types, 0, -2); 
$count_params = array_slice($params, 0, -2);

if ($count_params) {
    $count_stmt->bind_param($count_types, ...$count_params);
}
$count_stmt->execute();
$total_result = $count_stmt->get_result();
$total_rows = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $limit);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MG Students - MG Skills</title>
    <style>
        :root{--active:#22c55e;--indigo:#4f46e5;--line:#e2e8f0;--text:#1e293b;--muted:#64748b;--bg:#f8fafc}
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:'Outfit',system-ui,sans-serif;background:var(--bg);color:var(--text)}
        .admin-content{margin-left:260px;min-height:100vh;padding:30px;transition:margin-left .3s ease}
        body.sidebar-collapsed .admin-content{margin-left:80px}
        .admin-wrap{max-width:1400px;margin:0 auto}
        
        .page-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:24px}
        .page-title{font-size:24px;font-weight:700;color:var(--text)}
        
        .filter-card{background:#fff;padding:20px;border-radius:12px;box-shadow:0 1px 3px rgba(0,0,0,0.05);margin-bottom:24px}
        .filter-form{display:grid;grid-template-columns:repeat(auto-fit, minmax(200px, 1fr));gap:16px;align-items:end}
        .form-group label{display:block;font-size:13px;font-weight:600;margin-bottom:6px;color:var(--muted)}
        .form-control{width:100%;padding:10px 12px;border:1px solid var(--line);border-radius:8px;font-size:14px;transition:all 0.2s}
        .form-control:focus{outline:none;border-color:var(--indigo);box-shadow:0 0 0 3px rgba(79, 70, 229, 0.1)}
        
        .btn{padding:10px 20px;border-radius:8px;font-weight:500;text-decoration:none;display:inline-flex;align-items:center;justify-content:center;gap:8px;font-size:14px;border:none;cursor:pointer;transition:all 0.2s}
        .btn-primary{background:var(--indigo);color:#fff}
        .btn-primary:hover{background:#4338ca}
        .btn-secondary{background:#fff;border:1px solid var(--line);color:var(--text)}
        .btn-secondary:hover{background:#f8fafc}
        
        .table-card{background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,0.05);border:1px solid var(--line)}
        table{width:100%;border-collapse:collapse;font-size:14px}
        th{background:#f8fafc;padding:12px 20px;text-align:left;font-weight:600;color:var(--muted);border-bottom:1px solid var(--line);font-size:12px;text-transform:uppercase;letter-spacing:0.5px}
        td{padding:14px 20px;border-bottom:1px solid var(--line);vertical-align:middle}
        tr:last-child td{border-bottom:none}
        tr:hover td{background:#f8fafc}
        
        .badge{padding:4px 10px;border-radius:99px;font-size:12px;font-weight:600;display:inline-block}
        .badge-success{background:#dcfce7;color:#166534}
        .badge-warning{background:#fef3c7;color:#92400e}
        
        .actions{display:flex;gap:8px}
        .btn-sm{padding:6px 12px;font-size:12px;border-radius:6px;height:30px}
        
        .pagination{display:flex;justify-content:center;gap:6px;margin-top:24px}
        .page-link{width:36px;height:36px;display:flex;align-items:center;justify-content:center;border-radius:8px;background:#fff;border:1px solid var(--line);color:var(--text);text-decoration:none;font-weight:500;transition:all 0.2s}
        .page-link:hover{border-color:var(--indigo);color:var(--indigo)}
        .page-link.active{background:var(--indigo);color:#fff;border-color:var(--indigo)}
        
        .user-info{display:flex;flex-direction:column}
        .user-name{font-weight:600;color:var(--text)}
        .user-email{font-size:12px;color:var(--muted)}
    </style>
</head>
<body>
    <?php include __DIR__ . "/../sidebar.php"; ?>
    
    <main class="admin-content">
        <div class="admin-wrap">
            <div class="page-header">
                <div>
                    <h1 class="page-title">MG Students</h1>
                    <p style="color:var(--muted); font-size:14px; margin-top:4px">Manage students enrolled directly by Admin</p>
                </div>
                <a href="add-student.php" class="btn btn-primary">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
                    New Student
                </a>
            </div>
            
            <div class="filter-card">
                <form method="GET" class="filter-form">
                    <div class="form-group">
                        <label>Search Student</label>
                        <input type="text" name="search" class="form-control" placeholder="Name, Enrollment, Email..." value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label>Filter by Course</label>
                        <select name="course_id" class="form-control">
                            <option value="">All Courses</option>
                            <?php foreach($courses as $c): ?>
                                <option value="<?php echo $c['id']; ?>" <?php echo (isset($_GET['course_id']) && $_GET['course_id'] == $c['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($c['title']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Filter by Session</label>
                        <select name="session_id" class="form-control">
                            <option value="">All Sessions</option>
                            <?php foreach($sessions as $s): ?>
                                <option value="<?php echo $s['id']; ?>" <?php echo (isset($_GET['session_id']) && $_GET['session_id'] == $s['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($s['session_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group" style="display:flex; gap:10px">
                        <button type="submit" class="btn btn-primary" style="flex:1">Filter</button>
                        <a href="mg-students.php" class="btn btn-secondary">Reset</a>
                    </div>
                </form>
            </div>
            
            <div class="table-card">
                <table>
                    <thead>
                        <tr>
                            <th>Enrollment No</th>
                            <th>Student Details</th>
                            <th>Course & Session</th>
                            <th>Info</th>
                            <th>Status Detail</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <span style="font-family:monospace;font-weight:600;color:var(--indigo)"><?php echo htmlspecialchars($row['enrollment_no']); ?></span>
                                    </td>
                                    <td>
                                        <div class="user-info">
                                            <span class="user-name"><?php echo htmlspecialchars($row['full_name']); ?></span>
                                            <span class="user-email"><?php echo htmlspecialchars($row['email']); ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <div style="font-weight:500"><?php echo htmlspecialchars($row['course_title'] ?? 'N/A'); ?></div>
                                        <div style="font-size:12px;color:var(--muted)"><?php echo htmlspecialchars($row['session_name'] ?? 'N/A'); ?></div>
                                    </td>
                                    <td>
                                        <div style="font-size:13px;color:var(--text)"><?php echo htmlspecialchars($row['mobile']); ?></div>
                                        <div style="font-size:12px;color:var(--muted)"><?php echo htmlspecialchars($row['admission_mode']); ?></div>
                                    </td>
                                    <td>
                                        <span class="badge <?php echo $row['payment_status'] == 'success' ? 'badge-success' : 'badge-warning'; ?>">
                                            <?php echo ucfirst($row['payment_status']); ?>
                                        </span>
                                    </td>
                                    <td class="actions">
                                        <a href="view-student.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-secondary" title="View">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                        </a>
                                        <a href="edit-student.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-secondary" title="Edit">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="6" style="text-align:center;padding:40px;color:var(--muted)">No students found matching your criteria.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php if($page > 1): ?>
                    <a href="?page=<?php echo $page-1; ?>&search=<?php echo urlencode($_GET['search']??''); ?>&course_id=<?php echo $_GET['course_id']??''; ?>&session_id=<?php echo $_GET['session_id']??''; ?>" class="page-link">&laquo;</a>
                <?php endif; ?>
                
                <?php for($i=1; $i<=$total_pages; $i++): ?>
                    <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($_GET['search']??''); ?>&course_id=<?php echo $_GET['course_id']??''; ?>&session_id=<?php echo $_GET['session_id']??''; ?>" class="page-link <?php echo $page == $i ? 'active' : ''; ?>"><?php echo $i; ?></a>
                <?php endfor; ?>
                
                <?php if($page < $total_pages): ?>
                    <a href="?page=<?php echo $page+1; ?>&search=<?php echo urlencode($_GET['search']??''); ?>&course_id=<?php echo $_GET['course_id']??''; ?>&session_id=<?php echo $_GET['session_id']??''; ?>" class="page-link">&raquo;</a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            
        </div>
    </main>
    <script>
        // Simple script to toggle sidebar if needed, taken from sidebar.php which usually handles it.
        // But usually sidebar.php has the script.
    </script>
</body>
</html>
<?php $conn->close(); ?>
