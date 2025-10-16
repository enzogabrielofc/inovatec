<?php
/**
 * API para geração de perguntas bíblicas aleatórias
 * Retorna perguntas dinâmicas sobre a Bíblia
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

// Banco extenso de perguntas bíblicas
$biblicalQuestions = [
    [
        "question" => "Quantos dias e noites choveu durante o dilúvio nos tempos de Noé?",
        "options" => ["30 dias", "40 dias", "50 dias", "60 dias"],
        "correct" => 1,
        "reference" => "Gênesis 7:12"
    ],
    [
        "question" => "Quem foi o primeiro rei de Israel?",
        "options" => ["Davi", "Saul", "Salomão", "Samuel"],
        "correct" => 1,
        "reference" => "1 Samuel 10:1"
    ],
    [
        "question" => "Quantos apóstolos Jesus escolheu?",
        "options" => ["10", "11", "12", "13"],
        "correct" => 2,
        "reference" => "Mateus 10:1-4"
    ],
    [
        "question" => "Em que cidade Jesus nasceu?",
        "options" => ["Nazaré", "Belém", "Jerusalém", "Cafarnaum"],
        "correct" => 1,
        "reference" => "Mateus 2:1"
    ],
    [
        "question" => "Quem foi vendido como escravo por seus irmãos?",
        "options" => ["José", "Benjamim", "Judá", "Simeão"],
        "correct" => 0,
        "reference" => "Gênesis 37:28"
    ],
    [
        "question" => "Qual o nome do mar que Moisés abriu?",
        "options" => ["Mar da Galiléia", "Mar Morto", "Mar Vermelho", "Mar Mediterrâneo"],
        "correct" => 2,
        "reference" => "Êxodo 14:21"
    ],
    [
        "question" => "Quantos filhos teve Jacó?",
        "options" => ["10", "11", "12", "13"],
        "correct" => 2,
        "reference" => "Gênesis 35:22"
    ],
    [
        "question" => "Quem batizou Jesus?",
        "options" => ["Pedro", "João Batista", "André", "Tiago"],
        "correct" => 1,
        "reference" => "Mateus 3:13-17"
    ],
    [
        "question" => "Qual foi o primeiro milagre de Jesus?",
        "options" => ["Cura do cego", "Multiplicação dos pães", "Transformar água em vinho", "Ressurreição de Lázaro"],
        "correct" => 2,
        "reference" => "João 2:1-11"
    ],
    [
        "question" => "Quem interpretou o sonho do Faraó?",
        "options" => ["Daniel", "José", "Moisés", "Elias"],
        "correct" => 1,
        "reference" => "Gênesis 41:25"
    ],
    [
        "question" => "Quantos livros tem a Bíblia?",
        "options" => ["64", "65", "66", "67"],
        "correct" => 2,
        "reference" => "Cânon Bíblico"
    ],
    [
        "question" => "Quem construiu o templo em Jerusalém?",
        "options" => ["Davi", "Salomão", "Ezequias", "Josias"],
        "correct" => 1,
        "reference" => "1 Reis 6:1"
    ],
    [
        "question" => "Qual profeta foi levado ao céu num redemoinho?",
        "options" => ["Elias", "Eliseu", "Ezequiel", "Isaías"],
        "correct" => 0,
        "reference" => "2 Reis 2:11"
    ],
    [
        "question" => "Quem negou Jesus três vezes?",
        "options" => ["Judas", "Pedro", "Tomé", "João"],
        "correct" => 1,
        "reference" => "Mateus 26:69-75"
    ],
    [
        "question" => "Quantos anos os israelitas peregrinaram no deserto?",
        "options" => ["30 anos", "40 anos", "50 anos", "60 anos"],
        "correct" => 1,
        "reference" => "Números 14:33"
    ],
    [
        "question" => "Quem foi o profeta que confrontou os profetas de Baal no Monte Carmelo?",
        "options" => ["Eliseu", "Elias", "Samuel", "Ezequiel"],
        "correct" => 1,
        "reference" => "1 Reis 18:19-40"
    ],
    [
        "question" => "Qual o nome da esposa de Abraão?",
        "options" => ["Sara", "Rebeca", "Raquel", "Lia"],
        "correct" => 0,
        "reference" => "Gênesis 11:29"
    ],
    [
        "question" => "Quem foi jogado na fornalha ardente?",
        "options" => ["Daniel", "Sadraque, Mesaque e Abede-Nego", "José", "Moisés"],
        "correct" => 1,
        "reference" => "Daniel 3:19-27"
    ],
    [
        "question" => "Qual cidade teve suas muralhas derrubadas ao som de trombetas?",
        "options" => ["Jerusalém", "Babilônia", "Jericó", "Nínive"],
        "correct" => 2,
        "reference" => "Josué 6:20"
    ],
    [
        "question" => "Quem foi o discípulo que Jesus amava?",
        "options" => ["Pedro", "André", "João", "Tiago"],
        "correct" => 2,
        "reference" => "João 13:23"
    ],
    [
        "question" => "Qual o nome do monte onde Jesus foi transfigurado?",
        "options" => ["Monte Sinai", "Monte das Oliveiras", "Monte Tabor", "Monte Hermom"],
        "correct" => 2,
        "reference" => "Mateus 17:1-9"
    ],
    [
        "question" => "Quem escreveu a maioria das cartas do Novo Testamento?",
        "options" => ["Pedro", "João", "Paulo", "Tiago"],
        "correct" => 2,
        "reference" => "Novo Testamento"
    ],
    [
        "question" => "Qual rei teve um sonho com uma grande estátua?",
        "options" => ["Nabucodonosor", "Dario", "Ciro", "Belsazar"],
        "correct" => 0,
        "reference" => "Daniel 2:31-35"
    ],
    [
        "question" => "Quantos dias Jesus ficou no deserto sendo tentado?",
        "options" => ["30 dias", "40 dias", "50 dias", "70 dias"],
        "correct" => 1,
        "reference" => "Mateus 4:2"
    ],
    [
        "question" => "Quem foi o juiz mais forte de Israel?",
        "options" => ["Gideão", "Sansão", "Débora", "Samuel"],
        "correct" => 1,
        "reference" => "Juízes 13-16"
    ],
    [
        "question" => "Qual animal falou com Balaão?",
        "options" => ["Cavalo", "Jumenta", "Ovelha", "Serpente"],
        "correct" => 1,
        "reference" => "Números 22:28"
    ],
    [
        "question" => "Quantas pragas Deus enviou sobre o Egito?",
        "options" => ["8", "9", "10", "11"],
        "correct" => 2,
        "reference" => "Êxodo 7-12"
    ],
    [
        "question" => "Quem foi engolido por um grande peixe?",
        "options" => ["Pedro", "Jonas", "Paulo", "Elias"],
        "correct" => 1,
        "reference" => "Jonas 1:17"
    ],
    [
        "question" => "Qual o primeiro livro da Bíblia?",
        "options" => ["Êxodo", "Salmos", "Gênesis", "Levítico"],
        "correct" => 2,
        "reference" => "Bíblia"
    ],
    [
        "question" => "Quem traiu Jesus por 30 moedas de prata?",
        "options" => ["Pedro", "Judas Iscariotes", "Tomé", "Pilatos"],
        "correct" => 1,
        "reference" => "Mateus 26:14-16"
    ],
    [
        "question" => "Quantos mandamentos Deus deu a Moisés?",
        "options" => ["8", "10", "12", "15"],
        "correct" => 1,
        "reference" => "Êxodo 20:1-17"
    ],
    [
        "question" => "Quem foi lançado na cova dos leões?",
        "options" => ["Daniel", "Jonas", "José", "Paulo"],
        "correct" => 0,
        "reference" => "Daniel 6"
    ],
    [
        "question" => "Qual o versículo mais famoso da Bíblia?",
        "options" => ["João 1:1", "João 3:16", "João 14:6", "Salmos 23:1"],
        "correct" => 1,
        "reference" => "João 3:16"
    ],
    [
        "question" => "Quem foi o primeiro homem criado por Deus?",
        "options" => ["Adão", "Abel", "Caim", "Sete"],
        "correct" => 0,
        "reference" => "Gênesis 2:7"
    ],
    [
        "question" => "Quantos discípulos Jesus escolheu além dos 12 apóstolos?",
        "options" => ["60", "70", "80", "100"],
        "correct" => 1,
        "reference" => "Lucas 10:1"
    ],
    [
        "question" => "Quem foi a primeira mulher criada por Deus?",
        "options" => ["Eva", "Sara", "Miriã", "Débora"],
        "correct" => 0,
        "reference" => "Gênesis 2:22"
    ],
    [
        "question" => "Qual profeta foi levado ao céu em carros de fogo?",
        "options" => ["Elias", "Eliseu", "Enoque", "Ezequiel"],
        "correct" => 0,
        "reference" => "2 Reis 2:11"
    ],
    [
        "question" => "Quantas vezes Pedro negou Jesus?",
        "options" => ["2 vezes", "3 vezes", "4 vezes", "5 vezes"],
        "correct" => 1,
        "reference" => "Mateus 26:75"
    ],
    [
        "question" => "Qual gigante Davi derrotou com uma pedra?",
        "options" => ["Golias", "Og", "Anaque", "Refaim"],
        "correct" => 0,
        "reference" => "1 Samuel 17:49"
    ],
    [
        "question" => "Quem escreveu o livro de Apocalipse?",
        "options" => ["Pedro", "Paulo", "João", "Tiago"],
        "correct" => 2,
        "reference" => "Apocalipse 1:1"
    ]
];

function getRandomQuestions($count = 5) {
    global $biblicalQuestions;
    
    // Embaralha o array de perguntas
    $shuffled = $biblicalQuestions;
    shuffle($shuffled);
    
    // Retorna apenas a quantidade solicitada
    return array_slice($shuffled, 0, min($count, count($shuffled)));
}

function validateRequest() {
    $method = $_SERVER['REQUEST_METHOD'] ?? '';
    
    if ($method !== 'GET') {
        http_response_code(405);
        return [
            'success' => false,
            'error' => 'Método não permitido. Use GET.'
        ];
    }
    
    return ['success' => true];
}

// Processa a requisição
try {
    $validation = validateRequest();
    if (!$validation['success']) {
        echo json_encode($validation);
        exit;
    }
    
    // Pega o parâmetro count da URL
    $count = isset($_GET['count']) ? (int)$_GET['count'] : 5;
    $count = max(1, min(40, $count)); // Limita entre 1 e 40 perguntas
    
    // Busca perguntas aleatórias
    $questions = getRandomQuestions($count);
    
    // Retorna resposta de sucesso
    echo json_encode([
        'success' => true,
        'count' => count($questions),
        'total_available' => count($GLOBALS['biblicalQuestions']),
        'questions' => $questions,
        'timestamp' => time()
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Erro interno do servidor',
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>