<?php
require_once __DIR__ . '/../config/database.php';

function user_find_by_username($username)
{
    $pdo = get_db();
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    return $stmt->fetch();
}

function user_find_by_email($email)
{
    $pdo = get_db();
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    return $stmt->fetch();
}

function user_find_by_id($id)
{
    $pdo = get_db();
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function user_create($data) {
    $pdo = get_db();
    $stmt = $pdo->prepare("
        INSERT INTO users (username, email, password, full_name, role, phone, address, semester_id, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    return $stmt->execute([
        $data['username'],
        $data['email'],
        $data['password'],
        $data['full_name'],
        $data['role'],
        $data['phone'] ?? '',
        $data['address'] ?? '',
        $data['semester_id'] ?? null,  
        $data['status']
    ]);
}


function user_update($id, $data)
{
    $set_parts = [];
    $params = [];
    foreach ($data as $key => $value) {
        $set_parts[] = "$key = ?";
        $params[] = $value;
    }
    $params[] = $id;
    $sql = "UPDATE users SET " . implode(', ', $set_parts) . ", updated_at = CURRENT_TIMESTAMP WHERE id = ?";
    $pdo = get_db();
    $stmt = $pdo->prepare($sql);
    return $stmt->execute($params);
}

function user_delete($id)
{
    $pdo = get_db();
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    return $stmt->execute([$id]);
}

function user_update_status($id, $status)
{
    $pdo = get_db();
    $stmt = $pdo->prepare("UPDATE users SET status = ? WHERE id = ?");
    return $stmt->execute([$status, $id]);
}

function user_get_all($role = null, $status = null)
{
    $pdo = get_db();
    $sql = "SELECT * FROM users WHERE 1=1";
    $params = [];
    if ($role) {
        $sql .= " AND role = ?";
        $params[] = $role;
    }
    if ($status) {
        $sql .= " AND status = ?";
        $params[] = $status;
    }
    $sql .= " ORDER BY created_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}
function user_get_students_by_semester($semester_id) {
    $pdo = get_db();
    $stmt = $pdo->prepare("
        SELECT * FROM users 
        WHERE role = 'student' 
        AND status = 'approved'
        AND semester_id = ?
        ORDER BY full_name
    ");
    $stmt->execute([$semester_id]);
    return $stmt->fetchAll();
}

function user_get_student_count_by_semester($semester_id) {
    $pdo = get_db();
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count 
        FROM users 
        WHERE role = 'student' 
        AND status = 'approved'
        AND semester_id = ?
    ");
    $stmt->execute([$semester_id]);
    $result = $stmt->fetch();
    return (int)($result['count'] ?? 0);
}