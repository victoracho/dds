<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once(__DIR__ . '/crest.php');
// Calendario Miami  
$calendar = CRest::call(
  'calendar.event.get',
  [
    'type' => 'group',
    'ownerId' => '4',
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

