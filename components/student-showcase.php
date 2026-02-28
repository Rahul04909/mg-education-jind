<?php
/**
 * Student Showcase Component
 * Displays enrolled students with their photos and course details in an interactive carousel.
 */
if (!function_exists('getDbConnection')) {
    require_once __DIR__ . '/../database/db-config.php';
}
$conn_showcase = getDbConnection();

// Fetch 12 latest students with photos
$sql_showcase = "SELECT s.full_name, s.student_photo, c.title as course_name, cs.session_name 
                 FROM admissions s 
                 LEFT JOIN courses c ON s.course_id = c.id 
                 LEFT JOIN course_sessions cs ON s.session_id = cs.id
                 WHERE s.student_photo IS NOT NULL AND s.student_photo != ''
                 ORDER BY s.id DESC LIMIT 12";
$res_showcase = $conn_showcase->query($sql_showcase);
$students = [];
if ($res_showcase) {
    while($row = $res_showcase->fetch_assoc()) {
        $students[] = $row;
    }
}
?>

<style>
    .student-showcase-section {
        padding: 80px 0;
        background: #fdf2ff; /* Very faint purple background */
        overflow: hidden;
    }

    .showcase-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 0 20px;
        position: relative;
    }

    .showcase-header {
        text-align: center;
        margin-bottom: 50px;
    }

    .showcase-title {
        font-size: 36px;
        font-weight: 800;
        color: #1e293b;
        margin-bottom: 12px;
        letter-spacing: -1px;
    }

    .showcase-title span {
        color: #a855f7; /* Theme Purple */
    }

    .showcase-subtitle {
        color: #64748b;
        font-size: 16px;
        max-width: 600px;
        margin: 0 auto;
    }

    /* Slider Engine */
    .student-slider {
        position: relative;
        overflow: hidden;
        padding: 20px 0 40px;
    }

    .student-track {
        display: flex;
        gap: 30px;
        transition: transform 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        padding: 10px;
    }

    /* Student Card */
    .student-card {
        flex: 0 0 calc(25% - 22.5px); /* 4 cards on desktop */
        background: #ffffff;
        border-radius: 24px;
        padding: 30px 20px;
        border: 1px solid #f1f5f9;
        text-align: center;
        transition: all 0.3s ease;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        display: flex;
        flex-direction: column;
        align-items: center;
        text-decoration: none;
        color: inherit;
    }

    .student-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 20px 25px -5px rgba(168, 85, 247, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        border-color: rgba(168, 85, 247, 0.2);
    }

    .s-photo-wrap {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        padding: 5px;
        background: linear-gradient(135deg, #a855f7, #6366f1);
        margin-bottom: 20px;
        position: relative;
    }

    .s-photo-wrap::after {
        content: '';
        position: absolute;
        inset: 3px;
        background: #fff;
        border-radius: 50%;
        z-index: 0;
    }

    .s-photo {
        width: 100%;
        height: 100%;
        border-radius: 50%;
        object-fit: cover;
        position: relative;
        z-index: 1;
        border: 2px solid #fff;
    }

    .s-name {
        font-size: 18px;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 8px;
    }

    .s-course {
        font-size: 14px;
        font-weight: 600;
        color: #a855f7;
        background: #f3e8ff;
        padding: 4px 12px;
        border-radius: 20px;
        margin-bottom: 12px;
        display: inline-block;
    }

    .s-session {
        font-size: 13px;
        color: #64748b;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 5px;
    }

    /* Navigation */
    .slider-controls {
        display: flex;
        justify-content: center;
        gap: 15px;
        margin-top: 20px;
    }

    .slider-btn {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        background: #fff;
        border: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
    }

    .slider-btn:hover {
        background: #a855f7;
        color: #fff;
        border-color: #a855f7;
        transform: scale(1.1);
    }

    .slider-btn svg { width: 24px; height: 24px; }

    /* Mobile Responsive */
    @media (max-width: 1024px) {
        .student-card { flex: 0 0 calc(33.33% - 20px); }
    }

    @media (max-width: 768px) {
        .student-card { flex: 0 0 calc(50% - 15px); }
        .showcase-title { font-size: 28px; }
    }

    @media (max-width: 480px) {
        .student-card { flex: 0 0 100%; }
        .showcase-container { padding: 0 15px; }
    }
</style>

<section class="student-showcase-section">
    <div class="showcase-container">
        <div class="showcase-header">
            <h2 class="showcase-title">Our Proud <span>Students</span></h2>
            <p class="showcase-subtitle">Meet our successful learners who are building their careers through MG Skill's world-class programs.</p>
        </div>

        <div class="student-slider" id="studentSlider">
            <div class="student-track" id="studentTrack">
                <?php if(!empty($students)): ?>
                    <?php foreach($students as $student): ?>
                        <div class="student-card">
                            <div class="s-photo-wrap">
                                <?php 
                                    $photo = file_exists($student['student_photo']) ? $student['student_photo'] : 'assets/images/avatar-placeholder.png';
                                ?>
                                <img src="<?php echo $photo; ?>" class="s-photo" alt="<?php echo htmlspecialchars($student['full_name']); ?>">
                            </div>
                            <h3 class="s-name"><?php echo htmlspecialchars($student['full_name']); ?></h3>
                            <div class="s-course"><?php echo htmlspecialchars($student['course_name'] ?? 'General Course'); ?></div>
                            <div class="s-session">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                                <?php echo htmlspecialchars($student['session_name'] ?? 'Current Session'); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div style="width: 100%; text-align: center; color: #64748b; padding: 40px;">Learning journeys in progress...</div>
                <?php endif; ?>
            </div>
        </div>

        <div class="slider-controls">
            <button class="slider-btn" id="prevStudent" aria-label="Previous">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M15 18l-6-6 6-6"/></svg>
            </button>
            <button class="slider-btn" id="nextStudent" aria-label="Next">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M9 18l6-6-6-6"/></svg>
            </button>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const track = document.getElementById('studentTrack');
    const cards = document.querySelectorAll('.student-card');
    const prevBtn = document.getElementById('prevStudent');
    const nextBtn = document.getElementById('nextStudent');
    
    if (cards.length === 0) return;

    let index = 0;
    let autoplayInterval;

    function getVisibleCards() {
        if (window.innerWidth > 1024) return 4;
        if (window.innerWidth > 768) return 3;
        if (window.innerWidth > 480) return 2;
        return 1;
    }

    function moveSlider() {
        const visibleCards = getVisibleCards();
        const maxIndex = Math.max(0, cards.length - visibleCards);
        
        if (index > maxIndex) index = 0;
        if (index < 0) index = maxIndex;

        const cardWidth = cards[0].offsetWidth + 30; // card + gap
        track.style.transform = `translateX(-${index * cardWidth}px)`;
    }

    function nextSlide() {
        index++;
        moveSlider();
    }

    function prevSlide() {
        index--;
        moveSlider();
    }

    nextBtn.addEventListener('click', () => {
        nextSlide();
        resetAutoplay();
    });

    prevBtn.addEventListener('click', () => {
        prevSlide();
        resetAutoplay();
    });

    function startAutoplay() {
        autoplayInterval = setInterval(nextSlide, 4000);
    }

    function resetAutoplay() {
        clearInterval(autoplayInterval);
        startAutoplay();
    }

    track.addEventListener('mouseenter', () => clearInterval(autoplayInterval));
    track.addEventListener('mouseleave', startAutoplay);

    // Initial setup
    startAutoplay();
    window.addEventListener('resize', moveSlider);
});
</script>
