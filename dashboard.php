<?php
/**
 * The Birthday Wishbook — Dashboard
 * Main app page with countdown, share link, and wishlist management
 */

$pageTitle = 'My Dashboard';
$pageScripts = ['app.js'];

require_once __DIR__ . '/includes/auth-guard.php';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/includes/functions.php';

$user = getCurrentUser();
if (!$user) {
    session_destroy();
    header('Location: signin.php');
    exit;
}

$stats = calculateBirthdayStats($user['birthday']);
$shareUrl = getBaseUrl() . '/wishlist.php?token=' . $user['share_token'];

// Get wishlist items with category names
$db = getDB();
$stmt = $db->prepare('SELECT w.*, c.category_name FROM wishlist_items w LEFT JOIN categories c ON w.category_id = c.id WHERE w.user_id = ? ORDER BY COALESCE(c.sort_order, 99999) ASC, c.category_name ASC, w.sort_order ASC, w.created_at DESC');
$stmt->execute([$user['id']]);
$items = $stmt->fetchAll();

// Get list of categories for dropdown
$stmt = $db->prepare('SELECT * FROM categories WHERE user_id = ? ORDER BY sort_order ASC, category_name ASC');
$stmt->execute([$user['id']]);
$categories = $stmt->fetchAll();

include __DIR__ . '/includes/header.php';
?>

<!-- Confetti canvas (for birthday) -->
<?php if ($stats['is_birthday_today']): ?>
    <canvas id="confetti-canvas"></canvas>
<?php endif; ?>
<main class="main-content-full fade-in">
    <!-- Birthday Banner (shown on birthday) -->
    <?php if ($stats['is_birthday_today']): ?>
        <div class="birthday-banner slide-up">
            <h2><?php echo getSvgIcon('cake'); ?> Happy Birthday, <?php echo sanitize($user['username']); ?>! <?php echo getSvgIcon('cake'); ?></h2>
            <p style="font-size: 1.1rem; margin-top: var(--space-sm);">You're turning <strong><?php echo $stats['age']; ?></strong> today! Make it magical! <?php echo getSvgIcon('sparkles'); ?></p>
        </div>
    <?php endif; ?>
    
    <div class="dashboard-layout">
        <!-- Left Column: Sidebar (Countdown & Share Link) -->
        <div class="dashboard-sidebar">
            <!-- Countdown Section -->
            <section class="countdown-section" style="margin-bottom: var(--space-lg);">
                <div class="card-static" style="text-align: center; padding: var(--space-lg) var(--space-md);">
                    <h3 style="margin-bottom: var(--space-sm); font-size: 1.15rem;">
                        <?php if ($stats['is_birthday_today']): ?>
                            <?php echo getSvgIcon('popper'); ?> It's Today! <?php echo getSvgIcon('popper'); ?>
                        <?php else: ?>
                            <?php echo getSvgIcon('clock'); ?> Birthday Countdown
                        <?php endif; ?>
                    </h3>
                    
                    <?php if (!$stats['is_birthday_today']): ?>
                        <div class="countdown-grid" id="countdownGrid" 
                             data-target="<?php echo $stats['next_birthday_ts']; ?>"
                             style="grid-template-columns: repeat(4, 1fr); gap: 6px; margin: var(--space-sm) 0;">
                            <div class="countdown-card" style="padding: var(--space-xs) 2px; border-radius: var(--radius-sm);">
                                 <span class="countdown-number" id="countdown-days" style="font-size: 1.35rem;"><?php echo $stats['days_until']; ?></span>
                                 <span class="countdown-label" style="font-size: 0.55rem; margin-top: 2px;">Days</span>
                            </div>
                            <div class="countdown-card" style="padding: var(--space-xs) 2px; border-radius: var(--radius-sm);">
                                 <span class="countdown-number" id="countdown-hours" style="font-size: 1.35rem;">0</span>
                                 <span class="countdown-label" style="font-size: 0.55rem; margin-top: 2px;">Hrs</span>
                            </div>
                            <div class="countdown-card" style="padding: var(--space-xs) 2px; border-radius: var(--radius-sm);">
                                 <span class="countdown-number" id="countdown-minutes" style="font-size: 1.35rem;">0</span>
                                 <span class="countdown-label" style="font-size: 0.55rem; margin-top: 2px;">Min</span>
                            </div>
                            <div class="countdown-card" style="padding: var(--space-xs) 2px; border-radius: var(--radius-sm);">
                                 <span class="countdown-number" id="countdown-seconds" style="font-size: 1.35rem;">0</span>
                                 <span class="countdown-label" style="font-size: 0.55rem; margin-top: 2px;">Sec</span>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Stats Badges inside sidebar -->
                    <div style="display: flex; flex-direction: column; gap: var(--space-xs); margin-top: var(--space-md); text-align: left;">
                        <span class="stat-badge" style="width: 100%; display: flex; justify-content: flex-start; padding: 6px 12px; border-radius: var(--radius);"><?php echo getSvgIcon('cake'); ?> Age: <strong><?php echo $stats['age']; ?></strong></span>
                        <span class="stat-badge" style="width: 100%; display: flex; justify-content: flex-start; padding: 6px 12px; border-radius: var(--radius);"><?php echo getSvgIcon('zodiac-' . $stats['zodiac']['svg']); ?> <?php echo $stats['zodiac']['name']; ?></span>
                        <span class="stat-badge" style="width: 100%; display: flex; justify-content: flex-start; padding: 6px 12px; border-radius: var(--radius);"><?php echo getSvgIcon('clock'); ?> Born: <?php echo $stats['birthday_formatted']; ?></span>
                        <span class="stat-badge" style="width: 100%; display: flex; justify-content: flex-start; padding: 6px 12px; border-radius: var(--radius);"><?php echo getSvgIcon('sparkles'); ?> <?php echo number_format($stats['days_alive']); ?> days alive!</span>
                    </div>
                </div>
            </section>
            
            <!-- Share Link Section -->
            <section class="share-section" style="margin-bottom: var(--space-lg);">
                <div class="card-static" style="background: var(--lavender-light); padding: var(--space-lg);">
                    <h4 style="margin-bottom: var(--space-sm); display: flex; align-items: center; gap: var(--space-xs); font-size: 1.1rem;"><?php echo getSvgIcon('link'); ?> Share Your Wishlist</h4>
                    <p style="color: var(--text-mid); margin-bottom: var(--space-md); font-size: 0.85rem; line-height: 1.5;">
                        Copy your personalized link and share it with family and friends.
                    </p>
                    <div class="share-link-box" style="flex-direction: column; gap: var(--space-xs);">
                        <input type="text" class="share-link-input" id="shareLink" value="<?php echo sanitize($shareUrl); ?>" readonly style="width: 100%; text-align: center;">
                        <button class="btn btn-primary" id="copyLinkBtn" onclick="copyShareLink()" style="width: 100%;">
                            <?php echo getSvgIcon('clipboard'); ?> Copy Link
                        </button>
                    </div>
                </div>
            </section>

            <!-- Organize Categories Widget -->
            <?php if (!empty($categories)): ?>
                <section class="category-index-section">
                    <div class="card-static" style="padding: var(--space-lg);">
                        <h4 style="margin-bottom: var(--space-sm); display: flex; align-items: center; gap: var(--space-xs); font-size: 1.1rem;">
                            <?php echo getSvgIcon('menu'); ?> Category Order
                        </h4>
                        <p style="color: var(--text-mid); margin-bottom: var(--space-md); font-size: 0.8rem; line-height: 1.4;">
                            Reorder your categories. The wishlist updates instantly.
                        </p>
                        <ul class="category-order-list" id="categoryOrderList">
                            <?php foreach ($categories as $cat): ?>
                                <li class="category-order-item" data-id="<?php echo $cat['id']; ?>" data-name="<?php echo sanitize($cat['category_name']); ?>">
                                    <span class="category-order-name"><?php echo sanitize($cat['category_name']); ?></span>
                                    <div class="category-order-actions">
                                        <button class="btn btn-icon" onclick="moveCategoryUp(this)" title="Move Up">↑</button>
                                        <button class="btn btn-icon" onclick="moveCategoryDown(this)" title="Move Down">↓</button>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </section>
            <?php endif; ?>
        </div>
        
        <!-- Right Column: Wishlist Items Grouped by Category -->
        <div class="dashboard-main">
            <section class="wishlist-section">
                <div class="wishlist-header">
                    <h2><?php echo getSvgIcon('gift'); ?> My Wishlist <span class="badge badge-pink" id="itemCount"><?php echo count($items); ?> items</span></h2>
                    <button class="btn btn-success" onclick="openAddModal()">+ Add Item</button>
                </div>
                
                <div id="wishlistContainer">
                    <?php if (empty($items)): ?>
                        <div class="wishlist-grid" id="wishlistGrid">
                            <div class="empty-state" id="emptyState" style="grid-column: 1 / -1;">
                                <span class="empty-state-emoji"><?php echo getSvgIcon('gift', 'svg-icon-xl'); ?></span>
                                <h3>Your wishlist is empty!</h3>
                                <p>Start adding items you'd love to receive for your birthday.</p>
                                <button class="btn btn-primary" style="margin-top: var(--space-md);" onclick="openAddModal()">+ Add Your First Wish</button>
                            </div>
                        </div>
                    <?php else: 
                        // Group items in PHP
                        $grouped = [];
                        foreach ($items as $item) {
                            $catName = $item['category_name'] ?? 'General';
                            $grouped[$catName][] = $item;
                        }
                        
                        $catIndex = 0;
                        foreach ($grouped as $catName => $catItems): 
                            if ($catIndex > 0): ?>
                                <hr class="category-separator">
                            <?php endif; ?>
                            
                            <div class="category-group" data-category="<?php echo sanitize($catName); ?>">
                                <h3 class="category-title">
                                    <?php echo sanitize($catName); ?> 
                                    <span class="badge badge-lavender"><?php echo count($catItems); ?></span>
                                </h3>
                                
                                <div class="wishlist-grid">
                                    <?php foreach ($catItems as $item): ?>
                                        <div class="wish-card" id="wish-<?php echo $item['id']; ?>" data-id="<?php echo $item['id']; ?>">
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
                                                    
                                                    <div class="wish-card-actions" style="margin-left: auto;">
                                                        <button class="btn btn-sm btn-outline btn-icon" 
                                                                onclick="openEditModal(<?php echo htmlspecialchars(json_encode($item)); ?>)" 
                                                                title="Edit"><?php echo getSvgIcon('edit'); ?></button>
                                                        <button class="btn btn-sm btn-danger btn-icon" 
                                                                onclick="deleteItem(<?php echo $item['id']; ?>)" 
                                                                title="Delete"><?php echo getSvgIcon('trash'); ?></button>
                                                    </div>
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
            </section>
        </div>
    </div>
</main>

<!-- Add/Edit Item Modal -->
<div class="modal-overlay" id="itemModal">
    <div class="modal">
        <div class="modal-header">
            <h3 id="modalTitle"><?php echo getSvgIcon('gift'); ?> Add New Wish</h3>
            <button class="modal-close" onclick="closeModal()">&times;</button>
        </div>
        
        <form id="itemForm" onsubmit="handleItemSubmit(event)">
            <input type="hidden" id="itemId" name="item_id" value="">
            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
            
            <div class="form-group">
                <label class="form-label" for="itemName"><?php echo getSvgIcon('tag'); ?> Item Name</label>
                <input type="text" id="itemName" name="item_name" class="form-input" 
                       placeholder="e.g., Sony WH-1000XM5 Headphones" required maxlength="255">
            </div>
            
            <div class="form-group">
                <label class="form-label" for="itemUrl"><?php echo getSvgIcon('link'); ?> Product Link</label>
                <input type="url" id="itemUrl" name="item_url" class="form-input" 
                       placeholder="https://www.amazon.com/...">
                <p class="form-hint">Paste the product page URL</p>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="imageUrl"><?php echo getSvgIcon('image'); ?> Image URL</label>
                <input type="url" id="imageUrl" name="image_url" class="form-input" 
                       placeholder="https://example.com/image.jpg"
                       oninput="previewImage(this.value)">
                <p class="form-hint">Paste an image URL — a preview will appear below</p>
                
                <div class="image-preview-box" id="imagePreview">
                    <div class="image-preview-placeholder">
                        <span><?php echo getSvgIcon('image', 'svg-icon-xl'); ?></span>
                        <p>Image preview will appear here</p>
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="itemCategory"><?php echo getSvgIcon('tag'); ?> Category</label>
                <select id="itemCategory" name="category_id" class="form-input" onchange="toggleNewCategoryField(this.value)">
                    <option value="">Uncategorized / General</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>"><?php echo sanitize($cat['category_name']); ?></option>
                    <?php endforeach; ?>
                    <option value="new">+ Create Custom Category...</option>
                </select>
            </div>
            
            <div class="form-group" id="newCategoryGroup" style="display: none;">
                <label class="form-label" for="newCategoryName">New Category Name</label>
                <input type="text" id="newCategoryName" name="new_category_name" class="form-input" 
                       placeholder="e.g., Books, Tech, Clothes" maxlength="100">
            </div>
            
            <button type="submit" class="btn btn-primary btn-lg" style="width: 100%;" id="modalSubmitBtn">
                Add to Wishlist <?php echo getSvgIcon('popper'); ?>
            </button>
        </form>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal-overlay" id="confirmModal">
    <div class="modal modal-confirm">
        <div class="modal-header">
            <h3>Confirm Delete</h3>
            <button class="modal-close" onclick="closeConfirmModal()">&times;</button>
        </div>
        <div class="modal-body" style="padding: var(--space-lg); text-align: center;">
            <p style="font-size: 1.05rem; color: var(--text-dark); margin-bottom: var(--space-lg);">
                Are you sure you want to delete this item from your database?
            </p>
            <div style="display: flex; gap: var(--space-md); justify-content: center;">
                <button class="btn btn-outline" onclick="closeConfirmModal()">Cancel</button>
                <button class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Category Selection & Reordering Helpers -->
<script>
function toggleNewCategoryField(value) {
    const group = document.getElementById('newCategoryGroup');
    const input = document.getElementById('newCategoryName');
    if (value === 'new') {
        group.style.display = 'block';
        input.required = true;
        input.focus();
    } else {
        group.style.display = 'none';
        input.required = false;
        input.value = '';
    }
}

function moveCategoryUp(btn) {
    const li = btn.closest('.category-order-item');
    const prev = li.previousElementSibling;
    if (prev) {
        li.parentNode.insertBefore(li, prev);
        updateCategoryDOMOrder();
        saveCategoryOrder();
    }
}

function moveCategoryDown(btn) {
    const li = btn.closest('.category-order-item');
    const next = li.nextElementSibling;
    if (next) {
        li.parentNode.insertBefore(next, li);
        updateCategoryDOMOrder();
        saveCategoryOrder();
    }
}

function updateCategoryDOMOrder() {
    const list = document.getElementById('categoryOrderList');
    if (!list) return;
    const items = list.querySelectorAll('.category-order-item');
    const container = document.getElementById('wishlistContainer');
    if (!container) return;
    
    // Re-order the category elements in the DOM based on the category list order
    items.forEach(item => {
        const catName = item.getAttribute('data-name');
        const groupDiv = container.querySelector(`.category-group[data-category="${catName}"]`);
        if (groupDiv) {
            container.appendChild(groupDiv);
        }
    });
    
    // Make sure General/Uncategorized is always pushed to the end
    const generalDiv = container.querySelector('.category-group[data-category="General"]');
    if (generalDiv) {
        container.appendChild(generalDiv);
    }
    
    // Re-insert separators between category groups
    const allGroups = container.querySelectorAll('.category-group');
    const allSeparators = container.querySelectorAll('.category-separator');
    allSeparators.forEach(sep => sep.remove());
    
    allGroups.forEach((group, idx) => {
        if (idx < allGroups.length - 1) {
            const hr = document.createElement('hr');
            hr.className = 'category-separator';
            group.parentNode.insertBefore(hr, group.nextSibling);
        }
    });
}

async function saveCategoryOrder() {
    const list = document.getElementById('categoryOrderList');
    if (!list) return;
    const items = list.querySelectorAll('.category-order-item');
    const orderData = [];
    items.forEach((item, idx) => {
        const catId = item.getAttribute('data-id');
        if (catId) {
            orderData.push({ id: catId, sort_order: idx });
        }
    });
    
    const formData = new FormData();
    formData.append('order', JSON.stringify(orderData));
    formData.append('csrf_token', csrfToken);
    
    try {
        await fetch('api/update-category-order.php', {
            method: 'POST',
            body: formData
        });
    } catch (err) {
        console.error('Failed to save category order:', err);
    }
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
