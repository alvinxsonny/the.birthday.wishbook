<?php
/**
 * The Birthday Wishbook — Public Wishlist Page
 * Shareable page accessible via token — no auth required
 */

session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/includes/functions.php';

$token = trim($_GET['token'] ?? '');

if (empty($token)) {
    http_response_code(404);
    $pageTitle = 'Wishlist Not Found';
    include __DIR__ . '/includes/header.php';
    echo '<main class="auth-wrapper"><div class="card-static auth-card" style="text-align:center;">
        <span style="font-size:4rem; display:block; margin-bottom:1rem; color:var(--text-light);">' . getSvgIcon('search', 'svg-icon-xl') . '</span>
        <h2>Wishlist Not Found</h2>
        <p style="color:var(--text-mid); margin: 1rem 0;">This link doesn\'t seem to lead anywhere. Make sure you have the right URL!</p>
        <a href="index.php" class="btn btn-primary">Go Home</a>
    </div></main>';
    include __DIR__ . '/includes/footer.php';
    exit;
}

$db = getDB();

// Get user by token
$stmt = $db->prepare('SELECT id, username, birthday, share_token FROM users WHERE share_token = ?');
$stmt->execute([$token]);
$wishlistOwner = $stmt->fetch();

if (!$wishlistOwner) {
    http_response_code(404);
    $pageTitle = 'Wishlist Not Found';
    include __DIR__ . '/includes/header.php';
    echo '<main class="auth-wrapper"><div class="card-static auth-card" style="text-align:center;">
        <span style="font-size:4rem; display:block; margin-bottom:1rem; color:var(--text-light);">' . getSvgIcon('search', 'svg-icon-xl') . '</span>
        <h2>Wishlist Not Found</h2>
        <p style="color:var(--text-mid); margin: 1rem 0;">This wishlist doesn\'t exist or has been removed.</p>
        <a href="index.php" class="btn btn-primary">Go Home</a>
    </div></main>';
    include __DIR__ . '/includes/footer.php';
    exit;
}

// Get birthday stats
$stats = calculateBirthdayStats($wishlistOwner['birthday']);

// Get wishlist items with category names
$stmt = $db->prepare('SELECT w.*, c.category_name FROM wishlist_items w LEFT JOIN categories c ON w.category_id = c.id WHERE w.user_id = ? ORDER BY COALESCE(c.sort_order, 99999) ASC, c.category_name ASC, w.sort_order ASC, w.created_at DESC');
$stmt->execute([$wishlistOwner['id']]);
$items = $stmt->fetchAll();

$pageTitle = sanitize($wishlistOwner['username']) . '\'s Birthday Wishlist';
$pageScripts = ['wishlist-public.js'];

include __DIR__ . '/includes/header.php';
?>

<main class="main-content fade-in">
    
    <!-- Public Header -->
    <div class="public-header">
        <h1><?php echo getSvgIcon('cake'); ?> <?php echo sanitize($wishlistOwner['username']); ?>'s Wishlist</h1>
        <p style="color: var(--text-mid); font-size: 1.1rem; margin-top: var(--space-sm);">
            <?php if ($stats['is_birthday_today']): ?>
                <?php echo getSvgIcon('popper'); ?> Today is their birthday! <?php echo getSvgIcon('popper'); ?>
            <?php else: ?>
                Birthday coming up on <strong><?php echo $stats['next_birthday']; ?></strong>!
            <?php endif; ?>
        </p>
        
        <!-- Mini Countdown -->
        <?php if (!$stats['is_birthday_today']): ?>
            <div class="public-countdown" id="publicCountdown" data-target="<?php echo $stats['next_birthday_ts']; ?>">
                <div class="public-countdown-item">
                    <span class="public-countdown-number" id="pub-days"><?php echo $stats['days_until']; ?></span>
                    <span class="public-countdown-label">Days</span>
                </div>
                <div class="public-countdown-item">
                    <span class="public-countdown-number" id="pub-hours">0</span>
                    <span class="public-countdown-label">Hours</span>
                </div>
                <div class="public-countdown-item">
                    <span class="public-countdown-number" id="pub-mins">0</span>
                    <span class="public-countdown-label">Mins</span>
                </div>
                <div class="public-countdown-item">
                    <span class="public-countdown-number" id="pub-secs">0</span>
                    <span class="public-countdown-label">Secs</span>
                </div>
            </div>
        <?php endif; ?>
        
        <div class="stats-row" style="margin-top: var(--space-md);">
            <span class="stat-badge"><?php echo getSvgIcon('zodiac-' . $stats['zodiac']['svg']); ?> <?php echo $stats['zodiac']['name']; ?></span>
            <span class="stat-badge"><?php echo getSvgIcon('gift'); ?> <?php echo count($items); ?> wish<?php echo count($items) !== 1 ? 'es' : ''; ?></span>
        </div>
    </div>
    
    <!-- Wishlist Items Wrapper for Auto-Refresh -->
    <div id="wishlistItemsContainer">
        <?php if (empty($items)): ?>
            <div class="empty-state">
                <span class="empty-state-emoji"><?php echo getSvgIcon('mailbox', 'svg-icon-xl'); ?></span>
                <h3>No wishes yet!</h3>
                <p><?php echo sanitize($wishlistOwner['username']); ?> hasn't added any items to their wishlist yet. Check back soon!</p>
            </div>
        <?php else: 
            // Group items in PHP
            $grouped = [];
            foreach ($items as $item) {
                $catName = $item['category_name'] ?? 'General';
                $grouped[$catName][] = $item;
            }
            
            // Separator line just above the first category
            ?>
            <hr class="category-separator" style="margin-top: 0; margin-bottom: var(--space-xl);">
            
            <?php
            $catIndex = 0;
            foreach ($grouped as $catName => $catItems): 
                if ($catIndex > 0): ?>
                    <hr class="category-separator">
                <?php endif; ?>
                
                <div class="category-group">
                    <h3 class="category-title">
                        <?php echo sanitize($catName); ?> 
                        <span class="badge badge-lavender"><?php echo count($catItems); ?></span>
                    </h3>
                    
                    <div class="wishlist-grid">
                        <?php foreach ($catItems as $item): ?>
                            <div class="wish-card" id="pub-wish-<?php echo $item['id']; ?>">
                                <?php if (!empty($item['item_url'])): ?>
                                    <a href="<?php echo sanitize($item['item_url']); ?>" 
                                       target="_blank" rel="noopener noreferrer" 
                                       class="wish-card-overlay-link"></a>
                                <?php endif; ?>
    
                                <?php if (!empty($item['image_url'])): ?>
                                    <img src="<?php echo sanitize($item['image_url']); ?>" 
                                         alt="<?php echo sanitize($item['item_name']); ?>"
                                         class="wish-card-image"
                                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                    <div class="wish-card-placeholder" style="display: none;"><?php echo getSvgIcon('gift', 'svg-icon-xl'); ?></div>
                                <?php else: ?>
                                    <div class="wish-card-placeholder"><?php echo getSvgIcon('gift', 'svg-icon-xl'); ?></div>
                                <?php endif; ?>
                                
                                <div class="wish-card-body">
                                    <h4 class="wish-card-name"><?php echo sanitize($item['item_name']); ?></h4>
                                    
                                    <div class="wish-card-footer">
                                        <?php if (!empty($item['item_url'])): ?>
                                            <a href="<?php echo sanitize($item['item_url']); ?>" 
                                               target="_blank" rel="noopener noreferrer" 
                                               class="wish-card-link"><?php echo getSvgIcon('link'); ?> View Product →</a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php 
                $catIndex++;
            endforeach; 
        endif; ?>
    </div>
    
</main>

<!-- Auto-refresh Script -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    setInterval(async () => {
        try {
            const res = await fetch(window.location.href);
            if (!res.ok) return;
            const text = await res.text();
            
            const parser = new DOMParser();
            const doc = parser.parseFromString(text, 'text/html');
            
            const newContent = doc.getElementById('wishlistItemsContainer');
            const currentContent = document.getElementById('wishlistItemsContainer');
            
            if (newContent && currentContent) {
                // Check if HTML has actually changed to prevent unneeded repaints
                if (currentContent.innerHTML !== newContent.innerHTML) {
                    currentContent.innerHTML = newContent.innerHTML;
                }
            }
            
            // Also refresh wishes count badge in stats row
            const newBadge = doc.querySelector('.stats-row .stat-badge:nth-child(2)');
            const currentBadge = document.querySelector('.stats-row .stat-badge:nth-child(2)');
            if (newBadge && currentBadge) {
                currentBadge.innerHTML = newBadge.innerHTML;
            }
        } catch (err) {
            console.error('Silently refreshing wishlist failed:', err);
        }
    }, 10000);
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
