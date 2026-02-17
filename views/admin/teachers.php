<?php
// Procedural version — FMS_P
$page_title = 'Manage Teachers';

// Include database connection
//require_once '../../config/database.php';
require_once  '../../includes/users.php';
require_once  '../../includes/semesters.php';

// Initialize message
$message = '';
$messageType = '';

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_teacher'])) {
        $full_name = trim($_POST['full_name']);
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $password = password_hash(trim($_POST['password']), PASSWORD_DEFAULT);
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');

        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, full_name, role, status, phone, address) VALUES (?, ?, ?, ?, 'teacher', 'approved', ?, ?)");
        if ($stmt->execute([$username, $email, $password, $full_name, $phone, $address])) {
            $message = 'Teacher added successfully!';
            $messageType = 'success';
        } else {
            $message = 'Failed to add teacher.';
            $messageType = 'danger';
        }
    } elseif (isset($_POST['edit_teacher'])) {
        $user_id = (int)$_POST['user_id'];
        $full_name = trim($_POST['full_name']);
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $semester_id = (int)($_POST['semester_id'] ?? 0);

        // Update user
        $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, full_name = ?, phone = ?, address = ?, semester_id = ? WHERE id = ?");
        if ($stmt->execute([$username, $email, $full_name, $phone, $address, $semester_id, $user_id])) {
            $message = 'Teacher updated successfully!';
            $messageType = 'success';
        } else {
            $message = 'Failed to update teacher.';
            $messageType = 'danger';
        }
    } elseif (isset($_POST['delete_teacher'])) {
        $user_id = (int)$_POST['user_id'];
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        if ($stmt->execute([$user_id])) {
            $message = 'Teacher deleted successfully!';
            $messageType = 'success';
        } else {
            $message = 'Failed to delete teacher.';
            $messageType = 'danger';
        }
    }
}

// Fetch all teachers
$stmt = $pdo->query("SELECT * FROM users WHERE role = 'teacher' AND status = 'approved'");
$teachers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Group teachers by semester
$semester_tabs = [];
for ($i = 1; $i <= 8; $i++) {
    $semester_tabs[$i] = [];
}
$unassigned_teachers = [];

foreach ($teachers as $teacher) {
    $semester_id = $teacher['semester_id'];
    if ($semester_id) {
        $stmt_sem = $pdo->prepare("SELECT semester_number FROM semesters WHERE id = ?");
        $stmt_sem->execute([$semester_id]);
        $sem = $stmt_sem->fetch();
        if ($sem && $sem['semester_number'] >= 1 && $sem['semester_number'] <= 8) {
            $semester_tabs[$sem['semester_number']][] = $teacher;
        } else {
            $unassigned_teachers[] = $teacher;
        }
    } else {
        $unassigned_teachers[] = $teacher;
    }
}

$currentSemester = (int)($_GET['semester'] ?? 0);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-person-badge"></i> Manage Teachers</h2>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTeacherModal">
            <i class="bi bi-plus-lg"></i> Add Teacher
        </button>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-<?php echo htmlspecialchars($messageType); ?> alert-dismissible fade show">
            <?php echo htmlspecialchars($message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Semester Tabs -->
    <ul class="nav nav-tabs mb-4" role="tablist">
        <?php if (!empty($unassigned_teachers)): ?>
            <li class="nav-item">
                <button class="nav-link <?php echo (empty($semester_tabs[1])) ? 'active' : ''; ?>" data-bs-toggle="tab"
                    data-bs-target="#unassigned">
                    Unassigned <span class="badge bg-warning ms-2"><?php echo count($unassigned_teachers); ?></span>
                </button>
            </li>
        <?php endif; ?>

        <?php for ($i = 1; $i <= 8; $i++): ?>
            <li class="nav-item">
                <a href="/FMS/index.php?page=admin_teachers&semester=<?php echo $i; ?>"
                    class="nav-link <?php echo $i === $currentSemester ? 'active' : ''; ?>">
                    <?php echo numberToOrdinalName($i); ?> Semester
                    <span class="badge bg-info ms-2"><?php echo count($semester_tabs[$i]); ?></span>
                </a>
            </li>
        <?php endfor; ?>
        <li class="nav-item">
            <a href="/FMS/index.php?page=admin_teachers"
                class="nav-link <?php echo $currentSemester === 0 ? 'active' : ''; ?>">
                All Teachers
            </a>
        </li>
    </ul>

    <div class="tab-content">
        <!-- Unassigned Teachers -->
        <?php if (!empty($unassigned_teachers)): ?>
            <div class="tab-pane fade <?php echo $currentSemester === 0 ? 'show active' : ''; ?>" id="unassigned">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="text-warning">Unassigned Teachers</h5>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Full Name</th>
                                        <th>Username</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($unassigned_teachers as $teacher): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($teacher['full_name']); ?></td>
                                            <td><?php echo htmlspecialchars($teacher['username']); ?></td>
                                            <td><?php echo htmlspecialchars($teacher['email']); ?></td>
                                            <td><?php echo htmlspecialchars($teacher['phone'] ?: '-'); ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-warning" onclick="editTeacher(<?php echo htmlspecialchars(json_encode([
                                                    'id' => $teacher['id'],
                                                    'full_name' => $teacher['full_name'],
                                                    'username' => $teacher['username'],
                                                    'email' => $teacher['email'],
                                                    'phone' => $teacher['phone'],
                                                    'address' => $teacher['address'],
                                                    'semester_id' => $teacher['semester_id']
                                                ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT)); ?>)">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <form method="POST" style="display: inline;"
                                                    action="/FMS/index.php?page=admin_teachers">
                                                    <input type="hidden" name="user_id"
                                                        value="<?php echo (int) $teacher['id']; ?>">
                                                    <button type="submit" name="delete_teacher" class="btn btn-sm btn-danger"
                                                        onclick="return confirm('Delete this teacher?');">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Semester Tabs (1 to 8) -->
        <?php for ($i = 1; $i <= 8; $i++): ?>
            <div class="tab-pane fade <?php echo $i === $currentSemester ? 'show active' : ''; ?>" id="sem-<?php echo $i; ?>">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5><?php echo numberToOrdinalName($i); ?> Semester Teachers</h5>
                        <?php if (empty($semester_tabs[$i])): ?>
                            <p class="text-muted">No teachers in this semester.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Full Name</th>
                                            <th>Username</th>
                                            <th>Email</th>
                                            <th>Phone</th>
                                            <th>Subjects Taught</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($semester_tabs[$i] as $teacher): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($teacher['full_name']); ?></td>
                                                <td><?php echo htmlspecialchars($teacher['username']); ?></td>
                                                <td><?php echo htmlspecialchars($teacher['email']); ?></td>
                                                <td><?php echo htmlspecialchars($teacher['phone'] ?: '-'); ?></td>
                                                <td>
                                                    <ul class="list-unstyled mb-0">
                                                        <?php
                                                        // Fetch subjects taught by this teacher in this semester
                                                        $stmt_subjects = $pdo->prepare("
                                                            SELECT s.name 
                                                            FROM subjects s
                                                            INNER JOIN subject_teachers st ON s.id = st.subject_id
                                                            WHERE st.teacher_id = ? AND s.semester_id = ?
                                                        ");
                                                        $stmt_subjects->execute([$teacher['id'], $teacher['semester_id']]);
                                                        $subjectsTaught = $stmt_subjects->fetchAll(PDO::FETCH_COLUMN);
                                                        foreach ($subjectsTaught as $subjectName) {
                                                            echo "<li>" . htmlspecialchars($subjectName) . "</li>";
                                                        }
                                                        ?>
                                                    </ul>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endfor; ?>

        <!-- All Teachers Tab -->
        <div class="tab-pane fade <?php echo $currentSemester === 0 ? 'show active' : ''; ?>" id="all-teachers">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5>All Teachers</h5>
                    <?php if (empty($teachers)): ?>
                        <p class="text-muted">No teachers found.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Full Name</th>
                                        <th>Username</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($teachers as $teacher): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($teacher['full_name']); ?></td>
                                            <td><?php echo htmlspecialchars($teacher['username']); ?></td>
                                            <td><?php echo htmlspecialchars($teacher['email']); ?></td>
                                            <td><?php echo htmlspecialchars($teacher['phone'] ?: '-'); ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-warning" onclick="editTeacher(<?php echo htmlspecialchars(json_encode([
                                                    'id' => $teacher['id'],
                                                    'full_name' => $teacher['full_name'],
                                                    'username' => $teacher['username'],
                                                    'email' => $teacher['email'],
                                                    'phone' => $teacher['phone'],
                                                    'address' => $teacher['address'],
                                                    'semester_id' => $teacher['semester_id']
                                                ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT)); ?>)">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <form method="POST" style="display: inline;"
                                                    action="/FMS/index.php?page=admin_teachers">
                                                    <input type="hidden" name="user_id"
                                                        value="<?php echo (int) $teacher['id']; ?>">
                                                    <button type="submit" name="delete_teacher" class="btn btn-sm btn-danger"
                                                        onclick="return confirm('Delete this teacher?');">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Teacher Modal -->
<div class="modal fade" id="addTeacherModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="/FMS/index.php?page=admin_teachers">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Teacher</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" class="form-control" name="full_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" class="form-control" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" class="form-control" name="password" minlength="6" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phone</label>
                        <input type="tel" class="form-control" name="phone">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <textarea class="form-control" name="address" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="add_teacher" class="btn btn-primary">Add Teacher</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Teacher Modal -->
<div class="modal fade" id="editTeacherModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="/FMS/index.php?page=admin_teachers">
                <input type="hidden" name="user_id" id="edit_user_id">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Teacher</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" class="form-control" name="full_name" id="edit_full_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" class="form-control" name="username" id="edit_username" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Semester</label>
                        <select class="form-select" name="semester_id" id="edit_semester_id" required>
                            <option value="">Select semester</option>
                            <?php for ($i = 1; $i <= 8; $i++): ?>
                                <option value="<?php echo $i; ?>">
                                    <?php echo numberToOrdinalName($i); ?> Semester
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" id="edit_email" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password (leave blank to keep current)</label>
                        <input type="password" class="form-control" name="password" minlength="6">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phone</label>
                        <input type="tel" class="form-control" name="phone" id="edit_phone">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <textarea class="form-control" name="address" id="edit_address" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="edit_teacher" class="btn btn-primary">Update Teacher</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editTeacher(teacher) {
    try {
        if (!teacher || typeof teacher !== 'object') {
            alert("Invalid teacher data.");
            return;
        }

        document.getElementById('edit_user_id').value = teacher.id;
        document.getElementById('edit_full_name').value = teacher.full_name || '';
        document.getElementById('edit_username').value = teacher.username || '';
        document.getElementById('edit_email').value = teacher.email || '';

        // ✅ Safely set phone and address
        const phoneInput = document.getElementById('edit_phone');
        if (phoneInput) phoneInput.value = teacher.phone || '';

        const addressInput = document.getElementById('edit_address');
        if (addressInput) addressInput.value = teacher.address || '';

        // ✅ Safely set semester_id
        const semesterSelect = document.getElementById('edit_semester_id');
        if (semesterSelect) {
            semesterSelect.value = teacher.semester_id || '';
        }

        const modalElement = document.getElementById('editTeacherModal');
        if (!modalElement) {
            alert("Edit modal not found.");
            return;
        }

        // ✅ Check if bootstrap.Modal is available
        if (typeof bootstrap === 'undefined' || typeof bootstrap.Modal === 'undefined') {
            alert("Bootstrap Modal is not available. Please include Bootstrap JS.");
            return;
        }

        const modal = new bootstrap.Modal(modalElement);
        modal.show();

    } catch (e) {
        console.error("Error in editTeacher:", e);
        alert("An error occurred. Check console for details.");
    }
}
</script>

</body>
</html>