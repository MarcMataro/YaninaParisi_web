<?php
/**
 * Gestió del Blog - Panel de Control
 * 
 * Gestió d'articles del blog amb dades de prova
 * 
 * @author Marc Mataró
 * @version 1.0.0
 */

session_start();

// Verificar autenticación
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

// Dades de prova per als articles del blog
$articles_prova = [
    [
        'id' => 1,
        'titol' => 'La importancia de la salud mental en el día a día',
        'resum' => 'Descubre cómo cuidar tu salud mental con pequeños hábitos cotidianos que marcan la diferencia.',
        'contingut' => 'La salud mental es fundamental para nuestro bienestar general. En este artículo exploraremos...',
        'categoria' => 'Salud Mental',
        'estat' => 'Publicado',
        'data_creacio' => '2024-03-15 10:30:00',
        'data_publicacio' => '2024-03-15 12:00:00',
        'autor' => 'Yanina Parisi',
        'visualitzacions' => 245,
        'comentaris' => 12,
        'etiquetes' => 'salud mental, bienestar, consejos'
    ],
    [
        'id' => 2,
        'titol' => 'Técnicas de respiración para reducir la ansiedad',
        'resum' => 'Aprende técnicas simples de respiración que puedes utilizar en cualquier momento para calmar la ansiedad.',
        'contingut' => 'La ansiedad es una respuesta natural del cuerpo, pero cuando se vuelve abrumadora...',
        'categoria' => 'Ansiedad',
        'estat' => 'Publicado',
        'data_creacio' => '2024-03-10 14:20:00',
        'data_publicacio' => '2024-03-12 09:00:00',
        'autor' => 'Yanina Parisi',
        'visualitzacions' => 189,
        'comentaris' => 8,
        'etiquetes' => 'ansiedad, respiración, técnicas'
    ],
    [
        'id' => 3,
        'titol' => 'Cómo mejorar la comunicación en pareja',
        'resum' => 'Estrategias efectivas para mejorar la comunicación con tu pareja y fortalecer la relación.',
        'contingut' => 'La comunicación es la base de cualquier relación sana. En este artículo veremos...',
        'categoria' => 'Relaciones',
        'estat' => 'Borrador',
        'data_creacio' => '2024-03-08 16:45:00',
        'data_publicacio' => null,
        'autor' => 'Yanina Parisi',
        'visualitzacions' => 0,
        'comentaris' => 0,
        'etiquetes' => 'pareja, comunicación, relaciones'
    ],
    [
        'id' => 4,
        'titol' => 'Los beneficios de la terapia psicológica',
        'resum' => 'Desmitifiquemos la terapia psicológica y exploremos sus múltiples beneficios para el bienestar personal.',
        'contingut' => 'Muchas personas tienen dudas sobre la terapia psicológica. En este artículo...',
        'categoria' => 'Terapia',
        'estat' => 'Revisar',
        'data_creacio' => '2024-03-05 11:15:00',
        'data_publicacio' => null,
        'autor' => 'Yanina Parisi',
        'visualitzacions' => 0,
        'comentaris' => 0,
        'etiquetes' => 'terapia, psicología, beneficios'
    ],
    [
        'id' => 5,
        'titol' => 'Gestionar el estrés laboral de manera efectiva',
        'resum' => 'Consejos prácticos para gestionar el estrés laboral y mantener un equilibrio saludable entre vida y trabajo.',
        'contingut' => 'El estrés laboral es un problema cada vez más común. Aprende cómo...',
        'categoria' => 'Estrés',
        'estat' => 'Publicado',
        'data_creacio' => '2024-02-28 09:30:00',
        'data_publicacio' => '2024-03-01 08:00:00',
        'autor' => 'Yanina Parisi',
        'visualitzacions' => 312,
        'comentaris' => 15,
        'etiquetes' => 'estrés, trabajo, equilibrio'
    ]
];

// Estadístiques de prova
$stats = [
    'total_articles' => count($articles_prova),
    'publicats' => count(array_filter($articles_prova, fn($a) => $a['estat'] === 'Publicado')),
    'esborranys' => count(array_filter($articles_prova, fn($a) => $a['estat'] === 'Borrador')),
    'total_visualitzacions' => array_sum(array_column($articles_prova, 'visualitzacions')),
    'total_comentaris' => array_sum(array_column($articles_prova, 'comentaris'))
];

$categories = ['Todas', 'Salud Mental', 'Ansiedad', 'Relaciones', 'Terapia', 'Estrés'];
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
    <link rel="stylesheet" href="css/gblog.css">
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

        <!-- Content Wrapper -->
        <div class="content-wrapper">
            <!-- Stats Cards -->
            <section class="stats-section">
                <div class="stat-card">
                    <div class="stat-icon articles">
                        <i class="fas fa-newspaper"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $stats['total_articles']; ?></h3>
                        <p>Artículos Totales</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon published">
                        <i class="fas fa-eye"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $stats['publicats']; ?></h3>
                        <p>Publicados</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon drafts">
                        <i class="fas fa-edit"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $stats['esborranys']; ?></h3>
                        <p>Borradores</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon views">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo number_format($stats['total_visualitzacions']); ?></h3>
                        <p>Visualitzacions</p>
                    </div>
                </div>
            </section>

            <!-- Actions and Filters -->
            <section class="blog-actions">
                <div class="actions-left">
                    <button class="btn btn-primary" onclick="afegirArticle()">
                        <i class="fas fa-plus"></i>
                        Nuevo Artículo
                    </button>
                    <button class="btn btn-outline" onclick="gestionarCategories()">
                        <i class="fas fa-tags"></i>
                        Categorías
                    </button>
                    <button class="btn btn-outline" onclick="gestionarEtiquetes()">
                        <i class="fas fa-hashtag"></i>
                        Etiquetas
                    </button>
                </div>
                <div class="actions-right">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" placeholder="Buscar artículos..." id="searchInput">
                    </div>
                    <select class="filter-select" id="categoryFilter">
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat; ?>"><?php echo $cat; ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select class="filter-select" id="statusFilter">
                        <?php foreach ($estats as $estat): ?>
                            <option value="<?php echo $estat; ?>"><?php echo $estat; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </section>

            <!-- Articles Table -->
            <section class="card articles-table-card">
                <div class="card-header">
                    <h2><i class="fas fa-list"></i> Artículos del Blog</h2>
                </div>
                <div class="table-container">
                    <table class="articles-table">
                        <thead>
                            <tr>
                                <th>Título</th>
                                <th>Categoría</th>
                                <th>Estado</th>
                                <th>Fecha</th>
                                <th>Visualizaciones</th>
                                <th>Comentarios</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($articles_prova as $article): ?>
                            <tr>
                                <td class="article-title">
                                    <div class="title-content">
                                        <h4><?php echo htmlspecialchars($article['titol']); ?></h4>
                                        <p class="article-summary"><?php echo htmlspecialchars($article['resum']); ?></p>
                                        <div class="article-tags">
                                            <?php 
                                            $etiquetes = explode(', ', $article['etiquetes']);
                                            foreach ($etiquetes as $etiqueta): 
                                            ?>
                                                <span class="tag"><?php echo htmlspecialchars($etiqueta); ?></span>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="category-badge"><?php echo htmlspecialchars($article['categoria']); ?></span>
                                </td>
                                <td>
                                    <span class="status-badge status-<?php echo strtolower($article['estat']); ?>">
                                        <?php echo htmlspecialchars($article['estat']); ?>
                                    </span>
                                </td>
                                <td class="date-cell">
                                    <div class="date-info">
                                        <strong>Creado:</strong> <?php echo date('d/m/Y', strtotime($article['data_creacio'])); ?><br>
                                        <?php if ($article['data_publicacio']): ?>
                                            <strong>Publicado:</strong> <?php echo date('d/m/Y', strtotime($article['data_publicacio'])); ?>
                                        <?php else: ?>
                                            <em>No publicado</em>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="stats-cell">
                                    <i class="fas fa-eye"></i>
                                    <?php echo number_format($article['visualitzacions']); ?>
                                </td>
                                <td class="stats-cell">
                                    <i class="fas fa-comments"></i>
                                    <?php echo $article['comentaris']; ?>
                                </td>
                                <td class="actions-cell">
                                    <div class="action-buttons">
                                        <button class="btn-icon btn-edit" onclick="editarArticle(<?php echo $article['id']; ?>)" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn-icon btn-view" onclick="visualitzarArticle(<?php echo $article['id']; ?>)" title="Visualizar">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <?php if ($article['estat'] === 'Publicado'): ?>
                                            <button class="btn-icon btn-unpublish" onclick="despublicarArticle(<?php echo $article['id']; ?>)" title="Despublicar">
                                                <i class="fas fa-eye-slash"></i>
                                            </button>
                                        <?php else: ?>
                                            <button class="btn-icon btn-publish" onclick="publicarArticle(<?php echo $article['id']; ?>)" title="Publicar">
                                                <i class="fas fa-globe"></i>
                                            </button>
                                        <?php endif; ?>
                                        <button class="btn-icon btn-delete" onclick="eliminarArticle(<?php echo $article['id']; ?>)" title="Eliminar">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>

    <!-- Modal per gestionar categories -->
    <div id="categoriesModal" class="modal">
        <div class="modal-content modal-medium">
            <div class="modal-header">
                <h3>Gestión de Categorías</h3>
                <button class="modal-close" onclick="tancarModalCategories()">&times;</button>
            </div>
            <div class="modal-body">
                <div class="seo-section">
                    <div class="form-group">
                        <label for="newCategory">Nueva Categoría</label>
                        <div class="input-group">
                            <input type="text" id="newCategory" placeholder="Nombre de la categoría">
                            <button class="btn btn-primary" onclick="afegirCategoria()">
                                <i class="fas fa-plus"></i>
                                Añadir
                            </button>
                        </div>
                    </div>
                    <div class="categories-list">
                        <h4>Categorías Existentes</h4>
                        <div class="categories-grid" id="categoriesGrid">
                            <!-- Categories dinàmiques aquí -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal per gestionar etiquetes -->
    <div id="etiquetesModal" class="modal">
        <div class="modal-content modal-medium">
            <div class="modal-header">
                <h3>Gestión de Etiquetas</h3>
                <button class="modal-close" onclick="tancarModalEtiquetes()">&times;</button>
            </div>
            <div class="modal-body">
                <div class="seo-section">
                    <div class="form-group">
                        <label for="newTag">Nueva Etiqueta</label>
                        <div class="input-group">
                            <input type="text" id="newTag" placeholder="Nombre de la etiqueta">
                            <button class="btn btn-primary" onclick="afegirEtiqueta()">
                                <i class="fas fa-plus"></i>
                                Añadir
                            </button>
                        </div>
                    </div>
                    <div class="tags-section">
                        <h4>Etiquetas Existentes</h4>
                        <div class="tags-cloud" id="tagsCloud">
                            <!-- Etiquetes dinàmiques aquí -->
                        </div>
                    </div>
                    <div class="seo-info">
                        <h4><i class="fas fa-search"></i> Optimización SEO</h4>
                        <div class="seo-tips">
                            <p><strong>Consejos para etiquetas SEO:</strong></p>
                            <ul>
                                <li>Usa entre 3-5 etiquetas por artículo</li>
                                <li>Incluye palabras clave relevantes</li>
                                <li>Evita etiquetas muy genéricas</li>
                                <li>Mantén consistencia en la nomenclatura</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal per afegir/editar article -->
    <div id="articleModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalTitle">Nuevo Artículo</h3>
                <button class="modal-close" onclick="tancarModal()">&times;</button>
            </div>
            <div class="modal-body">
                <form id="articleForm">
                    <div class="form-group">
                        <label for="articleTitle">Título</label>
                        <input type="text" id="articleTitle" placeholder="Título del artículo">
                    </div>
                    <div class="form-group">
                        <label for="articleSummary">Resumen</label>
                        <textarea id="articleSummary" placeholder="Resumen breve del artículo" rows="3"></textarea>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="articleCategory">Categoría</label>
                            <select id="articleCategory">
                                <option value="Salud Mental">Salud Mental</option>
                                <option value="Ansiedad">Ansiedad</option>
                                <option value="Relaciones">Relaciones</option>
                                <option value="Terapia">Terapia</option>
                                <option value="Estrés">Estrés</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="articleStatus">Estado</label>
                            <select id="articleStatus">
                                <option value="Borrador">Borrador</option>
                                <option value="Revisar">Revisar</option>
                                <option value="Publicado">Publicado</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="articleTags">Etiquetas</label>
                        <div class="tags-input-container">
                            <input type="text" id="articleTags" placeholder="Escribe una etiqueta y presiona Enter">
                            <div class="selected-tags" id="selectedTags"></div>
                        </div>
                        <div class="tags-suggestions" id="tagsSuggestions"></div>
                        <small class="form-help">Presiona Enter para añadir etiquetas. Máximo 8 etiquetas por artículo.</small>
                    </div>
                    <div class="form-group seo-group">
                        <label for="articleContent">Contenido</label>
                        <div class="content-toolbar">
                            <button type="button" class="btn-tool" onclick="insertarTexto('**', '**')" title="Negrita">
                                <i class="fas fa-bold"></i>
                            </button>
                            <button type="button" class="btn-tool" onclick="insertarTexto('*', '*')" title="Cursiva">
                                <i class="fas fa-italic"></i>
                            </button>
                            <button type="button" class="btn-tool" onclick="insertarTexto('## ', '')" title="Subtítulo">
                                <i class="fas fa-heading"></i>
                            </button>
                            <button type="button" class="btn-tool" onclick="insertarTexto('[', '](url)')" title="Enlace">
                                <i class="fas fa-link"></i>
                            </button>
                        </div>
                        <textarea id="articleContent" placeholder="Contenido del artículo (compatible con Markdown)" rows="12"></textarea>
                        <div class="content-info">
                            <span id="wordCount">0 palabras</span>
                            <span id="readTime">0 min lectura</span>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="tancarModal()">
                    Cancelar
                </button>
                <button type="button" class="btn btn-primary" onclick="desarArticle()">
                    Guardar Artículo
                </button>
            </div>
        </div>
    </div>

    <script src="js/gblog.js"></script>
    <script src="js/dashboard.js"></script>
</body>
</html>