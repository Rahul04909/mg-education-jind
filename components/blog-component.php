<?php
require_once __DIR__ . '/../database/db-config.php';
$conn = getDbConnection();

// Fetch Latest 10 Active Blogs
$sql = "SELECT * FROM blogs WHERE is_active = 1 ORDER BY created_at DESC LIMIT 10";
$result = $conn->query($sql);
?>
<style>
.blogs-section{background:#ffffff;padding:40px 0;background-image:url('assets/images/backgronds/8292829.jpg');background-size:cover;background-position:center;background-repeat:no-repeat;}
.blogs-wrap{max-width:1200px;margin:0 auto;padding:0 16px}
.blogs-head{display:flex;align-items:flex-end;justify-content:space-between;gap:10px;margin-bottom:24px}
.blogs-title{margin:0;color:#0b1020;line-height:1.2;font-size:28px;font-weight:800}
.blogs-subtitle{font-size:16px;color:#64748b;margin-top:8px;font-weight:500;}
.accent-text{color:#3b82f6;}

.blogs-shell{position:relative;overflow:hidden}
.blogs-track{display:flex;gap:20px;transition:transform 0.5s ease-in-out;}
.blog-card{
    flex:0 0 300px; /* Fixed width for equal cards */
    background:#fff;
    border:1px solid #e2e8f0;
    border-radius:16px;
    overflow:hidden;
    display:flex;
    flex-direction:column;
    position:relative;
    transition:all 0.3s ease;
    height: 400px; /* Fixed height for uniformity */
}
.blog-card:hover{transform:translateY(-5px);box-shadow:0 10px 25px -5px rgba(0,0,0,0.1), 0 8px 10px -6px rgba(0,0,0,0.1)}

.blog-img{height:180px;width:100%;overflow:hidden;background:#f1f5f9;position:relative}
.blog-img img{width:100%;height:100%;object-fit:cover;transition:transform 0.5s ease}
.blog-card:hover .blog-img img{transform:scale(1.05)}

.blog-body{padding:20px;display:flex;flex-direction:column;flex:1;position:relative}
.blog-date{font-size:12px;color:#64748b;font-weight:600;margin-bottom:8px;text-transform:uppercase;letter-spacing:0.5px}

/* Truncation Logic */
.blog-heading{
    margin:0 0 10px 0;
    color:#1e293b;
    font-size:18px;
    font-weight:700;
    line-height:1.4;
    display:-webkit-box;
    -webkit-line-clamp:2; /* Limit to 2 lines */
    -webkit-box-orient:vertical;
    overflow:hidden;
}

.blog-desc{
    margin:0;
    color:#64748b;
    font-size:14px;
    line-height:1.6;
    display:-webkit-box;
    -webkit-line-clamp:3; /* Limit to 3 lines */
    -webkit-box-orient:vertical;
    overflow:hidden;
    flex:1; /* Pushes content ensuring footer aligns if needed */
}

/* Read More Button - Hidden by default, appears on hover */
.read-more-overlay{
    position:absolute;
    bottom:0;
    left:0;
    width:100%;
    padding:20px;
    background:linear-gradient(to top, #fff 80%, rgba(255,255,255,0));
    transform:translateY(100%);
    transition:transform 0.3s ease;
    display:flex;
    justify-content:center;
    align-items:flex-end;
}
.blog-card:hover .read-more-overlay{transform:translateY(0)}

.btn-read-more{
    display:inline-flex;
    align-items:center;
    padding:10px 24px;
    background:#1358db;
    color:#fff;
    border-radius:99px;
    font-weight:600;
    font-size:14px;
    text-decoration:none;
    box-shadow:0 4px 6px -1px rgba(19, 88, 219, 0.3);
    transition:background 0.2s;
}
.btn-read-more:hover{background:#0b45b0}
.btn-read-more svg{width:16px;height:16px;margin-left:6px}

/* Navigation Buttons */
.blogs-nav{display:flex;gap:8px}
.blog-nav-btn{height:40px;width:40px;border-radius:50%;border:1px solid #cbd5e1;background:#fff;display:flex;align-items:center;justify-content:center;cursor:pointer;transition:all 0.2s;color:#64748b}
.blog-nav-btn:hover{border-color:#1358db;color:#1358db}
.blog-nav-btn svg{width:20px;height:20px;stroke-width:2;stroke:currentColor;fill:none}

@media(max-width:768px){
    .blog-card{flex:0 0 280px;height:auto;min-height:380px}
    .read-more-overlay{position:relative;transform:translateY(0);background:none;padding:15px 0 0 0;justify-content:flex-start}
    .btn-read-more{width:100%;justify-content:center;background:#eff6ff;color:#1358db;box-shadow:none}
}
</style>

<section class="blogs-section" aria-label="Latest Blogs">
    <div class="blogs-wrap">
        <div class="blogs-head">
            <div>
                <h3 class="blogs-title">Latest <span class="accent-text">Updates & Articles</span></h3>
                <p class="blogs-subtitle">Stay informed with our latest news and educational insights.</p>
            </div>
            <div class="blogs-nav">
                <button class="blog-nav-btn prev-blog" aria-label="Previous"><svg viewBox="0 0 24 24"><path d="M15 18l-6-6 6-6"/></svg></button>
                <button class="blog-nav-btn next-blog" aria-label="Next"><svg viewBox="0 0 24 24"><path d="M9 18l6-6-6-6"/></svg></button>
            </div>
        </div>

        <div class="blogs-shell" id="blogsShell">
            <div class="blogs-track" id="blogsTrack">
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <?php 
                            $title = htmlspecialchars($row['title']);
                            $date = date("M d, Y", strtotime($row['created_at']));
                            // Strip HTML tags for description and decode entities
                            $clean_desc = strip_tags(html_entity_decode($row['description']));
                            $img = !empty($row['featured_image']) ? $row['featured_image'] : 'https://placehold.co/600x400/e2e8f0/64748b?text=MG+Skill';
                            $link = "blog-details.php?slug=" . urlencode($row['slug']);
                        ?>
                        <article class="blog-card">
                            <div class="blog-img">
                                <img src="<?php echo htmlspecialchars($img); ?>" alt="<?php echo $title; ?>" loading="lazy">
                            </div>
                            <div class="blog-body">
                                <span class="blog-date"><?php echo $date; ?></span>
                                <h4 class="blog-heading"><?php echo $title; ?></h4>
                                <p class="blog-desc"><?php echo $clean_desc; ?></p>
                                
                                <div class="read-more-overlay">
                                    <a href="<?php echo $link; ?>" class="btn-read-more">
                                        Read More <svg viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                                    </a>
                                </div>
                            </div>
                        </article>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p style="padding:20px;color:#64748b;width:100%;text-align:center;">No blog posts available at the moment.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const track = document.getElementById('blogsTrack');
    const shell = document.getElementById('blogsShell');
    const prevBtn = document.querySelector('.prev-blog');
    const nextBtn = document.querySelector('.next-blog');
    
    if(!track || !track.children.length) return;

    let cardWidth = track.children[0].offsetWidth + 20; // Width + gap
    let scrollPos = 0;
    let maxScroll = track.scrollWidth - shell.offsetWidth;
    let autoPlayInterval;

    // Handle Resize
    window.addEventListener('resize', () => {
        cardWidth = track.children[0].offsetWidth + 20;
        maxScroll = track.scrollWidth - shell.offsetWidth;
    });

    function scroll(direction) {
        if (direction === 1) {
            scrollPos += cardWidth;
            if (scrollPos > maxScroll) scrollPos = 0; // Loop back to start
        } else {
            scrollPos -= cardWidth;
            if (scrollPos < 0) scrollPos = maxScroll; // Loop to end
        }
        track.style.transform = `translateX(-${scrollPos}px)`;
    }

    // AutoPlay Logic
    function startAutoPlay() {
        autoPlayInterval = setInterval(() => scroll(1), 3000); // 3 seconds
    }

    function stopAutoPlay() {
        clearInterval(autoPlayInterval);
    }

    // Event Listeners
    nextBtn.addEventListener('click', () => {
        scroll(1);
        stopAutoPlay();
        startAutoPlay(); // Restart timer
    });

    prevBtn.addEventListener('click', () => {
        scroll(-1);
        stopAutoPlay();
        startAutoPlay();
    });

    // Pause on hover
    shell.addEventListener('mouseenter', stopAutoPlay);
    shell.addEventListener('mouseleave', startAutoPlay);

    // Initialize
    startAutoPlay();
});
</script>
