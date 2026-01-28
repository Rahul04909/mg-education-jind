<?php
require_once __DIR__ . '/../../database/db-config.php';
$conn = getDbConnection();

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    // First fetch file paths to delete them
    $stmt = $conn->prepare("SELECT student_photo, student_sign, aadhar_file, edu_cert_file FROM internship_enrollments WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();
    
    if ($res->num_rows > 0) {
        $files = $res->fetch_assoc();
        
        // Delete record
        $del_stmt = $conn->prepare("DELETE FROM internship_enrollments WHERE id = ?");
        $del_stmt->bind_param("i", $id);
        
        if ($del_stmt->execute()) {
            // Remove files
            foreach ($files as $path) {
                if (!empty($path) && file_exists(__DIR__ . '/../../' . $path)) {
                    unlink(__DIR__ . '/../../' . $path);
                }
            }
            echo "<script>alert('Student deleted successfully'); window.location.href='student-list.php';</script>";
        } else {
            echo "<script>alert('Error deleting student'); window.location.href='student-list.php';</script>";
        }
    } else {
        echo "<script>alert('Student not found'); window.location.href='student-list.php';</script>";
    }
} else {
    header("Location: student-list.php");
}
$conn->close();
?>
