# Sistema de Gestió Off-Page SEO - Backlinks

## 📋 Descripció

Sistema complet per gestionar backlinks i enllaços externs (Off-Page SEO) amb seguiment d'autoritat, qualitat i rendiment.

## 🎯 Funcionalitats Principals

### 1. **Gestió de Backlinks**
- ✅ Crear nous backlinks
- ✅ Editar backlinks existents
- ✅ Eliminar backlinks
- ✅ Verificar estat dels backlinks
- ✅ Marcar com perdut/recuperat

### 2. **Mètriques d'Autoritat**
- **Domain Authority (DA)** - Moz
- **Domain Rating (DR)** - Ahrefs
- **Trust Flow (TF)** - Majestic
- **Citation Flow (CF)** - Majestic
- **Page Authority** de la pàgina origen
- **Tràfic mensual estimat**

### 3. **Tipus de Backlinks Suportats**
- Guest Post
- Directori
- Premsa
- Blog/Comentari
- Fòrum
- Xarxes Socials
- Recurs Útil
- Col·laboració
- Natural
- Adquirit

### 4. **Anàlisi Automàtica**
- **Quality Score** (0-100): Puntuació basada en:
  - Domain Authority (30%)
  - Trust/Citation Flow (20%)
  - Relevància temàtica (20%)
  - Posició de l'enllaç (10%)
  - Atributs REL (10%)
  - Tràfic origen (10%)

- **Valor Estimat**: Càlcul del valor monetari en euros
- **Anàlisi d'Anchor Text**: Tipus, longitud i recomanacions

### 5. **Filtres Avançats**
- Per estat (actiu, perdut, trencat, en revisió)
- Per tipus de backlink
- Per campanya SEO
- Per domini origen
- Per DA mínim
- Per URL destí

### 6. **Estadístiques Globals**
- Score global Off-Page (0-100)
- Total de backlinks actius/perduts/trencats
- DA/DR promig
- Distribució DoFollow vs NoFollow
- Clics i tràfic total
- Valor total estimat
- Top 10 dominis
- Distribució per tipus i relevància

## 📁 Estructura de Fitxers

```
yaninaparisi/
├── classes/
│   └── seo_offpage_links.php          # Classe principal
├── _pcontrol/
│   ├── gseo.php                       # Panel principal
│   ├── includes/
│   │   └── offpage_links_interface.php # Interfície de gestió
│   └── css/
│       └── offpage.css                # Estils específics
└── _secret/
    └── db.html                        # Estructura BD
```

## 🗄️ Base de Dades

**Taula:** `seo_offpage`

**Camps principals:**
- Identificació: url_origen, url_destino, anchor_text, dominio_origen
- Autoritat: da_origen, dr_origen, tf_origen, cf_origen
- Context: tipo_backlink, posicion_enlace, nofollow, sponsored, ugc
- Qualitat: relevancia_tematica, calidad_percibida, autoridad_tematica
- Estat: estado, fecha_descubrimiento, fecha_ultima_verificacion
- Rendiment: clicks_mensuales, traffic_estimado, valor_estimado
- Estratègia: campana_seo, objetivo_seo, prioridad

## 🚀 Ús del Sistema

### Accedir a la Interfície

1. Navegar a: `gseo.php?tab=offpage`
2. Tres vistes disponibles:
   - **Llistat** (`view=list`) - Taula de backlinks
   - **Crear/Editar** (`view=create` o `view=edit`) - Formularis
   - **Estadístiques** (`view=stats`) - Dashboard analític

### Crear un Backlink

```php
// Exemple bàsic
$data = [
    'url_origen' => 'https://ejemplo.com/articulo',
    'url_destino' => 'https://psicologiayanina.com/servicios',
    'anchor_text' => 'psicóloga en Girona',
    'dominio_origen' => 'ejemplo.com',
    'tipo_backlink' => 'guest_post',
    'da_origen' => 45,
    'relevancia_tematica' => 'alta'
];

$backlink = SEO_OffPage_Links::crear($data);
```

### Actualitzar un Backlink

```php
$backlink = new SEO_OffPage_Links(1);
$backlink->actualitzarMultiplesCamps([
    'da_origen' => 50,
    'estado' => 'activo',
    'notas_internas' => 'Actualitzat després de verificació'
]);
```

### Verificar Backlink

```php
$backlink = new SEO_OffPage_Links(1);
$resultado = $backlink->verificarBacklink();

// Retorna:
// ['estado' => 'activo', 'mensaje' => 'Backlink verificat i actiu']
// o
// ['estado' => 'perdido', 'mensaje' => 'L\'enllaç ja no existeix']
```

### Llistar amb Filtres

```php
$filtros = [
    'estado' => 'activo',
    'tipo_backlink' => 'guest_post',
    'da_min' => 40
];

$backlinks = SEO_OffPage_Links::llistarBacklinks(
    $filtros, 
    'da_origen',  // ordenar per
    'DESC',       // direcció
    50            // límit
);
```

### Obtenir Estadístiques

```php
$stats = SEO_OffPage_Links::obtenirEstadistiquesGlobals();

// Retorna:
// [
//     'score_global' => 75,
//     'total' => 150,
//     'activos' => 120,
//     'perdidos' => 20,
//     'rotos' => 10,
//     'da_promedio' => 45,
//     'dofollow' => 100,
//     'nofollow' => 50,
//     'clicks_totales' => 5000,
//     'valor_total' => 12500.00,
//     'estado_global' => 'Muy bueno',
//     'por_tipo' => [...],
//     'por_relevancia' => [...],
//     'top_dominios' => [...]
// ]
```

## 📊 Càlcul del Quality Score

Factors considerats per al Quality Score (0-100):

1. **Domain Authority** (30 punts)
   - DA o DR del domini origen

2. **Trust Flow / Citation Flow** (20 punts)
   - Ratio TF/CF >= 1: 20 punts
   - Ratio TF/CF >= 0.7: 15 punts
   - Ratio TF/CF >= 0.5: 10 punts

3. **Relevància Temàtica** (20 punts)
   - Alta: 20 punts
   - Mitjana: 12 punts
   - Baixa: 5 punts

4. **Posició de l'Enllaç** (10 punts)
   - Contingut: 10 punts
   - Navegació/Header: 7 punts
   - Sidebar: 5 punts
   - Footer/Comentaris: 3 punts

5. **Atributs REL** (10 punts)
   - DoFollow + No sponsored: 10 punts
   - DoFollow + Sponsored: 7 punts
   - NoFollow + No sponsored: 5 punts
   - NoFollow + Sponsored: 2 punts

6. **Tràfic Origen** (10 punts)
   - >= 10.000 visites: 10 punts
   - >= 5.000 visites: 7 punts
   - >= 1.000 visites: 5 punts
   - >= 100 visites: 3 punts

## 💡 Bones Pràctiques

### Anchor Text
- **Marca**: Utilitzar nom de marca per branding
- **Exacte**: Keywords exactes amb moderació (risc sobre-optimització)
- **Parcial**: Variacions de keywords (recomanat)
- **Genèric**: Evitar "clic aquí", "més info"
- **URL**: Acceptable però millorable

### Diversificació
- Variar tipus de backlinks
- Diversificar dominis origen
- Equilibrar DoFollow/NoFollow (80/20 ideal)
- Mantenir relevància temàtica alta

### Seguiment
- Verificar backlinks cada 30 dies
- Contactar propietaris si es perden
- Documentar totes les accions
- Analitzar rendiment per campanya

## 🔧 Manteniment

### Verificació Automàtica

```php
// Obtenir backlinks pendents de verificar (>30 dies)
$pendents = SEO_OffPage_Links::obtenirBacklinksPerVerificar(30);

foreach ($pendents as $backlink) {
    $resultado = $backlink->verificarBacklink();
    echo "Backlink {$backlink->getId()}: {$resultado['mensaje']}\n";
}
```

### Actualització de Mètriques

Es recomana actualitzar DA, DR, TF, CF cada trimestre utilitzant eines externes:
- MOZ Link Explorer
- Ahrefs Site Explorer
- Majestic Site Explorer

## 📈 Dashboard

El dashboard mostra:
- **Puntuació global** (0-100) amb indicador visual
- **Resum de backlinks** (actius, perduts, trencats)
- **Mètriques clau** (DA promig, clics, valor)
- **Gràfics de distribució** (per tipus, relevància)
- **Top 10 dominis** amb més backlinks

## 🎨 Personalització

### Colors per Estat
- **Actiu**: Verd (#28a745)
- **Perdut**: Taronja (#ffc107)
- **Trencat**: Vermell (#dc3545)
- **En Revisió**: Blau (#17a2b8)

### Icones FontAwesome
- Backlink: `fa-link`
- Verificar: `fa-sync`
- Estadístiques: `fa-chart-line`
- Domini: `fa-globe`
- Autoritat: `fa-trophy`

## ⚠️ Notes Importants

1. **Privacitat**: No compartir URLs origen sense permís
2. **Verificació**: Respectar robots.txt al verificar
3. **Rate Limiting**: No verificar més de 10 backlinks/minut
4. **Backup**: Fer còpia de seguretat abans d'eliminacions massives

## 📞 Suport

Per dubtes o problemes:
- Revisar logs PHP: `error_log()`
- Comprovar permisos BD
- Validar estructura de taula
- Verificar connexió a internet (verificació backlinks)

---

**Versió:** 1.0.0  
**Data:** 2025-10-07  
**Autor:** Marc Mataró
