<?php
session_start();

// Verificar se é admin (simulado)
if (!isset($_SESSION['admin_logged']) && !isset($_GET['login'])) {
    ?>
    <!DOCTYPE html>
    <html lang="pt-BR">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Login Admin - InovaTech Store</title>
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; }
            .login-container { background: white; padding: 2rem; border-radius: 10px; box-shadow: 0 10px 25px rgba(0,0,0,0.2); max-width: 400px; width: 100%; }
            .login-box h1 { color: #333; margin-bottom: 1rem; text-align: center; }
            .form-group { margin-bottom: 1rem; }
            .form-group label { display: block; margin-bottom: 0.5rem; font-weight: 500; color: #555; }
            .form-group input { width: 100%; padding: 0.75rem; border: 2px solid #ddd; border-radius: 5px; font-size: 1rem; }
            .btn-primary { width: 100%; padding: 1rem; background: #007ACC; color: white; border: none; border-radius: 5px; font-size: 1rem; font-weight: 500; cursor: pointer; margin-top: 1rem; }
            .btn-primary:hover { background: #005999; }
            small { color: #666; text-align: center; display: block; margin-top: 1rem; }
        </style>
    </head>
    <body>
        <div class="login-container">
            <div class="login-box">
                <h1>🔐 Admin Login</h1>
                <p>Acesso ao painel administrativo</p>
                <form method="get">
                    <input type="hidden" name="login" value="1">
                    <div class="form-group">
                        <label>Email:</label>
                        <input type="email" value="admin@inovatech.com" readonly>
                    </div>
                    <div class="form-group">
                        <label>Senha:</label>
                        <input type="password" value="admin123" readonly>
                    </div>
                    <button type="submit" class="btn-primary">Entrar no Sistema</button>
                </form>
                <p><small>Para demonstração: use as credenciais preenchidas</small></p>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

if (isset($_GET['login'])) {
    $_SESSION['admin_logged'] = true;
    header('Location: admin-dashboard.php');
    exit;
}

if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: admin-dashboard.php');
    exit;
}

require_once 'classes/Product.php';
require_once 'classes/Order.php';

// Instanciar classes
try {
    $product = new Product();
    $order = new Order();
    
    // Buscar estatísticas
    $stats = $product->getDashboardStats();
    $bestSellers = $product->getBestSellers(5);
    $lowStock = $product->getLowStockProducts();
    $recentOrders = $order->readAll(['limit' => 10]);
    $monthlyReport = $product->getMonthlySalesReport();
} catch (Exception $e) {
    // Dados de exemplo caso não consiga conectar
    $stats = [
        'total_produtos' => 25,
        'produtos_destaque' => 8,
        'total_pedidos' => 156,
        'total_faturamento' => 89750.00,
        'estoque_baixo' => 3,
        'vendas_mes' => 23
    ];
    $bestSellers = [];
    $lowStock = [];
    $recentOrders = [];
    $monthlyReport = [];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - InovaTech Store</title>
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
        
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem; }
        
        .stat-card { background: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); display: flex; align-items: center; }
        .stat-card.warning { border-left: 4px solid #f39c12; }
        .stat-card.success { border-left: 4px solid #27ae60; }
        .stat-card.danger { border-left: 4px solid #e74c3c; }
        
        .stat-icon { font-size: 2rem; margin-right: 1rem; }
        .stat-info h3 { font-size: 1.5rem; color: #2c3e50; margin-bottom: 0.25rem; }
        .stat-info p { color: #7f8c8d; font-size: 0.9rem; }
        
        .dashboard-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 1.5rem; }
        
        .dashboard-card { background: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .dashboard-card h3 { padding: 1.5rem; border-bottom: 1px solid #ecf0f1; margin: 0; color: #2c3e50; font-size: 1.1rem; }
        .dashboard-card h3 i { margin-right: 0.5rem; color: #3498db; }
        
        .table-responsive { overflow-x: auto; }
        .admin-table { width: 100%; border-collapse: collapse; }
        .admin-table th, .admin-table td { padding: 1rem; text-align: left; border-bottom: 1px solid #ecf0f1; }
        .admin-table th { background: #f8f9fa; font-weight: 600; color: #2c3e50; }
        .admin-table tr:hover { background: #f8f9fa; }
        
        .badge { padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem; font-weight: 500; }
        .badge.danger { background: #fee; color: #e74c3c; }
        .badge.success { background: #efe; color: #27ae60; }
        .badge.warning { background: #ffc; color: #f39c12; }
        
        .badge.status-pendente { background: #ffc; color: #f39c12; }
        .badge.status-confirmado { background: #e3f2fd; color: #2196f3; }
        .badge.status-enviado { background: #f3e5f5; color: #9c27b0; }
        .badge.status-entregue { background: #e8f5e8; color: #4caf50; }
        .badge.status-cancelado { background: #ffebee; color: #f44336; }
        
        .price { font-weight: 600; color: #27ae60; }
        .btn-sm { padding: 0.25rem 0.5rem; background: #3498db; color: white; text-decoration: none; border-radius: 4px; font-size: 0.8rem; }
        .btn-sm:hover { background: #2980b9; }
        .btn-outline { padding: 0.5rem 1rem; border: 2px solid #3498db; color: #3498db; text-decoration: none; border-radius: 4px; display: inline-block; margin-top: 1rem; }
        .btn-outline:hover { background: #3498db; color: white; }
        
        .card-footer { padding: 1.5rem; border-top: 1px solid #ecf0f1; }
        
        .quick-actions { display: flex; gap: 1rem; margin-bottom: 2rem; }
        .quick-action-btn { padding: 1rem 2rem; background: #3498db; color: white; text-decoration: none; border-radius: 8px; text-align: center; transition: all 0.3s; }
        .quick-action-btn:hover { background: #2980b9; transform: translateY(-2px); }
        .quick-action-btn i { display: block; font-size: 2rem; margin-bottom: 0.5rem; }
        
        @keyframes pulse { 0% { transform: scale(1); } 50% { transform: scale(1.05); } 100% { transform: scale(1); } }
        .pulse { animation: pulse 2s infinite; }
        
        @media (max-width: 768px) {
            .admin-layout { flex-direction: column; }
            .sidebar { width: 100%; }
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
            .dashboard-grid { grid-template-columns: 1fr; }
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
                <a href="admin-dashboard.php" class="nav-link active">
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
                <a href="admin-vendas.php" class="nav-link">
                    <i class="fas fa-cash-register"></i> Nova Venda
                </a>
                <a href="admin-relatorios.php" class="nav-link">
                    <i class="fas fa-chart-line"></i> Relatórios
                </a>
                <a href="../index.php" class="nav-link">
                    <i class="fas fa-store"></i> Ver Loja
                </a>
                <a href="?logout=1" class="nav-link logout">
                    <i class="fas fa-sign-out-alt"></i> Sair
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <header class="admin-header">
                <h1>Dashboard</h1>
                <p>Visão geral da InovaTech Store</p>
            </header>

            <!-- Quick Actions -->
            <div class="quick-actions">
                <a href="admin-adicionar-produto.php" class="quick-action-btn">
                    <i class="fas fa-plus"></i>
                    Novo Produto
                </a>
                <a href="admin-vendas.php" class="quick-action-btn">
                    <i class="fas fa-cash-register"></i>
                    Nova Venda
                </a>
                <a href="admin-relatorios.php" class="quick-action-btn">
                    <i class="fas fa-chart-bar"></i>
                    Ver Relatórios
                </a>
            </div>

            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">📦</div>
                    <div class="stat-info">
                        <h3><?= $stats['total_produtos'] ?? 0 ?></h3>
                        <p>Produtos Cadastrados</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">⭐</div>
                    <div class="stat-info">
                        <h3><?= $stats['produtos_destaque'] ?? 0 ?></h3>
                        <p>Produtos em Destaque</p>
                    </div>
                </div>
                
                <div class="stat-card success">
                    <div class="stat-icon">📊</div>
                    <div class="stat-info">
                        <h3><?= $stats['total_pedidos'] ?? 0 ?></h3>
                        <p>Total de Vendas</p>
                    </div>
                </div>
                
                <div class="stat-card success">
                    <div class="stat-icon">💰</div>
                    <div class="stat-info">
                        <h3>R$ <?= formatPrice($stats['total_faturamento'] ?? 0) ?></h3>
                        <p>Faturamento Total</p>
                    </div>
                </div>
                
                <div class="stat-card <?= ($stats['estoque_baixo'] ?? 0) > 0 ? 'warning pulse' : '' ?>">
                    <div class="stat-icon">⚠️</div>
                    <div class="stat-info">
                        <h3><?= $stats['estoque_baixo'] ?? 0 ?></h3>
                        <p>Produtos com Estoque Baixo</p>
                    </div>
                </div>
                
                <div class="stat-card success">
                    <div class="stat-icon">📈</div>
                    <div class="stat-info">
                        <h3><?= $stats['vendas_mes'] ?? 0 ?></h3>
                        <p>Vendas do Mês</p>
                    </div>
                </div>
            </div>

            <!-- Charts and Tables -->
            <div class="dashboard-grid">
                <!-- Produtos Mais Vendidos -->
                <div class="dashboard-card">
                    <h3><i class="fas fa-trophy"></i> Produtos Mais Vendidos</h3>
                    <div class="table-responsive">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Produto</th>
                                    <th>Vendidos</th>
                                    <th>Faturamento</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($bestSellers)): ?>
                                    <tr>
                                        <td colspan="3" style="text-align: center; padding: 2rem;">
                                            <i class="fas fa-chart-line fa-3x" style="color: #bdc3c7; margin-bottom: 1rem;"></i>
                                            <br>Nenhuma venda registrada ainda
                                            <br><small>Vendas aparecerão aqui após a primeira compra</small>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($bestSellers as $produto): ?>
                                        <tr>
                                            <td>
                                                <strong><?= htmlspecialchars($produto['nome']) ?></strong>
                                                <br><small><?= htmlspecialchars($produto['marca'] ?? '') ?></small>
                                            </td>
                                            <td>
                                                <span class="badge success"><?= $produto['quantidade_vendida'] ?> un.</span>
                                            </td>
                                            <td class="price">R$ <?= formatPrice($produto['total_faturamento']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Estoque Baixo -->
                <div class="dashboard-card">
                    <h3><i class="fas fa-exclamation-triangle"></i> Estoque Baixo</h3>
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
                                <?php if (empty($lowStock)): ?>
                                    <tr>
                                        <td colspan="3" style="text-align: center; padding: 2rem;">
                                            <i class="fas fa-check-circle fa-3x" style="color: #27ae60; margin-bottom: 1rem;"></i>
                                            <br>Todos os produtos com estoque adequado ✅
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($lowStock as $produto): ?>
                                        <tr>
                                            <td>
                                                <strong><?= htmlspecialchars($produto['nome']) ?></strong>
                                                <br><small><?= htmlspecialchars($produto['categoria_nome'] ?? '') ?></small>
                                            </td>
                                            <td>
                                                <span class="badge danger"><?= $produto['estoque'] ?> un.</span>
                                            </td>
                                            <td>
                                                <a href="admin-produtos.php?edit=<?= $produto['id'] ?>" class="btn-sm">Editar</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Relatório Mensal -->
                <div class="dashboard-card">
                    <h3><i class="fas fa-calendar-alt"></i> Vendas Mensais</h3>
                    <div class="table-responsive">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Mês</th>
                                    <th>Vendas</th>
                                    <th>Faturamento</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($monthlyReport)): ?>
                                    <tr>
                                        <td colspan="3" style="text-align: center; padding: 2rem;">
                                            <i class="fas fa-calendar-times fa-3x" style="color: #bdc3c7; margin-bottom: 1rem;"></i>
                                            <br>Nenhuma venda mensal registrada
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach (array_slice($monthlyReport, 0, 6) as $mes): ?>
                                        <tr>
                                            <td><strong><?= $mes['mes_nome'] ?></strong></td>
                                            <td><span class="badge"><?= $mes['total_pedidos'] ?></span></td>
                                            <td class="price">R$ <?= formatPrice($mes['total_faturamento']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer">
                        <a href="admin-relatorios.php" class="btn-outline">Ver Relatório Completo</a>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Auto refresh dashboard a cada 5 minutos
        setTimeout(() => {
            location.reload();
        }, 300000);
        
        // Notification para estoque baixo
        const estoquebaixo = <?= $stats['estoque_baixo'] ?? 0 ?>;
        if (estoquebaixo > 0) {
            console.warn(`⚠️ Atenção: ${estoquebaixo} produto(s) com estoque baixo!`);
        }
    </script>
</body>
</html>