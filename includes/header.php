<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'Faculty Management System'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css  " rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css  ">
    <link rel="stylesheet" href="/FMS_P/assets/css/style.css">
</head>
<body>
    <?php if (isLoggedIn()): ?>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="/FMS_P/index.php?page=<?php echo getUserRole(); ?>_dashboard">
                <i class="bi bi-mortarboard"></i> Faculty Management
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <?php if (getUserRole() === 'admin'): ?>
                        <li class="nav-item"><a class="nav-link" href="/FMS_P/index.php?page=admin_dashboard">Dashboard</a></li>
                        <li class="nav-item"><a class="nav-link" href="/FMS_P/index.php?page=admin_semesters">Semesters</a></li>
                        <li class="nav-item"><a class="nav-link" href="/FMS_P/index.php?page=admin_subjects">Subjects</a></li>
                        <li class="nav-item"><a class="nav-link" href="/FMS_P/index.php?page=admin_teachers">Teachers</a></li>
                        <li class="nav-item"><a class="nav-link" href="/FMS_P/index.php?page=admin_students">Students</a></li>
                        <li class="nav-item"><a class="nav-link" href="/FMS_P/index.php?page=admin_monographs">Monographs</a></li>
                        
                            <a class="nav-link" href="/FMS_P/index.php?page=admin_requests">
                                Requests
                                <?php
                                require_once __DIR__ . '/../includes/users.php';
                                $pending = user_get_all(null, 'pending');
                                if (count($pending) > 0): ?>
                                    <span class="badge bg-danger"><?php echo count($pending); ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                    <?php elseif (getUserRole() === 'teacher'): ?>
                        <li class="nav-item"><a class="nav-link" href="/FMS_P/index.php?page=teacher_dashboard">My Subjects</a></li>
                        <li class="nav-item"><a class="nav-link" href="/FMS_P/index.php?page=teacher_monographs">Monographs</a></li>
                    <?php elseif (getUserRole() === 'student'): ?>
                        <li class="nav-item"><a class="nav-link" href="/FMS_P/index.php?page=student_dashboard">My Subjects</a></li>
                        <li class="nav-item"><a class="nav-link" href="/FMS_P/index.php?page=student_monographs">Monographs</a></li>
                    <?php endif; ?>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" 
                           data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars(getFullName()); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="/FMS_P/index.php?page=profile"><i class="bi bi-person"></i> Profile</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="/FMS_P/index.php?page=logout"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <?php endif; ?>
    
    <div class="container-fluid mt-4"></div>