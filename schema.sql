SET FOREIGN_KEY_CHECKS = 0;
SET NAMES utf8mb4;

-- ============================================================
-- 0. USUÁRIOS
-- ============================================================
CREATE TABLE IF NOT EXISTS usuarios (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome          VARCHAR(100) NOT NULL,
    email         VARCHAR(100) NOT NULL,
    senha         VARCHAR(255) NOT NULL,
    nivel         ENUM('admin', 'gerente', 'operador') DEFAULT 'operador',
    ativo         TINYINT(1)   NOT NULL DEFAULT 1,
    criado_em     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE KEY uk_usuario_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Cadastro de usuários e operadores';

CREATE INDEX idx_user_ativo ON usuarios(ativo);
CREATE INDEX idx_user_email ON usuarios(email);

-- Insere o Administrador Padrão (Senha: admin123)
INSERT INTO usuarios (nome, email, senha, nivel, ativo) 
VALUES (
  'Administrador', 
  'admin@zafenate.com', 
  '$2y$10$wK58mEunKqf7G.AehVfWvO6VpZgL8n7G2W6PshT4pM.M97rYxZ2V6',
  'admin', 
  1
) ON DUPLICATE KEY UPDATE id=id;


-- ============================================================
-- 1. CATEGORIAS
-- ============================================================
CREATE TABLE IF NOT EXISTS categorias (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    parent_id     INT UNSIGNED NULL DEFAULT NULL,
    nome          VARCHAR(100) NOT NULL,
    descricao     VARCHAR(255) NULL,
    ativo         TINYINT(1)   NOT NULL DEFAULT 1,
    criado_em     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_categoria_parent
        FOREIGN KEY (parent_id) REFERENCES categorias(id)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Categorias de produtos';

CREATE INDEX idx_cat_parent ON categorias(parent_id);
CREATE INDEX idx_cat_ativo  ON categorias(ativo);


-- ============================================================
-- 2. UNIDADES DE MEDIDA
-- ============================================================
CREATE TABLE IF NOT EXISTS unidades (
    id    INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    sigla VARCHAR(10)  NOT NULL,
    nome  VARCHAR(50)  NOT NULL,
    UNIQUE KEY uk_unidade_sigla (sigla)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Unidades de medida';

INSERT INTO unidades (sigla, nome) VALUES
    ('UN',  'Unidade'), ('KG',  'Quilograma'), ('G',   'Grama'),
    ('L',   'Litro'), ('ML',  'Mililitro'), ('CX',  'Caixa'),
    ('PCT', 'Pacote'), ('MT',  'Metro'), ('CM',  'Centímetro'), ('PAR', 'Par')
ON DUPLICATE KEY UPDATE id=id;


-- ============================================================
-- 3. FORNECEDORES
-- ============================================================
CREATE TABLE IF NOT EXISTS fornecedores (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    razao_social    VARCHAR(150) NOT NULL,
    nome_fantasia   VARCHAR(150) NULL,
    cnpj_cpf        VARCHAR(18)  NULL,
    ie              VARCHAR(20)  NULL,
    email           VARCHAR(100) NULL,
    telefone        VARCHAR(20)  NULL,
    celular         VARCHAR(20)  NULL,
    contato         VARCHAR(100) NULL,
    cep             VARCHAR(9)   NULL,
    logradouro      VARCHAR(150) NULL,
    numero          VARCHAR(10)  NULL,
    complemento     VARCHAR(100) NULL,
    bairro          VARCHAR(80)  NULL,
    cidade          VARCHAR(80)  NULL,
    uf              CHAR(2)      NULL,
    prazo_pagamento TINYINT UNSIGNED NULL,
    observacoes     TEXT         NULL,
    ativo           TINYINT(1)   NOT NULL DEFAULT 1,
    criado_em       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY uk_fornecedor_cnpj (cnpj_cpf)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Fornecedores';

CREATE INDEX idx_forn_ativo         ON fornecedores(ativo);
CREATE INDEX idx_forn_razao_social ON fornecedores(razao_social);


-- ============================================================
-- 4. PRODUTOS
-- ============================================================
CREATE TABLE IF NOT EXISTS produtos (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    categoria_id  INT UNSIGNED NULL,
    unidade_id    INT UNSIGNED NOT NULL DEFAULT 1,
    codigo        VARCHAR(50)  NOT NULL,
    codigo_barras VARCHAR(50)  NULL,
    nome          VARCHAR(150) NOT NULL,
    descricao     TEXT         NULL,
    imagem        VARCHAR(255) NULL,
    preco_custo   DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    preco_venda   DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    estoque_atual   DECIMAL(10,3) NOT NULL DEFAULT 0.000,
    estoque_minimo  DECIMAL(10,3) NOT NULL DEFAULT 0.000,
    estoque_maximo  DECIMAL(10,3) NULL,
    ativo         TINYINT(1)   NOT NULL DEFAULT 1,
    criado_em     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY uk_produto_codigo        (codigo),
    UNIQUE KEY uk_produto_codigo_barras (codigo_barras),

    CONSTRAINT fk_produto_categoria
        FOREIGN KEY (categoria_id) REFERENCES categorias(id)
        ON DELETE SET NULL ON UPDATE CASCADE,

    CONSTRAINT fk_produto_unidade
        FOREIGN KEY (unidade_id) REFERENCES unidades(id)
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Cadastro de produtos';

CREATE INDEX idx_prod_categoria  ON produtos(categoria_id);
CREATE INDEX idx_prod_ativo      ON produtos(ativo);
CREATE INDEX idx_prod_nome       ON produtos(nome);


-- ============================================================
-- 5. MOVIMENTAÇÕES DE ESTOQUE (Corrigida a referência para fornecedores)
-- ============================================================
CREATE TABLE IF NOT EXISTS movimentacoes_estoque (
    id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    produto_id     INT UNSIGNED NOT NULL,
    fornecedor_id  INT UNSIGNED NULL,
    usuario_id     INT UNSIGNED NULL,
    tipo           ENUM('ENTRADA','SAIDA','AJUSTE') NOT NULL,
    motivo         ENUM('COMPRA','DEVOLUCAO','VENDA','PERDA','USO_INTERNO','AJUSTE_MANUAL','TRANSFERENCIA') NOT NULL,
    quantidade     DECIMAL(10,3) NOT NULL,
    estoque_antes  DECIMAL(10,3) NOT NULL,
    estoque_depois DECIMAL(10,3) NOT NULL,
    preco_custo_unitario DECIMAL(10,2) NULL,
    numero_nf            VARCHAR(20)   NULL,
    observacao     VARCHAR(255) NULL,
    criado_em      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_mov_produto
        FOREIGN KEY (produto_id) REFERENCES produtos(id)
        ON UPDATE CASCADE,

    CONSTRAINT fk_mov_fornecedor
        -- 🛠️ Corrigido de 'suppliers' para 'fornecedores' bem aqui!
        FOREIGN KEY (fornecedor_id) REFERENCES fornecedores(id)
        ON DELETE SET NULL ON UPDATE CASCADE,

    CONSTRAINT fk_mov_usuario
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Histórico de estoque';

CREATE INDEX idx_mov_produto     ON movimentacoes_estoque(produto_id);
CREATE INDEX idx_mov_fornecedor  ON movimentacoes_estoque(fornecedor_id);
CREATE INDEX idx_mov_usuario     ON movimentacoes_estoque(usuario_id);

SET FOREIGN_KEY_CHECKS = 1;