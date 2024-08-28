<?php

date_default_timezone_set('America/Sao_Paulo');

include("config.php"); // banco de dados

// Verifica se o CPF do funcionário foi passado na URL
if (isset($_GET['id'])) {
    $funcionario_cpf = $_GET['id'];

    // Atualiza $_SESSION['mes'] com a data selecionada no filtro
    if (isset($_POST['filtro'])) {
        $_SESSION['mes'] = $_POST['mes'];
    }

    // Obtém o ID do funcionário pelo CPF
    $sql_id = "SELECT id FROM funcionarios WHERE cpf = ?";
    $stmt_id = $conn->prepare($sql_id);
    $stmt_id->bind_param("s", $funcionario_cpf);
    $stmt_id->execute();
    $result_id = $stmt_id->get_result();

    if ($result_id->num_rows > 0) {
        $funcionario_data = $result_id->fetch_assoc();
        $funcionario_id = $funcionario_data['id'];

        // Ajusta a consulta para considerar valores nulos
        $sql_horas = "SELECT 
            funcionario_id, 
            SEC_TO_TIME(SUM(
                TIME_TO_SEC(TIMEDIFF(hora_saida, hora_entrada)) - 
                IFNULL(TIME_TO_SEC(TIMEDIFF(almoco_saida, almoco_entrada)), 0)
            )) as jornada_liquida
        FROM 
            pontos 
        WHERE 
            funcionario_id = ?
        GROUP BY 
            funcionario_id;";
        $stmt_horas = $conn->prepare($sql_horas);
        $stmt_horas->bind_param("i", $funcionario_id);
        $stmt_horas->execute();
        $result_horas = $stmt_horas->get_result();

        $sql = "SELECT id, nome, cpf, email, turno, cargo, data_admissao FROM funcionarios WHERE cpf = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $funcionario_cpf);
        $stmt->execute();
        $result = $stmt->get_result();

        // Verifica se encontrou o funcionário
        if ($result->num_rows > 0) {
            $funcionario = $result->fetch_assoc();
            $horas_total = $result_horas->fetch_assoc();

            // Obtém o mês e ano do filtro
            $mes = isset($_SESSION['mes']) ? $_SESSION['mes'] : date('Y-m');

            // Ajusta a consulta para filtrar por mês e ano
            $sql_verifica = "SELECT f.cpf, f.id, f.nome, f.email, f.status, p.funcionario_id, p.hora_entrada, p.hora_saida, p.almoco_entrada, p.almoco_saida, p.data 
                            FROM funcionarios f 
                            LEFT JOIN pontos p ON f.id = p.funcionario_id 
                            WHERE f.cpf = ? AND DATE_FORMAT(p.data, '%Y-%m') = ? 
                            ORDER BY p.data DESC";

            $stmt = $conn->prepare($sql_verifica);
            $stmt->bind_param("ss", $funcionario_cpf, $mes);
            $stmt->execute();
            $result = $stmt->get_result();

            // Variável para armazenar o total de segundos trabalhados
            $total_segundos = 0;
        }
    }
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="https://gtsnet.com.br/wp-content/uploads/sites/98/2020/08/cropped-favicon-32x32.png" sizes="32x32">
    <title>Ver Funcionário</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/media.css">
</head>
<body>

<div class="ie-fixMinHeight">
    <div class="main">
        <div id="">
            <img id="logogts" src="img/logo_gts.png" />
            <div id="tabelauser">
                <!-- Formulário de filtro por mês -->
                <form class="menu" method="post" action="meus_horarios.php?id=<?php echo $funcionario_cpf; ?>">
                    <br>
                    <label for="mes">Filtrar por mês:</label>
                    <input type="month" id="mes" name="mes" value="<?php echo $_SESSION['mes']; ?>"><br><br>
                    <input type="submit" name="filtro" id="filtro" value="Filtrar"/>
                    <div id="btt_func">
                        <div id="div_credenciais">
                            <p><b>CPF:</b> <?php echo $funcionario['cpf']; ?></p>
                            <p><b>Nome:</b> <?php echo $funcionario['nome']; ?></p>
                            <p><b>E-mail:</b> <?php echo $funcionario['email']; ?></p>
                            <p><b>Cargo:</b> <?php echo $funcionario['cargo']; ?></p>
                            <?php if ($funcionario['turno'] == 'dia_todo'): ?>
                                <p><b>Turno:</b> Dia todo</p>
                            <?php else: ?>
                                <p><b>Turno:</b> <?php echo $funcionario['turno']; ?></p>
                            <?php endif;?>
                            <p><b>Data de admissão:</b> <?php echo date('d/m/Y', strtotime($funcionario['data_admissao'])); ?></p>
                        </div>
                    </div>
                </form>

                <!-- Tabela de usuários -->
                <table>
                    <tr>
                        <th>DATA</th>
                        <th>Hora de entrada</th>
                        <th>Entrada ao almoço</th>
                        <th>Saída do almoço</th>
                        <th>Hora de saída</th>
                    </tr>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td class="txtTabela"><?php echo date('d/m/Y', strtotime($row['data'])); ?></td>
                            <td class="txtTabela"><?php echo $row['hora_entrada']; ?></td>
                            <td class="txtTabela"><?php echo $row['almoco_entrada'] ? $row['almoco_entrada'] : '-'; ?></td>
                            <td class="txtTabela"><?php echo $row['almoco_saida'] ? $row['almoco_saida'] : '-'; ?></td>
                            <td class="txtTabela"><?php echo $row['hora_saida']; ?></td>
                        </tr>
                    <?php endwhile; ?>
                </table>
                <!-- Exibe o total de horas trabalhadas -->
                <p id="horast">Total de horas trabalhadas no mês: <?php echo $horas_total['jornada_liquida']; ?></p>
                <p class="info bt">GTS Net</p>
            </div>
        </div>
    </div>
</div>

</body>
</html>

<?php
    } else {
        // Caso não encontre o funcionário, redireciona de volta para a página inicial
        header("Location: funcionarios.php");
        exit();
    }
?>
