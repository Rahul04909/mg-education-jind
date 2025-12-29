<?php
require_once __DIR__ . '/../../database/db-config.php';
$conn = getDbConnection();

// Fetch Categories
$categories = [];
$cat_sql = "SELECT * FROM course_categories WHERE is_active = 1";
$cat_res = $conn->query($cat_sql);
if ($cat_res) {
    while ($row = $cat_res->fetch_assoc()) {
        $categories[] = $row;
    }
}

// Fetch Courses (with Filters)
$where_clauses = ["is_active = 1"];

// Search
if (!empty($_GET['search'])) {
    $search = $conn->real_escape_string($_GET['search']);
    $where_clauses[] = "(title LIKE '%$search%' OR meta_keywords LIKE '%$search%')";
}

// Category Filter
if (!empty($_GET['category'])) {
    $cat_ids = array_map('intval', $_GET['category']);
    $cat_ids_str = implode(',', $cat_ids);
    $where_clauses[] = "category_id IN ($cat_ids_str)";
}

// Price Range Filter
// Note: fees is JSON. Simple filtering might be tricky in pure SQL if JSON structure varies.
// Assuming we filter by base fee or a 'total' field if extracted.
// For MVP, we'll fetch all and filter in PHP or just ignore price filter complexity for now unless we have a generated column.
// Just fetching all active courses for now and we can add price logic if Schema allows.

$where_sql = implode(' AND ', $where_clauses);
$sql = "SELECT * FROM courses WHERE $where_sql ORDER BY id DESC";
$result = $conn->query($sql);
$courses = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        // Decode JSON fields
        $row['fees'] = json_decode($row['fees'], true);
        $courses[] = $row;
    }
}

// Include Header
include __DIR__ . '/../../includes/header.php';
?>

<style>
    :root {
        --primary-color: #6f75ff;
        --secondary-color: #4a5568;
        --bg-color: #f8fafc;
        --card-bg: #ffffff;
        --border-color: #e2e8f0;
    }

    body {
        background-color: var(--bg-color);
        font-family: 'Outfit', sans-serif;
    }

    .page-wrapper {
        display: flex;
        gap: 30px;
        max-width: 1280px;
        margin: 40px auto;
        padding: 0 20px;
    }

    /* Sidebar */
    .sidebar {
        width: 300px;
        flex-shrink: 0;
    }

    .filter-card {
        background: var(--card-bg);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 24px;
        margin-bottom: 24px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    }

    .filter-title {
        font-size: 18px;
        font-weight: 700;
        margin-bottom: 16px;
        color: #1a202c;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .checkbox-group {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .custom-checkbox {
        display: flex;
        align-items: center;
        gap: 10px;
        cursor: pointer;
        font-size: 15px;
        color: #4a5568;
    }

    .custom-checkbox input {
        width: 18px;
        height: 18px;
        accent-color: var(--primary-color);
    }

    /* Range Slider Styling (Simple) */
    .range-slider {
        width: 100%;
        margin: 10px 0;
    }
    
    /* Enquiry Form */
    .enquiry-form input, .enquiry-form textarea {
        width: 100%;
        padding: 12px;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        margin-bottom: 12px;
        font-family: inherit;
    }
    .enquiry-btn {
        width: 100%;
        padding: 12px;
        background: var(--primary-color);
        color: white;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.2s;
    }
    .enquiry-btn:hover {
        background: #5a60d6;
    }

    /* Main Content */
    .main-content {
        flex: 1;
    }

    .top-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
        background: white;
        padding: 15px 24px;
        border-radius: 12px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }

    .search-box {
        position: relative;
        flex: 1;
        max-width: 400px;
    }
    .search-input {
        width: 100%;
        padding: 10px 16px 10px 40px;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        font-size: 14px;
    }
    .search-icon {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #a0aec0;
    }

    .view-toggles {
        display: flex;
        gap: 10px;
    }
    .view-btn {
        padding: 8px;
        border: 1px solid var(--border-color);
        border-radius: 6px;
        background: white;
        cursor: pointer;
        color: #718096;
    }
    .view-btn.active {
        background: var(--primary-color);
        color: white;
        border-color: var(--primary-color);
    }

    /* Course Grid */
    .course-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 24px;
    }

    .course-list-view {
        display: flex;
        flex-direction: column;
        gap: 24px;
    }

    /* Course Card */
    .course-card {
        background: white;
        border-radius: 16px;
        overflow: hidden;
        border: 1px solid var(--border-color);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        position: relative;
        display: flex;
        flex-direction: column;
    }

    .course-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 30px rgba(0,0,0,0.1);
    }

    .card-thumb {
        height: 200px;
        background: #f1f5f9;
        overflow: hidden;
        position: relative;
    }
    
    .card-thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }

    .course-card:hover .card-thumb img {
        transform: scale(1.05);
    }

    .card-body {
        padding: 20px;
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    .course-badge {
        display: inline-block;
        padding: 4px 12px;
        background: #e0e7ff;
        color: var(--primary-color);
        border-radius: 50px;
        font-size: 12px;
        font-weight: 600;
        margin-bottom: 12px;
        align-self: flex-start;
    }

    .course-title {
        font-size: 18px;
        font-weight: 700;
        color: #1a202c;
        margin-bottom: 10px;
        line-height: 1.4;
    }

    .course-meta {
        display: flex;
        align-items: center;
        gap: 15px;
        font-size: 13px;
        color: #718096;
        margin-bottom: 20px;
    }

    .meta-item {
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .course-footer {
        margin-top: auto;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding-top: 15px;
        border-top: 1px solid #f1f5f9;
    }

    .price {
        font-size: 20px;
        font-weight: 800;
        color: var(--primary-color);
    }

    .view-btn-link {
        padding: 8px 20px;
        background: #f8fafc;
        color: #4a5568;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 600;
        font-size: 14px;
        transition: all 0.2s;
    }
    .view-btn-link:hover {
        background: var(--primary-color);
        color: white;
    }

    /* List View Overrides */
    .course-list-view .course-card {
        flex-direction: row;
        height: 220px;
    }
    .course-list-view .card-thumb {
        width: 300px;
        height: 100%;
    }
    .course-list-view .card-body {
        padding: 24px;
    }

    @media (max-width: 992px) {
        .page-wrapper {
            flex-direction: column;
        }
        .sidebar {
            width: 100%;
        }
        .course-list-view .course-card {
            flex-direction: column;
            height: auto;
        }
        .course-list-view .card-thumb {
            width: 100%;
            height: 200px;
        }
    }
</style>

<div class="page-wrapper">
    <!-- Sidebar -->
    <aside class="sidebar">
        <!-- Search (Mobile) -->
        
        <!-- Filter Form -->
        <form id="filter-form" method="GET">
            <div class="filter-card">
                <h3 class="filter-title">
                    <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/></svg>
                    Categories
                </h3>
                <div class="checkbox-group">
                    <?php 
                    $selected_cats = isset($_GET['category']) ? $_GET['category'] : [];
                    foreach($categories as $cat): 
                    ?>
                    <label class="custom-checkbox">
                        <input type="checkbox" name="category[]" value="<?php echo $cat['id']; ?>" 
                            <?php echo in_array($cat['id'], $selected_cats) ? 'checked' : ''; ?> 
                            onchange="this.form.submit()">
                        <span><?php echo htmlspecialchars($cat['name']); ?></span>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="filter-card">
                <h3 class="filter-title">
                    <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Price Range
                </h3>
                <input type="range" class="range-slider" min="0" max="50000" step="1000">
                <div style="display:flex; justify-content:space-between; font-size:14px; color:#64748b; margin-top:5px;">
                    <span>₹0</span>
                    <span>₹50k+</span>
                </div>
            </div>
        </form>

        <!-- Enquiry Form -->
        <div class="filter-card">
            <h3 class="filter-title" style="color:var(--primary-color)">
                <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                Quick Enquiry
            </h3>
            <form class="enquiry-form">
                <input type="text" placeholder="Your Name" required>
                <input type="tel" placeholder="Mobile Number" required>
                <textarea rows="3" placeholder="Message / Course Interest"></textarea>
                <button type="submit" class="enquiry-btn">Send Message</button>
            </form>
        </div>
    </aside>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Bar -->
        <div class="top-bar">
            <div class="search-box">
                <svg class="search-icon" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" class="search-input" placeholder="Search for courses..." form="filter-form" name="search" value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
            </div>
            
            <div class="view-toggles">
                <button class="view-btn active" id="grid-view" title="Grid View">
                    <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                </button>
                <button class="view-btn" id="list-view" title="List View">
                    <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
            </div>
        </div>

        <!-- Course Listings -->
        <div id="course-container" class="course-grid">
            <?php if (count($courses) > 0): ?>
                <?php foreach($courses as $course): 
                    // Calculate Price (Basic logic since it's JSON)
                    $price = isset($course['fees']['total_fee']) ? '₹' . number_format($course['fees']['total_fee']) : 'Fees Apply';
                    // Category Name mapping (simplified, ideal to join table)
                    $cat_name = "Course";
                    foreach($categories as $c) { if($c['id'] == $course['category_id']) { $cat_name = $c['name']; break; } }
                ?>
                <div class="course-card">
                    <div class="card-thumb">
                        <img src="../../<?php echo !empty($course['featured_image']) ? $course['featured_image'] : 'assets/images/placeholder-course.jpg'; ?>" 
                             alt="<?php echo htmlspecialchars($course['title']); ?>"
                             onerror="this.src='https://via.placeholder.com/400x300?text=No+Image'">
                    </div>
                    <div class="card-body">
                        <span class="course-badge"><?php echo htmlspecialchars($cat_name); ?></span>
                        <h3 class="course-title"><?php echo htmlspecialchars($course['title']); ?></h3>
                        
                        <div class="course-meta">
                            <div class="meta-item">
                                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <?php echo $course['duration_value'] . ' ' . $course['duration_type']; ?>
                            </div>
                            <!-- Add more meta if needed -->
                        </div>

                        <p style="font-size:14px; color:#718096; margin-bottom:20px; line-height:1.6; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden;">
                            <?php echo strip_tags($course['description']); ?>
                        </p>

                        <div class="course-footer">
                            <span class="price"><?php echo $price; ?></span>
                            <a href="../../course-details.php?id=<?php echo $course['id']; ?>" class="view-btn-link">View Details</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="grid-column: 1/-1; text-align: center; padding: 50px; background: white; border-radius: 12px;">
                    <h3 style="color:#4a5568">No courses found matching your criteria.</h3>
                    <a href="index.php" style="color:var(--primary-color); text-decoration:none; font-weight:600; margin-top:10px; display:inline-block">Clear Filters</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    const gridBtn = document.getElementById('grid-view');
    const listBtn = document.getElementById('list-view');
    const container = document.getElementById('course-container');

    gridBtn.addEventListener('click', () => {
        container.classList.remove('course-list-view');
        container.classList.add('course-grid');
        gridBtn.classList.add('active');
        listBtn.classList.remove('active');
    });

    listBtn.addEventListener('click', () => {
        container.classList.remove('course-grid');
        container.classList.add('course-list-view');
        listBtn.classList.add('active');
        gridBtn.classList.remove('active');
    });
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
