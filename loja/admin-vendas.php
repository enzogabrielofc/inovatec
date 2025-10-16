<?php
session_start();

// Verificar se é admin
if (!isset($_SESSION['admin_logged'])) {
    header('Location: admin-dashboard.php');
    exit;
}

require_once 'classes/Product.php';
require_once 'classes/Order.php';

$product = new Product();
$order = new Order();
$message = '';
$messageType = '';

// Processar venda
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['finalizar_venda'])) {
    try {
        $dadosCliente = [
            'nome_cliente' => $_POST['nome_cliente'],
            'email_cliente' => $_POST['email_cliente'],
            'telefone_cliente' => $_POST['telefone_cliente'] ?? null,
            'endereco_entrega' => $_POST['endereco_entrega'],
            'subtotal' => $_POST['subtotal'],
            'desconto' => $_POST['desconto'] ?? 0,
            'frete' => $_POST['frete'] ?? 0,
            'total' => $_POST['total'],
            'forma_pagamento' => $_POST['forma_pagamento'],
            'observacoes' => $_POST['observacoes'] ?? null,
            'status_pedido' => 'confirmado' // Vendas presenciais já confirmadas
        ];
        
        $itens = json_decode($_POST['itens'], true);
        if (!$itens) {
            throw new Exception('Nenhum item no carrinho');
        }
        
        $pedidoId = $order->create($dadosCliente, $itens);
        
        if ($pedidoId) {
            $message = 'Venda realizada com sucesso! Pedido #' . $pedidoId;
            $messageType = 'success';
            
            // Limpar carrinho (via JavaScript)
            echo "<script>localStorage.removeItem('vendas_cart');</script>";
        } else {
            $message = 'Erro ao processar venda';
            $messageType = 'error';
        }
    } catch (Exception $e) {
        $message = 'Erro: ' . $e->getMessage();
        $messageType = 'error';
    }
}

// Buscar produtos para venda
$produtosVenda = $product->readAll(['order' => 'nome', 'dir' => 'ASC']);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nova Venda - InovaTech Store</title>
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
        
        .message { padding: 1rem; border-radius: 8px; margin-bottom: 1rem; }
        .message.success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .message.error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        
        .venda-layout { display: grid; grid-template-columns: 1fr 400px; gap: 2rem; }
        
        .produtos-section { background: white; border-radius: 8px; padding: 1.5rem; }
        .produtos-search { margin-bottom: 1.5rem; }
        .produtos-search input { width: 100%; padding: 1rem; border: 2px solid #ddd; border-radius: 8px; font-size: 1rem; }
        
        .produtos-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 1rem; max-height: 600px; overflow-y: auto; }
        
        .produto-card { border: 2px solid #ecf0f1; border-radius: 8px; padding: 1rem; cursor: pointer; transition: all 0.3s; }
        .produto-card:hover { border-color: #3498db; background: #f8f9fa; }
        .produto-card.stock-low { opacity: 0.5; }
        .produto-card.stock-low:hover { cursor: not-allowed; background: white; border-color: #ecf0f1; }
        
        .produto-info h4 { margin-bottom: 0.5rem; color: #2c3e50; }
        .produto-info .price { font-weight: 600; color: #27ae60; margin-bottom: 0.5rem; }
        .produto-info .stock { font-size: 0.9rem; color: #7f8c8d; }
        .produto-info .stock.low { color: #e74c3c; }
        
        .carrinho-section { background: white; border-radius: 8px; padding: 1.5rem; }
        .carrinho-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; border-bottom: 2px solid #ecf0f1; padding-bottom: 1rem; }
        .carrinho-header h3 { color: #2c3e50; }
        .carrinho-count { background: #3498db; color: white; padding: 0.25rem 0.5rem; border-radius: 50%; font-size: 0.9rem; }
        
        .carrinho-items { margin-bottom: 1rem; max-height: 300px; overflow-y: auto; }
        .carrinho-item { display: flex; justify-content: space-between; align-items: center; padding: 1rem; border-bottom: 1px solid #ecf0f1; }
        .carrinho-item:last-child { border-bottom: none; }
        
        .item-info h5 { margin-bottom: 0.25rem; }
        .item-info .item-price { color: #7f8c8d; font-size: 0.9rem; }
        
        .item-controls { display: flex; align-items: center; gap: 0.5rem; }
        .item-controls button { width: 30px; height: 30px; border: none; border-radius: 4px; cursor: pointer; display: flex; align-items: center; justify-content: center; }
        .btn-decrease { background: #e74c3c; color: white; }
        .btn-increase { background: #27ae60; color: white; }
        .btn-remove { background: #95a5a6; color: white; }
        .item-quantity { min-width: 40px; text-align: center; padding: 0.25rem; border: 1px solid #ddd; border-radius: 4px; }
        
        .carrinho-totals { border-top: 2px solid #ecf0f1; padding-top: 1rem; }
        .total-line { display: flex; justify-content: space-between; margin-bottom: 0.5rem; }
        .total-line.final { font-weight: 600; font-size: 1.1rem; color: #2c3e50; border-top: 1px solid #ecf0f1; padding-top: 0.5rem; margin-top: 0.5rem; }
        
        .desconto-controls { display: flex; gap: 0.5rem; margin: 1rem 0; }
        .desconto-controls input { flex: 1; padding: 0.5rem; border: 2px solid #ddd; border-radius: 4px; }
        .desconto-controls select { padding: 0.5rem; border: 2px solid #ddd; border-radius: 4px; }
        .desconto-controls button { padding: 0.5rem 1rem; background: #f39c12; color: white; border: none; border-radius: 4px; cursor: pointer; }
        
        .finalizar-section { margin-top: 1rem; }
        .btn-finalizar { width: 100%; padding: 1rem; background: #27ae60; color: white; border: none; border-radius: 8px; font-size: 1.1rem; font-weight: 600; cursor: pointer; }
        .btn-finalizar:hover { background: #229954; }
        .btn-finalizar:disabled { background: #95a5a6; cursor: not-allowed; }
        
        .btn-limpar { width: 100%; padding: 0.75rem; background: #e74c3c; color: white; border: none; border-radius: 4px; margin-top: 0.5rem; cursor: pointer; }
        
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; }
        .modal.show { display: flex; align-items: center; justify-content: center; }
        .modal-content { background: white; padding: 2rem; border-radius: 8px; width: 90%; max-width: 500px; }
        .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; }
        .modal-close { background: none; border: none; font-size: 1.5rem; cursor: pointer; }
        
        .form-group { margin-bottom: 1rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; font-weight: 500; color: #555; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 0.75rem; border: 2px solid #ddd; border-radius: 4px; }
        
        .form-actions { display: flex; gap: 1rem; justify-content: flex-end; margin-top: 1.5rem; }
        .btn-secondary { padding: 0.75rem 1.5rem; background: #95a5a6; color: white; border: none; border-radius: 4px; cursor: pointer; }
        .btn-primary { padding: 0.75rem 1.5rem; background: #3498db; color: white; border: none; border-radius: 4px; cursor: pointer; }
        
        @media (max-width: 1024px) {
            .admin-layout { flex-direction: column; }
            .sidebar { width: 100%; }
            .venda-layout { grid-template-columns: 1fr; }
            .produtos-grid { grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); }
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
                <a href="admin-adicionar-produto.php" class="nav-link">
                    <i class="fas fa-plus"></i> Adicionar Produto
                </a>
                <a href="admin-pedidos.php" class="nav-link">
                    <i class="fas fa-shopping-cart"></i> Pedidos
                </a>
                <a href="admin-vendas.php" class="nav-link active">
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
                <h1><i class="fas fa-cash-register"></i> Nova Venda</h1>
                <p>Sistema de Ponto de Venda (PDV)</p>
            </header>

            <?php if ($message): ?>
                <div class="message <?= $messageType ?>">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <div class="venda-layout">
                <!-- Seção de Produtos -->
                <div class="produtos-section">
                    <h3><i class="fas fa-box"></i> Produtos Disponíveis</h3>
                    
                    <div class="produtos-search">
                        <input type="text" id="searchProducts" placeholder="Buscar produto...">
                    </div>
                    
                    <div class="produtos-grid" id="produtosGrid">
                        <?php foreach ($produtosVenda as $prod): ?>
                            <?php if ($prod['ativo'] && $prod['estoque'] > 0): ?>
                                <div class="produto-card" onclick="adicionarAoCarrinho(<?= htmlspecialchars(json_encode($prod)) ?>)">
                                    <div class="produto-info">
                                        <h4><?= htmlspecialchars($prod['nome']) ?></h4>
                                        <div class="price">R$ <?= formatPrice($prod['preco_promocional'] ?? $prod['preco']) ?></div>
                                        <div class="stock <?= $prod['estoque'] <= ($prod['estoque_minimo'] ?? 5) ? 'low' : '' ?>">
                                            Estoque: <?= $prod['estoque'] ?> un.
                                        </div>
                                        <?php if (!empty($prod['marca'])): ?>
                                            <small><?= htmlspecialchars($prod['marca']) ?></small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php elseif ($prod['ativo']): ?>
                                <div class="produto-card stock-low">
                                    <div class="produto-info">
                                        <h4><?= htmlspecialchars($prod['nome']) ?></h4>
                                        <div class="price">R$ <?= formatPrice($prod['preco_promocional'] ?? $prod['preco']) ?></div>
                                        <div class="stock low">Esgotado</div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- Seção do Carrinho -->
                <div class="carrinho-section">
                    <div class="carrinho-header">
                        <h3><i class="fas fa-shopping-cart"></i> Carrinho</h3>
                        <span class="carrinho-count" id="carrinhoCount">0</span>
                    </div>
                    
                    <div class="carrinho-items" id="carrinhoItems">
                        <div style="text-align: center; color: #bdc3c7; padding: 2rem;">
                            <i class="fas fa-shopping-cart fa-2x"></i>
                            <p>Carrinho vazio</p>
                            <small>Clique nos produtos para adicionar</small>
                        </div>
                    </div>
                    
                    <div class="desconto-controls">
                        <select id="descontoTipo">
                            <option value="valor">R$ Desconto</option>
                            <option value="percentual">% Desconto</option>
                        </select>
                        <input type="number" id="descontoValor" placeholder="0" step="0.01" min="0">
                        <button onclick="aplicarDesconto()">Aplicar</button>
                    </div>
                    
                    <div class="carrinho-totals">
                        <div class="total-line">
                            <span>Subtotal:</span>
                            <span id="subtotalValue">R$ 0,00</span>
                        </div>
                        <div class="total-line">
                            <span>Desconto:</span>
                            <span id="descontoValue">R$ 0,00</span>
                        </div>
                        <div class="total-line final">
                            <span>Total:</span>
                            <span id="totalValue">R$ 0,00</span>
                        </div>
                    </div>
                    
                    <div class="finalizar-section">
                        <button class="btn-finalizar" id="btnFinalizar" onclick="abrirModalVenda()" disabled>
                            <i class="fas fa-check"></i> Finalizar Venda
                        </button>
                        <button class="btn-limpar" onclick="limparCarrinho()">
                            <i class="fas fa-trash"></i> Limpar Carrinho
                        </button>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal de Finalização -->
    <div id="vendaModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-cash-register"></i> Finalizar Venda</h2>
                <button class="modal-close" onclick="fecharModalVenda()">&times;</button>
            </div>
            
            <form method="POST" id="vendaForm">
                <input type="hidden" name="finalizar_venda" value="1">
                <input type="hidden" name="subtotal" id="formSubtotal">
                <input type="hidden" name="desconto" id="formDesconto">
                <input type="hidden" name="total" id="formTotal">
                <input type="hidden" name="itens" id="formItens">
                
                <div class="form-group">
                    <label>Nome do Cliente *</label>
                    <input type="text" name="nome_cliente" required>
                </div>
                
                <div class="form-group">
                    <label>Email do Cliente *</label>
                    <input type="email" name="email_cliente" required>
                </div>
                
                <div class="form-group">
                    <label>Telefone</label>
                    <input type="tel" name="telefone_cliente">
                </div>
                
                <div class="form-group">
                    <label>Endereço de Entrega *</label>
                    <textarea name="endereco_entrega" rows="3" required></textarea>
                </div>
                
                <div class="form-group">
                    <label>Forma de Pagamento *</label>
                    <select name="forma_pagamento" required>
                        <option value="">Selecione...</option>
                        <option value="Dinheiro">Dinheiro</option>
                        <option value="Cartão de Crédito">Cartão de Crédito</option>
                        <option value="Cartão de Débito">Cartão de Débito</option>
                        <option value="PIX">PIX</option>
                        <option value="Boleto">Boleto</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Observações</label>
                    <textarea name="observacoes" rows="2"></textarea>
                </div>
                
                <div class="total-line final" style="margin: 1rem 0; padding: 1rem; background: #f8f9fa; border-radius: 4px;">
                    <span>Total da Venda:</span>
                    <span id="modalTotal">R$ 0,00</span>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn-secondary" onclick="fecharModalVenda()">Cancelar</button>
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-check"></i> Confirmar Venda
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let carrinho = JSON.parse(localStorage.getItem('vendas_cart') || '[]');
        let subtotal = 0;
        let desconto = 0;
        let total = 0;

        // Atualizar exibição do carrinho
        function atualizarCarrinho() {
            const carrinhoItems = document.getElementById('carrinhoItems');
            const carrinhoCount = document.getElementById('carrinhoCount');
            const btnFinalizar = document.getElementById('btnFinalizar');
            
            if (carrinho.length === 0) {
                carrinhoItems.innerHTML = `
                    <div style="text-align: center; color: #bdc3c7; padding: 2rem;">
                        <i class="fas fa-shopping-cart fa-2x"></i>
                        <p>Carrinho vazio</p>
                        <small>Clique nos produtos para adicionar</small>
                    </div>
                `;
                carrinhoCount.textContent = '0';
                btnFinalizar.disabled = true;
            } else {
                let html = '';
                carrinho.forEach((item, index) => {
                    html += `
                        <div class="carrinho-item">
                            <div class="item-info">
                                <h5>${item.nome}</h5>
                                <div class="item-price">R$ ${formatPrice(item.preco_unitario)} cada</div>
                            </div>
                            <div class="item-controls">
                                <button class="btn-decrease" onclick="alterarQuantidade(${index}, -1)">-</button>
                                <input type="number" class="item-quantity" value="${item.quantidade}" onchange="definirQuantidade(${index}, this.value)" min="1">
                                <button class="btn-increase" onclick="alterarQuantidade(${index}, 1)">+</button>
                                <button class="btn-remove" onclick="removerItem(${index})"><i class="fas fa-trash"></i></button>
                            </div>
                        </div>
                    `;
                });
                carrinhoItems.innerHTML = html;
                carrinhoCount.textContent = carrinho.reduce((sum, item) => sum + item.quantidade, 0);
                btnFinalizar.disabled = false;
            }
            
            calcularTotais();
            salvarCarrinho();
        }

        // Calcular totais
        function calcularTotais() {
            subtotal = carrinho.reduce((sum, item) => sum + (item.preco_unitario * item.quantidade), 0);
            total = subtotal - desconto;
            
            document.getElementById('subtotalValue').textContent = 'R$ ' + formatPrice(subtotal);
            document.getElementById('descontoValue').textContent = 'R$ ' + formatPrice(desconto);
            document.getElementById('totalValue').textContent = 'R$ ' + formatPrice(total);
        }

        // Adicionar produto ao carrinho
        function adicionarAoCarrinho(produto) {
            const existingIndex = carrinho.findIndex(item => item.produto_id === produto.id);
            
            if (existingIndex >= 0) {
                // Se já existe, aumenta quantidade
                if (carrinho[existingIndex].quantidade < produto.estoque) {
                    carrinho[existingIndex].quantidade++;
                } else {
                    alert('Quantidade em estoque insuficiente!');
                    return;
                }
            } else {
                // Adiciona novo item
                carrinho.push({
                    produto_id: produto.id,
                    nome_produto: produto.nome,
                    nome: produto.nome,
                    preco_unitario: produto.preco_promocional || produto.preco,
                    quantidade: 1,
                    estoque_disponivel: produto.estoque
                });
            }
            
            atualizarCarrinho();
        }

        // Alterar quantidade
        function alterarQuantidade(index, delta) {
            const novaQuantidade = carrinho[index].quantidade + delta;
            
            if (novaQuantidade <= 0) {
                removerItem(index);
            } else if (novaQuantidade <= carrinho[index].estoque_disponivel) {
                carrinho[index].quantidade = novaQuantidade;
                atualizarCarrinho();
            } else {
                alert('Quantidade em estoque insuficiente!');
            }
        }

        // Definir quantidade específica
        function definirQuantidade(index, quantidade) {
            quantidade = parseInt(quantidade);
            
            if (quantidade <= 0) {
                removerItem(index);
            } else if (quantidade <= carrinho[index].estoque_disponivel) {
                carrinho[index].quantidade = quantidade;
                atualizarCarrinho();
            } else {
                alert('Quantidade em estoque insuficiente!');
                atualizarCarrinho(); // Restaura valor anterior
            }
        }

        // Remover item
        function removerItem(index) {
            carrinho.splice(index, 1);
            atualizarCarrinho();
        }

        // Aplicar desconto
        function aplicarDesconto() {
            const tipo = document.getElementById('descontoTipo').value;
            const valor = parseFloat(document.getElementById('descontoValor').value) || 0;
            
            if (tipo === 'percentual') {
                desconto = subtotal * (valor / 100);
            } else {
                desconto = valor;
            }
            
            // Não permitir desconto maior que o subtotal
            if (desconto > subtotal) {
                desconto = subtotal;
            }
            
            calcularTotais();
        }

        // Limpar carrinho
        function limparCarrinho() {
            if (confirm('Tem certeza que deseja limpar o carrinho?')) {
                carrinho = [];
                desconto = 0;
                document.getElementById('descontoValor').value = '';
                atualizarCarrinho();
            }
        }

        // Salvar carrinho no localStorage
        function salvarCarrinho() {
            localStorage.setItem('vendas_cart', JSON.stringify(carrinho));
        }

        // Abrir modal de venda
        function abrirModalVenda() {
            document.getElementById('formSubtotal').value = subtotal;
            document.getElementById('formDesconto').value = desconto;
            document.getElementById('formTotal').value = total;
            document.getElementById('modalTotal').textContent = 'R$ ' + formatPrice(total);
            
            // Preparar itens para envio
            const itensVenda = carrinho.map(item => ({
                produto_id: item.produto_id,
                nome_produto: item.nome_produto,
                preco_unitario: item.preco_unitario,
                quantidade: item.quantidade,
                subtotal: item.preco_unitario * item.quantidade
            }));
            
            document.getElementById('formItens').value = JSON.stringify(itensVenda);
            document.getElementById('vendaModal').classList.add('show');
        }

        // Fechar modal de venda
        function fecharModalVenda() {
            document.getElementById('vendaModal').classList.remove('show');
        }

        // Busca de produtos
        document.getElementById('searchProducts').addEventListener('input', function() {
            const search = this.value.toLowerCase();
            const produtos = document.querySelectorAll('.produto-card');
            
            produtos.forEach(card => {
                const nome = card.querySelector('h4').textContent.toLowerCase();
                const marca = card.querySelector('small')?.textContent.toLowerCase() || '';
                
                if (nome.includes(search) || marca.includes(search)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });

        // Formatação de preço
        function formatPrice(value) {
            return parseFloat(value).toFixed(2).replace('.', ',');
        }

        // Inicializar carrinho ao carregar
        atualizarCarrinho();

        // Fechar modal clicando fora
        document.getElementById('vendaModal').addEventListener('click', function(e) {
            if (e.target === this) {
                fecharModalVenda();
            }
        });
    </script>
</body>
</html>