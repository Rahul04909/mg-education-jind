<?php
// Ensure database connection
if (!function_exists('getDbConnection')) {
    require_once __DIR__ . '/../database/db-config.php';
}
$conn = getDbConnection();

// Fetch News
$news_items = [];
try {
    $sql = "SELECT title, url FROM news WHERE is_active = 1 ORDER BY created_at DESC LIMIT 10";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $news_items[] = $row;
        }
    }
} catch (Exception $e) {
    // Table might not exist yet, ignore error and fall back to static
}

// Fallback Data if DB empty or error
if (empty($news_items)) {
    $news_items = [
        ['title' => 'Admissions open for 2026 session - Apply Now', 'url' => 'online-admission'],
        ['title' => 'New Skill Centre opened in Jind - Visit today', 'url' => 'skill-centre'],
        ['title' => 'Scholarship results announced for Batch 2025', 'url' => '#'],
        ['title' => 'MG Skills is now hiring volunteers - Join Us', 'url' => 'pages/join-as-a-volunteer/index.php']
    ];
}
?>
<style>
    .news-ticker-section {
        background: #0b1020;
        color: #fff;
        border-bottom: 1px solid #1e293b;
        font-family: 'Outfit', system-ui, sans-serif;
        height: 48px;
        display: flex;
        overflow: hidden;
        position: relative;
        z-index: 50; /* Above hero slider often */
    }

    .news-label {
        background: #1358db;
        color: #fff;
        padding: 0 24px;
        display: flex;
        align-items: center;
        font-weight: 700;
        font-size: 14px;
        text-transform: uppercase;
        position: relative;
        z-index: 2;
        flex-shrink: 0;
        clip-path: polygon(0 0, 100% 0, 90% 100%, 0% 100%);
        padding-right: 32px;
    }

    .news-label .pulse-dot {
        height: 8px;
        width: 8px;
        background: #fff;
        border-radius: 50%;
        margin-right: 10px;
        animation: pulse 1.5s infinite;
    }

    @keyframes pulse {
        0% { box-shadow: 0 0 0 0 rgba(255, 255, 255, 0.7); }
        70% { box-shadow: 0 0 0 6px rgba(255, 255, 255, 0); }
        100% { box-shadow: 0 0 0 0 rgba(255, 255, 255, 0); }
    }

    .ticker-wrap {
        flex: 1;
        display: flex;
        align-items: center;
        overflow: hidden;
        position: relative;
    }

    /* Gradient masks for smooth fade */
    .ticker-wrap::before, .ticker-wrap::after {
        content: '';
        position: absolute;
        top: 0;
        bottom: 0;
        width: 30px;
        z-index: 1;
        pointer-events: none;
    }
    .ticker-wrap::before {
        left: 0;
        background: linear-gradient(to right, #0b1020, transparent);
    }
    .ticker-wrap::after {
        right: 0;
        background: linear-gradient(to left, #0b1020, transparent);
    }

    .ticker-move {
        display: inline-flex;
        white-space: nowrap;
        animation: ticker 40s linear infinite;
        padding-left: 100%; /* Start off-screen */
        align-items: center;
    }

    .news-ticker-section:hover .ticker-move {
        animation-play-state: paused;
    }

    .ticker-item {
        display: inline-flex;
        align-items: center;
        margin-right: 40px;
        color: #cbd5e1;
        text-decoration: none;
        font-size: 14px;
        font-weight: 500;
        transition: color 0.2s;
    }

    .ticker-item:hover {
        color: #fff;
        text-decoration: underline;
        text-underline-offset: 4px;
    }

    .ticker-separator {
        display: inline-block;
        width: 6px;
        height: 6px;
        background: #334155;
        border-radius: 50%;
        margin-right: 40px;
    }

    @keyframes ticker {
        0% { transform: translate3d(0, 0, 0); }
        100% { transform: translate3d(-100%, 0, 0); }
    }

    @media (max-width: 600px) {
        .news-label {
            padding: 0 16px;
            font-size: 12px;
            padding-right: 24px;
        }
        .ticker-item {
            font-size: 13px;
        }
    }
</style>

<div class="news-ticker-section">
    <div class="news-label">
        <span class="pulse-dot"></span>
        Latest News
    </div>
    <div class="ticker-wrap">
        <div class="ticker-move">
            <?php foreach ($news_items as $index => $item): ?>
                <a href="<?php echo htmlspecialchars($item['url']); ?>" class="ticker-item">
                    <?php echo htmlspecialchars($item['title']); ?>
                </a>
                <!-- Separator (except for last potentially, but loop repeats so always good) -->
                <span class="ticker-separator"></span>
            <?php endforeach; ?>
            
            <!-- Duplicate content for seamless loop if content is short (optional logic but CSS handles start/end) -->
        </div>
    </div>
</div>
