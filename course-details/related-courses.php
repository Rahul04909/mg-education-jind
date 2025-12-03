<?php
?>
<style>
.related{background:#fff}
.related-wrap{max-width:1200px;margin:0 auto;padding:12px 16px 18px}
.related-head{display:flex;align-items:flex-end;justify-content:space-between;gap:10px;margin-bottom:12px}
.related-title{margin:0;color:#0b1020;line-height:1.06;font-size:24px;font-weight:800}
.accent-gradient{background:linear-gradient(90deg,#1358db 0%,#5b61ff 60%,#7c83ff 100%);-webkit-background-clip:text;background-clip:text;color:transparent}
.related-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:14px}
.course-card{background:#fff;border:1px solid #e6e8ee;border-radius:14px;overflow:hidden;display:flex;flex-direction:column}
.ci-img{height:120px;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:800}
.ci-a{background:linear-gradient(135deg,#0ea5e9 0%,#6366f1 100%)}
.ci-b{background:linear-gradient(135deg,#10b981 0%,#22c55e 100%)}
.ci-c{background:linear-gradient(135deg,#f59e0b 0%,#b2560a 100%)}
.ci-d{background:linear-gradient(135deg,#ef4444 0%,#f97316 100%)}
.course-body{padding:12px}
.course-title{margin:0 0 6px 0;color:#0b1020;font-size:17px;font-weight:800;line-height:1.2;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
.course-author{margin:0;color:#667285;font-size:13px}
.course-meta{display:flex;align-items:center;gap:10px;margin-top:8px;color:#2b313b;font-weight:600;font-size:13px;flex-wrap:wrap}
.badge{display:inline-flex;align-items:center;padding:4px 8px;border-radius:999px;background:#e8f0ff;color:#1e3a8a;font-weight:700;font-size:12px}
.star{display:inline-flex;align-items:center;gap:4px;color:#0a50c9}
.star svg{height:14px;width:14px;fill:#f59e0b}
.star span{font-weight:800;color:#0b1020}
.rating-count{color:#8b94a7;font-weight:600}
.price-box{display:inline-flex;align-items:center;gap:6px;margin-top:6px;width:100%}
.price{color:#0b1020;font-weight:800;font-size:16px}
.mrp{color:#8b94a7;text-decoration:line-through;font-weight:700}
@media(max-width:1024px){.related-grid{grid-template-columns:repeat(3,1fr)}.related-title{font-size:22px}}
@media(max-width:900px){.related-wrap{padding:12px 12px 16px}.related-grid{grid-template-columns:repeat(2,1fr)}}
@media(max-width:640px){.related-title{font-size:20px}.ci-img{height:90px}.related-grid{grid-template-columns:1fr}.rating-count{display:none}}
</style>
<section class="related" aria-label="Related courses">
  <div class="related-wrap">
    <div class="related-head">
      <h3 class="related-title"><span class="accent-gradient">Related Courses</span> you may like</h3>
      <a class="badge" href="/courses">Explore All</a>
    </div>
    <div class="related-grid">
      <div class="course-card"><div class="ci-img ci-a">GEN AI</div><div class="course-body"><p class="course-title">GenAI Masters 2025: Python to LLMs & Deployment</p><p class="course-author">MG Skill</p><div class="course-meta"><span class="badge">Bestseller</span><span class="star"><svg viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg><span>4.7</span></span><span class="rating-count">1,204 ratings</span><span class="price-box"><span class="price">₹519</span><span class="mrp">₹3,009</span></span></div></div></div>
      <div class="course-card"><div class="ci-img ci-b">PYTHON</div><div class="course-body"><p class="course-title">Python for AI: Numpy, Pandas, NLP Basics</p><p class="course-author">KRISHAI Tech</p><div class="course-meta"><span class="badge">Trending</span><span class="star"><svg viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg><span>4.6</span></span><span class="rating-count">786 ratings</span><span class="price-box"><span class="price">₹519</span><span class="mrp">₹3,009</span></span></div></div></div>
      <div class="course-card"><div class="ci-img ci-c">RAG</div><div class="course-body"><p class="course-title">LLM Apps with RAG: FAISS, RAGAS, Deployment</p><p class="course-author">MG Skill Labs</p><div class="course-meta"><span class="badge">New</span><span class="star"><svg viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg><span>4.8</span></span><span class="rating-count">342 ratings</span><span class="price-box"><span class="price">₹519</span><span class="mrp">₹3,009</span></span></div></div></div>
      <div class="course-card"><div class="ci-img ci-d">DATA</div><div class="course-body"><p class="course-title">Data Science Starter: EDA, Features, Model Basics</p><p class="course-author">MG Skill</p><div class="course-meta"><span class="badge">Popular</span><span class="star"><svg viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg><span>4.5</span></span><span class="rating-count">1,023 ratings</span><span class="price-box"><span class="price">₹519</span><span class="mrp">₹3,009</span></span></div></div></div>
    </div>
  </div>
</section>
