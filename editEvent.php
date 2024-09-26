<?php
header("Access-Control-Allow-Origin: *");
error_reporting(E_ALL);
header("Access-Control-Allow-Headers: Content-Type");
ini_set('display_errors', 'On');

try {
  $servername = "16.171.204.95";
  $username = "bitrix";
  $password = "8726231";
  $dbname = "newJersey";
  $_POST = json_decode(file_get_contents("php://input"), true);
  $user = $_POST['user'];
  $eventId = $_POST['event_id'];

  $event = $_POST['event'];
  $now = date('Y-m-d\TH:i:sP');

  // Create connection
  $conn = new mysqli($servername, $username, $password, $dbname);
  // Check connection
  if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT * FROM appointments where id = $eventId order by id limit 1";
  $result = mysqli_query($conn, $sql);
  $previousStatus = null;
  if (mysqli_num_rows($result) > 0) {
    $res = mysqli_fetch_assoc($result);
    var_dump($res);
    die();
    $previousStatus = $res['previous_status'];
  }
  $stmt = $conn->prepare($sql = "UPDATE appointments SET name= ?, status= ?, user_modified= ?, substatus= ?, start = ?, end = ?,  date_modified = ?, comment = ?, previous_status = ?   WHERE  id= ?");
  $stmt->bind_param('ssssssssss', $event['title'], $event['BackgroundColor'], $user, $event['substatus'], $event['start'], $event['end'], $now, $event['text'], $previousStatus, $eventId);
  $result = $stmt->execute();
  $conn->close();

  $response = array(
    'message' => 'Edited Succesfully'
  );
  echo json_encode($response);
} catch (Exception $e) {
  $response = array(
    'message' => 'An error ocurred, try again'
  );
  echo json_encode($response);
}

die();
