<?php
// Multi-language landing: redirect to correct home.php in subdirectory

// Supported languages
$languages = ['ca', 'es'];

// Detect language from browser, cookie, or default
function detectLang($languages, $default = 'es') {
    // 1. Check ?lang= param
    if (isset($_GET['lang']) && in_array($_GET['lang'], $languages)) {
        return $_GET['lang'];
    }
    // 2. Check cookie
    if (isset($_COOKIE['lang']) && in_array($_COOKIE['lang'], $languages)) {
        return $_COOKIE['lang'];
    }
    // 3. Check browser
    if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
        $browserLangs = explode(',', strtolower($_SERVER['HTTP_ACCEPT_LANGUAGE']));
        foreach ($browserLangs as $bl) {
            $code = substr($bl, 0, 2);
            if (in_array($code, $languages)) {
                return $code;
            }
        }
    }
    // 4. Default
    return $default;
}

$lang = detectLang($languages);
setcookie('lang', $lang, time()+60*60*24*30, '/'); // 30 days

// Redirect to language home
header('Location: ' . $lang . '/home.php');
exit;
