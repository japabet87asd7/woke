<?php
// (Opcional) Exibir erros de debug (apenas em desenvolvimento)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// ======================
// 1. DADOS DE CONEXÃO COM O BANCO
// ======================
$db_host = 'localhost';
$db_name = 'elonl5453133_checkout';
$db_user = 'elonl5453133_checkout';
$db_pass = '0~W.9p#[0#L_';

// ======================
// 2. OBTÉM CPF DA QUERY STRING
// ======================
$cpf = isset($_GET['cpf']) ? $_GET['cpf'] : '';
if (empty($cpf)) {
    die("Erro: CPF não informado.");
}

// ======================
// 3. BUSCA INFORMAÇÕES DA COMPRA PELO CPF
// ======================
try {
    // Conecta ao banco de dados
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Prepara e executa a consulta
    $stmt = $pdo->prepare("SELECT * FROM pix_transactions WHERE customer_document = :cpf LIMIT 1");
    $stmt->bindParam(':cpf', $cpf);
    $stmt->execute();

    // Busca o resultado
    $compra = $stmt->fetch(PDO::FETCH_ASSOC);

    // Se não encontrar nenhuma compra, exibe erro
    if (!$compra) {
        die("Erro: Nenhuma compra encontrada para o CPF informado.");
    }

    // ======================
    // 3.1. DADOS DO COMPRADOR
    // ======================
    $nome          = $compra['customer_name'];
    $email_usuario = $compra['customer_email'];
    $valor_pago    = number_format($compra['amount'], 2, ',', '.');

    // ======================
    // 3.2. TRATA A DATA DE PAGAMENTO
    // ======================
    if (empty($compra['paid_at'])) {
        $data_pagamento = date('Y-m-d'); // Formato correto para cálculos
    } else {
        $data_pagamento = date('Y-m-d', strtotime($compra['paid_at']));
    }

    // ======================
    // 3.3. DEFINE INFORMAÇÕES DO CURSO
    // ======================
    $curso_nome   = "O Novo Mapa Do Digital";
    $empresa_nome = "Suporte Cursos";

    // ======================
    // 3.4. CALCULA DATAS DE ACESSO
    // ======================
    $hoje = new DateTime(); // Pega a data atual
    $data_lancamento = clone $hoje;
    $data_lancamento->modify('+13 days'); // Sempre 13 dias após hoje

    $data_acesso_antecipado = new DateTime($data_pagamento);
    $data_acesso_antecipado->modify('+8 days'); // Sempre 8 dias após o pagamento

    // Formata as datas para exibição no e-mail e na página
    $data_pagamento_formatada = date('d/m/Y', strtotime($data_pagamento));
    $data_acesso_antecipado_formatada = $data_acesso_antecipado->format('d/m/Y');
    $data_lancamento_formatada = $data_lancamento->format('d/m/Y');

} catch (PDOException $e) {
    die("Erro ao conectar ao banco de dados: " . $e->getMessage());
}

// ======================
// 4. ENVIA O E-MAIL SEM PHPMailer
// ======================
$assunto = "Confirmação de Pagamento - $curso_nome";
$mensagem = "
<html>
<head>
    <title>Confirmação da sua pré-inscrição - Panobianco Academia</title>
</head>
<body style='font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 0;'>
    <div style='width: 100%; max-width: 600px; margin: 0 auto; background-color: #ffffff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);'>
        <h1 style='color: #ff4d00; font-size: 24px; text-align: center;'>Pré-inscrição confirmada! 💪</h1>
        
        <p style='font-size: 16px; line-height: 1.5; color: #555;'>Olá <strong>$nome</strong>,</p>
        
        <p style='font-size: 16px; line-height: 1.5; color: #555;'>Parabéns! Sua pré-inscrição na <strong>Panobianco Academia – Unidade Centro/Queimados</strong> foi confirmada com sucesso.</p>
        
        <p style='font-size: 16px; line-height: 1.5; color: #555;'>Recebemos o pagamento de <strong>R$ $valor_pago</strong> em <strong>$data_pagamento_formatada</strong>, que garante:</p>
        <ul style='font-size: 16px; color: #555;'>
            <li>✔️ 2 meses de treino</li>
            <li>✔️ Aulas coletivas inclusas</li>
            <li>✔️ Acesso antecipado à academia</li>
        </ul>
        
    
        
        <p style='font-size: 16px; line-height: 1.5; color: #555;'>Prepare-se para uma experiência incrível! 💥</p>

        <div style='text-align: center; font-size: 12px; color: #999; margin-top: 30px;'>
            <p>Se você não reconhece esta compra, <strong>ignore este e-mail</strong>.</p>
            <p>Com atitude e energia,<br>Equipe <strong>Panobianco Academia</strong></p>
        </div>
    </div>
</body>
</html>

";

// Cabeçalhos do e-mail
$cabecalhos = "MIME-Version: 1.0\r\n";
$cabecalhos .= "Content-type: text/html; charset=UTF-8\r\n";
$cabecalhos .= "From: $empresa_nome <contato@suporte-cursos.online>\r\n";
$cabecalhos .= "Reply-To: contato@suporte-cursos.online\r\n";

// Envia o e-mail
if (!empty($email_usuario)) {
    mail($email_usuario, $assunto, $mensagem, $cabecalhos);
}

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagamento Confirmado</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen p-4">

    <div class="bg-white shadow-lg rounded-xl p-6 max-w-md text-center">
        <div class="flex justify-center">
            <i class="ph ph-check-circle text-green-500 text-6xl"></i>
        </div>

        <h1 class="text-2xl font-bold text-gray-800 mt-4"><B>Pagamento Confirmado!</B></h1>

        <p class="text-lg text-gray-600 mt-2">
            Olá <span class="font-semibold text-blue-600"><B><?php echo htmlspecialchars($nome); ?></B></span>, 
            seu pagamento de <span class="font-semibold text-green-600">R$ <?php echo $valor_pago; ?></span> 
            foi confirmado no dia <span class="font-semibold"><?php echo $data_pagamento_formatada; ?></span>.
        </p>

        <p class="text-gray-500 mt-4">
            Você receberá mais informações detalhadas por e-mail. <br>
            Verifique sua caixa de entrada e fique atento às próximas atualizações.
        </p>

        <div class="mt-6">
           
        </div>
    </div>

</body>
</html>
