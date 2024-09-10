<?php
header("Access-Control-Allow-Origin: *");
require_once(__DIR__ . '/crest.php');
$servername = "localhost";
$username = "root";
$password = "Laravel2024!";
$dbname = "calendar";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

$stmt = $conn->prepare($sql = "INSERT into appointments SET deal_id = ? , name= ?, status= ?, user= ?, substatus= ?, start = ?, end = ? ");
$stmt->bind_param('sssssss', $deal_id, $name, $status, $user, $substatus, $start, $end);
$result = $stmt->execute();
$conn->close();
