<?php
require_once __DIR__ . '/database/db-config.php';
$conn = getDbConnection();

$slug = isset($_GET['slug']) ? mysqli_real_escape_string($conn, $_GET['slug']) : '';
$blog = null;

if ($slug) {
    $sql = "SELECT b.*, cat.name as category_name 
            FROM blogs b 
            LEFT JOIN blog_categories cat ON b.category_id = cat.id 
            WHERE b.slug = '$slug' AND b.is_active = 1";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        $blog = $result->fetch_assoc();
    }
}

if (!$blog) {
    // Redirect to home if not found
    header("Location: /");
    exit;
}

// Fetch 5 Recent Blogs for Sidebar
$recent_sql = "SELECT title, slug, created_at, featured_image FROM blogs WHERE is_active = 1 AND id != " . $blog['id'] . " ORDER BY created_at DESC LIMIT 5";
$recent_res = $conn->query($recent_sql);

$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
$url = $scheme . '://' . $host . (isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/');
$origin = $scheme . '://' . $host . '/';

$ogImage = !empty($blog['featured_image']) ? $origin . $blog['featured_image'] : $origin . 'assets/images/mg-logo.jpg';
$description = !empty($blog['meta_desc']) ? $blog['meta_desc'] : substr(strip_tags($blog['description']), 0, 160);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($blog['meta_title'] ?: $blog['title'] . ' - MG Skill'); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($description); ?>">
    <link rel="canonical" href="<?php echo htmlspecialchars($url, ENT_QUOTES, 'UTF-8'); ?>"/>
    
    <!-- OG Tags -->
    <meta property="og:title" content="<?php echo htmlspecialchars($blog['title']); ?>"/>
    <meta property="og:description" content="<?php echo htmlspecialchars($description); ?>"/>
    <meta property="og:type" content="article"/>
    <meta property="og:image" content="<?php echo htmlspecialchars($ogImage, ENT_QUOTES, 'UTF-8'); ?>"/>
    <meta property="og:url" content="<?php echo htmlspecialchars($url, ENT_QUOTES, 'UTF-8'); ?>"/>
    
    <style>
        :root{--active:#22c55e;--indigo:#4f46e5;--line:#e2e8f0;--text:#1e293b;--muted:#64748b;--bg:#f8fafc;}
        
        body { margin: 0; font-family: 'Outfit', system-ui, sans-serif; background: #fff; color: var(--text); }
        .blog-hero { background: #f8fafc; padding: 40px 0; border-bottom: 1px solid var(--line); }
        .container { max-width: 1200px; margin: 0 auto; padding: 0 16px; }
        
        .breadcrumb { display: flex; gap: 8px; font-size: 14px; color: var(--muted); margin-bottom: 16px; align-items: center; }
        .breadcrumb a { text-decoration: none; color: var(--indigo); font-weight: 500; }
        .breadcrumb span { color: var(--line); }
        
        .hero-grid { display: grid; grid-template-columns: 1.2fr 0.8fr; gap: 40px; align-items: center; }
        
        .blog-title { font-size: 42px; font-weight: 800; line-height: 1.2; color: #0b1020; margin: 0 0 16px 0; }
        .blog-meta { display: flex; gap: 16px; font-size: 14px; color: var(--muted); font-weight: 500; align-items: center; margin-bottom: 24px; }
        .blog-badge { background: #e0e7ff; color: var(--indigo); padding: 4px 12px; border-radius: 99px; font-weight: 600; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; }
        
        .hero-img { width: 100%; border-radius: 16px; overflow: hidden; box-shadow: 0 12px 24px rgba(0,0,0,0.08); aspect-ratio: 16/9; }
        .hero-img img { width: 100%; height: 100%; object-fit: cover; }
        
        /* content layout */
        .content-wrap { padding: 40px 0 60px; }
        .content-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 40px; }
        
        .article-body { font-size: 18px; line-height: 1.8; color: #334155; }
        .article-body p { margin-bottom: 24px; }
        .article-body h2 { font-size: 28px; font-weight: 700; color: #1e293b; margin: 40px 0 20px; }
        .article-body h3 { font-size: 22px; font-weight: 600; color: #1e293b; margin: 30px 0 16px; }
        .article-body ul, .article-body ol { margin-bottom: 24px; padding-left: 24px; }
        .article-body img { max-width: 100%; border-radius: 12px; margin: 24px 0; }
        
        .share-box { margin-top: 40px; padding-top: 24px; border-top: 1px solid var(--line); }
        .share-title { font-size: 14px; font-weight: 700; color: var(--muted); text-transform: uppercase; margin-bottom: 12px; }
        .share-links { display: flex; gap: 12px; }
        .share-btn { width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; background: #f1f5f9; color: var(--text); text-decoration: none; transition: all 0.2s; }
        .share-btn:hover { background: var(--indigo); color: #fff; transform: translateY(-3px); }
        
        /* Sidebar */
        .sidebar { position: sticky; top: 100px; }
        .widget { background: #fff; border: 1px solid var(--line); border-radius: 16px; padding: 24px; margin-bottom: 24px; }
        .widget-title { font-size: 18px; font-weight: 700; color: #1e293b; margin-bottom: 20px; padding-bottom: 12px; border-bottom: 1px solid var(--line); }
        
        .recent-blog { display: flex; gap: 12px; margin-bottom: 16px; align-items: start; }
        .recent-blog:last-child { margin-bottom: 0; }
        .rb-img { width: 80px; height: 60px; border-radius: 8px; overflow: hidden; flex-shrink: 0; background: #f1f5f9; }
        .rb-img img { width: 100%; height: 100%; object-fit: cover; }
        .rb-info h4 { margin: 0 0 4px 0; font-size: 14px; line-height: 1.4; font-weight: 600; }
        .rb-info h4 a { text-decoration: none; color: #334155; transition: color 0.2s; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        .rb-info h4 a:hover { color: var(--indigo); }
        .rb-date { font-size: 12px; color: var(--muted); }

        @media(max-width: 900px) {
            .hero-grid { grid-template-columns: 1fr; gap: 24px; }
            .hero-img { order: -1; }
            .content-grid { grid-template-columns: 1fr; }
            .sidebar { position: static; margin-top: 40px; }
            .blog-title { font-size: 32px; }
        }
    </style>
</head>
<body>

    <?php include 'includes/header.php'; ?>

    <section class="blog-hero">
        <div class="container">
            <div class="hero-grid">
                <div>
                    <div class="breadcrumb">
                        <a href="/">Home</a> <span>/</span>
                        <a href="#">Blogs</a> <span>/</span>
                        <span style="color:var(--muted);"><?php echo htmlspecialchars($blog['category_name'] ?: 'General'); ?></span>
                    </div>
                    
                    <span class="blog-badge"><?php echo htmlspecialchars($blog['category_name'] ?: 'Update'); ?></span>
                    
                    <h1 class="blog-title"><?php echo htmlspecialchars($blog['title']); ?></h1>
                    
                    <div class="blog-meta">
                        <span><svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="vertical-align:text-bottom;margin-right:6px"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg> <?php echo date("F d, Y", strtotime($blog['created_at'])); ?></span>
                        <span>•</span>
                        <span>MG Education Team</span>
                    </div>
                </div>
                
                <div class="hero-img">
                    <?php 
                        $img = !empty($blog['featured_image']) ? $blog['featured_image'] : 'https://placehold.co/800x450/e2e8f0/64748b?text=MG+Skills';
                    ?>
                    <img src="<?php echo htmlspecialchars($img); ?>" alt="<?php echo htmlspecialchars($blog['title']); ?>">
                </div>
            </div>
        </div>
    </section>

    <div class="content-wrap">
        <div class="container">
            <div class="content-grid">
                
                <!-- Main Content -->
                <article class="article-body">
                    <?php echo html_entity_decode($blog['description']); ?>
                    
                    <div class="share-box">
                        <div class="share-title">Share this article</div>
                        <div class="share-links">
                            <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode($url); ?>" target="_blank" class="share-btn" style="color:#1877f2"><i class="fab fa-facebook-f"></i></a>
                            <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode($url); ?>&text=<?php echo urlencode($blog['title']); ?>" target="_blank" class="share-btn" style="color:#1da1f2"><i class="fab fa-twitter"></i></a>
                            <a href="https://api.whatsapp.com/send?text=<?php echo urlencode($blog['title'] . ' ' . $url); ?>" target="_blank" class="share-btn" style="color:#25d366"><i class="fab fa-whatsapp"></i></a>
                            <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?php echo urlencode($url); ?>" target="_blank" class="share-btn" style="color:#0a66c2"><i class="fab fa-linkedin-in"></i></a>
                        </div>
                    </div>

                    <!-- Blog Reviews Component -->
                    <?php 
                        $blog_id = $blog['id'];
                        include 'components/blog-reviews.php'; 
                    ?>
                </article>

                <!-- Sidebar -->
                <aside class="sidebar">
                    <div class="widget">
                        <h4 class="widget-title">Recent Posts</h4>
                        
                        <?php if ($recent_res && $recent_res->num_rows > 0): ?>
                            <?php while($recent = $recent_res->fetch_assoc()): ?>
                                <?php 
                                    $r_img = !empty($recent['featured_image']) ? $recent['featured_image'] : 'https://placehold.co/100x100/e2e8f0/64748b?text=Img';
                                ?>
                                <div class="recent-blog">
                                    <div class="rb-img">
                                        <img src="<?php echo htmlspecialchars($r_img); ?>" alt="<?php echo htmlspecialchars($recent['title']); ?>">
                                    </div>
                                    <div class="rb-info">
                                        <h4><a href="blog-details.php?slug=<?php echo urlencode($recent['slug']); ?>"><?php echo htmlspecialchars($recent['title']); ?></a></h4>
                                        <span class="rb-date"><?php echo date("M d, Y", strtotime($recent['created_at'])); ?></span>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p style="color:var(--muted);font-size:14px;">No recent posts.</p>
                        <?php endif; ?>
                        
                    </div>
                    
                    <!-- Enhanced Donate Widget -->
                    <style>
                        .donate-card {
                            background: linear-gradient(135deg, #0ea5e9 0%, #2563eb 100%);
                            border-radius: 16px;
                            padding: 30px 24px;
                            color: #fff;
                            text-align: center;
                            box-shadow: 0 10px 25px -5px rgba(14, 165, 233, 0.4);
                            position: relative;
                            overflow: hidden;
                        }
                        .donate-card::before {
                            content: '';
                            position: absolute;
                            top: -20px;
                            left: -20px;
                            width: 100px;
                            height: 100px;
                            background: rgba(255,255,255,0.1);
                            border-radius: 50%;
                        }
                        .donate-card::after {
                            content: '';
                            position: absolute;
                            bottom: -10px;
                            right: -10px;
                            width: 80px;
                            height: 80px;
                            background: rgba(255,255,255,0.1);
                            border-radius: 50%;
                        }
                        .dc-icon {
                            width: 60px;
                            height: 60px;
                            background: rgba(255,255,255,0.2);
                            border-radius: 50%;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            margin: 0 auto 20px;
                            font-size: 24px;
                        }
                        .dc-title { font-size: 22px; font-weight: 800; margin-bottom: 10px; line-height: 1.2; position: relative; z-index: 2; }
                        .dc-text { font-size: 14px; opacity: 0.9; margin-bottom: 24px; line-height: 1.6; position: relative; z-index: 2; }
                        .dc-btn {
                            display: inline-block;
                            background: #fff;
                            color: #2563eb;
                            font-weight: 700;
                            padding: 14px 28px;
                            border-radius: 99px;
                            text-decoration: none;
                            box-shadow: 0 4px 10px rgba(0,0,0,0.15);
                            transition: transform 0.2s;
                            position: relative;
                            z-index: 2;
                        }
                        .dc-btn:hover { transform: translateY(-3px); }
                    </style>
                    <div class="donate-card">
                        <div class="dc-icon"><i class="fas fa-heart"></i></div>
                        <h4 class="dc-title">Support Our Mission</h4>
                        <p class="dc-text">Your small contribution can empower a student's dream. Help us make a difference today.</p>
                        <a href="pages/donate-now/" class="dc-btn">Donate Now</a>
                    </div>
                </aside>
                
            </div>
        </div>
    </div>
    
    <!-- Load FontAwesome for Share Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

    <?php include 'includes/footer.php'; ?>
</body>
</html>
<?php $conn->close(); ?>
