<?php
include __DIR__ . '/sidebar.php';
?>
<style>
:root{--active:#22c55e;--indigo:#6f75ff;--line:#e6e8ee;--text:#0b1020}
.admin-content{margin-left:260px;min-height:100vh;background:linear-gradient(180deg,#f8fafc 0%,#ffffff 100%)}
body.sidebar-collapsed .admin-content{margin-left:88px}
.admin-wrap{max-width:1200px;margin:0 auto;padding:16px}
.topbar{display:flex;align-items:center;gap:10px}
.topchip{display:inline-flex;align-items:center;gap:8px;padding:10px 12px;border-radius:12px;background:#fff;border:1px solid var(--line);color:#1a1f27}
.topchip .icon{height:18px;width:18px;stroke:#1e293b;fill:none;stroke-width:2}
.top-actions{display:flex;align-items:center;gap:10px;margin-left:auto}
.top-avatar{height:64px;width:64px;border-radius:12px;object-fit:cover;box-shadow:0 8px 18px rgba(0,0,0,.12);border:2px solid #fff}
.title{margin:8px 0 4px 0;color:#0b1020;font-size:26px;font-weight:800}
.crumbs{color:#6f7787}
.hero{display:grid;grid-template-columns:1.6fr 1fr 1fr;gap:14px;margin-top:16px}
.hero-card{border-radius:18px;background:#0b1b2e;color:#fff;padding:18px;position:relative;overflow:hidden}
.hero-card .mini{opacity:.9}
.hero-card .accent{position:absolute;right:0;bottom:0;height:180px;width:180px;border-radius:999px;background:radial-gradient(circle at 30% 30%,#22c55e 0%,transparent 60%);opacity:.35}
.stat{position:relative;overflow:hidden;border-radius:18px;background:#fff;border:1px solid var(--line);padding:16px;display:flex;align-items:center;justify-content:space-between;gap:10px;transition:transform .08s ease,box-shadow .2s ease,border-color .2s ease}
.stat .meta{display:flex;flex-direction:column}
.stat .label{color:#3a4050;font-weight:700}
.stat .value{font-size:24px;font-weight:800;color:#0b1020}
.stat .bubble{height:48px;width:48px;border-radius:14px;background:#f3f5ff;border:1px solid #e2e6f3;display:flex;align-items:center;justify-content:center;box-shadow:0 6px 14px rgba(0,0,0,.08)}
.stat .bubble .icon{height:22px;width:22px;stroke:#334155;fill:none;stroke-width:2}
.stat .decor{position:absolute;left:14px;top:12px;display:flex;gap:8px;opacity:.25}
.stat .decor span{display:block;border-radius:999px}
.stat .decor span:nth-child(1){width:22px;height:22px}
.stat .decor span:nth-child(2){width:16px;height:16px}
.stat .decor span:nth-child(3){width:10px;height:10px}
.stat::after{content:"";position:absolute;right:-24px;bottom:-24px;width:140px;height:140px;border-radius:999px;opacity:.35}
.stat:hover{box-shadow:0 12px 26px rgba(111,117,255,.18);transform:translateY(-1px)}
.stat.pink{--c:#fb7185;background:linear-gradient(135deg,#fff 0%,#fee2e8 100%);border-color:#f8ccd7}
.stat.pink .decor span{background:#fb7185}
.stat.pink::after{background:radial-gradient(circle at 50% 50%,#fb7185 0%,transparent 60%)}
.stat.green{--c:#22c55e;background:linear-gradient(135deg,#fff 0%,#d1fadf 100%);border-color:#b6f3ca}
.stat.green .decor span{background:#22c55e}
.stat.green::after{background:radial-gradient(circle at 50% 50%,#22c55e 0%,transparent 60%)}
.stat.orange{--c:#f59e0b;background:linear-gradient(135deg,#fff 0%,#fde68a 100%);border-color:#f7d76a}
.stat.orange .decor span{background:#f59e0b}
.stat.orange::after{background:radial-gradient(circle at 50% 50%,#f59e0b 0%,transparent 60%)}
.stat.teal{--c:#3b82f6;background:linear-gradient(135deg,#fff 0%,#dbeafe 100%);border-color:#c7d9fb}
.stat.teal .decor span{background:#3b82f6}
.stat.teal::after{background:radial-gradient(circle at 50% 50%,#3b82f6 0%,transparent 60%)}
.stat.indigo{--c:#6f75ff;background:linear-gradient(135deg,#fff 0%,#e0e5ff 100%);border-color:#cfd3ff}
.stat.indigo .decor span{background:#6f75ff}
.stat.indigo::after{background:radial-gradient(circle at 50% 50%,#6f75ff 0%,transparent 60%)}
.grid{display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-top:16px}
.panel{border-radius:18px;background:#fff;border:1px solid var(--line);padding:16px}
.panel h3{margin:0 0 10px 0;color:#0b1020;font-size:18px;font-weight:800}
.flex{display:flex;align-items:center;gap:10px}
.kpi{display:grid;grid-template-columns:repeat(4,1fr);gap:10px}
.kpi .card{border-radius:14px;background:#fff;border:1px solid var(--line);padding:12px}
.kpi .name{color:#3a4050;font-weight:700}
.kpi .num{color:#0b1020;font-weight:800;font-size:22px}
.bars{display:grid;grid-template-columns:repeat(12,1fr);gap:6px;align-items:end;height:120px;margin-top:10px}
.bars .bar{background:linear-gradient(180deg,#c7f3d7 0%,#22c55e 80%);border-radius:10px}
.bars .bar:nth-child(3n){background:linear-gradient(180deg,#e1e7ff 0%,#6f75ff 80%)}
.list{width:100%;border-collapse:collapse;margin-top:10px}
.list th,.list td{padding:10px;border-bottom:1px solid var(--line);text-align:left}
.badge{display:inline-flex;align-items:center;padding:6px 10px;border-radius:999px;background:#eef0ff;color:#1f2e78;font-weight:700;font-size:12px}
.cta{display:inline-flex;align-items:center;gap:8px;padding:10px 12px;border-radius:12px;background:#22c55e;color:#fff;font-weight:700;border:0}
.cta .icon{height:18px;width:18px;stroke:#fff;fill:none;stroke-width:2}
@media(max-width:1024px){.hero{grid-template-columns:1fr}.grid{grid-template-columns:1fr}.admin-content{margin-left:88px}}
</style>
<main class="admin-content">
  <div class="admin-wrap">
    <div class="topbar">
      <div class="topchip"><svg class="icon" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>WorkDo</div>
      <div class="topchip"><svg class="icon" viewBox="0 0 24 24"><rect x="4" y="4" width="16" height="16" rx="4"/></svg>Add-On Manager</div>
      <div class="top-actions">
        <img src="../assets/images/rajkumar.jpeg" alt="Rajkumar" class="top-avatar"/>
      </div>
    </div>
    <h1 class="title">Dashboard</h1>
    <div class="crumbs">Dashboard › Project</div>

    <div class="hero">
      <div class="hero-card">
        <h2 class="title" style="color:#fff">MG Education</h2>
        <p class="mini">Optimizes training with tracking, analytics and outcomes.</p>
        <div class="accent"></div>
      </div>
      <div class="stat pink">
        <div class="decor"><span></span><span></span><span></span></div>
        <div class="bubble"><svg class="icon" viewBox="0 0 24 24"><path d="M8 6h13"/><path d="M8 12h13"/><path d="M8 18h13"/><path d="M3 6h.01"/><path d="M3 12h.01"/><path d="M3 18h.01"/></svg></div>
        <div class="meta"><span class="label">Total Project</span><span class="value">12</span></div>
      </div>
      <div class="stat green">
        <div class="decor"><span></span><span></span><span></span></div>
        <div class="bubble"><svg class="icon" viewBox="0 0 24 24"><path d="M3 4h18v4H3z"/><path d="M3 12h18v8H3z"/></svg></div>
        <div class="meta"><span class="label">Total Task</span><span class="value">36</span></div>
      </div>
    </div>

    <div class="hero" style="grid-template-columns:1fr 1fr 1fr; margin-top:12px">
      <div class="stat orange">
        <div class="decor"><span></span><span></span><span></span></div>
        <div class="bubble"><svg class="icon" viewBox="0 0 24 24"><path d="M12 3v5"/><path d="M6 8l6-5 6 5"/><path d="M19 21H5V8h14z"/></svg></div>
        <div class="meta"><span class="label">Total Bug</span><span class="value">8</span></div>
      </div>
      <div class="stat teal">
        <div class="decor"><span></span><span></span><span></span></div>
        <div class="bubble"><svg class="icon" viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-8 0v2"/><circle cx="12" cy="7" r="4"/></svg></div>
        <div class="meta"><span class="label">Total User</span><span class="value">18</span></div>
      </div>
      <div class="stat indigo">
        <div class="decor"><span></span><span></span><span></span></div>
        <div class="bubble"><svg class="icon" viewBox="0 0 24 24"><path d="M3 12h18"/><path d="M12 3v18"/></svg></div>
        <div class="meta"><span class="label">Active Courses</span><span class="value">24</span></div>
      </div>
    </div>

    <div class="grid">
      <div class="panel">
        <h3>Education Analytics</h3>
        <div class="kpi">
          <div class="card"><div class="name">Enrollments</div><div class="num">1,248</div></div>
          <div class="card"><div class="name">Active Students</div><div class="num">932</div></div>
          <div class="card"><div class="name">Completion Rate</div><div class="num">78%</div></div>
          <div class="card"><div class="name">Avg Score</div><div class="num">86</div></div>
        </div>
        <div class="bars">
          <div class="bar" style="height:68px"></div>
          <div class="bar" style="height:92px"></div>
          <div class="bar" style="height:70px"></div>
          <div class="bar" style="height:110px"></div>
          <div class="bar" style="height:80px"></div>
          <div class="bar" style="height:96px"></div>
          <div class="bar" style="height:88px"></div>
          <div class="bar" style="height:120px"></div>
          <div class="bar" style="height:74px"></div>
          <div class="bar" style="height:102px"></div>
          <div class="bar" style="height:90px"></div>
          <div class="bar" style="height:116px"></div>
        </div>
      </div>
      <div class="panel">
        <h3>Course Performance</h3>
        <table class="list">
          <thead><tr><th>Course</th><th>Enrolled</th><th>Completion</th><th>Status</th></tr></thead>
          <tbody>
            <tr><td>Web Development</td><td>340</td><td>82%</td><td><span class="badge">Stable</span></td></tr>
            <tr><td>Data Analytics</td><td>290</td><td>76%</td><td><span class="badge">Improving</span></td></tr>
            <tr><td>Digital Marketing</td><td>210</td><td>68%</td><td><span class="badge">Watch</span></td></tr>
            <tr><td>UI/UX Design</td><td>188</td><td>74%</td><td><span class="badge">Stable</span></td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</main>
