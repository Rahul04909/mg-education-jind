<?php
/**
 * Donation Enquiry Component
 * 
 * Features:
 * - Background image with overlay
 * - Title and Subtitle
 * - Donation Enquiry Form
 */
?>
<style>
    .donation-section {
        position: relative;
        background-color: #f8f9fa; /* Fallback */
        background-image: url('assets/images/frontend/student-banner.png');
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        padding: 60px 16px;
        color: #fff;
        overflow: hidden;
        border-radius: 24px;
        margin: 40px 16px;
    }

    /* Overlay */
    .donation-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(135deg, rgba(19, 88, 219, 0.6) 0%, rgba(11, 16, 32, 0.8) 100%);
        z-index: 1;
    }

    .donation-container {
        position: relative;
        z-index: 2;
        max-width: 1200px;
        margin: 0 auto;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 40px;
        align-items: center;
    }

    /* Content Side */
    .donation-content {
        text-align: left;
    }

    .donation-badge {
        display: inline-block;
        background: rgba(255, 255, 255, 0.15);
        border: 1px solid rgba(255, 255, 255, 0.3);
        backdrop-filter: blur(4px);
        padding: 6px 16px;
        border-radius: 50px;
        font-size: 14px;
        font-weight: 600;
        margin-bottom: 20px;
        letter-spacing: 0.5px;
        color: #ffda79;
    }

    .donation-title {
        font-size: 42px;
        font-weight: 800;
        line-height: 1.2;
        margin: 0 0 20px 0;
        color: #fff;
    }

    .donation-subtitle {
        font-size: 18px;
        line-height: 1.6;
        opacity: 0.9;
        margin-bottom: 30px;
        max-width: 500px;
    }

    .donation-stats {
        display: flex;
        gap: 30px;
        margin-top: 30px;
    }

    .stat-item h4 {
        font-size: 32px;
        font-weight: 800;
        margin: 0;
        color: #ffda79;
    }

    .stat-item p {
        margin: 5px 0 0 0;
        font-size: 14px;
        opacity: 0.8;
    }

    /* Form Side */
    .donation-form-wrapper {
        background: #fff;
        padding: 40px;
        border-radius: 20px;
        box-shadow: 0 20px 40px rgba(0,0,0,0.2);
        color: #333;
    }
    
    .donation-form-header {
        margin-bottom: 24px;
        text-align: center;
    }

    .donation-form-header h3 {
        font-size: 24px;
        font-weight: 700;
        margin: 0 0 8px 0;
        color: #0b1020;
    }
    
    .donation-form-header p {
        margin: 0;
        color: #666;
        font-size: 14px;
    }

    .form-group {
        margin-bottom: 16px;
    }

    .form-label {
        display: block;
        margin-bottom: 6px;
        font-weight: 600;
        font-size: 14px;
        color: #374151;
    }

    .form-control {
        width: 100%;
        padding: 12px 16px;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        font-size: 15px;
        transition: border-color 0.2s, box-shadow 0.2s;
        font-family: inherit;
    }

    .form-control:focus {
        outline: none;
        border-color: #1358db;
        box-shadow: 0 0 0 3px rgba(19, 88, 219, 0.1);
    }
    
    select.form-control {
        appearance: none;
        -webkit-appearance: none;
        -moz-appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%23374151' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 16px center;
        background-size: 16px;
        padding-right: 40px;
        cursor: pointer;
    }

    .form-row {
        display: flex;
        gap: 16px;
    }
    
    .form-row .form-group {
        flex: 1;
    }

    .btn-submit {
        display: block;
        width: 100%;
        background: #1358db;
        color: #fff;
        border: none;
        padding: 14px;
        font-size: 16px;
        font-weight: 700;
        border-radius: 10px;
        cursor: pointer;
        transition: background 0.2s, transform 0.1s;
    }

    .btn-submit:hover {
        background: #0f46b3;
    }
    
    .btn-submit:active {
        transform: scale(0.98);
    }
    
    .secure-note {
        text-align: center;
        margin-top: 14px;
        font-size: 12px;
        color: #888;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
    }
    
    .secure-note svg {
        height: 14px;
        width: 14px;
        fill: currentColor;
    }

    @media (max-width: 900px) {
        .donation-container {
            grid-template-columns: 1fr;
            text-align: center;
        }
        
        .donation-content {
            text-align: center;
            margin-bottom: 20px;
        }
        
        .donation-subtitle {
            margin: 0 auto 30px auto;
        }
        
        .donation-stats {
            justify-content: center;
        }
        
        .donation-title {
            font-size: 32px;
        }
    }
    
@media (max-width: 600px) {
        .donation-section {
            padding: 30px 16px; /* Much tighter padding */
            margin: 24px 16px; /* Less margin */
        }
        
        .donation-form-wrapper {
            padding: 20px 16px; /* Compact form container */
        }
        
        .donation-title {
            font-size: 26px;
            margin-bottom: 10px;
        }
        
        .donation-subtitle {
            font-size: 14px;
            margin-bottom: 20px;
            line-height: 1.5;
        }
        
        /* Compact Stats */
        .donation-stats {
            margin-top: 16px;
            gap: 16px;
        }
        .stat-item h4 {
            font-size: 22px;
        }
        .stat-item p {
            font-size: 11px;
        }

        /* Compact Form Elements */
        .donation-form-header {
            margin-bottom: 16px;
        }
        .donation-form-header h3 {
            font-size: 20px;
        }
        .form-group {
            margin-bottom: 12px;
        }
        .form-label {
            margin-bottom: 4px;
            font-size: 13px;
        }
        .form-control {
            padding: 10px 12px;
            font-size: 14px;
            border-radius: 8px;
        }
        .form-row {
            flex-direction: column;
            gap: 0;
        }
        .btn-submit {
            padding: 12px;
            font-size: 15px;
            margin-top: 8px;
        }
        .secure-note {
            margin-top: 10px;
            font-size: 11px;
        }
    }
</style>

<section class="donation-section">
    <div class="donation-overlay"></div>
    <div class="donation-container">
        <!-- Text Content -->
        <div class="donation-content">
            <span class="donation-badge">Join the Mission</span>
            <h2 class="donation-title">Empower a Student,<br>Transform a Life.</h2>
            <p class="donation-subtitle">
                Your contribution helps us provide skill development training to underprivileged students according to government standards, paving the way for a brighter future.
            </p>
            
            <div class="donation-stats">
                <div class="stat-item">
                    <h4>8M+</h4>
                    <p>Students Reached</p>
                </div>
                <div class="stat-item">
                    <h4>270K</h4>
                    <p>Women Empowered</p>
                </div>
                <div class="stat-item">
                    <h4>124K</h4>
                    <p>Youth Skilled</p>
                </div>
            </div>
        </div>

        <!-- Enquiry Form -->
        <div class="donation-form-wrapper">
            <div class="donation-form-header">
                <h3>Donation Enquiry</h3>
                <p>Fill out the form to know how you can contribute</p>
            </div>
            
            <form action="actions/submit-donation-enquiry.php" method="POST">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="full_name" class="form-control" placeholder="John Doe" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Phone Number</label>
                        <input type="tel" name="phone" class="form-control" placeholder="+91 98765 43210" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" class="form-control" placeholder="john@example.com" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Purpose of Donation</label>
                    <select name="purpose" class="form-control">
                        <option>Sponsor a Student's Education</option>
                        <option>Support Infrastructure</option>
                        <option>General Donation</option>
                        <option>Corporate CSR</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Message (Optional)</label>
                    <textarea name="message" class="form-control" rows="2" placeholder="Tell us more..."></textarea>
                </div>
                
                <button type="submit" class="btn-submit">Submit Enquiry</button>
                
                <div class="secure-note">
                    <svg viewBox="0 0 24 24"><path d="M12 2L3 7v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V7l-9-5zm-2 16l-4-4 1.41-1.41L10 15.17l6.59-6.59L18 10l-8 8z"/></svg>
                    Your details are secure with us.
                </div>
            </form>
        </div>
    </div>
</section>
