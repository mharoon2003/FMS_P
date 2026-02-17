<?php
require_once __DIR__ . '/../../includes/semesters.php';
require_once __DIR__ . '/../../includes/users.php';

$message = '';
$messageType = '';

// Handle enrollment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['enroll_student'])) {
    $student_id = intval($_POST['student_id']);
    $semester_id = intval($_POST['semester_id']);
    
    if (user_enroll_in_semester($student_id, $semester_id)) {
        $message = 'Student enrolled in semester successfully!';
        $messageType = 'success';
    } else {
        $message = 'Failed to enroll student.';
        $messageType = 'danger';
    }
}

// Fetch data
$semesters = semester_get_all();
$students = user_get_all('student', 'approved');

// Get current enrollments: student → semester
$enrolled_students = [];
foreach ($students as $student) {
    if (!empty($student['semester_id'])) {
        $semester = semester_find_by_id($student['semester_id']);
        $enrolled_students[] = [
            'student' => $student,
            'semester' => $semester
        ];
    }
}

$page_title = 'Manage Student Enrollments';
?>
<?php require __DIR__ . '/../../includes/header.php'; ?>

<h2 class="mb-4"><i class="bi bi-person-check"></i> Manage Student Enrollments</h2>

<?php if ($message): ?>
    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show">
        <?php echo htmlspecialchars($message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Enroll Student in Semester -->
<div class="card shadow-sm mb-4">
    <div class="card-header">
        <h5 class="mb-0">Enroll Student in Semester</h5>
    </div>
    <div class="card-body">
        <form method="POST" class="row g-3">
            <div class="col-md-5">
                <label class="form-label">Select Student</label>
                <select class="form-select" name="student_id" required>
                    <option value="">Choose student...</option>
                    <?php foreach ($students as $student): ?>
                        <!-- Only show students NOT already enrolled -->
                        <?php if (empty($student['semester_id'])): ?>
                            <option value="<?php echo (int)$student['id']; ?>">
                                <?php echo htmlspecialchars($student['full_name']); ?> (<?php echo htmlspecialchars($student['email']); ?>)
                            </option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-5">
                <label class="form-label">Select Semester</label>
                <select class="form-select" name="semester_id" required>
                    <option value="">Choose semester...</option>
                    <?php foreach ($semesters as $semester): ?>
                        <option value="<?php echo (int)$semester['id']; ?>">
                            <?php echo htmlspecialchars($semester['name']); ?> 
                            (<?php echo htmlspecialchars($semester['academic_year']); ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">&nbsp;</label>
                <button type="submit" name="enroll_student" class="btn btn-primary w-100">
                    <i class="bi bi-plus-lg"></i> Enroll
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Current Enrollments: Student → Semester -->
<div class="card shadow-sm">
    <div class="card-header">
        <h5 class="mb-0">Current Student Enrollments</h5>
    </div>
    <div class="card-body">
        <?php if (empty($enrolled_students)): ?>
            <p class="text-muted">No students enrolled in any semester yet.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Student Name</th>
                            <th>Email</th>
                            <th>Semester</th>
                            <th>Academic Year</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($enrolled_students as $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['student']['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($item['student']['email']); ?></td>
                                <td><?php echo htmlspecialchars($item['semester']['name'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($item['semester']['academic_year'] ?? ''); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require __DIR__ . '/../../includes/footer.php'; ?>