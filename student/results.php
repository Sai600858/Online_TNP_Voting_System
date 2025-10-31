<?php
include '../includes/db_connect.php';
// Ensure only non-admin students are here
if (!isset($_SESSION['student_id']) || (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1)) {
    header("Location: ../admin/dashboard.php");
    exit;
}

$election_id = $_GET['election_id'] ?? 1;

// Check if election has ended
$election_sql = "SELECT title, end_time FROM elections WHERE election_id = ?";
$stmt = $conn->prepare($election_sql);
$stmt->bind_param("i", $election_id);
$stmt->execute();
$election = $stmt->get_result()->fetch_assoc();

if (!$election || strtotime($election['end_time']) > time()) {
    $_SESSION['error'] = "Results are not yet final for this election.";
    header("Location: dashboard.php");
    exit;
}

// Fetch final results
$results_sql = "
    SELECT 
        c.name AS candidate_name, 
        c.branch,
        COUNT(v.vote_id) AS vote_count
    FROM 
        candidates c
    LEFT JOIN 
        votes v ON c.candidate_id = v.candidate_id AND v.election_id = ?
    WHERE
        c.election_id = ?
    GROUP BY 
        c.candidate_id, c.name, c.branch
    ORDER BY 
        vote_count DESC";

$stmt = $conn->prepare($results_sql);
$stmt->bind_param("ii", $election_id, $election_id);
$stmt->execute();
$results = $stmt->get_result();
$total_votes = 0;
$final_data = [];
while($row = $results->fetch_assoc()) {
    $total_votes += $row['vote_count'];
    $final_data[] = $row;
}
$winner = $final_data[0] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Election Results</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container py-5">
        <h1 class="text-center text-success mb-4">Final Election Results</h1>
        <h2 class="text-center text-secondary mb-5"><?php echo htmlspecialchars($election['title']); ?></h2>
        
        <?php if ($winner && $total_votes > 0): ?>
            <div class="card mb-5 shadow-lg border-success">
                <div class="card-body text-center bg-success text-white">
                    <h3 class="display-4">🥇 The Winner Is:</h3>
                    <h1 class="display-3"><?php echo htmlspecialchars($winner['candidate_name']); ?></h1>
                    <p class="lead">Branch: <?php echo htmlspecialchars($winner['branch']); ?> | Total Votes: **<?php echo $winner['vote_count']; ?>**</p>
                </div>
            </div>
        <?php endif; ?>

        <h4 class="mb-4">Complete Results Summary (Total Votes: <?php echo $total_votes; ?>)</h4>
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>Rank</th>
                        <th>Candidate Name</th>
                        <th>Branch</th>
                        <th>Votes</th>
                        <th>Percentage</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $rank = 1;
                    foreach($final_data as $row): 
                    $percentage = $total_votes > 0 ? number_format(($row['vote_count'] / $total_votes) * 100, 2) : 0;
                    ?>
                    <tr class="<?php echo ($rank === 1) ? 'table-success fw-bold' : ''; ?>">
                        <td><?php echo $rank++; ?></td>
                        <td><?php echo htmlspecialchars($row['candidate_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['branch']); ?></td>
                        <td><?php echo $row['vote_count']; ?></td>
                        <td><?php echo $percentage; ?>%</td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="text-center mt-4">
            <a href="dashboard.php" class="btn btn-primary">Back to Dashboard</a>
        </div>
    </div>
</body>
</html>
<?php $conn->close(); ?>