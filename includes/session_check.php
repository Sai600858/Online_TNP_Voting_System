<?php
// Note: This file should be included AFTER db_connect.php

/**
 * Checks if the user is logged in and has the required administrative role.
 * * @param bool $is_admin_required True if the page requires admin privileges.
 */
function check_session($is_admin_required = false) {
    // Case 1: User is not logged in at all
    if (!isset($_SESSION['student_id'])) {
        // Redirect non-logged-in users to the main index/login page
        header("Location: ../index.php"); 
        exit;
    }
    
    // Case 2: User is logged in, check role for access control
    $is_admin = $_SESSION['is_admin'] ?? 0;
    
    if ($is_admin_required) {
        // If Admin access is required but the user is NOT an admin
        if ($is_admin != 1) {
            $_SESSION['error'] = "Access denied. Administrator privileges required.";
            header("Location: ../student/dashboard.php"); // Redirect unauthorized user
            exit;
        }
    } else {
        // If Student access is required (or a public page)
        // Redirect admins trying to access student pages (e.g., student/dashboard.php)
        if ($is_admin == 1) {
            header("Location: ../admin/dashboard.php"); // Redirect admin to their correct area
            exit;
        }
    }
}
?>