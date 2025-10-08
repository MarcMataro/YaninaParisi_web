# SEO On-Page - Sistema de Gestión de Páginas

## Descripción

Sistema completo de gestión SEO para páginas individuales del sitio web. Permite crear, editar y eliminar configuraciones SEO específicas para cada página con soporte bilingüe (Català/Español).

## Características

### 🎯 Gestión de Páginas
- **Lista completa** de páginas con filtros por tipo
- **Puntuación SEO** (0-100) visible para cada página
- **Estados** activo/inactivo
- **Filtrado** por tipo de página
- **Búsqueda** y organización

### 📝 Configuración SEO Completa

#### 1. Identificación y URL
- Título interno de la página
- URL relativa
- Tipo de página (Home, Sobre Mí, Servicios, Blog, Artículo, Contacto, Legal, Landing)
- Slugs bilingües (CA/ES)

#### 2. SEO Básico (Català)
- Meta Title (contador de caracteres: 60)
- Meta Description (contador de caracteres: 160)
- H1
- Keywords
- Palabra clave principal

#### 3. SEO Básico (Español)
- Meta Title (contador de caracteres: 60)
- Meta Description (contador de caracteres: 160)
- H1
- Keywords
- Palabra clave principal

#### 4. SEO Técnico
- Robots meta tag (index/noindex, follow/nofollow)
- URL canónica
- Prioridad del sitemap (0.0 - 1.0)
- Frecuencia de cambio (always, hourly, daily, weekly, monthly, yearly, never)

#### 5. Open Graph (Opcional, Colapsable)
- OG Title (CA/ES)
- OG Description (CA/ES)
- OG Image
- OG Type
- OG Locale

#### 6. Twitter Cards (Opcional, Colapsable)
- Twitter Title (CA/ES)
- Twitter Description (CA/ES)
- Twitter Image
- Twitter Card Type

#### 7. Imágenes SEO (Opcional, Colapsable)
- Imagen destacada
- Alt text (CA/ES)
- Caption (CA/ES)

#### 8. Estado y Publicación
- Estado activo/inactivo
- Fecha de publicación
- Última modificación

## Tipos de Página Disponibles

| Tipo | Uso | Color |
|------|-----|-------|
| **Home** | Página de inicio | Verde |
| **Sobre Mí** | Página sobre la psicóloga | Azul |
| **Servicios** | Página de servicios | Morado |
| **Blog** | Listado de artículos | Naranja |
| **Artículo** | Post individual del blog | Rosa |
| **Contacto** | Página de contacto | Turquesa |
| **Landing** | Landing pages especiales | Amarillo |
| **Legal** | Páginas legales (privacidad, cookies, etc.) | Gris |

## Interfaz de Usuario

### Vista Lista
- **Header** con título, descripción y botón "Nueva Página"
- **Filtros** por tipo de página con contador
- **Tabla** con las siguientes columnas:
  - Score (badge circular con color según puntuación)
  - Título (con subtítulo del meta title)
  - URL (código monoespaciado)
  - Tipo (badge con color por tipo)
  - Idiomas (badges CA/ES)
  - Fecha de publicación
  - Estado (activo/inactivo con animación)
  - Acciones (editar/eliminar)

### Vista Edición
- **Header** con botón "Volver al listado"
- **8 secciones** organizadas temáticamente
- **3 secciones colapsables** (OG, Twitter, Imágenes) para no abrumar
- **Contadores de caracteres** en títulos y descripciones
- **Validación** de campos requeridos
- **Botones** Guardar/Cancelar

## Características Técnicas

### Backend
- **Clase PHP**: `SEO_OnPage` (1,700+ líneas)
- **Tabla MySQL**: `seo_onpage_paginas` (50+ campos)
- **CRUD completo**: Crear, Leer, Actualizar, Eliminar
- **Métricas automáticas**: Word count, densidad de keywords
- **Puntuación SEO**: Cálculo automático de 0-100

### Frontend
- **CSS separado**: `onpage.css` (600+ líneas)
- **Animaciones**: Fade-in, hover effects, pulse
- **Responsive**: Adaptado a móvil, tablet y desktop
- **Accesibilidad**: Iconos descriptivos, colores contrastados

### JavaScript
- **Confirmación de eliminación**: Modal de confirmación
- **Toggle de secciones**: Expandir/colapsar secciones opcionales
- **Sin dependencias**: Vanilla JavaScript puro

## Estilos CSS

### Badges
- **Score Badge**: Circular con gradientes y sombras
  - Verde (≥80): Excelente
  - Dorado (≥60): Bueno
  - Naranja (<60): Necesita mejora

- **Type Badge**: Rectangular con gradientes por tipo
- **Lang Badge**: Cuadrado con bordes y hover
- **Status Badge**: Con animación pulse en estado activo

### Tabla
- **Hover effect**: Iluminación sutil con desplazamiento
- **Filas inactivas**: Opacidad reducida
- **Responsive**: Scroll horizontal en móvil

### Botones
- **Editar**: Azul con gradiente
- **Eliminar**: Rojo con gradiente
- **Hover**: Elevación con sombra y transform

## Flujo de Trabajo

### Crear Nueva Página
1. Clic en "Nueva Página"
2. Rellenar campos obligatorios (*, título, URL, tipo, SEO CA/ES)
3. Opcionalmente configurar OG, Twitter, Imágenes
4. Guardar → Redirección a lista con mensaje de éxito

### Editar Página Existente
1. Clic en botón "Editar" en la tabla
2. Modificar campos necesarios
3. Guardar → Actualización de métricas y score

### Eliminar Página
1. Clic en botón "Eliminar"
2. Confirmación con nombre de la página
3. Confirmación → Eliminación de BD

## Métricas y Puntuación

### Cálculo del SEO Score
El score se calcula automáticamente basándose en:
- ✅ Completitud de meta tags (CA/ES)
- ✅ Longitud óptima de títulos (50-60 caracteres)
- ✅ Longitud óptima de descripciones (150-160 caracteres)
- ✅ Presencia de H1 (CA/ES)
- ✅ Densidad de keyword principal (1-3%)
- ✅ Configuración técnica (robots, canonical)
- ✅ Open Graph completo
- ✅ Twitter Cards completo
- ✅ Imágenes con alt text

### Métricas Automáticas
- **Word Count**: Recuento de palabras en contenido (CA/ES)
- **Keyword Density**: % de aparición de keyword principal
- **Última Actualización**: Timestamp automático

## Base de Datos

### Tabla: `seo_onpage_paginas`

**Campos principales** (50+ en total):
- `id_pagina` (PK, AUTO_INCREMENT)
- `titulo_pagina`, `url_relativa`, `tipo_pagina`
- `slug_ca`, `slug_es`
- `title_ca`, `title_es`, `description_ca`, `description_es`
- `h1_ca`, `h1_es`, `keywords_ca`, `keywords_es`
- `focus_keyword_ca`, `focus_keyword_es`
- `robots_index`, `robots_follow`, `canonical_url`
- `sitemap_priority`, `sitemap_changefreq`
- `og_*`, `twitter_*`, `featured_image`, `alt_*`, `caption_*`
- `seo_score`, `word_count_*`, `keyword_density_*`
- `activa`, `fecha_publicacion`, `fecha_modificacion`

**Índices**:
- PRIMARY KEY (`id_pagina`)
- INDEX (`url_relativa`)
- INDEX (`tipo_pagina`)
- INDEX (`activa`)
- INDEX (`slug_ca`, `slug_es`)
- FULLTEXT (`contenido_principal_ca`, `contenido_principal_es`)
- FOREIGN KEY (`parent_id` → `id_pagina`) [jerarquía]

## Archivos del Sistema

```
_pcontrol/
├── gseo.php                        # Controlador principal
├── classes/
│   └── seo_onpage.php             # Clase SEO_OnPage (1,700 líneas)
├── includes/
│   ├── onpage_interface.php       # Interfaz dual (list/edit)
│   └── onpage_README.md           # Esta documentación
└── css/
    └── onpage.css                 # Estilos específicos (600 líneas)

_secret/
└── sql/
    └── seo_onpage_schema.sql      # Schema + datos iniciales
```

## Páginas Iniciales

El sistema viene con 3 páginas pre-configuradas:

1. **Pàgina d'Inici** (`/`, Home, prioridad 1.0, weekly)
2. **Sobre Mí** (`/sobre-mi`, Sobre-mi, prioridad 0.8, monthly)
3. **Contacto** (`/contacto`, Contacto, prioridad 0.8, monthly)

## Próximas Mejoras

- [ ] **Búsqueda avanzada** por título/URL/keywords
- [ ] **Acciones en lote** (activar/desactivar múltiples)
- [ ] **Editor JSON** para schema y breadcrumb
- [ ] **Auditoría SEO** automática con recomendaciones
- [ ] **Integración Google Search Console**
- [ ] **Generación automática** de sitemap.xml
- [ ] **Preview** de cómo se verá en buscadores
- [ ] **Historial de cambios** (versionado)
- [ ] **Duplicar página** como plantilla
- [ ] **Importar/Exportar** configuración

## Autor

Sistema desarrollado para **Yanina Parisi - Psicologia**

Implementación: Gestión completa de SEO On-Page con interfaz moderna y funcional.

Fecha: Diciembre 2024

---

**¿Necesitas ayuda?** Consulta la clase `SEO_OnPage` para métodos disponibles o revisa `onpage_interface.php` para la estructura de la interfaz.
