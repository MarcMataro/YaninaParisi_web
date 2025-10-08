<?php
/**
 * Classe SEO_Global
 * 
 * Gestiona la configuració SEO global del lloc web, incloent meta tags per defecte,
 * schema markup, perfils socials, configuració tècnica i internacionalització.
 * Aquesta classe proporciona una interfície per treballar amb la taula `seo_global`
 * que emmagatzema tots els paràmetres SEO aplicables a nivell de lloc complet.
 * 
 * @package     YaninaParisi
 * @subpackage  Classes
 * @category    SEO
 * @author      Marc Mataró
 * @version     1.0.0
 * @since       2025-10-07
 */

require_once __DIR__ . '/connexio.php';

class SEO_Global {
    
    // ============================================
    // PROPIETATS PRIVADES
    // ============================================
    
    /**
     * @var int|null ID del registre global (normalment sempre serà 1)
     */
    private $id_global;
    
    /**
     * @var Connexio Instància de la connexió a la base de dades
     */
    private $conn;
    
    /**
     * @var PDO Objecte PDO per a consultes
     */
    private $pdo;
    
    // ============================================
    // 1. SITE-WIDE META TAGS
    // ============================================
    
    /**
     * @var string Títol del lloc en català (màx 60 caràcters)
     */
    private $site_title_ca;
    
    /**
     * @var string Títol del lloc en espanyol (màx 60 caràcters)
     */
    private $site_title_es;
    
    /**
     * @var string Descripció del lloc en català (màx 160 caràcters)
     */
    private $site_description_ca;
    
    /**
     * @var string Descripció del lloc en espanyol (màx 160 caràcters)
     */
    private $site_description_es;
    
    // ============================================
    // 2. DEFAULT META TEMPLATES
    // ============================================
    
    /**
     * @var string Plantilla per títols de pàgina en català. Ex: "{page} | Psicòloga Yanina Parisi"
     */
    private $default_title_template_ca;
    
    /**
     * @var string Plantilla per títols de pàgina en espanyol. Ex: "{page} | Psicóloga Yanina Parisi"
     */
    private $default_title_template_es;
    
    /**
     * @var string Plantilla per meta descriptions en català
     */
    private $default_meta_template_ca;
    
    /**
     * @var string Plantilla per meta descriptions en espanyol
     */
    private $default_meta_template_es;
    
    // ============================================
    // 3. GLOBAL SCHEMA MARKUP
    // ============================================
    
    /**
     * @var string|null JSON amb dades d'organització (Schema.org Organization)
     */
    private $organization_schema;
    
    /**
     * @var string|null JSON amb dades de negoci local (Schema.org LocalBusiness)
     */
    private $local_business_schema;
    
    /**
     * @var string|null JSON amb dades de persona (Schema.org Person - Yanina Parisi)
     */
    private $person_schema;
    
    // ============================================
    // 4. SOCIAL PROFILES GLOBALES
    // ============================================
    
    /**
     * @var string|null URL del perfil de Facebook
     */
    private $facebook_url;
    
    /**
     * @var string|null URL del perfil de Twitter/X
     */
    private $twitter_url;
    
    /**
     * @var string|null URL del perfil de LinkedIn
     */
    private $linkedin_url;
    
    /**
     * @var string|null URL del perfil d'Instagram
     */
    private $instagram_url;
    
    /**
     * @var string|null URL de Google My Business
     */
    private $google_business_url;
    
    // ============================================
    // 5. GLOBAL OPEN GRAPH
    // ============================================
    
    /**
     * @var string Nom del lloc per Open Graph (Facebook, LinkedIn)
     */
    private $og_site_name;
    
    /**
     * @var string Locale d'Open Graph en català (ex: ca_ES)
     */
    private $og_locale_ca;
    
    /**
     * @var string Locale d'Open Graph en espanyol (ex: es_ES)
     */
    private $og_locale_es;
    
    /**
     * @var string|null Imatge per defecte d'Open Graph (1200x630px recomanat)
     */
    private $default_og_image;
    
    // ============================================
    // 6. GLOBAL TWITTER CARD
    // ============================================
    
    /**
     * @var string|null @username del lloc a Twitter/X
     */
    private $twitter_site;
    
    /**
     * @var string|null @username del creador de contingut a Twitter/X
     */
    private $twitter_creator;
    
    /**
     * @var string|null Imatge per defecte de Twitter Card (1200x675px recomanat)
     */
    private $default_twitter_image;
    
    // ============================================
    // 7. TECHNICAL SEO GLOBAL
    // ============================================
    
    /**
     * @var string|null Directiva meta robots per defecte (ex: "index, follow")
     */
    private $default_meta_robots;
    
    /**
     * @var string|null Codi de verificació de Google Search Console
     */
    private $google_site_verification;
    
    /**
     * @var string|null Codi de verificació de Bing Webmaster Tools
     */
    private $bing_verification;
    
    /**
     * @var string|null ID de Google Analytics (ex: G-XXXXXXXXXX)
     */
    private $google_analytics_id;
    
    /**
     * @var string|null ID de Google Tag Manager (ex: GTM-XXXXXX)
     */
    private $google_tag_manager_id;
    
    // ============================================
    // 8. STRUCTURED DATA GLOBAL
    // ============================================
    
    /**
     * @var string Text "Inici" per breadcrumbs en català
     */
    private $breadcrumb_home_text_ca;
    
    /**
     * @var string Text "Inicio" per breadcrumbs en espanyol
     */
    private $breadcrumb_home_text_es;
    
    /**
     * @var string|null JSON amb schema WebSite (Schema.org)
     */
    private $website_schema;
    
    /**
     * @var string|null JSON amb schema WebPage (Schema.org)
     */
    private $webpage_schema;
    
    // ============================================
    // 9. INTERNATIONAL SEO
    // ============================================
    
    /**
     * @var string Idioma per defecte per hreflang (ex: "ca", "es")
     */
    private $hreflang_default;
    
    /**
     * @var string|null JSON amb idiomes alternatius [{lang: "es", url: "..."}]
     */
    private $hreflang_alternates;
    
    // ============================================
    // 10. PERFORMANCE GLOBAL
    // ============================================
    
    /**
     * @var string Prioritat per defecte en sitemap.xml (0.2 - 1.0)
     */
    private $default_priority;
    
    /**
     * @var string Freqüència de canvi per defecte en sitemap.xml
     */
    private $default_changefreq;
    
    // ============================================
    // 11. CONTROL
    // ============================================
    
    /**
     * @var string Data i hora de l'última actualització
     */
    private $fecha_actualizacion;
    
    
    // ============================================
    // CONSTRUCTOR I DESTRUCTOR
    // ============================================
    
    /**
     * Constructor de la classe SEO_Global
     * 
     * Inicialitza la connexió a la base de dades i, si es proporciona un ID,
     * carrega les dades de configuració SEO global.
     * 
     * @param int|null $id_global ID del registre (normalment 1, ja que només n'hi ha un)
     * @throws Exception Si hi ha error de connexió a la base de dades
     */
    public function __construct($id_global = null) {
        try {
            $this->conn = Connexio::getInstance();
            $this->pdo = $this->conn->getConnexio();
            
            if ($id_global !== null) {
                $this->id_global = $id_global;
                $this->carregarDades();
            }
        } catch (Exception $e) {
            throw new Exception("Error al inicialitzar SEO_Global: " . $e->getMessage());
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
     * Carrega les dades de configuració SEO global des de la base de dades
     * 
     * Recupera tots els camps de la taula seo_global per l'ID especificat
     * i els assigna a les propietats privades de l'objecte.
     * 
     * @return bool True si les dades s'han carregat correctament, False si no existeix el registre
     * @throws Exception Si hi ha error en la consulta SQL
     */
    private function carregarDades() {
        try {
            $sql = "SELECT * FROM seo_global WHERE id_global = :id_global LIMIT 1";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':id_global', $this->id_global, PDO::PARAM_INT);
            $stmt->execute();
            
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($row) {
                // 1. Site-wide meta tags
                $this->site_title_ca = $row['site_title_ca'];
                $this->site_title_es = $row['site_title_es'];
                $this->site_description_ca = $row['site_description_ca'];
                $this->site_description_es = $row['site_description_es'];
                
                // 2. Default meta templates
                $this->default_title_template_ca = $row['default_title_template_ca'];
                $this->default_title_template_es = $row['default_title_template_es'];
                $this->default_meta_template_ca = $row['default_meta_template_ca'];
                $this->default_meta_template_es = $row['default_meta_template_es'];
                
                // 3. Global schema markup
                $this->organization_schema = $row['organization_schema'];
                $this->local_business_schema = $row['local_business_schema'];
                $this->person_schema = $row['person_schema'];
                
                // 4. Social profiles
                $this->facebook_url = $row['facebook_url'];
                $this->twitter_url = $row['twitter_url'];
                $this->linkedin_url = $row['linkedin_url'];
                $this->instagram_url = $row['instagram_url'];
                $this->google_business_url = $row['google_business_url'];
                
                // 5. Global Open Graph
                $this->og_site_name = $row['og_site_name'];
                $this->og_locale_ca = $row['og_locale_ca'];
                $this->og_locale_es = $row['og_locale_es'];
                $this->default_og_image = $row['default_og_image'];
                
                // 6. Global Twitter Card
                $this->twitter_site = $row['twitter_site'];
                $this->twitter_creator = $row['twitter_creator'];
                $this->default_twitter_image = $row['default_twitter_image'];
                
                // 7. Technical SEO
                $this->default_meta_robots = $row['default_meta_robots'];
                $this->google_site_verification = $row['google_site_verification'];
                $this->bing_verification = $row['bing_verification'];
                $this->google_analytics_id = $row['google_analytics_id'];
                $this->google_tag_manager_id = $row['google_tag_manager_id'];
                
                // 8. Structured data
                $this->breadcrumb_home_text_ca = $row['breadcrumb_home_text_ca'];
                $this->breadcrumb_home_text_es = $row['breadcrumb_home_text_es'];
                $this->website_schema = $row['website_schema'];
                $this->webpage_schema = $row['webpage_schema'];
                
                // 9. International SEO
                $this->hreflang_default = $row['hreflang_default'];
                $this->hreflang_alternates = $row['hreflang_alternates'];
                
                // 10. Performance
                $this->default_priority = $row['default_priority'];
                $this->default_changefreq = $row['default_changefreq'];
                
                // 11. Control
                $this->fecha_actualizacion = $row['fecha_actualizacion'];
                
                return true;
            }
            
            return false;
            
        } catch (PDOException $e) {
            throw new Exception("Error al carregar dades SEO globals: " . $e->getMessage());
        }
    }
    
    /**
     * Carrega la configuració SEO global principal (ID = 1)
     * 
     * Mètode estàtic que retorna una instància de SEO_Global amb la configuració
     * principal del lloc web (normalment només existeix un registre amb ID = 1).
     * 
     * @return SEO_Global|null Objecte amb la configuració global o null si no existeix
     */
    public static function carregarConfiguracio() {
        try {
            $seo = new self(1);
            return $seo->id_global ? $seo : null;
        } catch (Exception $e) {
            error_log("Error en carregarConfiguracio: " . $e->getMessage());
            return null;
        }
    }
    
    
    // ============================================
    // GETTERS - SITE-WIDE META TAGS
    // ============================================
    
    /**
     * Obté el títol del lloc en l'idioma especificat
     * 
     * @param string $lang Idioma (ca|es). Per defecte: 'ca'
     * @return string Títol del lloc
     */
    public function getSiteTitle($lang = 'ca') {
        return ($lang === 'es') ? $this->site_title_es : $this->site_title_ca;
    }
    
    /**
     * Obté la descripció del lloc en l'idioma especificat
     * 
     * @param string $lang Idioma (ca|es). Per defecte: 'ca'
     * @return string Descripció del lloc
     */
    public function getSiteDescription($lang = 'ca') {
        return ($lang === 'es') ? $this->site_description_es : $this->site_description_ca;
    }
    
    
    // ============================================
    // GETTERS - DEFAULT META TEMPLATES
    // ============================================
    
    /**
     * Genera un títol de pàgina utilitzant la plantilla per defecte
     * 
     * Substitueix {page} per el nom de la pàgina proporcionat.
     * Ex: "Inici | Psicòloga Yanina Parisi"
     * 
     * @param string $page_name Nom de la pàgina
     * @param string $lang Idioma (ca|es). Per defecte: 'ca'
     * @return string Títol generat
     */
    public function generarTitolPagina($page_name, $lang = 'ca') {
        $template = ($lang === 'es') ? $this->default_title_template_es : $this->default_title_template_ca;
        
        // Si no hi ha plantilla, retornar string buit
        if ($template === null || $template === '') {
            return '';
        }
        
        return str_replace('{page}', $page_name, $template);
    }
    
    /**
     * Genera una meta description utilitzant la plantilla per defecte
     * 
     * @param array $variables Array associatiu amb variables per substituir en la plantilla
     * @param string $lang Idioma (ca|es). Per defecte: 'ca'
     * @return string Meta description generada
     */
    public function generarMetaDescription($variables = [], $lang = 'ca') {
        $template = ($lang === 'es') ? $this->default_meta_template_es : $this->default_meta_template_ca;
        
        // Si no hi ha plantilla, retornar string buit
        if ($template === null || $template === '') {
            return '';
        }
        
        foreach ($variables as $key => $value) {
            $template = str_replace('{' . $key . '}', $value, $template);
        }
        
        return $template;
    }
    
    
    // ============================================
    // GETTERS - SCHEMA MARKUP
    // ============================================
    
    /**
     * Obté el schema d'organització decodificat
     * 
     * @return array|null Array amb dades de Schema.org Organization o null
     */
    public function getOrganizationSchema() {
        return $this->organization_schema ? json_decode($this->organization_schema, true) : null;
    }
    
    /**
     * Obté el schema de negoci local decodificat
     * 
     * @return array|null Array amb dades de Schema.org LocalBusiness o null
     */
    public function getLocalBusinessSchema() {
        return $this->local_business_schema ? json_decode($this->local_business_schema, true) : null;
    }
    
    /**
     * Obté el schema de persona decodificat
     * 
     * @return array|null Array amb dades de Schema.org Person o null
     */
    public function getPersonSchema() {
        return $this->person_schema ? json_decode($this->person_schema, true) : null;
    }
    
    /**
     * Genera el JSON-LD per inserir al <head> amb tots els schemas globals
     * 
     * @return string JSON-LD formatat per inserir directament al HTML
     */
    public function generarSchemaJSONLD() {
        $schemas = [];
        
        if ($org = $this->getOrganizationSchema()) {
            $schemas[] = $org;
        }
        if ($local = $this->getLocalBusinessSchema()) {
            $schemas[] = $local;
        }
        if ($person = $this->getPersonSchema()) {
            $schemas[] = $person;
        }
        
        if (empty($schemas)) {
            return '';
        }
        
        $jsonld = '<script type="application/ld+json">' . "\n";
        $jsonld .= json_encode($schemas, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $jsonld .= "\n</script>";
        
        return $jsonld;
    }
    
    
    // ============================================
    // GETTERS - SOCIAL PROFILES
    // ============================================
    
    /**
     * Obté la URL del perfil de Facebook
     * 
     * @return string|null URL de Facebook
     */
    public function getFacebookUrl() {
        return $this->facebook_url;
    }
    
    /**
     * Obté la URL del perfil de Twitter/X
     * 
     * @return string|null URL de Twitter
     */
    public function getTwitterUrl() {
        return $this->twitter_url;
    }
    
    /**
     * Obté la URL del perfil de LinkedIn
     * 
     * @return string|null URL de LinkedIn
     */
    public function getLinkedInUrl() {
        return $this->linkedin_url;
    }
    
    /**
     * Obté la URL del perfil d'Instagram
     * 
     * @return string|null URL d'Instagram
     */
    public function getInstagramUrl() {
        return $this->instagram_url;
    }
    
    /**
     * Obté la URL de Google My Business
     * 
     * @return string|null URL de Google Business
     */
    public function getGoogleBusinessUrl() {
        return $this->google_business_url;
    }
    
    /**
     * Obté totes les URLs de xarxes socials com array
     * 
     * @return array Array amb les URLs de xarxes socials
     */
    public function getSocialProfiles() {
        return [
            'facebook' => $this->facebook_url,
            'twitter' => $this->twitter_url,
            'linkedin' => $this->linkedin_url,
            'instagram' => $this->instagram_url,
            'google_business' => $this->google_business_url
        ];
    }
    
    
    // ============================================
    // GETTERS - OPEN GRAPH
    // ============================================
    
    /**
     * Obté el nom del lloc per Open Graph
     * 
     * @return string Nom del lloc
     */
    public function getOgSiteName() {
        return $this->og_site_name;
    }
    
    /**
     * Obté el locale d'Open Graph segons l'idioma
     * 
     * @param string $lang Idioma (ca|es). Per defecte: 'ca'
     * @return string Locale d'Open Graph (ex: ca_ES, es_ES)
     */
    public function getOgLocale($lang = 'ca') {
        return ($lang === 'es') ? $this->og_locale_es : $this->og_locale_ca;
    }
    
    /**
     * Obté la imatge per defecte d'Open Graph
     * 
     * @return string|null URL de la imatge
     */
    public function getDefaultOgImage() {
        return $this->default_og_image;
    }
    
    /**
     * Genera les meta tags d'Open Graph per una pàgina
     * 
     * @param array $params Paràmetres: title, description, image, url, type, lang
     * @return string HTML amb meta tags d'Open Graph
     */
    public function generarMetaTagsOG($params = []) {
        $lang = $params['lang'] ?? 'ca';
        $title = $params['title'] ?? $this->getSiteTitle($lang);
        $description = $params['description'] ?? $this->getSiteDescription($lang);
        $image = $params['image'] ?? $this->default_og_image;
        $url = $params['url'] ?? '';
        $type = $params['type'] ?? 'website';
        
        $html = '';
        $html .= '<meta property="og:site_name" content="' . htmlspecialchars($this->og_site_name) . '">' . "\n";
        $html .= '<meta property="og:title" content="' . htmlspecialchars($title) . '">' . "\n";
        $html .= '<meta property="og:description" content="' . htmlspecialchars($description) . '">' . "\n";
        $html .= '<meta property="og:type" content="' . $type . '">' . "\n";
        $html .= '<meta property="og:locale" content="' . $this->getOgLocale($lang) . '">' . "\n";
        
        if ($url) {
            $html .= '<meta property="og:url" content="' . htmlspecialchars($url) . '">' . "\n";
        }
        
        if ($image) {
            $html .= '<meta property="og:image" content="' . htmlspecialchars($image) . '">' . "\n";
            $html .= '<meta property="og:image:width" content="1200">' . "\n";
            $html .= '<meta property="og:image:height" content="630">' . "\n";
        }
        
        return $html;
    }
    
    
    // ============================================
    // GETTERS - TWITTER CARD
    // ============================================
    
    /**
     * Obté el @username del lloc a Twitter
     * 
     * @return string|null Username de Twitter
     */
    public function getTwitterSite() {
        return $this->twitter_site;
    }
    
    /**
     * Obté el @username del creador a Twitter
     * 
     * @return string|null Username del creador
     */
    public function getTwitterCreator() {
        return $this->twitter_creator;
    }
    
    /**
     * Obté la imatge per defecte de Twitter Card
     * 
     * @return string|null URL de la imatge
     */
    public function getDefaultTwitterImage() {
        return $this->default_twitter_image;
    }
    
    /**
     * Genera les meta tags de Twitter Card per una pàgina
     * 
     * @param array $params Paràmetres: title, description, image, card
     * @return string HTML amb meta tags de Twitter Card
     */
    public function generarMetaTagsTwitter($params = []) {
        $title = $params['title'] ?? $this->getSiteTitle();
        $description = $params['description'] ?? $this->getSiteDescription();
        $image = $params['image'] ?? $this->default_twitter_image;
        $card = $params['card'] ?? 'summary_large_image';
        
        $html = '';
        $html .= '<meta name="twitter:card" content="' . $card . '">' . "\n";
        $html .= '<meta name="twitter:title" content="' . htmlspecialchars($title) . '">' . "\n";
        $html .= '<meta name="twitter:description" content="' . htmlspecialchars($description) . '">' . "\n";
        
        if ($this->twitter_site) {
            $html .= '<meta name="twitter:site" content="' . htmlspecialchars($this->twitter_site) . '">' . "\n";
        }
        
        if ($this->twitter_creator) {
            $html .= '<meta name="twitter:creator" content="' . htmlspecialchars($this->twitter_creator) . '">' . "\n";
        }
        
        if ($image) {
            $html .= '<meta name="twitter:image" content="' . htmlspecialchars($image) . '">' . "\n";
        }
        
        return $html;
    }
    
    
    // ============================================
    // GETTERS - TECHNICAL SEO
    // ============================================
    
    /**
     * Obté la directiva meta robots per defecte
     * 
     * @return string|null Directiva robots (ex: "index, follow")
     */
    public function getDefaultMetaRobots() {
        return $this->default_meta_robots;
    }
    
    /**
     * Obté el codi de verificació de Google Search Console
     * 
     * @return string|null Codi de verificació
     */
    public function getGoogleSiteVerification() {
        return $this->google_site_verification;
    }
    
    /**
     * Obté el codi de verificació de Bing Webmaster Tools
     * 
     * @return string|null Codi de verificació
     */
    public function getBingVerification() {
        return $this->bing_verification;
    }
    
    /**
     * Obté l'ID de Google Analytics
     * 
     * @return string|null ID de Google Analytics
     */
    public function getGoogleAnalyticsId() {
        return $this->google_analytics_id;
    }
    
    /**
     * Obté l'ID de Google Tag Manager
     * 
     * @return string|null ID de Google Tag Manager
     */
    public function getGoogleTagManagerId() {
        return $this->google_tag_manager_id;
    }
    
    /**
     * Genera les meta tags de verificació per inserir al <head>
     * 
     * @return string HTML amb meta tags de verificació
     */
    public function generarMetaTagsVerificacio() {
        $html = '';
        
        if ($this->google_site_verification) {
            $html .= '<meta name="google-site-verification" content="' . htmlspecialchars($this->google_site_verification) . '">' . "\n";
        }
        
        if ($this->bing_verification) {
            $html .= '<meta name="msvalidate.01" content="' . htmlspecialchars($this->bing_verification) . '">' . "\n";
        }
        
        return $html;
    }
    
    /**
     * Genera el codi de Google Analytics per inserir al <head>
     * 
     * @return string Codi JavaScript de Google Analytics
     */
    public function generarCodiGoogleAnalytics() {
        if (!$this->google_analytics_id) {
            return '';
        }
        
        $html = "<!-- Google Analytics -->\n";
        $html .= "<script async src=\"https://www.googletagmanager.com/gtag/js?id={$this->google_analytics_id}\"></script>\n";
        $html .= "<script>\n";
        $html .= "  window.dataLayer = window.dataLayer || [];\n";
        $html .= "  function gtag(){dataLayer.push(arguments);}\n";
        $html .= "  gtag('js', new Date());\n";
        $html .= "  gtag('config', '{$this->google_analytics_id}');\n";
        $html .= "</script>\n";
        
        return $html;
    }
    
    /**
     * Genera el codi de Google Tag Manager per inserir al <head>
     * 
     * @return string Codi JavaScript de Google Tag Manager
     */
    public function generarCodiGTM() {
        if (!$this->google_tag_manager_id) {
            return '';
        }
        
        $html = "<!-- Google Tag Manager -->\n";
        $html .= "<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':\n";
        $html .= "new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],\n";
        $html .= "j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=\n";
        $html .= "'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);\n";
        $html .= "})(window,document,'script','dataLayer','{$this->google_tag_manager_id}');</script>\n";
        $html .= "<!-- End Google Tag Manager -->\n";
        
        return $html;
    }
    
    
    // ============================================
    // GETTERS - STRUCTURED DATA
    // ============================================
    
    /**
     * Obté el text d'inici per breadcrumbs segons l'idioma
     * 
     * @param string $lang Idioma (ca|es). Per defecte: 'ca'
     * @return string Text d'inici ("Inici" o "Inicio")
     */
    public function getBreadcrumbHomeText($lang = 'ca') {
        return ($lang === 'es') ? $this->breadcrumb_home_text_es : $this->breadcrumb_home_text_ca;
    }
    
    /**
     * Obté el schema WebSite decodificat
     * 
     * @return array|null Array amb dades de Schema.org WebSite o null
     */
    public function getWebsiteSchema() {
        return $this->website_schema ? json_decode($this->website_schema, true) : null;
    }
    
    /**
     * Obté el schema WebPage decodificat
     * 
     * @return array|null Array amb dades de Schema.org WebPage o null
     */
    public function getWebpageSchema() {
        return $this->webpage_schema ? json_decode($this->webpage_schema, true) : null;
    }
    
    
    // ============================================
    // GETTERS - INTERNATIONAL SEO
    // ============================================
    
    /**
     * Obté l'idioma per defecte per hreflang
     * 
     * @return string Codi d'idioma (ex: "ca", "es")
     */
    public function getHreflangDefault() {
        return $this->hreflang_default;
    }
    
    /**
     * Obté els idiomes alternatius per hreflang
     * 
     * @return array|null Array d'idiomes alternatius o null
     */
    public function getHreflangAlternates() {
        return $this->hreflang_alternates ? json_decode($this->hreflang_alternates, true) : null;
    }
    
    /**
     * Genera les etiquetes link hreflang per inserir al <head>
     * 
     * @param string $current_url URL de la pàgina actual
     * @return string HTML amb etiquetes link hreflang
     */
    public function generarHreflangTags($current_url) {
        $html = '';
        
        // Tag per l'idioma actual
        $html .= '<link rel="alternate" hreflang="' . $this->hreflang_default . '" href="' . htmlspecialchars($current_url) . '">' . "\n";
        
        // Tags per idiomes alternatius
        if ($alternates = $this->getHreflangAlternates()) {
            foreach ($alternates as $alternate) {
                $html .= '<link rel="alternate" hreflang="' . $alternate['lang'] . '" href="' . htmlspecialchars($alternate['url']) . '">' . "\n";
            }
        }
        
        // Tag x-default
        $html .= '<link rel="alternate" hreflang="x-default" href="' . htmlspecialchars($current_url) . '">' . "\n";
        
        return $html;
    }
    
    
    // ============================================
    // GETTERS - PERFORMANCE
    // ============================================
    
    /**
     * Obté la prioritat per defecte per sitemap.xml
     * 
     * @return string Prioritat (0.2 - 1.0)
     */
    public function getDefaultPriority() {
        return $this->default_priority;
    }
    
    /**
     * Obté la freqüència de canvi per defecte per sitemap.xml
     * 
     * @return string Freqüència (always, hourly, daily, weekly, monthly, yearly, never)
     */
    public function getDefaultChangefreq() {
        return $this->default_changefreq;
    }
    
    
    // ============================================
    // MÈTODES D'ACTUALITZACIÓ
    // ============================================
    
    /**
     * Actualitza un camp específic de la configuració SEO global
     * 
     * @param string $field Nom del camp a actualitzar
     * @param mixed $value Valor nou
     * @return bool True si l'actualització ha estat correcta
     * @throws Exception Si el camp no existeix o hi ha error SQL
     */
    public function actualitzarCamp($field, $value) {
        try {
            // Validar que el camp existeix
            $allowed_fields = [
                'site_title_ca', 'site_title_es', 'site_description_ca', 'site_description_es',
                'default_title_template_ca', 'default_title_template_es', 
                'default_meta_template_ca', 'default_meta_template_es',
                'organization_schema', 'local_business_schema', 'person_schema',
                'facebook_url', 'twitter_url', 'linkedin_url', 'instagram_url', 'google_business_url',
                'og_site_name', 'og_locale_ca', 'og_locale_es', 'default_og_image',
                'twitter_site', 'twitter_creator', 'default_twitter_image',
                'default_meta_robots', 'google_site_verification', 'bing_verification',
                'google_analytics_id', 'google_tag_manager_id',
                'breadcrumb_home_text_ca', 'breadcrumb_home_text_es', 
                'website_schema', 'webpage_schema',
                'hreflang_default', 'hreflang_alternates',
                'default_priority', 'default_changefreq'
            ];
            
            if (!in_array($field, $allowed_fields)) {
                throw new Exception("Camp '$field' no és vàlid per actualitzar");
            }
            
            $sql = "UPDATE seo_global SET $field = :value WHERE id_global = :id_global";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':value', $value);
            $stmt->bindParam(':id_global', $this->id_global, PDO::PARAM_INT);
            
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
     * Actualitza múltiples camps de la configuració SEO global
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
    // MÈTODES ÚTILS
    // ============================================
    
    /**
     * Genera totes les meta tags necessàries per al <head> d'una pàgina
     * 
     * Aquest mètode genera un bloc HTML complet amb:
     * - Meta tags bàsiques (title, description, robots)
     * - Open Graph tags
     * - Twitter Card tags
     * - Verificació de cercadors
     * - Hreflang tags
     * - Schema JSON-LD
     * 
     * @param array $params Paràmetres de la pàgina: page_name, description, image, url, lang
     * @return string HTML complet per inserir al <head>
     */
    public function generarHeadComplet($params = []) {
        $lang = $params['lang'] ?? 'ca';
        $page_name = $params['page_name'] ?? '';
        $title = $page_name ? $this->generarTitolPagina($page_name, $lang) : $this->getSiteTitle($lang);
        $description = $params['description'] ?? $this->getSiteDescription($lang);
        $image = $params['image'] ?? null;
        $url = $params['url'] ?? '';
        
        $html = "\n<!-- SEO Meta Tags -->\n";
        $html .= '<title>' . htmlspecialchars($title) . '</title>' . "\n";
        $html .= '<meta name="description" content="' . htmlspecialchars($description) . '">' . "\n";
        
        if ($this->default_meta_robots) {
            $html .= '<meta name="robots" content="' . $this->default_meta_robots . '">' . "\n";
        }
        
        $html .= "\n<!-- Verificació Cercadors -->\n";
        $html .= $this->generarMetaTagsVerificacio();
        
        $html .= "\n<!-- Open Graph -->\n";
        $html .= $this->generarMetaTagsOG([
            'title' => $title,
            'description' => $description,
            'image' => $image,
            'url' => $url,
            'lang' => $lang
        ]);
        
        $html .= "\n<!-- Twitter Card -->\n";
        $html .= $this->generarMetaTagsTwitter([
            'title' => $title,
            'description' => $description,
            'image' => $image
        ]);
        
        if ($url) {
            $html .= "\n<!-- Hreflang -->\n";
            $html .= $this->generarHreflangTags($url);
        }
        
        $html .= "\n<!-- Schema.org JSON-LD -->\n";
        $html .= $this->generarSchemaJSONLD();
        
        $html .= "\n<!-- Google Analytics -->\n";
        $html .= $this->generarCodiGoogleAnalytics();
        
        $html .= "\n<!-- Google Tag Manager -->\n";
        $html .= $this->generarCodiGTM();
        
        return $html;
    }
    
    /**
     * Retorna tota la configuració SEO com array associatiu
     * 
     * Útil per debugging o per enviar dades a altres sistemes.
     * 
     * @return array Array amb tota la configuració SEO
     */
    public function toArray() {
        return [
            'id_global' => $this->id_global,
            'site_title' => [
                'ca' => $this->site_title_ca,
                'es' => $this->site_title_es
            ],
            'site_description' => [
                'ca' => $this->site_description_ca,
                'es' => $this->site_description_es
            ],
            'templates' => [
                'title_ca' => $this->default_title_template_ca,
                'title_es' => $this->default_title_template_es,
                'meta_ca' => $this->default_meta_template_ca,
                'meta_es' => $this->default_meta_template_es
            ],
            'schemas' => [
                'organization' => $this->getOrganizationSchema(),
                'local_business' => $this->getLocalBusinessSchema(),
                'person' => $this->getPersonSchema(),
                'website' => $this->getWebsiteSchema(),
                'webpage' => $this->getWebpageSchema()
            ],
            'social' => $this->getSocialProfiles(),
            'open_graph' => [
                'site_name' => $this->og_site_name,
                'locale_ca' => $this->og_locale_ca,
                'locale_es' => $this->og_locale_es,
                'default_image' => $this->default_og_image
            ],
            'twitter' => [
                'site' => $this->twitter_site,
                'creator' => $this->twitter_creator,
                'default_image' => $this->default_twitter_image
            ],
            'technical' => [
                'robots' => $this->default_meta_robots,
                'google_verification' => $this->google_site_verification,
                'bing_verification' => $this->bing_verification,
                'ga_id' => $this->google_analytics_id,
                'gtm_id' => $this->google_tag_manager_id
            ],
            'international' => [
                'default_lang' => $this->hreflang_default,
                'alternates' => $this->getHreflangAlternates()
            ],
            'performance' => [
                'priority' => $this->default_priority,
                'changefreq' => $this->default_changefreq
            ],
            'fecha_actualizacion' => $this->fecha_actualizacion
        ];
    }
    
    /**
     * Calcula la puntuació de la configuració SEO Global (0-100)
     * 
     * Avalua el grau de completitud i qualitat de la configuració SEO global del lloc.
     * La puntuació es basa en:
     * - Completitud de meta tags bilingües (15 punts)
     * - Templates configurats correctament (10 punts)
     * - Schema.org markup configurat (15 punts)
     * - Xarxes socials configurades (10 punts)
     * - Open Graph configurat (10 punts)
     * - Twitter Cards configurat (10 punts)
     * - Analytics i verificacions (15 punts)
     * - Configuració internacional (10 punts)
     * - Configuració de rendiment (5 punts)
     * 
     * @return array Array amb 'score' (0-100) i 'detalles' (array de seccions)
     */
    public function calcularPuntuacioConfiguracio() {
        $puntuacio = 0;
        $maxim = 100;
        $detalls = [];
        
        // 1. Site-wide Meta Tags (15 punts)
        $meta_score = 0;
        if ($this->site_title_ca && mb_strlen($this->site_title_ca) >= 30 && mb_strlen($this->site_title_ca) <= 60) {
            $meta_score += 4;
        } elseif ($this->site_title_ca) {
            $meta_score += 2;
        }
        
        if ($this->site_title_es && mb_strlen($this->site_title_es) >= 30 && mb_strlen($this->site_title_es) <= 60) {
            $meta_score += 4;
        } elseif ($this->site_title_es) {
            $meta_score += 2;
        }
        
        if ($this->site_description_ca && mb_strlen($this->site_description_ca) >= 120 && mb_strlen($this->site_description_ca) <= 160) {
            $meta_score += 4;
        } elseif ($this->site_description_ca) {
            $meta_score += 2;
        }
        
        if ($this->site_description_es && mb_strlen($this->site_description_es) >= 120 && mb_strlen($this->site_description_es) <= 160) {
            $meta_score += 3;
        } elseif ($this->site_description_es) {
            $meta_score += 1;
        }
        
        $puntuacio += $meta_score;
        $detalls['meta_tags'] = [
            'puntuacio' => $meta_score,
            'maxim' => 15,
            'percentatge' => round(($meta_score / 15) * 100)
        ];
        
        // 2. Templates (10 punts)
        $template_score = 0;
        if ($this->default_title_template_ca && strpos($this->default_title_template_ca, '{page}') !== false) {
            $template_score += 3;
        }
        if ($this->default_title_template_es && strpos($this->default_title_template_es, '{page}') !== false) {
            $template_score += 2;
        }
        if ($this->default_meta_template_ca && strpos($this->default_meta_template_ca, '{page}') !== false) {
            $template_score += 3;
        }
        if ($this->default_meta_template_es && strpos($this->default_meta_template_es, '{page}') !== false) {
            $template_score += 2;
        }
        
        $puntuacio += $template_score;
        $detalls['templates'] = [
            'puntuacio' => $template_score,
            'maxim' => 10,
            'percentatge' => round(($template_score / 10) * 100)
        ];
        
        // 3. Schema Markup (15 punts)
        $schema_score = 0;
        if ($this->organization_schema) {
            $schema_score += 5;
        }
        if ($this->local_business_schema) {
            $schema_score += 5;
        }
        if ($this->person_schema) {
            $schema_score += 5;
        }
        
        $puntuacio += $schema_score;
        $detalls['schema'] = [
            'puntuacio' => $schema_score,
            'maxim' => 15,
            'percentatge' => round(($schema_score / 15) * 100)
        ];
        
        // 4. Xarxes Socials (10 punts)
        $social_score = 0;
        if ($this->facebook_url && filter_var($this->facebook_url, FILTER_VALIDATE_URL)) {
            $social_score += 2;
        }
        if ($this->twitter_url && filter_var($this->twitter_url, FILTER_VALIDATE_URL)) {
            $social_score += 2;
        }
        if ($this->instagram_url && filter_var($this->instagram_url, FILTER_VALIDATE_URL)) {
            $social_score += 2;
        }
        if ($this->linkedin_url && filter_var($this->linkedin_url, FILTER_VALIDATE_URL)) {
            $social_score += 2;
        }
        if ($this->google_business_url && filter_var($this->google_business_url, FILTER_VALIDATE_URL)) {
            $social_score += 2;
        }
        
        $puntuacio += $social_score;
        $detalls['social'] = [
            'puntuacio' => $social_score,
            'maxim' => 10,
            'percentatge' => round(($social_score / 10) * 100)
        ];
        
        // 5. Open Graph (10 punts)
        $og_score = 0;
        if ($this->og_site_name) {
            $og_score += 3;
        }
        if ($this->og_locale_ca) {
            $og_score += 2;
        }
        if ($this->og_locale_es) {
            $og_score += 2;
        }
        if ($this->default_og_image && filter_var($this->default_og_image, FILTER_VALIDATE_URL)) {
            $og_score += 3;
        }
        
        $puntuacio += $og_score;
        $detalls['open_graph'] = [
            'puntuacio' => $og_score,
            'maxim' => 10,
            'percentatge' => round(($og_score / 10) * 100)
        ];
        
        // 6. Twitter Cards (10 punts)
        $twitter_score = 0;
        if ($this->twitter_site && strpos($this->twitter_site, '@') === 0) {
            $twitter_score += 3;
        }
        if ($this->twitter_creator && strpos($this->twitter_creator, '@') === 0) {
            $twitter_score += 3;
        }
        if ($this->default_twitter_image && filter_var($this->default_twitter_image, FILTER_VALIDATE_URL)) {
            $twitter_score += 4;
        }
        
        $puntuacio += $twitter_score;
        $detalls['twitter'] = [
            'puntuacio' => $twitter_score,
            'maxim' => 10,
            'percentatge' => round(($twitter_score / 10) * 100)
        ];
        
        // 7. Analytics i Verificacions (15 punts)
        $tech_score = 0;
        if ($this->google_analytics_id && preg_match('/^(UA-|G-)/', $this->google_analytics_id)) {
            $tech_score += 5;
        }
        if ($this->google_tag_manager_id && preg_match('/^GTM-/', $this->google_tag_manager_id)) {
            $tech_score += 5;
        }
        if ($this->google_site_verification) {
            $tech_score += 3;
        }
        if ($this->bing_verification) {
            $tech_score += 2;
        }
        
        $puntuacio += $tech_score;
        $detalls['technical'] = [
            'puntuacio' => $tech_score,
            'maxim' => 15,
            'percentatge' => round(($tech_score / 15) * 100)
        ];
        
        // 8. Configuració Internacional (10 punts)
        $intl_score = 0;
        if ($this->hreflang_default) {
            $intl_score += 5;
        }
        if ($this->hreflang_alternates) {
            $alternates = json_decode($this->hreflang_alternates, true);
            if ($alternates && count($alternates) >= 2) {
                $intl_score += 5;
            } elseif ($alternates && count($alternates) >= 1) {
                $intl_score += 3;
            }
        }
        
        $puntuacio += $intl_score;
        $detalls['international'] = [
            'puntuacio' => $intl_score,
            'maxim' => 10,
            'percentatge' => round(($intl_score / 10) * 100)
        ];
        
        // 9. Rendiment i Sitemap (5 punts)
        $perf_score = 0;
        if ($this->default_priority) {
            $perf_score += 2;
        }
        if ($this->default_changefreq) {
            $perf_score += 3;
        }
        
        $puntuacio += $perf_score;
        $detalls['performance'] = [
            'puntuacio' => $perf_score,
            'maxim' => 5,
            'percentatge' => round(($perf_score / 5) * 100)
        ];
        
        // Determinar estat
        $estat = '';
        if ($puntuacio >= 90) {
            $estat = 'Excelente';
        } elseif ($puntuacio >= 75) {
            $estat = 'Muy buena';
        } elseif ($puntuacio >= 60) {
            $estat = 'Buena';
        } elseif ($puntuacio >= 40) {
            $estat = 'Mejorable';
        } else {
            $estat = 'Necesita atención';
        }
        
        return [
            'score' => $puntuacio,
            'maxim' => $maxim,
            'percentatge' => round(($puntuacio / $maxim) * 100),
            'estat' => $estat,
            'detalles' => $detalls
        ];
    }
}
