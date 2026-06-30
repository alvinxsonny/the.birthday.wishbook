<?php
/**
 * API: Get Wishlist Items
 */

session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    jsonResponse(false, 'Not authenticated.', [], 401);
}

$db = getDB();
$stmt = $db->prepare('SELECT w.*, c.category_name FROM wishlist_items w LEFT JOIN categories c ON w.category_id = c.id WHERE w.user_id = ? ORDER BY c.category_name ASC, w.sort_order ASC, w.created_at DESC');
$stmt->execute([$_SESSION['user_id']]);
$items = $stmt->fetchAll();

jsonResponse(true, '', ['items' => $items]);
