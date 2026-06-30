<?php
/**
 * API: Update Category Order
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

$order = json_decode($_POST['order'] ?? '[]', true);
if (empty($order)) {
    jsonResponse(false, 'Invalid data.');
}

$db = getDB();
$db->beginTransaction();
try {
    $stmt = $db->prepare('UPDATE categories SET sort_order = ? WHERE id = ? AND user_id = ?');
    foreach ($order as $item) {
        $stmt->execute([(int)$item['sort_order'], (int)$item['id'], $_SESSION['user_id']]);
    }
    $db->commit();
    jsonResponse(true, 'Order updated successfully.');
} catch (Exception $e) {
    $db->rollBack();
    jsonResponse(false, 'Database error: ' . $e->getMessage());
}
