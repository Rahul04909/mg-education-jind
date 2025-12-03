<?php
?>
<style>
.promo{background:#fff}
.promo-wrap{max-width:1200px;margin:0 auto;padding:12px 16px 18px}
.promo-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px}
.promo-card{position:relative;border-radius:18px;overflow:hidden;display:grid;grid-template-columns:1.2fr 1fr;align-items:center;min-height:180px;color:#fff}
.blue{background:linear-gradient(135deg,#1358db 0%,#5b61ff 60%,#7c83ff 100%)}
.indigo{background:linear-gradient(135deg,#0f1a34 0%,#142b5f 60%,#0b2d6b 100%)}
.promo-content{padding:18px}
.eyebrow{opacity:.95;margin:0 0 6px 0;font-weight:800}
.promo-title{margin:0 0 10px 0;font-size:22px;line-height:1.2;font-weight:800}
.promo-sub{margin:0;color:#e7ecf4;opacity:.9}
.promo-cta{display:inline-flex;align-items:center;gap:8px;margin-top:12px;padding:10px 14px;border-radius:12px;background:#fff;color:#0f1419;font-weight:800}
.promo-media{display:flex;align-items:center;justify-content:center;padding:12px}
.promo-image{max-width:240px;width:100%;height:auto}
@media(max-width:1024px){.promo-title{font-size:20px}}
@media(max-width:900px){.promo-grid{grid-template-columns:1fr}.promo-card{grid-template-columns:1fr;min-height:160px}.promo-title{font-size:18px}.promo-wrap{padding:12px 12px 16px}}
@media(max-width:640px){.promo-title{font-size:16px}.promo-cta{padding:10px 12px;font-weight:700}.promo-image{max-width:200px}}
</style>
<section class="promo" aria-label="Promotional banners">
  <div class="promo-wrap">
    <div class="promo-grid">
      <div class="promo-card blue">
        <div class="promo-content">
          <p class="eyebrow">MG Skill Plus</p>
          <h3 class="promo-title">Unlock access to 10,000+ courses with a subscription</h3>
          <p class="promo-sub">Start your 7‑day trial and learn in-demand skills.</p>
          <a class="promo-cta" href="/subscribe">Start 7‑day free trial</a>
        </div>
        <div class="promo-media">
          <img class="promo-image" src="assets/images/banner-1.png" alt="Learning banner" loading="lazy"/>
        </div>
      </div>
      <div class="promo-card indigo">
        <div class="promo-content">
          <p class="eyebrow">MG Skill for Business</p>
          <h3 class="promo-title">Drive your business forward and empower your teams</h3>
          <p class="promo-sub">Train teams with curated programs and certifications.</p>
          <a class="promo-cta" href="/business">Try MG Skill for Business</a>
        </div>
        <div class="promo-media">
          <img class="promo-image" src="assets/images/banner-2.png" alt="Business training banner" loading="lazy"/>
        </div>
      </div>
    </div>
  </div>
  </section>
