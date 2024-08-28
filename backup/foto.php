<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta http-equiv="pragma" content="no-cache" />
    <meta http-equiv="expires" content="-1" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" href="https://gtsnet.com.br/wp-content/uploads/sites/98/2020/08/cropped-favicon-32x32.png" sizes="32x32">
    <title>Foto</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php

    session_start();

    include("config.php");

    date_default_timezone_set('America/Sao_Paulo');

    $funcionario_cpf = '';

    include("config.php");

    $hoje_TOTAL = date('Y-m-d');

    if (isset($_GET['id'])) {
        $funcionario_cpf = $_GET['id'];
        $funcionario_id = $_GET['id2'];
        $atual = $_GET['atual'];
    }

    if (isset($_POST['enviarJustificativa'])) {

        $justificativa = trim($_POST['justificativa']);
        $caracteres_especiais = '/^[\.,!]+$/';

        if(empty($justificativa) || preg_match($caracteres_especiais, $justificativa)){
            echo '<script>alert("Digite uma justificativa válida antes de enviar")</script>';
        }
        else{
            $_SESSION['justificativa'] = $_POST['justificativa'];
            $_SESSION['atrasado'] = false;
        }
    }

    if (isset($_POST['photo'])) {
        $data = $_POST['photo'];

        // Remove o prefixo "data:image/png;base64,"
        $data = str_replace('data:image/png;base64,', '', $data);
        $data = str_replace(' ', '+', $data);
        $data = base64_decode($data);

        // Define o caminho e o nome do arquivo com a latitude e longitude
        $filePath = 'uploads/photo_' . $funcionario_cpf . '_' . date('Y-m-d') . '_' . $atual . '.png';

        // Salva a imagem no servidor
        file_put_contents($filePath, $data);

        $hoje = date('Y-m-d');
        echo $hoje; // Para depuração, pode ser removido posteriormente

        if ($atual == 'entrando') {
            $sql_verifica = "SELECT f.id, p.hora_entrada
                            FROM funcionarios f 
                            LEFT JOIN pontos p ON f.id = p.funcionario_id 
                            WHERE f.cpf = ? AND data = ?";

            $stmt = $conn->prepare($sql_verifica);
            if (!$stmt) {
                echo "Erro na preparação da consulta: " . $conn->error;
                exit();
            }
            $stmt->bind_param("is", $funcionario_cpf, $hoje);
            $stmt->execute();
            $result3 = $stmt->get_result();
            $row3 = $result3->fetch_assoc();

            if (is_null($row3)) {

                $trabalhando = "trabalhando";
                $hoje_entrar = date('Y-m-d');
                $horario = date('H:i');

                // Inserir novo usuário
                $sql = "INSERT INTO pontos (funcionario_id, data, hora_entrada, justificativa) VALUES (?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("isss", $funcionario_id, $hoje_entrar, $horario, $_SESSION['justificativa']);

                if (!$stmt->execute()) {
                    echo "Erro: " . $sql . "<br>" . $conn->error;
                }

            }
        } elseif ($atual == 'saindo') {
            
            $sql_verifica = "SELECT * FROM pontos WHERE funcionario_id = ? AND data = ? AND hora_saida IS NULL";
            $stmt = $conn->prepare($sql_verifica);
            $stmt->bind_param("is", $funcionario_id, $hoje);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows != 0) {
                $horario = date('H:i');
                $hoje = date('Y-m-d');

                $sql = "UPDATE pontos SET hora_saida = ? WHERE funcionario_id = ? AND data = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sis", $horario, $funcionario_id, $hoje);

                if (!$stmt->execute()) {
                    echo "Erro: " . $sql . "<br>" . $conn->error;
                }

            } else {
                $trabalhando = "fim";
            }
        }

        header("Location: index.php?id=" . $funcionario_cpf);
        exit();

    } else {
        echo "Nenhuma foto enviada.";
    }

    ?>

    <div class="ie-fixMinHeight">
        <div class="main">
            <div class="wrap animated fadeIn" id="principal">
                <img id="logogts" src="img/logo_gts.png"/>

                <?php if($_SESSION['atrasado']): ?>

                    <form action="foto.php?id=<?php echo htmlspecialchars($_GET['id']); ?>&id2=<?php echo htmlspecialchars($_GET['id2']); ?>&atual=<?php echo htmlspecialchars($_GET['atual']); ?>" method="post">
                        <label for="justificativa">Justifique o atraso de 5 minutos antes de prosseguir:</label>
                        <br>
                        <textarea id="justificativa" name="justificativa" rows="4" cols="34" required></textarea>
                        <br><br>
                        <input name="enviarJustificativa" type="submit" value="Justificar "/>
                    </form>

                <?php else: ?>

                <div id="overlay" class="overlay">
                    <div class="boxtxt">
                    <div id="mensagem">Você deve permitir o acesso à câmera para prosseguir.</div>
                    <br>
                    <button onclick="permitirAcesso()">OK</button>
                    <br>
                    </div>
                </div>
                <video id="video" width="325" height="430" muted autoplay playsinline></video>
                <input type="submit" id="capture" value="Capturar Foto">
                <p id="perm_cam"></p>
                <canvas id="canvas" id="photo" width="325" height="430" style="display:none;"></canvas>
                <form id="photoForm" method="post" enctype="multipart/form-data" action="foto.php?id=<?php echo htmlspecialchars($_GET['id']); ?>&id2=<?php echo htmlspecialchars($_GET['id2']); ?>&atual=<?php echo htmlspecialchars($_GET['atual']); ?>">
                    <input type="hidden" name="photo" id="photo">
                    <img id="photoPreview" src="" alt="Sua foto" style="display:none; width:325px; height:430px;"/>
                    <input type="submit" style="display:none;" id="sendPhotoButton" value="Enviar Foto">
                </form>

                <?php endif;?>
                
                <p class="info bt">GTS Net</p>
            </div>
        </div>
    </div>

    <script>

        var quantLoc = 0;

        exibirOverlay("Você deve permitir o acesso à câmera para prosseguir.");
        var permissao = true;

        function exibirOverlay(mensagem) {
                var overlay = document.getElementById('overlay');
                var mensagemElemento = document.getElementById('mensagem');
                mensagemElemento.textContent = mensagem;
                overlay.style.display = 'block';
            }

        function permitirAcesso() {
            document.getElementById('overlay').style.display = 'none';
            permCam();
        }

        // Acessa a câmera
        function permCam() {
        navigator.mediaDevices.getUserMedia({ audio: false, video: true })
        .then(function(stream) {
            document.getElementById('video').srcObject = stream;
        })
        .catch(function(error) {
        document.getElementById("perm_cam").innerHTML = "Você deve aceitar o acesso a câmera para prosseguir com o ponto.";
        permissao = false;
    });}

        // Captura a foto
        document.getElementById('capture').addEventListener('click', function() {
            var canvas = document.getElementById('canvas');
            var context = canvas.getContext('2d');
            context.drawImage(document.getElementById('video'), 0, 0, canvas.width, canvas.height);

            var dataUrl = canvas.toDataURL('image/png');
            document.getElementById('photo').value = dataUrl;

            // Mostra a pré-visualização da foto
            if (permissao){
            var photoPreview = document.getElementById('photoPreview');
            photoPreview.src = dataUrl;
            photoPreview.style.display = 'block';

            // Mostra o botão de enviar

                document.getElementById('sendPhotoButton').style.display = 'inline';
            }
        });

    </script>
</body>
</html>
