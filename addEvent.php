<?php
header("Access-Control-Allow-Origin: *");
require_once(__DIR__ . '/crest.php');

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

$stmt = $conn->prepare($sql = "INSERT into appointments SET deal_id = ? , name= ?, status= ?, user= ?, substatus= ?, start = ?, end = ? ");
$stmt->bind_param('ssssssss', $deal_id, $name, $status, $user, $substatus, $start, $end);
$result = $stmt->execute();
$conn->close();
