<?php
session_start();

// Verificar se é admin
if (!isset($_SESSION['admin_logged'])) {
    header('Location: admin-dashboard.php');
    exit;
}

require_once 'classes/Product.php';
require_once 'classes/Category.php';

$product = new Product();
$message = '';
$messageType = '';

// Processar criação de produto
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create') {
    try {
        // Validar dados
        $errors = $product->validate($_POST);
        
        if (empty($errors)) {
            $product->nome = $_POST['nome'];
            $product->descricao = $_POST['descricao'] ?? '';
            $product->especificacoes = $_POST['especificacoes'] ?? '';
            $product->preco = $_POST['preco'];
            $product->preco_promocional = !empty($_POST['preco_promocional']) ? $_POST['preco_promocional'] : null;
            $product->categoria_id = $_POST['categoria_id'];
            $product->estoque = $_POST['estoque'] ?? 0;
            $product->estoque_minimo = $_POST['estoque_minimo'] ?? 5;
            $product->marca = $_POST['marca'] ?? '';
            $product->modelo = $_POST['modelo'] ?? '';
            $product->peso = $_POST['peso'] ?? null;
            $product->dimensoes = $_POST['dimensoes'] ?? '';
            $product->garantia_meses = $_POST['garantia_meses'] ?? 12;
            $product->destaque = isset($_POST['destaque']) ? 1 : 0;
            $product->imagem_principal = $_POST['imagem_principal'] ?? '';
            
            if ($product->create()) {
                $message = 'Produto criado com sucesso! ID: ' . $product->id;
                $messageType = 'success';
                
                // Limpar formulário após sucesso
                $_POST = [];
            } else {
                $message = 'Erro ao criar produto. Tente novamente.';
                $messageType = 'error';
            }
        } else {
            $message = 'Erros encontrados: ' . implode(', ', $errors);
            $messageType = 'error';
        }
    } catch (Exception $e) {
        $message = 'Erro: ' . $e->getMessage();
        $messageType = 'error';
    }
}

// Buscar categorias para o formulário
try {
    $category = new Category();
    $categorias = $category->readAll();
} catch (Exception $e) {
    $categorias = [
        ['id' => 1, 'nome' => 'Computadores'],
        ['id' => 2, 'nome' => 'Videogames'],
        ['id' => 3, 'nome' => 'Periféricos'],
        ['id' => 4, 'nome' => 'Componentes']
    ];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adicionar Produto - InovaTech Store</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f5f7fa; }
        
        .admin-layout { display: flex; min-height: 100vh; }
        
        .sidebar { width: 250px; background: #2c3e50; color: white; }
        .sidebar .logo { padding: 2rem 1rem; border-bottom: 1px solid #34495e; text-align: center; }
        .sidebar .logo h2 { font-size: 1.5rem; margin-bottom: 0.5rem; }
        .sidebar .logo span { font-size: 0.9rem; color: #bdc3c7; }
        
        .admin-nav { padding: 1rem 0; }
        .admin-nav .nav-link { display: block; padding: 1rem 1.5rem; color: #ecf0f1; text-decoration: none; transition: all 0.3s; }
        .admin-nav .nav-link:hover { background: #34495e; }
        .admin-nav .nav-link.active { background: #3498db; }
        .admin-nav .nav-link i { margin-right: 0.75rem; width: 20px; }
        .admin-nav .nav-link.logout { border-top: 1px solid #34495e; margin-top: 2rem; color: #e74c3c; }
        
        .main-content { flex: 1; padding: 2rem; }
        
        .admin-header { margin-bottom: 2rem; }
        .admin-header h1 { font-size: 2rem; color: #2c3e50; margin-bottom: 0.5rem; }
        .admin-header p { color: #7f8c8d; }
        
        .breadcrumb { margin-bottom: 1.5rem; }
        .breadcrumb a { color: #3498db; text-decoration: none; }
        .breadcrumb a:hover { text-decoration: underline; }
        .breadcrumb span { color: #7f8c8d; margin: 0 0.5rem; }
        
        .message { padding: 1rem; border-radius: 8px; margin-bottom: 2rem; display: flex; align-items: center; }
        .message.success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .message.error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .message i { margin-right: 0.5rem; font-size: 1.2rem; }
        
        .form-container { background: white; border-radius: 8px; padding: 2rem; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        
        .form-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; padding-bottom: 1rem; border-bottom: 2px solid #ecf0f1; }
        .form-header h2 { color: #2c3e50; }
        .btn-secondary { padding: 0.75rem 1.5rem; background: #95a5a6; color: white; text-decoration: none; border-radius: 4px; }
        .btn-secondary:hover { background: #7f8c8d; }
        
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; }
        
        .form-section { margin-bottom: 2rem; }
        .form-section h3 { color: #2c3e50; margin-bottom: 1rem; padding-bottom: 0.5rem; border-bottom: 1px solid #ecf0f1; }
        
        .form-group { margin-bottom: 1.5rem; }
        .form-group.full-width { grid-column: 1 / -1; }
        .form-group label { display: block; margin-bottom: 0.5rem; font-weight: 600; color: #2c3e50; }
        .form-group label .required { color: #e74c3c; margin-left: 0.25rem; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 0.75rem; border: 2px solid #ddd; border-radius: 4px; font-size: 1rem; transition: border-color 0.3s; }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus { outline: none; border-color: #3498db; }
        .form-group textarea { resize: vertical; min-height: 120px; font-family: inherit; }
        .form-group small { color: #7f8c8d; font-size: 0.9rem; margin-top: 0.25rem; display: block; }
        
        .checkbox-group { display: flex; align-items: center; gap: 0.5rem; margin-top: 0.5rem; }
        .checkbox-group input[type="checkbox"] { width: auto; margin-right: 0.5rem; }
        
        .price-inputs { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
        .stock-inputs { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
        .dimensions-inputs { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
        
        .form-actions { display: flex; gap: 1rem; justify-content: flex-end; margin-top: 2rem; padding-top: 2rem; border-top: 2px solid #ecf0f1; }
        .btn-primary { padding: 1rem 2rem; background: #27ae60; color: white; border: none; border-radius: 4px; font-size: 1rem; font-weight: 600; cursor: pointer; transition: background 0.3s; }
        .btn-primary:hover { background: #229954; }
        .btn-outline { padding: 1rem 2rem; border: 2px solid #3498db; color: #3498db; background: transparent; text-decoration: none; border-radius: 4px; cursor: pointer; transition: all 0.3s; }
        .btn-outline:hover { background: #3498db; color: white; }
        
        .preview-section { background: #f8f9fa; padding: 1.5rem; border-radius: 4px; margin-top: 1rem; }
        .preview-section h4 { margin-bottom: 1rem; color: #2c3e50; }
        .preview-card { background: white; padding: 1rem; border-radius: 4px; border: 1px solid #ddd; }
        
        .input-group { position: relative; }
        .input-group .input-addon { position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%); color: #7f8c8d; }
        .input-group input { padding-left: 2.5rem; }
        
        @media (max-width: 1024px) {
            .admin-layout { flex-direction: column; }
            .sidebar { width: 100%; }
            .form-grid { grid-template-columns: 1fr; }
        }
        
        @media (max-width: 768px) {
            .price-inputs, .stock-inputs, .dimensions-inputs { grid-template-columns: 1fr; }
            .form-actions { flex-direction: column; }
            .form-header { flex-direction: column; align-items: stretch; gap: 1rem; }
        }
    </style>
</head>
<body>
    <div class="admin-layout">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="logo">
                <h2>🔧 InovaTech</h2>
                <span>Admin Panel</span>
            </div>
            <nav class="admin-nav">
                <a href="admin-dashboard.php" class="nav-link">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                <a href="admin-produtos.php" class="nav-link">
                    <i class="fas fa-box"></i> Produtos
                </a>
                <a href="admin-adicionar-produto.php" class="nav-link active">
                    <i class="fas fa-plus"></i> Adicionar Produto
                </a>
                <a href="admin-pedidos.php" class="nav-link">
                    <i class="fas fa-shopping-cart"></i> Pedidos
                </a>
                <a href="admin-vendas.php" class="nav-link">
                    <i class="fas fa-cash-register"></i> Nova Venda
                </a>
                <a href="admin-relatorios.php" class="nav-link">
                    <i class="fas fa-chart-line"></i> Relatórios
                </a>
                <a href="?logout=1" class="nav-link logout">
                    <i class="fas fa-sign-out-alt"></i> Sair
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <header class="admin-header">
                <h1><i class="fas fa-plus-circle"></i> Adicionar Novo Produto</h1>
                <p>Cadastre um novo produto no catálogo da loja</p>
            </header>

            <!-- Breadcrumb -->
            <nav class="breadcrumb">
                <a href="admin-dashboard.php">Dashboard</a>
                <span>/</span>
                <a href="admin-produtos.php">Produtos</a>
                <span>/</span>
                <span>Adicionar Produto</span>
            </nav>

            <?php if ($message): ?>
                <div class="message <?= $messageType ?>">
                    <i class="fas fa-<?= $messageType === 'success' ? 'check-circle' : 'exclamation-triangle' ?>"></i>
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <!-- Formulário -->
            <div class="form-container">
                <div class="form-header">
                    <h2><i class="fas fa-box"></i> Informações do Produto</h2>
                    <a href="admin-produtos.php" class="btn-secondary">
                        <i class="fas fa-arrow-left"></i> Voltar para Lista
                    </a>
                </div>

                <form method="POST" id="productForm">
                    <input type="hidden" name="action" value="create">
                    
                    <div class="form-grid">
                        <!-- Seção Básica -->
                        <div class="form-section">
                            <h3><i class="fas fa-info-circle"></i> Informações Básicas</h3>
                            
                            <div class="form-group">
                                <label>Nome do Produto<span class="required">*</span></label>
                                <input type="text" name="nome" required maxlength="150" 
                                       value="<?= htmlspecialchars($_POST['nome'] ?? '') ?>"
                                       placeholder="Ex: PC Gamer High Performance">
                                <small>Nome que aparecerá no catálogo (máx. 150 caracteres)</small>
                            </div>

                            <div class="form-group">
                                <label>Categoria<span class="required">*</span></label>
                                <select name="categoria_id" required>
                                    <option value="">Selecione uma categoria</option>
                                    <?php foreach ($categorias as $cat): ?>
                                        <option value="<?= $cat['id'] ?>" 
                                                <?= ($_POST['categoria_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($cat['nome']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Marca</label>
                                <input type="text" name="marca" maxlength="50" 
                                       value="<?= htmlspecialchars($_POST['marca'] ?? '') ?>"
                                       placeholder="Ex: Dell, Apple, Samsung">
                            </div>

                            <div class="form-group">
                                <label>Modelo</label>
                                <input type="text" name="modelo" maxlength="100" 
                                       value="<?= htmlspecialchars($_POST['modelo'] ?? '') ?>"
                                       placeholder="Ex: XPS 13, MacBook Pro">
                            </div>
                        </div>

                        <!-- Seção Preços -->
                        <div class="form-section">
                            <h3><i class="fas fa-dollar-sign"></i> Preços e Estoque</h3>
                            
                            <div class="price-inputs">
                                <div class="form-group">
                                    <label>Preço Regular<span class="required">*</span></label>
                                    <div class="input-group">
                                        <span class="input-addon">R$</span>
                                        <input type="number" name="preco" step="0.01" min="0" required 
                                               value="<?= $_POST['preco'] ?? '' ?>"
                                               placeholder="0,00">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Preço Promocional</label>
                                    <div class="input-group">
                                        <span class="input-addon">R$</span>
                                        <input type="number" name="preco_promocional" step="0.01" min="0" 
                                               value="<?= $_POST['preco_promocional'] ?? '' ?>"
                                               placeholder="0,00">
                                    </div>
                                    <small>Deixe vazio se não há promoção</small>
                                </div>
                            </div>

                            <div class="stock-inputs">
                                <div class="form-group">
                                    <label>Quantidade em Estoque</label>
                                    <input type="number" name="estoque" min="0" 
                                           value="<?= $_POST['estoque'] ?? '0' ?>">
                                </div>

                                <div class="form-group">
                                    <label>Estoque Mínimo</label>
                                    <input type="number" name="estoque_minimo" min="0" 
                                           value="<?= $_POST['estoque_minimo'] ?? '5' ?>">
                                    <small>Alerta quando atingir este valor</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Descrições - Full Width -->
                    <div class="form-section">
                        <h3><i class="fas fa-align-left"></i> Descrições</h3>
                        
                        <div class="form-group">
                            <label>Descrição</label>
                            <textarea name="descricao" rows="3" 
                                      placeholder="Descrição geral do produto..."><?= htmlspecialchars($_POST['descricao'] ?? '') ?></textarea>
                            <small>Descrição que aparece no catálogo</small>
                        </div>

                        <div class="form-group">
                            <label>Especificações Técnicas</label>
                            <textarea name="especificacoes" rows="6" 
                                      placeholder="• Processador Intel Core i5&#10;• 8GB RAM DDR4&#10;• SSD 256GB&#10;• Windows 11"><?= htmlspecialchars($_POST['especificacoes'] ?? '') ?></textarea>
                            <small>Uma especificação por linha, use • para marcadores</small>
                        </div>
                    </div>

                    <!-- Detalhes Técnicos -->
                    <div class="form-grid">
                        <div class="form-section">
                            <h3><i class="fas fa-cogs"></i> Detalhes Técnicos</h3>
                            
                            <div class="dimensions-inputs">
                                <div class="form-group">
                                    <label>Peso (kg)</label>
                                    <input type="number" name="peso" step="0.01" min="0" 
                                           value="<?= $_POST['peso'] ?? '' ?>"
                                           placeholder="0.00">
                                </div>

                                <div class="form-group">
                                    <label>Garantia (meses)</label>
                                    <input type="number" name="garantia_meses" min="0" 
                                           value="<?= $_POST['garantia_meses'] ?? '12' ?>">
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Dimensões</label>
                                <input type="text" name="dimensoes" maxlength="50" 
                                       value="<?= htmlspecialchars($_POST['dimensoes'] ?? '') ?>"
                                       placeholder="Ex: 30x20x5 cm">
                                <small>Formato: comprimento x largura x altura</small>
                            </div>
                        </div>

                        <div class="form-section">
                            <h3><i class="fas fa-image"></i> Imagem e Opções</h3>
                            
                            <div class="form-group">
                                <label>URL da Imagem Principal</label>
                                <input type="url" name="imagem_principal" 
                                       value="<?= htmlspecialchars($_POST['imagem_principal'] ?? '') ?>"
                                       placeholder="https://exemplo.com/imagem.jpg">
                                <small>URL completa da imagem do produto</small>
                            </div>

                            <div class="form-group">
                                <div class="checkbox-group">
                                    <input type="checkbox" name="destaque" value="1" id="destaque"
                                           <?= isset($_POST['destaque']) ? 'checked' : '' ?>>
                                    <label for="destaque">Produto em Destaque</label>
                                </div>
                                <small>Produtos em destaque aparecem na página inicial</small>
                            </div>

                            <!-- Preview da Imagem -->
                            <div class="preview-section" id="imagePreview" style="display: none;">
                                <h4>Preview da Imagem:</h4>
                                <div class="preview-card">
                                    <img id="previewImg" src="" alt="Preview" style="max-width: 200px; height: auto; border-radius: 4px;">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Ações -->
                    <div class="form-actions">
                        <a href="admin-produtos.php" class="btn-outline">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                        <button type="submit" class="btn-primary">
                            <i class="fas fa-save"></i> Salvar Produto
                        </button>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script>
        // Preview da imagem
        document.querySelector('input[name="imagem_principal"]').addEventListener('input', function() {
            const url = this.value;
            const preview = document.getElementById('imagePreview');
            const img = document.getElementById('previewImg');
            
            if (url && url.startsWith('http')) {
                img.src = url;
                img.onload = function() {
                    preview.style.display = 'block';
                };
                img.onerror = function() {
                    preview.style.display = 'none';
                };
            } else {
                preview.style.display = 'none';
            }
        });

        // Validação do preço promocional
        document.querySelector('input[name="preco_promocional"]').addEventListener('input', function() {
            const precoRegular = parseFloat(document.querySelector('input[name="preco"]').value) || 0;
            const precoPromocional = parseFloat(this.value) || 0;
            
            if (precoPromocional > 0 && precoPromocional >= precoRegular) {
                this.setCustomValidity('O preço promocional deve ser menor que o preço regular');
            } else {
                this.setCustomValidity('');
            }
        });

        // Validação do formulário antes de enviar
        document.getElementById('productForm').addEventListener('submit', function(e) {
            const nome = document.querySelector('input[name="nome"]').value.trim();
            const preco = parseFloat(document.querySelector('input[name="preco"]').value);
            const categoria = document.querySelector('select[name="categoria_id"]').value;
            
            if (!nome || nome.length < 3) {
                alert('Nome do produto deve ter pelo menos 3 caracteres');
                e.preventDefault();
                return;
            }
            
            if (!preco || preco <= 0) {
                alert('Preço deve ser maior que zero');
                e.preventDefault();
                return;
            }
            
            if (!categoria) {
                alert('Selecione uma categoria');
                e.preventDefault();
                return;
            }
            
            // Confirmação antes de salvar
            if (!confirm('Deseja salvar este produto?')) {
                e.preventDefault();
            }
        });

        // Auto-focus no primeiro campo
        document.querySelector('input[name="nome"]').focus();
        
        // Feedback visual para campos obrigatórios
        document.querySelectorAll('input[required], select[required]').forEach(field => {
            field.addEventListener('blur', function() {
                if (!this.value) {
                    this.style.borderColor = '#e74c3c';
                } else {
                    this.style.borderColor = '#27ae60';
                }
            });
        });
    </script>
</body>
</html>

<?php
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: admin-dashboard.php');
    exit;
}
?>