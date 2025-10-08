<?php
/**
 * Interfaz de gestión de Backlinks Off-Page SEO
 * 
 * Permite listar, crear, editar, verificar y analizar backlinks
 * 
 * @author Marc Mataró
 * @version 1.0.0
 */

// Variables de control
$modo_vista = $_GET['view'] ?? 'list'; // list | edit | create | stats
$backlink_edit = null;
$filtro_estado = $_GET['estado'] ?? 'all';
$filtro_tipo = $_GET['tipo_backlink'] ?? 'all';
$filtro_campana = $_GET['campana'] ?? 'all';

// Si estem editant, carregar el backlink
if ($modo_vista === 'edit' && isset($_GET['id_backlink'])) {
    try {
        $backlink_edit = new SEO_OffPage_Links($_GET['id_backlink']);
    } catch (Exception $e) {
        $error_message = "Error al carregar el backlink: " . $e->getMessage();
        $modo_vista = 'list';
    }
}

// Obtenir backlinks amb filtres
$filtros_backlinks = [];
if ($filtro_estado !== 'all') {
    $filtros_backlinks['estado'] = $filtro_estado;
}
if ($filtro_tipo !== 'all') {
    $filtros_backlinks['tipo_backlink'] = $filtro_tipo;
}
if ($filtro_campana !== 'all') {
    $filtros_backlinks['campana_seo'] = $filtro_campana;
}

$backlinks = SEO_OffPage_Links::llistarBacklinks($filtros_backlinks, 'fecha_descubrimiento', 'DESC', 100);

// Obtener estadísticas globales
$stats_offpage = SEO_OffPage_Links::obtenirEstadistiquesGlobals();

// Obtener campañas únicas para el filtro
try {
    $conn = Connexio::getInstance();
    $pdo = $conn->getConnexio();
    $stmt = $pdo->query("SELECT DISTINCT campana_seo FROM seo_offpage WHERE campana_seo IS NOT NULL ORDER BY campana_seo");
    $campanyas = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) {
    $campanyas = [];
}
?>

<!-- ============================================ -->
<!-- VISTA: ESTADÍSTICAS                          -->
<!-- ============================================ -->
<?php if ($modo_vista === 'stats'): ?>

<div class="offpage-stats-container">
    <div class="stats-header">
        <h2><i class="fas fa-chart-line"></i> Estadísticas de Backlinks</h2>
        <button onclick="window.location.href='gseo.php?tab=offpage&subtab=backlinks&view=list'" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver al listado
        </button>
    </div>
    
    <?php if ($stats_offpage): ?>
    
    <!-- Resum Global -->
    <div class="stats-summary">
        <div class="stat-box stat-primary">
            <i class="fas fa-link"></i>
            <div class="stat-content">
                <div class="stat-value"><?php echo $stats_offpage['total']; ?></div>
                <div class="stat-label">Total Backlinks</div>
            </div>
        </div>
        
        <div class="stat-box stat-success">
            <i class="fas fa-check-circle"></i>
            <div class="stat-content">
                <div class="stat-value"><?php echo $stats_offpage['activos']; ?></div>
                <div class="stat-label">Activos</div>
            </div>
        </div>
        
        <div class="stat-box stat-warning">
            <i class="fas fa-exclamation-triangle"></i>
            <div class="stat-content">
                <div class="stat-value"><?php echo $stats_offpage['perdidos']; ?></div>
                <div class="stat-label">Perdidos</div>
            </div>
        </div>
        
        <div class="stat-box stat-danger">
            <i class="fas fa-unlink"></i>
            <div class="stat-content">
                <div class="stat-value"><?php echo $stats_offpage['rotos']; ?></div>
                <div class="stat-label">Rotos</div>
            </div>
        </div>
        
        <div class="stat-box stat-info">
            <i class="fas fa-trophy"></i>
            <div class="stat-content">
                <div class="stat-value"><?php echo round($stats_offpage['da_promedio'] ?? 0); ?></div>
                <div class="stat-label">DA Promedio</div>
            </div>
        </div>
        
        <div class="stat-box stat-purple">
            <i class="fas fa-euro-sign"></i>
            <div class="stat-content">
                <div class="stat-value"><?php echo number_format($stats_offpage['valor_total'] ?? 0, 0); ?>€</div>
                <div class="stat-label">Valor Total</div>
            </div>
        </div>
    </div>
    
    <!-- Score Global -->
    <div class="score-section">
        <div class="score-card-large">
            <h3>Puntuación Off-Page SEO</h3>
            <div class="score-circle-large">
                <svg width="180" height="180">
                    <circle cx="90" cy="90" r="75" fill="none" stroke="#e9ecef" stroke-width="12"></circle>
                    <circle cx="90" cy="90" r="75" fill="none" 
                            stroke="<?php echo $stats_offpage['score_global'] >= 75 ? '#27ae60' : ($stats_offpage['score_global'] >= 60 ? '#c2b280' : '#e67e22'); ?>" 
                            stroke-width="12" 
                            stroke-dasharray="471" 
                            stroke-dashoffset="<?php echo 471 - (471 * $stats_offpage['score_global'] / 100); ?>" 
                            transform="rotate(-90 90 90)"></circle>
                </svg>
                <div class="score-number-large"><?php echo $stats_offpage['score_global']; ?><span>/100</span></div>
            </div>
            <p class="score-status-large"><?php echo htmlspecialchars($stats_offpage['estado_global']); ?></p>
        </div>
        
        <!-- DoFollow vs NoFollow -->
        <div class="chart-card">
            <h3><i class="fas fa-link"></i> DoFollow vs NoFollow</h3>
            <div class="chart-bars">
                <div class="chart-bar-row">
                    <span class="bar-label">DoFollow</span>
                    <div class="bar-container">
                        <div class="bar-fill bar-success" style="width: <?php echo ($stats_offpage['total'] > 0) ? round(($stats_offpage['dofollow'] / $stats_offpage['total']) * 100) : 0; ?>%"></div>
                    </div>
                    <span class="bar-value"><?php echo $stats_offpage['dofollow']; ?></span>
                </div>
                <div class="chart-bar-row">
                    <span class="bar-label">NoFollow</span>
                    <div class="bar-container">
                        <div class="bar-fill bar-warning" style="width: <?php echo ($stats_offpage['total'] > 0) ? round(($stats_offpage['nofollow'] / $stats_offpage['total']) * 100) : 0; ?>%"></div>
                    </div>
                    <span class="bar-value"><?php echo $stats_offpage['nofollow']; ?></span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Gràfics -->
    <div class="charts-grid">
        <!-- Per Tipus -->
        <div class="chart-card">
            <h3><i class="fas fa-chart-pie"></i> Distribución por tipos</h3>
            <div class="chart-list">
                <?php foreach ($stats_offpage['por_tipo'] as $tipo): ?>
                <div class="chart-item">
                    <span class="chart-label"><?php echo ucfirst(str_replace('_', ' ', $tipo['tipo_backlink'])); ?></span>
                    <div class="chart-progress">
                        <div class="chart-progress-bar" style="width: <?php echo ($stats_offpage['activos'] > 0) ? round(($tipo['cantidad'] / $stats_offpage['activos']) * 100) : 0; ?>%"></div>
                    </div>
                    <span class="chart-value"><?php echo $tipo['cantidad']; ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Per Relevància -->
        <div class="chart-card">
            <h3><i class="fas fa-star"></i> Distribución por relevancia</h3>
            <div class="chart-list">
                <?php foreach ($stats_offpage['por_relevancia'] as $rel): ?>
                <div class="chart-item">
                    <span class="chart-label"><?php echo ucfirst($rel['relevancia_tematica']); ?></span>
                    <div class="chart-progress">
                        <div class="chart-progress-bar" style="width: <?php echo ($stats_offpage['activos'] > 0) ? round(($rel['cantidad'] / $stats_offpage['activos']) * 100) : 0; ?>%"></div>
                    </div>
                    <span class="chart-value"><?php echo $rel['cantidad']; ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    
    <!-- Top Dominis -->
    <div class="top-domains-section">
        <h3><i class="fas fa-trophy"></i> Top 10 dominios</h3>
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Dominio</th>
                    <th>Backlinks</th>
                    <th>DA promedio</th>
                </tr>
            </thead>
            <tbody>
                <?php $rank = 1; foreach ($stats_offpage['top_dominios'] as $dominio): ?>
                <tr>
                    <td><?php echo $rank++; ?></td>
                    <td><?php echo htmlspecialchars($dominio['dominio_origen']); ?></td>
                    <td><span class="badge badge-primary"><?php echo $dominio['backlinks']; ?></span></td>
                    <td><span class="badge badge-info"><?php echo round($dominio['da'] ?? 0); ?></span></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <?php else: ?>
    <div class="alert alert-info">
        <i class="fas fa-info-circle"></i>
        <span>No hay datos de estadísticas disponibles.</span>
    </div>
    <?php endif; ?>
</div>

<!-- ============================================ -->
<!-- VISTA: LISTADO DE BACKLINKS                  -->
<!-- ============================================ -->
<?php elseif ($modo_vista === 'list'): ?>

<div class="offpage-header">
    <div class="header-left">
        <h2><i class="fas fa-link"></i> Gestión de Backlinks</h2>
        <p class="subtitle">Total: <?php echo count($backlinks); ?> backlinks</p>
    </div>
    <div class="header-actions">
        <button onclick="window.location.href='gseo.php?tab=offpage&subtab=backlinks&view=stats'" class="btn btn-info">
            <i class="fas fa-chart-bar"></i> Estadísticas
        </button>
        <button onclick="window.location.href='gseo.php?tab=offpage&subtab=backlinks&view=create'" class="btn btn-primary">
            <i class="fas fa-plus"></i> Añadir Backlink
        </button>
    </div>
</div>

<!-- Filtres -->
<div class="filters-bar">
    <div class="filter-group">
        <label><i class="fas fa-filter"></i> Estado:</label>
        <select id="filtro_estado" onchange="aplicarFiltros()">
            <option value="all" <?php echo $filtro_estado === 'all' ? 'selected' : ''; ?>>Todos</option>
            <option value="activo" <?php echo $filtro_estado === 'activo' ? 'selected' : ''; ?>>Activos</option>
            <option value="perdido" <?php echo $filtro_estado === 'perdido' ? 'selected' : ''; ?>>Perdidos</option>
            <option value="roto" <?php echo $filtro_estado === 'roto' ? 'selected' : ''; ?>>Rotos</option>
            <option value="en_revision" <?php echo $filtro_estado === 'en_revision' ? 'selected' : ''; ?>>En revisión</option>
        </select>
    </div>
    
    <div class="filter-group">
        <label><i class="fas fa-tag"></i> Tipos:</label>
        <select id="filtro_tipo" onchange="aplicarFiltros()">
            <option value="all" <?php echo $filtro_tipo === 'all' ? 'selected' : ''; ?>>Todos</option>
            <option value="guest_post" <?php echo $filtro_tipo === 'guest_post' ? 'selected' : ''; ?>>Guest Post</option>
            <option value="directorio" <?php echo $filtro_tipo === 'directorio' ? 'selected' : ''; ?>>Directorio</option>
            <option value="prensa" <?php echo $filtro_tipo === 'prensa' ? 'selected' : ''; ?>>Prensa</option>
            <option value="blog_comentario" <?php echo $filtro_tipo === 'blog_comentario' ? 'selected' : ''; ?>>Blog/Comentario</option>
            <option value="foro" <?php echo $filtro_tipo === 'foro' ? 'selected' : ''; ?>>Foro</option>
            <option value="social_media" <?php echo $filtro_tipo === 'social_media' ? 'selected' : ''; ?>>Redes sociales</option>
            <option value="recursos_util" <?php echo $filtro_tipo === 'recursos_util' ? 'selected' : ''; ?>>Recurso útil</option>
            <option value="colaboracion" <?php echo $filtro_tipo === 'colaboracion' ? 'selected' : ''; ?>>Colaboración</option>
            <option value="natural" <?php echo $filtro_tipo === 'natural' ? 'selected' : ''; ?>>Natural</option>
            <option value="adquirido" <?php echo $filtro_tipo === 'adquirido' ? 'selected' : ''; ?>>Adquirido</option>
        </select>
    </div>
    
    <div class="filter-group">
        <label><i class="fas fa-bullhorn"></i> Campaña:</label>
        <select id="filtro_campana" onchange="aplicarFiltros()">
            <option value="all" <?php echo $filtro_campana === 'all' ? 'selected' : ''; ?>>Todas</option>
            <?php foreach ($campanyas as $campana): ?>
            <option value="<?php echo htmlspecialchars($campana); ?>" <?php echo $filtro_campana === $campana ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($campana); ?>
            </option>
            <?php endforeach; ?>
        </select>
    </div>
    
    <button onclick="limpiarFiltros()" class="btn btn-sm btn-secondary">
        <i class="fas fa-times"></i> Limpiar
    </button>
</div>

<!-- Tabla de backlinks -->
<?php if (count($backlinks) > 0): ?>
<div class="backlinks-table-container">
    <table class="backlinks-table">
        <thead>
            <tr>
                <th>Estado</th>
                <th>Dominio Origen</th>
                <th>Anchor Text</th>
                <th>URL Destino</th>
                <th>DA</th>
                <th>Tipo</th>
                <th>Rel</th>
                <th>Calidad</th>
                <th>Fecha</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($backlinks as $backlink): 
                $quality_score = $backlink->calcularQualityScore();
                $quality_class = $quality_score >= 75 ? 'success' : ($quality_score >= 50 ? 'warning' : 'danger');
            ?>
            <tr>
                <td>
                    <?php 
                    $estado_icons = [
                        'activo' => '<span class="badge badge-success"><i class="fas fa-check"></i> Actiu</span>',
                        'perdido' => '<span class="badge badge-warning"><i class="fas fa-exclamation"></i> Perdut</span>',
                        'roto' => '<span class="badge badge-danger"><i class="fas fa-unlink"></i> Trencat</span>',
                        'en_revision' => '<span class="badge badge-info"><i class="fas fa-search"></i> Revisió</span>'
                    ];
                    echo $estado_icons[$backlink->getEstado()] ?? $backlink->getEstado();
                    ?>
                </td>
                <td>
                    <div class="domain-info">
                        <strong><?php echo htmlspecialchars($backlink->getDominioOrigen()); ?></strong>
                        <?php if ($backlink->getTituloPaginaOrigen()): ?>
                        <small class="text-muted"><?php echo htmlspecialchars(mb_substr($backlink->getTituloPaginaOrigen(), 0, 40)) . '...'; ?></small>
                        <?php endif; ?>
                    </div>
                </td>
                <td>
                    <span class="anchor-text" title="<?php echo htmlspecialchars($backlink->getAnchorText()); ?>">
                        <?php echo htmlspecialchars(mb_substr($backlink->getAnchorText(), 0, 30)) . (mb_strlen($backlink->getAnchorText()) > 30 ? '...' : ''); ?>
                    </span>
                </td>
                <td>
                    <a href="<?php echo htmlspecialchars($backlink->getUrlDestino()); ?>" target="_blank" class="url-link">
                        <?php echo htmlspecialchars(mb_substr($backlink->getUrlDestino(), 0, 40)) . '...'; ?>
                    </a>
                </td>
                <td>
                    <span class="badge badge-authority">
                        <?php echo $backlink->getDaOrigen() ?? $backlink->getDrOrigen() ?? '-'; ?>
                    </span>
                </td>
                <td>
                    <span class="badge badge-secondary">
                        <?php echo str_replace('_', ' ', $backlink->getTipoBacklink()); ?>
                    </span>
                </td>
                <td>
                    <?php if ($backlink->isNofollow()): ?>
                        <span class="badge badge-rel-nofollow">NoFollow</span>
                    <?php else: ?>
                        <span class="badge badge-rel-dofollow">DoFollow</span>
                    <?php endif; ?>
                    <?php if ($backlink->isSponsored()): ?>
                        <span class="badge badge-rel-sponsored">Sponsored</span>
                    <?php endif; ?>
                </td>
                <td>
                    <div class="quality-indicator">
                        <span class="badge badge-<?php echo $quality_class; ?>"><?php echo $quality_score; ?>/100</span>
                    </div>
                </td>
                <td>
                    <small><?php echo date('d/m/Y', strtotime($backlink->getFechaDescubrimiento())); ?></small>
                </td>
                <td class="actions-cell">
                    <button onclick="window.location.href='gseo.php?tab=offpage&view=edit&id_backlink=<?php echo $backlink->getId(); ?>'" 
                            class="btn-icon btn-edit" title="Editar">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button onclick="verificarBacklink(<?php echo $backlink->getId(); ?>)" 
                            class="btn-icon btn-verify" title="Verificar">
                        <i class="fas fa-sync"></i>
                    </button>
                    <button onclick="eliminarBacklink(<?php echo $backlink->getId(); ?>)" 
                            class="btn-icon btn-delete" title="Eliminar">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php else: ?>
<div class="alert alert-info">
    <i class="fas fa-info-circle"></i>
    <span>No se han encontrado backlinks con los filtros seleccionados.</span>
</div>
<?php endif; ?>

<!-- ============================================ -->
<!-- VISTA: CREAR/EDITAR BACKLINK                 -->
<!-- ============================================ -->
<?php elseif ($modo_vista === 'create' || $modo_vista === 'edit'): 
    $es_edicio = ($modo_vista === 'edit' && $backlink_edit);
?>

<div class="offpage-form-container">
    <div class="form-header">
        <h2>
            <i class="fas fa-<?php echo $es_edicio ? 'edit' : 'plus'; ?>"></i>
            <?php echo $es_edicio ? 'Editar Backlink' : 'Añadir Nuevo Backlink'; ?>
        </h2>
        <button onclick="window.location.href='gseo.php?tab=offpage&subtab=backlinks&view=list'" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver
        </button>
    </div>
    
    <form method="POST" action="gseo.php" class="offpage-form">
        <input type="hidden" name="action" value="<?php echo $es_edicio ? 'update_backlink' : 'create_backlink'; ?>">
        <?php if ($es_edicio): ?>
        <input type="hidden" name="id_offpage" value="<?php echo $backlink_edit->getId(); ?>">
        <?php endif; ?>
        
        <!-- Sección 1: Información Básica -->
        <div class="form-section">
            <div class="section-header">
                <h3><i class="fas fa-info-circle"></i> Información Básica del Backlink</h3>
            </div>
            
            <div class="form-row">
                <div class="form-group full-width">
                    <label for="url_origen">URL origen <span class="required">*</span></label>
                    <input type="url" id="url_origen" name="url_origen" 
                           value="<?php echo $es_edicio ? htmlspecialchars($backlink_edit->getUrlOrigen()) : ''; ?>" 
                           required placeholder="https://ejemplo.com/articulo-que-enlaza">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group full-width">
                    <label for="url_destino">URL destino (nuestra web) <span class="required">*</span></label>
                    <input type="url" id="url_destino" name="url_destino" 
                           value="<?php echo $es_edicio ? htmlspecialchars($backlink_edit->getUrlDestino()) : ''; ?>" 
                           required placeholder="https://psicologiayanina.com/pagina">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="anchor_text">Anchor Text <span class="required">*</span></label>
                    <input type="text" id="anchor_text" name="anchor_text" 
                           value="<?php echo $es_edicio ? htmlspecialchars($backlink_edit->getAnchorText()) : ''; ?>" 
                           required placeholder="Text de l'enllaç">
                </div>
                
                <div class="form-group">
                    <label for="dominio_origen">Dominio origen <span class="required">*</span></label>
                    <input type="text" id="dominio_origen" name="dominio_origen" 
                           value="<?php echo $es_edicio ? htmlspecialchars($backlink_edit->getDominioOrigen()) : ''; ?>" 
                           required placeholder="ejemplo.com">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="tipo_backlink">Tipos de Backlink <span class="required">*</span></label>
                    <select id="tipo_backlink" name="tipo_backlink" required>
                        <option value="">Selecciona un tipo</option>
                        <option value="guest_post" <?php echo ($es_edicio && $backlink_edit->getTipoBacklink() === 'guest_post') ? 'selected' : ''; ?>>Guest Post</option>
                        <option value="directorio" <?php echo ($es_edicio && $backlink_edit->getTipoBacklink() === 'directorio') ? 'selected' : ''; ?>>Directorio</option>
                        <option value="prensa" <?php echo ($es_edicio && $backlink_edit->getTipoBacklink() === 'prensa') ? 'selected' : ''; ?>>Prensa</option>
                        <option value="blog_comentario" <?php echo ($es_edicio && $backlink_edit->getTipoBacklink() === 'blog_comentario') ? 'selected' : ''; ?>>Blog/Comentario</option>
                        <option value="foro" <?php echo ($es_edicio && $backlink_edit->getTipoBacklink() === 'foro') ? 'selected' : ''; ?>>Foro</option>
                        <option value="social_media" <?php echo ($es_edicio && $backlink_edit->getTipoBacklink() === 'social_media') ? 'selected' : ''; ?>>Redes Sociales</option>
                        <option value="recursos_util" <?php echo ($es_edicio && $backlink_edit->getTipoBacklink() === 'recursos_util') ? 'selected' : ''; ?>>Recurso Útil</option>
                        <option value="colaboracion" <?php echo ($es_edicio && $backlink_edit->getTipoBacklink() === 'colaboracion') ? 'selected' : ''; ?>>Colaboración</option>
                        <option value="natural" <?php echo ($es_edicio && $backlink_edit->getTipoBacklink() === 'natural') ? 'selected' : ''; ?>>Natural</option>
                        <option value="adquirido" <?php echo ($es_edicio && $backlink_edit->getTipoBacklink() === 'adquirido') ? 'selected' : ''; ?>>Adquirido</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="fecha_descubrimiento">Data descubrimiento</label>
                    <input type="date" id="fecha_descubrimiento" name="fecha_descubrimiento" 
                           value="<?php echo $es_edicio ? $backlink_edit->getFechaDescubrimiento() : date('Y-m-d'); ?>">
                </div>
            </div>
        </div>
        
        <!-- Sección 2: Métricas de Autoridad -->
        <div class="form-section">
            <div class="section-header">
                <h3><i class="fas fa-chart-line"></i> Métricas de autoridad</h3>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="da_origen">Domain Authority (DA)</label>
                    <input type="number" id="da_origen" name="da_origen" min="0" max="100"
                           value="<?php echo $es_edicio ? $backlink_edit->getDaOrigen() : ''; ?>" 
                           placeholder="0-100">
                </div>
                
                <div class="form-group">
                    <label for="dr_origen">Domain Rating (DR)</label>
                    <input type="number" id="dr_origen" name="dr_origen" min="0" max="100"
                           value="<?php echo $es_edicio ? $backlink_edit->getDrOrigen() : ''; ?>" 
                           placeholder="0-100">
                </div>
                
                <div class="form-group">
                    <label for="tf_origen">Trust Flow (TF)</label>
                    <input type="number" id="tf_origen" name="tf_origen" min="0" max="100"
                           value="<?php echo $es_edicio ? $backlink_edit->getTfOrigen() : ''; ?>" 
                           placeholder="0-100">
                </div>
                
                <div class="form-group">
                    <label for="cf_origen">Citation Flow (CF)</label>
                    <input type="number" id="cf_origen" name="cf_origen" min="0" max="100"
                           value="<?php echo $es_edicio ? $backlink_edit->getCfOrigen() : ''; ?>" 
                           placeholder="0-100">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="traffic_origen">Tráfico mensual estimado</label>
                    <input type="number" id="traffic_origen" name="traffic_origen" min="0"
                           value="<?php echo $es_edicio ? $backlink_edit->getTrafficOrigen() : ''; ?>" 
                           placeholder="Visitas/mes">
                </div>
                
                <div class="form-group">
                    <label for="idioma_origen">Idioma</label>
                    <select id="idioma_origen" name="idioma_origen">
                        <option value="ca" <?php echo ($es_edicio && $backlink_edit->getIdiomaOrigen() === 'ca') ? 'selected' : ''; ?>>Català</option>
                        <option value="es" <?php echo ($es_edicio && $backlink_edit->getIdiomaOrigen() === 'es') ? 'selected' : 'selected'; ?>>Castellano</option>
                        <option value="en" <?php echo ($es_edicio && $backlink_edit->getIdiomaOrigen() === 'en') ? 'selected' : ''; ?>>Inglés</option>
                        <option value="fr" <?php echo ($es_edicio && $backlink_edit->getIdiomaOrigen() === 'fr') ? 'selected' : ''; ?>>Francés</option>
                        <option value="it" <?php echo ($es_edicio && $backlink_edit->getIdiomaOrigen() === 'it') ? 'selected' : ''; ?>>Italiano</option>
                        <option value="other" <?php echo ($es_edicio && $backlink_edit->getIdiomaOrigen() === 'other') ? 'selected' : ''; ?>>Otros</option>
                    </select>
                </div>
            </div>
        </div>
        
        <!-- Sección 3: Atributos y Contexto -->
        <div class="form-section">
            <div class="section-header">
                <h3><i class="fas fa-tags"></i> Atributos y Contexto</h3>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="posicion_enlace">Posición del Enlace</label>
                    <select id="posicion_enlace" name="posicion_enlace">
                        <option value="contenido" <?php echo ($es_edicio && $backlink_edit->getPosicionEnlace() === 'contenido') ? 'selected' : 'selected'; ?>>Contenido</option>
                        <option value="header" <?php echo ($es_edicio && $backlink_edit->getPosicionEnlace() === 'header') ? 'selected' : ''; ?>>Cabecera</option>
                        <option value="footer" <?php echo ($es_edicio && $backlink_edit->getPosicionEnlace() === 'footer') ? 'selected' : ''; ?>>Pie de página</option>
                        <option value="sidebar" <?php echo ($es_edicio && $backlink_edit->getPosicionEnlace() === 'sidebar') ? 'selected' : ''; ?>>Barra lateral</option>
                        <option value="comentarios" <?php echo ($es_edicio && $backlink_edit->getPosicionEnlace() === 'comentarios') ? 'selected' : ''; ?>>Comentarios</option>
                        <option value="navegacion" <?php echo ($es_edicio && $backlink_edit->getPosicionEnlace() === 'navegacion') ? 'selected' : ''; ?>>Navegación</option>
                    </select>
                </div>
                
                <div class="form-group checkboxes-group">
                    <label>Atributos REL:</label>
                    <div class="checkbox-row">
                        <label class="checkbox-label">
                            <input type="checkbox" name="nofollow" value="1" 
                                   <?php echo ($es_edicio && $backlink_edit->isNofollow()) ? 'checked' : ''; ?>>
                            NoFollow
                        </label>
                        <label class="checkbox-label">
                            <input type="checkbox" name="sponsored" value="1" 
                                   <?php echo ($es_edicio && $backlink_edit->isSponsored()) ? 'checked' : ''; ?>>
                            Sponsored
                        </label>
                        <label class="checkbox-label">
                            <input type="checkbox" name="ugc" value="1" 
                                   <?php echo ($es_edicio && $backlink_edit->isUgc()) ? 'checked' : ''; ?>>
                            UGC
                        </label>
                    </div>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group full-width">
                    <label for="contexto_backlink">Context de l'Enllaç</label>
                    <textarea id="contexto_backlink" name="contexto_backlink" rows="3" 
                              placeholder="Text que envolta l'enllaç..."><?php echo $es_edicio ? htmlspecialchars($backlink_edit->getContextoBacklink() ?? '') : ''; ?></textarea>
                </div>
            </div>
        </div>
        
        <!-- Sección 4: Calidad y Estrategia -->
        <div class="form-section">
            <div class="section-header">
                <h3><i class="fas fa-star"></i> Calidad y Estrategia</h3>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="relevancia_tematica">Relevancia Temática</label>
                    <select id="relevancia_tematica" name="relevancia_tematica">
                        <option value="alta" <?php echo ($es_edicio && $backlink_edit->getRelevanciaTematica() === 'alta') ? 'selected' : ''; ?>>Alta</option>
                        <option value="media" <?php echo ($es_edicio && $backlink_edit->getRelevanciaTematica() === 'media') ? 'selected' : 'selected'; ?>>Media</option>
                        <option value="baja" <?php echo ($es_edicio && $backlink_edit->getRelevanciaTematica() === 'baja') ? 'selected' : ''; ?>>Baja</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="calidad_percibida">Calidad percibida</label>
                    <select id="calidad_percibida" name="calidad_percibida">
                        <option value="excelente" <?php echo ($es_edicio && $backlink_edit->getCalidadPercibida() === 'excelente') ? 'selected' : ''; ?>>Excelente</option>
                        <option value="buena" <?php echo ($es_edicio && $backlink_edit->getCalidadPercibida() === 'buena') ? 'selected' : ''; ?>>Buena</option>
                        <option value="regular" <?php echo ($es_edicio && $backlink_edit->getCalidadPercibida() === 'regular') ? 'selected' : 'selected'; ?>>Regular</option>
                        <option value="mala" <?php echo ($es_edicio && $backlink_edit->getCalidadPercibida() === 'mala') ? 'selected' : ''; ?>>Mala</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="prioridad">Prioritat</label>
                    <select id="prioridad" name="prioridad">
                        <option value="alta" <?php echo ($es_edicio && $backlink_edit->getPrioridad() === 'alta') ? 'selected' : ''; ?>>Alta</option>
                        <option value="media" <?php echo ($es_edicio && $backlink_edit->getPrioridad() === 'media') ? 'selected' : 'selected'; ?>>Media</option>
                        <option value="baja" <?php echo ($es_edicio && $backlink_edit->getPrioridad() === 'baja') ? 'selected' : ''; ?>>Baja</option>
                    </select>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="campana_seo">Campaña SEO</label>
                    <input type="text" id="campana_seo" name="campana_seo" 
                           value="<?php echo $es_edicio ? htmlspecialchars($backlink_edit->getCampanaSeo() ?? '') : ''; ?>" 
                           placeholder="Nombre de la campaña">
                </div>
                
                <div class="form-group">
                    <label for="objetivo_seo">Objetivo SEO</label>
                    <select id="objetivo_seo" name="objetivo_seo">
                        <option value="branding" <?php echo ($es_edicio && $backlink_edit->getObjetivoSeo() === 'branding') ? 'selected' : ''; ?>>Branding</option>
                        <option value="trafico" <?php echo ($es_edicio && $backlink_edit->getObjetivoSeo() === 'trafico') ? 'selected' : ''; ?>>Tráfico</option>
                        <option value="autoridad" <?php echo ($es_edicio && $backlink_edit->getObjetivoSeo() === 'autoridad') ? 'selected' : 'selected'; ?>>Autoridad</option>
                        <option value="conversiones" <?php echo ($es_edicio && $backlink_edit->getObjetivoSeo() === 'conversiones') ? 'selected' : ''; ?>>Conversions</option>
                    </select>
                </div>
            </div>
        </div>
        
        <!-- Secció 5: Notes -->
        <div class="form-section">
            <div class="section-header">
                <h3><i class="fas fa-sticky-note"></i> Notas y Observaciones</h3>
            </div>
            
            <div class="form-row">
                <div class="form-group full-width">
                    <label for="notas_internas">Notas Internas</label>
                    <textarea id="notas_internas" name="notas_internas" rows="4" 
                              placeholder="Notas sobre el backlink, seguimiento, etc."><?php echo $es_edicio ? htmlspecialchars($backlink_edit->getNotasInternas() ?? '') : ''; ?></textarea>
                </div>
            </div>
        </div>
        
        <!-- Botons d'acció -->
        <div class="form-actions">
            <button type="button" onclick="window.location.href='gseo.php?tab=offpage&subtab=backlinks&view=list'" class="btn btn-secondary">
                <i class="fas fa-times"></i> Cancelar
            </button>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> <?php echo $es_edicio ? 'Actualizar' : 'Crear'; ?> Backlink
            </button>
        </div>
    </form>
</div>

<?php endif; ?>

<!-- JavaScript para filtros y acciones -->
<script>
function aplicarFiltros() {
    const estado = document.getElementById('filtro_estado').value;
    const tipo = document.getElementById('filtro_tipo').value;
    const campana = document.getElementById('filtro_campana').value;
    
    let url = 'gseo.php?tab=offpage&subtab=backlinks&view=list';
    if (estado !== 'all') url += '&estado=' + estado;
    if (tipo !== 'all') url += '&tipo_backlink=' + tipo;
    if (campana !== 'all') url += '&campana=' + encodeURIComponent(campana);
    
    window.location.href = url;
}

function limpiarFiltros() {
    window.location.href = 'gseo.php?tab=offpage&subtab=backlinks&view=list';
}

function verificarBacklink(id) {
    if (confirm('¿Quieres verificar este backlink? Esto comprobará si el enlace aún existe.')) {
        // Implementar llamada AJAX para verificar
        console.log('Verificando backlink ID:', id);
        alert('Funcionalidad de verificación en desarrollo');
    }
}

function eliminarBacklink(id) {
    if (confirm('¿Estás seguro de que quieres eliminar este backlink? Esta acción no se puede deshacer.')) {
        window.location.href = 'gseo.php?action=delete_backlink&id_offpage=' + id + '&tab=offpage&subtab=backlinks';
    }
}
</script>
