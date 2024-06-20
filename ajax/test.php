<?php

ob_start();
require_once 'connect.php';
require_once ("../functions.php");
$link = db_connect();
/*$auth = new AuthClass();
session_start();

$empl_dn = "CN=Калиганов Сергей,OU=IT-Отдел,OU=Департамент IT,OU=Сотрудники,OU=MEGA-F,DC=mega-f,DC=ru";

$filter = '(&(objectClass=user)(objectCategory=PERSON))';
$sr = ldap_search($connect, $empl_dn, $filter);
$info = ldap_get_entries($connect, $sr);


echo '<pre>';
print_r($info[0]);
echo '</pre>';*/

/*$query = "SELECT full_name from employee_data where login = ''";
$result = mysqli_query($link, $query);

while ($row = mysqli_fetch_assoc($result)) {
   echo $row["full_name"] . "<br>";
}*/

// Запрос на дубли в ФИО
/*$query = "SELECT surname, name, COUNT(*) AS count FROM employee_data GROUP BY surname,name HAVING count > 1";
$result = mysqli_query($link, $query);

while ($row = mysqli_fetch_assoc($result)) {
  echo "<pre>";
  var_dump($row);
  echo "</pre>";
}*/

$today_timestamp = time();
$plus_14days = $today_timestamp + (14 * 24 * 60 * 60);
$format_date_today = date('Y-m-d', $today_timestamp);
$format_date_14days = date('Y-m-d', $plus_14days);


$query = "SELECT * FROM employee_data WHERE date_probationary_period = '" . $format_date_14days . "'";
$result = mysqli_query($link, $query);
//echo "<br>";

while ($row = mysqli_fetch_assoc($result)) {
  echo "Сотрудник<br>";
  echo "<pre>";
  var_dump($row["full_name"]);
  echo "</pre>";

  $full_name_employee = $row["full_name"];
  $id_company = $row["id_company"];
  $id_department = $row["id_department"];
  $id_division = $row["id_division"];
  $id_office = $row["id_office"];
  $post_employee = $row["post"];
  $date_probationary_period = $row["date_probationary_period"];
  $format_date_probationary_period = date('d-m-Y', strtotime($date_probationary_period));

  $query_guide = "SELECT * FROM employee_data WHERE id_company = '" . $id_company . "' AND id_department = '" . $id_department . "' AND id_division = '" . $id_division . "' AND id_office = '" . $id_office . "' AND guide = 1";
  $result_guide = mysqli_query($link, $query_guide);
  while ($row_guide = mysqli_fetch_assoc($result_guide)) {
    echo "Руководитель<br>";
    echo "<pre>";
    var_dump($row_guide["full_name"]);
    echo "</pre>";

    $guide_email = $row_guide["email"];

    $mail = 'kaliganov@tfnopt.ru';// . $dep_direct_email;
    $subject = 'Через 14 дней закончится испытательный срок';
    $message = '<p>У следующего сотрудника:</p>';
    $message .= '<p>ФИО - ' . $full_name_employee . '<br>Должность - ' . $post_employee . '</p>';
    $message .= '<p>Через 14 дней (' . $format_date_probationary_period . ') закончится испытательный срок</p>';
    $message .= '<br><br>Кому отправится это письмо: ' . $guide_email . '<br>';
    $headers='';
    $headers.="Content-Type: text/html; charset=utf-8\r\n";
    $headers.="From: <staff@tfnopt.ru>\r\n";
    $headers.="X-Mailer: PHP/" . phpversion() . "\r\n";
    mail($mail, $subject, $message, $headers);
  }
  //break;
}

?>