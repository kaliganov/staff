<?php

require_once (dirname(__DIR__)."/functions.php");
require_once (dirname(__DIR__)."/ajax/connect.php");
$link = db_connect();

$today = date("Y-m-d", time());

// $query = "SELECT e.way_in_ad, e.id, d.de_name FROM employee_data e LEFT JOIN department d ON e.id_department = d.id WHERE e.date_dismissal = '$today' AND e.dismissed = 'n'";
$query = "SELECT e.way_in_ad, e.id, d.de_name FROM employee_data e LEFT JOIN department d ON e.id_department = d.id WHERE e.date_dismissal = '$today'";
$result = mysqli_query($link, $query);

while ($row = mysqli_fetch_assoc($result)) {

  sleep(5);

  $empl_dn = $row["way_in_ad"];
  $department = $row["de_name"];

  $dn = $empl_dn;
  $newRdn = current(explode(',', $empl_dn));
  $newParent = 'OU=Disabled,OU=MEGA-F,DC=mega-f,DC=ru';

  // Получаем имя учётной записи сотрудника
  $filter = '(&(objectClass=user)(objectCategory=PERSON))';
  $sr = ldap_search($connect, $empl_dn, $filter);
  $info = ldap_get_entries($connect, $sr);
  $samaccountname = $info[0]['samaccountname'][0];
  $post = $info[0]['title'][0];

  // Блокируем пользователя
  $update_arr['userAccountControl'] = 0x0202; // 514
  $result = ldap_modify($connect, $empl_dn, $update_arr);

  // Переносим в папку Disabled
  $ldap_result = ldap_rename($connect, $dn, $newRdn, $newParent, true);


  if ($ldap_result) {

    $text = "Дата: " . date("Y-m-d H:i:s") . "\nУволен сотрудник: " . str_replace('CN=', '', $newRdn) . "\nИз отдела: " . dep($dn) . "\nЛогин: " . $samaccountname . "\nАдминистратор: Система\nСотрудник успешно уволен\n\n\n";
    file_put_contents("../logs/employees_delete.txt", $text, FILE_APPEND);

    $query_up = "UPDATE employee_data SET dismissed = 'y' WHERE id = '$row[id]'";
    $result_up = mysqli_query($link, $query_up);

    $mail = 'ad_mail@tfnopt.ru,kaliganov@tfnopt.ru';
    if ($department == "Дивизион АВТО") $mail .= ',baranov@tfnopt.ru';
    $subject = 'Удаление сотрудника из AD '.str_replace('CN=', '', $newRdn);
    $message = '<p>Администратор: Система</p>';
    $message .= 'Удалённый сотрудник: '.str_replace('CN=', '', $newRdn).' ('.$samaccountname.')<br>';
    $message .= 'Должность: '.$post.'<br>';
    $message .= 'Из: '.dep($dn).'<br>';
    $headers = '';
    $headers .= "Content-Type: text/html; charset=utf-8\r\n";
    $headers .= "From: <staff@tfnopt.ru>\r\n";
    $headers .= "X-Mailer: PHP/".phpversion()."\r\n";
    mail($mail, $subject, $message, $headers);

  } else {

    $text = "Дата: " . date("Y-m-d H:i:s") . "\nПопытка уволить сотрудника: " . str_replace('CN=', '', $newRdn) . "\nИз отдела: " . dep($dn) . "\nЛогин: " . $samaccountname . "\nАдминистратор: Система\nОшибка:" . ldap_error($connect) . "\n\n\n";
    file_put_contents("../logs/employees_delete.txt", $text, FILE_APPEND);

    $mail = 'kaliganov@tfnopt.ru';
    $subject = 'Не удавшаяся попытка увольнения сотрудника из AD '.str_replace('CN=', '', $newRdn) . ' системой';
    $message = '<p>Администратор: Система</p>';
    $message .= 'Удалённый сотрудник: '.str_replace('CN=', '', $newRdn).' ('.$samaccountname.')<br>';
    $message .= 'Должность: '.$post.'<br>';
    $message .= 'Из: '.dep($dn).'<br>';
    $headers = '';
    $headers .= "Content-Type: text/html; charset=utf-8\r\n";
    $headers .= "From: <staff@tfnopt.ru>\r\n";
    $headers .= "X-Mailer: PHP/".phpversion()."\r\n";
    mail($mail, $subject, $message, $headers);

  }

}

function dep($cn) {
  $arr_cn = array_reverse(explode(',', $cn));
  foreach($arr_cn as $row) {
    if(strpos($row, 'OU=') !== false) {
      if($row != 'OU=Сотрудники' and $row != 'OU=MEGA-F') {
        $arr[] = str_replace('OU=', '', $row);
      }
    }
  }
  return implode(" -> ", $arr);
}
?>