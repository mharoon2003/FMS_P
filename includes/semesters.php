<?php
require_once __DIR__ . '/../config/database.php';

function semester_create($data) {
    $pdo = get_db();
    $stmt = $pdo->prepare("
        INSERT INTO semesters (name, academic_year, semester_number, is_active)
        VALUES (?, ?, ?, ?)
    ");
    return $stmt->execute([
        $data['name'],
        $data['academic_year'],
        $data['semester_number'],
        $data['is_active'] ? 1 : 0
    ]);
}

function semester_get_all() {
    $pdo = get_db();
    //  Include semester_number in SELECT
    $stmt = $pdo->prepare("SELECT * FROM semesters ORDER BY academic_year DESC, semester_number ASC");
    $stmt->execute();
    return $stmt->fetchAll();
}

function semester_find_by_id($id) {
    $pdo = get_db();
    $stmt = $pdo->prepare("SELECT * FROM semesters WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function semester_update($id, $data) {
    $pdo = get_db();
    //  Update semester_number as well
    $stmt = $pdo->prepare("
        UPDATE semesters 
        SET name = ?, academic_year = ?, semester_number = ?, is_active = ?
        WHERE id = ?
    ");
    return $stmt->execute([
        $data['name'],
        $data['academic_year'],
        $data['semester_number'],
        $data['is_active'] ? 1 : 0,
        $id
    ]);
}

function semester_delete($id) {
    $pdo = get_db();
    $stmt = $pdo->prepare("DELETE FROM semesters WHERE id = ?");
    return $stmt->execute([$id]);
}

function semester_get_active() {
    $pdo = get_db();
    $stmt = $pdo->prepare("SELECT * FROM semesters WHERE is_active = 1 ORDER BY semester_number ASC");
    $stmt->execute();
    return $stmt->fetchAll();
}

function semester_get_with_subject_count() {
    $pdo = get_db();
    $stmt = $pdo->prepare("
        SELECT s.*, COUNT(sub.id) as subject_count 
        FROM semesters s
        LEFT JOIN subjects sub ON s.id = sub.semester_id
        GROUP BY s.id
        ORDER BY s.academic_year DESC, s.semester_number ASC
    ");
    $stmt->execute();
    return $stmt->fetchAll();
}