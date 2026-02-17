<?php
require_once __DIR__ . '/config/session.php';

$page = $_GET['page'] ?? 'login';

switch ($page) {
    case 'login':
        require_once __DIR__ . '/includes/auth.php';
        auth_login();
        break;

    case 'register':
        require_once __DIR__ . '/includes/auth.php';
        auth_register();
        break;

    case 'logout':
        logout();
        break;

    case 'admin_dashboard':
        requireRole('admin');
        require __DIR__ . '/views/admin/dashboard.php';
        break;

    case 'admin_teachers':
        requireRole('admin');
        require __DIR__ . '/views/admin/teachers.php';
        break;

    case 'admin_students':
        requireRole('admin');
        require __DIR__ . '/views/admin/students.php';
        break;

    case 'admin_semesters':
        requireRole('admin');
        require __DIR__ . '/views/admin/semesters.php';
        break;

    case 'admin_subjects':
        requireRole('admin');
        require __DIR__ . '/views/admin/subjects.php';
        break;

    case 'admin_requests':
        requireRole('admin');
        require __DIR__ . '/views/admin/requests.php';
        break;

    case 'teacher_dashboard':
        requireRole('teacher');
        require __DIR__ . '/views/teacher/dashboard.php';
        break;

    case 'teacher_subject':
        requireRole('teacher');
        require __DIR__ . '/views/teacher/subject.php';
        break;

    case 'student_dashboard':
        requireRole('student');
        require __DIR__ . '/views/student/dashboard.php';
        break;

    case 'student_subject':
        requireRole('student');
        require __DIR__ . '/views/student/subject.php';
        break;
        
    case 'admin_monographs':
         requireRole('admin');
         require __DIR__ . '/views/admin/monographs.php';
         break;
    
     case 'student_monographs':
           
             requireLogin(); //  Only requires login, not specific role
            require __DIR__ . '/views/student/monographs.php';
              break;

    case 'teacher_monographs':
          requireRole('teacher'); // Teachers & admins can access
           require __DIR__ . '/views/teacher/monographs.php';
             break;

    case 'profile':
        requireLogin();
        require __DIR__ . '/views/shared/profile.php';
        break;

    case 'unauthorized':
        require __DIR__ . '/views/shared/unauthorized.php';
        break;

    default:
        require __DIR__ . '/views/shared/404.php';
        break;
}