<?php
/**
 * API REST para Produtos
 * InovaTech Store
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../classes/Product.php';
require_once __DIR__ . '/../config/database.php';

try {
    $product = new Product();
    $method = $_SERVER['REQUEST_METHOD'];
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Obter ID da URL se presente
    $id = null;
    if (isset($_GET['id']) && is_numeric($_GET['id'])) {
        $id = (int)$_GET['id'];
    }

    switch ($method) {
        case 'GET':
            if ($id) {
                // Buscar produto específico
                $productData = $product->readOne($id);
                
                if ($productData) {
                    // Buscar produtos relacionados
                    $related = $product->getRelatedProducts($id, $productData['categoria_id'], 4);
                    $productData['produtos_relacionados'] = $related;
                    
                    jsonResponse([
                        'success' => true,
                        'data' => $productData
                    ]);
                } else {
                    jsonResponse([
                        'success' => false,
                        'message' => 'Produto não encontrado'
                    ], 404);
                }
                
            } else {
                // Listar produtos com filtros
                $filters = [
                    'categoria_id' => $_GET['categoria'] ?? null,
                    'destaque' => isset($_GET['destaque']) ? true : null,
                    'busca' => $_GET['busca'] ?? null,
                    'preco_min' => $_GET['preco_min'] ?? null,
                    'preco_max' => $_GET['preco_max'] ?? null,
                    'order' => $_GET['order'] ?? 'nome',
                    'dir' => $_GET['dir'] ?? 'ASC',
                    'limit' => isset($_GET['limit']) ? (int)$_GET['limit'] : 12,
                    'offset' => isset($_GET['offset']) ? (int)$_GET['offset'] : 0
                ];

                // Remover filtros vazios
                $filters = array_filter($filters, function($value) {
                    return $value !== null && $value !== '';
                });

                $products = $product->readAll($filters);
                $total = $product->count($filters);

                // Calcular paginação
                $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                $perPage = $filters['limit'] ?? 12;
                $totalPages = ceil($total / $perPage);

                jsonResponse([
                    'success' => true,
                    'data' => $products,
                    'pagination' => [
                        'total' => $total,
                        'page' => $page,
                        'per_page' => $perPage,
                        'total_pages' => $totalPages,
                        'has_next' => $page < $totalPages,
                        'has_prev' => $page > 1
                    ]
                ]);
            }
            break;

        case 'POST':
            // Criar novo produto
            if (!$input) {
                jsonResponse([
                    'success' => false,
                    'message' => 'Dados não fornecidos'
                ], 400);
            }

            // Validar dados
            $validation_errors = $product->validate($input);
            if (!empty($validation_errors)) {
                jsonResponse([
                    'success' => false,
                    'message' => 'Dados inválidos',
                    'errors' => $validation_errors
                ], 400);
            }

            // Definir propriedades
            $product->nome = $input['nome'];
            $product->descricao = $input['descricao'] ?? '';
            $product->especificacoes = $input['especificacoes'] ?? '';
            $product->preco = $input['preco'];
            $product->preco_promocional = !empty($input['preco_promocional']) ? $input['preco_promocional'] : null;
            $product->categoria_id = $input['categoria_id'];
            $product->imagem_principal = $input['imagem_principal'] ?? '';
            $product->imagens_adicionais = json_encode($input['imagens_adicionais'] ?? []);
            $product->estoque = $input['estoque'] ?? 0;
            $product->estoque_minimo = $input['estoque_minimo'] ?? 5;
            $product->marca = $input['marca'] ?? '';
            $product->modelo = $input['modelo'] ?? '';
            $product->peso = !empty($input['peso']) ? $input['peso'] : null;
            $product->dimensoes = $input['dimensoes'] ?? '';
            $product->garantia_meses = $input['garantia_meses'] ?? 12;
            $product->destaque = isset($input['destaque']) ? (bool)$input['destaque'] : false;

            if ($product->create()) {
                jsonResponse([
                    'success' => true,
                    'message' => 'Produto criado com sucesso',
                    'data' => ['id' => $product->id]
                ], 201);
            } else {
                jsonResponse([
                    'success' => false,
                    'message' => 'Erro ao criar produto'
                ], 500);
            }
            break;

        case 'PUT':
            // Atualizar produto
            if (!$id) {
                jsonResponse([
                    'success' => false,
                    'message' => 'ID do produto não fornecido'
                ], 400);
            }

            if (!$input) {
                jsonResponse([
                    'success' => false,
                    'message' => 'Dados não fornecidos'
                ], 400);
            }

            // Verificar se produto existe
            $existingProduct = $product->readOne($id);
            if (!$existingProduct) {
                jsonResponse([
                    'success' => false,
                    'message' => 'Produto não encontrado'
                ], 404);
            }

            // Validar dados
            $validation_errors = $product->validate($input);
            if (!empty($validation_errors)) {
                jsonResponse([
                    'success' => false,
                    'message' => 'Dados inválidos',
                    'errors' => $validation_errors
                ], 400);
            }

            // Definir propriedades
            $product->id = $id;
            $product->nome = $input['nome'];
            $product->descricao = $input['descricao'] ?? '';
            $product->especificacoes = $input['especificacoes'] ?? '';
            $product->preco = $input['preco'];
            $product->preco_promocional = !empty($input['preco_promocional']) ? $input['preco_promocional'] : null;
            $product->categoria_id = $input['categoria_id'];
            $product->imagem_principal = $input['imagem_principal'] ?? '';
            $product->imagens_adicionais = json_encode($input['imagens_adicionais'] ?? []);
            $product->estoque = $input['estoque'] ?? 0;
            $product->estoque_minimo = $input['estoque_minimo'] ?? 5;
            $product->marca = $input['marca'] ?? '';
            $product->modelo = $input['modelo'] ?? '';
            $product->peso = !empty($input['peso']) ? $input['peso'] : null;
            $product->dimensoes = $input['dimensoes'] ?? '';
            $product->garantia_meses = $input['garantia_meses'] ?? 12;
            $product->destaque = isset($input['destaque']) ? (bool)$input['destaque'] : false;
            $product->ativo = isset($input['ativo']) ? (bool)$input['ativo'] : true;

            if ($product->update()) {
                jsonResponse([
                    'success' => true,
                    'message' => 'Produto atualizado com sucesso'
                ]);
            } else {
                jsonResponse([
                    'success' => false,
                    'message' => 'Erro ao atualizar produto'
                ], 500);
            }
            break;

        case 'DELETE':
            // Excluir produto (soft delete)
            if (!$id) {
                jsonResponse([
                    'success' => false,
                    'message' => 'ID do produto não fornecido'
                ], 400);
            }

            // Verificar se produto existe
            $existingProduct = $product->readOne($id);
            if (!$existingProduct) {
                jsonResponse([
                    'success' => false,
                    'message' => 'Produto não encontrado'
                ], 404);
            }

            if ($product->delete($id)) {
                jsonResponse([
                    'success' => true,
                    'message' => 'Produto excluído com sucesso'
                ]);
            } else {
                jsonResponse([
                    'success' => false,
                    'message' => 'Erro ao excluir produto'
                ], 500);
            }
            break;

        default:
            jsonResponse([
                'success' => false,
                'message' => 'Método não permitido'
            ], 405);
            break;
    }

} catch (Exception $e) {
    logError('Erro na API de produtos: ' . $e->getMessage());
    
    jsonResponse([
        'success' => false,
        'message' => 'Erro interno do servidor'
    ], 500);
}
?>