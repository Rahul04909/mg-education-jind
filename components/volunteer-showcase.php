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
        background: #ffffff;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.05), 0 10px 10px -5px rgba(0, 0, 0, 0.01);
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
            border-radius: 16px;
        }
        .vol-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
        }
        .vol-title {
            font-size: 26px;
        }
        .vol-img-wrap {
            width: 100px;
            height: 100px;
        }
    }

    @media (max-width: 480px) {
        .vol-section {
            padding: 30px 0;
        }
        .vol-grid {
            grid-template-columns: 1fr;
            gap: 20px;
        }
        .vol-card {
            padding: 16px;
            background: rgba(255,255,255,0.5); /* Slight background on mobile for better separation */
        }
        .vol-card:hover {
            transform: none; /* Disable hover translation on touch */
        }
    }
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
