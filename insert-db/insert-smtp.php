<?php
/**
 * SMTP Settings Database Installation Script
 * This script creates the necessary database table for SMTP configuration
 * Run this file once to set up the SMTP settings table
 */

// Database configuration - Update these values according to your setup
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'mg_skill');

// Create database connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>SMTP Database Installation</title>
    <style>
        :root {
            --success: #22c55e;
            --error: #ef4444;
            --warning: #f59e0b;
            --info: #3b82f6;
            --bg: #f8fafc;
            --text: #0b1020;
            --line: #e6e8ee;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            width: 100%;
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 40px;
        }
        h1 {
            color: var(--text);
            font-size: 28px;
            font-weight: 800;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .subtitle {
            color: #6b7280;
            margin-bottom: 30px;
            font-size: 15px;
        }
        .message {
            padding: 16px 20px;
            border-radius: 12px;
            margin-bottom: 16px;
            display: flex;
            align-items: flex-start;
            gap: 12px;
            border: 1px solid;
        }
        .message.success {
            background: #d1fae5;
            border-color: #86efac;
            color: #065f46;
        }
        .message.error {
            background: #fee2e2;
            border-color: #fca5a5;
            color: #991b1b;
        }
        .message.warning {
            background: #fef3c7;
            border-color: #fde047;
            color: #92400e;
        }
        .message.info {
            background: #dbeafe;
            border-color: #93c5fd;
            color: #1e40af;
        }
        .icon {
            width: 24px;
            height: 24px;
            flex-shrink: 0;
        }
        .icon.success {
            stroke: #22c55e;
        }
        .icon.error {
            stroke: #ef4444;
        }
        .icon.warning {
            stroke: #f59e0b;
        }
        .icon.info {
            stroke: #3b82f6;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 700;
            border: none;
            cursor: pointer;
            font-size: 15px;
            transition: all 0.2s ease;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        .btn-success {
            background: var(--success);
            color: #fff;
        }
        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(34, 197, 94, 0.3);
        }
        .actions {
            display: flex;
            gap: 12px;
            margin-top: 30px;
            flex-wrap: wrap;
        }
        code {
            background: #f3f4f6;
            padding: 2px 8px;
            border-radius: 6px;
            font-family: 'Courier New', monospace;
            font-size: 13px;
            color: #dc2626;
        }
        .sql-block {
            background: #1e293b;
            color: #e2e8f0;
            padding: 20px;
            border-radius: 12px;
            overflow-x: auto;
            margin: 20px 0;
            font-family: 'Courier New', monospace;
            font-size: 13px;
            line-height: 1.6;
        }
        .details {
            background: var(--bg);
            padding: 20px;
            border-radius: 12px;
            margin: 20px 0;
        }
        .details h3 {
            color: var(--text);
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 12px;
        }
        .details ul {
            list-style: none;
            padding: 0;
        }
        .details li {
            padding: 8px 0;
            border-bottom: 1px solid var(--line);
            color: #4b5563;
        }
        .details li:last-child {
            border-bottom: none;
        }
        .details strong {
            color: var(--text);
        }
    </style>
</head>
<body>
    <div class='container'>";

// SQL to create SMTP settings table
$sql_create_table = "CREATE TABLE IF NOT EXISTS `smtp_settings` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

// SQL to create email logs table
$sql_create_logs = "CREATE TABLE IF NOT EXISTS `email_logs` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

echo "<h1>
    <svg class='icon info' fill='none' stroke='currentColor' viewBox='0 0 24 24'>
        <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4'></path>
    </svg>
    SMTP Database Installation
</h1>
<p class='subtitle'>Setting up database tables for SMTP email configuration system</p>";

$success_count = 0;
$error_count = 0;
$messages = [];

// Create smtp_settings table
if ($conn->query($sql_create_table) === TRUE) {
    $success_count++;
    $messages[] = [
        'type' => 'success',
        'text' => 'Table <code>smtp_settings</code> created successfully or already exists.'
    ];
} else {
    $error_count++;
    $messages[] = [
        'type' => 'error',
        'text' => 'Error creating smtp_settings table: ' . $conn->error
    ];
}

// Create email_logs table
if ($conn->query($sql_create_logs) === TRUE) {
    $success_count++;
    $messages[] = [
        'type' => 'success',
        'text' => 'Table <code>email_logs</code> created successfully or already exists.'
    ];
} else {
    $error_count++;
    $messages[] = [
        'type' => 'error',
        'text' => 'Error creating email_logs table: ' . $conn->error
    ];
}

// Check if default SMTP settings exist
$check_sql = "SELECT COUNT(*) as count FROM smtp_settings";
$result = $conn->query($check_sql);
$row = $result->fetch_assoc();

if ($row['count'] == 0) {
    // Insert default SMTP settings
    $insert_sql = "INSERT INTO `smtp_settings`
        (`smtp_host`, `smtp_port`, `smtp_username`, `smtp_password`, `smtp_encryption`, `from_email`, `from_name`, `is_active`)
        VALUES
        ('smtp.gmail.com', 587, '', '', 'tls', 'noreply@mgedu.com', 'MG Education', 1)";

    if ($conn->query($insert_sql) === TRUE) {
        $success_count++;
        $messages[] = [
            'type' => 'success',
            'text' => 'Default SMTP configuration inserted successfully. Please update the settings in admin panel.'
        ];
    } else {
        $error_count++;
        $messages[] = [
            'type' => 'error',
            'text' => 'Error inserting default settings: ' . $conn->error
        ];
    }
} else {
    $messages[] = [
        'type' => 'info',
        'text' => 'SMTP settings already exist in the database. No default data inserted.'
    ];
}

// Display messages
foreach ($messages as $message) {
    $icon_path = '';
    switch ($message['type']) {
        case 'success':
            $icon_path = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>';
            break;
        case 'error':
            $icon_path = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>';
            break;
        case 'warning':
            $icon_path = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>';
            break;
        case 'info':
            $icon_path = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>';
            break;
    }

    echo "<div class='message {$message['type']}'>
        <svg class='icon {$message['type']}' fill='none' stroke='currentColor' viewBox='0 0 24 24'>
            {$icon_path}
        </svg>
        <div>{$message['text']}</div>
    </div>";
}

// Display summary
echo "<div class='details'>
    <h3>Installation Summary</h3>
    <ul>
        <li><strong>Database:</strong> " . DB_NAME . "</li>
        <li><strong>Host:</strong> " . DB_HOST . "</li>
        <li><strong>Tables Created:</strong> smtp_settings, email_logs</li>
        <li><strong>Successful Operations:</strong> {$success_count}</li>
        <li><strong>Failed Operations:</strong> {$error_count}</li>
        <li><strong>Status:</strong> " . ($error_count == 0 ? '<span style="color: var(--success); font-weight: 700;">✓ Installation Complete</span>' : '<span style="color: var(--error); font-weight: 700;">✗ Installation Failed</span>') . "</li>
    </ul>
</div>";

// Display table structure
echo "<div class='details'>
    <h3>Table Structure: smtp_settings</h3>
    <div class='sql-block'>
    " . nl2br(htmlspecialchars($sql_create_table)) . "
    </div>
</div>";

echo "<div class='details'>
    <h3>Table Structure: email_logs</h3>
    <div class='sql-block'>
    " . nl2br(htmlspecialchars($sql_create_logs)) . "
    </div>
</div>";

if ($error_count == 0) {
    echo "<div class='message success'>
        <svg class='icon success' fill='none' stroke='currentColor' viewBox='0 0 24 24'>
            <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'></path>
        </svg>
        <div>
            <strong>Installation completed successfully!</strong><br>
            You can now configure your SMTP settings in the admin panel.
        </div>
    </div>";
}

echo "<div class='actions'>
    <a href='../admin/settings/smtp-settings.php' class='btn btn-success'>
        <svg style='width: 20px; height: 20px;' fill='none' stroke='currentColor' viewBox='0 0 24 24'>
            <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z'></path>
            <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M15 12a3 3 0 11-6 0 3 3 0 016 0z'></path>
        </svg>
        Go to SMTP Settings
    </a>
    <a href='../admin/index.php' class='btn btn-primary'>
        <svg style='width: 20px; height: 20px;' fill='none' stroke='currentColor' viewBox='0 0 24 24'>
            <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'></path>
        </svg>
        Go to Dashboard
    </a>
</div>";

echo "</div>
</body>
</html>";

$conn->close();
?>
