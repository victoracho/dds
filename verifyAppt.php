<?php
header("Access-Control-Allow-Origin: *");
require_once(__DIR__ . '/crest.php');
//date_default_timezone_set('America/New York');
ini_set('display_errors', 'On');

try {
  $ini = parse_ini_file('app.ini');
  $servername = $ini['db_name'];
  $username = $ini['db_user'];
  $password = $ini['db_password'];
  $dbname = $ini['db_name'];

  // Create connection
  $conn = new mysqli($servername, $username, $password, $dbname);
  // Check connection
  if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
  }
  $sql = "SELECT * FROM appointments where deal_id = $deal_id ";
  $result = mysqli_query($conn, $sql);

  if (mysqli_num_rows($result) > 0) {
    $res = mysqli_fetch_assoc($result);
    mysqli_close($conn);
    $response = array(
      'message' => 'found',
      'result' => $res
    );
    echo json_encode($response);
  } else {
    mysqli_close($conn);
    $response = array(
      'message' => 'not found'
    );
    echo json_encode($response);
  }
} catch (Exception $e) {
  $response = array(
    'message' => 'not found'
  );
  echo json_encode($response);
}
