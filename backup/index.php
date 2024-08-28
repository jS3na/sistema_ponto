<?php
session_start();

if (!$_SESSION['logado']) {
    header("Location: https://10.10.86.80/login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <meta http-equiv="pragma" content="no-cache" />
    <meta http-equiv="expires" content="-1" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" href="https://gtsnet.com.br/wp-content/uploads/sites/98/2020/08/cropped-favicon-32x32.png" sizes="32x32">
    <title>Bater ponto</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/media.css">
</head>

<body>
    <div class="ie-fixMinHeight">
        <div class="main">
            <div class="wrap animated fadeIn" id="principal">

                <form name="login" method="post" action="index.php?id=<?php echo htmlspecialchars($_GET['id']); ?>">
                    <img id="logogts" src="img/logo_gts.png" />

                    <?php
                    date_default_timezone_set('America/Sao_Paulo');

                    $trabalhando = '';
                    $funcionario_id = 'aaaa';

                    $hoje_TOTAL = date('Y-m-d');

                    include("config.php");

                    if (isset($_GET['id'])) {

                        $funcionario_cpf = $_GET['id'];

                        $sql_verifica = "SELECT f.id, f.nome, f.turno, f.data_admissao, p.funcionario_id, p.data, p.hora_entrada, p.hora_saida, p.almoco_entrada, p.almoco_saida
                    FROM funcionarios f 
                    LEFT JOIN pontos p ON f.id = p.funcionario_id 
                    WHERE f.cpf = ? AND data = ?";

                        $stmt = $conn->prepare($sql_verifica);
                        if (!$stmt) {
                            echo "Erro na preparação da consulta: " . $conn->error;
                            exit();
                        }
                        $stmt->bind_param("ss", $funcionario_cpf, $hoje_TOTAL);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        $row = $result->fetch_assoc();
                        if (!is_null($row)) {
                            $hora_saida = $row['hora_saida'];
                            $turno = $row['turno'];
                            $almoco_entrada = $row['almoco_entrada'];
                            $almoco_saida = $row['almoco_saida'];
                        }

                        $sql_verifica = "SELECT id, nome, horario_entrada FROM funcionarios WHERE cpf = ?";
                        $stmt = $conn->prepare($sql_verifica);
                        if (!$stmt) {
                            echo "Erro na preparação da consulta: " . $conn->error;
                            exit();
                        }
                        $stmt->bind_param("s", $funcionario_cpf);
                        $stmt->execute();
                        $result2 = $stmt->get_result();
                        $row2 = $result2->fetch_assoc();

                        $funcionario_id = $row2['id'];
                        $nome = $row2['nome'];
                        $horario_entrada = $row2['horario_entrada'];
                        $horario_entrada_limite = date('H:i:s', strtotime($horario_entrada . ' + 5 minutes'));

                        if ($result->num_rows == 0) {
                            $trabalhando = "inicio";
                        } elseif ($turno == 'dia_todo' && is_null($almoco_entrada)) {
                            $trabalhando = "inicio_almoco";
                        } elseif ($turno == 'dia_todo' && !is_null($almoco_entrada) && is_null($almoco_saida)) {
                            $trabalhando = "almocando";
                        } elseif (!isset($hora_saida)) {
                            $trabalhando = "trabalhando";
                        } else {
                            $trabalhando = "fim";
                        }

                        $hora_atual = date('H:i:s');

                        if ($hora_atual < '12:00:00') {
                            echo '<p class="bemvindo">Bom dia, ' . htmlspecialchars($nome) . '!</p>';
                        } else {
                            echo '<p class="bemvindo">Boa tarde, ' . htmlspecialchars($nome) . '!</p>';
                        }

                        echo '<br>';

                        if (isset($_POST['entrar'])) {

                            $horario_entrada_atual = date('H:i:s');

                            if ($horario_entrada_atual > $horario_entrada_limite) {
                                $_SESSION['atrasado'] = true;
                            }
                            else{
                                $_SESSION['atrasado'] = false;
                            }

                            header("Location: foto.php?id=" . $funcionario_cpf . "&id2=" . $funcionario_id . "&atual=entrando");
                            exit();

                        }

                        if (isset($_POST['inicio_almoco'])) {
                            $hora_almoco = date('H:i:s');
                            $dia_almoco = date('Y-m-d');

                            $sql = "UPDATE pontos SET almoco_entrada = ? WHERE funcionario_id = ? AND data = ?";
                            $stmt = $conn->prepare($sql);
                            $stmt->bind_param("sss", $hora_almoco, $funcionario_id, $dia_almoco);
                            $stmt->execute();

                            header("Location: index.php?id=" . $funcionario_cpf);
                            exit();
                        }

                        if (isset($_POST['final_almoco'])) {
                            $hora_almoco = date('H:i:s');
                            $dia_almoco = date('Y-m-d');

                            $sql = "UPDATE pontos SET almoco_saida = ? WHERE funcionario_id = ? AND data = ?";
                            $stmt = $conn->prepare($sql);
                            $stmt->bind_param("sss", $hora_almoco, $funcionario_id, $dia_almoco);
                            $stmt->execute();

                            header("Location: index.php?id=" . $funcionario_cpf);
                            exit();
                        }

                        if (isset($_POST['sair-conta'])) {
                            session_destroy();
                            header("Location: login.php");
                            exit();
                        }

                        if (isset($_POST['sair'])) {
                            header("Location: foto.php?id=" . $funcionario_cpf . "&id2=" . $funcionario_id . "&atual=saindo");
                            exit();
                        }
                    } else {
                        if ($trabalhando == 'fim') {
                            header("Location: login.php");
                            exit();
                        }
                    }
                    ?>

                    <?php if ($trabalhando == 'inicio') : ?>
                        <input class="entrar" name="entrar" type="submit" value="Iniciar expediente"/>
                    <?php endif; ?>

                    <?php if ($trabalhando == 'inicio_almoco') : ?>
                        <input class="inicio_almoco" name="inicio_almoco" type="submit" value="Iniciar almoço" />
                    <?php endif; ?>

                    <?php if ($trabalhando == 'almocando') : ?>
                        <input class="final_almoco" name="final_almoco" type="submit" value="Finalizar almoço" />
                    <?php endif; ?>

                    <?php if ($trabalhando == 'trabalhando') : ?>
                        <input class="sair" name="sair" type="submit" value="Finalizar expediente" />
                    <?php endif; ?>

                    <?php if ($trabalhando == 'fim') : ?>
                        <p class="bemvindo">Você já trabalhou hoje</p>
                    <?php endif; ?>
                </form>

                <form method="post" action="meus_horarios.php?id=<?php echo htmlspecialchars($funcionario_cpf); ?>">
                    <input class="name_verm" type="submit" name="name_ver" value="Meus Horários">
                </form>

                <form method="post">
                    <input class="sair-conta" name="sair-conta" type="submit" value="Sair da conta" />
                </form>

                <p class="info bt">GTS Net</p>
            </div>
        </div>
    </div>

</body>

</html>