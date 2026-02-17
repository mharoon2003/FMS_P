<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/file_upload.php'; // Assuming you have this for file validation

// Get all unique publish years
function monograph_get_years() {
    $pdo = get_db();
    $stmt = $pdo->prepare("SELECT DISTINCT publish_year FROM monographs ORDER BY publish_year DESC");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

// Get monographs by year
function monograph_get_by_year($year) {
    $pdo = get_db();
    $stmt = $pdo->prepare("
        SELECT * FROM monographs 
        WHERE publish_year = ? 
        ORDER BY title
    ");
    $stmt->execute([$year]);
    return $stmt->fetchAll();
}

// Get all monographs (for search or admin view)
function monograph_get_all() {
    $pdo = get_db();
    $stmt = $pdo->prepare("
        SELECT m.*, m.student_full_name as student_name, m.instructor_full_name as instructor_name
        FROM monographs m
        ORDER BY m.publish_year DESC, m.title
    ");
    $stmt->execute();
    return $stmt->fetchAll();
}

function monograph_search($keyword) {
    $pdo = get_db();
    $stmt = $pdo->prepare("
        SELECT m.*, m.student_full_name as student_name, m.instructor_full_name as instructor_name
        FROM monographs m
        WHERE MATCH(m.title) AGAINST(? IN NATURAL LANGUAGE MODE)
        OR m.title LIKE ?
        ORDER BY m.publish_year DESC, m.title
    ");
    $like = "%{$keyword}%";
    $stmt->execute([$keyword, $like]);
    return $stmt->fetchAll();
}




// Create a new monograph
function monograph_create($data) {
    $pdo = get_db();
    $stmt = $pdo->prepare("
        INSERT INTO monographs (
            title, file_path, student_full_name, student_email, student_phone, student_photo,
            instructor_full_name, instructor_email, instructor_phone, publish_year
        ) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    return $stmt->execute([
        $data['title'],
        $data['file_path'],
        $data['student_full_name'],
        $data['student_email'] ?? '',
        $data['student_phone'] ?? '',
        $data['student_photo'] ?? '',
        $data['instructor_full_name'],
        $data['instructor_email'] ?? '',
        $data['instructor_phone'] ?? '',
        $data['publish_year']
    ]);
}

// Find by ID
function monograph_find_by_id($id) {
    $pdo = get_db();
    $stmt = $pdo->prepare("SELECT * FROM monographs WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

// Update (optional)
function monograph_update($id, $data) {
    $pdo = get_db();
    $stmt = $pdo->prepare("
        UPDATE monographs 
        SET title = ?, file_path = ?, student_full_name = ?, student_email = ?, 
            student_phone = ?, student_photo = ?, instructor_full_name = ?, 
            instructor_email = ?, instructor_phone = ?, publish_year = ?
        WHERE id = ?
    ");
    return $stmt->execute([
        $data['title'],
        $data['file_path'],
        $data['student_full_name'],
        $data['student_email'] ?? '',
        $data['student_phone'] ?? '',
        $data['student_photo'] ?? '',
        $data['instructor_full_name'],
        $data['instructor_email'] ?? '',
        $data['instructor_phone'] ?? '',
        $data['publish_year'],
        $id
    ]);
}

// Delete
function monograph_delete($id) {
    $pdo = get_db();
    $stmt = $pdo->prepare("DELETE FROM monographs WHERE id = ?");
    return $stmt->execute([$id]);
}

// Get monographs by semester (for students)
function monograph_get_by_semester($semester_id) {
    $pdo = get_db();
    $stmt = $pdo->prepare("
        SELECT m.*, s.name as subject_name, u.full_name as student_full_name, 
               i.full_name as instructor_full_name
        FROM monographs m
        INNER JOIN subjects s ON m.subject_id = s.id
        INNER JOIN users u ON m.student_id = u.id
        INNER JOIN users i ON m.instructor_id = i.id
        WHERE s.semester_id = ?
        ORDER BY m.publish_year DESC, m.title
    ");
    $stmt->execute([$semester_id]);
    return $stmt->fetchAll();
}

// Get monographs by subject IDs (for teachers)
function monograph_get_by_subject_ids($subject_ids) {
    if (empty($subject_ids)) return [];
    
    $placeholders = str_repeat('?,', count($subject_ids) - 1) . '?';
    $pdo = get_db();
    $stmt = $pdo->prepare("
        SELECT m.*, s.name as subject_name, u.full_name as student_full_name, 
               i.full_name as instructor_full_name
        FROM monographs m
        INNER JOIN subjects s ON m.subject_id = s.id
        INNER JOIN users u ON m.student_id = u.id
        INNER JOIN users i ON m.instructor_id = i.id
        WHERE s.id IN ($placeholders)
        ORDER BY m.publish_year DESC, m.title
    ");
    $stmt->execute($subject_ids);
    return $stmt->fetchAll();
}