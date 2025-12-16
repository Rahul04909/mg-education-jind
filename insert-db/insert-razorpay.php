<?php
/**
 * Razorpay Settings Database Installation Script
 * This script creates the necessary database tables for Razorpay configuration
 * Run this file once to set up the Razorpay settings tables
 */

// Include database configuration
require_once __DIR__ . '/../database/db-config.php';

// Create database connection using our new function
$conn = getDbConnection();

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Razorpay Database Installation</title>
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

// SQL to create Razorpay settings table
$sql_create_table = "CREATE TABLE IF NOT EXISTS `razorpay_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `razorpay_key_id` varchar(255) NOT NULL,
  `razorpay_key_secret` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

// SQL to create payment logs table
$sql_create_logs = "CREATE TABLE IF NOT EXISTS `payment_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `amount` decimal(10,2) NOT NULL,
  `currency` varchar(10) DEFAULT 'INR',
  `payment_id` varchar(255) DEFAULT NULL,
  `order_id` varchar(255) DEFAULT NULL,
  `signature` varchar(255) DEFAULT NULL,
  `status` enum('success','failed','pending') NOT NULL,
  `error_message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

echo "<h1>
    <svg class='icon info' fill='none' stroke='currentColor' viewBox='0 0 24 24'>
        <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z'></path>
    </svg>
    Razorpay Database Installation
</h1>
<p class='subtitle'>Setting up database tables for Razorpay payment configuration system</p>";

$success_count = 0;
$error_count = 0;
$messages = [];

// Create razorpay_settings table
if ($conn->query($sql_create_table) === TRUE) {
    $success_count++;
    $messages[] = [
        'type' => 'success',
        'text' => 'Table <code>razorpay_settings</code> created successfully or already exists.'
    ];
} else {
    $error_count++;
    $messages[] = [
        'type' => 'error',
        'text' => 'Error creating razorpay_settings table: ' . $conn->error
    ];
}

// Create payment_logs table
if ($conn->query($sql_create_logs) === TRUE) {
    $success_count++;
    $messages[] = [
        'type' => 'success',
        'text' => 'Table <code>payment_logs</code> created successfully or already exists.'
    ];
} else {
    $error_count++;
    $messages[] = [
        'type' => 'error',
        'text' => 'Error creating payment_logs table: ' . $conn->error
    ];
}

// Check if default Razorpay settings exist
$check_sql = "SELECT COUNT(*) as count FROM razorpay_settings";
$result = $conn->query($check_sql);
$row = $result->fetch_assoc();

if ($row['count'] == 0) {
    // Insert default Razorpay settings
    $insert_sql = "INSERT INTO `razorpay_settings`
        (`razorpay_key_id`, `razorpay_key_secret`, `is_active`)
        VALUES
        ('', '', 0)";

    if ($conn->query($insert_sql) === TRUE) {
        $success_count++;
        $messages[] = [
            'type' => 'success',
            'text' => 'Default Razorpay configuration inserted successfully. Please update the settings in admin panel.'
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
        'text' => 'Razorpay settings already exist in the database. No default data inserted.'
    ];
}

// Display messages
foreach ($messages as $message) {
    $icon_path = '';
    switch ($message['type']) {
        case 'success':
            $icon_path = "<path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'></path>";
            break;
        case 'error':
            $icon_path = "<path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z'></path>";
            break;
        case 'warning':
            $icon_path = "<path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z'></path>";
            break;
        case 'info':
            $icon_path = "<path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'></path>";
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
        <li><strong>Tables Created:</strong> razorpay_settings, payment_logs</li>
        <li><strong>Successful Operations:</strong> {$success_count}</li>
        <li><strong>Failed Operations:</strong> {$error_count}</li>
        <li><strong>Status:</strong> " . ($error_count == 0 ? "<span style='color: var(--success); font-weight: 700;'>✓ Installation Complete</span>" : "<span style='color: var(--error); font-weight: 700;'>✗ Installation Failed</span>") . "</li>
    </ul>
</div>";

// Display table structure
echo "<div class='details'>
    <h3>Table Structure: razorpay_settings</h3>
    <div class='sql-block'>
    " . nl2br(htmlspecialchars($sql_create_table)) . "
    </div>
</div>";

echo "<div class='details'>
    <h3>Table Structure: payment_logs</h3>
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
            You can now configure your Razorpay settings in the admin panel.
        </div>
    </div>";
}

echo "<div class='actions'>
    <a href='../admin/settings/razorpay-settings.php' class='btn btn-success'>
        <svg style='width: 20px; height: 20px;' fill='none' stroke='currentColor' viewBox='0 0 24 24'>
            <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z'></path>
            <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M15 12a3 3 0 11-6 0 3 3 0 016 0z'></path>
        </svg>
        Go to Razorpay Settings
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