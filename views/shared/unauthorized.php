<?php $page_title = 'Access Denied'; ?>
<?php require __DIR__ . '/../../includes/header.php'; ?>

<div class="text-center py-5">
    <i class="bi bi-x-circle text-danger" style="font-size: 5rem;"></i>
    <h2 class="mt-4">Access Denied</h2>
    <p class="lead">You don't have permission to view this page.</p>
    <a href="/FMS_P/index.php?page=<?php echo getUserRole() ? getUserRole() . '_dashboard' : 'login'; ?>" class="btn btn-primary">
        <i class="bi bi-arrow-left"></i> Go Back
    </a>
</div>

<?php require __DIR__ . '/../../includes/footer.php'; ?>