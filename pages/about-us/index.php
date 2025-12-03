<?php
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
$url = $scheme.'://'.$host.(isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/');
$origin = $scheme.'://'.$host.'/';
$basePath = rtrim(str_replace('\\','/', dirname($_SERVER['SCRIPT_NAME'])), '/');
$baseUrl = $scheme.'://'.$host.($basePath === '' ? '/' : $basePath.'/');
$logo = $baseUrl.'../../assets/images/mg-logo.jpg';
$promo = $baseUrl.'../../assets/images/about-us-page/promotional-banner.webp';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover"/>
  <title>About MG Skill • Empowering Freshers, Interns & Employers</title>
  <meta name="description" content="Learn about MG Skill’s mission, programs and AI-powered tools that connect freshers and interns with employers across India."/>
  <meta name="keywords" content="About MG Skill, freshers hiring, interns, skill development, employers"/>
  <meta name="robots" content="index,follow"/>
  <link rel="canonical" href="<?php echo htmlspecialchars($url, ENT_QUOTES, 'UTF-8'); ?>"/>
  <meta property="og:title" content="About MG Skill • Empowering Freshers, Interns & Employers"/>
  <meta property="og:description" content="Our story and how we help talent and employers meet using skills and AI-powered tools."/>
  <meta property="og:type" content="website"/>
  <meta property="og:url" content="<?php echo htmlspecialchars($url, ENT_QUOTES, 'UTF-8'); ?>"/>
  <meta property="og:image" content="<?php echo htmlspecialchars($promo, ENT_QUOTES, 'UTF-8'); ?>"/>
  <meta property="og:locale" content="en_IN"/>
  <meta name="twitter:card" content="summary_large_image"/>
  <meta name="twitter:title" content="About MG Skill • Empowering Freshers, Interns & Employers"/>
  <meta name="twitter:description" content="Mission, programs and tools that enable hiring and learning."/>
  <meta name="twitter:image" content="<?php echo htmlspecialchars($promo, ENT_QUOTES, 'UTF-8'); ?>"/>
  <meta name="theme-color" content="#1358db"/>
  <link rel="preload" as="image" href="<?php echo htmlspecialchars($promo, ENT_QUOTES, 'UTF-8'); ?>"/>
  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "AboutPage",
    "name": "About MG Skill",
    "description": "MG Skill empowers learners and helps employers hire freshers and interns with AI-powered tools.",
    "url": "<?php echo htmlspecialchars($url, ENT_QUOTES, 'UTF-8'); ?>",
    "isPartOf": {
      "@type": "WebSite",
      "name": "MG Skill",
      "url": "<?php echo htmlspecialchars($origin, ENT_QUOTES, 'UTF-8'); ?>"
    },
    "primaryImageOfPage": {
      "@type": "ImageObject",
      "contentUrl": "<?php echo htmlspecialchars($promo, ENT_QUOTES, 'UTF-8'); ?>",
      "representativeOfPage": true
    },
    "publisher": {
      "@type": "Organization",
      "name": "MG Skill",
      "logo": {
        "@type": "ImageObject",
        "url": "<?php echo htmlspecialchars($logo, ENT_QUOTES, 'UTF-8'); ?>"
      }
    }
  }
  </script>
  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "BreadcrumbList",
    "itemListElement": [
      {"@type": "ListItem", "position": 1, "name": "Home", "item": "<?php echo htmlspecialchars($origin, ENT_QUOTES, 'UTF-8'); ?>"},
      {"@type": "ListItem", "position": 2, "name": "About Us", "item": "<?php echo htmlspecialchars($url, ENT_QUOTES, 'UTF-8'); ?>"}
    ]
  }
  </script>
  <style>
    *{box-sizing:border-box}
    html,body{height:100%}
    body{margin:0;font-family:system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,Helvetica,Arial,'Noto Sans','Apple Color Emoji','Segoe UI Emoji','Segoe UI Symbol';color:#0f1419;background:#ffffff}
    a{text-decoration:none;color:inherit}
    img{max-width:100%;display:block}
    .container{max-width:1200px;margin:0 auto;padding:0 16px}
    .about{background:#fff}
    .about-wrap{max-width:1200px;margin:0 auto;padding:12px 16px 18px}
    .promo-card{position:relative;border-radius:18px;overflow:hidden;background:#2466bd}
    .promo-inner{display:grid;grid-template-columns:1.2fr 1fr;gap:24px;align-items:center;padding:24px}
    .pill{display:inline-flex;align-items:center;gap:8px;padding:8px 12px;border-radius:999px;background:#4e83d2;color:#fff;font-weight:800}
    .promo-title{margin:10px 0 12px 0;color:#fff;font-size:32px;line-height:1.15;font-weight:800}
    .promo-text{margin:0 0 18px 0;color:#e7ecf4;font-size:18px;line-height:1.5}
    .cta{display:inline-flex;align-items:center;gap:8px;padding:12px 16px;border-radius:12px;background:#ffd34a;color:#0b1020;font-weight:800}
    .promo-media{display:flex;align-items:center;justify-content:flex-end}
    .promo-image{width:100%;max-width:420px;height:auto;border-radius:12px}
    .promo-logo{height:44px;width:auto;border-radius:10px;display:block;margin-bottom:8px}
    @media(max-width:900px){.promo-title{font-size:28px}.promo-inner{grid-template-columns:1fr;gap:14px;padding:18px}.promo-media{justify-content:center}.promo-image{max-width:360px}}
    @media(max-width:640px){.promo-title{font-size:24px}.promo-text{font-size:16px}}
  </style>
  <link rel="preload" as="image" href="<?php echo htmlspecialchars($logo, ENT_QUOTES, 'UTF-8'); ?>"/>
  <link rel="preload" as="image" href="<?php echo htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8'); ?>assets/images/logo.jpg"/>
</head>
<body>
  <?php require_once __DIR__.'/../../includes/header.php'; ?>
  <section class="about" aria-label="About MG Skill">
    <div class="about-wrap">
      <div class="promo-card" role="region" aria-label="Employers promotional banner">
        <div class="promo-inner">
          <div>
            <picture>
              <source srcset="<?php echo htmlspecialchars($logo, ENT_QUOTES, 'UTF-8'); ?>" type="image/jpeg">
              <img class="promo-logo" src="<?php echo htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8'); ?>assets/images/logo.jpg" alt="MG Skill logo" loading="eager" decoding="async" fetchpriority="high"/>
            </picture>
            <span class="pill">For Employers</span>
            <h1 class="promo-title">Looking to hire freshers and interns?</h1>
            <p class="promo-text">Access India’s talent pool with AI-powered tools and smart filters to hire faster through MG Skill.</p>
            <a class="cta" href="/jobs">Post now for free</a>
          </div>
          <div class="promo-media">
            <picture>
              <source srcset="<?php echo htmlspecialchars($promo, ENT_QUOTES, 'UTF-8'); ?>" type="image/webp">
              <img class="promo-image" src="<?php echo htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8'); ?>assets/images/about-us-page/promotional-banner.webp" alt="Promotional hiring tools banner" loading="eager" decoding="async"/>
            </picture>
          </div>
        </div>
      </div>
    </div>
  </section>
  <?php require_once __DIR__.'/../../includes/footer.php'; ?>
</body>
</html>
