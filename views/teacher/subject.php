<?php
require_once __DIR__ . '/../../includes/subjects.php';
require_once __DIR__ . '/../../includes/lectures.php';
require_once __DIR__ . '/../../includes/exercises.php';
require_once __DIR__ . '/../../includes/videos.php';
require_once __DIR__ . '/../../includes/grades.php';
require_once __DIR__ . '/../../includes/users.php';
require_once __DIR__ . '/../../includes/file_upload.php';

$subject_id = intval($_GET['id'] ?? 0);
$subject = subject_find_by_id($subject_id);

if (!$subject) {
    header('Location: /FMS_P/index.php?page=teacher_dashboard');
    exit();
}

if (!subject_is_teacher_assigned($subject_id, getUserId())) {
    header('Location: /FMS_P/index.php?page=unauthorized');
    exit();
}

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_lecture'])) {
        $file_path = '';
        if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
            $upload_result = validateAndUploadFile($_FILES['file'], $_SERVER['DOCUMENT_ROOT'] . '/FMS_P/uploads/lectures/');
            if ($upload_result['success']) {
                $file_path = $upload_result['path'];
            } else {
                $message = 'File upload error: ' . $upload_result['error'];
                $messageType = 'danger';
            }
        }
        if (!$message) {
            $data = [
                'subject_id' => $subject_id,
                'teacher_id' => getUserId(),
                'title' => trim($_POST['title']),
                'content' => trim($_POST['content'] ?? ''),
                'file_path' => $file_path
            ];
            if (lecture_create($data)) {
                $message = 'Lecture added successfully!';
                $messageType = 'success';
            } else {
                $message = 'Failed to add lecture.';
                $messageType = 'danger';
            }
        }
    } elseif (isset($_POST['add_exercise'])) {
        $file_path = '';
        if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
            $upload_result = validateAndUploadFile($_FILES['file'], $_SERVER['DOCUMENT_ROOT'] . '/FMS_P/uploads/exercises/');
            if ($upload_result['success']) {
                $file_path = $upload_result['path'];
            } else {
                $message = 'File upload error: ' . $upload_result['error'];
                $messageType = 'danger';
            }
        }
        if (!$message) {
            $data = [
                'subject_id' => $subject_id,
                'teacher_id' => getUserId(),
                'title' => trim($_POST['title']),
                'description' => trim($_POST['description'] ?? ''),
                'file_path' => $file_path,
                'due_date' => $_POST['due_date'] ?: null
            ];
            if (exercise_create($data)) {
                $message = 'Exercise added successfully!';
                $messageType = 'success';
            } else {
                $message = 'Failed to add exercise.';
                $messageType = 'danger';
            }
        }
    } elseif (isset($_POST['add_video'])) {
        $video_url = '';

        // Handle file upload
        if (!empty($_FILES['video_file']['name'])) {
            $upload_result = validateAndUploadFile(
                $_FILES['video_file'],
                $_SERVER['DOCUMENT_ROOT'] . '/FMS_P/uploads/videos/',
                ['mp4', 'mov', 'avi', 'webm']
            );
            if ($upload_result['success']) {
                $video_url = $upload_result['path'];
            } else {
                $message = 'Video upload error: ' . $upload_result['error'];
                $messageType = 'danger';
            }
        }
        // Handle URL
        elseif (!empty($_POST['video_url'])) {
            $video_url = trim($_POST['video_url']);
            if (!filter_var($video_url, FILTER_VALIDATE_URL)) {
                $message = 'Invalid video URL.';
                $messageType = 'danger';
            }
        }
        // Neither provided
        else {
            $message = 'Please upload a video file or provide a URL.';
            $messageType = 'danger';
        }

        if (!$message) {
            $data = [
                'subject_id' => $subject_id,
                'teacher_id' => getUserId(),
                'title' => trim($_POST['title']),
                'video_url' => $video_url,
                'description' => trim($_POST['description'] ?? '')
            ];
            if (video_create($data)) {
                $message = 'Video added successfully!';
                $messageType = 'success';
            } else {
                $message = 'Failed to add video.';
                $messageType = 'danger';
            }
        }
    } elseif (isset($_POST['add_grade'])) {
        $data = [
            'subject_id' => $subject_id,
            'student_id' => intval($_POST['student_id']),
            'teacher_id' => getUserId(),
            'class_activity' => $_POST['class_activity'] ?? 0,
            'midterm_exam' => $_POST['midterm_exam'] ?? 0,
            'final_exam' => $_POST['final_exam'] ?? 0,
            'remarks' => trim($_POST['remarks'] ?? '')
        ];
        if (grade_upsert($data)) {
            $message = 'Grade saved successfully!';
            $messageType = 'success';
        } else {
            $message = 'Grade already exists or invalid data.';
            $messageType = 'danger';
        }
    }
    elseif (isset($_POST['edit_grade'])) {
        $data = [
            'class_activity' => $_POST['class_activity'] ?? 0,
            'midterm_exam' => $_POST['midterm_exam'] ?? 0,
            'final_exam' => $_POST['final_exam'] ?? 0,
            'remarks' => trim($_POST['remarks'] ?? '')
        ];
        if (grade_update_by_id($_POST['grade_id'], $data)) {
            $message = 'Grade updated successfully!';
            $messageType = 'success';
        } else {
            $message = 'Failed to update grade.';
            $messageType = 'danger';
        }
    }elseif (isset($_POST['delete_grade'])) {//deleting the grade 
    $grade_id = intval($_POST['grade_id']);
    if ($grade_id > 0 && grade_delete($grade_id)) {
        $message = 'Grade deleted successfully!';
        $messageType = 'success';
    } else {
        $message = 'Failed to delete grade.';
        $messageType = 'danger';
    }
}


}

$lectures = lecture_get_by_subject($subject_id);
$exercises = exercise_get_by_subject($subject_id);
$videos = video_get_by_subject($subject_id);
$grades = grade_get_by_subject($subject_id);
// Get students in the SAME SEMESTER as this subject
$subject = subject_find_by_id($subject_id);
$semester_id = $subject['semester_id'];
$students = user_get_students_by_semester($semester_id);

$page_title = $subject['name'];
?>
<?php require __DIR__ . '/../../includes/header.php'; ?>

<div class="mb-4">
    <a href="/FMS_P/index.php?page=teacher_dashboard" class="btn btn-outline-secondary mb-3">
        <i class="bi bi-arrow-left"></i> Back to My Subjects
    </a>
    <h2><i class="bi bi-journal-text"></i> <?php echo htmlspecialchars($subject['name']); ?></h2>
    <p class="text-muted"><?php echo htmlspecialchars($subject['code']); ?> -
        <?php echo htmlspecialchars($subject['semester_name']); ?>
    </p>
</div>

<?php if ($message): ?>
    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show">
        <?php echo htmlspecialchars($message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<ul class="nav nav-tabs mb-4" role="tablist">
    <li class="nav-item">
        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#lectures">
            <i class="bi bi-file-text"></i> Lectures
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#exercises">
            <i class="bi bi-pencil-square"></i> Exercises
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#videos">
            <i class="bi bi-play-circle"></i> Videos
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#grades">
            <i class="bi bi-award"></i> Grades
        </button>
    </li>
</ul>

<div class="tab-content">
    <!-- Lectures Tab -->
    <div class="tab-pane fade show active" id="lectures">
        <div class="d-flex justify-content-between mb-3">
            <h4>Lectures</h4>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addLectureModal">
                <i class="bi bi-plus-lg"></i> Add Lecture
            </button>
        </div>
        <?php if (empty($lectures)): ?>
            <p class="text-muted">No lectures added yet.</p>
        <?php else: ?>
            <?php foreach ($lectures as $lecture): ?>
                <div class="card mb-3 content-item">
                    <div class="card-body">
                        <h5><?php echo htmlspecialchars($lecture['title']); ?></h5>
                        <p><?php echo nl2br(htmlspecialchars($lecture['content'])); ?></p>
                        <?php if ($lecture['file_path']): ?>
                            <a href="/FMS_P/<?php echo htmlspecialchars($lecture['file_path']); ?>"
                                class="btn btn-sm btn-outline-primary" target="_blank">
                                <i class="bi bi-download"></i> Download File
                            </a>
                        <?php endif; ?>
                        <small class="text-muted d-block mt-2">
                            Added on <?php echo date('M d, Y', strtotime($lecture['created_at'])); ?>
                        </small>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Exercises Tab -->
    <div class="tab-pane fade" id="exercises">
        <div class="d-flex justify-content-between mb-3">
            <h4>Exercises</h4>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addExerciseModal">
                <i class="bi bi-plus-lg"></i> Add Exercise
            </button>
        </div>
        <?php if (empty($exercises)): ?>
            <p class="text-muted">No exercises added yet.</p>
        <?php else: ?>
            <?php foreach ($exercises as $exercise): ?>
                <div class="card mb-3 content-item">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <h5><?php echo htmlspecialchars($exercise['title']); ?></h5>
                            <?php if ($exercise['due_date']): ?>
                                <span class="badge bg-warning">
                                    Due: <?php echo date('M d, Y', strtotime($exercise['due_date'])); ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        <p><?php echo nl2br(htmlspecialchars($exercise['description'])); ?></p>
                        <?php if ($exercise['file_path']): ?>
                            <a href="/FMS_P/<?php echo htmlspecialchars($exercise['file_path']); ?>"
                                class="btn btn-sm btn-outline-primary" target="_blank">
                                <i class="bi bi-download"></i> Download File
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Videos Tab -->
    <div class="tab-pane fade" id="videos">
        <div class="d-flex justify-content-between mb-3">
            <h4>Videos</h4>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addVideoModal">
                <i class="bi bi-plus-lg"></i> Add Video
            </button>
        </div>
        <?php if (empty($videos)): ?>
            <p class="text-muted">No videos added yet.</p>
        <?php else: ?>
            <?php foreach ($videos as $video): ?>
                <div class="card mb-3">
                    <div class="card-body">
                        <h5><?php echo htmlspecialchars($video['title']); ?></h5>
                        <p><?php echo nl2br(htmlspecialchars($video['description'])); ?></p>

                        <?php if (filter_var($video['video_url'], FILTER_VALIDATE_URL)): ?>
                            <!-- Embedded URL (YouTube, etc.) -->
                            <div class="ratio ratio-16x9 mb-3">
                                <iframe src="<?php echo htmlspecialchars($video['video_url']); ?>" frameborder="0"
                                    allowfullscreen></iframe>
                            </div>
                        <?php else: ?>
                            <!-- Local video file -->
                            <div class="mb-3">
                                <video controls width="100%" preload="metadata">
                                    <source src="/FMS_P/<?php echo htmlspecialchars($video['video_url']); ?>" type="video/mp4">
                                    Your browser does not support the video tag.
                                </video>
                            </div>
                            <!-- Download button with original filename -->
                            <a href="/FMS_P/<?php echo htmlspecialchars($video['video_url']); ?>" 
                               download="<?php echo basename($video['video_url']); ?>"
                               class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-download"></i> Download Video
                            </a>
                        <?php endif; ?>

                        <small class="text-muted d-block mt-2">
                            By <?php echo htmlspecialchars($video['teacher_name']); ?>
                        </small>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Grades Tab -->
 <div class="tab-pane fade" id="grades">
        <div class="d-flex justify-content-between mb-3">
            <h4>Student Grades</h4>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addGradeModal">
                <i class="bi bi-plus-lg"></i> Add Grade
            </button>
        </div>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Student</th>
                        <th>Class<br><small>(20)</small></th>
                        <th>Midterm<br><small>(20)</small></th>
                        <th>Final<br><small>(60)</small></th>
                        <th>Total<br><small>(100)</small></th>
                        <th>%</th>
                        <th>Remarks</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($grades)): ?>
                        <tr><td colspan="8" class="text-center text-muted">No grades entered yet</td></tr>
                    <?php else: ?>
                        <?php foreach ($grades as $grade): 
                            $total = $grade['class_activity'] + $grade['midterm_exam'] + $grade['final_exam'];
                            $pct = ($total / 100) * 100;
                            $cls = $pct >= 70 ? 'success' : ($pct >= 50 ? 'warning' : 'danger');
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($grade['student_name']); ?></td>
                            <td><?php echo number_format($grade['class_activity'], 1); ?></td>
                            <td><?php echo number_format($grade['midterm_exam'], 1); ?></td>
                            <td><?php echo number_format($grade['final_exam'], 1); ?></td>
                            <td><strong><?php echo number_format($total, 1); ?></strong></td>
                            <td><span class="badge bg-<?php echo $cls; ?>"><?php echo number_format($pct, 1); ?>%</span></td>
                            <td><?php echo htmlspecialchars($grade['remarks'] ?: '-'); ?></td>
                            <td>
                                 <button class="btn btn-sm btn-info me-1" 
                                    title="Edit" 
                                     onclick="editGrade(<?php echo htmlspecialchars(json_encode($grade, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT)); ?>)">
                                     <i class="bi bi-pencil"></i>
                                </button>
                                <form method="POST" style="display: inline;" 
                                     action="/FMS_P/index.php?page=teacher_subject&id=<?php echo $subject_id; ?>"
                                     onsubmit="return confirm('Delete grade for <?php echo addslashes($grade['student_name']); ?>?');">
                                         <input type="hidden" name="grade_id" value="<?php echo (int)$grade ['id']; ?>">
                                  <button type="submit" name="delete_grade" class="btn btn-sm btn-danger" title="Delete Grade">
                                  <i class="bi bi-trash"></i>
                                  </button>
                                 </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</div>


<!-- Add Lecture Modal -->
<div class="modal fade" id="addLectureModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="/FMS_P/index.php?page=teacher_subject&id=<?php echo $subject_id; ?>"
                enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title">Add Lecture</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Lecture Title</label>
                        <input type="text" class="form-control" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Content</label>
                        <textarea class="form-control" name="content" rows="4"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Upload File (optional)</label>
                        <input type="file" class="form-control" name="file">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="add_lecture" class="btn btn-primary">Add Lecture</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Exercise Modal -->
<div class="modal fade" id="addExerciseModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="/FMS_P/index.php?page=teacher_subject&id=<?php echo $subject_id; ?>"
                enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title">Add Exercise</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Exercise Title</label>
                        <input type="text" class="form-control" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Due Date (optional)</label>
                        <input type="date" class="form-control" name="due_date">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Upload File (optional)</label>
                        <input type="file" class="form-control" name="file">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="add_exercise" class="btn btn-primary">Add Exercise</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Video Modal -->
<div class="modal fade" id="addVideoModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="/FMS_P/index.php?page=teacher_subject&id=<?php echo $subject_id; ?>"
                enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title">Add Video</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Video Title</label>
                        <input type="text" class="form-control" name="title" required>
                    </div>

                    <!-- Option 1: Upload File -->
                    <div class="mb-3">
                        <label class="form-label">Upload Video File (optional)</label>
                        <input type="file" class="form-control" name="video_file"
                            accept="video/mp4,video/mov,video/avi,video/webm">
                        <small class="text-muted">Supported: MP4, MOV, AVI, WebM (max 100MB)</small>
                    </div>

                    <!-- Option 2: Embed URL -->
                    <div class="mb-3">
                        <label class="form-label">Or Embed URL (YouTube, Vimeo, etc.)</label>
                        <input type="url" class="form-control" name="video_url"
                            placeholder="https://youtube.com/embed/...   ">
                        <small class="text-muted">Leave blank if uploading a file</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="add_video" class="btn btn-primary">Add Video</button>
                </div>
            </form>
        </div>
    </div>
</div>


<!-- Add Grade Modal -->
<div class="modal fade" id="addGradeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="/FMS_P/index.php?page=teacher_subject&id=<?php echo $subject_id; ?>">
                <div class="modal-header">
                    <h5 class="modal-title">Add Grade</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Student</label>
                        <select class="form-select" name="student_id" required>
                            <option value="">Select Student</option>
                            <?php foreach ($students as $student): 
                                $has_grade = grade_find_by_subject_and_student($subject_id, $student['id']);
                            ?>
                                <option value="<?php echo (int)$student['id']; ?>" <?php echo $has_grade ? 'disabled' : ''; ?>>
                                    <?php echo htmlspecialchars($student['full_name']); ?>
                                    <?php echo $has_grade ? ' ✔️' : ''; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Class (20)</label>
                            <input type="number" class="form-control" name="class_activity" min="0" max="20" step="0.5" value="0" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Midterm (20)</label>
                            <input type="number" class="form-control" name="midterm_exam" min="0" max="20" step="0.5" value="0" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Final (60)</label>
                            <input type="number" class="form-control" name="final_exam" min="0" max="60" step="0.5" value="0" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Remarks</label>
                        <textarea class="form-control" name="remarks" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="add_grade" class="btn btn-primary">Save Grade</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Grade Modal  -->
<div class="modal fade" id="editGradeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="/FMS_P/index.php?page=teacher_subject&id=<?php echo $subject_id; ?>">
                <input type="hidden" name="grade_id" id="edit_grade_id">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Grade</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Class (20)</label>
                            <input type="number" class="form-control" name="class_activity" id="edit_class_activity" min="0" max="20" step="0.5" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Midterm (20)</label>
                            <input type="number" class="form-control" name="midterm_exam" id="edit_midterm_exam" min="0" max="20" step="0.5" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Final (60)</label>
                            <input type="number" class="form-control" name="final_exam" id="edit_final_exam" min="0" max="60" step="0.5" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Remarks</label>
                        <textarea class="form-control" name="remarks" id="edit_remarks" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="edit_grade" class="btn btn-primary">Update Grade</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
function editGrade(grade) {
    document.getElementById('edit_grade_id').value = grade.id;
    document.getElementById('edit_class_activity').value = grade.class_activity || 0;
    document.getElementById('edit_midterm_exam').value = grade.midterm_exam || 0;
    document.getElementById('edit_final_exam').value = grade.final_exam || 0;
    document.getElementById('edit_remarks').value = grade.remarks || '';
    const modal = new bootstrap.Modal(document.getElementById('editGradeModal'));
    modal.show();
}

// Auto-focus first input in modals
document.addEventListener('shown.bs.modal', function(e) {
    const modal = e.target;
    const input = modal.querySelector('input, textarea, select');
    if (input) input.focus();
});
</script>

<?php require __DIR__ . '/../../includes/footer.php'; ?>"   32_view/shared/404.php"<?php $page_title = 'Page Not Found'; ?>
<?php require __DIR__ . '/../../includes/header.php'; ?>

<div class="text-center py-5">
    <i class="bi bi-exclamation-triangle text-warning" style="font-size: 5rem;"></i>
    <h1 class="mt-4">404 - Page Not Found</h1>
    <p class="lead">The page you are looking for doesn't exist.</p>
    <a href="/FMS_P/index.php?page=<?php echo isLoggedIn() ? getUserRole() . '_dashboard' : 'login'; ?>" class="btn btn-primary">
        <i class="bi bi-house"></i> Go to Home
    </a>
</div>

<?php require __DIR__ . '/../../includes/footer.php'; ?>