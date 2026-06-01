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

-- ============================================================
-- Migration: 004_clientes_ampliado.sql
-- Amplia a tabela clientes com campos para PF/PJ,
-- dados comerciais e integração futura com PDV/NF-e
-- ============================================================

ALTER TABLE clientes
    ADD COLUMN IF NOT EXISTS tipo_pessoa    ENUM('fisica','juridica') NOT NULL DEFAULT 'fisica'
        COMMENT 'Tipo de pessoa para NF-e e relatórios'
        AFTER nome,

    ADD COLUMN IF NOT EXISTS nome_fantasia  VARCHAR(150) NULL
        COMMENT 'Nome fantasia (PJ) ou apelido (PF)'
        AFTER tipo_pessoa,

    ADD COLUMN IF NOT EXISTS ie             VARCHAR(20) NULL
        COMMENT 'Inscrição Estadual (PJ)'
        AFTER cpf_cnpj,

    ADD COLUMN IF NOT EXISTS data_nascimento DATE NULL
        COMMENT 'Data de nascimento (PF) ou fundação (PJ)'
        AFTER ie,

    ADD COLUMN IF NOT EXISTS contato        VARCHAR(100) NULL
        COMMENT 'Nome do contato secundário (PJ)'
        AFTER celular,

    ADD COLUMN IF NOT EXISTS limite_credito DECIMAL(10,2) NOT NULL DEFAULT 0.00
        COMMENT 'Limite de crédito para vendas a prazo'
        AFTER observacoes;

-- Índice útil para busca por aniversariantes (relatórios)
CREATE INDEX IF NOT EXISTS idx_cli_nascimento ON clientes(data_nascimento);
CREATE INDEX IF NOT EXISTS idx_cli_tipo       ON clientes(tipo_pessoa);

-- ============================================================
-- Migration: 005_fornecedores_ampliado.sql
-- Amplia a tabela fornecedores com dados financeiros,
-- tipo de pessoa, Pix, avaliação e obs internas.
-- ============================================================

ALTER TABLE fornecedores

    -- Tipo de pessoa (já indica PJ/PF para NF-e)
    ADD COLUMN IF NOT EXISTS tipo_pessoa     ENUM('fisica','juridica') NOT NULL DEFAULT 'juridica'
        COMMENT 'Tipo de pessoa'
        AFTER id,

    -- WhatsApp e site (faltavam)
    ADD COLUMN IF NOT EXISTS whatsapp        VARCHAR(20)  NULL
        COMMENT 'WhatsApp do fornecedor'
        AFTER celular,

    ADD COLUMN IF NOT EXISTS site            VARCHAR(150) NULL
        COMMENT 'Site do fornecedor'
        AFTER whatsapp,

    -- Financeiro
    ADD COLUMN IF NOT EXISTS forma_pagamento ENUM('boleto','pix','deposito','cartao','dinheiro','cheque','outros') NULL
        COMMENT 'Forma de pagamento padrão'
        AFTER prazo_pagamento,

    ADD COLUMN IF NOT EXISTS limite_credito  DECIMAL(10,2) NOT NULL DEFAULT 0.00
        COMMENT 'Limite de crédito para compras'
        AFTER forma_pagamento,

    ADD COLUMN IF NOT EXISTS banco           VARCHAR(80)  NULL
        COMMENT 'Nome do banco'
        AFTER limite_credito,

    ADD COLUMN IF NOT EXISTS agencia         VARCHAR(20)  NULL
        AFTER banco,

    ADD COLUMN IF NOT EXISTS conta           VARCHAR(30)  NULL
        AFTER agencia,

    ADD COLUMN IF NOT EXISTS chave_pix       VARCHAR(150) NULL
        COMMENT 'Chave Pix'
        AFTER conta,

    ADD COLUMN IF NOT EXISTS obs_financeiras TEXT NULL
        COMMENT 'Observações financeiras internas'
        AFTER chave_pix,

    -- Comercial
    ADD COLUMN IF NOT EXISTS prazo_entrega   TINYINT UNSIGNED NULL
        COMMENT 'Prazo de entrega em dias'
        AFTER obs_financeiras,

    ADD COLUMN IF NOT EXISTS obs_internas    TEXT NULL
        COMMENT 'Observações internas (ex: só atende até 17h)'
        AFTER prazo_entrega,

    -- Avaliação (1 a 5)
    ADD COLUMN IF NOT EXISTS avaliacao_prazo       TINYINT UNSIGNED NULL
        COMMENT 'Avaliação de prazo (1-5)'
        AFTER obs_internas,

    ADD COLUMN IF NOT EXISTS avaliacao_qualidade   TINYINT UNSIGNED NULL
        COMMENT 'Avaliação de qualidade (1-5)'
        AFTER avaliacao_prazo,

    ADD COLUMN IF NOT EXISTS avaliacao_atendimento TINYINT UNSIGNED NULL
        COMMENT 'Avaliação de atendimento (1-5)'
        AFTER avaliacao_qualidade;

-- Índice para busca por tipo
CREATE INDEX IF NOT EXISTS idx_forn_tipo ON fornecedores(tipo_pessoa);

-- ============================================================
-- ZAFENATE CONTROL — Migration 06: Módulo de Compras
-- Depende de: fornecedores, produtos, usuarios
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;
SET NAMES utf8mb4;

-- ============================================================
-- COMPRAS (cabeçalho)
-- ============================================================
CREATE TABLE IF NOT EXISTS compras (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    fornecedor_id   INT UNSIGNED NOT NULL,
    usuario_id      INT UNSIGNED NOT NULL,

    numero          VARCHAR(20)  NOT NULL COMMENT 'Número sequencial interno CMP-000001',
    numero_nf       VARCHAR(20)  NULL     COMMENT 'Número da Nota Fiscal do fornecedor',
    serie_nf        VARCHAR(5)   NULL,

    status          ENUM('rascunho','confirmada','cancelada') NOT NULL DEFAULT 'rascunho',

    -- Totais
    subtotal        DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    desconto_valor  DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    frete           DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    total           DECIMAL(10,2) NOT NULL DEFAULT 0.00,

    -- Pagamento
    forma_pagamento ENUM('boleto','pix','deposito','cartao','dinheiro','cheque','outros') NULL,
    prazo_pagamento TINYINT UNSIGNED NULL COMMENT 'Prazo em dias',
    vencimento      DATE NULL             COMMENT 'Data de vencimento do pagamento',

    -- Entrega
    data_emissao    DATE NOT NULL         COMMENT 'Data de emissão da NF ou da compra',
    data_entrega    DATE NULL             COMMENT 'Data real de entrega/entrada no estoque',

    observacao      TEXT NULL,
    motivo_cancelamento VARCHAR(255) NULL,

    -- Auditoria
    criado_em       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY uk_compra_numero (numero),

    CONSTRAINT fk_compra_fornecedor
        FOREIGN KEY (fornecedor_id) REFERENCES fornecedores(id)
        ON UPDATE CASCADE,

    CONSTRAINT fk_compra_usuario
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
        ON UPDATE CASCADE

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Cabeçalho das ordens de compra';

CREATE INDEX idx_compra_fornecedor ON compras(fornecedor_id);
CREATE INDEX idx_compra_status     ON compras(status);
CREATE INDEX idx_compra_emissao    ON compras(data_emissao);
CREATE INDEX idx_compra_usuario    ON compras(usuario_id);

-- ============================================================
-- ITENS DA COMPRA
-- ============================================================
CREATE TABLE IF NOT EXISTS compra_itens (
    id                   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    compra_id            INT UNSIGNED NOT NULL,
    produto_id           INT UNSIGNED NOT NULL,

    -- Snapshot do produto no momento da compra
    produto_nome         VARCHAR(150) NOT NULL,
    produto_codigo       VARCHAR(50)  NOT NULL,
    unidade_sigla        VARCHAR(10)  NOT NULL DEFAULT 'UN',

    quantidade           DECIMAL(10,3) NOT NULL,
    preco_unitario       DECIMAL(10,2) NOT NULL COMMENT 'Preço pago nesta compra',
    desconto_item        DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    subtotal             DECIMAL(10,2) NOT NULL,

    -- Controle de entrada no estoque
    estoque_atualizado   TINYINT(1)   NOT NULL DEFAULT 0 COMMENT '1 = já entrou no estoque',

    CONSTRAINT fk_ci_compra
        FOREIGN KEY (compra_id) REFERENCES compras(id)
        ON DELETE CASCADE ON UPDATE CASCADE,

    CONSTRAINT fk_ci_produto
        FOREIGN KEY (produto_id) REFERENCES produtos(id)
        ON UPDATE CASCADE

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Itens de cada ordem de compra';

CREATE INDEX idx_ci_compra   ON compra_itens(compra_id);
CREATE INDEX idx_ci_produto  ON compra_itens(produto_id);

SET FOREIGN_KEY_CHECKS = 1;

-- Adiciona colunas para suporte a parcelamento no módulo de vendas. O campo 'parcelas' indica em quantas vezes o pagamento foi dividido (1 = à vista). O campo 'valor_parcela' é gerado automaticamente dividindo o valor total pelo número de parcelas, facilitando exibição e cálculos futuros. Essa estrutura suporta principalmente cartões de crédito, mas pode ser usada para qualquer forma de pagamento que permita parcelamento.
ALTER TABLE venda_pagamentos
    ADD COLUMN parcelas TINYINT UNSIGNED NOT NULL DEFAULT 1 AFTER troco,
    ADD COLUMN valor_parcela DECIMAL(10,2) GENERATED ALWAYS AS (valor / parcelas) STORED;