<?php
use Dompdf\Dompdf;

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['gerar_relatorio']) && isset($_GET['id']) && isset($_GET['mes'])) {

    require_once '../dompdf/vendor/autoload.php';
    include('../config.php');

    $funcionario_id = $_GET['id'];
    $mes = $_GET['mes'];
    $total_horas = $_GET['total_horas'];
    $nome = $_GET['nome'];

    $conn->set_charset('utf8mb4');

    $data_hoje = date('Y-m-d');

    $sql = "SELECT f.cpf, f.id, f.nome, f.email, f.status, p.funcionario_id, p.hora_entrada, p.justificativa, p.hora_saida, p.almoco_entrada, p.almoco_saida, p.data
            FROM funcionarios f
            LEFT JOIN pontos p ON f.id = p.funcionario_id
            WHERE f.id = ? AND DATE_FORMAT(p.data, '%Y-%m') = ?
            ORDER BY p.data DESC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $funcionario_id, $mes);

    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {

        $html = '
        <html>
        <head>
            <style>
                body { font-family: DejaVu Sans, sans-serif; }
                table { border-collapse: collapse; width: 100%; }
                th, td { border: 1px solid black; padding: 8px; text-align: left; }
                th { background-color: #f2f2f2; }
            </style>
        </head>
        <body>
            <h2>Relatório de Pontos</h2>
            <p>Nome: '.htmlspecialchars($nome).'</p>
            <p>Total de Horas Líquidas Trabalhadas: '.$total_horas.' horas</p>
            <table>
                <thead>
                    <tr>
                        <th>DATA</th>
                        <th>Horário de Entrada</th>
                        <th>Justificativa</th>
                        <th>Entrada ao almoço</th>
                        <th>Saída do almoço</th>
                        <th>Horário de Saída</th>
                    </tr>
                </thead>
                <tbody>';

        while ($row = $result->fetch_assoc()) {
            $html .= '<tr>
                        <td>'.htmlspecialchars($row['data']).'</td>
                        <td>'.htmlspecialchars($row['hora_entrada']).'</td>
                        <td>'.htmlspecialchars($row['justificativa']).'</td>
                        <td>'.htmlspecialchars($row['almoco_entrada']).'</td>
                        <td>'.htmlspecialchars($row['almoco_saida']).'</td>
                        <td>'.htmlspecialchars($row['hora_saida']).'</td>
                      </tr>';
        }

        $html .= '
                </tbody>
            </table>
        </body>
        </html>';

        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        $dompdf->stream('relatorio_do_'.$nome.'_do_mes_'.$mes.'.pdf', ['Attachment' => 1]);
    } else {
        echo "0 resultados";
    }

    $conn->close();
}
?>
