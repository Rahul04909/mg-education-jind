<?php
// Include database configuration
require_once __DIR__ . '/../../database/db-config.php';

// Create database connection
$conn = getDbConnection();

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$course_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$course = null;

// Fetch Course Data
if ($course_id > 0) {
    $sql = "SELECT * FROM courses WHERE id = $course_id";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $course = $result->fetch_assoc();
    } else {
        die("Course not found.");
    }
} else {
    die("Invalid Course ID.");
}

// Fetch Categories
$cat_sql = "SELECT id, name FROM course_categories WHERE is_active = 1 ORDER BY name ASC";
$cat_result = $conn->query($cat_sql);
$categories = [];
if ($cat_result->num_rows > 0) {
    while($row = $cat_result->fetch_assoc()) {
        $categories[] = $row;
    }
}

$success_message = "";
$error_message = "";

// Handle Form Submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Basic Info
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $category_id = intval($_POST['category_id']);
    $video_url = mysqli_real_escape_string($conn, $_POST['video_url']);
    
    // Slug
    $slug = !empty($_POST['slug']) ? $_POST['slug'] : strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
    $slug = mysqli_real_escape_string($conn, $slug);

    // Details
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    
    // Labels (JSON)
    $labels_input = isset($_POST['labels']) ? $_POST['labels'] : '';
    $labels_array = array_map('trim', explode(',', $labels_input));
    $labels_array = array_filter($labels_array); // Remove empty
    $labels_json = mysqli_real_escape_string($conn, json_encode($labels_array));

    // Features (JSON)
    $features_input = isset($_POST['features']) ? $_POST['features'] : [];
    $features_input = array_filter($features_input); // Remove empty
    $features_json = mysqli_real_escape_string($conn, json_encode(array_values($features_input)));

    // Fees (JSON)
    $fee_amount = isset($_POST['fee_amount']) ? $_POST['fee_amount'] : '';
    $fee_text = isset($_POST['fee_text']) ? $_POST['fee_text'] : '';
    $fees_data = [
        'amount' => $fee_amount,
        'description' => $fee_text
    ];
    $fees_json = mysqli_real_escape_string($conn, json_encode($fees_data));
    $base_currency = 'INR';

    // SEO
    $meta_title = mysqli_real_escape_string($conn, $_POST['meta_title']);
    $meta_desc = mysqli_real_escape_string($conn, $_POST['meta_desc']);
    $meta_keywords = mysqli_real_escape_string($conn, $_POST['meta_keywords']);
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    // Image Upload
    $featured_image = $course['featured_image']; // Default to existing
    if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] == 0) {
        $target_dir = "../../assets/uploads/courses/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $file_extension = pathinfo($_FILES["featured_image"]["name"], PATHINFO_EXTENSION);
        $new_filename = "course_" . time() . "." . $file_extension;
        $target_file = $target_dir . $new_filename;
        
        if (move_uploaded_file($_FILES["featured_image"]["tmp_name"], $target_file)) {
            $featured_image = "assets/uploads/courses/" . $new_filename;
        } else {
            $error_message = "Error uploading image.";
        }
    }

    if (empty($error_message)) {
        $sql = "UPDATE courses SET 
            category_id = $category_id,
            title = '$title',
            slug = '$slug',
            video_url = '$video_url',
            description = '$description',
            labels = '$labels_json',
            features = '$features_json',
            fees = '$fees_json',
            base_currency = '$base_currency',
            featured_image = '$featured_image',
            meta_title = '$meta_title',
            meta_desc = '$meta_desc',
            meta_keywords = '$meta_keywords',
            is_active = $is_active,
            updated_at = NOW()
            WHERE id = $course_id";

        if ($conn->query($sql) === TRUE) {
            $success_message = "Course updated successfully!";
            // Refresh course data
            $course_result = $conn->query("SELECT * FROM courses WHERE id = $course_id");
            $course = $course_result->fetch_assoc();
        } else {
            $error_message = "Database Error: " . $conn->error;
        }
    }
}

// Decode JSON data for display
$labels = json_decode($course['labels'], true) ?? [];
$features = json_decode($course['features'], true) ?? [];
$fees = json_decode($course['fees'], true) ?? ['amount' => '', 'description' => ''];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Course - MG Education</title>
    
    <!-- TinyMCE JS (Cloud) -->
    <script src="https://cdn.tiny.cloud/1/zzf1ium270xgdjvayd6ocr6p0e7uej8ogum1kdm771lsz41d/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    
    <style>
        :root{--active:#22c55e;--indigo:#6f75ff;--line:#e6e8ee;--text:#0b1020;--muted:#6f7787;--error:#ef4444;--success:#22c55e;--info:#3b82f6}
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;background:linear-gradient(180deg,#f8fafc 0%,#ffffff 100%);color:var(--text)}
        .admin-content{margin-left:260px;min-height:100vh;padding:20px;transition:margin-left .25s ease}
        body.sidebar-collapsed .admin-content{margin-left:88px}
        .admin-wrap{max-width:1200px;margin:0 auto}
        .page-header{margin-bottom:30px}
        .page-title{font-size:32px;font-weight:800;color:var(--text);margin-bottom:8px;display:flex;align-items:center;gap:12px}
        .page-subtitle{color:var(--muted);font-size:15px}
        .breadcrumb{color:var(--muted);margin-bottom:10px;font-size:14px}
        .breadcrumb a{color:var(--indigo);text-decoration:none}
        .alert{padding:16px 20px;border-radius:12px;margin-bottom:20px;display:flex;align-items:center;gap:12px;border:1px solid;animation:slideDown .3s ease}
        .alert-success{background:#d1fae5;border-color:#86efac;color:#065f46}
        .alert-error{background:#fee2e2;border-color:#fca5a5;color:#991b1b}
        .card{background:#fff;border:1px solid var(--line);border-radius:18px;padding:30px;box-shadow:0 4px 12px rgba(0,0,0,.05);margin-bottom: 20px;}
        .form-group{margin-bottom:20px}
        .form-label{display:block;font-weight:700;color:var(--text);margin-bottom:8px;font-size:14px}
        .form-input, .form-textarea, .form-select{width:100%;padding:12px 16px;border:1px solid var(--line);border-radius:10px;font-size:15px;transition:all .2s ease;font-family:inherit;background:#fff}
        .form-input:focus, .form-textarea:focus, .form-select:focus{outline:none;border-color:var(--indigo);box-shadow:0 0 0 3px rgba(111,117,255,.1)}
        .form-help{font-size:13px;color:var(--muted);margin-top:6px}
        .btn{display:inline-flex;align-items:center;gap:8px;padding:12px 24px;border-radius:12px;font-weight:700;font-size:15px;border:none;cursor:pointer;transition:all .2s ease;text-decoration:none}
        .btn-primary{background:linear-gradient(135deg,var(--indigo) 0%,#5a5fff 100%);color:#fff}
        .btn-outline{background:#fff;color:var(--indigo);border:2px solid var(--indigo)}
        .grid-2{display:grid;grid-template-columns:1fr 1fr;gap:20px}
        .grid-3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:20px}
        .dynamic-list { margin-top: 10px; }
        .dynamic-item { display: flex; gap: 10px; margin-bottom: 10px; }
        .dynamic-item button { padding: 10px; background: #fee2e2; color: #ef4444; border: 1px solid #fca5a5; border-radius: 8px; cursor: pointer; }
        .section-title { font-size: 18px; font-weight: 700; margin-bottom: 20px; border-bottom: 1px solid var(--line); padding-bottom: 10px; }
        .preview-image{max-width:200px;margin-top:10px;border-radius:8px;display:block}
    </style>
</head>
<body>
    <?php include __DIR__ . "/../sidebar.php"; ?>
    
    <main class="admin-content">
        <div class="admin-wrap">
            <div class="page-header">
                <div class="breadcrumb">
                    <a href="../index.php">Dashboard</a> › <a href="index.php">Courses</a> › Edit Course
                </div>
                <h1 class="page-title">Edit Course</h1>
                <p class="page-subtitle">Update course details and curriculum</p>
            </div>

            <?php if (!empty($success_message)): ?>
            <div class="alert alert-success"><span>✓ <?php echo $success_message; ?></span></div>
            <?php endif; ?>

            <?php if (!empty($error_message)): ?>
            <div class="alert alert-error"><span>⚠ <?php echo $error_message; ?></span></div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                
                <!-- Basic Info -->
                <div class="card">
                    <h3 class="section-title">Course Information</h3>
                    <div class="grid-2">
                        <div class="form-group">
                            <label class="form-label">Course Title *</label>
                            <input type="text" name="title" id="title" class="form-input" required value="<?php echo htmlspecialchars($course['title']); ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Category *</label>
                            <select name="category_id" class="form-select" required>
                                <option value="">Select Category</option>
                                <?php foreach($categories as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>" <?php echo ($cat['id'] == $course['category_id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cat['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="grid-2">
                        <div class="form-group">
                            <label class="form-label">Slug (Optional)</label>
                            <input type="text" name="slug" id="slug" class="form-input" value="<?php echo htmlspecialchars($course['slug']); ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Video URL</label>
                            <input type="url" name="video_url" class="form-input" value="<?php echo htmlspecialchars($course['video_url']); ?>">
                        </div>
                    </div>
                </div>

                <!-- Description (TinyMCE) -->
                <div class="card">
                    <h3 class="section-title">Description & Curriculum</h3>
                    <div class="form-group">
                        <label class="form-label">Course Details</label>
                        <textarea name="description" id="description" rows="10"><?php echo htmlspecialchars($course['description']); ?></textarea>
                    </div>
                </div>

                <!-- Features & Labels -->
                <div class="card">
                    <h3 class="section-title">Features & Highlights</h3>
                    
                    <div class="form-group">
                        <label class="form-label">Labels / Badges</label>
                        <input type="text" name="labels" class="form-input" value="<?php echo htmlspecialchars(implode(', ', $labels)); ?>" placeholder="e.g., 100% Offline Classes, Class 12, New Batch">
                        <div class="form-help">Separate multiple labels with commas.</div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Course Features</label>
                        <div id="features-list" class="dynamic-list">
                            <?php foreach ($features as $feature): ?>
                            <div class="dynamic-item">
                                <input type="text" name="features[]" class="form-input" value="<?php echo htmlspecialchars($feature); ?>">
                                <button type="button" onclick="this.parentElement.remove()">×</button>
                            </div>
                            <?php endforeach; ?>
                            <?php if(empty($features)): ?>
                            <div class="dynamic-item">
                                <input type="text" name="features[]" class="form-input" placeholder="e.g., Learn from India's Top Teachers">
                            </div>
                            <?php endif; ?>
                        </div>
                        <button type="button" class="btn btn-outline" onclick="addFeature()" style="margin-top: 10px; font-size: 13px; padding: 8px 16px;">+ Add Another Feature</button>
                    </div>
                </div>

                <!-- Fees & Media -->
                <div class="card">
                    <h3 class="section-title">Fees & Media</h3>
                    <div class="grid-2">
                        <div class="form-group">
                            <label class="form-label">Fee Amount (INR)</label>
                            <input type="number" name="fee_amount" class="form-input" value="<?php echo htmlspecialchars($fees['amount']); ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Featured Image</label>
                            <input type="file" name="featured_image" class="form-input" accept="image/*" onchange="previewImage(this)">
                            <?php if(!empty($course['featured_image'])): ?>
                                <img id="image-preview" class="preview-image" src="../../<?php echo htmlspecialchars($course['featured_image']); ?>" alt="Current Image">
                            <?php else: ?>
                                <img id="image-preview" class="preview-image" src="#" alt="Preview" style="display:none">
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Fee Details / Note</label>
                        <input type="text" name="fee_text" class="form-input" value="<?php echo htmlspecialchars($fees['description']); ?>">
                    </div>
                </div>

                <!-- SEO -->
                <div class="card">
                    <h3 class="section-title">SEO Configuration</h3>
                    <div class="form-group">
                        <label class="form-label">Meta Title</label>
                        <input type="text" name="meta_title" class="form-input" value="<?php echo htmlspecialchars($course['meta_title']); ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Meta Description</label>
                        <textarea name="meta_desc" class="form-textarea" rows="2"><?php echo htmlspecialchars($course['meta_desc']); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Meta Keywords</label>
                        <input type="text" name="meta_keywords" class="form-input" value="<?php echo htmlspecialchars($course['meta_keywords']); ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">
                            <input type="checkbox" name="is_active" <?php echo ($course['is_active']) ? 'checked' : ''; ?>> Active
                        </label>
                    </div>
                </div>

                <div style="margin-bottom: 50px;">
                    <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 16px; font-size: 16px;">Update Course</button>
                </div>

            </form>
        </div>
    </main>

    <script>
        // Init TinyMCE
        document.addEventListener("DOMContentLoaded", function() {
            if (typeof tinymce !== 'undefined') {
                tinymce.init({
                    selector: '#description',
                    height: 300,
                    plugins: 'advlist autolink lists link image charmap preview anchor searchreplace visualblocks code fullscreen insertdatetime media table help wordcount',
                    toolbar: 'undo redo | blocks | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | help'
                });
            } else {
                console.error("TinyMCE script not loaded.");
            }
        });

        // Slug Generation (only if empty)
        document.getElementById('title').addEventListener('input', function() {
            // Only auto-generate if user hasn't manually edited the slug
            // For edit mode, we generally want to leave the slug alone unless explicitly changed
        });

        // Feature Management
        function addFeature() {
            const container = document.getElementById('features-list');
            const div = document.createElement('div');
            div.className = 'dynamic-item';
            div.innerHTML = `
                <input type="text" name="features[]" class="form-input" placeholder="Feature highlight">
                <button type="button" onclick="this.parentElement.remove()">×</button>
            `;
            container.appendChild(div);
        }

        // Image Preview
        function previewImage(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    var preview = document.getElementById('image-preview');
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</body>
</html>
<?php $conn->close(); ?>
