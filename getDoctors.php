<?php
require_once(__DIR__ . '/crest.php');
header("Access-Control-Allow-Origin: *");
//109657
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

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
$sql = "SELECT * FROM  doctors";
$result = mysqli_query($conn, $sql);
$results = [];
$results[] = [
  'id' => 'All Doctors',
  'name' => 'All Doctors'
];

if (mysqli_num_rows($result) > 0) {
  while ($res = mysqli_fetch_assoc($result)) {
    $results[] =
      [
        'id' => $res['id'],
        'name' => $res['name'],
      ];
  }
}

mysqli_close($conn);
$results = json_encode(array(
  'results' => $results,
  'message' => 'success'
));
echo $results;
