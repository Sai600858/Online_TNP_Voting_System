<?php
include '../includes/db_connect.php';
include '../includes/session_check.php';
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: index.php");
    exit;
}

$candidate_name = $_POST['name'] ?? '';
$candidate_branch = $_POST['branch'] ?? '';
$election_id = $_POST['election_id'] ?? 1; 

if (empty($candidate_name) || empty($candidate_branch)) {
    $_SESSION['error'] = "Candidate Name and Branch are required.";
    header("Location: candidate_mgt.php");
    exit;
}

$photo_url = null;
$uploadOk = 1;

// --- Handle File Upload ---
if (isset($_FILES["photo"]) && $_FILES["photo"]["error"] == 0) {
    $target_dir = "../candidate_photos/"; 
    $file_extension = strtolower(pathinfo(basename($_FILES["photo"]["name"]), PATHINFO_EXTENSION));
    $new_file_name = uniqid('cand_', true) . '.' . $file_extension; 
    $target_file = $target_dir . $new_file_name;

    $check = getimagesize($_FILES["photo"]["tmp_name"]);
    if ($check === false) { $uploadOk = 0; }
    if ($_FILES["photo"]["size"] > 1000000) { $_SESSION['error'] = "Sorry, file size exceeds 1MB."; $uploadOk = 0; }
    if ($file_extension != "jpg" && $file_extension != "png" && $file_extension != "jpeg") { $_SESSION['error'] = "Sorry, only JPG, JPEG, & PNG files allowed."; $uploadOk = 0; }

    if ($uploadOk == 1) {
        if (move_uploaded_file($_FILES["photo"]["tmp_name"], $target_file)) {
            $photo_url = "candidate_photos/" . $new_file_name;
        } else {
            $_SESSION['error'] = "Error uploading photo. Check folder permissions (777).";
            $uploadOk = 0;
        }
    }
} else {
     $_SESSION['error'] = "Photo upload failed or no file selected.";
     $uploadOk = 0;
}

// --- Insert Data ---
if ($uploadOk == 1) {
    $sql = "INSERT INTO candidates (election_id, name, branch, photo_url) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isss", $election_id, $candidate_name, $candidate_branch, $photo_url);

    if ($stmt->execute()) {
        $_SESSION['success'] = "🎉 Candidate **{$candidate_name}** successfully added.";
    } else {
        $_SESSION['error'] = "Database insertion error: " . $conn->error;
    }
}

header("Location: candidate_mgt.php");
$conn->close();
?>