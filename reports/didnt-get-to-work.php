<?php

require_once ("functions.php");
$link = db_connect();

$date = date("Y-m-d", strtotime("01.01.2020"));

while ($date) {
//for ($i = 0; $i < 10; $i++) {
  $query  = "SELECT em.*, co.company_name, de.de_name, di.div_name, off.off_name 
FROM employee_data em 
LEFT JOIN company co ON em.id_company = co.id 
LEFT JOIN department de ON em.id_department = de.id 
LEFT JOIN division di ON em.id_division = di.id 
LEFT JOIN office off ON em.id_office = off.id 
WHERE em.date_employment = '$date' AND em.date_dismissal = '$date'";
  $result = mysqli_query($link, $query);

  while ($employee_array = mysqli_fetch_assoc($result)) {
    $enter  = $employee_array["full_name"] . " / ";
    $enter .= $employee_array["company_name"];
    $enter .= "→" . $employee_array["de_name"];
    if ($employee_array["id_division"] != "0") {
      $enter .= "→" . $employee_array["div_name"];
    }
    if ($employee_array["id_office"] != "0") {
      $enter .= "→" . $employee_array["off_name"];
    }
    $enter .= " / " . $employee_array["post"];
    $enter .= " / " . date("d-m-Y", strtotime($employee_array["date_dismissal"])) . "<br>";

    echo $enter;
  }

  $date = date("Y-m-d", strtotime($date."+1 day"));
  //echo $date;
  if ($date == "2023-02-02") {
    $date = false;
  }
}