<?php
/**
 * API: Delete Wishlist Item
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

$itemId = (int)($_POST['item_id'] ?? 0);

if ($itemId <= 0) {
    jsonResponse(false, 'Invalid item ID.');
}

$db = getDB();

// Verify ownership and delete
$stmt = $db->prepare('DELETE FROM wishlist_items WHERE id = ? AND user_id = ?');
$stmt->execute([$itemId, $_SESSION['user_id']]);

if ($stmt->rowCount() === 0) {
    jsonResponse(false, 'Item not found.', [], 404);
}

jsonResponse(true, 'Item deleted!');
