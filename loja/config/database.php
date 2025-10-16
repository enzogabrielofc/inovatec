<?php

class Database {
    private $host = 'localhost';
    private $db_name = 'loja2.0';
    private $username = 'root';
    private $password = '';
    private $charset = 'utf8mb4';
    public $connection;

    /**
     * Conectar ao banco de dados
     */
    public function getConnection() {
        $this->connection = null;
        
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->db_name};charset={$this->charset}";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
            ];
            
            $this->connection = new PDO($dsn, $this->username, $this->password, $options);
            
        } catch(PDOException $exception) {
            error_log("Erro de conexão: " . $exception->getMessage());
            throw new Exception("Erro na conexão com o banco de dados");
        }

        return $this->connection;
    }

    /**
     * Fechar conexão
     */
    public function closeConnection() {
        $this->connection = null;
    }

    /**
     * Iniciar transação
     */
    public function beginTransaction() {
        return $this->connection->beginTransaction();
    }

    /**
     * Confirmar transação
     */
    public function commit() {
        return $this->connection->commit();
    }

    /**
     * Reverter transação
     */
    public function rollback() {
        return $this->connection->rollback();
    }

    /**
     * Obter último ID inserido
     */
    public function lastInsertId() {
        return $this->connection->lastInsertId();
    }
}

/**
 * Função helper para obter conexão
 */
function getDBConnection() {
    $database = new Database();
    return $database->getConnection();
}

/**
 * Configurações gerais da aplicação
 */
define('APP_NAME', 'InovaTech Store');
define('APP_URL', 'http://localhost/inovatec/loja');
define('ADMIN_EMAIL', 'admin@inovatech.com.br');

// Configurações de segurança
define('JWT_SECRET', 'sua_chave_secreta_jwt_muito_segura_aqui');
define('PASSWORD_MIN_LENGTH', 6);
define('SESSION_TIMEOUT', 3600); // 1 hora

// Configurações de upload
define('UPLOAD_PATH', __DIR__ . '/../uploads/');
define('UPLOAD_URL', APP_URL . '/uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'webp']);

// Timezone
date_default_timezone_set('America/Sao_Paulo');

// Função para log de erros
function logError($message, $context = []) {
    $logFile = __DIR__ . '/../logs/error.log';
    $timestamp = date('Y-m-d H:i:s');
    $contextStr = !empty($context) ? json_encode($context) : '';
    
    if (!file_exists(dirname($logFile))) {
        mkdir(dirname($logFile), 0777, true);
    }
    
    file_put_contents($logFile, "[$timestamp] $message $contextStr\n", FILE_APPEND | LOCK_EX);
}

// Headers para API
function setAPIHeaders() {
    header('Content-Type: application/json; charset=utf-8');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
    
    // Handle preflight requests
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit();
    }
}

// Função para resposta JSON
function jsonResponse($data, $status = 200) {
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit();
}

// Função para sanitizar dados
function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

// Função para validar email
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Função para gerar código único
function generateUniqueCode($prefix = '', $length = 8) {
    $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $code = $prefix;
    
    for ($i = 0; $i < $length; $i++) {
        $code .= $characters[rand(0, strlen($characters) - 1)];
    }
    
    return $code;
}

// Função para formatar preço
function formatPrice($price) {
    return number_format($price, 2, ',', '.');
}

// Função para formatar data
function formatDate($date, $format = 'd/m/Y H:i') {
    if (!$date) return '';
    
    if (is_string($date)) {
        $date = new DateTime($date);
    }
    
    return $date->format($format);
}

// Criar diretórios necessários se não existirem
$dirs = ['uploads', 'logs', 'temp'];
foreach ($dirs as $dir) {
    $path = __DIR__ . "/../$dir/";
    if (!file_exists($path)) {
        mkdir($path, 0777, true);
    }
}
?>