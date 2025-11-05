<?php
    // Funció per obtenir el idioma actual
    if (!function_exists('getCurrentLanguage')) {
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
    }

    // Funció per canviar idioma
    if (!function_exists('setLanguage')) {
    function setLanguage($lang) {
        if (in_array($lang, array('ca', 'es'))) {
            $_SESSION['language'] = $lang;
        }
    }
    }

    // Funció per obtenir traducció
    if (!function_exists('t')) {
    function t($key) {
        global $translations;
        $lang = getCurrentLanguage();
        
        if (isset($translations[$lang][$key])) {
            return $translations[$lang][$key];
        }
        
        // Si no encuentra la traducción, devuelve la clave
        return $key;
    }
    }

    /**
     * Resolve a stored media path into an absolute URL that will work from any page.
     * Accepts:
     *  - absolute URLs (http:// or https://) -> returned as-is
     *  - protocol-relative URLs (//host/...) -> returned as-is
     *  - root-relative paths (/yaninaparisi/img/...) -> converted to absolute with origin
     *  - relative paths (img/media/..., media/...) -> converted to absolute using the app base
     */
    function resolve_media_url($path) {
        if (empty($path)) return '';
        $path = trim($path);
        // absolute URL
        if (preg_match('#^https?://#i', $path) || strpos($path, '//') === 0) return $path;

        // origin
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $origin = $scheme . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');

        // app base: two levels up from script (e.g. /yaninaparisi/ca/script.php -> /yaninaparisi)
        $appBase = rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'])), '/\\');
        if ($appBase === '/' || $appBase === '.') $appBase = '';

        // root-relative path
        if (strpos($path, '/') === 0) {
            return rtrim($origin, '/') . $path;
        }

        // already relative with ../ - let browser resolve relative paths
        if (strpos($path, '../') === 0) return $path;

        // otherwise build absolute using appBase
        return rtrim($origin, '/') . $appBase . '/' . ltrim($path, '/');
    }

    // Inicializar sesión si no está iniciada
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // El processament del canvi d'idioma ara es fa a cada pàgina individual
    // per evitar conflictes amb altres processaments
    
    /**
     * Render breadcrumbs (HTML) and emit JSON-LD BreadcrumbList for rich snippets.
     *
    * Usage: provide an array of items where each item is [ 'label' => 'Home', 'url' => 'home.php' ]
     * The last item may omit 'url' or set it to null (current page).
     *
     * Example:
     *   render_breadcrumbs([
    *     ['label' => t('nav_home'), 'url' => 'home.php'],
     *     ['label' => t('nav_blog'), 'url' => '/ca/blog.php'],
     *     ['label' => htmlspecialchars($post_title)],
     *   ]);
     *
     * This will print a <nav aria-label="Breadcrumb"> with an ordered list and
     * a <script type="application/ld+json"> with the BreadcrumbList structure.
     */
    function render_breadcrumbs(array $items, array $opts = []) {
        // Build origin (scheme + host)
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $origin = $scheme . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');

        // Compute script base (project prefix) so root-relative paths can be adjusted when site is in a subfolder
        $scriptBase = dirname($_SERVER['SCRIPT_NAME']);
        if ($scriptBase === '/' || $scriptBase === '.') $scriptBase = '';

        // Normalize a path to absolute URL, taking into account project subfolder
        $normalize = function($path) use ($origin, $scriptBase) {
            if (!$path) return null;
            if (strpos($path, 'http://') === 0 || strpos($path, 'https://') === 0) return $path;
            // If starts with slash, it's root-relative -> prepend script base
            if (strpos($path, '/') === 0) {
                return rtrim($origin, '/') . rtrim($scriptBase, '/') . $path;
            }
            // Otherwise treat as relative to script folder
            $p = '/' . trim($scriptBase, '/') . '/' . ltrim($path, '/');
            return rtrim($origin, '/') . $p;
        };

        // HTML output
        echo '<nav class="breadcrumbs" aria-label="Breadcrumb">';
        echo '<ol itemscope itemtype="http://schema.org/BreadcrumbList" style="list-style:none;padding:0;margin:0;display:flex;gap:.5rem;flex-wrap:wrap;">';

        $position = 1;
        $jsonItems = [];
        $total = count($items);

        foreach ($items as $i => $it) {
            $label = isset($it['label']) ? $it['label'] : '';
            $url = isset($it['url']) ? $it['url'] : null;
            $isLast = ($i === $total - 1);

            // If the label looks like a translation key (e.g., 'nav_home') try to translate it here.
            if (is_string($label) && preg_match('/^nav_/', $label)) {
                // Attempt to translate via t(); if translation missing, fall back to sane defaults per language
                $translated = function_exists('t') ? t($label) : $label;
                if ($translated === $label) {
                    // provide fallback defaults
                    $lang = function_exists('getCurrentLanguage') ? getCurrentLanguage() : 'es';
                    $defaults = [
                        'ca' => [
                            'nav_home' => 'Inici',
                            'nav_blog' => 'Blog',
                            'nav_contact' => 'Contacte',
                            'nav_services' => 'Clínica',
                            'nav_about' => 'Sobre mi',
                            'nav_couple_search' => 'Les dues ànimes',
                            'nav_appointment' => 'Demana una cita'
                        ],
                        'es' => [
                            'nav_home' => 'Inicio',
                            'nav_blog' => 'Blog',
                            'nav_contact' => 'Contacto',
                            'nav_services' => 'Clínica',
                            'nav_about' => 'Sobre mí',
                            'nav_couple_search' => 'Dos almas',
                            'nav_appointment' => 'Pide una cita'
                        ]
                    ];
                    if (isset($defaults[$lang][$label])) {
                        $label = $defaults[$lang][$label];
                    } else {
                        // leave as-is if no default
                    }
                } else {
                    $label = $translated;
                }
            }

            // Decide whether this item is the 'home' breadcrumb so we can render an icon
            $isHomeCrumb = false;
            // If original item used the nav_home key
            if (isset($it['label']) && $it['label'] === 'nav_home') {
                $isHomeCrumb = true;
            } else {
                // Also accept common translated forms (Inici / Inicio / Home)
                $label_check = is_string($label) ? trim($label) : '';
                if (in_array($label_check, array('Inici', 'Inicio', 'Home', 'Inicio'))) {
                    $isHomeCrumb = true;
                }
            }

            echo '<li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem" style="margin:0;">';
            if ($url && !$isLast) {
                $abs = $normalize($url);
                // Build href that respects project base for root-relative paths
                if (strpos($url, 'http://') === 0 || strpos($url, 'https://') === 0) {
                    $hrefOut = $url;
                } elseif (strpos($url, '/') === 0) {
                    $hrefOut = rtrim($scriptBase, '/') . $url;
                    if ($hrefOut === '') $hrefOut = $url; // fallback
                } else {
                    $hrefOut = rtrim($scriptBase, '/') . '/' . ltrim($url, '/');
                }

                echo '<a href="' . htmlspecialchars($hrefOut) . '" itemprop="item" style="color:inherit;text-decoration:none;">';
                if ($isHomeCrumb) {
                    // Home icon with screen-reader-only label
                    echo '<span class="breadcrumb-home" itemprop="name"><i class="fas fa-home" aria-hidden="true"></i><span class="sr-only">' . htmlspecialchars($label) . '</span></span>';
                } else {
                    echo '<span itemprop="name">' . $label . '</span>';
                }
                echo '</a>';
                echo '<meta itemprop="position" content="' . $position . '" />';
                // Add to JSON-LD with absolute URL
                $jsonItems[] = [
                    '@type' => 'ListItem',
                    'position' => $position,
                    'name' => $label,
                    'item' => $abs
                ];
            } else {
                // Current item or no URL provided
                echo '<span aria-current="page" itemprop="item">';
                if ($isHomeCrumb) {
                    echo '<span class="breadcrumb-home" itemprop="name"><i class="fas fa-home" aria-hidden="true"></i><span class="sr-only">' . htmlspecialchars($label) . '</span></span>';
                } else {
                    echo '<span itemprop="name">' . $label . '</span>';
                }
                echo '</span>';
                echo '<meta itemprop="position" content="' . $position . '" />';
                $abs = $url ? $normalize($url) : null;
                $jsonItems[] = [
                    '@type' => 'ListItem',
                    'position' => $position,
                    'name' => $label,
                    'item' => $abs ?: ($origin . ($_SERVER['REQUEST_URI'] ?? '/'))
                ];
            }
            // Separator (visual)
            if (!$isLast) echo '<span aria-hidden="true" style="margin:0 0.5rem;">›</span>';
            echo '</li>';

            $position++;
        }

        echo '</ol></nav>';

        // JSON-LD output
        $ld = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $jsonItems
        ];
        echo '<script type="application/ld+json">' . json_encode($ld, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>';
    }
?>