<?php $page_title = 'Page Not Found'; ?>
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