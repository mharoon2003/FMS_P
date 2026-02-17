<?php
require_once __DIR__ . '/../../includes/users.php';
require_once __DIR__ . '/../../includes/semesters.php';

$message = '';
$messageType = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_student'])) {
        // Validate input
        $full_name = trim($_POST['full_name'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $semester_id = !empty($_POST['semester_id']) ? intval($_POST['semester_id']) : null;

        if (empty($full_name) || empty($username) || empty($email) || empty($password)) {
            $message = 'All fields are required.';
            $messageType = 'danger';
        } elseif (strlen($password) < 6) {
            $message = 'Password must be at least 6 characters.';
            $messageType = 'danger';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = 'Invalid email address.';
            $messageType = 'danger';
        } else {
            $data = [
                'username' => $username,
                'email' => $email,
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'full_name' => $full_name,
                'role' => 'student',
                'phone' => trim($_POST['phone'] ?? ''),
                'address' => trim($_POST['address'] ?? ''),
                'semester_id' => $semester_id,
                'status' => 'approved'
            ];
            if (user_create($data)) {
                $message = 'Student added successfully!';
                $messageType = 'success';
            } else {
                $message = 'Failed to add student.';
                $messageType = 'danger';
            }
        }
    } 
    elseif (isset($_POST['edit_student'])) {
        $user_id = intval($_POST['user_id']);
        $data = [
            'username' => trim($_POST['username']),
            'email' => trim($_POST['email']),
            'full_name' => trim($_POST['full_name']),
            'phone' => trim($_POST['phone'] ?? ''),
            'address' => trim($_POST['address'] ?? ''),
            'semester_id' => !empty($_POST['semester_id']) ? intval($_POST['semester_id']) : null
        ];
        if (!empty($_POST['password'])) {
            $data['password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
        }
        if (user_update($user_id, $data)) {
            $message = 'Student updated successfully!';
            $messageType = 'success';
        } else {
            $message = 'Failed to update student.';
            $messageType = 'danger';
        }
    } 
    elseif (isset($_POST['delete_student'])) {
        $user_id = intval($_POST['user_id']);
        if (user_delete($user_id)) {
            $message = 'Student deleted successfully!';
            $messageType = 'success';
        } else {
            $message = 'Failed to delete student.';
            $messageType = 'danger';
        }
    }
}

// Fetch data
$semesters = semester_get_all();
$all_students = user_get_all('student', 'approved');

// Group students by semester number (1 to 8)
$semester_tabs = [];
for ($i = 1; $i <= 8; $i++) {
    $semester_tabs[$i] = [];
}

$unassigned_students = [];

foreach ($all_students as $student) {
    $sem_id = $student['semester_id'];
    if ($sem_id) {
        // Find semester number
        $sem_num = 0;
        foreach ($semesters as $s) {
            if ($s['id'] == $sem_id) {
                $sem_num = $s['semester_number'];
                break;
            }
        }
        if ($sem_num >= 1 && $sem_num <= 8) {
            $semester_tabs[$sem_num][] = $student;
        } else {
            $unassigned_students[] = $student; // Fallback
        }
    } else {
        $unassigned_students[] = $student;
    }
}

// Helper: Convert number to ordinal name
function number_to_ordinal_name($num) {
    $names = [
        1 => 'First', 2 => 'Second', 3 => 'Third', 4 => 'Fourth',
        5 => 'Fifth', 6 => 'Sixth', 7 => 'Seventh', 8 => 'Eighth'
    ];
    return $names[$num] ?? "Semester $num";
}

$page_title = 'Manage Students';
?>
<?php require __DIR__ . '/../../includes/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-people"></i> Manage Students</h2>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addStudentModal">
        <i class="bi bi-plus-lg"></i> Add Student
    </button>
</div>

<?php if ($message): ?>
    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show">
        <?php echo htmlspecialchars($message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Semester Tabs -->
<ul class="nav nav-tabs mb-4" role="tablist">
    <?php if (!empty($unassigned_students)): ?>
        <li class="nav-item">
            <button class="nav-link <?php echo (empty($semester_tabs[1])) ? 'active' : ''; ?>" 
                    data-bs-toggle="tab" data-bs-target="#unassigned">
                Unassigned <span class="badge bg-warning ms-2"><?php echo count($unassigned_students); ?></span>
            </button>
        </li>
    <?php endif; ?>
    
    <?php for ($i = 1; $i <= 8; $i++): ?>
        <li class="nav-item">
            <button class="nav-link <?php echo (!empty($unassigned_students) && $i === 1) ? '' : ($i === 1 ? 'active' : ''); ?>" 
                    data-bs-toggle="tab" data-bs-target="#sem-<?php echo $i; ?>">
                <?php echo number_to_ordinal_name($i); ?> Semester 
                <span class="badge bg-info ms-2"><?php echo count($semester_tabs[$i]); ?></span>
            </button>
        </li>
    <?php endfor; ?>
</ul>

<!-- Tab Content -->
<div class="tab-content">
    <!-- Unassigned Students -->
    <?php if (!empty($unassigned_students)): ?>
        <div class="tab-pane fade <?php echo (empty($semester_tabs[1])) ? 'show active' : ''; ?>" id="unassigned">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="text-warning">Unassigned Students</h5>
                    <?php if (empty($unassigned_students)): ?>
                        <p class="text-muted">No unassigned students.</p>
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
                                    <?php foreach ($unassigned_students as $student): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($student['full_name']); ?></td>
                                            <td><?php echo htmlspecialchars($student['username']); ?></td>
                                            <td><?php echo htmlspecialchars($student['email']); ?></td>
                                            <td><?php echo htmlspecialchars($student['phone'] ?: '-'); ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-warning" 
                                                        onclick="editStudent(<?php echo htmlspecialchars(json_encode($student, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT)); ?>)">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <form method="POST" style="display: inline;" action="/FMS_P/index.php?page=admin_students">
                                                    <input type="hidden" name="user_id" value="<?php echo (int)$student['id']; ?>">
                                                    <button type="submit" name="delete_student" class="btn btn-sm btn-danger"
                                                            onclick="return confirm('Delete this student?');">
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
    <?php endif; ?>

    <!-- Semester Tabs (1 to 8) -->
    <?php for ($i = 1; $i <= 8; $i++): ?>
        <div class="tab-pane fade <?php echo ((!empty($unassigned_students) && $i === 1) ? '' : ($i === 1 ? 'show active' : '')); ?>" 
             id="sem-<?php echo $i; ?>">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5><?php echo number_to_ordinal_name($i); ?> Semester Students</h5>
                    <?php if (empty($semester_tabs[$i])): ?>
                        <p class="text-muted">No students in this semester.</p>
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
                                    <?php foreach ($semester_tabs[$i] as $student): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($student['full_name']); ?></td>
                                            <td><?php echo htmlspecialchars($student['username']); ?></td>
                                            <td><?php echo htmlspecialchars($student['email']); ?></td>
                                            <td><?php echo htmlspecialchars($student['phone'] ?: '-'); ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-warning" 
                                                        onclick="editStudent(<?php echo htmlspecialchars(json_encode($student, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT)); ?>)">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <form method="POST" style="display: inline;" action="/FMS_P/index.php?page=admin_students">
                                                    <input type="hidden" name="user_id" value="<?php echo (int)$student['id']; ?>">
                                                    <button type="submit" name="delete_student" class="btn btn-sm btn-danger"
                                                            onclick="return confirm('Delete this student?');">
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
    <?php endfor; ?>
</div>

<!-- Add Student Modal -->
<div class="modal fade" id="addStudentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="/FMS_P/index.php?page=admin_students">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Student</h5>
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
                        <label class="form-label">Semester</label>
                        <select class="form-select" name="semester_id" required>
                            <option value="">Select Semester</option>
                            <?php foreach ($semesters as $semester): ?>
                                <option value="<?php echo (int)$semester['id']; ?>">
                                    <?php echo htmlspecialchars($semester['name']); ?> 
                                    (<?php echo number_to_ordinal_name($semester['semester_number']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
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
                    <button type="submit" name="add_student" class="btn btn-primary">Add Student</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Student Modal -->
<div class="modal fade" id="editStudentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="/FMS_P/index.php?page=admin_students">
                <input type="hidden" name="user_id" id="edit_user_id">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Student</h5>
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
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" id="edit_email" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password (leave blank to keep current)</label>
                        <input type="password" class="form-control" name="password" minlength="6">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Semester</label>
                        <select class="form-select" name="semester_id" id="edit_semester_id">
                            <option value="">Not Assigned</option>
                            <?php foreach ($semesters as $semester): ?>
                                <option value="<?php echo (int)$semester['id']; ?>">
                                    <?php echo htmlspecialchars($semester['name']); ?> 
                                    (<?php echo number_to_ordinal_name($semester['semester_number']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
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
                    <button type="submit" name="edit_student" class="btn btn-primary">Update Student</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editStudent(student) {
    document.getElementById('edit_user_id').value = student.id;
    document.getElementById('edit_full_name').value = student.full_name;
    document.getElementById('edit_username').value = student.username;
    document.getElementById('edit_email').value = student.email;
    document.getElementById('edit_phone').value = student.phone || '';
    document.getElementById('edit_address').value = student.address || '';
    document.getElementById('edit_semester_id').value = student.semester_id || '';
    
    const modal = new bootstrap.Modal(document.getElementById('editStudentModal'));
    modal.show();
}
</script>

<?php require __DIR__ . '/../../includes/footer.php'; ?>