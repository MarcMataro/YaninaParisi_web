-- ============================================
-- TAULA: seo_global
-- ============================================
-- Descripció: Configuració SEO global del lloc web
-- Autor: Marc Mataró
-- Data: 2025-10-07
-- Versió: 1.0.0
-- ============================================

CREATE TABLE IF NOT EXISTS seo_global (
    id_global INT PRIMARY KEY AUTO_INCREMENT COMMENT 'Identificador únic (normalment 1)',
    
    -- ============================================
    -- 1. SITE-WIDE META TAGS (Bilingüe)
    -- ============================================
    site_title_ca VARCHAR(60) COMMENT 'Títol global del lloc en català',
    site_title_es VARCHAR(60) COMMENT 'Títol global del lloc en espanyol',
    site_description_ca VARCHAR(160) COMMENT 'Descripció global en català',
    site_description_es VARCHAR(160) COMMENT 'Descripció global en espanyol',
    
    -- ============================================
    -- 2. DEFAULT TEMPLATES (Plantilles per pàgines)
    -- ============================================
    default_title_template_ca VARCHAR(100) COMMENT 'Template títol: {page} | Yanina Parisi',
    default_title_template_es VARCHAR(100) COMMENT 'Template títol en espanyol',
    default_meta_template_ca VARCHAR(200) COMMENT 'Template meta description en català',
    default_meta_template_es VARCHAR(200) COMMENT 'Template meta description en espanyol',
    
    -- ============================================
    -- 3. SCHEMA MARKUP (JSON estructurat)
    -- ============================================
    organization_schema JSON COMMENT 'Schema.org Organization JSON-LD',
    local_business_schema JSON COMMENT 'Schema.org LocalBusiness JSON-LD',
    person_schema JSON COMMENT 'Schema.org Person JSON-LD (Yanina)',
    
    -- ============================================
    -- 4. SOCIAL PROFILES (URLs de xarxes socials)
    -- ============================================
    facebook_url VARCHAR(255) COMMENT 'URL del perfil de Facebook',
    twitter_url VARCHAR(255) COMMENT 'URL del perfil de Twitter/X',
    linkedin_url VARCHAR(255) COMMENT 'URL del perfil de LinkedIn',
    instagram_url VARCHAR(255) COMMENT 'URL del perfil d\'Instagram',
    google_business_url VARCHAR(255) COMMENT 'URL de Google My Business',
    
    -- ============================================
    -- 5. GLOBAL OPEN GRAPH (Meta tags socials)
    -- ============================================
    og_site_name VARCHAR(100) COMMENT 'Nom del lloc per Open Graph',
    og_locale_ca VARCHAR(10) DEFAULT 'ca_ES' COMMENT 'Locale català',
    og_locale_es VARCHAR(10) DEFAULT 'es_ES' COMMENT 'Locale espanyol',
    default_og_image VARCHAR(255) COMMENT 'Imatge per defecte OG (1200x630px)',
    
    -- ============================================
    -- 6. GLOBAL TWITTER CARD (Twitter meta tags)
    -- ============================================
    twitter_site VARCHAR(50) COMMENT 'Handle Twitter del lloc (@username)',
    twitter_creator VARCHAR(50) COMMENT 'Handle Twitter de l\'autor (@username)',
    default_twitter_image VARCHAR(255) COMMENT 'Imatge per defecte Twitter Card',
    
    -- ============================================
    -- 7. TECHNICAL SEO (Configuració tècnica)
    -- ============================================
    default_meta_robots VARCHAR(50) DEFAULT 'index, follow' COMMENT 'Directiva robots per defecte',
    google_site_verification VARCHAR(100) COMMENT 'Codi verificació Google Search Console',
    bing_verification VARCHAR(100) COMMENT 'Codi verificació Bing Webmaster',
    google_analytics_id VARCHAR(20) COMMENT 'ID Google Analytics (G-XXXXXXXXXX)',
    google_tag_manager_id VARCHAR(20) COMMENT 'ID Google Tag Manager (GTM-XXXXXXX)',
    
    -- ============================================
    -- 8. BREADCRUMBS (Migues de pa)
    -- ============================================
    breadcrumb_home_text_ca VARCHAR(50) DEFAULT 'Inici' COMMENT 'Text "Inici" en català',
    breadcrumb_home_text_es VARCHAR(50) DEFAULT 'Inicio' COMMENT 'Text "Inicio" en espanyol',
    website_schema JSON COMMENT 'Schema.org WebSite per breadcrumbs',
    webpage_schema JSON COMMENT 'Schema.org WebPage per breadcrumbs',
    
    -- ============================================
    -- 9. INTERNATIONAL SEO (Multiidioma)
    -- ============================================
    hreflang_default ENUM('ca', 'es', 'en') DEFAULT 'ca' COMMENT 'Idioma per defecte',
    hreflang_alternates JSON COMMENT 'Idiomes alternatius i URLs',
    
    -- ============================================
    -- 10. PERFORMANCE & SITEMAP (Optimització)
    -- ============================================
    default_priority ENUM('1.0', '0.8', '0.6', '0.4', '0.2') DEFAULT '0.8' COMMENT 'Prioritat per defecte sitemap',
    default_changefreq ENUM('always', 'hourly', 'daily', 'weekly', 'monthly', 'yearly', 'never') DEFAULT 'monthly' COMMENT 'Freqüència canvi per defecte',
    
    -- ============================================
    -- 11. CONTROL I TEMPORALITAT
    -- ============================================
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Última modificació',
    
    -- ============================================
    -- ÍNDEXS
    -- ============================================
    INDEX idx_fecha_actualizacion (fecha_actualizacion)
    
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Configuració SEO global del lloc web';


-- ============================================
-- INSERT INICIAL: Registre per defecte
-- ============================================

INSERT INTO seo_global (
    id_global,
    
    -- 1. Site-wide meta tags
    site_title_ca,
    site_title_es,
    site_description_ca,
    site_description_es,
    
    -- 2. Default templates
    default_title_template_ca,
    default_title_template_es,
    default_meta_template_ca,
    default_meta_template_es,
    
    -- 3. Schema markup (JSON)
    organization_schema,
    local_business_schema,
    person_schema,
    
    -- 4. Social profiles
    facebook_url,
    twitter_url,
    linkedin_url,
    instagram_url,
    google_business_url,
    
    -- 5. Global Open Graph
    og_site_name,
    og_locale_ca,
    og_locale_es,
    default_og_image,
    
    -- 6. Global Twitter Card
    twitter_site,
    twitter_creator,
    default_twitter_image,
    
    -- 7. Technical SEO
    default_meta_robots,
    google_site_verification,
    bing_verification,
    google_analytics_id,
    google_tag_manager_id,
    
    -- 8. Breadcrumbs
    breadcrumb_home_text_ca,
    breadcrumb_home_text_es,
    website_schema,
    webpage_schema,
    
    -- 9. International SEO
    hreflang_default,
    hreflang_alternates,
    
    -- 10. Performance
    default_priority,
    default_changefreq
    
) VALUES (
    1,
    
    -- 1. Site-wide meta tags
    'Yanina Parisi - Psicòloga Col·legiada a Girona',
    'Yanina Parisi - Psicóloga Colegiada en Girona',
    'Psicòloga col·legiada especialitzada en teràpia de parella, adults i psicologia judicial a Girona. Primera sessió gratuïta.',
    'Psicóloga colegiada especializada en terapia de pareja, adultos y psicología judicial en Girona. Primera sesión gratuita.',
    
    -- 2. Default templates
    '{page} | Yanina Parisi - Psicòloga Girona',
    '{page} | Yanina Parisi - Psicóloga Girona',
    'Descobreix més sobre {page}. Atenció psicològica professional a Girona amb primera sessió gratuïta.',
    'Descubre más sobre {page}. Atención psicológica profesional en Girona con primera sesión gratuita.',
    
    -- 3. Schema markup (JSON) - Organization
    '{
        "@context": "https://schema.org",
        "@type": "Organization",
        "name": "Yanina Parisi - Psicologia",
        "url": "https://www.psicologiayanina.com",
        "logo": "https://www.psicologiayanina.com/img/Logo.png",
        "contactPoint": {
            "@type": "ContactPoint",
            "telephone": "+34-XXX-XXX-XXX",
            "contactType": "customer service",
            "availableLanguage": ["ca", "es"]
        },
        "address": {
            "@type": "PostalAddress",
            "addressLocality": "Girona",
            "addressRegion": "Girona",
            "addressCountry": "ES"
        }
    }',
    
    -- 3. Schema markup (JSON) - LocalBusiness
    '{
        "@context": "https://schema.org",
        "@type": "LocalBusiness",
        "name": "Yanina Parisi - Psicologia",
        "image": "https://www.psicologiayanina.com/img/Logo.png",
        "priceRange": "€€",
        "address": {
            "@type": "PostalAddress",
            "streetAddress": "Carrer Example, 123",
            "addressLocality": "Girona",
            "postalCode": "17001",
            "addressCountry": "ES"
        },
        "geo": {
            "@type": "GeoCoordinates",
            "latitude": 41.9794,
            "longitude": 2.8214
        },
        "openingHoursSpecification": {
            "@type": "OpeningHoursSpecification",
            "dayOfWeek": ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday"],
            "opens": "09:00",
            "closes": "20:00"
        }
    }',
    
    -- 3. Schema markup (JSON) - Person
    '{
        "@context": "https://schema.org",
        "@type": "Person",
        "name": "Yanina Parisi",
        "jobTitle": "Psicòloga Col·legiada",
        "url": "https://www.psicologiayanina.com",
        "image": "https://www.psicologiayanina.com/img/yanina-parisi.jpg",
        "worksFor": {
            "@type": "Organization",
            "name": "Yanina Parisi - Psicologia"
        },
        "alumniOf": {
            "@type": "CollegeOrUniversity",
            "name": "Universitat de Girona"
        },
        "knowsAbout": ["Psicologia", "Teràpia de Parella", "Psicologia Judicial"]
    }',
    
    -- 4. Social profiles
    'https://www.facebook.com/yaninaparisi',
    'https://twitter.com/yaninaparisi',
    'https://www.linkedin.com/in/yaninaparisi',
    'https://www.instagram.com/yaninaparisi',
    'https://g.page/yaninaparisi',
    
    -- 5. Global Open Graph
    'Yanina Parisi - Psicologia',
    'ca_ES',
    'es_ES',
    'https://www.psicologiayanina.com/img/og-default.jpg',
    
    -- 6. Global Twitter Card
    '@yaninaparisi',
    '@yaninaparisi',
    'https://www.psicologiayanina.com/img/twitter-default.jpg',
    
    -- 7. Technical SEO
    'index, follow',
    '',
    '',
    '',
    '',
    
    -- 8. Breadcrumbs
    'Inici',
    'Inicio',
    '{
        "@context": "https://schema.org",
        "@type": "WebSite",
        "name": "Yanina Parisi - Psicologia",
        "url": "https://www.psicologiayanina.com"
    }',
    '{
        "@context": "https://schema.org",
        "@type": "WebPage",
        "name": "Pàgina",
        "url": "https://www.psicologiayanina.com"
    }',
    
    -- 9. International SEO
    'ca',
    '{
        "ca": "https://www.psicologiayanina.com/ca/",
        "es": "https://www.psicologiayanina.com/es/",
        "en": "https://www.psicologiayanina.com/en/"
    }',
    
    -- 10. Performance
    '0.8',
    'monthly'
);


-- ============================================
-- VERIFICACIÓ
-- ============================================

SELECT 'Taula seo_global creada correctament!' AS status;
SELECT * FROM seo_global WHERE id_global = 1;
