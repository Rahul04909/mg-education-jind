<?php
/**
 * Benchmark wrapper for submit-exam.php
 * This file allows testing the DB insertion logic without session requirements.
 * WARNING: Do not leave this file on production.
 */
session_start();
require_once __DIR__ . '/../database/db-config.php';

// Mock session if provided via GET for testing
if (isset($_GET['test_student_id'])) {
    $_SESSION['student_id'] = $_GET['test_student_id'];
}

include 'submit-exam.php';
?>
