<?php
/**
 * The Birthday Wishbook — Sign Up
 */

$pageTitle = 'Create Your Account';
$pageScripts = ['auth.js'];

if (session_status() === PHP_SESSION_NONE) session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/includes/functions.php';

$errors = [];
$old = ['username' => '', 'email' => '', 'birthday' => ''];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF check
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid form submission. Please try again.';
    }
    
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';
    $birthday = $_POST['birthday'] ?? '';
    
    $old = ['username' => $username, 'email' => $email, 'birthday' => $birthday];
    
    // Validation
    if (empty($username) || strlen($username) < 2 || strlen($username) > 50) {
        $errors[] = 'Name must be between 2 and 50 characters.';
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }
    
    if (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters.';
    }
    
    if ($password !== $confirm) {
        $errors[] = 'Passwords do not match.';
    }
    
    if (empty($birthday)) {
        $errors[] = 'Please enter your birthday.';
    } else {
        $bday = new DateTime($birthday);
        $now = new DateTime();
        if ($bday > $now) {
            $errors[] = 'Birthday cannot be in the future.';
        }
    }
    
    // Check uniqueness
    if (empty($errors)) {
        $db = getDB();
        
        $stmt = $db->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = 'An account with this email already exists.';
        }
    }
    
    // Create user
    if (empty($errors)) {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $shareToken = generateShareToken();
        
        $stmt = $db->prepare('INSERT INTO users (username, email, password_hash, birthday, share_token) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([$username, $email, $passwordHash, $birthday, $shareToken]);
        
        $userId = $db->lastInsertId();
        
        // Auto-login
        session_regenerate_id(true);
        $_SESSION['user_id'] = $userId;
        $_SESSION['username'] = $username;
        
        setFlash('success', 'Welcome aboard, ' . $username . '! Your wishlist is ready.');
        header('Location: dashboard.php');
        exit;
    }
}

include __DIR__ . '/includes/header.php';
?>

<main class="auth-wrapper fade-in">
    <div class="card-static auth-card">
        <h2>Join the Party</h2>
        <p class="auth-subtitle">Create your account and start building your birthday wishlist</p>
        
        <div class="alert alert-warning" style="margin-top: var(--space-md); margin-bottom: var(--space-md); text-align: left;">
            ⚠️ Please carefully remember your password! There is no password reset option, and you won't be able to recover it if lost or forgotten.
        </div>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <?php echo getSvgIcon('warning'); ?> <?php echo sanitize($errors[0]); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="signup.php" id="signupForm" novalidate>
            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
            
            <div class="form-group">
                <label class="form-label" for="username">Name</label>
                <input type="text" id="username" name="username" class="form-input" placeholder="What should we call you?" value="<?php echo sanitize($old['username']); ?>" required minlength="2" maxlength="50" autocomplete="username">
            </div>
            
            <div class="form-group">
                <label class="form-label" for="email">Email</label>
                <input type="email" id="email" name="email" class="form-input" placeholder="your@email.com" value="<?php echo sanitize($old['email']); ?>" required autocomplete="email">
            </div>
            
            <div class="form-group">
                <label class="form-label" for="birthday">Birthday</label>
                <input type="date" id="birthday" name="birthday" class="form-input" value="<?php echo sanitize($old['birthday']); ?>" required max="<?php echo date('Y-m-d'); ?>">
                <p class="form-hint">We'll use this for your countdown!</p>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="password">Password</label>
                <input type="password" id="password" name="password" class="form-input" placeholder="At least 6 characters" required minlength="6" autocomplete="new-password">
            </div>
            
            <div class="form-group">
                <label class="form-label" for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" class="form-input" placeholder="Type it again" required autocomplete="new-password">
            </div>
            
            <button type="submit" class="btn btn-primary btn-lg" style="width: 100%;">Sign Up</button>
        </form>
        
        <p class="auth-footer">
            Already have an account? <a href="signin.php">Sign In</a>
        </p>
    </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
