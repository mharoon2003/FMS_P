<?php
require_once __DIR__ . '/../../includes/semesters.php';
require_once __DIR__ . '/../../includes/subjects.php';
require_once __DIR__ . '/../../includes/users.php';

$message = '';
$messageType = '';

// Handle POST (add/edit/delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_semester'])) {
        $data = [
            'name' => trim($_POST['name']),
            'academic_year' => trim($_POST['academic_year']),
            'semester_number' => intval($_POST['semester_number']),
            'is_active' => !empty($_POST['is_active'])
        ];
        if (semester_create($data)) {
            $message = 'Semester created successfully!';
            $messageType = 'success';
        } else {
            $message = 'Failed to create semester.';
            $messageType = 'danger';
        }
    } 
    elseif (isset($_POST['edit_semester'])) {
        $data = [
            'name' => trim($_POST['name']),
            'academic_year' => trim($_POST['academic_year']),
            'semester_number' => intval($_POST['semester_number']),
            'is_active' => !empty($_POST['is_active'])
        ];
        if (semester_update($_POST['semester_id'], $data)) {
            $message = 'Semester updated successfully!';
            $messageType = 'success';
        } else {
            $message = 'Failed to update semester.';
            $messageType = 'danger';
        }
    } 
    elseif (isset($_POST['delete_semester'])) {
        if (semester_delete($_POST['semester_id'])) {
            $message = 'Semester deleted successfully!';
            $messageType = 'success';
        } else {
            $message = 'Failed to delete semester.';
            $messageType = 'danger';
        }
    }
}

// Fetch all semesters
$semesters = semester_get_all();

// Helper
function number_to_ordinal_name($num) {
    $names = [
        1 => 'First', 2 => 'Second', 3 => 'Third', 4 => 'Fourth',
        5 => 'Fifth', 6 => 'Sixth', 7 => 'Seventh', 8 => 'Eighth'
    ];
    return $names[$num] ?? "Semester $num";
}

$page_title = 'Manage Semesters';
?>
<?php require __DIR__ . '/../../includes/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-calendar3"></i> Manage Semesters</h2>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSemesterModal">
        <i class="bi bi-plus-lg"></i> Add Semester
    </button>
</div>

<?php if ($message): ?>
    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show">
        <?php echo htmlspecialchars($message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="card-body">
        <?php if (empty($semesters)): ?>
            <p class="text-muted">No semesters created yet.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Semester</th>
                            <th>Academic Year</th>
                            <th>Teachers</th>
                            <th>Students</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($semesters as $semester): ?>
                            <?php
                            $semester_id = $semester['id'];
                            $subjects = subject_get_by_semester($semester_id);
                            
                            $teacher_ids = [];
                            foreach ($subjects as $subj) {
                                $teachers = subject_get_teachers($subj['id']);
                                foreach ($teachers as $t) {
                                    $teacher_ids[$t['id']] = $t;
                                }
                            }
                            $teachers = array_values($teacher_ids);
                            $student_count = user_get_student_count_by_semester($semester_id);
                            ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars(number_to_ordinal_name($semester['semester_number'])); ?></strong>
                                    <br><small><?php echo htmlspecialchars($semester['name']); ?></small>
                                </td>
                                <td><?php echo htmlspecialchars($semester['academic_year']); ?></td>
                                <td>
                                    <?php if (!empty($teachers)): ?>
                                        <!-- ✅ FIXED: Navigate to teachers page with semester param -->
                                        <a href="/FMS_P/index.php?page=admin_teachers&semester=<?php echo (int)$semester['semester_number']; ?>" 
                                           class="btn btn-sm btn-outline-primary">
                                            <?php echo count($teachers); ?> teacher(s)
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">No teachers</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($student_count > 0): ?>
                                        <a href="/FMS_P/index.php?page=admin_students&semester_id=<?php echo (int)$semester_id; ?>" 
                                           class="btn btn-sm btn-outline-primary">
                                            <?php echo $student_count; ?> student(s)
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">0 students</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-<?php echo $semester['is_active'] ? 'success' : 'secondary'; ?>">
                                        <?php echo $semester['is_active'] ? 'Active' : 'Inactive'; ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-warning" 
                                            onclick="editSemester(<?php echo htmlspecialchars(json_encode($semester, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT)); ?>)">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form method="POST" style="display: inline;" action="/FMS_P/index.php?page=admin_semesters">
                                        <input type="hidden" name="semester_id" value="<?php echo (int)$semester['id']; ?>">
                                        <button type="submit" name="delete_semester" class="btn btn-sm btn-danger"
                                                onclick="return confirm('Delete this semester? All subjects will be removed.');">
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

<!-- Modals (unchanged from your original) -->
<!-- Add Semester Modal -->
<div class="modal fade" id="addSemesterModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="/FMS_P/index.php?page=admin_semesters">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Semester</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Semester Name</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Semester Number</label>
                        <select class="form-select" name="semester_number" required>
                            <?php for ($i = 1; $i <= 8; $i++): ?>
                                <option value="<?php echo $i; ?>"><?php echo number_to_ordinal_name($i); ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Academic Year</label>
                        <input type="text" class="form-control" name="academic_year" placeholder="e.g., 2024-2025" required>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" name="is_active" id="is_active" checked>
                        <label class="form-check-label" for="is_active">Active</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="add_semester" class="btn btn-primary">Add Semester</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Semester Modal -->
<div class="modal fade" id="editSemesterModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="/FMS_P/index.php?page=admin_semesters">
                <input type="hidden" name="semester_id" id="edit_semester_id">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Semester</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Semester Name</label>
                        <input type="text" class="form-control" name="name" id="edit_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Semester Number</label>
                        <select class="form-select" name="semester_number" id="edit_semester_number" required>
                            <?php for ($i = 1; $i <= 8; $i++): ?>
                                <option value="<?php echo $i; ?>"><?php echo number_to_ordinal_name($i); ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Academic Year</label>
                        <input type="text" class="form-control" name="academic_year" id="edit_year" required>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" name="is_active" id="edit_active">
                        <label class="form-check-label" for="edit_active">Active</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="edit_semester" class="btn btn-primary">Update Semester</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Teachers Modal (unchanged) -->
<div class="modal fade" id="teachersModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Teachers in This Semester</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <ul id="teacherList" class="list-group">
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
function editSemester(semester) {
    document.getElementById('edit_semester_id').value = semester.id;
    document.getElementById('edit_name').value = semester.name;
    document.getElementById('edit_semester_number').value = semester.semester_number;
    document.getElementById('edit_year').value = semester.academic_year;
    document.getElementById('edit_active').checked = semester.is_active;
    const modal = new bootstrap.Modal(document.getElementById('editSemesterModal'));
    modal.show();
}

function showTeachers(teachers) {
    const list = document.getElementById('teacherList');
    list.innerHTML = '';
    if (teachers.length === 0) {
        list.innerHTML = '<li class="list-group-item text-muted">No teachers found.</li>';
    } else {
        teachers.forEach(teacher => {
            const li = document.createElement('li');
            li.className = 'list-group-item';
            li.textContent = teacher.full_name + ' (' + teacher.email + ')';
            list.appendChild(li);
        });
    }
    const modal = new bootstrap.Modal(document.getElementById('teachersModal'));
    modal.show();
}
</script>

<?php require __DIR__ . '/../../includes/footer.php'; ?>