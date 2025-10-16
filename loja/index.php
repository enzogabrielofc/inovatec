<?php
/**
 * Página Principal da Loja - InovaTech Store
 */
require_once 'classes/Product.php';
require_once 'classes/Category.php';

try {
    $product = new Product();
    
    // Buscar produtos em destaque
    $produtosDestaque = $product->readAll(['destaque' => true, 'limit' => 6]);
    
    // Buscar categorias
    $category = new Category();
    $categorias = $category->readAll();
} catch (Exception $e) {
    $produtosDestaque = [];
    $categorias = [
        ['id' => 1, 'nome' => 'Computadores', 'icone' => '💻'],
        ['id' => 2, 'nome' => 'Videogames', 'icone' => '🎮']
    ];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>InovaTech Store - Sua Loja de Tecnologia</title>
    <link rel="stylesheet" href="style-clean.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <header>
        <div class="container">
            <h1>InovaTech Store</h1>
            <p class="slogan">Sua loja de tecnologia online</p>
        </div>
    </header>

    <nav>
        <div class="container">
            <ul class="nav-menu">
                <li><a href="index.php" class="active">Início</a></li>
                <li><a href="produtos.php?categoria=1">Computadores</a></li>
                <li><a href="produtos.php?categoria=2">Videogames</a></li>
                <li><a href="produtos.php">Todos os Produtos</a></li>
                <li><a href="contato.php">Contato</a></li>
                <li><a href="#" class="cart-btn" onclick="toggleCart()"><i class="fas fa-shopping-cart"></i> Carrinho (<span id="cart-count">0</span>)</a></li>
                <li><a href="admin-dashboard.php" class="login-btn" title="Área Administrativa"><i class="fas fa-user-shield"></i> Admin</a></li>
            </ul>
        </div>
    </nav>

    <main>
        <section class="hero">
            <div class="container">
                <h2>Bem-vindo à InovaTech Store!</h2>
                <p>Descubra os melhores produtos em tecnologia com preços incríveis</p>
            </div>
        </section>

        <section class="categories">
            <div class="container">
                <h2>Nossas Categorias</h2>
                <div class="category-grid">
                    <?php if (!empty($categorias)): ?>
                        <?php foreach ($categorias as $categoria): ?>
                            <div class="category-card">
                                <div class="card-icon"><?= $categoria['icone'] ?></div>
                                <h3><?= htmlspecialchars($categoria['nome']) ?></h3>
                                <p><?= htmlspecialchars($categoria['descricao']) ?></p>
                                <a href="produtos.php?categoria=<?= $categoria['id'] ?>" class="btn">Ver <?= htmlspecialchars($categoria['nome']) ?></a>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="category-card">
                            <div class="card-icon">💻</div>
                            <h3>Computadores</h3>
                            <p>Desktops, notebooks e workstations para todas as necessidades</p>
                            <a href="produtos.php?categoria=1" class="btn">Ver Computadores</a>
                        </div>
                        
                        <div class="category-card">
                            <div class="card-icon">🎮</div>
                            <h3>Videogames</h3>
                            <p>Consoles, jogos e acessórios para gamers</p>
                            <a href="produtos.php?categoria=2" class="btn">Ver Videogames</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <section class="featured-products">
            <div class="container">
                <h2>Produtos em Destaque</h2>
                <div class="product-grid">
                    <?php if (empty($produtosDestaque)): ?>
                        <div class="no-products">
                            <p>Nenhum produto em destaque no momento.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($produtosDestaque as $produto): ?>
                            <div class="product-card">
                                <?php if (!empty($produto['imagem_principal'])): ?>
                                    <img src="<?= htmlspecialchars($produto['imagem_principal']) ?>" alt="<?= htmlspecialchars($produto['nome']) ?>">
                                <?php else: ?>
                                    <img src="https://via.placeholder.com/280x200/007ACC/ffffff?text=<?= urlencode($produto['nome']) ?>" alt="<?= htmlspecialchars($produto['nome']) ?>">
                                <?php endif; ?>
                                
                                <h3><?= htmlspecialchars($produto['nome']) ?></h3>
                                
                                <?php if (!empty($produto['preco_promocional'])): ?>
                                    <div class="price-container">
                                        <p class="price old-price">R$ <?= formatPrice($produto['preco']) ?></p>
                                        <p class="price promo-price">R$ <?= formatPrice($produto['preco_promocional']) ?></p>
                                        <span class="discount-badge">-<?= $produto['desconto_percentual'] ?>%</span>
                                    </div>
                                <?php else: ?>
                                    <p class="price">R$ <?= formatPrice($produto['preco']) ?></p>
                                <?php endif; ?>
                                
                                <?php if ($produto['estoque'] > 0): ?>
                                    <button class="btn" onclick="addToCart('<?= htmlspecialchars($produto['nome']) ?>', <?= $produto['preco_promocional'] ?: $produto['preco'] ?>)">
                                        <i class="fas fa-shopping-cart"></i> Adicionar ao Carrinho
                                    </button>
                                <?php else: ?>
                                    <button class="btn btn-disabled" disabled>
                                        <i class="fas fa-times"></i> Esgotado
                                    </button>
                                <?php endif; ?>
                                
                                <?php if ($produto['estoque'] > 0 && $produto['estoque'] <= $produto['estoque_minimo']): ?>
                                    <div class="stock-warning">
                                        <i class="fas fa-exclamation-triangle"></i> Últimas unidades!
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                
                <?php if (!empty($produtosDestaque)): ?>
                    <div class="see-more">
                        <a href="produtos.php" class="btn btn-outline">
                            <i class="fas fa-th-large"></i> Ver Todos os Produtos
                        </a>
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
</body>
</html>