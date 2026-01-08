<?php
require_once __DIR__ . '/db-config.php';
$conn = getDbConnection();

// Create Table
$sql = "CREATE TABLE IF NOT EXISTS admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('super_admin', 'admin', 'editor') DEFAULT 'super_admin',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    echo "Table 'admin_users' created successfully.<br>";
} else {
    echo "Error creating table: " . $conn->error . "<br>";
}

// Check if default admin exists
$check_sql = "SELECT * FROM admin_users WHERE username = 'admin'";
$result = $conn->query($check_sql);

if ($result->num_rows == 0) {
    // Insert Default Admin
    // Username: admin
    // Password: admin123
    $username = 'admin';
    $email = 'admin@mgedu.in'; // Default email
    $password = password_hash('admin123', PASSWORD_BCRYPT); // Hash the password
    $full_name = 'Super Admin';
    $role = 'super_admin';

    $stmt = $conn->prepare("INSERT INTO admin_users (username, email, password, full_name, role) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $username, $email, $password, $full_name, $role);

    if ($stmt->execute()) {
        echo "Default admin user created successfully.<br>";
        echo "Username: admin<br>";
        echo "Password: admin123<br>";
    } else {
        echo "Error creating default admin: " . $stmt->error . "<br>";
    }
    $stmt->close();
} else {
    echo "Default admin user already exists.<br>";
}

$conn->close();
?>
