<?php
require_once __DIR__ . '/users.php';
require_once __DIR__ . '/../config/session.php';

function auth_login() {
    if (isLoggedIn()) {
        auth_redirect_dashboard();
    }

    $error = '';
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($password)) {
            $error = 'Please enter both username and password.';
        } else {
            $user = user_find_by_username($username);
            if (!$user) $user = user_find_by_email($username);

            if ($user && password_verify($password, $user['password'])) {
                if ($user['status'] === 'pending') {
                    $error = 'Your account is pending approval.';
                } elseif ($user['status'] === 'rejected') {
                    $error = 'Your account has been rejected.';
                } else {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['full_name'] = $user['full_name'];
                    auth_redirect_dashboard();
                }
            } else {
                $error = 'Invalid username or password.';
            }
        }
    }

    require __DIR__ . '/../views/auth/login.php';
}

function auth_register() {
    if (isLoggedIn()) {
        auth_redirect_dashboard();
    }

    $error = ''; $success = '';
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        $full_name = trim($_POST['full_name'] ?? '');
        $role = $_POST['role'] ?? '';
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');

        if (empty($username) || empty($email) || empty($password) || empty($full_name) || empty($role)) {
            $error = 'Please fill in all required fields.';
        } elseif ($password !== $confirm_password) {
            $error = 'Passwords do not match.';
        } elseif (strlen($password) < 6) {
            $error = 'Password must be at least 6 characters long.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Invalid email address.';
        } elseif (!in_array($role, ['teacher', 'student'])) {
            $error = 'Invalid role selected.';
        } else {
            if (user_find_by_username($username)) {
                $error = 'Username already exists.';
            } elseif (user_find_by_email($email)) {
                $error = 'Email already exists.';
            } else {
                $data = [
                    'username' => $username,
                    'email' => $email,
                    'password' => password_hash($password, PASSWORD_DEFAULT),
                    'full_name' => $full_name,
                    'role' => $role,
                    'phone' => $phone,
                    'address' => $address,
                    'status' => 'pending'
                ];

                if (user_create($data)) {
                    $success = 'Registration successful! Please wait for admin approval.';
                } else {
                    $error = 'Registration failed. Please try again.';
                }
            }
        }
    }

    require __DIR__ . '/../views/auth/register.php';
}

function auth_redirect_dashboard() {
    $role = getUserRole();
    
    $base = '/FMS_P/index.php?page=';
    switch ($role) {
        case 'admin':
            header("Location: {$base}admin_dashboard");
            break;
        case 'teacher':
            header("Location: {$base}teacher_dashboard");
            break;
        case 'student':
            header("Location: {$base}student_dashboard");
            break;
        default:
            header("Location: {$base}login");
    }
    exit();
}