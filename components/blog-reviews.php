<?php
// Ensure $blog_id is available
if (!isset($blog_id)) return;

// Fetch Reviews
$rev_sql = "SELECT * FROM blog_reviews WHERE blog_id = $blog_id AND status = 'approved' ORDER BY created_at DESC";
$rev_result = $conn->query($rev_sql);

// Calculate Stats
$total_reviews = 0;
$avg_rating = 0;
$dist = [5=>0, 4=>0, 3=>0, 2=>0, 1=>0];

if ($rev_result && $rev_result->num_rows > 0) {
    $reviews = [];
    while($r = $rev_result->fetch_assoc()) {
        $reviews[] = $r;
        $dist[$r['rating']]++;
        $total_reviews++;
        $avg_rating += $r['rating'];
    }
    $avg_rating = $total_reviews > 0 ? number_format($avg_rating / $total_reviews, 1) : 0;
    
    // Reset pointer for display loop
    // $rev_result->data_seek(0); // If using direct result loop, but we stored in array
}
?>
<style>
.reviews-section { margin-top: 40px; padding-top: 24px; border-top: 1px solid var(--line); }
.reviews-head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; }
.rev-title { font-size: 24px; font-weight: 700; color: #1e293b; margin: 0; }
.btn-write-rev { background: #0f1419; color: #fff; border: none; padding: 10px 20px; border-radius: 99px; font-weight: 600; cursor: pointer; transition: background 0.2s; }
.btn-write-rev:hover { background: #334155; }

/* Stats */
.stats-box { background: #f8fafc; border: 1px solid var(--line); border-radius: 16px; padding: 24px; margin-bottom: 24px; display: grid; grid-template-columns: 1fr 1.5fr; gap: 30px; align-items: center; }
.big-rating { text-align: center; }
.rating-num { font-size: 48px; font-weight: 800; color: #1e293b; line-height: 1; margin-bottom: 8px; }
.rating-stars { color: #fbbf24; font-size: 20px; display: flex; gap: 4px; justify-content: center; margin-bottom: 8px; }
.rating-total { color: var(--muted); font-size: 14px; font-weight: 500; }

.rating-bars { display: flex; flex-direction: column; gap: 8px; }
.bar-row { display: flex; align-items: center; gap: 12px; font-size: 14px; color: var(--muted); }
.bar-track { flex: 1; height: 8px; background: #e2e8f0; border-radius: 99px; overflow: hidden; }
.bar-fill { height: 100%; background: #4f46e5; border-radius: 99px; width: 0; }

/* Review List */
.reviews-list { margin-top: 24px; display: grid; gap: 20px; }
.review-item { border: 1px solid var(--line); border-radius: 12px; padding: 20px; background: #fff; }
.ri-head { display: flex; align-items: center; gap: 12px; margin-bottom: 12px; }
.ri-avatar { width: 40px; height: 40px; background: #e0e7ff; color: #4338ca; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 18px; }
.ri-meta h5 { margin: 0 0 2px 0; font-size: 16px; font-weight: 700; color: #1e293b; }
.ri-meta span { font-size: 12px; color: var(--muted); }
.ri-stars { color: #fbbf24; display: flex; gap: 2px; }
.ri-msg { color: #334155; line-height: 1.6; }

/* Form */
.rev-form-wrap { display: none; background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 30px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05), 0 2px 4px -1px rgba(0,0,0,0.03); margin-bottom: 24px; }
.rev-form-wrap.open { display: block; animation: fadeIn 0.3s ease; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(-5px); } to { opacity: 1; transform: translateY(0); } }

.star-input { display: flex; gap: 4px; margin-bottom: 24px; cursor: pointer; }
.star-icon { width: 32px; height: 32px; fill: #cbd5e1; transition: fill 0.2s, transform 0.1s; }
.star-icon:hover { transform: scale(1.1); }
.star-icon.active { fill: #fbbf24; }

.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px; }
.form-group { margin-bottom: 0; } /* Reset margin since row handles gap */
.form-input { 
    width: 100%; 
    padding: 14px 16px; 
    border: 1px solid #e2e8f0; 
    border-radius: 8px; 
    font-size: 15px; 
    color: #334155;
    background: #f8fafc;
    transition: all 0.2s;
    font-family: inherit;
}
.form-input:focus { outline: none; border-color: #6366f1; background: #fff; box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1); }
.form-input::placeholder { color: #94a3b8; }
.form-textarea { margin-bottom: 24px; resize: vertical; min-height: 120px; }

.btn-submit { 
    background: #4f46e5; 
    color: #fff; 
    padding: 14px 28px; 
    border-radius: 8px; 
    border: none; 
    font-weight: 600; 
    cursor: pointer; 
    font-size: 15px;
    transition: background 0.2s, transform 0.1s;
}
.btn-submit:hover { background: #4338ca; transform: translateY(-1px); }
.btn-submit:active { transform: translateY(0); }

@media(max-width: 600px) {
    .stats-box { grid-template-columns: 1fr; gap: 20px; text-align: center; }
    .rating-bars { padding: 0 10px; }
    .form-row { grid-template-columns: 1fr; gap: 16px; }
    .rev-form-wrap { padding: 20px; }
}
</style>

<div class="reviews-section" id="reviews">
    <div class="reviews-head">
        <h3 class="rev-title">Comments & Reviews</h3>
        <button class="btn-write-rev" onclick="toggleReviewForm()">Write a Review</button>
    </div>

    <!-- Review Form -->
    <div class="rev-form-wrap" id="reviewForm">
        <h4 style="margin:0 0 16px 0;font-size:18px">Share your experience</h4>
        <form id="reviewFormEl">
            <input type="hidden" name="blog_id" value="<?php echo $blog_id; ?>">
            <input type="hidden" name="rating" id="ratingVal" value="0">
            
            <div class="star-input" id="starInput">
                <?php for($i=1; $i<=5; $i++): ?>
                    <svg class="star-icon" data-val="<?php echo $i; ?>" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                <?php endfor; ?>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <input type="text" name="name" class="form-input" placeholder="Your Name" required>
                </div>
                <div class="form-group">
                    <input type="email" name="email" class="form-input" placeholder="Your Email" required>
                </div>
            </div>
            
            <textarea name="review" class="form-input form-textarea" placeholder="Write your thoughts..." required></textarea>
            
            <button type="submit" class="btn-submit" id="submitBtn">Submit Review</button>
            <div id="formMsg" style="margin-top:12px;font-size:14px;display:none"></div>
        </form>
    </div>

    <!-- Stats -->
    <div class="stats-box">
        <div class="big-rating">
            <div class="rating-num"><?php echo $avg_rating; ?></div>
            <div class="rating-stars">
                <?php
                for($i=1; $i<=5; $i++) {
                    echo '<svg style="width:20px;height:20px;fill:'.($i <= round($avg_rating) ? '#fbbf24' : '#e2e8f0').'" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>';
                }
                ?>
            </div>
            <div class="rating-total"><?php echo $total_reviews; ?> ratings</div>
        </div>
        <div class="rating-bars">
            <?php for($i=5; $i>=1; $i--): 
                $p = $total_reviews > 0 ? ($dist[$i] / $total_reviews) * 100 : 0;
            ?>
            <div class="bar-row">
                <span style="width:10px"><?php echo $i; ?></span>
                <div class="bar-track">
                    <div class="bar-fill" style="width:<?php echo $p; ?>%"></div>
                </div>
                <span style="width:30px;text-align:right"><?php echo round($p); ?>%</span>
            </div>
            <?php endfor; ?>
        </div>
    </div>

    <!-- List -->
    <div class="reviews-list">
        <?php if (!empty($reviews)): ?>
            <?php foreach($reviews as $r): ?>
                <div class="review-item">
                    <div class="ri-head">
                        <div class="ri-avatar"><?php echo strtoupper(substr($r['name'], 0, 1)); ?></div>
                        <div class="ri-meta">
                            <h5><?php echo htmlspecialchars($r['name']); ?></h5>
                            <span><?php echo date("M d, Y", strtotime($r['created_at'])); ?></span>
                        </div>
                        <div style="flex:1"></div>
                        <div class="ri-stars">
                            <?php for($s=1; $s<=5; $s++) echo '<svg style="width:16px;height:16px;fill:'.($s<=$r['rating']?'#fbbf24':'#e2e8f0').'" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>'; ?>
                        </div>
                    </div>
                    <div class="ri-msg">
                        <?php echo nl2br(htmlspecialchars($r['review'])); ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="text-align:center;color:var(--muted);padding:20px;">No reviews yet. Be the first to share your thoughts!</p>
        <?php endif; ?>
    </div>
</div>

<script>
function toggleReviewForm() {
    document.getElementById('reviewForm').classList.toggle('open');
}

// Star Rating Logic
const stars = document.querySelectorAll('.star-icon');
const ratingInput = document.getElementById('ratingVal');

stars.forEach(star => {
    star.addEventListener('click', function() {
        const val = parseInt(this.getAttribute('data-val'));
        ratingInput.value = val;
        updateStars(val);
    });
    
    star.addEventListener('mouseover', function() {
        updateStars(parseInt(this.getAttribute('data-val')));
    });
});

document.getElementById('starInput').addEventListener('mouseleave', function() {
    updateStars(parseInt(ratingInput.value));
});

function updateStars(val) {
    stars.forEach(s => {
        if (parseInt(s.getAttribute('data-val')) <= val) {
            s.classList.add('active');
        } else {
            s.classList.remove('active');
        }
    });
}

// AJAX Submission
document.getElementById('reviewFormEl').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Basic validation
    if(ratingInput.value == 0) {
        alert("Please select a rating.");
        return;
    }

    const btn = document.getElementById('submitBtn');
    const msg = document.getElementById('formMsg');
    const formData = new FormData(this);
    
    btn.disabled = true;
    btn.innerText = "Submitting...";
    
    fetch('actions/submit-blog-review.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        msg.style.display = 'block';
        if(data.status === 'success') {
            msg.style.color = 'green';
            msg.innerText = data.message;
            setTimeout(() => location.reload(), 1500);
        } else {
            msg.style.color = 'red';
            msg.innerText = data.message;
            btn.disabled = false;
            btn.innerText = "Submit Review";
        }
    })
    .catch(err => {
        console.error(err);
        msg.style.display = 'block';
        msg.style.color = 'red';
        msg.innerText = "Something went wrong.";
        btn.disabled = false;
        btn.innerText = "Submit Review";
    });
});
</script>
