-- Script para añadir campo id_usuario a la tabla faqs
-- Este campo relaciona cada FAQ con el usuario que la creó
-- Ejecutar este script una sola vez

-- Añadir el campo id_usuario (nullable primero para las FAQs existentes)
ALTER TABLE `faqs` 
ADD COLUMN `id_usuario` INT NULL AFTER `data_actualitzacio`,
ADD INDEX `idx_faq_usuario` (`id_usuario`);

-- Añadir foreign key a usuarios_panel
ALTER TABLE `faqs`
ADD CONSTRAINT `fk_faq_usuario` 
FOREIGN KEY (`id_usuario`) REFERENCES `usuarios_panel`(`id_usuario`) 
ON DELETE SET NULL 
ON UPDATE CASCADE;

-- Para las FAQs existentes, asignar al usuario admin (id=1) o dejar NULL
-- UPDATE `faqs` SET `id_usuario` = 1 WHERE `id_usuario` IS NULL;
