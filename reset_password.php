<?php
include 'db.php'; // Inclui a conexão com o banco de dados

// Verifica se o token foi passado via GET (link enviado por email)
if (isset($_GET['token'])) {
    $token = htmlspecialchars($_GET['token']); // Sanitiza o token para evitar ataques de XSS

    // Verifica se o token é válido e não expirou
    $stmt = $mysqli->prepare("SELECT * FROM usuarios WHERE reset_token = ? AND token_expiry > NOW()");
    $stmt->bind_param('s', $token);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if ($user) {
            // A nova senha foi enviada
            $new_password = $_POST['new_password'];
            $new_password_confirm = $_POST['confirm_password'];

            if ($new_password !== $new_password_confirm) {
                echo "<div class='error-message'>As senhas não coincidem. Tente novamente.</div>";
            } else {
                // Atualiza a senha no banco de dados
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

                $stmt = $mysqli->prepare("UPDATE usuarios SET senha = ?, reset_token = NULL, token_expiry = NULL WHERE reset_token = ?");
                $stmt->bind_param('ss', $hashed_password, $token);
                $stmt->execute();

                echo "<div class='success-message'>Sua senha foi atualizada com sucesso!</div>";
            }
        } else {
            echo "<div class='error-message'>Token inválido ou expirado.</div>";
        }
    }
} else {
    echo "<div class='error-message'>Token não fornecido.</div>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redefinir Senha</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <!-- Formulário para redefinir a senha -->
    <?php if (isset($user)) { ?>
        <form action="reset_password.php?token=<?php echo htmlspecialchars($_GET['token']); ?>" method="POST">
            <label for="new_password">Nova senha:</label>
            <input type="password" name="new_password" id="new_password" required><br>

            <label for="confirm_password">Confirmar nova senha:</label>
            <input type="password" name="confirm_password" id="confirm_password" required><br>

            <button type="submit">Redefinir Senha</button>
        </form>
    <?php } ?>
</body>
</html>
        