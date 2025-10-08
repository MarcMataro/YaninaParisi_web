# 📁 Gestió de Directoris Off-Page SEO

## 📋 Descripció

Sistema de gestió de **directoris empresarials** per millorar la presència Off-Page SEO del negoci. Permet registrar, seguir i analitzar les inscripcions en directoris com Google My Business, Yelp, Páginas Amarillas, directoris de psicòlegs, etc.

---

## 🎯 Funcionalitats

### ✅ **Gestió de Directoris**
- Llistat complet de directoris registrats
- Creació de nous directoris
- Edició d'informació dels directoris
- Eliminació de directoris
- Filtrat per estat, categoria i idioma

### 📊 **Seguiment d'Estat**
- **Pendiente**: No s'ha enviat encara
- **Enviado**: Sol·licitud enviada
- **Aprobado**: Sol·licitud aprovada
- **Activo**: Perfil operatiu i visible
- **Rechazado**: Sol·licitud rebutjada

### 📈 **Estadístiques i Anàlisi**
- Score global de directoris (0-100)
- Total de directoris per estat
- DA (Domain Authority) promig
- Distribució DoFollow/NoFollow
- Cost total anual
- Distribució per categoria i idioma
- Top 10 directoris per autoritat

### 💰 **Control de Costos**
- Seguiment del cost anual per directori
- Gratuït o de pagament
- ROI estimat
- Cost total consolidat

---

## 🏗️ Estructura de Dades

### **Camps de la Base de Dades**

```sql
CREATE TABLE seo_offpage_directorios (
    id_directorio INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(255) NOT NULL,                  -- Nom del directori
    url VARCHAR(500) NOT NULL,                     -- URL del directori
    categoria ENUM(...),                           -- Tipus de directori
    da_directorio TINYINT UNSIGNED,                -- Domain Authority (0-100)
    costo DECIMAL(8,2) DEFAULT 0,                  -- Cost anual en euros
    idioma ENUM('ca', 'es', 'en', 'other'),       -- Idioma principal
    nofollow BOOLEAN DEFAULT FALSE,                -- Si l'enllaç és nofollow
    permite_anchor_personalizado BOOLEAN,          -- Permet anchor personalitzat
    estado ENUM(...),                              -- Estat actual
    fecha_envio DATE,                              -- Data d'enviament
    fecha_aprobacion DATE,                         -- Data d'aprovació
    notas TEXT,                                    -- Notes internes
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP
);
```

### **Categories de Directoris**
- **Psicologia**: Directoris específics de psicòlegs
- **Salud**: Directoris del sector salut
- **Locales**: Directoris locals/geogràfics
- **Negocios**: Directoris generals de negocis
- **Genérico**: Altres directoris

---

## 💻 Ús de la Classe PHP

### **Crear un Directori**

```php
$data = [
    'nombre' => 'Google My Business',
    'url' => 'https://business.google.com',
    'categoria' => 'locales',
    'da_directorio' => 98,
    'costo' => 0,
    'idioma' => 'es',
    'nofollow' => false,
    'permite_anchor_personalizado' => true,
    'estado' => 'activo'
];

$directorio = SEO_OffPage_Directories::crear($data);
```

### **Actualitzar un Directori**

```php
$directorio = new SEO_OffPage_Directories($id);
$directorio->setEstado('activo');
$directorio->setFechaAprobacion('2025-10-07');
```

### **Gestionar Estats**

```php
// Marcar com enviat
$directorio->marcarComEnviat('2025-10-01', 'Enviada sol·licitud via formulari web');

// Marcar com aprovat
$directorio->marcarComAprovat('2025-10-05', 'Rebut email de confirmació');

// Marcar com actiu
$directorio->marcarComActiu('Perfil publicat i verificat');

// Marcar com rebutjat
$directorio->marcarComRebutjat('No compleix els requisits de categoria');
```

### **Calcular Qualitat i ROI**

```php
$directorio = new SEO_OffPage_Directories($id);

// Puntuació de qualitat (0-100)
$quality = $directorio->calcularQualityScore();
// Factors: DA (40%), Categoria (20%), DoFollow (15%), Anchor (15%), Cost (10%)

// Return on Investment
$roi = $directorio->calcularROI();
// ROI = (quality * DA / 10000) / cost
```

### **Consultes i Filtres**

```php
// Llistar tots els directoris
$todos = SEO_OffPage_Directories::llistarDirectoris();

// Filtrar per estat
$activos = SEO_OffPage_Directories::llistarDirectoris(['estado' => 'activo']);

// Filtrar per categoria i idioma
$filtros = [
    'categoria' => 'psicologia',
    'idioma' => 'es',
    'da_min' => 30
];
$directorios = SEO_OffPage_Directories::llistarDirectoris($filtros, 'da_directorio', 'DESC');

// Obtenir estadístiques globals
$stats = SEO_OffPage_Directories::obtenirEstadistiquesGlobals();
```

### **Consultes Específiques**

```php
// Directoris pendents d'enviament
$pendientes = SEO_OffPage_Directories::obtenirPendentsEnviament();

// Directoris pendents de revisió (>15 dies enviats)
$revisar = SEO_OffPage_Directories::obtenirPendentsRevisio();

// Millors opcions per registrar-se (gratuïts o baix cost + alta autoritat)
$mejores = SEO_OffPage_Directories::obtenirMillorsOpcions(10);
```

### **Anàlisi Temporal**

```php
$directorio = new SEO_OffPage_Directories($id);

// Dies des de l'enviament
$dies_envio = $directorio->getDiesDesDeEnviament();

// Dies des de l'aprovació
$dies_aprovacio = $directorio->getDiesDesDeAprovacio();

// Comprovar si està pendent de revisió
if ($directorio->isPendienteRevision()) {
    echo "Portem més de 15 dies esperant resposta!";
}
```

### **Exportar Dades**

```php
$directorio = new SEO_OffPage_Directories($id);
$data = $directorio->toArray();

// Inclou tots els camps + mètriques calculades:
// - quality_score
// - roi
// - dies_des_enviament
// - dies_des_aprovacio
// - pendent_revisio
```

---

## 📊 Sistema de Qualitat

### **Factors del Quality Score (0-100)**

1. **Domain Authority (40 punts)**
   - DA 80-100: 40 punts
   - DA 50-79: 25-39 punts
   - DA 30-49: 12-24 punts
   - DA 0-29: 0-11 punts
   - Sense DA: 15 punts per defecte

2. **Categoria Específica (20 punts)**
   - Psicologia: 20 punts (màxima rellevància)
   - Salud: 15 punts
   - Locales: 12 punts (SEO local)
   - Negocios: 8 punts
   - Genérico: 5 punts

3. **DoFollow vs NoFollow (15 punts)**
   - DoFollow: 15 punts (valor SEO)
   - NoFollow: 5 punts (menys valor però útil)

4. **Anchor Personalitzat (15 punts)**
   - Permet: 15 punts
   - No permet: 7 punts

5. **Cost vs Valor (10 punts)**
   - Gratuït: 10 punts
   - ≤50€: 8 punts
   - ≤150€: 5 punts
   - >150€: 2 punts

---

## 🎨 Interfície d'Usuari

### **Vistes Disponibles**

1. **Vista de Llistat** (`view=list`)
   - Taula amb tots els directoris
   - Filtres per estat, categoria, idioma
   - Accions: editar, eliminar
   - Badges visuals per estat, qualitat, cost

2. **Vista de Creació/Edició** (`view=create` o `view=edit`)
   - Formulari amb 5 seccions:
     1. Informació Bàsica
     2. Mètriques i Cost
     3. Atributs SEO
     4. Estat i Seguiment
     5. Notes
   - Validació de camps obligatoris
   - Checkboxes per nofollow i anchor personalitzat

3. **Vista d'Estadístiques** (`view=stats`)
   - Score global amb cercle visual
   - Resum en 6 caixes: Total, Activos, Pendientes, Enviados, DA Promedio, Coste Anual
   - Gràfics de distribució: DoFollow/NoFollow, Per categoria, Per idioma
   - Top 10 directoris per autoritat

### **Navegació per Sub-Tabs**

El sistema Off-Page ara té **sub-tabs**:
- 🔗 **Backlinks**: Gestió d'enllaços entrants
- 📁 **Directorios**: Gestió de directoris empresarials

Accés: `gseo.php?tab=offpage&subtab=directories`

---

## 🔍 Exemples de Directoris Recomanats

### **Directoris de Psicòlegs (Alta Prioritat)**
- **Psicólogos en España** - DA: 40-50
- **Doctoralia** - DA: 70+ (de pagament)
- **Topdoctors** - DA: 65+ (de pagament)
- **Psicología Online** - DA: 35-45

### **Directoris Locals (Girona)**
- **Google My Business** - DA: 98 ✅ GRATUÏT
- **Bing Places** - DA: 92 ✅ GRATUÏT
- **Apple Maps** - DA: 95 ✅ GRATUÏT
- **Páginas Amarillas** - DA: 70
- **11870.com** - DA: 60

### **Directoris de Salut**
- **iSalud** - DA: 55
- **DocPlanner** - DA: 70+
- **Mundopsicologos** - DA: 50+

---

## 🔧 Manteniment

### **Tasques Periòdiques**

1. **Setmanal**
   - Revisar directoris "Pendientes" i enviar sol·licituds
   - Verificar estats dels directoris "Enviados"
   - Actualitzar DA dels directoris (usar Moz)

2. **Mensual**
   - Analitzar estadístiques globals
   - Avaluar ROI dels directoris de pagament
   - Buscar nous directoris rellevants

3. **Trimestral**
   - Auditar perfils actius (verificar que segueixen actius)
   - Actualitzar informació de contacte si cal
   - Optimitzar descripcions i categories

---

## 📝 Notes Importants

- **DA (Domain Authority)**: Actualitzar periòdicament amb eines com Moz, Ahrefs o SEMrush
- **Cost**: Si és de pagament, revisar renovacions i facturació
- **Nofollow**: Encara que sigui nofollow, els directoris aporten visibilitat i trànsit
- **Anchor Text**: Prioritzar directoris que permetin personalitzar l'anchor
- **Categoria**: Prioritzar directoris específics de psicologia > salut > locals > genèrics

---

## 🚀 Integració amb Dashboard

El **dashboard principal** mostra:
- **Score combinat** Off-Page (60% backlinks + 40% directoris)
- Total de backlinks i directoris
- Elements actius
- DA promig combinat
- DoFollow combinats
- Cost anual total dels directoris

---

## 📞 Directoris Essencials per Començar

### ✅ **Prioritat Alta (Gratuïts + Alta Autoritat)**
1. Google My Business (DA: 98) ⭐⭐⭐⭐⭐
2. Bing Places (DA: 92) ⭐⭐⭐⭐⭐
3. Apple Maps (DA: 95) ⭐⭐⭐⭐⭐
4. Facebook Business (DA: 96) ⭐⭐⭐⭐
5. LinkedIn Company (DA: 98) ⭐⭐⭐⭐

### 🎯 **Prioritat Mitjana (Específics de Psicologia)**
6. Psicólogos Online España
7. Colegio Oficial de Psicólogos
8. Doctoralia / DocPlanner (considerar versió de pagament)

---

**Versió**: 1.0.0  
**Data**: 2025-10-07  
**Autor**: Marc Mataró
