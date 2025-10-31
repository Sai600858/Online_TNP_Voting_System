<?php
include '../includes/db_connect.php';

$student_id = $_POST['student_id'] ?? '';
$password = $_POST['password'] ?? '';

if (empty($student_id) || empty($password)) {
    $_SESSION['error'] = "Student ID and Password are required.";
    header("Location: index.php");
    exit;
}

$sql = "SELECT student_id, password, name, is_admin FROM students WHERE student_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($user) {
    if (password_verify($password, $user['password'])) {
        if ($user['is_admin'] == 0) { // Check that the user is NOT an Admin
            $_SESSION['student_id'] = $user['student_id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['is_admin'] = 0;
            
            $_SESSION['success'] = "Welcome, " . htmlspecialchars($user['name']) . "! Cast your vote now.";
            header("Location: dashboard.php");
            exit;
        } else {
            $_SESSION['error'] = "You are registered as an Admin. Please use the Admin Login portal.";
        }
    } else {
        $_SESSION['error'] = "Invalid Student ID or Password.";
    }
} else {
    $_SESSION['error'] = "Invalid Student ID or Password.";
}

header("Location: index.php");
$conn->close();
?>