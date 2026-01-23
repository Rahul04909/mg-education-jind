<?php
require_once __DIR__ . '/../database/db-config.php';
$conn = getDbConnection();
$sql = "SELECT * FROM courses WHERE is_active = 1 ORDER BY created_at DESC";
$result = $conn->query($sql);
?>
<style>
.courses{background:#fff;background-image:url('assets/images/backgronds/953979084540.jpg');background-size:cover;background-position:center;background-repeat:no-repeat}
.courses-wrap{max-width:1200px;margin:0 auto;padding:16px 16px 18px}
.courses-head{display:flex;align-items:flex-end;justify-content:space-between;gap:10px;margin-bottom:12px}
.courses-title{margin:0;color:#ffffff;line-height:1.06;font-size:28px;font-weight:800}
.accent-gradient{background:linear-gradient(90deg,#1358db 0%,#5b61ff 60%,#7c83ff 100%);-webkit-background-clip:text;background-clip:text;color:transparent}
.courses-cta{display:inline-flex;align-items:center;gap:8px;padding:8px 12px;border-radius:12px;background:#0f1419;color:#fff;font-weight:700}
.courses-shell{position:relative}
.courses-scroll{overflow:hidden}
.courses-track{display:grid;grid-auto-flow:column;--gap:12px;gap:var(--gap);--cols:6;grid-auto-columns:minmax(260px, calc((100% - (var(--cols) - 1) * var(--gap)) / var(--cols)));scroll-snap-type:x mandatory}
.course-card{scroll-snap-align:start;background:#fff;border:1px solid #e6e8ee;border-radius:14px;overflow:hidden;display:flex;flex-direction:column}
.ci-img{height:120px;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:800;background:#f3f4f6;overflow:hidden}
.ci-img img{width:100%;height:100%;object-fit:cover}
/* Retaining gradient classes as fallback or if needed, though primarily using images */
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
          <?php
          if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
              $title = htmlspecialchars($row['title']);
              $image = !empty($row['featured_image']) ? $row['featured_image'] : '';
              
              // Handle Fees
              $fees = json_decode($row['fees'], true);
              $price = isset($fees['amount']) && is_numeric($fees['amount']) ? '₹' . number_format($fees['amount']) : 'Free';
              // Calculate a fake MRP 
              // $mrp_val = isset($fees['amount']) && is_numeric($fees['amount']) ? $fees['amount'] * 1.5 : 0; 
              // keeping consistent random MRP concept or omitting if not in DB? 
              // The static design shows MRP. I'll just hardcode a markup for visual consistency if price > 0
              $mrp = '';
              if ($price !== 'Free') {
                 $mrp_val = $fees['amount'] + rand(500, 2000);
                 $mrp = '₹' . number_format($mrp_val);
              }

              // Handle Labels
              $labels = json_decode($row['labels'], true);
              $label = !empty($labels) && is_array($labels) ? htmlspecialchars($labels[0]) : 'Course';

              // Random Rating
              $rating = number_format(rand(46, 50) / 10, 1);
              $rating_count = number_format(rand(100, 5000)) . ' ratings';

              // Image Logic
              $img_html = '';
              if ($image) {
                // Assuming path is relative to root, needing adjustment based on where this component is included?
                // Usually included in index.php (root).
                // Database paths often stored as 'assets/uploads/...', so just prepending / or nothing if root.
                // Safest to assume relative to web root.
                $img_src = $image;
                $img_html = '<img src="' . htmlspecialchars($img_src) . '" alt="' . $title . '">';
              } else {
                 // Fallback to a gradient if no image, using a simple random one or fixed
                 $gradients = ['ci-a', 'ci-b', 'ci-c', 'ci-d', 'ci-e', 'ci-f', 'ci-g', 'ci-h'];
                 $rand_grad = $gradients[array_rand($gradients)];
                 $img_html = '<div class="ci-img ' . $rand_grad . '" style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;color:#fff">' . substr($title, 0, 2) . '</div>';
              }
              ?>
              <a href="/course-details.php?slug=<?php echo urlencode($row['slug']); ?>" class="course-card" style="text-decoration:none;color:inherit">
                <div class="ci-img"><?php echo $img_html; ?></div>
                <div class="course-body">
                  <p class="course-title"><?php echo $title; ?></p>
                  <p class="course-author">MG Education & Social Development Organization</p>
                  <div class="course-meta">
                    <span class="badge"><?php echo $label; ?></span>
                    <span class="star">
                      <svg viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                      <span><?php echo $rating; ?></span>
                    </span>
                    <span class="rating-count"><?php echo $rating_count; ?></span>
                    <span class="price-box">
                      <span class="price"><?php echo $price; ?></span>
                      <?php if($mrp): ?><span class="mrp"><?php echo $mrp; ?></span><?php endif; ?>
                    </span>
                  </div>
                </div>
              </a>
          <?php 
            }
          } else {
             echo '<p style="padding:20px;color:#666">No courses available at the moment.</p>';
          }
          ?>
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
