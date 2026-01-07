<?php
require_once __DIR__ . '/../../database/db-config.php';
$conn = getDbConnection();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = mysqli_real_escape_string($conn, trim($_POST['name']));
    
    // Slug Generation
    $slug = !empty($_POST['slug']) ? $_POST['slug'] : strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
    $slug = mysqli_real_escape_string($conn, $slug);

    $meta_title = mysqli_real_escape_string($conn, $_POST['meta_title']);
    $meta_desc = mysqli_real_escape_string($conn, $_POST['meta_desc']);
    $meta_keywords = mysqli_real_escape_string($conn, $_POST['meta_keywords']);
    $schema_markup = mysqli_real_escape_string($conn, $_POST['schema_markup']);
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    $sql = "INSERT INTO blog_categories (name, slug, meta_title, meta_desc, meta_keywords, schema_markup, is_active) 
            VALUES ('$name', '$slug', '$meta_title', '$meta_desc', '$meta_keywords', '$schema_markup', $is_active)";

    if ($conn->query($sql) === TRUE) {
        $success = "Category added successfully!";
    } else {
        $error = "Error: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Blog Category - MG Skills</title>
    <style>
        :root{--active:#22c55e;--indigo:#4f46e5;--line:#e2e8f0;--text:#1e293b;--muted:#64748b;--bg:#f8fafc;}
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:'Outfit',system-ui,sans-serif;background:var(--bg);color:var(--text)}
        .admin-content{margin-left:260px;min-height:100vh;padding:30px;transition:margin-left .3s ease}
        body.sidebar-collapsed .admin-content{margin-left:80px}
        
        .page-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:24px}
        .page-title{font-size:24px;font-weight:700;color:var(--text)}
        
        .card { background: white; border-radius: 12px; border: 1px solid var(--line); padding: 30px; max-width: 800px; }
        
        .form-group { margin-bottom: 20px; }
        .form-label { display: block; margin-bottom: 8px; font-weight: 600; font-size: 14px; color: var(--text); }
        .form-control { width: 100%; padding: 10px 12px; border: 1px solid var(--line); border-radius: 8px; font-size: 14px; background: #fff; }
        .form-textarea { width: 100%; padding: 10px 12px; border: 1px solid var(--line); border-radius: 8px; font-size: 14px; background: #fff; resize: vertical; }
        
        .section-title { font-size: 16px; font-weight: 700; color: var(--indigo); border-bottom: 1px solid var(--line); padding-bottom: 10px; margin: 30px 0 20px; }
        
        .btn-submit { padding: 12px 24px; background: var(--indigo); color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; }
    </style>
</head>
<body class="<?php echo isset($_COOKIE['sidebar_collapsed']) && $_COOKIE['sidebar_collapsed'] == 'true' ? 'sidebar-collapsed' : ''; ?>">

    <?php include '../../admin/sidebar.php'; ?>

    <div class="admin-content">
        <div class="page-header">
            <h1 class="page-title">Add Blog Category</h1>
            <a href="categories.php" style="text-decoration:none;color:var(--indigo);font-weight:600">View All Categories</a>
        </div>

        <?php if(isset($success)): ?>
            <div style="background:#dcfce7;color:#166534;padding:15px;border-radius:8px;margin-bottom:20px">✓ <?php echo $success; ?></div>
        <?php endif; ?>
        <?php if(isset($error)): ?>
            <div style="background:#fee2e2;color:#991b1b;padding:15px;border-radius:8px;margin-bottom:20px">⚠ <?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" class="card">
            
            <div class="form-group">
                <label class="form-label">Category Name *</label>
                <input type="text" name="name" id="name" class="form-control" required placeholder="e.g. Technology">
            </div>
            
            <div class="form-group">
                <label class="form-label">Slug (URL Friendly)</label>
                <input type="text" name="slug" id="slug" class="form-control" placeholder="auto-generated-if-empty">
            </div>

            <div class="section-title">SEO Configuration</div>

            <div class="form-group">
                <label class="form-label">Meta Title</label>
                <input type="text" name="meta_title" class="form-control" placeholder="SEO Title">
            </div>

            <div class="form-group">
                <label class="form-label">Meta Description</label>
                <textarea name="meta_desc" class="form-textarea" rows="2" placeholder="SEO Description"></textarea>
            </div>

            <div class="form-group">
                <label class="form-label">Meta Keywords</label>
                <input type="text" name="meta_keywords" class="form-control" placeholder="keyword1, keyword2">
            </div>

            <div class="form-group">
                <label class="form-label">Schema Markup (JSON-LD)</label>
                <textarea name="schema_markup" class="form-textarea" rows="4" placeholder='<script type="application/ld+json">{...}</script>'></textarea>
                <p style="font-size:12px;color:var(--muted);margin-top:4px">Paste complete JSON-LD script including script tags.</p>
            </div>

            <div class="form-group">
                <label style="display:flex;align-items:center;gap:8px;font-weight:600;cursor:pointer">
                    <input type="checkbox" name="is_active" checked> Active
                </label>
            </div>

            <button type="submit" class="btn-submit">Save Category</button>

        </form>
    </div>

    <script>
        document.getElementById('name').addEventListener('input', function() {
            if(!document.getElementById('slug').value) {
                this.value = this.value.replace(/[^a-zA-Z0-9\s]/g, ''); // Clean special chars
                let slug = this.value.toLowerCase().replace(/\s+/g, '-');
                // Don't auto-fill visual slug for now to avoid confusion, simpler to let backend handle or user type
                // But user usually likes visual feedback
                // document.getElementById('slug').placeholder = slug;
            }
        });
    </script>
</body>
</html>
<?php $conn->close(); ?>
