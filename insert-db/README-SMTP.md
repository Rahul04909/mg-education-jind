# SMTP Email System Setup Guide

## Overview
This guide will help you set up the SMTP email configuration system for MG Education Portal. The system allows administrators to configure email server settings and send test emails to verify the configuration.

## Features
- ✅ Complete SMTP configuration management
- ✅ Support for popular email providers (Gmail, Outlook, Yahoo, SendGrid, etc.)
- ✅ Test email functionality
- ✅ Email sending logs
- ✅ Secure password storage
- ✅ Beautiful admin UI matching the portal design
- ✅ Responsive design for all devices

## Installation Steps

### Step 1: Database Setup
Run the database installation script to create the required tables.

**Option A: Via Browser**
1. Open your browser and navigate to:
   ```
   http://localhost/mg-skill/insert-db/insert-smtp.php
   ```
2. The script will automatically create two tables:
   - `smtp_settings` - Stores SMTP configuration
   - `email_logs` - Stores email sending history
3. Follow the on-screen instructions

**Option B: Via MySQL Command Line**
```sql
-- Run these commands in your MySQL console

USE mg_skill;

-- Create SMTP settings table
CREATE TABLE IF NOT EXISTS `smtp_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `smtp_host` varchar(255) NOT NULL,
  `smtp_port` int(11) NOT NULL DEFAULT 587,
  `smtp_username` varchar(255) NOT NULL,
  `smtp_password` varchar(255) NOT NULL,
  `smtp_encryption` enum('tls','ssl','none') NOT NULL DEFAULT 'tls',
  `from_email` varchar(255) NOT NULL,
  `from_name` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create email logs table
CREATE TABLE IF NOT EXISTS `email_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `to_email` varchar(255) NOT NULL,
  `subject` varchar(500) NOT NULL,
  `message` text NOT NULL,
  `status` enum('success','failed') NOT NULL,
  `error_message` text DEFAULT NULL,
  `sent_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`),
  KEY `idx_sent_at` (`sent_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default settings
INSERT INTO `smtp_settings`
  (`smtp_host`, `smtp_port`, `smtp_username`, `smtp_password`, `smtp_encryption`, `from_email`, `from_name`, `is_active`)
VALUES
  ('smtp.gmail.com', 587, '', '', 'tls', 'noreply@mgedu.com', 'MG Education', 1);
```

### Step 2: Access SMTP Settings
1. Log in to the admin panel
2. Navigate to: **Dashboard → Settings → SMTP Settings**
3. Or directly access: `http://localhost/mg-skill/admin/settings/smtp-settings.php`

### Step 3: Configure SMTP Settings
Fill in the following information based on your email provider:

#### For Gmail:
- **SMTP Host:** smtp.gmail.com
- **SMTP Port:** 587
- **Encryption:** TLS
- **Username:** your-email@gmail.com
- **Password:** Your Gmail App Password (see below)
- **From Email:** your-email@gmail.com
- **From Name:** MG Education

**Important for Gmail Users:**
You need to use an "App Password" instead of your regular Gmail password.

To generate an App Password:
1. Go to your Google Account settings
2. Select Security
3. Enable 2-Step Verification (if not already enabled)
4. Go to "App passwords"
5. Generate a new app password for "Mail"
6. Use this 16-character password in the SMTP settings

#### For Outlook/Hotmail:
- **SMTP Host:** smtp-mail.outlook.com
- **SMTP Port:** 587
- **Encryption:** TLS
- **Username:** your-email@outlook.com
- **Password:** Your Outlook password

#### For Yahoo:
- **SMTP Host:** smtp.mail.yahoo.com
- **SMTP Port:** 587
- **Encryption:** TLS
- **Username:** your-email@yahoo.com
- **Password:** Your Yahoo App Password

#### For SendGrid:
- **SMTP Host:** smtp.sendgrid.net
- **SMTP Port:** 587
- **Encryption:** TLS
- **Username:** apikey
- **Password:** Your SendGrid API Key

### Step 4: Test Your Configuration
1. After saving your SMTP settings, scroll down to the "Send Test Email" section
2. Enter a test email address
3. Click "Send Test Email"
4. Check the email inbox for the test email
5. Check the "Email Logs" panel to see the sending status

## Database Configuration

The database connection settings are configured in both files:
- `insert-db/insert-smtp.php` (line 8-11)
- `admin/settings/smtp-settings.php` (line 2-5)

**Default settings:**
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'mg_skill');
```

If your database settings are different, update these values in both files.

## Troubleshooting

### Common Issues

**1. Connection Failed Error**
- Check if your database credentials are correct
- Ensure MySQL server is running
- Verify the database name exists

**2. Test Email Not Sending**
- Verify SMTP credentials are correct
- Check if the SMTP port is not blocked by firewall
- For Gmail, ensure you're using an App Password
- Check the Email Logs for error details

**3. SSL/TLS Certificate Error**
- Try switching between TLS and SSL encryption
- For development, you might need to disable SSL verification (not recommended for production)

**4. Authentication Failed**
- Double-check username and password
- For Gmail, use App Password instead of regular password
- Ensure 2FA is properly configured if required by your provider

**5. Tables Not Created**
- Check MySQL user permissions
- Ensure the user has CREATE TABLE privileges
- Check error messages in the installation script

### Debug Mode
To enable debug mode and see detailed error messages, you can modify the PHPMailer configuration in `smtp-settings.php`:

```php
$mail->SMTPDebug = 2; // Add this line after $mail = new PHPMailer(true);
```

## Security Recommendations

1. **Use Environment Variables:** Consider moving database credentials to environment variables
2. **Secure Passwords:** Use strong, unique passwords for SMTP accounts
3. **App Passwords:** Always use app-specific passwords instead of main account passwords
4. **SSL/TLS:** Always use encryption (TLS or SSL) for SMTP connections
5. **Access Control:** Restrict access to the SMTP settings page to super administrators only
6. **Regular Updates:** Keep PHPMailer library updated to the latest version

## File Structure

```
mg-skill/
├── admin/
│   ├── settings/
│   │   └── smtp-settings.php      (SMTP configuration interface)
│   └── sidebar.php                 (Updated with SMTP Settings link)
├── insert-db/
│   ├── insert-smtp.php            (Database installation script)
│   └── README-SMTP.md             (This file)
└── vendor/
    └── phpmailer/                 (PHPMailer library - already installed)
```

## Features in Detail

### SMTP Configuration Form
- Host, Port, and Encryption settings
- Username and password (with show/hide toggle)
- From email and name configuration
- Enable/disable toggle for email system
- Helpful tooltips for each field

### Test Email Feature
- Send test emails to verify configuration
- Beautiful HTML email template
- Success/failure notifications
- Automatic logging of test results

### Email Logs
- View recent email sending history
- Status indicators (success/failed)
- Timestamp for each email
- Recipient email address tracking

### User Interface
- Matches admin dashboard color scheme
- Responsive design for mobile devices
- Sidebar integration
- Modern, clean design
- Smooth animations and transitions

## Support

For issues or questions:
1. Check the Email Logs in the admin panel
2. Review the troubleshooting section above
3. Check PHPMailer documentation: https://github.com/PHPMailer/PHPMailer
4. Verify email provider's SMTP documentation

## Version History

**Version 1.0.0** (Current)
- Initial release
- SMTP configuration management
- Test email functionality
- Email logs tracking
- Support for popular email providers

## Credits

- **PHPMailer:** Email sending library
- **MG Education Portal:** Educational platform system
- **Developer:** Created for MG Skill educational portal

---

**Note:** After successful installation, you can delete the `insert-db/insert-smtp.php` file for security reasons, or move it outside the web root directory.