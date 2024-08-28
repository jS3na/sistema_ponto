<?php

$host = '10.10.86.80';
$login = "sistema_ponto";
$senha_bd = "sistemaponto";
$banco = 'gts';

$conn = new mysqli($host, $login, $senha_bd, $banco);

if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
}

?>