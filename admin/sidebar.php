<?php
?>
<style>
:root{--pink:#fee2e8;--indigo:#6f75ff;--text:#0b1020;--line:#e6e8ee;--shadow:0 14px 28px rgba(0,0,0,.08);--active:#22c55e}
.sidebar{position:fixed;left:0;top:0;height:100vh;width:260px;background:linear-gradient(180deg,var(--pink) 0%,#f7eef2 60%,#ffffff 100%);border-right:1px solid var(--line);display:flex;flex-direction:column;color:#000;transition:width .25s ease;box-sizing:border-box;overflow:visible;z-index:1000}
.sidebar.is-collapsed{width:88px}
.sidebar-header{display:flex;align-items:center;gap:10px;padding:12px 10px;position:relative}
.brand-chip{display:flex;align-items:center;gap:0;padding:8px;border-radius:12px;background:#fff;border:1px solid var(--line);box-shadow:var(--shadow)}
.brand-logo{height:40px;width:40px;border-radius:10px;object-fit:cover}
.collapse-btn{height:36px;width:36px;border-radius:10px;border:1px solid var(--line);background:#fff;display:inline-flex;align-items:center;justify-content:center;margin-left:auto;position:absolute;right:-18px;top:50%;transform:translateY(-50%);box-shadow:var(--shadow);z-index:3}
.collapse-btn:hover{border-color:var(--active);box-shadow:0 12px 24px rgba(34,197,94,.2)}
.collapse-btn svg{height:18px;width:18px;stroke:#475569;fill:none;stroke-width:2}
.sidebar-scroll{flex:1;overflow-y:auto;overscroll-behavior:contain;-webkit-overflow-scrolling:touch;overflow-x:hidden}
.sidebar-footer{padding:12px;border-top:1px solid var(--line);background:#fff}
.nav{list-style:none;margin:0;padding:8px 10px 12px;display:grid;gap:8px}
.nav-group{margin-top:8px}
.nav-label{margin:12px 10px 6px;color:#4b5565;font-weight:700;letter-spacing:.2px}
.nav-link{display:flex;align-items:center;gap:12px;padding:12px 14px;border-radius:14px;color:#0b1020;text-decoration:none;border:1px solid #e6e8ee;background:#fff;box-shadow:0 8px 20px rgba(0,0,0,.06);transition:background .2s ease,border-color .2s ease,transform .08s ease,box-shadow .2s ease}
.nav-link:hover{background:#fff;border-color:var(--active);box-shadow:0 10px 22px rgba(34,197,94,.16);color:var(--active)}
.nav-link:hover .icon,.nav-link:hover .caret{stroke:var(--active)}
.nav-link.is-active{background:var(--active);color:#fff;border-color:transparent;box-shadow:0 12px 26px rgba(34,197,94,.25)}
.nav-link.is-active:hover{background:var(--active);color:#fff;border-color:transparent;box-shadow:0 12px 26px rgba(34,197,94,.25)}
.nav-link.is-active .icon,.nav-link.is-active .caret{stroke:#fff}
.nav-link:active{transform:scale(.98)}
.nav-link .icon{height:18px;width:18px;stroke:#1e2a3b;fill:none;stroke-width:2;flex-shrink:0}
.nav-link .text{flex:1;min-width:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;font-weight:700}
.caret{margin-left:auto;height:18px;width:18px;stroke:#4a5568;fill:none;transition:transform .2s ease}
.submenu{display:grid;gap:6px;margin:6px 0 0 0;padding-left:28px;border-left:2px dashed #e8eaf5;max-height:0;overflow:hidden;transition:max-height .25s ease}
.menu.is-open .submenu{max-height:600px}
.menu.is-open .caret{transform:rotate(90deg)}
.menu-toggle{display:flex;align-items:center;width:100%;background:transparent;border:0;padding:0}
.menu-toggle .nav-link{width:100%}
.is-collapsed .nav-label,.is-collapsed .text{display:none}
.is-collapsed .nav-link{justify-content:center}
.is-collapsed .submenu{display:none}
.is-collapsed .caret{display:none}
.is-collapsed .brand-logo{height:32px;width:32px}
::-webkit-scrollbar{width:8px}
::-webkit-scrollbar-track{background:#f3f4f7}
::-webkit-scrollbar-thumb{background:#d2d6ff;border-radius:999px}
::-webkit-scrollbar-thumb:hover{background:#b7bdff}
@media(max-width:900px){.sidebar{width:88px}}
</style>
<aside class="sidebar" role="navigation" aria-label="Admin Sidebar" id="adminSidebar">
  <div class="sidebar-header">
    <div class="brand-chip">
      <img src="../assets/images/mg-logo.jpg" alt="MG Skill" class="brand-logo"/>
    </div>
    <button class="collapse-btn" aria-label="Collapse sidebar" aria-controls="adminSidebar" aria-expanded="true" id="sidebarCollapse">
      <svg viewBox="0 0 24 24"><path d="M15 18l-6-6 6-6"/></svg>
    </button>
  </div>
  <div class="sidebar-scroll">
    <ul class="nav">
      <li>
        <a href="/admin/dashboard" class="nav-link is-active">
          <svg class="icon" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
          <span class="text">Dashboard</span>
          <svg class="caret" viewBox="0 0 24 24"><path d="M9 6l6 6-6 6"/></svg>
        </a>
      </li>

      <li class="menu">
        <button class="menu-toggle" aria-expanded="false">
          <span class="nav-link">
            <svg class="icon" viewBox="0 0 24 24"><path d="M4 4h16v4H4z"/><path d="M4 10h16v10H4z"/></svg>
            <span class="text">Courses</span>
            <svg class="caret" viewBox="0 0 24 24"><path d="M9 6l6 6-6 6"/></svg>
          </span>
        </button>
        <ul class="submenu" aria-hidden="true">
          <li><a href="/admin/courses" class="nav-link"><span class="text">All Courses</span></a></li>
          <li><a href="/admin/course-new" class="nav-link"><span class="text">Create Course</span></a></li>
          <li><a href="/admin/categories" class="nav-link"><span class="text">Categories</span></a></li>
        </ul>
      </li>

      <li class="menu">
        <button class="menu-toggle" aria-expanded="false">
          <span class="nav-link">
            <svg class="icon" viewBox="0 0 24 24"><circle cx="12" cy="7" r="3"/><path d="M5 21v-2a7 7 0 0 1 14 0v2"/></svg>
            <span class="text">Students</span>
            <svg class="caret" viewBox="0 0 24 24"><path d="M9 6l6 6-6 6"/></svg>
          </span>
        </button>
        <ul class="submenu" aria-hidden="true">
          <li><a href="/admin/students" class="nav-link"><span class="text">All Students</span></a></li>
          <li><a href="/admin/student-new" class="nav-link"><span class="text">Add Student</span></a></li>
          <li><a href="/admin/batches" class="nav-link"><span class="text">Batches</span></a></li>
        </ul>
      </li>

      <li class="menu">
        <button class="menu-toggle" aria-expanded="false">
          <span class="nav-link">
            <svg class="icon" viewBox="0 0 24 24"><path d="M4 7h16"/><path d="M4 12h16"/><path d="M4 17h16"/></svg>
            <span class="text">Payments</span>
            <svg class="caret" viewBox="0 0 24 24"><path d="M9 6l6 6-6 6"/></svg>
          </span>
        </button>
        <ul class="submenu" aria-hidden="true">
          <li><a href="/admin/payments" class="nav-link"><span class="text">Transactions</span></a></li>
          <li><a href="/admin/invoices" class="nav-link"><span class="text">Invoices</span></a></li>
          <li><a href="/admin/refunds" class="nav-link"><span class="text">Refunds</span></a></li>
        </ul>
      </li>

      <li class="menu">
        <button class="menu-toggle" aria-expanded="false">
          <span class="nav-link">
            <svg class="icon" viewBox="0 0 24 24"><path d="M3 12h18"/><path d="M12 3v18"/></svg>
            <span class="text">Reports</span>
            <svg class="caret" viewBox="0 0 24 24"><path d="M9 6l6 6-6 6"/></svg>
          </span>
        </button>
        <ul class="submenu" aria-hidden="true">
          <li><a href="/admin/reports-sales" class="nav-link"><span class="text">Sales</span></a></li>
          <li><a href="/admin/reports-students" class="nav-link"><span class="text">Student Growth</span></a></li>
          <li><a href="/admin/reports-courses" class="nav-link"><span class="text">Course Performance</span></a></li>
        </ul>
      </li>

      <li class="menu">
        <button class="menu-toggle" aria-expanded="false">
          <span class="nav-link">
            <svg class="icon" viewBox="0 0 24 24"><path d="M12 2l9 9-9 9-9-9 9-9z"/></svg>
            <span class="text">Settings</span>
            <svg class="caret" viewBox="0 0 24 24"><path d="M9 6l6 6-6 6"/></svg>
          </span>
        </button>
        <ul class="submenu" aria-hidden="true">
          <li><a href="/admin/profile" class="nav-link"><span class="text">Profile</span></a></li>
          <li><a href="/admin/roles" class="nav-link"><span class="text">Roles & Permissions</span></a></li>
          <li><a href="/admin/preferences" class="nav-link"><span class="text">Preferences</span></a></li>
        </ul>
      </li>
    </ul>
  </div>
  <div class="sidebar-footer">
    <a href="/logout" class="nav-link" style="padding:10px 12px;background:linear-gradient(90deg,#ffffff 0%,var(--indigo) 100%);color:#fff;border:1px solid #cdd0ff">
      <svg class="icon" viewBox="0 0 24 24"><path d="M9 3h6v4"/><path d="M9 21h6v-4"/><path d="M16 12H3"/><path d="M12 8l4 4-4 4"/></svg>
      <span class="text">Logout</span>
    </a>
  </div>
</aside>
<script>
var s=document.getElementById('adminSidebar');
var b=document.getElementById('sidebarCollapse');
if(s&&b){
  b.addEventListener('click',function(){
    var c=s.classList.toggle('is-collapsed');
    b.setAttribute('aria-expanded',c?'false':'true');
    b.innerHTML=c?'<svg viewBox="0 0 24 24"><path d="M9 18l6-6-6-6"/></svg>':'<svg viewBox="0 0 24 24"><path d="M15 18l-6-6 6-6"/></svg>';
    document.body.classList.toggle('sidebar-collapsed',c);
  });
}
var menus=document.querySelectorAll('.menu');
menus.forEach(function(m){
  var t=m.querySelector('.menu-toggle');
  var sbox=m.querySelector('.submenu');
  if(t&&sbox){
    t.addEventListener('click',function(){
      var open=m.classList.toggle('is-open');
      t.setAttribute('aria-expanded',open?'true':'false');
      sbox.setAttribute('aria-hidden',open?'false':'true');
    });
  }
});
</script>
