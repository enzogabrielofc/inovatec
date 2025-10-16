<?php
/**
 * Teste de Conexão com Banco de Dados
 * InovaTech Store
 */

echo "<h2>🔧 Teste de Conexão - InovaTech Store</h2>";

try {
    // Tentar incluir o arquivo de configuração
    require_once 'config/database.php';
    echo "✅ Arquivo de configuração carregado<br>";
    
    // Tentar conectar ao banco
    $database = new Database();
    $conn = $database->getConnection();
    echo "✅ Conexão com MySQL estabelecida<br>";
    
    // Verificar se o banco existe
    $stmt = $conn->query("SELECT DATABASE() as current_db");
    $result = $stmt->fetch();
    echo "✅ Banco atual: " . $result['current_db'] . "<br>";
    
    // Verificar se as tabelas existem
    $tables = ['produtos', 'categorias', 'usuarios', 'pedidos'];
    echo "<br><strong>📊 Verificando tabelas:</strong><br>";
    
    foreach ($tables as $table) {
        try {
            $stmt = $conn->query("SELECT COUNT(*) as total FROM $table");
            $result = $stmt->fetch();
            echo "✅ Tabela '$table': " . $result['total'] . " registros<br>";
        } catch (Exception $e) {
            echo "❌ Tabela '$table': NÃO EXISTE<br>";
        }
    }
    
    echo "<br><strong>🎯 Status:</strong><br>";
    echo "✅ Sistema pronto para usar!<br>";
    echo "<br><a href='index.php' style='background: #667eea; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🛒 Ir para a Loja</a>";
    
} catch (Exception $e) {
    echo "❌ <strong>ERRO:</strong> " . $e->getMessage() . "<br><br>";
    
    echo "<strong>🛠️ Soluções possíveis:</strong><br>";
    echo "1. Certifique-se que o XAMPP está rodando (Apache + MySQL)<br>";
    echo "2. Importe o arquivo database.sql no phpMyAdmin<br>";
    echo "3. Verifique as configurações em config/database.php<br>";
    echo "<br><a href='http://localhost/phpmyadmin' target='_blank' style='background: #e74c3c; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🗄️ Abrir phpMyAdmin</a>";
}
?>

<style>
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    margin: 40px;
    background: #f8f9fa;
}

h2 {
    color: #2c3e50;
    border-bottom: 2px solid #3498db;
    padding-bottom: 10px;
}
</style>