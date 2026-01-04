<?php
$scheme=(!empty($_SERVER['HTTPS'])&&$_SERVER['HTTPS']!=='off')?'https':'http';
$host=isset($_SERVER['HTTP_HOST'])?$_SERVER['HTTP_HOST']:'localhost';
$url=$scheme.'://'.$host.(isset($_SERVER['REQUEST_URI'])?$_SERVER['REQUEST_URI']:'/');
$origin=$scheme.'://'.$host.'/';
$ogImage=$origin.'assets/images/mg-logo.jpg';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover"/>
  <title>MG Skill • Skill Courses, Job Exchange, Skill Centres</title>
  <meta name="description" content="Explore skill development courses, job exchange opportunities, schemes and skill centres across India. Register or login to access personalized dashboards on MG Skill."/>
  <meta name="keywords" content="Skill India, skill courses, job exchange, skill centre, MSDE, schemes, programs"/>
  <meta name="robots" content="index,follow"/>
  <link rel="canonical" href="<?php echo htmlspecialchars($url,ENT_QUOTES,'UTF-8'); ?>"/>
  <meta property="og:title" content="MG Skill • Skill Courses, Job Exchange, Skill Centres"/>
  <meta property="og:description" content="Discover and enroll in skill development programs, find jobs and explore centres near you."/>
  <meta property="og:type" content="website"/>
  <meta property="og:url" content="<?php echo htmlspecialchars($url,ENT_QUOTES,'UTF-8'); ?>"/>
  <meta property="og:image" content="<?php echo htmlspecialchars($ogImage,ENT_QUOTES,'UTF-8'); ?>"/>
  <meta property="og:locale" content="en_IN"/>
  <meta name="twitter:card" content="summary_large_image"/>
  <meta name="twitter:title" content="MG Skill • Skill Courses, Job Exchange, Skill Centres"/>
  <meta name="twitter:description" content="Enroll in courses, access dashboards, and explore opportunities."/>
  <meta name="twitter:image" content="<?php echo htmlspecialchars($ogImage,ENT_QUOTES,'UTF-8'); ?>"/>
  <meta name="theme-color" content="#1358db"/>
  <link rel="preload" as="image" href="assets/images/mg-logo.jpg"/>
  <script type="application/ld+json">
  {
    "@context":"https://schema.org",
    "@type":"Organization",
    "name":"MG Skill",
    "url":"<?php echo htmlspecialchars($origin,ENT_QUOTES,'UTF-8'); ?>",
    "logo":"<?php echo htmlspecialchars($ogImage,ENT_QUOTES,'UTF-8'); ?>"
  }
  </script>
  <style>
    *{box-sizing:border-box}
    html,body{height:100%}
    body{margin:0;font-family:system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,Helvetica,Arial,'Noto Sans','Apple Color Emoji','Segoe UI Emoji','Segoe UI Symbol';color:#0f1419;background:#ffffff}
    a{text-decoration:none;color:inherit}
    img{max-width:100%;display:block}
    .container{max-width:1200px;margin:0 auto;padding:0 16px}
  </style>
</head>
<body>
  <?php require_once __DIR__.'/includes/header.php'; ?>
  <?php require_once __DIR__.'/components/hero-slider.php'; ?>
  <?php require_once __DIR__.'/components/courses-grid.php'; ?>
  <?php require_once __DIR__.'/components/skills-ticker.php'; ?>
  <?php require_once __DIR__.'/components/promo-banners.php'; ?>
  <?php require_once __DIR__.'/components/donation-enquiry.php'; ?>
  <?php require_once __DIR__.'/components/nsdc-certificate.php'; ?>
  <?php require_once __DIR__.'/includes/footer.php'; ?>
</body>
</html>
