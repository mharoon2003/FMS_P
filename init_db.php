<?php
require_once __DIR__ . '/config/database.php';

$pdo = get_db();

try {
    // Drop tables in reverse order
    $pdo->exec("DROP TABLE IF EXISTS grades");
    $pdo->exec("DROP TABLE IF EXISTS videos");
    $pdo->exec("DROP TABLE IF EXISTS exercises");
    $pdo->exec("DROP TABLE IF EXISTS lectures");
    $pdo->exec("DROP TABLE IF EXISTS subject_students");
    $pdo->exec("DROP TABLE IF EXISTS subject_teachers");
    $pdo->exec("DROP TABLE IF EXISTS subjects");
    $pdo->exec("DROP TABLE IF EXISTS semesters");
    $pdo->exec("DROP TABLE IF EXISTS users");

    // Create tables
    $pdo->exec("
        CREATE TABLE users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            full_name VARCHAR(100) NOT NULL,
            role ENUM('admin','teacher','student') NOT NULL,
            status ENUM('pending','approved','rejected') DEFAULT 'pending',
            phone VARCHAR(20),
            address TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");

    $pdo->exec("
        CREATE TABLE semesters (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(50) NOT NULL,
            type ENUM('Odd','Even') NOT NULL,
            academic_year VARCHAR(20) NOT NULL,
            is_active TINYINT(1) DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");

    $pdo->exec("
        CREATE TABLE subjects (
            id INT AUTO_INCREMENT PRIMARY KEY,
            semester_id INT,
            name VARCHAR(100) NOT NULL,
            code VARCHAR(20) UNIQUE NOT NULL,
            description TEXT,
            credits INT DEFAULT 3,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (semester_id) REFERENCES semesters(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");

    $pdo->exec("
        CREATE TABLE subject_teachers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            subject_id INT,
            teacher_id INT,
            assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_assignment (subject_id, teacher_id),
            FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
            FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");

    $pdo->exec("
        CREATE TABLE subject_students (
            id INT AUTO_INCREMENT PRIMARY KEY,
            subject_id INT,
            student_id INT,
            enrolled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_enrollment (subject_id, student_id),
            FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
            FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");

    $pdo->exec("
        CREATE TABLE lectures (
            id INT AUTO_INCREMENT PRIMARY KEY,
            subject_id INT,
            teacher_id INT,
            title VARCHAR(200) NOT NULL,
            content TEXT,
            file_path VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
            FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");

    $pdo->exec("
        CREATE TABLE exercises (
            id INT AUTO_INCREMENT PRIMARY KEY,
            subject_id INT,
            teacher_id INT,
            title VARCHAR(200) NOT NULL,
            description TEXT,
            file_path VARCHAR(255),
            due_date DATE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
            FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");

    $pdo->exec("
        CREATE TABLE videos (
            id INT AUTO_INCREMENT PRIMARY KEY,
            subject_id INT,
            teacher_id INT,
            title VARCHAR(200) NOT NULL,
            video_url TEXT NOT NULL,
            description TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
            FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");

    $pdo->exec("
        CREATE TABLE grades (
            id INT AUTO_INCREMENT PRIMARY KEY,
            subject_id INT,
            student_id INT,
            teacher_id INT,
            grade DECIMAL(5,2) NOT NULL,
            max_grade DECIMAL(5,2) DEFAULT 100,
            remarks TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY unique_grade (subject_id, student_id),
            FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
            FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");

    // Insert default admin
    $admin_hash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';
    $stmt = $pdo->prepare("
        INSERT INTO users (username, email, password, full_name, role, status)
        VALUES (?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE username = username
    ");
    $stmt->execute(['admin', 'admin@faculty.edu', $admin_hash, 'System Administrator', 'admin', 'approved']);

    echo " Database initialized successfully!\n";
    echo "Default admin:\nUsername: admin\nPassword: admin123\n";

} catch (PDOException $e) {
    echo " Database error: " . $e->getMessage() . "\n";
}