<?php
session_start();
// Unset all session variables specific to admin
unset($_SESSION['admin_logged_in']);
unset($_SESSION['admin_id']);
unset($_SESSION['admin_name']);
unset($_SESSION['admin_role']);

// Optional: Destroy the entire session if it's only used for admin, 
// but since there might be other logins on the same domain (e.g. testing), just precise unsetting or full destroy works.
session_destroy(); 

header("Location: login.php");
exit;
?>
