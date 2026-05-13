<?php
require_once __DIR__ . '/database/db-config.php';
$conn = getDbConnection();

// Ensure connection is UTF-8
$conn->set_charset("utf8mb4");

$sql = "SELECT id, title, description, meta_title, meta_desc FROM blogs";
$result = $conn->query($sql);

if ($result) {
    while($row = $result->fetch_assoc()) {
        $id = $row['id'];
        $title = $row['title'];
        echo "Checking blog ID $id: " . htmlspecialchars(substr($title, 0, 50)) . "...<br>";
        
        $fix = function($str) {
            if (empty($str)) return $str;
            
            // utf8_decode converts UTF-8 bytes to ISO-8859-1 characters.
            // If the string was double-encoded, this restores the original UTF-8 bytes.
            $fixed = utf8_decode($str);
            
            if ($fixed !== $str) {
                // Check if the fixed string contains Devanagari characters (Hindi)
                // Range \x{0900}-\x{097F} covers the Devanagari script
                if (preg_match('/[\x{0900}-\x{097F}]/u', $fixed)) {
                    return $fixed;
                }
            }
            return $str;
        };

        $new_title = $fix($row['title']);
        $new_desc = $fix($row['description']);
        $new_mtitle = $fix($row['meta_title']);
        $new_mdesc = $fix($row['meta_desc']);
        
        if ($new_title !== $row['title'] || $new_desc !== $row['description']) {
            $stmt = $conn->prepare("UPDATE blogs SET title = ?, description = ?, meta_title = ?, meta_desc = ? WHERE id = ?");
            $stmt->bind_param("ssssi", $new_title, $new_desc, $new_mtitle, $new_mdesc, $id);
            if ($stmt->execute()) {
                echo "<strong>Successfully FIXED blog ID $id (Hindi detected)</strong><br>";
            } else {
                echo "Error updating blog ID $id: " . $conn->error . "<br>";
            }
            $stmt->close();
        } else {
            echo "No changes needed (already correct or no Hindi Mojibake detected).<br>";
        }
    }
} else {
    echo "Query failed: " . $conn->error . "<br>";
}
echo "Done.\n";
?>
