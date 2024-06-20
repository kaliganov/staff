<?php

ob_start();
require_once 'connect.php';
require_once ("../functions.php");
$link = db_connect();
$auth = new AuthClass();
session_start();


$query_all_data = "SELECT * from employee_data where id = '" . $id_sotrudnika . "'";
$result_all_data = mysqli_query($link, $query_all_data);
$all_data_array_sotrudnika = array();
while ($row_all_data = mysqli_fetch_assoc($result_all_data)) {
    $all_data_array_sotrudnika = $row_all_data;
}
$id_company = (int) $all_data_array_sotrudnika["id_company"];
$id_department = (int) $all_data_array_sotrudnika["id_department"];
$id_division = (int) $all_data_array_sotrudnika["id_division"];
$id_office = (int) $all_data_array_sotrudnika["id_office"];

$dirFound = 0;
if ($id_office != 0 || $id_division != 0 && $id_office != 0) {
    $query_director = "SELECT email FROM employee_data WHERE id_company = '" . $id_company . "' AND id_department = '" . $id_department . "' AND id_division = '0' AND id_office = '0' AND guide = 1 AND dismissed = 'n'";
    $result_director = mysqli_query($link, $query_director);
    $row_director = mysqli_fetch_assoc($result_director);
    $users['director_email'] = $row_director['email'];
    $dirFound = 1;
}

$users['dirFound'] = $dirFound;
$users['id_company'] = $id_company;
$users['id_department'] = $id_department;

$request = "SELECT * FROM `employee_data` WHERE `login` != '' AND `id_company` = 1 AND `id_department` = 1 AND `dismissed` LIKE 'n'";
$result = mysqli_query($link, $request);
$usersArray = [];

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        if( ($row['id_office'] == 28 && $row['guide'] == 1) || $row['id_office'] == 29 ){
            $usersArray[] = $row;
        }
    }
}
$users['hr-list'] = $usersArray;

return $users;
?>