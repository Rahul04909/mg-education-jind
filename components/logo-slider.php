<?php
/**
 * Logo Slider Component
 * Displays government department logos in an infinite scrolling carousel.
 */
?>
<style>
    .logo-slider-section {
        padding: 40px 0;
        background: #fff;
        border-bottom: 1px solid #f1f5f9;
        overflow: hidden;
    }

    .logo-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 30px;
        position: relative;
    }
    
    /* "With Registration" styling based on user image */
    .reg-title {
        font-size: 24px;
        font-weight: 800;
        text-transform: uppercase;
        color: #0f172a;
        margin: 0;
        letter-spacing: -0.5px;
    }
    .reg-highlight {
        color: #f59e0b; /* Orange/Gold color matching the image */
    }

    /* Infinite Slider Container */
    .logo-track-container {
        position: relative;
        width: 100%;
        overflow: hidden;
        mask-image: linear-gradient(to right, transparent, black 10%, black 90%, transparent);
        -webkit-mask-image: linear-gradient(to right, transparent, black 10%, black 90%, transparent);
    }

    .logo-track {
        display: flex;
        gap: 60px;
        width: max-content;
        animation: scroll 30s linear infinite;
    }
    
    .logo-track:hover {
        animation-play-state: paused;
    }

    .logo-item {
        height: 60px;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        filter: grayscale(100%);
        opacity: 0.7;
        transition: all 0.3s;
    }

    .logo-item:hover {
        filter: grayscale(0%);
        opacity: 1;
        transform: scale(1.05);
    }

    .logo-img {
        height: 100%;
        width: auto;
        object-fit: contain;
    }

    /* Animation Keyframes */
    @keyframes scroll {
        0% { transform: translateX(0); }
        100% { transform: translateX(calc(-50% - 30px)); } /* Scroll half width (original set) + half gap */
    }

    @media (max-width: 768px) {
        .logo-track { gap: 40px; }
        .logo-item { height: 45px; }
        .reg-title { font-size: 20px; }
        .logo-slider-section { padding: 30px 0; }
    }
</style>

<section class="logo-slider-section">
    <div class="container">
        <div class="logo-header">
            <h2 class="reg-title">WITH <span class="reg-highlight">REGISTRATION</span></h2>
        </div>
        
        <div class="logo-track-container">
            <div class="logo-track">
                <!-- Original Set of Logos -->
                <div class="logo-item"><img src="../assets/images/logos/skill-india.jpg" class="logo-img" alt="Ministry of IT" onerror="this.src='https://via.placeholder.com/150x60?text=Govt+Logo+1'"></div>
                <div class="logo-item"><img src="../assets/images/logos/digital-india.png" class="logo-img" alt="MHRD" onerror="this.src='https://via.placeholder.com/150x60?text=MHRD'"></div>
                <div class="logo-item"><img src="../assets/images/logos/nsdc-logo.webp" class="logo-img" alt="MSME" onerror="this.src='https://via.placeholder.com/150x60?text=MSME'"></div>
                <div class="logo-item"><img src="../assets/images/logos/iso.png" class="logo-img" alt="IAF" onerror="this.src='https://via.placeholder.com/150x60?text=IAF'"></div>
                <div class="logo-item"><img src="../assets/images/logos/msme.png" class="logo-img" alt="UAF" onerror="this.src='https://via.placeholder.com/150x60?text=UAF'"></div>
                <div class="logo-item"><img src="../assets/images/logos/mha.png" class="logo-img" alt="ISO" onerror="this.src='https://via.placeholder.com/150x60?text=ISO'"></div>
                
                <!-- Duplicate Set for Infinite Loop logic (must match exactly)-->
                <div class="logo-item"><img src="../assets/images/logos/skill-india.jpg" class="logo-img" alt="Ministry of IT" onerror="this.src='https://via.placeholder.com/150x60?text=Govt+Logo+1'"></div>
                <div class="logo-item"><img src="../assets/images/logos/digital-india.png" class="logo-img" alt="MHRD" onerror="this.src='https://via.placeholder.com/150x60?text=MHRD'"></div>
                <div class="logo-item"><img src="../assets/images/logos/nsdc-logo.webp" class="logo-img" alt="MSME" onerror="this.src='https://via.placeholder.com/150x60?text=MSME'"></div>
                <div class="logo-item"><img src="../assets/images/logos/iso.png" class="logo-img" alt="IAF" onerror="this.src='https://via.placeholder.com/150x60?text=IAF'"></div>
                <div class="logo-item"><img src="../assets/images/logos/msme.png" class="logo-img" alt="UAF" onerror="this.src='https://via.placeholder.com/150x60?text=UAF'"></div>
                <div class="logo-item"><img src="../assets/images/logos/mha.png" class="logo-img" alt="ISO" onerror="this.src='https://via.placeholder.com/150x60?text=ISO'"></div>
            </div>
        </div>
    </div>
</section>
