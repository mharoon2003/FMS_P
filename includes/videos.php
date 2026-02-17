<?php
require_once __DIR__ . '/../config/database.php';

function video_create($data) {
    $pdo = get_db();
    $stmt = $pdo->prepare("
        INSERT INTO videos (subject_id, teacher_id, title, video_url, description)
        VALUES (?, ?, ?, ?, ?)
    ");
    return $stmt->execute([
        $data['subject_id'],
        $data['teacher_id'],
        $data['title'],
        $data['video_url'], // This can be URL or file path
        $data['description'] ?? ''
    ]);
}

function video_get_by_subject($subject_id) {
    $pdo = get_db();
    $stmt = $pdo->prepare("
        SELECT v.*, u.full_name as teacher_name 
        FROM videos v
        LEFT JOIN users u ON v.teacher_id = u.id
        WHERE v.subject_id = ?
        ORDER BY v.created_at DESC
    ");
    $stmt->execute([$subject_id]);
    return $stmt->fetchAll();
}

function video_find_by_id($id) {
    $pdo = get_db();
    $stmt = $pdo->prepare("SELECT * FROM videos WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function video_update($id, $data) {
    $set_parts = [];
    $params = [];
    foreach ($data as $key => $value) {
        $set_parts[] = "$key = ?";
        $params[] = $value;
    }
    $params[] = $id;
    $sql = "UPDATE videos SET " . implode(', ', $set_parts) . " WHERE id = ?";
    $pdo = get_db();
    $stmt = $pdo->prepare($sql);
    return $stmt->execute($params);
}

function video_delete($id) {
    $pdo = get_db();
    $stmt = $pdo->prepare("DELETE FROM videos WHERE id = ?");
    return $stmt->execute([$id]);
}