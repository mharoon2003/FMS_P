<?php
require_once __DIR__ . '/../../includes/monographs.php';

$message = '';
$messageType = '';

// Handle POST (add/edit/delete) - only for teachers
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_monograph'])) {
        // Validate and upload file
        $file_path = '';
        if (isset($_FILES['monograph_file']) && $_FILES['monograph_file']['error'] === UPLOAD_ERR_OK) {
            $upload_result = validateAndUploadFile(
                $_FILES['monograph_file'],
                $_SERVER['DOCUMENT_ROOT'] . '/FMS_P/uploads/monographs/',
                ['pdf', 'doc', 'docx']
            );
            if ($upload_result['success']) {
                $file_path = $upload_result['path'];
            } else {
                $message = 'File upload error: ' . $upload_result['error'];
                $messageType = 'danger';
            }
        } else {
            $message = 'Please select a monograph file.';
            $messageType = 'danger';
        }

        if (empty($message)) {
            $data = [
                'title' => trim($_POST['title']),
                'file_path' => $file_path,
                'student_full_name' => trim($_POST['student_full_name']),
                'student_email' => trim($_POST['student_email'] ?? ''),
                'student_phone' => trim($_POST['student_phone'] ?? ''),
                'instructor_full_name' => getFullName(), // Teacher adds it
                'instructor_email' => getUserName(), // Teacher's email
                'instructor_phone' => trim($_POST['instructor_phone'] ?? ''),
                'publish_year' => intval($_POST['publish_year'])
            ];
            if (monograph_create($data)) {
                $message = 'Monograph added successfully!';
                $messageType = 'success';
            } else {
                $message = 'Failed to save monograph.';
                $messageType = 'danger';
            }
        }
    }
    // Add other POST handlers for edit/delete...
}

// Fetch monographs (all for teachers)
$monographs = [];
$keyword = '';

if (!empty($_GET['search'])) {
    $keyword = trim($_GET['search']);
    $monographs = monograph_search($keyword);
} else {
    $monographs = monograph_get_all();
}

$page_title = 'Monographs';
?>
<?php require __DIR__ . '/../../includes/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-file-text"></i> Monographs</h2>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addMonographModal">
        <i class="bi bi-plus-lg"></i> Add Monograph
    </button>
</div>

<?php if ($message): ?>
    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show">
        <?php echo htmlspecialchars($message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Search Bar -->
<form method="GET" class="mb-4">
    <input type="hidden" name="page" value="teacher_monographs">
    <div class="input-group">
        <input type="text" class="form-control" name="search" placeholder="Search monographs by title..."
            value="<?php echo htmlspecialchars($keyword); ?>">
        <button class="btn btn-outline-primary" type="submit">
            <i class="bi bi-search"></i> Search
        </button>
        <?php if (!empty($keyword)): ?>
            <a href="/FMS_P/index.php?page=teacher_monographs" class="btn btn-outline-secondary">
                <i class="bi bi-x-lg"></i> Clear
            </a>
        <?php endif; ?>
    </div>
</form>

<?php if (empty($monographs)): ?>
    <div class="alert alert-info">
        <h5><i class="bi bi-info-circle"></i> No Monographs Found</h5>
        <p class="mb-0">
            <?php echo empty($keyword) ? 'No monographs have been added yet.' : "No monographs match your search for '$keyword'."; ?>
        </p>
    </div>
<?php else: ?>
    <div class="row g-4">
        <?php foreach ($monographs as $monograph): ?>
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($monograph['title']); ?></h5>
                        <p class="card-text text-muted">
                            <small>
                                <i class="bi bi-person"></i> <?php echo htmlspecialchars($monograph['student_name']); ?><br>
                                <i class="bi bi-person-badge"></i>
                                <?php echo htmlspecialchars($monograph['instructor_name']); ?><br>
                                <i class="bi bi-calendar"></i> <?php echo $monograph['publish_year']; ?>
                            </small>
                        </p>
                    </div>
                    <div class="card-footer bg-transparent border-top-0">
                        <a href="/FMS_P/<?php echo htmlspecialchars($monograph['file_path']); ?>"
                            class="btn btn-sm btn-primary w-100" target="_blank">
                            <i class="bi bi-download"></i> Download
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- Add Monograph Modal -->
<div class="modal fade" id="addMonographModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="/FMS_P/index.php?page=teacher_monographs" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Monograph</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Title *</label>
                        <input type="text" class="form-control" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Upload File *</label>
                        <input type="file" class="form-control" name="monograph_file" accept=".pdf,.doc,.docx" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Student Full Name *</label>
                                <input type="text" class="form-control" name="student_full_name" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Student Email</label>
                                <input type="email" class="form-control" name="student_email">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Student Phone</label>
                                <input type="tel" class="form-control" name="student_phone">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Instructor Full Name *</label>
                                <input type="text" class="form-control" name="instructor_full_name"
                                    value="<?php echo htmlspecialchars(getFullName()); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Instructor Email *</label>
                                <input type="email" class="form-control" name="instructor_email"
                                    value="<?php echo htmlspecialchars(getUserName()); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Instructor Phone</label>
                                <input type="tel" class="form-control" name="instructor_phone">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Publish Year *</label>
                                <select class="form-select" name="publish_year" required>
                                    <?php $current = date('Y');
                                    for ($y = $current; $y >= 2000; $y--): ?>
                                        <option value="<?php echo $y; ?>" <?php echo ($y === $current) ? 'selected' : ''; ?>>
                                            <?php echo $y; ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="add_monograph" class="btn btn-primary">Add Monograph</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../../includes/footer.php'; ?>