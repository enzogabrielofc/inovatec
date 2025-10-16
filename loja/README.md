# 🛍️ InovaTech Store - Loja Online Completa

Uma loja online moderna e funcional com sistema de gerenciamento integrado para produtos, pedidos e muito mais!

## ✨ Funcionalidades

### 🛒 **Loja Online (Frontend)**
- **Catálogo de Produtos** - Lista dinâmica de produtos do banco de dados
- **Sistema de Categorias** - Organização por computadores, videogames, etc.
- **Busca e Filtros** - Encontre produtos por nome, categoria ou preço
- **Carrinho de Compras** - Adicionar/remover produtos com persistência local
- **Design Responsivo** - Funciona perfeitamente em mobile e desktop
- **Produtos em Destaque** - Seção especial para produtos promocionais
- **Indicadores de Estoque** - Avisos de estoque baixo e produtos esgotados
- **Preços Promocionais** - Suporte a descontos e promoções

### ⚙️ **Dashboard Administrativo**
- **Login Seguro** - Área administrativa protegida
- **Gerenciamento de Produtos** - CRUD completo via API REST
- **Estatísticas em Tempo Real** - Métricas de produtos, vendas e estoque
- **Alertas de Estoque** - Notificações automáticas de produtos com estoque baixo
- **Gráficos Interativos** - Visualização de dados de vendas
- **Interface Intuitiva** - Design moderno e fácil de usar

### 🛠️ **Sistema Backend**
- **API REST Completa** - Endpoints para todas as operações
- **Banco de Dados MySQL** - Estrutura robusta e otimizada
- **Validações de Segurança** - Sanitização e validação de dados
- **Sistema de Logs** - Rastreamento de erros e ações
- **Paginação Automática** - Performance otimizada para grandes catálogos

## 🚀 Como Configurar

### 1. **Preparar o Ambiente**
```bash
# Certifique-se que o XAMPP está rodando com:
# - Apache ativo
# - MySQL ativo
```

### 2. **Configurar o Banco de Dados**
```sql
-- No phpMyAdmin (http://localhost/phpmyadmin):
-- 1. Importe o arquivo: /loja/database.sql
-- 2. Isso criará automaticamente:
--    - Banco: inovatech_store
--    - Tabelas: produtos, categorias, pedidos, etc.
--    - Dados de exemplo
```

### 3. **Verificar Configurações**
```php
// Arquivo: /loja/config/database.php
// Certifique-se que as configurações estão corretas:

$host = 'localhost';        // ✅ Padrão do XAMPP
$db_name = 'inovatech_store'; // ✅ Nome do banco
$username = 'root';         // ✅ Usuário padrão
$password = '';             // ✅ Senha vazia no XAMPP
```

### 4. **Acessar a Loja**
```
🌐 Loja Principal: http://localhost/inovatec/loja/
📋 Dashboard Admin: http://localhost/inovatec/loja/admin/
```

## 🔑 Credenciais de Acesso

### **Dashboard Administrativo**
- **Email:** `admin@inovatech.com`
- **Senha:** `password`

## 📁 Estrutura de Arquivos

```
📁 loja/
├── 📄 index.php              # Página inicial da loja
├── 📄 produtos.php           # Lista de produtos com filtros
├── 📄 database.sql           # Script do banco de dados
├── 📄 style.css              # Estilos principais
├── 📄 script.js              # JavaScript do carrinho
├── 📄 README.md              # Este arquivo
│
├── 📁 config/
│   └── 📄 database.php       # Configurações do banco
│
├── 📁 classes/
│   └── 📄 Product.php        # Classe para produtos
│
├── 📁 api/
│   └── 📄 products.php       # API REST para produtos
│
├── 📁 admin/                 # Dashboard administrativo
│   ├── 📄 index.php          # Dashboard principal
│   ├── 📄 login.php          # Tela de login
│   └── 📄 admin-style.css    # Estilos do admin
│
├── 📁 uploads/               # Imagens dos produtos
├── 📁 logs/                  # Logs do sistema
└── 📁 temp/                  # Arquivos temporários
```

## 🎯 Como Usar

### **1. Gerenciar Produtos (Admin)**
1. Acesse: `http://localhost/inovatec/loja/admin/`
2. Faça login com as credenciais acima
3. Use o menu lateral para navegar
4. **Dashboard** - Veja estatísticas e produtos recentes
5. **Produtos** - Adicionar/editar/remover produtos *(em desenvolvimento)*

### **2. Navegação da Loja**
- **Página Inicial** - Produtos em destaque e categorias
- **Produtos** - Catálogo completo com filtros
- **Busca** - Digite qualquer termo na barra de busca
- **Carrinho** - Clique no ícone do carrinho para ver itens

### **3. API REST (Desenvolvedores)**
```bash
# Listar produtos
GET /loja/api/products.php

# Buscar produto específico
GET /loja/api/products.php?id=1

# Filtrar por categoria
GET /loja/api/products.php?categoria=1

# Buscar produtos
GET /loja/api/products.php?busca=notebook

# Paginação
GET /loja/api/products.php?page=2&limit=8
```

## 🔧 Recursos Técnicos

### **Frontend**
- **HTML5** semântico e acessível
- **CSS3** com Grid, Flexbox e animações
- **JavaScript ES6** moderno
- **Font Awesome** para ícones
- **Responsive Design** mobile-first

### **Backend**
- **PHP 7.4+** orientado a objetos
- **MySQL 5.7+** com prepared statements
- **PDO** para acesso seguro ao banco
- **API REST** com JSON responses
- **Sistema de Sessões** para autenticação

### **Segurança**
- **Sanitização** de dados de entrada
- **Prepared Statements** contra SQL injection
- **CSRF Protection** em formulários
- **XSS Prevention** com htmlspecialchars()
- **Validação** client-side e server-side

## 🎨 Personalização

### **Cores e Tema**
```css
/* Arquivo: style.css */
/* Principais variáveis de cor: */

:root {
  --primary: #667eea;      /* Azul principal */
  --secondary: #764ba2;    /* Roxo secundário */
  --success: #28a745;      /* Verde sucesso */
  --warning: #ffc107;      /* Amarelo aviso */
  --danger: #dc3545;       /* Vermelho erro */
}
```

### **Adicionar Produtos**
1. Use o dashboard admin *(em desenvolvimento)*
2. Ou adicione diretamente no banco:
```sql
INSERT INTO produtos (nome, preco, categoria_id, estoque, destaque) 
VALUES ('Novo Produto', 999.00, 1, 10, 1);
```

## 🚀 Próximas Funcionalidades

- [ ] **Checkout Real** - Processar pedidos no banco de dados
- [ ] **Painel de Produtos** - CRUD completo no admin
- [ ] **Sistema de Clientes** - Cadastro e login de usuários
- [ ] **Histórico de Pedidos** - Acompanhamento de compras
- [ ] **Integração com Pagamentos** - PagSeguro, PayPal, etc.
- [ ] **Sistema de Cupons** - Códigos de desconto
- [ ] **Relatórios Avançados** - Analytics completos

## 🆘 Solução de Problemas

### **Erro de Conexão com Banco**
```bash
# Verifique se o MySQL está rodando:
# No XAMPP Control Panel > MySQL > Start

# Importe novamente o database.sql se necessário
```

### **Página em Branco**
```bash
# Ative o display de erros PHP:
# No XAMPP > Config > PHP (php.ini)
# Encontre: display_errors = Off
# Altere para: display_errors = On
```

### **Produtos Não Aparecem**
```sql
-- Verifique se há produtos no banco:
SELECT * FROM produtos WHERE ativo = 1;

-- Se vazio, importe novamente o database.sql
```

## 💻 Desenvolvimento

### **Estrutura MVC**
- **Models** - Classes PHP (Product.php)
- **Views** - Templates PHP (index.php, produtos.php)
- **Controllers** - APIs REST (api/products.php)

### **Padrões Utilizados**
- **PSR-4** Autoloading
- **RESTful** API design
- **SOLID** Principles
- **DRY** Don't Repeat Yourself
- **Mobile-First** Responsive design

---

## 🎉 Status do Projeto

✅ **Banco de Dados** - Estrutura completa criada  
✅ **API REST** - Endpoints funcionais  
✅ **Frontend** - Loja responsiva e moderna  
✅ **Dashboard** - Interface administrativa  
✅ **Sistema de Busca** - Filtros e paginação  
⏳ **Sistema de Pedidos** - Em desenvolvimento  
⏳ **Autenticação** - Login de clientes  
⏳ **Checkout** - Processar compras reais  

**Versão:** 2.0 - Loja Completa  
**Última Atualização:** $(Get-Date -Format "dd/MM/yyyy")

---

🚀 **A InovaTech Store está pronta para uso!** Explore todas as funcionalidades e personalize conforme suas necessidades.