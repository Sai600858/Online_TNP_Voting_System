<?php
include '../includes/db_connect.php';
header('Content-Type: application/json');

// Get the active election ID, defaulting to 1
$active_election_id = $_GET['election_id'] ?? 1;

// SQL to count votes per candidate
$results_sql = "
    SELECT 
        c.name AS label, 
        c.branch,
        COUNT(v.vote_id) AS data
    FROM 
        candidates c
    LEFT JOIN 
        votes v ON c.candidate_id = v.candidate_id AND v.election_id = ?
    WHERE
        c.election_id = ?
    GROUP BY 
        c.candidate_id, c.name, c.branch
    ORDER BY 
        data DESC";

$stmt = $conn->prepare($results_sql);
$stmt->bind_param("ii", $active_election_id, $active_election_id);
$stmt->execute();
$results = $stmt->get_result();

$vote_data = [];
$total_votes = 0;
while ($row = $results->fetch_assoc()) {
    $total_votes += $row['data'];
    $vote_data[] = $row;
}

// Return total votes along with candidate data
echo json_encode(['candidates' => $vote_data, 'total_votes' => $total_votes]); 
$conn->close();
?>