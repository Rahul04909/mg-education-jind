<?php
require_once __DIR__ . '/../../database/db-config.php';
$conn = getDbConnection();

// Check if ID is provided
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = intval($_GET['id']);

    // Prepare Delete Statement
    $stmt = $conn->prepare("DELETE FROM blogs WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        // Redirect with Success
        echo "<script>
            alert('Blog post deleted successfully.');
            window.location.href = 'index.php';
        </script>";
    } else {
        // Redirect with Error
        echo "<script>
            alert('Error deleting blog post: " . $conn->error . "');
            window.location.href = 'index.php';
        </script>";
    }
    
    $stmt->close();
} else {
    // Redirect if Invalid ID
    header("Location: index.php");
    exit();
}

$conn->close();
?>
