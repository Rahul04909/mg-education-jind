<?php
require_once __DIR__ . '/../../database/db-config.php';
$conn = getDbConnection();

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Search
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$where_clause = "WHERE 1=1";
if (!empty($search)) {
    $where_clause .= " AND (b.title LIKE '%$search%' OR c.name LIKE '%$search%')";
}

// Count Total
$total_sql = "SELECT COUNT(*) as count FROM blogs b LEFT JOIN blog_categories c ON b.category_id = c.id $where_clause";
$total_res = $conn->query($total_sql);
$total_row = $total_res->fetch_assoc();
$total_records = $total_row['count'];
$total_pages = ceil($total_records / $limit);

// Fetch Blogs
$sql = "SELECT b.*, c.name as category_name 
        FROM blogs b 
        LEFT JOIN blog_categories c ON b.category_id = c.id 
        $where_clause 
        ORDER BY b.created_at DESC 
        LIMIT $limit OFFSET $offset";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Blogs - MG Skills</title>
    <style>
        :root{--active:#22c55e;--indigo:#4f46e5;--line:#e2e8f0;--text:#1e293b;--muted:#64748b;--bg:#f8fafc;}
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:'Outfit',system-ui,sans-serif;background:var(--bg);color:var(--text)}
        .admin-content{margin-left:260px;min-height:100vh;padding:30px;transition:margin-left .3s ease}
        body.sidebar-collapsed .admin-content{margin-left:80px}
        
        .page-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:24px}
        .page-title{font-size:24px;font-weight:700;color:var(--text)}
        
        .filters{display:flex;gap:16px;margin-bottom:24px;background:#fff;padding:16px;border-radius:12px;border:1px solid var(--line)}
        .search-box{flex:1;position:relative}
        .search-input{width:100%;padding:10px 16px 10px 40px;border:1px solid var(--line);border-radius:8px;font-size:14px;outline:none}
        .search-icon{position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--muted);width:16px;height:16px}
        
        .table-card{background:#fff;border-radius:12px;border:1px solid var(--line);overflow:hidden}
        table{width:100%;border-collapse:collapse}
        th{text-align:left;padding:16px 24px;background:#f8fafc;border-bottom:1px solid var(--line);font-weight:600;font-size:13px;color:var(--muted);text-transform:uppercase}
        td{padding:16px 24px;border-bottom:1px solid var(--line);font-size:14px}
        tr:last-child td{border-bottom:none}
        
        .status-badge{padding:4px 10px;border-radius:20px;font-size:12px;font-weight:600}
        .status-active{background:#dcfce7;color:#166534}
        .status-inactive{background:#f1f5f9;color:var(--muted)}
        
        .actions{display:flex;gap:8px}
        .action-btn{width:32px;height:32px;border-radius:6px;display:flex;align-items:center;justify-content:center;color:var(--muted);border:1px solid transparent;transition:all .2s;text-decoration:none}
        .action-btn:hover{background:#f1f5f9;color:var(--text);border-color:var(--line)}
        .action-btn.edit:hover{background:#e0e7ff;color:var(--indigo);border-color:#c7d2fe}
        .action-btn.delete:hover{background:#fee2e2;color:#991b1b;border-color:#fecaca}
        
        .pagination{display:flex;justify-content:center;gap:8px;padding:24px}
        .page-link{width:36px;height:36px;display:flex;align-items:center;justify-content:center;border-radius:8px;border:1px solid var(--line);color:var(--text);text-decoration:none;font-weight:500}
        .page-link.active{background:var(--indigo);color:#fff;border-color:var(--indigo)}
        
        .btn-add{padding:10px 20px;background:var(--indigo);color:#fff;border-radius:8px;text-decoration:none;font-weight:600;display:inline-flex;align-items:center;gap:8px;font-size:14px}
    </style>
</head>
<body class="<?php echo isset($_COOKIE['sidebar_collapsed']) && $_COOKIE['sidebar_collapsed'] == 'true' ? 'sidebar-collapsed' : ''; ?>">

    <?php include '../../admin/sidebar.php'; ?>

    <div class="admin-content">
        <div class="page-header">
            <h1 class="page-title">Blog Posts</h1>
            <a href="add-blog.php" class="btn-add">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                Write New Blog
            </a>
        </div>

        <div class="filters">
            <div class="search-box">
                <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                <form action="" method="GET">
                    <input type="text" name="search" class="search-input" placeholder="Search blogs by title or category..." value="<?php echo htmlspecialchars($search); ?>">
                </form>
            </div>
        </div>

        <div class="table-card">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Views</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td>#<?php echo $row['id']; ?></td>
                                <td style="font-weight:500"><?php echo htmlspecialchars($row['title']); ?></td>
                                <td><?php echo htmlspecialchars($row['category_name']); ?></td>
                                <td><?php echo $row['views']; ?></td>
                                <td>
                                    <?php if($row['is_active']): ?>
                                        <span class="status-badge status-active">Published</span>
                                    <?php else: ?>
                                        <span class="status-badge status-inactive">Draft</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('d M Y', strtotime($row['created_at'])); ?></td>
                                <td class="actions">
                                    <a href="#" class="action-btn" title="View"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg></a>
                                    <a href="edit-blog.php?id=<?php echo $row['id']; ?>" class="action-btn edit" title="Edit"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg></a>
                                    <a href="javascript:void(0)" onclick="deleteBlog(<?php echo $row['id']; ?>)" class="action-btn delete" title="Delete"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg></a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="7" style="text-align:center;padding:40px;color:var(--muted)">No blogs found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($total_pages > 1): ?>
        <div class="pagination">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>" class="page-link <?php echo ($i == $page) ? 'active' : ''; ?>"><?php echo $i; ?></a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
    </div>
    
    <script>
        function deleteBlog(id) {
            if(confirm('Are you sure you want to delete this blog post?')) {
                window.location.href = 'delete-blog.php?id=' + id;
            }
        }
    </script>
</body>
</html>
<?php $conn->close(); ?>
