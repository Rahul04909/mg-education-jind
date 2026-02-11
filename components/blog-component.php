<?php
require_once __DIR__ . '/../database/db-config.php';
$conn = getDbConnection();

// Fetch Latest 10 Active Blogs
$sql = "SELECT * FROM blogs WHERE is_active = 1 ORDER BY created_at DESC LIMIT 10";
$result = $conn->query($sql);
?>
<style>
.blogs-section{background:#f8fafc;padding:60px 0;}
.blogs-wrap{max-width:1200px;margin:0 auto;padding:0 16px}
.blogs-head{text-align:center;margin-bottom:40px}
.blogs-title{margin:0;color:#0f172a;font-size:32px;font-weight:800;letter-spacing:-0.03em}
.blogs-subtitle{font-size:16px;color:#64748b;margin-top:10px;font-weight:500;}
.accent-text{color:#1358db;}

/* Responsive Grid */
.blogs-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 30px;
}

/* Card Design */
.blog-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    height: 100%; /* Equal height */
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
}

.blog-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
    border-color: #cbd5e1;
}

/* 16:9 Image with Zoom Effect */
.blog-img-wrap {
    position: relative;
    width: 100%;
    aspect-ratio: 16/9;
    overflow: hidden;
    border-radius: 16px 16px 0 0; /* Top corners rounded */
}

.blog-img-wrap img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center;
    transition: transform 0.5s ease;
}

/* Hover Zoom */
.blog-card:hover .blog-img-wrap img {
    transform: scale(1.08);
}

/* Gradient Overlay at bottom of image */
.blog-img-overlay {
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    height: 40%;
    background: linear-gradient(to top, rgba(0,0,0,0.4), transparent);
    pointer-events: none;
    opacity: 0.6;
}

/* Card Content */
.blog-body {
    padding: 24px;
    display: flex;
    flex-direction: column;
    flex: 1;
}

.blog-meta {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 12px;
    font-size: 13px;
    font-weight: 600;
    color: #b2560a; /* Accent Color */
}

.meta-date {
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.blog-heading {
    margin: 0 0 12px 0;
    color: #0f172a;
    font-size: 18px;
    font-weight: 700;
    line-height: 1.4;
    display: -webkit-box;
    -webkit-line-clamp: 2; /* Limit to 2 lines */
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.blog-card:hover .blog-heading {
    color: #1358db; /* Brand Color on Hover */
}

.blog-desc {
    margin: 0;
    color: #475569;
    font-size: 15px;
    line-height: 1.6;
    display: -webkit-box;
    -webkit-line-clamp: 3; /* Limit to 3 lines */
    -webkit-box-orient: vertical;
    overflow: hidden;
    margin-bottom: 20px;
}

.blog-footer {
    margin-top: auto;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-top: 1px solid #f1f5f9;
    padding-top: 16px;
}

.read-more-link {
    color: #1358db;
    font-weight: 600;
    font-size: 14px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    transition: gap 0.2s;
}

.read-more-link:hover {
    gap: 8px;
    text-decoration: underline;
}

/* Mobile Responsiveness */
@media (max-width: 768px) {
    .blogs-grid {
        grid-template-columns: 1fr; /* Stack on mobile */
        gap: 20px;
    }
    .blog-card {
        max-width: 100%;
        min-height: auto;
    }
}
</style>

<section class="blogs-section" aria-label="Latest Blogs">
    <div class="blogs-wrap">
        <div class="blogs-head">
            <h3 class="blogs-title">Latest <span class="accent-text">Insights</span></h3>
            <p class="blogs-subtitle">Stay updated with our newest articles and announcements.</p>
        </div>

        <div class="blogs-grid">
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <?php 
                        $title = htmlspecialchars($row['title']);
                        $date = date("M d, Y", strtotime($row['created_at']));
                        // Clean description
                        $clean_desc = strip_tags(html_entity_decode($row['description']));
                        // Fallback image
                        $img = !empty($row['featured_image']) ? $row['featured_image'] : 'https://placehold.co/600x400/f1f5f9/64748b?text=MG+Skills';
                        $link = "blog-details.php?slug=" . urlencode($row['slug']);
                        
                        // Category (mock if not exists)
                        $category = "Education"; // Fallback
                        if(isset($row['category_name'])) {
                            $category = htmlspecialchars($row['category_name']);
                        }
                    ?>
                    <article class="blog-card">
                        <a href="<?php echo $link; ?>" class="blog-img-wrap" aria-label="<?php echo $title; ?>">
                            <img src="<?php echo htmlspecialchars($img); ?>" alt="<?php echo $title; ?>" loading="lazy">
                            <div class="blog-img-overlay"></div>
                        </a>
                        <div class="blog-body">
                            <div class="blog-meta">
                                <span class="meta-date"><?php echo $date; ?></span>
                            </div>
                            <h4 class="blog-heading">
                                <a href="<?php echo $link; ?>" style="color:inherit;text-decoration:none"><?php echo $title; ?></a>
                            </h4>
                            <p class="blog-desc"><?php echo $clean_desc; ?></p>
                            
                            <div class="blog-footer">
                                <a href="<?php echo $link; ?>" class="read-more-link">
                                    Read Article <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                                </a>
                            </div>
                        </div>
                    </article>
                <?php endwhile; ?>
            <?php else: ?>
                <div style="grid-column: 1/-1; text-align: center; padding: 40px; color: #64748b;">
                    <p>No recent updates available.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
