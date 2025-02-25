<?php 
// Defina suas variáveis de conexão corretamente
$servername = "127.0.0.1"; // Ou o IP do servidor MySQL
$username = "root"; // Nome de usuário para o MySQL
$password = ""; // Senha do MySQL
$dbname = "armazenarclientes"; // Nome do banco de dados

// Cria a conexão
$mysqli = new mysqli($servername, $username, $password, $dbname);

// Verifica se houve erro na conexão
if ($mysqli->connect_error) {
    die("Erro na conexão: " . $mysqli->connect_error);
}
?>
