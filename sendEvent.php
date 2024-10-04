<?php
header("Access-Control-Allow-Origin: *");
error_reporting(E_ALL);
header("Access-Control-Allow-Headers: Content-Type");
ini_set('display_errors', 'On');
require_once(__DIR__ . '/crest.php');

try {
  // convierte el valor del campo de edad a su valor en edad
  $ini = parse_ini_file('app.ini');
  $servername = $ini['servername'];
  $username = $ini['db_user'];
  $password = $ini['db_password'];
  $dbname = $ini['db_name'];

  $_POST = json_decode(file_get_contents("php://input"), true);
  $user = $_POST['user'];
  $deal = $_POST['deal_id'];
  $event = $_POST['event'];
  $now = date('Y-m-d\TH:i:sP');
  // se obtiene el deal para capturar los campos
  $currentDeal = crest::call(
    'crm.deal.get',
    [
      'id' => $deal
    ],
  );

  $allPhones = null;
  $leadName = null;
  $edad = null;
  $state = null;
  if ($currentDeal['result']) {
    $currentDeal = $currentDeal['result'];
    if (isset($currentDeal['TITLE'])) {
      $leadName = $currentDeal['TITLE'];
    }
    $contactId = $currentDeal['CONTACT_ID'];
    if ($contactId) {
      $contactData = crest::call(
        'crm.contact.get',
        [
          'id' => $contactId
        ],
      );
      if ($contactData && isset($contactData['result'])) {
        $contact = $contactData['result'];
        $phones = $contact['PHONE'];
        $allPhones = '';
        foreach ($phones as $phone) {
          $allPhones .= ' ' .  $phone['VALUE'];
        }
      }
    }
  }

  $conn = new mysqli($servername, $username, $password, $dbname);
  // Check connection
  if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
  }

  $stmt = $conn->prepare($sql = "INSERT into appointments SET name= ?, status= ?, user= ?, substatus= ?, start = ?, end =?,  date_created = ?, comment = ?, deal_id = ?, phone = ? , lead_name = ?,  salon = ?, doctor = ? ");
  $stmt->bind_param('sssssssssssss', $event['title'], $event['BackgroundColor'], $user, $event['substatus'], $event['start'], $event['end'], $now, $event['text'], $deal, $allPhones, $leadName, $salon, $doctor);
  $result = $stmt->execute();
  $conn->close();
  $response = array(
    'message' => 'Added Succesfully'
  );
  $desde = $event['start'];
  $desde = new DateTime($desde);
  $desde = $desde->format('Y-m-d H:i');

  $hasta = $event['end'];
  $hasta = new DateTime($hasta);
  $hasta = $hasta->format('Y-m-d H:i');

  $comment = CRest::call(
    'crm.timeline.comment.add',
    [
      'fields' =>  [
        'ENTITY_ID' => $deal,
        'ENTITY_TYPE' => "deal",
        'COMMENT' => "Se ha creado un evento del tipo: " . $event['BackgroundColor'] . ' Desde : ' . $desde . ' Hasta : ' . $hasta . ' creado por: ' . $user . ' Para Daso calendar con el doctor: ' . $doctor . ' en el salon: ' . $salon
      ],
    ],
  );
  echo json_encode($response);
} catch (Exception $e) {
  $response = array(
    'message' => 'An error has ocurred'
  );
  echo json_encode($response);
}

die();
