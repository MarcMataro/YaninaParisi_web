<?php
/**
 * Media Manager - Upload images and videos
 */
session_start();

// No-cache
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Auth check
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'No autenticado']);
        exit;
    }
    header('Location: index.php');
    exit;
}

require_once 'includes/role_check.php';

// Root paths
$ROOT = dirname(__DIR__);
    $IMG_DIR = $ROOT . '/img/media';
    $IMG_THUMBS = $IMG_DIR . '/thumbs';
    $VIDEO_DIR = $ROOT . '/img/videos';
    $META_FILE = __DIR__ . '/media_meta.json';

// Ensure directories exist
    if (!is_dir($IMG_DIR)) mkdir($IMG_DIR, 0755, true);
    if (!is_dir($IMG_THUMBS)) mkdir($IMG_THUMBS, 0755, true);
    if (!is_dir($VIDEO_DIR)) mkdir($VIDEO_DIR, 0755, true);

// Helper: sanitize filename
function safe_filename($name) {
    $name = basename($name);
    // replace spaces
    $name = preg_replace('/[^A-Za-z0-9._-]/', '_', $name);
    return $name;
}

// Simple debug logger (admin-only, small file) to help diagnose upload failures
function gmedia_log($msg) {
    $logfile = __DIR__ . '/gmedia_debug.log';
    $time = date('Y-m-d H:i:s');
    if (is_array($msg) || is_object($msg)) $msg = print_r($msg, true);
    @file_put_contents($logfile, "[{$time}] " . $msg . "\n", FILE_APPEND | LOCK_EX);
}

// Metadata helpers (store titles and other small metadata per filename)
function meta_load() {
    global $META_FILE;
    if (!is_file($META_FILE)) return [];
    $json = @file_get_contents($META_FILE);
    if (!$json) return [];
    $data = json_decode($json, true);
    return is_array($data) ? $data : [];
}

function meta_save($data) {
    global $META_FILE;
    @file_put_contents($META_FILE, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
}

// Simple picker modal output for TinyMCE/file browser integration
if (isset($_GET['picker']) && $_GET['picker'] == '1') {
    $images = array_values(array_filter(scandir($IMG_DIR), function($f) use ($IMG_DIR){ return is_file($IMG_DIR . '/' . $f) && $f[0] !== '.'; }));
    $meta = meta_load();
    ?><!DOCTYPE html>
    <html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Seleccionar imagen</title>
    <link rel="stylesheet" href="css/dashboard.css">
    <style>
    body{font-family:Arial,Helvetica,sans-serif;padding:12px;background:#fafafa}
    .grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:12px}
    .pick-card{cursor:pointer;border:1px solid #eee;padding:8px;border-radius:8px;background:white}
    .pick-card img{width:100%;height:100px;object-fit:cover;border-radius:6px}
    .pick-title{font-size:0.85rem;color:#444;overflow:hidden;white-space:nowrap;text-overflow:ellipsis;margin-top:6px}
    </style>
    </head><body>
    <h2>Selecciona una imagen</h2>
    <div class="grid">
    <?php foreach ($images as $f) {
    // use absolute URLs including application base path so editor iframe and parent resolve correctly
    $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
    // compute app base (one level up from _pcontrol)
    $appBase = rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'])), '/\\');
    if ($appBase === '/' || $appBase === '\\') $appBase = '';
    $base = $scheme . '://' . $_SERVER['HTTP_HOST'] . $appBase;
        $url = $base . '/img/media/' . rawurlencode($f);
        $title = $meta[$f]['title'] ?? $f;
        $thumb = file_exists($IMG_THUMBS . '/' . $f) ? ($base . '/img/media/thumbs/' . rawurlencode($f)) : $url;
        ?>
        <div class="pick-card" data-url="<?php echo $url; ?>" data-title="<?php echo htmlspecialchars($title, ENT_QUOTES); ?>" data-thumb="<?php echo $thumb; ?>">
            <img src="<?php echo $thumb; ?>" alt="<?php echo htmlspecialchars($title); ?>">
            <div class="pick-title"><?php echo htmlspecialchars($title); ?></div>
            <div class="pick-meta" style="font-size:0.75rem;color:#999;margin-top:6px;word-break:break-all;">
                <div>Thumb: <a href="<?php echo $thumb; ?>" target="_blank"><?php echo $thumb; ?></a></div>
                <div>Full: <a href="<?php echo $url; ?>" target="_blank"><?php echo $url; ?></a></div>
            </div>
        </div>
    <?php } ?>
    </div>
    <script>
    document.querySelectorAll('.pick-card').forEach(function(el){
        var img = el.querySelector('img');
        // log computed urls for debugging
        console.log('pick-card init', {url: el.dataset.url, thumb: el.dataset.thumb, title: el.dataset.title});
        // handle broken images
        if (img) {
            img.addEventListener('error', function(){
                console.warn('Thumbnail failed to load for', el.dataset.thumb);
                img.style.opacity = 0.3;
                img.style.filter = 'grayscale(100%)';
            });
        }
        el.addEventListener('click', function(){
            var url = this.getAttribute('data-url');
            var title = this.getAttribute('data-title');
            console.log('picker selected', {url: url, title: title});
            try { window.opener.postMessage({mediaUrl: url, alt: title}, '*'); } catch(e) { console.error('postMessage failed', e); }
            window.close();
        });
    });
    </script>
    </body></html>
    <?php
    exit;
}

// AJAX handlers
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    $action = $_POST['action'];

    if ($action === 'list') {
        $images = array_values(array_filter(scandir($IMG_DIR), function($f) use ($IMG_DIR){ return is_file($IMG_DIR . '/' . $f) && $f[0] !== '.'; }));
        $videos = array_values(array_filter(scandir($VIDEO_DIR), function($f) use ($VIDEO_DIR){ return is_file($VIDEO_DIR . '/' . $f) && $f[0] !== '.'; }));
        $outImages = [];
        $meta = meta_load();
        foreach ($images as $f) {
            $path = $IMG_DIR . '/' . $f;
            $mime = mime_content_type($path);
            $thumb = file_exists($IMG_THUMBS . '/' . $f) ? '../img/media/thumbs/' . rawurlencode($f) : '../img/media/' . rawurlencode($f);
            $outImages[] = [
                'name' => $f,
                'title' => isset($meta[$f]['title']) ? $meta[$f]['title'] : $f,
                'size' => filesize($path),
                'mime' => $mime,
                'url' => '../img/media/' . rawurlencode($f),
                'thumb' => $thumb,
                'modified' => filemtime($path)
            ];
        }
        $outVideos = [];
        // re-use meta for videos too
        foreach ($videos as $f) {
            $path = $VIDEO_DIR . '/' . $f;
            $mime = mime_content_type($path);
            $outVideos[] = [
                'name' => $f,
                'title' => isset($meta[$f]['title']) ? $meta[$f]['title'] : $f,
                'size' => filesize($path),
                'mime' => $mime,
                'url' => '../img/videos/' . rawurlencode($f),
                'modified' => filemtime($path)
            ];
        }
        echo json_encode(['success' => true, 'images' => $outImages, 'videos' => $outVideos]);
        exit;
    }

    if ($action === 'delete') {
        $type = $_POST['type'] ?? 'image';
        $file = $_POST['file'] ?? '';
        $file = safe_filename($file);
        $target = ($type === 'video') ? $VIDEO_DIR . '/' . $file : $IMG_DIR . '/' . $file;
        if (is_file($target)) {
            // delete thumbnail if exists
            if ($type !== 'video') {
                $thumb = $IMG_THUMBS . '/' . $file;
                if (is_file($thumb)) @unlink($thumb);
            }
            if (unlink($target)) echo json_encode(['success' => true]); else echo json_encode(['success' => false, 'message' => 'No s\'ha pogut eliminar']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Arxiu no trobat']);
        }
        exit;
    }

    if ($action === 'upload') {
        $results = ['uploaded' => [], 'errors' => []];
        // Log incoming files for debugging (admin-only)
        gmedia_log(['action'=>'upload_start','_FILES_keys'=>array_keys($_FILES)]);
        // log PHP upload error codes for each file (UPLOAD_ERR_*) to diagnose failures like exceeding php.ini limits
        $files_summary = [];
        foreach ($_FILES as $k => $v) {
            if (is_array($v['name'])) {
                for ($j = 0; $j < count($v['name']); $j++) {
                    $files_summary[] = ['key'=>$k, 'name'=>$v['name'][$j] ?? '', 'error'=>($v['error'][$j] ?? null)];
                }
            } else {
                $files_summary[] = ['key'=>$k, 'name'=>$v['name'] ?? '', 'error'=>($v['error'] ?? null)];
            }
        }
        gmedia_log(['_FILES_summary' => $files_summary]);
        // size limits (bytes)
        $max_image = 12 * 1024 * 1024; // 12 MB
        $max_video = 300 * 1024 * 1024; // 300 MB
        foreach ($_FILES as $fkey => $finfo) {
            // normalize
            if (!is_array($finfo['name'])) {
                $names = [$finfo['name']];
                $temps = [$finfo['tmp_name']];
                $types = [$finfo['type']];
                $sizes = [$finfo['size']];
            } else {
                $names = $finfo['name'];
                $temps = $finfo['tmp_name'];
                $types = $finfo['type'];
                $sizes = $finfo['size'];
            }
            for ($i=0;$i<count($names);$i++) {
                $orig = $names[$i];
                $tmp = $temps[$i];
                $type = $types[$i];
                $size = $sizes[$i];
                // detailed debug: log incoming file metadata (no file contents)
                gmedia_log(['event'=>'processing_file','orig'=>$orig,'tmp'=>$tmp,'php_type'=>$type,'size'=>$size]);
                if (!is_uploaded_file($tmp)) { 
                    $results['errors'][] = ['file'=>$orig,'message'=>'Upload error']; 
                    gmedia_log(['event'=>'not_uploaded_file','file'=>$orig,'tmp'=>$tmp,'fkey'=>$fkey]);
                    continue; 
                }
                $ext = strtolower(pathinfo($orig, PATHINFO_EXTENSION));
                $safe = time() . '_' . safe_filename($orig);
                $finfo_obj = finfo_open(FILEINFO_MIME_TYPE);
                $mime = finfo_file($finfo_obj, $tmp);
                finfo_close($finfo_obj);
                $image_mimes = ['image/jpeg','image/png','image/gif','image/webp','image/avif','image/svg+xml'];
                $video_mimes = ['video/mp4','video/webm','video/ogg','video/quicktime','video/x-matroska'];
                if (in_array($mime, $image_mimes)) {
                    if ($size > $max_image) { $results['errors'][] = ['file'=>$orig,'message'=>'Imagen demasiado grande']; continue; }
                    // basic image validation
                    $imginfo = @getimagesize($tmp);
                        if ($imginfo === false) { 
                            $results['errors'][] = ['file'=>$orig,'message'=>'Archivo no es imagen válida']; 
                            gmedia_log(['event'=>'invalid_image','file'=>$orig,'tmp'=>$tmp,'mime_detected'=>$mime]);
                            continue; 
                        }
                    $dest = $IMG_DIR . '/' . $safe;
                    if (move_uploaded_file($tmp, $dest)) {
                        // create thumbnail
                        $thumbPath = $IMG_THUMBS . '/' . $safe;
                        try {
                            $w = $imginfo[0]; $h = $imginfo[1];
                            $newW = 320; $newH = intval(($newW / $w) * $h);
                            $src = null;
                            switch ($mime) {
                                case 'image/jpeg': $src = imagecreatefromjpeg($dest); break;
                                case 'image/png': $src = imagecreatefrompng($dest); break;
                                case 'image/webp': $src = imagecreatefromwebp($dest); break;
                                case 'image/gif': $src = imagecreatefromgif($dest); break;
                                case 'image/avif': /* skip avif thumbnail if no support */ $src = null; break;
                                case 'image/svg+xml': $src = null; break;
                            }
                            if ($src) {
                                $thumb = imagecreatetruecolor($newW, $newH);
                                // preserve PNG transparency
                                if ($mime === 'image/png' || $mime === 'image/webp') {
                                    imagecolortransparent($thumb, imagecolorallocatealpha($thumb, 0, 0, 0, 127));
                                    imagealphablending($thumb, false);
                                    imagesavealpha($thumb, true);
                                }
                                imagecopyresampled($thumb, $src, 0,0,0,0,$newW,$newH,$w,$h);
                                imagejpeg($thumb, $thumbPath, 82);
                                imagedestroy($thumb);
                                imagedestroy($src);
                            }
                        } catch (Exception $e) {
                            // ignore thumbnail errors
                        }
                        $results['uploaded'][] = basename($dest);
                        // set default title metadata (original filename without timestamp prefix)
                        try {
                            $meta = meta_load();
                            $displayName = preg_replace('/^[0-9_]+_/', '', basename($dest));
                            $meta[basename($dest)] = ['title' => $displayName];
                            meta_save($meta);
                        } catch (Exception $e) {
                            gmedia_log(['event'=>'meta_save_error','error'=>$e->getMessage()]);
                        }
                    } else {
                        $err = error_get_last();
                        $results['errors'][] = ['file' => $orig, 'message' => 'Error al mover'];
                        gmedia_log(['event'=>'move_failed','file'=>$orig,'tmp'=>$tmp,'dest'=>$dest,'error'=>$err]);
                    }
                } elseif (in_array($mime, $video_mimes)) {
                    if ($size > $max_video) { $results['errors'][] = ['file'=>$orig,'message'=>'Video demasiado grande']; continue; }
                    $dest = $VIDEO_DIR . '/' . $safe;
                    if (move_uploaded_file($tmp, $dest)) {
                        $results['uploaded'][] = basename($dest);
                        // default title for videos too
                        try {
                            $meta = meta_load();
                            $displayName = preg_replace('/^[0-9_]+_/', '', basename($dest));
                            $meta[basename($dest)] = ['title' => $displayName];
                            meta_save($meta);
                        } catch (Exception $e) {
                            gmedia_log(['event'=>'meta_save_error_video','error'=>$e->getMessage()]);
                        }
                    } else {
                        $err = error_get_last();
                        $results['errors'][] = ['file' => $orig, 'message' => 'Error al mover'];
                        gmedia_log(['event'=>'move_failed_video','file'=>$orig,'tmp'=>$tmp,'dest'=>$dest,'error'=>$err]);
                    }
                } else {
                    $results['errors'][] = ['file' => $orig, 'message' => 'Tipo no permitido: ' . $mime];
                    gmedia_log(['event'=>'mime_not_allowed','file'=>$orig,'mime'=>$mime]);
                }
            }
        }
        echo json_encode(['success' => true, 'results' => $results]);
        exit;
    }

    // Set metadata for an existing file (title)
    if ($action === 'setmeta') {
        $file = $_POST['file'] ?? '';
        $title = $_POST['title'] ?? '';
        $type = $_POST['type'] ?? 'image';
        $file = safe_filename($file);
        $target = ($type === 'video') ? $VIDEO_DIR . '/' . $file : $IMG_DIR . '/' . $file;
        $doRename = isset($_POST['rename']) && ($_POST['rename'] === '1' || $_POST['rename'] === 'true');
        if (!is_file($target)) {
            echo json_encode(['success' => false, 'message' => 'Arxiu no trobat']);
            exit;
        }
        $meta = meta_load();
        $oldKey = $file;
        // perform rename on disk (file and thumb) if requested
        if ($doRename && $title !== '') {
            // create a safe new filename based on title, preserve extension
            $ext = pathinfo($file, PATHINFO_EXTENSION);
            $base = pathinfo($title, PATHINFO_FILENAME);
            $base = safe_filename($base);
            if ($base === '') $base = 'file';
            $newName = $base . '.' . $ext;
            // avoid overwriting existing file by appending suffix
            $counter = 1;
            while (is_file(($type === 'video' ? $VIDEO_DIR : $IMG_DIR) . '/' . $newName)) {
                $newName = $base . '_' . $counter . '.' . $ext;
                $counter++;
            }
            $newTarget = ($type === 'video' ? $VIDEO_DIR : $IMG_DIR) . '/' . $newName;
            $moved = false;
            try {
                // attempt to rename
                if (@rename($target, $newTarget)) {
                    $moved = true;
                    // move thumbnail if exists
                    if ($type !== 'video') {
                        $oldThumb = $IMG_THUMBS . '/' . $oldKey;
                        $newThumb = $IMG_THUMBS . '/' . $newName;
                        if (is_file($oldThumb)) {@rename($oldThumb, $newThumb);} 
                    }
                    // update metadata keys
                    $meta = meta_load();
                    $meta[$newName] = $meta[$oldKey] ?? [];
                    $meta[$newName]['title'] = $title;
                    if (isset($meta[$oldKey])) unset($meta[$oldKey]);
                    meta_save($meta);
                    echo json_encode(['success' => true, 'renamed' => true, 'old' => $oldKey, 'new' => $newName, 'title' => $title]);
                    exit;
                } else {
                    $err = error_get_last();
                    gmedia_log(['event'=>'rename_failed','file'=>$oldKey,'target'=>$newTarget,'error'=>$err]);
                    echo json_encode(['success' => false, 'message' => 'No s\'ha pogut renombrar el fitxer']);
                    exit;
                }
            } catch (Exception $e) {
                gmedia_log(['event'=>'rename_exception','error'=>$e->getMessage()]);
                echo json_encode(['success' => false, 'message' => 'Error al renombrar']);
                exit;
            }
        }
        // default: just update metadata title
        $meta[$file] = $meta[$file] ?? [];
        $meta[$file]['title'] = $title;
        meta_save($meta);
        echo json_encode(['success' => true, 'file' => $file, 'title' => $title]);
        exit;
    }

    // Info / stat for a single file (safe, no directory traversal)
    if ($action === 'info') {
        $type = $_POST['type'] ?? 'image';
        $file = $_POST['file'] ?? '';
        $file = safe_filename($file);
        $target = ($type === 'video') ? $VIDEO_DIR . '/' . $file : $IMG_DIR . '/' . $file;
        $exists = is_file($target);
        $thumb_exists = false;
        if ($type !== 'video' && $exists) {
            $thumb_exists = is_file($IMG_THUMBS . '/' . $file);
        }
        echo json_encode([
            'success' => true,
            'file' => $file,
            'exists' => $exists,
            'thumb_exists' => $thumb_exists,
            'url' => $exists ? ('../' . ($type === 'video' ? 'img/videos/' : 'img/media/') . rawurlencode($file)) : null,
            'thumb_url' => $thumb_exists ? ('../img/media/thumbs/' . rawurlencode($file)) : null
        ]);
        exit;
    }

    echo json_encode(['success' => false, 'message' => 'Acció desconeguda']);
    exit;
}

// If not AJAX, render the page
?><!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Gestor de Media</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@7.0.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Libre+Baskerville:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/gblog.css?v=2.2">
    <style>
    /* Small styles for media manager */
    .drop-zone { border: 2px dashed rgba(71,71,66,0.15); padding: 18px; border-radius: 10px; background: rgba(204,202,167,0.03); cursor: pointer; }
    .drop-zone.dragover { background: rgba(170,158,107,0.06); }
    .media-controls { display:flex; gap:12px; align-items:center; margin-bottom:12px; }
    .media-search { padding:8px 12px; border-radius:8px; border:1px solid #ddd; }
    .progress-row { margin-top:8px; }
    /* Simple modal */
    .mp-modal { position:fixed; inset:0; display:none; align-items:center; justify-content:center; background:rgba(0,0,0,0.6); z-index:2000; }
    .mp-modal.show { display:flex; }
    .mp-modal .mp-content { background:white; padding:18px; border-radius:10px; max-width:900px; width:95%; max-height:90vh; overflow:auto; }
    .mp-modal .mp-content img, .mp-modal .mp-content video { max-width:100%; height:auto; display:block; margin:0 auto; }
    /* Card layout improvements */
    .card { display:flex; flex-direction:column; gap:8px; padding:8px; min-height:160px; box-sizing:border-box; width:100%; }
    .card-media { position:relative; }
    .card-check { position:absolute; left:8px; top:8px; z-index:3; }
    .card-img { width:100%; height:120px; object-fit:cover; border-radius:8px; display:block; }
    .card-video-placeholder { position:relative; display:flex; align-items:center; justify-content:center; height:120px; border-radius:8px; background:#111; color:white; }
    .card-video-placeholder i { font-size:34px; opacity:0.9; }
    .card-bottom { display:flex; flex-direction:column; align-items:stretch; gap:6px; }
    .card-title { display:block; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; font-size:0.85rem; color:#666; margin:0; }
    .card-actions { display:flex; gap:6px; justify-content:flex-start; }
    /* Grid container for media items — responsive rows */
    /* Desktop: 6 cards per row; responsive down to 1 on small screens */
    /* Use minmax(0,1fr) to prevent overflow due to content/gap rounding in some browsers */
    #mediaGridImages, #mediaGridVideos { display:grid; grid-template-columns: repeat(6, minmax(0, 1fr)); grid-auto-rows: minmax(140px, auto); gap:14px; align-items:start; box-sizing: border-box; max-width:100%; }
    @media (max-width:1200px) {
        #mediaGridImages, #mediaGridVideos { grid-template-columns: repeat(4, minmax(0, 1fr)); }
    }
    @media (max-width:900px) {
        #mediaGridImages, #mediaGridVideos { grid-template-columns: repeat(3, minmax(0, 1fr)); }
    }
    @media (max-width:600px) {
        #mediaGridImages, #mediaGridVideos { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
    @media (max-width:420px) {
        #mediaGridImages, #mediaGridVideos { grid-template-columns: repeat(1, minmax(0, 1fr)); }
    }

    /* ensure progress list doesn't affect grid layout */
    #progressList { margin-top:8px; }

    /* Edit row styles */
    .edit-row { display:flex; gap:8px; align-items:center; margin-top:6px; }
    .edit-row .edit-input { flex:1 1 auto; min-width:0; padding:6px 8px; border:1px solid #ddd; border-radius:6px; box-sizing:border-box; }
    .edit-row .btn-small { white-space:nowrap; }
    /* Ensure card-bottom shows overflow for edit controls */
    .card-bottom { overflow:visible; }
    </style>
</head>
<body>
    <link rel="icon" type="image/png" sizes="32x32" href="../img/Logo32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../img/Logo16.png">
    <?php include __DIR__ . '/includes/sidebar.php'; ?>

    <div class="main-content">
        <!-- Top Bar (copied from dashboard.php to ensure consistent layout) -->
        <header class="top-bar">
            <div class="top-bar-left">
                <button class="menu-toggle" id="menuToggle">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="top-bar-info">
                    <h1><i class="fas fa-camera"></i> Gestión de los archivos multimedia</h1>
                    <p class="page-description">Sube aquí tus fotos y vídeos para mostrar en la web.</p>
                </div>
            </div>
            <div class="top-bar-right">
                <div class="user-profile">
                    <img src="../img/Logo.png" alt="Profile" class="profile-img">
                    <span class="profile-name"><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Usuario'); ?></span>
                </div>
            </div>
        </header>

        <!-- Gestor dels arxius media -->
        <div class="content-wrapper" style="margin-top: 8px;">
            <div class="card">
                <div class="card-header">
                    <h2><i class="fas fa-photo-video"></i> Gestor de Media</h2>
                    <div>
                        <!-- placeholder actions -->
                    </div>
                </div>
                <div class="card-body">
                    <div class="media-controls">
                        <div style="flex:1">
                            <div id="dropZone" class="drop-zone">
                                <strong>Arrastra y suelta archivos aquí</strong>
                                <div style="font-size:0.85rem;color:#666;">También puedes usar el botón "Seleccionar archivos"</div>
                            </div>
                            <input id="fileInput" type="file" name="media[]" multiple style="display:none;" accept="image/*,video/*">
                        </div>
                        <div style="display:flex;gap:8px;align-items:center;">
                            <input id="searchInput" class="media-search" placeholder="Buscar por nombre...">
                                <select id="sortSelect" class="media-search" style="width:180px;">
                                    <option value="date_desc">Ordenar: Fecha ↓</option>
                                    <option value="date_asc">Fecha ↑</option>
                                    <option value="name_asc">Nombre A→Z</option>
                                    <option value="name_desc">Nombre Z→A</option>
                                    <option value="size_desc">Tamaño ↓</option>
                                    <option value="size_asc">Tamaño ↑</option>
                                </select>
                                <button id="btnSelectAll" class="btn-small">Seleccionar todo</button>
                            <button id="btnDeleteSelected" class="btn-small" style="background:#c94b4b;">Eliminar seleccionados</button>
                            <button id="btnRefresh" class="btn-small">Refrescar</button>
                        </div>
                    </div>
                    <div id="uploadStatus" style="color:#666;font-size:0.95rem;margin-top:8px;"></div>
                    <div id="progressList"></div>

                    <h3 style="margin-top:6px;">Imágenes</h3>
                    <div id="mediaGridImages" style="margin-top:12px;"></div>
                    <div id="paginationImages" style="margin-top:14px;display:flex;gap:8px;align-items:center;justify-content:center;"></div>

                    <h3 style="margin-top:18px;">Vídeos</h3>
                    <div id="mediaGridVideos" style="margin-top:12px;"></div>
                    <div id="paginationVideos" style="margin-top:14px;display:flex;gap:8px;align-items:center;justify-content:center;"></div>
                </div>
            </div>
        </div>
    </div>

    <script src="js/dashboard.js"></script>
    <script src="js/gmedia.js"></script>
    <!-- Preview modal -->
    <div id="previewModal" class="mp-modal" aria-hidden="true">
        <div class="mp-content">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                <div>
                    <button id="btnCopyUrl" class="btn-small">Copiar URL</button>
                    <a id="btnOpenNew" class="btn-small" target="_blank" style="margin-left:8px;">Abrir en nueva pestaña</a>
                </div>
                <button id="btnClosePreview" class="btn-small">Cerrar</button>
            </div>
            <div id="previewInner"></div>
        </div>
    </div>
</body>
</html>
