<?php
$scheme=(!empty($_SERVER['HTTPS'])&&$_SERVER['HTTPS']!=='off')?'https':'http';
$host=isset($_SERVER['HTTP_HOST'])?$_SERVER['HTTP_HOST']:'localhost';
$basePath=rtrim(str_replace('\\','/',dirname($_SERVER['SCRIPT_NAME'])),'/');
$baseUrl=$scheme.'://'.$host.($basePath===''?'/':$basePath.'/');
?>
<style>
.site-footer{--cta-offset:140px;background:#0f1419;color:#e7ecf4;margin-top:calc(var(--cta-offset)/2 + 24px);position:relative}
.ft-wrap{max-width:1200px;margin:0 auto;padding:calc(var(--cta-offset)/2 + 8px) 16px 0;position:relative}
.footer-top{position:absolute;left:16px;right:16px;top:0;transform:translateY(-50%);z-index:2;display:flex;align-items:center;gap:16px;padding:18px;border-radius:16px;background:linear-gradient(90deg,#1358db 0%,#5b61ff 60%,#7c83ff 100%);color:#fff;box-shadow:0 14px 28px rgba(0,0,0,.12)}
.cta-text{flex:1;}
.cta-title{font-size:22px;font-weight:800;margin:0}
.cta-sub{margin:6px 0 0 0;opacity:.95}
.footer-form{display:grid;grid-template-columns:1fr 1fr 1.4fr auto;gap:10px;align-items:center;width:100%}
.news-input{min-width:0;border:0;border-radius:12px;padding:12px;background:#fff;color:#0f1419}
.news-textarea{border:0;border-radius:12px;padding:12px;background:#fff;color:#0f1419;resize:vertical;min-height:44px}
.news-btn{padding:12px 16px;border-radius:12px;background:#b2560a;color:#fff;font-weight:700;border:0}
.footer-grid{display:grid;grid-template-columns:2fr 1fr 1fr 1.2fr;gap:18px;padding:24px 0;border-top:1px solid #233042}
.footer-brand{display:flex;align-items:center;gap:12px}
.footer-logo{height:40px;width:auto;border-radius:10px}
.footer-desc{color:#c7cbd3;margin:8px 0 0 0}
.footer-col h4{margin:0 0 10px 0;color:#fff;font-size:16px}
.footer-links{display:grid;gap:8px}
.footer-link{color:#c7cbd3}
.footer-link:hover{color:#fff}
.socials{display:flex;gap:10px;margin-top:10px}
.social{height:36px;width:36px;border-radius:12px;background:#1a2533;border:1px solid #263447;display:flex;align-items:center;justify-content:center}
.social svg{height:18px;width:18px;stroke:#c7cbd3;fill:none;stroke-width:2}
.footer-bottom{display:flex;justify-content:space-between;align-items:center;padding:14px 0;border-top:1px solid #233042;color:#aeb4bf}
.legal{display:flex;gap:12px}
.legal a{color:#aeb4bf}
.legal a:hover{color:#fff}
@media(max-width:900px){.site-footer{--cta-offset:220px}.footer-grid{grid-template-columns:1fr 1fr}.footer-top{flex-direction:column;align-items:flex-start}.footer-form{grid-template-columns:1fr;}}
@media(max-width:640px){.site-footer{--cta-offset:260px}.footer-grid{grid-template-columns:1fr}.footer-bottom{flex-direction:column;gap:8px}}
</style>
<footer class="site-footer" role="contentinfo">
  <div class="ft-wrap">
    <div class="footer-top">
      <div class="footer-brand">
        <img src="<?php echo htmlspecialchars($baseUrl,ENT_QUOTES,'UTF-8'); ?>../../assets/images/mg-logo.jpg" alt="MG Skill" class="footer-logo"/>
        <div class="cta-text">
          <p class="cta-title">Join MG Skill — Empower Your Career</p>
          <p class="cta-sub">Get updates on new courses, scholarships and events.</p>
        </div>
      </div>
      <form class="footer-form" action="/enquiry" method="post">
        <input class="news-input" type="tel" name="phone" placeholder="Enter mobile" aria-label="Mobile" pattern="[0-9]{10}"/>
        <input class="news-input" type="text" name="address" placeholder="Enter address" aria-label="Address"/>
        <textarea class="news-textarea" name="message" placeholder="Message" aria-label="Message"></textarea>
        <button class="news-btn" type="submit">Send Enquiry</button>
      </form>
    </div>
    <div class="footer-grid">
      <div>
        <p class="footer-desc">Skills, opportunities and community to help you learn and grow.</p>
        <div class="socials">
          <a href="#" class="social" aria-label="Facebook"><svg viewBox="0 0 24 24"><path d="M15 3h-3a4 4 0 0 0-4 4v3H6v4h2v7h4v-7h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg></a>
          <a href="#" class="social" aria-label="Twitter"><svg viewBox="0 0 24 24"><path d="M22 5c-1 .7-2 .9-3 .9 1-1 2-2 2-3-1 .6-2 1-3 1-3-3-7 2-5 5-4 0-7-2-9-5 0 3 2 5 4 6-1 0-2-.1-3-.8 0 3 2 5 5 5-1 .7-3 1-4 1 1 2 3 3 6 3 7 0 11-6 10-12z"/></svg></a>
          <a href="#" class="social" aria-label="LinkedIn"><svg viewBox="0 0 24 24"><path d="M4 4h4v16H4zM14 10c2 0 4 1 4 4v6h-4v-6c0-1-1-2-2-2s-2 1-2 2v6H6V8h4v2c1-1 2-2 4-2z"/></svg></a>
          <a href="#" class="social" aria-label="YouTube"><svg viewBox="0 0 24 24"><path d="M22 12s0-4-1-5c-1-1-3-1-6-1H9c-3 0-5 0-6 1-1 1-1 5-1 5s0 4 1 5c1 1 3 1 6 1h6c3 0 5 0 6-1 1-1 1-5 1-5z"/><path d="M10 9l5 3-5 3V9z"/></svg></a>
        </div>
      </div>
      <div class="footer-col">
        <h4>Programs</h4>
        <div class="footer-links">
          <a class="footer-link" href="/courses">Skill Courses</a>
          <a class="footer-link" href="/schemes">Schemes & Grants</a>
          <a class="footer-link" href="/dashboards">Student Dashboard</a>
          <a class="footer-link" href="/skill-centre">Skill Centres</a>
        </div>
      </div>
      <div class="footer-col">
        <h4>Resources</h4>
        <div class="footer-links">
          <a class="footer-link" href="/jobs">Job Exchange</a>
          <a class="footer-link" href="/recommendations">Recommendations</a>
          <a class="footer-link" href="/soar">AI SOAR</a>
          <a class="footer-link" href="/news">News & Events</a>
        </div>
      </div>
      <div class="footer-col">
        <h4>Contact</h4>
        <div class="footer-links">
          <a class="footer-link" href="mailto:info@mgskill.org">info@mgskill.org</a>
          <a class="footer-link" href="/contact">Contact Form</a>
          <a class="footer-link" href="/about">About Us</a>
          <a class="footer-link" href="/support">Support</a>
        </div>
      </div>
    </div>
    <div class="footer-bottom">
      <span>© <?php echo date('Y'); ?> MG Skill. All rights reserved. A Website Designed By Rahul Dhiman</span>
      <div class="legal">
        <a href="/privacy">Privacy</a>
        <a href="/terms">Terms</a>
        <a href="/accessibility">Accessibility</a>
      </div>
    </div>
  </div>
</footer>
