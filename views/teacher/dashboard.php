<?php
require_once __DIR__ . '/../../includes/subjects.php';

$subjects = subject_get_by_teacher(getUserId());
$page_title = 'Teacher Dashboard';
?>
<?php require __DIR__ . '/../../includes/header.php'; ?>

<h2 class="mb-4"><i class="bi bi-book"></i> My Subjects</h2>

<?php if (empty($subjects)): ?>
    <div class="alert alert-info">
        <h5><i class="bi bi-info-circle"></i> No Subjects Assigned</h5>
        <p class="mb-0">You haven't been assigned to any subjects yet. Please contact the administrator.</p>
    </div>
<?php else: ?>
    <div class="row g-4">
        <?php foreach ($subjects as $subject): ?>
            <div class="col-md-6 col-lg-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="bi bi-journal-text text-primary"></i> 
                            <?php echo htmlspecialchars($subject['name']); ?>
                        </h5>
                        <p class="text-muted mb-2">
                            <small><strong><?php echo htmlspecialchars($subject['code']); ?></strong></small>
                        </p>
                        <p class="card-text"><?php echo htmlspecialchars($subject['description'] ?: 'No description'); ?></p>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="badge bg-info">
                                <?php echo htmlspecialchars($subject['semester_name']); ?>
                            </span>
                            <span class="badge bg-secondary">
                                <?php echo $subject['credits']; ?> Credits
                            </span>
                        </div>
                        <a href="/FMS_P/index.php?page=teacher_subject&id=<?php echo $subject['id']; ?>" 
                           class="btn btn-primary w-100">
                            <i class="bi bi-box-arrow-in-right"></i> Manage Subject
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/../../includes/footer.php'; ?>