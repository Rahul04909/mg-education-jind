<?php
// Database configuration file
// This file contains the database connection settings for the mg-skill project

// Database credentials
define('DB_HOST', 'localhost');
define('DB_USER', 'jghfrodu_rahul_dhiman');
define('DB_PASS', 'Rd14072003');
define('DB_NAME', 'jghfrodu_mgedu');

/**
 * Function to create database if it doesn't exist
 */
function createDatabaseIfNotExists()
{
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Create database if it doesn't exist
    $sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME;
    if ($conn->query($sql) === TRUE) {
        echo "Database '" . DB_NAME . "' checked/created successfully\n";
    } else {
        echo "Error creating database: " . $conn->error . "\n";
    }

    $conn->close();
}

/**
 * Function to create required tables if they don't exist
 */
function createTablesIfNotExists()
{
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Create smtp_settings table
    $sql_smtp = "CREATE TABLE IF NOT EXISTS smtp_settings (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        smtp_host VARCHAR(255) NOT NULL,
        smtp_port INT(11) NOT NULL,
        smtp_username VARCHAR(255) NOT NULL,
        smtp_password VARCHAR(255) NOT NULL,
        smtp_encryption VARCHAR(10) NOT NULL,
        from_email VARCHAR(255) NOT NULL,
        from_name VARCHAR(255) NOT NULL,
        is_active TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";

    if ($conn->query($sql_smtp) === TRUE) {
        echo "Table 'smtp_settings' checked/created successfully\n";
    } else {
        echo "Error creating table 'smtp_settings': " . $conn->error . "\n";
    }

    // Create email_logs table
    $sql_logs = "CREATE TABLE IF NOT EXISTS email_logs (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        to_email VARCHAR(255) NOT NULL,
        subject VARCHAR(255) NOT NULL,
        message TEXT NOT NULL,
        status ENUM('success', 'failed') NOT NULL,
        error_message TEXT,
        sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";

    if ($conn->query($sql_logs) === TRUE) {
        echo "Table 'email_logs' checked/created successfully\n";
    } else {
        echo "Error creating table 'email_logs': " . $conn->error . "\n";
    }

    // Create razorpay_settings table
    $sql_razorpay = "CREATE TABLE IF NOT EXISTS razorpay_settings (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        razorpay_key_id VARCHAR(255) NOT NULL,
        razorpay_key_secret VARCHAR(255) NOT NULL,
        is_active TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";

    if ($conn->query($sql_razorpay) === TRUE) {
        echo "Table 'razorpay_settings' checked/created successfully\n";
    } else {
        echo "Error creating table 'razorpay_settings': " . $conn->error . "\n";
    }

    // Create payment_logs table
    $sql_payment_logs = "CREATE TABLE IF NOT EXISTS payment_logs (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        amount DECIMAL(10, 2) NOT NULL,
        currency VARCHAR(10) DEFAULT 'INR',
        payment_id VARCHAR(255),
        order_id VARCHAR(255),
        signature VARCHAR(255),
        status ENUM('success', 'failed', 'pending') NOT NULL,
        error_message TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";

    if ($conn->query($sql_payment_logs) === TRUE) {
        echo "Table 'payment_logs' checked/created successfully\n";
    } else {
        echo "Error creating table 'payment_logs': " . $conn->error . "\n";
    }

    $conn->close();
}

/**
 * Function to establish database connection
 * @return mysqli connection object
 */
function getDbConnection()
{
    try {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        $conn->set_charset("utf8mb4");
        return $conn;
    } catch (Exception $e) {
        die("<div style='margin-top: 120px; padding: 20px; text-align: center; color: red; font-weight: bold; font-family: sans-serif; z-index: 9999; position: relative; background: #fee2e2; border: 1px solid #ef4444; margin-bottom: 20px;'>Database Connection Failed: " . htmlspecialchars($e->getMessage()) . "</div>");
    }
}

// createDatabaseIfNotExists();
// createTablesIfNotExists();
