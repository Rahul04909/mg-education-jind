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
      <?php
      if ($course) {
          $cat_id = intval($course['category_id']);
          $current_id = intval($course['id']);
          $sql_related = "SELECT * FROM courses WHERE category_id = $cat_id AND id != $current_id AND is_active = 1 ORDER BY created_at DESC LIMIT 4";
          $res_related = $conn->query($sql_related);
          
          if ($res_related && $res_related->num_rows > 0) {
              while ($row = $res_related->fetch_assoc()) {
                  $title = htmlspecialchars($row['title']);
                  $image = !empty($row['featured_image']) ? $row['featured_image'] : '';
                  $labels = json_decode($row['labels'], true);
                  $label = !empty($labels) && is_array($labels) ? htmlspecialchars($labels[0]) : 'Course';
                  $fees = json_decode($row['fees'], true);
                  $price = isset($fees['amount']) && is_numeric($fees['amount']) ? '₹' . number_format($fees['amount']) : 'Free';
                  
                  // MRP Logic
                  $mrp = '';
                  if ($price !== 'Free') {
                     $mrp_val = $fees['amount'] + rand(500, 2000);
                     $mrp = '₹' . number_format($mrp_val);
                  }
                  
                  // Random Rating
                  $rating = number_format(rand(46, 50) / 10, 1);
                  $rating_count = number_format(rand(100, 5000)) . ' ratings';
                  
                  // Image Logic
                  $img_html = '';
                  if ($image) {
                      $img_html = '<img src="' . htmlspecialchars($image) . '" alt="' . $title . '" style="width:100%;height:100%;object-fit:cover">';
                  } else {
                     $gradients = ['ci-a', 'ci-b', 'ci-c', 'ci-d'];
                     $rand_grad = $gradients[array_rand($gradients)];
                     $img_html = '<div class="ci-img ' . $rand_grad . '" style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;color:#fff">' . substr($title, 0, 2) . '</div>';
                  }
                  ?>
                  <a href="/course-details.php?slug=<?php echo urlencode($row['slug']); ?>" class="course-card" style="text-decoration:none;color:inherit">
                    <div class="ci-img" style="background:#f3f4f6;overflow:hidden"><?php echo $img_html; ?></div>
                    <div class="course-body">
                      <p class="course-title"><?php echo $title; ?></p>
                      <p class="course-author">MG Skill</p>
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
              echo '<p style="grid-column:1/-1;color:#666">No related courses found.</p>';
          }
      }
      ?>
    </div>
  </div>
</section>
