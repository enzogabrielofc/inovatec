<?php
/**
 * Dashboard Administrativo - InovaTech Store
 */
session_start();

require_once '../classes/Product.php';
require_once '../config/database.php';

// Verificar autenticação (por enquanto simples)
$isAdmin = isset($_SESSION['admin_logged']) && $_SESSION['admin_logged'] === true;

// Login simples para demonstração
if (isset($_POST['admin_login'])) {
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';
    
    // Verificação básica (em produção usar hash e BD)
    if ($email === 'admin@inovatech.com' && $senha === 'password') {
        $_SESSION['admin_logged'] = true;
        $_SESSION['admin_name'] = 'Administrador';
        header('Location: index.php');
        exit;
    } else {
        $error = 'Credenciais inválidas';
    }
}

// Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}

// Se não estiver logado, mostrar tela de login
if (!$isAdmin) {
    include 'login.php';
    exit;
}

try {
    $product = new Product();
    
    // Buscar estatísticas
    $stats = $product->getStats();
    $lowStock = $product->getLowStockProducts();
    
    // Buscar produtos recentes
    $recentProducts = $product->readAll(['order' => 'data_criacao', 'dir' => 'DESC', 'limit' => 5]);
    
    // Buscar dados para gráficos (simulado)
    $vendasMes = [
        'Janeiro' => 15420,
        'Fevereiro' => 18350,
        'Março' => 22180,
        'Abril' => 19850,
        'Maio' => 25600,
        'Junho' => 28900
    ];

} catch (Exception $e) {
    $stats = ['total_produtos' => 0, 'produtos_destaque' => 0, 'estoque_baixo' => 0, 'preco_medio' => '0,00'];
    $lowStock = [];
    $recentProducts = [];
    $vendasMes = [];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - InovaTech Store</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="admin-style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="admin-body">
    <div class="admin-container">
        <!-- Sidebar -->
        <nav class="admin-sidebar">
            <div class="sidebar-header">
                <h2><i class="fas fa-cogs"></i> Admin Panel</h2>
                <p>Bem-vindo, <?= $_SESSION['admin_name'] ?>!</p>
            </div>
            
            <ul class="sidebar-menu">
                <li class="active">
                    <a href="index.php">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                </li>
                <li>
                    <a href="produtos.php">
                        <i class="fas fa-box"></i> Produtos
                    </a>
                </li>
                <li>
                    <a href="categorias.php">
                        <i class="fas fa-tags"></i> Categorias
                    </a>
                </li>
                <li>
                    <a href="pedidos.php">
                        <i class="fas fa-shopping-cart"></i> Pedidos
                    </a>
                </li>
                <li>
                    <a href="clientes.php">
                        <i class="fas fa-users"></i> Clientes
                    </a>
                </li>
                <li>
                    <a href="relatorios.php">
                        <i class="fas fa-chart-bar"></i> Relatórios
                    </a>
                </li>
                <li>
                    <a href="configuracoes.php">
                        <i class="fas fa-cog"></i> Configurações
                    </a>
                </li>
                <li class="sidebar-divider"></li>
                <li>
                    <a href="../index.php" target="_blank">
                        <i class="fas fa-external-link-alt"></i> Ver Loja
                    </a>
                </li>
                <li>
                    <a href="?logout=1" onclick="return confirm('Deseja realmente sair?')">
                        <i class="fas fa-sign-out-alt"></i> Sair
                    </a>
                </li>
            </ul>
        </nav>

        <!-- Main Content -->
        <main class="admin-main">
            <div class="admin-header">
                <h1><i class="fas fa-tachometer-alt"></i> Dashboard</h1>
                <div class="header-actions">
                    <button class="btn btn-primary" onclick="location.href='produtos.php?action=new'">
                        <i class="fas fa-plus"></i> Novo Produto
                    </button>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stats-card">
                    <div class="stats-icon">
                        <i class="fas fa-box"></i>
                    </div>
                    <div class="stats-content">
                        <h3><?= $stats['total_produtos'] ?></h3>
                        <p>Total de Produtos</p>
                    </div>
                </div>

                <div class="stats-card">
                    <div class="stats-icon featured">
                        <i class="fas fa-star"></i>
                    </div>
                    <div class="stats-content">
                        <h3><?= $stats['produtos_destaque'] ?></h3>
                        <p>Em Destaque</p>
                    </div>
                </div>

                <div class="stats-card">
                    <div class="stats-icon warning">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="stats-content">
                        <h3><?= $stats['estoque_baixo'] ?></h3>
                        <p>Estoque Baixo</p>
                    </div>
                </div>

                <div class="stats-card">
                    <div class="stats-icon success">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="stats-content">
                        <h3>R$ <?= $stats['preco_medio'] ?></h3>
                        <p>Preço Médio</p>
                    </div>
                </div>
            </div>

            <!-- Charts and Tables Row -->
            <div class="dashboard-row">
                <!-- Sales Chart -->
                <div class="dashboard-card chart-card">
                    <div class="card-header">
                        <h3><i class="fas fa-chart-line"></i> Vendas Mensais</h3>
                    </div>
                    <div class="card-content">
                        <canvas id="salesChart"></canvas>
                    </div>
                </div>

                <!-- Low Stock Alert -->
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3><i class="fas fa-exclamation-triangle text-warning"></i> Estoque Baixo</h3>
                        <?php if (!empty($lowStock)): ?>
                            <span class="badge badge-warning"><?= count($lowStock) ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="card-content">
                        <?php if (empty($lowStock)): ?>
                            <div class="empty-state">
                                <i class="fas fa-check-circle text-success"></i>
                                <p>Todos os produtos com estoque adequado!</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="admin-table">
                                    <thead>
                                        <tr>
                                            <th>Produto</th>
                                            <th>Estoque</th>
                                            <th>Ação</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($lowStock as $item): ?>
                                            <tr>
                                                <td>
                                                    <strong><?= htmlspecialchars($item['nome']) ?></strong><br>
                                                    <small class="text-muted"><?= htmlspecialchars($item['categoria_nome']) ?></small>
                                                </td>
                                                <td>
                                                    <span class="badge badge-danger"><?= $item['estoque'] ?></span>
                                                </td>
                                                <td>
                                                    <a href="produtos.php?action=edit&id=<?= $item['id'] ?>" class="btn-sm btn-primary">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Recent Products -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h3><i class="fas fa-clock"></i> Produtos Recentes</h3>
                    <a href="produtos.php" class="btn-sm btn-outline">Ver Todos</a>
                </div>
                <div class="card-content">
                    <?php if (empty($recentProducts)): ?>
                        <div class="empty-state">
                            <i class="fas fa-box-open"></i>
                            <p>Nenhum produto cadastrado ainda.</p>
                            <a href="produtos.php?action=new" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Cadastrar Primeiro Produto
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>Produto</th>
                                        <th>Categoria</th>
                                        <th>Preço</th>
                                        <th>Estoque</th>
                                        <th>Status</th>
                                        <th>Data</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentProducts as $produto): ?>
                                        <tr>
                                            <td>
                                                <div class="product-info">
                                                    <?php if (!empty($produto['imagem_principal'])): ?>
                                                        <img src="<?= htmlspecialchars($produto['imagem_principal']) ?>" alt="<?= htmlspecialchars($produto['nome']) ?>" class="product-thumb">
                                                    <?php else: ?>
                                                        <div class="product-thumb placeholder">
                                                            <i class="fas fa-image"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                    <div>
                                                        <strong><?= htmlspecialchars($produto['nome']) ?></strong>
                                                        <?php if (!empty($produto['marca'])): ?>
                                                            <br><small class="text-muted"><?= htmlspecialchars($produto['marca']) ?></small>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><?= htmlspecialchars($produto['categoria_nome']) ?></td>
                                            <td>
                                                <?php if (!empty($produto['preco_promocional'])): ?>
                                                    <span class="old-price">R$ <?= formatPrice($produto['preco']) ?></span><br>
                                                    <strong class="promo-price">R$ <?= formatPrice($produto['preco_promocional']) ?></strong>
                                                <?php else: ?>
                                                    <strong>R$ <?= formatPrice($produto['preco']) ?></strong>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($produto['estoque'] <= $produto['estoque_minimo']): ?>
                                                    <span class="badge badge-danger"><?= $produto['estoque'] ?></span>
                                                <?php else: ?>
                                                    <span class="badge badge-success"><?= $produto['estoque'] ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($produto['ativo']): ?>
                                                    <span class="badge badge-success">Ativo</span>
                                                    <?php if ($produto['destaque']): ?>
                                                        <span class="badge badge-warning">Destaque</span>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <span class="badge badge-secondary">Inativo</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= formatDate($produto['data_criacao'], 'd/m/Y') ?></td>
                                            <td class="actions">
                                                <a href="produtos.php?action=edit&id=<?= $produto['id'] ?>" class="btn-sm btn-primary" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="../produtos.php?id=<?= $produto['id'] ?>" class="btn-sm btn-outline" target="_blank" title="Visualizar">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Gráfico de vendas
        const ctx = document.getElementById('salesChart').getContext('2d');
        const salesChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?= json_encode(array_keys($vendasMes)) ?>,
                datasets: [{
                    label: 'Vendas (R$)',
                    data: <?= json_encode(array_values($vendasMes)) ?>,
                    borderColor: '#667eea',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'R$ ' + value.toLocaleString('pt-BR');
                            }
                        }
                    }
                }
            }
        });

        // Auto-refresh da página a cada 5 minutos
        setTimeout(() => {
            location.reload();
        }, 300000);
    </script>
</body>
</html>