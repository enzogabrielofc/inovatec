<?php
/**
 * Página de Produtos - InovaTech Store
 */
require_once 'classes/Product.php';
require_once 'classes/Category.php';

try {
    $product = new Product();
    
    // Parâmetros de filtro da URL
    $categoria = isset($_GET['categoria']) ? (int)$_GET['categoria'] : null;
    $busca = isset($_GET['busca']) ? trim($_GET['busca']) : '';
    $ordem = isset($_GET['ordem']) ? $_GET['ordem'] : 'nome';
    $direcao = isset($_GET['dir']) ? $_GET['dir'] : 'ASC';
    $pagina = isset($_GET['pagina']) ? max(1, (int)$_GET['pagina']) : 1;
    $porPagina = 12;
    $offset = ($pagina - 1) * $porPagina;

    // Filtros para produtos
    $filtros = [
        'categoria_id' => $categoria,
        'busca' => $busca,
        'order' => $ordem,
        'dir' => $direcao,
        'limit' => $porPagina,
        'offset' => $offset
    ];

    // Buscar produtos
    $produtos = $product->readAll($filtros);
    $totalProdutos = $product->count($filtros);
    $totalPaginas = ceil($totalProdutos / $porPagina);

    // Buscar categorias para filtro
    $category = new Category();
    $categorias = $category->readAll();

} catch (Exception $e) {
    $produtos = [];
    $totalProdutos = 0;
    $totalPaginas = 0;
    $categorias = [];
}

// Função para gerar URL com parâmetros
function buildUrl($params = []) {
    $currentParams = $_GET;
    $newParams = array_merge($currentParams, $params);
    
    // Remover parâmetros vazios
    $newParams = array_filter($newParams, function($value) {
        return $value !== '' && $value !== null;
    });
    
    return '?' . http_build_query($newParams);
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produtos - InovaTech Store</title>
    <link rel="stylesheet" href="style-modern.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <header>
        <div class="container">
            <h1>🔧 InovaTech Store</h1>
            <p class="slogan">
                <?php if (!empty($busca)): ?>
                    Resultados para "<?= htmlspecialchars($busca) ?>"
                <?php elseif ($categoria): ?>
                    <?php 
                    $categoriaNome = '';
                    foreach ($categorias as $cat) {
                        if ($cat['id'] == $categoria) {
                            $categoriaNome = $cat['nome'];
                            break;
                        }
                    }
                    echo htmlspecialchars($categoriaNome);
                    ?>
                <?php else: ?>
                    Todos os produtos
                <?php endif; ?>
            </p>
        </div>
    </header>

    <nav>
        <div class="container">
            <ul class="nav-menu">
                <li><a href="index.php">Início</a></li>
                <li><a href="produtos.php?categoria=1">Computadores</a></li>
                <li><a href="produtos.php?categoria=2">Videogames</a></li>
                <li><a href="contato.php">Contato</a></li>
                <li><a href="#" class="cart-btn" onclick="toggleCart()">🛒 Carrinho (<span id="cart-count">0</span>)</a></li>
            </ul>
        </div>
    </nav>

    <main>
        <!-- Filtros e Busca -->
        <section class="filters-section">
            <div class="container">
                <form method="GET" class="filters-container">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" name="busca" placeholder="Buscar produtos..." value="<?= htmlspecialchars($busca) ?>">
                    </div>

                    <div class="filter-group">
                        <label>Categoria</label>
                        <select name="categoria" onchange="this.form.submit()">
                            <option value="">Todas as categorias</option>
                            <?php foreach ($categorias as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= $categoria == $cat['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['nome']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label>Ordenar por</label>
                        <select name="ordem" onchange="this.form.submit()">
                            <option value="nome" <?= $ordem == 'nome' ? 'selected' : '' ?>>Nome</option>
                            <option value="preco" <?= $ordem == 'preco' ? 'selected' : '' ?>>Menor preço</option>
                            <option value="preco DESC" <?= $ordem == 'preco DESC' ? 'selected' : '' ?>>Maior preço</option>
                            <option value="data_criacao DESC" <?= $ordem == 'data_criacao DESC' ? 'selected' : '' ?>>Mais novos</option>
                        </select>
                    </div>

                    <button type="submit" class="btn">
                        <i class="fas fa-filter"></i> Filtrar
                    </button>

                    <?php if (!empty($busca) || $categoria): ?>
                        <a href="produtos.php" class="btn btn-outline">
                            <i class="fas fa-times"></i> Limpar
                        </a>
                    <?php endif; ?>
                </form>
            </div>
        </section>

        <!-- Resultados -->
        <section class="products">
            <div class="container">
                <div class="results-info">
                    <p>
                        <?php if ($totalProdutos > 0): ?>
                            Exibindo <?= count($produtos) ?> de <?= $totalProdutos ?> produtos
                            <?php if ($totalPaginas > 1): ?>
                                (Página <?= $pagina ?> de <?= $totalPaginas ?>)
                            <?php endif; ?>
                        <?php endif; ?>
                    </p>
                </div>

                <div class="product-grid">
                    <?php if (empty($produtos)): ?>
                        <div class="no-products">
                            <i class="fas fa-search fa-3x"></i>
                            <h3>Nenhum produto encontrado</h3>
                            <p>Tente ajustar os filtros ou fazer uma nova busca.</p>
                            <a href="produtos.php" class="btn">Ver todos os produtos</a>
                        </div>
                    <?php else: ?>
                        <?php foreach ($produtos as $produto): ?>
                            <div class="product-card">
                                <?php if (!empty($produto['imagem_principal'])): ?>
                                    <img src="<?= htmlspecialchars($produto['imagem_principal']) ?>" alt="<?= htmlspecialchars($produto['nome']) ?>">
                                <?php else: ?>
                                    <img src="https://via.placeholder.com/280x200/007ACC/ffffff?text=<?= urlencode($produto['nome']) ?>" alt="<?= htmlspecialchars($produto['nome']) ?>">
                                <?php endif; ?>
                                
                                <div class="product-meta">
                                    <?php if (!empty($produto['marca'])): ?>
                                        <span class="brand-tag"><?= htmlspecialchars($produto['marca']) ?></span>
                                    <?php endif; ?>
                                    
                                    <div class="stock-indicator">
                                        <?php if ($produto['estoque'] > $produto['estoque_minimo']): ?>
                                            <i class="fas fa-check-circle stock-high"></i>
                                            <span class="stock-high">Em estoque</span>
                                        <?php elseif ($produto['estoque'] > 0): ?>
                                            <i class="fas fa-exclamation-triangle stock-medium"></i>
                                            <span class="stock-medium">Poucas unidades</span>
                                        <?php else: ?>
                                            <i class="fas fa-times-circle stock-low"></i>
                                            <span class="stock-low">Esgotado</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <h3><?= htmlspecialchars($produto['nome']) ?></h3>
                                
                                <?php if (!empty($produto['descricao'])): ?>
                                    <p class="specs"><?= htmlspecialchars(substr($produto['descricao'], 0, 100)) ?><?= strlen($produto['descricao']) > 100 ? '...' : '' ?></p>
                                <?php endif; ?>
                                
                                <?php if (!empty($produto['preco_promocional'])): ?>
                                    <div class="price-container">
                                        <p class="price old-price">R$ <?= formatPrice($produto['preco']) ?></p>
                                        <p class="price promo-price">R$ <?= formatPrice($produto['preco_promocional']) ?></p>
                                        <span class="discount-badge">-<?= $produto['desconto_percentual'] ?>%</span>
                                    </div>
                                <?php else: ?>
                                    <p class="price">R$ <?= formatPrice($produto['preco']) ?></p>
                                <?php endif; ?>
                                
                                <div class="product-actions">
                                    <?php if ($produto['estoque'] > 0): ?>
                                        <button class="btn" onclick="addToCart('<?= htmlspecialchars($produto['nome']) ?>', <?= $produto['preco_promocional'] ?: $produto['preco'] ?>, <?= $produto['id'] ?>)">
                                            <i class="fas fa-shopping-cart"></i> Adicionar
                                        </button>
                                    <?php else: ?>
                                        <button class="btn btn-disabled" disabled>
                                            <i class="fas fa-times"></i> Esgotado
                                        </button>
                                    <?php endif; ?>
                                    
                                    <button class="btn btn-outline" onclick="viewProduct(<?= $produto['id'] ?>)">
                                        <i class="fas fa-eye"></i> Detalhes
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Paginação -->
                <?php if ($totalPaginas > 1): ?>
                    <div class="pagination">
                        <?php if ($pagina > 1): ?>
                            <a href="<?= buildUrl(['pagina' => $pagina - 1]) ?>">
                                <i class="fas fa-chevron-left"></i> Anterior
                            </a>
                        <?php endif; ?>

                        <?php
                        $inicio = max(1, $pagina - 2);
                        $fim = min($totalPaginas, $pagina + 2);
                        
                        if ($inicio > 1): ?>
                            <a href="<?= buildUrl(['pagina' => 1]) ?>">1</a>
                            <?php if ($inicio > 2): ?>
                                <span class="disabled">...</span>
                            <?php endif; ?>
                        <?php endif; ?>

                        <?php for ($i = $inicio; $i <= $fim; $i++): ?>
                            <?php if ($i == $pagina): ?>
                                <span class="current"><?= $i ?></span>
                            <?php else: ?>
                                <a href="<?= buildUrl(['pagina' => $i]) ?>"><?= $i ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>

                        <?php if ($fim < $totalPaginas): ?>
                            <?php if ($fim < $totalPaginas - 1): ?>
                                <span class="disabled">...</span>
                            <?php endif; ?>
                            <a href="<?= buildUrl(['pagina' => $totalPaginas]) ?>"><?= $totalPaginas ?></a>
                        <?php endif; ?>

                        <?php if ($pagina < $totalPaginas): ?>
                            <a href="<?= buildUrl(['pagina' => $pagina + 1]) ?>">
                                Próxima <i class="fas fa-chevron-right"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <!-- Carrinho Modal -->
    <div id="cart-modal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="toggleCart()">&times;</span>
            <h2>Carrinho de Compras</h2>
            <div id="cart-items"></div>
            <div class="cart-total">
                <strong>Total: R$ <span id="cart-total">0,00</span></strong>
            </div>
            <button class="btn checkout-btn">Finalizar Compra</button>
        </div>
    </div>

    <footer>
        <div class="container">
            <p>&copy; 2024 InovaTech Store. Todos os direitos reservados.</p>
            <p>Desenvolvido com ❤️ para tecnologia</p>
        </div>
    </footer>

    <script src="script.js"></script>
    <script>
        // Função para visualizar detalhes do produto
        function viewProduct(id) {
            // Por enquanto, abre em nova aba - depois pode ser modal
            window.open(`produto.php?id=${id}`, '_blank');
        }

        // Adicionar produto ao carrinho com ID
        function addToCart(productName, price, productId) {
            // Versão melhorada que inclui ID do produto
            const existingItem = cart.find(item => item.id === productId);
            
            if (existingItem) {
                existingItem.quantity += 1;
            } else {
                cart.push({
                    id: productId,
                    name: productName,
                    price: price,
                    quantity: 1
                });
            }
            
            localStorage.setItem('cart', JSON.stringify(cart));
            updateCartDisplay();
            updateCartCount();
            showToast(`${productName} adicionado ao carrinho!`);
        }

        // Função para mostrar toast
        function showToast(message, type = 'success') {
            const toast = document.createElement('div');
            toast.className = `toast ${type} show`;
            toast.innerHTML = `<i class="fas fa-check-circle"></i> ${message}`;
            
            document.body.appendChild(toast);
            
            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => {
                    document.body.removeChild(toast);
                }, 300);
            }, 3000);
        }
    </script>
</body>
</html>