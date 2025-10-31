<?php
include '../includes/db_connect.php';
include '../includes/session_check.php';
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: index.php");
    exit;
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $start_time = $_POST['start_time'] ?? '';
    $end_time = $_POST['end_time'] ?? '';
    $election_id = $_POST['election_id'] ?? null;

    if (empty($title) || empty($start_time) || empty($end_time)) {
        $message = '<div class="alert alert-danger">All fields are required.</div>';
    } else if (strtotime($start_time) >= strtotime($end_time)) {
        $message = '<div class="alert alert-danger">The Start Time must be before the End Time.</div>';
    } else {
        if ($election_id) {
            // Update existing election
            $sql = "UPDATE elections SET title = ?, start_time = ?, end_time = ? WHERE election_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssi", $title, $start_time, $end_time, $election_id);
            if ($stmt->execute()) {
                $message = '<div class="alert alert-success">Election updated successfully!</div>';
            } else {
                $message = '<div class="alert alert-danger">Error updating election.</div>';
            }
        } else {
            // Create new election
            $sql = "INSERT INTO elections (title, start_time, end_time, is_active) VALUES (?, ?, ?, 1)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sss", $title, $start_time, $end_time);
            if ($stmt->execute()) {
                $message = '<div class="alert alert-success">New election created successfully!</div>';
            } else {
                $message = '<div class="alert alert-danger">Error creating election.</div>';
            }
        }
    }
}

$elections_result = $conn->query("SELECT * FROM elections ORDER BY start_time DESC");
$current_election = $conn->query("SELECT * FROM elections ORDER BY start_time DESC LIMIT 1")->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Election Management</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/style.css"> 
</head>
<body>
    <div class="container-fluid">
        <div class="row">
             <nav id="sidebar" class="col-md-3 col-lg-2 d-md-block bg-dark sidebar collapse min-vh-100 p-3">
                <div class="position-sticky pt-3">
                    <h4 class="text-white mb-4">Admin Panel</h4>
                    <ul class="nav flex-column">
                        <li class="nav-item mb-2"><a class="nav-link text-white-50" href="dashboard.php">📊 Live Results</a></li>
                        <li class="nav-item mb-2"><a class="nav-link text-white-50" href="candidate_mgt.php">🧑‍💻 Candidate Management</a></li>
                        <li class="nav-item mb-2"><a class="nav-link active text-white" href="election_mgt.php">⏱️ Election Management</a></li>
                        <li class="nav-item"><a class="nav-link text-danger" href="../logout.php">🚪 Logout</a></li>
                    </ul>
                </div>
            </nav>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <h1 class="mb-4">Election Management</h1>
                <hr>
                <?php echo $message; ?>

                <div class="card mb-5 shadow">
                    <div class="card-header bg-success text-white">
                        <h4>Create / Update Election Schedule</h4>
                    </div>
                    <div class="card-body">
                        <form action="election_mgt.php" method="POST">
                            <input type="hidden" name="election_id" value="<?php echo htmlspecialchars($current_election['election_id'] ?? ''); ?>">

                            <div class="mb-3">
                                <label for="title" class="form-label">Election Title</label>
                                <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($current_election['title'] ?? ''); ?>" required>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="start_time" class="form-label">Start Time</label>
                                    <input type="datetime-local" class="form-control" id="start_time" name="start_time" value="<?php echo date('Y-m-d\TH:i', strtotime($current_election['start_time'] ?? '')); ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="end_time" class="form-label">End Time</label>
                                    <input type="datetime-local" class="form-control" id="end_time" name="end_time" value="<?php echo date('Y-m-d\TH:i', strtotime($current_election['end_time'] ?? '')); ?>" required>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary mt-3">
                                <?php echo $current_election ? 'Update Current Election' : 'Create New Election'; ?>
                            </button>
                        </form>
                    </div>
                </div>

                <div class="card shadow">
                    <div class="card-header bg-secondary text-white">
                        <h4>Election History</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Title</th>
                                        <th>Start Time</th>
                                        <th>End Time</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $elections_result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $row['election_id']; ?></td>
                                        <td><?php echo htmlspecialchars($row['title']); ?></td>
                                        <td><?php echo date('Y-m-d H:i', strtotime($row['start_time'])); ?></td>
                                        <td><?php echo date('Y-m-d H:i', strtotime($row['end_time'])); ?></td>
                                        <td>
                                            <?php 
                                            $now = time();
                                            $start = strtotime($row['start_time']);
                                            $end = strtotime($row['end_time']);

                                            if ($now < $start) {
                                                echo '<span class="badge bg-warning">Scheduled</span>';
                                            } elseif ($now >= $start && $now <= $end) {
                                                echo '<span class="badge bg-success">Active</span>';
                                            } else {
                                                echo '<span class="badge bg-danger">Completed</span>';
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
</body>
</html>
<?php $conn->close(); ?>