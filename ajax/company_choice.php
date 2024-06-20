<?php

ob_start();
require_once 'connect.php';

session_start();

require_once ("../functions.php");
$link = db_connect();

$out = '';
$import_string = $_POST['company_choice'];

// Собираем массив из ИД-компании и ИД-отдела
$ConvertedImportStringToArray = explode(' ', $import_string);

$company_id = $ConvertedImportStringToArray[0];
$office_id = $ConvertedImportStringToArray[1];

$query = "SELECT * from position_parallel where id_office = " . $office_id;
$result = mysqli_query($link, $query);

$out .= '<option value="" disabled="" selected="">Должность</option>';

while($row = mysqli_fetch_assoc($result)) {
  $out .= '<option value="'.$row['id'].'">'.$row['pos_name'].'</option>';
}

echo $out;