<?php
require_once __DIR__ . '/../config/database.php';

// Safe upsert: create or update grade by (subject_id, student_id)
function grade_upsert($data) {
    $pdo = get_db();
    
    // Sanitize & cap values
    $class = max(0, min(20, floatval($data['class_activity'] ?? 0)));
    $mid = max(0, min(20, floatval($data['midterm_exam'] ?? 0)));
    $final = max(0, min(60, floatval($data['final_exam'] ?? 0)));
    $total = min(100, $class + $mid + $final);

    $stmt = $pdo->prepare("
        INSERT INTO grades 
        (subject_id, student_id, teacher_id, class_activity, midterm_exam, final_exam, grade, max_grade, remarks)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            class_activity = VALUES(class_activity),
            midterm_exam = VALUES(midterm_exam),
            final_exam = VALUES(final_exam),
            grade = VALUES(grade),
            max_grade = VALUES(max_grade),
            remarks = VALUES(remarks),
            teacher_id = VALUES(teacher_id),
            updated_at = CURRENT_TIMESTAMP
    ");
    return $stmt->execute([
        $data['subject_id'],
        $data['student_id'],
        $data['teacher_id'] ?? getUserId(),
        $class,
        $mid,
        $final,
        $total,
        100,
        $data['remarks'] ?? ''
    ]);
}

// Edit by primary key (for explicit edit modal)
function grade_update_by_id($id, $data) {
    $pdo = get_db();
    
    $class = max(0, min(20, floatval($data['class_activity'] ?? 0)));
    $mid = max(0, min(20, floatval($data['midterm_exam'] ?? 0)));
    $final = max(0, min(60, floatval($data['final_exam'] ?? 0)));
    $total = min(100, $class + $mid + $final);

    $stmt = $pdo->prepare("
        UPDATE grades 
        SET 
            class_activity = ?,
            midterm_exam = ?,
            final_exam = ?,
            grade = ?,
            remarks = ?,
            updated_at = CURRENT_TIMESTAMP
        WHERE id = ?
    ");
    return $stmt->execute([$class, $mid, $final, $total, $data['remarks'] ?? '', $id]);
}

// Fetchers (unchanged, correct)
function grade_get_by_student($student_id) {
    $pdo = get_db();
    $stmt = $pdo->prepare("
        SELECT 
            g.*,
            s.name as subject_name, 
            s.code as subject_code,
            u.full_name as teacher_name,
            sem.name as semester_name
        FROM grades g
        LEFT JOIN subjects s ON g.subject_id = s.id
        LEFT JOIN users u ON g.teacher_id = u.id
        LEFT JOIN semesters sem ON s.semester_id = sem.id
        WHERE g.student_id = ?
        ORDER BY s.name
    ");
    $stmt->execute([$student_id]);
    return $stmt->fetchAll(); //  return class_activity, midterm_exam, final_exam
}

function grade_get_by_subject($subject_id) {
    $pdo = get_db();
    $stmt = $pdo->prepare("
        SELECT g.*, u.full_name as student_name, u.email as student_email
        FROM grades g
        LEFT JOIN users u ON g.student_id = u.id
        WHERE g.subject_id = ?
        ORDER BY u.full_name
    ");
    $stmt->execute([$subject_id]);
    return $stmt->fetchAll();
}

function grade_find_by_id($id) {
    $pdo = get_db();
    $stmt = $pdo->prepare("SELECT * FROM grades WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function grade_find_by_subject_and_student($subject_id, $student_id) {
    $pdo = get_db();
    $stmt = $pdo->prepare("
        SELECT * FROM grades 
        WHERE subject_id = ? AND student_id = ?
    ");
    $stmt->execute([$subject_id, $student_id]);
    return $stmt->fetch();
}

function grade_delete($id) {
    $pdo = get_db();
    $stmt = $pdo->prepare("DELETE FROM grades WHERE id = ?");
    return $stmt->execute([$id]);
}