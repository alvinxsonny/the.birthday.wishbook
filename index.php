<?php
/**
 * The Birthday Wishbook — Welcome / Landing Page
 */

$pageTitle = 'Your Birthday, Your Wishlist, One Link';

// If already logged in, go to dashboard
if (session_status() === PHP_SESSION_NONE) session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

include __DIR__ . '/includes/header.php';
?>

<main class="hero-section fade-in">
    <div class="hero-container">
        <div class="hero-content">
            <h1 class="hero-title">
                Your Birthday,<br>
                Your <span class="highlight">Wishlist</span>,<br>
                One Link.
            </h1>
            
            <p class="hero-subtitle">
                Create your perfect birthday wishlist, watch the countdown tick, and share it with everyone who matters — all in one beautiful place.
            </p>
            
            <div class="hero-cta">
                <a href="signup.php" class="btn btn-primary btn-lg"><?php echo getSvgIcon('cake'); ?> Create My Wishlist</a>
                <a href="signin.php" class="btn btn-outline btn-lg">Sign In</a>
            </div>
        </div>
        
        <div class="hero-image">
            <img src="<?php echo getBaseUrl(); ?>/assets/img/hero-cake.png" alt="Birthday cake illustration">
        </div>
    </div>
</main>

<section class="features-section">
    <h2 style="text-align: center;">Why You'll Love It</h2>
    
    <div class="features-grid">
        <div class="card-colored feature-card" style="background: var(--pink-light);">
            <span class="feature-icon"><img src="<?php echo getBaseUrl(); ?>/assets/img/countdown.svg" alt="Countdown icon"></span>
            <h3>Live Countdown</h3>
            <p>Watch the seconds tick down to your special day with a real-time birthday countdown that keeps the excitement alive.</p>
        </div>
        
        <div class="card-colored feature-card" style="background: var(--lavender-light);">
            <span class="feature-icon"><img src="<?php echo getBaseUrl(); ?>/assets/img/wishes.svg" alt="Wishes icon"></span>
            <h3>Curate Your Wishes</h3>
            <p>Add product links, custom names, and images for everything you're dreaming of. Your perfect list, organized your way.</p>
        </div>
        
        <div class="card-colored feature-card" style="background: var(--mint-light);">
            <span class="feature-icon"><img src="<?php echo getBaseUrl(); ?>/assets/img/linksvg.svg" alt="Link icon"></span>
            <h3>One Link To Share</h3>
            <p>Copy your unique link and share it with friends, family, or anyone. They can easily browse your wishes and find the perfect gift!</p>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
