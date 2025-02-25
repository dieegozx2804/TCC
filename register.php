<?php
require 'vendor/autoload.php'; // Carrega o autoloader do Composer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'db.php';

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}

$nome = $_POST['name'];
$email = $_POST['email'];
$senha = $_POST['password'];
$phone = $_POST['phone'];
$user_type = $_POST['user_type']; // Tipo de usuário (Freelancer ou Empresa)

// Campos exclusivos para empresas
$nome_empresa = isset($_POST['nome_empresa']) ? $_POST['nome_empresa'] : null;
$cnpj_empresa = isset($_POST['cnpj_empresa']) ? $_POST['cnpj_empresa'] : null;

// Verificação de campos obrigatórios
if (empty($nome) || empty($email) || empty($senha) || empty($phone) || empty($user_type)) {
    echo json_encode(["status" => "error", "message" => "Todos os campos são obrigatórios."]);
    exit();
}

// Se for uma empresa, o nome e CNPJ são obrigatórios
if ($user_type == 'empresa') {
    if (empty($nome_empresa) || empty($cnpj_empresa)) {
        echo json_encode(["status" => "error", "message" => "Nome da empresa e CNPJ são obrigatórios."]);
        exit();
    }
}

// Validação de senha
if (strlen($senha) < 9) {
    echo json_encode(["status" => "error", "message" => "A senha deve ter pelo menos 9 caracteres."]);
    exit();
}

// Remover caracteres não numéricos do telefone
$phone = preg_replace('/\D/', '', $phone);
if (strlen($phone) > 11) {
    echo json_encode(["status" => "error", "message" => "O número de telefone deve ter no máximo 15 caracteres."]);
    exit();
}

// Validação de e-mail
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["status" => "error", "message" => "O e-mail fornecido não é válido."]);
    exit();
}

// Verificar se o email ou telefone já estão cadastrados
$sql = "SELECT * FROM usuarios WHERE email = ? OR phone = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $email, $phone);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Verificando se o email ou telefone já existem
    $row = $result->fetch_assoc();
    if ($row['email'] == $email) {
        echo json_encode(["status" => "error", "message" => "Este e-mail já está cadastrado."]);
    } elseif ($row['phone'] == $phone) {
        echo json_encode(["status" => "error", "message" => "Este número de telefone já está cadastrado."]);
    }
    exit();
}

// Hash da senha
$senhaHash = password_hash($senha, PASSWORD_DEFAULT);

// Inserir o novo usuário no banco de dados
if ($user_type == 'empresa') {
    // Inserir dados de empresa
    $stmt = $conn->prepare("INSERT INTO usuarios (nome, email, senha, phone, user_type, nome_empresa, cnpj_empresa) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $nome, $email, $senhaHash, $phone, $user_type, $nome_empresa, $cnpj_empresa);
} else {
    // Inserir dados de freelancer
    $stmt = $conn->prepare("INSERT INTO usuarios (nome, email, senha, phone, user_type) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $nome, $email, $senhaHash, $phone, $user_type);
}

if ($stmt->execute()) {
    // Envio do email de confirmação
    $mail = new PHPMailer(true);

    try {
        // Configurações do servidor SMTP
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // SMTP do Gmail
        $mail->SMTPAuth = true;
        $mail->Username = 'dsbcamargo08@gmail.com'; // Seu e-mail de remetente
        $mail->Password = 'ujvk wpqg eher eatl'; // Senha de app do Gmail
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Configurações do e-mail
        $mail->setFrom('dsbcamargo08@gmail.com', '!'); // Remetente
        $mail->addAddress($email); // Destinatário (o e-mail do usuário)
        $mail->Subject = 'Conta Criada com Sucesso';
        $mail->Body = "Olá $nome,\n\nSua conta foi criada com sucesso em nosso sistema. Bem-vindo!";
        $mail->isHTML(false);

        // Envia o email
        $mail->send();
        echo json_encode(["status" => "success", "message" => "Cadastro realizado com sucesso! Verifique seu email."]);
    } catch (Exception $e) {
        echo json_encode(["status" => "error", "message" => "Cadastro realizado, mas houve um erro ao enviar o email: {$mail->ErrorInfo}"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Erro ao cadastrar: " . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
