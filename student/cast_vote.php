<?php
include '../includes/db_connect.php';

// 1. Session and Role Check: Ensures only logged-in students are here
if (!isset($_SESSION['student_id']) || (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1)) {
    $_SESSION['error'] = "Authentication failed. Redirecting to login.";
    header("Location: index.php"); 
    exit;
}

$student_id = $_SESSION['student_id'];
$candidate_id = $_POST['candidate_id'] ?? null;
$active_election_id = $_POST['election_id'] ?? null; 

if (!$candidate_id || !$active_election_id) {
    $_SESSION['error'] = "Invalid voting parameters.";
    header("Location: dashboard.php");
    exit;
}

// 2. Election Active Check (Time check)
$election_check_sql = "SELECT election_id FROM elections WHERE election_id = ? AND NOW() BETWEEN start_time AND end_time AND is_active = 1";
$stmt = $conn->prepare($election_check_sql);
$stmt->bind_param("i", $active_election_id);
$stmt->execute();
if ($stmt->get_result()->num_rows === 0) {
    $_SESSION['error'] = "The election is either not active or has ended. Vote denied.";
    header("Location: dashboard.php");
    exit;
}

// 3. 🛑 CRITICAL CHECK: ONE-TIME VOTE PREVENTION
// This query checks the 'votes' table for any existing entry for this student in this election.
$check_vote_sql = "SELECT vote_id FROM votes WHERE student_id = ? AND election_id = ?";
$stmt_check = $conn->prepare($check_vote_sql);
$stmt_check->bind_param("ii", $student_id, $active_election_id);
$stmt_check->execute();

if ($stmt_check->get_result()->num_rows > 0) {
    // ❌ Error message if vote already exists (second time vote attempt)
    $_SESSION['error'] = "⚠️ **Duplicate Vote Blocked:** You have already cast your vote for this election.";
} else {
    // 4. Record the Vote (Only executes if no existing vote was found)
    $insert_sql = "INSERT INTO votes (student_id, candidate_id, election_id, timestamp) VALUES (?, ?, ?, NOW())";
    $stmt_insert = $conn->prepare($insert_sql);
    $stmt_insert->bind_param("iii", $student_id, $candidate_id, $active_election_id);

    if ($stmt_insert->execute()) {
        $_SESSION['success'] = "✅ Vote cast successfully! Thank you for participating.";
    } else {
        // This is a safety net for database errors (e.g., if UNIQUE KEY constraint fails)
        $_SESSION['error'] = "❌ Error casting vote. Please try again.";
    }
}

// Redirect back to dashboard to show success/error message
header("Location: dashboard.php");
$conn->close();
?>