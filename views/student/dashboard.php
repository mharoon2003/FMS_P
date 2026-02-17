<?php
require_once __DIR__ . '/../../includes/subjects.php';
require_once __DIR__ . '/../../includes/grades.php';
require_once __DIR__ . '/../../includes/users.php';
require_once __DIR__ . '/../../includes/semesters.php';

// Ensure logged-in student
if (!getUserId() || getUserRole() !== 'student') {
    header('Location: /FMS_P/index.php?page=login');
    exit();
}

$user = user_find_by_id(getUserId());
if (!$user || empty($user['semester_id'])) {
    $message = 'You are not assigned to any semester. Please contact the administrator.';
    $show_alert = true;
} else {
    $semester_id = $user['semester_id'];
    $subjects = subject_get_by_semester($semester_id);
    $grades = grade_get_by_student(getUserId()); // Fetch all grades for this student

    // Map grades by subject_id
    $grades_map = [];
    foreach ($grades as $grade) {
        $grades_map[$grade['subject_id']] = $grade;
    }
}

$page_title = 'Student Dashboard';
?>
<?php require __DIR__ . '/../../includes/header.php'; ?>

<h2 class="mb-4"><i class="bi bi-book"></i> My Subjects & Grades</h2>

<?php if (!empty($show_alert)): ?>
    <div class="alert alert-warning">
        <h5><i class="bi bi-exclamation-triangle"></i> Semester Not Assigned</h5>
        <p class="mb-0"><?php echo htmlspecialchars($message); ?></p>
    </div>
<?php elseif (empty($subjects)): ?>
    <div class="alert alert-info">
        <h5><i class="bi bi-info-circle"></i> No Subjects Available</h5>
        <p class="mb-0">No subjects have been created for your semester yet.</p>
    </div>
<?php else: ?>
    <!-- Tabs: Subjects & Grades -->
    <ul class="nav nav-tabs mb-4" role="tablist">
        <li class="nav-item">
            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#subjects">
                <i class="bi bi-journal-text"></i> Subjects
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#grades">
                <i class="bi bi-award"></i> Grades (<span id="grade-count"><?php echo count($grades); ?></span>)
            </button>
        </li>
    </ul>

    <div class="tab-content">
        <!-- Subjects Tab -->
        <div class="tab-pane fade show active" id="subjects">
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h4 class="mb-0">
                        <i class="bi bi-calendar3"></i> 
                        <?php 
                        $sem = semester_find_by_id($semester_id);
                        echo htmlspecialchars($sem ? $sem['name'] : 'Current Semester');
                        ?>
                    </h4>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <?php foreach ($subjects as $subject): 
                            $grade_data = $grades_map[$subject['id']] ?? null;
                            $total = $grade_data ? 
                                ($grade_data['class_activity'] + $grade_data['midterm_exam'] + $grade_data['final_exam']) : 0;
                            $pct = $grade_data ? ($total / 100) * 100 : 0;
                            $badge_class = $pct >= 70 ? 'success' : ($pct >= 50 ? 'warning' : 'danger');
                        ?>
                            <div class="col-md-6 col-lg-4">
                                <div class="card h-100">
                                    <div class="card-body d-flex flex-column">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div>
                                                <h5 class="mb-1"><?php echo htmlspecialchars($subject['name']); ?></h5>
                                                <small class="text-muted"><?php echo htmlspecialchars($subject['code']); ?></small>
                                            </div>
                                            <?php if ($grade_data): ?>
                                                <h3 class="mb-0">
                                                    <span class="badge bg-<?php echo $badge_class; ?>">
                                                        <?php echo number_format($pct, 1); ?>%
                                                    </span>
                                                </h3>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">No Grade</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="mt-auto">
                                            <a href="/FMS_P/index.php?page=student_subject&id=<?php echo (int)$subject['id']; ?>" 
                                               class="btn btn-sm btn-primary w-100">
                                                <i class="bi bi-eye"></i> View Content
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- ✅ NEW: Grades Tab -->
        <div class="tab-pane fade" id="grades">
            <?php if (empty($grades)): ?>
                <div class="text-center py-5">
                    <i class="bi bi-award text-muted" style="font-size: 4rem;"></i>
                    <h4 class="mt-3">No Grades Yet</h4>
                    <p class="text-muted">Your teachers haven't posted any grades yet.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Subject</th>
                                <th>Class<br><small>(20)</small></th>
                                <th>Midterm<br><small>(20)</small></th>
                                <th>Final<br><small>(60)</small></th>
                                <th>Total<br><small>(100)</small></th>
                                <th>%</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($grades as $grade):
                                $total = $grade['class_activity'] + $grade['midterm_exam'] + $grade['final_exam'];
                                $pct = ($total / 100) * 100;
                                $status = $pct >= 55 ? 'Pass' : 'Fail';
                                $status_class = $pct >= 55 ? 'success' : 'danger';
                            ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($grade['subject_code']); ?></strong><br>
                                        <small class="text-muted"><?php echo htmlspecialchars($grade['subject_name']); ?></small>
                                    </td>
                                    <td><?php echo number_format($grade['class_activity'], 1); ?></td>
                                    <td><?php echo number_format($grade['midterm_exam'], 1); ?></td>
                                    <td><?php echo number_format($grade['final_exam'], 1); ?></td>
                                    <td><strong><?php echo number_format($total, 1); ?></strong></td>
                                    <td>
                                        <span class="badge bg-<?php echo $pct >= 70 ? 'success' : ($pct >= 50 ? 'warning' : 'danger'); ?>">
                                            <?php echo number_format($pct, 1); ?>%
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php echo $status_class; ?>"><?php echo $status; ?></span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/../../includes/footer.php'; ?>