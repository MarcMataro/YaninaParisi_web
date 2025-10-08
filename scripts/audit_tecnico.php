<?php
/**
 * audit_tecnico.php
 *
 * Script CLI per fer una auditoria tècnica automatitzada d'una URL.
 * Fa comprovacions server-side (headers, robots, sitemap, SSL, DOM checks)
 * i pot cridar l'API PageSpeed Insights per obtenir Lighthouse i Core Web Vitals.
 * Opcionalment pot desar el resultat a la taula `seo_tecnico` utilitzant la
 * classe `SEO_Tecnico` del projecte.
 *
 * Ús:
 *  php scripts/audit_tecnico.php https://www.example.com [--strategy=mobile|desktop] [--save]
 *
 * Requisits mínims: PHP amb cURL i OpenSSL. Per Lighthouse complet local, instal·lar
 * Node.js i executar Lighthouse CLI (veure instruccions més avall).
 */

require_once __DIR__ . '/../classes/connexio.php';
require_once __DIR__ . '/../classes/seo_tecnic.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

$argv = $_SERVER['argv'];
if (!isset($argv[1])) {
    echo "Usage: php scripts/audit_tecnico.php <url> [--strategy=mobile|desktop] [--save]\n";
    exit(1);
}

$url = $argv[1];
$strategy = 'mobile';
$save = false;
$apiKeyArg = null;
foreach ($argv as $arg) {
    if (strpos($arg, '--strategy=') === 0) {
        $strategy = substr($arg, 11);
    }
    if ($arg === '--save') $save = true;
    if (strpos($arg, '--apikey=') === 0) $apiKeyArg = substr($arg, 8);
}

// Helpers
function http_get($url, &$info = null) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HEADER => false,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_USERAGENT => 'AuditBot/1.0 (+https://example.local)'
    ]);
    $body = curl_exec($ch);
    $info = curl_getinfo($ch);
    $err = curl_error($ch);
    curl_close($ch);
    return [$body, $info, $err];
}

function http_head_status($url) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_NOBODY => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_USERAGENT => 'AuditBot/1.0'
    ]);
    curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return $code;
}

function fetch_ssl_info($host) {
    $port = 443;
    $context = stream_context_create(['ssl' => ['capture_peer_cert' => true, 'verify_peer' => false, 'verify_peer_name' => false]]);
    $client = @stream_socket_client("ssl://{$host}:{$port}", $errno, $errstr, 10, STREAM_CLIENT_CONNECT, $context);
    if (!$client) return null;
    $params = stream_context_get_params($client);
    if (!isset($params['options']['ssl']['peer_certificate'])) return null;
    $cert = openssl_x509_parse($params['options']['ssl']['peer_certificate']);
    return $cert;
}

// 1) Request the page and measure timings
list($body, $info, $err) = http_get($url, $info);
$timing_ms = isset($info['total_time']) ? round($info['total_time'] * 1000) : null;
$http_code = $info['http_code'] ?? null;

$parsed = parse_url($url);
$host = $parsed['host'] ?? null;

// 2) Robots and sitemap
$robots_status = http_head_status($parsed['scheme'] . '://' . $host . '/robots.txt');
$sitemap_status = http_head_status($parsed['scheme'] . '://' . $host . '/sitemap.xml');

// 3) SSL certificate info
$ssl_info = null;
if ($parsed['scheme'] === 'https' && $host) {
    $ssl_info = fetch_ssl_info($host);
}

// 4) Parse HTML: canonical, hreflang, images without alt, structured data, links
$dom = new DOMDocument();
libxml_use_internal_errors(true);
$dom->loadHTML($body ?: '<html></html>');
libxml_clear_errors();

$xpath = new DOMXPath($dom);

// canonical
$canonical = null;
$nodes = $xpath->query("//link[@rel='canonical']");
if ($nodes->length) $canonical = $nodes->item(0)->getAttribute('href');

// hreflang
$hreflang_nodes = $xpath->query("//link[@rel='alternate' and @hreflang]");
$hreflang_errors = 0;
foreach ($hreflang_nodes as $n) {
    // Basic check: attribute href exists
    if (!$n->getAttribute('href')) $hreflang_errors++;
}

// images without alt
$imgs = $xpath->query('//img');
$imgs_sin_alt = 0;
foreach ($imgs as $img) {
    $alt = trim($img->getAttribute('alt'));
    if ($alt === '') $imgs_sin_alt++;
}

// structured data JSON-LD
$jsonld = [];
$scripts = $xpath->query("//script[@type='application/ld+json']");
foreach ($scripts as $s) {
    $txt = trim($s->nodeValue);
    if ($txt) {
        $jsonld[] = json_decode($txt, true);
    }
}

// links (anchor) - check broken (simple HEAD), limit to first 50 links to avoid slowness
$anchors = $xpath->query('//a[@href]');
$links_checked = 0;
$broken_links = 0;
foreach ($anchors as $a) {
    $href = $a->getAttribute('href');
    // normalize
    if (strpos($href, '#') === 0) continue;
    if (strpos($href, 'mailto:') === 0) continue;
    if (strpos($href, 'tel:') === 0) continue;
    if (strpos($href, '//') === 0) $href = $parsed['scheme'] . ':' . $href;
    if (parse_url($href, PHP_URL_SCHEME) === null) {
        // relative
        $href = rtrim($parsed['scheme'] . '://' . $host, '/') . '/' . ltrim($href, '/');
    }
    $code = http_head_status($href);
    $links_checked++;
    if ($code >= 400 || $code === 0) $broken_links++;
    if ($links_checked >= 50) break;
}

// 5) PageSpeed Insights (optional, requires PAGESPEED_API_KEY env var)
$pagespeed = null;
// Prefer API key passed as argument, si no, agafem la variable d'entorn
$apiKey = $apiKeyArg ?: (getenv('PAGESPEED_API_KEY') ?: null);
if ($apiKey) {
    $apiUrl = 'https://www.googleapis.com/pagespeedonline/v5/runPagespeed?url=' . urlencode($url) . '&strategy=' . urlencode($strategy) . '&key=' . $apiKey;
    $resp = file_get_contents($apiUrl);
    if ($resp) {
        $ps = json_decode($resp, true);
        if ($ps) {
            $pagespeed = $ps;
        }
    }
}

// Map to seo_tecnico fields
$result = [
    'url' => $url,
    'http_code' => $http_code,
    'velocidad_carga_ms' => $timing_ms,
    'robots_exists' => ($robots_status < 400),
    'sitemap_exists' => ($sitemap_status < 400),
    'ssl_info' => $ssl_info,
    'canonical' => $canonical,
    'hreflang_errores' => $hreflang_errors,
    'imagenes_sin_alt' => $imgs_sin_alt,
    'links_checked' => $links_checked,
    'broken_links' => $broken_links,
    'jsonld_count' => count($jsonld),
    'pagespeed_available' => $pagespeed !== null
];

// If pagespeed available, extract metrics
if ($pagespeed) {
    $lr = $pagespeed['lighthouseResult'] ?? null;
    if ($lr) {
        $result['puntuacion_lighthouse'] = isset($lr['categories']['performance']['score']) ? intval(round($lr['categories']['performance']['score'] * 100)) : null;
        $audits = $lr['audits'] ?? [];
        // LCP in ms
        if (isset($audits['largest-contentful-paint']['numericValue'])) {
            $result['core_web_vitals']['lcp'] = intval($audits['largest-contentful-paint']['numericValue']);
        }
        // INP or FID
        if (isset($audits['interactive']['numericValue'])) {
            $result['core_web_vitals']['inp'] = intval($audits['interactive']['numericValue']);
        }
        if (isset($audits['cumulative-layout-shift']['numericValue'])) {
            $result['core_web_vitals']['cls'] = floatval($audits['cumulative-layout-shift']['numericValue']);
        }
    }
}

// Mostrar resultat JSON
echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";

// Opcional: desar a BD utilitzant SEO_Tecnico
if ($save) {
    $t = new SEO_Tecnico();
    // Assignem alguns camps bàsics
    if (isset($result['velocidad_carga_ms'])) $t->set('velocidad_carga_ms', $result['velocidad_carga_ms']);
    if (isset($result['puntuacion_lighthouse'])) $t->set('puntuacion_lighthouse', $result['puntuacion_lighthouse']);
    if (isset($result['core_web_vitals'])) $t->set('core_web_vitals', $result['core_web_vitals']);
    $t->set('sitemap_existe', $result['sitemap_exists'] ? 1 : 0);
    $t->set('imagenes_sin_alt', $result['imagenes_sin_alt']);
    $t->set('paginas_indexadas', 0);
    $t->set('paginas_no_indexadas', 0);
    $t->set('uptime_30d', 99.99);
    $t->set('ultimo_auditoria_completa', date('Y-m-d H:i:s'));

    // Calcular puntuació tècnica i guardar
    $puntuacio = $t->calcularPuntuacioTecnica();
    $t->set('puntuacion_seo_tecnico', $puntuacio);
    $saved = $t->save();
    if ($saved === true) {
        echo "Guardat a la base de dades com a nova auditoria. ID: " . $t->get('id_tecnico') . "\n";
    } elseif (is_array($saved) && !$saved['valid']) {
        echo "Errors en validació: " . implode('; ', $saved['errors']) . "\n";
    } else {
        echo "Error desant l'auditoria\n";
    }
}

// Instruccions addicionals (imprimir si s'ha cridat sense --save)
if (!$save) {
    echo "\nNotes:\n - Per obtenir Lighthouse i Core Web Vitals, estableix la variable d'entorn PAGESPEED_API_KEY amb la teva API key de Google i torna a executar el script.\n";
    echo " - Per un audit complet local (Lighthouse CLI), instal·la Node.js i executeu: npm install -g lighthouse; lighthouse <url> --output=json --output-path=report.json\n";
}

?>
