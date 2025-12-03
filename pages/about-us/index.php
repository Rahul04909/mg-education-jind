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
    "@type": "Organization",
    "name": "M.G. Education and Social Development Organisation",
    "url": "<?php echo htmlspecialchars($origin, ENT_QUOTES, 'UTF-8'); ?>",
    "logo": "<?php echo htmlspecialchars($logo, ENT_QUOTES, 'UTF-8'); ?>",
    "address": {
      "@type": "PostalAddress",
      "streetAddress": "2102, Near Jat Dharamshala, Urban Estate",
      "addressLocality": "Jind",
      "addressRegion": "Haryana",
      "postalCode": "126102",
      "addressCountry": "IN"
    },
    "contactPoint": [{
      "@type": "ContactPoint",
      "telephone": "+91-9813354588",
      "contactType": "customer support",
      "areaServed": "IN"
    }]
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
    .org{background:#fff}
    .org-wrap{max-width:1200px;margin:0 auto;padding:12px 16px 18px}
    .org-card{border:1px solid #e6e8ee;background:#fff;border-radius:18px;padding:24px;box-shadow:0 10px 24px rgba(0,0,0,.06)}
    .eyebrow3{color:#d41e5b;font-weight:800;margin:0}
    .org-name{margin:8px 0 10px 0;color:#0b1020;font-size:28px;line-height:1.2;font-weight:900}
    .org-location{margin:0 0 10px 0;color:#5b616e;font-weight:700}
    .org-desc{margin:8px 0 0 0;color:#0b1020;font-size:18px;line-height:1.7}
    .accent-gradient{background:linear-gradient(90deg,#1358db 0%,#5b61ff 60%,#7c83ff 100%);-webkit-background-clip:text;background-clip:text;color:transparent}
    .section{background:#fff}
    .section-wrap{max-width:1200px;margin:0 auto;padding:12px 16px 18px}
    .section-title{margin:8px 0 12px 0;color:#0b1020;font-size:26px;font-weight:900}
    .card{border:1px solid #e6e8ee;background:#fff;border-radius:18px;padding:24px;box-shadow:0 10px 24px rgba(0,0,0,.06)}
    .grid{display:grid;grid-template-columns:1fr 1fr;gap:16px}
    .chip{display:flex;align-items:flex-start;gap:12px;padding:14px;border:1px solid #e6e8ee;border-radius:14px;background:#fff}
    .ico{height:32px;width:32px;border-radius:10px;background:#eef6ff;display:flex;align-items:center;justify-content:center}
    .ico svg{height:18px;width:18px;stroke:#0a50c9;fill:none;stroke-width:2}
    .chip h4{margin:0 0 6px 0;color:#0b1020;font-size:16px;font-weight:800}
    .chip p{margin:0;color:#5b616e}
    .contact-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px}
    .contact-item{border:1px solid #e6e8ee;border-radius:14px;padding:14px}
    .contact-item h5{margin:0 0 6px 0;font-size:16px;color:#0b1020}
    .contact-item p{margin:0;color:#5b616e}
    .lead-grid{display:grid;grid-template-columns:1fr 1.2fr;gap:16px;align-items:center}
    .lead-media{display:flex;align-items:center;justify-content:center}
    .lead-image{max-width:380px;width:100%;height:auto;border-radius:16px;box-shadow:0 12px 28px rgba(0,0,0,.12)}
    @media(max-width:900px){.promo-title{font-size:28px}.promo-inner{grid-template-columns:1fr;gap:14px;padding:18px}.promo-media{justify-content:center}.promo-image{max-width:360px}.grid{grid-template-columns:1fr}.contact-grid{grid-template-columns:1fr}.lead-grid{grid-template-columns:1fr}}
    @media(max-width:900px){.promo-title{font-size:28px}.promo-inner{grid-template-columns:1fr;gap:14px;padding:18px}.promo-media{justify-content:center}.promo-image{max-width:360px}}
    @media(max-width:640px){.promo-title{font-size:24px}.promo-text{font-size:16px}}
  </style>
  <link rel="preload" as="image" href="<?php echo htmlspecialchars($logo, ENT_QUOTES, 'UTF-8'); ?>"/>
  <link rel="preload" as="image" href="<?php echo htmlspecialchars($origin, ENT_QUOTES, 'UTF-8'); ?>assets/images/logo.jpg"/>
  <link rel="preload" as="image" href="/assets/images/rajkumar.jpeg"/>
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
              <img class="promo-logo" src="<?php echo htmlspecialchars($origin, ENT_QUOTES, 'UTF-8'); ?>assets/images/logo.jpg" alt="MG Skill logo" loading="eager" decoding="async" fetchpriority="high"/>
            </picture>
            <span class="pill">For Employers</span>
            <h1 class="promo-title">Looking to hire freshers and interns?</h1>
            <p class="promo-text">Access India’s talent pool with AI-powered tools and smart filters to hire faster through MG Skill.</p>
            <a class="cta" href="/jobs">Post now for free</a>
          </div>
          <div class="promo-media">
            <picture>
              <source srcset="<?php echo htmlspecialchars($promo, ENT_QUOTES, 'UTF-8'); ?>" type="image/webp">
              <img class="promo-image" src="<?php echo htmlspecialchars($origin, ENT_QUOTES, 'UTF-8'); ?>assets/images/about-us-page/promotional-banner.webp" alt="Promotional hiring tools banner" loading="eager" decoding="async"/>
            </picture>
          </div>
        </div>
      </div>
    </div>
  </section>
  <section class="org" aria-label="Organization details">
    <div class="org-wrap">
      <div class="org-card">
        <p class="eyebrow3">About Us</p>
        <h2 class="org-name"><span class="accent-gradient">M.G. Education and Social Development Organisation</span></h2>
        <p class="org-location">Jind, Haryana, India</p>
        <p class="org-desc">The M.G. Education & Social Development Organisation (MGESDO) is a non-profit society established on November 11, 2010, and registered under the Haryana Societies Registration Act. Headquartered in Jind, Haryana, the organization is committed to empowering communities through education, skill development, women’s empowerment, and social welfare.</p>
        <p class="org-desc">Led by Chairman & Chief Functionary Mr. Raj Kumar Bhola, MGESDO operates with a vision to uplift marginalized sections of society by addressing real, local challenges with sustainable and inclusive solutions.</p>
      </div>
    </div>
  </section>
  <section class="section" aria-label="Mission and focus areas">
    <div class="section-wrap">
      <div class="card">
        <h3 class="section-title"><span class="accent-gradient">Our Mission</span></h3>
        <p class="org-desc">To build a just, equitable, and empowered society by providing access to quality education, skill-building opportunities, and social welfare programs — particularly for underprivileged communities.</p>
      </div>
      <div style="height:10px"></div>
      <div class="card">
        <h3 class="section-title"><span class="accent-gradient">Our Focus Areas</span></h3>
        <div class="grid">
          <div class="chip">
            <div class="ico"><svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="14" rx="2"/><path d="M7 8h10M7 12h6"/></svg></div>
            <div><h4>Education for All</h4><p>Free and affordable education for children and youth from low-income families.</p></div>
          </div>
          <div class="chip">
            <div class="ico"><svg viewBox="0 0 24 24"><path d="M12 14c3 0 5-2 5-5S15 4 12 4 7 6 7 9s2 5 5 5z"/><path d="M5 20a7 7 0 0 1 14 0"/></svg></div>
            <div><h4>Women Empowerment</h4><p>Vocational training, awareness drives, and self-help initiatives for women.</p></div>
          </div>
          <div class="chip">
            <div class="ico"><svg viewBox="0 0 24 24"><path d="M6 19l6-6 6 6"/><path d="M12 13V5"/></svg></div>
            <div><h4>Skill Development</h4><p>Training programs to boost employability for youth and school dropouts.</p></div>
          </div>
          <div class="chip">
            <div class="ico"><svg viewBox="0 0 24 24"><path d="M4 6h16v12H4z"/><path d="M8 10h8"/></svg></div>
            <div><h4>Health & Sanitation</h4><p>Community health check-ups, hygiene education, and awareness camps.</p></div>
          </div>
          <div class="chip">
            <div class="ico"><svg viewBox="0 0 24 24"><path d="M6 10l4-4 4 4"/><path d="M6 14l4 4 4-4"/></svg></div>
            <div><h4>Social Justice & Welfare</h4><p>Promoting equality, inclusion, and rights for disadvantaged groups.</p></div>
          </div>
        </div>
      </div>
    </div>
  </section>
  <section class="section" aria-label="Leadership">
    <div class="section-wrap">
      <div class="card">
        <div class="lead-grid">
          <div class="lead-media">
            <picture>
              <img class="lead-image" src="../../assets/images/rajkumar.jpeg" alt="Mr. Raj Kumar Bhola" loading="eager" decoding="async"/>
            </picture>
          </div>
          <div class="lead-content">
            <h3 class="section-title"><span class="accent-gradient">Our Leadership</span></h3>
            <p class="org-desc">President / Chairman / Chief Functionary: <strong>Mr. Raj Kumar Bhola</strong></p>
            <p class="org-desc">A visionary leader with a deep commitment to community development, Mr. Bhola has been the driving force behind MGESDO's growth and credibility in Haryana’s social sector.</p>
          </div>
        </div>
      </div>
    </div>
  </section>
  <section class="section" aria-label="Contact information">
    <div class="section-wrap">
      <div class="card">
        <h3 class="section-title"><span class="accent-gradient">Contact Us</span></h3>
        <div class="contact-grid">
          <div class="contact-item">
            <h5>Main Office</h5>
            <p>2102, Near Jat Dharamshala, Urban Estate, Jind, Haryana – 126102</p>
            <p>Phone: <a href="tel:01681359712">01681-359712</a></p>
            <p>Mobile: <a href="tel:+919813354588">+91 98133 54588</a></p>
            <p>Email: <a href="mailto:mgesdojind@gmail.com">mgesdojind@gmail.com</a></p>
            <p>Website: <a href="https://www.hkcledu.in" rel="noopener" target="_blank">www.hkcledu.in</a></p>
          </div>
          <div class="contact-item">
            <h5>Alternate Address</h5>
            <p>VPO Ahirka, Jind, Haryana – 126102</p>
          </div>
        </div>
      </div>
    </div>
  </section>
  <section class="section" aria-label="Affiliation">
    <div class="section-wrap">
      <div class="card">
        <h3 class="section-title"><span class="accent-gradient">Our Affiliation with HKCL</span></h3>
        <p class="org-desc">MGESDO is affiliated with the Haryana Knowledge Corporation Limited (HKCL), a public initiative promoted by the Government of Haryana and administered by Citizen Resource Information Department (CRID).</p>
        <p class="org-desc">HKCL, incorporated under the Companies Act, 1956 (CIN: U80904HR2013PLC050331), envisions a digital transformation in education through the integration of Information Technology into learning, teaching, and community development.</p>
      </div>
    </div>
  </section>
  <?php require_once __DIR__.'/../../includes/footer.php'; ?>
</body>
</html>
