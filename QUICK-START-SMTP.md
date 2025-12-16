# SMTP System Quick Start Guide

## 🚀 Get Started in 3 Steps

### Step 1: Install Database Tables (2 minutes)
Open your browser and navigate to:
```
http://localhost/mg-skill/insert-db/insert-smtp.php
```

This will automatically create:
- `smtp_settings` table
- `email_logs` table
- Default configuration

**Expected Result:** Green success messages confirming table creation.

---

### Step 2: Configure SMTP Settings (5 minutes)

1. Go to Admin Panel: `http://localhost/mg-skill/admin/index.php`
2. Click **Settings → SMTP Settings** in the sidebar
3. Fill in your email provider details

#### Gmail Example:
```
SMTP Host:       smtp.gmail.com
SMTP Port:       587
Encryption:      TLS
Username:        your-email@gmail.com
Password:        [Your Gmail App Password]
From Email:      your-email@gmail.com
From Name:       MG Education
☑ Enable SMTP
```

**Important for Gmail:**
1. Enable 2-Step Verification in Google Account
2. Go to Security → App Passwords
3. Generate new app password for "Mail"
4. Use the 16-character password (not your Gmail password)

4. Click **Save Settings**

---

### Step 3: Test Your Configuration (1 minute)

1. Scroll down to "Send Test Email" section
2. Enter your email address
3. Click **Send Test Email**
4. Check your inbox!

**Expected Result:** Beautiful test email in your inbox + green success message.

---

## 📧 Send Your First Email

Create a new PHP file or add to existing code:

```php
<?php
require_once __DIR__ . '/includes/email-helper.php';

// Simple email
$result = sendEmail(
    'user@example.com',
    'Hello from MG Education',
    'This is a test email',
    '<h1>Hello!</h1><p>This is a <strong>test</strong> email.</p>'
);

if ($result['success']) {
    echo "Email sent successfully!";
} else {
    echo "Error: " . $result['error'];
}
?>
```

---

## 🎯 Common Use Cases

### Welcome New User
```php
sendWelcomeEmail('newuser@example.com', 'John Doe');
```

### Password Reset
```php
$resetLink = 'https://mgedu.com/reset?token=abc123';
sendPasswordResetEmail('user@example.com', $resetLink);
```

### Send Notification
```php
sendNotificationEmail(
    'student@example.com',
    'New Course Available',
    'Check out our latest course on PHP development!'
);
```

### Custom Email with Attachments
```php
sendEmail(
    'student@example.com',
    'Your Certificate',
    'Certificate attached',
    '<h2>Congratulations!</h2>',
    ['/path/to/certificate.pdf']
);
```

---

## 🔍 View Email Logs

1. Go to **Admin → Settings → SMTP Settings**
2. Scroll down to see "Email Logs" table
3. View recent emails with status (success/failed)

---

## ⚙️ SMTP Providers Quick Setup

### Gmail
- Host: `smtp.gmail.com`
- Port: `587`
- Encryption: `TLS`
- Password: Use App Password

### Outlook/Hotmail
- Host: `smtp-mail.outlook.com`
- Port: `587`
- Encryption: `TLS`
- Password: Your Outlook password

### Yahoo
- Host: `smtp.mail.yahoo.com`
- Port: `587`
- Encryption: `TLS`
- Password: Use App Password

### SendGrid (Recommended for production)
- Host: `smtp.sendgrid.net`
- Port: `587`
- Encryption: `TLS`
- Username: `apikey`
- Password: Your SendGrid API key

---

## 🛠️ Troubleshooting

### Email not sending?

**1. Check SMTP credentials**
- Verify username and password
- For Gmail, use App Password (not regular password)

**2. Check email logs**
- Go to Admin → Settings → SMTP Settings
- Check "Email Logs" section for error messages

**3. Common errors:**

| Error | Solution |
|-------|----------|
| Authentication failed | Check username/password |
| Connection timeout | Check port and firewall |
| SSL/TLS error | Try different encryption (TLS/SSL) |
| SMTP not configured | Save settings first |

**4. Test connection**
```php
// Add to test file
$result = sendEmail('your-email@example.com', 'Test', 'Testing');
print_r($result);  // See detailed error
```

---

## 📱 Access Points

| What | Where |
|------|-------|
| Install Database | `/insert-db/insert-smtp.php` |
| SMTP Settings | `/admin/settings/smtp-settings.php` |
| Email Helper | `/includes/email-helper.php` |
| Examples | `/includes/email-example-usage.php` |
| Full Docs | `/insert-db/README-SMTP.md` |

---

## ✅ Verification Checklist

After setup, verify:
- [ ] Database tables created successfully
- [ ] SMTP settings saved in admin panel
- [ ] Test email received in inbox
- [ ] Email appears in logs with "success" status
- [ ] Helper functions work in your code
- [ ] No error messages in logs

---

## 💡 Pro Tips

1. **Gmail Users:** Always use App Password, never your main password
2. **Testing:** Test with your own email first
3. **Production:** Use professional SMTP service (SendGrid, AWS SES, etc.)
4. **Monitoring:** Check email logs regularly
5. **Security:** Keep SMTP credentials secure
6. **Spam:** Add SPF/DKIM records to your domain
7. **Rate Limiting:** Don't send too many emails at once
8. **Templates:** Use HTML templates for professional emails

---

## 🎓 Learn More

- **Full Documentation:** `insert-db/README-SMTP.md`
- **Code Examples:** `includes/email-example-usage.php`
- **Helper Functions:** `includes/email-helper.php`
- **PHPMailer Docs:** https://github.com/PHPMailer/PHPMailer

---

## 📊 Quick Stats

Check email performance:
```php
$stats = getEmailStats(7);  // Last 7 days
echo "Total: {$stats['total']}";
echo "Success Rate: {$stats['success_rate']}%";
```

---

## 🆘 Need Help?

1. Check email logs in admin panel
2. Read full documentation in `README-SMTP.md`
3. Review example usage file
4. Check PHPMailer documentation
5. Verify email provider's SMTP settings

---

## 🎉 You're All Set!

Your SMTP email system is now ready to use. Start sending emails from anywhere in your application using the helper functions!

**Happy Emailing! 📧**

---

**Quick Reference Card**

```php
// Include helper
require_once 'includes/email-helper.php';

// Simple email
sendEmail($to, $subject, $body, $htmlBody);

// Welcome email
sendWelcomeEmail($email, $name);

// Password reset
sendPasswordResetEmail($email, $resetLink);

// Notification
sendNotificationEmail($email, $title, $message);

// Bulk email
sendBulkEmail($recipients, $subject, $body, $htmlBody);

// Get stats
getEmailStats($days);
```

---

**Total Setup Time:** ~10 minutes
**Difficulty:** Easy
**Status:** Production Ready ✅