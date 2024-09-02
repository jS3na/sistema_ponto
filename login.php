<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <meta http-equiv="pragma" content="no-cache" />
    <meta http-equiv="expires" content="-1" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" href="https://gtsnet.com.br/wp-content/uploads/sites/98/2020/08/cropped-favicon-32x32.png" sizes="32x32">
    <title>Login</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>

    <div class="ie-fixMinHeight">
        <div class="main">
            <div class="wrap animated fadeIn" id="principal">
                <form name="login" method="post" action="login.php">
                    <img id="logogts" src="img/logo_gts.png"/>

                    <?php
                    session_start();
                    $_SESSION['login'] = false;

                    include("./db/config.php");

                    if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['logar'])){

                        $cpf = $_POST['cpf'];
                        $cpf = str_replace(array('(', ')', '-', '.'), '', $cpf);

                        if (empty($cpf)) {

                            echo '<style>.infort {
                                color: red;
                                text-align: center;
                                margin-bottom: 30px
                                }</style>
                                <p class="infort">Preencha todos os campos obrigatórios</p>';
                        } else {

                            $sql_verifica = "SELECT * FROM funcionarios WHERE cpf = ? AND status = 'ativo'";
                            $stmt = $conn->prepare($sql_verifica);
                            $stmt->bind_param("s", $cpf);
                            $stmt->execute();
                            $result = $stmt->get_result();

                            if ($result->num_rows == 0) {
                                echo '<style>.infort {
                                    color: red;
                                    text-align: center;
                                    margin-bottom: 30px
                                    }</style>
                                    <p class="infort">CPF incorreto</p>';
                            } else {
                                $row = $result->fetch_assoc();
				$_SESSION['login'] = true;
				$_SESSION['username'] = $row['nome'];

                                header("Location: index.php?id=" . $cpf);
                                exit();
                            }
                        }
                    }
                    ?>

                    <label>
                        <img class="ico" src="img/cpf.svg" alt="#" />
                        <input name="cpf" type="number" placeholder="CPF *" />
                    </label>

                    <input name="logar" id="conectar" type="submit" value="Logar"/>
                </form>
                <p class="info bt">GTS Net</p>

            </div>
        </div>
    </div>

</body>

</html>
