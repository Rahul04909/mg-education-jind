<?php
?>
<style>
.desc{background:#fff}
.desc-wrap{max-width:1200px;margin:0 auto;padding:12px 16px 18px}
.desc-card{border:1px solid #e6e8ee;background:#fff;border-radius:18px;padding:18px}
.desc-head{display:flex;align-items:center;gap:12px;margin-bottom:10px}
.desc-title{margin:0;color:#0b1020;font-size:22px;font-weight:800}
.desc-toggle{display:inline-flex;align-items:center;gap:8px;padding:8px 12px;border-radius:12px;background:#0f1419;color:#fff;font-weight:800;border:0}
.desc-footer{display:flex;align-items:center;justify-content:flex-start;margin-top:10px}
.desc-content{position:relative;color:#2b313b}
.collapsed{max-height:260px;overflow:hidden}
.collapsed::after{content:"";position:absolute;left:0;right:0;bottom:0;height:60px;background:linear-gradient(180deg,rgba(255,255,255,0) 0%, #fff 100%)}
.desc-content p{margin:10px 0}
.desc-content ul{margin:10px 0 0 0;padding-left:18px}
.desc-content li{margin:8px 0}
@media(max-width:900px){.desc-title{font-size:20px}.desc-wrap{padding:12px 12px 16px}}
@media(max-width:640px){.desc-title{font-size:18px}.desc-card{padding:14px}.desc-toggle{padding:8px 10px;font-weight:700}}
</style>
<section class="desc" aria-label="Course description">
  <div class="desc-wrap">
    <div class="desc-card">
      <div class="desc-head">
        <h3 class="desc-title">Description</h3>
      </div>
      <div id="descContent" class="desc-content collapsed">
        <?php if (!empty($course['description'])): ?>
            <?php echo $course['description']; ?>
        <?php else: ?>
            <p>No description available for this course.</p>
        <?php endif; ?>
      </div>
      <div class="desc-footer">
        <button id="descToggle" class="desc-toggle" type="button" aria-expanded="false" aria-controls="descContent">Show More</button>
      </div>
    </div>
  </div>
</section>
<script>
var btn=document.getElementById('descToggle');
var box=document.getElementById('descContent');
if(btn&&box){
  btn.addEventListener('click',function(){
    var ex=box.classList.toggle('collapsed');
    var expanded=!ex;
    btn.textContent=expanded?'Show Less':'Show More';
    btn.setAttribute('aria-expanded',expanded?'true':'false');
  });
}
</script>
