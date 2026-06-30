<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/functions.php';

$csrfToken = generateCSRFToken();
$flash = getFlash();

// Determine current page for nav highlighting
$currentPage = basename($_SERVER['SCRIPT_NAME'], '.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo APP_NAME; ?> — Create your birthday wishlist and share it with a single link. Your Birthday, Your Wishlist, One Link.">
    <meta name="csrf-token" content="<?php echo $csrfToken; ?>">
    <title><?php echo isset($pageTitle) ? sanitize($pageTitle) . ' — ' . APP_NAME : APP_NAME; ?></title>
    
    <!-- Igra Sans Font Import -->
    <link href="https://db.onlinewebfonts.com/c/9759cef799ea6efc20b46b06ed13b47e?family=Igra+Sans" rel="stylesheet">
    
    <!-- Styles -->
    <link rel="stylesheet" href="<?php echo getBaseUrl(); ?>/assets/css/style.css">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?php echo getBaseUrl(); ?>/assets/img/flaticon.png">
</head>
<body>
    <!-- Floating blurred dual-tone background circles -->
    <div class="bg-blur-circle bg-blur-1"></div>
    <div class="bg-blur-circle bg-blur-2"></div>
    <div class="bg-blur-circle bg-blur-3"></div>
    
    <div class="page-wrapper">
    
    <!-- Navigation -->
    <nav class="navbar" id="navbar">
        <div class="navbar-inner">
            <a href="<?php echo getBaseUrl(); ?>/index.php" class="navbar-brand">
                <img src="<?php echo getBaseUrl(); ?>/assets/img/flaticon.png" alt="<?php echo APP_NAME; ?> Logo" class="brand-logo">
                <span><?php echo APP_NAME; ?></span>
            </a>
            
            <button class="nav-toggle" id="navToggle" aria-label="Toggle navigation"><?php echo getSvgIcon('menu'); ?></button>
            
            <ul class="navbar-nav" id="navMenu">
                <?php if (isLoggedIn()): ?>
                    <li><a href="<?php echo getBaseUrl(); ?>/dashboard.php" class="<?php echo $currentPage === 'dashboard' ? 'active' : ''; ?>"><?php echo getSvgIcon('dashboard'); ?> Dashboard</a></li>
                    <li><a href="<?php echo getBaseUrl(); ?>/change-password.php" class="<?php echo $currentPage === 'change-password' ? 'active' : ''; ?>"><?php echo getSvgIcon('key'); ?> Password</a></li>
                    <li class="nav-separator"></li>
                    <li class="nav-user-greeting"><span><?php echo sanitize($_SESSION['username']); ?> 👋</span></li>
                    <li><a href="<?php echo getBaseUrl(); ?>/signout.php" class="btn btn-sm btn-outline">Sign Out</a></li>
                <?php else: ?>
                    <li><a href="<?php echo getBaseUrl(); ?>/signin.php" class="<?php echo $currentPage === 'signin' ? 'active' : ''; ?>">Sign In</a></li>
                    <li><a href="<?php echo getBaseUrl(); ?>/signup.php" class="btn btn-sm btn-primary">Sign Up</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>
    

