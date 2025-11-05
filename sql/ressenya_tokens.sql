-- Taula per gestionar tokens d'opinió/ressenya per pacients
-- Cada token permet que un pacient validi la seva capacitat d'enviar UNA ressenya

CREATE TABLE IF NOT EXISTS ressenya_tokens (
    id INT PRIMARY KEY AUTO_INCREMENT,
    pacient_id INT NOT NULL,
    token VARCHAR(64) NOT NULL UNIQUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    used_at DATETIME NULL,
    expires_at DATETIME NULL,
    INDEX idx_pacient_id (pacient_id),
    INDEX idx_token (token)
);

-- Nota: si voleu, podeu afegir la restricció FOREIGN KEY si la taula pacients existeix al mateix esquema:
-- ALTER TABLE ressenya_tokens ADD CONSTRAINT fk_ress_tokens_pacient FOREIGN KEY (pacient_id) REFERENCES pacients(id) ON DELETE CASCADE;
