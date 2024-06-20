<?php

require_once (dirname(__DIR__)."/functions.php");
$link = db_connect();

$today_timestamp = time();
$plus_30days = $today_timestamp + (30 * 24 * 60 * 60);
$format_date_today = date('Y-m-d', $today_timestamp);
$format_date_30days = date('Y-m-d', $plus_30days);
//echo $format_date_30days;

$query = "SELECT * FROM employee_data WHERE date_withdrawal_decree = '" . $format_date_30days . "'";
$result = mysqli_query($link, $query);

while ($row = mysqli_fetch_assoc($result)) {
  $full_name_employee = $row["full_name"];
  $id_company = $row["id_company"];
  $id_department = $row["id_department"];
  $id_division = $row["id_division"];
  $id_office = $row["id_office"];
  $post_employee = $row["post"];
  $date_withdrawal_decree = $row["date_withdrawal_decree"];
  $format_date_withdrawal_decree = date('d.m.Y', strtotime($date_withdrawal_decree));

  $query_guide = "SELECT * FROM employee_data WHERE id_company = '" . $id_company . "' AND id_department = '" . $id_department . "' AND id_division = '" . $id_division . "' AND id_office = '" . $id_office . "' AND guide = 1";
  $result_guide = mysqli_query($link, $query_guide);
  while ($row_guide = mysqli_fetch_assoc($result_guide)) {
    $guide_email = $row_guide["email"];
    if($id_department == 34){
      $guide_email = "Karasev@tfnopt.ru";
    }
    if($id_department == 39){
      $guide_email = "titov@tfnopt.ru";
    }

    //$mail = $guide_email . ",murzo@tfnopt.ru";
    $mail = 'kaliganov@tfnopt.ru,murzo@tfnopt.ru,' . $guide_email;
    $subject = 'Окончание декретного отпуска';
    $message = '<p>ФИО: ' . $full_name_employee . '</p>';
    $message .= '<p>Дата окончания декрета: ' . $format_date_withdrawal_decree . '</p>';
    $headers='';
    $headers.="Content-Type: text/html; charset=utf-8\r\n";
    $headers.="From: <staff@tfnopt.ru>\r\n";
    $headers.="X-Mailer: PHP/" . phpversion() . "\r\n";
    mail($mail, $subject, $message, $headers);
  }

}
	
?>