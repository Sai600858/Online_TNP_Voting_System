<?php
include '../includes/db_connect.php';
include '../includes/session_check.php';
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: index.php");
    exit;
}

$candidate_id = $_POST['candidate_id'] ?? null;
$photo_url = $_POST['photo_url'] ?? null;

if (!$candidate_id) {
    $_SESSION['error'] = "Candidate ID not provided for deletion.";
    header("Location: candidate_mgt.php");
    exit;
}

$conn->begin_transaction();
$success = true;

try {
    // 1. Delete associated votes first
    $delete_votes_sql = "DELETE FROM votes WHERE candidate_id = ?";
    $stmt_votes = $conn->prepare($delete_votes_sql);
    $stmt_votes->bind_param("i", $candidate_id);
    if (!$stmt_votes->execute()) { $success = false; }

    // 2. Delete the candidate record
    $delete_candidate_sql = "DELETE FROM candidates WHERE candidate_id = ?";
    $stmt_candidate = $conn->prepare($delete_candidate_sql);
    $stmt_candidate->bind_param("i", $candidate_id);
    if (!$stmt_candidate->execute()) { $success = false; }

    if ($success) {
        $conn->commit();
        
        // 3. Delete the photo file from the server
        if ($photo_url && file_exists("../" . $photo_url)) {
            unlink("../" . $photo_url);
        }
        
        $_SESSION['success'] = "✅ Candidate (ID: {$candidate_id}) and all associated votes successfully deleted.";
    } else {
        $conn->rollback();
        $_SESSION['error'] = "Database error during deletion. Transaction rolled back.";
    }
} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['error'] = "Deletion failed: " . $e->getMessage();
}

header("Location: candidate_mgt.php");
$conn->close();
?>