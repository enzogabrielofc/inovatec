<?php
/**
 * Classe Category - Gerenciamento de Categorias
 * InovaTech Store
 */

require_once __DIR__ . '/../config/database.php';

class Category {
    private $conn;
    private $table = 'categorias';

    // Propriedades da categoria
    public $id;
    public $nome;
    public $descricao;
    public $icone;
    public $ordem;
    public $ativo;

    /**
     * Construtor
     */
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    /**
     * Listar todas as categorias ativas
     */
    public function readAll($activeOnly = true) {
        $query = "SELECT * FROM " . $this->table;
        
        if ($activeOnly) {
            $query .= " WHERE ativo = 1";
        }
        
        $query .= " ORDER BY ordem ASC, nome ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Buscar categoria por ID
     */
    public function readOne($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        return $stmt->fetch();
    }

    /**
     * Criar nova categoria
     */
    public function create() {
        $query = "INSERT INTO " . $this->table . " 
                  SET nome = :nome, 
                      descricao = :descricao,
                      icone = :icone,
                      ordem = :ordem";

        $stmt = $this->conn->prepare($query);

        // Sanitizar dados
        $this->nome = sanitize($this->nome);
        $this->descricao = sanitize($this->descricao);
        $this->icone = sanitize($this->icone);

        // Bind dados
        $stmt->bindParam(':nome', $this->nome);
        $stmt->bindParam(':descricao', $this->descricao);
        $stmt->bindParam(':icone', $this->icone);
        $stmt->bindParam(':ordem', $this->ordem);

        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }

        return false;
    }

    /**
     * Atualizar categoria
     */
    public function update() {
        $query = "UPDATE " . $this->table . " 
                  SET nome = :nome,
                      descricao = :descricao,
                      icone = :icone,
                      ordem = :ordem,
                      ativo = :ativo
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        // Sanitizar dados
        $this->nome = sanitize($this->nome);
        $this->descricao = sanitize($this->descricao);
        $this->icone = sanitize($this->icone);

        // Bind dados
        $stmt->bindParam(':id', $this->id);
        $stmt->bindParam(':nome', $this->nome);
        $stmt->bindParam(':descricao', $this->descricao);
        $stmt->bindParam(':icone', $this->icone);
        $stmt->bindParam(':ordem', $this->ordem);
        $stmt->bindParam(':ativo', $this->ativo, PDO::PARAM_BOOL);

        return $stmt->execute();
    }

    /**
     * Excluir categoria (soft delete)
     */
    public function delete($id) {
        $query = "UPDATE " . $this->table . " SET ativo = 0 WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);

        return $stmt->execute();
    }

    /**
     * Contar produtos por categoria
     */
    public function countProducts($categoryId = null) {
        if ($categoryId) {
            $query = "SELECT COUNT(*) as total FROM produtos WHERE categoria_id = :categoria_id AND ativo = 1";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':categoria_id', $categoryId);
        } else {
            $query = "SELECT 
                        c.id, 
                        c.nome, 
                        COUNT(p.id) as total_produtos 
                      FROM " . $this->table . " c 
                      LEFT JOIN produtos p ON c.id = p.categoria_id AND p.ativo = 1 
                      WHERE c.ativo = 1 
                      GROUP BY c.id, c.nome 
                      ORDER BY c.ordem ASC, c.nome ASC";
            $stmt = $this->conn->prepare($query);
        }

        $stmt->execute();

        if ($categoryId) {
            $result = $stmt->fetch();
            return $result['total'];
        } else {
            return $stmt->fetchAll();
        }
    }

    /**
     * Validar dados da categoria
     */
    public function validate($data) {
        $errors = [];

        if (empty($data['nome'])) {
            $errors[] = 'Nome é obrigatório';
        } elseif (strlen($data['nome']) < 2) {
            $errors[] = 'Nome deve ter pelo menos 2 caracteres';
        }

        if (!empty($data['ordem']) && !is_numeric($data['ordem'])) {
            $errors[] = 'Ordem deve ser um número';
        }

        return $errors;
    }
}
?>