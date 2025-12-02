<?php
?>
<style>
.courses{background:#fff}
.courses-wrap{max-width:1200px;margin:0 auto;padding:16px 16px 18px}
.courses-head{display:flex;align-items:flex-end;justify-content:space-between;gap:10px;margin-bottom:12px}
.courses-title{margin:0;color:#0b1020;line-height:1.06;font-size:28px;font-weight:800}
.accent-gradient{background:linear-gradient(90deg,#1358db 0%,#5b61ff 60%,#7c83ff 100%);-webkit-background-clip:text;background-clip:text;color:transparent}
.courses-cta{display:inline-flex;align-items:center;gap:8px;padding:8px 12px;border-radius:12px;background:#0f1419;color:#fff;font-weight:700}
.courses-shell{position:relative}
.courses-scroll{overflow:hidden}
.courses-track{display:grid;grid-auto-flow:column;--gap:12px;gap:var(--gap);--cols:6;grid-auto-columns:minmax(260px, calc((100% - (var(--cols) - 1) * var(--gap)) / var(--cols)));scroll-snap-type:x mandatory}
.course-card{scroll-snap-align:start;background:#fff;border:1px solid #e6e8ee;border-radius:14px;overflow:hidden;display:flex;flex-direction:column}
.ci-img{height:120px;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:800}
.ci-a{background:linear-gradient(135deg,#0ea5e9 0%,#6366f1 100%)}
.ci-b{background:linear-gradient(135deg,#10b981 0%,#22c55e 100%)}
.ci-c{background:linear-gradient(135deg,#f59e0b 0%,#b2560a 100%)}
.ci-d{background:linear-gradient(135deg,#ef4444 0%,#f97316 100%)}
.ci-e{background:linear-gradient(135deg,#7c83ff 0%,#5b61ff 100%)}
.ci-f{background:linear-gradient(135deg,#14b8a6 0%,#06b6d4 100%)}
.ci-g{background:linear-gradient(135deg,#a855f7 0%,#8b5cf6 100%)}
.ci-h{background:linear-gradient(135deg,#374151 0%,#1f2937 100%)}
.course-body{padding:12px}
.course-title{margin:0 0 6px 0;color:#0b1020;font-size:17px;font-weight:800;line-height:1.2;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
.course-author{margin:0;color:#667285;font-size:13px}
.course-meta{display:flex;align-items:center;gap:10px;margin-top:8px;color:#2b313b;font-weight:600;font-size:13px;flex-wrap:wrap}
.star{display:inline-flex;align-items:center;gap:4px;color:#0a50c9}
.star svg{height:14px;width:14px;fill:#f59e0b}
.star span{font-weight:800;color:#0b1020}
.badge{display:inline-flex;align-items:center;padding:4px 8px;border-radius:999px;background:#e8f0ff;color:#1e3a8a;font-weight:700;font-size:12px}
.rating-count{color:#8b94a7;font-weight:600}
.price-box{display:inline-flex;align-items:center;gap:6px;margin-top:6px;order:-1;width:100%;justify-content:flex-start}
.price{color:#0b1020;font-weight:800;font-size:16px}
.mrp{color:#8b94a7;text-decoration:line-through;font-weight:700}
.nav{position:absolute;right:8px;top:50%;transform:translateY(-50%);display:flex;gap:8px;z-index:2}
.bubble{height:34px;width:34px;border-radius:999px;background:#fff;border:1px solid #e1e6f0;display:flex;align-items:center;justify-content:center}
.bubble svg{height:16px;width:16px;stroke:#475569;fill:none;stroke-width:2}
@media(max-width:1024px){.courses-track{--cols:5}}
@media(max-width:900px){.courses-title{font-size:24px}.courses-track{--cols:4}}
@media(max-width:640px){.courses-wrap{padding:12px 12px 16px}.courses-track{--cols:2}.ci-img{height:90px}.rating-count{display:none}}
</style>
<section class="courses" aria-label="Courses">
  <div class="courses-wrap">
    <div class="courses-head">
      <h3 class="courses-title"><span class="strong accent-gradient">Top Courses</span> to grow your career</h3>
      <a class="courses-cta" href="/courses">Explore All</a>
    </div>
    <div class="courses-shell">
      <div class="courses-scroll" id="coursesScroll">
        <div class="courses-track" id="coursesTrack">
          <div class="course-card"><div class="ci-img ci-a">AI</div><div class="course-body"><p class="course-title">AI Engineer Bootcamp</p><p class="course-author">365 Careers</p><div class="course-meta"><span class="badge">Bestseller</span><span class="star"><svg viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg><span>4.6</span></span><span class="rating-count">12,286 ratings</span><span class="price-box"><span class="price">₹519</span><span class="mrp">₹3,009</span></span></div></div></div>
          <div class="course-card"><div class="ci-img ci-b">AUTOMATION</div><div class="course-body"><p class="course-title">Agentic AI with n8n</p><p class="course-author">KRISHAI Tech</p><div class="course-meta"><span class="badge">Trending</span><span class="star"><svg viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg><span>4.7</span></span><span class="rating-count">107 ratings</span><span class="price-box"><span class="price">₹519</span><span class="mrp">₹3,009</span></span></div></div></div>
          <div class="course-card"><div class="ci-img ci-c">SALESFORCE</div><div class="course-body"><p class="course-title">AI Powered Salesforce Dev</p><p class="course-author">Matt Gerry</p><div class="course-meta"><span class="badge">Hot</span><span class="star"><svg viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg><span>4.8</span></span><span class="rating-count">94 ratings</span><span class="price-box"><span class="price">₹519</span><span class="mrp">₹1,769</span></span></div></div></div>
          <div class="course-card"><div class="ci-img ci-d">AGENTS</div><div class="course-body"><p class="course-title">Intro to AI Agents</p><p class="course-author">365 Careers</p><div class="course-meta"><span class="badge">New</span><span class="star"><svg viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg><span>4.5</span></span><span class="rating-count">2,597 ratings</span><span class="price-box"><span class="price">₹519</span><span class="mrp">₹1,709</span></span></div></div></div>
          <div class="course-card"><div class="ci-img ci-e">WEB</div><div class="course-body"><p class="course-title">Complete Web Dev 2025</p><p class="course-author">MG Skill</p><div class="course-meta"><span class="badge">Bestseller</span><span class="star"><svg viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg><span>4.7</span></span><span class="rating-count">12,001 ratings</span><span class="price-box"><span class="price">₹519</span><span class="mrp">₹3,009</span></span></div></div></div>
          <div class="course-card"><div class="ci-img ci-f">REACT</div><div class="course-body"><p class="course-title">React & Next.js Pro</p><p class="course-author">MG Skill</p><div class="course-meta"><span class="badge">Popular</span><span class="star"><svg viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg><span>4.8</span></span><span class="rating-count">8,431 ratings</span><span class="price-box"><span class="price">₹519</span><span class="mrp">₹3,009</span></span></div></div></div>
          <div class="course-card"><div class="ci-img ci-g">TAILWIND</div><div class="course-body"><p class="course-title">Tailwind CSS Mastery</p><p class="course-author">MG Skill</p><div class="course-meta"><span class="badge">Hot</span><span class="star"><svg viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg><span>4.7</span></span><span class="rating-count">7,315 ratings</span><span class="price-box"><span class="price">₹519</span><span class="mrp">₹3,009</span></span></div></div></div>
          <div class="course-card"><div class="ci-img ci-h">SQL</div><div class="course-body"><p class="course-title">SQL & Databases</p><p class="course-author">MG Skill</p><div class="course-meta"><span class="badge">Essential</span><span class="star"><svg viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg><span>4.6</span></span><span class="rating-count">3,215 ratings</span><span class="price-box"><span class="price">₹519</span><span class="mrp">₹3,009</span></span></div></div></div>
          <div class="course-card"><div class="ci-img ci-a">MONGO</div><div class="course-body"><p class="course-title">MongoDB Essentials</p><p class="course-author">MG Skill</p><div class="course-meta"><span class="badge">Essential</span><span class="star"><svg viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg><span>4.6</span></span><span class="rating-count">1,204 ratings</span><span class="price-box"><span class="price">₹519</span><span class="mrp">₹3,009</span></span></div></div></div>
          <div class="course-card"><div class="ci-img ci-b">GIT</div><div class="course-body"><p class="course-title">Git & GitHub</p><p class="course-author">MG Skill</p><div class="course-meta"><span class="badge">Popular</span><span class="star"><svg viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg><span>4.7</span></span><span class="rating-count">7,821 ratings</span><span class="price-box"><span class="price">₹519</span><span class="mrp">₹3,009</span></span></div></div></div>
          <div class="course-card"><div class="ci-img ci-c">NODE</div><div class="course-body"><p class="course-title">Node.js Complete Guide</p><p class="course-author">MG Skill</p><div class="course-meta"><span class="badge">Hot</span><span class="star"><svg viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg><span>4.7</span></span><span class="rating-count">9,018 ratings</span><span class="price-box"><span class="price">₹519</span><span class="mrp">₹3,009</span></span></div></div></div>
          <div class="course-card"><div class="ci-img ci-d">DATA</div><div class="course-body"><p class="course-title">Data Science Crash Course</p><p class="course-author">MG Skill</p><div class="course-meta"><span class="badge">Bestseller</span><span class="star"><svg viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg><span>4.8</span></span><span class="rating-count">3,420 ratings</span><span class="price-box"><span class="price">₹519</span><span class="mrp">₹3,009</span></span></div></div></div>
          <div class="course-card"><div class="ci-img ci-e">SECURITY</div><div class="course-body"><p class="course-title">Cyber Security Basics</p><p class="course-author">MG Skill</p><div class="course-meta"><span class="badge">New</span><span class="star"><svg viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg><span>4.5</span></span><span class="rating-count">2,105 ratings</span><span class="price-box"><span class="price">₹519</span><span class="mrp">₹3,009</span></span></div></div></div>
          <div class="course-card"><div class="ci-img ci-f">UI/UX</div><div class="course-body"><p class="course-title">UI/UX Design Foundations</p><p class="course-author">MG Skill</p><div class="course-meta"><span class="badge">Popular</span><span class="star"><svg viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg><span>4.7</span></span><span class="rating-count">1,980 ratings</span><span class="price-box"><span class="price">₹519</span><span class="mrp">₹3,009</span></span></div></div></div>
          <div class="course-card"><div class="ci-img ci-g">NGINX</div><div class="course-body"><p class="course-title">Nginx for Beginners</p><p class="course-author">MG Skill</p><div class="course-meta"><span class="badge">Essential</span><span class="star"><svg viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg><span>4.6</span></span><span class="rating-count">870 ratings</span><span class="price-box"><span class="price">₹519</span><span class="mrp">₹3,009</span></span></div></div></div>
          <div class="course-card"><div class="ci-img ci-h">JENKINS</div><div class="course-body"><p class="course-title">Jenkins CI/CD</p><p class="course-author">MG Skill</p><div class="course-meta"><span class="badge">Essential</span><span class="star"><svg viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg><span>4.6</span></span><span class="rating-count">950 ratings</span><span class="price-box"><span class="price">₹519</span><span class="mrp">₹3,009</span></span></div></div></div>
          <div class="course-card"><div class="ci-img ci-a">TS</div><div class="course-body"><p class="course-title">TypeScript Deep Dive</p><p class="course-author">MG Skill</p><div class="course-meta"><span class="badge">Hot</span><span class="star"><svg viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg><span>4.7</span></span><span class="rating-count">1,750 ratings</span><span class="price-box"><span class="price">₹519</span><span class="mrp">₹3,009</span></span></div></div></div>
          <div class="course-card"><div class="ci-img ci-b">EXPRESS</div><div class="course-body"><p class="course-title">Express.js Practical</p><p class="course-author">MG Skill</p><div class="course-meta"><span class="badge">Popular</span><span class="star"><svg viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg><span>4.7</span></span><span class="rating-count">1,230 ratings</span><span class="price-box"><span class="price">₹519</span><span class="mrp">₹3,009</span></span></div></div></div>
          <div class="course-card"><div class="ci-img ci-c">REDUX</div><div class="course-body"><p class="course-title">Redux Toolkit</p><p class="course-author">MG Skill</p><div class="course-meta"><span class="badge">Trending</span><span class="star"><svg viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg><span>4.6</span></span><span class="rating-count">980 ratings</span><span class="price-box"><span class="price">₹519</span><span class="mrp">₹3,009</span></span></div></div></div>
          <div class="course-card"><div class="ci-img ci-d">AWS</div><div class="course-body"><p class="course-title">AWS Practitioner</p><p class="course-author">MG Skill</p><div class="course-meta"><span class="badge">Hot</span><span class="star"><svg viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg><span>4.7</span></span><span class="rating-count">2,300 ratings</span><span class="price-box"><span class="price">₹519</span><span class="mrp">₹3,009</span></span></div></div></div>
          <div class="course-card"><div class="ci-img ci-e">DOCKER</div><div class="course-body"><p class="course-title">Docker & Kubernetes</p><p class="course-author">MG Skill</p><div class="course-meta"><span class="badge">Bestseller</span><span class="star"><svg viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg><span>4.8</span></span><span class="rating-count">4,850 ratings</span><span class="price-box"><span class="price">₹519</span><span class="mrp">₹3,009</span></span></div></div></div>
        </div>
      </div>
      <div class="nav">
        <button class="bubble prev" aria-label="Previous"><svg viewBox="0 0 24 24"><path d="M15 6l-6 6 6 6"/></svg></button>
        <button class="bubble next" aria-label="Next"><svg viewBox="0 0 24 24"><path d="M9 6l6 6-6 6"/></svg></button>
      </div>
    </div>
  </div>
</section>
<script>
(function(){
  var sc=document.getElementById('coursesScroll');
  var prev=document.querySelector('.courses .prev');
  var next=document.querySelector('.courses .next');
  function by(dir){if(!sc)return;sc.scrollBy({left:dir*sc.clientWidth,behavior:'smooth'})}
  if(prev)prev.addEventListener('click',function(){by(-1)});
  if(next)next.addEventListener('click',function(){by(1)});
})();
</script>
