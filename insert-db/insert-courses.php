<?php
/**
 * Courses & Categories Database Installation Script
 * This script creates the necessary database tables for the Course Management System
 */

// Database configuration
require_once __DIR__ . '/../database/db-config.php';

// Create database connection
$conn = getDbConnection();

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if we have a valid database selected (from db-config)
// If not, we might need to select it or rely on the connection default
// Assuming db-config selects the DB.

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Course Management Database Installation</title>
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
        * { margin: 0; padding: 0; box-sizing: border-box; }
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
        .subtitle { color: #6b7280; margin-bottom: 30px; font-size: 15px; }
        .message {
            padding: 16px 20px;
            border-radius: 12px;
            margin-bottom: 16px;
            display: flex;
            align-items: flex-start;
            gap: 12px;
            border: 1px solid;
        }
        .message.success { background: #d1fae5; border-color: #86efac; color: #065f46; }
        .message.error { background: #fee2e2; border-color: #fca5a5; color: #991b1b; }
        .icon { width: 24px; height: 24px; flex-shrink: 0; }
        .btn {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 12px 24px; border-radius: 12px; text-decoration: none;
            font-weight: 700; border: none; cursor: pointer; font-size: 15px;
            transition: all 0.2s ease;
        }
        .btn-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff; }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3); }
        .actions { display: flex; gap: 12px; margin-top: 30px; flex-wrap: wrap; }
        .sql-block {
            background: #1e293b; color: #e2e8f0; padding: 20px;
            border-radius: 12px; overflow-x: auto; margin: 20px 0;
            font-family: 'Courier New', monospace; font-size: 13px; line-height: 1.6;
        }
        .details h3 { font-size: 18px; margin-bottom: 12px; color: var(--text); }
    </style>
</head>
<body>
    <div class='container'>
        <h1>Course Management Installation</h1>
        <p class='subtitle'>Setting up database tables for Courses and Categories</p>";

$success_count = 0;
$error_count = 0;
$messages = [];

// 1. Create course_categories table
$sql_create_categories = "CREATE TABLE IF NOT EXISTS `course_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `banner_image` varchar(255) DEFAULT NULL,
  `meta_title` text DEFAULT NULL,
  `meta_desc` text DEFAULT NULL,
  `meta_keywords` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if ($conn->query($sql_create_categories) === TRUE) {
    $success_count++;
    $messages[] = ['type' => 'success', 'text' => 'Table <code>course_categories</code> created successfully.'];
} else {
    $error_count++;
    $messages[] = ['type' => 'error', 'text' => 'Error creating course_categories: ' . $conn->error];
}

// 2. Create courses table
// Including columns for Labels, Features, Fees as JSON to store structured/flexible data
$sql_create_courses = "CREATE TABLE IF NOT EXISTS `courses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `video_url` varchar(255) DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `labels` json DEFAULT NULL COMMENT 'Stores badges like Offline Classes, Class 12',
  `features` json DEFAULT NULL COMMENT 'Stores features list',
  `fees` json DEFAULT NULL COMMENT 'Stores fee structure',
  `featured_image` varchar(255) DEFAULT NULL,
  `meta_title` text DEFAULT NULL,
  `meta_desc` text DEFAULT NULL,
  `meta_keywords` text DEFAULT NULL,
  `base_currency` varchar(10) NOT NULL DEFAULT 'INR',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `fk_course_category` FOREIGN KEY (`category_id`) REFERENCES `course_categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if ($conn->query($sql_create_courses) === TRUE) {
    $success_count++;
    $messages[] = ['type' => 'success', 'text' => 'Table <code>courses</code> created successfully.'];
} else {
    $error_count++;
    $messages[] = ['type' => 'error', 'text' => 'Error creating courses: ' . $conn->error];
}

// Display Messages
foreach ($messages as $msg) {
    echo "<div class='message {$msg['type']}'>
        <div>{$msg['text']}</div>
    </div>";
}

// Display SQL Structure
echo "<div class='details'>
    <h3>Table Structure: course_categories</h3>
    <div class='sql-block'>" . nl2br(htmlspecialchars($sql_create_categories)) . "</div>
</div>";

echo "<div class='details'>
    <h3>Table Structure: courses</h3>
    <div class='sql-block'>" . nl2br(htmlspecialchars($sql_create_courses)) . "</div>
</div>";

echo "<div class='actions'>
    <a href='../admin/index.php' class='btn btn-primary'>Go to Admin Dashboard</a>
</div>";

echo "</div></body></html>";
$conn->close();
?>
