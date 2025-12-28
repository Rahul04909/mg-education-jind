<?php
session_start();
require_once __DIR__ . '/../database/db-config.php';

// Mock auth check
// if(!isset($_SESSION['reception_id'])) { header("Location: login.php"); exit; }

$conn = getDbConnection();

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$results = [];
$searched = false;

if ($search !== '') {
    $searched = true;
    $search_term = "%{$search}%";
    
    // Filter for Admin-added students only: center_id IS NULL or center_id = 0
    // Assuming 'admissions' table has 'full_name', 'enrollment_no', 'mobile', 'email', 'course_id', 'center_id'
    
    $sql = "SELECT a.*, c.title as course_title 
            FROM admissions a 
            LEFT JOIN courses c ON a.course_id = c.id 
            WHERE (a.center_id IS NULL OR a.center_id = 0) 
            AND (a.full_name LIKE ? OR a.enrollment_no LIKE ? OR a.mobile LIKE ?)
            ORDER BY a.created_at DESC LIMIT 20";
            
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("sss", $search_term, $search_term, $search_term);
        $stmt->execute();
        $res = $stmt->get_result();
        while($row = $res->fetch_assoc()) {
            $results[] = $row;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Search - Reception Panel</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
         :root {
            --primary: #ec4899; /* Pink 500 */
            --bg-body: #fdf2f8; 
            --text-main: #1e293b;
            --text-light: #64748b;
            --sidebar-w: 260px;
            --card-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -2px rgba(0, 0, 0, 0.1);
        }
        
        * { box-sizing: border-box; }
        body { margin: 0; font-family: 'Outfit', sans-serif; background: var(--bg-body); color: var(--text-main); }
        .main-content { margin-left: var(--sidebar-w); padding: 30px; transition: all 0.3s; min-height: 100vh; }
        
        .page-header { margin-bottom: 30px; }
        .page-title { font-size: 24px; font-weight: 700; color: #831843; margin-bottom: 5px; }
        .page-subtitle { color: var(--text-light); font-size: 14px; }

        .search-card {
            background: white;
            padding: 24px;
            border-radius: 16px;
            box-shadow: var(--card-shadow);
            margin-bottom: 30px;
        }
        
        .search-form { display: flex; gap: 10px; max-width: 600px; }
        .search-input {
            flex: 1;
            padding: 12px 16px;
            border: 2px solid #fbcfe8;
            border-radius: 12px;
            font-family: inherit;
            font-size: 15px;
            outline: none;
            transition: border-color 0.2s;
        }
        .search-input:focus { border-color: var(--primary); }
        
        .btn-search {
            background: var(--primary);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 15px;
        }
        .btn-search:hover { background: #db2777; }

        .results-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: var(--card-shadow);
        }
        
        .results-table th {
            text-align: left;
            padding: 16px 24px;
            background: #fce7f3;
            color: #be185d;
            font-weight: 600;
            font-size: 13px;
            text-transform: uppercase;
        }
        
        .results-table td {
            padding: 16px 24px;
            border-bottom: 1px solid #f1f5f9;
        }
        .results-table tr:last-child td { border-bottom: none; }
        .results-table tr:hover { background: #fff1f2; }

        .badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .badge-success { background: #dcfce7; color: #15803d; }
        .badge-warning { background: #fef9c3; color: #a16207; }

        .no-results {
            text-align: center;
            padding: 40px;
            color: var(--text-light);
            background: white;
            border-radius: 16px;
            box-shadow: var(--card-shadow);
        }

        @media (max-width: 768px) {
            .main-content { margin-left: 0; padding: 20px; }
            .search-form { flex-direction: column; }
            .results-table { display: block; overflow-x: auto; }
        }
    </style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<main class="main-content">
    <div class="page-header">
        <h1 class="page-title">Student Search</h1>
        <div class="page-subtitle">Search for students admitted by Head Office (Admin Only)</div>
    </div>

    <div class="search-card">
        <form class="search-form" method="GET">
            <input type="text" name="search" class="search-input" 
                   placeholder="Enter Name, Enrollment No, or Mobile..." 
                   value="<?php echo htmlspecialchars($search); ?>" required>
            <button type="submit" class="btn-search">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                Search
            </button>
        </form>
    </div>

    <?php if ($searched): ?>
        <?php if (count($results) > 0): ?>
            <div style="overflow-x:auto;">
                <table class="results-table">
                    <thead>
                        <tr>
                            <th>Enrollment No</th>
                            <th>Student Name</th>
                            <th>Course</th>
                            <th>Mobile</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($results as $row): ?>
                            <tr>
                                <td style="font-family:monospace; font-weight:600; color:#831843;">
                                    <?php echo htmlspecialchars($row['enrollment_no']); ?>
                                </td>
                                <td>
                                    <div style="font-weight:600;"><?php echo htmlspecialchars($row['full_name']); ?></div>
                                    <div style="font-size:12px; color:var(--text-light);"><?php echo htmlspecialchars($row['email']); ?></div>
                                </td>
                                <td><?php echo htmlspecialchars($row['course_title'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($row['mobile']); ?></td>
                                <td>
                                    <span class="badge <?php echo ($row['payment_status'] ?? 'pending') === 'success' ? 'badge-success' : 'badge-warning'; ?>">
                                        <?php echo ucfirst($row['payment_status'] ?? 'Pending'); ?>
                                    </span>
                                </td>
                                <td>
                                    <!-- Placeholder links, waiting for specific view page requirement or use admin's view -->
                                    <a href="#" style="color:var(--primary); font-weight:600; text-decoration:none;">View Details</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="no-results">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#fb7185" stroke-width="1.5" style="margin-bottom:15px;"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line><line x1="8" y1="11" x2="14" y2="11"></line></svg>
                <h3>No students found</h3>
                <p>No records match "<?php echo htmlspecialchars($search); ?>" within Admin admissions.</p>
            </div>
        <?php endif; ?>
    <?php endif; ?>

</main>
</body>
</html>
