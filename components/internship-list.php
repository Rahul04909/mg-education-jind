<?php
require_once __DIR__ . '/../database/db-config.php';
$conn = getDbConnection();
$sql = "SELECT * FROM internships WHERE is_active = 1 ORDER BY created_at DESC";
$result = $conn->query($sql);
?>
<style>
.internships{background:#f8fafc;padding:40px 0;}
.internships-wrap{max-width:1200px;margin:0 auto;padding:0 16px;}
.internships-head{display:flex;align-items:flex-end;justify-content:space-between;gap:10px;margin-bottom:20px}
.internships-title{margin:0;color:#0b1020;line-height:1.2;font-size:28px;font-weight:800}
.accent-gradient{background:linear-gradient(90deg,#1358db 0%,#5b61ff 60%,#7c83ff 100%);-webkit-background-clip:text;background-clip:text;color:transparent}
.internships-cta{display:inline-flex;align-items:center;gap:8px;padding:8px 16px;border-radius:12px;background:#fff;color:#0b1020;font-weight:700;border:1px solid #e2e8f0;transition:all 0.2s}
.internships-cta:hover{background:#f1f5f9;border-color:#cbd5e1}
.internships-shell{position:relative}
.internships-scroll{overflow:hidden}
.internships-track{display:grid;grid-auto-flow:column;--gap:20px;gap:var(--gap);--cols:4;grid-auto-columns:minmax(280px, calc((100% - (var(--cols) - 1) * var(--gap)) / var(--cols)));scroll-snap-type:x mandatory;padding-bottom:10px}
.internship-card{scroll-snap-align:start;background:#fff;border:1px solid #e6e8ee;border-radius:18px;overflow:hidden;display:flex;flex-direction:column;transition:transform 0.2s, box-shadow 0.2s;height:100%}
.internship-card:hover{transform:translateY(-4px);box-shadow:0 12px 24px -6px rgba(0,0,0,.08)}
.ii-img{height:160px;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:800;background:#f3f4f6;overflow:hidden;position:relative}
.ii-img img{width:100%;height:100%;object-fit:cover}
/* Gradients for fallback */
.gi-a{background:linear-gradient(135deg,#0ea5e9 0%,#6366f1 100%)}
.gi-b{background:linear-gradient(135deg,#10b981 0%,#22c55e 100%)}
.gi-c{background:linear-gradient(135deg,#f59e0b 0%,#b2560a 100%)}
.gi-d{background:linear-gradient(135deg,#ef4444 0%,#f97316 100%)}
.internship-body{padding:20px;flex:1;display:flex;flex-direction:column}
.internship-title{margin:0 0 8px 0;color:#0b1020;font-size:18px;font-weight:800;line-height:1.3;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
.internship-meta{display:flex;align-items:center;gap:12px;margin-top:auto;padding-top:16px;border-top:1px solid #f1f5f9;color:#64748b;font-size:13px;font-weight:600}
.meta-item{display:flex;align-items:center;gap:6px}
.meta-item svg{width:16px;height:16px;stroke-width:2;stroke:currentColor;fill:none}
.duration-badge{padding:4px 10px;background:#eef2ff;color:#4f46e5;border-radius:6px;font-size:12px;font-weight:700}
.price-tag{margin-left:auto;font-weight:800;color:#0b1020;font-size:15px}
.nav{position:absolute;right:-20px;top:50%;transform:translateY(-50%);display:flex;justify-content:space-between;width:calc(100% + 40px);pointer-events:none;z-index:2}
.nav .bubble{pointer-events:auto;height:40px;width:40px;border-radius:999px;background:#fff;border:1px solid #e1e6f0;display:flex;align-items:center;justify-content:center;box-shadow:0 4px 12px rgba(0,0,0,0.08);cursor:pointer;transition:transform 0.2s}
.nav .bubble:hover{transform:scale(1.1);border-color:#cbd5e1}
.nav .bubble svg{height:20px;width:20px;stroke:#475569;fill:none;stroke-width:2}
@media(max-width:1200px){.nav{display:none}}
@media(max-width:1024px){.internships-track{--cols:3}}
@media(max-width:768px){.internships-track{--cols:2}}
@media(max-width:500px){.internships-track{--cols:1;grid-auto-columns:100%}}
</style>
<section class="internships" aria-label="Internships">
  <div class="internships-wrap">
    <div class="internships-head">
      <div>
        <h3 class="internships-title"><span class="accent-gradient">Featured Internships</span></h3>
        <p style="color:#64748b;margin:6px 0 0 0">Gain real-world experience with our practical programs.</p>
      </div>
      <a class="internships-cta" href="/internships">View All</a>
    </div>
    <div class="internships-shell">
      <div class="internships-scroll" id="internshipsScroll">
        <div class="internships-track" id="internshipsTrack">
          <?php
          if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
              $title = htmlspecialchars($row['title']);
              $image = !empty($row['featured_image']) ? $row['featured_image'] : '';
              
              // Duration
              $duration = $row['duration_value'] . ' ' . $row['duration_type'];
              
              // Fees
              $fees = json_decode($row['fees'], true);
              $price = isset($fees['amount']) && is_numeric($fees['amount']) && $fees['amount'] > 0 
                       ? '₹' . number_format($fees['amount']) 
                       : 'Free';

              // Image Logic
              $img_html = '';
              if ($image) {
                // Assuming path is relative to root
                $img_src = $image;
                $img_html = '<img src="' . htmlspecialchars($img_src) . '" alt="' . $title . '">';
              } else {
                 $gradients = ['gi-a', 'gi-b', 'gi-c', 'gi-d'];
                 $rand_grad = $gradients[array_rand($gradients)];
                 $img_html = '<div class="ii-img ' . $rand_grad . '" style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;color:#fff;font-size:24px">' . substr($title, 0, 1) . '</div>';
              }
              ?>
              <a href="/internship-details.php?slug=<?php echo urlencode($row['slug']); ?>" class="internship-card" style="text-decoration:none;color:inherit">
                <div class="ii-img"><?php echo $img_html; ?>
                    <div style="position:absolute;top:10px;left:10px;background:rgba(0,0,0,0.6);color:#fff;padding:4px 10px;border-radius:20px;font-size:11px;font-weight:700;backdrop-filter:blur(4px)">Internship</div>
                </div>
                <div class="internship-body">
                  <h4 class="internship-title"><?php echo $title; ?></h4>
                  
                  <div class="internship-meta">
                    <span class="duration-badge">
                        <svg style="width:12px;height:12px;display:inline-block;vertical-align:middle;margin-right:2px;stroke:currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        <?php echo $duration; ?>
                    </span>
                    <div style="margin-left:auto;text-align:right;line-height:1.2">
                        <?php if(isset($fees['amount']) && $fees['amount'] > 0): 
                            $original_price = $fees['amount'];
                            $discount_percent = rand(60, 75);
                            $mrp = round($original_price * 100 / (100 - $discount_percent));
                        ?>
                            <div style="font-size:11px;color:#94a3b8;text-decoration:line-through">₹<?php echo number_format($mrp); ?></div>
                            <div class="price-tag">
                                ₹<?php echo number_format($original_price); ?>
                                <span style="font-size:10px;color:#16a34a;background:#dcfce7;padding:2px 4px;border-radius:4px;vertical-align:top;margin-left:2px"><?php echo $discount_percent; ?>% OFF</span>
                            </div>
                        <?php else: ?>
                             <span class="price-tag"><?php echo $price; ?></span>
                        <?php endif; ?>
                    </div>
                  </div>
                </div>
              </a>
          <?php 
            }
          } else {
             echo '<div style="grid-column:1/-1;text-align:center;padding:40px;background:#fff;border-radius:12px;border:1px dashed #e2e8f0;color:#64748b">No internships available right now.</div>';
          }
          ?>
        </div>
      </div>
      <div class="nav">
        <button class="bubble prev" aria-label="Previous"><svg viewBox="0 0 24 24"><path d="M15 18l-6-6 6-6"/></svg></button>
        <button class="bubble next" aria-label="Next"><svg viewBox="0 0 24 24"><path d="M9 18l6-6-6-6"/></svg></button>
      </div>
    </div>
  </div>
</section>
<script>
(function(){
  var isc=document.getElementById('internshipsScroll');
  var iprev=document.querySelector('.internships .prev');
  var inext=document.querySelector('.internships .next');
  function scroll(dir){if(!isc)return;isc.scrollBy({left:dir*300,behavior:'smooth'})}
  if(iprev)iprev.addEventListener('click',function(){scroll(-1)});
  if(inext)inext.addEventListener('click',function(){scroll(1)});
})();
</script>
