<?php
include '../includes/db_connect.php';

$student_id = $_POST['student_id'] ?? '';
$password = $_POST['password'] ?? '';

if (empty($student_id) || empty($password)) {
    $_SESSION['error'] = "Both Admin ID and Password are required.";
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
        if ($user['is_admin'] == 1) { // 🔑 Admin Check
            $_SESSION['student_id'] = $user['student_id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['is_admin'] = 1;
            
            $_SESSION['success'] = "Welcome, " . htmlspecialchars($user['name']) . "!";
            header("Location: dashboard.php");
            exit;
        } else {
            $_SESSION['error'] = "Access denied. This ID belongs to a student/voter.";
        }
    } else {
        $_SESSION['error'] = "Invalid Admin ID or Password.";
    }
} else {
    $_SESSION['error'] = "Invalid Admin ID or Password.";
}

header("Location: index.php");
$conn->close();
?>