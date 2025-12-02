<?php
?>
<style>
.intern{background:#fff;}
.intern-wrap{max-width:1200px; margin:0 auto;padding:12px 16px 18px}
.intern-card{position:relative;border-radius:28px;overflow:hidden;background:#f9fbff;min-height:420px}
.intern-bg{position:absolute;inset:0;background:url('assets/images/placement-banner.webp') center/cover no-repeat;filter:saturate(1.05)}
.intern-overlay{position:absolute;inset:0;background:none}
.intern-content{position:absolute;left:50%;top:26px;transform:translateX(-50%);width:92%;text-align:center}
.eyebrow2{color:#d41e5b;font-weight:800;margin:0}
.intern-title{margin:8px 0 12px 0;color:#0b1020;font-size:36px;line-height:1.1;font-weight:800}
.intern-actions{display:flex;gap:14px;justify-content:center}
.btn-primary{display:inline-flex;align-items:center;gap:8px;padding:12px 16px;border-radius:14px;background:linear-gradient(90deg,#1358db 0%,#5b61ff 60%,#7c83ff 100%);color:#fff;font-weight:800}
.btn-secondary{display:inline-flex;align-items:center;gap:8px;padding:12px 16px;border-radius:14px;background:#fff;color:#d41e5b;font-weight:800;border:1px solid #ffd1df}
@media(max-width:900px){.intern-title{font-size:30px}.intern-card{min-height:360px}}
@media(max-width:640px){.intern-title{font-size:24px}.intern-actions{flex-direction:column}.intern-card{min-height:340px}}
</style>
<section class="intern" aria-label="Internship banner">
  <div class="intern-wrap">
    <div class="intern-card">
      <div class="intern-bg"></div>
      <div class="intern-overlay"></div>
      <div class="intern-content">
        <p class="eyebrow2">100% Job Assurance with MG Career Track</p>
        <h3 class="intern-title">Take a Moonshot at your Tech Career</h3>
        <div class="intern-actions">
          <a class="btn-primary" href="/internships">Explore Internships</a>
          <a class="btn-secondary" href="/counsellor">Talk to Expert Counsellor</a>
        </div>
      </div>
    </div>
  </div>
</section>
