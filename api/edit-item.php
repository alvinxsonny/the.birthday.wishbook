<?php
/**
 * API: Edit Wishlist Item
 */

session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Method not allowed.', [], 405);
}

if (!isLoggedIn()) {
    jsonResponse(false, 'Not authenticated.', [], 401);
}

if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
    jsonResponse(false, 'Invalid CSRF token.', [], 403);
}

$itemId    = (int)($_POST['item_id'] ?? 0);
$itemName  = trim($_POST['item_name'] ?? '');
$itemUrl   = trim($_POST['item_url'] ?? '');
$imageUrl  = trim($_POST['image_url'] ?? '');
$categoryId = $_POST['category_id'] ?? null;
$newCategoryName = trim($_POST['new_category_name'] ?? '');

if ($itemId <= 0) {
    jsonResponse(false, 'Invalid item ID.');
}

if (empty($itemName) || strlen($itemName) > 255) {
    jsonResponse(false, 'Item name is required (max 255 characters).');
}

if (!empty($itemUrl) && !filter_var($itemUrl, FILTER_VALIDATE_URL)) {
    jsonResponse(false, 'Please enter a valid product URL.');
}

if (!empty($imageUrl) && !filter_var($imageUrl, FILTER_VALIDATE_URL)) {
    jsonResponse(false, 'Please enter a valid image URL.');
}

if (strlen($newCategoryName) > 100) {
    jsonResponse(false, 'Category name must be under 100 characters.');
}

$db = getDB();

// Verify ownership
$stmt = $db->prepare('SELECT id FROM wishlist_items WHERE id = ? AND user_id = ?');
$stmt->execute([$itemId, $_SESSION['user_id']]);
if (!$stmt->fetch()) {
    jsonResponse(false, 'Item not found.', [], 404);
}

// Handle new custom category if provided
if ($newCategoryName !== '') {
    $stmt = $db->prepare('SELECT id FROM categories WHERE user_id = ? AND category_name = ?');
    $stmt->execute([$_SESSION['user_id'], $newCategoryName]);
    $cat = $stmt->fetch();
    if ($cat) {
        $categoryId = $cat['id'];
    } else {
        $stmt = $db->prepare('INSERT INTO categories (user_id, category_name) VALUES (?, ?)');
        $stmt->execute([$_SESSION['user_id'], $newCategoryName]);
        $categoryId = $db->lastInsertId();
    }
} elseif ($categoryId === '' || $categoryId === '0' || $categoryId === 0) {
    $categoryId = null;
}

// Update
$stmt = $db->prepare('UPDATE wishlist_items SET item_name = ?, item_url = ?, image_url = ?, category_id = ? WHERE id = ? AND user_id = ?');
$stmt->execute([
    $itemName,
    $itemUrl ?: null,
    $imageUrl ?: null,
    $categoryId ? (int)$categoryId : null,
    $itemId,
    $_SESSION['user_id']
]);

jsonResponse(true, 'Item updated!', [
    'id'          => $itemId,
    'item_name'   => $itemName,
    'item_url'    => $itemUrl,
    'image_url'   => $imageUrl,
    'category_id' => $categoryId ? (int)$categoryId : null,
]);
