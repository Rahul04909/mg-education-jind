<?php
require_once __DIR__ . '/db-config.php';
$conn = getDbConnection();

try {
    // Create blog_categories table
    $sql_categories = "CREATE TABLE IF NOT EXISTS `blog_categories` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `name` VARCHAR(255) NOT NULL,
        `slug` VARCHAR(255) UNIQUE NOT NULL,
        `meta_title` VARCHAR(255),
        `meta_desc` TEXT,
        `meta_keywords` TEXT,
        `schema_markup` TEXT,
        `is_active` TINYINT(1) DEFAULT 1,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

    if ($conn->query($sql_categories) === TRUE) {
        echo "Table 'blog_categories' created successfully.<br>";
    }

    // Create blogs table
    $sql_blogs = "CREATE TABLE IF NOT EXISTS `blogs` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `category_id` INT NOT NULL,
        `title` VARCHAR(255) NOT NULL,
        `slug` VARCHAR(255) UNIQUE NOT NULL,
        `featured_image` VARCHAR(255),
        `description` LONGTEXT,
        
        `meta_title` VARCHAR(255),
        `meta_desc` TEXT,
        `meta_keywords` TEXT,
        `schema_markup` TEXT,
        
        `is_active` TINYINT(1) DEFAULT 1,
        `views` INT DEFAULT 0,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        
        FOREIGN KEY (`category_id`) REFERENCES `blog_categories`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

    if ($conn->query($sql_blogs) === TRUE) {
        echo "Table 'blogs' created successfully.<br>";
    }

} catch (mysqli_sql_exception $e) {
    echo "Error: " . $e->getMessage();
}

$conn->close();
?>
