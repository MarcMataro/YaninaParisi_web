-- ============================================
-- TAULA: seo_onpage_paginas
-- ============================================
-- Descripció: Configuració SEO específica per cada pàgina del lloc web
-- Autor: Marc Mataró
-- Data: 2025-10-07
-- Versió: 1.0.0
-- ============================================

CREATE TABLE IF NOT EXISTS seo_onpage_paginas (
    id_pagina INT PRIMARY KEY AUTO_INCREMENT,
    
    -- ============================================
    -- 1. IDENTIFICACIÓ I URL
    -- ============================================
    url_relativa VARCHAR(255) UNIQUE NOT NULL COMMENT 'URL sense domini ej: /terapia-ansietat',
    titulo_pagina VARCHAR(100) NOT NULL COMMENT 'Títol visible de la pàgina',
    tipo_pagina ENUM('home', 'sobre-mi', 'servicios', 'blog', 'articulo', 'contacto', 'legal', 'landing') NOT NULL,
    
    -- ============================================
    -- 2. SEO BÀSIC CATALÀ
    -- ============================================
    title_ca VARCHAR(60) NOT NULL,
    meta_description_ca VARCHAR(160) NOT NULL,
    h1_ca VARCHAR(100) NOT NULL,
    contenido_principal_ca TEXT,
    
    -- ============================================
    -- 3. SEO BÀSIC CASTELLÀ
    -- ============================================
    title_es VARCHAR(60) NOT NULL,
    meta_description_es VARCHAR(160) NOT NULL,
    h1_es VARCHAR(100) NOT NULL,
    contenido_principal_es TEXT,
    
    -- ============================================
    -- 4. ESTRUCTURA I ORGANITZACIÓ
    -- ============================================
    breadcrumb_json JSON COMMENT 'Estructura jeràrquica de migues de pa',
    slug_ca VARCHAR(100) UNIQUE,
    slug_es VARCHAR(100) UNIQUE,
    parent_id INT NULL COMMENT 'Pàgina pare si existeix jerarquia',
    
    -- ============================================
    -- 5. SEO TÈCNIC ESPECÍFIC
    -- ============================================
    meta_robots VARCHAR(100) DEFAULT 'index, follow',
    canonical_url VARCHAR(255) COMMENT 'Si és diferent a la URL relativa',
    priority ENUM('1.0', '0.8', '0.6', '0.4', '0.2') DEFAULT '0.8',
    changefreq ENUM('always', 'hourly', 'daily', 'weekly', 'monthly', 'yearly', 'never') DEFAULT 'monthly',
    
    -- ============================================
    -- 6. SEO AVANÇAT
    -- ============================================
    focus_keyword_ca VARCHAR(50),
    focus_keyword_es VARCHAR(50),
    keywords_secundarias_ca VARCHAR(255),
    keywords_secundarias_es VARCHAR(255),
    schema_json JSON COMMENT 'Structured data específic de la pàgina',
    
    -- ============================================
    -- 7. OPEN GRAPH ESPECÍFIC
    -- ============================================
    og_title_ca VARCHAR(100),
    og_title_es VARCHAR(100),
    og_description_ca VARCHAR(200),
    og_description_es VARCHAR(200),
    og_image VARCHAR(255) COMMENT 'Imatge específica, sinó usa la global',
    
    -- ============================================
    -- 8. TWITTER CARD ESPECÍFIC
    -- ============================================
    twitter_title_ca VARCHAR(100),
    twitter_title_es VARCHAR(100),
    twitter_description_ca VARCHAR(200),
    twitter_description_es VARCHAR(200),
    twitter_image VARCHAR(255),
    
    -- ============================================
    -- 9. IMATGES SEO
    -- ============================================
    featured_image VARCHAR(255),
    alt_image_ca VARCHAR(125),
    alt_image_es VARCHAR(125),
    image_caption_ca VARCHAR(200),
    image_caption_es VARCHAR(200),
    
    -- ============================================
    -- 10. MÈTRIQUES I CONTROL
    -- ============================================
    seo_score TINYINT DEFAULT 0 COMMENT 'Puntuació SEO 0-100',
    word_count_ca INT DEFAULT 0,
    word_count_es INT DEFAULT 0,
    densidad_keyword_ca DECIMAL(4,2) DEFAULT 0,
    densidad_keyword_es DECIMAL(4,2) DEFAULT 0,
    
    -- ============================================
    -- 11. ESTAT I TEMPORALITAT
    -- ============================================
    activa BOOLEAN DEFAULT TRUE,
    fecha_publicacion DATETIME,
    fecha_ultima_actualizacion DATETIME,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_modificacion DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- ============================================
    -- 12. CLAUS I ÍNDEXS
    -- ============================================
    FOREIGN KEY (parent_id) REFERENCES seo_onpage_paginas(id_pagina) ON DELETE SET NULL,
    
    -- ÍNDEXS
    INDEX idx_url_relativa (url_relativa),
    INDEX idx_tipo_pagina (tipo_pagina),
    INDEX idx_activa (activa),
    INDEX idx_slug_ca (slug_ca),
    INDEX idx_slug_es (slug_es),
    INDEX idx_parent_id (parent_id),
    INDEX idx_fecha_publicacion (fecha_publicacion),
    INDEX idx_focus_keyword_ca (focus_keyword_ca),
    INDEX idx_focus_keyword_es (focus_keyword_es),
    FULLTEXT idx_contenido_ca (contenido_principal_ca),
    FULLTEXT idx_contenido_es (contenido_principal_es)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Configuració SEO específica per cada pàgina';


-- ============================================
-- INSERTS INICIALS: Pàgines principals
-- ============================================

-- Pàgina Home
INSERT INTO seo_onpage_paginas (
    url_relativa, titulo_pagina, tipo_pagina,
    title_ca, meta_description_ca, h1_ca,
    title_es, meta_description_es, h1_es,
    slug_ca, slug_es,
    meta_robots, priority, changefreq,
    focus_keyword_ca, focus_keyword_es,
    activa, fecha_publicacion
) VALUES (
    '/', 'Pàgina d\'Inici', 'home',
    'Yanina Parisi - Psicòloga Col·legiada a Girona', 
    'Psicòloga col·legiada especialitzada en teràpia de parella, adults i psicologia judicial a Girona. Primera sessió gratuïta.',
    'Psicòloga Col·legiada a Girona',
    'Yanina Parisi - Psicóloga Colegiada en Girona',
    'Psicóloga colegiada especializada en terapia de pareja, adultos y psicología judicial en Girona. Primera sesión gratuita.',
    'Psicóloga Colegiada en Girona',
    'inici', 'inicio',
    'index, follow', '1.0', 'weekly',
    'psicòloga girona', 'psicóloga girona',
    TRUE, NOW()
);

-- Pàgina Sobre Mi
INSERT INTO seo_onpage_paginas (
    url_relativa, titulo_pagina, tipo_pagina,
    title_ca, meta_description_ca, h1_ca,
    title_es, meta_description_es, h1_es,
    slug_ca, slug_es,
    meta_robots, priority, changefreq,
    focus_keyword_ca, focus_keyword_es,
    activa, fecha_publicacion
) VALUES (
    '/sobre-mi', 'Sobre Mí', 'sobre-mi',
    'Sobre Mí - Yanina Parisi | Psicòloga Col·legiada',
    'Coneix més sobre la meva experiència professional com a psicòloga col·legiada especialitzada en teràpia de parella i psicologia judicial.',
    'Sobre Mí - Yanina Parisi',
    'Sobre Mí - Yanina Parisi | Psicóloga Colegiada',
    'Conoce más sobre mi experiencia profesional como psicóloga colegiada especializada en terapia de pareja y psicología judicial.',
    'Sobre Mí - Yanina Parisi',
    'sobre-mi', 'sobre-mi',
    'index, follow', '0.8', 'monthly',
    'psicòloga col·legiada girona', 'psicóloga colegiada girona',
    TRUE, NOW()
);

-- Pàgina Contacto
INSERT INTO seo_onpage_paginas (
    url_relativa, titulo_pagina, tipo_pagina,
    title_ca, meta_description_ca, h1_ca,
    title_es, meta_description_es, h1_es,
    slug_ca, slug_es,
    meta_robots, priority, changefreq,
    focus_keyword_ca, focus_keyword_es,
    activa, fecha_publicacion
) VALUES (
    '/contacto', 'Contacto', 'contacto',
    'Contacte - Primera Sessió Gratuïta | Yanina Parisi',
    'Posa\'t en contacte per reservar la teva primera sessió gratuïta. Psicòloga a Girona especialitzada en teràpia de parella i adults.',
    'Contacte - Primera Sessió Gratuïta',
    'Contacto - Primera Sesión Gratuita | Yanina Parisi',
    'Ponte en contacto para reservar tu primera sesión gratuita. Psicóloga en Girona especializada en terapia de pareja y adultos.',
    'Contacto - Primera Sesión Gratuita',
    'contacte', 'contacto',
    'index, follow', '0.8', 'monthly',
    'psicòloga girona contacte', 'psicóloga girona contacto',
    TRUE, NOW()
);


-- ============================================
-- VERIFICACIÓ
-- ============================================

SELECT 'Taula seo_onpage_paginas creada correctament!' AS status;
SELECT id_pagina, titulo_pagina, url_relativa, tipo_pagina, seo_score, activa 
FROM seo_onpage_paginas;
