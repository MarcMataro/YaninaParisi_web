<?php
/**
 * Gestió del Blog - Panel de Control
 * 
 * Gestió d'articles del blog amb dades de prova i gestió real de categories
 * 
 * @author Marc Mataró
 * @version 1.1.0
 */

session_start();

// Headers per evitar cache
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Verificar autenticación
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    // Si és una petició AJAX, retornar JSON
    if (isset($_POST['action']) || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'No autenticado', 'redirect' => 'index.php']);
        exit;
    }
    // Si no, redirigir normalment
    header('Location: index.php');
    exit;
}

// Incluir classes necessàries
require_once __DIR__ . '/../classes/connexio.php';
require_once __DIR__ . '/../classes/categories.php';
require_once __DIR__ . '/../classes/etiquetes.php';
require_once __DIR__ . '/../classes/entrades.php';
require_once __DIR__ . '/../classes/rel_cat_ent.php';
require_once __DIR__ . '/../classes/rel_eti_ent.php';

// Inicialitzar variables per evitar errors
$articles_prova = []; // Variable temporal per compatibilitat - es pot eliminar després
$stats = [];
$categories = [];
$categories_db = [];
$estats = [];

// Obtener conexión a la base de datos
try {
    $connexio = Connexio::getInstance();
    $pdo = $connexio->getConnexio();
} catch (Exception $e) {
    die("Error de conexión: " . $e->getMessage());
}

// Inicializar objetos
$categoryModel = new Category($pdo);
$etiquetaModel = new Etiqueta($pdo);
$entradaModel = new Entrada($pdo);
$relCatEntModel = new RelacioEntradesCategories($pdo);
$relEtiEntModel = new RelacioEntradesEtiquetes($pdo);

// Processar accions AJAX per categories
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    error_log("=== AJAX REQUEST REBUDA === Action: " . $_POST['action']);
    
    switch ($_POST['action']) {
        case 'test':
            echo json_encode(['success' => true, 'message' => 'Test OK', 'session' => $_SESSION]);
            exit;
            
        case 'crear_categoria':
            $categoryModel->nom_ca = $_POST['nom_ca'] ?? '';
            $categoryModel->nom_es = $_POST['nom_es'] ?? '';
            $categoryModel->descripcio_ca = $_POST['descripcio_ca'] ?? null;
            $categoryModel->descripcion_es = $_POST['descripcion_es'] ?? null;
            $categoryModel->ordre = (int)($_POST['ordre'] ?? 0);
            $categoryModel->activa = true;
            
            $result = $categoryModel->crear();
            if ($result) {
                echo json_encode(['success' => true, 'id' => $result, 'message' => 'Categoría creada correctamente']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al crear la categoría']);
            }
            exit;
            
        case 'actualitzar_categoria':
            $id = (int)($_POST['id'] ?? 0);
            if ($categoryModel->llegirUn($id)) {
                $categoryModel->nom_ca = $_POST['nom_ca'] ?? $categoryModel->nom_ca;
                $categoryModel->nom_es = $_POST['nom_es'] ?? $categoryModel->nom_es;
                $categoryModel->descripcio_ca = $_POST['descripcio_ca'] ?? $categoryModel->descripcio_ca;
                $categoryModel->descripcion_es = $_POST['descripcion_es'] ?? $categoryModel->descripcion_es;
                $categoryModel->ordre = (int)($_POST['ordre'] ?? $categoryModel->ordre);
                $categoryModel->activa = isset($_POST['activa']) ? (bool)$_POST['activa'] : $categoryModel->activa;
                
                if ($categoryModel->actualitzar()) {
                    echo json_encode(['success' => true, 'message' => 'Categoría actualizada correctamente']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Error al actualizar la categoría']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Categoría no encontrada']);
            }
            exit;
            
        case 'eliminar_categoria':
            $id = (int)($_POST['id'] ?? 0);
            if ($categoryModel->eliminar($id)) {
                echo json_encode(['success' => true, 'message' => 'Categoría eliminada correctamente']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al eliminar la categoría']);
            }
            exit;
            
        case 'canviar_estat_categoria':
            $id = (int)($_POST['id'] ?? 0);
            $activa = (bool)($_POST['activa'] ?? false);
            if ($categoryModel->activarDesactivar($id, $activa)) {
                $status = $activa ? 'activada' : 'desactivada';
                echo json_encode(['success' => true, 'message' => "Categoría {$status} correctamente"]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al cambiar el estado de la categoría']);
            }
            exit;
            
        case 'obtenir_categories':
            try {
                $stmt = $categoryModel->llegirTots(null, null, null, 'ordre', 'ASC');
                $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
                error_log("Categories trobades: " . count($categories));
                echo json_encode([
                    'success' => true, 
                    'categories' => $categories,
                    'count' => count($categories)
                ]);
            } catch (Exception $e) {
                error_log("Error obtenint categories: " . $e->getMessage());
                echo json_encode([
                    'success' => false, 
                    'message' => 'Error: ' . $e->getMessage()
                ]);
            }
            exit;
            
        // Accions per etiquetes
        case 'crear_etiqueta':
            $etiquetaModel->nom_ca = $_POST['nom_ca'] ?? '';
            $etiquetaModel->nom_es = $_POST['nom_es'] ?? '';
            $etiquetaModel->descripcio_ca = $_POST['descripcio_ca'] ?? null;
            $etiquetaModel->descripcion_es = $_POST['descripcion_es'] ?? null;
            $etiquetaModel->ordre = (int)($_POST['ordre'] ?? 0);
            $etiquetaModel->activa = true;
            
            $result = $etiquetaModel->crear();
            if ($result) {
                echo json_encode(['success' => true, 'id' => $result, 'message' => 'Etiqueta creada correctamente']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al crear la etiqueta']);
            }
            exit;
            
        case 'actualitzar_etiqueta':
            $id = (int)($_POST['id'] ?? 0);
            if ($etiquetaModel->llegirUn($id)) {
                $etiquetaModel->nom_ca = $_POST['nom_ca'] ?? $etiquetaModel->nom_ca;
                $etiquetaModel->nom_es = $_POST['nom_es'] ?? $etiquetaModel->nom_es;
                $etiquetaModel->descripcio_ca = $_POST['descripcio_ca'] ?? $etiquetaModel->descripcio_ca;
                $etiquetaModel->descripcion_es = $_POST['descripcion_es'] ?? $etiquetaModel->descripcion_es;
                $etiquetaModel->ordre = (int)($_POST['ordre'] ?? $etiquetaModel->ordre);
                $etiquetaModel->activa = isset($_POST['activa']) ? (bool)$_POST['activa'] : $etiquetaModel->activa;
                
                if ($etiquetaModel->actualitzar()) {
                    echo json_encode(['success' => true, 'message' => 'Etiqueta actualizada correctamente']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Error al actualizar la etiqueta']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Etiqueta no encontrada']);
            }
            exit;
            
        case 'eliminar_etiqueta':
            $id = (int)($_POST['id'] ?? 0);
            if ($etiquetaModel->eliminar($id)) {
                echo json_encode(['success' => true, 'message' => 'Etiqueta eliminada correctamente']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al eliminar la etiqueta']);
            }
            exit;
            
        case 'canviar_estat_etiqueta':
            $id = (int)($_POST['id'] ?? 0);
            $activa = (bool)($_POST['activa'] ?? false);
            if ($etiquetaModel->activarDesactivar($id, $activa)) {
                $status = $activa ? 'activada' : 'desactivada';
                echo json_encode(['success' => true, 'message' => "Etiqueta {$status} correctamente"]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al cambiar el estado de la etiqueta']);
            }
            exit;
            
        case 'cercar_etiquetes':
            $terme = $_POST['terme'] ?? '';
            $etiquetes = $etiquetaModel->cercarPerNom($terme, 'es', true, 10);
            echo json_encode(['success' => true, 'etiquetes' => $etiquetes]);
            exit;
            
        case 'obtenir_etiquetes_entrada':
            $entradaId = $_POST['entrada_id'] ?? '';
            if ($entradaId) {
                $etiquetes = $relEtiEntModel->obtenirEtiquetes($entradaId);
                echo json_encode(['success' => true, 'etiquetes' => $etiquetes]);
            } else {
                echo json_encode(['success' => false, 'error' => 'ID d\'entrada requerit']);
            }
            exit;
            
        // Accions per entrades de blog
        /* DUPLICAT - COMENTAT
        case 'crear_entrada':
            // Assignar dades bàsiques
            $entradaModel->titol_ca = $_POST['titol_ca'] ?? '';
            $entradaModel->titol_es = $_POST['titol_es'] ?? '';
            $entradaModel->contingut_ca = $_POST['contingut_ca'] ?? '';
            $entradaModel->contingut_es = $_POST['contingut_es'] ?? '';
            $entradaModel->resum_ca = $_POST['resum_ca'] ?? null;
            $entradaModel->resum_es = $_POST['resum_es'] ?? null;
            $entradaModel->estat = $_POST['estat'] ?? 'esborrany';
            $entradaModel->visible = isset($_POST['visible']) ? (bool)$_POST['visible'] : true;
            $entradaModel->id_autor = $_SESSION['user_id'] ?? 1; // Assumint que tenim user_id a la sessió
            
            // Dades SEO opcionals
            $entradaModel->meta_title_ca = $_POST['meta_title_ca'] ?? null;
            $entradaModel->meta_title_es = $_POST['meta_title_es'] ?? null;
            $entradaModel->meta_description_ca = $_POST['meta_description_ca'] ?? null;
            $entradaModel->meta_description_es = $_POST['meta_description_es'] ?? null;
            $entradaModel->meta_keywords_ca = $_POST['meta_keywords_ca'] ?? null;
            $entradaModel->meta_keywords_es = $_POST['meta_keywords_es'] ?? null;
            
            // Imatges
            $entradaModel->imatge_portada = $_POST['imatge_portada'] ?? null;
            $entradaModel->alt_imatge_ca = $_POST['alt_imatge_ca'] ?? null;
            $entradaModel->alt_imatge_es = $_POST['alt_imatge_es'] ?? null;
            
            // Altres configuracions
            $entradaModel->comentaris_actius = isset($_POST['comentaris_actius']) ? (bool)$_POST['comentaris_actius'] : true;
            $entradaModel->data_publicacio = $_POST['data_publicacio'] ?? null;
            
            $idEntrada = $entradaModel->crear();
            if ($idEntrada) {
                // Assignar categories si s'han especificat
                $categories = json_decode($_POST['categories'] ?? '[]', true);
                if (!empty($categories)) {
                    $relCatEntModel->assignarCategories($idEntrada, $categories);
                }
                
                // Gestionar etiquetes (inclou crear noves)
                $etiquetes = json_decode($_POST['etiquetes'] ?? '[]', true);
                if (!empty($etiquetes)) {
                    $etiquetesIds = [];
                    
                    foreach ($etiquetes as $etiqueta) {
                        if (is_array($etiqueta)) {
                            if ($etiqueta['is_new'] ?? false) {
                                // Crear nova etiqueta
                                $novaEtiqueta = new Etiqueta($pdo);
                                $novaEtiqueta->nom_ca = $etiqueta['nom_ca'] ?? $etiqueta['nom_es'];
                                $novaEtiqueta->nom_es = $etiqueta['nom_es'];
                                $novaEtiqueta->activa = 1;
                                
                                $etiquetaId = $novaEtiqueta->crear();
                                if ($etiquetaId) {
                                    $etiquetesIds[] = $etiquetaId;
                                }
                            } else {
                                // Etiqueta existent
                                if (isset($etiqueta['id']) && $etiqueta['id']) {
                                    $etiquetesIds[] = $etiqueta['id'];
                                }
                            }
                        } else {
                            // Format antic (string) - cercar o crear
                            $stmt = $pdo->prepare("SELECT id_etiqueta FROM etiquetes WHERE nom_es = ? OR nom_ca = ? LIMIT 1");
                            $stmt->execute([$etiqueta, $etiqueta]);
                            $existeix = $stmt->fetch();
                            
                            if ($existeix) {
                                $etiquetesIds[] = $existeix['id_etiqueta'];
                            } else {
                                // Crear nova
                                $novaEtiqueta = new Etiqueta($pdo);
                                $novaEtiqueta->nom_ca = $etiqueta;
                                $novaEtiqueta->nom_es = $etiqueta;
                                $novaEtiqueta->activa = 1;
                                
                                $etiquetaId = $novaEtiqueta->crear();
                                if ($etiquetaId) {
                                    $etiquetesIds[] = $etiquetaId;
                                }
                            }
                        }
                    }
                    
                    if (!empty($etiquetesIds)) {
                        $relEtiEntModel->assignarEtiquetes($idEntrada, $etiquetesIds);
                    }
                }
                
                echo json_encode(['success' => true, 'id' => $idEntrada, 'message' => 'Entrada creada correctamente']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al crear la entrada']);
            }
            exit;
        FI DUPLICAT COMENTAT */
            
        case 'actualitzar_entrada':
            $id = (int)($_POST['id'] ?? 0);
            if ($entradaModel->llegirUn($id)) {
                // Actualitzar dades bàsiques
                $entradaModel->titol_ca = $_POST['titol_ca'] ?? $entradaModel->titol_ca;
                $entradaModel->titol_es = $_POST['titol_es'] ?? $entradaModel->titol_es;
                $entradaModel->contingut_ca = $_POST['contingut_ca'] ?? $entradaModel->contingut_ca;
                $entradaModel->contingut_es = $_POST['contingut_es'] ?? $entradaModel->contingut_es;
                $entradaModel->resum_ca = $_POST['resum_ca'] ?? $entradaModel->resum_ca;
                $entradaModel->resum_es = $_POST['resum_es'] ?? $entradaModel->resum_es;
                $entradaModel->estat = $_POST['estat'] ?? $entradaModel->estat;
                $entradaModel->visible = isset($_POST['visible']) ? (bool)$_POST['visible'] : $entradaModel->visible;
                
                // Actualitzar SEO
                $entradaModel->meta_title_ca = $_POST['meta_title_ca'] ?? $entradaModel->meta_title_ca;
                $entradaModel->meta_title_es = $_POST['meta_title_es'] ?? $entradaModel->meta_title_es;
                $entradaModel->meta_description_ca = $_POST['meta_description_ca'] ?? $entradaModel->meta_description_ca;
                $entradaModel->meta_description_es = $_POST['meta_description_es'] ?? $entradaModel->meta_description_es;
                $entradaModel->meta_keywords_ca = $_POST['meta_keywords_ca'] ?? $entradaModel->meta_keywords_ca;
                $entradaModel->meta_keywords_es = $_POST['meta_keywords_es'] ?? $entradaModel->meta_keywords_es;
                
                // Actualitzar imatges
                $entradaModel->imatge_portada = $_POST['imatge_portada'] ?? $entradaModel->imatge_portada;
                $entradaModel->alt_imatge_ca = $_POST['alt_imatge_ca'] ?? $entradaModel->alt_imatge_ca;
                $entradaModel->alt_imatge_es = $_POST['alt_imatge_es'] ?? $entradaModel->alt_imatge_es;
                
                // Altres configuracions
                $entradaModel->comentaris_actius = isset($_POST['comentaris_actius']) ? (bool)$_POST['comentaris_actius'] : $entradaModel->comentaris_actius;
                $entradaModel->data_publicacio = $_POST['data_publicacio'] ?? $entradaModel->data_publicacio;
                
                if ($entradaModel->actualitzar()) {
                    // Actualitzar relacions amb categories
                    if (isset($_POST['categories']) && is_array($_POST['categories'])) {
                        $relCatEntModel->assignarCategories($id, $_POST['categories']);
                    }
                    
                    // Actualitzar relacions amb etiquetes
                    if (isset($_POST['etiquetes']) && is_array($_POST['etiquetes'])) {
                        $relEtiEntModel->assignarEtiquetes($id, $_POST['etiquetes']);
                    }
                    
                    echo json_encode(['success' => true, 'message' => 'Entrada actualizada correctamente']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Error al actualizar la entrada']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Entrada no encontrada']);
            }
            exit;
            
        case 'obtenir_entrada':
            ob_clean(); // Netejar qualsevol output previ ABANS del header
            header('Content-Type: application/json');
            try {
                $id = (int)($_POST['id'] ?? 0);
                error_log("=== OBTENIR_ENTRADA === ID: $id");
                
                if ($id <= 0) {
                    echo json_encode(['success' => false, 'message' => 'ID no válido']);
                    exit;
                }
                
                $entrada = $entradaModel->llegirUn($id);
                error_log("Entrada trobada: " . ($entrada ? "SI" : "NO"));
                
                if ($entrada) {
                    error_log("Obtenint categories de l'entrada...");
                    // Obtenir categories de l'entrada (només IDs)
                    $categoriesData = $relCatEntModel->obtenirCategoriesEntrada($id, 'es', false);
                    error_log("Categories data: " . print_r($categoriesData, true));
                    $categories = array_column($categoriesData, 'id_category');
                    error_log("Categories IDs: " . print_r($categories, true));
                    
                    error_log("Obtenint etiquetes de l'entrada...");
                    // Obtenir etiquetes de l'entrada (només IDs)
                    $etiquetesData = $relEtiEntModel->obtenirEtiquetesEntrada($id, 'es', false);
                    error_log("Etiquetes data: " . print_r($etiquetesData, true));
                    $etiquetes = array_column($etiquetesData, 'id_etiqueta');
                    error_log("Etiquetes IDs: " . print_r($etiquetes, true));
                    
                    $entrada['categories'] = $categories;
                    $entrada['etiquetes'] = $etiquetes;
                    
                    error_log("Enviant resposta JSON...");
                    echo json_encode(['success' => true, 'entrada' => $entrada]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Entrada no encontrada']);
                }
            } catch (Exception $e) {
                error_log("Error obtenint entrada: " . $e->getMessage());
                echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
            }
            exit;
            
        case 'obtenir_entrades':
            error_log("=== OBTENIR ENTRADES CRIDAT ===");
            try {
                $filtres = [];
                if (isset($_POST['estat'])) $filtres['estat'] = $_POST['estat'];
                if (isset($_POST['visible'])) $filtres['visible'] = (bool)$_POST['visible'];
                if (isset($_POST['cerca'])) $filtres['cerca'] = $_POST['cerca'];
                if (isset($_POST['data_desde'])) $filtres['data_desde'] = $_POST['data_desde'];
                if (isset($_POST['data_fins'])) $filtres['data_fins'] = $_POST['data_fins'];
                
                $limit = isset($_POST['limit']) ? (int)$_POST['limit'] : 50;
                $offset = isset($_POST['offset']) ? (int)$_POST['offset'] : 0;
                $orderBy = $_POST['orderBy'] ?? 'data_creacio';
                $direction = $_POST['direction'] ?? 'DESC';
                
                error_log("Cridant llegirTots amb filtres: " . json_encode($filtres));
                $stmt = $entradaModel->llegirTots($filtres, $limit, $offset, $orderBy, $direction);
                $entrades = $stmt->fetchAll(PDO::FETCH_ASSOC);
                // Afegir categories_noms a cada entrada
                foreach ($entrades as &$entrada) {
                    $cats = $relCatEntModel->obtenirCategoriesEntrada($entrada['id_entrada'], 'es', true);
                    $entrada['categories_noms'] = array_map(function($cat) { return $cat['nom']; }, $cats);
                }
                unset($entrada);
                error_log("Entrades trobades: " . count($entrades));
                echo json_encode(['success' => true, 'entrades' => $entrades]);
            } catch (Exception $e) {
                error_log("Error obtenint entrades: " . $e->getMessage());
                echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
            }
            exit;
            
        case 'canviar_estat_entrada':
            $id = (int)($_POST['id'] ?? 0);
            $nouEstat = $_POST['nou_estat'] ?? '';
            if ($entradaModel->canviarEstat($id, $nouEstat)) {
                echo json_encode(['success' => true, 'message' => "Entrada cambiada a estado: {$nouEstat}"]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al cambiar el estado de la entrada']);
            }
            exit;

        case 'obtenir_categories':
            $categories = $categoryModel->llegirTots(true, null, null, 'ordre', 'ASC')->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode([
                'success' => true, 
                'categories' => $categories
            ]);
            exit;

        case 'obtenir_etiquetes':
            $etiquetes = $etiquetaModel->llegirTots(true, null, null, 'nom_es', 'ASC')->fetchAll(PDO::FETCH_ASSOC);
            // Afegir num_entrades a cada etiqueta
            foreach ($etiquetes as &$eti) {
                $stats = $relEtiEntModel->obtenirEstadistiquesEtiqueta($eti['id_etiqueta']);
                $eti['num_entrades'] = isset($stats[0]['total_entrades']) ? (int)$stats[0]['total_entrades'] : 0;
            }
            unset($eti);
            echo json_encode([
                'success' => true, 
                'etiquetes' => $etiquetes
            ]);
            exit;

        case 'suggerir_etiquetes':
            $text = $_POST['text'] ?? '';
            if ($text && strlen($text) >= 2) {
                $suggestions = $relEtiEntModel->suggerirEtiquetes($text);
                echo json_encode([
                    'success' => true, 
                    'suggestions' => $suggestions
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Text mínim de 2 caràcters requerit']);
            }
            exit;
            
        // Accions per gestionar entrades (CRUD)
        case 'crear_entrada':
            header('Content-Type: application/json');
            error_log("=== CREAR ENTRADA ===");
            error_log("POST data: " . print_r($_POST, true));
            
            try {
                $entradaModel->titol_ca = $_POST['titol_ca'] ?? '';
                $entradaModel->titol_es = $_POST['titol_es'] ?? '';
                $entradaModel->slug_ca = !empty($_POST['slug_ca']) ? $_POST['slug_ca'] : null;
                $entradaModel->slug_es = !empty($_POST['slug_es']) ? $_POST['slug_es'] : null;
                $entradaModel->contingut_ca = $_POST['contingut_ca'] ?? '';
                $entradaModel->contingut_es = $_POST['contingut_es'] ?? '';
                $entradaModel->resum_ca = $_POST['resum_ca'] ?? null;
                $entradaModel->resum_es = $_POST['resum_es'] ?? null;
                $entradaModel->estat = $_POST['estat'] ?? 'esborrany';
                $entradaModel->data_publicacio = !empty($_POST['data_publicacio']) ? $_POST['data_publicacio'] : null;
                $entradaModel->visible = (int)($_POST['visible'] ?? 1);
                $entradaModel->imatge_portada = $_POST['imatge_portada'] ?? null;
                $entradaModel->alt_imatge_ca = $_POST['alt_imatge_ca'] ?? null;
                $entradaModel->alt_imatge_es = $_POST['alt_imatge_es'] ?? null;
                $entradaModel->meta_title_ca = $_POST['meta_title_ca'] ?? null;
                $entradaModel->meta_title_es = $_POST['meta_title_es'] ?? null;
                $entradaModel->meta_description_ca = $_POST['meta_description_ca'] ?? null;
                $entradaModel->meta_description_es = $_POST['meta_description_es'] ?? null;
                $entradaModel->meta_keywords_ca = $_POST['meta_keywords_ca'] ?? null;
                $entradaModel->meta_keywords_es = $_POST['meta_keywords_es'] ?? null;
                $entradaModel->comentaris_actius = (int)($_POST['comentaris_actius'] ?? 0); // Per defecte: desactivats
                $entradaModel->id_autor = $_SESSION['user_id'] ?? 1;
                
                $idEntrada = $entradaModel->crear();
                
                if ($idEntrada) {
                    if (isset($_POST['categories']) && is_array($_POST['categories'])) {
                        foreach ($_POST['categories'] as $idCategoria) {
                            $relCatEntModel->crearRelacio($idEntrada, (int)$idCategoria);
                        }
                    }
                    if (isset($_POST['etiquetes']) && is_array($_POST['etiquetes'])) {
                        foreach ($_POST['etiquetes'] as $idEtiqueta) {
                            $relEtiEntModel->crearRelacio($idEntrada, (int)$idEtiqueta);
                        }
                    }
                    echo json_encode(['success' => true, 'id' => $idEntrada, 'message' => 'Entrada creada correctamente']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Error al crear la entrada']);
                }
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
            }
            exit;
            
        case 'actualitzar_entrada':
            try {
                $id = (int)($_POST['id'] ?? 0);
                if ($entradaModel->llegirUn($id)) {
                    $entradaModel->titol_ca = $_POST['titol_ca'] ?? $entradaModel->titol_ca;
                    $entradaModel->titol_es = $_POST['titol_es'] ?? $entradaModel->titol_es;
                    $entradaModel->slug_ca = !empty($_POST['slug_ca']) ? $_POST['slug_ca'] : $entradaModel->slug_ca;
                    $entradaModel->slug_es = !empty($_POST['slug_es']) ? $_POST['slug_es'] : $entradaModel->slug_es;
                    $entradaModel->contingut_ca = $_POST['contingut_ca'] ?? $entradaModel->contingut_ca;
                    $entradaModel->contingut_es = $_POST['contingut_es'] ?? $entradaModel->contingut_es;
                    $entradaModel->resum_ca = $_POST['resum_ca'] ?? $entradaModel->resum_ca;
                    $entradaModel->resum_es = $_POST['resum_es'] ?? $entradaModel->resum_es;
                    $entradaModel->estat = $_POST['estat'] ?? $entradaModel->estat;
                    $entradaModel->data_publicacio = !empty($_POST['data_publicacio']) ? $_POST['data_publicacio'] : $entradaModel->data_publicacio;
                    $entradaModel->visible = (int)($_POST['visible'] ?? $entradaModel->visible);
                    $entradaModel->imatge_portada = $_POST['imatge_portada'] ?? $entradaModel->imatge_portada;
                    $entradaModel->alt_imatge_ca = $_POST['alt_imatge_ca'] ?? $entradaModel->alt_imatge_ca;
                    $entradaModel->alt_imatge_es = $_POST['alt_imatge_es'] ?? $entradaModel->alt_imatge_es;
                    $entradaModel->meta_title_ca = $_POST['meta_title_ca'] ?? $entradaModel->meta_title_ca;
                    $entradaModel->meta_title_es = $_POST['meta_title_es'] ?? $entradaModel->meta_title_es;
                    $entradaModel->meta_description_ca = $_POST['meta_description_ca'] ?? $entradaModel->meta_description_ca;
                    $entradaModel->meta_description_es = $_POST['meta_description_es'] ?? $entradaModel->meta_description_es;
                    $entradaModel->meta_keywords_ca = $_POST['meta_keywords_ca'] ?? $entradaModel->meta_keywords_ca;
                    $entradaModel->meta_keywords_es = $_POST['meta_keywords_es'] ?? $entradaModel->meta_keywords_es;
                    $entradaModel->comentaris_actius = (int)($_POST['comentaris_actius'] ?? 0); // Per defecte: desactivats
                    
                    if ($entradaModel->actualitzar()) {
                        // Eliminar relacions anteriors
                        $relCatEntModel->eliminarRelacionsEntrada($id);
                        $relEtiEntModel->eliminarRelacionsEntrada($id);
                        
                        // Afegir noves categories
                        if (isset($_POST['categories']) && is_array($_POST['categories'])) {
                            foreach ($_POST['categories'] as $idCategoria) {
                                $relCatEntModel->crearRelacio($id, (int)$idCategoria);
                            }
                        }
                        
                        // Afegir noves etiquetes
                        if (isset($_POST['etiquetes']) && is_array($_POST['etiquetes'])) {
                            foreach ($_POST['etiquetes'] as $idEtiqueta) {
                                $relEtiEntModel->crearRelacio($id, (int)$idEtiqueta);
                            }
                        }
                        
                        echo json_encode(['success' => true, 'message' => 'Entrada actualizada correctamente']);
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Error al actualizar la entrada']);
                    }
                } else {
                    echo json_encode(['success' => false, 'message' => 'Entrada no encontrada']);
                }
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
            }
            exit;
            
        case 'eliminar_entrada':
            header('Content-Type: application/json');
            error_log("=== ELIMINAR ENTRADA ===");
            try {
                $id = (int)($_POST['id'] ?? 0);
                error_log("ID a eliminar: " . $id);
                
                if ($entradaModel->eliminar($id)) {
                    error_log("Entrada eliminada correctament");
                    echo json_encode(['success' => true, 'message' => 'Entrada eliminada correctamente']);
                } else {
                    error_log("Error eliminant entrada");
                    echo json_encode(['success' => false, 'message' => 'Error al eliminar la entrada']);
                }
            } catch (Exception $e) {
                error_log("Excepció eliminant entrada: " . $e->getMessage());
                echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
            }
            exit;
            
        case 'obtenir_categories_entrada':
            try {
                $idEntrada = (int)($_GET['id_entrada'] ?? $_POST['id_entrada'] ?? 0);
                $categories = $relCatEntModel->categoriesPerEntrada($idEntrada)->fetchAll(PDO::FETCH_COLUMN);
                echo json_encode(['success' => true, 'categories' => $categories]);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
            }
            exit;
            
        case 'obtenir_etiquetes_entrada':
            try {
                $idEntrada = (int)($_GET['id_entrada'] ?? $_POST['id_entrada'] ?? 0);
                $etiquetes = $relEtiEntModel->etiquetesPerEntrada($idEntrada)->fetchAll(PDO::FETCH_COLUMN);
                echo json_encode(['success' => true, 'etiquetes' => $etiquetes]);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
            }
            exit;
    }
}

// Obtenir categories per al select
$stmt_categories = $categoryModel->llegirTots(true, null, null, 'ordre', 'ASC');
$categories_db = $stmt_categories->fetchAll(PDO::FETCH_ASSOC);

// Preparar array de categories per als selects (afegir opció "Todas")
$categories = ['Todas'];
foreach ($categories_db as $cat) {
    $categories[] = $cat['nom_es']; // Usar el nombre en español
}

// Obtenir estadístiques reals de la base de dades
try {
    // Verificar si la taula blog_entrades existeix
    $stmt = $pdo->query("SHOW TABLES LIKE 'blog_entrades'");
    if ($stmt->rowCount() > 0) {
        $stmt = $pdo->query("SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN estat = 'publicat' THEN 1 ELSE 0 END) as publicats,
            SUM(CASE WHEN estat = 'esborrany' THEN 1 ELSE 0 END) as esborranys,
            SUM(COALESCE(visualitzacions, 0)) as total_visualitzacions
            FROM blog_entrades");
        $statsData = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $stats = [
            'total_articles' => (int)($statsData['total'] ?? 0),
            'publicats' => (int)($statsData['publicats'] ?? 0),
            'esborranys' => (int)($statsData['esborranys'] ?? 0),
            'total_visualitzacions' => (int)($statsData['total_visualitzacions'] ?? 0),
            'total_comentaris' => 0 // Comentaris no implementats encara
        ];
    } else {
        throw new Exception("Taula blog_entrades no existeix");
    }
} catch (Exception $e) {
    // Estadístiques per defecte si hi ha error
    error_log("Error obtenint estadístiques: " . $e->getMessage());
    $stats = [
        'total_articles' => 0,
        'publicats' => 0,
        'esborranys' => 0,
        'total_visualitzacions' => 0,
        'total_comentaris' => 0
    ];
}

$estats = ['Todos', 'Publicado', 'Borrador', 'Revisar'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión del Blog - Panel de Control</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Libre+Baskerville:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/gblog.css?v=2.2">
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Bar -->
        <header class="top-bar">
            <div class="top-bar-left">
                <button class="menu-toggle" id="menuToggle">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="top-bar-info">
                    <h1><i class="fas fa-blog"></i> Gestión del Blog</h1>
                    <p class="page-description">Gestiona los artículos de tu blog profesional</p>
                </div>
            </div>
            <div class="top-bar-right">
                <div class="user-profile">
                    <img src="../img/Logo.png" alt="Profile" class="profile-img">
                    <span class="profile-name"><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Usuario'); ?></span>
                </div>
            </div>
        </header>

        <!-- Contenedor principal con padding -->
        <div class="content-wrapper">
        
            <!-- Dashboard Stats Cards -->
            <section class="dashboard-stats">
                <div class="stat-card">
                    <div class="stat-icon blue">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <div class="stat-content">
                        <span class="stat-number"><?php echo $stats['total_articles']; ?></span>
                        <span class="stat-label">Total Artículos</span>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon green">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-content">
                        <span class="stat-number"><?php echo $stats['publicats']; ?></span>
                        <span class="stat-label">Publicados</span>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon orange">
                        <i class="fas fa-edit"></i>
                    </div>
                    <div class="stat-content">
                        <span class="stat-number"><?php echo $stats['esborranys']; ?></span>
                        <span class="stat-label">Borradores</span>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon purple">
                        <i class="fas fa-eye"></i>
                    </div>
                    <div class="stat-content">
                        <span class="stat-number"><?php echo number_format($stats['total_visualitzacions']); ?></span>
                        <span class="stat-label">Visualizaciones</span>
                    </div>
                </div>
            </section>

            <!-- Sistema de Tabs -->
            <div class="tabs-container">
                <div class="tabs-header">
                    <button class="tab-btn active" data-tab="entradas" onclick="switchTab('entradas')">
                        <i class="fas fa-file-alt"></i>
                        <span>Entradas</span>
                    </button>
                    <button class="tab-btn" data-tab="categories" onclick="switchTab('categories')">
                        <i class="fas fa-folder"></i>
                        <span>Categorías</span>
                    </button>
                    <button class="tab-btn" data-tab="etiquetes" onclick="switchTab('etiquetes')">
                        <i class="fas fa-tags"></i>
                        <span>Etiquetas</span>
                    </button>
                </div>

                <div class="tabs-content">
                    <!-- Tab: Entradas -->
                    <div class="tab-panel active" id="tab-entradas">
                        <div class="tab-panel-header">
                            <h2>Gestión de Entradas</h2>
                            <button class="btn btn-primary" onclick="obrirModalEntrada()">
                                <i class="fas fa-plus"></i> Nueva Entrada
                            </button>
                        </div>
                        <div class="tab-panel-body no-side-padding">
                            <!-- Filtres d'entrades -->
                            <form id="filtres-entrades" class="filters-bar" style="margin-bottom: 24px; display: flex; gap: 18px; flex-wrap: wrap; align-items: center;">
                                <div>
                                    <label for="filtre-estat">Estado:</label>
                                    <select id="filtre-estat" name="estat" class="form-control">
                                        <option value="">Todos</option>
                                        <option value="publicat">Publicada</option>
                                        <option value="esborrany">Borrador</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="filtre-categoria">Categoría:</label>
                                    <select id="filtre-categoria" name="categoria" class="form-control">
                                        <option value="">Todas</option>
                                    </select>
                                </div>
                                
                                <button type="button" class="btn btn-secondary" onclick="aplicarFiltresEntrades()"><i class="fas fa-filter"></i> Filtrar</button>
                                <button type="button" class="btn btn-light" onclick="resetFiltresEntrades()"><i class="fas fa-times"></i> Limpiar</button>
                            </form>
                            <!-- Llista d'entrades -->
                            <div id="entrades-list">
                                <div class="loading-container">
                                    <i class="fas fa-spinner fa-spin"></i>
                                    <p>Cargando entradas...</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tab: Categories -->
                    <div class="tab-panel" id="tab-categories">
                        <div class="tab-panel-header">
                            <h2>Gestión de Categorías</h2>
                            <button class="btn btn-primary" onclick="obrirModalCategoria()">
                                <i class="fas fa-plus"></i> Nueva Categoría
                            </button>
                        </div>
                        <div class="tab-panel-body no-side-padding">
                            <!-- Llista de categories -->
                            <div id="categories-list">
                                <div class="loading-container">
                                    <i class="fas fa-spinner fa-spin"></i>
                                    <p>Cargando categorías...</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tab: Etiquetes -->
                    <div class="tab-panel" id="tab-etiquetes">
                        <div class="tab-panel-header">
                            <h2>Gestión de Etiquetas</h2>
                            <button class="btn btn-primary" onclick="obrirModalEtiqueta()">
                                <i class="fas fa-plus"></i> Nueva Etiqueta
                            </button>
                        </div>
                        <div class="tab-panel-body no-side-padding">
                            <!-- Llista d'etiquetes -->
                            <div id="etiquetes-list">
                                <div class="loading-container">
                                    <i class="fas fa-spinner fa-spin"></i>
                                    <p>Cargando etiquetas...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div><!-- .content-wrapper -->
    </div><!-- .main-content -->

    <!-- MODAL CATEGORIA -->
    <div id="modalCategoria" class="modal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 id="modalCategoriaTitle">
                        <i class="fas fa-folder"></i>
                        <span>Nueva Categoría</span>
                    </h3>
                    <button class="btn-close-modal" onclick="tancarModalCategoria()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <div class="modal-body">
                    <form id="formCategoria">
                        <input type="hidden" id="categoria_id" name="id">
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="categoria_nom_ca">
                                    <span class="flag">🔵</span> Nombre en Catalán *
                                </label>
                                <input type="text" id="categoria_nom_ca" name="nom_ca" required
                                       placeholder="Nom de la categoria en català">
                            </div>
                            
                            <div class="form-group">
                                <label for="categoria_nom_es">
                                    <span class="flag">🟡</span> Nombre en Español *
                                </label>
                                <input type="text" id="categoria_nom_es" name="nom_es" required
                                       placeholder="Nombre de la categoría en español">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="categoria_desc_ca">
                                    <span class="flag">🔵</span> Descripción en Catalán
                                </label>
                                <textarea id="categoria_desc_ca" name="descripcio_ca" rows="3"
                                          placeholder="Descripció opcional en català"></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label for="categoria_desc_es">
                                    <span class="flag">🟡</span> Descripción en Español
                                </label>
                                <textarea id="categoria_desc_es" name="descripcion_es" rows="3"
                                          placeholder="Descripción opcional en español"></textarea>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="categoria_ordre">
                                    <i class="fas fa-sort"></i> Orden
                                </label>
                                <input type="number" id="categoria_ordre" name="ordre" value="0" min="0"
                                       placeholder="Orden de visualización">
                            </div>
                            
                            <div class="form-group">
                                <label class="checkbox-label">
                                    <input type="checkbox" id="categoria_activa" name="activa" checked>
                                    <span>Categoría activa</span>
                                </label>
                            </div>
                        </div>
                    </form>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="tancarModalCategoria()">
                        Cancelar
                    </button>
                    <button type="button" class="btn btn-primary" onclick="guardarCategoria()">
                        <i class="fas fa-save"></i> Guardar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL ETIQUETA -->
    <div id="modalEtiqueta" class="modal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 id="modalEtiquetaTitle">
                        <i class="fas fa-tag"></i>
                        <span>Nueva Etiqueta</span>
                    </h3>
                    <button class="btn-close-modal" onclick="tancarModalEtiqueta()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <div class="modal-body">
                    <form id="formEtiqueta">
                        <input type="hidden" id="etiqueta_id" name="id">
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="etiqueta_nom_ca">
                                    <span class="flag">🔵</span> Nombre en Catalán *
                                </label>
                                <input type="text" id="etiqueta_nom_ca" name="nom_ca" required
                                       placeholder="Nom de l'etiqueta en català">
                            </div>
                            
                            <div class="form-group">
                                <label for="etiqueta_nom_es">
                                    <span class="flag">🟡</span> Nombre en Español *
                                </label>
                                <input type="text" id="etiqueta_nom_es" name="nom_es" required
                                       placeholder="Nombre de la etiqueta en español">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="etiqueta_desc_ca">
                                    <span class="flag">🔵</span> Descripción en Catalán
                                </label>
                                <textarea id="etiqueta_desc_ca" name="descripcio_ca" rows="3"
                                          placeholder="Descripció opcional en català"></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label for="etiqueta_desc_es">
                                    <span class="flag">🟡</span> Descripción en Español
                                </label>
                                <textarea id="etiqueta_desc_es" name="descripcion_es" rows="3"
                                          placeholder="Descripción opcional en español"></textarea>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="etiqueta_ordre">
                                    <i class="fas fa-sort"></i> Orden
                                </label>
                                <input type="number" id="etiqueta_ordre" name="ordre" value="0" min="0"
                                       placeholder="Orden de visualización">
                            </div>
                            
                            <div class="form-group">
                                <label class="checkbox-label">
                                    <input type="checkbox" id="etiqueta_activa" name="activa" checked>
                                    <span>Etiqueta activa</span>
                                </label>
                            </div>
                        </div>
                    </form>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="tancarModalEtiqueta()">
                        Cancelar
                    </button>
                    <button type="button" class="btn btn-primary" onclick="guardarEtiqueta()">
                        <i class="fas fa-save"></i> Guardar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Entrada (AMPLE) -->
    <div id="modalEntrada" class="modal modal-wide">
        <div class="modal-overlay" onclick="tancarModalEntrada()"></div>
        <div class="modal-container">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 id="modalEntradaTitle">
                        <i class="fas fa-file-alt"></i>
                        <span>Nueva Entrada</span>
                    </h3>
                    <button class="modal-close" onclick="tancarModalEntrada()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <div class="modal-body modal-body-wide">
                    <form id="formEntrada">
                        <input type="hidden" id="entrada_id" name="id">
                        
                        <!-- Grid de 2 columnes per idiomes -->
                        <div class="form-grid-2cols">
                            <!-- Columna CATALÀ -->
                            <div class="form-column">
                                <h4 class="column-title">
                                    <i class="fas fa-language"></i> Català
                                </h4>
                                
                                <div class="form-group">
                                    <label for="entrada_titol_ca">
                                        Títol *
                                    </label>
                                    <input type="text" 
                                           id="entrada_titol_ca" 
                                           name="titol_ca" 
                                           class="form-control" 
                                           required
                                           placeholder="Títol de l'entrada en català">
                                </div>
                                
                                <div class="form-group">
                                    <label for="entrada_resum_ca">
                                        Resum / Extracte
                                    </label>
                                    <textarea id="entrada_resum_ca" 
                                              name="resum_ca" 
                                              class="form-control" 
                                              rows="3"
                                              placeholder="Resum curt de l'entrada..."></textarea>
                                </div>
                                
                                <div class="form-group">
                                    <label for="entrada_contingut_ca">
                                        Contingut *
                                    </label>
                                    <textarea id="entrada_contingut_ca" 
                                              name="contingut_ca" 
                                              class="form-control" 
                                              rows="8"
                                              required
                                              placeholder="Contingut complet de l'entrada..."></textarea>
                                </div>
                                
                                <!-- SEO Català -->
                                <div class="form-section-divider">
                                    <span><i class="fas fa-search"></i> SEO</span>
                                </div>
                                
                                <div class="form-group">
                                    <label for="entrada_meta_title_ca">
                                        Meta Títol
                                    </label>
                                    <input type="text" 
                                           id="entrada_meta_title_ca" 
                                           name="meta_title_ca" 
                                           class="form-control" 
                                           placeholder="Títol per SEO (màx. 60 caràcters)">
                                </div>
                                
                                <div class="form-group">
                                    <label for="entrada_meta_desc_ca">
                                        Meta Descripció
                                    </label>
                                    <textarea id="entrada_meta_desc_ca" 
                                              name="meta_description_ca" 
                                              class="form-control" 
                                              rows="2"
                                              placeholder="Descripció per SEO (màx. 160 caràcters)"></textarea>
                                </div>
                                
                                <div class="form-group">
                                    <label for="entrada_meta_keywords_ca">
                                        Paraules clau
                                    </label>
                                    <input type="text" 
                                           id="entrada_meta_keywords_ca" 
                                           name="meta_keywords_ca" 
                                           class="form-control" 
                                           placeholder="paraula1, paraula2, paraula3">
                                </div>
                            </div>
                            
                            <!-- Columna ESPANYOL -->
                            <div class="form-column">
                                <h4 class="column-title">
                                    <i class="fas fa-language"></i> Español
                                </h4>
                                
                                <div class="form-group">
                                    <label for="entrada_titol_es">
                                        Título *
                                    </label>
                                    <input type="text" 
                                           id="entrada_titol_es" 
                                           name="titol_es" 
                                           class="form-control" 
                                           required
                                           placeholder="Título de la entrada en español">
                                </div>
                                
                                <div class="form-group">
                                    <label for="entrada_resum_es">
                                        Resumen / Extracto
                                    </label>
                                    <textarea id="entrada_resum_es" 
                                              name="resum_es" 
                                              class="form-control" 
                                              rows="3"
                                              placeholder="Resumen corto de la entrada..."></textarea>
                                </div>
                                
                                <div class="form-group">
                                    <label for="entrada_contingut_es">
                                        Contenido *
                                    </label>
                                    <textarea id="entrada_contingut_es" 
                                              name="contingut_es" 
                                              class="form-control" 
                                              rows="8"
                                              required
                                              placeholder="Contenido completo de la entrada..."></textarea>
                                </div>
                                
                                <!-- SEO Español -->
                                <div class="form-section-divider">
                                    <span><i class="fas fa-search"></i> SEO</span>
                                </div>
                                
                                <div class="form-group">
                                    <label for="entrada_meta_title_es">
                                        Meta Título
                                    </label>
                                    <input type="text" 
                                           id="entrada_meta_title_es" 
                                           name="meta_title_es" 
                                           class="form-control" 
                                           placeholder="Título para SEO (máx. 60 caracteres)">
                                </div>
                                
                                <div class="form-group">
                                    <label for="entrada_meta_desc_es">
                                        Meta Descripción
                                    </label>
                                    <textarea id="entrada_meta_desc_es" 
                                              name="meta_description_es" 
                                              class="form-control" 
                                              rows="2"
                                              placeholder="Descripción para SEO (máx. 160 caracteres)"></textarea>
                                </div>
                                
                                <div class="form-group">
                                    <label for="entrada_meta_keywords_es">
                                        Palabras clave
                                    </label>
                                    <input type="text" 
                                           id="entrada_meta_keywords_es" 
                                           name="meta_keywords_es" 
                                           class="form-control" 
                                           placeholder="palabra1, palabra2, palabra3">
                                </div>
                            </div>
                        </div>
                        
                        <!-- Secció general (full width) -->
                        <div class="form-section-divider">
                            <span><i class="fas fa-cog"></i> Configuración General</span>
                        </div>
                        
                        <div class="form-grid-3cols">
                            <div class="form-group">
                                <label for="entrada_estat">
                                    Estado *
                                </label>
                                <select id="entrada_estat" name="estat" class="form-control" required>
                                    <option value="esborrany">Borrador</option>
                                    <option value="revisio">En Revisión</option>
                                    <option value="publicat">Publicado</option>
                                    <option value="programat">Programado</option>
                                    <option value="arxivat">Archivado</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="entrada_data_publicacio">
                                    Fecha de Publicación
                                </label>
                                <input type="datetime-local" 
                                       id="entrada_data_publicacio" 
                                       name="data_publicacio" 
                                       class="form-control">
                            </div>
                            
                            <div class="form-group">
                                <label for="entrada_visible">
                                    Visibilidad
                                </label>
                                <select id="entrada_visible" name="visible" class="form-control">
                                    <option value="1">Visible</option>
                                    <option value="0">Oculta</option>
                                </select>
                            </div>
                        </div>
                        
                        <!-- Imatge i multimèdia -->
                        <div class="form-section-divider">
                            <span><i class="fas fa-image"></i> Imagen de Portada</span>
                        </div>
                        
                        <div class="form-grid-2cols">
                            <div class="form-group">
                                <label for="entrada_imatge">
                                    URL Imagen
                                </label>
                                <input type="text" 
                                       id="entrada_imatge" 
                                       name="imatge_portada" 
                                       class="form-control" 
                                       placeholder="https://ejemplo.com/imagen.jpg">
                            </div>
                            
                            <div class="form-group">
                                <label for="entrada_alt_ca">
                                    Texto Alt (CA) / (ES)
                                </label>
                                <div class="form-inline-group">
                                    <input type="text" 
                                           id="entrada_alt_ca" 
                                           name="alt_imatge_ca" 
                                           class="form-control" 
                                           placeholder="Alt en català">
                                    <input type="text" 
                                           id="entrada_alt_es" 
                                           name="alt_imatge_es" 
                                           class="form-control" 
                                           placeholder="Alt en español">
                                </div>
                            </div>
                        </div>
                        
                        <!-- Categories i Etiquetes -->
                        <div class="form-section-divider">
                            <span><i class="fas fa-tags"></i> Categorías y Etiquetas</span>
                        </div>
                        
                        <div class="form-grid-2cols">
                            <div class="form-group">
                                <label>
                                    Categorías
                                </label>
                                <div id="categories-selector" class="checkbox-group">
                                    <!-- Les categories es carregaran dinàmicament -->
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label>
                                    Etiquetas
                                </label>
                                <div id="etiquetes-selector" class="checkbox-group">
                                    <!-- Les etiquetes es carregaran dinàmicament -->
                                </div>
                            </div>
                        </div>
                        
                        <!-- Camp ocult per comentaris (sempre desactivats) -->
                        <input type="hidden" name="comentaris_actius" value="0">
                    </form>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="tancarModalEntrada()">
                        Cancelar
                    </button>
                    <button type="button" class="btn btn-primary" onclick="guardarEntrada()">
                        <i class="fas fa-save"></i> Guardar Entrada
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="js/dashboard.js"></script>
    <script src="js/gblog-categories.js"></script>
    <script src="js/gblog-etiquetes.js"></script>
    <script src="js/gblog-entrades-simple.js"></script>
    <script src="js/gblog-tabs.js"></script>
</body>
</html>