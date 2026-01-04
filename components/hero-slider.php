<?php
/**
 * Hero Slider Component
 * 
 * Features:
 * - Static background image
 * - Autoplaying text slides
 * - Navigation buttons
 * - Mobile responsive
 */
?>
<style>
    .hero-slider-section {
        position: relative;
        height: 600px;
        background-color: #0b1020;
        background-image: url('assets/images/frontend/student-banner.png');
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* Dark Overlay */
    .hero-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, rgba(11, 16, 32, 0.9) 0%, rgba(11, 16, 32, 0.7) 50%, rgba(11, 16, 32, 0.4) 100%);
        z-index: 1;
    }

    .hero-container {
        position: relative;
        z-index: 2;
        width: 100%;
        max-width: 1200px;
        padding: 0 20px;
        margin: 0 auto;
    }

    /* Slider Styles */
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
        padding: 40px 0;
        opacity: 0;
        transform: translateY(20px);
        transition: opacity 0.6s, transform 0.6s;
    }

    .hero-slide.active {
        opacity: 1;
        transform: translateY(0);
    }

    /* Typography */
    .slide-badge {
        display: inline-block;
        background-color: #1358db;
        color: #fff;
        padding: 6px 14px;
        border-radius: 50px;
        font-size: 14px;
        font-weight: 700;
        margin-bottom: 24px;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .slide-title {
        font-size: 56px;
        font-weight: 800;
        color: #fff;
        line-height: 1.1;
        margin: 0 0 20px 0;
        max-width: 800px;
    }

    .slide-subtitle {
        font-size: 20px;
        color: #e2e8f0;
        line-height: 1.6;
        margin: 0 0 32px 0;
        max-width: 600px;
    }

    /* Buttons */
    .slide-cta {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        background-color: #ffda79;
        color: #0b1020;
        padding: 16px 32px;
        border-radius: 12px;
        font-size: 18px;
        font-weight: 700;
        text-decoration: none;
        transition: all 0.3s ease;
    }

    .slide-cta:hover {
        background-color: #ffcd38;
        transform: translateY(-2px);
    }
    
    .slide-cta svg {
        width: 20px;
        height: 20px;
        fill: none;
        stroke: currentColor;
        stroke-width: 2.5;
    }

    /* Navigation */
    .slider-nav {
        position: absolute;
        bottom: 0;
        right: 0;
        display: flex;
        gap: 12px;
    }

    .nav-btn {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        border: 2px solid rgba(255, 255, 255, 0.2);
        background: transparent;
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
        display: flex;
        gap: 8px;
        margin-top: 40px;
    }

    .dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background-color: rgba(255, 255, 255, 0.3);
        cursor: pointer;
        transition: all 0.3s;
    }

    .dot.active {
        width: 30px;
        border-radius: 10px;
        background-color: #1358db;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .hero-slider-section {
            height: 500px;
            background-position: 70% center;
        }

        .hero-overlay {
            background: linear-gradient(0deg, rgba(11, 16, 32, 0.95) 0%, rgba(11, 16, 32, 0.6) 100%);
        }

        .slide-title {
            font-size: 36px;
        }

        .slide-subtitle {
            font-size: 16px;
        }

        .slider-nav {
            /* display: none; Removed to show buttons on mobile */
            bottom: 20px;
            right: 20px;
        }
        
        .nav-btn {
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.1);
        }
    }
</style>

<section class="hero-slider-section" id="heroSlider">
    <div class="hero-overlay"></div>
    <div class="hero-container">
        <div class="hero-slider">
            <div class="hero-slides-wrapper">
                <!-- Slide 1 -->
                <div class="hero-slide active">
                    <span class="slide-badge">Welcome to MG Skill</span>
                    <h1 class="slide-title">Empowering Youth,<br>Building the Future</h1>
                    <p class="slide-subtitle">Join our comprehensive skill development programs designed to make you industry-ready and successful.</p>
                    <a href="courses.php" class="slide-cta">
                        Explore Courses
                        <svg viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                    </a>
                </div>
                
                <!-- Slide 2 -->
                <div class="hero-slide">
                    <span class="slide-badge" style="background-color: #10b981;">Government Recognized</span>
                    <h1 class="slide-title">Certified Skill<br>Development Programs</h1>
                    <p class="slide-subtitle">Get certified by NSDC and other government bodies. Valid across India and recognized by top employers.</p>
                    <a href="about.php" class="slide-cta">
                        Know More
                        <svg viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                    </a>
                </div>

                <!-- Slide 3 -->
                <div class="hero-slide">
                    <span class="slide-badge" style="background-color: #f59e0b;">Placement Support</span>
                    <h1 class="slide-title">Bridging Talent<br>with Opportunity</h1>
                    <p class="slide-subtitle">We don't just teach; we place. Benefit from our dedicated placement cell and industry partnerships.</p>
                    <a href="placements.php" class="slide-cta">
                        Placement Records
                        <svg viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                    </a>
                </div>
            </div>

            <!-- Dots -->
            <div class="slider-dots">
                <div class="dot active" onclick="goToSlide(0)"></div>
                <div class="dot" onclick="goToSlide(1)"></div>
                <div class="dot" onclick="goToSlide(2)"></div>
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
        
        let currentSlide = 0;
        let slideInterval;
        const intervalTime = 5000; // 5 seconds

        function showSlide(index) {
            // Remove active class from all
            slides.forEach(slide => slide.classList.remove('active'));
            dots.forEach(dot => dot.classList.remove('active'));
            
            // Handle index bounds
            if (index >= slides.length) currentSlide = 0;
            else if (index < 0) currentSlide = slides.length - 1;
            else currentSlide = index;

            // Add active class to current
            // Note: We're using simple display/opacity toggle for simplicity and performance
            // For sliding effect, we can manipulate the wrapper transform, but opacity is cleaner for text slides
            slides.forEach((slide, i) => {
                if (i === currentSlide) {
                    slide.style.display = 'block';
                    setTimeout(() => slide.classList.add('active'), 50); // Small delay for transition
                } else {
                    slide.classList.remove('active');
                    setTimeout(() => slide.style.display = 'none', 600); // Wait for transition
                }
            });
             
            // Using logic to hide non-active immediately for better stacking context if needed, 
            // but the CSS handles absolute/relative. 
            // Actually, let's keep them all in flow but hide via display none or absolute positioning?
            // The CSS uses display: flex on wrapper. If we want cross-fade/slide, the logic changes.
            // Let's adjust the JS to match the CSS structure which suggests a simple show/hide or transform.
            // The current CSS has .hero-slide min-width: 100%. We need to translate the wrapper.
            
            const wrapper = document.querySelector('.hero-slides-wrapper');
            wrapper.style.transform = `translateX(-${currentSlide * 100}%)`;
            
            // Re-add active class for specific internal animations (like text fade in)
            slides.forEach(s => s.classList.remove('active'));
            slides[currentSlide].classList.add('active');
            
            dots[currentSlide].classList.add('active');
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
            slideInterval = setInterval(nextSlide, intervalTime);
        }

        function stopTimer() {
            clearInterval(slideInterval);
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

        sliderSection.addEventListener('mouseenter', stopTimer);
        sliderSection.addEventListener('mouseleave', startTimer);

        // Initialize
        // Initial state set in CSS/HTML (active class), but let's ensure JS logic syncs
        // showSlide(0); // This might cause a jump, let's just start timer
        startTimer();
    });
</script>
