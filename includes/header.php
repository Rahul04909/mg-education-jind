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
.header-top{display:flex;align-items:center;gap:12px;padding:8px 0}
.menu-toggle{display:none;border:1px solid var(--line);background:var(--bg);height:40px;width:40px;border-radius:10px;align-items:center;justify-content:center}
.brand{display:flex;align-items:center;gap:10px;color:#121212;font-weight:700;flex-shrink:0}
.brand-logo{height:64px;width:auto;border-radius:8px;display:block}
.search-wrap{flex:1;min-width:220px}
.search-form{display:flex;align-items:center;gap:10px;background:#f7f8fa;border:1px solid var(--line);border-radius:14px;padding:6px 10px}
.search-input{flex:1;border:0;outline:none;background:transparent;font-size:16px;color:#0f1419}
.search-btn{height:36px;width:36px;border-radius:10px;border:1px solid var(--line);background:var(--bg);display:inline-flex;align-items:center;justify-content:center}
.icon{height:18px;width:18px;fill:none;stroke:#4a5568;stroke-width:2}
.actions{display:flex;align-items:center;gap:8px;margin-left:auto}
.btn{display:inline-flex;align-items:center;gap:8px;padding:8px 12px;border-radius:10px;text-decoration:none;font-weight:600;line-height:1;border:1px solid transparent}
.btn-outline{border-color:#cfe0ff;color:#0a50c9;background:#f8fbff}
.btn-primary{background:var(--accent);color:#fff;border-color:transparent}
.btn-light{background:#fff;color:#5b616e;border-color:#d0d6e3}
 
.header-nav{display:flex;align-items:center;gap:18px;padding:10px 0;border-top:1px solid var(--line);overflow-x:auto}
.nav-link{color:#2b313b;font-weight:600;white-space:nowrap}
.pill{display:inline-flex;align-items:center;gap:8px;padding:8px 12px;border-radius:999px;background:#eef6ff;color:#0a50c9;font-weight:700}
.pill-badge{margin-left:6px;background:#a80028;color:#fff;border-radius:999px;padding:3px 8px;font-size:12px}
.more-btn{height:34px;width:34px;border:1px solid var(--line);border-radius:10px;background:#fff;display:inline-flex;align-items:center;justify-content:center}
.mobile-drawer{position:fixed;left:0;right:0;top:0;background:#fff;transform:translateY(-120%);transition:transform .25s ease;box-shadow:0 24px 40px rgba(0,0,0,.12);z-index:999}
.mobile-drawer.open{transform:translateY(0)}
.drawer-inner{padding:16px 16px 20px}
.drawer-nav{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:12px}
.drawer-link{display:block;padding:12px;border:1px solid var(--line);border-radius:12px;color:#1a1f27;font-weight:600;background:#fff}
.drawer-ctas{display:flex;gap:10px;margin-top:14px}
.btn-block{justify-content:center;flex:1}
body.noscroll{overflow:hidden}
@media (max-width:1024px){
  .menu-toggle{display:inline-flex}
  .actions .btn-outline{display:none}
  .header-nav{display:none}
}
@media (max-width:768px){
  .header-top{gap:10px}
  .search-wrap{display:none}
  .actions{order:2}
  .brand-logo{height:40px}
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
      <a href="/register" class="btn btn-primary">REGISTER</a>
      <a href="/login" class="btn btn-light">LOGIN</a>
    </div>
  </div>
  <div class="container header-nav" role="navigation" aria-label="Secondary">
    <a href="../../pages/join-as-a-volunteer/index.php" class="nav-link">Join As a Volunteer</a>
    <a href="../../online-admisson" class="nav-link">Online Admission</a>
    <a href="/recommendations" class="nav-link">Recommendation</a>
    <a href="/courses" class="nav-link">Skill Courses</a>
    <a href="/jobs" class="nav-link">Job Exchange</a>
    <a href="/skill-centre" class="nav-link">Skill Centre</a>
    <a href="/soar" class="nav-link pill">MG EDU AI<span class="pill-badge">New</span></a>
  </div>
  <div id="mobileMenu" class="mobile-drawer" role="dialog" aria-modal="true">
    <div class="drawer-inner container">
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
        <a class="drawer-link" href="/recommendations">Recommendation</a>
        <a class="drawer-link" href="/courses">Skill Courses</a>
        <a class="drawer-link" href="/jobs">Job Exchange</a>
        <a class="drawer-link" href="/skill-centre">Skill Centre</a>
        <a class="drawer-link" href="/soar">AI SOAR</a>
      </nav>
      <div class="drawer-ctas">
        <a href="../../pages/join-as-a-volunteer" class="btn btn-primary btn-block">REGISTER</a>
        <a href="https://mgedu.in/admin" class="btn btn-light btn-block">Admin Login</a>
        <a href="https://mgedu.in/student" class="btn btn-light btn-block">Student Login</a>
        <a href="https://mgedu.in/center" class="btn btn-light btn-block">Franchise Login</a>
      </div>
    </div>
  </div>
</header>
<script>
var t=document.querySelector('.menu-toggle');
var m=document.getElementById('mobileMenu');
if(t&&m){
  t.addEventListener('click',function(){
    var o=m.classList.toggle('open');
    t.setAttribute('aria-expanded',o?'true':'false');
    document.body.classList.toggle('noscroll',o);
  });
  document.addEventListener('keydown',function(e){
    if(e.key==='Escape'&&m.classList.contains('open')){
      m.classList.remove('open');
      t.setAttribute('aria-expanded','false');
      document.body.classList.remove('noscroll');
    }
  });
  document.addEventListener('click',function(e){
    if(m.classList.contains('open')&&!m.contains(e.target)&&!t.contains(e.target)){
      m.classList.remove('open');
      t.setAttribute('aria-expanded','false');
      document.body.classList.remove('noscroll');
    }
  });
}
</script>
