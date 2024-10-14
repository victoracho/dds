<?php
require_once(__DIR__ . '/crest.php');
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

function exportar($results)
{
  // Crear un nuevo archivo de Excel
  $spreadsheet = new Spreadsheet();
  $sheet = $spreadsheet->getActiveSheet();

  // Escribir los encabezados de la tabla
  $sheet->setCellValue('A1', 'Fecha Desde');
  $sheet->setCellValue('B1', 'Fecha Hasta');
  $sheet->setCellValue('C1', 'Nombre Paciente');
  $sheet->setCellValue('D1', 'Status');
  $sheet->setCellValue('E1', 'Substatus');
  $sheet->setCellValue('F1', 'Estado');
  $sheet->setCellValue('G1', 'Edad');

  // Escribir los datos de los pacientes
  $row = 2; // Empezamos en la fila 2 porque la 1 es para los encabezados
  foreach ($results as $paciente) {
    $sheet->setCellValue('A' . $row, $paciente['start']);
    $sheet->setCellValue('B' . $row, $paciente['end']);
    $sheet->setCellValue('C' . $row, $paciente['title']);
    $sheet->setCellValue('D' . $row, $paciente['status']);
    $sheet->setCellValue('E' . $row, $paciente['substatus']);
    $sheet->setCellValue('F' . $row, $paciente['estado']);
    $sheet->setCellValue('G' . $row, $paciente['edad']);
    $row++;
  }

  // Configurar el archivo para la descarga
  $filename = "pacientes_" . date('Y-m-d') . ".xlsx";

  // Enviar el archivo al navegador para la descarga
  header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
  header('Content-Disposition: attachment;filename="archivo.xlsx"');
  header('Cache-Control: max-age=0');
  header('Cache-Control: max-age=1'); // Requerido para IE11 y versiones anteriores

  // Evitar almacenamiento en caché
  header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
  header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
  header('Cache-Control: cache, must-revalidate'); // Para HTTP/1.1
  header('Pragma: public'); // Para HTTP/1.0

  // Crear el escritor de Excel y guardar el archivo en la salida
  $writer = new Xlsx($spreadsheet);
  $writer->save('php://output');
}

// obtengo todos los eventos
$results = [];
if (isset($_GET['desde']) && $_GET['desde'] != null) {

  if ($_GET['desde'] <  $_GET['hasta']) {
    $desde =  $_GET['desde'];
    $fechaObj = DateTime::createFromFormat('d/m/Y', $desde);
    $formatoISO = $fechaObj->format('Y-m-d\TH:i:s');


    $desde = DateTime::createFromFormat('d/m/Y', $desde);
    $desde = $desde->format('Y-m-d');

    $hasta =  $_GET['hasta'];
    $hasta = DateTime::createFromFormat('d/m/Y', $hasta);
    $hasta = $hasta->format('Y-m-d');

    if ($desde == $hasta) {
      $desde = $desde . 'T00:00:00';
      $hasta = $hasta . 'T23:59:00';
    }

    $servername = "16.171.204.95";
    $username = "bitrix";
    $password = "8726231";
    $dbname = "miami";
    // Create connection
    $conn = mysqli_connect($servername, $username, $password, $dbname);
    // Check connection
    if (!$conn) {
      die("Connection failed: " . mysqli_connect_error());
    }

    if (empty($status)) {
      $status = null;
    }

    $sql = "SELECT * FROM appointments where start between '$desde' AND '$hasta' and status is not null and status !='deleted' ";


    $result = mysqli_query($conn, $sql);
    $results = [];

    if (mysqli_num_rows($result) > 0) {
      // output data of each row
      while ($res = mysqli_fetch_assoc($result)) {
        $from = new DateTime($res['start']);
        $from = $from->format("Y/m/d H:i");

        $end = new DateTime($res['end']);
        $end = $end->format("Y/m/d H:i");


        $results[] =
          [
            'id' => $res['id'],
            'deal_id' => $res['deal_id'],
            'substatusColor' => $res['substatus'],
            'allDay' => false,
            'title' => $res['name'],
            'status' => $res['status'],
            'start' => $from,
            'end' => $end,
            'comment' => $res['comment'],
            'substatus' => $res['substatus'],
            'phone' => $res['phone'],
            'user' => $res['user'],
            'amount' => $res['amount'],
            'invoice_number' => $res['invoice_number'],
            'lodging' => $res['lodging'],
            'more_invoices' => $res['more_invoices'],
            'edad' => $res['edad'],
            'estado' => $res['estado'],
            'transportation' => $res['transportation'],
            'previous_status' => $res['previous_status'],
            'user_modified' => $res['user_modified'],
            'date_created' => $res['date_created'],
            'date_modified' => $res['date_modified']
          ];
      }
    }
    mysqli_close($conn);

    if (empty($results)) {
      $results[] = [];
    }
    $quantity = array(
      'free eval' => 0,
      'evaluation' => 0,
      're-evaluation' => 0,
      'emergency' => 0,
      'vip' => 0,
      'missing-appointment' => 0,
      'payed' => 0,
      'not payed' => 0,
      'deleted' => 0,
    );
    foreach ($results as $res) {
      if ($res['status'] == 'free eval') {
        $quantity['free eval']++;
      }
      if ($res['status'] == 'evaluation') {
        $quantity['evaluation']++;
      }
      if ($res['status'] == 're-evaluation') {
        $quantity['re-evaluation']++;
      }
      if ($res['status'] == 'emergency') {
        $quantity['emergency']++;
      }
      if ($res['status'] == 'vip') {
        $quantity['vip']++;
      }
      if ($res['status'] == 'missing-appointment') {
        $quantity['missing-appointment']++;
      }
      if ($res['status'] == 'payed') {
        $quantity['payed']++;
      }
      if ($res['status'] == 'not payed') {
        $quantity['not payed']++;
      }
      if ($res['status'] == 'deleted') {
        $quantity['deleted']++;
      }
    }
  }
}


?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pacientes - DataTable</title>
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.1/css/jquery.dataTables.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
  <script src="https://cdn.rawgit.com/bpampuch/pdfmake/0.1.36/build/pdfmake.min.js"></script>
  <script src="https://cdn.rawgit.com/bpampuch/pdfmake/0.1.36/build/vfs_fonts.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
  <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
  <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.js"></script>
</head>

<body>
  <div class="container">
    <h2>Lista de citas en Miami</h2>
    <!-- Filtros de Fecha -->
    <label for="fecha_desde">Fecha Desde:</label>
    <form action="miamiCalendar.php" method="GET">
      <input type="text" id="desde" name="desde" placeholder="Selecciona la fecha desde">
      <label for="fecha_hasta">Fecha Hasta:</label>
      <input type="text" id="hasta" name="hasta" placeholder="Selecciona la fecha hasta">
      <button submit id="filtrar">Filtrar</button>
    </form>
    <br>
    <table id="example" class="display nowrap" style="width:100%">
      <thead>
        <tr>
          <th>Fecha Desde</th>
          <th>Fecha Hasta</th>
          <th>Nombre Paciente</th>
          <th>Status</th>
          <th>Substatus</th>
          <th>Estado</th>
          <th>Edad</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($results as $result) : ?>
          <tr>
            <td>
              <?= $result['start']; ?>
            </td>
            <td>
              <?= $result['end']; ?>
            </td>
            <td>
              <?= $result['title']; ?>
            </td>
            <td>
              <?= $result['status']; ?>
            </td>
            <td>
              <?= $result['substatus']; ?>
            </td>
            <td>
              <?= $result['estado']; ?>
            </td>
            <td>
              <?= $result['edad']; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

  </div>
  <script>
    // Inicializar el DataTable cuando la página esté lista
    $(document).ready(function() {
      $("#desde").datepicker({
        dateFormat: 'dd/mm/yy'
      });
      $("#hasta").datepicker({
        dateFormat: 'dd/mm/yy'
      });
      // Insertar datos en la tabla
      $('#example').DataTable({
        dom: 'Bfrtip', // Esto coloca los botones antes de la tabla (B=botones, f=filtro, r=procesando, t=tabla, i=info, p=paginación)
        buttons: [
          'copy', 'excel', 'pdf', 'print'
        ]
      });
    });
  </script>
</body>

</html>
