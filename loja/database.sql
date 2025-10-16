-- Banco de dados para InovaTech Store
-- Executar no phpMyAdmin ou MySQL

CREATE DATABASE IF NOT EXISTS inovatech_store CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE inovatech_store;

-- Tabela de usuários (admins e clientes)
CREATE TABLE usuarios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL,
    telefone VARCHAR(20),
    tipo_usuario ENUM('admin', 'cliente') DEFAULT 'cliente',
    endereco TEXT,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    ativo BOOLEAN DEFAULT TRUE
);

-- Tabela de categorias
CREATE TABLE categorias (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(50) NOT NULL,
    descricao TEXT,
    icone VARCHAR(50),
    ordem INT DEFAULT 0,
    ativo BOOLEAN DEFAULT TRUE,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de produtos
CREATE TABLE produtos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(150) NOT NULL,
    descricao TEXT,
    especificacoes TEXT,
    preco DECIMAL(10,2) NOT NULL,
    preco_promocional DECIMAL(10,2) NULL,
    categoria_id INT NOT NULL,
    imagem_principal VARCHAR(255),
    imagens_adicionais JSON,
    estoque INT DEFAULT 0,
    estoque_minimo INT DEFAULT 5,
    marca VARCHAR(50),
    modelo VARCHAR(100),
    peso DECIMAL(5,2),
    dimensoes VARCHAR(50),
    garantia_meses INT DEFAULT 12,
    destaque BOOLEAN DEFAULT FALSE,
    ativo BOOLEAN DEFAULT TRUE,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (categoria_id) REFERENCES categorias(id)
);

-- Tabela de pedidos
CREATE TABLE pedidos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    codigo_pedido VARCHAR(50) UNIQUE NOT NULL,
    usuario_id INT,
    nome_cliente VARCHAR(100) NOT NULL,
    email_cliente VARCHAR(100) NOT NULL,
    telefone_cliente VARCHAR(20),
    endereco_entrega TEXT NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    desconto DECIMAL(10,2) DEFAULT 0.00,
    frete DECIMAL(10,2) DEFAULT 0.00,
    total DECIMAL(10,2) NOT NULL,
    status_pedido ENUM('pendente', 'confirmado', 'preparando', 'enviado', 'entregue', 'cancelado') DEFAULT 'pendente',
    forma_pagamento VARCHAR(50),
    observacoes TEXT,
    data_pedido TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
);

-- Tabela de itens do pedido
CREATE TABLE itens_pedido (
    id INT PRIMARY KEY AUTO_INCREMENT,
    pedido_id INT NOT NULL,
    produto_id INT NOT NULL,
    nome_produto VARCHAR(150) NOT NULL,
    preco_unitario DECIMAL(10,2) NOT NULL,
    quantidade INT NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,
    FOREIGN KEY (produto_id) REFERENCES produtos(id)
);

-- Tabela de contatos/mensagens
CREATE TABLE contatos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    telefone VARCHAR(20),
    assunto VARCHAR(100) NOT NULL,
    mensagem TEXT NOT NULL,
    status ENUM('novo', 'lido', 'respondido', 'arquivado') DEFAULT 'novo',
    data_contato TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_resposta TIMESTAMP NULL
);

-- Tabela de configurações da loja
CREATE TABLE configuracoes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    chave VARCHAR(100) UNIQUE NOT NULL,
    valor TEXT,
    descricao TEXT,
    tipo ENUM('string', 'number', 'boolean', 'json') DEFAULT 'string',
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Inserir usuário admin padrão
INSERT INTO usuarios (nome, email, senha, tipo_usuario) VALUES 
('Administrador', 'admin@inovatech.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');
-- Senha padrão: password (criptografada)

-- Inserir categorias iniciais
INSERT INTO categorias (nome, descricao, icone, ordem) VALUES 
('Computadores', 'PCs, notebooks e workstations', '💻', 1),
('Videogames', 'Consoles, jogos e acessórios', '🎮', 2),
('Periféricos', 'Teclados, mouses e monitores', '⌨️', 3),
('Componentes', 'Processadores, placas de vídeo, RAM', '🔧', 4);

-- Inserir produtos de exemplo (Computadores)
INSERT INTO produtos (nome, descricao, especificacoes, preco, categoria_id, marca, modelo, estoque, destaque) VALUES 
('PC Gamer Entry Level', 'Computador ideal para jogos casuais e trabalho básico', '• Processador AMD Ryzen 5 3400G\n• 8GB RAM DDR4\n• SSD 256GB\n• Placa de vídeo integrada Vega 11\n• Windows 11 Home', 2299.00, 1, 'Custom Build', 'Entry Gaming', 15, TRUE),

('PC Gamer Pro', 'Setup balanceado para jogos modernos em Full HD', '• Processador AMD Ryzen 5 5600G\n• 16GB RAM DDR4 3200MHz\n• SSD 512GB + HD 1TB\n• GeForce GTX 1660 Super 6GB\n• Windows 11 Home', 3499.00, 1, 'Custom Build', 'Pro Gaming', 8, TRUE),

('PC Gamer Ultra', 'Máxima performance para jogos 4K e streaming', '• Processador AMD Ryzen 7 5700X\n• 32GB RAM DDR4 3600MHz\n• SSD 1TB NVMe PCIe 4.0\n• GeForce RTX 3060 Ti 8GB\n• Windows 11 Pro', 5999.00, 1, 'Custom Build', 'Ultra Gaming', 5, TRUE),

('MacBook Air M2', 'Ultrabook da Apple com chip M2 revolucionário', '• Processador Apple M2 8-core\n• 8GB RAM Unificada\n• SSD 256GB\n• Tela Retina 13.6" Liquid Retina\n• macOS Ventura', 8999.00, 1, 'Apple', 'MacBook Air M2', 3, FALSE),

('Dell Inspiron 15 3000', 'Notebook versátil para trabalho e estudos', '• Processador Intel Core i5-1235U\n• 8GB RAM DDR4\n• SSD 256GB\n• Tela 15.6" Full HD\n• Windows 11 Home', 2799.00, 1, 'Dell', 'Inspiron 15 3000', 12, FALSE);

-- Inserir produtos de exemplo (Videogames)
INSERT INTO produtos (nome, descricao, especificacoes, preco, categoria_id, marca, modelo, estoque, destaque) VALUES 
('PlayStation 5', 'Console de nova geração da Sony', '• Processador AMD Zen 2 8-core\n• 16GB GDDR6\n• SSD 825GB ultrarrápido\n• Ray Tracing em tempo real\n• Suporte 4K até 120fps', 4199.00, 2, 'Sony', 'PlayStation 5', 2, TRUE),

('Xbox Series X', 'O Xbox mais poderoso de todos os tempos', '• Processador AMD Zen 2 8-core\n• 16GB GDDR6\n• SSD 1TB NVMe\n• 4K nativo até 120fps\n• Ray Tracing avançado', 3999.00, 2, 'Microsoft', 'Xbox Series X', 4, TRUE),

('Nintendo Switch OLED', 'Console híbrido com tela OLED vibrante', '• Tela OLED 7" portátil\n• 64GB armazenamento interno\n• Dock para TV incluído\n• Modo portátil e mesa\n• Bateria até 9 horas', 2299.00, 2, 'Nintendo', 'Switch OLED', 8, TRUE),

('God of War Ragnarök', 'Aventura épica de Kratos e Atreus', '• Plataforma: PlayStation 5\n• Gênero: Ação/Aventura\n• Single Player\n• Classificação: +16 anos\n• Dublado em português', 299.00, 2, 'Sony', 'God of War Ragnarök', 25, FALSE);

-- Inserir configurações iniciais
INSERT INTO configuracoes (chave, valor, descricao, tipo) VALUES 
('loja_nome', 'InovaTech Store', 'Nome da loja', 'string'),
('loja_email', 'contato@inovatech.com.br', 'E-mail principal da loja', 'string'),
('loja_telefone', '(11) 3456-7890', 'Telefone da loja', 'string'),
('loja_endereco', 'Rua da Inovação, 123 - Vila Tecnológica, São Paulo - SP', 'Endereço da loja', 'string'),
('frete_gratis_valor', '299.00', 'Valor mínimo para frete grátis', 'number'),
('taxa_entrega', '29.90', 'Taxa padrão de entrega', 'number'),
('loja_ativa', '1', 'Loja ativa/inativa', 'boolean');

-- Criar índices para otimização
CREATE INDEX idx_produtos_categoria ON produtos(categoria_id);
CREATE INDEX idx_produtos_ativo ON produtos(ativo);
CREATE INDEX idx_produtos_destaque ON produtos(destaque);
CREATE INDEX idx_pedidos_status ON pedidos(status_pedido);
CREATE INDEX idx_pedidos_data ON pedidos(data_pedido);
CREATE INDEX idx_usuarios_email ON usuarios(email);
CREATE INDEX idx_contatos_status ON contatos(status);

-- Views úteis para relatórios
CREATE VIEW view_produtos_completa AS
SELECT 
    p.id,
    p.nome,
    p.descricao,
    p.preco,
    p.preco_promocional,
    p.estoque,
    p.destaque,
    p.ativo,
    c.nome AS categoria_nome,
    p.data_criacao
FROM produtos p
JOIN categorias c ON p.categoria_id = c.id;

CREATE VIEW view_pedidos_resumo AS
SELECT 
    p.id,
    p.codigo_pedido,
    p.nome_cliente,
    p.total,
    p.status_pedido,
    p.data_pedido,
    COUNT(ip.id) AS total_itens
FROM pedidos p
LEFT JOIN itens_pedido ip ON p.id = ip.pedido_id
GROUP BY p.id;

-- Triggers para atualizar estoque
DELIMITER $$

CREATE TRIGGER after_pedido_confirmado
AFTER UPDATE ON pedidos
FOR EACH ROW
BEGIN
    IF NEW.status_pedido = 'confirmado' AND OLD.status_pedido != 'confirmado' THEN
        UPDATE produtos p
        JOIN itens_pedido ip ON p.id = ip.produto_id
        SET p.estoque = p.estoque - ip.quantidade
        WHERE ip.pedido_id = NEW.id;
    END IF;
END$$

CREATE TRIGGER after_pedido_cancelado
AFTER UPDATE ON pedidos
FOR EACH ROW
BEGIN
    IF NEW.status_pedido = 'cancelado' AND OLD.status_pedido = 'confirmado' THEN
        UPDATE produtos p
        JOIN itens_pedido ip ON p.id = ip.produto_id
        SET p.estoque = p.estoque + ip.quantidade
        WHERE ip.pedido_id = NEW.id;
    END IF;
END$$

DELIMITER ;

-- Inserir alguns pedidos de exemplo
INSERT INTO pedidos (codigo_pedido, nome_cliente, email_cliente, telefone_cliente, endereco_entrega, subtotal, total, status_pedido) VALUES 
('PED2024001', 'João Silva', 'joao@email.com', '(11) 99999-1111', 'Rua das Flores, 123 - São Paulo, SP', 2299.00, 2329.90, 'confirmado'),
('PED2024002', 'Maria Santos', 'maria@email.com', '(11) 99999-2222', 'Av. Paulista, 456 - São Paulo, SP', 4199.00, 4199.00, 'enviado');

-- Inserir itens dos pedidos de exemplo
INSERT INTO itens_pedido (pedido_id, produto_id, nome_produto, preco_unitario, quantidade, subtotal) VALUES 
(1, 1, 'PC Gamer Entry Level', 2299.00, 1, 2299.00),
(2, 6, 'PlayStation 5', 4199.00, 1, 4199.00);

COMMIT;