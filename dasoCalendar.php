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

  // Escribir los datos de los pacientes
  $row = 2; // Empezamos en la fila 2 porque la 1 es para los encabezados
  foreach ($results as $paciente) {
    $sheet->setCellValue('A' . $row, $paciente['from']);
    $sheet->setCellValue('B' . $row, $paciente['to']);
    $sheet->setCellValue('C' . $row, $paciente['name']);
    $sheet->setCellValue('D' . $row, $paciente['status']);
    $row++;
  }

  // Configurar el archivo para la descarga
  $filename = "pacientes_" . date('Y-m-d') . ".xlsx";

  // Enviar el archivo al navegador para la descarga
  header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
  header('Content-Disposition: attachment;filename="' . $filename . '"');
  header('Cache-Control: max-age=0');

  // Crear el escritor de Excel y guardar el archivo en la salida
  $writer = new Xlsx($spreadsheet);
  $writer->save('php://output');
}

// obtengo todos los eventos
$results = [];
if (isset($_GET['desde']) && $_GET['desde'] != null) {

  $desde =  $_GET['desde'];
  $desde = DateTime::createFromFormat('d/m/Y', $desde);
  $desde = $desde->format('Y-m-d');

  $hasta =  $_GET['hasta'];
  $hasta = DateTime::createFromFormat('d/m/Y', $hasta);
  $hasta = $hasta->format('Y-m-d');

  $arData = [
    'crm_source_status' => [
      'method' => 'calendar.event.get',
      'params' => [
        'type' => 'group',
        'ownerId' => '5',
        'from' => $desde,
        'to' => $hasta,
        'section' => [84, 85, 86, 88, 89],
      ]
    ]
  ];

  $result = CRest::callBatch($arData, $halt = 0);
  $query = [];
  $results = $result['result']['result']['crm_source_status'];
  foreach ($results as $res) {
    $query['execution' . $res['ID']] = [
      'method' => 'calendar.event.getbyid',
      'params' => [
        'id' => $res['ID'],
      ]
    ];
  }

  $query = array_chunk($query, 50);
  $result = [];
  //se obtienen de nuevo con este metodo para el deal_id
  $allEvents = [];
  foreach ($query as $qr) {
    $results = CRest::callBatch($qr, $halt = 0);
    $allEvents = array_merge($allEvents, $results['result']['result']);
  }
  $queries = [];
  $results = [];
  foreach ($allEvents as $find) {
    if ($find['SECTION_ID'] == 84) {
      $status = 'evaluation';
    }
    if ($find['SECTION_ID'] == 85) {
      $status = 'free eval';
    }
    if ($find['SECTION_ID'] == 86) {
      $status = 're-evaluation';
    }
    if ($find['SECTION_ID'] == 88) {
      $status = 'emergency';
    }
    if ($find['SECTION_ID'] == 89) {
      $status = 'vip';
    }

    $deal_id = null;
    if (isset($find['~DESCRIPTION'])) {
      $description = $find['~DESCRIPTION'];
      // obtenemos el deal_id de un href
      if ($description != null) {
        $dom = new DOMDocument();
        @$dom->loadHTML($description);
        $links = $dom->getElementsByTagName('a');
        foreach ($links as $link) {
          $deal_id = $link->getAttribute('href');
          $deal_id = explode('/', $deal_id);
          $deal_id = $deal_id[4];
        }
      }
    }
    $from = $find['DATE_FROM'];
    if ($find['TZ_FROM'] == 'Europe/Dublin') {
      // Crear un objeto DateTime con la zona horaria de Dublín
      $from = DateTime::createFromFormat('m/d/Y h:i:s a', $from, new DateTimeZone('Europe/Dublin'));

      // Cambiar la zona horaria a Nueva York
      $from->setTimezone(new DateTimeZone('America/New_York'));

      // Imprimir la fecha y hora convertida
      $from = $from->format('m/d/Y h:i:s a');
    }
    $to = $find['DATE_TO'];
    if ($find['TZ_TO'] == 'Europe/Dublin') {
      // Crear un objeto DateTime con la zona horaria de Dublín
      $to = DateTime::createFromFormat('m/d/Y h:i:s a', $to, new DateTimeZone('Europe/Dublin'));

      // Cambiar la zona horaria a Nueva York
      $to->setTimezone(new DateTimeZone('America/New_York'));

      // Imprimir la fecha y hora convertida
      $to = $to->format('m/d/Y h:i:s a');
    }
    $event = [
      'name' => $find['NAME'],
      'from' => $from,
      'to' => $to,
      'status' => $status,
      'deal_id' => $deal_id,
      'state' => null,
      'edad' => null
    ];
    $results[] = $event;
    if ($deal_id != null) {
      $queries[$deal_id] = [
        'method' => 'crm.deal.list',
        'params' => [
          'filter' => [
            'ID' => (int) $deal_id
          ],
          'select' => [
            'UF_CRM_6596BEA5BA903',
            'UF_CRM_1722807403'
          ]
        ]
      ];
    }
  }

  $queries = array_chunk($queries, 50);
  $allEvents = [];
  foreach ($queries as $query) {
    $execute = CRest::callBatch($query, $halt = 0);
    $allEvents = array_merge($allEvents, $execute['result']['result']);
  }
  $arr = [];
  foreach ($allEvents as $event) {
    if (isset($event[0])) {
      $state = $event[0]['UF_CRM_6596BEA5BA903'];
      $edad = $event[0]['UF_CRM_1722807403'];
      $state = giveState($state);
      $arr[$event[0]['ID']] = array(
        'state' => $state,
        'edad' => $edad
      );
    }
  }
  foreach ($results as &$result) {
    if (array_key_exists($result['deal_id'], $arr)) {
      $result['state'] = $arr[$result['deal_id']]['state'];
      $result['edad'] = $arr[$result['deal_id']]['edad'];
    }
  }
  if (isset($_GET['exportar'])) {
    exportar($results);
  }
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
          <th>Estado</th>
          <th>Edad</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($results as $result) : ?>
          <tr>
            <td>
              <?= $result['from']; ?>
            </td>
            <td>
              <?= $result['to']; ?>
            </td>
            <td>
              <?= $result['name']; ?>
            </td>
            <td>
              <?= $result['status']; ?>
            </td>
            <td>
              <?= $result['state']; ?>
            </td>
            <td>
              <?= giveEdad($result['edad']); ?>
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
