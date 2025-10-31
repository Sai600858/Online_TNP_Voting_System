<?php
// Generates a new secure hash using PHP's recommended algorithm (bcrypt)

$admin_password = 'password123';
$student_password = 'student123';

$admin_hash = password_hash($admin_password, PASSWORD_DEFAULT);
$student_hash = password_hash($student_password, PASSWORD_DEFAULT);

echo "Admin Password (password123) Hash: <br> <strong>" . $admin_hash . "</strong><br><br>";
echo "Student Password (student123) Hash: <br> <strong>" . $student_hash . "</strong><br>";
?>