<?php
require_once __DIR__ . '/../config/database.php';

/**
 * Classe Order - Gerencia pedidos/vendas
 */
class Order {
    private $conn;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    /**
     * Cria novo pedido
     */
    public function create($dadosPedido, $itens) {
        try {
            $this->conn->beginTransaction();
            
            // Gera código único do pedido
            $codigoPedido = $this->generateOrderCode();
            
            // Insere pedido principal
            $sqlPedido = "INSERT INTO pedidos (codigo_pedido, nome_cliente, email_cliente, 
                          telefone_cliente, endereco_entrega, subtotal, desconto, frete, total, 
                          status_pedido, forma_pagamento, observacoes) 
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->conn->prepare($sqlPedido);
            $stmt->execute([
                $codigoPedido,
                $dadosPedido['nome_cliente'],
                $dadosPedido['email_cliente'],
                $dadosPedido['telefone_cliente'] ?? null,
                $dadosPedido['endereco_entrega'],
                $dadosPedido['subtotal'],
                $dadosPedido['desconto'] ?? 0,
                $dadosPedido['frete'] ?? 0,
                $dadosPedido['total'],
                $dadosPedido['status_pedido'] ?? 'pendente',
                $dadosPedido['forma_pagamento'] ?? null,
                $dadosPedido['observacoes'] ?? null
            ]);
            
            $pedidoId = $this->conn->lastInsertId();
            
            // Insere itens do pedido
            $sqlItem = "INSERT INTO itens_pedido (pedido_id, produto_id, nome_produto, 
                        preco_unitario, quantidade, subtotal) VALUES (?, ?, ?, ?, ?, ?)";
            
            foreach ($itens as $item) {
                $stmt = $this->conn->prepare($sqlItem);
                $stmt->execute([
                    $pedidoId,
                    $item['produto_id'],
                    $item['nome_produto'],
                    $item['preco_unitario'],
                    $item['quantidade'],
                    $item['subtotal']
                ]);
            }
            
            $this->conn->commit();
            return $pedidoId;
            
        } catch (Exception $e) {
            $this->conn->rollback();
            throw new Exception("Erro ao criar pedido: " . $e->getMessage());
        }
    }
    
    /**
     * Lê pedido por ID
     */
    public function read($id) {
        $sql = "SELECT * FROM pedidos WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);
        $pedido = $stmt->fetch();
        
        if ($pedido) {
            // Busca itens do pedido
            $sqlItens = "SELECT ip.*, p.imagem_principal 
                         FROM itens_pedido ip 
                         LEFT JOIN produtos p ON ip.produto_id = p.id 
                         WHERE ip.pedido_id = ?";
            $stmtItens = $this->conn->prepare($sqlItens);
            $stmtItens->execute([$id]);
            $pedido['itens'] = $stmtItens->fetchAll();
        }
        
        return $pedido;
    }
    
    /**
     * Lista pedidos com filtros
     */
    public function readAll($filters = []) {
        $sql = "SELECT p.*, 
                COUNT(ip.id) as total_itens 
                FROM pedidos p 
                LEFT JOIN itens_pedido ip ON p.id = ip.pedido_id 
                WHERE 1=1";
        
        $params = [];
        
        // Filtros
        if (!empty($filters['status'])) {
            $sql .= " AND p.status_pedido = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['email'])) {
            $sql .= " AND p.email_cliente LIKE ?";
            $params[] = '%' . $filters['email'] . '%';
        }
        
        if (!empty($filters['data_inicio'])) {
            $sql .= " AND p.data_pedido >= ?";
            $params[] = $filters['data_inicio'];
        }
        
        if (!empty($filters['data_fim'])) {
            $sql .= " AND p.data_pedido <= ?";
            $params[] = $filters['data_fim'] . ' 23:59:59';
        }
        
        $sql .= " GROUP BY p.id";
        
        // Ordenação
        $order = $filters['order'] ?? 'p.data_pedido';
        $dir = $filters['dir'] ?? 'DESC';
        $sql .= " ORDER BY " . $order . " " . $dir;
        
        // Paginação
        if (!empty($filters['limit'])) {
            $sql .= " LIMIT ?";
            $params[] = (int)$filters['limit'];
            
            if (!empty($filters['offset'])) {
                $sql .= " OFFSET ?";
                $params[] = (int)$filters['offset'];
            }
        }
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Atualiza status do pedido
     */
    public function updateStatus($id, $status) {
        $sql = "UPDATE pedidos SET status_pedido = ?, data_atualizacao = CURRENT_TIMESTAMP WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$status, $id]);
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Estatísticas de pedidos
     */
    public function getStats() {
        $sql = "SELECT 
                COUNT(*) as total_pedidos,
                COUNT(CASE WHEN status_pedido = 'pendente' THEN 1 END) as pedidos_pendentes,
                COUNT(CASE WHEN status_pedido IN ('confirmado', 'preparando', 'enviado', 'entregue') THEN 1 END) as pedidos_confirmados,
                SUM(CASE WHEN status_pedido IN ('confirmado', 'preparando', 'enviado', 'entregue') THEN total ELSE 0 END) as faturamento_total,
                AVG(CASE WHEN status_pedido IN ('confirmado', 'preparando', 'enviado', 'entregue') THEN total ELSE NULL END) as ticket_medio
                FROM pedidos";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetch();
    }
    
    /**
     * Vendas por período
     */
    public function getSalesByPeriod($inicio, $fim) {
        $sql = "SELECT 
                DATE(data_pedido) as data,
                COUNT(*) as total_pedidos,
                SUM(total) as faturamento_dia
                FROM pedidos 
                WHERE status_pedido IN ('confirmado', 'preparando', 'enviado', 'entregue')
                AND data_pedido BETWEEN ? AND ?
                GROUP BY DATE(data_pedido)
                ORDER BY data DESC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$inicio, $fim . ' 23:59:59']);
        return $stmt->fetchAll();
    }
    
    /**
     * Gera código único do pedido
     */
    private function generateOrderCode() {
        $year = date('Y');
        $sql = "SELECT COUNT(*) as total FROM pedidos WHERE YEAR(data_pedido) = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$year]);
        $result = $stmt->fetch();
        
        $sequence = str_pad($result['total'] + 1, 6, '0', STR_PAD_LEFT);
        return "PED{$year}{$sequence}";
    }
    
    /**
     * Confirma pedido e atualiza estoque
     */
    public function confirmar($id) {
        try {
            $this->conn->beginTransaction();
            
            // Busca itens do pedido
            $pedido = $this->read($id);
            if (!$pedido) {
                throw new Exception("Pedido não encontrado");
            }
            
            // Verifica e atualiza estoque
            foreach ($pedido['itens'] as $item) {
                $sqlStock = "UPDATE produtos SET estoque = estoque - ? WHERE id = ? AND estoque >= ?";
                $stmt = $this->conn->prepare($sqlStock);
                $stmt->execute([
                    $item['quantidade'],
                    $item['produto_id'],
                    $item['quantidade']
                ]);
                
                if ($stmt->rowCount() === 0) {
                    throw new Exception("Estoque insuficiente para o produto: " . $item['nome_produto']);
                }
            }
            
            // Atualiza status do pedido
            $this->updateStatus($id, 'confirmado');
            
            $this->conn->commit();
            return true;
            
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }
    
    /**
     * Cancela pedido e restaura estoque
     */
    public function cancelar($id) {
        try {
            $this->conn->beginTransaction();
            
            $pedido = $this->read($id);
            if (!$pedido) {
                throw new Exception("Pedido não encontrado");
            }
            
            // Se pedido estava confirmado, restaura estoque
            if ($pedido['status_pedido'] === 'confirmado') {
                foreach ($pedido['itens'] as $item) {
                    $sqlStock = "UPDATE produtos SET estoque = estoque + ? WHERE id = ?";
                    $stmt = $this->conn->prepare($sqlStock);
                    $stmt->execute([$item['quantidade'], $item['produto_id']]);
                }
            }
            
            $this->updateStatus($id, 'cancelado');
            
            $this->conn->commit();
            return true;
            
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }
}