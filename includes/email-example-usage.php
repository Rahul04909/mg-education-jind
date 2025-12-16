<?php
/**
 * Email Helper Usage Examples
 * This file demonstrates how to use the email helper functions
 * throughout the MG Education Portal application
 */

// Include the email helper
require_once __DIR__ . '/email-helper.php';

// ============================================
// Example 1: Send a Simple Email
// ============================================
echo "<h2>Example 1: Send Simple Email</h2>";

$result = sendEmail(
    'user@example.com',                    // To
    'Hello from MG Education',             // Subject
    'This is a plain text email.',         // Body (plain text)
    '<h1>This is an HTML email</h1>'      // HTML Body (optional)
);

if ($result['success']) {
    echo "✓ Email sent successfully!<br>";
} else {
    echo "✗ Failed to send email: " . $result['error'] . "<br>";
}

// ============================================
// Example 2: Send Welcome Email
// ============================================
echo "<h2>Example 2: Send Welcome Email</h2>";

$welcomeResult = sendWelcomeEmail(
    'newuser@example.com',
    'John Doe'
);

if ($welcomeResult['success']) {
    echo "✓ Welcome email sent!<br>";
} else {
    echo "✗ Failed: " . $welcomeResult['error'] . "<br>";
}

// ============================================
// Example 3: Send Password Reset Email
// ============================================
echo "<h2>Example 3: Send Password Reset Email</h2>";

$resetLink = 'https://mgedu.com/reset-password?token=abc123xyz';
$resetResult = sendPasswordResetEmail(
    'user@example.com',
    $resetLink
);

if ($resetResult['success']) {
    echo "✓ Password reset email sent!<br>";
} else {
    echo "✗ Failed: " . $resetResult['error'] . "<br>";
}

// ============================================
// Example 4: Send Notification Email
// ============================================
echo "<h2>Example 4: Send Notification Email</h2>";

$notifResult = sendNotificationEmail(
    'student@example.com',
    'New Course Available',
    'A new course "Advanced PHP Development" is now available. Enroll now to start learning!'
);

if ($notifResult['success']) {
    echo "✓ Notification sent!<br>";
} else {
    echo "✗ Failed: " . $notifResult['error'] . "<br>";
}

// ============================================
// Example 5: Send Email with Attachments
// ============================================
echo "<h2>Example 5: Send Email with Attachments</h2>";

$attachResult = sendEmail(
    'recipient@example.com',
    'Course Certificate',
    'Please find your course completion certificate attached.',
    '<h2>Congratulations!</h2><p>Your certificate is attached.</p>',
    [
        __DIR__ . '/../uploads/certificate.pdf',
        __DIR__ . '/../uploads/syllabus.pdf'
    ]
);

if ($attachResult['success']) {
    echo "✓ Email with attachments sent!<br>";
} else {
    echo "✗ Failed: " . $attachResult['error'] . "<br>";
}

// ============================================
// Example 6: Send Bulk Emails
// ============================================
echo "<h2>Example 6: Send Bulk Emails</h2>";

$recipients = [
    'student1@example.com',
    'student2@example.com',
    'student3@example.com',
    'student4@example.com'
];

$bulkResult = sendBulkEmail(
    $recipients,
    'Important Announcement',
    'This is a bulk email to all students.',
    '<h2>Important Announcement</h2><p>Please read this carefully.</p>'
);

echo "Total: {$bulkResult['total']}, Success: {$bulkResult['success']}, Failed: {$bulkResult['failed']}<br>";

foreach ($bulkResult['results'] as $result) {
    echo "• {$result['email']}: {$result['status']}<br>";
}

// ============================================
// Example 7: Send Email with Reply-To
// ============================================
echo "<h2>Example 7: Send Email with Reply-To</h2>";

$replyResult = sendEmail(
    'customer@example.com',
    'Support Ticket Response',
    'Your ticket has been resolved.',
    '<h2>Support Response</h2><p>Your issue has been resolved.</p>',
    [],
    'support@mgedu.com'  // Reply-to address
);

if ($replyResult['success']) {
    echo "✓ Email with reply-to sent!<br>";
} else {
    echo "✗ Failed: " . $replyResult['error'] . "<br>";
}

// ============================================
// Example 8: Get Email Statistics
// ============================================
echo "<h2>Example 8: Email Statistics (Last 7 Days)</h2>";

$stats = getEmailStats(7);  // Last 7 days

echo "Total Emails: {$stats['total']}<br>";
echo "Successful: {$stats['success']}<br>";
echo "Failed: {$stats['failed']}<br>";
echo "Success Rate: {$stats['success_rate']}%<br>";

// ============================================
// Example 9: Course Enrollment Confirmation
// ============================================
echo "<h2>Example 9: Course Enrollment Confirmation</h2>";

function sendEnrollmentConfirmation($studentEmail, $studentName, $courseName, $courseLink) {
    $subject = 'Course Enrollment Confirmation';
    $htmlBody = '
    <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background: #f8fafc;">
        <div style="background: #fff; border-radius: 12px; padding: 30px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            <h2 style="color: #0b1020; margin-bottom: 20px;">Enrollment Confirmed!</h2>
            <p style="color: #4b5563; line-height: 1.6; margin-bottom: 15px;">
                Hi ' . htmlspecialchars($studentName) . ',
            </p>
            <p style="color: #4b5563; line-height: 1.6; margin-bottom: 15px;">
                You have successfully enrolled in <strong>' . htmlspecialchars($courseName) . '</strong>.
            </p>
            <div style="background: #d1fae5; border-left: 4px solid #22c55e; padding: 15px; border-radius: 8px; margin: 20px 0;">
                <p style="color: #065f46; margin: 0; font-weight: 600;">✓ Enrollment Successful</p>
            </div>
            <div style="text-align: center; margin: 30px 0;">
                <a href="' . htmlspecialchars($courseLink) . '" style="display: inline-block; padding: 12px 30px; background: #6f75ff; color: #fff; text-decoration: none; border-radius: 8px; font-weight: 600;">
                    Start Learning
                </a>
            </div>
            <p style="color: #4b5563; line-height: 1.6; margin-bottom: 15px;">
                Happy learning!
            </p>
            <p style="color: #6b7280; font-size: 13px; margin-top: 30px; padding-top: 20px; border-top: 1px solid #e5e7eb;">
                MG Education Portal | Student Services
            </p>
        </div>
    </div>
    ';

    return sendEmail($studentEmail, $subject, '', $htmlBody);
}

$enrollResult = sendEnrollmentConfirmation(
    'student@example.com',
    'Jane Smith',
    'Web Development Bootcamp',
    'https://mgedu.com/courses/web-dev-bootcamp'
);

if ($enrollResult['success']) {
    echo "✓ Enrollment confirmation sent!<br>";
} else {
    echo "✗ Failed: " . $enrollResult['error'] . "<br>";
}

// ============================================
// Example 10: Assignment Submission Confirmation
// ============================================
echo "<h2>Example 10: Assignment Submission Confirmation</h2>";

function sendAssignmentConfirmation($studentEmail, $studentName, $assignmentName, $submissionDate) {
    $subject = 'Assignment Submission Received';
    $htmlBody = '
    <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background: #f8fafc;">
        <div style="background: #fff; border-radius: 12px; padding: 30px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            <h2 style="color: #0b1020; margin-bottom: 20px;">Assignment Submitted Successfully</h2>
            <p style="color: #4b5563; line-height: 1.6; margin-bottom: 15px;">
                Hi ' . htmlspecialchars($studentName) . ',
            </p>
            <p style="color: #4b5563; line-height: 1.6; margin-bottom: 15px;">
                Your assignment <strong>' . htmlspecialchars($assignmentName) . '</strong> has been submitted successfully.
            </p>
            <div style="background: #dbeafe; border-left: 4px solid #3b82f6; padding: 15px; border-radius: 8px; margin: 20px 0;">
                <p style="color: #1e40af; margin: 0;"><strong>Submission Date:</strong> ' . htmlspecialchars($submissionDate) . '</p>
            </div>
            <p style="color: #4b5563; line-height: 1.6; margin-bottom: 15px;">
                Your instructor will review and grade your submission. You will receive a notification once grading is complete.
            </p>
            <p style="color: #6b7280; font-size: 13px; margin-top: 30px; padding-top: 20px; border-top: 1px solid #e5e7eb;">
                MG Education Portal | Academic Services
            </p>
        </div>
    </div>
    ';

    return sendEmail($studentEmail, $subject, '', $htmlBody);
}

$assignmentResult = sendAssignmentConfirmation(
    'student@example.com',
    'John Doe',
    'PHP Project - E-commerce Website',
    date('F d, Y H:i A')
);

if ($assignmentResult['success']) {
    echo "✓ Assignment confirmation sent!<br>";
} else {
    echo "✗ Failed: " . $assignmentResult['error'] . "<br>";
}

// ============================================
// Real-World Integration Examples
// ============================================

echo "<h2>Real-World Integration Examples</h2>";

// Example: User Registration
function onUserRegistration($email, $username, $verificationLink) {
    $subject = 'Welcome to MG Education - Verify Your Email';
    $htmlBody = '
    <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background: #f8fafc;">
        <div style="background: #fff; border-radius: 12px; padding: 30px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            <h2 style="color: #0b1020; margin-bottom: 20px;">Welcome to MG Education!</h2>
            <p style="color: #4b5563; line-height: 1.6; margin-bottom: 15px;">
                Hi ' . htmlspecialchars($username) . ',
            </p>
            <p style="color: #4b5563; line-height: 1.6; margin-bottom: 15px;">
                Thank you for registering with MG Education Portal. To complete your registration, please verify your email address.
            </p>
            <div style="text-align: center; margin: 30px 0;">
                <a href="' . htmlspecialchars($verificationLink) . '" style="display: inline-block; padding: 12px 30px; background: #22c55e; color: #fff; text-decoration: none; border-radius: 8px; font-weight: 600;">
                    Verify Email Address
                </a>
            </div>
            <p style="color: #6b7280; font-size: 13px;">
                This link will expire in 48 hours.
            </p>
            <p style="color: #6b7280; font-size: 13px; margin-top: 30px; padding-top: 20px; border-top: 1px solid #e5e7eb;">
                MG Education Portal | Registration Team
            </p>
        </div>
    </div>
    ';

    return sendEmail($email, $subject, '', $htmlBody);
}

// Example: Payment Confirmation
function sendPaymentConfirmation($email, $userName, $courseName, $amount, $transactionId) {
    $subject = 'Payment Confirmation - MG Education';
    $htmlBody = '
    <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background: #f8fafc;">
        <div style="background: #fff; border-radius: 12px; padding: 30px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            <h2 style="color: #0b1020; margin-bottom: 20px;">Payment Successful!</h2>
            <p style="color: #4b5563; line-height: 1.6; margin-bottom: 15px;">
                Hi ' . htmlspecialchars($userName) . ',
            </p>
            <p style="color: #4b5563; line-height: 1.6; margin-bottom: 15px;">
                Your payment has been processed successfully.
            </p>
            <div style="background: #f3f4f6; border-radius: 8px; padding: 20px; margin: 20px 0;">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 8px 0; color: #6b7280;"><strong>Course:</strong></td>
                        <td style="padding: 8px 0; color: #0b1020; text-align: right;">' . htmlspecialchars($courseName) . '</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; color: #6b7280;"><strong>Amount:</strong></td>
                        <td style="padding: 8px 0; color: #0b1020; text-align: right;">₹' . number_format($amount, 2) . '</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; color: #6b7280;"><strong>Transaction ID:</strong></td>
                        <td style="padding: 8px 0; color: #0b1020; text-align: right;">' . htmlspecialchars($transactionId) . '</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; color: #6b7280;"><strong>Date:</strong></td>
                        <td style="padding: 8px 0; color: #0b1020; text-align: right;">' . date('F d, Y H:i A') . '</td>
                    </tr>
                </table>
            </div>
            <p style="color: #6b7280; font-size: 13px; margin-top: 30px; padding-top: 20px; border-top: 1px solid #e5e7eb;">
                MG Education Portal | Billing Department
            </p>
        </div>
    </div>
    ';

    return sendEmail($email, $subject, '', $htmlBody);
}

echo "<p>Integration examples defined. Use these functions in your user registration, payment, and course management modules.</p>";

// ============================================
// Tips and Best Practices
// ============================================
echo "<h2>Tips and Best Practices</h2>";
echo "<ul>";
echo "<li>Always check the result array for success/failure status</li>";
echo "<li>Log email sending errors for debugging</li>";
echo "<li>Use HTML templates for better-looking emails</li>";
echo "<li>Include both HTML and plain text versions</li>";
echo "<li>Test emails thoroughly before sending to real users</li>";
echo "<li>Monitor email statistics to track deliverability</li>";
echo "<li>Keep email subjects clear and concise</li>";
echo "<li>Personalize emails with user names and relevant data</li>";
echo "<li>Include unsubscribe links for marketing emails</li>";
echo "<li>Use appropriate email templates for different purposes</li>";
echo "</ul>";

?>
