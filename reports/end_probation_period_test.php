<?php

require_once (dirname(__DIR__)."/functions.php");
require_once (dirname(__DIR__)."/ajax/connect.php");
$link = db_connect();

$today_timestamp = time();
$plus_14days = $today_timestamp + (14 * 24 * 60 * 60);
$format_date_today = date('Y-m-d', $today_timestamp);
$format_date_14days = date('Y-m-d', $plus_14days);

$query = "SELECT * FROM employee_data WHERE date_probationary_period = '" . $format_date_14days . "' AND dismissed = 'n'";
$result = mysqli_query($link, $query);

while ($row = mysqli_fetch_assoc($result)) {
  $full_name_employee = $row["full_name"];
  $id_company = $row["id_company"];
  $id_department = $row["id_department"];
  $id_division = $row["id_division"];
  $id_office = $row["id_office"];
  $post_employee = $row["post"];
  $date_probationary_period = $row["date_probationary_period"];
  $format_date_probationary_period = date('d.m.Y', strtotime($date_probationary_period));

  $query_guide = "SELECT email FROM employee_data WHERE id_company = '" . $id_company . "' AND id_department = '" . $id_department . "' AND id_division = '" . $id_division . "' AND id_office = '" . $id_office . "' AND guide = 1";
  $result_guide = mysqli_query($link, $query_guide);
  $num_rows = mysqli_num_rows($result_guide);
  $row_guide = mysqli_fetch_assoc($result_guide);

  if ($id_office != 0 || $id_division != 0 && $id_office != 0) {
    $query_director = "SELECT email FROM employee_data WHERE id_company = '" . $id_company . "' AND id_department = '" . $id_department . "' AND id_division = '0' AND id_office = '0' AND guide = 1";
    $result_director = mysqli_query($link, $query_director);
    $row_director = mysqli_fetch_assoc($result_director);
    $director_email = $row_director['email'];
  }

  if ($num_rows != 0) {
    $guide_email = $row_guide["email"];
    if (isset($director_email)) {
      $guide_email .= "," . $director_email;
    }

    $mail = $guide_email . ",salakhova@tfnopt.ru,mityaev@tfnopt.ru";
    $subject = 'Через 14 дней закончится испытательный срок';
    $message = '<p>Через 14 дней (' . $format_date_probationary_period . ') у сотрудника закончится испытательный срок.</p>';
    $message .= '<p>ФИО: ' . $full_name_employee . '<br>Должность: ' . $post_employee . '</p>';
    $headers='';
    $headers.="Content-Type: text/html; charset=utf-8\r\n";
    $headers.="From: <staff@tfnopt.ru>\r\n";
    $headers.="X-Mailer: PHP/" . phpversion() . "\r\n";
    mail($mail, $subject, $message, $headers);

  } else {

    $original_dn = $row["way_in_ad"];
    $array_dn = explode(",", $original_dn);
    $specific_dn = "";
    foreach ($array_dn as $key => $value) {
      if ($key == 0) continue;
      if ($key == 1) {
        $specific_dn = $value;
        continue;
      }
      $specific_dn .= ",$value";
    }
    $filter = '(|(cn=*)(givenname=*)(ou=*))';
    $justthese = array("ou", "cn", "givenname", "mail", "extensionAttribute3");
    $sr = ldap_list($connect, $specific_dn, $filter, $justthese);
    $info = ldap_get_entries($connect, $sr);

    $guide_email = "";
    foreach($info as $key => $value) {
      if ($value["extensionattribute3"][0] == 1) {
        $guide_email = $value["mail"][0];
      }
    }

    if (isset($director_email)) {
      $guide_email .= "," . $director_email;
    }

    $mail = $guide_email . ",salakhova@tfnopt.ru,mityaev@tfnopt.ru";
    $subject = 'Через 14 дней закончится испытательный срок';
    $message = '<p>Через 14 дней (' . $format_date_probationary_period . ') у сотрудника закончится испытательный срок.</p>';
    $message .= '<p>ФИО: ' . $full_name_employee . '<br>Должность: ' . $post_employee . '</p>';
    $headers='';
    $headers.="Content-Type: text/html; charset=utf-8\r\n";
    $headers.="From: <staff@tfnopt.ru>\r\n";
    $headers.="X-Mailer: PHP/" . phpversion() . "\r\n";
    mail($mail, $subject, $message, $headers);
  }
}
?>