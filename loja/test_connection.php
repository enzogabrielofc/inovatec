<?php
echo "<h2>Teste de Conexão - InovaTech Store</h2>";

try {
    // Teste 1: Carregar classes
    echo "<h3>1. Testando carregamento das classes...</h3>";
    
    require_once 'classes/Product.php';
    echo "✅ Classe Product carregada com sucesso<br>";
    
    require_once 'classes/Category.php'; 
    echo "✅ Classe Category carregada com sucesso<br>";
    
    require_once 'classes/Order.php';
    echo "✅ Classe Order carregada com sucesso<br>";
    
    // Teste 2: Instanciar classes
    echo "<h3>2. Testando instanciação das classes...</h3>";
    
    $product = new Product();
    echo "✅ Instância Product criada com sucesso<br>";
    
    $category = new Category();
    echo "✅ Instância Category criada com sucesso<br>";
    
    $order = new Order();
    echo "✅ Instância Order criada com sucesso<br>";
    
    // Teste 3: Testar métodos básicos
    echo "<h3>3. Testando métodos básicos...</h3>";
    
    $stats = $product->getStats();
    echo "✅ Estatísticas de produtos obtidas: " . $stats['total_produtos'] . " produtos<br>";
    
    $categorias = $category->readAll();
    echo "✅ Categorias carregadas: " . count($categorias) . " categorias encontradas<br>";
    
    echo "<h3>🎉 Todos os testes passaram! Sistema funcionando corretamente.</h3>";
    echo "<p><a href='admin-dashboard.php'>Ir para Dashboard Admin</a></p>";
    echo "<p><a href='index.php'>Ir para Loja</a></p>";
    
} catch (Exception $e) {
    echo "<h3>❌ Erro encontrado:</h3>";
    echo "<p style='color: red; background: #fee; padding: 10px; border: 1px solid #fcc;'>";
    echo "<strong>Erro:</strong> " . $e->getMessage() . "<br>";
    echo "<strong>Arquivo:</strong> " . $e->getFile() . "<br>";
    echo "<strong>Linha:</strong> " . $e->getLine();
    echo "</p>";
    
    echo "<h4>Possíveis soluções:</h4>";
    echo "<ul>";
    echo "<li>Verifique se o MySQL está rodando no XAMPP</li>";
    echo "<li>Execute o script database.sql no phpMyAdmin</li>";
    echo "<li>Verifique as configurações de conexão em config/database.php</li>";
    echo "</ul>";
}
?>