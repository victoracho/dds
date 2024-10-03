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

$doctor = $_GET['doctor'];
if ($doctor != 'All Doctors') {
  $doctor =  "doctor = '$doctor' AND";
}
if ($doctor === 'All Doctors') {
  $doctor = null;
}

$salon = $_GET['salon'];
if ($salon != 'All Salons') {
  $salon =  "salon= '$salon' AND";
}
if ($salon === 'All Salons') {
  $salon = null;
}

$range = explode(",", $range);
$ini = parse_ini_file('app.ini');
$servername = $ini['db_name'];
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
$sql = "SELECT * FROM appointments where $substatus $doctor $salon status in ('$status') AND start between '$range[0]' AND '$range[1]' ";
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
      $status = '#f69ac1';
    }
    if ($res['status'] == 'surgery') {
      $status = '#86b100';
    }
    if ($res['status'] == 'missing-appointment') {
      $status = '#7b03fc';
    }

    // substatus 
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
  'hyperbaric chamber' => 0,
  'labs' => 0,
  'massage' => 0,
  'post-op' => 0,
  'pre-op appt' => 0,
  'pre-op surgery' => 0,
  'surgery' => 0,
  'missing-appointment' => 0,
);
foreach ($results as $res) {
  if ($res['status'] == 'evaluation') {
    $quantity['evaluation']++;
  }
  if ($res['status'] == 'follow up') {
    $quantity['follow up']++;
  }
  if ($res['status'] == 'hyperbaric chamber') {
    $quantity['hyperbaric chamber']++;
  }
  if ($res['status'] == 'labs') {
    $quantity['labs']++;
  }
  if ($res['status'] == 'massage') {
    $quantity['massage']++;
  }
  if ($res['status'] == 'post-op') {
    $quantity['post-op']++;
  }
  if ($res['status'] == 'pre-op appt') {
    $quantity['post-op']++;
  }
  if ($res['status'] == 'pre-op surgery') {
    $quantity['pre-op surgery']++;
  }
  if ($res['status'] == 'surgery') {
    $quantity['surgery']++;
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
