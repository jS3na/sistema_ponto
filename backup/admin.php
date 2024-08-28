<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <meta http-equiv="pragma" content="no-cache" />
    <meta http-equiv="expires" content="-1" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" href="https://gtsnet.com.br/wp-content/uploads/sites/98/2020/08/cropped-favicon-32x32.png" sizes="32x32">
    <title>Admin</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>

    <div class="ie-fixMinHeight">
        <div class="main">
            <div class="wrap animated fadeIn">
                <form name="registro" method="post" action="admin.php" onsubmit="return camposPreenchidos()">
                    <img id="logogts" src="img/logo_gts.png" />

                    <?php
                    session_start(); // Inicie a sessão no início do script

                    include("config.php"); // Inclua a configuração do banco de dados

                    if (isset($_POST['admin'])) {
                        $cpf = $_POST['password']; // Recebe a senha
                        $cpf = str_replace(array('(', ')', '-', '.'), '', $cpf);

                        // Verifica se tem algum admin com essa senha
                        $sql_verifica = "SELECT * FROM funcionarios WHERE cpf = ? AND admin = 1";
                        $stmt = $conn->prepare($sql_verifica);
                        $stmt->bind_param("s", $cpf);
                        $stmt->execute();
                        $result = $stmt->get_result();

                        // Se não tiver admin com essa senha
                        if ($result->num_rows == 0) {
                            echo '<style>.infort {
                                color: red;
                                text-align: center;
                                margin-bottom: 30px;
                            }</style>
                            <p class="infort">Você não tem permissão</p>';
                        } else {
                            // Se tiver admin com esse CPF, inicia a sessão e redireciona para a página de funcionários
                            $_SESSION['admin'] = true;
                            header("Location: funcionarios_dia.php");
                            exit(); // Certifique-se de chamar exit() após o redirecionamento
                        }
                    }
                    ?>

                    <label>
                        <img class="ico" src="img/password.svg" alt="#" />
                        <input id="password" name="password" type="password" placeholder="CPF *" />
                    </label>

                    <input name="admin" id="conectar" type="submit" value="Conectar" />
                </form>
                <p class="info bt">GTS Net</p>

            </div>
        </div>
    </div>

</body>

</html>
