<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../index.php");
        exit;
    }
}

function requireAdmin() {
    requireLogin();
    // يقبل super_admin أو admin باش ما يطرد حد
    $allowed = ['super_admin','admin_dept', 'admin'];
    if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], $allowed)) {
        header("Location: ../user/dashboard.php");
        exit;
    }
}
?>