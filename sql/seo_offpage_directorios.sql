-- ============================================
-- TAULA: seo_offpage_directorios
-- ============================================
-- Gestió de directoris empresarials per Off-Page SEO
-- Autor: Marc Mataró
-- Data: 2025-10-07
-- ============================================

CREATE TABLE IF NOT EXISTS seo_offpage_directorios (
    -- Identificació
    id_directorio INT PRIMARY KEY AUTO_INCREMENT COMMENT 'ID únic del directori',
    
    -- Informació Bàsica
    nombre VARCHAR(255) NOT NULL COMMENT 'Nom del directori (ex: Google My Business, Yelp)',
    url VARCHAR(500) NOT NULL COMMENT 'URL del perfil o del directori',
    categoria ENUM('salud', 'psicologia', 'negocios', 'locales', 'generico') DEFAULT 'psicologia' COMMENT 'Tipus de directori',
    
    -- Mètriques d'Autoritat
    da_directorio TINYINT UNSIGNED COMMENT 'Domain Authority del directori (0-100)',
    
    -- Cost
    costo DECIMAL(8,2) DEFAULT 0 COMMENT 'Cost anual d\'inscripció en euros',
    
    -- Idioma
    idioma ENUM('ca', 'es', 'en', 'other') DEFAULT 'es' COMMENT 'Idioma principal del directori',
    
    -- Atributs SEO
    nofollow BOOLEAN DEFAULT FALSE COMMENT 'Si l\'enllaç des del directori és nofollow',
    permite_anchor_personalizado BOOLEAN DEFAULT TRUE COMMENT 'Si el directori permet personalitzar l\'anchor text',
    
    -- Estat i Seguiment
    estado ENUM('pendiente', 'enviado', 'aprobado', 'rechazado', 'activo') DEFAULT 'pendiente' COMMENT 'Estat del registre',
    fecha_envio DATE COMMENT 'Data d\'enviament de la sol·licitud',
    fecha_aprobacion DATE COMMENT 'Data d\'aprovació del registre',
    
    -- Notes
    notas TEXT COMMENT 'Notes internes sobre el procés, requisits, etc.',
    
    -- Tracking
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT 'Data de creació del registre',
    
    -- Índexs per millorar el rendiment
    INDEX idx_estado (estado),
    INDEX idx_categoria (categoria),
    INDEX idx_idioma (idioma),
    INDEX idx_da (da_directorio),
    INDEX idx_fecha_envio (fecha_envio),
    INDEX idx_fecha_aprobacion (fecha_aprobacion)
    
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Directoris empresarials per Off-Page SEO';

-- ============================================
-- DADES D'EXEMPLE (OPCIONAL)
-- ============================================
-- Descomenta les següents línies per inserir directoris d'exemple

/*
INSERT INTO seo_offpage_directorios (nombre, url, categoria, da_directorio, costo, idioma, nofollow, permite_anchor_personalizado, estado) VALUES
-- Directoris Locals (Alta Prioritat - Gratuïts)
('Google My Business', 'https://business.google.com', 'locales', 98, 0, 'es', 0, 1, 'pendiente'),
('Bing Places', 'https://www.bingplaces.com', 'locales', 92, 0, 'es', 0, 1, 'pendiente'),
('Apple Maps', 'https://mapsconnect.apple.com', 'locales', 95, 0, 'es', 0, 0, 'pendiente'),
('Facebook Business', 'https://www.facebook.com/business', 'locales', 96, 0, 'es', 1, 1, 'pendiente'),
('LinkedIn Company', 'https://www.linkedin.com/company', 'negocios', 98, 0, 'es', 1, 1, 'pendiente'),

-- Directoris de Psicologia (Alta Rellevància)
('Psicologos en España', 'https://www.psicologosenespana.es', 'psicologia', 45, 0, 'es', 0, 1, 'pendiente'),
('Doctoralia', 'https://www.doctoralia.es', 'salud', 75, 199, 'es', 0, 1, 'pendiente'),
('Topdoctors', 'https://www.topdoctors.es', 'salud', 68, 150, 'es', 0, 1, 'pendiente'),
('MundoPsicologos', 'https://www.mundopsicologos.com', 'psicologia', 52, 0, 'es', 0, 1, 'pendiente'),

-- Directoris Locals Girona
('Páginas Amarillas', 'https://www.paginasamarillas.es', 'locales', 70, 0, 'es', 0, 0, 'pendiente'),
('11870.com', 'https://www.11870.com', 'locales', 60, 0, 'es', 0, 0, 'pendiente'),

-- Directoris de Salut
('iSalud', 'https://www.isalud.com', 'salud', 55, 0, 'es', 0, 1, 'pendiente'),
('DocPlanner', 'https://www.docplanner.es', 'salud', 72, 180, 'es', 0, 1, 'pendiente'),

-- Directoris Genèrics
('Yelp España', 'https://www.yelp.es', 'generico', 85, 0, 'es', 1, 0, 'pendiente'),
('Foursquare', 'https://foursquare.com', 'generico', 88, 0, 'es', 1, 0, 'pendiente');
*/

-- ============================================
-- CONSULTES ÚTILS
-- ============================================

-- Veure tots els directoris actius
-- SELECT * FROM seo_offpage_directorios WHERE estado = 'activo' ORDER BY da_directorio DESC;

-- Directoris pendents d'enviament
-- SELECT * FROM seo_offpage_directorios WHERE estado = 'pendiente' ORDER BY da_directorio DESC;

-- Directoris gratuïts amb alta autoritat
-- SELECT * FROM seo_offpage_directorios WHERE costo = 0 AND da_directorio >= 60 ORDER BY da_directorio DESC;

-- Cost total anual
-- SELECT SUM(costo) as costo_total FROM seo_offpage_directorios WHERE estado IN ('aprobado', 'activo');

-- Estadístiques per categoria
-- SELECT categoria, COUNT(*) as total, AVG(da_directorio) as da_promedio FROM seo_offpage_directorios GROUP BY categoria;

-- Directoris pendents de revisió (més de 15 dies enviats)
-- SELECT * FROM seo_offpage_directorios WHERE estado = 'enviado' AND fecha_envio <= DATE_SUB(NOW(), INTERVAL 15 DAY);
