<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="https://gtsnet.com.br/wp-content/uploads/sites/98/2020/08/cropped-favicon-32x32.png" sizes="32x32">
    <title>Adicionar Funcionário</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="ie-fixMinHeight">
    <div class="main">
        <div class="wrap animated fadeIn" id="principal">
            <img id="logogts" src="img/logo_gts.png" />

            <form name='login' method="post" action="add_funcionario.php">

<?php

date_default_timezone_set('America/Sao_Paulo');

session_start();

if (!isset($_SESSION['admin']) || $_SESSION['admin'] != true) {
    header("Location: login.php");
    exit();
}

function validaCPF($cpf)
{

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

include("./db/config.php");

if(isset($_POST['registrar'])){

    $username = $_POST['username'];
    $email = $_POST['email'];
    $cpf = $_POST['cpf'];
    $cpf = str_replace(array('(', ')', '-', '.'), '', $cpf);
    $cargo = $_POST['cargo'];
    $turno = $_POST['turno'];
    $data_admissao = $_POST['data_admissao'];
    $horario_entrada = $_POST['horario_entrada'];

    if (empty($username) || empty($cpf) || empty($cargo) || empty($turno) || empty($data_admissao) || empty($horario_entrada)) {
        echo'<style>.infort {
            color: red;
            text-align: center;
            margin-bottom: 30px
            }</style>
        
            <p class="infort" >Preencha todos os campos obrigatórios</p>';
    }

    elseif(strlen($cpf) != 11 || !validaCPF($cpf)){

        echo'<style>.infort {
            color: red;
            text-align: center;
            margin-bottom: 30px
            }</style>
        
            <p class="infort" >CPF inválido</p>';
    }

    else{

        $sql_verifica = "SELECT * FROM funcionarios WHERE nome = ? OR email = ? OR cpf = ?";
        $stmt = $conn->prepare($sql_verifica);
        $stmt->bind_param("sss", $username, $email, $cpf);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {

            echo'<style>.infort {
                color: red;
                text-align: center;
                margin-bottom: 30px
                }</style>
            
                <p class="infort" >Usuário existente</p>';

        }

        else{

            $sql_insert = "INSERT INTO funcionarios (nome, email, cpf, cargo, turno, horario_entrada, data_admissao) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql_insert);
            $stmt->bind_param("sssssss", $username, $email, $cpf, $cargo, $turno, $horario_entrada, $data_admissao);
        
            if ($stmt->execute()) {

                header("Location: funcionarios.php");
                exit();
            } else {
                echo "Erro: " . $sql_insert . "<br>" . $conn->error;
            }

            
        }
    }

}

?>

                    <label>
                        <img class="ico" src="img/user.svg" alt="#" />
                        <input name="username" type="text" placeholder="Nome *" />
                    </label>

                    <label>
                        <img class="ico" src="img/email.svg" alt="#" />
                        <input name="email" type="email" placeholder="E-Mail" />
                    </label>

                    <label>
                        <img class="ico" src="img/cpf.svg" alt="#" />
                        <input name="cpf" type="number" placeholder="CPF *" />
                    </label>

                    <label for="cargo">Cargo:</label>
                    <select id="cargo" name="cargo">
                        <option value="Operacional">Operacional</option>
                        <option value="Administrativo">Administrativo</option>
                    </select>

                    <label for="turno">Turno:</label>
                    <select id="turno" name="turno">
                        <option value="manha">Manhã</option>
                        <option value="tarde">Tarde</option>
                        <option value="dia_todo">Dia todo</option>
                    </select>

                    <label for="data_admissao">Data de Admissão:</label>
                    <input type="date" id="data_admissao" name="data_admissao" value="<?php echo date("Y-m-d"); ?>"><br><br>
                    
                    <label for="horario_entrada">Horário de entrada:</label>
                    <input type="time" id="horario_entrada" name="horario_entrada" value="<?php echo date("H:m"); ?>"><br><br>

                    <label for="admin">Admin:</label>
                    <select id="admin" name="admin">
                        <option value="0">Não</option>
                        <option value="1">Sim</option>
                    </select>

                <input type="submit" name="registrar" value="Adicionar">
            </form>

            <p class="info bt">GTS Net</p>
        </div>
    </div>
</div>

</body>
</html>
