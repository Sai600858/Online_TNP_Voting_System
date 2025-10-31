<?php 
include '../includes/db_connect.php'; 
// Optional: Check if at least one admin exists to block casual registration
// if ($conn->query("SELECT COUNT(*) FROM students WHERE is_admin = 1")->fetch_row()[0] > 0) {
//     $_SESSION['error'] = "Admin registration is closed.";
//     header("Location: index.php");
//     exit;
// }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Registration</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="bg-light">
    <div class="container d-flex justify-content-center align-items-center min-vh-100">
        <div class="card p-4 shadow-lg" style="width: 450px;">
            <h2 class="text-center mb-4 text-danger">Admin Registration</h2>
            
            <?php 
            if (isset($_SESSION['error'])) {
                echo '<div class="alert alert-danger">' . $_SESSION['error'] . '</div>';
                unset($_SESSION['error']);
            }
            if (isset($_SESSION['success'])) {
                echo '<div class="alert alert-success">' . $_SESSION['success'] . '</div>';
                unset($_SESSION['success']);
            }
            ?>

            <form action="register_process.php" method="POST">
                <div class="mb-3">
                    <label for="student_id" class="form-label">Admin ID (Unique)</label>
                    <input type="number" class="form-control" id="student_id" name="student_id" required>
                </div>
                <div class="mb-3">
                    <label for="name" class="form-label">Full Name</label>
                    <input type="text" class="form-control" id="name" name="name" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-danger w-100 mt-3">Register Admin</button>
            </form>
            <p class="text-center mt-3">
                Already registered? <a href="index.php">Login Here</a>
            </p>
        </div>
    </div>
</body>
</html>