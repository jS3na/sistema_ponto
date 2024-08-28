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

    if (!isset($_SESSION['hoje'])) {
        $_SESSION['hoje'] = date('Y-m-d');
    }

    include("./db/config.php");

    if (!isset($_SESSION['admin']) || $_SESSION['admin'] != true) {
        header("Location: login.php");
        exit();
    }

    if (isset($_POST['funcionarios'])) {
        header("Location: funcionarios.php");
        exit();
    }

    if (isset($_POST['filtro'])) {
        $_SESSION['hoje'] = $_POST['data'];
    }

    $sql_verifica = "SELECT f.cpf, f.id, f.nome, f.email, f.status, p.funcionario_id, p.hora_entrada, p.justificativa, p.hora_saida, p.almoco_entrada, p.almoco_saida 
                FROM funcionarios f 
                LEFT JOIN pontos p ON f.id = p.funcionario_id 
                WHERE p.data = ?";

    $stmt = $conn->prepare($sql_verifica);
    $stmt->bind_param("s", $_SESSION['hoje']);
    $stmt->execute();
    $result = $stmt->get_result();

    ?>

    <div class="ie-fixMinHeight">
        <div class="main">
            <div id="">
                <img id="logogts" src="img/logo_gts.png" />
                <div id="tabelauser">
                    <form class="menu" method="post" action="funcionarios_dia.php">
                        <br>
                        <label for="data">Filtrar por data:</label>
                        <input type="date" id="data" name="data" value="<?php echo $_SESSION['hoje']; ?>"><br><br>
                        <div id="btt_func">
                            <input type="submit" name="filtro" id="filtro" value="Filtrar" />
                            <input type="submit" name="funcionarios" id="funcionarios" value="Funcionários cadastrados" />
                        </div>
                    </form>

                    <table>
                        <tr>
                            <th>Nome do Funcionário</th>
                            <th>Email</th>
                            <th>Hora de entrada</th>
                            <th>Justificativa</th>
                            <th>Foto</th>
                            <th>Entrada ao almoço</th>
                            <th>Saída do almoço</th>
                            <th>Hora de saída</th>
                            <th>Foto</th>
                        </tr>
                        <?php while ($row = $result->fetch_assoc()): ?>

                            <?php $ativoClass = ($row['status'] == 'ativo') ? '' : 'desativado'; ?>
                            <tr class="<?php echo $ativoClass; ?>">
                                <td class="txtTabela"><?php echo $row['nome']; ?></td>
                                <td class="txtTabela"><?php echo $row['email']; ?></td>

                                <?php if ($row['justificativa'] == '' || empty($row['justificativa'])): ?>
                                    <td class="txtTabela"><?php echo $row['hora_entrada']; ?></td>
                                <?php else: ?>
                                    <td class="txtTabela" style="background-color: #fa5c5c"><?php echo $row['hora_entrada']; ?></td>
                                <?php endif ?>

                                <td class="txtTabela"><?php echo $row['justificativa']; ?></td>
                                <td class="tdFoto">
                                    <img class="fotoPessoa" src="uploads/photo_<?php echo $row['cpf']; ?>_<?php echo $_SESSION['hoje']; ?>_entrando.png" alt="Foto de <?php echo $row['nome']; ?> ao entrar">
                                </td>
                                <td class="txtTabela"><?php echo $row['almoco_entrada']; ?></td>
                                <td class="txtTabela"><?php echo $row['almoco_saida']; ?></td>
                                <td class="txtTabela"><?php echo $row['hora_saida']; ?></td>
                                <td class="tdFoto">
                                    <img class="fotoPessoa" src="uploads/photo_<?php echo $row['cpf']; ?>_<?php echo $_SESSION['hoje']; ?>_saindo.png" alt="Foto de <?php echo $row['nome']; ?> ao sair">
                                </td>
                            </tr>

                        <?php endwhile; ?>
                    </table>
                    <p class="info bt">GTS Net</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        function verFotos(btn) {
            var fotosRow = btn.parentNode.parentNode.querySelector('.fotos');
            if (fotosRow) {
                fotosRow.style.display = (fotosRow.style.display === 'none' || fotosRow.style.display === '') ? 'table-row' : 'none';
            }
        }
    </script>

</body>

</html>