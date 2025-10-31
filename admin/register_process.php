<?php
include '../includes/db_connect.php';

$student_id = $_POST['student_id'] ?? '';
$name = $_POST['name'] ?? '';
$password = $_POST['password'] ?? '';
$branch = "Admin"; // Set a default branch for admin

if (empty($student_id) || empty($name) || empty($password)) {
    $_SESSION['error'] = "All fields are required.";
    header("Location: register.php");
    exit;
}

// Check if student ID already exists
$check_sql = "SELECT student_id FROM students WHERE student_id = ?";
$stmt_check = $conn->prepare($check_sql);
$stmt_check->bind_param("i", $student_id);
$stmt_check->execute();
$stmt_check->store_result();

if ($stmt_check->num_rows > 0) {
    $_SESSION['error'] = "ID {$student_id} is already registered.";
    header("Location: register.php");
    exit;
}

// Hash the Password securely
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Insert new Admin (is_admin = 1)
$insert_sql = "INSERT INTO students (student_id, name, branch, password, is_admin) VALUES (?, ?, ?, ?, 1)";
$stmt_insert = $conn->prepare($insert_sql);
$stmt_insert->bind_param("isss", $student_id, $name, $branch, $hashed_password);

if ($stmt_insert->execute()) {
    $_SESSION['success'] = "Admin registration successful! You can now log in.";
    header("Location: index.php");
    exit;
} else {
    $_SESSION['error'] = "Registration failed. Database error: " . $conn->error;
    header("Location: register.php");
    exit;
}

$conn->close();