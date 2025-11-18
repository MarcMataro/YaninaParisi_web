<?php
// Página de FAQs - Español
if (session_status() === PHP_SESSION_NONE) session_start();
$_SESSION['language'] = 'es';
include '../includes/functions.php';
require_once __DIR__ . '/../classes/connexio.php';
require_once __DIR__ . '/../classes/faqs.php';

try {
	$conn = Connexio::getInstance()->getConnexio();
} catch (Exception $e) {
	die('Error de conexión: ' . $e->getMessage());
}

$faqModel = new Faq($conn);
$faqs = $faqModel->llistar(['activa' => true]);

// Agrupar per categoria
$grouped = [];
foreach ($faqs as $f) {
	$cat = $f['categoria'] ?? 'general';
	if (!isset($grouped[$cat])) $grouped[$cat] = [];
	$grouped[$cat][] = $f;
}

// Map categories a noms llegibles (espanyol)
$catNames = [
	'general' => 'General',
	'terapia' => 'Terapia',
	'tarifes' => 'Tarifas',
	'tecnica' => 'Técnica',
	'primera_visita' => 'Primera visita',
	'urgencies' => 'Urgencias'
];

// Preparar JSON-LD (FAQPage) per posar-lo al <head>
$faqSchema = [
	'@context' => 'https://schema.org',
	'@type' => 'FAQPage',
	'mainEntity' => []
];
foreach ($faqs as $f) {
	$q = trim(strip_tags($f['pregunta_es']));
	$a = trim(strip_tags($f['resposta_es']));
	if ($q === '' || $a === '') continue;
	$faqSchema['mainEntity'][] = [
		'@type' => 'Question',
		'name' => $q,
		'acceptedAnswer' => [
			'@type' => 'Answer',
			'text' => $a
		]
	];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width,initial-scale=1">
	<title>Preguntas frecuentes - Yanina Parisi</title>
	<meta name="description" content="Preguntas frecuentes sobre los servicios y funcionamiento de la consulta de Yanina Parisi.">
	<link rel="stylesheet" href="../css/estils.css">
	<link rel="stylesheet" href="../css/brands.css">
	<link rel="stylesheet" href="../css/contacte.css">
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@7.0.0/css/all.min.css">
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Libre+Baskerville:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
	<link rel="icon" type="image/png" sizes="32x32" href="../img/Logo32.png">
	<link rel="icon" type="image/png" sizes="16x16" href="../img/Logo16.png">
	<?php if (!empty($faqSchema['mainEntity'])): ?>
	<script type="application/ld+json">
	<?php echo json_encode($faqSchema, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT); ?>
	</script>
	<?php endif; ?>
	<style>
		.faq-section { padding: 40px 0; }
		.faq-list { max-width: 1000px; margin: 0 auto; display: grid; gap: 14px; }
		details.faq-item { background: #fff; border-radius: 10px; padding: 16px 18px; box-shadow: 0 6px 18px rgba(0,0,0,0.04); }
		details.faq-item summary { cursor: pointer; font-weight:700; font-size:1.02rem; list-style:none; }
		details.faq-item[open] { box-shadow: 0 10px 30px rgba(0,0,0,0.06); }
		.faq-body { padding-top:10px; color: #333; line-height:1.6; }
	</style>
</head>
<body>
	<?php include '_includes/navigation.php'; ?>

		<!-- Hero Section (reuse contact hero image for now) -->
		<section class="contact-hero">
			<img src="../img/IMG_2283.jpeg" alt="" class="contact-hero-img" aria-hidden="true">
			<div class="container">
				<div class="contact-hero-content">
					<h1>Preguntas frecuentes</h1>
					<p class="contact-hero-subtitle">Resuelve tus dudas sobre servicios, tarifas y cómo reservar una cita.</p>
				</div>
			</div>
		</section>

	<main>
		<?php
			// Breadcrumbs: Home > Preguntas frecuentes (ES)
			if (function_exists('render_breadcrumbs')) {
				render_breadcrumbs([
					['label' => t('nav_home'), 'url' => 'home.php'],
					['label' => t('nav_faq')]
				]);
			}
		?>
		<section class="faq-section">
			<div class="container">

				<div class="faq-list">
					<?php if (empty($faqs)): ?>
						<p>No hay preguntas frecuentes disponibles.</p>
					<?php else:
						// Render per categories
						foreach ($grouped as $cat => $items):
							$label = $catNames[$cat] ?? ucfirst($cat);
					?>
						<div class="faq-category">
							<h3><?php echo htmlspecialchars($label); ?></h3>
							<?php foreach ($items as $f): ?>
								<details class="faq-item" id="faq-<?php echo $f['id_faq']; ?>">
									<summary><?php echo htmlspecialchars($f['pregunta_es']); ?></summary>
									<div class="faq-body"><?php echo $f['resposta_es']; ?></div>
								</details>
							<?php endforeach; ?>
						</div>
					<?php endforeach; endif; ?>
				</div>
			</div>
		</section>
	</main>

	<?php include '_includes/footer.php'; ?>
        <script>
        // Script per a la navegació suau
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });

        // Script per l'efecte scroll de la navegació
        window.addEventListener('scroll', function() {
            const header = document.querySelector('header');
            if (window.scrollY > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });

        // Script per al selector d'idioma

        
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.lang-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    // Obtenir l'idioma del data attribute
                    const lang = this.getAttribute('data-lang');
                    console.log('Botó clickat, idioma:', lang);
                    
                    // Eliminar classe active de tots els botons (tant desktop com mòbil)
                    document.querySelectorAll('.lang-btn').forEach(b => b.classList.remove('active'));
                    // Afegir classe active a tots els botons del mateix idioma
                    document.querySelectorAll(`.lang-btn[data-lang="${lang}"]`).forEach(b => b.classList.add('active'));
                    
                    // Tancar menú mòbil si està obert
                    const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
                    const navMenu = document.querySelector('.nav-menu ul');
                    if (mobileMenuToggle && navMenu) {
                        mobileMenuToggle.classList.remove('active');
                        navMenu.classList.remove('show');
                    }
                    
                    // Canviar idioma
                    changeLanguage(lang);
                });
            });

            // Funcionalitat del menú hamburguesa
            const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
            const navMenu = document.querySelector('.nav-menu ul');

            if (mobileMenuToggle && navMenu) {
                mobileMenuToggle.addEventListener('click', function() {
                    this.classList.toggle('active');
                    navMenu.classList.toggle('show');
                });

                // Tancar menú quan es clica un enllaç
                document.querySelectorAll('.nav-menu ul li a').forEach(link => {
                    link.addEventListener('click', function() {
                        mobileMenuToggle.classList.remove('active');
                        navMenu.classList.remove('show');
                    });
                });

                // Tancar menú quan es clica fora
                document.addEventListener('click', function(e) {
                    if (!mobileMenuToggle.contains(e.target) && !navMenu.contains(e.target)) {
                        mobileMenuToggle.classList.remove('active');
                        navMenu.classList.remove('show');
                    }
                });
            }
        });
    </script>
    <script src="../js/language.js"></script>
</body>
</html>
