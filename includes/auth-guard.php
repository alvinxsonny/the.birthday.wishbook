<?php
/**
 * Auth Guard — Include at top of protected pages
 * Redirects to signin if not authenticated
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: signin.php');
    exit;
}
