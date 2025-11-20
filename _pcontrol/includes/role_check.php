<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$user_role = $_SESSION['user_role'] ?? 'viewer';
$current_script = basename($_SERVER['PHP_SELF']);

// SEO Manager restrictions
if ($user_role === 'seo_manager') {
    // Allow SEO pages and logout
    $allowed_seo = ['gseo.php', 'gseogeneral.php', 'gseooffpage.php', 'gseoonpage.php', 'logout.php'];
    if (!in_array($current_script, $allowed_seo)) {
        header('Location: gseo.php');
        exit;
    }
}

// Editor restrictions
if ($user_role === 'editor') {
    // Allow Blog page and logout
    $allowed_editor = ['gblog.php', 'logout.php'];
    if (!in_array($current_script, $allowed_editor)) {
        header('Location: gblog.php');
        exit;
    }
}
?>