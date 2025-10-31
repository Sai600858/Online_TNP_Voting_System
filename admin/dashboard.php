<?php
include '../includes/db_connect.php';
include '../includes/session_check.php';
// Use session check to ensure admin access
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: index.php");
    exit;
}

$admin_name = $_SESSION['name'];
// Fetch the latest election ID to display results for it
$election_sql = "SELECT * FROM elections ORDER BY start_time DESC LIMIT 1";
$election_result = $conn->query($election_sql);
$election = $election_result->fetch_assoc();
$election_id = $election['election_id'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <nav id="sidebar" class="col-md-3 col-lg-2 d-md-block bg-dark sidebar collapse min-vh-100 p-3">
                <div class="position-sticky pt-3">
                    <h4 class="text-white mb-4">Admin Panel</h4>
                    <ul class="nav flex-column">
                        <li class="nav-item mb-2"><a class="nav-link active text-white" href="dashboard.php">📊 Live Results</a></li>
                        <li class="nav-item mb-2"><a class="nav-link text-white-50" href="candidate_mgt.php">🧑‍💻 Candidate Management</a></li>
                        <li class="nav-item mb-2"><a class="nav-link text-white-50" href="election_mgt.php">⏱️ Election Management</a></li>
                        <li class="nav-item"><a class="nav-link text-danger" href="../logout.php">🚪 Logout</a></li>
                    </ul>
                </div>
            </nav>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <h1 class="mb-4">Welcome, <?php echo htmlspecialchars($admin_name); ?>!</h1>
                <hr>

                <?php 
                if (isset($_SESSION['success'])) {
                    echo '<div class="alert alert-success">' . $_SESSION['success'] . '</div>';
                    unset($_SESSION['success']);
                }

                if ($election) : ?>
                    <div class="alert alert-info">
                        **Current Election:** <?php echo htmlspecialchars($election['title']); ?> 
                        <br>Voting Ends: **<?php echo date('F j, Y, g:i a', strtotime($election['end_time'])); ?>**
                    </div>
                <?php else : ?>
                    <div class="alert alert-warning">No elections configured. Please create one in Election Management.</div>
                <?php endif; ?>

                <div class="row">
                    <div class="col-lg-8">
                        <div class="card shadow">
                            <div class="card-header bg-primary text-white">
                                <h3>Live Election Graph (Updates every 5 seconds)</h3>
                            </div>
                            <div class="card-body">
                                <canvas id="liveVotesChart" width="400" height="200"></canvas>
                                <p class="text-muted mt-3">Total Votes Cast: <span id="totalVotes">...</span></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                         <div class="card mb-3 text-center bg-success text-white">
                            <div class="card-body">
                                <h5>Total Candidates</h5>
                                <?php 
                                $cand_count = $conn->query("SELECT COUNT(*) FROM candidates WHERE election_id = $election_id")->fetch_row()[0] ?? 0;
                                echo "<h1>$cand_count</h1>";
                                ?>
                            </div>
                        </div>
                        <div class="card text-center bg-info text-white">
                            <div class="card-body">
                                <h5>Total Eligible Voters</h5>
                                <?php 
                                $student_count = $conn->query("SELECT COUNT(*) FROM students WHERE is_admin = 0")->fetch_row()[0] ?? 0;
                                echo "<h1>$student_count</h1>";
                                ?>
                            </div>
                        </div>
                         <div class="text-center mt-3">
                            <a href="results.php?election_id=<?php echo $election_id; ?>" class="btn btn-warning w-100">View Final Results (After End Date)</a>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <script src="../assets/js/charts.js"></script>
</body>
</html>
<?php $conn->close(); ?>