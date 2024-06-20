<?php

ob_start();
require_once 'connect.php';
require_once ("../functions.php");
$link = db_connect();
$auth = new AuthClass();
session_start();

if (isset($_POST['empl_date'])) {

  if ($_POST['date_delete'] == '00.00.0000') {
    $new_date_delete = '0000-00-00';
  } else {
    $new_date_delete = date("Y-m-d", strtotime($_POST['date_delete']));
  }
  $id_sotrudnika = $_POST['empl_id'];

  $query = "UPDATE `employee_data` SET `date_dismissal` = '" . $new_date_delete. "' WHERE `employee_data`.`id` = '" . $id_sotrudnika . "'";
  $result = mysqli_query($link, $query);

  echo '<h2 style="text-align:center">Дата изменена</h2>';

} else {

  $empl_dn = $_POST['empl_dn'];
  $id_sotrudnika = $_POST['empl_id'];
  $department = $_POST['empl_department'];
  $new_date_delete = date("Y-m-d", strtotime($_POST['date_delete']));

  $query = "UPDATE `employee_data` SET `date_dismissal` = '" . $new_date_delete. "', `dismissed` = 'y' WHERE `employee_data`.`id` = '" . $id_sotrudnika . "'";
  $result = mysqli_query($link, $query);

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
  $result = ldap_rename($connect, $dn, $newRdn, $newParent, true);
  if($result) {
    $out = '<h2 style="text-align:center">Сотрудник уволен!</h2>';

    $text = "Дата: " . date("Y-m-d H:i:s") . "\nУволен сотрудник: " . str_replace('CN=', '', $newRdn) . "\nИз отдела: " . dep($dn) . "\nЛогин: " . $samaccountname . "\nАдминистратор: " . $auth->getLogin() . "\nСотрудник успешно уволен\n\n\n";
    file_put_contents("../logs/employees_delete.txt", $text, FILE_APPEND);

  } else {
    $out = "<h2>При увольнении сотрудника возникла ошибка!</h2><p>Свяжитесь с отделом Web-разработки</p>";

    $text = "Дата: " . date("Y-m-d H:i:s") . "\nПопытка уволить сотрудника: " . str_replace('CN=', '', $newRdn) . "\nИз отдела: " . dep($dn) . "\nЛогин: " . $samaccountname . "\nАдминистратор: " . $auth->getLogin() . "\nОшибка:" . ldap_error($connect) . "\n\n\n";
    file_put_contents("../logs/employees_delete.txt", $text, FILE_APPEND);
  }

  $mail = 'ad_mail@tfnopt.ru,kaliganov@tfnopt.ru';
  if ($department == "Дивизион АВТО") {
    $mail .= ',baranov@tfnopt.ru';
  }
  $subject = 'Удаление сотрудника из AD '.str_replace('CN=', '', $newRdn);
  $message = '<p>Администратор: '.$auth->getLogin().'</p>';
  $message .= 'Удалённый сотрудник: '.str_replace('CN=', '', $newRdn).' ('.$samaccountname.')<br>';
  $message .= 'Должность: '.$post.'<br>';
  $message .= 'Из: '.dep($dn).'<br>';
  $headers='';
  $headers.="Content-Type: text/html; charset=utf-8\r\n";
  $headers.="From: <staff@tfnopt.ru>\r\n";
  $headers.="X-Mailer: PHP/".phpversion()."\r\n";
  mail($mail, $subject, $message, $headers);


  echo $out;

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