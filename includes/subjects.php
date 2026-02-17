<?php
require_once __DIR__ . '/../config/database.php';

function subject_create($data) {
    $pdo = get_db();
    $stmt = $pdo->prepare("
        INSERT INTO subjects (semester_id, name, code, description, credits)
        VALUES (?, ?, ?, ?, ?)
    ");
    return $stmt->execute([
        $data['semester_id'],
        $data['name'],
        $data['code'],
        $data['description'] ?? '',
        $data['credits']
    ]);
}

function subject_get_all() {
    $pdo = get_db();
    $stmt = $pdo->prepare("
        SELECT s.*, sem.name as semester_name, sem.semester_number
        FROM subjects s
        LEFT JOIN semesters sem ON s.semester_id = sem.id
        ORDER BY sem.academic_year DESC, s.name
    ");
    $stmt->execute();
    return $stmt->fetchAll();
}

function subject_find_by_id($id) {
    $pdo = get_db();
    $stmt = $pdo->prepare("
        SELECT s.*, sem.name as semester_name, sem.semester_number
        FROM subjects s
        LEFT JOIN semesters sem ON s.semester_id = sem.id
        WHERE s.id = ?
    ");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function subject_get_by_semester($semester_id) {
    $pdo = get_db();
    $stmt = $pdo->prepare("SELECT * FROM subjects WHERE semester_id = ? ORDER BY name");
    $stmt->execute([$semester_id]);
    return $stmt->fetchAll();
}

function subject_get_by_teacher($teacher_id) {
    $pdo = get_db();
    $stmt = $pdo->prepare("
        SELECT s.*, sem.name as semester_name, sem.semester_number
        FROM subjects s
        LEFT JOIN semesters sem ON s.semester_id = sem.id
        INNER JOIN subject_teachers st ON s.id = st.subject_id
        WHERE st.teacher_id = ?
        ORDER BY sem.academic_year DESC, s.name
    ");
    $stmt->execute([$teacher_id]);
    return $stmt->fetchAll();
}

function subject_update($id, $data) {
    $pdo = get_db();
    $stmt = $pdo->prepare("
        UPDATE subjects 
        SET semester_id = ?, name = ?, code = ?, description = ?, credits = ?
        WHERE id = ?
    ");
    return $stmt->execute([
        $data['semester_id'],
        $data['name'],
        $data['code'],
        $data['description'] ?? '',
        $data['credits'],
        $id
    ]);
}

function subject_delete($id) {
    $pdo = get_db();
    $stmt = $pdo->prepare("DELETE FROM subjects WHERE id = ?");
    return $stmt->execute([$id]);
}

//  Assign teacher with semester constraint
function subject_assign_teacher($subject_id, $teacher_id) {
    $pdo = get_db();
    
    // Validate subject exists and get semester
    $stmt = $pdo->prepare("SELECT semester_id FROM subjects WHERE id = ? AND semester_id IS NOT NULL");
    $stmt->execute([$subject_id]);
    $semester_row = $stmt->fetch();
    if (!$semester_row) return false;
    $semester_id = $semester_row['semester_id'];
    
    // Check if teacher already teaches ANY subject in this semester
    $stmt = $pdo->prepare("
        SELECT 1 FROM subject_teachers st
        JOIN subjects s ON st.subject_id = s.id
        WHERE st.teacher_id = ? AND s.semester_id = ?
        LIMIT 1
    ");
    $stmt->execute([$teacher_id, $semester_id]);
    if ($stmt->fetch()) {
        return false; // Already assigned to another subject in this semester
    }
    
    // Assign teacher (use INSERT IGNORE + unique key)
    $stmt = $pdo->prepare("INSERT IGNORE INTO subject_teachers (subject_id, teacher_id) VALUES (?, ?)");
    return $stmt->execute([$subject_id, $teacher_id]);
}
function subject_remove_teacher($subject_id, $teacher_id) {
    $pdo = get_db();
    $stmt = $pdo->prepare("
        DELETE FROM subject_teachers 
        WHERE subject_id = ? AND teacher_id = ?
    ");
    return $stmt->execute([$subject_id, $teacher_id]);
}

function subject_get_teachers($subject_id) {
    $pdo = get_db();
    $stmt = $pdo->prepare("
        SELECT u.* FROM users u
        INNER JOIN subject_teachers st ON u.id = st.teacher_id
        WHERE st.subject_id = ?
        ORDER BY u.full_name
    ");
    $stmt->execute([$subject_id]);
    return $stmt->fetchAll();
}

function subject_is_teacher_assigned($subject_id, $teacher_id) {
    $pdo = get_db();
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count FROM subject_teachers 
        WHERE subject_id = ? AND teacher_id = ?
    ");
    $stmt->execute([$subject_id, $teacher_id]);
    $result = $stmt->fetch();
    return $result['count'] > 0;
}

function subject_unenroll_student($subject_id, $student_id) {
    $pdo = get_db();
    $stmt = $pdo->prepare("
        DELETE FROM subject_students 
        WHERE subject_id = ? AND student_id = ?
    ");
    return $stmt->execute([$subject_id, $student_id]);
}

function subject_is_student_enrolled($subject_id, $student_id) {
    $pdo = get_db();
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count FROM subject_students 
        WHERE subject_id = ? AND student_id = ?
    ");
    $stmt->execute([$subject_id, $student_id]);
    $result = $stmt->fetch();
    return $result['count'] > 0;
}

function subject_get_enrolled_students($subject_id) {
    $pdo = get_db();
    $stmt = $pdo->prepare("
        SELECT u.* FROM users u
        INNER JOIN subject_students ss ON u.id = ss.student_id
        WHERE ss.subject_id = ?
        ORDER BY u.full_name
    ");
    $stmt->execute([$subject_id]);
    return $stmt->fetchAll();
}

function subject_get_by_student($student_id) {
    $pdo = get_db();
    $stmt = $pdo->prepare("
        SELECT s.*, sem.name as semester_name, sem.semester_number
        FROM subjects s
        LEFT JOIN semesters sem ON s.semester_id = sem.id
        INNER JOIN subject_students ss ON s.id = ss.subject_id
        WHERE ss.student_id = ?
        ORDER BY sem.academic_year DESC, s.name
    ");
    $stmt->execute([$student_id]);
    return $stmt->fetchAll();
}

// For admin/teachers.php
function subject_get_teachers_with_subjects_by_semester($semester_id) {
    $pdo = get_db();
    $stmt = $pdo->prepare("
        SELECT DISTINCT 
            u.id, u.full_name, u.email, u.phone, u.address,
            s.id as subject_id, s.name as subject_name, s.code as subject_code
        FROM users u
        INNER JOIN subject_teachers st ON u.id = st.teacher_id
        INNER JOIN subjects s ON st.subject_id = s.id
        WHERE s.semester_id = ?
        AND u.role = 'teacher'
        AND u.status = 'approved'
        ORDER BY u.full_name, s.name
    ");
    $stmt->execute([$semester_id]);
    return $stmt->fetchAll();
}

// For admin/subjects.php
function subject_get_all_with_teachers() {
    $pdo = get_db();
    $stmt = $pdo->prepare("
        SELECT 
            s.*,
            sem.name as semester_name,
            sem.semester_number,
            u.full_name as teacher_name,
            u.id as teacher_id
        FROM subjects s
        LEFT JOIN semesters sem ON s.semester_id = sem.id
        LEFT JOIN subject_teachers st ON s.id = st.subject_id
        LEFT JOIN users u ON st.teacher_id = u.id
        ORDER BY sem.academic_year DESC, s.name
    ");
    $stmt->execute();
    return $stmt->fetchAll();
}

function subject_get_by_semester_with_teachers($semester_id) {
    $pdo = get_db();
    $stmt = $pdo->prepare("
        SELECT 
            s.*,
            sem.name as semester_name,
            sem.semester_number,
            u.full_name as teacher_name,
            u.id as teacher_id
        FROM subjects s
        LEFT JOIN semesters sem ON s.semester_id = sem.id
        LEFT JOIN subject_teachers st ON s.id = st.subject_id
        LEFT JOIN users u ON st.teacher_id = u.id
        WHERE s.semester_id = ?
        ORDER BY s.name
    ");
    $stmt->execute([$semester_id]);
    return $stmt->fetchAll();
}