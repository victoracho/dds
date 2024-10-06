<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);
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
  $sheet->setCellValue('F1', 'Doctor');
  $sheet->setCellValue('G1', 'Salon');

  // Escribir los datos de los pacientes
  $row = 2; // Empezamos en la fila 2 porque la 1 es para los encabezados
  foreach ($results as $paciente) {
    $doctor = null;
    if ($paciente['doctor'] == 1) {
      $doctor = 'doctora stover';
    }
    if ($paciente['doctor'] == 2) {
      $doctor = 'doctora lora';
    }
    if ($paciente['doctor'] == 3) {
      $doctor = 'doctora ortega';
    }

    $sheet->setCellValue('A' . $row, $paciente['start']);
    $sheet->setCellValue('B' . $row, $paciente['end']);
    $sheet->setCellValue('C' . $row, $paciente['title']);
    $sheet->setCellValue('D' . $row, $paciente['status']);
    $sheet->setCellValue('E' . $row, $paciente['substatus']);
    $sheet->setCellValue('F' . $row, $doctor);
    $sheet->setCellValue('G' . $row, $paciente['salon']);
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
  $dbname = "daso";
  // Create connection
  $conn = mysqli_connect($servername, $username, $password, $dbname);
  // Check connection
  if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
  }

  if (empty($status)) {
    $status = null;
  }

  $sql = "SELECT * FROM appointments where start between '$desde' AND '$hasta' and deal_id is not null and status is not null";


  $result = mysqli_query($conn, $sql);
  $results = [];

  if (mysqli_num_rows($result) > 0) {
    // output data of each row
    while ($res = mysqli_fetch_assoc($result)) {
      $from = new DateTime($res['start']);
      $from = $from->format("Y/m/d H:i");

      $end = new DateTime($res['end']);
      $end = $end->format("Y/m/d H:i");
      $doctor = null;
      if ($res['doctor'] == 1) {
        $doctor = 'doctora stover';
      }
      if ($res['doctor'] == 2) {
        $doctor = 'doctora lora';
      }
      if ($res['doctor'] == 3) {
        $doctor = 'doctora ortega';
      }

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
          'doctor' => $doctor,
          'salon' => $res['salon'],
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
}

if (isset($_GET['exportar'])) {
  exportar($results);
}

?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pacientes - DataTable</title>
  <!-- Incluir DataTables CSS y jQuery -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.5/css/jquery.dataTables.min.css">
  <!-- jQuery UI CSS para el Datepicker -->
  <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.js"></script>
  <script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
</head>

<body>
  <div class="container">
    <h2>Lista de citas en Daso</h2>
    <!-- Filtros de Fecha -->
    <label for="fecha_desde">Fecha Desde:</label>
    <form action="dasoCalendar.php" method="GET">
      <input type="text" id="desde" name="desde" placeholder="Selecciona la fecha desde">
      <label for="fecha_hasta">Fecha Hasta:</label>
      <input type="text" id="hasta" name="hasta" placeholder="Selecciona la fecha hasta">
      <button submit id="filtrar">Filtrar</button>
      <button submit id="exportar" name="exportar" type="submit">Exportar a Excel</button>
    </form>
    <table id="tablaPacientes" class="display">
      <thead>
        <tr>
          <th>Fecha Desde</th>
          <th>Fecha Hasta</th>
          <th>Nombre Paciente</th>
          <th>Status</th>
          <th>Substatus</th>
          <th>Doctor</th>
          <th>Salon</th>
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
              <?= $result['doctor']; ?>
            </td>
            <td>
              <?= $result['salon']; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
      <br>
      <br>
      <br>
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
      var table = $('#tablaPacientes').DataTable();
    });
  </script>
</body>

</html>
