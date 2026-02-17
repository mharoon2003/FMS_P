<?php
require_once __DIR__ . '/../config/database.php';

function lecture_create($data) {
    $pdo = get_db();
    $stmt = $pdo->prepare("
        INSERT INTO lectures (subject_id, teacher_id, title, content, file_path)
        VALUES (?, ?, ?, ?, ?)
    ");
    return $stmt->execute([
        $data['subject_id'],
        $data['teacher_id'],
        $data['title'],
        $data['content'] ?? '',
        $data['file_path'] ?? ''
    ]);
}

function lecture_get_by_subject($subject_id) {
    $pdo = get_db();
    $stmt = $pdo->prepare("
        SELECT l.*, u.full_name as teacher_name 
        FROM lectures l
        LEFT JOIN users u ON l.teacher_id = u.id
        WHERE l.subject_id = ?
        ORDER BY l.created_at DESC
    ");
    $stmt->execute([$subject_id]);
    return $stmt->fetchAll();
}

function lecture_find_by_id($id) {
    $pdo = get_db();
    $stmt = $pdo->prepare("SELECT * FROM lectures WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function lecture_update($id, $data) {
    $set_parts = [];
    $params = [];
    foreach ($data as $key => $value) {
        $set_parts[] = "$key = ?";
        $params[] = $value;
    }
    $params[] = $id;
    $sql = "UPDATE lectures SET " . implode(', ', $set_parts) . ", updated_at = CURRENT_TIMESTAMP WHERE id = ?";
    $pdo = get_db();
    $stmt = $pdo->prepare($sql);
    return $stmt->execute($params);
}

function lecture_delete($id) {
    $pdo = get_db();
    $stmt = $pdo->prepare("SELECT file_path FROM lectures WHERE id = ?");
    $stmt->execute([$id]);
    $lecture = $stmt->fetch();
    
    if ($lecture && $lecture['file_path'] && file_exists($_SERVER['DOCUMENT_ROOT'] . '/' . $lecture['file_path'])) {
        unlink($_SERVER['DOCUMENT_ROOT'] . '/' . $lecture['file_path']);
    }

    $stmt = $pdo->prepare("DELETE FROM lectures WHERE id = ?");
    return $stmt->execute([$id]);
}