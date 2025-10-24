<?php
/**
 * generate_sitemap.php
 *
 * Scans the project for public PHP pages (root, ca/, es/) and regenerates sitemap.xml
 * with <url> entries and hreflang alternates. Intended to be run from CLI or via cron.
 *
 * Usage (CLI): php scripts/generate_sitemap.php
 */

$baseUrl = 'https://yaninaparisi.com';

$projectRoot = realpath(__DIR__ . '/..');
if (!$projectRoot) {
    echo "Unable to determine project root.\n";
    exit(1);
}

// directories to ignore when scanning
$ignoreDirs = [
    'vendor', 'node_modules', 'classes', '_pcontrol', '_secret', 'includes', '_includes', 'ca/_includes', 'es/_includes'
];

// collect php files
$rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($projectRoot));
$pages = [];

foreach ($rii as $file) {
    if ($file->isDir()) continue;
    $path = $file->getPathname();
    if (substr($path, -4) !== '.php') continue;

    // normalize to forward-slash
    $npath = str_replace('\\', '/', $path);

    // skip files in ignored directories
    $skip = false;
    foreach ($ignoreDirs as $d) {
        if (strpos($npath, '/' . $d . '/') !== false) { $skip = true; break; }
    }
    if ($skip) continue;

    // skip files starting with underscore
    $basename = basename($npath);
    if (strpos($basename, '_') === 0) continue;

    // determine language and slug
    $rel = substr($npath, strlen($projectRoot));
    $rel = ltrim(str_replace('\\','/',$rel), '/');

    $parts = explode('/', $rel);
    $lang = 'root';
    if ($parts[0] === 'ca' || $parts[0] === 'es') {
        $lang = array_shift($parts); // 'ca' or 'es'
    }

    $fileName = array_pop($parts);
    // normalize index/home to 'home'
    $slug = $fileName;

    // group by the filename (e.g., clinica.php). For root index.php use 'index'
    $key = strtolower($fileName);

    if (!isset($pages[$key])) $pages[$key] = [];
    $pages[$key][$lang] = [
        'path' => $npath,
        'rel' => $rel,
        'mtime' => filemtime($path)
    ];
}

// Helper to format date
function fmtDate($ts) { return date('Y-m-d', $ts); }

// build sitemap entries
$entries = [];
foreach ($pages as $file => $variants) {
    // Skip backend-like pages that are not public by name heuristics
    if (preg_match('#^_(.*)#', $file)) continue;

    // Determine primary loc: prefer ca version if available, else root, else es
    if (isset($variants['ca'])) {
        $primary = ['lang'=>'ca','info'=>$variants['ca']];
    } elseif (isset($variants['root'])) {
        $primary = ['lang'=>'root','info'=>$variants['root']];
    } else {
        $primary = ['lang'=>'es','info'=>$variants['es']];
    }

    // construct alternates
    $alternates = [];
    if (isset($variants['ca'])) {
        $alternates['ca'] = $variants['ca'];
    } elseif (isset($variants['root']) && file_exists($projectRoot . '/ca/' . $file)) {
        // root with ca file existing as counterpart
        $alternates['ca'] = ['rel' => 'ca/' . $file, 'path' => $projectRoot . '/ca/' . $file, 'mtime' => file_exists($projectRoot . '/ca/' . $file) ? filemtime($projectRoot . '/ca/' . $file) : $variants['root']['mtime']];
    }
    if (isset($variants['es'])) {
        $alternates['es'] = $variants['es'];
    } elseif (isset($variants['root']) && file_exists($projectRoot . '/es/' . $file)) {
        $alternates['es'] = ['rel' => 'es/' . $file, 'path' => $projectRoot . '/es/' . $file, 'mtime' => file_exists($projectRoot . '/es/' . $file) ? filemtime($projectRoot . '/es/' . $file) : $variants['root']['mtime']];
    }

    // Build url entry data
    // compute lastmod as the newest mtime among variants
    $mtimes = array_map(function($v){ return $v['mtime']; }, $variants);
    $lastmodTs = max($mtimes);

    // heuristics for changefreq/priority
    $name = strtolower($file);
    $changefreq = 'monthly';
    $priority = '0.7';
    if (preg_match('#(index|home)#', $name)) { $changefreq = 'weekly'; $priority = '1.0'; }
    if (preg_match('#(blog)#', $name)) { $changefreq = 'weekly'; $priority = '0.6'; }
    if (preg_match('#(contact|contacta|contacte)#', $name)) { $changefreq = 'monthly'; $priority = '0.9'; }

    $entries[] = [
        'primary' => $primary,
        'alternates' => $alternates,
        'lastmod' => fmtDate($lastmodTs),
        'changefreq' => $changefreq,
        'priority' => $priority,
        'file' => $file
    ];
}

// Ensure root/home appears at top
usort($entries, function($a,$b){
    $aName = $a['file']; $bName = $b['file'];
    if (preg_match('#^(index|home)#', $aName)) return -1;
    if (preg_match('#^(index|home)#', $bName)) return 1;
    return strcmp($aName,$bName);
});

// build XML
$xml = '<?xml version="1.0" encoding="UTF-8"?>\n';
$xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">\n\n';

foreach ($entries as $e) {
    // build primary loc URL
    $p = $e['primary']['info'];
    $rel = $p['rel'];
    // normalize rel to URL path
    $urlPath = str_replace('\\','/',$rel);
    // handle root index special case
    if (preg_match('#(^|/)index.php$#', $urlPath) || $urlPath === 'index.php') {
        $loc = $baseUrl . '/';
    } else {
        // ensure leading slash
        $loc = $baseUrl . '/' . ltrim($urlPath, '/');
    }

    $xml .= "    <url>\n";
    $xml .= "        <loc>{$loc}</loc>\n";
    $xml .= "        <lastmod>{$e['lastmod']}</lastmod>\n";
    $xml .= "        <changefreq>{$e['changefreq']}</changefreq>\n";
    $xml .= "        <priority>{$e['priority']}</priority>\n";

    // alternates: include ca and es where possible
    // prefer canonical absolute paths for alternates
    if (!empty($e['alternates'])) {
        foreach ($e['alternates'] as $lang => $info) {
            $altRel = isset($info['rel']) ? $info['rel'] : $info['rel'] ?? '';
            // compute href
            $href = $baseUrl . '/' . ltrim($info['rel'], '/');
            $xml .= "        <xhtml:link rel=\"alternate\" hreflang=\"{$lang}\" href=\"{$href}\"/>\n";
        }
    } else {
        // no alternates found: try to infer counterpart paths
        // skip
    }

    $xml .= "    </url>\n\n";
}

$xml .= '</urlset>\n';

$sitemapPath = $projectRoot . DIRECTORY_SEPARATOR . 'sitemap.xml';
if (file_put_contents($sitemapPath, $xml) === false) {
    echo "Failed to write sitemap to {$sitemapPath}\n";
    exit(1);
}

echo "Sitemap written to: {$sitemapPath}\n";
exit(0);
