<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta http-equiv="pragma" content="no-cache" />
    <meta http-equiv="expires" content="-1" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" href="https://gtsnet.com.br/wp-content/uploads/sites/98/2020/08/cropped-favicon-32x32.png" sizes="32x32">
    <title>Tabela de usuários</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .fotos {
            display: none;
            margin-top: 10px;
        }
        .fotos img {
            display: block;
            max-width: 100%;
            margin-bottom: 5px;
        }
    </style>
</head>
<body>

<?php
session_start();

include("./db/config.php");

if (!isset($_SESSION['admin']) || $_SESSION['admin'] != true) {
    header("Location: login.php");
    exit();
}

if (isset($_POST['add_funcionario'])) {
    header("Location: add_funcionario.php");
    exit();
}

if (isset($_POST['funcionarios_dia'])) {
    header("Location: funcionarios_dia.php");
    exit();
}

$sql_verifica = "SELECT * FROM funcionarios ORDER BY id;";

$stmt = $conn->prepare($sql_verifica);
$stmt->execute();
$result = $stmt->get_result();

?>

<div class="ie-fixMinHeight">
    <div class="main">
        <div id="">
            <img id="logogts" src="img/logo_gts.png" />
            <div id="tabelauser">

                <form class="menu" method="post" action="funcionarios.php">
                    <br>
                    <div id="btt_func">
                        <input type="submit" name="add_funcionario" id="filtro" value="Adicionar"/>
                        <input type="submit" name="funcionarios_dia" id="funcionarios_dia" value="Funcionários trabalhando hoje"/>
                    </div>
                </form>

                <table>
                    <tr>
                        <th>Nome do Funcionário</th>
                        <th>Email</th>
                        <th>CPF</th>
                        <th>Cargo</th>
                        <th>Horário de entrada</th>
                        <th>Data de admissão</th>
                    </tr>

                    <?php while ($row = $result->fetch_assoc()): ?>
                        <?php $ativoClass = ($row['status'] == 'ativo') ? '' : 'desativado'; ?>
                        <tr class="<?php echo $ativoClass; ?>">
                            
                            <td class="txtTabela">

                                <form method="post" action="ver_funcionario.php?id=<?php echo htmlspecialchars($row['id']); ?>">
                                        <input class="name_ver" type="submit" name="name_ver" value="<?php echo $row['nome']; ?>">
                                </form>

                            </td>

                            <td class="txtTabela"><?php echo $row['email']; ?></td>
                            <td class="txtTabela"><?php echo $row['cpf']; ?></td>
                            <td class="txtTabela"><?php echo $row['cargo']; ?></td>
                            <td class="txtTabela"><?php echo $row['horario_entrada']; ?></td>
                            <td class="txtTabela"><?php echo $row['data_admissao']; ?></td>
                        </tr>
                    <?php endwhile; ?>

                </table>
                <p class="info bt">GTS Net</p>
            </div>
        </div>
    </div>
</div>

</body>
</html>
