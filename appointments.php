<?php
require_once(__DIR__ . '/crest.php');
header("Access-Control-Allow-Origin: *");
//109657
// calendario de miami

$range = $_GET['range'];
$status = strtolower($_GET['status']);

$substatus = $_GET['substatus'];
if ($substatus != 'All Substatus') {
  $substatus =  "substatus = '$substatus' AND";
}
if ($substatus === 'All Substatus') {
  $substatus = null;
}

$range = explode(",", $range);
$ini = parse_ini_file('app.ini');
$servername = $ini['servername'];
$username = $ini['db_user'];
$password = $ini['db_password'];
$dbname = $ini['db_name'];



// Create connection
$conn = mysqli_connect($servername, $username, $password, $dbname);
// Check connection
if (!$conn) {
  die("Connection failed: " . mysqli_connect_error());
}

if (empty($status)) {
  $status = null;
}
$sql = "SELECT * FROM appointments where $substatus status in ('$status') AND start between '$range[0]' AND '$range[1]' ";

$result = mysqli_query($conn, $sql);
$results = [];

if (mysqli_num_rows($result) > 0) {
  // output data of each row
  while ($res = mysqli_fetch_assoc($result)) {
    if ($res['status'] == 'evaluation') {
      $status = '#bd7ac9';
    }
    if ($res['status'] == 'follow up') {
      $status = '#fff300';
    }
    if ($res['status'] == 'surgery') {
      $status = '#86b100';
    }
    if ($res['status'] == 'deleted') {
      $status = '#333';
    }

    // substatus 
    if ($res['substatus'] == 'not specified') {
      $substatus = '#F0F0F0';
    }
    if ($res['substatus'] == 'simple') {
      $substatus = '#15870b';
    }
    if ($res['substatus'] == 'combo simple') {
      $substatus = '#f0e351';
    }
    if ($res['substatus'] == 'combo plus') {
      $substatus = '#d4021e';
    }

    $results[] =
      [
        'id' => $res['id'],
        'deal_id' => $res['deal_id'],
        'substatusColor' => $substatus,
        'allDay' => false,
        'title' => $res['name'],
        'backgroundColor' => $status,
        'status' => $res['status'],
        'start' => $res['start'],
        'end' => $res['end'],
        'comment' => $res['comment'],
        'substatus' => $res['substatus'],
        'phone' => $res['phone'],
        'user' => $res['user'],
        'doctor' => $res['doctor'],
        'salon' => $res['salon'],
        'amount' => $res['amount'],
        'invoice_number' => $res['invoice_number'],
        'lodging' => $res['lodging'],
        'more_invoices' => $res['more_invoices'],
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
  'evaluation' => 0,
  'follow up' => 0,
  'surgery' => 0,
  'deleted' => 0,
);
foreach ($results as $res) {
  if ($res['status'] == 'evaluation') {
    $quantity['evaluation']++;
  }
  if ($res['status'] == 'follow up') {
    $quantity['follow up']++;
  }
  if ($res['status'] == 'surgery') {
    $quantity['surgery']++;
  }
  if ($res['status'] == 'deleted') {
    $quantity['deleted']++;
  }
}

$results = json_encode(array(
  'results' => $results,
  'quantity' => $quantity
));
echo $results;
