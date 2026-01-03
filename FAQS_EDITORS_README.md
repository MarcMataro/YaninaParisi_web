# Instruccions per habilitar FAQs per a Editors

## Resum de canvis implementats

S'ha implementat la funcionalitat perquè els usuaris amb rol "Editor" puguin gestionar les seves pròpies FAQs al panell de control.

## ⚠️ IMPORTANT: Abans de començar

**Cal executar l'script SQL** per afegir el camp `id_usuario` a la taula `faqs`.

### Pas 1: Executar l'script SQL

1. Obre phpMyAdmin o el teu gestor de bases de dades MySQL
2. Selecciona la base de dades del projecte
3. Executa l'script SQL que trobaràs a: `sql/add_usuario_to_faqs.sql`

```sql
-- Script para añadir campo id_usuario a la tabla faqs
ALTER TABLE `faqs` 
ADD COLUMN `id_usuario` INT NULL AFTER `data_actualitzacio`,
ADD INDEX `idx_faq_usuario` (`id_usuario`);

ALTER TABLE `faqs`
ADD CONSTRAINT `fk_faq_usuario` 
FOREIGN KEY (`id_usuario`) REFERENCES `usuarios_panel`(`id_usuario`) 
ON DELETE SET NULL 
ON UPDATE CASCADE;
```

### Pas 2: Verificar els canvis

Un cop executat l'script, els canvis ja estan actius. No cal fer res més!

## Funcionalitats implementades

### Per als usuaris "Editor":

1. **Accés al menú:**
   - Ara poden accedir a Blog i FAQ's
   - L'opció FAQ's apareix al menú lateral

2. **Gestió de FAQs:**
   - Poden crear noves FAQs
   - Només veuen les seves pròpies FAQs al llistat
   - Només poden editar les seves pròpies FAQs
   - Només poden eliminar les seves pròpies FAQs
   - Quan creen una FAQ, s'assigna automàticament com a autor

3. **Restriccions de seguretat:**
   - Si intenten editar/eliminar una FAQ d'un altre usuari, reben un missatge d'error
   - No poden veure FAQs d'altres usuaris

### Per als usuaris "Admin" i altres rols:

- Continuen tenint accés complet a totes les FAQs
- Poden veure i editar FAQs de qualsevol usuari
- No hi ha cap canvi en la seva funcionalitat

## Fitxers modificats

1. **sql/add_usuario_to_faqs.sql** - Script SQL nou
2. **classes/faqs.php** - Afegit suport per `id_usuario`
3. **_pcontrol/includes/role_check.php** - Afegit `gfaq.php` a pàgines permeses per Editors
4. **_pcontrol/includes/sidebar.php** - Mostrar FAQ's al menú per a Editors
5. **_pcontrol/gfaq.php** - Implementades restriccions per a Editors

## FAQs existents

Les FAQs que ja existien a la base de dades tindran `id_usuario = NULL`. Si vols assignar-les a un usuari específic, pots executar:

```sql
-- Assignar totes les FAQs existents a l'administrador (ID=1)
UPDATE `faqs` SET `id_usuario` = 1 WHERE `id_usuario` IS NULL;
```

---

**Data d'implementació:** 2 de gener de 2026  
**Versió:** 1.0
