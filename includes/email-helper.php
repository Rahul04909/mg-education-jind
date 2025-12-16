<?php
/**
 * Email Helper Functions
 * Simple wrapper for sending emails using configured SMTP settings
 *
 * Usage:
 *   require_once __DIR__ . '/email-helper.php';
 *   $result = sendEmail('user@example.com', 'Subject', 'Message body', '<h1>HTML Body</h1>');
 */

require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Send email using configured SMTP settings
 *
 * @param string $to Recipient email address
 * @param string $subject Email subject
 * @param string $body Plain text body (optional if htmlBody is provided)
 * @param string $htmlBody HTML body (optional)
 * @param array $attachments Array of file paths to attach (optional)
 * @param string $replyTo Reply-to email address (optional)
 * @return array ['success' => bool, 'message' => string, 'error' => string|null]
 */
function sendEmail($to, $subject, $body = '', $htmlBody = '', $attachments = [], $replyTo = '') {
    // Database configuration
    $dbHost = 'localhost';
    $dbUser = 'root';
    $dbPass = '';
    $dbName = 'mg_skill';

    try {
        // Connect to database
        $conn = new mysqli($dbHost, $dbUser, $dbPass, $dbName);

        if ($conn->connect_error) {
            return [
                'success' => false,
                'message' => 'Database connection failed',
                'error' => $conn->connect_error
            ];
        }

        // Get SMTP settings
        $sql = "SELECT * FROM smtp_settings WHERE id = 1 AND is_active = 1";
        $result = $conn->query($sql);

        if ($result->num_rows === 0) {
            $conn->close();
            return [
                'success' => false,
                'message' => 'SMTP is not configured or disabled',
                'error' => 'No active SMTP configuration found'
            ];
        }

        $settings = $result->fetch_assoc();

        // Create PHPMailer instance
        $mail = new PHPMailer(true);

        // Server settings
        $mail->isSMTP();
        $mail->Host = $settings['smtp_host'];
        $mail->SMTPAuth = true;
        $mail->Username = $settings['smtp_username'];
        $mail->Password = $settings['smtp_password'];
        $mail->SMTPSecure = $settings['smtp_encryption'];
        $mail->Port = $settings['smtp_port'];

        // Recipients
        $mail->setFrom($settings['from_email'], $settings['from_name']);
        $mail->addAddress($to);

        // Reply-to address
        if (!empty($replyTo)) {
            $mail->addReplyTo($replyTo);
        }

        // Content
        $mail->isHTML(!empty($htmlBody));
        $mail->Subject = $subject;

        if (!empty($htmlBody)) {
            $mail->Body = $htmlBody;
            $mail->AltBody = !empty($body) ? $body : strip_tags($htmlBody);
        } else {
            $mail->Body = $body;
        }

        // Attachments
        if (!empty($attachments) && is_array($attachments)) {
            foreach ($attachments as $attachment) {
                if (file_exists($attachment)) {
                    $mail->addAttachment($attachment);
                }
            }
        }

        // Send email
        $mail->send();

        // Log successful email
        $toEsc = mysqli_real_escape_string($conn, $to);
        $subjectEsc = mysqli_real_escape_string($conn, $subject);
        $messageEsc = mysqli_real_escape_string($conn, !empty($htmlBody) ? substr($htmlBody, 0, 500) : substr($body, 0, 500));

        $logSql = "INSERT INTO email_logs (to_email, subject, message, status)
                   VALUES ('$toEsc', '$subjectEsc', '$messageEsc', 'success')";
        $conn->query($logSql);

        $conn->close();

        return [
            'success' => true,
            'message' => 'Email sent successfully',
            'error' => null
        ];

    } catch (Exception $e) {
        // Log failed email
        if (isset($conn) && !$conn->connect_error) {
            $toEsc = mysqli_real_escape_string($conn, $to);
            $subjectEsc = mysqli_real_escape_string($conn, $subject);
            $messageEsc = mysqli_real_escape_string($conn, !empty($htmlBody) ? substr($htmlBody, 0, 500) : substr($body, 0, 500));
            $errorEsc = mysqli_real_escape_string($conn, $e->getMessage());

            $logSql = "INSERT INTO email_logs (to_email, subject, message, status, error_message)
                       VALUES ('$toEsc', '$subjectEsc', '$messageEsc', 'failed', '$errorEsc')";
            $conn->query($logSql);

            $conn->close();
        }

        return [
            'success' => false,
            'message' => 'Failed to send email',
            'error' => $e->getMessage()
        ];
    }
}

/**
 * Send bulk emails to multiple recipients
 *
 * @param array $recipients Array of email addresses
 * @param string $subject Email subject
 * @param string $body Plain text body
 * @param string $htmlBody HTML body (optional)
 * @return array ['total' => int, 'success' => int, 'failed' => int, 'results' => array]
 */
function sendBulkEmail($recipients, $subject, $body = '', $htmlBody = '') {
    $total = count($recipients);
    $success = 0;
    $failed = 0;
    $results = [];

    foreach ($recipients as $recipient) {
        $result = sendEmail($recipient, $subject, $body, $htmlBody);

        if ($result['success']) {
            $success++;
        } else {
            $failed++;
        }

        $results[] = [
            'email' => $recipient,
            'status' => $result['success'] ? 'sent' : 'failed',
            'error' => $result['error']
        ];
    }

    return [
        'total' => $total,
        'success' => $success,
        'failed' => $failed,
        'results' => $results
    ];
}

/**
 * Send email with template
 *
 * @param string $to Recipient email address
 * @param string $subject Email subject
 * @param string $templateName Template name (welcome, reset-password, notification, etc.)
 * @param array $variables Variables to replace in template
 * @return array Result from sendEmail()
 */
function sendTemplateEmail($to, $subject, $templateName, $variables = []) {
    $templatePath = __DIR__ . '/email-templates/' . $templateName . '.html';

    if (!file_exists($templatePath)) {
        return [
            'success' => false,
            'message' => 'Email template not found',
            'error' => 'Template file does not exist: ' . $templateName
        ];
    }

    // Load template
    $htmlBody = file_get_contents($templatePath);

    // Replace variables
    foreach ($variables as $key => $value) {
        $htmlBody = str_replace('{{' . $key . '}}', $value, $htmlBody);
    }

    // Send email
    return sendEmail($to, $subject, '', $htmlBody);
}

/**
 * Get email sending statistics
 *
 * @param int $days Number of days to look back (default: 7)
 * @return array Statistics array
 */
function getEmailStats($days = 7) {
    $dbHost = 'localhost';
    $dbUser = 'root';
    $dbPass = '';
    $dbName = 'mg_skill';

    $conn = new mysqli($dbHost, $dbUser, $dbPass, $dbName);

    if ($conn->connect_error) {
        return [
            'total' => 0,
            'success' => 0,
            'failed' => 0,
            'success_rate' => 0
        ];
    }

    $sql = "SELECT
                COUNT(*) as total,
                SUM(CASE WHEN status = 'success' THEN 1 ELSE 0 END) as success,
                SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed
            FROM email_logs
            WHERE sent_at >= DATE_SUB(NOW(), INTERVAL $days DAY)";

    $result = $conn->query($sql);
    $stats = $result->fetch_assoc();

    $conn->close();

    return [
        'total' => (int)$stats['total'],
        'success' => (int)$stats['success'],
        'failed' => (int)$stats['failed'],
        'success_rate' => $stats['total'] > 0 ? round(($stats['success'] / $stats['total']) * 100, 2) : 0
    ];
}

/**
 * Quick email templates
 */

/**
 * Send welcome email
 */
function sendWelcomeEmail($to, $userName) {
    $subject = 'Welcome to MG Education!';
    $htmlBody = '
    <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background: #f8fafc;">
        <div style="background: #fff; border-radius: 12px; padding: 30px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            <h2 style="color: #0b1020; margin-bottom: 20px;">Welcome to MG Education!</h2>
            <p style="color: #4b5563; line-height: 1.6; margin-bottom: 15px;">
                Hi ' . htmlspecialchars($userName) . ',
            </p>
            <p style="color: #4b5563; line-height: 1.6; margin-bottom: 15px;">
                Thank you for joining MG Education Portal! We are excited to have you as part of our learning community.
            </p>
            <p style="color: #4b5563; line-height: 1.6; margin-bottom: 15px;">
                Start exploring our courses and begin your learning journey today!
            </p>
            <div style="text-align: center; margin: 30px 0;">
                <a href="' . getBaseUrl() . '" style="display: inline-block; padding: 12px 30px; background: #6f75ff; color: #fff; text-decoration: none; border-radius: 8px; font-weight: 600;">
                    Explore Courses
                </a>
            </div>
            <p style="color: #6b7280; font-size: 13px; margin-top: 30px; padding-top: 20px; border-top: 1px solid #e5e7eb;">
                MG Education Portal | Your Learning Partner
            </p>
        </div>
    </div>
    ';

    return sendEmail($to, $subject, '', $htmlBody);
}

/**
 * Send password reset email
 */
function sendPasswordResetEmail($to, $resetLink) {
    $subject = 'Password Reset Request';
    $htmlBody = '
    <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background: #f8fafc;">
        <div style="background: #fff; border-radius: 12px; padding: 30px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            <h2 style="color: #0b1020; margin-bottom: 20px;">Password Reset Request</h2>
            <p style="color: #4b5563; line-height: 1.6; margin-bottom: 15px;">
                We received a request to reset your password. Click the button below to create a new password:
            </p>
            <div style="text-align: center; margin: 30px 0;">
                <a href="' . htmlspecialchars($resetLink) . '" style="display: inline-block; padding: 12px 30px; background: #22c55e; color: #fff; text-decoration: none; border-radius: 8px; font-weight: 600;">
                    Reset Password
                </a>
            </div>
            <p style="color: #4b5563; line-height: 1.6; margin-bottom: 15px;">
                If you did not request a password reset, please ignore this email or contact support if you have concerns.
            </p>
            <p style="color: #6b7280; font-size: 13px;">
                This link will expire in 24 hours.
            </p>
            <p style="color: #6b7280; font-size: 13px; margin-top: 30px; padding-top: 20px; border-top: 1px solid #e5e7eb;">
                MG Education Portal | Support Team
            </p>
        </div>
    </div>
    ';

    return sendEmail($to, $subject, '', $htmlBody);
}

/**
 * Send notification email
 */
function sendNotificationEmail($to, $title, $message) {
    $subject = $title;
    $htmlBody = '
    <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background: #f8fafc;">
        <div style="background: #fff; border-radius: 12px; padding: 30px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            <h2 style="color: #0b1020; margin-bottom: 20px;">' . htmlspecialchars($title) . '</h2>
            <p style="color: #4b5563; line-height: 1.6; margin-bottom: 15px;">
                ' . nl2br(htmlspecialchars($message)) . '
            </p>
            <p style="color: #6b7280; font-size: 13px; margin-top: 30px; padding-top: 20px; border-top: 1px solid #e5e7eb;">
                MG Education Portal | Notification System
            </p>
        </div>
    </div>
    ';

    return sendEmail($to, $subject, '', $htmlBody);
}

/**
 * Get base URL of the application
 */
function getBaseUrl() {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
    return $scheme . '://' . $host;
}
