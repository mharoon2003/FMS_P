monographs.php"<?php
require_once __DIR__ . '/../../includes/monographs.php';

$message = '';
$messageType = '';

// Handle POST (add/edit/delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_monograph'])) {
        $error = '';
        
        // Upload monograph file
        if (isset($_FILES['monograph_file']) && $_FILES['monograph_file']['error'] === UPLOAD_ERR_OK) {
            $upload_result = validateAndUploadFile(
                $_FILES['monograph_file'],
                $_SERVER['DOCUMENT_ROOT'] . '/FMS_P/uploads/monographs/',
                ['pdf', 'doc', 'docx']
            );
            if ($upload_result['success']) {
                $file_path = $upload_result['path'];
            } else {
                $error = 'File upload error: ' . $upload_result['error'];
            }
        } else {
            $error = 'Please upload a monograph file.';
        }

        // Upload photo (optional)
        $photo_path = '';
        if (empty($error) && !empty($_FILES['student_photo']['name']) && $_FILES['student_photo']['error'] === UPLOAD_ERR_OK) {
            $upload_result = validateAndUploadFile(
                $_FILES['student_photo'],
                $_SERVER['DOCUMENT_ROOT'] . '/FMS_P/uploads/photos/',
                ['jpg', 'jpeg', 'png']
            );
            if ($upload_result['success']) {
                $photo_path = $upload_result['path'];
            } else {
                $error = 'Photo upload error: ' . $upload_result['error'];
            }
        }

        if (!$error) {
            $data = [
                'title' => trim($_POST['title']),
                'file_path' => $file_path,
                'student_full_name' => trim($_POST['student_full_name']),
                'student_email' => trim($_POST['student_email'] ?? ''),
                'student_phone' => trim($_POST['student_phone'] ?? ''),
                'student_photo' => $photo_path,
                'instructor_full_name' => trim($_POST['instructor_full_name']),
                'instructor_email' => trim($_POST['instructor_email'] ?? ''),
                'instructor_phone' => trim($_POST['instructor_phone'] ?? ''),
                'publish_year' => intval($_POST['publish_year'])
            ];
            if (monograph_create($data)) {
                $message = 'Monograph added successfully!';
                $messageType = 'success';
            } else {
                $error = 'Failed to save monograph.';
            }
        }
        if ($error) {
            $message = $error;
            $messageType = 'danger';
        }
    }
    elseif (isset($_POST['edit_monograph'])) {
        $photo_path = $_POST['existing_photo'] ?? '';
        if (!empty($_FILES['student_photo']['name']) && $_FILES['student_photo']['error'] === UPLOAD_ERR_OK) {
            // Delete old photo if exists
            if ($photo_path && file_exists($_SERVER['DOCUMENT_ROOT'] . '/' . $photo_path)) {
                unlink($_SERVER['DOCUMENT_ROOT'] . '/' . $photo_path);
            }
            $upload_result = validateAndUploadFile(
                $_FILES['student_photo'],
                $_SERVER['DOCUMENT_ROOT'] . '/FMS_P/uploads/photos/',
                ['jpg', 'jpeg', 'png']
            );
            if ($upload_result['success']) {
                $photo_path = $upload_result['path'];
            } else {
                $message = 'Photo update error: ' . $upload_result['error'];
                $messageType = 'danger';
            }
        }
        if (!$message) {
            $data = [
                'title' => trim($_POST['title']),
                'file_path' => $_POST['existing_file'],
                'student_full_name' => trim($_POST['student_full_name']),
                'student_email' => trim($_POST['student_email'] ?? ''),
                'student_phone' => trim($_POST['student_phone'] ?? ''),
                'student_photo' => $photo_path,
                'instructor_full_name' => trim($_POST['instructor_full_name']),
                'instructor_email' => trim($_POST['instructor_email'] ?? ''),
                'instructor_phone' => trim($_POST['instructor_phone'] ?? ''),
                'publish_year' => intval($_POST['publish_year'])
            ];
            if (monograph_update($_POST['monograph_id'], $data)) {
                $message = 'Monograph updated successfully!';
                $messageType = 'success';
            } else {
                $message = 'Failed to update monograph.';
                $messageType = 'danger';
            }
        }
    }
    elseif (isset($_POST['delete_monograph'])) {
        $monograph = monograph_find_by_id($_POST['monograph_id']);
        if ($monograph) {
            // Delete files if exist
            if ($monograph['file_path'] && file_exists($_SERVER['DOCUMENT_ROOT'] . '/' . $monograph['file_path'])) {
                unlink($_SERVER['DOCUMENT_ROOT'] . '/' . $monograph['file_path']);
            }
            if ($monograph['student_photo'] && file_exists($_SERVER['DOCUMENT_ROOT'] . '/' . $monograph['student_photo'])) {
                unlink($_SERVER['DOCUMENT_ROOT'] . '/' . $monograph['student_photo']);
            }
            if (monograph_delete($_POST['monograph_id'])) {
                $message = 'Monograph deleted successfully!';
                $messageType = 'success';
            } else {
                $message = 'Failed to delete monograph.';
                $messageType = 'danger';
            }
        }
    }
}

// Fetch data
$monograph_years = monograph_get_years();
$monographs = monograph_get_all();

// Handle search
if (!empty($_GET['search'])) {
    $keyword = trim($_GET['search']);
    $monographs = monograph_search($keyword);
}

$page_title = 'Manage Monographs';
?>
<?php require __DIR__ . '/../../includes/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-file-text"></i> Manage Monographs</h2>
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
    <input type="hidden" name="page" value="admin_monographs">
    <div class="input-group">
        <input type="text" class="form-control" name="search" 
               placeholder="Search monographs by title..." 
               value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
        <button class="btn btn-outline-secondary" type="submit">
            <i class="bi bi-search"></i> Search
        </button>
        <?php if (!empty($_GET['search'])): ?>
            <a href="/FMS_P/index.php?page=admin_monographs" class="btn btn-outline-secondary">
                <i class="bi bi-x-lg"></i> Clear
            </a>
        <?php endif; ?>
    </div>
</form>

<!-- Year Tabs -->
<?php if (!empty($monograph_years)): ?>
    <ul class="nav nav-tabs mb-4" role="tablist">
        <li class="nav-item">
            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#all-years">
                All Years
            </button>
        </li>
        <?php foreach ($monograph_years as $year): ?>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#year-<?php echo $year; ?>">
                    <?php echo $year; ?>
                </button>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<div class="tab-content">
    <!-- All Years Tab -->
    <div class="tab-pane fade show active" id="all-years">
        <div class="card shadow-sm">
            <div class="card-body">
                <?php if (empty($monographs)): ?>
                    <p class="text-muted">No monographs found.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Title</th>
                                    <th>Student</th>
                                    <th>Instructor</th>
                                    <th>Year</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($monographs as $monograph): ?>
                                    <tr>
                                        <td>
                                            <a href="/FMS_P/<?php echo htmlspecialchars($monograph['file_path']); ?>" 
                                               target="_blank" class="text-decoration-none">
                                                <strong><?php echo htmlspecialchars($monograph['title']); ?></strong>
                                            </a>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($monograph['student_full_name']); ?><br>
                                            <small class="text-muted"><?php echo htmlspecialchars($monograph['student_email'] ?: '-'); ?></small>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($monograph['instructor_full_name']); ?><br>
                                            <small class="text-muted"><?php echo htmlspecialchars($monograph['instructor_email'] ?: '-'); ?></small>
                                        </td>
                                        <td><?php echo $monograph['publish_year']; ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-warning me-1" 
                                                    onclick="editMonograph(<?php echo htmlspecialchars(json_encode($monograph, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT)); ?>)">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <form method="POST" style="display: inline;" 
                                                  action="/FMS_P/index.php?page=admin_monographs">
                                                <input type="hidden" name="monograph_id" value="<?php echo (int)$monograph['id']; ?>">
                                                <button type="submit" name="delete_monograph" class="btn btn-sm btn-danger"
                                                        onclick="return confirm('Delete this monograph?');">
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

    <!-- Individual Year Tabs -->
    <?php foreach ($monograph_years as $year): 
        $year_monographs = monograph_get_by_year($year);
    ?>
        <div class="tab-pane fade" id="year-<?php echo $year; ?>">
            <div class="card shadow-sm">
                <div class="card-body">
                    <?php if (empty($year_monographs)): ?>
                        <p class="text-muted">No monographs in <?php echo $year; ?>.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Title</th>
                                        <th>Student</th>
                                        <th>Instructor</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($year_monographs as $monograph): ?>
                                        <tr>
                                            <td>
                                                <a href="/FMS_P/<?php echo htmlspecialchars($monograph['file_path']); ?>" 
                                                   target="_blank" class="text-decoration-none">
                                                    <strong><?php echo htmlspecialchars($monograph['title']); ?></strong>
                                                </a>
                                            </td>
                                            <td>
                                                <?php echo htmlspecialchars($monograph['student_full_name']); ?><br>
                                                <small class="text-muted"><?php echo htmlspecialchars($monograph['student_email'] ?: '-'); ?></small>
                                            </td>
                                            <td>
                                                <?php echo htmlspecialchars($monograph['instructor_full_name']); ?><br>
                                                <small class="text-muted"><?php echo htmlspecialchars($monograph['instructor_email'] ?: '-'); ?></small>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-warning me-1" 
                                                        onclick="editMonograph(<?php echo htmlspecialchars(json_encode($monograph, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT)); ?>)">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <form method="POST" style="display: inline;" 
                                                      action="/FMS_P/index.php?page=admin_monographs">
                                                    <input type="hidden" name="monograph_id" value="<?php echo (int)$monograph['id']; ?>">
                                                    <button type="submit" name="delete_monograph" class="btn btn-sm btn-danger"
                                                            onclick="return confirm('Delete this monograph?');">
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
    <?php endforeach; ?>
</div>

<!-- Add Monograph Modal -->
<div class="modal fade" id="addMonographModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="/FMS_P/index.php?page=admin_monographs" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Monograph</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Title *</label>
                                <input type="text" class="form-control" name="title" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Upload File *</label>
                                <input type="file" class="form-control" name="monograph_file" accept=".pdf,.doc,.docx" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Year *</label>
                                <select class="form-select" name="publish_year" required>
                                    <?php $current = date('Y'); for ($y = $current; $y >= 2000; $y--): ?>
                                        <option value="<?php echo $y; ?>"><?php echo $y; ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6 class="border-bottom pb-2">Student</h6>
                            <div class="mb-3">
                                <label class="form-label">Full Name *</label>
                                <input type="text" class="form-control" name="student_full_name" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" name="student_email">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Phone</label>
                                <input type="tel" class="form-control" name="student_phone">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Photo (optional)</label>
                                <input type="file" class="form-control" name="student_photo" accept="image/*">
                            </div>

                            <h6 class="border-bottom pb-2 mt-3">Instructor</h6>
                            <div class="mb-3">
                                <label class="form-label">Full Name *</label>
                                <input type="text" class="form-control" name="instructor_full_name" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" name="instructor_email">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Phone</label>
                                <input type="tel" class="form-control" name="instructor_phone">
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

<!-- Edit Monograph Modal -->
<div class="modal fade" id="editMonographModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="/FMS_P/index.php?page=admin_monographs" enctype="multipart/form-data">
                <input type="hidden" name="monograph_id" id="edit_monograph_id">
                <input type="hidden" name="existing_file" id="edit_existing_file">
                <input type="hidden" name="existing_photo" id="edit_existing_photo">
                
                <div class="modal-header">
                    <h5 class="modal-title">Edit Monograph</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Title *</label>
                                <input type="text" class="form-control" name="title" id="edit_title" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Current File</label>
                                <a href="" id="edit_current_file_link" target="_blank" class="form-control d-block text-truncate">Current File</a>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Upload New File (optional)</label>
                                <input type="file" class="form-control" name="monograph_file" accept=".pdf,.doc,.docx">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Year *</label>
                                <select class="form-select" name="publish_year" id="edit_publish_year" required>
                                    <?php for ($y = date('Y'); $y >= 2000; $y--): ?>
                                        <option value="<?php echo $y; ?>"><?php echo $y; ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6 class="border-bottom pb-2">Student</h6>
                            <div class="mb-3">
                                <label class="form-label">Full Name *</label>
                                <input type="text" class="form-control" name="student_full_name" id="edit_student_name" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" name="student_email" id="edit_student_email">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Phone</label>
                                <input type="tel" class="form-control" name="student_phone" id="edit_student_phone">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Current Photo</label>
                                <img id="edit_current_photo_img" src="" alt="Current Photo" class="img-thumbnail mb-2" style="max-width: 100px;">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Upload New Photo (optional)</label>
                                <input type="file" class="form-control" name="student_photo" accept="image/*">
                            </div>

                            <h6 class="border-bottom pb-2 mt-3">Instructor</h6>
                            <div class="mb-3">
                                <label class="form-label">Full Name *</label>
                                <input type="text" class="form-control" name="instructor_full_name" id="edit_instructor_name" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" name="instructor_email" id="edit_instructor_email">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Phone</label>
                                <input type="tel" class="form-control" name="instructor_phone" id="edit_instructor_phone">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="edit_monograph" class="btn btn-primary">Update Monograph</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editMonograph(monograph) {
    document.getElementById('edit_monograph_id').value = monograph.id;
    document.getElementById('edit_title').value = monograph.title;
    document.getElementById('edit_student_name').value = monograph.student_full_name;
    document.getElementById('edit_student_email').value = monograph.student_email || '';
    document.getElementById('edit_student_phone').value = monograph.student_phone || '';
    document.getElementById('edit_instructor_name').value = monograph.instructor_full_name;
    document.getElementById('edit_instructor_email').value = monograph.instructor_email || '';
    document.getElementById('edit_instructor_phone').value = monograph.instructor_phone || '';
    document.getElementById('edit_publish_year').value = monograph.publish_year;
    
    // Set file/photo paths
    document.getElementById('edit_existing_file').value = monograph.file_path;
    document.getElementById('edit_current_file_link').href = '/FMS_P/' + monograph.file_path;
    document.getElementById('edit_current_file_link').textContent = monograph.file_path.split('/').pop();
    
    if (monograph.student_photo) {
        document.getElementById('edit_existing_photo').value = monograph.student_photo;
        document.getElementById('edit_current_photo_img').src = '/FMS_P/' + monograph.student_photo;
        document.getElementById('edit_current_photo_img').style.display = 'block';
    } else {
        document.getElementById('edit_current_photo_img').style.display = 'none';
    }
    
    const modal = new bootstrap.Modal(document.getElementById('editMonographModal'));
    modal.show();
}
</script>

<?php require __DIR__ . '/../../includes/footer.php'; ?>