<?php
$scheme=(!empty($_SERVER['HTTPS'])&&$_SERVER['HTTPS']!=='off')?'https':'http';
$host=isset($_SERVER['HTTP_HOST'])?$_SERVER['HTTP_HOST']:'localhost';
$basePath=rtrim(str_replace('\\','/',dirname($_SERVER['SCRIPT_NAME'])),'/');
$baseUrl=$scheme.'://'.$host.($basePath===''?'/':$basePath.'/');
?>
<style>
:root{--brand:#1358db;--accent:#b2560a;--bg:#ffffff;--muted:#6f7787;--line:#e6e8ee;--chip:#f4f5f7;--pill:#0a7cff}
.site-header{position:sticky;top:0;left:0;right:0;background:var(--bg);z-index:1000;box-shadow:0 4px 18px rgba(0,0,0,.06)}
.container{max-width:1200px;margin:0 auto;padding:0 16px}
.header-top{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:12px 0;position:relative}

/* Menu Toggle - Always Visible */
.menu-toggle{display:flex;border:1px solid var(--line);background:var(--bg);height:42px;width:42px;border-radius:10px;align-items:center;justify-content:center;cursor:pointer;transition:all .2s ease; z-index: 20;}
.menu-toggle:hover{background:#f8fafc;border-color:#cbd5e1}

/* Brand - Centered Absolutely */
.brand{display:flex;align-items:center;gap:10px;color:#121212;font-weight:700;flex-shrink:0; position:absolute; left:50%; transform:translateX(-50%); z-index: 10;}
.brand-logo{height:54px;width:auto;border-radius:8px;display:block}

/* Search */
.search-wrap{flex:1;max-width:300px; margin-left: 60px; display: none;} /* Hidden by default, maybe show if space */

/* Actions - Right Aligned */
.actions{display:flex;align-items:center;gap:10px;margin-left:auto; z-index: 20;}
.btn{display:inline-flex;align-items:center;gap:8px;padding:10px 16px;border-radius:10px;text-decoration:none;font-weight:600;line-height:1;border:1px solid transparent; font-size: 14px;}
.btn-outline{border-color:#cfe0ff;color:#0a50c9;background:#f8fbff}
.btn-primary{background:var(--accent);color:#fff;border-color:transparent}
.btn-light{background:#fff;color:#5b616e;border-color:#d0d6e3}
.btn-light:hover{background:#f8fafc;border-color:#cbd5e1;color:#1e293b}

/* Sidebar / Drawer */
.mobile-drawer{position:fixed;left:0;top:0;bottom:0;width:300px;background:#ffffff;transform:translateX(-100%);transition:transform .3s cubic-bezier(0.4, 0, 0.2, 1);box-shadow:none;z-index:1001;border-right:1px solid var(--line);}
.mobile-drawer.open{transform:translateX(0);box-shadow: 10px 0 25px -5px rgba(0, 0, 0, 0.1), 8px 0 10px -6px rgba(0, 0, 0, 0.1);}

/* Backdrop */
.drawer-backdrop {
    position: fixed; top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(0,0,0,0.4);
    backdrop-filter: blur(2px);
    z-index: 1000;
    opacity: 0; pointer-events: none;
    transition: opacity .3s ease;
}
.drawer-backdrop.open { opacity: 1; pointer-events: auto; }

.drawer-header {
    padding: 20px;
    border-bottom: 1px solid var(--line);
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.drawer-close {
    background: none; border: none; cursor: pointer; color: var(--muted);
}
.drawer-inner{padding:0; height: calc(100vh - 80px); overflow-y: auto;}

.drawer-nav{display:flex;flex-direction:column;padding:10px 0;}
.drawer-link{display:flex;align-items:center;gap:12px;padding:14px 24px;color:#334155;font-weight:500;text-decoration:none;transition:all .2s; border-left: 3px solid transparent;}
.drawer-link:hover{background:#f1f5f9;color:#0f172a;border-left-color:var(--brand);}
.drawer-link svg { width: 20px; height: 20px; stroke-width: 2px; color: #94a3b8; }
.drawer-link:hover svg { color: var(--brand); }

.drawer-ctas{padding:20px;border-top:1px solid var(--line); margin-top: auto;}
.btn-block{justify-content:center;width:100%;}
body.noscroll{overflow:hidden}

@media (max-width:1024px){
  /* Adjustments for tablet/mobile */
}
@media (max-width:768px){
    .brand-logo{height:40px}
    .btn-outline { display: none; } /* Hide Donate on small screens if crowded */
    .header-top { padding: 10px 0; }
}
</style>

<!-- Backdrop -->
<div id="drawerBackdrop" class="drawer-backdrop"></div>

<header class="site-header" role="banner">
  <div class="container header-top">
    <!-- Menu Toggle (Left) -->
    <button class="menu-toggle" id="menuToggle" aria-controls="mobileMenu" aria-expanded="false" aria-label="Menu">
      <svg class="icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
    </button>

    <!-- Brand (Center) -->
    <a href="<?php echo $baseUrl; ?>" class="brand">
      <img src="<?php echo htmlspecialchars($baseUrl,ENT_QUOTES,'UTF-8'); ?>../../assets/images/sidebar-logo.jpg" alt="MG Skill" class="brand-logo"/>
    </a>

    <!-- Actions (Right) -->
    <div class="actions">
      <a href="<?php echo $baseUrl; ?>../../pages/donate-now/index.php" class="btn btn-outline" style="border-color: #eab308; color: #ca8a04; background: #fefce8;">
        <svg class="icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#ca8a04" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
        <span style="margin-left:6px">DONATE</span>
      </a>
      <a href="<?php echo $baseUrl; ?>../../student" class="btn btn-light">Student Login</a>
    </div>
  </div>

  <!-- Sidebar / Drawer -->
  <div id="mobileMenu" class="mobile-drawer" role="dialog" aria-modal="true">
    <div class="drawer-header">
        <span style="font-weight:700; font-size:18px; color:#0f172a;">Menu</span>
        <button class="drawer-close" id="drawerClose">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
        </button>
    </div>
    
    <div class="drawer-inner">
      <nav class="drawer-nav" aria-label="Mobile Navigation">
        <a class="drawer-link" href="<?php echo $baseUrl; ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
            Home
        </a>
        <a class="drawer-link" href="<?php echo $baseUrl; ?>../../pages/about-us">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
            About Us
        </a>
        <a class="drawer-link" href="<?php echo $baseUrl; ?>../../pages/course-listings/">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path></svg>
            Our Courses
        </a>
        <a class="drawer-link" href="<?php echo $baseUrl; ?>../../pages/internships">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path></svg>
            Internships
        </a>
        <a class="drawer-link" href="<?php echo $baseUrl; ?>../../online-admisson">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><line x1="20" y1="8" x2="20" y2="14"></line><line x1="23" y1="11" x2="17" y2="11"></line></svg>
            Online Admission
        </a>
        <a class="drawer-link" href="<?php echo $baseUrl; ?>../../pages/join-as-a-volunteer/index.php">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
            Join Volunteer
        </a>
        <a class="drawer-link" href="<?php echo $baseUrl; ?>../../pages/gallery/">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>
            Gallery
        </a>
        <a class="drawer-link" href="<?php echo $baseUrl; ?>../../pages/blogs">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path></svg>
            Our Blogs
        </a>
      </nav>
      
      <div class="drawer-ctas">
        <a href="<?php echo $baseUrl; ?>../../student" class="btn btn-light btn-block" style="margin-bottom:10px;">Student Login</a>
        <a href="<?php echo $baseUrl; ?>../../pages/donate-now/index.php" class="btn btn-primary btn-block">Donate Now</a>
      </div>
    </div>
  </div>
</header>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var toggle = document.getElementById('menuToggle');
    var closeBtn = document.getElementById('drawerClose');
    var drawer = document.getElementById('mobileMenu');
    var backdrop = document.getElementById('drawerBackdrop');

    function openDrawer() {
        drawer.classList.add('open');
        backdrop.classList.add('open');
        toggle.setAttribute('aria-expanded', 'true');
        document.body.classList.add('noscroll');
    }

    function closeDrawer() {
        drawer.classList.remove('open');
        backdrop.classList.remove('open');
        toggle.setAttribute('aria-expanded', 'false');
        document.body.classList.remove('noscroll');
    }

    if(toggle) toggle.addEventListener('click', openDrawer);
    if(closeBtn) closeBtn.addEventListener('click', closeDrawer);
    if(backdrop) backdrop.addEventListener('click', closeDrawer);

    document.addEventListener('keydown', function(e) {
        if(e.key === 'Escape' && drawer.classList.contains('open')){
            closeDrawer();
        }
    });
});
</script>
