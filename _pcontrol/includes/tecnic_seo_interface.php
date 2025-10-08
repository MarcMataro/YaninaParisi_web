<?php
/**
 * Interfície SEO Tècnic
 * Formulari i llistat per gestionar els audits tècnics mitjançant la classe SEO_Tecnico
 */

$modo_vista = isset($_GET['edit_tecnico']) || isset($_GET['new_tecnico']) ? 'edit' : 'list';
?>

<!-- Technical SEO Tab -->
<div id="technical-tab" class="tab-content <?php echo $activeTab === 'technical' ? 'active' : ''; ?>">

    <?php if ($modo_vista === 'list'): ?>
    <div class="technical-header">
        <div class="technical-header-left">
            <h2><i class="fas fa-cogs"></i> SEO Técnico</h2>
            <p>Auditorías técnicas y métricas del sitio</p>
        </div>
        <div class="technical-header-right">
            <a href="gseo.php?tab=technical&new_tecnico=1" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nueva Auditoría
            </a>
        </div>
    </div>

    <?php if (empty($seo_tecnico_list)): ?>
    <div class="empty-state">
        <i class="fas fa-cogs"></i>
        <h3>No hay auditorías técnicas</h3>
        <p>Genera tu primera auditoría técnica para comenzar el seguimiento.</p>
        <a href="gseo.php?tab=technical&new_tecnico=1" class="btn btn-primary">
            <i class="fas fa-plus"></i> Añadir Auditoría
        </a>
    </div>
    <?php else: ?>
    <div class="technical-table-container">
        <table class="technical-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Última auditoría</th>
                    <th>Lighthouse</th>
                    <th>Vel. carga (ms)</th>
                    <th>Uptime 30d</th>
                    <th>Puntuación técnica</th>
                    <th>Criticidad</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($seo_tecnico_list as $t): ?>
                <tr>
                    <td><?php echo htmlspecialchars($t['id_tecnico']); ?></td>
                    <td><?php echo $t['ultima_auditoria_completa'] ? date('d/m/Y H:i', strtotime($t['ultima_auditoria_completa'])) : '-'; ?></td>
                    <td><?php echo $t['puntuacion_lighthouse'] ?? '-'; ?></td>
                    <td><?php echo $t['velocidad_carga_ms'] ?? '-'; ?></td>
                    <td><?php echo isset($t['uptime_30d']) ? $t['uptime_30d'] . '%' : '-'; ?></td>
                    <td><?php echo $t['puntuacion_seo_tecnico'] ?? '-'; ?></td>
                    <td><?php echo ucfirst($t['criticidad_issues'] ?? 'media'); ?></td>
                    <td>
                        <a href="gseo.php?tab=technical&edit_tecnico=<?php echo $t['id_tecnico']; ?>" class="btn-icon btn-edit" title="Editar">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="gseo.php" method="POST" style="display:inline-block" onsubmit="return confirm('Eliminar auditoría?');">
                            <input type="hidden" name="action" value="delete_tecnico">
                            <input type="hidden" name="id_tecnico" value="<?php echo $t['id_tecnico']; ?>">
                            <button class="btn-icon btn-delete" title="Eliminar"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    <?php else: ?>
    <!-- VISTA: EDITAR/CREAR AUDITORÍA -->
    <div class="technical-edit-header">
        <a href="gseo.php?tab=technical" class="btn-back">
            <i class="fas fa-arrow-left"></i> Volver a SEO Técnico
        </a>
        <h2><i class="fas fa-<?php echo isset($_GET['new_tecnico']) ? 'plus' : 'edit'; ?>"></i>
            <?php echo isset($_GET['new_tecnico']) ? 'Nueva Auditoría Técnica' : 'Editar Auditoría Técnica'; ?>
        </h2>
    </div>

    <?php
    // Si estem editant, convertim l'objecte a array de propietats per facilitar l'ús
    $tdata = [];
    if ($tecnico_edit) {
        $tdata = $tecnico_edit->toArray();
    }
    ?>

    <form action="gseo.php" method="POST" class="seo-form">
        <input type="hidden" name="action" value="<?php echo isset($_GET['new_tecnico']) ? 'create_tecnico' : 'save_tecnico'; ?>">
        <?php if ($tecnico_edit): ?>
        <input type="hidden" name="id_tecnico" value="<?php echo $tecnico_edit->get('id_tecnico'); ?>">
        <?php endif; ?>

        <!-- Secció 1: Rendiment i velocitat -->
        <section class="seo-section">
            <div class="section-header">
                <h2><i class="fas fa-tachometer-alt"></i> Rendimiento y Velocidad</h2>
                <p class="section-description">Métricas clave de performance</p>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Velocidad carga (ms)</label>
                    <input type="number" name="velocidad_carga_ms" value="<?php echo htmlspecialchars($tdata['velocidad_carga_ms'] ?? ''); ?>" min="0">
                </div>
                <div class="form-group">
                    <label>First Paint (ms)</label>
                    <input type="number" name="velocidad_primera_pintura" value="<?php echo htmlspecialchars($tdata['velocidad_primera_pintura'] ?? ''); ?>" min="0">
                </div>
                <div class="form-group">
                    <label>First Contentful Paint (ms)</label>
                    <input type="number" name="velocidad_pintura_contenido" value="<?php echo htmlspecialchars($tdata['velocidad_pintura_contenido'] ?? ''); ?>" min="0">
                </div>
                <div class="form-group">
                    <label>Time to Interactive (ms)</label>
                    <input type="number" name="velocidad_interactividad" value="<?php echo htmlspecialchars($tdata['velocidad_interactividad'] ?? ''); ?>" min="0">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Puntuación Lighthouse (0-100)</label>
                    <input type="number" name="puntuacion_lighthouse" value="<?php echo htmlspecialchars($tdata['puntuacion_lighthouse'] ?? ''); ?>" min="0" max="100">
                </div>
                <div class="form-group">
                    <label>Core Web Vitals (JSON)</label>
                    <textarea name="core_web_vitals" rows="3"><?php echo htmlspecialchars($tdata['core_web_vitals'] ?? ''); ?></textarea>
                </div>
            </div>
        </section>

        <!-- Secció 2: Indexació i Sitemap -->
        <section class="seo-section">
            <div class="section-header">
                <h2><i class="fas fa-sitemap"></i> Indexación y Sitemap</h2>
                <p class="section-description">Estado de indexación y sitemap</p>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Estado indexación</label>
                    <select name="estado_indexacion">
                        <option value="completa" <?php echo (isset($tdata['estado_indexacion']) && $tdata['estado_indexacion'] === 'completa') ? 'selected' : ''; ?>>Completa</option>
                        <option value="parcial" <?php echo (isset($tdata['estado_indexacion']) && $tdata['estado_indexacion'] === 'parcial') ? 'selected' : ''; ?>>Parcial</option>
                        <option value="limitada" <?php echo (isset($tdata['estado_indexacion']) && $tdata['estado_indexacion'] === 'limitada') ? 'selected' : ''; ?>>Limitada</option>
                        <option value="bloqueada" <?php echo (isset($tdata['estado_indexacion']) && $tdata['estado_indexacion'] === 'bloqueada') ? 'selected' : ''; ?>>Bloqueada</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Paginas indexadas</label>
                    <input type="number" name="paginas_indexadas" value="<?php echo htmlspecialchars($tdata['paginas_indexadas'] ?? ''); ?>" min="0">
                </div>
                <div class="form-group">
                    <label>Paginas no indexadas</label>
                    <input type="number" name="paginas_no_indexadas" value="<?php echo htmlspecialchars($tdata['paginas_no_indexadas'] ?? ''); ?>" min="0">
                </div>
                <div class="form-group">
                    <label>Último rastreo Google</label>
                    <input type="date" name="ultimo_rastreo_google" value="<?php echo isset($tdata['ultimo_rastreo_google']) ? date('Y-m-d', strtotime($tdata['ultimo_rastreo_google'])) : ''; ?>">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Frecuencia rastreo</label>
                    <select name="frecuencia_rastreo">
                        <option value="diaria" <?php echo (isset($tdata['frecuencia_rastreo']) && $tdata['frecuencia_rastreo'] === 'diaria') ? 'selected' : ''; ?>>Diaria</option>
                        <option value="semanal" <?php echo (isset($tdata['frecuencia_rastreo']) && $tdata['frecuencia_rastreo'] === 'semanal') ? 'selected' : ''; ?>>Semanal</option>
                        <option value="mensual" <?php echo (isset($tdata['frecuencia_rastreo']) && $tdata['frecuencia_rastreo'] === 'mensual') ? 'selected' : ''; ?>>Mensual</option>
                        <option value="poco_frecuente" <?php echo (isset($tdata['frecuencia_rastreo']) && $tdata['frecuencia_rastreo'] === 'poco_frecuente') ? 'selected' : ''; ?>>Poco frecuente</option>
                    </select>
                </div>
            </div>
        </section>

        <!-- Secció 3: HTTPS i SSL -->
        <section class="seo-section">
            <div class="section-header">
                <h2><i class="fas fa-lock"></i> HTTPS / SSL</h2>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>SSL activo</label>
                    <input type="checkbox" name="ssl_activo" value="1" <?php echo (!empty($tdata['ssl_activo'])) ? 'checked' : ''; ?>>
                </div>
                <div class="form-group">
                    <label>SSL válido</label>
                    <input type="checkbox" name="ssl_valido" value="1" <?php echo (!empty($tdata['ssl_valido'])) ? 'checked' : ''; ?>>
                </div>
                <div class="form-group">
                    <label>Tipo SSL</label>
                    <select name="ssl_tipo">
                        <option value="dv" <?php echo (isset($tdata['ssl_tipo']) && $tdata['ssl_tipo'] === 'dv') ? 'selected' : ''; ?>>DV</option>
                        <option value="ov" <?php echo (isset($tdata['ssl_tipo']) && $tdata['ssl_tipo'] === 'ov') ? 'selected' : ''; ?>>OV</option>
                        <option value="ev" <?php echo (isset($tdata['ssl_tipo']) && $tdata['ssl_tipo'] === 'ev') ? 'selected' : ''; ?>>EV</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Caducidad SSL</label>
                    <input type="date" name="ssl_caducidad" value="<?php echo isset($tdata['ssl_caducidad']) ? date('Y-m-d', strtotime($tdata['ssl_caducidad'])) : ''; ?>">
                </div>
            </div>
        </section>

        <!-- Secció final: puntuació, criticidad i notes -->
        <section class="seo-section">
            <div class="form-row">
                <div class="form-group">
                    <label>Puntuación SEO Técnico (0-100)</label>
                    <input type="number" name="puntuacion_seo_tecnico" min="0" max="100" value="<?php echo htmlspecialchars($tdata['puntuacion_seo_tecnico'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label>Criticidad issues</label>
                    <select name="criticidad_issues">
                        <option value="critica" <?php echo (isset($tdata['criticidad_issues']) && $tdata['criticidad_issues'] === 'critica') ? 'selected' : ''; ?>>Crítica</option>
                        <option value="alta" <?php echo (isset($tdata['criticidad_issues']) && $tdata['criticidad_issues'] === 'alta') ? 'selected' : ''; ?>>Alta</option>
                        <option value="media" <?php echo (isset($tdata['criticidad_issues']) && $tdata['criticidad_issues'] === 'media') ? 'selected' : ''; ?>>Media</option>
                        <option value="baja" <?php echo (isset($tdata['criticidad_issues']) && $tdata['criticidad_issues'] === 'baja') ? 'selected' : ''; ?>>Baja</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <button class="btn btn-primary" type="submit"><i class="fas fa-save"></i> Guardar Auditoría</button>
                    <a href="gseo.php?tab=technical" class="btn btn-secondary">Cancelar</a>
                </div>
            </div>
        </section>
    </form>
    <?php endif; ?>

</div>
