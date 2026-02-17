<?php
require_once __DIR__ . '/../config/database.php';

function exercise_create($data) {
    $pdo = get_db();
    $stmt = $pdo->prepare("
        INSERT INTO exercises (subject_id, teacher_id, title, description, file_path, due_date)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    return $stmt->execute([
        $data['subject_id'],
        $data['teacher_id'],
        $data['title'],
        $data['description'] ?? '',
        $data['file_path'] ?? '',
        $data['due_date'] ?: null
    ]);
}

function exercise_get_by_subject($subject_id) {
    $pdo = get_db();
    $stmt = $pdo->prepare("
        SELECT e.*, u.full_name as teacher_name 
        FROM exercises e
        LEFT JOIN users u ON e.teacher_id = u.id
        WHERE e.subject_id = ?
        ORDER BY e.due_date DESC, e.created_at DESC
    ");
    $stmt->execute([$subject_id]);
    return $stmt->fetchAll();
}

function exercise_find_by_id($id) {
    $pdo = get_db();
    $stmt = $pdo->prepare("SELECT * FROM exercises WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function exercise_update($id, $data) {
    $set_parts = [];
    $params = [];
    foreach ($data as $key => $value) {
        $set_parts[] = "$key = ?";
        $params[] = $value;
    }
    $params[] = $id;
    $sql = "UPDATE exercises SET " . implode(', ', $set_parts) . " WHERE id = ?";
    $pdo = get_db();
    $stmt = $pdo->prepare($sql);
    return $stmt->execute($params);
}

function exercise_delete($id) {
    $pdo = get_db();
    $stmt = $pdo->prepare("SELECT file_path FROM exercises WHERE id = ?");
    $stmt->execute([$id]);
    $exercise = $stmt->fetch();
    
    if ($exercise && $exercise['file_path'] && file_exists($_SERVER['DOCUMENT_ROOT'] . '/' . $exercise['file_path'])) {
        unlink($_SERVER['DOCUMENT_ROOT'] . '/' . $exercise['file_path']);
    }

    $stmt = $pdo->prepare("DELETE FROM exercises WHERE id = ?");
    return $stmt->execute([$id]);
}