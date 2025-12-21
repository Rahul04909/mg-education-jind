<?php
require_once '../auth_check.php';
require_once '../../database/db-config.php';

$conn = getDbConnection();

// Fetch Categories for Filter
$cats = $conn->query("SELECT id, name FROM course_categories WHERE is_active = 1");

// Filters
$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';


// Pagination
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Query Construction
$where = "WHERE c.is_active = 1";
$params = [];
$types = "";

if ($search) {
    $where .= " AND c.title LIKE ?";
    $params[] = "%$search%";
    $types .= "s";
}

if ($category) {
    $where .= " AND c.category_id = ?";
    $params[] = $category;
    $types .= "i";
}

// Total Count for Pagination
$count_sql = "SELECT COUNT(*) as total FROM courses c $where";
$stmt = $conn->prepare($count_sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$total_rows = $stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $limit);

// Main Query
$sql = "SELECT c.*, cc.name as category_name 
        FROM courses c 
        LEFT JOIN course_categories cc ON c.category_id = cc.id 
        $where 
        ORDER BY c.created_at DESC 
        LIMIT ?, ?";

$params[] = $offset;
$params[] = $limit;
$types .= "ii";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Available Courses - MG Skills</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #059669; --text: #0b1020; --muted: #64748b; --line: #e2e8f0; --bg: #f1f5f9; }
        body { font-family: 'Outfit', sans-serif; background: var(--bg); color: var(--text); margin: 0; padding: 0; }
        
        .main-content { margin-left: 280px; padding: 32px; min-height: 100vh; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; }
        .title { font-size: 24px; font-weight: 700; margin: 0; color: #1e293b; }
        
        /* Filters */
        .filter-bar { 
            background: #fff; padding: 20px; border-radius: 16px; border: 1px solid var(--line); margin-bottom: 24px; 
            display: flex; gap: 16px; flex-wrap: wrap; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02); align-items: end;
        }
        .filter-group { display: flex; flexDirection: column; gap: 6px; flex: 1; min-width: 200px; }
        .filter-label { font-size: 12px; font-weight: 600; color: var(--muted); text-transform: uppercase; }
        .form-input { 
            width: 100%; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-family: inherit; font-size: 14px; 
            transition: all 0.2s; box-sizing: border-box;
        }
        .form-input:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.1); }
        
        .btn-filter { 
            background: var(--primary); color: white; border: none; padding: 10px 24px; border-radius: 8px; font-weight: 600; cursor: pointer; height: 42px;
            transition: all 0.2s;
        }
        .btn-filter:hover { background: #047857; }
        .btn-reset { 
            background: white; color: var(--muted); border: 1px solid var(--line); padding: 10px 16px; border-radius: 8px; font-weight: 600; cursor: pointer; height: 42px;
            transition: all 0.2s; text-decoration: none; display: flex; align-items: center;
        }
        .btn-reset:hover { background: #f8fafc; color: var(--text); border-color: #cbd5e1; }

        /* Table */
        .table-card { background: #fff; border-radius: 16px; border: 1px solid var(--line); overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02); }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 16px 24px; font-size: 12px; font-weight: 700; color: var(--muted); text-transform: uppercase; background: #f8fafc; border-bottom: 1px solid var(--line); }
        td { padding: 16px 24px; border-bottom: 1px solid #f1f5f9; font-size: 14px; font-weight: 500; }
        tr:last-child td { border-bottom: none; }
        tr:hover td { background: #f8fafc; }
        
        .status-badge { padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; }
        .status-active { background: #ecfdf5; color: #059669; }
        .status-inactive { background: #fef2f2; color: #ef4444; }
        
        .price { font-weight: 700; color: #059669; }
        
        /* Pagination */
        .pagination { display: flex; justify-content: center; gap: 8px; margin-top: 24px; }
        .page-link { 
            width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; border-radius: 8px; background: white; border: 1px solid var(--line); 
            color: var(--text); text-decoration: none; font-weight: 600; transition: all 0.2s; font-size: 14px;
        }
        .page-link:hover { border-color: var(--primary); color: var(--primary); }
        .page-link.active { background: var(--primary); color: white; border-color: var(--primary); }
        .page-link.disabled { opacity: 0.5; pointer-events: none; }

        /* Empty State */
        .empty-state { padding: 40px; text-align: center; color: var(--muted); }

        @media (max-width: 900px) { .main-content { margin-left: 0; padding: 20px; } .filter-bar { flex-direction: column; } .filter-group { width: 100%; } }
    </style>
</head>
<body>
    <?php include '../sidebar.php'; ?>
    
    <main class="main-content">
        <div class="header">
            <h1 class="title">Available Courses</h1>
            <div style="font-size:14px; color:var(--muted)">Total: <?php echo $total_rows; ?> Courses</div>
        </div>
        
        <!-- Filters -->
        <form method="GET" class="filter-bar">
            <div class="filter-group" style="flex: 2;">
                <label class="filter-label">Search Course</label>
                <input type="text" name="search" class="form-input" placeholder="Enter course name..." value="<?php echo htmlspecialchars($search); ?>">
            </div>
            
            <div class="filter-group">
                <label class="filter-label">Category</label>
                <select name="category" class="form-input">
                    <option value="">All Categories</option>
                    <?php while($cat = $cats->fetch_assoc()): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo $category == $cat['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <button type="submit" class="btn-filter">Filters</button>
            <a href="index.php" class="btn-reset">Reset</a>
        </form>
        
        <!-- Course List -->
        <div class="table-card">
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th width="50">Sr.</th>
                            <th>Course Name</th>
                            <th>Category</th>
                            <th>Duration</th>
                            <th>Fees</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($result->num_rows > 0): ?>
                            <?php $sr = $offset + 1; ?>
                            <?php while($row = $result->fetch_assoc()): ?>
                                <?php 
                                    $fees = json_decode($row['fees'], true) ?? ['amount' => 0]; 
                                    $amount = is_numeric($fees['amount']) ? '₹ ' . number_format($fees['amount']) : $fees['amount'];
                                ?>
                                <tr>
                                    <td><?php echo $sr++; ?></td>
                                    <td>
                                        <div style="font-weight:600; color:#1e293b"><?php echo htmlspecialchars($row['title']); ?></div>
                                        <div style="font-size:12px; color:var(--muted)">Slug: <?php echo htmlspecialchars($row['slug']); ?></div>
                                    </td>
                                    <td><span class="status-badge" style="background:#f1f5f9; color:#475569; border:1px solid #e2e8f0"><?php echo htmlspecialchars($row['category_name'] ?? 'Uncategorized'); ?></span></td>
                                    <td>
                                        <?php echo htmlspecialchars($row['duration_value'] . ' ' . ucfirst($row['duration_type'])); ?>
                                    </td>
                                    <td class="price"><?php echo $amount; ?></td>
                                    <td>
                                        <?php if($row['is_active']): ?>
                                            <span class="status-badge status-active">Active</span>
                                        <?php else: ?>
                                            <span class="status-badge status-inactive">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="empty-state">No courses found matching your criteria.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Pagination -->
        <?php if($total_pages > 1): ?>
        <div class="pagination">
            <a href="?page=<?php echo max(1, $page - 1); ?>&search=<?php echo urlencode($search); ?>&category=<?php echo $category; ?>" class="page-link <?php echo $page <= 1 ? 'disabled' : ''; ?>">←</a>
            
            <?php for($i = 1; $i <= $total_pages; $i++): ?>
                <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo $category; ?>" class="page-link <?php echo $i == $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
            <?php endfor; ?>
            
            <a href="?page=<?php echo min($total_pages, $page + 1); ?>&search=<?php echo urlencode($search); ?>&category=<?php echo $category; ?>" class="page-link <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">→</a>
        </div>
        <?php endif; ?>
        
    </main>
</body>
</html>
<?php $conn->close(); ?>
