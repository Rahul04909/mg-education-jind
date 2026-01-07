<?php
require_once __DIR__ . '/../../database/db-config.php';
$conn = getDbConnection();

// Enable Error Reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Get Blog ID
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    die("Invalid Blog ID");
}

// Fetch Blog Data
$sql = "SELECT * FROM blogs WHERE id = $id";
$result = $conn->query($sql);
if ($result->num_rows == 0) {
    die("Blog not found");
}
$blog = $result->fetch_assoc();

// Fetch Categories
$cats = [];
$cat_res = $conn->query("SELECT id, name FROM blog_categories WHERE is_active = 1");
if($cat_res) {
    while($row = $cat_res->fetch_assoc()) $cats[] = $row;
}

// Handle Form Submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Check for Post Max Size Exceeded
    if (empty($_POST) && $_SERVER['CONTENT_LENGTH'] > 0) {
        $error = "Error: The file is too large. It exceeds the server's post_max_size.";
    } else {
        $title = isset($_POST['title']) ? mysqli_real_escape_string($conn, trim($_POST['title'])) : '';
        $category_id = isset($_POST['category_id']) ? intval($_POST['category_id']) : 0;
        
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
            $featured_image = $blog['featured_image']; // Default to existing
            if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] == 0) {
                $target_dir = "../../assets/uploads/blogs/";
                if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);
                
                $ext = pathinfo($_FILES['featured_image']['name'], PATHINFO_EXTENSION);
                $filename = "blog_" . time() . "." . $ext;
                
                if (move_uploaded_file($_FILES['featured_image']['tmp_name'], $target_dir . $filename)) {
                    $featured_image = "assets/uploads/blogs/" . $filename;
                    // Optional: Delete old image if exists
                }
            }

            try {
                $update_sql = "UPDATE blogs SET 
                    category_id = $category_id,
                    title = '$title',
                    slug = '$slug',
                    description = '$description',
                    featured_image = '$featured_image',
                    meta_title = '$meta_title',
                    meta_desc = '$meta_desc',
                    meta_keywords = '$meta_keywords',
                    schema_markup = '$schema_markup',
                    is_active = $is_active,
                    updated_at = NOW()
                    WHERE id = $id";

                if ($conn->query($update_sql) === TRUE) {
                    $success = "Blog updated successfully!";
                    // Refresh data
                    $result = $conn->query($sql);
                    $blog = $result->fetch_assoc();
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
    <title>Edit Blog - MG Skills</title>
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
        
        .preview-img { max-width: 200px; margin-top: 10px; border-radius: 8px; border: 1px solid var(--line); }
    </style>
</head>
<body class="<?php echo isset($_COOKIE['sidebar_collapsed']) && $_COOKIE['sidebar_collapsed'] == 'true' ? 'sidebar-collapsed' : ''; ?>">

    <?php include '../../admin/sidebar.php'; ?>

    <div class="admin-content">
        <div class="page-header">
            <h1 class="page-title">Edit Blog Post</h1>
            <a href="index.php" style="text-decoration:none;color:var(--indigo);font-weight:600">Back to List</a>
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
                        <input type="text" name="title" class="form-control" required value="<?php echo htmlspecialchars($blog['title']); ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Category *</label>
                        <select name="category_id" class="form-control" required>
                            <option value="">Select Category</option>
                            <?php foreach($cats as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>" <?php echo $cat['id'] == $blog['category_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Slug</label>
                    <input type="text" name="slug" class="form-control" value="<?php echo htmlspecialchars($blog['slug']); ?>">
                </div>

                <div class="form-group">
                    <label class="form-label">Featured Image</label>
                    <input type="file" name="featured_image" class="form-control" accept="image/*" onchange="previewImage(this)">
                    <?php if(!empty($blog['featured_image'])): ?>
                        <div style="margin-top:10px">
                            <small class="form-label">Current Image:</small>
                            <img src="../../<?php echo htmlspecialchars($blog['featured_image']); ?>" class="preview-img" style="display:block">
                        </div>
                    <?php endif; ?>
                    <img id="imgPreview" class="preview-img" src="#" alt="New Preview" style="display:none;margin-top:10px">
                </div>

                <div class="form-group">
                    <label class="form-label">Content *</label>
                    <textarea name="description" id="contentEditor"><?php echo htmlspecialchars($blog['description']); ?></textarea>
                </div>
            </div>

            <div class="card">
                <div class="section-title">SEO & Schema</div>
                <div class="form-group">
                    <label class="form-label">Meta Title</label>
                    <input type="text" name="meta_title" class="form-control" value="<?php echo htmlspecialchars($blog['meta_title']); ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Meta Description</label>
                    <textarea name="meta_desc" class="form-textarea" rows="2"><?php echo htmlspecialchars($blog['meta_desc']); ?></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Meta Keywords</label>
                    <input type="text" name="meta_keywords" class="form-control" value="<?php echo htmlspecialchars($blog['meta_keywords']); ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Article Schema (JSON-LD)</label>
                    <textarea name="schema_markup" class="form-textarea" rows="4"><?php echo htmlspecialchars($blog['schema_markup']); ?></textarea>
                </div>
                <div class="form-group">
                    <label style="display:flex;align-items:center;gap:8px;font-weight:600;cursor:pointer">
                        <input type="checkbox" name="is_active" <?php echo $blog['is_active'] ? 'checked' : ''; ?>> Published
                    </label>
                </div>
            </div>

            <button type="submit" class="btn-submit">Update Blog</button>

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
