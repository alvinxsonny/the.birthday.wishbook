<?php
/**
 * The Birthday Wishbook — Sign In
 */

$pageTitle = 'Sign In';
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
$oldEmail = '';

// Rate limiting
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
    $_SESSION['login_lockout'] = 0;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF check
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid form submission. Please try again.';
    }
    
    // Rate limiting check
    if ($_SESSION['login_attempts'] >= 5 && time() < $_SESSION['login_lockout']) {
        $remaining = $_SESSION['login_lockout'] - time();
        $errors[] = "Too many attempts. Please wait {$remaining} seconds.";
    }
    
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $oldEmail = $email;
    
    if (empty($email) || empty($password)) {
        $errors[] = 'Please fill in all fields.';
    }
    
    if (empty($errors)) {
        $db = getDB();
        $stmt = $db->prepare('SELECT id, username, password_hash FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password_hash'])) {
            // Success — reset attempts
            $_SESSION['login_attempts'] = 0;
            
            // Regenerate session ID for security
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            
            setFlash('success', 'Welcome back, ' . $user['username'] . '!');
            header('Location: dashboard.php');
            exit;
        } else {
            // Failed login
            $_SESSION['login_attempts']++;
            if ($_SESSION['login_attempts'] >= 5) {
                $_SESSION['login_lockout'] = time() + 900; // 15 min lockout
            }
            $errors[] = 'Invalid email or password.';
        }
    }
}

include __DIR__ . '/includes/header.php';
?>

<main class="auth-wrapper fade-in">
    <div class="card-static auth-card">
        <h2>Welcome Back</h2>
        <p class="auth-subtitle">Sign in to manage your birthday wishlist</p>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <?php echo getSvgIcon('warning'); ?> <?php echo sanitize($errors[0]); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="signin.php" id="signinForm" novalidate>
            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
            
            <div class="form-group">
                <label class="form-label" for="email">Email</label>
                <input type="email" id="email" name="email" class="form-input" placeholder="your@email.com" value="<?php echo sanitize($oldEmail); ?>" required autocomplete="email">
            </div>
            
            <div class="form-group">
                <label class="form-label" for="password">Password</label>
                <input type="password" id="password" name="password" class="form-input" placeholder="Your password" required autocomplete="current-password">
            </div>
            
            <button type="submit" class="btn btn-primary btn-lg" style="width: 100%;">Sign In</button>
        </form>
        
        <p class="auth-footer">
            Don't have an account? <a href="signup.php">Create One</a>
        </p>
    </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
