<?php
?>
<style>
.testi{background:#fff}
.testi-wrap{max-width:1200px;margin:0 auto;padding:12px 16px 18px}
.testi-head{display:flex;align-items:flex-end;justify-content:space-between;gap:10px;margin-bottom:12px}
.testi-title{margin:0;color:#0b1020;line-height:1.06;font-size:24px;font-weight:800}
.accent-gradient{background:linear-gradient(90deg,#1358db 0%,#5b61ff 60%,#7c83ff 100%);-webkit-background-clip:text;background-clip:text;color:transparent}
.write-btn{display:inline-flex;align-items:center;gap:8px;padding:10px 14px;border-radius:12px;background:#0f1419;color:#fff;font-weight:800;border:0}
.stats-card{border:1px solid #e6e8ee;background:#fff;border-radius:18px;padding:16px;display:grid;grid-template-columns:1fr 1fr;gap:16px}
.big-rate{display:flex;align-items:center;gap:12px}
.rate-num{font-size:32px;font-weight:800;color:#0b1020}
.rate-stars{display:flex;gap:4px}
.rate-stars svg{height:18px;width:18px;fill:#f59e0b}
.rating-count{color:#667285;font-weight:600}
.dist{display:grid;gap:6px}
.dist-row{display:grid;grid-template-columns:16px 1fr 36px;align-items:center;gap:8px}
.bar{height:10px;border-radius:999px;background:#eef2f9;overflow:hidden}
.fill{display:block;height:100%;background:linear-gradient(90deg,#1358db 0%,#5b61ff 60%,#7c83ff 100%);width:0}
.fill[style]{width:calc(var(--p)*1%)}
.reviews-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-top:12px}
.review-card{border:1px solid #e6e8ee;background:#fff;border-radius:14px;padding:14px}
.review-top{display:flex;align-items:center;gap:10px;margin-bottom:8px}
.avatar{height:38px;width:38px;border-radius:999px;background:#e8f0ff;display:flex;align-items:center;justify-content:center;color:#1e3a8a;font-weight:800}
.name{color:#0b1020;font-weight:800}
.r-stars{display:flex;gap:4px}
.r-stars svg{height:14px;width:14px;fill:#f59e0b}
.r-date{color:#8b94a7;font-size:12px;margin-left:auto}
.review-text{color:#2b313b}
.form-card{border:1px solid #e6e8ee;background:#fff;border-radius:16px;padding:18px;margin-top:16px;display:none;box-shadow:0 12px 28px rgba(0,0,0,.06)}
.form-card.is-open{display:block}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-top:12px}
.form-field{border:1px solid #e6e8ee;border-radius:12px;padding:12px;width:100%;background:#f7f8fa}
.form-row + .form-field{margin-top:12px}
.rate-input{display:flex;align-items:center;gap:8px;margin-bottom:12px;margin-top:2px}
.form-actions{display:flex;align-items:center;gap:10px;margin-top:12px}
.rate-star{height:20px;width:20px;fill:#cfd6e8;cursor:pointer}
.rate-star.is-active{fill:#f59e0b}
.submit-btn{display:inline-flex;align-items:center;gap:8px;padding:12px 16px;border-radius:12px;background:#1358db;color:#fff;font-weight:800;border:0}
@media(max-width:1024px){.reviews-grid{grid-template-columns:repeat(2,1fr)}.testi-title{font-size:22px}}
@media(max-width:900px){.testi-wrap{padding:12px 12px 16px}.stats-card{grid-template-columns:1fr}}
@media(max-width:640px){.reviews-grid{grid-template-columns:1fr}.testi-title{font-size:20px}.form-row{grid-template-columns:1fr}}
</style>
<section class="testi" aria-label="Student testimonials">
  <div class="testi-wrap">
    <div class="testi-head">
      <h3 class="testi-title"><span class="accent-gradient">Student Reviews</span></h3>
      <button id="writeReviewBtn" class="write-btn" type="button" aria-expanded="false" aria-controls="reviewFormCard">Write a Review</button>
    </div>
    <div class="stats-card">
      <div>
        <div class="big-rate">
          <span class="rate-num">4.7</span>
          <span class="rate-stars">
            <svg viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
            <svg viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
            <svg viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
            <svg viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
            <svg viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
          </span>
        </div>
        <div class="rating-count">1,204 ratings</div>
      </div>
      <div class="dist">
        <div class="dist-row"><span>5</span><div class="bar"><span class="fill" style="--p:72"></span></div><span>72%</span></div>
        <div class="dist-row"><span>4</span><div class="bar"><span class="fill" style="--p:18"></span></div><span>18%</span></div>
        <div class="dist-row"><span>3</span><div class="bar"><span class="fill" style="--p:6"></span></div><span>6%</span></div>
        <div class="dist-row"><span>2</span><div class="bar"><span class="fill" style="--p:3"></span></div><span>3%</span></div>
        <div class="dist-row"><span>1</span><div class="bar"><span class="fill" style="--p:1"></span></div><span>1%</span></div>
      </div>
    </div>
    <div class="reviews-grid">
      <div class="review-card"><div class="review-top"><div class="avatar">A</div><div class="name">Anita Sharma</div><div class="r-stars"><svg viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg><svg viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg><svg viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg><svg viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg><svg viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg></div><div class="r-date">2 days ago</div></div><div class="review-text">Excellent course. Clear explanations on RAG and deployment. The projects are practical.</div></div>
      <div class="review-card"><div class="review-top"><div class="avatar">R</div><div class="name">Rahul Verma</div><div class="r-stars"><svg viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg><svg viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg><svg viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg><svg viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg></div><div class="r-date">1 week ago</div></div><div class="review-text">Loved the mentor support and detailed walkthroughs. Great pacing.</div></div>
      <div class="review-card"><div class="review-top"><div class="avatar">K</div><div class="name">Kavya Singh</div><div class="r-stars"><svg viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg><svg viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg><svg viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg></div><div class="r-date">3 weeks ago</div></div><div class="review-text">Solid introduction to GenAI with hands-on examples.</div></div>
    </div>
    <div id="reviewFormCard" class="form-card" aria-label="Write a review">
      <form action="/submit-review" method="post">
        <div class="rate-input" id="ratingStars">
          <input type="hidden" name="rating" id="reviewRating" value="0"/>
          <svg class="rate-star" data-v="1" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
          <svg class="rate-star" data-v="2" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
          <svg class="rate-star" data-v="3" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
          <svg class="rate-star" data-v="4" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
          <svg class="rate-star" data-v="5" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
        </div>
        <div class="form-row">
          <input class="form-field" type="text" name="name" placeholder="Your name" aria-label="Your name" required/>
          <input class="form-field" type="email" name="email" placeholder="Your email" aria-label="Your email" required/>
        </div>
        <textarea class="form-field" name="review" placeholder="Write your review" aria-label="Write your review" rows="4" required></textarea>
        <div class="form-actions">
          <button class="submit-btn" type="submit">Submit Review</button>
        </div>
      </form>
    </div>
  </div>
</section>
<script>
var w=document.getElementById('writeReviewBtn');
var f=document.getElementById('reviewFormCard');
if(w&&f){w.addEventListener('click',function(){var o=f.classList.toggle('is-open');w.setAttribute('aria-expanded',o?'true':'false');});}
var stars=[].slice.call(document.querySelectorAll('#ratingStars .rate-star'));
var out=document.getElementById('reviewRating');
function setRating(v){stars.forEach(function(s,i){s.classList.toggle('is-active',i<v)});out.value=v}
stars.forEach(function(s){s.addEventListener('click',function(){setRating(parseInt(s.getAttribute('data-v'),10))})});
</script>
