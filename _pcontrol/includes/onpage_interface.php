<?php
/**
 * Interfície SEO On-Page
 * Gestió de pàgines individuals amb configuració SEO completa
 */

$modo_vista = isset($_GET['edit']) || isset($_GET['new']) ? 'edit' : 'list';
?>

<!-- On-Page SEO Tab -->
<div id="onpage-tab" class="tab-content <?php echo $activeTab === 'onpage' ? 'active' : ''; ?>">
    
    <?php if ($modo_vista === 'list'): ?>
    <!-- VISTA: LLISTAT DE PÀGINES -->
    
    <div class="onpage-header">
        <div class="onpage-header-left">
            <h2><i class="fas fa-file-code"></i> Gestión SEO On-Page</h2>
            <p>Configura el SEO específico de cada página de tu web</p>
        </div>
        <div class="onpage-header-right">
            <a href="gseo.php?tab=onpage&new=1" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nueva Página
            </a>
        </div>
    </div>

    <!-- Filtros -->
    <div class="onpage-filters">
        <div class="filter-group">
            <label><i class="fas fa-filter"></i> Filtrar por tipo:</label>
            <select onchange="window.location.href='gseo.php?tab=onpage&tipo=' + this.value">
                <option value="all" <?php echo $tipo_filtro === 'all' ? 'selected' : ''; ?>>Todas las páginas</option>
                <option value="home" <?php echo $tipo_filtro === 'home' ? 'selected' : ''; ?>>Home</option>
                <option value="sobre-mi" <?php echo $tipo_filtro === 'sobre-mi' ? 'selected' : ''; ?>>Sobre Mí</option>
                <option value="servicios" <?php echo $tipo_filtro === 'servicios' ? 'selected' : ''; ?>>Servicios</option>
                <option value="blog" <?php echo $tipo_filtro === 'blog' ? 'selected' : ''; ?>>Blog</option>
                <option value="articulo" <?php echo $tipo_filtro === 'articulo' ? 'selected' : ''; ?>>Artículos</option>
                <option value="contacto" <?php echo $tipo_filtro === 'contacto' ? 'selected' : ''; ?>>Contacto</option>
                <option value="landing" <?php echo $tipo_filtro === 'landing' ? 'selected' : ''; ?>>Landing Pages</option>
            </select>
        </div>
        <div class="filter-stats">
            <span class="stat-item">
                <i class="fas fa-file-alt"></i> <strong><?php echo count($paginas_onpage); ?></strong> páginas
            </span>
        </div>
    </div>

    <!-- Tabla de páginas -->
    <?php if (empty($paginas_onpage)): ?>
    <div class="empty-state">
        <i class="fas fa-file-alt"></i>
        <h3>No hay páginas configuradas</h3>
        <p>Comienza añadiendo tu primera página para gestionar su SEO</p>
        <a href="gseo.php?tab=onpage&new=1" class="btn btn-primary">
            <i class="fas fa-plus"></i> Añadir Primera Página
        </a>
    </div>
    <?php else: ?>
    <div class="onpage-table-container">
        <table class="onpage-table">
            <thead>
                <tr>
                    <th><i class="fas fa-chart-line"></i> Score</th>
                    <th><i class="fas fa-heading"></i> Título</th>
                    <th><i class="fas fa-link"></i> URL CA</th>
                    <th><i class="fas fa-link"></i> URL ES</th>
                    <th><i class="fas fa-tag"></i> Tipo</th>
                    <th><i class="fas fa-language"></i> Idiomas</th>
                    <th><i class="fas fa-calendar"></i> Publicación</th>
                    <th><i class="fas fa-toggle-on"></i> Estado</th>
                    <th><i class="fas fa-cogs"></i> Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($paginas_onpage as $pagina): ?>
                <tr class="<?php echo $pagina->isActiva() ? '' : 'inactive-row'; ?>">
                    <td>
                        <div class="score-badge score-<?php 
                            $score = $pagina->getSeoScore();
                            echo $score >= 80 ? 'excellent' : ($score >= 60 ? 'good' : 'poor');
                        ?>">
                            <?php echo $score; ?>
                        </div>
                    </td>
                    <td>
                        <div class="page-title-cell">
                            <strong><?php echo htmlspecialchars($pagina->getTituloPagina()); ?></strong>
                            <small><?php echo htmlspecialchars($pagina->getTitle('ca')); ?></small>
                        </div>
                    </td>
                    <td>
                        <code class="url-code"><?php echo htmlspecialchars($pagina->getUrlRelativaCa()); ?></code>
                    </td>
                    <td>
                        <code class="url-code"><?php echo htmlspecialchars($pagina->getUrlRelativaEs()); ?></code>
                    </td>
                    <td>
                        <span class="type-badge type-<?php echo $pagina->getTipoPagina(); ?>">
                            <?php echo ucfirst($pagina->getTipoPagina()); ?>
                        </span>
                    </td>
                    <td>
                        <div class="lang-indicators">
                            <span class="lang-badge lang-ca" title="Català">CA</span>
                            <span class="lang-badge lang-es" title="Español">ES</span>
                        </div>
                    </td>
                    <td>
                        <?php 
                        $fecha = $pagina->getFechaPublicacion();
                        echo $fecha ? date('d/m/Y', strtotime($fecha)) : '-';
                        ?>
                    </td>
                    <td>
                        <span class="status-badge status-<?php echo $pagina->isActiva() ? 'active' : 'inactive'; ?>">
                            <i class="fas fa-circle"></i> <?php echo $pagina->isActiva() ? 'Activa' : 'Inactiva'; ?>
                        </span>
                    </td>
                    <td>
                        <div class="action-buttons">
                            <a href="gseo.php?tab=onpage&edit=<?php echo $pagina->getIdPagina(); ?>" 
                               class="btn-icon btn-edit" title="Editar">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button type="button" class="btn-icon btn-delete" 
                                    onclick="confirmarEliminar(<?php echo $pagina->getIdPagina(); ?>, '<?php echo htmlspecialchars($pagina->getTituloPagina(), ENT_QUOTES); ?>')"
                                    title="Eliminar">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    <?php else: ?>
    <!-- VISTA: EDITAR/CREAR PÀGINA -->
    
    <div class="onpage-edit-header">
        <a href="gseo.php?tab=onpage" class="btn-back">
            <i class="fas fa-arrow-left"></i> Volver al listado
        </a>
        <h2>
            <i class="fas fa-<?php echo isset($_GET['new']) ? 'plus' : 'edit'; ?>"></i>
            <?php echo isset($_GET['new']) ? 'Nueva Página SEO' : 'Editar Página SEO'; ?>
        </h2>
    </div>

    <form action="gseo.php" method="POST" class="seo-form">
        <input type="hidden" name="action" value="<?php echo isset($_GET['new']) ? 'create_onpage' : 'save_onpage'; ?>">
        <?php if ($pagina_edit): ?>
        <input type="hidden" name="id_pagina" value="<?php echo $pagina_edit->getIdPagina(); ?>">
        <?php endif; ?>

        <!-- 1. IDENTIFICACIÓN Y URL -->
        <section class="seo-section">
            <div class="section-header">
                <h2><i class="fas fa-link"></i> Identificación y URL</h2>
                <p class="section-description">Información básica de la página</p>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label><i class="fas fa-heading"></i> Título de la Página <span class="required">*</span></label>
                    <input type="text" name="titulo_pagina" required
                           value="<?php echo $pagina_edit ? htmlspecialchars($pagina_edit->getTituloPagina()) : ''; ?>"
                           placeholder="Ej: Página de Inicio">
                    <small>Título interno para identificar la página (no se muestra en la web)</small>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label><i class="fas fa-link"></i> URL Relativa Català <span class="required">*</span></label>
                    <input type="text" name="url_relativa_ca" required
                           value="<?php echo $pagina_edit ? htmlspecialchars($pagina_edit->getUrlRelativaCa()) : ''; ?>"
                           placeholder="Ej: /terapia-ansietat">
                    <small>URL en català (sense domini, ha de començar per /)</small>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-link"></i> URL Relativa Español <span class="required">*</span></label>
                    <input type="text" name="url_relativa_es" required
                           value="<?php echo $pagina_edit ? htmlspecialchars($pagina_edit->getUrlRelativaEs()) : ''; ?>"
                           placeholder="Ej: /terapia-ansiedad">
                    <small>URL en castellano (sin dominio, debe empezar por /)</small>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-tag"></i> Tipo de Página <span class="required">*</span></label>
                    <select name="tipo_pagina" required>
                        <option value="home" <?php echo $pagina_edit && $pagina_edit->getTipoPagina() === 'home' ? 'selected' : ''; ?>>Home</option>
                        <option value="sobre-mi" <?php echo $pagina_edit && $pagina_edit->getTipoPagina() === 'sobre-mi' ? 'selected' : ''; ?>>Sobre Mí</option>
                        <option value="servicios" <?php echo $pagina_edit && $pagina_edit->getTipoPagina() === 'servicios' ? 'selected' : ''; ?>>Servicios</option>
                        <option value="blog" <?php echo $pagina_edit && $pagina_edit->getTipoPagina() === 'blog' ? 'selected' : ''; ?>>Blog</option>
                        <option value="articulo" <?php echo $pagina_edit && $pagina_edit->getTipoPagina() === 'articulo' ? 'selected' : ''; ?>>Artículo</option>
                        <option value="contacto" <?php echo $pagina_edit && $pagina_edit->getTipoPagina() === 'contacto' ? 'selected' : ''; ?>>Contacto</option>
                        <option value="legal" <?php echo $pagina_edit && $pagina_edit->getTipoPagina() === 'legal' ? 'selected' : ''; ?>>Legal</option>
                        <option value="landing" <?php echo $pagina_edit && $pagina_edit->getTipoPagina() === 'landing' ? 'selected' : ''; ?>>Landing Page</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label><i class="fas fa-link"></i> Slug Català</label>
                    <input type="text" name="slug_ca"
                           value="<?php echo $pagina_edit ? htmlspecialchars($pagina_edit->getSlug('ca') ?? '') : ''; ?>"
                           placeholder="terapia-ansietat">
                </div>
                <div class="form-group">
                    <label><i class="fas fa-link"></i> Slug Español</label>
                    <input type="text" name="slug_es"
                           value="<?php echo $pagina_edit ? htmlspecialchars($pagina_edit->getSlug('es') ?? '') : ''; ?>"
                           placeholder="terapia-ansiedad">
                </div>
            </div>
        </section>

        <!-- 2. SEO BÀSIC CATALÀ -->
        <section class="seo-section">
            <div class="section-header">
                <h2><i class="fas fa-language"></i> SEO Básico - Català</h2>
                <p class="section-description">Meta tags y contenido en catalán</p>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>
                        <i class="fas fa-heading"></i> Meta Title (Català) <span class="required">*</span>
                        <span class="char-counter" id="title_ca_counter">0/60</span>
                    </label>
                    <input type="text" name="title_ca" id="title_ca" required maxlength="60"
                           value="<?php echo $pagina_edit ? htmlspecialchars($pagina_edit->getTitle('ca')) : ''; ?>"
                           placeholder="Teràpia per l'ansietat a Girona | Yanina Parisi"
                           oninput="updateCharCounter(this, 'title_ca_counter')">
                    <small><i class="fas fa-info-circle"></i> Recomanat: 50-60 caràcters</small>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>
                        <i class="fas fa-align-left"></i> Meta Description (Català) <span class="required">*</span>
                        <span class="char-counter" id="description_ca_counter">0/160</span>
                    </label>
                    <textarea name="meta_description_ca" id="meta_description_ca" required maxlength="160" rows="3"
                              placeholder="Descripció optimitzada per SEO..."
                              oninput="updateCharCounter(this, 'description_ca_counter')"><?php echo $pagina_edit ? htmlspecialchars($pagina_edit->getMetaDescription('ca')) : ''; ?></textarea>
                    <small><i class="fas fa-info-circle"></i> Recomanat: 150-160 caràcters</small>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label><i class="fas fa-h1"></i> H1 Principal (Català) <span class="required">*</span></label>
                    <input type="text" name="h1_ca" required maxlength="100"
                           value="<?php echo $pagina_edit ? htmlspecialchars($pagina_edit->getH1('ca')) : ''; ?>"
                           placeholder="Títol H1 visible a la pàgina">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label><i class="fas fa-key"></i> Paraula Clau Principal (Català)</label>
                    <input type="text" name="focus_keyword_ca"
                           value="<?php echo $pagina_edit ? htmlspecialchars($pagina_edit->getFocusKeyword('ca') ?? '') : ''; ?>"
                           placeholder="teràpia ansietat girona">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label><i class="fas fa-tags"></i> Paraules Clau Secundàries (Català)</label>
                    <input type="text" name="keywords_secundarias_ca"
                           value="<?php echo $pagina_edit && $pagina_edit->getFocusKeyword('ca') ? implode(', ', $pagina_edit->getKeywordsSecundarias('ca')) : ''; ?>"
                           placeholder="psicòloga ansietat, tractament ansietat, superar ansietat">
                    <small>Separades per comes</small>
                </div>
            </div>
        </section>

        <!-- 3. SEO BÀSIC ESPAÑOL -->
        <section class="seo-section">
            <div class="section-header">
                <h2><i class="fas fa-language"></i> SEO Básico - Español</h2>
                <p class="section-description">Meta tags y contenido en español</p>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>
                        <i class="fas fa-heading"></i> Meta Title (Español) <span class="required">*</span>
                        <span class="char-counter" id="title_es_counter">0/60</span>
                    </label>
                    <input type="text" name="title_es" id="title_es" required maxlength="60"
                           value="<?php echo $pagina_edit ? htmlspecialchars($pagina_edit->getTitle('es')) : ''; ?>"
                           placeholder="Terapia para la ansiedad en Girona | Yanina Parisi"
                           oninput="updateCharCounter(this, 'title_es_counter')">
                    <small><i class="fas fa-info-circle"></i> Recomendado: 50-60 caracteres</small>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>
                        <i class="fas fa-align-left"></i> Meta Description (Español) <span class="required">*</span>
                        <span class="char-counter" id="description_es_counter">0/160</span>
                    </label>
                    <textarea name="meta_description_es" id="meta_description_es" required maxlength="160" rows="3"
                              placeholder="Descripción optimizada para SEO..."
                              oninput="updateCharCounter(this, 'description_es_counter')"><?php echo $pagina_edit ? htmlspecialchars($pagina_edit->getMetaDescription('es')) : ''; ?></textarea>
                    <small><i class="fas fa-info-circle"></i> Recomendado: 150-160 caracteres</small>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label><i class="fas fa-h1"></i> H1 Principal (Español) <span class="required">*</span></label>
                    <input type="text" name="h1_es" required maxlength="100"
                           value="<?php echo $pagina_edit ? htmlspecialchars($pagina_edit->getH1('es')) : ''; ?>"
                           placeholder="Título H1 visible en la página">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label><i class="fas fa-key"></i> Palabra Clave Principal (Español)</label>
                    <input type="text" name="focus_keyword_es"
                           value="<?php echo $pagina_edit ? htmlspecialchars($pagina_edit->getFocusKeyword('es') ?? '') : ''; ?>"
                           placeholder="terapia ansiedad girona">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label><i class="fas fa-tags"></i> Palabras Clave Secundarias (Español)</label>
                    <input type="text" name="keywords_secundarias_es"
                           value="<?php echo $pagina_edit && $pagina_edit->getFocusKeyword('es') ? implode(', ', $pagina_edit->getKeywordsSecundarias('es')) : ''; ?>"
                           placeholder="psicóloga ansiedad, tratamiento ansiedad, superar ansiedad">
                    <small>Separadas por comas</small>
                </div>
            </div>
        </section>

        <!-- 4. SEO TÈCNIC -->
        <section class="seo-section">
            <div class="section-header">
                <h2><i class="fas fa-cogs"></i> SEO Técnico</h2>
                <p class="section-description">Configuración técnica y robots</p>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label><i class="fas fa-robot"></i> Meta Robots</label>
                    <input type="text" name="meta_robots"
                           value="<?php echo $pagina_edit ? htmlspecialchars($pagina_edit->getMetaRobots()) : 'index, follow'; ?>"
                           placeholder="index, follow">
                    <small>Directiva para robots de búsqueda</small>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-link"></i> URL Canónica</label>
                    <input type="text" name="canonical_url"
                           value="<?php echo $pagina_edit ? htmlspecialchars($pagina_edit->getCanonicalUrl() ?? '') : ''; ?>"
                           placeholder="https://www.psicologiayanina.com/terapia">
                    <small>Solo si es diferente a la URL relativa</small>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label><i class="fas fa-star"></i> Prioridad Sitemap</label>
                    <select name="priority">
                        <option value="1.0" <?php echo $pagina_edit && $pagina_edit->getPriority() === '1.0' ? 'selected' : ''; ?>>1.0 (Máxima)</option>
                        <option value="0.8" <?php echo $pagina_edit && $pagina_edit->getPriority() === '0.8' ? 'selected' : ''; ?>>0.8 (Alta)</option>
                        <option value="0.6" <?php echo $pagina_edit && $pagina_edit->getPriority() === '0.6' ? 'selected' : ''; ?>>0.6 (Media)</option>
                        <option value="0.4" <?php echo $pagina_edit && $pagina_edit->getPriority() === '0.4' ? 'selected' : ''; ?>>0.4 (Baja)</option>
                        <option value="0.2" <?php echo $pagina_edit && $pagina_edit->getPriority() === '0.2' ? 'selected' : ''; ?>>0.2 (Mínima)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-sync"></i> Frecuencia de Cambio</label>
                    <select name="changefreq">
                        <option value="always" <?php echo $pagina_edit && $pagina_edit->getChangefreq() === 'always' ? 'selected' : ''; ?>>Siempre</option>
                        <option value="hourly" <?php echo $pagina_edit && $pagina_edit->getChangefreq() === 'hourly' ? 'selected' : ''; ?>>Cada hora</option>
                        <option value="daily" <?php echo $pagina_edit && $pagina_edit->getChangefreq() === 'daily' ? 'selected' : ''; ?>>Diariamente</option>
                        <option value="weekly" <?php echo $pagina_edit && $pagina_edit->getChangefreq() === 'weekly' ? 'selected' : ''; ?>>Semanalmente</option>
                        <option value="monthly" <?php echo $pagina_edit && $pagina_edit->getChangefreq() === 'monthly' ? 'selected' : ''; ?>>Mensualmente</option>
                        <option value="yearly" <?php echo $pagina_edit && $pagina_edit->getChangefreq() === 'yearly' ? 'selected' : ''; ?>>Anualmente</option>
                        <option value="never" <?php echo $pagina_edit && $pagina_edit->getChangefreq() === 'never' ? 'selected' : ''; ?>>Nunca</option>
                    </select>
                </div>
            </div>
        </section>

        <!-- 5. OPEN GRAPH -->
        <section class="seo-section">
            <div class="section-header">
                <h2><i class="fab fa-facebook"></i> Open Graph (Opcional)</h2>
                <p class="section-description">Meta tags para redes sociales (Facebook, LinkedIn)</p>
            </div>

            <div class="section-content">
                <div class="form-row">
                    <div class="form-group">
                        <label><i class="fab fa-facebook"></i> OG Title (Català)</label>
                        <input type="text" name="og_title_ca"
                               value="<?php echo $pagina_edit ? htmlspecialchars($pagina_edit->getOgTitle('ca') ?? '') : ''; ?>"
                               placeholder="Deixar buit per usar el meta title">
                    </div>
                    <div class="form-group">
                        <label><i class="fab fa-facebook"></i> OG Title (Español)</label>
                        <input type="text" name="og_title_es"
                               value="<?php echo $pagina_edit ? htmlspecialchars($pagina_edit->getOgTitle('es') ?? '') : ''; ?>"
                               placeholder="Dejar vacío para usar el meta title">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label><i class="fas fa-align-left"></i> OG Description (Català)</label>
                        <textarea name="og_description_ca" rows="2"
                                  placeholder="Deixar buit per usar la meta description"><?php echo $pagina_edit ? htmlspecialchars($pagina_edit->getOgDescription('ca') ?? '') : ''; ?></textarea>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-align-left"></i> OG Description (Español)</label>
                        <textarea name="og_description_es" rows="2"
                                  placeholder="Dejar vacío para usar la meta description"><?php echo $pagina_edit ? htmlspecialchars($pagina_edit->getOgDescription('es') ?? '') : ''; ?></textarea>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label><i class="fas fa-image"></i> Imagen Open Graph</label>
                        <input type="text" name="og_image"
                               value="<?php echo $pagina_edit ? htmlspecialchars($pagina_edit->getOgImage() ?? '') : ''; ?>"
                               placeholder="https://www.psicologiayanina.com/img/og-image.jpg">
                        <small>Tamaño recomendado: 1200x630px</small>
                    </div>
                </div>
            </div>
        </section>

        <!-- 6. TWITTER CARDS -->
        <section class="seo-section">
            <div class="section-header">
                <h2><i class="fab fa-twitter"></i> Twitter Cards (Opcional)</h2>
                <p class="section-description">Meta tags para Twitter/X</p>
            </div>

            <div class="section-content">
                <div class="form-row">
                    <div class="form-group">
                        <label><i class="fab fa-twitter"></i> Twitter Title (Català)</label>
                        <input type="text" name="twitter_title_ca"
                               value="<?php echo $pagina_edit ? htmlspecialchars($pagina_edit->getTwitterTitle('ca') ?? '') : ''; ?>"
                               placeholder="Deixar buit per usar el meta title">
                    </div>
                    <div class="form-group">
                        <label><i class="fab fa-twitter"></i> Twitter Title (Español)</label>
                        <input type="text" name="twitter_title_es"
                               value="<?php echo $pagina_edit ? htmlspecialchars($pagina_edit->getTwitterTitle('es') ?? '') : ''; ?>"
                               placeholder="Dejar vacío para usar el meta title">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label><i class="fas fa-align-left"></i> Twitter Description (Català)</label>
                        <textarea name="twitter_description_ca" rows="2"
                                  placeholder="Deixar buit per usar la meta description"><?php echo $pagina_edit ? htmlspecialchars($pagina_edit->getTwitterDescription('ca') ?? '') : ''; ?></textarea>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-align-left"></i> Twitter Description (Español)</label>
                        <textarea name="twitter_description_es" rows="2"
                                  placeholder="Dejar vacío para usar la meta description"><?php echo $pagina_edit ? htmlspecialchars($pagina_edit->getTwitterDescription('es') ?? '') : ''; ?></textarea>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label><i class="fas fa-image"></i> Imagen Twitter Card</label>
                        <input type="text" name="twitter_image"
                               value="<?php echo $pagina_edit ? htmlspecialchars($pagina_edit->getTwitterImage() ?? '') : ''; ?>"
                               placeholder="https://www.psicologiayanina.com/img/twitter-card.jpg">
                        <small>Tamaño recomendado: 1200x675px</small>
                    </div>
                </div>
            </div>
        </section>

        <!-- 7. IMATGES -->
        <section class="seo-section">
            <div class="section-header">
                <h2><i class="fas fa-image"></i> Imágenes SEO (Opcional)</h2>
                <p class="section-description">Imagen destacada y textos ALT</p>
            </div>

            <div class="section-content">
                <div class="form-row">
                    <div class="form-group">
                        <label><i class="fas fa-image"></i> Imagen Destacada</label>
                        <input type="text" name="featured_image"
                               value="<?php echo $pagina_edit ? htmlspecialchars($pagina_edit->getFeaturedImage() ?? '') : ''; ?>"
                               placeholder="https://www.psicologiayanina.com/img/featured.jpg">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label><i class="fas fa-font"></i> Texto ALT (Català)</label>
                        <input type="text" name="alt_image_ca" maxlength="125"
                               value="<?php echo $pagina_edit ? htmlspecialchars($pagina_edit->getAltImage('ca') ?? '') : ''; ?>"
                               placeholder="Descripció de la imatge per accessibilitat">
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-font"></i> Texto ALT (Español)</label>
                        <input type="text" name="alt_image_es" maxlength="125"
                               value="<?php echo $pagina_edit ? htmlspecialchars($pagina_edit->getAltImage('es') ?? '') : ''; ?>"
                               placeholder="Descripción de la imagen para accesibilidad">
                    </div>
                </div>
            </div>
        </section>

        <!-- 8. ESTAT I PUBLICACIÓ -->
        <section class="seo-section">
            <div class="section-header">
                <h2><i class="fas fa-toggle-on"></i> Estado y Publicación</h2>
                <p class="section-description">Control de visibilidad y fecha</p>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="activa" value="1"
                               <?php echo !$pagina_edit || $pagina_edit->isActiva() ? 'checked' : ''; ?>>
                        <span><i class="fas fa-eye"></i> Página activa y visible</span>
                    </label>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-calendar"></i> Fecha de Publicación</label>
                    <input type="datetime-local" name="fecha_publicacion"
                           value="<?php echo $pagina_edit && $pagina_edit->getFechaPublicacion() ? date('Y-m-d\TH:i', strtotime($pagina_edit->getFechaPublicacion())) : ''; ?>">
                </div>
            </div>
        </section>

        <!-- Botones de acción -->
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> <?php echo isset($_GET['new']) ? 'Crear Página' : 'Guardar Cambios'; ?>
            </button>
            <a href="gseo.php?tab=onpage" class="btn btn-secondary">
                <i class="fas fa-times"></i> Cancelar
            </a>
        </div>
    </form>

    <?php endif; ?>
</div>

<!-- Script para confirmación de eliminación -->
<script>
function confirmarEliminar(id, titulo) {
    if (confirm('¿Estás seguro de que quieres eliminar la página "' + titulo + '"?\n\nEsta acción no se puede deshacer.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'gseo.php';
        
        const actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = 'delete_onpage';
        form.appendChild(actionInput);
        
        const idInput = document.createElement('input');
        idInput.type = 'hidden';
        idInput.name = 'id_pagina';
        idInput.value = id;
        form.appendChild(idInput);
        
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
