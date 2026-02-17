<?php
require_once __DIR__ . '/../../includes/subjects.php';
require_once __DIR__ . '/../../includes/semesters.php';
require_once __DIR__ . '/../../includes/users.php';

// Helper: Convert number to ordinal name
function number_to_ordinal_name($num) {
    $names = [
        1 => 'First', 2 => 'Second', 3 => 'Third', 4 => 'Fourth',
        5 => 'Fifth', 6 => 'Sixth', 7 => 'Seventh', 8 => 'Eighth'
    ];
    return $names[$num] ?? "Semester $num";
}

$message = '';
$messageType = '';

// Handle POST (add/edit/delete/assign)
// Handle POST (add/edit/delete/assign)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_subject'])) {
        $data = [
            'semester_id' => $_POST['semester_id'],
            'name' => trim($_POST['name']),
            'code' => trim($_POST['code']),
            'description' => trim($_POST['description'] ?? ''),
            'credits' => intval($_POST['credits'])
        ];
        if (subject_create($data)) {
            header("Location: /FMS_P/index.php?page=admin_subjects");
            exit();
        } else {
            $message = 'Failed to create subject.';
            $messageType = 'danger';
        }
    } 
    elseif (isset($_POST['edit_subject'])) {
        $data = [
            'semester_id' => $_POST['semester_id'],
            'name' => trim($_POST['name']),
            'code' => trim($_POST['code']),
            'description' => trim($_POST['description'] ?? ''),
            'credits' => intval($_POST['credits'])
        ];
        if (subject_update($_POST['subject_id'], $data)) {  // ✅ CORRECT: subject_update()
            header("Location: /FMS_P/index.php?page=admin_subjects");
            exit();
        } else {
            $message = 'Failed to update subject.';
            $messageType = 'danger';
        }
    } 
    elseif (isset($_POST['delete_subject'])) {
        if (subject_delete($_POST['subject_id'])) {
            header("Location: /FMS_P/index.php?page=admin_subjects");
            exit();
        } else {
            $message = 'Failed to delete subject.';
            $messageType = 'danger';
        }
    } 
    elseif (isset($_POST['assign_teacher'])) {
    $subject_id = intval($_POST['subject_id']);
    $teacher_id = intval($_POST['teacher_id']);
    
    if ($subject_id <= 0 || $teacher_id <= 0) {
        $message = 'Invalid subject or teacher ID.';
        $messageType = 'danger';
    } elseif (subject_assign_teacher($subject_id, $teacher_id)) {
        $message = 'Teacher assigned successfully!';
        $messageType = 'success';
    } else {
        $message = 'Failed to assign teacher. They may already teach in this semester.';
        $messageType = 'danger';
    }
    // Do NOT redirect — show message on page
}


}
// Fetch data
$semesters = semester_get_all();
$all_subjects = subject_get_all_with_teachers();
$teachers = user_get_all('teacher', 'approved');

$page_title = 'Manage Subjects';
?>
<?php require __DIR__ . '/../../includes/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-book"></i> Manage Subjects</h2>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSubjectModal">
        <i class="bi bi-plus-lg"></i> Add Subject
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
    <?php for ($i = 1; $i <= 8; $i++): ?>
        <li class="nav-item">
            <button class="nav-link <?php echo ($i === 1) ? 'active' : ''; ?>" 
                    data-bs-toggle="tab" 
                    data-bs-target="#sem-<?php echo $i; ?>">
                <?php echo number_to_ordinal_name($i); ?> Semester
            </button>
        </li>
    <?php endfor; ?>
    
</ul>

<div class="tab-content">
    <!-- Semester Tabs (1 to 8) -->
    <?php for ($i = 1; $i <= 8; $i++): ?>
        <div class="tab-pane fade <?php echo ($i === 1) ? 'show active' : ''; ?>" id="sem-<?php echo $i; ?>">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5><?php echo number_to_ordinal_name($i); ?> Semester Subjects</h5>
                    <?php
                    $semester_id = null;
                    foreach ($semesters as $s) {
                        if ($s['semester_number'] == $i) {
                            $semester_id = $s['id'];
                            break;
                        }
                    }
                    if ($semester_id):
                        $subjects = subject_get_by_semester_with_teachers($semester_id);
                        if (empty($subjects)):
                            echo '<p class="text-muted">No subjects created for this semester.</p>';
                        else:
                            ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Code</th>
                                            <th>Name</th>
                                            <th>Teacher</th>
                                            <th>Credits</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($subjects as $subject): ?>
                                            <tr>
                                                <td><strong><?php echo htmlspecialchars($subject['code']); ?></strong></td>
                                                <td><?php echo htmlspecialchars($subject['name']); ?></td>
                                                <td>
                                                    <?php if (!empty($subject['teacher_name'])): ?>
                                                        <?php echo htmlspecialchars($subject['teacher_name']); ?>
                                                    <?php else: ?>
                                                        <span class="text-muted">Not assigned</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo $subject['credits']; ?></td>
                                                <td>
                                                    <button class="btn btn-sm btn-info" 
                                                            onclick="assignTeacher(<?php echo $subject['id']; ?>)">
                                                        <i class="bi bi-person-plus"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-warning" 
                                                            onclick="editSubject(<?php echo htmlspecialchars(json_encode($subject, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT)); ?>)">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <form method="POST" style="display: inline;" action="/FMS_P/index.php?page=admin_subjects">
                                                        <input type="hidden" name="subject_id" value="<?php echo $subject['id']; ?>">
                                                        <button type="submit" name="delete_subject" class="btn btn-sm btn-danger"
                                                                onclick="return confirm('Delete this subject?');">
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
                    <?php else: ?>
                        <p class="text-muted">Semester <?php echo $i; ?> not created yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endfor; ?>

    

<!-- Add Subject Modal -->
<div class="modal fade" id="addSubjectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="/FMS_P/index.php?page=admin_subjects">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Subject</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Semester</label>
                        <select class="form-select" name="semester_id" required>
                            <option value="">Select Semester</option>
                            <?php foreach ($semesters as $sem): ?>
                                <option value="<?php echo (int)$sem['id']; ?>">
                                    <?php echo htmlspecialchars($sem['name']); ?> (<?php echo htmlspecialchars($sem['semester_number']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Subject Code</label>
                        <input type="text" class="form-control" name="code" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Subject Name</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Credits</label>
                        <input type="number" class="form-control" name="credits" value="3" min="1" max="6" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="add_subject" class="btn btn-primary">Add Subject</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Subject Modal -->
<div class="modal fade" id="editSubjectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="/FMS_P/index.php?page=admin_subjects">
                <input type="hidden" name="subject_id" id="edit_subject_id">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Subject</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Semester</label>
                        <select class="form-select" name="semester_id" id="edit_semester_id" required>
                            <?php foreach ($semesters as $sem): ?>
                                <option value="<?php echo (int)$sem['id']; ?>">
                                     <?php echo htmlspecialchars($sem['name']); ?> 
                                     (<?php echo number_to_ordinal_name($sem['semester_number']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Subject Code</label>
                        <input type="text" class="form-control" name="code" id="edit_code" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Subject Name</label>
                        <input type="text" class="form-control" name="name" id="edit_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" id="edit_description" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Credits</label>
                        <input type="number" class="form-control" name="credits" id="edit_credits" min="1" max="6" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="edit_subject" class="btn btn-primary">Update Subject</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Assign Teacher Modal -->
<div class="modal fade" id="assignTeacherModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="/FMS_P/index.php?page=admin_subjects">
                <input type="hidden" name="subject_id" id="assign_subject_id">
                <div class="modal-header">
                    <h5 class="modal-title">Assign Teacher</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Select Teacher</label>
                        <select class="form-select" name="teacher_id" required>
                            <option value="">Choose a teacher...</option>
                            <?php foreach ($teachers as $teacher): ?>
                                <option value="<?php echo (int)$teacher['id']; ?>">
                                    <?php echo htmlspecialchars($teacher['full_name']); ?> 
                                    (<?php echo htmlspecialchars($teacher['email']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="assign_teacher" class="btn btn-primary">Assign</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editSubject(subject) {
    document.getElementById('edit_subject_id').value = subject.id;
    document.getElementById('edit_semester_id').value = subject.semester_id;
    document.getElementById('edit_code').value = subject.code;
    document.getElementById('edit_name').value = subject.name;
    document.getElementById('edit_description').value = subject.description || '';
    document.getElementById('edit_credits').value = subject.credits;
    const modal = new bootstrap.Modal(document.getElementById('editSubjectModal'));
    modal.show();
}

function assignTeacher(subjectId) {
    const id = parseInt(subjectId);
    if (isNaN(id) || id <= 0) {
        alert('Invalid subject ID');
        return;
    }
    document.getElementById('assign_subject_id').value = id;
    const modal = new bootstrap.Modal(document.getElementById('assignTeacherModal'));
    modal.show();
}
</script>

<?php require __DIR__ . '/../../includes/footer.php'; ?>

