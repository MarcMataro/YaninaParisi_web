-- phpMyAdmin SQL Dump
-- version 4.9.11
-- https://www.phpmyadmin.net/
--
-- Host: db5019031934.hosting-data.io
-- Generation Time: Dec 31, 2025 at 10:51 AM
-- Server version: 8.0.36
-- PHP Version: 7.4.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Database: `dbs14977739`
--

-- --------------------------------------------------------

--
-- Table structure for table `blog_entrades`
--

CREATE TABLE `blog_entrades` (
  `id_entrada` int NOT NULL,
  `titol_ca` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `titol_es` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug_ca` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug_es` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `contingut_ca` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `contingut_es` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `resum_ca` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `resum_es` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `estat` enum('esborrany','revisio','publicat','programat','arxivat') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'esborrany',
  `data_publicacio` datetime DEFAULT NULL,
  `data_arxivat` datetime DEFAULT NULL,
  `visible` tinyint(1) DEFAULT '1',
  `imatge_portada` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alt_imatge_ca` varchar(125) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alt_imatge_es` varchar(125) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `galeria_imatges` json DEFAULT NULL,
  `meta_title_ca` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_title_es` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_description_ca` varchar(160) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_description_es` varchar(160) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_keywords_ca` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_keywords_es` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_autor` int NOT NULL,
  `temps_lectura_ca` tinyint DEFAULT NULL COMMENT 'Temps de lectura en minuts',
  `temps_lectura_es` tinyint DEFAULT NULL COMMENT 'Tiempo de lectura en minutos',
  `visualitzacions` int DEFAULT '0',
  `compartits` int DEFAULT '0',
  `comentaris_actius` tinyint(1) DEFAULT '1',
  `data_creacio` datetime DEFAULT CURRENT_TIMESTAMP,
  `data_actualitzacio` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `data_modificacio` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `blog_entrades_categories`
--

CREATE TABLE `blog_entrades_categories` (
  `id_relacio` int NOT NULL,
  `id_entrada` int NOT NULL,
  `id_categoria` int NOT NULL,
  `data_assignacio` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `blog_entrades_etiquetes`
--

CREATE TABLE `blog_entrades_etiquetes` (
  `id_relacio` int NOT NULL,
  `id_entrada` int NOT NULL,
  `id_etiqueta` int NOT NULL,
  `data_assignacio` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id_category` int NOT NULL,
  `nom_ca` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nom_es` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug_ca` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug_es` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcio_ca` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `descripcion_es` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `ordre` int DEFAULT '0',
  `activa` tinyint(1) DEFAULT '1',
  `data_creacio` datetime DEFAULT CURRENT_TIMESTAMP,
  `data_actualitzacio` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id_category`, `nom_ca`, `nom_es`, `slug_ca`, `slug_es`, `descripcio_ca`, `descripcion_es`, `ordre`, `activa`, `data_creacio`) VALUES
(1, 'Ansietat', 'Ansiedad', 'ansietat', 'ansiedad', 'Articles del blog relacionats amb l\'ansietat.', 'Artículos del blog relacionados con la ansiedad.', 0, 1, '2025-10-11 10:49:22'),
(2, 'Estrès', 'Estrés', 'estres', 'estres', 'Articles del blog relacionats amb l\'estrès.', 'Artículos del blog relacionados con el estrés.', 0, 1, '2025-10-12 07:32:59');

-- --------------------------------------------------------

--
-- Table structure for table `etiquetes`
--

CREATE TABLE `etiquetes` (
  `id_etiqueta` int NOT NULL,
  `nom_ca` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nom_es` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug_ca` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug_es` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcio_ca` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `descripcion_es` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `ordre` int DEFAULT '0',
  `activa` tinyint(1) DEFAULT '1',
  `data_creacio` datetime DEFAULT CURRENT_TIMESTAMP,
  `data_actualitzacio` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `etiquetes`
--

INSERT INTO `etiquetes` (`id_etiqueta`, `nom_ca`, `nom_es`, `slug_ca`, `slug_es`, `descripcio_ca`, `descripcion_es`, `ordre`, `activa`, `data_creacio`) VALUES
(1, 'Ansietat crònica', 'Ansiedad crónica', 'ansietat-cronica', 'ansiedad-cronica', 'Etiqueta d\'ansietat crònica', 'Etiqueta de ansiedad crónica', 0, 1, '2025-10-11 11:07:25'),
(2, 'Ansietat aguda', 'Ansiedad aguda', 'ansietat-aguda', 'ansiedad-aguda', 'Hi ha casos d\'ansietat aguda', 'Hay casos de ansiedad aguda', 0, 1, '2025-10-12 09:40:14');

-- --------------------------------------------------------

--
-- Table structure for table `faqs`
--

CREATE TABLE `faqs` (
  `id_faq` int NOT NULL,
  `pregunta_ca` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `pregunta_es` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `resposta_ca` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `resposta_es` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `categoria` enum('general','terapia','tarifes','tecnica','primera_visita','urgencies') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'general',
  `ordre` int DEFAULT '0' COMMENT 'Ordre dins de la categoria',
  `activa` tinyint(1) DEFAULT '1',
  `destacada` tinyint(1) DEFAULT '0' COMMENT 'Si apareix a la pàgina principal',
  `meta_title_ca` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_title_es` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_description_ca` varchar(160) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_description_es` varchar(160) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `slug_ca` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `slug_es` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `vegades_visualitzada` int DEFAULT '0',
  `vegades_util` int DEFAULT '0' COMMENT 'Quant han dit que els ha estat útil',
  `data_creacio` datetime DEFAULT CURRENT_TIMESTAMP,
  `data_actualitzacio` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `faqs`
--

INSERT INTO `faqs` (`id_faq`, `pregunta_ca`, `pregunta_es`, `resposta_ca`, `resposta_es`, `categoria`, `ordre`, `activa`, `destacada`, `meta_title_ca`, `meta_title_es`, `meta_description_ca`, `meta_description_es`, `slug_ca`, `slug_es`, `vegades_visualitzada`, `vegades_util`, `data_creacio`) VALUES
(1, 'Quan hauria de demanar ajuda a un psicòleg?', '¿Cuándo debería pedir ayuda a un psicólogo?', 'És recomanable demanar ajuda quan sents que el malestar emocional (ansietat, tristesa, estrès, etc.) és intens, dura molt de temps, interfereix en la teva vida diària (feina, relacions, descans) o simplement quan et sents aturat i no saps com continuar.', 'Es recomendable pedir ayuda cuando sientes que el malestar emocional (ansiedad, tristeza, estrés, etc.) es intenso, dura mucho tiempo, interfiere en tu vida diaria (trabajo, relaciones, descanso) o simplement cuando te sientes estancado y no sabes cómo continuar.', 'terapia', 4, 1, 0, '<br /><font size=\'1\'><table class=\'xdebug-error xe-deprecate', '<br /><font size=\'1\'><table class=\'xdebug-error xe-deprecate', '<br /><font size=\'1\'><table class=\'xdebug-error xe-deprecated\' dir=\'ltr\' border=\'1\' cellspacing=\'0\' cellpadding=\'1\'><tr><th align=\'left\' bgcolor=\'#f57900\' colsp', '<br /><font size=\'1\'><table class=\'xdebug-error xe-deprecated\' dir=\'ltr\' border=\'1\' cellspacing=\'0\' cellpadding=\'1\'><tr><th align=\'left\' bgcolor=\'#f57900\' colsp', 'quan-hauria-de-demanar-ajuda-a-un-psicòleg', 'cuándo-debería-pedir-ayuda-a-un-psicólogo', 0, 0, '2025-11-01 08:19:29'),
(2, 'Quina és la diferència entre un psicòleg i un psiquiatre?', '¿Cuál es la diferencia entre un psicólogo y un psiquiatra?', 'El psicòleg és un professional de la salut mental que tracta els problemes a través de la teràpia i l\'aprenentatge d\'eines psicològiques. El psiquiatre és un metge que pot diagnosticar i tractar trastorns mentals, i està habilitat per prescriure medicació. Ambdós professionals poden col·laborar per a un abordatge integral.', 'El psicólogo es un profesional de la salud mental que trata los problemas a través de la terapia y el aprendizaje de herramientas psicológicas. El psiquiatra es un médico que puede diagnosticar y tratar trastornos mentales, y está habilitado para recetar medicación. Ambos profesionales pueden colaborar para un abordaje integral.', 'general', 0, 1, 0, NULL, NULL, NULL, NULL, 'quina-és-la-diferència-entre-un-psicòleg-i-un-psiquiatre', 'cuál-es-la-diferencia-entre-un-psicólogo-y-un-psiquiatra', 0, 0, '2025-11-01 08:20:51'),
(3, 'Com funciona la primera sessió?', '¿Cómo funciona la primera sesión?', 'La primera sessió és una trobada d\'avaluació. És l\'oportunitat perquè tu m\'expliquis què et porta a consultar i jo pugui conèixer-te millor. També és el moment per aclarir les teves expectatives, explicar-te com treballo i comencem a traçar els objectius de la teràpia. És un espai sense compromís per a tu.', 'La primera sesión es una sesión de evaluación. Es la oportunidad para que tú me cuentes qué te trae a consulta y yo pueda conocerte mejor. También es el momento para aclarar tus expectativas, explicarte cómo trabajo y comenzar a trazar los objetivos de la terapia. Es un espacio sin compromiso para ti.', 'primera_visita', 0, 1, 0, NULL, NULL, NULL, NULL, 'com-funciona-la-primera-sessió', 'cómo-funciona-la-primera-sesión', 0, 0, '2025-11-01 08:23:05'),
(4, 'Quants de temps dura una teràpia?', '¿Cuánto tiempo dura una terapia?', 'No hi ha una resposta única. La durada depèn de cada persona, de la seva situació i dels seus objectius. Alguns problemes es poden abordar en teràpies més breus (3-6 mesos), mentre que altres requereixen un procés més llarg. Això és alguna cosa que valorem i revisem junts periòdicament.', 'No hay una respuesta única. La duración depende de cada persona, de su situación y de sus objetivos. Algunos problemas se pueden abordar en terapias más breves (3-6 meses), mientras que otros requieren un proceso más largo. Esto es algo que valoramos y revisamos juntos periódicamente.', 'terapia', 1, 1, 0, '<br /><b>Deprecated</b>:  htmlspecialchars(): Passing null t', '<br /><b>Deprecated</b>:  htmlspecialchars(): Passing null t', '<br /><b>Deprecated</b>:  htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated in <b>C:\\wamp64\\www\\yaninaparisi\\_pcontrol\\gfa', '<br /><b>Deprecated</b>:  htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated in <b>C:\\wamp64\\www\\yaninaparisi\\_pcontrol\\gfa', 'quants-de-temps-dura-una-teràpia', 'cuánto-tiempo-dura-una-terapia', 0, 0, '2025-11-01 08:23:50'),
(5, 'És confidencial el que es parla a la teràpia?', '¿Es confidencial lo que se habla en terapia?', 'Absolutament. El secret professional és un dels pilars fonamentals de la meva feina. Tot el que es parla a la consulta és estrictament confidencial, amb les excepcions que marca la llei (situacions de risc greu per a tu o per a altres persones).', 'Absolutamente. El secreto profesional es uno de los pilares fundamentales de mi trabajo. Todo lo que se habla en consulta es estrictamente confidencial, con las excepciones que marca la ley (situaciones de riesgo grave para ti o para otras personas).', 'terapia', 2, 1, 0, NULL, NULL, NULL, NULL, 'És-confidencial-el-que-es-parla-a-la-teràpia', 'es-confidencial-lo-que-se-habla-en-terapia', 0, 0, '2025-11-01 08:25:00'),
(6, 'Amb quina freqüència són les sessions?', '¿Con qué frecuencia son las sesiones?', 'Normalment, les sessions són setmanals, especialment al començament del procés. A mesura que es van assolint els objectius, la freqüència es pot reduir a quinzenal o mensual, fins a la finalització de la teràpia.', 'Normalmente, las sesiones son semanales, especialmente al comienzo del proceso. A medida que se van alcanzando los objetivos, la frecuencia se puede reducir a quincenal o mensual, hasta la finalización de la terapia.', 'terapia', 3, 1, 0, '<br /><b>Deprecated</b>:  htmlspecialchars(): Passing null t', '<br /><b>Deprecated</b>:  htmlspecialchars(): Passing null t', '<br /><b>Deprecated</b>:  htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated in <b>C:\\wamp64\\www\\yaninaparisi\\_pcontrol\\gfa', '<br /><b>Deprecated</b>:  htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated in <b>C:\\wamp64\\www\\yaninaparisi\\_pcontrol\\gfa', 'amb-quina-freqüència-són-les-sessions', 'con-qué-frecuencia-son-las-sesiones', 0, 0, '2025-11-01 08:27:04'),
(7, 'Quina és la teva orientació o mètode de treball?', '¿Cuál es tu orientación o método de trabajo?', 'La meva formació i enfoc es basa en la Teràpia Cognitivo-Conductual integrada amb elements d\'altres corrents com la teràpia centrada en la comprensió i l\'acceptació. Això vol dir que treballarem per identificar i canviar patrons de pensament i comportament que et generin malestar, alhora que aprens eines pràctiques per a la teva vida diària.', 'Mi formación y enfoque se basa en la Terapia Cognitivo-Conductual integrada con elementos de otras corrientes como la terapia centrada en la compresión y la aceptación. Esto significa que trabajaremos para identificar y cambiar patrones de pensamiento y comportamiento que te generen malestar, a la vez que aprendes herramientas prácticas para tu vida diaria.', 'general', 4, 1, 0, NULL, NULL, NULL, NULL, 'quina-és-la-teva-orientació-o-mètode-de-treball', 'cuál-es-tu-orientación-o-método-de-trabajo', 0, 0, '2025-11-01 08:28:39'),
(8, 'Puc anar a teràpia si només necessito desfogar-me o parlar amb algú?', '¿Puedo ir a terapia si solo necesito desahogarme o hablar con alguien?', 'Per descomptat. La teràpia és un espai segur per a tu. Tot i que el seu objectiu va més enllà de \"desfogar-se\" (es busca un canvi i una millora), el fet de poder parlar lliurement i ser escoltat sense judici és una part essencial i molt terapèutica del procés.', 'Por supuesto. La terapia es un espacio seguro para ti. Aunque su objetivo va más allá de \"desahogarse\" (se busca un cambio y una mejora), el poder hablar libremente y ser escuchado sin juicio es una parte esencial y muy terapéutica del proceso.', 'terapia', 5, 1, 0, NULL, NULL, NULL, NULL, 'puc-anar-a-teràpia-si-només-necessito-desfogar-me-o-parlar-amb-algú', 'puedo-ir-a-terapia-si-solo-necesito-desahogarme-o-hablar-con-alguien', 0, 0, '2025-11-01 08:29:26');

-- --------------------------------------------------------

--
-- Table structure for table `info_psicologa`
--

CREATE TABLE `info_psicologa` (
  `id_info` int NOT NULL,
  `nom_complet_ca` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nom_complet_es` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `titulacio_ca` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `titulacio_es` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `foto_perfil` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alt_foto_ca` varchar(125) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alt_foto_es` varchar(125) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_professional` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefon_professional` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `linkedin_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `instagram_professional` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `num_collegiat` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `college_professional` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `data_creacio` datetime DEFAULT CURRENT_TIMESTAMP,
  `data_actualitzacio` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pacients`
--

CREATE TABLE `pacients` (
  `id_pacient` int NOT NULL,
  `nom` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `cognoms` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `data_naixement` date DEFAULT NULL,
  `sexe` enum('Hombre','Mujer','Otro','No especificado') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefon` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `adreca` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `ciutat` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `codi_postal` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `antecedents_medics` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `medicacio_actual` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `alergies` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `contacte_emergencia_nom` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contacte_emergencia_telefon` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contacte_emergencia_relacio` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `data_registre` datetime DEFAULT CURRENT_TIMESTAMP,
  `data_ultima_actualitzacio` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `estat` enum('Activo','Inactivo','Alta','Seguimiento') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Activo',
  `observacions` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pagaments`
--

CREATE TABLE `pagaments` (
  `id_pagament` int NOT NULL,
  `id_sessio` int NOT NULL,
  `data_pagament` date NOT NULL,
  `import` decimal(8,2) NOT NULL,
  `metode_pagament` enum('Efectivo','Tarjeta','Transferencia','Bizum') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Efectivo',
  `estat` enum('Pendiente','Completado','Anulado') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Completado',
  `facturat` tinyint(1) DEFAULT '0',
  `numero_factura` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `observacions` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `data_registre` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ressenya_tokens`
--

CREATE TABLE `ressenya_tokens` (
  `id` int NOT NULL,
  `pacient_id` int NOT NULL,
  `token` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `used_at` datetime DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Table structure for table `ressenyes`
--

CREATE TABLE `ressenyes` (
  `id_ressenya` int NOT NULL,
  `pacient_id` int DEFAULT NULL,
  `nom_pacient` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Nom o pseudònim, opcional',
  `inicials` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Inicials per a major privacitat',
  `edat` tinyint UNSIGNED DEFAULT NULL COMMENT 'Edat del pacient',
  `titol_ca` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `titol_es` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `text_ressenya_ca` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `text_ressenya_es` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `puntuacio` tinyint UNSIGNED NOT NULL COMMENT 'Puntuació de 1 a 5',
  `data_terapia` date DEFAULT NULL COMMENT 'Data aproximada quan va rebre la teràpia',
  `tipus_terapia` enum('individual','parella','familiar','online','presencial') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'individual',
  `estat` enum('pendent','aprovat','rebutjat') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'pendent',
  `verificada` tinyint(1) DEFAULT '0' COMMENT 'Si es verifica que és un pacient real',
  `autoritzacio_publicacio` tinyint(1) DEFAULT '1',
  `mostrar_nom` tinyint(1) DEFAULT '0',
  `mostrar_inicials` tinyint(1) DEFAULT '1',
  `likes` int DEFAULT '0',
  `reportada` tinyint(1) DEFAULT '0',
  `data_creacio` datetime DEFAULT CURRENT_TIMESTAMP,
  `data_aprovacio` datetime DEFAULT NULL,
  `data_actualitzacio` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `seo_global`
--

CREATE TABLE `seo_global` (
  `id_global` int NOT NULL,
  `site_title_ca` varchar(70) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Psicòloga Yanina Parisi | Barcelona',
  `site_title_es` varchar(70) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Psicóloga Yanina Parisi | Barcelona',
  `site_description_ca` varchar(160) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `site_description_es` varchar(160) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `default_title_template_ca` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '{page} | Psicòloga Yanina Parisi',
  `default_title_template_es` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '{page} | Psicóloga Yanina Parisi',
  `default_meta_template_ca` varchar(160) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `default_meta_template_es` varchar(160) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `organization_schema` json DEFAULT NULL,
  `local_business_schema` json DEFAULT NULL,
  `person_schema` json DEFAULT NULL,
  `facebook_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `twitter_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `linkedin_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `instagram_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `google_business_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `og_site_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Consultori Psicològic Yanina Parisi',
  `og_locale_ca` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'ca_ES',
  `og_locale_es` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'es_ES',
  `default_og_image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `twitter_site` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `twitter_creator` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `default_twitter_image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `default_meta_robots` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `google_site_verification` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `bing_verification` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `google_analytics_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `google_tag_manager_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `breadcrumb_home_text_ca` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Inici',
  `breadcrumb_home_text_es` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Inicio',
  `website_schema` json DEFAULT NULL,
  `webpage_schema` json DEFAULT NULL,
  `hreflang_default` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'es',
  `hreflang_alternates` json DEFAULT NULL,
  `default_priority` enum('1.0','0.8','0.6','0.4','0.2') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '0.8',
  `default_changefreq` enum('always','hourly','daily','weekly','monthly','yearly','never') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'monthly',
  `fecha_actualizacion` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `seo_global`
--

INSERT INTO `seo_global` (`id_global`, `site_title_ca`, `site_title_es`, `site_description_ca`, `site_description_es`, `default_title_template_ca`, `default_title_template_es`, `default_meta_template_ca`, `default_meta_template_es`, `organization_schema`, `local_business_schema`, `person_schema`, `facebook_url`, `twitter_url`, `linkedin_url`, `instagram_url`, `google_business_url`, `og_site_name`, `og_locale_ca`, `og_locale_es`, `default_og_image`, `twitter_site`, `twitter_creator`, `default_twitter_image`, `default_meta_robots`, `google_site_verification`, `bing_verification`, `google_analytics_id`, `google_tag_manager_id`, `breadcrumb_home_text_ca`, `breadcrumb_home_text_es`, `website_schema`, `webpage_schema`, `hreflang_default`, `hreflang_alternates`, `default_priority`, `default_changefreq`) VALUES
(1, 'Psicòloga Yanina Parisi 🧠 | Teràpia per ansietat i depressió a Girona', 'Psicóloga Yanina Parisi 🧠 | Terapia ansiedad y depressión en Girona', 'Psicòloga col·legiada especialitzada en teràpia de parella, adults i psicologia judicial a Girona. Primera sessió gratuïta.', 'Psicóloga colegiada especializada en terapia de pareja, adultos y psicología judicial en Girona. Primera sesión gratuita.', '{page} | Yanina Parisi - Psicòloga Girona', '{page} | Yanina Parisi - Psicóloga Girona', 'Descobreix més sobre {page}. Atenció psicològica professional a Girona amb primera sessió gratuïta.', 'Descubre más sobre {page}. Atención psicológica profesional en Girona con primera sesión gratuita.', '{\"url\": \"https://www.psicologiayanina.com\", \"logo\": \"https://www.psicologiayanina.com/img/Logo.png\", \"name\": \"Yanina Parisi - Psicologia\", \"@type\": \"Organization\", \"address\": {\"@type\": \"PostalAddress\", \"addressRegion\": \"Girona\", \"addressCountry\": \"ES\", \"addressLocality\": \"Girona\"}, \"@context\": \"https://schema.org\", \"contactPoint\": {\"@type\": \"ContactPoint\", \"telephone\": \"+34-XXX-XXX-XXX\", \"contactType\": \"customer service\", \"availableLanguage\": [\"ca\", \"es\"]}}', '{\"geo\": {\"@type\": \"GeoCoordinates\", \"latitude\": 41.9794, \"longitude\": 2.8214}, \"name\": \"Yanina Parisi - Psicologia\", \"@type\": \"LocalBusiness\", \"image\": \"https://www.psicologiayanina.com/img/Logo.png\", \"address\": {\"@type\": \"PostalAddress\", \"postalCode\": \"17001\", \"streetAddress\": \"Carrer Example, 123\", \"addressCountry\": \"ES\", \"addressLocality\": \"Girona\"}, \"@context\": \"https://schema.org\", \"priceRange\": \"??????\", \"openingHoursSpecification\": {\"@type\": \"OpeningHoursSpecification\", \"opens\": \"09:00\", \"closes\": \"20:00\", \"dayOfWeek\": [\"Monday\", \"Tuesday\", \"Wednesday\", \"Thursday\", \"Friday\"]}}', '{\"url\": \"https://www.psicologiayanina.com\", \"name\": \"Yanina Parisi\", \"@type\": \"Person\", \"image\": \"https://www.psicologiayanina.com/img/yanina-parisi.jpg\", \"@context\": \"https://schema.org\", \"alumniOf\": {\"name\": \"Universitat de Girona\", \"@type\": \"CollegeOrUniversity\"}, \"jobTitle\": \"Psic??loga Col??legiada\", \"worksFor\": {\"name\": \"Yanina Parisi - Psicologia\", \"@type\": \"Organization\"}, \"knowsAbout\": [\"Psicologia\", \"Ter??pia de Parella\", \"Psicologia Judicial\"]}', 'https://www.facebook.com/yaninaparisi', 'https://twitter.com/yaninaparisi', 'https://www.linkedin.com/in/yaninaparisi', 'https://www.instagram.com/yaninaparisi', 'https://g.page/yaninaparisi', 'Yanina Parisi - Psicologia', 'ca_ES', 'es_ES', 'https://www.psicologiayanina.com/img/og-default.jpg', '@yaninaparisi', '@yaninaparisi', 'https://www.psicologiayanina.com/img/twitter-default.jpg', 'index, follow', '', '', '', '', 'Inici', 'Inicio', '{\"url\": \"https://www.psicologiayanina.com\", \"name\": \"Yanina Parisi - Psicologia\", \"@type\": \"WebSite\", \"@context\": \"https://schema.org\"}', '{\"url\": \"https://www.psicologiayanina.com\", \"name\": \"P??gina\", \"@type\": \"WebPage\", \"@context\": \"https://schema.org\"}', 'es', '{\"ca\": \"https://www.psicologiayanina.com/ca/\", \"en\": \"https://www.psicologiayanina.com/en/\", \"es\": \"https://www.psicologiayanina.com/es/\"}', '0.8', 'weekly');

-- --------------------------------------------------------

--
-- Table structure for table `seo_offpage`
--

CREATE TABLE `seo_offpage` (
  `id_offpage` int NOT NULL,
  `url_origen` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'URL que enllaça al nostre lloc',
  `url_destino` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'URL del nostre lloc que enllaça',
  `anchor_text` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Text de l''enllaç',
  `dominio_origen` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `da_origen` tinyint UNSIGNED DEFAULT NULL COMMENT 'Domain Authority 0-100',
  `dr_origen` tinyint UNSIGNED DEFAULT NULL COMMENT 'Domain Rating 0-100',
  `tf_origen` tinyint UNSIGNED DEFAULT NULL COMMENT 'Trust Flow 0-100',
  `cf_origen` tinyint UNSIGNED DEFAULT NULL COMMENT 'Citation Flow 0-100',
  `titulo_pagina_origen` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `da_pagina_origen` tinyint UNSIGNED DEFAULT NULL,
  `traffic_origen` int DEFAULT NULL COMMENT 'Tràfic mensual estimat',
  `idioma_origen` enum('ca','es','en','fr','it','other') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'es',
  `tipo_backlink` enum('guest_post','directorio','prensa','blog_comentario','foro','social_media','recursos_util','colaboracion','natural','adquirido') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `contexto_backlink` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'Text que envolta l''enllaç',
  `posicion_enlace` enum('header','footer','sidebar','contenido','comentarios','navegacion') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'contenido',
  `nofollow` tinyint(1) DEFAULT '0',
  `sponsored` tinyint(1) DEFAULT '0',
  `ugc` tinyint(1) DEFAULT '0' COMMENT 'User Generated Content',
  `relevancia_tematica` enum('alta','media','baja') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'media',
  `calidad_percibida` enum('excelente','buena','regular','mala') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'regular',
  `autoridad_tematica` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Temàtica principal del domini origen',
  `fecha_descubrimiento` date NOT NULL,
  `fecha_ultima_verificacion` date DEFAULT NULL,
  `estado` enum('activo','perdido','roto','en_revision') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'activo',
  `fecha_perdida` date DEFAULT NULL COMMENT 'Data quan es va perdre el backlink',
  `clicks_mensuales` int DEFAULT '0',
  `traffic_estimado` int DEFAULT '0',
  `valor_estimado` decimal(10,2) DEFAULT NULL COMMENT 'Valor monetari estimat',
  `contacto_realizado` tinyint(1) DEFAULT '0',
  `fecha_contacto` date DEFAULT NULL,
  `respuesta_recibida` tinyint(1) DEFAULT '0',
  `notas_contacto` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `campana_seo` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Campanya a la que pertany',
  `objetivo_seo` enum('branding','trafico','autoridad','conversiones') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'autoridad',
  `prioridad` enum('alta','media','baja') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'media',
  `ip_origen` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pais_origen` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tld_origen` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Domini de primer nivell .es, .com, etc',
  `indexacion_origen` enum('indexada','no_indexada','desconocido') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'desconocido',
  `semrush_id` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ahrefs_id` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `majestic_id` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notas_internas` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `fecha_creacion` datetime DEFAULT CURRENT_TIMESTAMP,
  `fecha_actualizacion` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `seo_offpage`
--

INSERT INTO `seo_offpage` (`id_offpage`, `url_origen`, `url_destino`, `anchor_text`, `dominio_origen`, `da_origen`, `dr_origen`, `tf_origen`, `cf_origen`, `titulo_pagina_origen`, `da_pagina_origen`, `traffic_origen`, `idioma_origen`, `tipo_backlink`, `contexto_backlink`, `posicion_enlace`, `nofollow`, `sponsored`, `ugc`, `relevancia_tematica`, `calidad_percibida`, `autoridad_tematica`, `fecha_descubrimiento`, `fecha_ultima_verificacion`, `estado`, `fecha_perdida`, `clicks_mensuales`, `traffic_estimado`, `valor_estimado`, `contacto_realizado`, `fecha_contacto`, `respuesta_recibida`, `notas_contacto`, `campana_seo`, `objetivo_seo`, `prioridad`, `ip_origen`, `pais_origen`, `tld_origen`, `indexacion_origen`, `semrush_id`, `ahrefs_id`, `majestic_id`, `notas_internas`, `fecha_creacion`) VALUES
(1, 'https://www.marmataro.dev', 'https://yaninaparisi.com', 'Projecte de la web de la Psicòloga Yanina Parisi', 'marcmataro.dev', 70, 70, 90, 80, NULL, NULL, 10, 'es', 'blog_comentario', 'Pàgina web del desenvolupador de la web i que mostra el meu projecte', 'contenido', 1, 0, 0, 'media', 'regular', NULL, '2025-10-08', NULL, 'activo', NULL, 0, 0, NULL, 0, NULL, 0, NULL, 'Promoció Marc', 'autoridad', 'media', NULL, NULL, NULL, 'desconocido', NULL, NULL, NULL, 'Prueba de backlink', '2025-10-08 06:38:37');

-- --------------------------------------------------------

--
-- Table structure for table `seo_offpage_directorios`
--

CREATE TABLE `seo_offpage_directorios` (
  `id_directorio` int NOT NULL,
  `nombre` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `url` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `categoria` enum('salud','psicologia','negocios','locales','generico') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'psicologia',
  `da_directorio` tinyint UNSIGNED DEFAULT NULL,
  `costo` decimal(8,2) DEFAULT '0.00',
  `idioma` enum('ca','es','en','other') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'es',
  `nofollow` tinyint(1) DEFAULT '0',
  `permite_anchor_personalizado` tinyint(1) DEFAULT '1',
  `estado` enum('pendiente','enviado','aprobado','rechazado','activo') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'pendiente',
  `fecha_envio` date DEFAULT NULL,
  `fecha_aprobacion` date DEFAULT NULL,
  `notas` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `fecha_creacion` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `seo_onpage_paginas`
--

CREATE TABLE `seo_onpage_paginas` (
  `id_pagina` int NOT NULL,
  `url_relativa_ca` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'URL sense domini ej: /terapia-ansietat',
  `url_relativa_es` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `titulo_pagina` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'T??tol visible de la p??gina',
  `tipo_pagina` enum('home','sobre-mi','servicios','blog','articulo','contacto','legal','landing') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `title_ca` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `meta_description_ca` varchar(160) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `h1_ca` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `contenido_principal_ca` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `title_es` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `meta_description_es` varchar(160) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `h1_es` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `contenido_principal_es` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `breadcrumb_json` json DEFAULT NULL COMMENT 'Estructura jer??rquica de migues de pa',
  `slug_ca` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `slug_es` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `parent_id` int DEFAULT NULL COMMENT 'P??gina pare si existeix jerarquia',
  `meta_robots` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'index, follow',
  `canonical_url_ca` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Si ??s diferent a la URL relativa',
  `canonical_url_es` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `priority` enum('1.0','0.8','0.6','0.4','0.2') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '0.8',
  `changefreq` enum('always','hourly','daily','weekly','monthly','yearly','never') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'monthly',
  `focus_keyword_ca` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `focus_keyword_es` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `keywords_secundarias_ca` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `keywords_secundarias_es` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `schema_json` json DEFAULT NULL COMMENT 'Structured data espec??fic de la p??gina',
  `og_title_ca` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `og_title_es` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `og_description_ca` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `og_description_es` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `og_image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Imatge espec??fica, sin?? usa la global',
  `twitter_title_ca` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `twitter_title_es` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `twitter_description_ca` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `twitter_description_es` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `twitter_image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `featured_image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alt_image_ca` varchar(125) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alt_image_es` varchar(125) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image_caption_ca` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image_caption_es` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `seo_score` tinyint DEFAULT '0' COMMENT 'Puntuaci?? SEO 0-100',
  `word_count_ca` int DEFAULT '0',
  `word_count_es` int DEFAULT '0',
  `densidad_keyword_ca` decimal(4,2) DEFAULT '0.00',
  `densidad_keyword_es` decimal(4,2) DEFAULT '0.00',
  `activa` tinyint(1) DEFAULT '1',
  `fecha_publicacion` datetime DEFAULT NULL,
  `fecha_ultima_actualizacion` datetime DEFAULT NULL,
  `fecha_creacion` datetime DEFAULT CURRENT_TIMESTAMP,
  `fecha_modificacion` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Configuraci?? SEO espec??fica per cada p??gina';

--
-- Dumping data for table `seo_onpage_paginas`
--

INSERT INTO `seo_onpage_paginas` (`id_pagina`, `url_relativa_ca`, `url_relativa_es`, `titulo_pagina`, `tipo_pagina`, `title_ca`, `meta_description_ca`, `h1_ca`, `contenido_principal_ca`, `title_es`, `meta_description_es`, `h1_es`, `contenido_principal_es`, `breadcrumb_json`, `slug_ca`, `slug_es`, `parent_id`, `meta_robots`, `canonical_url_ca`, `canonical_url_es`, `priority`, `changefreq`, `focus_keyword_ca`, `focus_keyword_es`, `keywords_secundarias_ca`, `keywords_secundarias_es`, `schema_json`, `og_title_ca`, `og_title_es`, `og_description_ca`, `og_description_es`, `og_image`, `twitter_title_ca`, `twitter_title_es`, `twitter_description_ca`, `twitter_description_es`, `twitter_image`, `featured_image`, `alt_image_ca`, `alt_image_es`, `image_caption_ca`, `image_caption_es`, `seo_score`, `word_count_ca`, `word_count_es`, `densidad_keyword_ca`, `densidad_keyword_es`, `activa`, `fecha_publicacion`, `fecha_ultima_actualizacion`, `fecha_creacion`) VALUES
(1, '/ca/home.php', '/es/home.php', 'Pàgina d\'Inici', 'home', 'La teva psicòloga a Girona | Teràpia online | Yanina Parisi', 'Psicòloga col·legiada especialitzada en teràpia de parella, adults i psicologia judicial a Girona. Primera sessió gratuïta.', 'Soc la teva psicòloga a Girona', '', 'Psicóloga online | Terapia online en España | Yanina Parisi', 'Psicóloga online en España para adultos. Terapia para la ansiedad, el estrés y crisis de pareja desde casa. Reserva tu sessión hoy.', 'Soy tu psicóloga online', '', NULL, 'inici', 'inicio', NULL, 'index, follow', 'https://www.yaninaparisi.com/ca/home.php', 'https://www.yaninaparisi.com/es/home.php', '1.0', 'monthly', 'psicòloga a girona, psicòloga online, teràpia onli', 'psicóloga girona', '', '', NULL, 'La teva psicòloga a Girona | Teràpia online | Yanina Parisi', 'Psicóloga online | Terapia online en España | Yanina Parisi', 'Psicòloga col·legiada especialitzada en teràpia de parella, adults i psicologia judicial a Girona. Primera sessió gratuïta.', 'Psicóloga online en España para adultos. Terapia para la ansiedad, el estrés y crisis de pareja desde casa. Reserva tu sessión hoy.', '', 'La teva psicòloga a Girona | Teràpia online | Yanina Parisi', 'Psicóloga online | Terapia online en España | Yanina Parisi', 'Psicòloga col·legiada especialitzada en teràpia de parella, adults i psicologia judicial a Girona. Primera sessió gratuïta.', 'Psicóloga online en España para adultos. Terapia para la ansiedad, el estrés y crisis de pareja desde casa. Reserva tu sessión hoy.', '', '', 'La teva psicòloga a Girona | Teràpia online | Yanina Parisi', 'Psicóloga online | Terapia online en España | Yanina Parisi', NULL, NULL, 50, 0, 0, '0.00', '0.00', 1, '0000-00-00 00:00:00', NULL, '2025-10-07 16:26:43'),
(2, '/ca/sobremi.php', '/es/sobremi.php', 'Sobre mí', 'landing', 'Sobre Mí - Yanina Parisi | Psic??loga Col??legiada', 'Coneix m??s sobre la meva experi??ncia professional com a psic??loga col??legiada especialitzada en ter??pia de parella i psicologia judicial.', 'Sobre M?? - Yanina Parisi', '', 'Sobre M?? - Yanina Parisi | Psic??loga Colegiada', 'Conoce m??s sobre mi experiencia profesional como psic??loga colegiada especializada en terapia de pareja y psicolog??a judicial.', 'Sobre M?? - Yanina Parisi', '', NULL, 'sobre-mi', 'sobre-mi', NULL, 'index, follow', '', '', '0.8', 'monthly', 'psic??loga col??legiada girona', 'psic??loga colegiada girona', '', '', NULL, 'Sobre M?? - Yanina Parisi | Psic??loga Col??legiada', 'Sobre M?? - Yanina Parisi | Psic??loga Colegiada', 'Coneix m??s sobre la meva experi??ncia professional com a psic??loga col??legiada especialitzada en ter??pia de parella i psicologia judicial.', 'Conoce m??s sobre mi experiencia profesional como psic??loga colegiada especializada en terapia de pareja y psicolog??a judicial.', '', 'Sobre M?? - Yanina Parisi | Psic??loga Col??legiada', 'Sobre M?? - Yanina Parisi | Psic??loga Colegiada', 'Coneix m??s sobre la meva experi??ncia professional com a psic??loga col??legiada especialitzada en ter??pia de parella i psicologia judicial.', 'Conoce m??s sobre mi experiencia profesional como psic??loga colegiada especializada en terapia de pareja y psicolog??a judicial.', '', '', '', '', NULL, NULL, 50, 0, 0, '0.00', '0.00', 1, '0000-00-00 00:00:00', NULL, '2025-10-07 16:26:43'),
(3, '/ca/contacta.php', '/es/contacta.php', 'Yanina Parisi | Contacta', 'contacto', 'Contacte - Primera Sessió Gratuïta | Yanina Parisi', 'Posa\'t en contacte per reservar la teva primera sessió gratuïta. Psicòloga a Girona especialitzada en teràpia de parella i adults.', 'Contacte - Primera Sessií Gratuïta', '', 'Contacto - Primera Sesión Gratuita | Yanina Parisi', 'Ponte en contacto para reservar tu primera sesión gratuita. Psicóloga en Girona especializada en terapia de pareja y adultos.', 'Contacto - Primera Sesión Gratuita', '', NULL, 'contacte', 'contacto', NULL, 'index, follow', 'www.yaninaparisi.com/es/contacta.php', 'www.yaninaparisi.com/ca/contacta.php', '0.8', 'weekly', 'psicòloga girona contacte', 'psicóloga girona contacto', '', '', NULL, 'Contacte - Primera Sessió Gratuïta | Yanina Parisi', 'Contacto - Primera Sesión Gratuita | Yanina Parisi', 'Posa\'t en contacte per reservar la teva primera sessió gratuïta. Psicòloga a Girona especialitzada en teràpia de parella i adults.', 'Ponte en contacto para reservar tu primera sesión gratuita. Psicóloga en Girona especializada en terapia de pareja y adultos.', '', 'Contacte - Primera Sessió Gratuïta | Yanina Parisi', 'Contacto - Primera Sesión Gratuita | Yanina Parisi', 'Posa\'t en contacte per reservar la teva primera sessió gratuïta. Psicòloga a Girona especialitzada en teràpia de parella i adults.', 'Ponte en contacto para reservar tu primera sesión gratuita. Psicóloga en Girona especializada en terapia de pareja y adultos.', '', '', '', '', NULL, NULL, 50, 0, 0, '0.00', '0.00', 1, '0000-00-00 00:00:00', NULL, '2025-10-07 16:26:43'),
(4, '/ca/clinica.php', '/es/clinica.php', 'Servicios psicológicos en Girona y online | Terapia para adultos y parejas | Yanina Parisi', 'servicios', 'Serveis psicològics a Girona i online | Teràpia per adults i', 'Psicòloga a Girona i online. Teràpia per a l\'ansietat, depressió i teràpia de parella. Atenció personalitzada en català. Demana la teva visita.', 'Els meus serveis de psicologia i teràpia', '', 'Servicios psicológicos en Girona y online | Terapia para adu', 'Psicóloga en Girona y online. Terapia para ansiedad, depresión y terapia de pareja. Atención personalizada en castellano. Solicita tu consulta.', 'Mis servicios de psicología y terapia', '', NULL, 'els-meus-serveis-de-psicologia-i-terapia', 'mis-servicios-de-psicologia-y-terapia', NULL, 'index, follow', 'https://yaninaparisi.com/ca/clinica.php', 'https://yaninaparisi.com/es/clinica.php', '0.8', 'monthly', 'serveis psicològics i teràpia', 'servicios psicológicos y terapia', 'terapia ansietat, estrés, despressió, de parella', 'terapia ansiedad, estrés, depresión, de pareja', NULL, 'Serveis psicològics a Girona i online | Teràpia per adults i parelles | Yanina Parisi', 'Servicios psicológicos en Girona y online | Terapia para adultos y parejas | Yanina Parisi', 'Psicòloga a Girona i online. Teràpia per a l\'ansietat, depressió i teràpia de parella. Atenció personalitzada en català. Demana la teva visita.', 'Psicóloga en Girona y online. Terapia para ansiedad, depresión y terapia de pareja. Atención personalizada en castellano. Solicita tu consulta.', '', 'Serveis psicològics a Girona i online | Teràpia per adults i parelles | Yanina Parisi', 'Servicios psicológicos en Girona y online | Terapia para adultos y parejas | Yanina Parisi', 'Psicòloga a Girona i online. Teràpia per a l\'ansietat, depressió i teràpia de parella. Atenció personalitzada en català. Demana la teva visita.', 'Psicóloga en Girona y online. Terapia para ansiedad, depresión y terapia de pareja. Atención personalizada en castellano. Solicita tu consulta.', '', '', 'Serveis psicològics a Girona i online | Teràpia per adults i parelles | Yanina Parisi', 'Servicios psicológicos en Girona y online | Terapia para adultos y parejas | Yanina Parisi', NULL, NULL, 50, 0, 0, '0.00', '0.00', 1, '2025-11-03 00:00:00', NULL, '2025-11-03 11:54:00'),
(5, '/ca/blog.php', '/es/blog.php', 'Blog de Psicología | Artículos y Recursos para tu Salud Mental', 'blog', 'Blog de psicologia | Articles i recursos per a la teva salut', 'Descobreix tots els meus articles de psicologia. Ansietat, depressió, relacions, autoestima i més. Recursos práctics per millorar la teva salut mental.', 'El meu blog de psicologia i benestar emocional', '', 'Blog de psicología | Artículos y recursos para tu salud ment', 'Descubre todos mis artículos de psicología. Ansiedad, depresión, relaciones, autoestima y más. Recursos prácticos para mejorar tu salud mental.', 'Mi blog de psicología y bienestar emocional', '', NULL, 'el-meu-blog-de-psicologia-i-benestar-emocional', 'mi-blog-de-psicologia-y-bienestar-emocional', NULL, 'index, follow', 'https://www.yaninaparisi.com/ca/blog.php', 'https://www.yaninaparisi.com/es/blog.php', '0.8', 'monthly', 'Blog de psicologia', 'Blog de psicología', 'arxiu del blog de psicologia  tots els articles de psicologia  recursos de psicologia en línia  biblioteca de psicologia  llistat d\'articles de salut mental  entrades del blog psicològic', 'archivo del blog de psicología  todos los artículos de psicología  recursos de psicología online  biblioteca de psicología  listado de artículos de salud mental', NULL, 'Blog de psicologia | Articles i recursos per a la teva salut mental', 'Blog de psicología | Artículos y recursos para tu salud mental', 'Descobreix tots els meus articles de psicologia. Ansietat, depressió, relacions, autoestima i més. Recursos práctics per millorar la teva salut mental.', 'Descubre todos mis artículos de psicología. Ansiedad, depresión, relaciones, autoestima y más. Recursos prácticos para mejorar tu salud mental.', '', 'Blog de psicologia | Articles i recursos per a la teva salut mental', 'Blog de psicología | Artículos y recursos para tu salud mental', 'Descobreix tots els meus articles de psicologia. Ansietat, depressió, relacions, autoestima i més. Recursos práctics per millorar la teva salut mental.', 'Descubre todos mis artículos de psicología. Ansiedad, depresión, relaciones, autoestima y más. Recursos prácticos para mejorar tu salud mental.', '', '', 'Blog de psicologia', 'Blog de psicología', NULL, NULL, 55, 0, 0, '0.00', '0.00', 1, '0000-00-00 00:00:00', NULL, '2025-11-03 16:04:53');

-- --------------------------------------------------------

--
-- Table structure for table `seo_tecnico`
--

CREATE TABLE `seo_tecnico` (
  `id_tecnico` int NOT NULL,
  `velocidad_carga_ms` int DEFAULT NULL COMMENT 'Temps de càrrega en mil·lisegons',
  `velocidad_primera_pintura` int DEFAULT NULL COMMENT 'First Paint',
  `velocidad_pintura_contenido` int DEFAULT NULL COMMENT 'First Contentful Paint',
  `velocidad_interactividad` int DEFAULT NULL COMMENT 'Time to Interactive',
  `puntuacion_lighthouse` tinyint DEFAULT NULL COMMENT 'Puntuació 0-100',
  `core_web_vitals` json DEFAULT NULL COMMENT 'Dades de LCP, FID, CLS',
  `estado_indexacion` enum('completa','parcial','limitada','bloqueada') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'completa',
  `paginas_indexadas` int DEFAULT '0',
  `paginas_no_indexadas` int DEFAULT '0',
  `ultimo_rastreo_google` date DEFAULT NULL,
  `frecuencia_rastreo` enum('diaria','semanal','mensual','poco_frecuente') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'semanal',
  `estructura_url` enum('amigable','parametrica','mixta') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'amigable',
  `urls_canonicas_incorrectas` int DEFAULT '0',
  `urls_duplicadas` int DEFAULT '0',
  `parametros_url_problematicos` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'Paràmetres que generen contingut duplicat',
  `sitemap_existe` tinyint(1) DEFAULT '1',
  `sitemap_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '/sitemap.xml',
  `sitemap_ultima_actualizacion` date DEFAULT NULL,
  `sitemap_urls_total` int DEFAULT '0',
  `sitemap_urls_indexables` int DEFAULT '0',
  `robots_txt_existe` tinyint(1) DEFAULT '1',
  `robots_txt_configurado` tinyint(1) DEFAULT '0',
  `bloqueos_inecesarios` int DEFAULT '0' COMMENT 'Recursos bloqueats incorrectament',
  `ssl_activo` tinyint(1) DEFAULT '1',
  `ssl_valido` tinyint(1) DEFAULT '1',
  `ssl_tipo` enum('dv','ov','ev') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'dv',
  `ssl_caducidad` date DEFAULT NULL,
  `http2_activo` tinyint(1) DEFAULT '1',
  `mobile_friendly` tinyint(1) DEFAULT '1',
  `viewport_configurado` tinyint(1) DEFAULT '1',
  `tap_targets_adecuados` tinyint(1) DEFAULT '1',
  `font_sizes_legibles` tinyint(1) DEFAULT '1',
  `profundidad_maxima` tinyint DEFAULT '3',
  `enlaces_rotos` int DEFAULT '0',
  `enlaces_internos_total` int DEFAULT '0',
  `enlaces_salientes_total` int DEFAULT '0',
  `arquitectura_optimizada` tinyint(1) DEFAULT '1',
  `headers_seguridad` json DEFAULT NULL COMMENT 'CSP, HSTS, X-Frame-Options, etc.',
  `vulnerabilidades_detectadas` int DEFAULT '0',
  `proteccion_malware` tinyint(1) DEFAULT '1',
  `hreflang_implementado` tinyint(1) DEFAULT '1',
  `hreflang_errores` int DEFAULT '0',
  `geotargeting_configurado` tinyint(1) DEFAULT '0',
  `ccTLDs_implementados` tinyint(1) DEFAULT '0',
  `schema_implementado` tinyint(1) DEFAULT '1',
  `schema_errores` int DEFAULT '0',
  `rich_results_activos` tinyint(1) DEFAULT '0',
  `tipo_rich_results` json DEFAULT NULL COMMENT 'Tipus de resultats enriquits detectats',
  `imagenes_optimizadas` tinyint(1) DEFAULT '1',
  `imagenes_sin_alt` int DEFAULT '0',
  `lazy_loading_activo` tinyint(1) DEFAULT '1',
  `webp_soportado` tinyint(1) DEFAULT '1',
  `cache_implementado` tinyint(1) DEFAULT '1',
  `cdn_activo` tinyint(1) DEFAULT '0',
  `tiempo_cache_browser` int DEFAULT '86400' COMMENT 'En segons',
  `compresion_activa` tinyint(1) DEFAULT '1',
  `uptime_30d` decimal(5,2) DEFAULT '99.99' COMMENT 'Percentatge',
  `errores_404` int DEFAULT '0',
  `errores_500` int DEFAULT '0',
  `redirects_encadenados` int DEFAULT '0',
  `google_search_console_configurado` tinyint(1) DEFAULT '1',
  `google_analytics_configurado` tinyint(1) DEFAULT '1',
  `google_tag_manager_configurado` tinyint(1) DEFAULT '1',
  `google_business_profile_configurado` tinyint(1) DEFAULT '0',
  `ultima_auditoria_completa` datetime DEFAULT NULL,
  `puntuacion_seo_tecnico` tinyint DEFAULT '0' COMMENT '0-100',
  `criticidad_issues` enum('critica','alta','media','baja') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'media',
  `fecha_creacion` datetime DEFAULT CURRENT_TIMESTAMP,
  `fecha_actualizacion` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id_sessio` int NOT NULL,
  `id_pacient` int NOT NULL,
  `data_sessio` date NOT NULL,
  `hora_inici` time NOT NULL,
  `hora_fi` time NOT NULL,
  `tipus_sessio` enum('Individual','Pareja','Familiar','Grupo') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Individual',
  `estat_sessio` enum('Programada','Realizada','Cancelada','No asistida') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Programada',
  `ubicacio` enum('Presencial','Online') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Presencial',
  `preu_sessio` decimal(8,2) NOT NULL,
  `notes_terapeuta` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `data_creacio` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tarifes`
--

CREATE TABLE `tarifes` (
  `id_tarifa` int NOT NULL,
  `nom_servei_ca` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nom_servei_es` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipus_servei` enum('individual','pareja','familiar','grupo','evaluación','urgente','pack') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcio_ca` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcio_es` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `durada_minuts` int NOT NULL COMMENT 'Durada en minuts de la sessió',
  `preu_base` decimal(8,2) NOT NULL COMMENT 'Preu normal',
  `preu_promocio` decimal(8,2) DEFAULT NULL COMMENT 'Preu en promoció',
  `iva_percentatge` decimal(4,2) DEFAULT '21.00' COMMENT 'Percentatge d''IVA',
  `moneda` varchar(3) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'EUR',
  `disponible` tinyint(1) DEFAULT '1',
  `visible_web` tinyint(1) DEFAULT '1',
  `destacat` tinyint(1) DEFAULT '0' COMMENT 'Si apareix com a servei destacat',
  `modalitat` enum('presencial','online','híbrida','telefónica') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'presencial',
  `sessions_pack` tinyint DEFAULT '1' COMMENT 'Nombre de sessions si és un pack',
  `validesa_dies` int DEFAULT NULL COMMENT 'Dies de validesa des de la compra',
  `requisits` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'Requisits o condicions especials',
  `beneficios_ca` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'Beneficis d''aquest servei',
  `beneficios_es` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'Beneficios de este servicio',
  `ordre_visualitzacio` int DEFAULT '0',
  `color_etiqueta` varchar(7) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '#3B82F6' COMMENT 'Color per a la web',
  `vegades_contractat` int DEFAULT '0',
  `data_creacio` datetime DEFAULT CURRENT_TIMESTAMP,
  `data_actualitzacio` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `data_inici_promocio` date DEFAULT NULL,
  `data_fi_promocio` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `usuarios_panel`
--

CREATE TABLE `usuarios_panel` (
  `id_usuario` int NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `apellidos` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `telefono` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `avatar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rol` enum('superadmin','admin','editor','seo_manager','viewer') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'editor',
  `permisos` json DEFAULT NULL COMMENT 'Permisos específics sobre mòduls',
  `activo` tinyint(1) DEFAULT '1',
  `ultimo_acceso` datetime DEFAULT NULL,
  `fecha_expiracion` date DEFAULT NULL COMMENT 'Data de caducitat del compte',
  `intentos_login` tinyint DEFAULT '0',
  `bloqueado` tinyint(1) DEFAULT '0',
  `idioma` enum('ca','es','en') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'ca',
  `zona_horiana` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Europe/Madrid',
  `notificaciones_email` tinyint(1) DEFAULT '1',
  `notificaciones_push` tinyint(1) DEFAULT '1',
  `token_restablecimiento` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `token_expiracion` datetime DEFAULT NULL,
  `two_factor_auth` tinyint(1) DEFAULT '0',
  `two_factor_secret` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha_creacion` datetime DEFAULT CURRENT_TIMESTAMP,
  `fecha_actualizacion` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `creado_por` int DEFAULT NULL COMMENT 'Usuari que va crear aquest compte'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `usuarios_panel`
--

INSERT INTO `usuarios_panel` (`id_usuario`, `email`, `password_hash`, `nombre`, `apellidos`, `telefono`, `avatar`, `rol`, `permisos`, `activo`, `ultimo_acceso`, `fecha_expiracion`, `intentos_login`, `bloqueado`, `idioma`, `zona_horiana`, `notificaciones_email`, `notificaciones_push`, `token_restablecimiento`, `token_expiracion`, `two_factor_auth`, `two_factor_secret`, `fecha_creacion`, `creado_por`) VALUES
(1, 'marcmataro@gmail.com', '$2y$12$aNnVyrdzHqmw.xKsvh2qD.USM7WjIRIT0QZYUAdrPR50oLrmmUD4W', 'Marc', 'Mataró Malleu', NULL, NULL, 'superadmin', NULL, 1, '2025-12-30 20:39:16', NULL, 0, 0, 'ca', 'Europe/Madrid', 1, 1, NULL, NULL, 0, NULL, '2025-10-08 18:10:04', NULL),
(3, 'info@yaninaparisi.com', '$2y$12$XctNfnrqr74SQqTxw5yMTO23ZvyoTTn8eIm6lX1aGnCt/XAjW1Iue', 'Yanina', 'Parisi Faranna', '', NULL, 'admin', '{}', 1, '2025-12-30 20:35:03', NULL, 0, 0, 'es', 'Europe/Madrid', 0, 1, NULL, NULL, 0, NULL, '2025-11-18 10:10:06', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `blog_entrades`
--
ALTER TABLE `blog_entrades`
  ADD PRIMARY KEY (`id_entrada`),
  ADD UNIQUE KEY `slug_ca` (`slug_ca`),
  ADD UNIQUE KEY `slug_es` (`slug_es`),
  ADD KEY `idx_slug_ca` (`slug_ca`),
  ADD KEY `idx_slug_es` (`slug_es`),
  ADD KEY `idx_estat` (`estat`),
  ADD KEY `idx_data_publicacio` (`data_publicacio`),
  ADD KEY `idx_autor` (`id_autor`),
  ADD KEY `idx_visible` (`visible`),
  ADD KEY `idx_visualitzacions` (`visualitzacions`);
ALTER TABLE `blog_entrades` ADD FULLTEXT KEY `idx_contingut_ca` (`contingut_ca`);
ALTER TABLE `blog_entrades` ADD FULLTEXT KEY `idx_contingut_es` (`contingut_es`);
ALTER TABLE `blog_entrades` ADD FULLTEXT KEY `idx_titol_ca` (`titol_ca`);
ALTER TABLE `blog_entrades` ADD FULLTEXT KEY `idx_titol_es` (`titol_es`);

--
-- Indexes for table `blog_entrades_categories`
--
ALTER TABLE `blog_entrades_categories`
  ADD PRIMARY KEY (`id_relacio`),
  ADD UNIQUE KEY `unique_entrada_categoria` (`id_entrada`,`id_categoria`),
  ADD KEY `idx_entrada` (`id_entrada`),
  ADD KEY `idx_categoria` (`id_categoria`);

--
-- Indexes for table `blog_entrades_etiquetes`
--
ALTER TABLE `blog_entrades_etiquetes`
  ADD PRIMARY KEY (`id_relacio`),
  ADD UNIQUE KEY `unique_entrada_etiqueta` (`id_entrada`,`id_etiqueta`),
  ADD KEY `idx_entrada` (`id_entrada`),
  ADD KEY `idx_etiqueta` (`id_etiqueta`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id_category`),
  ADD UNIQUE KEY `slug_ca` (`slug_ca`),
  ADD UNIQUE KEY `slug_es` (`slug_es`),
  ADD KEY `idx_slug_ca` (`slug_ca`),
  ADD KEY `idx_slug_es` (`slug_es`),
  ADD KEY `idx_activ` (`activa`),
  ADD KEY `idx_ordre` (`ordre`);

--
-- Indexes for table `etiquetes`
--
ALTER TABLE `etiquetes`
  ADD PRIMARY KEY (`id_etiqueta`),
  ADD UNIQUE KEY `slug_ca` (`slug_ca`),
  ADD UNIQUE KEY `slug_es` (`slug_es`),
  ADD KEY `idx_slug_ca` (`slug_ca`),
  ADD KEY `idx_slug_es` (`slug_es`),
  ADD KEY `idx_activ` (`activa`),
  ADD KEY `idx_ordre` (`ordre`);

--
-- Indexes for table `faqs`
--
ALTER TABLE `faqs`
  ADD PRIMARY KEY (`id_faq`),
  ADD UNIQUE KEY `slug_ca` (`slug_ca`),
  ADD UNIQUE KEY `slug_es` (`slug_es`),
  ADD KEY `idx_categoria` (`categoria`),
  ADD KEY `idx_activ` (`activa`),
  ADD KEY `idx_destacada` (`destacada`),
  ADD KEY `idx_ordre` (`ordre`),
  ADD KEY `idx_slug_ca` (`slug_ca`),
  ADD KEY `idx_slug_es` (`slug_es`);

--
-- Indexes for table `info_psicologa`
--
ALTER TABLE `info_psicologa`
  ADD PRIMARY KEY (`id_info`),
  ADD KEY `idx_email_professional` (`email_professional`),
  ADD KEY `idx_num_collegiat` (`num_collegiat`);

--
-- Indexes for table `pacients`
--
ALTER TABLE `pacients`
  ADD PRIMARY KEY (`id_pacient`),
  ADD KEY `idx_cognoms` (`cognoms`),
  ADD KEY `idx_estat` (`estat`);

--
-- Indexes for table `pagaments`
--
ALTER TABLE `pagaments`
  ADD PRIMARY KEY (`id_pagament`),
  ADD KEY `id_sessio` (`id_sessio`),
  ADD KEY `idx_data_pagament` (`data_pagament`),
  ADD KEY `idx_estat` (`estat`),
  ADD KEY `idx_facturat` (`facturat`);

--
-- Indexes for table `ressenya_tokens`
--
ALTER TABLE `ressenya_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `idx_pacient_id` (`pacient_id`),
  ADD KEY `idx_token` (`token`);

--
-- Indexes for table `ressenyes`
--
ALTER TABLE `ressenyes`
  ADD PRIMARY KEY (`id_ressenya`),
  ADD KEY `idx_estat` (`estat`),
  ADD KEY `idx_puntuacio` (`puntuacio`),
  ADD KEY `idx_data_terapia` (`data_terapia`),
  ADD KEY `idx_tipus_terapia` (`tipus_terapia`),
  ADD KEY `idx_verificada` (`verificada`),
  ADD KEY `idx_data_creacio` (`data_creacio`),
  ADD KEY `idx_pacient_id` (`pacient_id`);
ALTER TABLE `ressenyes` ADD FULLTEXT KEY `idx_text_ca` (`text_ressenya_ca`);
ALTER TABLE `ressenyes` ADD FULLTEXT KEY `idx_text_es` (`text_ressenya_es`);

--
-- Indexes for table `seo_global`
--
ALTER TABLE `seo_global`
  ADD PRIMARY KEY (`id_global`);

--
-- Indexes for table `seo_offpage`
--
ALTER TABLE `seo_offpage`
  ADD PRIMARY KEY (`id_offpage`),
  ADD UNIQUE KEY `idx_enlace_unico` (`url_origen`(200),`url_destino`(200)),
  ADD KEY `idx_dominio_origen` (`dominio_origen`),
  ADD KEY `idx_tipo_backlink` (`tipo_backlink`),
  ADD KEY `idx_estado` (`estado`),
  ADD KEY `idx_fecha_descubrimiento` (`fecha_descubrimiento`),
  ADD KEY `idx_da_origen` (`da_origen`),
  ADD KEY `idx_relevancia` (`relevancia_tematica`),
  ADD KEY `idx_campana` (`campana_seo`),
  ADD KEY `idx_anchor_text` (`anchor_text`(100)),
  ADD KEY `idx_url_destino` (`url_destino`(100)),
  ADD KEY `idx_prioridad` (`prioridad`),
  ADD KEY `idx_fecha_verificacion` (`fecha_ultima_verificacion`);

--
-- Indexes for table `seo_offpage_directorios`
--
ALTER TABLE `seo_offpage_directorios`
  ADD PRIMARY KEY (`id_directorio`);

--
-- Indexes for table `seo_onpage_paginas`
--
ALTER TABLE `seo_onpage_paginas`
  ADD PRIMARY KEY (`id_pagina`),
  ADD UNIQUE KEY `url_relativa` (`url_relativa_ca`),
  ADD UNIQUE KEY `slug_ca` (`slug_ca`),
  ADD UNIQUE KEY `slug_es` (`slug_es`),
  ADD KEY `idx_url_relativa` (`url_relativa_ca`),
  ADD KEY `idx_tipo_pagina` (`tipo_pagina`),
  ADD KEY `idx_activa` (`activa`),
  ADD KEY `idx_slug_ca` (`slug_ca`),
  ADD KEY `idx_slug_es` (`slug_es`),
  ADD KEY `idx_parent_id` (`parent_id`),
  ADD KEY `idx_fecha_publicacion` (`fecha_publicacion`),
  ADD KEY `idx_focus_keyword_ca` (`focus_keyword_ca`),
  ADD KEY `idx_focus_keyword_es` (`focus_keyword_es`);
ALTER TABLE `seo_onpage_paginas` ADD FULLTEXT KEY `idx_contenido_ca` (`contenido_principal_ca`);
ALTER TABLE `seo_onpage_paginas` ADD FULLTEXT KEY `idx_contenido_es` (`contenido_principal_es`);

--
-- Indexes for table `seo_tecnico`
--
ALTER TABLE `seo_tecnico`
  ADD PRIMARY KEY (`id_tecnico`),
  ADD KEY `idx_puntuacion` (`puntuacion_seo_tecnico`),
  ADD KEY `idx_ultima_auditoria` (`ultima_auditoria_completa`),
  ADD KEY `idx_criticidad` (`criticidad_issues`),
  ADD KEY `idx_velocidad` (`velocidad_carga_ms`),
  ADD KEY `idx_indexacion` (`estado_indexacion`),
  ADD KEY `idx_mobile` (`mobile_friendly`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id_sessio`),
  ADD KEY `idx_data_sessio` (`data_sessio`),
  ADD KEY `idx_pacient_data` (`id_pacient`,`data_sessio`),
  ADD KEY `idx_estat` (`estat_sessio`);

--
-- Indexes for table `tarifes`
--
ALTER TABLE `tarifes`
  ADD PRIMARY KEY (`id_tarifa`),
  ADD KEY `idx_tipus_servei` (`tipus_servei`),
  ADD KEY `idx_disponible` (`disponible`),
  ADD KEY `idx_visible_web` (`visible_web`),
  ADD KEY `idx_destacat` (`destacat`),
  ADD KEY `idx_modalitat` (`modalitat`),
  ADD KEY `idx_ordre` (`ordre_visualitzacio`),
  ADD KEY `idx_preu_base` (`preu_base`),
  ADD KEY `idx_data_promocio` (`data_inici_promocio`,`data_fi_promocio`);

--
-- Indexes for table `usuarios_panel`
--
ALTER TABLE `usuarios_panel`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `creado_por` (`creado_por`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_rol` (`rol`),
  ADD KEY `idx_activo` (`activo`),
  ADD KEY `idx_ultimo_acceso` (`ultimo_acceso`),
  ADD KEY `idx_bloqueado` (`bloqueado`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `blog_entrades`
--
ALTER TABLE `blog_entrades`
  MODIFY `id_entrada` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `blog_entrades_categories`
--
ALTER TABLE `blog_entrades_categories`
  MODIFY `id_relacio` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `blog_entrades_etiquetes`
--
ALTER TABLE `blog_entrades_etiquetes`
  MODIFY `id_relacio` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id_category` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `etiquetes`
--
ALTER TABLE `etiquetes`
  MODIFY `id_etiqueta` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `faqs`
--
ALTER TABLE `faqs`
  MODIFY `id_faq` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `info_psicologa`
--
ALTER TABLE `info_psicologa`
  MODIFY `id_info` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pacients`
--
ALTER TABLE `pacients`
  MODIFY `id_pacient` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `pagaments`
--
ALTER TABLE `pagaments`
  MODIFY `id_pagament` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `ressenya_tokens`
--
ALTER TABLE `ressenya_tokens`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `ressenyes`
--
ALTER TABLE `ressenyes`
  MODIFY `id_ressenya` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `seo_global`
--
ALTER TABLE `seo_global`
  MODIFY `id_global` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `seo_offpage`
--
ALTER TABLE `seo_offpage`
  MODIFY `id_offpage` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `seo_offpage_directorios`
--
ALTER TABLE `seo_offpage_directorios`
  MODIFY `id_directorio` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `seo_onpage_paginas`
--
ALTER TABLE `seo_onpage_paginas`
  MODIFY `id_pagina` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `seo_tecnico`
--
ALTER TABLE `seo_tecnico`
  MODIFY `id_tecnico` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sessions`
--
ALTER TABLE `sessions`
  MODIFY `id_sessio` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `tarifes`
--
ALTER TABLE `tarifes`
  MODIFY `id_tarifa` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `usuarios_panel`
--
ALTER TABLE `usuarios_panel`
  MODIFY `id_usuario` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `blog_entrades_categories`
--
ALTER TABLE `blog_entrades_categories`
  ADD CONSTRAINT `blog_entrades_categories_ibfk_1` FOREIGN KEY (`id_entrada`) REFERENCES `blog_entrades` (`id_entrada`) ON DELETE CASCADE,
  ADD CONSTRAINT `blog_entrades_categories_ibfk_2` FOREIGN KEY (`id_categoria`) REFERENCES `categories` (`id_category`) ON DELETE CASCADE;

--
-- Constraints for table `blog_entrades_etiquetes`
--
ALTER TABLE `blog_entrades_etiquetes`
  ADD CONSTRAINT `blog_entrades_etiquetes_ibfk_1` FOREIGN KEY (`id_entrada`) REFERENCES `blog_entrades` (`id_entrada`) ON DELETE CASCADE,
  ADD CONSTRAINT `blog_entrades_etiquetes_ibfk_2` FOREIGN KEY (`id_etiqueta`) REFERENCES `etiquetes` (`id_etiqueta`) ON DELETE CASCADE;

--
-- Constraints for table `pagaments`
--
ALTER TABLE `pagaments`
  ADD CONSTRAINT `pagaments_ibfk_1` FOREIGN KEY (`id_sessio`) REFERENCES `sessions` (`id_sessio`) ON DELETE RESTRICT;

--
-- Constraints for table `seo_onpage_paginas`
--
ALTER TABLE `seo_onpage_paginas`
  ADD CONSTRAINT `seo_onpage_paginas_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `seo_onpage_paginas` (`id_pagina`) ON DELETE SET NULL;

--
-- Constraints for table `sessions`
--
ALTER TABLE `sessions`
  ADD CONSTRAINT `sessions_ibfk_1` FOREIGN KEY (`id_pacient`) REFERENCES `pacients` (`id_pacient`) ON DELETE RESTRICT;

--
-- Constraints for table `usuarios_panel`
--
ALTER TABLE `usuarios_panel`
  ADD CONSTRAINT `usuarios_panel_ibfk_1` FOREIGN KEY (`creado_por`) REFERENCES `usuarios_panel` (`id_usuario`);
COMMIT;
