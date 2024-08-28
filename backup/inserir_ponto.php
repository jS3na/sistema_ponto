<?php

date_default_timezone_set('America/Sao_Paulo');

include("config.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $hoje = date('Y-m-d');
    echo $hoje; // Para depuração, pode ser removido posteriormente

    $funcionario_id = (int)$_POST['funcionario_id'];
    $funcionario_cpf = (int)$_POST['funcionario_cpf'];
    $atual = $_POST['atual'];

    $teste = $atual . $funcionarioid . $funcionario_cpf;
    
        if ($atual == 'entrando') {
            $sql_verifica = "SELECT f.id, p.hora_entrada
                            FROM funcionarios f 
                            LEFT JOIN pontos p ON f.id = p.funcionario_id 
                            WHERE f.cpf = ? AND data = ?";

            $stmt = $conn->prepare($sql_verifica);
            if (!$stmt) {
                echo "Erro na preparação da consulta: " . $conn->error;
                exit();
            }
            $stmt->bind_param("is", $funcionario_cpf, $hoje);
            $stmt->execute();
            $result3 = $stmt->get_result();
            $row3 = $result3->fetch_assoc();

            if (is_null($row3)) {

                $trabalhando = "trabalhando";
                $hoje_entrar = date('Y-m-d');
                $horario = date('H:i:s');

                // Inserir novo usuário
                $sql = "INSERT INTO pontos (funcionario_id, data, hora_entrada) VALUES (?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("iss", $funcionario_id, $hoje_entrar, $horario);

                if (!$stmt->execute()) {
                    echo "Erro: " . $sql . "<br>" . $conn->error;
                }

                $sql = "INSERT INTO tbteste (teste) VALUES (?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("s", $horario);

                if (!$stmt->execute()) {
                    echo "Erro: " . $sql . "<br>" . $conn->error;
                }

            }

        } elseif ($atual == 'saindo') {
            
            $sql_verifica = "SELECT * FROM pontos WHERE funcionario_id = ? AND data = ? AND hora_saida IS NULL";
            $stmt = $conn->prepare($sql_verifica);
            $stmt->bind_param("is", $funcionario_id, $hoje);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows != 0) {
                $agora = new DateTime();
                $horario = date('H:i:s');
                $hoje = date('Y-m-d');

                $sql = "UPDATE pontos SET hora_saida = ? WHERE funcionario_id = ? AND data = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sis", $horario, $funcionario_id, $hoje);

                if (!$stmt->execute()) {
                    echo "Erro: " . $sql . "<br>" . $conn->error;
                }

            } else {
                $trabalhando = "fim";
            }
        }

    #$sql_verifica = "INSERT INTO  tbteste (teste) VALUES (?)";
} else {
    echo "Nenhuma localização enviada";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    
</body>
</html>
