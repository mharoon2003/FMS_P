<?php
require_once __DIR__ . '/../../includes/subjects.php';
require_once __DIR__ . '/../../includes/lectures.php';
require_once __DIR__ . '/../../includes/exercises.php';
require_once __DIR__ . '/../../includes/videos.php';
require_once __DIR__ . '/../../includes/grades.php';
require_once __DIR__ . '/../../includes/semesters.php';
require_once __DIR__ . '/../../includes/users.php';

$subject_id = intval($_GET['id'] ?? 0);
if ($subject_id <= 0) {
    header('Location: /FMS_P/index.php?page=student_dashboard');
    exit();
}

$subject = subject_find_by_id($subject_id);
if (!$subject) {
    header('Location: /FMS_P/index.php?page=student_dashboard');
    exit();
}

// Security: Ensure student is in the same semester as the subject
$user = user_find_by_id(getUserId());
if (!$user || $user['semester_id'] != $subject['semester_id']) {
    header('Location: /FMS_P/index.php?page=unauthorized');
    exit();
}

// Fetch content
$lectures = lecture_get_by_subject($subject_id);
$exercises = exercise_get_by_subject($subject_id);
$videos = video_get_by_subject($subject_id);
$my_grade = grade_find_by_subject_and_student($subject_id, getUserId());

$page_title = htmlspecialchars($subject['name']);
?>
<?php require __DIR__ . '/../../includes/header.php'; ?>

<div class="mb-4">
    <a href="/FMS_P/index.php?page=student_dashboard" class="btn btn-outline-secondary mb-3">
        <i class="bi bi-arrow-left"></i> Back to Dashboard
    </a>
    <h2><i class="bi bi-journal-text"></i> <?php echo htmlspecialchars($subject['name']); ?></h2>
    <p class="text-muted">
        <?php echo htmlspecialchars($subject['code']); ?> • 
        <?php echo htmlspecialchars($subject['semester_name']); ?> • 
        <?php echo $subject['credits']; ?> credits
    </p>

    <?php if ($my_grade): ?>
        <?php
        $percentage = ($my_grade['grade'] / $my_grade['max_grade']) * 100;
        $badge_class = $percentage >= 70 ? 'success' : ($percentage >= 50 ? 'warning' : 'danger');
        ?>
        <div class="alert alert-<?php echo $badge_class; ?> d-flex align-items-center">
            <i class="bi bi-award fs-4 me-2"></i>
            <div>
                <strong>Your Grade:</strong> <?php echo $my_grade['grade']; ?> / <?php echo $my_grade['max_grade']; ?> 
                (<strong><?php echo number_format($percentage, 1); ?>%</strong>)
                <?php if ($my_grade['remarks']): ?>
                    <br><small class="text-dark"><em><?php echo htmlspecialchars($my_grade['remarks']); ?></em></small>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<ul class="nav nav-tabs mb-4" role="tablist">
    <li class="nav-item">
        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#lectures">
            <i class="bi bi-file-text"></i> Lectures (<?php echo count($lectures); ?>)
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#exercises">
            <i class="bi bi-pencil-square"></i> Exercises (<?php echo count($exercises); ?>)
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#videos">
            <i class="bi bi-play-circle"></i> Videos (<?php echo count($videos); ?>)
        </button>
    </li>
</ul>

<div class="tab-content">
    <!-- Lectures Tab -->
    <div class="tab-pane fade show active" id="lectures">
        <?php if (empty($lectures)): ?>
            <div class="text-center py-4">
                <i class="bi bi-file-earmark-text text-muted" style="font-size: 3rem;"></i>
                <p class="mt-2 text-muted">No lectures uploaded yet.</p>
            </div>
        <?php else: ?>
            <div class="row g-3">
                <?php foreach ($lectures as $lecture): ?>
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <h5><?php echo htmlspecialchars($lecture['title']); ?></h5>
                                <?php if (!empty($lecture['content'])): ?>
                                    <p class="mb-3"><?php echo nl2br(htmlspecialchars($lecture['content'])); ?></p>
                                <?php endif; ?>
                                
                                <?php if ($lecture['file_path']): ?>
                                    <a href="/FMS_P/<?php echo htmlspecialchars($lecture['file_path']); ?>" 
                                       class="btn btn-outline-primary btn-sm" target="_blank">
                                        <i class="bi bi-download"></i> Download Lecture
                                    </a>
                                <?php endif; ?>
                                
                                <small class="text-muted d-block mt-2">
                                    <i class="bi bi-person-circle"></i> 
                                    <?php echo htmlspecialchars($lecture['teacher_name']); ?> • 
                                    <?php echo date('M d, Y', strtotime($lecture['created_at'])); ?>
                                </small>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Exercises Tab -->
    <div class="tab-pane fade" id="exercises">
        <?php if (empty($exercises)): ?>
            <div class="text-center py-4">
                <i class="bi bi-journal-text text-muted" style="font-size: 3rem;"></i>
                <p class="mt-2 text-muted">No exercises assigned yet.</p>
            </div>
        <?php else: ?>
            <div class="row g-3">
                <?php foreach ($exercises as $exercise): ?>
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <h5><?php echo htmlspecialchars($exercise['title']); ?></h5>
                                    <?php if ($exercise['due_date']): ?>
                                        <span class="badge bg-<?php echo strtotime($exercise['due_date']) < time() ? 'danger' : 'warning'; ?>">
                                            Due: <?php echo date('M d, Y', strtotime($exercise['due_date'])); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                
                                <?php if (!empty($exercise['description'])): ?>
                                    <p class="mb-3"><?php echo nl2br(htmlspecialchars($exercise['description'])); ?></p>
                                <?php endif; ?>
                                
                                <?php if ($exercise['file_path']): ?>
                                    <a href="/FMS_P/<?php echo htmlspecialchars($exercise['file_path']); ?>" 
                                       class="btn btn-outline-primary btn-sm" target="_blank">
                                        <i class="bi bi-download"></i> Download Exercise
                                    </a>
                                <?php endif; ?>
                                
                                <small class="text-muted d-block mt-2">
                                    <i class="bi bi-person-circle"></i> 
                                    <?php echo htmlspecialchars($exercise['teacher_name']); ?>
                                </small>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Videos Tab -->
    <div class="tab-pane fade" id="videos">
        <?php if (empty($videos)): ?>
            <div class="text-center py-4">
                <i class="bi bi-camera-video text-muted" style="font-size: 3rem;"></i>
                <p class="mt-2 text-muted">No videos available yet.</p>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($videos as $video): ?>
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <h5><?php echo htmlspecialchars($video['title']); ?></h5>
                                <?php if (!empty($video['description'])): ?>
                                    <p class="mb-3"><?php echo nl2br(htmlspecialchars($video['description'])); ?></p>
                                <?php endif; ?>
                                
                                <div class="mb-3">
                                    <?php if (filter_var($video['video_url'], FILTER_VALIDATE_URL)): ?>
                                        <div class="ratio ratio-16x9">
                                            <iframe 
                                                src="<?php echo htmlspecialchars($video['video_url']); ?>" 
                                                frameborder="0" 
                                                allowfullscreen
                                                title="<?php echo htmlspecialchars($video['title']); ?>">
                                            </iframe>
                                        </div>
                                    <?php else: ?>
                                        <div class="ratio ratio-16x9">
                                            <video controls class="w-100" title="<?php echo htmlspecialchars($video['title']); ?>">
                                                <source src="/FMS_P/<?php echo htmlspecialchars($video['video_url']); ?>" type="video/mp4">
                                                Your browser does not support the video tag.
                                            </video>
                                        </div>
                                        <a href="/FMS_P/<?php echo htmlspecialchars($video['video_url']); ?>" 
                                           download="<?php echo basename($video['video_url']); ?>"
                                           class="btn btn-outline-primary btn-sm mt-2">
                                            <i class="bi bi-download"></i> Download Video
                                        </a>
                                    <?php endif; ?>
                                </div>
                                
                                <small class="text-muted">
                                    <i class="bi bi-person-circle"></i> 
                                    <?php echo htmlspecialchars($video['teacher_name']); ?>
                                </small>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require __DIR__ . '/../../includes/footer.php'; ?>