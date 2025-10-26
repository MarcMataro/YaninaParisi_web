<?php
/**
 * Classe SEO_OnPage
 * 
 * Gestiona el SEO específic de cada pàgina del lloc web, incloent meta tags,
 * Open Graph, Twitter Cards, structured data, i mètriques SEO per pàgina.
 * Aquesta classe proporciona una interfície completa per treballar amb la taula
 * `seo_onpage_paginas` que emmagatzema la configuració SEO individual de cada URL.
 * 
 * Funcionalitats principals:
 * - Gestió bilingüe (català/espanyol) de tots els camps SEO
 * - Generació automàtica de meta tags, Open Graph i Twitter Cards
 * - Càlcul de mètriques SEO (word count, keyword density, SEO score)
 * - Gestió de breadcrumbs i jerarquia de pàgines
 * - Generació de structured data (Schema.org JSON-LD)
 * - Suport per diferents tipus de pàgines (home, blog, servicios, etc.)
 * 
 * @package     YaninaParisi
 * @subpackage  Classes
 * @category    SEO
 * @author      Marc Mataró
 * @version     1.0.0
 * @since       2025-10-07
 */

require_once __DIR__ . '/connexio.php';

class SEO_OnPage {
    // ...existing code...
    /**
     * @var Connexio Instància de la connexió a la base de dades
     */
    private $conn;

    /**
     * @var PDO Objecte PDO per a consultes
     */
    private $pdo;

    /**
     * @var int|null ID de la pàgina
     */
    private $id_pagina;

    /**
     * @var string URL relativa en català (ex: /terapia-ansietat)
     */
    private $url_relativa_ca;

    /**
     * @var string URL relativa en espanyol (ex: /terapia-ansiedad)
     */
    private $url_relativa_es;

    /**
     * @var string Títol visible de la pàgina
     */
    private $titulo_pagina;

    /**
     * @var string Tipus de pàgina (home, sobre-mi, servicios, blog, articulo, contacto, legal, landing)
     */
    private $tipo_pagina;

    /**
     * @var string Meta title en català (màx 60 caràcters)
     */
    private $title_ca;

    /**
     * @var string Meta description en català (màx 160 caràcters)
     */
    private $meta_description_ca;

    /**
     * @var string H1 principal en català (màx 100 caràcters)
     */
    private $h1_ca;

    /**
     * @var string|null Contingut principal de la pàgina en català
     */
    private $contenido_principal_ca;

    /**
     * @var string Meta title en espanyol (màx 60 caràcters)
     */
    private $title_es;


    // ========== GETTERS PERSONALITZATS PER A LA TAULA =============
    /**
     * Obté la URL relativa en català
     * @return string
     */
    public function getUrlRelativaCa() {
        return $this->url_relativa_ca;
    }

    /**
     * Obté la URL relativa en espanyol
     * @return string
     */
    public function getUrlRelativaEs() {
        return $this->url_relativa_es;
    }
    
    /**
     * @var string Meta description en espanyol (màx 160 caràcters)
     */
    private $meta_description_es;
    
    /**
     * @var string H1 principal en espanyol (màx 100 caràcters)
     */
    private $h1_es;
    
    /**
     * @var string|null Contingut principal de la pàgina en espanyol
     */
    private $contenido_principal_es;
    
    // ============================================
    // 4. ESTRUCTURA I ORGANITZACIÓ
    // ============================================
    
    /**
     * @var string|null JSON amb l'estructura de breadcrumbs
     */
    private $breadcrumb_json;
    
    /**
     * @var string|null Slug de la pàgina en català (URL-friendly)
     */
    private $slug_ca;
    
    /**
     * @var string|null Slug de la pàgina en espanyol (URL-friendly)
     */
    private $slug_es;
    
    /**
     * @var int|null ID de la pàgina pare (per jerarquia de pàgines)
     */
    private $parent_id;
    
    // ============================================
    // 5. SEO TÈCNIC ESPECÍFIC
    // ============================================
    
    /**
     * @var string Directiva meta robots (ex: "index, follow")
     */
    private $meta_robots;
    
    /**
     * @var string|null URL canònica específica en català (si és diferent de la URL relativa)
     */
    private $canonical_url_ca;

    /**
     * @var string|null URL canònica específica en espanyol (si és diferent de la URL relativa)
     */
    private $canonical_url_es;
    
    /**
     * @var string Prioritat en sitemap.xml (1.0, 0.8, 0.6, 0.4, 0.2)
     */
    private $priority;
    
    /**
     * @var string Freqüència de canvi en sitemap.xml
     */
    private $changefreq;
    
    // ============================================
    // 6. SEO AVANÇAT
    // ============================================
    
    /**
     * @var string|null Paraula clau principal en català
     */
    private $focus_keyword_ca;
    
    /**
     * @var string|null Paraula clau principal en espanyol
     */
    private $focus_keyword_es;
    
    /**
     * @var string|null Paraules clau secundàries en català (separades per comes)
     */
    private $keywords_secundarias_ca;
    
    /**
     * @var string|null Paraules clau secundàries en espanyol (separades per comes)
     */
    private $keywords_secundarias_es;
    
    /**
     * @var string|null JSON amb structured data específic de la pàgina
     */
    private $schema_json;
    
    // ============================================
    // 7. OPEN GRAPH ESPECÍFIC
    // ============================================
    
    /**
     * @var string|null Títol d'Open Graph en català
     */
    private $og_title_ca;
    
    /**
     * @var string|null Títol d'Open Graph en espanyol
     */
    private $og_title_es;
    
    /**
     * @var string|null Descripció d'Open Graph en català
     */
    private $og_description_ca;
    
    /**
     * @var string|null Descripció d'Open Graph en espanyol
     */
    private $og_description_es;
    
    /**
     * @var string|null URL de la imatge d'Open Graph específica de la pàgina
     */
    private $og_image;
    
    // ============================================
    // 8. TWITTER CARD ESPECÍFIC
    // ============================================
    
    /**
     * @var string|null Títol de Twitter Card en català
     */
    private $twitter_title_ca;
    
    /**
     * @var string|null Títol de Twitter Card en espanyol
     */
    private $twitter_title_es;
    
    /**
     * @var string|null Descripció de Twitter Card en català
     */
    private $twitter_description_ca;
    
    /**
     * @var string|null Descripció de Twitter Card en espanyol
     */
    private $twitter_description_es;
    
    /**
     * @var string|null URL de la imatge de Twitter Card
     */
    private $twitter_image;
    
    // ============================================
    // 9. IMATGES SEO
    // ============================================
    
    /**
     * @var string|null URL de la imatge destacada de la pàgina
     */
    private $featured_image;
    
    /**
     * @var string|null Text ALT de la imatge en català
     */
    private $alt_image_ca;
    
    /**
     * @var string|null Text ALT de la imatge en espanyol
     */
    private $alt_image_es;
    
    /**
     * @var string|null Caption/peu de foto en català
     */
    private $image_caption_ca;
    
    /**
     * @var string|null Caption/peu de foto en espanyol
     */
    private $image_caption_es;
    
    // ============================================
    // 10. MÈTRIQUES I CONTROL
    // ============================================
    
    /**
     * @var int Puntuació SEO de 0 a 100
     */
    private $seo_score;
    
    /**
     * @var int Recompte de paraules del contingut en català
     */
    private $word_count_ca;
    
    /**
     * @var int Recompte de paraules del contingut en espanyol
     */
    private $word_count_es;
    
    /**
     * @var float Densitat de la paraula clau en català (%)
     */
    private $densidad_keyword_ca;
    
    /**
     * @var float Densitat de la paraula clau en espanyol (%)
     */
    private $densidad_keyword_es;
    
    // ============================================
    // 11. ESTAT I TEMPORALITAT
    // ============================================
    
    /**
     * @var bool Indica si la pàgina està activa
     */
    private $activa;
    
    /**
     * @var string|null Data de publicació de la pàgina
     */
    private $fecha_publicacion;
    
    /**
     * @var string|null Data de l'última actualització del contingut
     */
    private $fecha_ultima_actualizacion;
    
    /**
     * @var string Data de creació del registre
     */
    private $fecha_creacion;
    
    /**
     * @var string Data de la última modificació del registre
     */
    private $fecha_modificacion;
    
    
    // ============================================
    // CONSTRUCTOR I DESTRUCTOR
    // ============================================
    
    /**
     * Constructor de la classe SEO_OnPage
     * 
     * Inicialitza la connexió a la base de dades i, si es proporciona un ID,
     * carrega les dades SEO de la pàgina especificada.
     * 
     * @param int|null $id_pagina ID de la pàgina a carregar
     * @throws Exception Si hi ha error de connexió a la base de dades
     */
    public function __construct($id_pagina = null) {
        try {
            $this->conn = Connexio::getInstance();
            $this->pdo = $this->conn->getConnexio();
            
            if ($id_pagina !== null) {
                $this->id_pagina = $id_pagina;
                $this->carregarDades();
            }
        } catch (Exception $e) {
            throw new Exception("Error al inicialitzar SEO_OnPage: " . $e->getMessage());
        }
    }
    
    /**
     * Destructor de la classe
     * 
     * La connexió es gestiona automàticament pel Singleton de Connexio.
     */
    public function __destruct() {
        // La connexió Singleton es tanca automàticament
    }
    
    
    // ============================================
    // MÈTODES DE CÀRREGA DE DADES
    // ============================================
    
    /**
     * Carrega les dades SEO d'una pàgina des de la base de dades
     * 
     * Recupera tots els camps de la taula seo_onpage_paginas per l'ID especificat
     * i els assigna a les propietats privades de l'objecte.
     * 
     * @return bool True si les dades s'han carregat correctament, False si no existeix
     * @throws Exception Si hi ha error en la consulta SQL
     */
    private function carregarDades() {
        try {
            $sql = "SELECT * FROM seo_onpage_paginas WHERE id_pagina = :id_pagina LIMIT 1";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':id_pagina', $this->id_pagina, PDO::PARAM_INT);
            $stmt->execute();
            
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($row) {
                // 1. Identificació i URL
                $this->url_relativa_ca = $row['url_relativa_ca'] ?? '';
                $this->url_relativa_es = $row['url_relativa_es'] ?? '';
                $this->titulo_pagina = $row['titulo_pagina'];
                $this->tipo_pagina = $row['tipo_pagina'];
                
                // 2. SEO Bàsic Català
                $this->title_ca = $row['title_ca'];
                $this->meta_description_ca = $row['meta_description_ca'];
                $this->h1_ca = $row['h1_ca'];
                $this->contenido_principal_ca = $row['contenido_principal_ca'];
                
                // 3. SEO Bàsic Castellà
                $this->title_es = $row['title_es'];
                $this->meta_description_es = $row['meta_description_es'];
                $this->h1_es = $row['h1_es'];
                $this->contenido_principal_es = $row['contenido_principal_es'];
                
                // 4. Estructura i Organització
                $this->breadcrumb_json = $row['breadcrumb_json'];
                $this->slug_ca = $row['slug_ca'];
                $this->slug_es = $row['slug_es'];
                $this->parent_id = $row['parent_id'];
                
                // 5. SEO Tècnic Específic
                $this->meta_robots = $row['meta_robots'];
                // La base de dades ara guarda canonicals per idioma
                $this->canonical_url_ca = $row['canonical_url_ca'] ?? null;
                $this->canonical_url_es = $row['canonical_url_es'] ?? null;
                $this->priority = $row['priority'];
                $this->changefreq = $row['changefreq'];
                
                // 6. SEO Avançat
                $this->focus_keyword_ca = $row['focus_keyword_ca'];
                $this->focus_keyword_es = $row['focus_keyword_es'];
                $this->keywords_secundarias_ca = $row['keywords_secundarias_ca'];
                $this->keywords_secundarias_es = $row['keywords_secundarias_es'];
                $this->schema_json = $row['schema_json'];
                
                // 7. Open Graph Específic
                $this->og_title_ca = $row['og_title_ca'];
                $this->og_title_es = $row['og_title_es'];
                $this->og_description_ca = $row['og_description_ca'];
                $this->og_description_es = $row['og_description_es'];
                $this->og_image = $row['og_image'];
                
                // 8. Twitter Card Específic
                $this->twitter_title_ca = $row['twitter_title_ca'];
                $this->twitter_title_es = $row['twitter_title_es'];
                $this->twitter_description_ca = $row['twitter_description_ca'];
                $this->twitter_description_es = $row['twitter_description_es'];
                $this->twitter_image = $row['twitter_image'];
                
                // 9. Imatges SEO
                $this->featured_image = $row['featured_image'];
                $this->alt_image_ca = $row['alt_image_ca'];
                $this->alt_image_es = $row['alt_image_es'];
                $this->image_caption_ca = $row['image_caption_ca'];
                $this->image_caption_es = $row['image_caption_es'];
                
                // 10. Mètriques i Control
                $this->seo_score = $row['seo_score'];
                $this->word_count_ca = $row['word_count_ca'];
                $this->word_count_es = $row['word_count_es'];
                $this->densidad_keyword_ca = $row['densidad_keyword_ca'];
                $this->densidad_keyword_es = $row['densidad_keyword_es'];
                
                // 11. Estat i Temporalitat
                $this->activa = $row['activa'];
                $this->fecha_publicacion = $row['fecha_publicacion'];
                $this->fecha_ultima_actualizacion = $row['fecha_ultima_actualizacion'];
                $this->fecha_creacion = $row['fecha_creacion'];
                $this->fecha_modificacion = $row['fecha_modificacion'];
                
                return true;
            }
            
            return false;
            
        } catch (PDOException $e) {
            throw new Exception("Error al carregar dades de la pàgina: " . $e->getMessage());
        }
    }
    
    /**
     * Carrega una pàgina per la seva URL relativa
     * 
     * Mètode estàtic que busca una pàgina per la seva URL i retorna un objecte SEO_OnPage.
     * 
     * @param string $url_relativa URL relativa de la pàgina (ex: /terapia-ansietat)
     * @return SEO_OnPage|null Objecte amb les dades de la pàgina o null si no existeix
     */
    public static function carregarPerUrl($url_relativa, $lang = 'ca') {
        try {
            $conn = Connexio::getInstance();
            $pdo = $conn->getConnexio();
            $field = ($lang === 'es') ? 'url_relativa_es' : 'url_relativa_ca';
            $sql = "SELECT id_pagina FROM seo_onpage_paginas WHERE $field = :url LIMIT 1";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':url', $url_relativa, PDO::PARAM_STR);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                return new self($row['id_pagina']);
            }
            return null;
            
        } catch (Exception $e) {
            error_log("Error en carregarPerUrl: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Carrega una pàgina pel seu slug i idioma
     * 
     * @param string $slug Slug de la pàgina
     * @param string $lang Idioma (ca|es). Per defecte: 'ca'
     * @return SEO_OnPage|null Objecte amb les dades de la pàgina o null si no existeix
     */
    public static function carregarPerSlug($slug, $lang = 'ca') {
        try {
            $conn = Connexio::getInstance();
            $pdo = $conn->getConnexio();
            
            $campo = ($lang === 'es') ? 'slug_es' : 'slug_ca';
            $sql = "SELECT id_pagina FROM seo_onpage_paginas WHERE $campo = :slug LIMIT 1";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':slug', $slug, PDO::PARAM_STR);
            $stmt->execute();
            
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($row) {
                return new self($row['id_pagina']);
            }
            
            return null;
            
        } catch (Exception $e) {
            error_log("Error en carregarPerSlug: " . $e->getMessage());
            return null;
        }
    }
    
    
    // ============================================
    // GETTERS - IDENTIFICACIÓ I URL
    // ============================================
    
    /**
     * Obté l'ID de la pàgina
     * 
     * @return int|null ID de la pàgina
     */
    public function getIdPagina() {
        return $this->id_pagina;
    }
    
    /**
     * Obté la URL relativa de la pàgina
     * 
     * @return string URL relativa (ex: /terapia-ansietat)
     */
    public function getUrlRelativa() {
    // Retorna la URL relativa segons l'idioma
    $lang = isset($_SESSION['language']) ? $_SESSION['language'] : 'ca';
    return ($lang === 'es') ? $this->url_relativa_es : $this->url_relativa_ca;
    }
    
    /**
     * Obté el títol visible de la pàgina
     * 
     * @return string Títol de la pàgina
     */
    public function getTituloPagina() {
        return $this->titulo_pagina;
    }
    
    /**
     * Obté el tipus de pàgina
     * 
     * @return string Tipus (home, sobre-mi, servicios, blog, articulo, contacto, legal, landing)
     */
    public function getTipoPagina() {
        return $this->tipo_pagina;
    }
    
    
    // ============================================
    // GETTERS - SEO BÀSIC
    // ============================================
    
    /**
     * Obté el meta title segons l'idioma
     * 
     * @param string $lang Idioma (ca|es). Per defecte: 'ca'
     * @return string Meta title
     */
    public function getTitle($lang = 'ca') {
        return ($lang === 'es') ? $this->title_es : $this->title_ca;
    }
    
    /**
     * Obté la meta description segons l'idioma
     * 
     * @param string $lang Idioma (ca|es). Per defecte: 'ca'
     * @return string Meta description
     */
    public function getMetaDescription($lang = 'ca') {
        return ($lang === 'es') ? $this->meta_description_es : $this->meta_description_ca;
    }
    
    /**
     * Obté el H1 principal segons l'idioma
     * 
     * @param string $lang Idioma (ca|es). Per defecte: 'ca'
     * @return string H1
     */
    public function getH1($lang = 'ca') {
        return ($lang === 'es') ? $this->h1_es : $this->h1_ca;
    }
    
    /**
     * Obté el contingut principal segons l'idioma
     * 
     * @param string $lang Idioma (ca|es). Per defecte: 'ca'
     * @return string|null Contingut principal
     */
    public function getContenido($lang = 'ca') {
        return ($lang === 'es') ? $this->contenido_principal_es : $this->contenido_principal_ca;
    }
    
    
    // ============================================
    // GETTERS - ESTRUCTURA
    // ============================================
    
    /**
     * Obté els breadcrumbs decodificats
     * 
     * @return array|null Array amb l'estructura de breadcrumbs o null
     */
    public function getBreadcrumbs() {
        return $this->breadcrumb_json ? json_decode($this->breadcrumb_json, true) : null;
    }
    
    /**
     * Obté el slug segons l'idioma
     * 
     * @param string $lang Idioma (ca|es). Per defecte: 'ca'
     * @return string|null Slug
     */
    public function getSlug($lang = 'ca') {
        return ($lang === 'es') ? $this->slug_es : $this->slug_ca;
    }
    
    /**
     * Obté l'ID de la pàgina pare
     * 
     * @return int|null ID de la pàgina pare
     */
    public function getParentId() {
        return $this->parent_id;
    }
    
    /**
     * Obté la pàgina pare com objecte SEO_OnPage
     * 
     * @return SEO_OnPage|null Objecte de la pàgina pare o null
     */
    public function getParent() {
        if ($this->parent_id) {
            return new self($this->parent_id);
        }
        return null;
    }
    
    
    // ============================================
    // GETTERS - SEO TÈCNIC
    // ============================================
    
    /**
     * Obté la directiva meta robots
     * 
     * @return string Directiva robots (ex: "index, follow")
     */
    public function getMetaRobots() {
        return $this->meta_robots;
    }
    
    /**
     * Obté la URL canònica segons l'idioma
     *
     * @param string $lang Idioma (ca|es). Per defecte: 'ca'
     * @return string|null URL canònica o null
     */
    public function getCanonicalUrl($lang = 'ca') {
        return ($lang === 'es') ? $this->canonical_url_es : $this->canonical_url_ca;
    }
    
    /**
     * Obté la prioritat per sitemap.xml
     * 
     * @return string Prioritat (1.0, 0.8, 0.6, 0.4, 0.2)
     */
    public function getPriority() {
        return $this->priority;
    }
    
    /**
     * Obté la freqüència de canvi per sitemap.xml
     * 
     * @return string Freqüència (always, hourly, daily, weekly, monthly, yearly, never)
     */
    public function getChangefreq() {
        return $this->changefreq;
    }
    
    
    // ============================================
    // GETTERS - SEO AVANÇAT
    // ============================================
    
    /**
     * Obté la paraula clau principal segons l'idioma
     * 
     * @param string $lang Idioma (ca|es). Per defecte: 'ca'
     * @return string|null Focus keyword
     */
    public function getFocusKeyword($lang = 'ca') {
        return ($lang === 'es') ? $this->focus_keyword_es : $this->focus_keyword_ca;
    }
    
    /**
     * Obté les paraules clau secundàries segons l'idioma
     * 
     * @param string $lang Idioma (ca|es). Per defecte: 'ca'
     * @return array Array de keywords secundàries
     */
    public function getKeywordsSecundarias($lang = 'ca') {
        $keywords = ($lang === 'es') ? $this->keywords_secundarias_es : $this->keywords_secundarias_ca;
        return $keywords ? array_map('trim', explode(',', $keywords)) : [];
    }
    
    /**
     * Obté l'schema structured data decodificat
     * 
     * @return array|null Array amb structured data o null
     */
    public function getSchema() {
        return $this->schema_json ? json_decode($this->schema_json, true) : null;
    }
    
    /**
     * Genera el JSON-LD de l'schema per inserir al <head>
     * 
     * @return string JSON-LD formatat
     */
    public function generarSchemaJSONLD() {
        $schema = $this->getSchema();
        
        if (!$schema) {
            return '';
        }
        
        $jsonld = '<script type="application/ld+json">' . "\n";
        $jsonld .= json_encode($schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $jsonld .= "\n</script>";
        
        return $jsonld;
    }
    
    
    // ============================================
    // GETTERS - OPEN GRAPH
    // ============================================
    
    /**
     * Obté el títol d'Open Graph segons l'idioma
     * 
     * @param string $lang Idioma (ca|es). Per defecte: 'ca'
     * @return string|null Títol OG (si no existeix, retorna el title normal)
     */
    public function getOgTitle($lang = 'ca') {
        $og_title = ($lang === 'es') ? $this->og_title_es : $this->og_title_ca;
        return $og_title ?: $this->getTitle($lang);
    }
    
    /**
     * Obté la descripció d'Open Graph segons l'idioma
     * 
     * @param string $lang Idioma (ca|es). Per defecte: 'ca'
     * @return string|null Descripció OG (si no existeix, retorna la meta description)
     */
    public function getOgDescription($lang = 'ca') {
        $og_desc = ($lang === 'es') ? $this->og_description_es : $this->og_description_ca;
        return $og_desc ?: $this->getMetaDescription($lang);
    }
    
    /**
     * Obté la imatge d'Open Graph
     * 
     * @return string|null URL de la imatge OG
     */
    public function getOgImage() {
        return $this->og_image ?: $this->featured_image;
    }
    
    /**
     * Genera les meta tags d'Open Graph per aquesta pàgina
     * 
     * @param string $base_url URL base del lloc (ex: https://www.psicologiayanina.com)
     * @param string $lang Idioma (ca|es). Per defecte: 'ca'
     * @return string HTML amb meta tags d'Open Graph
     */
    public function generarMetaTagsOG($base_url, $lang = 'ca') {
        $html = '';
        $html .= '<meta property="og:type" content="' . ($this->tipo_pagina === 'articulo' ? 'article' : 'website') . '">' . "\n";
        $html .= '<meta property="og:title" content="' . htmlspecialchars($this->getOgTitle($lang)) . '">' . "\n";
        $html .= '<meta property="og:description" content="' . htmlspecialchars($this->getOgDescription($lang)) . '">' . "\n";
    $url_relativa = ($lang === 'es') ? $this->url_relativa_es : $this->url_relativa_ca;
    $html .= '<meta property="og:url" content="' . htmlspecialchars($base_url . $url_relativa) . '">' . "\n";
        
        if ($image = $this->getOgImage()) {
            $html .= '<meta property="og:image" content="' . htmlspecialchars($image) . '">' . "\n";
            $html .= '<meta property="og:image:width" content="1200">' . "\n";
            $html .= '<meta property="og:image:height" content="630">' . "\n";
        }
        
        if ($this->tipo_pagina === 'articulo' && $this->fecha_publicacion) {
            $html .= '<meta property="article:published_time" content="' . $this->fecha_publicacion . '">' . "\n";
            if ($this->fecha_ultima_actualizacion) {
                $html .= '<meta property="article:modified_time" content="' . $this->fecha_ultima_actualizacion . '">' . "\n";
            }
        }
        
        return $html;
    }
    
    
    // ============================================
    // GETTERS - TWITTER CARD
    // ============================================
    
    /**
     * Obté el títol de Twitter Card segons l'idioma
     * 
     * @param string $lang Idioma (ca|es). Per defecte: 'ca'
     * @return string|null Títol Twitter (si no existeix, retorna el title normal)
     */
    public function getTwitterTitle($lang = 'ca') {
        $tw_title = ($lang === 'es') ? $this->twitter_title_es : $this->twitter_title_ca;
        return $tw_title ?: $this->getTitle($lang);
    }
    
    /**
     * Obté la descripció de Twitter Card segons l'idioma
     * 
     * @param string $lang Idioma (ca|es). Per defecte: 'ca'
     * @return string|null Descripció Twitter (si no existeix, retorna la meta description)
     */
    public function getTwitterDescription($lang = 'ca') {
        $tw_desc = ($lang === 'es') ? $this->twitter_description_es : $this->twitter_description_ca;
        return $tw_desc ?: $this->getMetaDescription($lang);
    }
    
    /**
     * Obté la imatge de Twitter Card
     * 
     * @return string|null URL de la imatge Twitter
     */
    public function getTwitterImage() {
        return $this->twitter_image ?: $this->featured_image;
    }
    
    /**
     * Genera les meta tags de Twitter Card per aquesta pàgina
     * 
     * @param string $lang Idioma (ca|es). Per defecte: 'ca'
     * @return string HTML amb meta tags de Twitter Card
     */
    public function generarMetaTagsTwitter($lang = 'ca') {
        $html = '';
        $html .= '<meta name="twitter:card" content="summary_large_image">' . "\n";
        $html .= '<meta name="twitter:title" content="' . htmlspecialchars($this->getTwitterTitle($lang)) . '">' . "\n";
        $html .= '<meta name="twitter:description" content="' . htmlspecialchars($this->getTwitterDescription($lang)) . '">' . "\n";
        
        if ($image = $this->getTwitterImage()) {
            $html .= '<meta name="twitter:image" content="' . htmlspecialchars($image) . '">' . "\n";
        }
        
        return $html;
    }
    
    
    // ============================================
    // GETTERS - IMATGES
    // ============================================
    
    /**
     * Obté la URL de la imatge destacada
     * 
     * @return string|null URL de la imatge
     */
    public function getFeaturedImage() {
        return $this->featured_image;
    }
    
    /**
     * Obté el text ALT de la imatge segons l'idioma
     * 
     * @param string $lang Idioma (ca|es). Per defecte: 'ca'
     * @return string|null Text ALT
     */
    public function getAltImage($lang = 'ca') {
        return ($lang === 'es') ? $this->alt_image_es : $this->alt_image_ca;
    }
    
    /**
     * Obté el caption de la imatge segons l'idioma
     * 
     * @param string $lang Idioma (ca|es). Per defecte: 'ca'
     * @return string|null Caption
     */
    public function getImageCaption($lang = 'ca') {
        return ($lang === 'es') ? $this->image_caption_es : $this->image_caption_ca;
    }
    
    
    // ============================================
    // GETTERS - MÈTRIQUES
    // ============================================
    
    /**
     * Obté la puntuació SEO
     * 
     * @return int Puntuació de 0 a 100
     */
    public function getSeoScore() {
        return $this->seo_score;
    }
    
    /**
     * Obté el recompte de paraules segons l'idioma
     * 
     * @param string $lang Idioma (ca|es). Per defecte: 'ca'
     * @return int Nombre de paraules
     */
    public function getWordCount($lang = 'ca') {
        return ($lang === 'es') ? $this->word_count_es : $this->word_count_ca;
    }
    
    /**
     * Obté la densitat de la paraula clau segons l'idioma
     * 
     * @param string $lang Idioma (ca|es). Per defecte: 'ca'
     * @return float Densitat en percentatge
     */
    public function getDensidadKeyword($lang = 'ca') {
        return ($lang === 'es') ? $this->densidad_keyword_es : $this->densidad_keyword_ca;
    }
    
    
    // ============================================
    // GETTERS - ESTAT
    // ============================================
    
    /**
     * Comprova si la pàgina està activa
     * 
     * @return bool True si està activa
     */
    public function isActiva() {
        return $this->activa;
    }
    
    /**
     * Obté la data de publicació
     * 
     * @return string|null Data de publicació
     */
    public function getFechaPublicacion() {
        return $this->fecha_publicacion;
    }
    
    /**
     * Obté la data de l'última actualització
     * 
     * @return string|null Data d'actualització
     */
    public function getFechaUltimaActualizacion() {
        return $this->fecha_ultima_actualizacion;
    }
    
    
    // ============================================
    // MÈTODES DE CÀLCUL DE MÈTRIQUES
    // ============================================
    
    /**
     * Calcula el recompte de paraules d'un text
     * 
     * @param string $texto Text a analitzar
     * @return int Nombre de paraules
     */
    private function calcularWordCount($texto) {
        if (!$texto) {
            return 0;
        }
        
        // Eliminar etiquetes HTML
        $texto_limpio = strip_tags($texto);
        
        // Dividir per espais i comptar
        $palabras = preg_split('/\s+/', trim($texto_limpio), -1, PREG_SPLIT_NO_EMPTY);
        
        return count($palabras);
    }
    
    /**
     * Calcula la densitat d'una paraula clau en un text
     * 
     * @param string $texto Text a analitzar
     * @param string $keyword Paraula clau a buscar
     * @return float Densitat en percentatge (0.00 - 100.00)
     */
    private function calcularDensidadKeyword($texto, $keyword) {
        if (!$texto || !$keyword) {
            return 0.0;
        }
        
        $texto_limpio = strip_tags(strtolower($texto));
        $keyword_limpio = strtolower($keyword);
        
        // Comptar aparicions de la keyword
        $apariciones = substr_count($texto_limpio, $keyword_limpio);
        
        // Comptar paraules totals
        $total_palabras = $this->calcularWordCount($texto);
        
        if ($total_palabras == 0) {
            return 0.0;
        }
        
        // Calcular densitat
        $densidad = ($apariciones / $total_palabras) * 100;
        
        return round($densidad, 2);
    }
    
    /**
     * Actualitza les mètriques SEO de la pàgina (word count i keyword density)
     * 
     * @param string $lang Idioma a actualitzar (ca|es|all). Per defecte: 'all'
     * @return bool True si l'actualització ha estat correcta
     * @throws Exception Si hi ha error SQL
     */
    public function actualitzarMetriques($lang = 'all') {
        try {
            $updates = [];
            $params = [':id' => $this->id_pagina];
            
            if ($lang === 'ca' || $lang === 'all') {
                // Calcular mètriques català
                $this->word_count_ca = $this->calcularWordCount($this->contenido_principal_ca);
                $this->densidad_keyword_ca = $this->calcularDensidadKeyword(
                    $this->contenido_principal_ca, 
                    $this->focus_keyword_ca
                );
                
                $updates[] = 'word_count_ca = :word_count_ca';
                $updates[] = 'densidad_keyword_ca = :densidad_keyword_ca';
                $params[':word_count_ca'] = $this->word_count_ca;
                $params[':densidad_keyword_ca'] = $this->densidad_keyword_ca;
            }
            
            if ($lang === 'es' || $lang === 'all') {
                // Calcular mètriques espanyol
                $this->word_count_es = $this->calcularWordCount($this->contenido_principal_es);
                $this->densidad_keyword_es = $this->calcularDensidadKeyword(
                    $this->contenido_principal_es, 
                    $this->focus_keyword_es
                );
                
                $updates[] = 'word_count_es = :word_count_es';
                $updates[] = 'densidad_keyword_es = :densidad_keyword_es';
                $params[':word_count_es'] = $this->word_count_es;
                $params[':densidad_keyword_es'] = $this->densidad_keyword_es;
            }
            
            if (empty($updates)) {
                return false;
            }
            
            $sql = "UPDATE seo_onpage_paginas SET " . implode(', ', $updates) . " WHERE id_pagina = :id";
            $stmt = $this->pdo->prepare($sql);
            
            return $stmt->execute($params);
            
        } catch (PDOException $e) {
            throw new Exception("Error al actualitzar mètriques: " . $e->getMessage());
        }
    }
    
    /**
     * Calcula i actualitza la puntuació SEO global de la pàgina (0-100)
     * 
     * La puntuació es basa en:
     * - Existència i longitud dels camps obligatoris (title, description, H1)
     * - Densitat de paraula clau òptima (1-3%)
     * - Longitud del contingut (mínim 300 paraules)
     * - Optimització d'imatges (ALT, caption)
     * - Open Graph i Twitter Cards
     * - Structured data
     * 
     * @param string $lang Idioma a avaluar (ca|es). Per defecte: 'ca'
     * @return int Puntuació SEO de 0 a 100
     */
    public function calcularSeoScore($lang = 'ca') {
        $puntuacion = 0;
        
        // 1. Title (màx 15 punts)
        $title = $this->getTitle($lang);
        if ($title) {
            $len = mb_strlen($title);
            if ($len >= 50 && $len <= 60) {
                $puntuacion += 15;
            } elseif ($len >= 40 && $len < 70) {
                $puntuacion += 10;
            } elseif ($len > 0) {
                $puntuacion += 5;
            }
        }
        
        // 2. Meta Description (màx 15 punts)
        $description = $this->getMetaDescription($lang);
        if ($description) {
            $len = mb_strlen($description);
            if ($len >= 150 && $len <= 160) {
                $puntuacion += 15;
            } elseif ($len >= 120 && $len < 170) {
                $puntuacion += 10;
            } elseif ($len > 0) {
                $puntuacion += 5;
            }
        }
        
        // 3. H1 (màx 10 punts)
        $h1 = $this->getH1($lang);
        if ($h1 && mb_strlen($h1) > 0) {
            $puntuacion += 10;
        }
        
        // 4. Focus Keyword (màx 10 punts)
        $keyword = $this->getFocusKeyword($lang);
        if ($keyword) {
            $puntuacion += 10;
        }
        
        // 5. Densitat Keyword (màx 15 punts)
        $densidad = $this->getDensidadKeyword($lang);
        if ($densidad >= 1.0 && $densidad <= 3.0) {
            $puntuacion += 15;
        } elseif ($densidad > 0 && $densidad < 5.0) {
            $puntuacion += 8;
        }
        
        // 6. Longitud del contingut (màx 15 punts)
        $word_count = $this->getWordCount($lang);
        if ($word_count >= 1000) {
            $puntuacion += 15;
        } elseif ($word_count >= 500) {
            $puntuacion += 10;
        } elseif ($word_count >= 300) {
            $puntuacion += 7;
        } elseif ($word_count > 0) {
            $puntuacion += 3;
        }
        
        // 7. Imatges optimitzades (màx 10 punts)
        if ($this->featured_image) {
            $puntuacion += 3;
            if ($this->getAltImage($lang)) {
                $puntuacion += 4;
            }
            if ($this->getImageCaption($lang)) {
                $puntuacion += 3;
            }
        }
        
        // 8. Open Graph (màx 5 punts)
        if ($this->getOgTitle($lang) && $this->getOgDescription($lang)) {
            $puntuacion += 5;
        }
        
        // 9. Structured Data (màx 5 punts)
        if ($this->schema_json) {
            $puntuacion += 5;
        }
        
        // Actualitzar a la base de dades
        $this->seo_score = min($puntuacion, 100);
        
        try {
            $sql = "UPDATE seo_onpage_paginas SET seo_score = :score WHERE id_pagina = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':score', $this->seo_score, PDO::PARAM_INT);
            $stmt->bindParam(':id', $this->id_pagina, PDO::PARAM_INT);
            $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error al actualitzar SEO score: " . $e->getMessage());
        }
        
        return $this->seo_score;
    }
    
    
    // ============================================
    // MÈTODES D'ACTUALITZACIÓ
    // ============================================
    
    /**
     * Actualitza un camp específic de la pàgina
     * 
     * @param string $field Nom del camp a actualitzar
     * @param mixed $value Valor nou
     * @return bool True si l'actualització ha estat correcta
     * @throws Exception Si el camp no existeix o hi ha error SQL
     */
    public function actualitzarCamp($field, $value) {
        try {
            // Llista de camps permesos per actualitzar
            $allowed_fields = [
                'url_relativa_ca', 'url_relativa_es', 'titulo_pagina', 'tipo_pagina',
                'title_ca', 'meta_description_ca', 'h1_ca', 'contenido_principal_ca',
                'title_es', 'meta_description_es', 'h1_es', 'contenido_principal_es',
                'breadcrumb_json', 'slug_ca', 'slug_es', 'parent_id',
                'meta_robots', 'canonical_url_ca', 'canonical_url_es', 'priority', 'changefreq',
                'focus_keyword_ca', 'focus_keyword_es', 'keywords_secundarias_ca', 'keywords_secundarias_es',
                'schema_json',
                'og_title_ca', 'og_title_es', 'og_description_ca', 'og_description_es', 'og_image',
                'twitter_title_ca', 'twitter_title_es', 'twitter_description_ca', 'twitter_description_es', 'twitter_image',
                'featured_image', 'alt_image_ca', 'alt_image_es', 'image_caption_ca', 'image_caption_es',
                'activa', 'fecha_publicacion', 'fecha_ultima_actualizacion'
            ];
            
            if (!in_array($field, $allowed_fields)) {
                throw new Exception("Camp '$field' no és vàlid per actualitzar");
            }
            
            $sql = "UPDATE seo_onpage_paginas SET $field = :value WHERE id_pagina = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':value', $value);
            $stmt->bindParam(':id', $this->id_pagina, PDO::PARAM_INT);
            
            $result = $stmt->execute();
            
            if ($result) {
                // Actualitzar la propietat de l'objecte
                $this->$field = $value;
            }
            
            return $result;
            
        } catch (PDOException $e) {
            throw new Exception("Error al actualitzar camp '$field': " . $e->getMessage());
        }
    }
    
    /**
     * Actualitza múltiples camps de la pàgina
     * 
     * @param array $data Array associatiu amb els camps i valors a actualitzar
     * @return bool True si l'actualització ha estat correcta
     * @throws Exception Si hi ha error SQL
     */
    public function actualitzarMultiplesCamps($data) {
        try {
            $this->pdo->beginTransaction();

            foreach ($data as $field => $value) {
                $this->actualitzarCamp($field, $value);
            }

            $this->pdo->commit();
            return true;

        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw new Exception("Error al actualitzar múltiples camps: " . $e->getMessage());
        }
    }
    
    
    // ============================================
    // MÈTODES DE CREACIÓ I ELIMINACIÓ
    // ============================================
    
    /**
     * Crea una nova pàgina a la base de dades
     * 
     * @param array $data Array associatiu amb les dades de la nova pàgina
     * @return int|false ID de la pàgina creada o false si hi ha error
     * @throws Exception Si falten camps obligatoris o hi ha error SQL
     */
    public static function crear($data) {
        try {
            // Validar camps obligatoris
            $required = ['url_relativa_ca', 'titulo_pagina', 'tipo_pagina', 'title_ca', 'meta_description_ca', 
                        'h1_ca', 'title_es', 'meta_description_es', 'h1_es'];
            
            foreach ($required as $field) {
                if (!isset($data[$field]) || empty($data[$field])) {
                    throw new Exception("Camp obligatori '$field' no proporcionat");
                }
            }
            
            $conn = Connexio::getInstance();
            $pdo = $conn->getConnexio();
            
            // Preparar camps i valors
            $fields = array_keys($data);
            $placeholders = array_map(function($field) { return ":$field"; }, $fields);
            
            $sql = "INSERT INTO seo_onpage_paginas (" . implode(', ', $fields) . ") 
                    VALUES (" . implode(', ', $placeholders) . ")";
            
            $stmt = $pdo->prepare($sql);
            
            foreach ($data as $field => $value) {
                $stmt->bindValue(":$field", $value);
            }
            
            if ($stmt->execute()) {
                return $pdo->lastInsertId();
            }
            
            return false;
            
        } catch (PDOException $e) {
            throw new Exception("Error al crear pàgina: " . $e->getMessage());
        }
    }
    
    /**
     * Elimina una pàgina de la base de dades
     * 
     * @return bool True si l'eliminació ha estat correcta
     * @throws Exception Si hi ha error SQL
     */
    public function eliminar() {
        try {
            $sql = "DELETE FROM seo_onpage_paginas WHERE id_pagina = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':id', $this->id_pagina, PDO::PARAM_INT);
            
            return $stmt->execute();
            
        } catch (PDOException $e) {
            throw new Exception("Error al eliminar pàgina: " . $e->getMessage());
        }
    }
    
    
    // ============================================
    // MÈTODES ESTÀTICS ÚTILS
    // ============================================
    
    /**
     * Obté totes les pàgines actives
     * 
     * @param string|null $tipo_pagina Filtrar per tipus de pàgina (opcional)
     * @return array Array d'objectes SEO_OnPage
     */
    public static function llistarPaginesActives($tipo_pagina = null) {
        try {
            $conn = Connexio::getInstance();
            $pdo = $conn->getConnexio();
            
            $sql = "SELECT id_pagina FROM seo_onpage_paginas WHERE activa = 1";
            
            if ($tipo_pagina) {
                $sql .= " AND tipo_pagina = :tipo";
            }
            
            $sql .= " ORDER BY fecha_publicacion DESC";
            
            $stmt = $pdo->prepare($sql);
            
            if ($tipo_pagina) {
                $stmt->bindParam(':tipo', $tipo_pagina, PDO::PARAM_STR);
            }
            
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $paginas = [];
            foreach ($rows as $row) {
                $paginas[] = new self($row['id_pagina']);
            }
            
            return $paginas;
            
        } catch (Exception $e) {
            error_log("Error en llistarPaginesActives: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Genera el sitemap.xml amb totes les pàgines actives
     * 
     * @param string $base_url URL base del lloc
     * @return string XML del sitemap
     */
    public static function generarSitemap($base_url) {
        $paginas = self::llistarPaginesActives();
        
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        
        foreach ($paginas as $pagina) {
            $xml .= '  <url>' . "\n";
            $xml .= '    <loc>' . htmlspecialchars($base_url . $pagina->getUrlRelativa()) . '</loc>' . "\n";
            
            if ($fecha = $pagina->getFechaUltimaActualizacion() ?: $pagina->getFechaPublicacion()) {
                $xml .= '    <lastmod>' . date('Y-m-d', strtotime($fecha)) . '</lastmod>' . "\n";
            }
            
            $xml .= '    <changefreq>' . $pagina->getChangefreq() . '</changefreq>' . "\n";
            $xml .= '    <priority>' . $pagina->getPriority() . '</priority>' . "\n";
            $xml .= '  </url>' . "\n";
        }
        
        $xml .= '</urlset>';
        
        return $xml;
    }
    
    /**
     * Genera el bloc <head> complet per aquesta pàgina
     * 
     * Inclou: title, meta description, meta robots, canonical, Open Graph, Twitter Card, Schema
     * 
     * @param string $base_url URL base del lloc
     * @param string $lang Idioma (ca|es). Per defecte: 'ca'
     * @return string HTML complet per al <head>
     */
    public function generarHeadComplet($base_url, $lang = 'ca') {
        $html = "\n<!-- SEO Meta Tags -->\n";
        $html .= '<title>' . htmlspecialchars($this->getTitle($lang)) . '</title>' . "\n";
        $html .= '<meta name="description" content="' . htmlspecialchars($this->getMetaDescription($lang)) . '">' . "\n";
        $html .= '<meta name="robots" content="' . $this->meta_robots . '">' . "\n";
        
    $lang = isset($_SESSION['language']) ? $_SESSION['language'] : 'ca';
    $url_relativa = ($lang === 'es') ? $this->url_relativa_es : $this->url_relativa_ca;
    $canonical_candidate = $this->getCanonicalUrl($lang);
    $canonical = $canonical_candidate ?: ($base_url . $url_relativa);
        $html .= '<link rel="canonical" href="' . htmlspecialchars($canonical) . '">' . "\n";
        
        if ($keyword = $this->getFocusKeyword($lang)) {
            $html .= '<meta name="keywords" content="' . htmlspecialchars($keyword) . '">' . "\n";
        }
        
        $html .= "\n<!-- Open Graph -->\n";
        $html .= $this->generarMetaTagsOG($base_url, $lang);
        
        $html .= "\n<!-- Twitter Card -->\n";
        $html .= $this->generarMetaTagsTwitter($lang);
        
        $html .= "\n<!-- Structured Data -->\n";
        $html .= $this->generarSchemaJSONLD();
        
        return $html;
    }
    
    /**
     * Retorna totes les dades de la pàgina com array associatiu
     * 
     * @return array Array amb totes les dades de la pàgina
     */
    public function toArray() {
        return [
            'id_pagina' => $this->id_pagina,
            'url_relativa_ca' => $this->url_relativa_ca,
            'url_relativa_es' => $this->url_relativa_es,
            'titulo_pagina' => $this->titulo_pagina,
            'tipo_pagina' => $this->tipo_pagina,
            'seo_ca' => [
                'title' => $this->title_ca,
                'meta_description' => $this->meta_description_ca,
                'h1' => $this->h1_ca,
                'contenido' => $this->contenido_principal_ca,
                'focus_keyword' => $this->focus_keyword_ca,
                'keywords_secundarias' => $this->getKeywordsSecundarias('ca'),
                'word_count' => $this->word_count_ca,
                'densidad_keyword' => $this->densidad_keyword_ca
            ],
            'seo_es' => [
                'title' => $this->title_es,
                'meta_description' => $this->meta_description_es,
                'h1' => $this->h1_es,
                'contenido' => $this->contenido_principal_es,
                'focus_keyword' => $this->focus_keyword_es,
                'keywords_secundarias' => $this->getKeywordsSecundarias('es'),
                'word_count' => $this->word_count_es,
                'densidad_keyword' => $this->densidad_keyword_es
            ],
            'estructura' => [
                'breadcrumbs' => $this->getBreadcrumbs(),
                'slug_ca' => $this->slug_ca,
                'slug_es' => $this->slug_es,
                'parent_id' => $this->parent_id
            ],
            'tecnico' => [
                'meta_robots' => $this->meta_robots,
                'canonical_url_ca' => $this->canonical_url_ca,
                'canonical_url_es' => $this->canonical_url_es,
                'priority' => $this->priority,
                'changefreq' => $this->changefreq
            ],
            'imagenes' => [
                'featured_image' => $this->featured_image,
                'alt_ca' => $this->alt_image_ca,
                'alt_es' => $this->alt_image_es,
                'caption_ca' => $this->image_caption_ca,
                'caption_es' => $this->image_caption_es
            ],
            'metricas' => [
                'seo_score' => $this->seo_score
            ],
            'estado' => [
                'activa' => $this->activa,
                'fecha_publicacion' => $this->fecha_publicacion,
                'fecha_ultima_actualizacion' => $this->fecha_ultima_actualizacion,
                'fecha_creacion' => $this->fecha_creacion,
                'fecha_modificacion' => $this->fecha_modificacion
            ]
        ];
    }
    
    /**
     * Calcula les estadístiques globals de SEO On-Page per a totes les pàgines actives
     * 
     * Retorna un array amb:
     * - score: Puntuació mitjana de 0-100
     * - total_paginas: Nombre total de pàgines actives
     * - promedio_score: Puntuació SEO mitjana de totes les pàgines
     * - paginas_optimizadas: Nombre de pàgines amb score >= 80
     * - paginas_mejorar: Nombre de pàgines amb score < 60
     * - estat: Text descriptiu de l'estat general
     * - detalles: Estadístiques detallades per categories
     * 
     * @return array|null Estadístiques o null si hi ha error
     */
    public static function calcularEstadistiquesGlobals() {
        try {
            $conn = Connexio::getInstance();
            $pdo = $conn->getConnexio();
            
            // Obtenir totes les pàgines actives
            $paginas = self::llistarPaginesActives();
            $total_paginas = count($paginas);
            
            if ($total_paginas === 0) {
                return [
                    'score' => 0,
                    'total_paginas' => 0,
                    'promedio_score' => 0,
                    'paginas_optimizadas' => 0,
                    'paginas_mejorar' => 0,
                    'estat' => 'Sin páginas',
                    'detalles' => []
                ];
            }
            
            // Inicialitzar contadors
            $suma_scores = 0;
            $paginas_optimizadas = 0;
            $paginas_mejorar = 0;
            
            // Comptadors per categories
            $con_title_ca = 0;
            $con_title_es = 0;
            $con_meta_ca = 0;
            $con_meta_es = 0;
            $con_h1_ca = 0;
            $con_h1_es = 0;
            $con_keyword_ca = 0;
            $con_keyword_es = 0;
            $con_og_image = 0;
            $con_twitter_image = 0;
            $con_featured_image = 0;
            $con_alt_ca = 0;
            $con_alt_es = 0;
            
            // Analitzar cada pàgina
            foreach ($paginas as $pagina) {
                $score = $pagina->getSeoScore();
                $suma_scores += $score;
                
                if ($score >= 80) {
                    $paginas_optimizadas++;
                } elseif ($score < 60) {
                    $paginas_mejorar++;
                }
                
                // Comprovar camps completats
                if ($pagina->getTitle('ca')) $con_title_ca++;
                if ($pagina->getTitle('es')) $con_title_es++;
                if ($pagina->getMetaDescription('ca')) $con_meta_ca++;
                if ($pagina->getMetaDescription('es')) $con_meta_es++;
                if ($pagina->getH1('ca')) $con_h1_ca++;
                if ($pagina->getH1('es')) $con_h1_es++;
                if ($pagina->getFocusKeyword('ca')) $con_keyword_ca++;
                if ($pagina->getFocusKeyword('es')) $con_keyword_es++;
                if ($pagina->getOgImage()) $con_og_image++;
                if ($pagina->getTwitterImage()) $con_twitter_image++;
                if ($pagina->getFeaturedImage()) $con_featured_image++;
                if ($pagina->getAltImage('ca')) $con_alt_ca++;
                if ($pagina->getAltImage('es')) $con_alt_es++;
            }
            
            // Calcular puntuacions per categories
            $promedio_score = round($suma_scores / $total_paginas);
            
            // 1. Meta Tags (titles + descriptions) - 30 punts
            $meta_score = 0;
            $meta_score += round(($con_title_ca / $total_paginas) * 8);
            $meta_score += round(($con_title_es / $total_paginas) * 7);
            $meta_score += round(($con_meta_ca / $total_paginas) * 8);
            $meta_score += round(($con_meta_es / $total_paginas) * 7);
            
            // 2. Estructura de contingut (H1 + keywords) - 25 punts
            $content_score = 0;
            $content_score += round(($con_h1_ca / $total_paginas) * 7);
            $content_score += round(($con_h1_es / $total_paginas) * 6);
            $content_score += round(($con_keyword_ca / $total_paginas) * 6);
            $content_score += round(($con_keyword_es / $total_paginas) * 6);
            
            // 3. Open Graph + Twitter - 25 punts
            $social_score = 0;
            $social_score += round(($con_og_image / $total_paginas) * 13);
            $social_score += round(($con_twitter_image / $total_paginas) * 12);
            
            // 4. Imatges (featured + alt tags) - 20 punts
            $images_score = 0;
            $images_score += round(($con_featured_image / $total_paginas) * 8);
            $images_score += round(($con_alt_ca / $total_paginas) * 6);
            $images_score += round(($con_alt_es / $total_paginas) * 6);
            
            // Score global (basat en els 4 apartats)
            $score_global = $meta_score + $content_score + $social_score + $images_score;
            
            // Determinar estat
            $estat = '';
            if ($score_global >= 90) {
                $estat = 'Excelente';
            } elseif ($score_global >= 75) {
                $estat = 'Muy buena';
            } elseif ($score_global >= 60) {
                $estat = 'Buena';
            } elseif ($score_global >= 40) {
                $estat = 'Mejorable';
            } else {
                $estat = 'Necesita atención';
            }
            
            return [
                'score' => $score_global,
                'total_paginas' => $total_paginas,
                'promedio_score' => $promedio_score,
                'paginas_optimizadas' => $paginas_optimizadas,
                'paginas_mejorar' => $paginas_mejorar,
                'estat' => $estat,
                'detalles' => [
                    'meta_tags' => [
                        'puntuacio' => $meta_score,
                        'maxim' => 30,
                        'percentatge' => round(($meta_score / 30) * 100)
                    ],
                    'content' => [
                        'puntuacio' => $content_score,
                        'maxim' => 25,
                        'percentatge' => round(($content_score / 25) * 100)
                    ],
                    'social' => [
                        'puntuacio' => $social_score,
                        'maxim' => 25,
                        'percentatge' => round(($social_score / 25) * 100)
                    ],
                    'images' => [
                        'puntuacio' => $images_score,
                        'maxim' => 20,
                        'percentatge' => round(($images_score / 20) * 100)
                    ]
                ]
            ];
            
        } catch (Exception $e) {
            error_log("Error en calcularEstadistiquesGlobals: " . $e->getMessage());
            return null;
        }
    }


    // ============================================
    // NOUS MÈTODES D'AUDITORIA/VALIDACIÓ
    // ============================================

    /**
     * Comprova la coherència del camp canonical per a l'idioma indicat.
     * S'assegura que la URL canònica (si existeix) és absoluta i que no hi ha duplicats a la BD.
     * Retorna un array amb 'ok' i 'issues' (llista d'advertències) i 'duplicates' amb altres pàgines que comparteixen la mateixa canonical.
     *
     * @param string $lang Idioma (ca|es)
     * @return array
     */
    public function checkCanonicalConsistency($lang = 'ca') {
        $issues = [];
        $duplicates = [];

        $field = ($lang === 'es') ? 'canonical_url_es' : 'canonical_url_ca';
        $url_rel_field = ($lang === 'es') ? 'url_relativa_es' : 'url_relativa_ca';

        $canonical = $this->$field ?? null;
        $url_rel = $this->$url_rel_field ?? null;

        if (!$canonical) {
            $issues[] = "Canonical buit per a l'idioma $lang";
        } else {
            // Absoluta?
            if (!preg_match('#^https?://#i', $canonical)) {
                $issues[] = "Canonical no és una URL absoluta: $canonical";
            }

            // Coherència amb url_relativa: si la url_relativa existeix, comprovar que s'hi contingui
            if ($url_rel && strpos($canonical, $url_rel) === false) {
                $issues[] = "Canonical ('$canonical') sembla no correspondre a la url_relativa ('$url_rel')";
            }

            // Buscar duplicats a la BD: altres pàgines amb la mateixa canonical
            try {
                $conn = Connexio::getInstance();
                $pdo = $conn->getConnexio();

                $sql = "SELECT id_pagina, url_relativa_ca, url_relativa_es, titulo_pagina FROM seo_onpage_paginas WHERE $field = :canonical AND id_pagina != :id";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':canonical', $canonical);
                $stmt->bindParam(':id', $this->id_pagina, PDO::PARAM_INT);
                $stmt->execute();
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if (!empty($rows)) {
                    foreach ($rows as $r) {
                        $duplicates[] = $r;
                    }
                    $issues[] = 'Existeixen altres pàgines amb la mateixa canonical';
                }
            } catch (Exception $e) {
                $issues[] = 'Error consultant duplicats de canonical: ' . $e->getMessage();
            }
        }

        return ['ok' => empty($issues), 'issues' => $issues, 'duplicates' => $duplicates];
    }


    /**
     * Comprova la presència de versions en ambdós idiomes i possibils conflictes entre canonical i hreflang.
     * Retorna un array amb 'ok', 'issues' i les URLs relatives trobades per cada idioma.
     *
     * @return array
     */
    public function checkHreflang() {
        $issues = [];

        $url_ca = $this->url_relativa_ca ?? null;
        $url_es = $this->url_relativa_es ?? null;

        if (!$url_ca) {
            $issues[] = 'Falta url_relativa_ca';
        }
        if (!$url_es) {
            $issues[] = 'Falta url_relativa_es';
        }

        // Comprovar que les canonical no entren en conflicte (p. ex. canonical_ca apunta a /es/)
        $can_ca = $this->canonical_url_ca ?? null;
        $can_es = $this->canonical_url_es ?? null;

        if ($can_ca && $can_es && $can_ca === $can_es) {
            // Pot estar bé (canonical compartida) però afegim advertència per a revisió
            $issues[] = 'Canonical ca i es són idèntiques; comprovar si això és intencional';
        }

        // Si existeix una canonical d'un idioma que apunta clarament a la versió de l'altre idioma, marcar-ho
        if ($can_ca && stripos($can_ca, '/es/') !== false) {
            $issues[] = "Canonical CA sembla apuntar a una ruta ES: $can_ca";
        }
        if ($can_es && stripos($can_es, '/ca/') !== false) {
            $issues[] = "Canonical ES sembla apuntar a una ruta CA: $can_es";
        }

        return ['ok' => empty($issues), 'issues' => $issues, 'urls' => ['ca' => $url_ca, 'es' => $url_es]];
    }


    /**
     * Cerca títols duplicats a la BD per ambdós idiomes i retorna llistats on count>1.
     *
     * @return array
     */
    public static function detectarTitolsDuplicats() {
        $result = ['ca' => [], 'es' => []];
        try {
            $conn = Connexio::getInstance();
            $pdo = $conn->getConnexio();

            // Català
            $sql_ca = "SELECT title_ca AS title, GROUP_CONCAT(id_pagina) AS ids, COUNT(*) AS cnt FROM seo_onpage_paginas WHERE title_ca IS NOT NULL AND title_ca <> '' GROUP BY title_ca HAVING cnt > 1";
            $stmt = $pdo->query($sql_ca);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rows as $r) {
                $r['ids'] = array_map('intval', explode(',', $r['ids']));
                $result['ca'][] = $r;
            }

            // Espanyol
            $sql_es = "SELECT title_es AS title, GROUP_CONCAT(id_pagina) AS ids, COUNT(*) AS cnt FROM seo_onpage_paginas WHERE title_es IS NOT NULL AND title_es <> '' GROUP BY title_es HAVING cnt > 1";
            $stmt = $pdo->query($sql_es);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rows as $r) {
                $r['ids'] = array_map('intval', explode(',', $r['ids']));
                $result['es'][] = $r;
            }

        } catch (Exception $e) {
            error_log('Error detectant títols duplicats: ' . $e->getMessage());
        }

        return $result;
    }


    /**
     * Audita les capçaleres dins del contingut principal (html) per a un idioma determinat.
     * Retorna comptatges H1..H6 i una llista d'issues (més d'un H1, salts d'ordre de capçaleres...)
     *
     * @param string $lang ca|es
     * @return array
     */
    public function auditHeadings($lang = 'ca') {
        $html = ($lang === 'es') ? $this->contenido_principal_es : $this->contenido_principal_ca;
        $issues = [];
        $counts = ['h1' => 0, 'h2' => 0, 'h3' => 0, 'h4' => 0, 'h5' => 0, 'h6' => 0];
        $sequence = [];

        if (!$html) {
            $issues[] = 'Contingut buit';
            return ['counts' => $counts, 'issues' => $issues, 'sequence' => $sequence];
        }

        libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        // Forçar encoding
        $dom->loadHTML('<?xml encoding="utf-8" ?>' . $html);
        for ($i = 1; $i <= 6; $i++) {
            $tag = 'h' . $i;
            $nodes = $dom->getElementsByTagName($tag);
            $counts[$tag] = $nodes->length;
            foreach ($nodes as $n) {
                $sequence[] = $i;
            }
        }

        // Issues: més d'un H1
        if ($counts['h1'] > 1) {
            $issues[] = 'Més d\'un H1 present (' . $counts['h1'] . ')';
        }

        // Detectar salts d'ordre (p.ex. H1 -> H3)
        $prev = null;
        foreach ($sequence as $lvl) {
            if ($prev !== null && ($lvl - $prev) > 1) {
                $issues[] = "Salt d'ordre de capçalera detectat: H$prev → H$lvl";
            }
            $prev = $lvl;
        }

        return ['counts' => $counts, 'issues' => $issues, 'sequence' => $sequence];
    }


    /**
     * Comptabilitza enllaços interns i externs present al contingut principal d'un idioma.
     * No fa peticions HTTP per verificar trencats (opcional). Retorna totals i listats.
     *
     * @param string $lang ca|es
     * @return array
     */
    public function countInternalExternalLinks($lang = 'ca') {
        $html = ($lang === 'es') ? $this->contenido_principal_es : $this->contenido_principal_ca;
        $internal = 0;
        $external = 0;
        $anchors = [];

        if (!$html) {
            return ['internal' => 0, 'external' => 0, 'anchors' => []];
        }

        libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        $dom->loadHTML('<?xml encoding="utf-8" ?>' . $html);
        $links = $dom->getElementsByTagName('a');

        $host = $_SERVER['HTTP_HOST'] ?? null;

        foreach ($links as $a) {
            $href = trim($a->getAttribute('href'));
            if (!$href) continue;
            // Ignore anchors/mailto/tel
            if (strpos($href, '#') === 0 || stripos($href, 'mailto:') === 0 || stripos($href, 'tel:') === 0) {
                $internal++;
                $anchors[] = ['href' => $href, 'type' => 'internal-aux'];
                continue;
            }

            // Absolute URL
            if (preg_match('#^https?://#i', $href)) {
                $p = parse_url($href);
                $link_host = $p['host'] ?? null;
                if ($link_host && $host && strcasecmp($link_host, $host) === 0) {
                    $internal++;
                    $anchors[] = ['href' => $href, 'type' => 'internal'];
                } else {
                    $external++;
                    $anchors[] = ['href' => $href, 'type' => 'external'];
                }
            } else {
                // Relative URL -> internal
                $internal++;
                $anchors[] = ['href' => $href, 'type' => 'internal'];
            }
        }

        return ['internal' => $internal, 'external' => $external, 'anchors' => $anchors];
    }

    /**
     * Calcula la llegibilitat i temps estimat de lectura del contingut
     * Utilitza una aproximació simple per a la puntuació Flesch (adaptada) basada
     * en comptatge d'oracions, paraules i síl·labes estimades.
     *
     * @param string $lang ca|es
     * @return array ['words'=>int, 'reading_time_min'=>float, 'flesch_score'=>float]
     */
    public function computeReadability($lang = 'ca') {
        $html = ($lang === 'es') ? $this->contenido_principal_es : $this->contenido_principal_ca;

        if (!$html) {
            return ['words' => 0, 'reading_time_min' => 0, 'flesch_score' => 0.0];
        }

        // Netejar HTML
        $text = trim(strip_tags($html));
        if ($text === '') {
            return ['words' => 0, 'reading_time_min' => 0, 'flesch_score' => 0.0];
        }

        // Paraules
        $words = $this->calcularWordCount($text);

        // Oracions: comptar punts, interrogants i exclamacions
        $sentences = preg_split('/[\.\!\?]+/', $text, -1, PREG_SPLIT_NO_EMPTY);
        $sentences_count = max(1, count($sentences));

        // Síl·labes estimades: grups de vocals com a aproximació
        $syllables = $this->estimateSyllables($text);

        // Temps de lectura: 200 wpm com a valor per defecte
        $reading_time_min = $words > 0 ? round($words / 200, 2) : 0;

        // Flesch Reading Ease (aprox) adaptat: 206.835 - 62.3*(syllables/words) - 0.8*(words/sentences)
        // Aquesta fórmula està pensada per idiomes romànics amb ajust conservador.
        $flesch = 0.0;
        if ($words > 0 && $sentences_count > 0) {
            $flesch = 206.835 - 62.3 * ($syllables / $words) - 0.8 * ($words / $sentences_count);
            $flesch = round($flesch, 2);
        }

        return [
            'words' => $words,
            'reading_time_min' => $reading_time_min,
            'flesch_score' => $flesch,
            'sentences' => $sentences_count,
            'syllables_est' => $syllables
        ];
    }

    /**
     * Estima el nombre de síl·labes d'un text utilitzant una heurística simple
     * Comptarà grups de vocals (a,e,i,o,u,àèéíòóúü) com a síl·labes aproximades.
     *
     * @param string $text
     * @return int
     */
    private function estimateSyllables($text) {
        $text = mb_strtolower($text, 'UTF-8');
        // Reemplaçar caràcters no vocals per espais
        // Incloure vocals accentuades i ï/ü
        $text = preg_replace('/[^a-zàáâäãåèéêëìíîïòóôöõùúûüyçñÀÁÂÄÃÅÈÉÊËÌÍÎÏÒÓÔÖÕÙÚÛÜÝÇÑ]/u', ' ', $text);
        // Comptar grups de vocals
        preg_match_all('/[aeiouyàáâäãåèéêëìíîïòóôöõùúûü]+/u', $text, $matches);
        $count = isset($matches[0]) ? count($matches[0]) : 0;

        // Assegurar mínim 1 síl·laba si hi ha paraules
        $words = $this->calcularWordCount($text);
        if ($words > 0 && $count < 1) $count = $words;

        return (int) $count;
    }

    /**
     * Audita les imatges presents al contingut principal i la imatge destacada.
     * Comprova: alt, caption (si hi ha), src absolut/relatiu, presence de srcset, i existencia al FS si és local.
     *
     * @param string $lang ca|es
     * @return array ['total'=>int,'missing_alt'=>int,'images'=>[...]]
     */
    public function auditImages($lang = 'ca') {
        $html = ($lang === 'es') ? $this->contenido_principal_es : $this->contenido_principal_ca;
        $results = ['total' => 0, 'missing_alt' => 0, 'without_srcset' => 0, 'images' => []];

        // Analitzar <img> dins del contingut
        if ($html) {
            libxml_use_internal_errors(true);
            $dom = new DOMDocument();
            $dom->loadHTML('<?xml encoding="utf-8" ?>' . $html);
            $imgs = $dom->getElementsByTagName('img');

            foreach ($imgs as $img) {
                $src = trim($img->getAttribute('src'));
                $alt = trim($img->getAttribute('alt')) ?: null;
                $srcset = trim($img->getAttribute('srcset')) ?: null;

                $info = ['src' => $src, 'alt' => $alt, 'has_srcset' => (bool)$srcset, 'exists_on_fs' => null, 'caption' => null];

                if (!$alt) {
                    $results['missing_alt']++;
                }
                if (!$srcset) {
                    $results['without_srcset']++;
                }

                // Buscar caption: si l'img està dins d'un <figure> amb <figcaption>
                $parent = $img->parentNode;
                if ($parent && $parent->nodeName === 'figure') {
                    foreach ($parent->childNodes as $child) {
                        if ($child->nodeName === 'figcaption') {
                            $cap = trim($child->textContent);
                            if ($cap) $info['caption'] = $cap;
                        }
                    }
                }

                // Comprovar existencia al FS per a rutes locals
                $exists = null;
                if ($src) {
                    if (preg_match('#^https?://#i', $src)) {
                        // No intentem fer request extern per defecte
                        $exists = null;
                    } else {
                        // Mapear ruta relativa al projecte
                        $basePath = realpath(__DIR__ . '/../');
                        $candidate = $basePath . '/' . ltrim($src, '/');
                        $exists = file_exists($candidate);
                    }
                }
                $info['exists_on_fs'] = $exists;

                $results['images'][] = $info;
                $results['total']++;
            }
        }

        // Incloure la imatge destacada si existeix i no està en el HTML
        if ($this->featured_image) {
            $found = false;
            foreach ($results['images'] as $i) {
                if ($i['src'] === $this->featured_image) { $found = true; break; }
            }
            if (!$found) {
                $src = $this->featured_image;
                $alt = $this->getAltImage($lang);
                $exists = null;
                if (!preg_match('#^https?://#i', $src)) {
                    $basePath = realpath(__DIR__ . '/../');
                    $candidate = $basePath . '/' . ltrim($src, '/');
                    $exists = file_exists($candidate);
                }
                $results['images'][] = ['src' => $src, 'alt' => $alt, 'has_srcset' => false, 'exists_on_fs' => $exists, 'caption' => $this->getImageCaption($lang)];
                $results['total']++;
                if (!$alt) $results['missing_alt']++;
            }
        }

        return $results;
    }

    /**
     * Valida mínimament el Schema JSON-LD emmagatzemat a la pàgina.
     * Retorna 'ok' i 'issues' amb misses/advertències; comprova tipus comuns (Article, LocalBusiness, Person/Psychologist).
     *
     * @return array ['ok'=>bool,'issues'=>array,'schema'=>array|null]
     */
    public function validateSchemaJSONLD() {
        $schema = $this->getSchema();
        $issues = [];

        if (!$schema) {
            $issues[] = 'No hi ha schema JSON-LD definits';
            return ['ok' => false, 'issues' => $issues, 'schema' => null];
        }

        $checkSingle = function($obj) use (&$issues) {
            if (!isset($obj['@type']) && !isset($obj['type'])) {
                $issues[] = 'Schema sense @type';
                return false;
            }
            $type = $obj['@type'] ?? $obj['type'];

            switch (strtolower($type)) {
                case 'article':
                    if (empty($obj['headline'])) $issues[] = 'Article sense headline';
                    if (empty($obj['author']) && empty($obj['creator'])) $issues[] = 'Article sense author';
                    if (empty($obj['datePublished'])) $issues[] = 'Article sense datePublished';
                    break;
                case 'localbusiness':
                case 'local business':
                    if (empty($obj['name'])) $issues[] = 'LocalBusiness sense name';
                    if (empty($obj['address'])) $issues[] = 'LocalBusiness sense address';
                    break;
                case 'person':
                case 'psychologist':
                    if (empty($obj['name'])) $issues[] = ucfirst($type) . ' sense name';
                    break;
                default:
                    // No forçar errors per altres tipus, només recomanacions
                    break;
            }

            return true;
        };

        // Schema pot ser array o objecte
        if (is_array($schema)) {
            // Si és un array associatiu amb @context, tractar com a objecte
            $assocKeys = array_keys($schema);
            $isAssoc = array_keys($assocKeys) !== $assocKeys;
            // Si els primers elements són numèrics, tractar com a llista
            if (array_keys($schema) === range(0, count($schema) - 1)) {
                foreach ($schema as $s) { $checkSingle($s); }
            } else {
                $checkSingle($schema);
            }
        } else {
            $issues[] = 'Schema JSON no té format array/object correcte';
            return ['ok' => false, 'issues' => $issues, 'schema' => $schema];
        }

        return ['ok' => empty($issues), 'issues' => $issues, 'schema' => $schema];
    }

    /**
     * Comprova indexabilitat: meta_robots, presencia al sitemap.xml i regles de robots.txt.
     * Retorna avisos si hi ha conflictes (p.ex. noindex a la pàgina però està al sitemap o robots bloqueja la ruta).
     *
     * @return array ['ok'=>bool,'issues'=>array,'robots_txt'=>string|null,'sitemap_contains'=>bool|null]
     */
    public function checkIndexability() {
        $issues = [];

        $meta = $this->getMetaRobots();
        if ($meta) {
            if (stripos($meta, 'noindex') !== false) {
                $issues[] = 'Meta robots conté noindex';
            }
        }

        // Comprovar sitemap.xml (arrel del projecte)
        $basePath = realpath(__DIR__ . '/../');
        $sitemapPath = $basePath . '/sitemap.xml';
        $sitemap_contains = null;
        if (file_exists($sitemapPath)) {
            $sitemap = file_get_contents($sitemapPath);
            $canonical = $this->getCanonicalUrl() ?: $this->getUrlRelativa();
            // buscar la ruta (pot ser relativa o absoluta). Fem strcmp simplista
            if ($canonical && strpos($sitemap, $canonical) !== false) {
                $sitemap_contains = true;
                if (stripos($meta, 'noindex') !== false) {
                    $issues[] = 'Pàgina marcada com noindex però figura al sitemap.xml';
                }
            } else {
                $sitemap_contains = false;
            }
        }

        // Comprovar robots.txt
        $robotsPath = $basePath . '/robots.txt';
        $robots_txt = null;
        if (file_exists($robotsPath)) {
            $robots_txt = file_get_contents($robotsPath);
            $urlRel = $this->getUrlRelativa();
            if ($urlRel && preg_match_all('/Disallow:\s*(.+)/i', $robots_txt, $m)) {
                foreach ($m[1] as $dis) {
                    $dis = trim($dis);
                    if ($dis === '' || $dis === '/') continue;
                    // Simple check: si la ruta de la pàgina comença pel patró disallow
                    if (strpos($urlRel, $dis) === 0) {
                        $issues[] = "robots.txt conté Disallow que afecta a la pàgina: $dis";
                    }
                }
            }
        }

        return ['ok' => empty($issues), 'issues' => $issues, 'robots_txt' => $robots_txt, 'sitemap_contains' => $sitemap_contains];
    }
}
