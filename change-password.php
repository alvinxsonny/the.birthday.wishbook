<?php
/**
 * The Birthday Wishbook — Change Password
 */

$pageTitle = 'Change Password';
$pageScripts = ['auth.js'];

require_once __DIR__ . '/includes/auth-guard.php';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/includes/functions.php';

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid form submission.';
    }
    
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword     = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        $errors[] = 'Please fill in all fields.';
    }
    
    if (strlen($newPassword) < 6) {
        $errors[] = 'New password must be at least 6 characters.';
    }
    
    if ($newPassword !== $confirmPassword) {
        $errors[] = 'New passwords do not match.';
    }
    
    if ($currentPassword === $newPassword) {
        $errors[] = 'New password must be different from current password.';
    }
    
    if (empty($errors)) {
        $db = getDB();
        $stmt = $db->prepare('SELECT password_hash FROM users WHERE id = ?');
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
        
        if (!$user || !password_verify($currentPassword, $user['password_hash'])) {
            $errors[] = 'Current password is incorrect.';
        } else {
            $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $db->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
            $stmt->execute([$newHash, $_SESSION['user_id']]);
            
            $success = true;
            setFlash('success', 'Password changed successfully!');
        }
    }
}

include __DIR__ . '/includes/header.php';
?>

<main class="auth-wrapper fade-in">
    <div class="card-static auth-card">
        <h2>Change Password</h2>
        <p class="auth-subtitle">Keep your account secure with a new password</p>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <?php echo getSvgIcon('warning'); ?> <?php echo sanitize($errors[0]); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success">
                <?php echo getSvgIcon('check'); ?> Password updated successfully!
            </div>
        <?php endif; ?>
        
        <form method="POST" action="change-password.php" id="changePasswordForm" novalidate>
            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
            
            <div class="form-group">
                <label class="form-label" for="current_password">Current Password</label>
                <input type="password" id="current_password" name="current_password" class="form-input" placeholder="Your current password" required autocomplete="current-password">
            </div>
            
            <div class="form-group">
                <label class="form-label" for="new_password">New Password</label>
                <input type="password" id="new_password" name="new_password" class="form-input" placeholder="At least 6 characters" required minlength="6" autocomplete="new-password">
            </div>
            
            <div class="form-group">
                <label class="form-label" for="confirm_password">Confirm New Password</label>
                <input type="password" id="confirm_password" name="confirm_password" class="form-input" placeholder="Type it again" required autocomplete="new-password">
            </div>
            
            <button type="submit" class="btn btn-primary btn-lg" style="width: 100%;">Update Password</button>
        </form>
        
        <p class="auth-footer">
            <a href="dashboard.php">← Back to Dashboard</a>
        </p>
    </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
