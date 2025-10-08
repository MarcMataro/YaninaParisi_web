<?php
/**
 * Interfaz de gestión de Directorios Off-Page SEO
 * 
 * Permite listar, crear, editar y analizar directorios empresariales
 * 
 * @author Marc Mataró
 * @version 1.0.0
 */

// Variables de control
$modo_vista_dir = $_GET['view'] ?? 'list'; // list | edit | create | stats
$directorio_edit = null;
$filtro_estado_dir = $_GET['estado'] ?? 'all';
$filtro_categoria_dir = $_GET['categoria'] ?? 'all';
$filtro_idioma_dir = $_GET['idioma'] ?? 'all';

// Si estem editant, carregar el directori
if ($modo_vista_dir === 'edit' && isset($_GET['id'])) {
    try {
        $directorio_edit = new SEO_OffPage_Directories($_GET['id']);
    } catch (Exception $e) {
        $error_message = "Error al carregar el directori: " . $e->getMessage();
        $modo_vista_dir = 'list';
    }
}

// Obtenir directoris amb filtres
$filtros_directories = [];
if ($filtro_estado_dir !== 'all') {
    $filtros_directories['estado'] = $filtro_estado_dir;
}
if ($filtro_categoria_dir !== 'all') {
    $filtros_directories['categoria'] = $filtro_categoria_dir;
}
if ($filtro_idioma_dir !== 'all') {
    $filtros_directories['idioma'] = $filtro_idioma_dir;
}

$directories = SEO_OffPage_Directories::llistarDirectoris($filtros_directories, 'nombre', 'ASC', 100);

// Obtener estadísticas globales
$stats_directories = $seo_directories_stats ?? SEO_OffPage_Directories::obtenirEstadistiquesGlobals();
?>

<!-- ============================================ -->
<!-- VISTA: ESTADÍSTICAS                          -->
<!-- ============================================ -->
<?php if ($modo_vista_dir === 'stats'): ?>

<div class="directories-stats-container">
    <div class="stats-header">
        <h2><i class="fas fa-chart-bar"></i> Estadísticas de Directorios</h2>
        <button onclick="window.location.href='gseo.php?tab=offpage&subtab=directories&view=list'" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver al listado
        </button>
    </div>
    
    <?php if ($stats_directories): ?>
    
    <!-- Resum Global -->
    <div class="stats-summary">
        <div class="stat-box stat-primary">
            <i class="fas fa-list-ul"></i>
            <div class="stat-content">
                <div class="stat-value"><?php echo $stats_directories['total']; ?></div>
                <div class="stat-label">Total Directorios</div>
            </div>
        </div>
        
        <div class="stat-box stat-success">
            <i class="fas fa-check-circle"></i>
            <div class="stat-content">
                <div class="stat-value"><?php echo $stats_directories['activos']; ?></div>
                <div class="stat-label">Activos</div>
            </div>
        </div>
        
        <div class="stat-box stat-warning">
            <i class="fas fa-clock"></i>
            <div class="stat-content">
                <div class="stat-value"><?php echo $stats_directories['pendientes']; ?></div>
                <div class="stat-label">Pendientes</div>
            </div>
        </div>
        
        <div class="stat-box stat-info">
            <i class="fas fa-paper-plane"></i>
            <div class="stat-content">
                <div class="stat-value"><?php echo $stats_directories['enviados']; ?></div>
                <div class="stat-label">Enviados</div>
            </div>
        </div>
        
        <div class="stat-box stat-authority">
            <i class="fas fa-trophy"></i>
            <div class="stat-content">
                <div class="stat-value"><?php echo round($stats_directories['da_promedio'] ?? 0); ?></div>
                <div class="stat-label">DA Promedio</div>
            </div>
        </div>
        
        <div class="stat-box stat-danger">
            <i class="fas fa-euro-sign"></i>
            <div class="stat-content">
                <div class="stat-value"><?php echo number_format($stats_directories['costo_total_anual'] ?? 0, 0); ?>€</div>
                <div class="stat-label">Coste Anual</div>
            </div>
        </div>
    </div>
    
    <!-- Score Global -->
    <div class="score-section">
        <div class="score-circle-large">
            <svg viewBox="0 0 200 200">
                <circle cx="100" cy="100" r="90" fill="none" stroke="#e9ecef" stroke-width="12"/>
                <circle cx="100" cy="100" r="90" fill="none" 
                        stroke="<?php 
                            $score = $stats_directories['score_global'];
                            if ($score >= 80) echo '#27ae60';
                            elseif ($score >= 60) echo '#f39c12';
                            else echo '#e74c3c';
                        ?>" 
                        stroke-width="12" 
                        stroke-dasharray="<?php echo ($score / 100) * 565.48; ?> 565.48"
                        stroke-linecap="round"
                        transform="rotate(-90 100 100)"/>
            </svg>
            <div class="score-text">
                <div class="score-number"><?php echo $stats_directories['score_global']; ?></div>
                <div class="score-label">Puntuación Global</div>
                <div class="score-status"><?php echo $stats_directories['estado_global']; ?></div>
            </div>
        </div>
    </div>
    
    <!-- Gràfics de Distribució -->
    <div class="charts-grid">
        <div class="chart-card">
            <h3><i class="fas fa-link"></i> DoFollow vs NoFollow</h3>
            <div class="chart-bars">
                <div class="chart-bar-item">
                    <div class="chart-bar-label">
                        <span>DoFollow</span>
                        <span class="chart-bar-value"><?php echo $stats_directories['dofollow']; ?></span>
                    </div>
                    <div class="chart-bar-bg">
                        <div class="chart-bar-fill" style="width: <?php echo $stats_directories['total'] > 0 ? ($stats_directories['dofollow'] / $stats_directories['total']) * 100 : 0; ?>%; background: #27ae60;"></div>
                    </div>
                </div>
                <div class="chart-bar-item">
                    <div class="chart-bar-label">
                        <span>NoFollow</span>
                        <span class="chart-bar-value"><?php echo $stats_directories['nofollow']; ?></span>
                    </div>
                    <div class="chart-bar-bg">
                        <div class="chart-bar-fill" style="width: <?php echo $stats_directories['total'] > 0 ? ($stats_directories['nofollow'] / $stats_directories['total']) * 100 : 0; ?>%; background: #95a5a6;"></div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="chart-card">
            <h3><i class="fas fa-tag"></i> Por categoría</h3>
            <div class="chart-list">
                <?php foreach ($stats_directories['por_categoria'] as $cat): ?>
                <div class="chart-list-item">
                    <span class="chart-list-label"><?php echo ucfirst($cat['categoria']); ?></span>
                    <span class="chart-list-bar">
                        <span class="chart-list-fill" style="width: <?php echo ($cat['cantidad'] / $stats_directories['total']) * 100; ?>%;"></span>
                    </span>
                    <span class="chart-list-value"><?php echo $cat['cantidad']; ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div class="chart-card">
            <h3><i class="fas fa-language"></i> Por idioma</h3>
            <div class="chart-list">
                <?php foreach ($stats_directories['por_idioma'] as $lang): ?>
                <div class="chart-list-item">
                    <span class="chart-list-label"><?php 
                        $idiomas = ['ca' => 'Català', 'es' => 'Español', 'en' => 'English', 'other' => 'Otros'];
                        echo $idiomas[$lang['idioma']] ?? $lang['idioma'];
                    ?></span>
                    <span class="chart-list-bar">
                        <span class="chart-list-fill" style="width: <?php echo ($lang['cantidad'] / $stats_directories['total']) * 100; ?>%;"></span>
                    </span>
                    <span class="chart-list-value"><?php echo $lang['cantidad']; ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    
    <!-- Top Directoris -->
    <div class="top-directories">
        <h3><i class="fas fa-star"></i> Top 10 Directorios por Autoridad</h3>
        <table class="top-table">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Categoría</th>
                    <th>DA</th>
                    <th>Estado</th>
                    <th>Coste</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($stats_directories['top_directorios'] as $dir): ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($dir['nombre']); ?></strong></td>
                    <td><span class="badge badge-info"><?php echo ucfirst($dir['categoria']); ?></span></td>
                    <td><span class="badge badge-authority"><?php echo $dir['da']; ?></span></td>
                    <td>
                        <?php
                        $badge_class = '';
                        $icon = '';
                        switch($dir['estado']) {
                            case 'activo': $badge_class = 'success'; $icon = 'check-circle'; break;
                            case 'aprobado': $badge_class = 'info'; $icon = 'thumbs-up'; break;
                            case 'enviado': $badge_class = 'warning'; $icon = 'paper-plane'; break;
                            case 'pendiente': $badge_class = 'secondary'; $icon = 'clock'; break;
                            case 'rechazado': $badge_class = 'danger'; $icon = 'times-circle'; break;
                        }
                        ?>
                        <span class="badge badge-<?php echo $badge_class; ?>">
                            <i class="fas fa-<?php echo $icon; ?>"></i> <?php echo ucfirst($dir['estado']); ?>
                        </span>
                    </td>
                    <td><?php echo $dir['costo'] > 0 ? number_format($dir['costo'], 2) . '€' : 'Gratuito'; ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <?php else: ?>
    <div class="empty-state">
        <i class="fas fa-chart-bar"></i>
        <h3>No hay estadísticas disponibles</h3>
        <p>Comienza añadiendo directorios para ver las estadísticas.</p>
    </div>
    <?php endif; ?>
</div>

<?php endif; ?>

<!-- ============================================ -->
<!-- VISTA: LISTADO DE DIRECTORIOS                -->
<!-- ============================================ -->
<?php if ($modo_vista_dir === 'list'): ?>

<div class="directories-list-container">
    <div class="offpage-header">
        <div class="header-left">
            <h2><i class="fas fa-list-ul"></i> Directorios Empresariales</h2>
            <p class="subtitle">Gestión de alta en directorios y listados de negocios</p>
        </div>
        <div class="header-actions">
            <button onclick="window.location.href='gseo.php?tab=offpage&subtab=directories&view=stats'" class="btn btn-info">
                <i class="fas fa-chart-bar"></i> Estadísticas
            </button>
            <button onclick="window.location.href='gseo.php?tab=offpage&subtab=directories&view=create'" class="btn btn-primary">
                <i class="fas fa-plus"></i> Añadir Directorio
            </button>
        </div>
    </div>
    
    <!-- Filtres -->
    <div class="filters-bar">
        <div class="filter-group">
            <label><i class="fas fa-filter"></i> Estado:</label>
            <select id="filter-estado" onchange="aplicarFiltrosDirectories()">
                <option value="all" <?php echo $filtro_estado_dir === 'all' ? 'selected' : ''; ?>>Todos</option>
                <option value="pendiente" <?php echo $filtro_estado_dir === 'pendiente' ? 'selected' : ''; ?>>Pendiente</option>
                <option value="enviado" <?php echo $filtro_estado_dir === 'enviado' ? 'selected' : ''; ?>>Enviado</option>
                <option value="aprobado" <?php echo $filtro_estado_dir === 'aprobado' ? 'selected' : ''; ?>>Aprobado</option>
                <option value="activo" <?php echo $filtro_estado_dir === 'activo' ? 'selected' : ''; ?>>Activo</option>
                <option value="rechazado" <?php echo $filtro_estado_dir === 'rechazado' ? 'selected' : ''; ?>>Rechazado</option>
            </select>
        </div>
        
        <div class="filter-group">
            <label><i class="fas fa-tag"></i> Categoría:</label>
            <select id="filter-categoria" onchange="aplicarFiltrosDirectories()">
                <option value="all" <?php echo $filtro_categoria_dir === 'all' ? 'selected' : ''; ?>>Todos</option>
                <option value="psicologia" <?php echo $filtro_categoria_dir === 'psicologia' ? 'selected' : ''; ?>>Psicología</option>
                <option value="salud" <?php echo $filtro_categoria_dir === 'salud' ? 'selected' : ''; ?>>Salud</option>
                <option value="locales" <?php echo $filtro_categoria_dir === 'locales' ? 'selected' : ''; ?>>Locales</option>
                <option value="negocios" <?php echo $filtro_categoria_dir === 'negocios' ? 'selected' : ''; ?>>Negocios</option>
                <option value="generico" <?php echo $filtro_categoria_dir === 'generico' ? 'selected' : ''; ?>>Genérico</option>
            </select>
        </div>
        
        <div class="filter-group">
            <label><i class="fas fa-language"></i> Idioma:</label>
            <select id="filter-idioma" onchange="aplicarFiltrosDirectories()">
                <option value="all" <?php echo $filtro_idioma_dir === 'all' ? 'selected' : ''; ?>>Todos</option>
                <option value="ca" <?php echo $filtro_idioma_dir === 'ca' ? 'selected' : ''; ?>>Català</option>
                <option value="es" <?php echo $filtro_idioma_dir === 'es' ? 'selected' : ''; ?>>Español</option>
                <option value="en" <?php echo $filtro_idioma_dir === 'en' ? 'selected' : ''; ?>>English</option>
                <option value="other" <?php echo $filtro_idioma_dir === 'other' ? 'selected' : ''; ?>>Otros</option>
            </select>
        </div>
        
        <button onclick="limpiarFiltrosDirectories()" class="btn btn-secondary btn-sm">
            <i class="fas fa-times"></i> Restablecer
        </button>
    </div>
    
    <!-- Taula de Directoris -->
    <?php if (count($directories) > 0): ?>
    <div class="directories-table-container">
        <table class="directories-table">
            <thead>
                <tr>
                    <th>Estado</th>
                    <th>Nombre</th>
                    <th>Categoría</th>
                    <th>DA</th>
                    <th>Idioma</th>
                    <th>REL</th>
                    <th>Coste</th>
                    <th>Calidad</th>
                    <th>Fecha creación</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($directories as $dir): 
                    $dir_data = $dir->toArray();
                ?>
                <tr>
                    <td>
                        <?php
                        $estado = $dir->getEstado();
                        $badge_class = '';
                        $icon = '';
                        switch($estado) {
                            case 'activo': $badge_class = 'success'; $icon = 'check-circle'; break;
                            case 'aprobado': $badge_class = 'info'; $icon = 'thumbs-up'; break;
                            case 'enviado': $badge_class = 'warning'; $icon = 'paper-plane'; break;
                            case 'pendiente': $badge_class = 'secondary'; $icon = 'clock'; break;
                            case 'rechazado': $badge_class = 'danger'; $icon = 'times-circle'; break;
                        }
                        ?>
                        <span class="badge badge-<?php echo $badge_class; ?>">
                            <i class="fas fa-<?php echo $icon; ?>"></i> <?php echo ucfirst($estado); ?>
                        </span>
                    </td>
                    <td>
                        <strong><?php echo htmlspecialchars($dir->getNombre()); ?></strong>
                        <br>
                        <small class="text-muted">
                            <a href="<?php echo htmlspecialchars($dir->getUrl()); ?>" target="_blank" rel="noopener">
                                <i class="fas fa-external-link-alt"></i> Ver web
                            </a>
                        </small>
                    </td>
                    <td><span class="badge badge-info"><?php echo ucfirst($dir->getCategoria()); ?></span></td>
                    <td>
                        <?php if ($dir->getDaDirectorio()): ?>
                            <span class="badge badge-authority"><?php echo $dir->getDaDirectorio(); ?></span>
                        <?php else: ?>
                            <span class="text-muted">-</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php
                        $idioma = $dir->getIdioma();
                        $idioma_text = ['ca' => 'CA', 'es' => 'ES', 'en' => 'EN', 'other' => 'Other'][$idioma] ?? $idioma;
                        ?>
                        <span class="badge badge-secondary"><?php echo $idioma_text; ?></span>
                    </td>
                    <td>
                        <?php if ($dir->isNofollow()): ?>
                            <span class="badge badge-rel-nofollow">NoFollow</span>
                        <?php else: ?>
                            <span class="badge badge-rel-dofollow">DoFollow</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($dir->getCosto() > 0): ?>
                            <strong><?php echo number_format($dir->getCosto(), 2); ?>€</strong>
                        <?php else: ?>
                            <span class="badge badge-success">Gratuito</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php 
                        $quality = $dir_data['quality_score'];
                        $quality_class = $quality >= 80 ? 'success' : ($quality >= 60 ? 'warning' : 'danger');
                        ?>
                        <span class="badge badge-<?php echo $quality_class; ?>"><?php echo $quality; ?>/100</span>
                    </td>
                    <td><?php echo date('d/m/Y', strtotime($dir->getFechaCreacion())); ?></td>
                    <td class="actions-cell">
                        <button onclick="window.location.href='gseo.php?tab=offpage&subtab=directories&view=edit&id=<?php echo $dir->getId(); ?>'" 
                                class="btn-icon btn-edit" title="Editar">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button onclick="eliminarDirectorio(<?php echo $dir->getId(); ?>)" 
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
    <div class="empty-state">
        <i class="fas fa-list-ul"></i>
        <h3>No hay directorios registrados</h3>
        <p>Comienza añadiendo directorios donde registrar tu negocio.</p>
        <button onclick="window.location.href='gseo.php?tab=offpage&subtab=directories&view=create'" class="btn btn-primary">
            <i class="fas fa-plus"></i> Añadir Primer Directorio
        </button>
    </div>
    <?php endif; ?>
</div>

<?php endif; ?>

<!-- ============================================ -->
<!-- VISTA: CREAR/EDITAR DIRECTORIO               -->
<!-- ============================================ -->
<?php if ($modo_vista_dir === 'create' || $modo_vista_dir === 'edit'): ?>

<div class="directories-form-container">
    <div class="form-header">
        <h2>
            <i class="fas fa-<?php echo $modo_vista_dir === 'edit' ? 'edit' : 'plus'; ?>"></i>
            <?php echo $modo_vista_dir === 'edit' ? 'Editar' : 'Añadir'; ?> Directorio
        </h2>
        <button onclick="window.location.href='gseo.php?tab=offpage&subtab=directories&view=list'" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver al listado
        </button>
    </div>
    
    <form method="POST" action="gseo.php" class="offpage-form">
        <input type="hidden" name="action" value="<?php echo $modo_vista_dir === 'edit' ? 'update_directorio' : 'create_directorio'; ?>">
        <?php if ($modo_vista_dir === 'edit' && $directorio_edit): ?>
        <input type="hidden" name="id_directorio" value="<?php echo $directorio_edit->getId(); ?>">
        <?php endif; ?>
        
        <!-- Secció 1: Informació Bàsica -->
        <div class="form-section">
            <h3><i class="fas fa-info-circle"></i> Información Básica</h3>
            <div class="form-grid">
                <div class="form-group form-group-full">
                    <label for="nombre">Nombre del directorio *</label>
                    <input type="text" id="nombre" name="nombre" required
                           value="<?php echo $directorio_edit ? htmlspecialchars($directorio_edit->getNombre()) : ''; ?>"
                           placeholder="Ex: Google My Business, Yelp, Páginas Amarillas...">
                </div>
                
                <div class="form-group form-group-full">
                    <label for="url">URL del directorio *</label>
                    <input type="url" id="url" name="url" required
                           value="<?php echo $directorio_edit ? htmlspecialchars($directorio_edit->getUrl()) : ''; ?>"
                           placeholder="https://www.ejemplo.com">
                </div>
                
                <div class="form-group">
                    <label for="categoria">Categoría *</label>
                    <select id="categoria" name="categoria" required>
                        <option value="psicologia" <?php echo ($directorio_edit && $directorio_edit->getCategoria() === 'psicologia') ? 'selected' : ''; ?>>Psicología</option>
                        <option value="salud" <?php echo ($directorio_edit && $directorio_edit->getCategoria() === 'salud') ? 'selected' : ''; ?>>Salud</option>
                        <option value="locales" <?php echo ($directorio_edit && $directorio_edit->getCategoria() === 'locales') ? 'selected' : ''; ?>>Locales</option>
                        <option value="negocios" <?php echo ($directorio_edit && $directorio_edit->getCategoria() === 'negocios') ? 'selected' : ''; ?>>Negocios</option>
                        <option value="generico" <?php echo ($directorio_edit && $directorio_edit->getCategoria() === 'generico') ? 'selected' : ''; ?>>Genérico</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="idioma">Idioma *</label>
                    <select id="idioma" name="idioma" required>
                        <option value="ca" <?php echo ($directorio_edit && $directorio_edit->getIdioma() === 'ca') ? 'selected' : ''; ?>>Català</option>
                        <option value="es" <?php echo ($directorio_edit && $directorio_edit->getIdioma() === 'es') ? 'selected' : ''; ?>>Español</option>
                        <option value="en" <?php echo ($directorio_edit && $directorio_edit->getIdioma() === 'en') ? 'selected' : ''; ?>>English</option>
                        <option value="other" <?php echo ($directorio_edit && $directorio_edit->getIdioma() === 'other') ? 'selected' : ''; ?>>Otros</option>
                    </select>
                </div>
            </div>
        </div>
        
        <!-- Sección 2: Métricas y Coste -->
        <div class="form-section">
            <h3><i class="fas fa-chart-line"></i> Métricas y Coste</h3>
            <div class="form-grid">
                <div class="form-group">
                    <label for="da_directorio">Domain Authority (DA)</label>
                    <input type="number" id="da_directorio" name="da_directorio" min="0" max="100"
                           value="<?php echo $directorio_edit && $directorio_edit->getDaDirectorio() ? $directorio_edit->getDaDirectorio() : ''; ?>"
                           placeholder="0-100">
                    <small class="form-help">Autoridad del dominio del directorio (opcional)</small>
                </div>
                
                <div class="form-group">
                    <label for="costo">Coste anual (€)</label>
                    <input type="number" id="costo" name="costo" min="0" step="0.01"
                           value="<?php echo $directorio_edit ? $directorio_edit->getCosto() : '0'; ?>"
                           placeholder="0.00">
                    <small class="form-help">0 si es gratuito</small>
                </div>
            </div>
        </div>
        
        <!-- Sección 3: Atributos SEO -->
        <div class="form-section">
            <h3><i class="fas fa-cog"></i> Atributos SEO</h3>
            <div class="form-grid">
                <div class="form-group form-group-full">
                    <div class="checkbox-group">
                        <label class="checkbox-label">
                            <input type="checkbox" name="nofollow" value="1"
                                   <?php echo ($directorio_edit && $directorio_edit->isNofollow()) ? 'checked' : ''; ?>>
                            <span>NoFollow</span>
                            <small>El enlace desde el directorio es nofollow</small>
                        </label>
                        
                        <label class="checkbox-label">
                            <input type="checkbox" name="permite_anchor_personalizado" value="1"
                                   <?php echo (!$directorio_edit || $directorio_edit->permiteAnchorPersonalizado()) ? 'checked' : ''; ?>>
                            <span>Permite Anchor Personalizado</span>
                            <small>El directorio permite personalizar el texto del enlace</small>
                        </label>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Sección 4: Estado y Fechas -->
        <div class="form-section">
            <h3><i class="fas fa-tasks"></i> Estado y Seguimiento</h3>
            <div class="form-grid">
                <div class="form-group">
                    <label for="estado">Estado *</label>
                    <select id="estado" name="estado" required>
                        <option value="pendiente" <?php echo (!$directorio_edit || $directorio_edit->getEstado() === 'pendiente') ? 'selected' : ''; ?>>Pendiente</option>
                        <option value="enviado" <?php echo ($directorio_edit && $directorio_edit->getEstado() === 'enviado') ? 'selected' : ''; ?>>Enviado</option>
                        <option value="aprobado" <?php echo ($directorio_edit && $directorio_edit->getEstado() === 'aprobado') ? 'selected' : ''; ?>>Aprobado</option>
                        <option value="activo" <?php echo ($directorio_edit && $directorio_edit->getEstado() === 'activo') ? 'selected' : ''; ?>>Activo</option>
                        <option value="rechazado" <?php echo ($directorio_edit && $directorio_edit->getEstado() === 'rechazado') ? 'selected' : ''; ?>>Rechazado</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="fecha_envio">Data de envío</label>
                    <input type="date" id="fecha_envio" name="fecha_envio"
                           value="<?php echo $directorio_edit && $directorio_edit->getFechaEnvio() ? $directorio_edit->getFechaEnvio() : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="fecha_aprobacion">Data de aprobación</label>
                    <input type="date" id="fecha_aprobacion" name="fecha_aprobacion"
                           value="<?php echo $directorio_edit && $directorio_edit->getFechaAprobacion() ? $directorio_edit->getFechaAprobacion() : ''; ?>">
                </div>
            </div>
        </div>
        
        <!-- Secció 5: Notes -->
        <div class="form-section">
            <h3><i class="fas fa-sticky-note"></i> Notas</h3>
            <div class="form-group form-group-full">
                <label for="notas">Notas Internas</label>
                <textarea id="notas" name="notas" rows="4"
                          placeholder="Notas sobre el proceso de registro, requisitos, etc..."><?php echo $directorio_edit ? htmlspecialchars($directorio_edit->getNotas() ?? '') : ''; ?></textarea>
            </div>
        </div>
        
        <!-- Botones de Acción -->
        <div class="form-actions">
            <button type="button" onclick="window.location.href='gseo.php?tab=offpage&subtab=directories&view=list'" class="btn btn-secondary">
                <i class="fas fa-times"></i> Cancelar
            </button>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> <?php echo $modo_vista_dir === 'edit' ? 'Actualizar' : 'Crear'; ?> Directorio
            </button>
        </div>
    </form>
</div>

<?php endif; ?>

<!-- JavaScript -->
<script>
function aplicarFiltrosDirectories() {
    const estado = document.getElementById('filter-estado').value;
    const categoria = document.getElementById('filter-categoria').value;
    const idioma = document.getElementById('filter-idioma').value;
    
    const url = new URL(window.location);
    url.searchParams.set('tab', 'offpage');
    url.searchParams.set('subtab', 'directories');
    url.searchParams.set('view', 'list');
    url.searchParams.set('estado', estado);
    url.searchParams.set('categoria', categoria);
    url.searchParams.set('idioma', idioma);
    
    window.location.href = url.toString();
}

function limpiarFiltrosDirectories() {
    window.location.href = 'gseo.php?tab=offpage&subtab=directories&view=list';
}

function eliminarDirectorio(id) {
    if (confirm('Estàs segur que vols eliminar aquest directori? Aquesta acció no es pot desfer.')) {
        window.location.href = 'gseo.php?action=delete_directorio&id_directorio=' + id;
    }
}
</script>
