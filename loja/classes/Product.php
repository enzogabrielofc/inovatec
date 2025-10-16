<?php
/**
 * Classe Product - Gerenciamento de Produtos
 * InovaTech Store
 */

require_once __DIR__ . '/../config/database.php';

class Product {
    private $conn;
    private $table = 'produtos';

    // Propriedades do produto
    public $id;
    public $nome;
    public $descricao;
    public $especificacoes;
    public $preco;
    public $preco_promocional;
    public $categoria_id;
    public $imagem_principal;
    public $imagens_adicionais;
    public $estoque;
    public $estoque_minimo;
    public $marca;
    public $modelo;
    public $peso;
    public $dimensoes;
    public $garantia_meses;
    public $destaque;
    public $ativo;

    /**
     * Construtor
     */
    public function __construct($db = null) {
        if ($db === null) {
            $database = new Database();
            $this->conn = $database->getConnection();
        } else {
            $this->conn = $db;
        }
    }

    /**
     * Listar todos os produtos
     */
    public function readAll($filters = []) {
        $query = "SELECT 
                    p.*, 
                    c.nome as categoria_nome,
                    c.icone as categoria_icone
                  FROM " . $this->table . " p 
                  LEFT JOIN categorias c ON p.categoria_id = c.id 
                  WHERE p.ativo = 1";

        $params = [];

        // Aplicar filtros
        if (!empty($filters['categoria_id'])) {
            $query .= " AND p.categoria_id = :categoria_id";
            $params[':categoria_id'] = $filters['categoria_id'];
        }

        if (!empty($filters['destaque'])) {
            $query .= " AND p.destaque = 1";
        }

        if (!empty($filters['busca'])) {
            $query .= " AND (p.nome LIKE :busca OR p.descricao LIKE :busca OR p.especificacoes LIKE :busca)";
            $params[':busca'] = '%' . $filters['busca'] . '%';
        }

        if (!empty($filters['preco_min'])) {
            $query .= " AND p.preco >= :preco_min";
            $params[':preco_min'] = $filters['preco_min'];
        }

        if (!empty($filters['preco_max'])) {
            $query .= " AND p.preco <= :preco_max";
            $params[':preco_max'] = $filters['preco_max'];
        }

        // Ordenação
        $orderBy = !empty($filters['order']) ? $filters['order'] : 'p.nome';
        $orderDir = !empty($filters['dir']) && strtoupper($filters['dir']) === 'DESC' ? 'DESC' : 'ASC';
        $query .= " ORDER BY $orderBy $orderDir";

        // Paginação
        if (!empty($filters['limit'])) {
            $offset = !empty($filters['offset']) ? (int)$filters['offset'] : 0;
            $query .= " LIMIT :offset, :limit";
            $params[':offset'] = $offset;
            $params[':limit'] = (int)$filters['limit'];
        }

        $stmt = $this->conn->prepare($query);
        
        // Bind parameters
        foreach ($params as $key => $value) {
            if ($key === ':offset' || $key === ':limit') {
                $stmt->bindValue($key, $value, PDO::PARAM_INT);
            } else {
                $stmt->bindValue($key, $value);
            }
        }

        $stmt->execute();
        
        $products = [];
        while ($row = $stmt->fetch()) {
            $row['preco_formatado'] = 'R$ ' . formatPrice($row['preco']);
            if ($row['preco_promocional']) {
                $row['preco_promocional_formatado'] = 'R$ ' . formatPrice($row['preco_promocional']);
                $row['desconto_percentual'] = round((($row['preco'] - $row['preco_promocional']) / $row['preco']) * 100);
            }
            $row['imagens_adicionais'] = json_decode($row['imagens_adicionais'], true) ?: [];
            $row['data_criacao_formatada'] = formatDate($row['data_criacao']);
            $products[] = $row;
        }

        return $products;
    }

    /**
     * Buscar produto por ID
     */
    public function readOne($id) {
        $query = "SELECT 
                    p.*, 
                    c.nome as categoria_nome,
                    c.icone as categoria_icone
                  FROM " . $this->table . " p 
                  LEFT JOIN categorias c ON p.categoria_id = c.id 
                  WHERE p.id = :id AND p.ativo = 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        $row = $stmt->fetch();
        
        if ($row) {
            $row['preco_formatado'] = 'R$ ' . formatPrice($row['preco']);
            if ($row['preco_promocional']) {
                $row['preco_promocional_formatado'] = 'R$ ' . formatPrice($row['preco_promocional']);
                $row['desconto_percentual'] = round((($row['preco'] - $row['preco_promocional']) / $row['preco']) * 100);
            }
            $row['imagens_adicionais'] = json_decode($row['imagens_adicionais'], true) ?: [];
            $row['especificacoes_array'] = explode('\n', $row['especificacoes']);
            $row['data_criacao_formatada'] = formatDate($row['data_criacao']);
            return $row;
        }

        return false;
    }

    /**
     * Criar novo produto
     */
    public function create() {
        $query = "INSERT INTO " . $this->table . " 
                  SET nome = :nome, 
                      descricao = :descricao,
                      especificacoes = :especificacoes,
                      preco = :preco,
                      preco_promocional = :preco_promocional,
                      categoria_id = :categoria_id,
                      imagem_principal = :imagem_principal,
                      imagens_adicionais = :imagens_adicionais,
                      estoque = :estoque,
                      estoque_minimo = :estoque_minimo,
                      marca = :marca,
                      modelo = :modelo,
                      peso = :peso,
                      dimensoes = :dimensoes,
                      garantia_meses = :garantia_meses,
                      destaque = :destaque";

        $stmt = $this->conn->prepare($query);

        // Sanitizar dados
        $this->nome = sanitize($this->nome);
        $this->descricao = sanitize($this->descricao);
        $this->especificacoes = sanitize($this->especificacoes);
        $this->marca = sanitize($this->marca);
        $this->modelo = sanitize($this->modelo);
        $this->dimensoes = sanitize($this->dimensoes);

        // Bind dados
        $stmt->bindParam(':nome', $this->nome);
        $stmt->bindParam(':descricao', $this->descricao);
        $stmt->bindParam(':especificacoes', $this->especificacoes);
        $stmt->bindParam(':preco', $this->preco);
        $stmt->bindParam(':preco_promocional', $this->preco_promocional);
        $stmt->bindParam(':categoria_id', $this->categoria_id);
        $stmt->bindParam(':imagem_principal', $this->imagem_principal);
        $stmt->bindParam(':imagens_adicionais', $this->imagens_adicionais);
        $stmt->bindParam(':estoque', $this->estoque);
        $stmt->bindParam(':estoque_minimo', $this->estoque_minimo);
        $stmt->bindParam(':marca', $this->marca);
        $stmt->bindParam(':modelo', $this->modelo);
        $stmt->bindParam(':peso', $this->peso);
        $stmt->bindParam(':dimensoes', $this->dimensoes);
        $stmt->bindParam(':garantia_meses', $this->garantia_meses);
        $stmt->bindParam(':destaque', $this->destaque, PDO::PARAM_BOOL);

        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }

        logError("Erro ao criar produto: " . implode(" | ", $stmt->errorInfo()));
        return false;
    }

    /**
     * Atualizar produto
     */
    public function update() {
        $query = "UPDATE " . $this->table . " 
                  SET nome = :nome,
                      descricao = :descricao,
                      especificacoes = :especificacoes,
                      preco = :preco,
                      preco_promocional = :preco_promocional,
                      categoria_id = :categoria_id,
                      imagem_principal = :imagem_principal,
                      imagens_adicionais = :imagens_adicionais,
                      estoque = :estoque,
                      estoque_minimo = :estoque_minimo,
                      marca = :marca,
                      modelo = :modelo,
                      peso = :peso,
                      dimensoes = :dimensoes,
                      garantia_meses = :garantia_meses,
                      destaque = :destaque,
                      ativo = :ativo
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        // Sanitizar dados
        $this->nome = sanitize($this->nome);
        $this->descricao = sanitize($this->descricao);
        $this->especificacoes = sanitize($this->especificacoes);
        $this->marca = sanitize($this->marca);
        $this->modelo = sanitize($this->modelo);
        $this->dimensoes = sanitize($this->dimensoes);

        // Bind dados
        $stmt->bindParam(':id', $this->id);
        $stmt->bindParam(':nome', $this->nome);
        $stmt->bindParam(':descricao', $this->descricao);
        $stmt->bindParam(':especificacoes', $this->especificacoes);
        $stmt->bindParam(':preco', $this->preco);
        $stmt->bindParam(':preco_promocional', $this->preco_promocional);
        $stmt->bindParam(':categoria_id', $this->categoria_id);
        $stmt->bindParam(':imagem_principal', $this->imagem_principal);
        $stmt->bindParam(':imagens_adicionais', $this->imagens_adicionais);
        $stmt->bindParam(':estoque', $this->estoque);
        $stmt->bindParam(':estoque_minimo', $this->estoque_minimo);
        $stmt->bindParam(':marca', $this->marca);
        $stmt->bindParam(':modelo', $this->modelo);
        $stmt->bindParam(':peso', $this->peso);
        $stmt->bindParam(':dimensoes', $this->dimensoes);
        $stmt->bindParam(':garantia_meses', $this->garantia_meses);
        $stmt->bindParam(':destaque', $this->destaque, PDO::PARAM_BOOL);
        $stmt->bindParam(':ativo', $this->ativo, PDO::PARAM_BOOL);

        if ($stmt->execute()) {
            return true;
        }

        logError("Erro ao atualizar produto: " . implode(" | ", $stmt->errorInfo()));
        return false;
    }

    /**
     * Excluir produto (soft delete)
     */
    public function delete($id) {
        $query = "UPDATE " . $this->table . " SET ativo = 0 WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);

        if ($stmt->execute()) {
            return true;
        }

        return false;
    }

    /**
     * Atualizar estoque
     */
    public function updateStock($id, $quantity, $operation = 'subtract') {
        $operator = $operation === 'add' ? '+' : '-';
        
        $query = "UPDATE " . $this->table . " 
                  SET estoque = estoque $operator :quantity 
                  WHERE id = :id AND estoque >= :min_quantity";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':quantity', $quantity);
        
        if ($operation === 'subtract') {
            $stmt->bindParam(':min_quantity', $quantity);
        } else {
            $stmt->bindValue(':min_quantity', 0);
        }

        return $stmt->execute() && $stmt->rowCount() > 0;
    }

    /**
     * Verificar disponibilidade em estoque
     */
    public function checkStock($id, $quantity = 1) {
        $query = "SELECT estoque FROM " . $this->table . " WHERE id = :id AND ativo = 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        $result = $stmt->fetch();
        
        if ($result) {
            return $result['estoque'] >= $quantity;
        }

        return false;
    }

    /**
     * Produtos com estoque baixo
     */
    public function getLowStockProducts() {
        $query = "SELECT p.*, c.nome as categoria_nome 
                  FROM " . $this->table . " p 
                  LEFT JOIN categorias c ON p.categoria_id = c.id 
                  WHERE p.ativo = 1 
                  AND p.estoque <= p.estoque_minimo 
                  ORDER BY p.estoque ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        $products = [];
        while ($row = $stmt->fetch()) {
            $row['preco_formatado'] = 'R$ ' . formatPrice($row['preco']);
            $products[] = $row;
        }

        return $products;
    }

    /**
     * Produtos relacionados (mesma categoria)
     */
    public function getRelatedProducts($id, $categoria_id, $limit = 4) {
        $query = "SELECT p.*, c.nome as categoria_nome 
                  FROM " . $this->table . " p 
                  LEFT JOIN categorias c ON p.categoria_id = c.id 
                  WHERE p.ativo = 1 
                  AND p.categoria_id = :categoria_id 
                  AND p.id != :id 
                  ORDER BY RAND() 
                  LIMIT :limit";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':categoria_id', $categoria_id);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        $products = [];
        while ($row = $stmt->fetch()) {
            $row['preco_formatado'] = 'R$ ' . formatPrice($row['preco']);
            if ($row['preco_promocional']) {
                $row['preco_promocional_formatado'] = 'R$ ' . formatPrice($row['preco_promocional']);
            }
            $row['imagens_adicionais'] = json_decode($row['imagens_adicionais'], true) ?: [];
            $products[] = $row;
        }

        return $products;
    }

    /**
     * Contar total de produtos
     */
    public function count($filters = []) {
        $query = "SELECT COUNT(*) as total FROM " . $this->table . " p WHERE p.ativo = 1";
        $params = [];

        if (!empty($filters['categoria_id'])) {
            $query .= " AND p.categoria_id = :categoria_id";
            $params[':categoria_id'] = $filters['categoria_id'];
        }

        if (!empty($filters['busca'])) {
            $query .= " AND (p.nome LIKE :busca OR p.descricao LIKE :busca)";
            $params[':busca'] = '%' . $filters['busca'] . '%';
        }

        $stmt = $this->conn->prepare($query);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->execute();
        $result = $stmt->fetch();

        return $result['total'];
    }

    /**
     * Estatísticas de produtos
     */
    public function getStats() {
        $query = "SELECT 
                    COUNT(*) as total_produtos,
                    COUNT(CASE WHEN destaque = 1 THEN 1 END) as produtos_destaque,
                    COUNT(CASE WHEN estoque <= estoque_minimo THEN 1 END) as estoque_baixo,
                    AVG(preco) as preco_medio
                  FROM " . $this->table . " 
                  WHERE ativo = 1";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        $result = $stmt->fetch();
        $result['preco_medio'] = $result['preco_medio'] ? formatPrice($result['preco_medio']) : '0,00';

        return $result;
    }

    /**
     * Validar dados do produto
     */
    public function validate($data) {
        $errors = [];

        if (empty($data['nome'])) {
            $errors[] = 'Nome é obrigatório';
        } elseif (strlen($data['nome']) < 3) {
            $errors[] = 'Nome deve ter pelo menos 3 caracteres';
        }

        if (empty($data['preco']) || !is_numeric($data['preco']) || $data['preco'] <= 0) {
            $errors[] = 'Preço deve ser um valor válido maior que zero';
        }

        if (!empty($data['preco_promocional']) && (!is_numeric($data['preco_promocional']) || $data['preco_promocional'] <= 0)) {
            $errors[] = 'Preço promocional deve ser um valor válido maior que zero';
        }

        if (!empty($data['preco_promocional']) && $data['preco_promocional'] >= $data['preco']) {
            $errors[] = 'Preço promocional deve ser menor que o preço normal';
        }

        if (empty($data['categoria_id']) || !is_numeric($data['categoria_id'])) {
            $errors[] = 'Categoria é obrigatória';
        }

        if (!empty($data['estoque']) && (!is_numeric($data['estoque']) || $data['estoque'] < 0)) {
            $errors[] = 'Estoque deve ser um número não negativo';
        }

        return $errors;
    }
}
?>