<?php
/**
 * Explorador de Media - Gestió d'arxius i carpetes
 * 
 * Administradors poden veure tot /img
 * Altres usuaris només veuen la seva carpeta personal
 * 
 * @author Marc Mataró
 * @version 2.0.0
 * @date 2026-01-02
 */

session_start();

// Headers de seguretat
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Verificar autenticació
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

require_once 'includes/role_check.php';

/**
 * Optimitza una imatge si pesa més de 300KB
 * Redueix la qualitat per aconseguir menys de 500KB i estableix DPI a 72
 * 
 * @param string $filePath Ruta completa del fitxer
 * @return bool True si s'ha optimitzat, false si no era necessari o hi ha hagut error
 */
function optimizeImageIfNeeded($filePath) {
    // Comprovar si el fitxer existeix
    if (!file_exists($filePath)) {
        return false;
    }
    
    // Comprovar la mida del fitxer (300KB = 300 * 1024 bytes)
    $fileSize = filesize($filePath);
    if ($fileSize <= 300 * 1024) {
        return false; // No cal optimitzar
    }
    
    // Obtenir informació de la imatge
    $imageInfo = @getimagesize($filePath);
    if ($imageInfo === false) {
        return false; // No és una imatge vàlida
    }
    
    $mimeType = $imageInfo['mime'];
    $width = $imageInfo[0];
    $height = $imageInfo[1];
    
    // Carregar la imatge segons el tipus
    $image = null;
    switch ($mimeType) {
        case 'image/jpeg':
        case 'image/jpg':
            $image = @imagecreatefromjpeg($filePath);
            break;
        case 'image/png':
            $image = @imagecreatefrompng($filePath);
            break;
        case 'image/gif':
            $image = @imagecreatefromgif($filePath);
            break;
        case 'image/webp':
            $image = @imagecreatefromwebp($filePath);
            break;
        default:
            return false; // Format no suportat per optimització
    }
    
    if ($image === false) {
        return false;
    }
    
    // Crear una nova imatge amb les mateixes dimensions
    $optimizedImage = imagecreatetruecolor($width, $height);
    
    // Preservar transparència per PNG i GIF
    if ($mimeType === 'image/png' || $mimeType === 'image/gif') {
        imagealphablending($optimizedImage, false);
        imagesavealpha($optimizedImage, true);
        $transparent = imagecolorallocatealpha($optimizedImage, 0, 0, 0, 127);
        imagefilledrectangle($optimizedImage, 0, 0, $width, $height, $transparent);
    }
    
    // Copiar la imatge
    imagecopyresampled($optimizedImage, $image, 0, 0, 0, 0, $width, $height, $width, $height);
    
    // Establir resolució a 72 DPI si la funció està disponible
    if (function_exists('imageresolution')) {
        imageresolution($optimizedImage, 72, 72);
    }
    
    // Guardar amb compressió, intentant aconseguir menys de 500KB
    $tempPath = $filePath . '.tmp';
    $quality = 85; // Qualitat inicial
    $targetSize = 500 * 1024; // 500KB
    $saved = false;
    
    // Intentar amb diferents nivells de qualitat fins aconseguir menys de 500KB
    for ($quality = 85; $quality >= 50; $quality -= 5) {
        $saved = false;
        
        switch ($mimeType) {
            case 'image/jpeg':
            case 'image/jpg':
                $saved = imagejpeg($optimizedImage, $tempPath, $quality);
                break;
            case 'image/png':
                // PNG: nivell de compressió 0-9 (convertim qualitat JPEG a nivell PNG)
                $pngCompression = (int) round((100 - $quality) / 11);
                $saved = imagepng($optimizedImage, $tempPath, $pngCompression);
                break;
            case 'image/gif':
                $saved = imagegif($optimizedImage, $tempPath);
                break;
            case 'image/webp':
                $saved = imagewebp($optimizedImage, $tempPath, $quality);
                break;
        }
        
        if ($saved && file_exists($tempPath)) {
            $newSize = filesize($tempPath);
            if ($newSize < $targetSize) {
                // Èxit! Reemplaçar l'arxiu original
                imagedestroy($image);
                imagedestroy($optimizedImage);
                unlink($filePath);
                rename($tempPath, $filePath);
                chmod($filePath, 0644);
                return true;
            }
        }
        
        // Si no s'ha aconseguit, continuar amb menys qualitat
        if (file_exists($tempPath)) {
            unlink($tempPath);
        }
    }
    
    // Si no s'ha aconseguit amb qualitat 50, guardar igualment
    if ($saved && file_exists($tempPath)) {
        imagedestroy($image);
        imagedestroy($optimizedImage);
        unlink($filePath);
        rename($tempPath, $filePath);
        chmod($filePath, 0644);
        return true;
    }
    
    // Netejar recursos
    imagedestroy($image);
    imagedestroy($optimizedImage);
    if (file_exists($tempPath)) {
        unlink($tempPath);
    }
    
    return false;
}

// Obtenir informació de l'usuari
$userId = $_SESSION['user_id'] ?? 0;
$userRole = $_SESSION['user_role'] ?? 'editor';
$isAdmin = in_array($userRole, ['admin', 'superadmin']);

// Detectar mode picker (per seleccionar imatges des del blog)
$isPickerMode = isset($_GET['picker']) && $_GET['picker'] == '1';

// Directori base d'imatges
$imgBaseDir = realpath(__DIR__ . '/../img');
$imgBaseDir = str_replace('\\', '/', $imgBaseDir);

// En mode picker, determinar el comportament segons el rol i el paràmetre admin_picker
// Si és admin/superadmin i es passa admin_picker=1, pot veure tot img/
// En cas contrari, només veu la seva carpeta personal
if ($isPickerMode) {
    $adminPicker = isset($_GET['admin_picker']) && $_GET['admin_picker'] == '1';
    
    if ($isAdmin && $adminPicker) {
        // Admins amb admin_picker=1 poden veure tot /img
        $userBaseDir = $imgBaseDir;
        $defaultPath = '';
    } else {
        // Resta d'usuaris: només la seva carpeta personal
        $userBaseDir = $imgBaseDir . '/user_' . $userId;
        $defaultPath = 'user_' . $userId;
        $isAdmin = false; // Forçar comportament no-admin per usuaris normals
        
        // Crear carpeta si no existeix
        if (!is_dir($userBaseDir)) {
            if (@mkdir($userBaseDir, 0755, true)) {
                chmod($userBaseDir, 0755);
                file_put_contents($userBaseDir . '/.htaccess', "# Carpeta personal de l'usuari\nOptions +Indexes\n");
                chmod($userBaseDir . '/.htaccess', 0644);
            } else {
                error_log("No s'ha pogut crear la carpeta d'usuari: " . $userBaseDir);
            }
        }
    }
} else {
    // Mode normal: determinar directori inicial segons el rol
    if ($isAdmin) {
        // Admins poden veure tot /img
        $userBaseDir = $imgBaseDir;
        $defaultPath = '';
    } else {
        // Altres usuaris: només la seva carpeta
        $userBaseDir = $imgBaseDir . '/user_' . $userId;
        $defaultPath = 'user_' . $userId;
        
        // Crear carpeta si no existeix
        if (!is_dir($userBaseDir)) {
            if (@mkdir($userBaseDir, 0755, true)) {
                chmod($userBaseDir, 0755);
                file_put_contents($userBaseDir . '/.htaccess', "# Carpeta personal de l'usuari\nOptions +Indexes\n");
                chmod($userBaseDir . '/.htaccess', 0644);
            } else {
                error_log("No s'ha pogut crear la carpeta d'usuari: " . $userBaseDir);
            }
        }
    }
}

// Obtenir ruta sol·licitada
$requestedPath = $_GET['path'] ?? $defaultPath;
$requestedPath = trim($requestedPath, '/');

// Construir ruta completa
if (empty($requestedPath)) {
    $fullPath = $imgBaseDir;
} else {
    $fullPath = $imgBaseDir . '/' . $requestedPath;
}

// Normalitzar ruta (convertir barres, eliminar ..)
$fullPath = str_replace('\\', '/', $fullPath);
$fullPath = preg_replace('#/+#', '/', $fullPath); // Eliminar barres dobles

// Si el directori existeix, usar realpath per normalitzar
if (file_exists($fullPath)) {
    $normalizedPath = realpath($fullPath);
    if ($normalizedPath !== false) {
        $fullPath = str_replace('\\', '/', $normalizedPath);
    }
}

// Normalitzar directoris base també
$imgBaseDir = str_replace('\\', '/', $imgBaseDir);
$userBaseDir = str_replace('\\', '/', $userBaseDir);

// Seguretat: Verificar que la ruta està dins del directori permès
if (!$isAdmin) {
    // Usuaris no-admin només poden accedir a la seva carpeta
    if (strpos($fullPath, $userBaseDir) !== 0) {
        $fullPath = $userBaseDir;
        $requestedPath = 'user_' . $userId;
    }
} else {
    // Admins: verificar que és dins de /img
    if (strpos($fullPath, $imgBaseDir) !== 0) {
        $fullPath = $imgBaseDir;
        $requestedPath = '';
    }
}

// Verificar que la carpeta de destí existeix, si no, intentar crear-la
$folderCreationError = null;
if (!is_dir($fullPath)) {
    if ($fullPath === $userBaseDir || strpos($fullPath, $userBaseDir) === 0) {
        // Intentar crear la carpeta d'usuari amb permisos màxims temporalment
        $oldUmask = umask(0); // Permetre crear amb qualsevol permís
        
        if (@mkdir($fullPath, 0777, true)) {
            // Carpeta creada, ara ajustar permisos de manera segura
            @chmod($fullPath, 0755);
            
            // Crear .htaccess per permetre llistat
            $htaccessContent = "# Carpeta personal de l'usuari\nOptions +Indexes\n";
            @file_put_contents($fullPath . '/.htaccess', $htaccessContent);
            @chmod($fullPath . '/.htaccess', 0644);
            
            umask($oldUmask); // Restaurar umask
            
            error_log("Carpeta d'usuari creada correctament: $fullPath");
        } else {
            umask($oldUmask); // Restaurar umask en cas d'error
            
            // Obtenir més informació sobre el problema
            $error = error_get_last();
            $parentDir = dirname($fullPath);
            
            $errorDetails = [];
            if (!is_dir($parentDir)) {
                $errorDetails[] = "La carpeta pare no existeix: $parentDir";
            }
            if (is_dir($parentDir) && !is_writable($parentDir)) {
                $errorDetails[] = "No tens permisos d'escriptura a: $parentDir";
            }
            if ($error) {
                $errorDetails[] = $error['message'];
            }
            
            $folderCreationError = "No s'ha pogut crear la carpeta d'usuari. " . 
                                    (!empty($errorDetails) ? implode('. ', $errorDetails) : 'Contacta amb l\'administrador.');
            
            error_log("Error creant carpeta d'usuari: $fullPath. Detalls: " . implode(', ', $errorDetails));
        }
    } else {
        // Per altres carpetes, simplement marcar l'error
        $folderCreationError = "La carpeta no existeix: " . basename($fullPath);
        error_log("Carpeta no existent: $fullPath");
        // Redirigir a la carpeta pare si és possible
        $parentPath = dirname($requestedPath);
        $fullPath = $imgBaseDir . ($parentPath !== '.' ? '/' . $parentPath : '');
        if (!is_dir($fullPath)) {
            $fullPath = $isAdmin ? $imgBaseDir : $userBaseDir;
        }
    }
}

// Processar accions AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    // Iniciar output buffering per evitar que warnings trenquin el JSON
    ob_start();
    
    header('Content-Type: application/json');
    
    $action = $_POST['action'];
    
    switch ($action) {
        case 'create_folder':
            $folderName = trim($_POST['folder_name'] ?? '');
            $folderName = preg_replace('/[^A-Za-z0-9_-]/', '_', $folderName);
            
            if (empty($folderName)) {
                ob_end_clean();
                echo json_encode(['success' => false, 'message' => 'Nom de carpeta no vàlid']);
                exit;
            }
            
            // Verificar que la carpeta pare existeix
            if (!is_dir($fullPath)) {
                ob_end_clean();
                error_log("Carpeta pare no existeix: $fullPath");
                echo json_encode(['success' => false, 'message' => 'La carpeta pare no existeix: ' . $fullPath]);
                exit;
            }
            
            // Verificar permisos d'escriptura
            if (!is_writable($fullPath)) {
                ob_end_clean();
                error_log("No hi ha permisos d'escriptura a: $fullPath");
                echo json_encode(['success' => false, 'message' => 'No tens permisos d\'escriptura a la carpeta pare']);
                exit;
            }
            
            $newFolderPath = $fullPath . '/' . $folderName;
            
            if (file_exists($newFolderPath)) {
                ob_end_clean();
                echo json_encode(['success' => false, 'message' => 'La carpeta ja existeix']);
                exit;
            }
            
            if (@mkdir($newFolderPath, 0755)) {
                chmod($newFolderPath, 0755);
                ob_end_clean();
                echo json_encode(['success' => true, 'message' => 'Carpeta creada']);
            } else {
                $error = error_get_last();
                $errorMsg = 'Error creant carpeta';
                
                if ($error && isset($error['message'])) {
                    $errorMsg .= ': ' . $error['message'];
                }
                
                error_log("Error creant carpeta $newFolderPath: " . ($error['message'] ?? 'desconegut'));
                ob_end_clean();
                echo json_encode(['success' => false, 'message' => $errorMsg]);
            }
            exit;
            
        case 'upload':
            if (!isset($_FILES['files'])) {
                echo json_encode(['success' => false, 'message' => 'No hi ha fitxers']);
                exit;
            }
            
            $uploaded = [];
            $errors = [];
            
            $files = $_FILES['files'];
            $fileCount = count($files['name']);
            
            for ($i = 0; $i < $fileCount; $i++) {
                if ($files['error'][$i] !== UPLOAD_ERR_OK) {
                    $uploadError = '';
                    switch ($files['error'][$i]) {
                        case UPLOAD_ERR_INI_SIZE:
                        case UPLOAD_ERR_FORM_SIZE:
                            $uploadError = 'El fitxer és massa gran';
                            break;
                        case UPLOAD_ERR_PARTIAL:
                            $uploadError = 'El fitxer només es va pujar parcialment';
                            break;
                        case UPLOAD_ERR_NO_FILE:
                            $uploadError = 'No s\'ha pujat cap fitxer';
                            break;
                        case UPLOAD_ERR_NO_TMP_DIR:
                            $uploadError = 'Falta la carpeta temporal';
                            break;
                        case UPLOAD_ERR_CANT_WRITE:
                            $uploadError = 'Error d\'escriptura al disc';
                            break;
                        case UPLOAD_ERR_EXTENSION:
                            $uploadError = 'Una extensió de PHP va aturar la pujada';
                            break;
                        default:
                            $uploadError = 'Error desconegut (' . $files['error'][$i] . ')';
                    }
                    $errors[] = $files['name'][$i] . ': ' . $uploadError;
                    continue;
                }
                
                $fileName = basename($files['name'][$i]);
                $fileName = preg_replace('/[^A-Za-z0-9._-]/', '_', $fileName);
                $targetPath = $fullPath . '/' . $fileName;
                
                // Verificar permisos abans d'intentar moure
                if (!is_writable($fullPath)) {
                    $errors[] = $fileName . ': No tens permisos d\'escriptura a la carpeta de destí';
                    continue;
                }
                
                if (move_uploaded_file($files['tmp_name'][$i], $targetPath)) {
                    chmod($targetPath, 0644);
                    // Optimitzar imatge si és necessari
                    optimizeImageIfNeeded($targetPath);
                    $uploaded[] = $fileName;
                } else {
                    $phpError = error_get_last();
                    $errorDetail = $phpError && isset($phpError['message']) ? ': ' . $phpError['message'] : '';
                    $errors[] = $fileName . ': Error movent fitxer' . $errorDetail;
                    error_log("Error movent fitxer $fileName a $targetPath" . $errorDetail);
                }
            }
            
            echo json_encode([
                'success' => count($uploaded) > 0,
                'uploaded' => $uploaded,
                'errors' => $errors,
                'message' => count($uploaded) . ' fitxers pujats correctament'
            ]);
            exit;
            
        case 'delete':
            $itemName = $_POST['item_name'] ?? '';
            $itemPath = $fullPath . '/' . basename($itemName);
            $itemPath = str_replace('\\', '/', $itemPath);
            
            // Verificar que l'item existeix
            if (!file_exists($itemPath)) {
                echo json_encode(['success' => false, 'message' => 'Element no trobat']);
                exit;
            }
            
            // Normalitzar ruta si existeix
            $realItemPath = realpath($itemPath);
            if ($realItemPath !== false) {
                $realItemPath = str_replace('\\', '/', $realItemPath);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error normalitzant ruta']);
                exit;
            }
            
            // Verificar permisos
            $baseCheck = $isAdmin ? $imgBaseDir : $userBaseDir;
            if (strpos($realItemPath, $baseCheck) !== 0) {
                echo json_encode(['success' => false, 'message' => 'No tens permís per eliminar aquest element']);
                exit;
            }
            
            if (is_dir($realItemPath)) {
                if (deleteDirectory($realItemPath)) {
                    echo json_encode(['success' => true, 'message' => 'Carpeta eliminada']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Error eliminant carpeta']);
                }
            } else {
                if (unlink($realItemPath)) {
                    echo json_encode(['success' => true, 'message' => 'Fitxer eliminat']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Error eliminant fitxer']);
                }
            }
            exit;
    }
    
    echo json_encode(['success' => false, 'message' => 'Acció no reconeguda']);
    exit;
}

/**
 * Elimina recursivament un directori
 */
function deleteDirectory($dir) {
    if (!is_dir($dir)) {
        return false;
    }
    
    $items = array_diff(scandir($dir), ['.', '..']);
    foreach ($items as $item) {
        $path = $dir . DIRECTORY_SEPARATOR . $item;
        if (is_dir($path)) {
            deleteDirectory($path);
        } else {
            unlink($path);
        }
    }
    
    return rmdir($dir);
}

/**
 * Obté la llista d'elements (carpetes i fitxers) del directori actual
 */
function getDirectoryContents($path) {
    if (!is_dir($path)) {
        return ['folders' => [], 'files' => []];
    }
    
    $folders = [];
    $files = [];
    
    $items = array_diff(scandir($path), ['.', '..']);
    
    foreach ($items as $item) {
        $itemPath = $path . '/' . $item;
        
        if (is_dir($itemPath)) {
            $folders[] = [
                'name' => $item,
                'modified' => filemtime($itemPath),
                'items' => count(array_diff(scandir($itemPath), ['.', '..']))
            ];
        } else {
            // Només mostrar fitxers media (imatges, vídeos, àudio)
            $extension = strtolower(pathinfo($item, PATHINFO_EXTENSION));
            $mediaExtensions = [
                // Imatges
                'jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp', 'ico', 'tiff', 'tif',
                // Vídeos
                'mp4', 'avi', 'mov', 'wmv', 'flv', 'webm', 'mkv', 'mpeg', 'mpg',
                // Àudio
                'mp3', 'wav', 'ogg', 'flac', 'm4a', 'aac', 'wma'
            ];
            
            if (!in_array($extension, $mediaExtensions)) {
                continue; // Saltar fitxers que no són media
            }
            
            $files[] = [
                'name' => $item,
                'size' => filesize($itemPath),
                'modified' => filemtime($itemPath),
                'type' => mime_content_type($itemPath),
                'extension' => $extension
            ];
        }
    }
    
    // Ordenar alfabèticament
    usort($folders, function($a, $b) {
        return strcasecmp($a['name'], $b['name']);
    });
    
    usort($files, function($a, $b) {
        return strcasecmp($a['name'], $b['name']);
    });
    
    return ['folders' => $folders, 'files' => $files];
}

/**
 * Formata bytes a format llegible
 */
function formatBytes($bytes) {
    if ($bytes < 1024) return $bytes . ' B';
    if ($bytes < 1048576) return round($bytes / 1024, 1) . ' KB';
    if ($bytes < 1073741824) return round($bytes / 1048576, 1) . ' MB';
    return round($bytes / 1073741824, 1) . ' GB';
}

// Obtenir contingut del directori
$contents = getDirectoryContents($fullPath);

// Breadcrumb navigation
$pathParts = array_filter(explode('/', $requestedPath));
$breadcrumbs = [];

if ($isAdmin) {
    $breadcrumbs[] = ['name' => 'img', 'path' => ''];
} else {
    $breadcrumbs[] = ['name' => 'La meva carpeta', 'path' => 'user_' . $userId];
}

$currentPath = '';
foreach ($pathParts as $index => $part) {
    // Per no-admins, saltar el prefix user_X del breadcrumb
    if (!$isAdmin && $index === 0 && $part === 'user_' . $userId) {
        continue;
    }
    
    $currentPath .= ($currentPath ? '/' : '') . $part;
    $breadcrumbs[] = ['name' => $part, 'path' => $currentPath];
}

?>
<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Explorador de Media - Panel de Control</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@7.0.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Libre+Baskerville:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/dashboard.css">
    <style>
        .media-explorer {
            padding: 20px;
        }
        
        .breadcrumb {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 12px 16px;
            background: #f8f9fa;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .breadcrumb a {
            color: #007bff;
            text-decoration: none;
            transition: color 0.2s;
        }
        
        .breadcrumb a:hover {
            color: #0056b3;
            text-decoration: underline;
        }
        
        .breadcrumb-separator {
            color: #999;
        }
        
        .breadcrumb-current {
            color: #333;
            font-weight: 600;
        }
        
        .toolbar {
            display: flex;
            gap: 12px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        .btn {
            padding: 10px 16px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
            font-weight: 500;
        }
        
        .btn-primary {
            background: #007bff;
            color: white;
        }
        
        .btn-primary:hover {
            background: #0056b3;
            transform: translateY(-1px);
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
        }
        
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        
        .btn-danger:hover {
            background: #c82333;
        }
        
        .media-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 16px;
            margin-top: 20px;
        }
        
        .media-item {
            background: white;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            padding: 12px;
            cursor: pointer;
            transition: all 0.2s;
            position: relative;
        }
        
        .media-item:hover {
            border-color: #007bff;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .media-item.selected {
            border-color: #007bff;
            background: #e7f3ff;
        }
        
        /* Estil específic per mode picker */
        body.picker-mode .media-item[data-type="file"]:hover {
            border-color: #28a745;
            background: #f0fff4;
        }
        
        body.picker-mode .media-item[data-type="file"]:hover::after {
            content: '✓ Seleccionar';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(40, 167, 69, 0.95);
            color: white;
            padding: 8px 16px;
            border-radius: 4px;
            font-weight: 600;
            font-size: 14px;
            pointer-events: none;
        }
        
        .media-icon {
            width: 100%;
            height: 120px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f8f9fa;
            border-radius: 6px;
            margin-bottom: 8px;
            overflow: hidden;
        }
        
        .media-icon i {
            font-size: 48px;
            color: #6c757d;
        }
        
        .media-icon img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .media-icon.folder {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .media-icon.folder i {
            color: white;
        }
        
        .media-name {
            font-size: 13px;
            font-weight: 500;
            color: #333;
            margin-bottom: 4px;
            word-break: break-word;
            line-height: 1.3;
        }
        
        .media-info {
            font-size: 11px;
            color: #999;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }
        
        .empty-state i {
            font-size: 64px;
            margin-bottom: 16px;
            opacity: 0.3;
        }
        
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 10000;
            align-items: center;
            justify-content: center;
        }
        
        .modal.active {
            display: flex;
        }
        
        .modal-content {
            background: white;
            border-radius: 12px;
            padding: 24px;
            max-width: 500px;
            width: 90%;
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .modal-header h3 {
            margin: 0;
            font-size: 18px;
        }
        
        .modal-close {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #999;
        }
        
        .form-group {
            margin-bottom: 16px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
            font-size: 14px;
        }
        
        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
        }
        
        .upload-zone {
            border: 2px dashed #ddd;
            border-radius: 8px;
            padding: 40px 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .upload-zone:hover {
            border-color: #007bff;
            background: #f8f9fa;
        }
        
        .upload-zone.dragover {
            border-color: #007bff;
            background: #e7f3ff;
        }
        
        .alert {
            padding: 12px 16px;
            border-radius: 6px;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        #deleteBtn {
            display: none;
        }
        
        #deleteBtn.active {
            display: inline-flex;
        }
    </style>
</head>
<body<?php echo $isPickerMode ? ' class="picker-mode"' : ''; ?>>
    <link rel="icon" type="image/png" sizes="32x32" href="../img/Logo32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../img/Logo16.png">
    
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="main-content">
        <header class="top-bar">
            <div class="top-bar-left">
                <button class="menu-toggle" id="menuToggle">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="top-bar-info">
                    <h1>Explorador de Media</h1>
                    <p class="date-today"><?php echo $isAdmin ? 'Vista completa' : 'La meva carpeta'; ?></p>
                </div>
            </div>
            <div class="top-bar-right">
                <div class="user-profile">
                    <img src="../img/Logo.png" alt="Profile" class="profile-img">
                    <span class="profile-name"><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Usuario'); ?></span>
                </div>
            </div>
        </header>
        
        <div class="content-wrapper media-explorer">
            <div id="alertContainer">
                <?php if ($folderCreationError): ?>
                    <div class="alert alert-warning" style="margin-bottom: 20px;">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Avís:</strong> <?php echo htmlspecialchars($folderCreationError); ?>
                        <p style="margin-top: 10px; font-size: 0.9em;">
                            Verifica que el servidor web té permisos d'escriptura a la carpeta <code><?php echo htmlspecialchars($imgBaseDir); ?></code>
                        </p>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Breadcrumb Navigation -->
            <div class="breadcrumb">
                <i class="fas fa-folder"></i>
                <?php foreach ($breadcrumbs as $index => $crumb): ?>
                    <?php if ($index > 0): ?>
                        <span class="breadcrumb-separator">/</span>
                    <?php endif; ?>
                    
                    <?php if ($index === count($breadcrumbs) - 1): ?>
                        <span class="breadcrumb-current"><?php echo htmlspecialchars($crumb['name']); ?></span>
                    <?php else: ?>
                        <a href="gmedia.php?path=<?php echo urlencode($crumb['path']); ?><?php echo $isPickerMode ? '&picker=1' : ''; ?>">
                            <?php echo htmlspecialchars($crumb['name']); ?>
                        </a>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
            
            <?php if ($isPickerMode): ?>
                <!-- Mode Picker: Títol informatiu -->
                <div style="background: #e3f2fd; border-left: 4px solid #2196f3; padding: 12px 16px; border-radius: 4px; margin-bottom: 20px;">
                    <strong><i class="fas fa-info-circle"></i> Mode de selecció:</strong> Clica sobre una imatge per seleccionar-la.
                </div>
            <?php endif; ?>
            
            <!-- Toolbar -->
            <?php if (!$isPickerMode): ?>
                <div class="toolbar">
                    <button class="btn btn-primary" onclick="openUploadModal()">
                        <i class="fas fa-upload"></i>
                        Pujar fitxers
                    </button>
                    <button class="btn btn-primary" onclick="openCreateFolderModal()">
                        <i class="fas fa-folder-plus"></i>
                        Nova carpeta
                    </button>
                    <button class="btn btn-danger" id="deleteBtn" onclick="deleteSelected()">
                        <i class="fas fa-trash"></i>
                        Eliminar
                    </button>
                </div>
            <?php else: ?>
                <!-- En mode picker, només mostrar el botó de pujar -->
                <div class="toolbar">
                    <button class="btn btn-primary" onclick="openUploadModal()">
                        <i class="fas fa-upload"></i>
                        Pujar fitxers
                    </button>
                </div>
            <?php endif; ?>
            
            <!-- Media Grid -->
            <div class="media-grid">
                <?php if (empty($contents['folders']) && empty($contents['files'])): ?>
                    <div class="empty-state" style="grid-column: 1 / -1;">
                        <i class="fas fa-folder-open"></i>
                        <p>Aquesta carpeta està buida</p>
                    </div>
                <?php endif; ?>
                
                <?php foreach ($contents['folders'] as $folder): ?>
                    <div class="media-item" data-type="folder" data-name="<?php echo htmlspecialchars($folder['name']); ?>" 
                         onclick="navigateToFolder('<?php echo htmlspecialchars($folder['name']); ?>')">
                        <div class="media-icon folder">
                            <i class="fas fa-folder"></i>
                        </div>
                        <div class="media-name"><?php echo htmlspecialchars($folder['name']); ?></div>
                        <div class="media-info"><?php echo $folder['items']; ?> elements</div>
                    </div>
                <?php endforeach; ?>
                
                <?php foreach ($contents['files'] as $file): ?>
                    <?php
                    $isImage = in_array($file['extension'], ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg']);
                    $relativePath = str_replace($imgBaseDir, '', $fullPath) . '/' . $file['name'];
                    $relativePath = ltrim($relativePath, '/');
                    
                    // Construir URL completa per a la imatge
                    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
                    $host = $_SERVER['HTTP_HOST'];
                    $pathParts = explode('/', trim($_SERVER['REQUEST_URI'], '/'));
                    $basePath = (count($pathParts) > 1 && $pathParts[0] !== '_pcontrol') ? '/' . $pathParts[0] : '';
                    $imageUrl = $protocol . '://' . $host . $basePath . '/img/' . $relativePath;
                    ?>
                    <div class="media-item" data-type="file" data-name="<?php echo htmlspecialchars($file['name']); ?>"
                         data-url="<?php echo htmlspecialchars($imageUrl); ?>"
                         onclick="<?php echo $isPickerMode ? 'selectImage(this, event)' : 'toggleSelect(this, event)'; ?>">
                        <div class="media-icon">
                            <?php if ($isImage): ?>
                                <img src="../img/<?php echo htmlspecialchars($relativePath); ?>" 
                                     alt="<?php echo htmlspecialchars($file['name']); ?>"
                                     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <i class="fas fa-image" style="display:none;"></i>
                            <?php else: ?>
                                <i class="fas fa-file"></i>
                            <?php endif; ?>
                        </div>
                        <div class="media-name"><?php echo htmlspecialchars($file['name']); ?></div>
                        <div class="media-info"><?php echo formatBytes($file['size']); ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    
    <!-- Modal: Create Folder -->
    <div class="modal" id="createFolderModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Nova carpeta</h3>
                <button class="modal-close" onclick="closeModal('createFolderModal')">&times;</button>
            </div>
            <form onsubmit="createFolder(event)">
                <div class="form-group">
                    <label for="folderName">Nom de la carpeta:</label>
                    <input type="text" id="folderName" name="folder_name" required pattern="[A-Za-z0-9_-]+" 
                           title="Només lletres, números, guions i guions baixos">
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%;">Crear</button>
            </form>
        </div>
    </div>
    
    <!-- Modal: Upload Files -->
    <div class="modal" id="uploadModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Pujar fitxers</h3>
                <button class="modal-close" onclick="closeModal('uploadModal')">&times;</button>
            </div>
            <form onsubmit="uploadFiles(event)">
                <div class="upload-zone" id="uploadZone" onclick="document.getElementById('fileInput').click()">
                    <i class="fas fa-cloud-upload-alt" style="font-size: 48px; color: #007bff; margin-bottom: 12px;"></i>
                    <p>Clica per seleccionar fitxers o arrossega'ls aquí</p>
                    <input type="file" id="fileInput" name="files[]" multiple style="display: none;" onchange="showSelectedFiles()">
                </div>
                <div id="selectedFiles" style="margin-top: 12px; font-size: 13px; color: #666;"></div>
                <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 16px;">Pujar</button>
            </form>
        </div>
    </div>
    
    <script>
        const currentPath = '<?php echo addslashes($requestedPath); ?>';
        let selectedItems = new Set();
        
        const isPickerMode = <?php echo $isPickerMode ? 'true' : 'false'; ?>;
        
        function navigateToFolder(folderName) {
            const newPath = currentPath ? currentPath + '/' + folderName : folderName;
            const pickerParam = isPickerMode ? '&picker=1' : '';
            window.location.href = 'gmedia.php?path=' + encodeURIComponent(newPath) + pickerParam;
        }
        
        function selectImage(element, event) {
            event.stopPropagation();
            
            const imageUrl = element.getAttribute('data-url');
            const imageName = element.getAttribute('data-name');
            
            if (!imageUrl) {
                console.error('No image URL found');
                return;
            }
            
            // Enviar imatge seleccionada de tornada al parent
            if (window.opener) {
                window.opener.postMessage({
                    mediaUrl: imageUrl,
                    alt: imageName
                }, '*');
                window.close();
            }
        }
        
        function toggleSelect(element, event) {
            event.stopPropagation();
            
            const itemName = element.getAttribute('data-name');
            
            if (element.classList.contains('selected')) {
                element.classList.remove('selected');
                selectedItems.delete(itemName);
            } else {
                element.classList.add('selected');
                selectedItems.add(itemName);
            }
            
            updateDeleteButton();
        }
        
        function updateDeleteButton() {
            const deleteBtn = document.getElementById('deleteBtn');
            if (selectedItems.size > 0) {
                deleteBtn.classList.add('active');
            } else {
                deleteBtn.classList.remove('active');
            }
        }
        
        function openCreateFolderModal() {
            document.getElementById('createFolderModal').classList.add('active');
            document.getElementById('folderName').focus();
        }
        
        function openUploadModal() {
            document.getElementById('uploadModal').classList.add('active');
        }
        
        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('active');
        }
        
        async function createFolder(event) {
            event.preventDefault();
            
            const formData = new FormData(event.target);
            formData.append('action', 'create_folder');
            formData.append('path', currentPath);
            
            try {
                const response = await fetch('gmedia.php?path=' + encodeURIComponent(currentPath), {
                    method: 'POST',
                    body: formData
                });
                
                if (!response.ok) {
                    throw new Error('HTTP error! status: ' + response.status + ' ' + response.statusText);
                }
                
                const responseText = await response.text();
                console.log('Response text:', responseText);
                
                let result;
                try {
                    result = JSON.parse(responseText);
                } catch (e) {
                    console.error('Invalid JSON response:', responseText);
                    throw new Error('Resposta no vàlida del servidor. Comprova la consola per més detalls.');
                }
                
                if (result.success) {
                    showAlert('success', result.message);
                    closeModal('createFolderModal');
                    setTimeout(() => location.reload(), 500);
                } else {
                    showAlert('danger', result.message);
                }
            } catch (error) {
                console.error('Error creant carpeta:', error);
                showAlert('danger', 'Error: ' + error.message);
            }
        }
        
        async function uploadFiles(event) {
            event.preventDefault();
            
            const fileInput = document.getElementById('fileInput');
            if (fileInput.files.length === 0) {
                showAlert('danger', 'Selecciona almenys un fitxer');
                return;
            }
            
            const formData = new FormData();
            formData.append('action', 'upload');
            formData.append('path', currentPath);
            
            for (let i = 0; i < fileInput.files.length; i++) {
                formData.append('files[]', fileInput.files[i]);
            }
            
            try {
                const response = await fetch('gmedia.php?path=' + encodeURIComponent(currentPath), {
                    method: 'POST',
                    body: formData
                });
                
                if (!response.ok) {
                    throw new Error('HTTP error! status: ' + response.status + ' ' + response.statusText);
                }
                
                const result = await response.json();
                
                if (result.success) {
                    showAlert('success', result.message);
                    closeModal('uploadModal');
                    setTimeout(() => location.reload(), 500);
                } else {
                    showAlert('danger', result.errors.join(', '));
                }
            } catch (error) {
                console.error('Error pujant fitxers:', error);
                showAlert('danger', 'Error de connexió: ' + error.message);
            }
        }
        
        async function deleteSelected() {
            if (selectedItems.size === 0) return;
            
            const itemsText = Array.from(selectedItems).join(', ');
            if (!confirm('Segur que vols eliminar: ' + itemsText + '?')) {
                return;
            }
            
            for (const itemName of selectedItems) {
                const formData = new FormData();
                formData.append('action', 'delete');
                formData.append('item_name', itemName);
                formData.append('path', currentPath);
                
                try {
                    const response = await fetch('gmedia.php?path=' + encodeURIComponent(currentPath), {
                        method: 'POST',
                        body: formData
                    });
                    
                    if (!response.ok) {
                        throw new Error('HTTP error! status: ' + response.status);
                    }
                    
                    const result = await response.json();
                    if (!result.success) {
                        showAlert('danger', 'Error eliminant ' + itemName + ': ' + (result.message || 'Error desconegut'));
                    }
                } catch (error) {
                    console.error('Error eliminant element:', error);
                    showAlert('danger', 'Error de connexió: ' + error.message);
                }
            }
            
            showAlert('success', 'Elements eliminats');
            setTimeout(() => location.reload(), 500);
        }
        
        function showSelectedFiles() {
            const fileInput = document.getElementById('fileInput');
            const container = document.getElementById('selectedFiles');
            
            if (fileInput.files.length > 0) {
                const fileNames = Array.from(fileInput.files).map(f => f.name).join(', ');
                container.textContent = fileInput.files.length + ' fitxer(s) seleccionat(s): ' + fileNames;
            } else {
                container.textContent = '';
            }
        }
        
        function showAlert(type, message) {
            const container = document.getElementById('alertContainer');
            const alert = document.createElement('div');
            alert.className = 'alert alert-' + type;
            alert.innerHTML = '<i class="fas fa-' + (type === 'success' ? 'check-circle' : 'exclamation-circle') + '"></i>' + message;
            container.appendChild(alert);
            
            setTimeout(() => alert.remove(), 5000);
        }
        
        // Drag & drop
        const uploadZone = document.getElementById('uploadZone');
        
        uploadZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadZone.classList.add('dragover');
        });
        
        uploadZone.addEventListener('dragleave', () => {
            uploadZone.classList.remove('dragover');
        });
        
        uploadZone.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadZone.classList.remove('dragover');
            
            const fileInput = document.getElementById('fileInput');
            fileInput.files = e.dataTransfer.files;
            showSelectedFiles();
        });
        
        // Close modals on Escape
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                document.querySelectorAll('.modal').forEach(modal => {
                    modal.classList.remove('active');
                });
            }
        });
    </script>
</body>
</html>
