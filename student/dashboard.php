<?php
include '../includes/db_connect.php';
// Ensure only non-admin students are here
if (!isset($_SESSION['student_id']) || (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1)) {
    header("Location: ../admin/dashboard.php");
    exit;
}

$student_id = $_SESSION['student_id'];
$student_name = $_SESSION['name'];

// 1. Get the current active election
$election_sql = "SELECT * FROM elections WHERE NOW() BETWEEN start_time AND end_time AND is_active = 1 LIMIT 1";
$election_result = $conn->query($election_sql);
$election = $election_result->fetch_assoc();

$active_election_id = $election['election_id'] ?? 0;
$has_voted = false;

if ($active_election_id) {
    // 2. Check if student has already voted
    $check_vote_sql = "SELECT vote_id FROM votes WHERE student_id = ? AND election_id = ?";
    $stmt = $conn->prepare($check_vote_sql);
    $stmt->bind_param("ii", $student_id, $active_election_id);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $has_voted = true;
    }

    // 3. Fetch candidates
    $candidates_sql = "SELECT candidate_id, name, branch, photo_url FROM candidates WHERE election_id = ?";
    $stmt = $conn->prepare($candidates_sql);
    $stmt->bind_param("i", $active_election_id);
    $stmt->execute();
    $candidates_result = $stmt->get_result();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="text-primary">Welcome, <?php echo htmlspecialchars($student_name); ?>!</h1>
            <a href="../logout.php" class="btn btn-danger">Logout</a>
        </div>
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

        <?php if ($election) : ?>
            <div class="alert alert-info text-center">
                <h2>🗳️ Current Election: <?php echo htmlspecialchars($election['title']); ?></h2>
                <p>Voting ends on: **<?php echo date('F j, Y, g:i a', strtotime($election['end_time'])); ?>**</p>
            </div>
            
            <?php if ($has_voted) : ?>
                <div class="alert alert-success text-center display-6">
                    ✅ Thank you! You have successfully cast your vote.
                </div>
                <div class="text-center mt-4">
                    <a href="results.php?election_id=<?php echo $active_election_id; ?>" class="btn btn-warning btn-lg">View Results (If Finalized)</a>
                </div>
            <?php else : ?>
                <h3 class="mt-5 mb-4 text-secondary">Cast Your Vote: Select One Candidate</h3>
                <div class="row row-cols-1 row-cols-md-3 g-4">
                    <?php while ($candidate = $candidates_result->fetch_assoc()): ?>
                    <div class="col">
                        <div class="card h-100 shadow border-success">
                            <img src="../<?php echo htmlspecialchars($candidate['photo_url'] ?? 'assets/images/placeholder.png'); ?>" class="card-img-top" alt="Candidate Photo">
                            <div class="card-body text-center">
                                <h5 class="card-title text-primary"><?php echo htmlspecialchars($candidate['name']); ?></h5>
                                <p class="card-text text-muted">Branch: **<?php echo htmlspecialchars($candidate['branch']); ?>**</p>
                                
                                <form action="cast_vote.php" method="POST" onsubmit="return confirm('Confirm your vote for <?php echo htmlspecialchars($candidate['name']); ?>? This cannot be undone.');">
                                    <input type="hidden" name="candidate_id" value="<?php echo $candidate['candidate_id']; ?>">
                                    <input type="hidden" name="election_id" value="<?php echo $active_election_id; ?>">
                                    <button type="submit" class="btn btn-success btn-lg w-100">VOTE FOR ME</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            <?php endif; ?>

        <?php else : ?>
            <div class="alert alert-warning text-center">
                <h3>No active election running right now.</h3>
                <p>Please check back later or contact the college administration.</p>
                <a href="results.php" class="btn btn-info">View Past Results</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
<?php $conn->close(); ?>