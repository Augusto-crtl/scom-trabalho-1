-- ============================================================================
-- SCRIPT DE CRIAÇÃO DO BANCO DE DADOS - METAL ARCHIVES
-- Trabalho Individual II (SCOM) - XAMPP (MySQL / MariaDB)
-- ============================================================================

CREATE DATABASE IF NOT EXISTS metal_archives
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE metal_archives;

-- ----------------------------------------------------------------------------
-- 1. Tabela Principal: usuarios
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    senha_hash VARCHAR(255) NOT NULL,
    genero VARCHAR(50) DEFAULT 'outro',
    perfil ENUM('headbanger', 'banda', 'admin') DEFAULT 'headbanger' NOT NULL,
    receber_novidades TINYINT(1) DEFAULT 0,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 2. Tabela Secundária: perfis_headbanger (Fãs)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS perfis_headbanger (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    banda_favorita VARCHAR(255) DEFAULT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 3. Tabela de Bandas: bandas
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS bandas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    nome_banda VARCHAR(255) NOT NULL,
    subgenero VARCHAR(100) DEFAULT 'heavy',
    link_media VARCHAR(500) DEFAULT NULL,
    biografia TEXT DEFAULT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 4. Tabela de Reações: curtidas_bandas (Hails 🤘)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS curtidas_bandas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    banda_id INT NOT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_curtida (usuario_id, banda_id),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (banda_id) REFERENCES bandas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 5. Tabela de Interações: comentarios
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS comentarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    banda_id INT NOT NULL,
    texto TEXT NOT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (banda_id) REFERENCES bandas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 6. Tabela de Auditoria e Segurança: logs_sistema
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS logs_sistema (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nivel VARCHAR(20) NOT NULL,
    evento VARCHAR(50) NOT NULL,
    mensagem TEXT NOT NULL,
    ip VARCHAR(45) NOT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- ÍNDICES DE DESEMPENHO (Atendimento aos critérios da rubrica de BD)
-- ----------------------------------------------------------------------------
CREATE INDEX idx_usuarios_email ON usuarios(email);
CREATE INDEX idx_bandas_subgenero ON bandas(subgenero);
CREATE INDEX idx_logs_evento_ip ON logs_sistema(evento, ip, criado_em);

-- ----------------------------------------------------------------------------
-- DADOS INICIAIS (SEED) - Usuário Administrador do Sistema
-- Credenciais: admin@metal.com | Senha: 123456
-- ----------------------------------------------------------------------------
INSERT INTO usuarios (nome, email, senha_hash, genero, perfil, receber_novidades)
VALUES (
    'Administrador do Fórum',
    'admin@metal.com',
    '$2y$10$e0MYzXyjpJS7Pd0RVvHwHe1a.Ff3Ff2w3fN8iW.uG1Jq9e',
    'outro',
    'admin',
    0
) ON DUPLICATE KEY UPDATE id=id;
