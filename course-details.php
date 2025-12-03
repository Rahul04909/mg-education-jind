<?php
$scheme=(!empty($_SERVER['HTTPS'])&&$_SERVER['HTTPS']!=='off')?'https':'http';
$host=isset($_SERVER['HTTP_HOST'])?$_SERVER['HTTP_HOST']:'localhost';
$url=$scheme.'://'.$host.(isset($_SERVER['REQUEST_URI'])?$_SERVER['REQUEST_URI']:'/');
$origin=$scheme.'://'.$host.'/';
$ogImage=$origin.'../../assets/images/mg-logo.jpg';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover"/>
  <title>Course Details • MG Skill</title>
  <link rel="canonical" href="<?php echo htmlspecialchars($url,ENT_QUOTES,'UTF-8'); ?>"/>
  <meta name="description" content="Explore detailed curriculum, features, and classroom tour for this course."/>
  <meta property="og:title" content="Course Details • MG Skill"/>
  <meta property="og:description" content="Course overview, features, and classroom tour."/>
  <meta property="og:type" content="website"/>
  <meta property="og:url" content="<?php echo htmlspecialchars($url,ENT_QUOTES,'UTF-8'); ?>"/>
  <meta property="og:image" content="<?php echo htmlspecialchars($ogImage,ENT_QUOTES,'UTF-8'); ?>"/>
  <meta name="theme-color" content="#1358db"/>
  <link rel="preload" as="image" href="assets/images/placement-banner.webp"/>
  <style>
    *{box-sizing:border-box}
    html,body{height:100%}
    body{margin:0;font-family:system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,Helvetica,Arial,'Noto Sans','Apple Color Emoji','Segoe UI Emoji','Segoe UI Symbol';color:#0f1419;background:#ffffff}
    .details{background:#fff}
    .details-wrap{max-width:1200px;margin:0 auto;padding:14px 16px 18px}
    .cd-grid{display:grid;grid-template-columns:1.3fr 1fr;gap:20px;align-items:center}
    .badge-bar{display:flex;align-items:center;gap:10px;margin-bottom:10px;flex-wrap:wrap}
    .badge{display:inline-flex;align-items:center;gap:8px;padding:8px 12px;border-radius:999px;font-weight:800}
    .badge.green{background:#d1fadf;color:#065f46}
    .badge.blue{background:#e8f0ff;color:#1e3a8a}
    .accent-gradient{background:linear-gradient(90deg,#1358db 0%,#5b61ff 60%,#7c83ff 100%);-webkit-background-clip:text;background-clip:text;color:transparent}
    .cd-title{margin:6px 0 14px 0;color:#0b1020;line-height:1.07;font-size:38px;font-weight:800}
    .feats{display:grid;grid-template-columns:repeat(2,minmax(240px,1fr));gap:18px}
    .feat{display:flex;align-items:center;gap:10px}
    .icon-bubble{height:36px;width:36px;border-radius:12px;background:#f3f5ff;border:1px solid #e2e6f3;display:flex;align-items:center;justify-content:center}
    .icon-bubble svg{height:18px;width:18px;stroke:#4a5bd5;fill:none;stroke-width:2}
    .feat span{color:#3a4050;font-weight:600}
    .video-box{position:relative;border-radius:18px;overflow:hidden;background:#fff;border:1px solid #e6e8ee;box-shadow:0 12px 24px rgba(0,0,0,.06)}
    .video-box img{width:100%;height:auto;display:block}
    .play-cta{position:absolute;left:50%;top:50%;transform:translate(-50%,-50%);display:inline-flex;align-items:center;gap:10px;padding:12px 16px;border-radius:999px;background:#fff;color:#0f1419;font-weight:800;box-shadow:0 10px 22px rgba(0,0,0,.12)}
    .play-cta .dot{height:26px;width:26px;border-radius:999px;background:#ef4444;display:inline-flex;align-items:center;justify-content:center}
    .play-cta .dot svg{height:14px;width:14px;fill:#fff}
    @media(max-width:1024px){.cd-title{font-size:34px}}
    @media(max-width:900px){.cd-grid{grid-template-columns:1fr;gap:16px}.details-wrap{padding:12px 12px 16px}.video-box{order:2}.feats{grid-template-columns:1fr}}
    @media(max-width:640px){.cd-title{font-size:26px}.badge{padding:8px 10px;font-weight:700}.feats{gap:12px}.icon-bubble{height:32px;width:32px}.play-cta{padding:10px 14px}.play-cta .dot{height:22px;width:22px}}
  </style>
</head>
<body>
  <?php require_once __DIR__.'/includes/header.php'; ?>
  <section class="details" aria-label="Course hero">
    <div class="details-wrap">
      <div class="cd-grid">
        <div>
          <div class="badge-bar">
            <span class="badge green">100% Offline Classes</span>
            <span class="badge blue">Class 12 JEE ADVANCED</span>
          </div>
          <h1 class="cd-title"><span class="strong">Grade 12 JEE ADVANCED</span> <span class="accent-gradient">(2025–26)</span></h1>
          <div class="feats">
            <div class="feat"><span class="icon-bubble"><svg viewBox="0 0 24 24"><path d="M12 3l8 4v5c0 5-3.5 8-8 9-4.5-1-8-4-8-9V7l8-4z"/></svg></span><span>Learn from India’s Top Teachers</span></div>
            <div class="feat"><span class="icon-bubble"><svg viewBox="0 0 24 24"><rect x="4" y="4" width="16" height="12" rx="2"/></svg></span><span>Dedicated Academic Mentor</span></div>
            <div class="feat"><span class="icon-bubble"><svg viewBox="0 0 24 24"><path d="M4 12h6l2-4 4 8 2-4h2"/></svg></span><span>3‑Way Doubt Support</span></div>
            <div class="feat"><span class="icon-bubble"><svg viewBox="0 0 24 24"><path d="M4 4h16v16H4z"/><path d="M8 8h8v8H8z"/></svg></span><span>Smart Clickers & Hi‑tech Classroom</span></div>
          </div>
        </div>
        <div class="video-box" aria-label="Classroom preview">
          <img src="assets/images/placement-banner.webp" alt="Classroom"/>
          <a class="play-cta" href="/classroom-tour" aria-label="Take a Tour"><span class="dot"><svg viewBox="0 0 24 24"><path d="M8 5l10 7-10 7z"/></svg></span>Take a Tour</a>
        </div>
      </div>
    </div>
  </section>
  <?php require_once __DIR__.'/course-details/description.php'; ?>
  <?php require_once __DIR__.'/course-details/related-courses.php'; ?>
  <?php require_once __DIR__.'/course-details/testimonials.php'; ?>
  <?php require_once __DIR__.'/includes/footer.php'; ?>
</body>
</html>
