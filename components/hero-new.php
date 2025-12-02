<?php
?>
<style>
.hero2{background:#fff}
.hero2-wrap{max-width:1200px;margin:0 auto;padding:14px 16px 18px}
.hero2-grid{display:grid;grid-template-columns:1.25fr .25fr 1fr;gap:20px;align-items:center}
.eyebrow{color:#1358db;font-weight:700;margin:0;letter-spacing:.2px}
.hero2-title{margin:6px 0 14px 0;color:#0b1020;line-height:1.06;font-size:36px}
.hero2-title .strong{font-weight:800}
.brand-callout{margin:2px 0 12px 0;color:#0b1020;line-height:1.08;font-size:28px;font-weight:800;letter-spacing:.2px}
.accent-gradient{background:linear-gradient(90deg,#1358db 0%,#5b61ff 60%,#7c83ff 100%);-webkit-background-clip:text;background-clip:text;color:transparent}
.feature{display:flex;align-items:center;gap:10px;margin:10px 0}
.chip-icon{height:36px;width:36px;border-radius:12px;background:#f3f5ff;border:1px solid #e2e6f3;display:flex;align-items:center;justify-content:center}
.chip-icon svg{height:18px;width:18px;stroke:#4a5bd5;fill:none;stroke-width:2}
.feature span{color:#3a4050;font-weight:600}
.brands{display:flex;flex-wrap:wrap;gap:10px;margin-top:8px}
.brand-chip{padding:8px 12px;border-radius:999px;border:1px solid #e1e6f0;background:#fff;color:#2b313b;font-weight:600}
.portrait{height:250px;width:132px;border-radius:24px;background:#fff;border:1px solid #e3ebf7;display:flex;align-items:center;justify-content:center;box-shadow:0 12px 24px rgba(0,0,0,.08);overflow:hidden}
.portrait img{width:100%;height:100%;object-fit:cover}
.card-slider{position:relative;overflow:hidden}
.card{background:linear-gradient(135deg,#3b0f7b 0%,#5b15bd 60%,#8b5cf6 100%);color:#fff;border-radius:18px;box-shadow:none;padding:18px;min-height:220px;display:flex;align-items:center;justify-content:space-between;gap:16px;flex:0 0 100%}
.card h3{margin:0;line-height:1.15;font-size:22px;font-weight:800}
.card p{margin:6px 0 10px 0;opacity:.92}
.cta{display:inline-flex;align-items:center;gap:8px;padding:10px 14px;border-radius:12px;background:#fff;color:#1e1b4b;font-weight:700}
.slide-media{background:#fff;border-radius:14px;padding:10px;box-shadow:0 14px 26px rgba(0,0,0,.12)}
.slide-media img{width:120px;height:auto;border-radius:8px}
.slides{display:flex;position:relative;transition:transform .35s ease;will-change:transform}
.hero-dotbar{position:absolute;left:50%;transform:translateX(-50%);bottom:12px;display:flex;gap:8px;justify-content:center}
.dot{height:8px;width:8px;border-radius:999px;background:#cfd6e8;border:0}
.dot.is-active{background:#1e40af;width:20px}
.bubble-arrows{position:absolute;right:16px;bottom:16px;display:flex;gap:10px;z-index:2}
.bubble{height:34px;width:34px;border-radius:999px;background:#fff;border:1px solid #e1e6f0;display:flex;align-items:center;justify-content:center}
.bubble svg{height:16px;width:16px;stroke:#475569;fill:none;stroke-width:2}
/* Ticker */
.ticker{position:relative;overflow:hidden;border:1px solid #e1e6f0;background:#fff;border-radius:12px;padding:8px;margin-top:10px;width:500px;max-width:100%}
.ticker-track{display:flex;gap:10px;align-items:center;min-width:max-content;animation:ticker 28s linear infinite}
.course-chip{display:inline-flex;align-items:center;gap:8px;padding:8px 12px;border-radius:999px;border:1px solid #e1e6f0;background:#fff;color:#2b313b;font-weight:700;white-space:nowrap}
.course-chip svg{height:16px;width:16px;stroke:#0a50c9;fill:none;stroke-width:2}
@keyframes ticker{0%{transform:translateX(0)}100%{transform:translateX(-50%)}}
@media(max-width:900px){.hero2-title{font-size:32px}.brand-callout{font-size:24px}}
@media(max-width:768px){
  .hero2-grid{grid-template-columns:1fr;gap:14px}
  .hero2-wrap{padding:12px 12px 16px}
  .portrait{display:none}
  .card{min-height:200px}
  .ticker-track{animation-duration:36s}
}
</style>
<section class="hero2" role="region" aria-label="Hero">
  <div class="hero2-wrap">
    <div class="hero2-grid">
      <div>
        <p class="eyebrow">Empower Your Future</p>
        <h2 class="hero2-title"><span class="strong">Learn Job‑Ready Skills</span> & <span class="strong accent-gradient">Grow Your Career</span></h2>
        <p class="brand-callout"><span class="accent-gradient">Mahatma Gandhi Education</span></p>
        <div class="feature">
          <div class="chip-icon"><svg viewBox="0 0 24 24"><path d="M12 3l8 4v5c0 5-3.5 8-8 9-4.5-1-8-4-8-9V7l8-4z"/><path d="M8 12l2 2 4-4"/></svg></div>
          <span>Government‑recognized certificates</span>
        </div>
        <div class="feature">
          <div class="chip-icon"><svg viewBox="0 0 24 24"><path d="M3 12h6l2-4 4 8 2-4h4"/></svg></div>
          <span>Hands‑on learning with mentors</span>
        </div>
        <div class="feature">
          <div class="chip-icon"><svg viewBox="0 0 24 24"><path d="M4 7h16M6 12h12M8 17h8"/></svg></div>
          <span>Placement assistance & career guidance</span>
        </div>
        <div class="ticker" aria-label="Course ticker">
          <div class="ticker-track">
            <a class="course-chip" href="/courses/web-development"><svg viewBox="0 0 24 24"><path d="M8 6l-5 6 5 6"/><path d="M16 6l5 6-5 6"/></svg>Web Development</a>
            <a class="course-chip" href="/courses/graphic-design"><svg viewBox="0 0 24 24"><path d="M12 3l8 8-8 8-8-8z"/></svg>Graphic Design</a>
            <a class="course-chip" href="/courses/data-science"><svg viewBox="0 0 24 24"><path d="M4 19h4v-6H4zM10 19h4v-10h-4zM16 19h4v-4h-4z"/></svg>Data Science</a>
            <a class="course-chip" href="/courses/cyber-security"><svg viewBox="0 0 24 24"><path d="M12 3l8 4v5c0 5-3.5 8-8 9-4.5-1-8-4-8-9V7l8-4z"/><path d="M8 12l2 2 4-4"/></svg>Cyber Security</a>
            <a class="course-chip" href="/courses/ai-ml"><svg viewBox="0 0 24 24"><path d="M12 6v12M6 12h12"/></svg>AI & ML</a>
            <a class="course-chip" href="/courses/ui-ux-design"><svg viewBox="0 0 24 24"><path d="M4 4h16v16H4z"/><path d="M9 9h6v6H9z"/></svg>UI/UX Design</a>
            <a class="course-chip" href="/courses/mobile-app"><svg viewBox="0 0 24 24"><rect x="7" y="2" width="10" height="20" rx="2"/><circle cx="12" cy="18" r="1"/></svg>Mobile App Dev</a>
            <a class="course-chip" href="/courses/cloud-computing"><svg viewBox="0 0 24 24"><path d="M7 18h10a3 3 0 0 0 0-6h-.5a5.5 5.5 0 0 0-10.7 1.5A3 3 0 0 0 7 18z"/></svg>Cloud Computing</a>
            <a class="course-chip" href="/courses/web-development"><svg viewBox="0 0 24 24"><path d="M8 6l-5 6 5 6"/><path d="M16 6l5 6-5 6"/></svg>Web Development</a>
            <a class="course-chip" href="/courses/graphic-design"><svg viewBox="0 0 24 24"><path d="M12 3l8 8-8 8-8-8z"/></svg>Graphic Design</a>
            <a class="course-chip" href="/courses/data-science"><svg viewBox="0 0 24 24"><path d="M4 19h4v-6H4zM10 19h4v-10h-4zM16 19h4v-4h-4z"/></svg>Data Science</a>
            <a class="course-chip" href="/courses/cyber-security"><svg viewBox="0 0 24 24"><path d="M12 3l8 4v5c0 5-3.5 8-8 9-4.5-1-8-4-8-9V7l8-4z"/><path d="M8 12l2 2 4-4"/></svg>Cyber Security</a>
            <a class="course-chip" href="/courses/ai-ml"><svg viewBox="0 0 24 24"><path d="M12 6v12M6 12h12"/></svg>AI & ML</a>
            <a class="course-chip" href="/courses/ui-ux-design"><svg viewBox="0 0 24 24"><path d="M4 4h16v16H4z"/><path d="M9 9h6v6H9z"/></svg>UI/UX Design</a>
            <a class="course-chip" href="/courses/mobile-app"><svg viewBox="0 0 24 24"><rect x="7" y="2" width="10" height="20" rx="2"/><circle cx="12" cy="18" r="1"/></svg>Mobile App Dev</a>
            <a class="course-chip" href="/courses/cloud-computing"><svg viewBox="0 0 24 24"><path d="M7 18h10a3 3 0 0 0 0-6h-.5a5.5 5.5 0 0 0-10.7 1.5A3 3 0 0 0 7 18z"/></svg>Cloud Computing</a>
          </div>
        </div>
      </div>
      <div class="portrait"><img src="assets/images/rahul-1.webp" alt="Student"/></div>
      <div class="card-slider">
        <div class="slides">
          <div class="card is-active">
            <div>
              <h3>Enroll in Job‑Ready Courses</h3>
              <p>Start today with guided projects</p>
              <a class="cta" href="/courses">Explore Courses</a>
            </div>
            <div class="slide-media"><img src="assets/images/mg-logo.jpg" alt="Courses"/></div>
          </div>
          <div class="card">
            <div>
              <h3>Scholarships & Financial Aid</h3>
              <p>Apply now for fee support</p>
              <a class="cta" href="/scholarships">View Scholarships</a>
            </div>
            <div class="slide-media"><img src="assets/images/mg-logo.jpg" alt="Scholarships"/></div>
          </div>
          <div class="card">
            <div>
              <h3>Placement & Career Support</h3>
              <p>Partner employers & job fairs</p>
              <a class="cta" href="/jobs">See Jobs</a>
            </div>
            <div class="slide-media"><img src="assets/images/mg-logo.jpg" alt="Jobs"/></div>
          </div>
        </div>
        <div class="bubble-arrows">
          <button class="bubble prev" aria-label="Previous"><svg viewBox="0 0 24 24"><path d="M15 6l-6 6 6 6"/></svg></button>
          <button class="bubble next" aria-label="Next"><svg viewBox="0 0 24 24"><path d="M9 6l6 6-6 6"/></svg></button>
        </div>
        <div class="hero-dotbar">
          <button class="dot is-active" data-i="0" aria-label="Slide 1"></button>
          <button class="dot" data-i="1" aria-label="Slide 2"></button>
          <button class="dot" data-i="2" aria-label="Slide 3"></button>
        </div>
      </div>
    </div>
  </div>
</section>
<script>
var s=document.querySelector('.card-slider');
if(s){
  var cards=[].slice.call(s.querySelectorAll('.card'));
  var track=s.querySelector('.slides');
  var dots=[].slice.call(s.querySelectorAll('.dot'));
  var p=s.querySelector('.prev');
  var n=s.querySelector('.next');
  var i=0;var len=cards.length;var t;var interval=5500;
  function show(x){
    i=(x+len)%len;
    if(track){track.style.transform='translateX('+(-i*100)+'%)';}
    dots.forEach(function(d,j){d.classList.toggle('is-active',j===i)});
  }
  function auto(){clearInterval(t);t=setInterval(function(){show(i+1)},interval)}
  dots.forEach(function(d){d.addEventListener('click',function(){show(parseInt(d.getAttribute('data-i'),10));auto()})});
  if(p)p.addEventListener('click',function(){show(i-1);auto()});
  if(n)n.addEventListener('click',function(){show(i+1);auto()});
  s.addEventListener('mouseenter',function(){clearInterval(t)});
  s.addEventListener('mouseleave',auto);
  auto();
}
</script>
