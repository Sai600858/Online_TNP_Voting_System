<?php 
// Includes db_connect to start the session automatically (as defined in previous steps)
include 'includes/db_connect.php'; 

// Optional: Redirect logged-in users to their respective dashboards
if (isset($_SESSION['is_admin'])) {
    if ($_SESSION['is_admin'] == 1) {
        header("Location: admin/dashboard.php");
        exit;
    } else {
        header("Location: student/dashboard.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Student Voting System</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-light">
    <div class="container d-flex justify-content-center align-items-center min-vh-100">
        <div class="text-center card p-5 shadow-lg">
            <h1 class="display-4 text-primary mb-5">🗳️ College Election Voting System</h1>
            
            <?php 
            // Display logout messages
            if (isset($_SESSION['success'])) {
                echo '<div class="alert alert-success">' . $_SESSION['success'] . '</div>';
                unset($_SESSION['success']);
            }
            ?>

            <p class="lead mb-4">Select your portal to continue:</p>
            <div class="d-grid gap-3 col-md-8 mx-auto">
                <a href="student/index.php" class="btn btn-primary btn-lg shadow">Student Login (Voter)</a>
                <a href="admin/index.php" class="btn btn-secondary btn-lg shadow">Admin Login (Management)</a>
            </div>
        </div>
    </div>
</body>
</html>