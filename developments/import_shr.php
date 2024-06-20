<?php

exit;

require_once 'connect.php';
require_once ("../functions.php");
$link = db_connect();
	
$i = 0;
// Список должностей для изменения
if (($handle = fopen("post_new.csv", "r")) !== FALSE) {
  while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {

    $i++;
    if($i == 1) {continue;}

    echo "<pre>";
    var_dump($data);
    echo "</pre>";

    echo $qyery = "INSERT INTO position_parallel 
                (id, pos_name, id_office, id_department, id_division, id_company, quantity, cfo)
                VALUES
                (NULL, '" . $data[0] . "', '" . $data[1] . "', '" . $data[2] . "', '" . $data[3] . "', '" . $data[4] . "', '" . $data[5] . "', '" . $data[6] . "')";
    mysqli_query($link, $qyery);
    echo '<br><br>';

  }
  fclose($handle);
}






// Запрос на обновление данных
/*if (($handle = fopen("import_shr.csv", "r")) !== FALSE) {
  while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {

    $i++;
    if($i == 1) {continue;}

    $companyID = trim($data[0]);
    $departmentID = trim($data[1]);
    $divisionID = trim($data[2]); if($divisionID == '') {$divisionID = 0;}
    $officeID = trim($data[3]); if($officeID == '') {$officeID = 0;}
    $pos_name = trim($data[4]);
    $mvz = trim($data[8]);

    echo $updateQuery = "UPDATE `position_parallel` SET `MVZ` = '$mvz' WHERE `pos_name` = '$pos_name' AND `id_company` = '$companyID' AND `id_department` = '$departmentID' AND `id_division` = '$divisionID' AND `id_office` = '$officeID'";
    mysqli_query($link, $updateQuery);
    echo '<br><br>';

    unset($companyID);
    unset($departmentID);
    unset($divisionID);
    unset($officeID);
    unset($pos_name);
    unset($mvz);

  }
  fclose($handle);
}*/

?>