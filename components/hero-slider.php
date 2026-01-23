<?php
/**
 * Hero Slider Component
 * 
 * Features:
 * - Image-only slides (Carousel)
 * - Autoplay
 * - Navigation buttons
 * - Mobile responsive
 */
?>
<?php
require_once __DIR__ . '/../database/db-config.php';
$conn_slider = getDbConnection();
$hero_slides = [];
$res_slider = $conn_slider->query("SELECT * FROM hero_slides ORDER BY created_at DESC");
while($row_s = $res_slider->fetch_assoc()) {
    $hero_slides[] = $row_s;
}
$conn_slider->close();
?>
<style>
    .hero-slider-section {
        position: relative;
        background-color: #0b1020;
        overflow: hidden;
        /* Removed static background image as slides will have images */
    }

    /* Slider Container */
    .hero-container {
        position: relative;
        width: 100%;
        max-width: 100%; /* Full width for image slider */
        padding: 0;
        margin: 0 auto;
    }

    .hero-slider {
        position: relative;
        overflow: hidden;
    }

    .hero-slides-wrapper {
        display: flex;
        transition: transform 0.6s cubic-bezier(0.25, 1, 0.5, 1);
        width: 100%;
    }

    .hero-slide {
        min-width: 100%;
        box-sizing: border-box;
        position: relative;
        /* Image Slide Styles */
    }
    
    .hero-slide img {
        width: 100%;
        height: auto;
        display: block;
        max-height: 600px; /* Limit height on large screens */
        object-fit: cover; /* Ensure it covers nicely */
    }

    /* Navigation */
    .slider-nav {
        position: absolute;
        bottom: 20px; /* Adjusted position */
        right: 20px;
        display: flex;
        gap: 12px;
        z-index: 10;
    }

    .nav-btn {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        border: 2px solid rgba(255, 255, 255, 0.5);
        background: rgba(0, 0, 0, 0.3);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s;
    }

    .nav-btn:hover {
        background: #fff;
        color: #0b1020;
        border-color: #fff;
    }

    .nav-btn svg {
        width: 24px;
        height: 24px;
        stroke-width: 2;
    }

    /* Dots */
    .slider-dots {
        position: absolute;
        bottom: 20px;
        left: 50%;
        transform: translateX(-50%);
        display: flex;
        gap: 8px;
        z-index: 10;
    }

    .dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background-color: rgba(255, 255, 255, 0.5);
        cursor: pointer;
        transition: all 0.3s;
    }

    .dot.active {
        width: 30px;
        border-radius: 10px;
        background-color: #fff; /* White active dot */
    }

    /* Responsive */
    @media (max-width: 768px) {
        .hero-slide img {
            height: 250px; /* Fixed height for mobile consistency */
            object-fit: cover;
        }
        
        .nav-btn {
            width: 36px;
            height: 36px;
        }
    }
</style>

<section class="hero-slider-section" id="heroSlider">
    <div class="hero-container">
        <div class="hero-slider">
            <div class="hero-slides-wrapper">
                <?php if(!empty($hero_slides)): ?>
                    <?php foreach($hero_slides as $index => $slide): ?>
                        <div class="hero-slide <?php echo $index === 0 ? 'active' : ''; ?>">
                            <img src="<?php echo htmlspecialchars($slide['image_path']); ?>" alt="Slide <?php echo $index + 1; ?>">
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <!-- Fallback / Default Slides if DB is empty -->
                    <div class="hero-slide active">
                        <img src="assets/images/frontend/hkcl-banner.jpg" alt="Default Slide 1">
                    </div>
                <?php endif; ?>
            </div>

            <!-- Dots -->
            <div class="slider-dots">
                <?php if(!empty($hero_slides)): ?>
                    <?php foreach($hero_slides as $index => $slide): ?>
                        <div class="dot <?php echo $index === 0 ? 'active' : ''; ?>" onclick="goToSlide(<?php echo $index; ?>)"></div>
                    <?php endforeach; ?>
                <?php else: ?>
                     <div class="dot active" onclick="goToSlide(0)"></div>
                <?php endif; ?>
            </div>

            <!-- Navigation -->
            <div class="slider-nav">
                <button class="nav-btn prev-btn" aria-label="Previous Slide">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
                </button>
                <button class="nav-btn next-btn" aria-label="Next Slide">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                </button>
            </div>
        </div>
    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const slides = document.querySelectorAll('.hero-slide');
        const dots = document.querySelectorAll('.dot');
        const prevBtn = document.querySelector('.prev-btn');
        const nextBtn = document.querySelector('.next-btn');
        const sliderSection = document.getElementById('heroSlider');
        const wrapper = document.querySelector('.hero-slides-wrapper');
        
        // Hide nav if 0 or 1 slide
        if (slides.length <= 1) {
            if(prevBtn) prevBtn.style.display = 'none';
            if(nextBtn) nextBtn.style.display = 'none';
            if(document.querySelector('.slider-dots')) document.querySelector('.slider-dots').style.display = 'none';
            return; // No sliding needed
        }

        let currentSlide = 0;
        let slideInterval;
        const intervalTime = 5000; // 5 seconds

        function showSlide(index) {
            // Handle index bounds
            if (index >= slides.length) currentSlide = 0;
            else if (index < 0) currentSlide = slides.length - 1;
            else currentSlide = index;

            // Update wrapper transform
            if(wrapper) {
                // Ensure slides are laid out horizontally in the wrapper: flex
                // Translate percentage based on number of slides or 100% per slide?
                // The CSS sets .hero-slide min-width: 100%. So translateX(-100% * index) is correct.
                wrapper.style.transform = `translateX(-${currentSlide * 100}%)`;
            }

            // Update active states
            slides.forEach((slide, i) => {
                if (i === currentSlide) slide.classList.add('active');
                else slide.classList.remove('active');
            });
            
            dots.forEach((dot, i) => {
                if (i === currentSlide) dot.classList.add('active');
                else dot.classList.remove('active');
            });
        }

        function nextSlide() {
            showSlide(currentSlide + 1);
        }

        function prevSlide() {
            showSlide(currentSlide - 1);
        }

        // Global function for dots
        window.goToSlide = function(index) {
            showSlide(index);
            resetTimer();
        }

        function startTimer() {
            stopTimer();
            slideInterval = setInterval(nextSlide, intervalTime);
        }

        function stopTimer() {
            if(slideInterval) clearInterval(slideInterval);
        }

        function resetTimer() {
            stopTimer();
            startTimer();
        }

        // Event Listeners
        if(nextBtn) nextBtn.addEventListener('click', () => {
            nextSlide();
            resetTimer();
        });

        if(prevBtn) prevBtn.addEventListener('click', () => {
            prevSlide();
            resetTimer();
        });

        if(sliderSection) {
            sliderSection.addEventListener('mouseenter', stopTimer);
            sliderSection.addEventListener('mouseleave', startTimer);
        }

        // Initialize
        startTimer();
    });
</script>
