<?php
// Include database configuration
require_once __DIR__ . '/../../database/db-config.php';
$conn = getDbConnection();

// Fetch active categories
$categories = [];
$cat_sql = "SELECT * FROM gallery_categories WHERE is_active = 1 ORDER BY name ASC";
$cat_res = $conn->query($cat_sql);
if ($cat_res) {
    while ($row = $cat_res->fetch_assoc()) {
        $categories[] = $row;
    }
}

// Check for category filter
$selected_category_id = isset($_GET['category']) ? intval($_GET['category']) : 0;
$category_title = "All Photos";

// Prepare Image Query
$sql = "SELECT g.*, c.name as cat_name FROM gallery_images g 
        LEFT JOIN gallery_categories c ON g.category_id = c.id 
        WHERE g.is_active = 1";

if ($selected_category_id > 0) {
    $sql .= " AND g.category_id = $selected_category_id";
    // Find category name for title
    foreach($categories as $cat) {
        if($cat['id'] == $selected_category_id) {
            $category_title = $cat['name'];
            break;
        }
    }
}

$sql .= " ORDER BY g.created_at DESC";
$img_res = $conn->query($sql);
$images = [];
if ($img_res) {
    while ($row = $img_res->fetch_assoc()) {
        $images[] = $row;
    }
}

// Base URL for images
// Assuming images are stored as 'assets/images/gallery/filename.jpg' in DB
// We are in pages/gallery/index.php, so we need to go up 2 levels
$assets_path = "../../"; 

// Page specific meta
$page_title = $category_title . " - Gallery | MG Education";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    
    <!-- Using the same layout/styles as other pages roughly, but with specific gallery styles -->
    <style>
        :root {
            --brand: #1358db; /* Specific to header but good to reuse */
            --primary: #4f46e5;
            --text-main: #0b1020;
            --text-light: #64748b;
            --bg-light: #f8fafc;
        }
        body { margin: 0; font-family: system-ui, -apple-system, sans-serif; background: #fff; color: var(--text-main); }
        
        /* Layout Grid */
        .gallery-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 16px;
            display: grid;
            grid-template-columns: 260px 1fr;
            gap: 40px;
        }
        
        /* Sidebar */
        .gallery-sidebar {
            /* sticky sidebar */
            position: sticky;
            top: 100px; /* Account for fixed header */
            align-self: start;
        }
        .cat-title {
            font-size: 18px;
            font-weight: 800;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e2e8f0;
            color: var(--text-main);
        }
        .cat-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .cat-link {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 16px;
            margin-bottom: 8px;
            border-radius: 12px;
            text-decoration: none;
            color: var(--text-light);
            font-weight: 600;
            transition: all 0.2s ease;
            background: #fff;
            border: 1px solid transparent;
        }
        .cat-link:hover {
            background: #f1f5f9;
            color: var(--brand);
            padding-left: 20px; /* Slight movement effect */
        }
        .cat-link.active {
            background: var(--brand);
            color: #fff;
            box-shadow: 0 4px 12px rgba(19, 88, 219, 0.3);
        }
        .count-badge {
            background: rgba(0,0,0,0.05);
            padding: 2px 8px;
            border-radius: 20px;
            font-size: 12px;
        }
        .cat-link.active .count-badge {
            background: rgba(255,255,255,0.2);
            color: #fff;
        }
        
        /* Main Gallery */
        .gallery-header {
            margin-bottom: 30px;
        }
        .gallery-title {
            font-size: 32px;
            font-weight: 900;
            margin: 0 0 10px 0;
            background: linear-gradient(90deg, #0b1020 0%, #4f46e5 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .gallery-subtitle {
            color: var(--text-light);
            font-size: 16px;
        }
        
        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 24px;
        }
        
        .gallery-item {
            position: relative;
            border-radius: 16px;
            overflow: hidden;
            background: #fff;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
            aspect-ratio: 4/3;
        }
        
        .gallery-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1), 0 8px 10px -6px rgba(0,0,0,0.1);
        }
        
        .g-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        
        .gallery-item:hover .g-img {
            transform: scale(1.1);
        }
        
        .g-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(to top, rgba(0,0,0,0.8), transparent);
            padding: 20px;
            transform: translateY(100%);
            transition: transform 0.3s ease;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
        }
        
        .gallery-item:hover .g-overlay {
            transform: translateY(0);
        }
        
        .g-title {
            color: #fff;
            font-size: 18px;
            font-weight: 700;
            margin: 0;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }
        .g-cat {
            color: rgba(255,255,255,0.8);
            font-size: 13px;
            margin-top: 4px;
            font-weight: 500;
        }

        /* Lightbox (Simple CSS/JS implementation) */
        .lightbox {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.95);
            z-index: 2000;
            display: none;
            justify-content: center;
            align-items: center;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        .lightbox.active {
            display: flex;
            opacity: 1;
        }
        .lightbox-img {
            max-width: 90%;
            max-height: 90vh;
            border-radius: 4px;
            box-shadow: 0 0 20px rgba(0,0,0,0.5);
        }
        .lightbox-close {
            position: absolute;
            top: 20px;
            right: 20px;
            color: #fff;
            font-size: 40px;
            cursor: pointer;
            line-height: 1;
        }
        
        /* Responsive */
        @media (max-width: 900px) {
            .gallery-container {
                grid-template-columns: 1fr;
                gap: 24px;
            }
            .gallery-sidebar {
                position: static;
                display: flex;
                overflow-x: auto;
                padding-bottom: 10px;
                border-bottom: 1px solid #e2e8f0;
                margin-bottom: 10px;
            }
            .cat-list {
                display: flex;
                gap: 10px;
            }
            .cat-link {
                margin: 0;
                white-space: nowrap;
                padding: 8px 16px;
                border: 1px solid #e2e8f0;
            }
            .cat-link:hover {
                padding-left: 16px;
            }
            .cat-title {
                display: none; /* Hide 'Categories' title on mobile to save space */
            }
        }
        
        /* Empty State */
        .empty-state {
            grid-column: 1 / -1;
            text-align: center;
            padding: 60px 20px;
            color: var(--text-light);
            background: #f8fafc;
            border-radius: 16px;
            border: 2px dashed #e2e8f0;
        }
    </style>
</head>
<body>
    
    <!-- Includes Header -->
    <?php require_once __DIR__ . '/../../includes/header.php'; ?>
    
    <div class="gallery-container">
        <!-- Sidebar -->
        <aside class="gallery-sidebar">
            <h3 class="cat-title">Categories</h3>
            <ul class="cat-list">
                <li>
                    <a href="index.php" class="cat-link <?php echo ($selected_category_id == 0) ? 'active' : ''; ?>">
                        <span>All Photos</span>
                        <!-- Optional: Count could be added here if we pre-calculate -->
                    </a>
                </li>
                <?php foreach($categories as $cat): ?>
                <li>
                    <a href="?category=<?php echo $cat['id']; ?>" class="cat-link <?php echo ($selected_category_id == $cat['id']) ? 'active' : ''; ?>">
                        <span><?php echo htmlspecialchars($cat['name']); ?></span>
                    </a>
                </li>
                <?php endforeach; ?>
            </ul>
        </aside>

        <!-- Main Content -->
        <main>
            <div class="gallery-header">
                <h1 class="gallery-title"><?php echo htmlspecialchars($category_title); ?></h1>
                <p class="gallery-subtitle">Explore our latest moments and events.</p>
            </div>

            <div class="gallery-grid">
                <?php if (count($images) > 0): ?>
                    <?php foreach($images as $img): ?>
                        <div class="gallery-item" onclick="openLightbox('<?php echo $assets_path . htmlspecialchars($img['image_path']); ?>')">
                            <img src="<?php echo $assets_path . htmlspecialchars($img['image_path']); ?>" alt="<?php echo htmlspecialchars($img['title']); ?>" class="g-img" loading="lazy">
                            <div class="g-overlay">
                                <h3 class="g-title"><?php echo htmlspecialchars($img['title']); ?></h3>
                                <span class="g-cat"><?php echo htmlspecialchars($img['cat_name'] ?? ''); ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <svg width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="2" style="margin-bottom:15px"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                        <h3>No images found</h3>
                        <p>We haven't added any photos for this category yet.</p>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- Lightbox -->
    <div id="lightbox" class="lightbox" onclick="closeLightbox()">
        <span class="lightbox-close">&times;</span>
        <img id="lightbox-img" class="lightbox-img" src="" alt="Full view">
    </div>

    <!-- Includes Footer -->
    <?php require_once __DIR__ . '/../../includes/footer.php'; ?>

    <script>
        function openLightbox(src) {
            const lightbox = document.getElementById('lightbox');
            const img = document.getElementById('lightbox-img');
            img.src = src;
            lightbox.classList.add('active');
            document.body.style.overflow = 'hidden'; // Prevent scrolling
        }

        function closeLightbox() {
            const lightbox = document.getElementById('lightbox');
            lightbox.classList.remove('active');
            document.body.style.overflow = ''; // Restore scrolling
            setTimeout(() => {
                 document.getElementById('lightbox-img').src = '';
            }, 300);
        }

        // Close on escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === "Escape") {
                closeLightbox();
            }
        });
    </script>
</body>
</html>
<?php $conn->close(); ?>
