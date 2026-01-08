<?php
require_once __DIR__ . '/../database/db-config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}

$conn = getDbConnection();

$blog_id = isset($_POST['blog_id']) ? intval($_POST['blog_id']) : 0;
$name = isset($_POST['name']) ? trim($_POST['name']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$rating = isset($_POST['rating']) ? intval($_POST['rating']) : 0;
$review = isset($_POST['review']) ? trim($_POST['review']) : '';

// Validation
if ($blog_id <= 0 || empty($name) || empty($email) || $rating <= 0 || $rating > 5 || empty($review)) {
    echo json_encode(['status' => 'error', 'message' => 'Please fill all required fields correctly.']);
    exit;
}

// Sanitize inputs
$name = mysqli_real_escape_string($conn, $name);
$email = mysqli_real_escape_string($conn, $email);
$review = mysqli_real_escape_string($conn, $review);

// Default status 'approved' as per plan
$status = 'approved';

$sql = "INSERT INTO blog_reviews (blog_id, name, email, rating, review, status) 
        VALUES ($blog_id, '$name', '$email', $rating, '$review', '$status')";

if ($conn->query($sql) === TRUE) {
    echo json_encode(['status' => 'success', 'message' => 'Thank you! Your review has been submitted successfully.']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $conn->error]);
}

$conn->close();
?>
