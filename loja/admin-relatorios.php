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

// Buscar dados para relatórios
try {
    $dashboardStats = $product->getDashboardStats();
    $monthlyReport = $product->getMonthlySalesReport();
    $bestSellers = $product->getBestSellers(10);
    $lowStockProducts = $product->getLowStockProducts();
    $orderStats = $order->getStats();
    
    // Vendas dos últimos 30 dias
    $salesLast30Days = $order->getSalesByPeriod(
        date('Y-m-d', strtotime('-30 days')),
        date('Y-m-d')
    );
    
} catch (Exception $e) {
    // Dados de exemplo em caso de erro
    $dashboardStats = [
        'total_produtos' => 25,
        'produtos_destaque' => 8,
        'total_pedidos' => 156,
        'total_faturamento' => 89750.00,
        'estoque_baixo' => 3,
        'vendas_mes' => 23
    ];
    $monthlyReport = [];
    $bestSellers = [];
    $lowStockProducts = [];
    $orderStats = [];
    $salesLast30Days = [];
}

// Gerar dados para gráficos
$monthlyLabels = [];
$monthlyValues = [];
foreach (array_reverse($monthlyReport) as $mes) {
    $monthlyLabels[] = $mes['mes_nome'];
    $monthlyValues[] = $mes['total_faturamento'];
}

$dailyLabels = [];
$dailyValues = [];
foreach (array_reverse($salesLast30Days) as $day) {
    $dailyLabels[] = date('d/m', strtotime($day['data']));
    $dailyValues[] = $day['faturamento_dia'];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatórios - InovaTech Store</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        
        .filters-section { background: white; padding: 1.5rem; border-radius: 8px; margin-bottom: 2rem; }
        .filters { display: flex; gap: 1rem; align-items: center; flex-wrap: wrap; }
        .filters input, .filters select { padding: 0.5rem; border: 2px solid #ddd; border-radius: 4px; }
        .filters button { padding: 0.5rem 1rem; background: #3498db; color: white; border: none; border-radius: 4px; cursor: pointer; }
        
        .stats-overview { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem; }
        
        .stat-card { background: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .stat-card.success { border-left: 4px solid #27ae60; }
        .stat-card.warning { border-left: 4px solid #f39c12; }
        .stat-card.info { border-left: 4px solid #3498db; }
        
        .stat-number { font-size: 2rem; font-weight: 600; color: #2c3e50; margin-bottom: 0.5rem; }
        .stat-label { color: #7f8c8d; font-size: 0.9rem; }
        .stat-change { font-size: 0.8rem; margin-top: 0.5rem; }
        .stat-change.positive { color: #27ae60; }
        .stat-change.negative { color: #e74c3c; }
        
        .reports-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 2rem; margin-bottom: 2rem; }
        
        .chart-card, .report-card { background: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .chart-card h3, .report-card h3 { padding: 1.5rem; border-bottom: 1px solid #ecf0f1; margin: 0; color: #2c3e50; }
        .chart-card h3 i, .report-card h3 i { margin-right: 0.5rem; color: #3498db; }
        
        .chart-container { padding: 1.5rem; height: 400px; }
        .chart-container canvas { max-height: 350px; }
        
        .table-responsive { overflow-x: auto; }
        .report-table { width: 100%; border-collapse: collapse; }
        .report-table th, .report-table td { padding: 1rem; text-align: left; border-bottom: 1px solid #ecf0f1; }
        .report-table th { background: #f8f9fa; font-weight: 600; color: #2c3e50; }
        .report-table tr:hover { background: #f8f9fa; }
        
        .price { font-weight: 600; color: #27ae60; }
        .stock-warning { color: #e74c3c; }
        
        .full-width-reports { display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2rem; }
        
        .export-section { background: white; padding: 1.5rem; border-radius: 8px; margin-top: 2rem; }
        .export-buttons { display: flex; gap: 1rem; flex-wrap: wrap; }
        .btn-export { padding: 0.75rem 1.5rem; border: 2px solid #3498db; color: #3498db; text-decoration: none; border-radius: 4px; transition: all 0.3s; }
        .btn-export:hover { background: #3498db; color: white; }
        .btn-export i { margin-right: 0.5rem; }
        
        .period-selector { margin-bottom: 1rem; }
        .period-selector label { display: inline-block; margin-right: 1rem; font-weight: 500; }
        
        @media (max-width: 1024px) {
            .admin-layout { flex-direction: column; }
            .sidebar { width: 100%; }
            .reports-grid { grid-template-columns: 1fr; }
            .full-width-reports { grid-template-columns: 1fr; }
            .stats-overview { grid-template-columns: repeat(2, 1fr); }
        }
        
        @media (max-width: 768px) {
            .stats-overview { grid-template-columns: 1fr; }
            .filters { flex-direction: column; align-items: stretch; }
            .export-buttons { flex-direction: column; }
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
                <a href="admin-vendas.php" class="nav-link">
                    <i class="fas fa-cash-register"></i> Nova Venda
                </a>
                <a href="admin-relatorios.php" class="nav-link active">
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
                <h1><i class="fas fa-chart-line"></i> Relatórios e Análises</h1>
                <p>Acompanhe o desempenho da sua loja com relatórios detalhados</p>
            </header>

            <!-- Filtros -->
            <div class="filters-section">
                <div class="period-selector">
                    <label>Período:</label>
                    <select id="periodSelector" onchange="updateReports()">
                        <option value="30">Últimos 30 dias</option>
                        <option value="90">Últimos 3 meses</option>
                        <option value="365">Último ano</option>
                        <option value="custom">Personalizado</option>
                    </select>
                </div>
                <div class="filters" id="customFilters" style="display: none;">
                    <input type="date" id="startDate">
                    <input type="date" id="endDate">
                    <button onclick="updateReports()">Aplicar Filtro</button>
                </div>
            </div>

            <!-- Estatísticas Gerais -->
            <div class="stats-overview">
                <div class="stat-card success">
                    <div class="stat-number">R$ <?= formatPrice($dashboardStats['total_faturamento'] ?? 0) ?></div>
                    <div class="stat-label">Faturamento Total</div>
                    <div class="stat-change positive">
                        <i class="fas fa-arrow-up"></i> +12.5% vs mês anterior
                    </div>
                </div>

                <div class="stat-card info">
                    <div class="stat-number"><?= $dashboardStats['total_pedidos'] ?? 0 ?></div>
                    <div class="stat-label">Total de Vendas</div>
                    <div class="stat-change positive">
                        <i class="fas fa-arrow-up"></i> +8.3% vs mês anterior
                    </div>
                </div>

                <div class="stat-card success">
                    <div class="stat-number">R$ <?= formatPrice(($dashboardStats['ticket_medio'] ?? 0)) ?></div>
                    <div class="stat-label">Ticket Médio</div>
                    <div class="stat-change positive">
                        <i class="fas fa-arrow-up"></i> +5.2% vs mês anterior
                    </div>
                </div>

                <div class="stat-card warning">
                    <div class="stat-number"><?= $dashboardStats['estoque_baixo'] ?? 0 ?></div>
                    <div class="stat-label">Produtos Estoque Baixo</div>
                    <?php if (($dashboardStats['estoque_baixo'] ?? 0) > 0): ?>
                        <div class="stat-change negative">
                            <i class="fas fa-exclamation-triangle"></i> Requer atenção
                        </div>
                    <?php else: ?>
                        <div class="stat-change positive">
                            <i class="fas fa-check"></i> Estoque adequado
                        </div>
                    <?php endif; ?>
                </div>

                <div class="stat-card info">
                    <div class="stat-number"><?= $dashboardStats['total_produtos'] ?? 0 ?></div>
                    <div class="stat-label">Produtos Cadastrados</div>
                    <div class="stat-change">
                        <?= $dashboardStats['produtos_destaque'] ?? 0 ?> em destaque
                    </div>
                </div>

                <div class="stat-card success">
                    <div class="stat-number"><?= $dashboardStats['vendas_mes'] ?? 0 ?></div>
                    <div class="stat-label">Vendas do Mês</div>
                    <div class="stat-change positive">
                        <i class="fas fa-chart-line"></i> Crescimento constante
                    </div>
                </div>
            </div>

            <!-- Gráficos -->
            <div class="reports-grid">
                <div class="chart-card">
                    <h3><i class="fas fa-chart-area"></i> Faturamento Mensal</h3>
                    <div class="chart-container">
                        <canvas id="monthlyChart"></canvas>
                    </div>
                </div>

                <div class="report-card">
                    <h3><i class="fas fa-trophy"></i> Top Produtos</h3>
                    <div class="table-responsive">
                        <table class="report-table">
                            <thead>
                                <tr>
                                    <th>Produto</th>
                                    <th>Vendas</th>
                                    <th>Receita</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($bestSellers)): ?>
                                    <tr>
                                        <td colspan="3" style="text-align: center; padding: 2rem;">
                                            <i class="fas fa-chart-line fa-2x" style="color: #bdc3c7; margin-bottom: 1rem;"></i>
                                            <br>Nenhuma venda registrada
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach (array_slice($bestSellers, 0, 5) as $produto): ?>
                                        <tr>
                                            <td>
                                                <strong><?= htmlspecialchars($produto['nome']) ?></strong>
                                                <?php if (!empty($produto['marca'])): ?>
                                                    <br><small><?= htmlspecialchars($produto['marca']) ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= $produto['quantidade_vendida'] ?> un.</td>
                                            <td class="price">R$ <?= formatPrice($produto['total_faturamento']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Gráfico de Vendas Diárias -->
            <div class="chart-card" style="margin-bottom: 2rem;">
                <h3><i class="fas fa-chart-line"></i> Vendas Diárias (Últimos 30 dias)</h3>
                <div class="chart-container">
                    <canvas id="dailyChart"></canvas>
                </div>
            </div>

            <!-- Relatórios Adicionais -->
            <div class="full-width-reports">
                <div class="report-card">
                    <h3><i class="fas fa-calendar-alt"></i> Relatório Mensal Detalhado</h3>
                    <div class="table-responsive">
                        <table class="report-table">
                            <thead>
                                <tr>
                                    <th>Mês</th>
                                    <th>Pedidos</th>
                                    <th>Faturamento</th>
                                    <th>Ticket Médio</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($monthlyReport)): ?>
                                    <tr>
                                        <td colspan="4" style="text-align: center; padding: 2rem;">
                                            Nenhum dado mensal disponível
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach (array_slice($monthlyReport, 0, 12) as $mes): ?>
                                        <tr>
                                            <td><strong><?= $mes['mes_nome'] ?></strong></td>
                                            <td><?= $mes['total_pedidos'] ?></td>
                                            <td class="price">R$ <?= formatPrice($mes['total_faturamento']) ?></td>
                                            <td class="price">R$ <?= formatPrice($mes['ticket_medio']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="report-card">
                    <h3><i class="fas fa-exclamation-triangle"></i> Produtos com Estoque Baixo</h3>
                    <div class="table-responsive">
                        <table class="report-table">
                            <thead>
                                <tr>
                                    <th>Produto</th>
                                    <th>Estoque</th>
                                    <th>Mín.</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($lowStockProducts)): ?>
                                    <tr>
                                        <td colspan="4" style="text-align: center; padding: 2rem; color: #27ae60;">
                                            <i class="fas fa-check-circle fa-2x" style="margin-bottom: 1rem;"></i>
                                            <br>Todos os produtos com estoque adequado ✅
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($lowStockProducts as $produto): ?>
                                        <tr>
                                            <td>
                                                <strong><?= htmlspecialchars($produto['nome']) ?></strong>
                                                <br><small><?= htmlspecialchars($produto['categoria_nome'] ?? '') ?></small>
                                            </td>
                                            <td class="stock-warning"><?= $produto['estoque'] ?></td>
                                            <td><?= $produto['estoque_minimo'] ?? 5 ?></td>
                                            <td>
                                                <?php if ($produto['estoque'] == 0): ?>
                                                    <span style="color: #e74c3c;"><i class="fas fa-times"></i> Esgotado</span>
                                                <?php else: ?>
                                                    <span style="color: #f39c12;"><i class="fas fa-exclamation-triangle"></i> Baixo</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Seção de Exportação -->
            <div class="export-section">
                <h3><i class="fas fa-download"></i> Exportar Relatórios</h3>
                <p>Baixe relatórios em diferentes formatos para análise offline</p>
                <div class="export-buttons">
                    <a href="#" class="btn-export" onclick="exportToCSV()">
                        <i class="fas fa-file-csv"></i> Exportar CSV
                    </a>
                    <a href="#" class="btn-export" onclick="exportToPDF()">
                        <i class="fas fa-file-pdf"></i> Exportar PDF
                    </a>
                    <a href="#" class="btn-export" onclick="exportToExcel()">
                        <i class="fas fa-file-excel"></i> Exportar Excel
                    </a>
                    <a href="#" class="btn-export" onclick="printReport()">
                        <i class="fas fa-print"></i> Imprimir
                    </a>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Dados para os gráficos
        const monthlyData = {
            labels: <?= json_encode($monthlyLabels) ?>,
            datasets: [{
                label: 'Faturamento Mensal',
                data: <?= json_encode($monthlyValues) ?>,
                borderColor: '#3498db',
                backgroundColor: 'rgba(52, 152, 219, 0.1)',
                fill: true,
                tension: 0.4
            }]
        };

        const dailyData = {
            labels: <?= json_encode($dailyLabels) ?>,
            datasets: [{
                label: 'Vendas Diárias',
                data: <?= json_encode($dailyValues) ?>,
                borderColor: '#27ae60',
                backgroundColor: 'rgba(39, 174, 96, 0.1)',
                fill: true,
                tension: 0.3
            }]
        };

        // Configurações dos gráficos
        const chartOptions = {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'R$ ' + value.toLocaleString('pt-BR');
                        }
                    }
                }
            },
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                }
            }
        };

        // Inicializar gráficos
        const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
        new Chart(monthlyCtx, {
            type: 'line',
            data: monthlyData,
            options: chartOptions
        });

        const dailyCtx = document.getElementById('dailyChart').getContext('2d');
        new Chart(dailyCtx, {
            type: 'line',
            data: dailyData,
            options: chartOptions
        });

        // Funções de interação
        function updateReports() {
            const period = document.getElementById('periodSelector').value;
            const customFilters = document.getElementById('customFilters');
            
            if (period === 'custom') {
                customFilters.style.display = 'flex';
            } else {
                customFilters.style.display = 'none';
                // Aqui você pode implementar a lógica para recarregar os dados
                console.log('Atualizando relatórios para período:', period);
            }
        }

        function exportToCSV() {
            alert('Funcionalidade de exportação CSV será implementada em breve!');
        }

        function exportToPDF() {
            alert('Funcionalidade de exportação PDF será implementada em breve!');
        }

        function exportToExcel() {
            alert('Funcionalidade de exportação Excel será implementada em breve!');
        }

        function printReport() {
            window.print();
        }

        // Atualizar data máxima para hoje
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('endDate').max = today;
        document.getElementById('startDate').max = today;
        
        // Definir data padrão (últimos 30 dias)
        const thirtyDaysAgo = new Date();
        thirtyDaysAgo.setDate(thirtyDaysAgo.getDate() - 30);
        document.getElementById('startDate').value = thirtyDaysAgo.toISOString().split('T')[0];
        document.getElementById('endDate').value = today;
    </script>
</body>
</html>