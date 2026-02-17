<?php
require_once __DIR__ . '/../../includes/monographs.php';

$message = '';
$messageType = '';

// Handle search
$monographs = [];
$keyword = '';

if (!empty($_GET['search'])) {
    $keyword = trim($_GET['search']);
    $monographs = monograph_search($keyword);
} else {
    $monographs = monograph_get_all();
}

$page_title = 'All Monographs';
?>
<?php require __DIR__ . '/../../includes/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-file-text"></i> All Monographs</h2>
</div>

<!-- Search Bar -->
<form method="GET" class="mb-4">
    <input type="hidden" name="page" value="student_monographs">
    <div class="input-group">
        <input type="text" class="form-control" name="search" 
               placeholder="Search monographs by title..." 
               value="<?php echo htmlspecialchars($keyword); ?>">
        <button class="btn btn-outline-primary" type="submit">
            <i class="bi bi-search"></i> Search
        </button>
        <?php if (!empty($keyword)): ?>
            <a href="/FMS_P/index.php?page=student_monographs" class="btn btn-outline-secondary">
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
                                <i class="bi bi-person-badge"></i> <?php echo htmlspecialchars($monograph['instructor_name']); ?><br>
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
    
    <p class="text-muted text-center mt-4">
        Showing <?php echo count($monographs); ?> monograph(s)
    </p>
<?php endif; ?>

<?php require __DIR__ . '/../../includes/footer.php'; ?>