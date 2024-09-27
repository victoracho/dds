<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once(__DIR__ . '/crest.php');
// Calendario Miami  
$calendar = CRest::call(
  'calendar.event.get',
  [
    'type' => 'group',
    'ownerId' => '6',
    'from' => '2023-05-10',
    'to' => '2025-08-20',
  ],
);
$results = $calendar['result'];
$results = array_map(function ($res) {
  $find = CRest::call(
    'calendar.event.getbyid',
    [
      'id' => $res['ID'],
    ],
  );
  $find = $find['result'];
  $deal_id = null;
  $comentary = null;
  if (isset($find['~DESCRIPTION'])) {
    $description = $find['~DESCRIPTION'];
    if ($description != null) {
      $dom = new DOMDocument();
      @$dom->loadHTML($description);
      $links = $dom->getElementsByTagName('a');
      foreach ($links as $link) {
        $deal = $link->getAttribute('href');
        $deal = explode('/', $deal);
        if ($deal[2] == 'deal') {
          $deal_id = $deal[4];
        }
      }
    }
    $description = explode("<br><br>", $description);
    foreach ($description as $desc) {
      if (str_contains($desc, 'Comentary:')) {
        $comentary = $desc;
      }
    }
  }
  $find['deal_id'] = $deal_id;
  $find['comentary'] = $comentary;
  return $find;
}, $results);

$servername = "16.171.204.95";
$username = "bitrix";
$password = "8726231";
$dbname = "newjersey";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

foreach ($results as $res) {
  if ($res['SECTION_ID'] == 92) {
    $color = '#f7699d';
    $status = 'evaluation';
  }
  if ($res['SECTION_ID'] == 93) {
    $color = '#bbecf1';
    $status = 'free eval';
  }
  if ($res['SECTION_ID'] == 94) {
    $color = '#fff55a';
    $status = 're-evaluation';
  }
  if ($res['SECTION_ID'] == 91) {
    $color = '#e89b06';
    $status = 'emergency';
  }
  if ($res['SECTION_ID'] == 95) {
    $color = '#0092cc';
    $status = 'vip';
  }

  $name = $res['NAME'];
  $substatus = 'not specified';

  $start = $res['DATE_FROM'];
  if ($res['TZ_FROM'] == 'Europe/Dublin') {
    $start = DateTime::createFromFormat('m/d/Y h:i:s a', $start, new DateTimeZone('Europe/Dublin'));
    $start->setTimezone(new DateTimeZone('America/New_York'));
    $start = $start->format('Y-m-d\TH:i:s');
  }
  if ($res['TZ_FROM'] != 'Europe/Dublin') {
    $start = DateTime::createFromFormat('m/d/Y h:i:s A', $start);
    $start = $start->format('Y-m-d\TH:i:s');
  }

  $end = $res['DATE_TO'];
  if ($res['TZ_TO'] == 'Europe/Dublin') {
    $end = DateTime::createFromFormat('m/d/Y h:i:s a', $end, new DateTimeZone('Europe/Dublin'));
    $end->setTimezone(new DateTimeZone('America/New_York'));
    $end = $end->format('Y-m-d\TH:i:s');
  }
  if ($res['TZ_TO'] != 'Europe/Dublin') {
    $end = DateTime::createFromFormat('m/d/Y h:i:s A', $end);
    $end = $end->format('Y-m-d\TH:i:s');
  }
  $user = 'No-name';
  $id_event = $res['ID'];
  $deal_id = $res['deal_id'];
  $comentary = $res['comentary'];

  if ($res['MEETING']) {
    if (isset($res['MEETING']['HOST_NAME'])) {
      $user = $res['MEETING']['HOST_NAME'] ? $res['MEETING']['HOST_NAME'] : 'No-name';
    }
  }

  $stmt = $conn->prepare($sql = "INSERT into appointments SET deal_id = ? , name= ?, status= ?, user= ?, substatus= ?, start = ?, end = ?, comment = ?, id_event = ?");
  $stmt->bind_param('sssssssss', $deal_id, $name, $status, $user, $substatus, $start, $end, $comentary, $id_event);
  $result = $stmt->execute();
}
$conn->close();
