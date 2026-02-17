<?php

if (session_status() === PHP_SESSION_NONE) {
    // Make session cookie expire when browser closes
    ini_set('session.cookie_lifetime', 0);       // Critical: 0 = session cookie
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', 0);         // Set to 1 if using HTTPS
    session_start();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /FMS_P/index.php?page=login');
        exit();
    }
}

function requireRole($allowed_roles) {
    requireLogin();
    $allowed_roles = is_array($allowed_roles) ? $allowed_roles : [$allowed_roles];
    if (!in_array($_SESSION['role'], $allowed_roles)) {
        header('Location: /FMS_P/index.php?page=unauthorized');
        exit();
    }
}

function getUserId() { return $_SESSION['user_id'] ?? null; }
function getUserRole() { return $_SESSION['role'] ?? null; }
function getUserName() { return $_SESSION['username'] ?? null; }
function getFullName() { return $_SESSION['full_name'] ?? null; }

function logout() {
    session_unset();
    session_destroy();
    header('Location: /FMS_P/index.php?page=login');
    exit();
}