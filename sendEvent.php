<?php
header("Access-Control-Allow-Origin: *");
error_reporting(E_ALL);
header("Access-Control-Allow-Headers: Content-Type");
ini_set('display_errors', 'On');
require_once(__DIR__ . '/crest.php');

try {
  function giveState($state)
  {
    if ($state == 339) {
      $state = 'Alabama';
    }
    if ($state == 340) {
      $state = 'Alaska';
    }
    if ($state == 341) {
      $state = 'Arizona';
    }
    if ($state == 342) {
      $state = 'Arkansas';
    }
    if ($state == 343) {
      $state = 'California';
    }
    if ($state == 344) {
      $state = 'Colorado';
    }
    if ($state == 345) {
      $state = 'Connecticut';
    }
    if ($state == 346) {
      $state = 'Delaware';
    }
    if ($state == 347) {
      $state = 'Florida';
    }
    if ($state == 348) {
      $state = 'Georgia';
    }
    if ($state == 349) {
      $state = 'Hawaii';
    }
    if ($state == 350) {
      $state = 'Idaho';
    }
    if ($state == 351) {
      $state = 'Illinois';
    }
    if ($state == 352) {
      $state = 'Indiana';
    }
    if ($state == 353) {
      $state = 'Iowa';
    }
    if ($state == 354) {
      $state = 'Kansas';
    }
    if ($state == 355) {
      $state = 'Kentucky';
    }
    if ($state == 356) {
      $state = 'Louisiana';
    }
    if ($state == 357) {
      $state = 'Maine';
    }
    if ($state == 358) {
      $state = 'Maryland';
    }
    if ($state == 359) {
      $state = 'Massachusetts';
    }
    if ($state == 360) {
      $state = 'Michigan';
    }
    if ($state == 361) {
      $state = 'Minnesota';
    }
    if ($state == 362) {
      $state = 'Mississippi';
    }
    if ($state == 363) {
      $state = 'Missouri';
    }
    if ($state == 364) {
      $state = 'Montana';
    }
    if ($state == 365) {
      $state = 'Nebraska';
    }
    if ($state == 366) {
      $state = 'Nevada';
    }
    if ($state == 367) {
      $state = 'New Hampshire';
    }
    if ($state == 368) {
      $state = 'New Jersey';
    }
    if ($state == 369) {
      $state = 'New Mexico';
    }
    if ($state == 370) {
      $state = 'New York';
    }
    if ($state == 371) {
      $state = 'North Carolina';
    }
    if ($state == 372) {
      $state = 'North Dakota';
    }
    if ($state == 373) {
      $state = 'Ohio';
    }
    if ($state == 374) {
      $state = 'Oklahoma';
    }
    if ($state == 375) {
      $state = 'Oregon';
    }
    if ($state == 376) {
      $state = 'Pennsylvania';
    }
    if ($state == 377) {
      $state = 'Rhode Island';
    }
    if ($state == 378) {
      $state = 'South Carolina';
    }
    if ($state == 379) {
      $state = 'South Dakota';
    }
    if ($state == 380) {
      $state = 'Tennessee';
    }
    if ($state == 381) {
      $state = 'Texas';
    }
    if ($state == 382) {
      $state = 'Utah';
    }
    if ($state == 383) {
      $state = 'Vermont';
    }
    if ($state == 384) {
      $state = 'Virginia';
    }
    if ($state == 385) {
      $state = 'Washington';
    }
    if ($state == 386) {
      $state = 'West Virginia';
    }
    if ($state == 387) {
      $state = 'Winsconsin';
    }
    if ($state == 388) {
      $state = 'Wyoming';
    }
    return $state;
  }

  function giveEdad($edad)
  {
    if ($edad != null) {
      $edad = ($edad + 1) - 779;
    }
    return $edad;
  }

  $servername = "16.171.204.95";
  $username = "bitrix";
  $password = "8726231";
  $dbname = "miami";

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
    // se checa la edad y el estado
    $info = CRest::call(
      'crm.deal.list',
      [
        'filter' =>  [
          'ID' => $deal,
        ],
        'select' => [
          'UF_CRM_6596BEA5BA903',
          'UF_CRM_1722807403'
        ]

      ],
    );

    if (!empty($info['result'])) {
      $array = $info['result'][0];
      if ($array['UF_CRM_6596BEA5BA903']) {
        $state = $array["UF_CRM_6596BEA5BA903"];
        $state = giveState($state);
      }
      if ($array['UF_CRM_1722807403']) {
        $edad = $array["UF_CRM_1722807403"];
        $edad = giveEdad($edad);
      }
    }
    // fin checar edad y estado

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

  $stmt = $conn->prepare($sql = "INSERT into appointments SET name= ?, status= ?, user= ?, substatus= ?, start = ?, end =?,  date_created = ?, comment = ?, deal_id = ?, phone = ? , lead_name = ?, lodging = ?, transportation = ?, more_invoices = ?, amount= ?, invoice_number = ?, edad = ?, estado = ? ");
  $stmt->bind_param('ssssssssssssssssss', $event['title'], $event['BackgroundColor'], $user, $event['substatus'], $event['start'], $event['end'], $now, $event['text'], $deal, $allPhones, $leadName, $event['lodging'], $event['transportation'], $event['more_invoices'], $event['amount'], $event['invoice_number'], $edad, $state);

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
        'COMMENT' => "An appointment has been created for the type: " . $event['BackgroundColor'] . ' From: ' . $desde . ' Until: ' . $hasta . ' created by: ' . $user . ' for Miami Calendar'
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
