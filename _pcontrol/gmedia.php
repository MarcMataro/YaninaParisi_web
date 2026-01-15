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
require_once '../classes/connexio.php'; // Include database connection

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
            
            // Per admins, permetre crear carpeta en una ubicació específica
            $parentFolder = $_POST['parent_folder'] ?? '';
            if ($isAdmin && !empty($parentFolder)) {
                // Verificar que la carpeta pare és vàlida
                $parentPath = $imgBaseDir . '/' . ltrim($parentFolder, '/');
                $parentPath = str_replace('\\', '/', $parentPath);
                
                // Normalitzar i verificar seguretat
                if (file_exists($parentPath)) {
                    $realParentPath = realpath($parentPath);
                    if ($realParentPath !== false) {
                        $realParentPath = str_replace('\\', '/', $realParentPath);
                        // Verificar que està dins de img/
                        if (strpos($realParentPath, $imgBaseDir) === 0) {
                            $fullPath = $realParentPath;
                        }
                    }
                }
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
            
            // Per admins, permetre seleccionar carpeta de destí
            $targetFolder = $_POST['target_folder'] ?? '';
            if ($isAdmin && !empty($targetFolder)) {
                // Verificar que la carpeta de destí és vàlida
                $targetPath = $imgBaseDir . '/' . ltrim($targetFolder, '/');
                $targetPath = str_replace('\\', '/', $targetPath);
                
                // Normalitzar i verificar seguretat
                if (file_exists($targetPath)) {
                    $realTargetPath = realpath($targetPath);
                    if ($realTargetPath !== false) {
                        $realTargetPath = str_replace('\\', '/', $realTargetPath);
                        // Verificar que està dins de img/
                        if (strpos($realTargetPath, $imgBaseDir) === 0) {
                            $fullPath = $realTargetPath;
                        }
                    }
                }
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
                'message' => count($uploaded) . ' fitxers pujats correctament',
                'target_folder' => isset($_POST['target_folder']) ? $_POST['target_folder'] : $requestedPath
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

// Function to check if user folders correspond to existing users
function checkOrphanFolders($folders) {
    try {
        $db = Connexio::getInstance()->getConnexio();
    } catch (Exception $e) {
        error_log("Database connection error in checkOrphanFolders: " . $e->getMessage());
        return $folders;
    }

    $userFolders = [];
    foreach ($folders as $key => $folder) {
        if (preg_match('/^user_(\d+)$/', $folder['name'], $matches)) {
            $userId = (int)$matches[1];
            $userFolders[$userId] = $key;
        }
    }

    if (empty($userFolders)) {
        return $folders;
    }

    $ids = array_keys($userFolders);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    
    // Note: checking table usuarios_panel
    $sql = "SELECT id_usuario FROM usuarios_panel WHERE id_usuario IN ($placeholders)";
    
    try {
        $stmt = $db->prepare($sql);
        $stmt->execute($ids);
        
        $foundIds = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $foundIds[] = $row['id_usuario'];
        }
        
        foreach ($userFolders as $id => $key) {
            if (!in_array($id, $foundIds)) {
                $folders[$key]['is_orphan'] = true;
            }
        }
    } catch (Exception $e) {
        error_log("Error checkOrphanFolders: " . $e->getMessage());
    }
    
    return $folders;
}

// Function to check if files are used in the database
function checkFilesUsage($files, $currentPath, $imgBaseDir) {
    try {
        $db = Connexio::getInstance()->getConnexio();
    } catch (Exception $e) {
        error_log("Database connection error in checkFilesUsage: " . $e->getMessage());
        return $files;
    }

    $filesToCheck = [];
    $filesMap = []; // Maps lowercase filename to original entries

    foreach ($files as $key => $file) {
        if (!$file['is_dir']) {
            $name = $file['name'];
            $filesToCheck[] = $name;
            $lowerName = mb_strtolower($name);
            if (!isset($filesMap[$lowerName])) {
                $filesMap[$lowerName] = [];
            }
            $filesMap[$lowerName][] = $key;
        }
    }

    if (empty($filesToCheck)) {
        return $files;
    }
    
    // Normalize paths
    $imgBaseDir = str_replace('\\', '/', $imgBaseDir);
    $currentPath = str_replace('\\', '/', $currentPath);
    
    // Determine relative path of the CURRENT folder being viewed
    // e.g. "user_1" or "media/videos"
    $relDir = '';
    if (strpos($currentPath, $imgBaseDir) === 0) {
        $relDir = substr($currentPath, strlen($imgBaseDir));
        $relDir = ltrim($relDir, '/');
    }

    $usedLowerNames = [];

    // Helper to check if a DB path matches one of our files
    // $dbPath: Value from database (e.g. "user_1/photo.jpg")
    $checkMatch = function($dbPath) use ($relDir, $filesMap, &$usedLowerNames) {
        if (empty($dbPath)) return;
        
        $dbPath = str_replace('\\', '/', $dbPath); // Normalize DB path
        $dbBasename = mb_strtolower(basename($dbPath));
        
        // If the filename appears in our list
        if (isset($filesMap[$dbBasename])) {
            // Strict check: does the DB path match our file's location?
            // If we are in "user_1" and checking "photo.jpg", our relative path is "user_1/photo.jpg"
            
            // 1. Calculate expected end of string for this file
            $expectedSuffix = ($relDir ? $relDir . '/' : '') . $dbBasename;
            $expectedSuffix = mb_strtolower($expectedSuffix);
            $fullDbPathLower = mb_strtolower($dbPath);
            
            // 2. Check if DB path ends with our expected suffix
            // This handles:
            // "user_1/photo.jpg" (Exact match)
            // "img/user_1/photo.jpg" (Ends with)
            // "/var/www/.../img/user_1/photo.jpg" (Ends with)
            
            // We check if it ends with $expectedSuffix
            // AND ensure it's boundary correct (preceded by / or is start of string)
            
            $endsWith = false;
            $len = strlen($expectedSuffix);
            $dbLen = strlen($fullDbPathLower);
            
            if ($dbLen === $len) {
                if ($fullDbPathLower === $expectedSuffix) $endsWith = true;
            } elseif ($dbLen > $len) {
                // Check if it ends with suffix AND preceding char is /
                if (substr($fullDbPathLower, -$len) === $expectedSuffix) {
                    $charBefore = substr($fullDbPathLower, -($len + 1), 1);
                    if ($charBefore === '/') {
                        $endsWith = true;
                    }
                }
            }
            
            // Fallback: If DB stores JUST the filename "photo.jpg" and we are in root, it matches.
            // If DB stores JUST "photo.jpg" and we are in subfolder, strict match fails.
            // BUT: legacy data might store just filenames assuming root? 
            // Let's assume strictness to avoid false positives in other folders.
            
            if ($endsWith) {
                $usedLowerNames[$dbBasename] = true;
            } else {
                // Special Case: If DB path has NO directory separators, match strictly on filename
                // BUT only if we can be sure.
                // If DB has "photo.jpg" and we have "user_1/photo.jpg", does it match?
                // Probably not safe to assume.
                
                // However, if the user sees NO marks, maybe the DB has absolute paths that differ slightly?
                // Let's rely on the suffix check.
            }
        }
    };

    // 1. Query Database Columns
    // To minimize data transfer, we filter using LIKE %name for ALL files
    // Then strictly filter in PHP
    
    $params = [];
    $clauses = [];
    foreach ($filesToCheck as $name) {
        $clauses[] = "foto LIKE ?";
        $params[] = "%$name";
    }
    // Optimization: If too many files, just fetch ALL non-null images? 
    // No, pagination/lazy loading not evident here, usually folder size is manageable (hundreds not millions).
    
    // Actually, constructing 300 ORs is bad.
    // Better strategy for folder view:
    // "SELECT col FROM table WHERE col LIKE '%query_relevant_string%'"
    // Since all files share the same folder, maybe filter by folder?
    // "WHERE foto LIKE '%user_1/%'"? 
    // If we are in root, we can't filter by folder easily.
    
    // Let's stick to checking names. If > 50 files, maybe just fetch all and filter PHP side?
    // Or chunk it.
    
    // For now, assuming reasonably sized folders.
    $validFilesCount = count($filesToCheck);
    
    if ($validFilesCount > 0) {
        // Construct ONE big query with bound parameters is safest/easiest given unknown constraints
        
        // Let's use array_fill for placeholders if we used IN, but we use LIKE ...
        // We can optimize: WHERE column REGEXP 'name1|name2|...'
        // MySQL REGEXP is powerful.
        // $pattern = implode('|', array_map(function($n){ return preg_quote($n); }, $filesToCheck));
        // "foto REGEXP ?" -> $pattern
        
        // Let's try REGEXP approach for brevity in SQL, if simple chars.
        // Filenames: alphanumeric + . _ -
        
        $namesSafe = array_map(function($f) { return preg_quote($f); }, $filesToCheck);
        $regexp = implode('|', $namesSafe);
        
        $sql = "
            SELECT foto as img FROM professionals WHERE foto REGEXP ?
            UNION
            SELECT image_path as img FROM professional_photos WHERE image_path REGEXP ?
            UNION
            SELECT imatge_portada as img FROM blog_entrades WHERE imatge_portada REGEXP ?
        ";
        
        try {
            $stmt = $db->prepare($sql);
            $stmt->execute([$regexp, $regexp, $regexp]);
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $checkMatch($row['img']);
            }
        } catch (Exception $e) {
             // Fallback to simpler query if REGEXP fails or not supported (though standard in MySQL/MariaDB)
             error_log("REGEXP query failed: " . $e->getMessage() . ". Trying simplified checks.");
             
             // Fallback: simplified broad fetch or iterating
        }
    }
    
    // 2. Check Content (LIKE) in blog posts
    // For content, we DEFINITELY should search using the path if possible to avoid false matches.
    // If $relDir is set, use it.
    
    $contentSearchTerms = [];
    foreach ($filesToCheck as $name) {
        // Term: relative path "user_1/photo.jpg"
        // If in root, just "photo.jpg"
        $term = $relDir ? $relDir . '/' . $name : $name;
        $contentSearchTerms[] = $term; // Used for PHP check
    }
    
    if (!empty($contentSearchTerms)) {
        // Use REGEXP again for content? Content is large text. LIKE is better for fulltext but REGEXP works.
        // "contingut REGEXP 'user_1/photo.jpg|user_1/video.mp4'"
        
        $termsSafe = array_map(function($t) { return preg_quote($t, '/'); }, $contentSearchTerms); // / delimiter for preg_quote logic if needed, but for sql regexp just chars
        // MySQL REGEXP doesn't use delimiters like PHP. Just escape special regexp chars.
        // . is special.
        $termsSafeSQL = array_map(function($t) { 
            return str_replace('.', '\\.', $t); // Escape dot for SQL Regexp
        }, $contentSearchTerms);
        
        $regexpContent = implode('|', $termsSafeSQL);
        
        $sqlC = "SELECT contingut_ca, contingut_es, galeria_imatges FROM blog_entrades WHERE 
                 contingut_ca REGEXP ? OR contingut_es REGEXP ? OR galeria_imatges REGEXP ?";
                 
        try {
            $stmtC = $db->prepare($sqlC);
            $stmtC->execute([$regexpContent, $regexpContent, $regexpContent]);
            
            while ($row = $stmtC->fetch(PDO::FETCH_ASSOC)) {
                $text = $row['contingut_ca'] . ' ' . $row['contingut_es'] . ' ' . $row['galeria_imatges'];
                // Check which files are in this text
                foreach ($contentSearchTerms as $term) {
                    // Check existence (case insensitive)
                    if (stripos($text, $term) !== false) {
                        // Mark the corresponding file used
                        // Recover filename from term (basename)
                        $bname = basename($term);
                        $usedLowerNames[mb_strtolower($bname)] = true;
                    }
                }
            }
        } catch (Exception $e) {
            error_log("Content REGEXP failed: " . $e->getMessage());
        }
    }

    // Update files array
    foreach ($files as &$file) {
        if (!$file['is_dir']) {
            $lower = mb_strtolower($file['name']);
            if (isset($usedLowerNames[$lower])) {
                $file['is_used'] = true;
            } else {
                $file['is_used'] = false;
            }
        }
    }
    
    return $files;
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
 * Obté recursivament totes les carpetes dins d'un directori
 */
function getAllFolders($dir, $baseDir = null, $prefix = '') {
    if ($baseDir === null) {
        $baseDir = $dir;
    }
    
    $folders = [];
    
    if (!is_dir($dir)) {
        return $folders;
    }
    
    $items = @scandir($dir);
    if ($items === false) {
        return $folders;
    }
    
    $items = array_diff($items, ['.', '..']);
    
    // Ordenar els items alfabèticament
    sort($items);
    
    foreach ($items as $item) {
        $itemPath = $dir . '/' . $item;
        
        if (is_dir($itemPath)) {
            // Obtenir ruta relativa
            $relativePath = $prefix . $item;
            
            $folders[] = [
                'path' => $relativePath,
                'name' => $item,
                'full_path' => $itemPath
            ];
            
            // Recursiu
            $subFolders = getAllFolders($itemPath, $baseDir, $relativePath . '/');
            $folders = array_merge($folders, $subFolders);
        }
    }
    
    return $folders;
}

/**
 * Obté la llista d'elements (carpetes i fitxers) del directori actual
 */
function getDirectoryContents($path) {
    if (!is_dir($path)) {
        return ['folders' => [], 'files' => []];
    }
    
    // Get base dir for usage check
    $imgBaseDir = realpath(__DIR__ . '/../img');
    $imgBaseDir = str_replace('\\', '/', $imgBaseDir);
    
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
                'extension' => $extension,
                'is_dir' => false
            ];
        }
    }

    // Check usage
    $files = checkFilesUsage($files, $path, $imgBaseDir);
    
    // Check orphan folders
    $folders = checkOrphanFolders($folders);
    
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

// Obtenir totes les carpetes disponibles per admins (per al selector de carpeta)
$availableFolders = [];
if ($isAdmin) {
    $availableFolders = getAllFolders($imgBaseDir);
    // Afegir la carpeta arrel
    array_unshift($availableFolders, [
        'path' => '',
        'name' => 'img/ (arrel)',
        'full_path' => $imgBaseDir
    ]);
}

// Breadcrumb navigation
$pathParts = array_filter(explode('/', $requestedPath));
$breadcrumbs = [];

if ($isAdmin) {
    $breadcrumbs[] = ['name' => 'img', 'path' => ''];
    
    // Per admins, afegir cada part del path com a breadcrumb
    $currentPath = '';
    foreach ($pathParts as $part) {
        $currentPath .= ($currentPath ? '/' : '') . $part;
        $breadcrumbs[] = ['name' => $part, 'path' => $currentPath];
    }
} else {
    $breadcrumbs[] = ['name' => 'Mi carpeta', 'path' => 'user_' . $userId];
    
    // Per no-admins, saltar el prefix user_X del breadcrumb visual
    $currentPath = '';
    foreach ($pathParts as $index => $part) {
        $currentPath .= ($currentPath ? '/' : '') . $part;
        
        // Saltar el prefix user_X del breadcrumb visual però mantenir el path correcte
        if ($index === 0 && $part === 'user_' . $userId) {
            continue;
        }
        
        $breadcrumbs[] = ['name' => $part, 'path' => $currentPath];
    }
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
        
        .btn-back {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            background: #007bff;
            color: white;
            border-radius: 6px;
            text-decoration: none;
            transition: all 0.2s;
            margin-right: 8px;
        }
        
        .btn-back:hover {
            background: #0056b3;
            transform: translateX(-2px);
        }
        
        .btn-back i {
            font-size: 14px;
        }
        
        .breadcrumb-link {
            color: #007bff;
            text-decoration: none;
            transition: color 0.2s;
            padding: 4px 8px;
            border-radius: 4px;
        }
        
        .breadcrumb-link:hover {
            color: #0056b3;
            background: rgba(0, 123, 255, 0.1);
            text-decoration: none;
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

        .delete-icon {
            position: absolute;
            top: 5px;
            right: 5px;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 50%;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #dc3545;
            cursor: pointer;
            transition: all 0.2s;
            z-index: 10;
            border: 1px solid #dc3545;
        }
        
        .delete-icon:hover {
            background: #dc3545;
            color: white;
        }

        .usage-badge {
            position: absolute;
            top: 5px;
            left: 5px;
            background: rgba(40, 167, 69, 0.9);
            color: white;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
            z-index: 10;
            box-shadow: 0 1px 2px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            gap: 3px;
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
                    <p class="date-today"><?php echo $isAdmin ? 'Vista completa' : 'Mi carpeta'; ?></p>
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
                <?php if (count($breadcrumbs) > 1): ?>
                    <?php
                        // Trobar el penúltim breadcrumb per fer el botó "Tornar"
                        $parentCrumb = $breadcrumbs[count($breadcrumbs) - 2];
                        $backLabel = $parentCrumb['name'] === 'img' ? 'Volver a raíz' : 'Volver a ' . $parentCrumb['name'];
                        
                        // Construir URL del botó tornar
                        $backUrl = 'gmedia.php';
                        if (!empty($parentCrumb['path'])) {
                            $backUrl .= '?path=' . urlencode($parentCrumb['path']);
                        }
                        if ($isPickerMode) {
                            $backUrl .= (strpos($backUrl, '?') !== false ? '&' : '?') . 'picker=1';
                            if (isset($_GET['admin_picker']) && $_GET['admin_picker'] == '1') {
                                $backUrl .= '&admin_picker=1';
                            }
                        }
                    ?>
                    <a href="<?php echo htmlspecialchars($backUrl); ?>" 
                       class="btn-back" title="<?php echo htmlspecialchars($backLabel); ?>">
                        <i class="fas fa-arrow-left"></i>
                    </a>
                <?php endif; ?>
                
                <i class="fas fa-folder"></i>
                <?php foreach ($breadcrumbs as $index => $crumb): ?>
                    <?php if ($index > 0): ?>
                        <span class="breadcrumb-separator">/</span>
                    <?php endif; ?>
                    
                    <?php if ($index === count($breadcrumbs) - 1): ?>
                        <span class="breadcrumb-current"><?php echo htmlspecialchars($crumb['name']); ?></span>
                    <?php else: ?>
                        <?php
                            // Construir URL del breadcrumb
                            $crumbUrl = 'gmedia.php';
                            if (!empty($crumb['path'])) {
                                $crumbUrl .= '?path=' . urlencode($crumb['path']);
                            }
                            if ($isPickerMode) {
                                $crumbUrl .= (strpos($crumbUrl, '?') !== false ? '&' : '?') . 'picker=1';
                                if (isset($_GET['admin_picker']) && $_GET['admin_picker'] == '1') {
                                    $crumbUrl .= '&admin_picker=1';
                                }
                            }
                        ?>
                        <a href="<?php echo htmlspecialchars($crumbUrl); ?>" 
                           class="breadcrumb-link">
                            <?php echo htmlspecialchars($crumb['name']); ?>
                        </a>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
            
            <?php if ($isPickerMode): ?>
                <!-- Modo Picker: Título informativo -->
                <div style="background: #e3f2fd; border-left: 4px solid #2196f3; padding: 12px 16px; border-radius: 4px; margin-bottom: 20px;">
                    <strong><i class="fas fa-info-circle"></i> Modo de selección:</strong> Haz clic sobre una imagen para seleccionarla.
                </div>
            <?php endif; ?>
            
            <!-- Toolbar -->
            <?php if (!$isPickerMode): ?>
                <div class="toolbar">
                    <button class="btn btn-primary" onclick="openUploadModal()">
                        <i class="fas fa-upload"></i>
                        Subir archivos
                    </button>
                    <button class="btn btn-primary" onclick="openCreateFolderModal()">
                        <i class="fas fa-folder-plus"></i>
                        Nueva carpeta
                    </button>
                    <button class="btn btn-danger" id="deleteBtn" onclick="deleteSelected()">
                        <i class="fas fa-trash"></i>
                        Eliminar
                    </button>
                </div>
            <?php else: ?>
                <!-- En modo picker, solo mostrar el botón de subir -->
                <div class="toolbar">
                    <button class="btn btn-primary" onclick="openUploadModal()">
                        <i class="fas fa-upload"></i>
                        Subir archivos
                    </button>
                </div>
            <?php endif; ?>
            
            <!-- Media Grid -->
            <div class="media-grid">
                <?php if (empty($contents['folders']) && empty($contents['files'])): ?>
                    <div class="empty-state" style="grid-column: 1 / -1;">
                        <i class="fas fa-folder-open"></i>
                        <p>Esta carpeta está vacía</p>
                    </div>
                <?php endif; ?>
                
                <?php foreach ($contents['folders'] as $folder): ?>
                    <div class="media-item" data-type="folder" data-name="<?php echo htmlspecialchars($folder['name']); ?>" 
                         onclick="navigateToFolder('<?php echo htmlspecialchars($folder['name']); ?>')"
                         style="position: relative;">
                        
                        <?php if ($isAdmin && ($folder['items'] == 0 || !empty($folder['is_orphan']))): ?>
                        <div class="delete-icon" 
                             onclick="deleteSingleFolder(event, '<?php echo htmlspecialchars($folder['name']); ?>', <?php echo !empty($folder['is_orphan']) ? 'true' : 'false'; ?>)"
                             title="<?php echo !empty($folder['is_orphan']) ? 'Eliminar carpeta d\'usuari inexistent (amb contingut)' : 'Eliminar carpeta buida'; ?>">
                            <i class="fas fa-trash"></i>
                        </div>
                        <?php endif; ?>

                        <div class="media-icon folder" style="<?php echo !empty($folder['is_orphan']) ? 'background: linear-gradient(135deg, #aeaeae 0%, #767676 100%);' : ''; ?>">
                            <?php if (!empty($folder['is_orphan'])): ?>
                                <div class="usage-badge" style="background: #dc3545;" title="Usuari no trobat a la base de dades">
                                    <i class="fas fa-user-slash"></i> Usuari inexistent
                                </div>
                            <?php endif; ?>
                            <i class="fas <?php echo !empty($folder['is_orphan']) ? 'fa-folder-minus' : 'fa-folder'; ?>"></i>
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
                         data-in-use="<?php echo !empty($file['is_used']) ? 'true' : 'false'; ?>"
                         onclick="<?php echo $isPickerMode ? 'selectImage(this, event)' : 'toggleSelect(this, event)'; ?>">
                         
                        <?php if (empty($file['is_used']) && !$isPickerMode): ?>
                        <div class="delete-icon" 
                             onclick="deleteSingleFile(event, '<?php echo htmlspecialchars($file['name']); ?>')"
                             title="Eliminar arxiu">
                            <i class="fas fa-trash"></i>
                        </div>
                        <?php endif; ?>

                        <div class="media-icon">
                            <?php if (!empty($file['is_used'])): ?>
                                <div class="usage-badge" title="Aquest arxiu està en ús"><i class="fas fa-link"></i> En ús</div>
                            <?php endif; ?>
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
                <h3>Nueva carpeta</h3>
                <button class="modal-close" onclick="closeModal('createFolderModal')">&times;</button>
            </div>
            <form onsubmit="createFolder(event)">
                <div class="form-group">
                    <label for="folderName">Nombre de la carpeta:</label>
                    <input type="text" id="folderName" name="folder_name" required pattern="[A-Za-z0-9_-]+" 
                           title="Solo letras, números, guiones y guiones bajos">
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%;">Crear</button>
            </form>
        </div>
    </div>
    
    <!-- Modal: Upload Files -->
    <div class="modal" id="uploadModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Subir archivos</h3>
                <button class="modal-close" onclick="closeModal('uploadModal')">&times;</button>
            </div>
            <form onsubmit="uploadFiles(event)">
                <?php if ($isAdmin && !empty($availableFolders)): ?>
                    <div class="form-group">
                        <label for="targetFolder">
                            Carpeta de destino:
                            <span style="font-weight: normal; color: #666; font-size: 12px;">(<?php echo count($availableFolders); ?> carpetas disponibles)</span>
                        </label>
                        <div style="display: flex; gap: 8px; align-items: flex-start;">
                            <select id="targetFolder" name="target_folder" class="form-control" style="flex: 1; padding: 10px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px; font-family: 'Courier New', monospace;">
                                <?php foreach ($availableFolders as $folder): ?>
                                    <option value="<?php echo htmlspecialchars($folder['path']); ?>" 
                                            <?php echo ($folder['path'] === $requestedPath) ? 'selected' : ''; ?>>
                                        <?php 
                                        if ($folder['path'] === '') {
                                            echo '📁 ' . $folder['name'];
                                        } else {
                                            // Mostrar la ruta completa amb format visualment atractiu
                                            echo '📁 img/' . htmlspecialchars($folder['path']);
                                        }
                                        ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button type="button" class="btn btn-secondary" onclick="openCreateFolderFromUpload()" 
                                    style="white-space: nowrap; padding: 10px 14px;" title="Crear nueva carpeta">
                                <i class="fas fa-folder-plus"></i>
                            </button>
                        </div>
                        <small style="display: block; margin-top: 6px; color: #666; font-size: 12px;">
                            <i class="fas fa-info-circle"></i> Puedes subir archivos a cualquier carpeta dentro de img/. Escribe para buscar una carpeta específica.
                        </small>
                    </div>
                <?php endif; ?>
                
                <div class="upload-zone" id="uploadZone" onclick="document.getElementById('fileInput').click()">
                    <i class="fas fa-cloud-upload-alt" style="font-size: 48px; color: #007bff; margin-bottom: 12px;"></i>
                    <p>Haz clic para seleccionar archivos o arrástralos aquí</p>
                    <input type="file" id="fileInput" name="files[]" multiple style="display: none;" onchange="showSelectedFiles()">
                </div>
                <div id="selectedFiles" style="margin-top: 12px; font-size: 13px; color: #666;"></div>
                <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 16px;">Subir</button>
            </form>
        </div>
    </div>
    
    <script>
        const currentPath = '<?php echo addslashes($requestedPath); ?>';
        let selectedItems = new Set();
        
        const isPickerMode = <?php echo $isPickerMode ? 'true' : 'false'; ?>;
        const isAdminPicker = <?php echo ($isPickerMode && isset($_GET['admin_picker']) && $_GET['admin_picker'] == '1') ? 'true' : 'false'; ?>;
        
        function navigateToFolder(folderName) {
            const newPath = currentPath ? currentPath + '/' + folderName : folderName;
            let url = 'gmedia.php?path=' + encodeURIComponent(newPath);
            if (isPickerMode) {
                url += '&picker=1';
                if (isAdminPicker) {
                    url += '&admin_picker=1';
                }
            }
            window.location.href = url;
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
        
        function openCreateFolderFromUpload() {
            // Tancar modal de upload i obrir el de crear carpeta
            closeModal('uploadModal');
            openCreateFolderModal();
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
            
            // Afegir carpeta de destí si existeix (per admins)
            const targetFolderSelect = document.getElementById('targetFolder');
            if (targetFolderSelect) {
                formData.append('target_folder', targetFolderSelect.value);
            }
            
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
                    let message = result.message;
                    
                    // Si s'ha pujat a una carpeta diferent de l'actual, informar l'usuari
                    if (targetFolderSelect && result.target_folder && result.target_folder !== currentPath) {
                        const folderDisplay = result.target_folder === '' ? 'img/ (arrel)' : 'img/' + result.target_folder;
                        message += ' a la carpeta ' + folderDisplay;
                    }
                    
                    showAlert('success', message);
                    closeModal('uploadModal');
                    
                    // Si s'ha pujat a una carpeta diferent, preguntar si vol navegar-hi
                    if (targetFolderSelect && result.target_folder && result.target_folder !== currentPath) {
                        setTimeout(() => {
                            if (confirm('Vols navegar a la carpeta on s\'han pujat els fitxers?')) {
                                window.location.href = 'gmedia.php?path=' + encodeURIComponent(result.target_folder);
                            } else {
                                location.reload();
                            }
                        }, 800);
                    } else {
                        setTimeout(() => location.reload(), 500);
                    }
                } else {
                    showAlert('danger', result.errors.join(', '));
                }
            } catch (error) {
                console.error('Error pujant fitxers:', error);
                showAlert('danger', 'Error de connexió: ' + error.message);
            }
        }

        async function deleteSingleFolder(event, folderName, isOrphan = false) {
            event.stopPropagation();
            
            let confirmMsg = 'Segur que vols eliminar la carpeta buida: "' + folderName + '"?';
            if (isOrphan) {
                confirmMsg = 'ATENCIÓ: Aquesta és una carpeta d\'un usuari que ja no existeix ("' + folderName + '").\n\nSi l\'elimines, s\'esborraran TOTS els arxius que conté permanentment.\n\nEstàs segur que vols continuar?';
            }
            
            if (!confirm(confirmMsg)) {
                return;
            }
            
            const formData = new FormData();
            formData.append('action', 'delete');
            formData.append('item_name', folderName);
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
                if (result.success) {
                    showAlert('success', 'Carpeta eliminada');
                    setTimeout(() => location.reload(), 500);
                } else {
                    showAlert('danger', 'Error: ' + (result.message || 'Error desconegut'));
                }
            } catch (error) {
                console.error('Error eliminant carpeta:', error);
                showAlert('danger', 'Error de connexió: ' + error.message);
            }
        }

        async function deleteSingleFile(event, fileName) {
            event.stopPropagation();
            
            if (!confirm('Segur que vols eliminar l\'arxiu: "' + fileName + '"?')) {
                return;
            }
            
            const formData = new FormData();
            formData.append('action', 'delete');
            formData.append('item_name', fileName);
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
                if (result.success) {
                    showAlert('success', 'Arxiu eliminat');
                    setTimeout(() => location.reload(), 500);
                } else {
                    showAlert('danger', 'Error eliminant arxiu: ' + (result.message || 'Error desconegut'));
                }
            } catch (error) {
                console.error('Error eliminant arxiu:', error);
                showAlert('danger', 'Error de connexió: ' + error.message);
            }
        }
        
        async function deleteSelected() {
            if (selectedItems.size === 0) return;
            
            // Filtrar arxius en ús
            const itemsToDelete = [];
            const skippedItems = [];
            
            for (const itemName of selectedItems) {
                // Busquem l'element DOM per comprovar l'atribut data-in-use
                // Escapem cometes dobles per al selector
                const safeName = itemName.replace(/"/g, '\\"');
                const element = document.querySelector(`.media-item[data-name="${safeName}"]`);
                
                if (element && element.getAttribute('data-in-use') === 'true') {
                    skippedItems.push(itemName);
                } else {
                    itemsToDelete.push(itemName);
                }
            }
            
            if (skippedItems.length > 0) {
                 if (itemsToDelete.length === 0) {
                     showAlert('warning', 'No es poden eliminar els arxius seleccionats perquè estan en ús: ' + skippedItems.join(', '));
                     return;
                 }
                 
                 if (!confirm('Alguns arxius estan en ús i no s\'eliminaran:\n' + skippedItems.join(', ') + '.\n\nVols continuar amb l\'eliminació de la resta (' + itemsToDelete.join(', ') + ')?')) {
                     return;
                 }
            } else {
                 const itemsText = Array.from(selectedItems).join(', ');
                 if (!confirm('Segur que vols eliminar: ' + itemsText + '?')) {
                     return;
                 }
            }
            
            for (const itemName of itemsToDelete) {
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
