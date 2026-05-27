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

-- ============================================================
-- ZAFENATE CONTROL — Módulo de Vendas e Caixa
-- Migration: 002_vendas_caixa.sql
-- Depende de: 001_base.sql (usuarios, produtos, clientes)
-- ============================================================
 
SET FOREIGN_KEY_CHECKS = 0;
SET NAMES utf8mb4;
 
-- ============================================================
-- 6. CLIENTES
-- (Necessário antes de vendas pois vendas referencia clientes)
-- ============================================================
CREATE TABLE IF NOT EXISTS clientes (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome          VARCHAR(150) NOT NULL,
    cpf_cnpj      VARCHAR(18)  NULL,
    email         VARCHAR(100) NULL,
    telefone      VARCHAR(20)  NULL,
    celular       VARCHAR(20)  NULL,
    cep           VARCHAR(9)   NULL,
    logradouro    VARCHAR(150) NULL,
    numero        VARCHAR(10)  NULL,
    complemento   VARCHAR(100) NULL,
    bairro        VARCHAR(80)  NULL,
    cidade        VARCHAR(80)  NULL,
    uf            CHAR(2)      NULL,
    observacoes   TEXT         NULL,
    ativo         TINYINT(1)   NOT NULL DEFAULT 1,
    criado_em     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 
    UNIQUE KEY uk_cliente_cpf_cnpj (cpf_cnpj)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Clientes';
 
-- Cliente padrão "Consumidor Final" para vendas sem identificação
INSERT INTO clientes (id, nome, ativo)
VALUES (1, 'Consumidor Final', 1)
ON DUPLICATE KEY UPDATE id = id;
 
CREATE INDEX idx_cli_ativo ON clientes(ativo);
CREATE INDEX idx_cli_nome  ON clientes(nome);
 
 
-- ============================================================
-- 7. CAIXAS
-- Cada abertura de caixa gera um registro.
-- Um caixa só pode estar aberto por um operador por vez.
-- ============================================================
CREATE TABLE IF NOT EXISTS caixas (
    id                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id        INT UNSIGNED NOT NULL,
    status            ENUM('aberto','fechado') NOT NULL DEFAULT 'aberto',
    saldo_abertura    DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    saldo_esperado    DECIMAL(10,2) NOT NULL DEFAULT 0.00, -- calculado ao fechar
    saldo_informado   DECIMAL(10,2) NULL,                  -- contado pelo operador
    diferenca         DECIMAL(10,2) GENERATED ALWAYS AS   -- diferença automática
                        (saldo_informado - saldo_esperado) STORED,
    total_vendas      DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    total_sangrias    DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    total_suprimentos DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    observacao_fechamento TEXT NULL,
    aberto_em         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fechado_em        DATETIME NULL,
    atualizado_em     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 
    CONSTRAINT fk_caixa_usuario
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Controle de abertura e fechamento de caixa';
 
CREATE INDEX idx_caixa_usuario ON caixas(usuario_id);
CREATE INDEX idx_caixa_status  ON caixas(status);
 
 
-- ============================================================
-- 8. MOVIMENTAÇÕES DO CAIXA (sangria, suprimento, etc.)
-- ============================================================
CREATE TABLE IF NOT EXISTS caixa_movimentos (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    caixa_id   INT UNSIGNED NOT NULL,
    usuario_id INT UNSIGNED NULL,
    tipo       ENUM('suprimento','sangria') NOT NULL,
    valor      DECIMAL(10,2) NOT NULL,
    observacao VARCHAR(255)  NULL,
    criado_em  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
 
    CONSTRAINT fk_cmov_caixa
        FOREIGN KEY (caixa_id) REFERENCES caixas(id)
        ON UPDATE CASCADE,
 
    CONSTRAINT fk_cmov_usuario
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Sangrias e suprimentos do caixa';
 
CREATE INDEX idx_cmov_caixa ON caixa_movimentos(caixa_id);
 
 
-- ============================================================
-- 9. VENDAS (cabeçalho)
-- ============================================================
CREATE TABLE IF NOT EXISTS vendas (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    caixa_id        INT UNSIGNED NOT NULL,
    cliente_id      INT UNSIGNED NOT NULL DEFAULT 1, -- padrão: Consumidor Final
    usuario_id      INT UNSIGNED NOT NULL,
    numero          INT UNSIGNED NOT NULL,           -- número sequencial da venda
    status          ENUM('aberta','finalizada','cancelada') NOT NULL DEFAULT 'aberta',
 
    -- Totais
    subtotal        DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    desconto_tipo   ENUM('percentual','valor') NULL,
    desconto_valor  DECIMAL(10,2) NOT NULL DEFAULT 0.00, -- valor em R$ calculado
    desconto_perc   DECIMAL(5,2)  NOT NULL DEFAULT 0.00, -- percentual
    total           DECIMAL(10,2) NOT NULL DEFAULT 0.00,
 
    -- Controle
    observacao      VARCHAR(255) NULL,
    cancelado_por   INT UNSIGNED NULL,
    motivo_cancelamento VARCHAR(255) NULL,
    cancelado_em    DATETIME NULL,
    criado_em       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 
    UNIQUE KEY uk_venda_numero (numero),
 
    CONSTRAINT fk_venda_caixa
        FOREIGN KEY (caixa_id) REFERENCES caixas(id)
        ON UPDATE CASCADE,
 
    CONSTRAINT fk_venda_cliente
        FOREIGN KEY (cliente_id) REFERENCES clientes(id)
        ON UPDATE CASCADE,
 
    CONSTRAINT fk_venda_usuario
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
        ON UPDATE CASCADE,
 
    CONSTRAINT fk_venda_cancelado_por
        FOREIGN KEY (cancelado_por) REFERENCES usuarios(id)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Cabeçalho das vendas';
 
CREATE INDEX idx_venda_caixa    ON vendas(caixa_id);
CREATE INDEX idx_venda_cliente  ON vendas(cliente_id);
CREATE INDEX idx_venda_status   ON vendas(status);
CREATE INDEX idx_venda_criado   ON vendas(criado_em);
 
 
-- ============================================================
-- 10. ITENS DA VENDA
-- ============================================================
CREATE TABLE IF NOT EXISTS venda_itens (
    id                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    venda_id          INT UNSIGNED NOT NULL,
    produto_id        INT UNSIGNED NOT NULL,
 
    -- Snapshot dos dados do produto no momento da venda
    produto_nome      VARCHAR(150) NOT NULL,
    produto_codigo    VARCHAR(50)  NOT NULL,
    unidade_sigla     VARCHAR(10)  NOT NULL DEFAULT 'UN',
 
    quantidade        DECIMAL(10,3) NOT NULL,
    preco_unitario    DECIMAL(10,2) NOT NULL, -- preço no momento da venda
    preco_custo       DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    desconto_item     DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    subtotal          DECIMAL(10,2) NOT NULL, -- (qtd * preco_unitario) - desconto_item
 
    CONSTRAINT fk_item_venda
        FOREIGN KEY (venda_id) REFERENCES vendas(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
 
    CONSTRAINT fk_item_produto
        FOREIGN KEY (produto_id) REFERENCES produtos(id)
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Itens de cada venda';
 
CREATE INDEX idx_item_venda   ON venda_itens(venda_id);
CREATE INDEX idx_item_produto ON venda_itens(produto_id);
 
 
-- ============================================================
-- 11. PAGAMENTOS DA VENDA
-- Suporte a pagamento misto (ex: R$50 dinheiro + R$50 PIX)
-- ============================================================
CREATE TABLE IF NOT EXISTS venda_pagamentos (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    venda_id    INT UNSIGNED NOT NULL,
    forma       ENUM('dinheiro','pix','cartao_debito','cartao_credito','voucher','outros') NOT NULL,
    valor       DECIMAL(10,2) NOT NULL,
    troco       DECIMAL(10,2) NOT NULL DEFAULT 0.00, -- só se forma = dinheiro
    referencia  VARCHAR(100) NULL, -- NSU, código PIX, etc.
    criado_em   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 
    CONSTRAINT fk_pgto_venda
        FOREIGN KEY (venda_id) REFERENCES vendas(id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Pagamentos de cada venda (suporte a misto)';
 
CREATE INDEX idx_pgto_venda ON venda_pagamentos(venda_id);
CREATE INDEX idx_pgto_forma ON venda_pagamentos(forma);
 
 
-- ============================================================
-- 12. CONFIGURAÇÕES DA EMPRESA
-- ============================================================
CREATE TABLE IF NOT EXISTS configuracoes (
    chave         VARCHAR(60)  NOT NULL PRIMARY KEY,
    valor         TEXT         NULL,
    tipo          ENUM('texto','imagem','cor','booleano','numero') NOT NULL DEFAULT 'texto',
    descricao     VARCHAR(150) NULL,
    atualizado_em DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Configurações da empresa';
 
INSERT INTO configuracoes (chave, valor, tipo, descricao) VALUES
    ('empresa_nome',    'Minha Loja',  'texto',   'Nome da empresa exibido no sistema'),
    ('empresa_logo',    '',            'imagem',  'Arquivo de logo (uploads/logo/)'),
    ('empresa_cor',     '#1A1A1A',     'cor',     'Cor primária da sidebar e botões'),
    ('empresa_slogan',  '',            'texto',   'Slogan ou descrição curta'),
    ('empresa_cnpj',    '',            'texto',   'CNPJ da empresa'),
    ('empresa_telefone','',            'texto',   'Telefone de contato'),
    ('pdv_desconto_max','10',          'numero',  'Desconto máximo permitido no PDV (%)'),
    ('pdv_exige_cliente','0',          'booleano','Exigir cliente identificado no PDV')
ON DUPLICATE KEY UPDATE chave = chave;
 
 
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE IF NOT EXISTS caixa_movimentos (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    caixa_id   INT UNSIGNED NOT NULL,
    usuario_id INT UNSIGNED NULL,
    tipo       ENUM('suprimento','sangria') NOT NULL,
    valor      DECIMAL(10,2) NOT NULL,
    observacao VARCHAR(255)  NULL,
    criado_em  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
 
    CONSTRAINT fk_cmov_caixa
        FOREIGN KEY (caixa_id) REFERENCES caixas(id)
        ON UPDATE CASCADE,
 
    CONSTRAINT fk_cmov_usuario
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Sangrias e suprimentos do caixa';
 
CREATE INDEX IF NOT EXISTS idx_cmov_caixa ON caixa_movimentos(caixa_id);

-- ============================================================
-- Migration: 003_estoque_tipos_ampliados.sql
-- Amplia os ENUMs de tipo e motivo para suportar
-- CANCELAMENTO e PERDA na tela de movimentação manual.
-- ============================================================

ALTER TABLE movimentacoes_estoque
    MODIFY COLUMN tipo ENUM(
        'ENTRADA',
        'SAIDA',
        'AJUSTE'
    ) NOT NULL;

-- Motivo já contém PERDA e DEVOLUCAO (=cancelamento); adicionamos
-- CANCELAMENTO_VENDA como alias explícito e AVARIA para clareza.
ALTER TABLE movimentacoes_estoque
    MODIFY COLUMN motivo ENUM(
        'COMPRA',
        'DEVOLUCAO',
        'VENDA',
        'CANCELAMENTO_VENDA',
        'PERDA',
        'AVARIA',
        'USO_INTERNO',
        'AJUSTE_MANUAL',
        'INVENTARIO',
        'TRANSFERENCIA'
    ) NOT NULL;

-- Índice de período (muito usado nos filtros de listagem)
CREATE INDEX IF NOT EXISTS idx_mov_criado ON movimentacoes_estoque(criado_em);
 