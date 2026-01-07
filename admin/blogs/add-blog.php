<?php
require_once __DIR__ . '/../../database/db-config.php';
$conn = getDbConnection();

// Fetch Categories
$cats = [];
$cat_res = $conn->query("SELECT id, name FROM blog_categories WHERE is_active = 1");
if($cat_res) {
    while($row = $cat_res->fetch_assoc()) $cats[] = $row;
}

// Enable Error Reporting for Debugging
// Enable Error Reporting for Debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Check for Post Max Size Exceeded
    if (empty($_POST) && $_SERVER['CONTENT_LENGTH'] > 0) {
        $error = "Error: The file is too large. It exceeds the server's post_max_size.";
    } else {
        $title = isset($_POST['title']) ? mysqli_real_escape_string($conn, trim($_POST['title'])) : '';
        $category_id = isset($_POST['category_id']) ? intval($_POST['category_id']) : 0;
        
        // Validation
        if($category_id <= 0 || empty($title)) {
            $error = "Please fill in all required fields (Title and Category).";
        } else {
            $slug = !empty($_POST['slug']) ? $_POST['slug'] : strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
            $slug = mysqli_real_escape_string($conn, $slug);
            
            $description = mysqli_real_escape_string($conn, $_POST['description'] ?? '');
            
            $meta_title = mysqli_real_escape_string($conn, $_POST['meta_title'] ?? '');
            $meta_desc = mysqli_real_escape_string($conn, $_POST['meta_desc'] ?? '');
            $meta_keywords = mysqli_real_escape_string($conn, $_POST['meta_keywords'] ?? '');
            $schema_markup = mysqli_real_escape_string($conn, $_POST['schema_markup'] ?? '');
            $is_active = isset($_POST['is_active']) ? 1 : 0;
            
            // Image Upload
            $featured_image = "";
            if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] == 0) {
                $target_dir = "../../assets/uploads/blogs/";
                if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);
                
                $ext = pathinfo($_FILES['featured_image']['name'], PATHINFO_EXTENSION);
                $filename = "blog_" . time() . "." . $ext;
                
                if (move_uploaded_file($_FILES['featured_image']['tmp_name'], $target_dir . $filename)) {
                    $featured_image = "assets/uploads/blogs/" . $filename;
                }
            }

            try {
                $sql = "INSERT INTO blogs (category_id, title, slug, description, featured_image, meta_title, meta_desc, meta_keywords, schema_markup, is_active) 
                        VALUES ($category_id, '$title', '$slug', '$description', '$featured_image', '$meta_title', '$meta_desc', '$meta_keywords', '$schema_markup', $is_active)";

                if ($conn->query($sql) === TRUE) {
                    $success = "Blog post published successfully!";
                }
            } catch (mysqli_sql_exception $e) {
                $error = "Database Error: " . $e->getMessage();
            } catch (Exception $e) {
                $error = "Error: " . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Blog - MG Skills</title>
    <!-- TinyMCE -->
    <script src="https://cdn.tiny.cloud/1/zzf1ium270xgdjvayd6ocr6p0e7uej8ogum1kdm771lsz41d/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    <style>
        :root{--active:#22c55e;--indigo:#4f46e5;--line:#e2e8f0;--text:#1e293b;--muted:#64748b;--bg:#f8fafc;}
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:'Outfit',system-ui,sans-serif;background:var(--bg);color:var(--text)}
        .admin-content{margin-left:260px;min-height:100vh;padding:30px;transition:margin-left .3s ease}
        body.sidebar-collapsed .admin-content{margin-left:80px}
        
        .page-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:24px}
        .page-title{font-size:24px;font-weight:700;color:var(--text)}
        
        .card { background: white; border-radius: 12px; border: 1px solid var(--line); padding: 30px; max-width: 1000px; margin-bottom: 20px; }
        
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .form-group { margin-bottom: 20px; }
        .form-label { display: block; margin-bottom: 8px; font-weight: 600; font-size: 14px; color: var(--text); }
        .form-control { width: 100%; padding: 10px 12px; border: 1px solid var(--line); border-radius: 8px; font-size: 14px; background: #fff; }
        .form-textarea { width: 100%; padding: 10px 12px; border: 1px solid var(--line); border-radius: 8px; font-size: 14px; background: #fff; resize: vertical; }
        
        .section-title { font-size: 16px; font-weight: 700; color: var(--indigo); border-bottom: 1px solid var(--line); padding-bottom: 10px; margin: 0 0 20px; }
        
        .btn-submit { padding: 14px 28px; background: var(--indigo); color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 16px; width: 100%; }
        
        .preview-img { max-width: 200px; margin-top: 10px; border-radius: 8px; display: none; border: 1px solid var(--line); }
    </style>
</head>
<body class="<?php echo isset($_COOKIE['sidebar_collapsed']) && $_COOKIE['sidebar_collapsed'] == 'true' ? 'sidebar-collapsed' : ''; ?>">

    <?php include '../../admin/sidebar.php'; ?>

    <div class="admin-content">
        <div class="page-header">
            <h1 class="page-title">Write New Blog</h1>
            <a href="index.php" style="text-decoration:none;color:var(--indigo);font-weight:600">View All Blogs</a>
        </div>

        <?php if(isset($success)): ?>
            <div style="background:#dcfce7;color:#166534;padding:15px;border-radius:8px;margin-bottom:20px">✓ <?php echo $success; ?></div>
        <?php endif; ?>
        <?php if(isset($error)): ?>
            <div style="background:#fee2e2;color:#991b1b;padding:15px;border-radius:8px;margin-bottom:20px">⚠ <?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            
            <div class="card">
                <div class="section-title">Content</div>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Blog Title *</label>
                        <input type="text" name="title" id="title" class="form-control" required placeholder="Enter engaging title">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Category *</label>
                        <select name="category_id" class="form-control" required>
                            <option value="">Select Category</option>
                            <?php foreach($cats as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Slug</label>
                    <input type="text" name="slug" id="slug" class="form-control" placeholder="seo-friendly-url">
                </div>

                <div class="form-group">
                    <label class="form-label">Featured Image</label>
                    <input type="file" name="featured_image" class="form-control" accept="image/*" onchange="previewImage(this)">
                    <img id="imgPreview" class="preview-img" src="#" alt="Preview">
                </div>

                <div class="form-group">
                    <label class="form-label">Content *</label>
                    <textarea name="description" id="contentEditor"></textarea>
                </div>
            </div>

            <div class="card">
                <div class="section-title">SEO & Schema</div>
                <div class="form-group">
                    <label class="form-label">Meta Title</label>
                    <input type="text" name="meta_title" class="form-control" placeholder="Optimized Title">
                </div>
                <div class="form-group">
                    <label class="form-label">Meta Description</label>
                    <textarea name="meta_desc" class="form-textarea" rows="2" placeholder="Optimized Description"></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Meta Keywords</label>
                    <input type="text" name="meta_keywords" class="form-control" placeholder="keyword1, keyword2">
                </div>
                <div class="form-group">
                    <label class="form-label">Article Schema (JSON-LD)</label>
                    <textarea name="schema_markup" class="form-textarea" rows="4" placeholder='<script type="application/ld+json">{...}</script>'></textarea>
                </div>
                <div class="form-group">
                    <label style="display:flex;align-items:center;gap:8px;font-weight:600;cursor:pointer">
                        <input type="checkbox" name="is_active" checked> Publish immediately
                    </label>
                </div>
            </div>

            <button type="submit" class="btn-submit">Publish Blog</button>

        </form>
    </div>

    <script>
        // TinyMCE
        tinymce.init({
            selector: '#contentEditor',
            height: 500,
            plugins: 'advlist autolink lists link image charmap preview anchor searchreplace visualblocks code fullscreen insertdatetime media table help wordcount',
            toolbar: 'undo redo | blocks | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | help'
        });

        // Image Preview
        function previewImage(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    var p = document.getElementById('imgPreview');
                    p.src = e.target.result;
                    p.style.display = 'block';
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</body>
</html>
<?php $conn->close(); ?>
