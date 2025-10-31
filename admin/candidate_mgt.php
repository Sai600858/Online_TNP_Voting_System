<?php
include '../includes/db_connect.php';
include '../includes/session_check.php';
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: index.php");
    exit;
}

$election_id = 1; // Assuming the latest election ID

// Fetch current candidates
$candidates_sql = "SELECT * FROM candidates WHERE election_id = ?";
$stmt = $conn->prepare($candidates_sql);
$stmt->bind_param("i", $election_id);
$stmt->execute();
$candidates_result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Candidate Management</title>
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
                        <li class="nav-item mb-2"><a class="nav-link active text-white" href="candidate_mgt.php">🧑‍💻 Candidate Management</a></li>
                        <li class="nav-item mb-2"><a class="nav-link text-white-50" href="election_mgt.php">⏱️ Election Management</a></li>
                        <li class="nav-item"><a class="nav-link text-danger" href="../logout.php">🚪 Logout</a></li>
                    </ul>
                </div>
            </nav>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <h1 class="mb-4">Candidate Management</h1>
                <hr>

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

                <div class="card mb-5 shadow">
                    <div class="card-header bg-primary text-white">
                        <h4>➕ Add New Candidate</h4>
                    </div>
                    <div class="card-body">
                        <form action="add_candidate.php" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="election_id" value="<?php echo $election_id; ?>">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="name" class="form-label">Name</label>
                                    <input type="text" class="form-control" id="name" name="name" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="branch" class="form-label">Branch/Dept</label>
                                    <input type="text" class="form-control" id="branch" name="branch" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="photo" class="form-label">Photo (JPG/PNG)</label>
                                    <input type="file" class="form-control" id="photo" name="photo" accept="image/jpeg, image/png" required>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-success mt-3">Add Candidate</button>
                        </form>
                    </div>
                </div>

                <div class="card shadow">
                    <div class="card-header bg-secondary text-white">
                        <h4>Current Candidates for Election ID: <?php echo $election_id; ?></h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Photo</th>
                                        <th>Name</th>
                                        <th>Branch</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($candidate = $candidates_result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $candidate['candidate_id']; ?></td>
                                        <td>
                                            <?php if ($candidate['photo_url']): ?>
                                                <img src="../<?php echo htmlspecialchars($candidate['photo_url']); ?>" alt="Photo" style="width: 50px; height: 50px; object-fit: cover;">
                                            <?php else: ?>
                                                
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($candidate['name']); ?></td>
                                        <td><?php echo htmlspecialchars($candidate['branch']); ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-info" disabled>Edit</button> 
                                            <form action="delete_candidate.php" method="POST" class="d-inline" onsubmit="return confirm('WARNING: Deleting this candidate will delete all their votes. Are you sure?');">
                                                <input type="hidden" name="candidate_id" value="<?php echo $candidate['candidate_id']; ?>">
                                                <input type="hidden" name="photo_url" value="<?php echo htmlspecialchars($candidate['photo_url']); ?>">
                                                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                            </form>
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