-- ============================================================
-- ZAFENATE CONTROL - Migration 001
-- Schema inicial: Categorias, Unidades, Fornecedores,
--                 Produtos, Movimentações de Estoque
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;
SET NAMES utf8mb4;

-- ============================================================
-- 1. CATEGORIAS
-- Suporta subcategoria via parent_id (autorelacionamento)
-- ============================================================
CREATE TABLE IF NOT EXISTS categorias (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    parent_id     INT UNSIGNED NULL DEFAULT NULL COMMENT 'NULL = categoria raiz',
    nome          VARCHAR(100) NOT NULL,
    descricao     VARCHAR(255) NULL,
    ativo         TINYINT(1)   NOT NULL DEFAULT 1,
    criado_em     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_categoria_parent
        FOREIGN KEY (parent_id) REFERENCES categorias(id)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Categorias e subcategorias de produtos';

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Unidades de medida dos produtos';

INSERT INTO unidades (sigla, nome) VALUES
    ('UN',  'Unidade'),
    ('KG',  'Quilograma'),
    ('G',   'Grama'),
    ('L',   'Litro'),
    ('ML',  'Mililitro'),
    ('CX',  'Caixa'),
    ('PCT', 'Pacote'),
    ('MT',  'Metro'),
    ('CM',  'Centímetro'),
    ('PAR', 'Par');

-- ============================================================
-- 3. FORNECEDORES
-- ============================================================
CREATE TABLE IF NOT EXISTS fornecedores (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    -- Identificação
    razao_social    VARCHAR(150) NOT NULL,
    nome_fantasia   VARCHAR(150) NULL,
    cnpj_cpf        VARCHAR(18)  NULL COMMENT 'CNPJ ou CPF formatado',
    ie              VARCHAR(20)  NULL COMMENT 'Inscrição Estadual',

    -- Contato
    email           VARCHAR(100) NULL,
    telefone        VARCHAR(20)  NULL,
    celular         VARCHAR(20)  NULL,
    contato         VARCHAR(100) NULL COMMENT 'Nome do contato/responsável',

    -- Endereço
    cep             VARCHAR(9)   NULL,
    logradouro      VARCHAR(150) NULL,
    numero          VARCHAR(10)  NULL,
    complemento     VARCHAR(100) NULL,
    bairro          VARCHAR(80)  NULL,
    cidade          VARCHAR(80)  NULL,
    uf              CHAR(2)      NULL,

    -- Financeiro
    prazo_pagamento TINYINT UNSIGNED NULL COMMENT 'Prazo em dias',
    observacoes     TEXT         NULL,

    -- Status
    ativo           TINYINT(1)   NOT NULL DEFAULT 1,
    criado_em       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY uk_fornecedor_cnpj (cnpj_cpf)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Fornecedores de produtos';

CREATE INDEX idx_forn_ativo        ON fornecedores(ativo);
CREATE INDEX idx_forn_razao_social ON fornecedores(razao_social);

-- ============================================================
-- 4. PRODUTOS
-- ============================================================
CREATE TABLE IF NOT EXISTS produtos (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    categoria_id  INT UNSIGNED NULL,
    unidade_id    INT UNSIGNED NOT NULL DEFAULT 1,

    -- Identificação
    codigo        VARCHAR(50)  NOT NULL COMMENT 'Código interno do produto',
    codigo_barras VARCHAR(50)  NULL     COMMENT 'EAN-13, EAN-8, QR Code etc.',
    nome          VARCHAR(150) NOT NULL,
    descricao     TEXT         NULL,

    -- Imagem
    imagem        VARCHAR(255) NULL COMMENT 'Caminho relativo em storage/uploads/produtos/',

    -- Preços
    preco_custo   DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Último preço de custo registrado',
    preco_venda   DECIMAL(10,2) NOT NULL DEFAULT 0.00,

    -- Estoque
    estoque_atual   DECIMAL(10,3) NOT NULL DEFAULT 0.000,
    estoque_minimo  DECIMAL(10,3) NOT NULL DEFAULT 0.000 COMMENT 'Dispara alerta abaixo deste valor',
    estoque_maximo  DECIMAL(10,3) NULL,

    -- Status
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Cadastro de produtos';

CREATE INDEX idx_prod_categoria  ON produtos(categoria_id);
CREATE INDEX idx_prod_ativo      ON produtos(ativo);
CREATE INDEX idx_prod_nome       ON produtos(nome);
CREATE INDEX idx_prod_cod_barras ON produtos(codigo_barras);
CREATE INDEX idx_prod_estoque    ON produtos(estoque_atual, estoque_minimo);

-- ============================================================
-- 5. MOVIMENTAÇÕES DE ESTOQUE
-- Toda entrada e saída passa por aqui.
-- O caixa futuramente vai inserir saídas automaticamente.
-- ============================================================
CREATE TABLE IF NOT EXISTS movimentacoes_estoque (
    id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    produto_id     INT UNSIGNED NOT NULL,
    fornecedor_id  INT UNSIGNED NULL COMMENT 'Preenchido nas entradas por compra',

    -- Tipo
    tipo           ENUM('ENTRADA','SAIDA','AJUSTE') NOT NULL,
    motivo         ENUM(
                       'COMPRA',        -- entrada via fornecedor
                       'DEVOLUCAO',     -- devolução de cliente
                       'VENDA',         -- saída pelo caixa
                       'PERDA',         -- avaria, vencimento etc.
                       'USO_INTERNO',   -- consumo interno
                       'AJUSTE_MANUAL', -- acerto de inventário
                       'TRANSFERENCIA' -- entre locais (futuro)
                   ) NOT NULL,

    -- Quantidades
    quantidade     DECIMAL(10,3) NOT NULL COMMENT 'Sempre positivo; tipo define sentido',
    estoque_antes  DECIMAL(10,3) NOT NULL COMMENT 'Snapshot antes da movimentação',
    estoque_depois DECIMAL(10,3) NOT NULL COMMENT 'Snapshot após a movimentação',

    -- Financeiro (opcional — preenchido nas compras)
    preco_custo_unitario DECIMAL(10,2) NULL COMMENT 'Preço pago na entrada',
    numero_nf            VARCHAR(20)   NULL COMMENT 'Número da NF de compra',

    -- Rastreabilidade
    observacao     VARCHAR(255) NULL,
    usuario_id     INT UNSIGNED NULL COMMENT 'Quem fez (FK para tabela users — a criar)',
    criado_em      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_mov_produto
        FOREIGN KEY (produto_id) REFERENCES produtos(id)
        ON UPDATE CASCADE,

    CONSTRAINT fk_mov_fornecedor
        FOREIGN KEY (fornecedor_id) REFERENCES fornecedores(id)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Histórico completo de movimentações de estoque';

CREATE INDEX idx_mov_produto     ON movimentacoes_estoque(produto_id);
CREATE INDEX idx_mov_fornecedor  ON movimentacoes_estoque(fornecedor_id);
CREATE INDEX idx_mov_tipo        ON movimentacoes_estoque(tipo);
CREATE INDEX idx_mov_motivo      ON movimentacoes_estoque(motivo);
CREATE INDEX idx_mov_criado_em   ON movimentacoes_estoque(criado_em);

-- ============================================================
SET FOREIGN_KEY_CHECKS = 1;
-- ============================================================