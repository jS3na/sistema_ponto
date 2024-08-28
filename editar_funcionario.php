<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="https://gtsnet.com.br/wp-content/uploads/sites/98/2020/08/cropped-favicon-32x32.png" sizes="32x32">
    <title>Editar Funcionário</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="ie-fixMinHeight">
    <div class="main">
        <div class="wrap animated fadeIn" id="principal">
            <img id="logogts" src="img/logo_gts.png" />

            <?php
            session_start();

            function validaCPF($cpf){

                $cpf = preg_replace('/[^0-9]/is', '', $cpf);

                if (strlen($cpf) != 11) {
                    return false;
                }

                if (preg_match('/(\d)\1{10}/', $cpf)) {
                    return false;
                }

                for ($t = 9; $t < 11; $t++) {
                    for ($d = 0, $c = 0; $c < $t; $c++) {
                        $d += $cpf[$c] * (($t + 1) - $c);
                    }
                    $d = ((10 * $d) % 11) % 10;
                    if ($cpf[$c] != $d) {
                        return false;
                    }
                }
                return true;
            }

            if (!isset($_SESSION['admin']) || $_SESSION['admin'] != true) {
                header("Location: login.php");
                exit();
            }

            include("./db/config.php");

            $funcionario_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

            if ($funcionario_id > 0) {
                $sql = "SELECT * FROM funcionarios WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $funcionario_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $funcionario = $result->fetch_assoc();
                $stmt->close();
            } else {
                echo '<p class="infort" style="color: red; text-align: center; margin-bottom: 30px">Funcionário não encontrado</p>';
                exit();
            }

            if (isset($_POST['editar'])) {
                $username = $_POST['username'];
                $email = $_POST['email'];
                $cpf = $_POST['cpf'];
                $cargo = $_POST['cargo'];
                $turno = $_POST['turno'];
                $data_admissao = $_POST['data_admissao'];
                $horario_entrada = $_POST['horario_entrada'];
                $admin = $_POST['admin'];

                if (empty($username) || empty($email) || empty($cpf) || empty($cargo) || empty($turno) || empty($data_admissao) || empty($horario_entrada)) {
                    echo '<p class="infort" style="color: red; text-align: center; margin-bottom: 30px">Preencha todos os campos obrigatórios</p>';
                } elseif (!validaCPF($cpf)) {
                    echo '<p class="infort" style="color: red; text-align: center; margin-bottom: 30px">CPF inválido</p>';
                } else {
                    $sql_update = "UPDATE funcionarios SET nome = ?, email = ?, cpf = ?, cargo = ?, turno = ?, data_admissao = ?, horario_entrada = ?, admin = ? WHERE id = ?";
                    $stmt = $conn->prepare($sql_update);
                    $stmt->bind_param("sssssssii", $username, $email, $cpf, $cargo, $turno, $data_admissao, $horario_entrada, $admin, $funcionario_id);

                    if ($stmt->execute()) {
                        header("Location: funcionarios.php");
                        exit();
                    } else {
                        echo "Erro: " . $sql_update . "<br>" . $conn->error;
                    }

                    $stmt->close();
                }
            }

            $conn->close();
            ?>

            <form name='editar' method="post" action="">
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($funcionario['id']); ?>">

                <label>
                    <img class="ico" src="img/user.svg" alt="#" />
                    <input name="username" type="text" placeholder="Nome *" value="<?php echo htmlspecialchars($funcionario['nome']); ?>" />
                </label>

                <label>
                    <img class="ico" src="img/email.svg" alt="#" />
                    <input name="email" type="email" placeholder="E-Mail *" value="<?php echo htmlspecialchars($funcionario['email']); ?>" />
                </label>

                <label>
                    <img class="ico" src="img/cpf.svg" alt="#" />
                    <input name="cpf" type="number" placeholder="CPF *" value="<?php echo htmlspecialchars($funcionario['cpf']); ?>" />
                </label>

                <label for="cargo">Cargo:</label>
                <select id="cargo" name="cargo">
                    <option value="Operacional" <?php echo $funcionario['cargo'] == 'Operacional' ? 'selected' : ''; ?>>Operacional</option>
                    <option value="Administrativo" <?php echo $funcionario['cargo'] == 'Administrativo' ? 'selected' : ''; ?>>Administrativo</option>
                </select>

                <label for="turno">Turno:</label>
                <select id="turno" name="turno">
                    <option value="manha" <?php echo $funcionario['turno'] == 'manha' ? 'selected' : ''; ?>>Manhã</option>
                    <option value="tarde" <?php echo $funcionario['turno'] == 'tarde' ? 'selected' : ''; ?>>Tarde</option>
                    <option value="dia_todo" <?php echo $funcionario['turno'] == 'dia_todo' ? 'selected' : ''; ?>>Dia todo</option>
                </select>

                <label for="data_admissao">Data de Admissão:</label>
                <input type="date" id="data_admissao" name="data_admissao" value="<?php echo htmlspecialchars($funcionario['data_admissao']); ?>"><br><br>

                <label for="horario_entrada">Horario de entrada:</label>
                <input type="time" id="horario_entrada" name="horario_entrada" value="<?php echo htmlspecialchars($funcionario['horario_entrada']); ?>"><br><br>

                <label for="admin">Admin:</label>
                <select id="admin" name="admin">
                    <option value="0" <?php echo $funcionario['admin'] == 0 ? 'selected' : ''; ?>>Não</option>
                    <option value="1" <?php echo $funcionario['admin'] == 1 ? 'selected' : ''; ?>>Sim</option>
                </select>

                <input type="submit" name="editar" value="Editar">
            </form>

            <p class="info bt">GTS Net</p>
        </div>
    </div>
</div>

</body>
</html>
