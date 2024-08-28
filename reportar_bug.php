<?php

session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require './PHPMailer/src/Exception.php';
require './PHPMailer/src/PHPMailer.php';
require './PHPMailer/src/SMTP.php';

if (isset($_GET['id'])) {
    $funcionario_cpf = $_GET['id'];
} else {
    die('Parâmetro "id" não encontrado.');
}

if (empty($_SESSION['logado'])) {
    header("Location: ./index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['reportar'])) {

    $bug = $_POST['bug'];

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'netgts14@gmail.com';
        $mail->Password = 'yxcb wpfz fseh johi';
        $mail->SMTPSecure = 'ssl';
        $mail->Port = 465;

        $mail->setFrom('netgts14@gmail.com');
        $mail->addAddress('joaogabriel.sena@gtsnet.com.br');

        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = 'SISTEMA DE PONTO: Bug Report de ' . $_SESSION['username'];
        $mail->Body = $bug;

        if ($mail->send()) {
            echo '<script>alert("Bug reportado com sucesso!")</script>';
            header("Location: index.php?id=" . urlencode($funcionario_cpf));
            exit();
        } else {
            echo '<script>alert("Erro ao reportar bug. Tente novamente.")</script>';
        }
    } catch (Exception $e) {
        echo '<script>alert("Erro ao enviar o e-mail: ' . $mail->ErrorInfo . '")</script>';
    }
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
    <title>Reportar Bug</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>

    <div class="ie-fixMinHeight">
        <div class="main">
            <div class="wrap animated fadeIn" id="principal">
                <img id="logogts" src="img/logo_gts.png" />
                <form action="./reportar_bug.php?id=<?php echo htmlspecialchars($funcionario_cpf); ?>" method="post">
                    <label for="bug">Detalhe o bug encontrado:</label>
                    <br>
                    <textarea id="bug" name="bug" rows="4" cols="34" required></textarea>
                    <br><br>
                    <input name="reportar" type="submit" value="Reportar" />
                </form>
                <p class="info bt">GTS Net</p>

            </div>
        </div>
    </div>

</body>

</html>
