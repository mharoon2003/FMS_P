<?php
require_once __DIR__ . '/../../includes/users.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: /FMS_P/index.php?page=login');
    exit();
}

$user = user_find_by_id($_SESSION['user_id']);
if (!$user) {
    header('Location: /FMS_P/index.php?page=logout');
    exit();
}

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize input
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');

    if (empty($full_name) || empty($email)) {
        $message = 'Full name and email are required.';
        $messageType = 'danger';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Invalid email address.';
        $messageType = 'danger';
    } else {
        $data = [
            'full_name' => $full_name,
            'email' => $email,
            'phone' => $phone,
            'address' => $address
        ];

        // Handle password change
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        if (!empty($new_password)) {
            if ($new_password !== $confirm_password) {
                $message = 'New passwords do not match.';
                $messageType = 'danger';
            } elseif (strlen($new_password) < 6) {
                $message = 'Password must be at least 6 characters long.';
                $messageType = 'danger';
            } else {
                $data['password'] = password_hash($new_password, PASSWORD_DEFAULT);
            }
        }

        // Update only if no error
        if (empty($message)) {
            if (user_update($_SESSION['user_id'], $data)) {
                // Update session full_name for immediate UI update
                $_SESSION['full_name'] = $full_name;
                $message = 'Profile updated successfully!';
                $messageType = 'success';
                // Refresh user data
                $user = user_find_by_id($_SESSION['user_id']);
            } else {
                $message = 'Failed to update profile.';
                $messageType = 'danger';
            }
        }
    }
}

$page_title = 'My Profile';
?>
<?php require __DIR__ . '/../../includes/header.php'; ?>

<h2 class="mb-4"><i class="bi bi-person-circle"></i> My Profile</h2>

<?php if ($message): ?>
    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show">
        <?php echo htmlspecialchars($message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-body">
                <form method="POST" action="/FMS_P/index.php?page=profile">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Full Name</label>
                            <input type="text" class="form-control" name="full_name" 
                                   value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" class="form-control" 
                                   value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
                            <small class="text-muted">Username cannot be changed</small>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" 
                                   value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phone</label>
                            <input type="tel" class="form-control" name="phone" 
                                   value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <textarea class="form-control" name="address" 
                                  rows="2"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                    </div>

                    <hr class="my-4">
                    
                    <h5 class="mb-3">Change Password</h5>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">New Password</label>
                            <input type="password" class="form-control" name="new_password" minlength="6">
                            <small class="text-muted">Leave blank to keep current password</small>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control" name="confirm_password">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Update Profile
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-body text-center">
                <i class="bi bi-person-circle text-primary" style="font-size: 5rem;"></i>
                <h4 class="mt-3"><?php echo htmlspecialchars($user['full_name']); ?></h4>
                <p class="text-muted mb-1"><?php echo htmlspecialchars($user['email']); ?></p>
                <span class="badge bg-<?php echo $user['role'] === 'admin' ? 'danger' : ($user['role'] === 'teacher' ? 'primary' : 'info'); ?>">
                    <?php echo ucfirst($user['role']); ?>
                </span>
                <hr>
                <p class="mb-1"><small class="text-muted">Member since</small></p>
                <p><strong><?php echo date('M d, Y', strtotime($user['created_at'])); ?></strong></p>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../../includes/footer.php'; ?>"    34_view/shared/unauthorized.php"<?php $page_title = 'Access Denied'; ?>
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