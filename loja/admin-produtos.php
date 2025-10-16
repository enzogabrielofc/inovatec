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

// Processar ações
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create':
                try {
                    $product->nome = $_POST['nome'];
                    $product->descricao = $_POST['descricao'];
                    $product->especificacoes = $_POST['especificacoes'];
                    $product->preco = $_POST['preco'];
                    $product->preco_promocional = !empty($_POST['preco_promocional']) ? $_POST['preco_promocional'] : null;
                    $product->categoria_id = $_POST['categoria_id'];
                    $product->estoque = $_POST['estoque'] ?? 0;
                    $product->estoque_minimo = $_POST['estoque_minimo'] ?? 5;
                    $product->marca = $_POST['marca'] ?? null;
                    $product->modelo = $_POST['modelo'] ?? null;
                    $product->destaque = isset($_POST['destaque']) ? 1 : 0;
                    
                    if ($product->create()) {
                        $message = 'Produto criado com sucesso!';
                        $messageType = 'success';
                    } else {
                        $message = 'Erro ao criar produto.';
                        $messageType = 'error';
                    }
                } catch (Exception $e) {
                    $message = 'Erro: ' . $e->getMessage();
                    $messageType = 'error';
                }
                break;
                
            case 'update':
                try {
                    $product->id = $_POST['id'];
                    $product->nome = $_POST['nome'];
                    $product->descricao = $_POST['descricao'];
                    $product->especificacoes = $_POST['especificacoes'];
                    $product->preco = $_POST['preco'];
                    $product->preco_promocional = !empty($_POST['preco_promocional']) ? $_POST['preco_promocional'] : null;
                    $product->categoria_id = $_POST['categoria_id'];
                    $product->estoque = $_POST['estoque'] ?? 0;
                    $product->estoque_minimo = $_POST['estoque_minimo'] ?? 5;
                    $product->marca = $_POST['marca'] ?? null;
                    $product->modelo = $_POST['modelo'] ?? null;
                    $product->destaque = isset($_POST['destaque']) ? 1 : 0;
                    $product->ativo = isset($_POST['ativo']) ? 1 : 0;
                    
                    if ($product->update()) {
                        $message = 'Produto atualizado com sucesso!';
                        $messageType = 'success';
                    } else {
                        $message = 'Erro ao atualizar produto.';
                        $messageType = 'error';
                    }
                } catch (Exception $e) {
                    $message = 'Erro: ' . $e->getMessage();
                    $messageType = 'error';
                }
                break;
        }
    }
}

// Deletar produto
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if ($product->delete($id)) {
        $message = 'Produto removido com sucesso!';
        $messageType = 'success';
    } else {
        $message = 'Erro ao remover produto.';
        $messageType = 'error';
    }
}

// Buscar dados
$editProduct = null;
if (isset($_GET['edit'])) {
    $editProduct = $product->readOne((int)$_GET['edit']);
}

// Filtros
$filters = [];
if (!empty($_GET['categoria'])) {
    $filters['categoria_id'] = $_GET['categoria'];
}
if (!empty($_GET['busca'])) {
    $filters['busca'] = $_GET['busca'];
}
$filters['order'] = $_GET['order'] ?? 'nome';
$filters['dir'] = $_GET['dir'] ?? 'ASC';

// Buscar produtos e categorias
$produtos = $product->readAll($filters);

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
    <title>Gerenciar Produtos - InovaTech Store</title>
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
        
        .admin-header { margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center; }
        .admin-header h1 { font-size: 2rem; color: #2c3e50; }
        .btn-primary { padding: 0.75rem 1.5rem; background: #3498db; color: white; text-decoration: none; border-radius: 8px; border: none; font-size: 1rem; cursor: pointer; }
        .btn-primary:hover { background: #2980b9; }
        
        .message { padding: 1rem; border-radius: 8px; margin-bottom: 1rem; }
        .message.success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .message.error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        
        .filters { background: white; padding: 1.5rem; border-radius: 8px; margin-bottom: 1.5rem; display: flex; gap: 1rem; flex-wrap: wrap; align-items: center; }
        .filters input, .filters select { padding: 0.5rem; border: 2px solid #ddd; border-radius: 4px; }
        .filters .btn { padding: 0.5rem 1rem; background: #3498db; color: white; border: none; border-radius: 4px; cursor: pointer; }
        
        .product-grid { background: white; border-radius: 8px; overflow: hidden; }
        .product-table { width: 100%; border-collapse: collapse; }
        .product-table th, .product-table td { padding: 1rem; text-align: left; border-bottom: 1px solid #ecf0f1; }
        .product-table th { background: #f8f9fa; font-weight: 600; color: #2c3e50; }
        .product-table tr:hover { background: #f8f9fa; }
        
        .product-image { width: 60px; height: 60px; object-fit: cover; border-radius: 8px; }
        .price { font-weight: 600; color: #27ae60; }
        .stock-low { color: #e74c3c; font-weight: 600; }
        .stock-ok { color: #27ae60; }
        
        .actions { display: flex; gap: 0.5rem; }
        .btn-sm { padding: 0.25rem 0.5rem; text-decoration: none; border-radius: 4px; font-size: 0.8rem; }
        .btn-edit { background: #f39c12; color: white; }
        .btn-delete { background: #e74c3c; color: white; }
        
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; }
        .modal.show { display: flex; align-items: center; justify-content: center; }
        .modal-content { background: white; padding: 2rem; border-radius: 8px; width: 90%; max-width: 600px; max-height: 90vh; overflow-y: auto; }
        .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; }
        .modal-close { background: none; border: none; font-size: 1.5rem; cursor: pointer; }
        
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
        .form-group { margin-bottom: 1rem; }
        .form-group.full { grid-column: 1 / -1; }
        .form-group label { display: block; margin-bottom: 0.5rem; font-weight: 500; color: #555; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 0.75rem; border: 2px solid #ddd; border-radius: 4px; font-size: 1rem; }
        .form-group textarea { resize: vertical; min-height: 100px; }
        .checkbox-group { display: flex; align-items: center; gap: 0.5rem; }
        
        .form-actions { display: flex; gap: 1rem; justify-content: flex-end; margin-top: 2rem; }
        .btn-secondary { padding: 0.75rem 1.5rem; background: #95a5a6; color: white; border: none; border-radius: 4px; cursor: pointer; }
        
        @media (max-width: 768px) {
            .admin-layout { flex-direction: column; }
            .sidebar { width: 100%; }
            .admin-header { flex-direction: column; align-items: stretch; gap: 1rem; }
            .filters { flex-direction: column; }
            .form-grid { grid-template-columns: 1fr; }
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
                <a href="admin-produtos.php" class="nav-link active">
                    <i class="fas fa-box"></i> Produtos
                </a>
                <a href="admin-adicionar-produto.php" class="nav-link">
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
                <div>
                    <h1>Gerenciar Produtos</h1>
                    <p>Adicionar, editar e remover produtos da loja</p>
                </div>
                <button class="btn-primary" onclick="showModal()">
                    <i class="fas fa-plus"></i> Novo Produto
                </button>
            </header>

            <?php if ($message): ?>
                <div class="message <?= $messageType ?>">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <!-- Filtros -->
            <div class="filters">
                <form method="GET" style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
                    <input type="text" name="busca" placeholder="Buscar produto..." value="<?= htmlspecialchars($_GET['busca'] ?? '') ?>">
                    
                    <select name="categoria">
                        <option value="">Todas categorias</option>
                        <?php foreach ($categorias as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= ($_GET['categoria'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    
                    <select name="order">
                        <option value="nome" <?= ($_GET['order'] ?? '') == 'nome' ? 'selected' : '' ?>>Nome</option>
                        <option value="preco" <?= ($_GET['order'] ?? '') == 'preco' ? 'selected' : '' ?>>Preço</option>
                        <option value="estoque" <?= ($_GET['order'] ?? '') == 'estoque' ? 'selected' : '' ?>>Estoque</option>
                        <option value="data_criacao DESC" <?= ($_GET['order'] ?? '') == 'data_criacao DESC' ? 'selected' : '' ?>>Mais novos</option>
                    </select>
                    
                    <button type="submit" class="btn">Filtrar</button>
                    <?php if (!empty($_GET['busca']) || !empty($_GET['categoria'])): ?>
                        <a href="admin-produtos.php" class="btn" style="background: #95a5a6;">Limpar</a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Lista de Produtos -->
            <div class="product-grid">
                <table class="product-table">
                    <thead>
                        <tr>
                            <th>Produto</th>
                            <th>Categoria</th>
                            <th>Preço</th>
                            <th>Estoque</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($produtos)): ?>
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 3rem;">
                                    <i class="fas fa-box fa-3x" style="color: #bdc3c7; margin-bottom: 1rem;"></i>
                                    <br>Nenhum produto encontrado
                                    <br><button class="btn-primary" onclick="showModal()" style="margin-top: 1rem;">
                                        <i class="fas fa-plus"></i> Adicionar Primeiro Produto
                                    </button>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($produtos as $prod): ?>
                                <tr>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 1rem;">
                                            <?php if (!empty($prod['imagem_principal'])): ?>
                                                <img src="<?= htmlspecialchars($prod['imagem_principal']) ?>" alt="<?= htmlspecialchars($prod['nome']) ?>" class="product-image">
                                            <?php else: ?>
                                                <div style="width: 60px; height: 60px; background: #f8f9fa; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                                    <i class="fas fa-image" style="color: #bdc3c7;"></i>
                                                </div>
                                            <?php endif; ?>
                                            <div>
                                                <strong><?= htmlspecialchars($prod['nome']) ?></strong>
                                                <?php if (!empty($prod['marca'])): ?>
                                                    <br><small><?= htmlspecialchars($prod['marca']) ?></small>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?= htmlspecialchars($prod['categoria_nome'] ?? 'N/A') ?></td>
                                    <td>
                                        <?php if (!empty($prod['preco_promocional'])): ?>
                                            <span style="text-decoration: line-through; color: #999;">R$ <?= formatPrice($prod['preco']) ?></span>
                                            <br><span class="price">R$ <?= formatPrice($prod['preco_promocional']) ?></span>
                                        <?php else: ?>
                                            <span class="price">R$ <?= formatPrice($prod['preco']) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="<?= $prod['estoque'] <= ($prod['estoque_minimo'] ?? 5) ? 'stock-low' : 'stock-ok' ?>">
                                            <?= $prod['estoque'] ?> un.
                                            <?php if ($prod['estoque'] <= ($prod['estoque_minimo'] ?? 5)): ?>
                                                <i class="fas fa-exclamation-triangle"></i>
                                            <?php endif; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($prod['ativo']): ?>
                                            <span style="color: #27ae60;"><i class="fas fa-check-circle"></i> Ativo</span>
                                        <?php else: ?>
                                            <span style="color: #e74c3c;"><i class="fas fa-times-circle"></i> Inativo</span>
                                        <?php endif; ?>
                                        <?php if ($prod['destaque']): ?>
                                            <br><span style="color: #f39c12;"><i class="fas fa-star"></i> Destaque</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="actions">
                                            <a href="?edit=<?= $prod['id'] ?>" class="btn-sm btn-edit" onclick="showModal(<?= htmlspecialchars(json_encode($prod)) ?>)">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="?delete=<?= $prod['id'] ?>" class="btn-sm btn-delete" 
                                               onclick="return confirm('Tem certeza que deseja remover este produto?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <!-- Modal de Formulário -->
    <div id="productModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle">Novo Produto</h2>
                <button class="modal-close" onclick="hideModal()">&times;</button>
            </div>
            
            <form id="productForm" method="POST">
                <input type="hidden" name="action" value="create" id="formAction">
                <input type="hidden" name="id" id="productId">
                
                <div class="form-grid">
                    <div class="form-group">
                        <label>Nome *</label>
                        <input type="text" name="nome" id="nome" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Categoria *</label>
                        <select name="categoria_id" id="categoria_id" required>
                            <option value="">Selecione uma categoria</option>
                            <?php foreach ($categorias as $cat): ?>
                                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['nome']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Preço *</label>
                        <input type="number" name="preco" id="preco" step="0.01" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Preço Promocional</label>
                        <input type="number" name="preco_promocional" id="preco_promocional" step="0.01">
                    </div>
                    
                    <div class="form-group">
                        <label>Estoque</label>
                        <input type="number" name="estoque" id="estoque" value="0">
                    </div>
                    
                    <div class="form-group">
                        <label>Estoque Mínimo</label>
                        <input type="number" name="estoque_minimo" id="estoque_minimo" value="5">
                    </div>
                    
                    <div class="form-group">
                        <label>Marca</label>
                        <input type="text" name="marca" id="marca">
                    </div>
                    
                    <div class="form-group">
                        <label>Modelo</label>
                        <input type="text" name="modelo" id="modelo">
                    </div>
                    
                    <div class="form-group full">
                        <label>Descrição</label>
                        <textarea name="descricao" id="descricao" rows="3"></textarea>
                    </div>
                    
                    <div class="form-group full">
                        <label>Especificações</label>
                        <textarea name="especificacoes" id="especificacoes" rows="4" 
                                  placeholder="Digite uma especificação por linha, ex:
• Processador Intel Core i5
• 8GB RAM DDR4
• SSD 256GB"></textarea>
                    </div>
                    
                    <div class="form-group full">
                        <div style="display: flex; gap: 2rem;">
                            <div class="checkbox-group">
                                <input type="checkbox" name="destaque" id="destaque" value="1">
                                <label for="destaque">Produto em destaque</label>
                            </div>
                            <div class="checkbox-group" id="ativoGroup" style="display: none;">
                                <input type="checkbox" name="ativo" id="ativo" value="1" checked>
                                <label for="ativo">Produto ativo</label>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn-secondary" onclick="hideModal()">Cancelar</button>
                    <button type="submit" class="btn-primary" id="submitBtn">Salvar Produto</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function showModal(product = null) {
            const modal = document.getElementById('productModal');
            const form = document.getElementById('productForm');
            const title = document.getElementById('modalTitle');
            const submitBtn = document.getElementById('submitBtn');
            const formAction = document.getElementById('formAction');
            const ativoGroup = document.getElementById('ativoGroup');
            
            if (product) {
                // Editar produto
                title.textContent = 'Editar Produto';
                submitBtn.textContent = 'Atualizar Produto';
                formAction.value = 'update';
                ativoGroup.style.display = 'block';
                
                // Preencher campos
                document.getElementById('productId').value = product.id;
                document.getElementById('nome').value = product.nome || '';
                document.getElementById('categoria_id').value = product.categoria_id || '';
                document.getElementById('preco').value = product.preco || '';
                document.getElementById('preco_promocional').value = product.preco_promocional || '';
                document.getElementById('estoque').value = product.estoque || 0;
                document.getElementById('estoque_minimo').value = product.estoque_minimo || 5;
                document.getElementById('marca').value = product.marca || '';
                document.getElementById('modelo').value = product.modelo || '';
                document.getElementById('descricao').value = product.descricao || '';
                document.getElementById('especificacoes').value = product.especificacoes || '';
                document.getElementById('destaque').checked = !!product.destaque;
                document.getElementById('ativo').checked = !!product.ativo;
            } else {
                // Novo produto
                title.textContent = 'Novo Produto';
                submitBtn.textContent = 'Criar Produto';
                formAction.value = 'create';
                ativoGroup.style.display = 'none';
                form.reset();
                document.getElementById('estoque').value = 0;
                document.getElementById('estoque_minimo').value = 5;
            }
            
            modal.classList.add('show');
        }
        
        function hideModal() {
            document.getElementById('productModal').classList.remove('show');
        }
        
        // Fechar modal clicando fora
        document.getElementById('productModal').addEventListener('click', function(e) {
            if (e.target === this) {
                hideModal();
            }
        });
        
        // Mostrar modal de edição se há produto para editar
        <?php if ($editProduct): ?>
            showModal(<?= json_encode($editProduct) ?>);
        <?php endif; ?>
        
        <?php if (isset($_GET['add'])): ?>
            showModal();
        <?php endif; ?>
    </script>
</body>
</html>