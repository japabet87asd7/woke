<?php
$host = 'localhost';
$dbname = 'elonl5453133_payv2db';
$username = 'elonl5453133_payv2db';
$password = 'Pactual2021@#';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro na conexão com o banco de dados: " . $e->getMessage());
}
?>