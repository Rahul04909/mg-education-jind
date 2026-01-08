<?php
require_once __DIR__ . '/../../database/db-config.php';
$conn = getDbConnection();

// Initial Params
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category_slug = isset($_GET['category']) ? trim($_GET['category']) : '';
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 9;
$offset = ($page - 1) * $limit;

// Fetch Categories for Sidebar/Filter
$categories = [];
$cat_res = $conn->query("SELECT * FROM blog_categories WHERE is_active = 1 ORDER BY name ASC");
if ($cat_res) {
    while ($row = $cat_res->fetch_assoc()) {
        $categories[] = $row;
    }
}

// Build Query
$where_clauses = ["b.is_active = 1"];
$params = [];
$types = "";

if ($search) {
    $where_clauses[] = "(b.title LIKE ? OR b.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $types .= "ss";
}

if ($category_slug) {
    // Find category ID by slug (assuming category table has slugs or just use ID if passed)
    // For now assuming ID is passed or we join. Let's join.
    // Actually simpler to filter by ID if URL uses ID, but SEO friendly uses slug.
    // Let's assume $_GET['category'] is ID for MVP simplicity or slug if we had it.
    // Checking add-blog.php, categories have names. Let's match by ID for robustness if using links from elsewhere, 
    // or better, if the UI uses IDs. Let's use ID for filter param 'cat_id' or 'category'.
    // If string is passed, try to match ID.
    if (is_numeric($category_slug)) {
         $where_clauses[] = "b.category_id = ?";
         $params[] = intval($category_slug);
         $types .= "i";
    }
}

$where_sql = implode(' AND ', $where_clauses);

// Count Total
$count_sql = "SELECT COUNT(*) as total FROM blogs b WHERE $where_sql";
$stmt = $conn->prepare($count_sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$total_rows = $stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $limit);

// Fetch Blogs
$sql = "SELECT b.*, c.name as category_name 
        FROM blogs b 
        LEFT JOIN blog_categories c ON b.category_id = c.id 
        WHERE $where_sql 
        ORDER BY b.created_at DESC 
        LIMIT ?, ?";

$params[] = $offset;
$params[] = $limit;
$types .= "ii";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$blogs = [];
while ($row = $result->fetch_assoc()) {
    $blogs[] = $row;
}

// Include Header
include __DIR__ . '/../../includes/header.php';
?>

<style>
    :root {
        --primary: #4f46e5;
        --secondary: #1e293b;
        --accent: #818cf8;
        --bg-light: #f8fafc;
        --text-gray: #64748b;
    }
    
    body { background: var(--bg-light); font-family: 'Outfit', sans-serif; }

    /* Hero Section */
    .blog-hero {
        background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
        color: white;
        padding: 80px 20px;
        text-align: center;
        position: relative;
        overflow: hidden;
    }
    .blog-hero::before {
        content: ''; position: absolute; top:0; left:0; right:0; bottom:0;
        background: url('../../assets/images/pattern-dot.png');
        opacity: 0.1;
    }
    .hero-content { position: relative; z-index: 2; max-width: 800px; margin: 0 auto; }
    .hero-title { font-size: 48px; font-weight: 800; margin-bottom: 16px; letter-spacing: -1px; background: linear-gradient(to right, #fff, #94a3b8); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
    .hero-sub { font-size: 18px; color: #cbd5e1; line-height: 1.6; }

    /* Filter Bar */
    .filter-bar {
        background: white;
        padding: 20px 0;
        border-bottom: 1px solid #e2e8f0;
        position: sticky;
        top: 0; /* Adjust if header is sticky */
        z-index: 90;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
    }
    .filter-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 20px;
    }
    
    .cat-pills { display: flex; gap: 10px; overflow-x: auto; padding-bottom: 2px; }
    .cat-pill {
        padding: 8px 16px;
        border-radius: 99px;
        background: #f1f5f9;
        color: var(--text-gray);
        font-size: 14px;
        font-weight: 600;
        text-decoration: none;
        white-space: nowrap;
        transition: all 0.2s;
    }
    .cat-pill:hover, .cat-pill.active {
        background: var(--primary);
        color: white;
    }

    .search-wrap-blog {
        position: relative;
        min-width: 250px;
    }
    .search-input-blog {
        width: 100%;
        padding: 10px 16px 10px 40px;
        border: 1px solid #e2e8f0;
        border-radius: 99px;
        font-size: 14px;
        outline: none;
        transition: border-color 0.2s;
    }
    .search-input-blog:focus { border-color: var(--primary); }
    .search-icon-blog {
        position: absolute; left: 14px; top: 50%; transform: translateY(-50%);
        color: #94a3b8; width: 16px; height: 16px;
    }

    /* Grid */
    .blog-grid-section { padding: 50px 20px; }
    .wrapper { max-width: 1200px; margin: 0 auto; }
    
    .blog-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
        gap: 30px;
    }

    /* Card */
    .blog-card {
        background: white;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        border: 1px solid #f1f5f9;
        display: flex; flex-direction: column;
    }
    .blog-card:hover { transform: translateY(-5px); box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1); }
    
    .blog-thumb {
        height: 220px;
        overflow: hidden;
        position: relative;
        background: #e2e8f0;
    }
    .blog-thumb img {
        width: 100%; height: 100%; object-fit: cover;
        transition: transform 0.5s ease;
    }
    .blog-card:hover .blog-thumb img { transform: scale(1.05); }

    .blog-badge {
        position: absolute; top: 16px; left: 16px;
        background: rgba(255,255,255,0.95);
        color: var(--primary);
        padding: 6px 12px;
        border-radius: 8px;
        font-size: 12px; font-weight: 700;
        text-transform: uppercase;
        backdrop-filter: blur(4px);
    }

    .blog-content { padding: 24px; flex: 1; display: flex; flex-direction: column; }
    
    .blog-meta {
        font-size: 13px; color: #94a3b8; margin-bottom: 12px;
        display: flex; align-items: center; gap: 8px;
    }
    .dot { width: 4px; height: 4px; background: #cbd5e1; border-radius: 50%; }

    .blog-title {
        font-size: 20px; font-weight: 700; color: #1e293b;
        margin-bottom: 12px; line-height: 1.4;
        display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
    }
    .blog-excerpt {
        font-size: 15px; color: var(--text-gray); line-height: 1.6;
        margin-bottom: 20px;
        display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;
        flex: 1;
    }

    .read-more {
        display: inline-flex; align-items: center; gap: 6px;
        color: var(--primary); font-weight: 600; font-size: 14px;
        text-decoration: none; transition: gap 0.2s;
    }
    .read-more:hover { gap: 10px; }

    /* Pagination */
    .pagination { display: flex; justify-content: center; gap: 8px; margin-top: 60px; }
    .page-link {
        width: 40px; height: 40px;
        display: flex; align-items: center; justify-content: center;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        background: white;
        color: var(--secondary);
        text-decoration: none; font-weight: 600;
        transition: all 0.2s;
    }
    .page-link:hover, .page-link.active {
        background: var(--primary); color: white; border-color: var(--primary);
    }

    @media (max-width: 768px) {
        .filter-container { flex-direction: column; align-items: stretch; gap: 15px; }
        .hero-title { font-size: 32px; }
        .blog-grid { grid-template-columns: 1fr; }
    }
</style>

<!-- Hero -->
<section class="blog-hero">
    <div class="hero-content">
        <h1 class="hero-title">Latest Insights & News</h1>
        <p class="hero-sub">Discover the latest updates, educational articles, and success stories from the MG Skills community.</p>
    </div>
</section>

<!-- Filter Bar -->
<div class="filter-bar">
    <div class="filter-container">
        <div class="cat-pills">
            <a href="index.php" class="cat-pill <?php echo $category_slug == '' ? 'active' : ''; ?>">All Posts</a>
            <?php foreach($categories as $cat): ?>
                <a href="?category=<?php echo $cat['id']; ?>" class="cat-pill <?php echo $category_slug == $cat['id'] ? 'active' : ''; ?>">
                    <?php echo htmlspecialchars($cat['name']); ?>
                </a>
            <?php endforeach; ?>
        </div>
        
        <form method="GET" class="search-wrap-blog">
            <?php if($category_slug): ?><input type="hidden" name="category" value="<?php echo htmlspecialchars($category_slug); ?>"><?php endif; ?>
            <svg class="search-icon-blog" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
            <input type="text" name="search" class="search-input-blog" placeholder="Search articles..." value="<?php echo htmlspecialchars($search); ?>">
        </form>
    </div>
</div>

<!-- Blog Grid -->
<section class="blog-grid-section">
    <div class="wrapper">
        <div class="blog-grid">
            <?php if (count($blogs) > 0): ?>
                <?php foreach($blogs as $blog): ?>
                <article class="blog-card">
                    <div class="blog-thumb">
                        <span class="blog-badge"><?php echo htmlspecialchars($blog['category_name'] ?? 'General'); ?></span>
                        <a href="../../blog-details.php?slug=<?php echo urlencode($blog['slug']); ?>">
                            <img src="../../<?php echo !empty($blog['featured_image']) ? $blog['featured_image'] : 'assets/images/placeholder-blog.jpg'; ?>" 
                                 alt="<?php echo htmlspecialchars($blog['title']); ?>"
                                 loading="lazy"
                                 onerror="this.src='data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI4MDAiIGhlaWdodD0iNjAwIiB2aWV3Qm94PSIwIDAgODAwIDYwMCI+PHJlY3Qgd2lkdGg9IjEwMCUiIGhlaWdodD0iMTAwJSIgZmlsbD0iI2YxZjViOSIvPjx0ZXh0IHg9IjUwJSIgeT0iNTAlIiBmb250LWZhbWlseT0iYXJpYWwiIGZvbnQtc2l6ZT0iMjQiIGZpbGw9IiM5NGEzYjgiIGRvbWluYW50LWJhc2VsaW5lPSJtaWRkbGUiIHRleHQtYW5jaG9yPSJtaWRkbGUiPk5vIEltYWdlPC90ZXh0Pjwvc3ZnPg=='">
                        </a>
                    </div>
                    <div class="blog-content">
                        <div class="blog-meta">
                            <span><?php echo date('M d, Y', strtotime($blog['created_at'])); ?></span>
                            <span class="dot"></span>
                            <span>5 min read</span>
                        </div>
                        <h3 class="blog-title">
                            <a href="../../blog-details.php?slug=<?php echo urlencode($blog['slug']); ?>" style="color:inherit; text-decoration:none;">
                                <?php echo htmlspecialchars($blog['title']); ?>
                            </a>
                        </h3>
                        <p class="blog-excerpt">
                            <?php echo substr(strip_tags($blog['description']), 0, 120) . '...'; ?>
                        </p>
                        <a href="../../blog-details.php?slug=<?php echo urlencode($blog['slug']); ?>" class="read-more">
                            Read Article <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                        </a>
                    </div>
                </article>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="grid-column:1/-1; text-align:center; padding:60px 20px;">
                    <h3 style="color:var(--secondary); margin-bottom:10px;">No articles found</h3>
                    <p style="color:var(--text-gray);">Try adjusting your search or category filter.</p>
                    <a href="index.php" style="display:inline-block; margin-top:15px; color:var(--primary); font-weight:600; text-decoration:none;">View All Posts</a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?page=<?php echo $page-1; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo $category_slug; ?>" class="page-link">&laquo;</a>
            <?php endif; ?>
            
            <?php for($i=1; $i<=$total_pages; $i++): ?>
                <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo $category_slug; ?>" class="page-link <?php echo $i == $page ? 'active' : ''; ?>">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>
            
            <?php if ($page < $total_pages): ?>
                <a href="?page=<?php echo $page+1; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo $category_slug; ?>" class="page-link">&raquo;</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
<?php $conn->close(); ?>
