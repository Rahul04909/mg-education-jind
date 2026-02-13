<?php
// Ensure database connection
if (!function_exists('getDbConnection')) {
    require_once __DIR__ . '/../database/db-config.php';
}
$conn = getDbConnection();

// Fetch volunteers
// Limit to 8 for display; prioritize those with photos
$sql = "SELECT full_name, photo_file, city, state, role 
        FROM volunteers 
        WHERE photo_file IS NOT NULL 
        ORDER BY id DESC LIMIT 8";
$result = $conn->query($sql);
?>

<style>
    /* Scoped-like styles for Volunteer Showcase */
    .vol-section {
        background-color: #516591; /* Light blue (Tailwind sky-100 approx) */
        padding: 60px 0;
        margin-top: 40px;
        margin-bottom: 40px;
        border-radius: 24px;
        margin-left: 16px;
        margin-right: 16px;
    }
    
    .vol-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px;
    }

    .vol-header {
        text-align: center;
        margin-bottom: 40px;
    }

    .vol-title {
        font-size: 32px;
        font-weight: 800;
        color: #ffffff; /* Slate 900 */
        margin: 0 0 10px 0;
        letter-spacing: -0.5px;
    }

    .vol-subtitle {
        font-size: 16px;
        color: #ffffff; /* Slate 500 */
        max-width: 600px;
        margin: 0 auto;
        line-height: 1.6;
    }

    /* Grid Layout */
    .vol-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 30px;
    }

    /* Card Design */
    .vol-card {
        background: transparent;
        border-radius: 16px;
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        padding: 24px;
        transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.3s ease;
        text-decoration: none;
        color: inherit;
    }

    .vol-card:hover {
        transform: translateY(-5px);
        background: none;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.05), 0 10px 10px -5px rgba(0, 0, 0, 0.01);
        border: "1px solid black"
    }

    /* Image Wrapper */
    .vol-img-wrap {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        background: #ffffff;
        padding: 4px; /* White border effect */
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        margin-bottom: 16px;
        overflow: hidden;
        transition: box-shadow 0.3s ease;
    }

    .vol-card:hover .vol-img-wrap {
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    }

    .vol-img {
        width: 100%;
        height: 100%;
        border-radius: 50%;
        object-fit: cover;
    }

    /* Typography */
    .vol-name {
        font-size: 18px;
        font-weight: 700;
        color: #ffffff; /* Slate 800 */
        margin: 0 0 6px 0;
    }

    .vol-location {
        font-size: 14px;
        font-weight: 500;
        color: #ffffff; /* Slate 500 */
        margin: 0 0 8px 0;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
    }
    
    .vol-role {
        font-size: 13px;
        font-weight: 600;
        color: #000000; /* Sky 700 */
        background: #ffffff; /* Light blue */
        padding: 4px 10px;
        border-radius: 20px;
        display: inline-block;
        margin-top: auto; /* Push to bottom if needed, though centered */
    }
    
    .vol-card:hover .vol-role {
        background: none;
    }
    
    .loc-icon {
        width: 14px;
        height: 14px;
        fill: currentColor;
    }

    /* Responsive Breakpoints */
    @media (max-width: 1024px) {
        .vol-grid {
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
        }
    }

    @media (max-width: 768px) {
        .vol-section {
            padding: 40px 0;
            background: #516591; /* Ensure bg is solid */
        }
        .vol-container {
            padding: 0 16px;
            overflow: hidden; /* Hide overflow from container */
        }
        .vol-grid {
            display: flex;
            overflow-x: auto;
            gap: 16px;
            scroll-snap-type: x mandatory;
            padding-bottom: 20px;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: none; /* Firefox */
        }
        .vol-grid::-webkit-scrollbar { display: none; } /* Chrome/Safari */
        
        .vol-card {
            min-width: 200px; /* Fixed width for mobile cards */
            flex: 0 0 auto;
            scroll-snap-align: center;
            background: rgba(255,255,255,0.1); /* Subtle card bg */
            border: 1px solid rgba(255,255,255,0.1);
            padding: 20px 16px;
            border-radius: 16px;
        }
        
        .vol-card:hover {
            transform: none;
            background: rgba(255,255,255,0.15);
        }

        .vol-img-wrap {
            width: 90px;
            height: 90px; /* Smaller image */
            margin-bottom: 12px;
        }
        
        .vol-title {
            font-size: 24px;
        }
        .vol-subtitle {
            font-size: 14px;
            margin-bottom: 24px;
        }
        .vol-name {
            font-size: 16px;
        }
        .vol-role {
            font-size: 12px;
            padding: 3px 8px;
        }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const volGrid = document.querySelector('.vol-grid');
    if (window.innerWidth <= 768 && volGrid) {
        let isPaused = false;
        let scrollAmount = 0;
        
        // Auto-scroll logic
        const autoScroll = () => {
            if (!isPaused) {
                const cardWidth = 216; // 200px width + 16px gap
                const maxScroll = volGrid.scrollWidth - volGrid.clientWidth;
                
                if (volGrid.scrollLeft >= maxScroll - 5) {
                    // Reset to start smoothly or instantly
                    volGrid.scrollTo({left: 0, behavior: 'smooth'});
                } else {
                    volGrid.scrollBy({left: cardWidth, behavior: 'smooth'});
                }
            }
        };

        // Start autoplay
        let scrollInterval = setInterval(autoScroll, 3000); // Scroll every 3 seconds

        // Pause interaction
        const pause = () => { isPaused = true; };
        const resume = () => { 
            // Slight delay before resuming to not jar user
            setTimeout(() => { isPaused = false; }, 2000); 
        };

        volGrid.addEventListener('touchstart', pause, {passive: true});
        volGrid.addEventListener('touchend', resume);
        volGrid.addEventListener('mouseenter', pause);
        volGrid.addEventListener('mouseleave', resume);
    }
});
</script>
</style>

<section class="vol-section" aria-label="Volunteer Showcase">
    <div class="vol-container">
        <div class="vol-header">
            <h2 class="vol-title">Meet Our Community Heroes</h2>
            <p class="vol-subtitle">Dedicated volunteers making a real difference across the globe. Join us in our mission to bring positive change.</p>
        </div>

        <div class="vol-grid">
            <?php
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $name = htmlspecialchars($row['full_name']);
                    $city = htmlspecialchars($row['city']);
                    $state = htmlspecialchars($row['state']);
                    $location = $city . ', ' . $state;
                    $role = !empty($row['role']) ? htmlspecialchars($row['role']) : 'Volunteer';
                    
                    // Handle Image Path
                    // Assuming $row['photo_file'] stores relative path like 'assets/uploads/...'
                    // If it starts with ../ or absolute, adjust accordingly.
                    // Based on add-volunteer.php, it saves as "assets/uploads/volunteers/photos/..."
                    $img_src = !empty($row['photo_file']) ? $row['photo_file'] : 'assets/images/default-avatar.png';
                    
                    // Simple check if path is valid or needs slash
                    if ($img_src[0] !== '/' && strpos($img_src, 'http') !== 0) {
                       // Assuming we are at root level (index.php includes this)
                       // If not, we might need a Helper for Base URL. 
                       // But standard HTML img src works relative to the page URL (e.g. index.php)
                       // So "assets/..." is correct for homepage.
                    }
                    ?>
                    
                    <div class="vol-card">
                        <div class="vol-img-wrap">
                            <img src="<?php echo $img_src; ?>" alt="<?php echo $name; ?>" class="vol-img" loading="lazy">
                        </div>
                        <h3 class="vol-name"><?php echo $name; ?></h3>
                        <div class="vol-location">
                            <svg class="loc-icon" viewBox="0 0 24 24"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>
                            <span><?php echo $location; ?></span>
                        </div>
                        <span class="vol-role"><?php echo $role; ?></span>
                    </div>

                    <?php
                }
            } else {
                // Empty State or Fallback
                echo '<div style="grid-column: 1/-1; text-align: center; color: #64748b; padding: 40px;">No volunteers to display at the moment.</div>';
            }
            ?>
        </div>
    </div>
</section>
