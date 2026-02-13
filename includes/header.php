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
.header-top{display:flex;align-items:center;gap:12px;padding:8px 0;position:relative} /* Added relative for absolute positioning of logo on mobile */
.menu-toggle{display:none;border:none;background:transparent;height:40px;width:40px;border-radius:50%;align-items:center;justify-content:center;cursor:pointer;transition:background .2s}
.menu-toggle:hover {background: var(--chip);}
.brand{display:flex;align-items:center;gap:10px;color:#121212;font-weight:700;flex-shrink:0}
.brand-logo{height:64px;width:auto;border-radius:8px;display:block}
.search-wrap{flex:1;min-width:220px}
.search-form{display:flex;align-items:center;gap:10px;background:#f7f8fa;border:1px solid var(--line);border-radius:14px;padding:6px 10px}
.search-input{flex:1;border:0;outline:none;background:transparent;font-size:16px;color:#0f1419}
.search-btn{height:36px;width:36px;border-radius:10px;border:1px solid var(--line);background:var(--bg);display:inline-flex;align-items:center;justify-content:center}
.icon{height:18px;width:18px;fill:none;stroke:#4a5568;stroke-width:2}
.actions{display:flex;align-items:center;gap:8px;margin-left:auto}
.btn{display:inline-flex;align-items:center;gap:8px;padding:8px 12px;border-radius:10px;text-decoration:none;font-weight:600;line-height:1;border:1px solid transparent;white-space:nowrap}
.btn-outline{border-color:#cfe0ff;color:#0a50c9;background:#f8fbff}
.btn-primary{background:var(--accent);color:#fff;border-color:transparent}
.btn-light{background:#fff;color:#5b616e;border-color:#d0d6e3}
 
.header-nav{display:flex;align-items:center;gap:18px;padding:10px 0;border-top:1px solid var(--line);overflow-x:auto}
.nav-link{color:#2b313b;font-weight:600;white-space:nowrap;text-decoration:none}
.pill{display:inline-flex;align-items:center;gap:8px;padding:8px 12px;border-radius:999px;background:#eef6ff;color:#0a50c9;font-weight:700}
.pill-badge{margin-left:6px;background:#a80028;color:#fff;border-radius:999px;padding:3px 8px;font-size:12px}
.more-btn{height:34px;width:34px;border:1px solid var(--line);border-radius:10px;background:#fff;display:inline-flex;align-items:center;justify-content:center}

/* Mobile Drawer (Sidebar) Styling */
.mobile-drawer {
    position: fixed;
    top: 0;
    left: 0;
    bottom: 0;
    width: 280px; /* Sidebar width */
    background: #fff;
    transform: translateX(-100%); /* Start hidden to the left */
    transition: transform 0.3s cubic-bezier(0.4, 0.0, 0.2, 1);
    box-shadow: 4px 0 24px rgba(0,0,0,0.15);
    z-index: 1200; /* Increased to be above header */
    overflow-y: auto;
    display: flex;
    flex-direction: column;
}

.mobile-drawer.open {
    transform: translateX(0);
}

/* Backdrop for styling */
.drawer-backdrop {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.4);
    z-index: 1100; /* Increased to be above header */
    opacity: 0;
    visibility: hidden;
    transition: opacity 0.3s;
}

.drawer-backdrop.open {
    opacity: 1;
    visibility: visible;
}

.drawer-header {
    padding: 20px 16px;
    border-bottom: 1px solid var(--line);
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.drawer-brand {
    height: 40px;
}

.close-drawer {
    background: transparent;
    border: none;
    padding: 8px;
    cursor: pointer;
}

.drawer-inner{padding:16px; flex: 1;}

.drawer-nav{display:flex;flex-direction:column;gap:8px;margin-top:12px}
.drawer-link{display:block;padding:12px;border:none;border-radius:8px;color:#1a1f27;font-weight:600;background:transparent;transition:background 0.2s}
.drawer-link:hover {background: var(--chip);}

.drawer-ctas{display:flex;flex-direction:column;gap:12px;margin-top:24px;padding-top:20px;border-top:1px solid var(--line)}

body.noscroll{overflow:hidden}

@media (max-width:1024px){
  .menu-toggle{display:inline-flex}
  .actions .btn-outline{display:none} /* Hide donate on tablet/mobile */
  .header-nav{display:none}
}
@media (max-width:768px){
  .header-top{
    justify-content: space-between; /* Space out menu and potential right items */
    padding: 12px 0;
  }
  .menu-toggle {
    order: 1; /* First item */
  }
  .brand {
    position: absolute;
    left: 50%;
    transform: translateX(-50%); /* Perfectly centered */
    width: auto;
    justify-content: center;
  }
  .brand-logo{height:40px}
  .search-wrap{display:none}
  
  /* Hide specific buttons as requested */
  .actions {
     display: none; /* Hide all action buttons on mobile to clear space for centered logo */
  }
  /* If we need to keep something on the right to balance visual weight, we can add it later. 
     For now, clean header with menu left and logo center. */
}
</style>
<header class="site-header" role="banner">
  <div class="container header-top">
    <button class="menu-toggle" aria-controls="mobileMenu" aria-expanded="false" aria-label="Menu">
      <svg class="icon" viewBox="0 0 24 24"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
    </button>
    <a href="/" class="brand">
      <img src="<?php echo htmlspecialchars($baseUrl,ENT_QUOTES,'UTF-8'); ?>../../assets/images/sidebar-logo.jpg" alt="MG Skill" class="brand-logo"/>
    </a>

    <div class="actions">
      <a href="../../pages/donate-now/index.php" class="btn btn-outline" style="border-color: #eab308; color: #ca8a04; background: #fefce8;">
        <svg class="icon" viewBox="0 0 24 24" style="stroke: #ca8a04;"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
        DONATE NOW
      </a>
      <a href="../../pages/join-as-a-volunteer" class="btn btn-primary">REGISTER</a>
      <a href="../../admin" class="btn btn-light">Admin Login</a>
      <a href="../../center" class="btn btn-light">Center Login</a>
      <a href="../../student" class=" btn btn-light"> Student Login</a>
    </div>
  </div>
  <div class="container header-nav" role="navigation" aria-label="Secondary">
    <a href="../../pages/join-as-a-volunteer/index.php" class="nav-link">Join As a Volunteer</a>
    <a href="../../online-admisson" class="nav-link">Online Admission</a>
    <a href="../../pages/course-listings/" class="nav-link">Our Courses</a>
    <a href="../../pages/gallery/" class="nav-link">Gallery</a>
    <a href="../../pages/blogs" class="nav-link">Our Blogs</a>
    <a href="../../internship-enrollment" class="nav-link">Internship Admission</a>
    <!-- <a href="/skill-centre" class="nav-link">Skill Centre</a> -->
    <!-- <a href="/soar" class="nav-link pill">MG EDU AI<span class="pill-badge">New</span></a> -->
  </div>
  <div id="drawerBackdrop" class="drawer-backdrop"></div>
  <div id="mobileMenu" class="mobile-drawer" role="dialog" aria-modal="true">
    <div class="drawer-header">
       <a href="/" class="brand">
          <img src="<?php echo htmlspecialchars($baseUrl,ENT_QUOTES,'UTF-8'); ?>../../assets/images/sidebar-logo.jpg" alt="MG Skill" class="brand-logo drawer-brand"/>
       </a>
       <button class="close-drawer" id="closeDrawerBtn" aria-label="Close Menu">
          <svg class="icon" viewBox="0 0 24 24"><path d="M18 6L6 18M6 6l12 12"/></svg>
       </button>
    </div>
    <div class="drawer-inner">
      <form class="search-form" action="/search" method="get">
        <svg class="icon" viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
        <input class="search-input" type="search" name="q" placeholder="Search Skill Courses" aria-label="Search Skill Courses"/>
        <button class="search-btn" type="submit">
          <svg class="icon" viewBox="0 0 24 24"><path d="M5 12l14 0"/><path d="M13 5l7 7-7 7"/></svg>
        </button>
      </form>
      <nav class="drawer-nav" aria-label="Mobile Navigation">
        <a class="drawer-link" href="/dashboards">Dashboards</a>
        <a class="drawer-link" href="../../pages/join-as-a-volunteer/index.php">Join As a Volunteer</a>
        <a class="drawer-link" href="../../online-admisson">Online Admission</a>
        <a class="drawer-link" href="../../internship-enrollment">Internship Enrollment</a>
        <a class="drawer-link" href="../../pages/course-listings/">Our Courses</a>
        <a class="drawer-link" href="../../pages/Internships">Internships</a>
        <a class="drawer-link" href="../../pages/gallery/">Gallery</a>
      </nav>
      <div class="drawer-ctas">
        <!-- These buttons are in the drawer for mobile access since they are hidden in header -->
        <a href="../../pages/join-as-a-volunteer" class="btn btn-primary btn-block">REGISTER</a>
        <a href="../../admin" class="btn btn-light btn-block">Admin Login</a>
        <a href="../../center" class="btn btn-light btn-block">Center Login</a>
        <a href="https://mgedu.in/student" class="btn btn-light btn-block">Student Login</a>
      </div>
    </div>
  </div>
</header>
<script>
document.addEventListener('DOMContentLoaded', function() {
  var toggle = document.querySelector('.menu-toggle');
  var drawer = document.getElementById('mobileMenu');
  var backdrop = document.getElementById('drawerBackdrop');
  var closeBtn = document.getElementById('closeDrawerBtn');
  var body = document.body;

  function openDrawer() {
    drawer.classList.add('open');
    backdrop.classList.add('open');
    body.classList.add('noscroll');
    toggle.setAttribute('aria-expanded', 'true');
  }

  function closeDrawer() {
    drawer.classList.remove('open');
    backdrop.classList.remove('open');
    body.classList.remove('noscroll');
    toggle.setAttribute('aria-expanded', 'false');
  }

  if(toggle && drawer && backdrop && closeBtn){
    toggle.addEventListener('click', openDrawer);
    closeBtn.addEventListener('click', closeDrawer);
    backdrop.addEventListener('click', closeDrawer);

    // Close on Escape key
    document.addEventListener('keydown', function(e){
      if(e.key === 'Escape' && drawer.classList.contains('open')){
        closeDrawer();
      }
    });
  }
});
</script>
