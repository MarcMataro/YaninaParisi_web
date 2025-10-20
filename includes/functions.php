<?php
    // Funció per obtenir el idioma actual
    function getCurrentLanguage() {
        // Primer mirar si està a la sessió
        if (isset($_SESSION['language']) && in_array($_SESSION['language'], array('ca', 'es'))) {
            return $_SESSION['language'];
        }
        
        // Si no hi ha sessió, mirar si ve del navegador (Accept-Language)
        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            $browser_lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
            if ($browser_lang === 'ca') {
                $_SESSION['language'] = 'ca';
                return 'ca';
            }
        }
        
        // Idioma per defecte
        $_SESSION['language'] = 'es';
        return 'es';
    }

    // Funció per canviar idioma
    function setLanguage($lang) {
        if (in_array($lang, array('ca', 'es'))) {
            $_SESSION['language'] = $lang;
        }
    }

    // Funció per obtenir traducció
    function t($key) {
        global $translations;
        $lang = getCurrentLanguage();
        
        if (isset($translations[$lang][$key])) {
            return $translations[$lang][$key];
        }
        
        // Si no encuentra la traducción, devuelve la clave
        return $key;
    }

    // Inicializar sesión si no está iniciada
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // El processament del canvi d'idioma ara es fa a cada pàgina individual
    // per evitar conflictes amb altres processaments
?>