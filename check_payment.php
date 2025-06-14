<?php
header('Content-Type: application/json');

// Incluir configurações
require_once 'db_connect.php';
require_once 'config_api.php';

// Receber os parâmetros da requisição
$transaction_id = $_GET['transaction_id'] ?? '';
$cpf = $_GET['cpf'] ?? '';

if (!$transaction_id || !$cpf) {
    die(json_encode(['error' => 'Parâmetros ausentes']));
}

// Verificar status na API Nova Era
$auth = base64_encode(API_PUBLIC_KEY . ':' . API_SECRET_KEY);
$url = "https://api.novaera-pagamentos.com/api/v1/transactions/$transaction_id";
$options = [
    "http" => [
        "header" => "Authorization: Basic $auth\r\n" .
                    "Accept: application/json\r\n",
        "method" => "GET"
    ]
];

$context = stream_context_create($options);
$result = @file_get_contents($url, false, $context);

if ($result === false) {
    die(json_encode(['error' => 'Falha ao verificar status do pagamento']));
}

$response = json_decode($result, true);
$status = $response['data']['status'] ?? 'pending';

// Mapear status da API para o banco
$status_map = [
    'paid' => 'completed',
    'pending' => 'pending',
    'failed' => 'failed'
];
$db_status = $status_map[$status] ?? 'pending';

if ($db_status === 'completed') {
    // Atualizar status no banco de dados
    try {
        $stmt = $pdo->prepare("UPDATE cadastros SET status = ? WHERE transaction_id = ? AND cpf = ?");
        $stmt->execute([$db_status, $transaction_id, $cpf]);
    } catch (PDOException $e) {
        error_log("Erro ao atualizar status para transaction_id: $transaction_id, cpf: $cpf - " . $e->getMessage());
        die(json_encode(['error' => 'Erro ao atualizar status no banco']));
    }
}

echo json_encode(['status' => $db_status]);
?>