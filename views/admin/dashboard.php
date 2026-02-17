<?php
require_once __DIR__ . '/../../includes/users.php';
require_once __DIR__ . '/../../includes/semesters.php';
require_once __DIR__ . '/../../includes/subjects.php';
require_once __DIR__ . '/../../includes/monographs.php'; // ✅ Add this

$teachers = user_get_all('teacher', 'approved');
$students = user_get_all('student', 'approved');
$semesters = semester_get_all();
$subjects = subject_get_all();
$pending = user_get_all(null, 'pending');

// ✅ New: Monograph stats
$monograph_years = monograph_get_years();
$monographs = monograph_get_all();
$monograph_count = count($monographs);

$page_title = 'Admin Dashboard';
?>
<?php require __DIR__ . '/../../includes/header.php'; ?>

<h2 class="mb-4"><i class="bi bi-speedometer2"></i> Admin Dashboard</h2>

<div class="row g-4">
    <div class="col-md-3">
        <div class="card stat-card primary shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Total Teachers</h6>
                        <h2 class="mb-0"><?php echo count($teachers); ?></h2>
                    </div>
                    <i class="bi bi-person-badge text-primary" style="font-size: 3rem; opacity: 0.3;"></i>
                </div>
                <a href="/FMS_P/index.php?page=admin_teachers" class="btn btn-sm btn-outline-primary mt-3 w-100">
                    View Teachers
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card stat-card success shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Total Students</h6>
                        <h2 class="mb-0"><?php echo count($students); ?></h2>
                    </div>
                    <i class="bi bi-people text-success" style="font-size: 3rem; opacity: 0.3;"></i>
                </div>
                <a href="/FMS_P/index.php?page=admin_students" class="btn btn-sm btn-outline-success mt-3 w-100">
                    View Students
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card stat-card info shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Total Subjects</h6>
                        <h2 class="mb-0"><?php echo count($subjects); ?></h2>
                    </div>
                    <i class="bi bi-book text-info" style="font-size: 3rem; opacity: 0.3;"></i>
                </div>
                <a href="/FMS_P/index.php?page=admin_subjects" class="btn btn-sm btn-outline-info mt-3 w-100">
                    View Subjects
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card stat-card warning shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Pending Requests</h6>
                        <h2 class="mb-0"><?php echo count($pending); ?></h2>
                    </div>
                    <i class="bi bi-clock-history text-warning" style="font-size: 3rem; opacity: 0.3;"></i>
                </div>
                <a href="/FMS_P/index.php?page=admin_requests" class="btn btn-sm btn-outline-warning mt-3 w-100">
                    Review Requests
                </a>
            </div>
        </div>
    </div>

    <!-- ✅ NEW: Monographs Card -->
    <div class="col-md-3">
        <div class="card stat-card secondary shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Total Monographs</h6>
                        <h2 class="mb-0"><?php echo $monograph_count; ?></h2>
                    </div>
                    <i class="bi bi-file-text text-secondary" style="font-size: 3rem; opacity: 0.3;"></i>
                </div>
                <a href="/FMS_P/index.php?page=admin_monographs" class="btn btn-sm btn-outline-secondary mt-3 w-100">
                    View Monographs
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-calendar3"></i> Semesters</h5>
            </div>
            <div class="card-body">
                <?php if (empty($semesters)): ?>
                    <p class="text-muted">No semesters created yet.</p>
                <?php else: ?>
                    <div class="list-group">
                        <?php foreach (array_slice($semesters, 0, 5) as $semester): ?>
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1"><?php echo htmlspecialchars($semester['name']); ?></h6>
                                        <small class="text-muted">
                                            <?php echo htmlspecialchars($semester['semester_number']); ?> Semester - 
                                            <?php echo htmlspecialchars($semester['academic_year']); ?>
                                        </small>
                                    </div>
                                    <span class="badge bg-<?php echo $semester['is_active'] ? 'success' : 'secondary'; ?>">
                                        <?php echo $semester['is_active'] ? 'Active' : 'Inactive'; ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                <a href="/FMS_P/index.php?page=admin_semesters" class="btn btn-primary mt-3">
                    Manage Semesters
                </a>
            </div>
        </div>
    </div>

    <!-- ✅ NEW: Monographs Preview Section -->
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-file-earmark-text"></i> Recent Monographs</h5>
                <a href="/FMS_P/index.php?page=admin_monographs" class="btn btn-sm btn-outline-primary">
                    View All
                </a>
            </div>
            <div class="card-body">
                <?php if (empty($monographs)): ?>
                    <p class="text-muted mb-0">No monographs added yet.</p>
                <?php else: ?>
                    <div class="list-group">
                        <?php foreach (array_slice($monographs, 0, 5) as $monograph): ?>
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="mb-1"><?php echo htmlspecialchars($monograph['title']); ?></h6>
                                        <small class="text-muted">
                                            By <?php echo htmlspecialchars($monograph['student_full_name']); ?> 
                                            (<?php echo $monograph['publish_year']; ?>)
                                        </small>
                                    </div>
                                    <a href="/FMS_P/<?php echo htmlspecialchars($monograph['file_path']); ?>" 
                                       class="btn btn-sm btn-outline-primary" target="_blank">
                                        <i class="bi bi-download"></i>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../../includes/footer.php'; ?>