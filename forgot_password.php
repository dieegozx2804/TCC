<?php  
require 'vendor/autoload.php'; // Carrega o autoloader do Composer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

include 'db.php'; // Inclui a conexão com o banco de dados

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];

    // Valida o formato do e-mail
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "Formato de e-mail inválido.";
        exit;
    }

    // Verifica se o e-mail existe no banco de dados
    $stmt = $mysqli->prepare("SELECT * FROM usuarios WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user) {
        // Gera um token de recuperação
        $token = bin2hex(random_bytes(32)); // Gera um token aleatório
        $expiry = date('Y-m-d H:i:s', strtotime('+1 hour')); // Define a expiração do token

        // Atualiza o banco de dados com o token e a expiração
        $stmt = $mysqli->prepare("UPDATE usuarios SET reset_token = ?, token_expiry = ? WHERE email = ?");
        $stmt->bind_param("sss", $token, $expiry, $email);
        $stmt->execute();

        // Configura o PHPMailer para enviar o e-mail
        $mail = new PHPMailer(true);

        try {
            // Configurações do servidor SMTP
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com'; // SMTP do Gmail
            $mail->SMTPAuth = true;
            $mail->Username = 'dsbcamargo08@gmail.com'; // Seu e-mail de remetente
            $mail->Password = 'ujvk wpqg eher eatl'; // App password (senha de app)
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Configurações do e-mail
            $mail->setFrom('dsbcamargo08@gmail.com', 'recuperar_senha'); // Remetente
            $mail->addAddress($email); // Destinatário (o e-mail do usuário)
            $mail->Subject = 'Recuperacao de Senha';

            // Gera o link de recuperação
            $reset_link = htmlspecialchars("http://localhost/projetovend/reset_password.php?token=" . $token);
            $mail->Body = "Clique no link para redefinir sua senha: <a href='" . $reset_link . "'>" . $reset_link . "</a>";
            $mail->isHTML(true);

            // Envia o e-mail
            $mail->send();
            echo "Se este email estiver cadastrado, você receberá um link para redefinir sua senha.";
        } catch (Exception $e) {
            echo "Erro ao enviar o e-mail de recuperação. Erro: {$mail->ErrorInfo}";
        }
    } else {
        echo "E-mail não cadastrado.";
    }
}
?>

<!-- Formulário para o usuário digitar o e-mail -->
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperação de Senha</title>
    <link rel="stylesheet" href="redefinir.css">
</head>
<body>
    <form method="POST" action="forgot_password.php">
        <label for="email">Digite seu e-mail:</label>
        <input type="email" id="email" name="email" required placeholder="Seu e-mail">
        <button type="submit">Recuperar Senha</button>
    </form>
</body>
</html>
