<?php
require_once(__DIR__ . '/crest.php');
header("Access-Control-Allow-Origin: *");
//109657
// calendario de miami

/* $calendar = CRest::call( */
/*   'calendar.event.get', */
/*   [ */
/*     'type' => 'group', */
/*     'ownerId' => '5', */
/*   ], */
/* ); */

/* $results = $calendar['result']; */

/* $results = array_map(function ($res) { */
/*   $date = strtotime($res['DATE_FROM']); */
/*   $arr = array( */
/*     'id' => $res['ID'], */
/*     'allDay' => true, */
/*     'title' => $res['NAME'], */
/*     'start' => date('Y-m-d', $date), */
/*     'backgroundColor' => $res['COLOR'], */
/*   ); */
/*   return $arr; */
/* }, $results); */

/* $results = json_encode($results); */

$range = $_GET['range'];
$status = strtolower($_GET['status']);

$substatus = $_GET['substatus'];

if ($substatus == "All Substatus") {
  $substatus = null;
}

if ($substatus != "All Substatus") {
  $substatus =  "substatus = '$substatus' AND";
}

$range = explode(",", $range);
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
$sql = "SELECT * FROM appointments where $substatus status in ('$status') AND start between '$range[0]' AND '$range[1]' ";
$result = mysqli_query($conn, $sql);
$results = [];

if (mysqli_num_rows($result) > 0) {
  // output data of each row
  while ($res = mysqli_fetch_assoc($result)) {
    if ($res['status'] == 'evaluation') {
      $status = '#00afc7';
    }
    if ($res['status'] == 'follow up') {
      $status = '#10e5fc';
    }
    if ($res['status'] == 'hyperbaric chamber') {
      $status = '#bd7ac9';
    }
    if ($res['status'] == 'labs') {
      $status = '#e89b06';
    }
    if ($res['status'] == 'massage') {
      $status = '#e97090';
    }
    if ($res['status'] == 'post-op') {
      $status = '#00ff00';
    }
    if ($res['status'] == 'pre-op appt') {
      $status = '#fff300';
    }
    if ($res['status'] == 'pre-op surgery') {
      $status = '#b57051';
    }
    if ($res['status'] == 'surgery') {
      $status = '#86b100';
    }
    if ($res['status'] == 'missing-appointment') {
      $status = '#7b03fc';
    }

    $results[] =
      [
        'id' => $res['id'],
        'deal_id' => $res['deal_id'],
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
  'hyperbaric chamber' => 0,
  'labs' => 0,
  'massage' => 0,
  'post-op' => 0,
  'pre-op appt' => 0,
  'pre-op surgery' => 0,
  'surgery' => 0,
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
}

$results = json_encode(array(
  'results' => $results,
  'quantity' => $quantity
));
echo $results;
