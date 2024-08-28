<?php

date_default_timezone_set('America/Sao_Paulo');

include("./db/config.php"); // banco de dados

if (isset($_GET['id'])) {
    $funcionario_cpf = $_GET['id'];

    if (isset($_POST['filtro'])) {
        $_SESSION['mes'] = $_POST['mes'];
    }

    $sql_verifica = "SELECT id FROM funcionarios WHERE cpf = ?";
    $stmt = $conn->prepare($sql_verifica);
    $stmt->bind_param("s", $funcionario_cpf);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $funcionario_data = $result->fetch_assoc();
        $funcionario_id = $funcionario_data['id'];

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

        $sql_verifica = "SELECT id, nome, cpf, email, turno, cargo, data_admissao FROM funcionarios WHERE cpf = ?";
        $stmt = $conn->prepare($sql_verifica);
        $stmt->bind_param("s", $funcionario_cpf);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $funcionario = $result->fetch_assoc();
            $horas_total = $result_horas->fetch_assoc();

            $mes = isset($_SESSION['mes']) ? $_SESSION['mes'] : date('Y-m');

            $sql_verifica = "SELECT f.cpf, f.id, f.nome, f.email, f.status, p.funcionario_id, p.hora_entrada, p.hora_saida, p.almoco_entrada, p.almoco_saida, p.data 
                            FROM funcionarios f 
                            LEFT JOIN pontos p ON f.id = p.funcionario_id 
                            WHERE f.cpf = ? AND DATE_FORMAT(p.data, '%Y-%m') = ? 
                            ORDER BY p.data DESC";

            $stmt = $conn->prepare($sql_verifica);
            $stmt->bind_param("ss", $funcionario_cpf, $mes);
            $stmt->execute();
            $result = $stmt->get_result();

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
        header("Location: login.php");
        exit();
    }
?>