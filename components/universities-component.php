<?php
// Ensure database connection
if (!function_exists('getDbConnection')) {
    require_once __DIR__ . '/../database/db-config.php';
}
$conn_uni = getDbConnection();

$universities = [];
try {
    $uni_sql = "SELECT * FROM universities ORDER BY created_at ASC";
    $uni_res = $conn_uni->query($uni_sql);
    if ($uni_res && $uni_res->num_rows > 0) {
        while ($uni_row = $uni_res->fetch_assoc()) {
            $universities[] = $uni_row;
        }
    }
} catch (Exception $e) {
    // Ignore if table doesn't exist
}

// Fallback if empty (Optional, or just hide section)
if (empty($universities)) {
    // We can hide the section if no universities are present
    // or add some placeholder ones if requested. 
    // Given the prompt "display to universities joined", better to only show if there are some.
}
?>
<?php if(!empty($universities)): ?>
<style>
    .uni-slider-section {
        padding: 30px 20px; /* Outer spacing */
        background: #fff;
    }

    .uni-container-box {
        background: #f8fafc;
        border-radius: 24px; /* Rounded borders */
        padding: 50px 0;
        margin: 0 auto;
        max-width: 1600px; /* Limit width */
        width: 100%;
        border: 1px solid #e2e8f0;
        overflow: hidden;
        position: relative;
    }

    .uni-header {
        text-align: center;
        margin-bottom: 40px;
        padding: 0 20px;
    }
    
    .uni-title {
        font-family: 'Outfit', sans-serif;
        font-size: 28px;
        font-weight: 700;
        color: #0f172a;
        margin: 0;
        text-transform: uppercase;
    }
    .uni-subtitle {
        color: #64748b;
        margin-top: 10px;
        font-size: 16px;
    }

    /* Infinite Slider Container */
    .uni-track-container {
        position: relative;
        width: 100%;
        overflow: hidden;
        mask-image: linear-gradient(to right, transparent, black 15%, black 85%, transparent);
        -webkit-mask-image: linear-gradient(to right, transparent, black 15%, black 85%, transparent);
    }

    .uni-track {
        display: flex;
        gap: 60px;
        width: max-content;
        animation: uniScroll 35s linear infinite;
    }
    
    .uni-track:hover {
        animation-play-state: paused;
    }

    .uni-item {
        height: 80px;
        min-width: 120px;
        flex-shrink: 0;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        transition: all 0.3s;
        /* Removed grayscale and opacity */
    }

    .uni-item:hover {
        filter: grayscale(0%);
        opacity: 1;
        transform: scale(1.05);
    }

    .uni-img {
        height: 100%;
        width: auto;
        object-fit: contain;
        max-width: 200px;
    }
    
    @keyframes uniScroll {
        0% { transform: translateX(0); }
        100% { transform: translateX(calc(-50% - 30px)); } /* Adjust based ongap */
    }

    @media (max-width: 768px) {
        .uni-track { gap: 40px; }
        .uni-item { height: 60px; }
        .uni-title { font-size: 22px; }
        .uni-container-box { border-radius: 16px; padding: 30px 0; }
    }
</style>

<section class="uni-slider-section">
    <div class="uni-container-box">
        <div class="uni-header">
            <h2 class="uni-title">Universities Joined with <span style="color:#d97706">MG Education</span></h2>
            <p class="uni-subtitle">Partnering for Social Development & Educational Excellence</p>
        </div>
        
        <div class="uni-track-container">
            <div class="uni-track">
                <!-- Original Set -->
                <?php foreach($universities as $uni): ?>
                <div class="uni-item" title="<?php echo htmlspecialchars($uni['name']); ?>">
                    <img src="<?php echo htmlspecialchars($uni['logo_path']); ?>" class="uni-img" alt="<?php echo htmlspecialchars($uni['name']); ?>">
                </div>
                <?php endforeach; ?>
                
                <!-- Duplicate Set for Infinite Loop -->
                <?php foreach($universities as $uni): ?>
                <div class="uni-item" title="<?php echo htmlspecialchars($uni['name']); ?>">
                    <img src="<?php echo htmlspecialchars($uni['logo_path']); ?>" class="uni-img" alt="<?php echo htmlspecialchars($uni['name']); ?>">
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>
