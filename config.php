<?php

//conexão com o banco de dados
$host = '10.10.86.80';
$login = "sistema_ponto";
$senha_bd = "sistemaponto";
$banco = 'gts';

$conn = new mysqli($host, $login, $senha_bd, $banco);
//verifica se a conexão foi estabelecida com sucesso
if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
}

?>