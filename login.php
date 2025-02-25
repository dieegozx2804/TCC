<?php
header("HTTP/1.1 200 OK");
header("Content-Type: application/json");  // Definindo o tipo de conteúdo como JSON

require 'db.php';

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}

$email = $_POST['email'];
$password = $_POST['password'];

$sql = "SELECT * FROM usuarios WHERE LOWER(email) = LOWER(?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    if (password_verify($password, $row['senha'])) {
        echo json_encode(["status" => "success", "message" => "Login bem-sucedido"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Email ou senha incorretos"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Email ou senha incorretos"]);
}

$stmt->close();
$conn->close();
?>
