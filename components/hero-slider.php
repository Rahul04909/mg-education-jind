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
                <!-- Slide 1 -->
                <div class="hero-slide active">
                    <img src="assets/images/frontend/student-banner.png" alt="Slide 1">
                </div>
                
                <!-- Slide 2 -->
                <div class="hero-slide">
                    <img src="assets/images/frontend/dr.midda.jpg" alt="Slide 2">
                </div>

                <!-- Slide 3 -->
                <div class="hero-slide">
                    <img src="assets/images/frontend/student-banner.png" alt="Slide 3">
                </div>
                
                <!-- Slide 4 -->
                <div class="hero-slide">
                    <img src="assets/images/frontend/student-banner.png" alt="Slide 4">
                </div>
                
                <!-- Slide 5 -->
                <div class="hero-slide">
                    <img src="assets/images/frontend/student-banner.png" alt="Slide 5">
                </div>
            </div>

            <!-- Dots -->
            <div class="slider-dots">
                <div class="dot active" onclick="goToSlide(0)"></div>
                <div class="dot" onclick="goToSlide(1)"></div>
                <div class="dot" onclick="goToSlide(2)"></div>
                <div class="dot" onclick="goToSlide(3)"></div>
                <div class="dot" onclick="goToSlide(4)"></div>
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

        sliderSection.addEventListener('mouseenter', stopTimer);
        sliderSection.addEventListener('mouseleave', startTimer);

        // Initialize
        startTimer();
    });
</script>
