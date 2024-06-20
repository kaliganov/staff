<?php

require_once (dirname(__DIR__)."/functions.php");
require_once (dirname(__DIR__)."/ajax/connect.php");
$link = db_connect();

$today_timestamp = time();
$plus_14days = $today_timestamp + (14 * 24 * 60 * 60);
$plus_45days = $today_timestamp + (45 * 24 * 60 * 60);
$format_date_today = date('Y-m-d', $today_timestamp);
$format_date_14days = date('Y-m-d', $plus_14days);
$format_date_45days = date('Y-m-d', $plus_45days);

$query = "SELECT * FROM employee_data WHERE date_probationary_period = '" . $format_date_14days . "' AND dismissed = 'n' AND date_dismissal <> '0000-00-00'";
$result = mysqli_query($link, $query);

/*while ($row = mysqli_fetch_assoc($result)) {
  echo '<pre>';
  var_dump($row);
  echo '</pre>';
}
exit;*/

while ($row = mysqli_fetch_assoc($result)) {
  $full_name_employee = $row["full_name"];
  $id_company = $row["id_company"];
  $id_department = $row["id_department"];
  $id_division = $row["id_division"];
  $id_office = $row["id_office"];
  $post_employee = $row["post"];
  $date_probationary_period = $row["date_probationary_period"];
  $format_date_probationary_period = date('d.m.Y', strtotime($date_probationary_period));

  $query_guide = "SELECT email FROM employee_data WHERE id_company = '" . $id_company . "' AND id_department = '" . $id_department . "' AND id_division = '" . $id_division . "' AND id_office = '" . $id_office . "' AND guide = 1 AND dismissed = 'n'";
  $result_guide = mysqli_query($link, $query_guide);
  $num_rows = mysqli_num_rows($result_guide);
  $row_guide = mysqli_fetch_assoc($result_guide);

  if ($id_office != 0 || $id_division != 0 && $id_office != 0) {
    $query_director = "SELECT email FROM employee_data WHERE id_company = '" . $id_company . "' AND id_department = '" . $id_department . "' AND id_division = '0' AND id_office = '0' AND guide = 1 AND dismissed = 'n'";
    $result_director = mysqli_query($link, $query_director);
    $row_director = mysqli_fetch_assoc($result_director);
    $director_email = $row_director['email'];
  }

  if ($num_rows != 0) {
    $guide_email = $row_guide["email"];
    if (isset($director_email)) {
      $guide_email .= "," . $director_email;
    }

    if ($id_company == '1' AND $id_department == '4' AND $id_division == '15' AND $id_office == '2') {
      $guide_email .= ',nelepa@tfnopt.ru,bazanov@tfnopt.ru';
    }
    if ($id_company == '1' AND $id_department == '4' AND $id_division == '0' AND $id_office == '43') {
      $guide_email .= ',nelepa@tfnopt.ru,bazanov@tfnopt.ru';
    }

    $mail = $guide_email . ",salakhova@tfnopt.ru,baryshnikova@tfnopt.ru,stukalo@tfnopt.ru,murzo@tfnopt.ru,kaliganov@tfnopt.ru";
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

    if ($id_company == '1' AND $id_department == '4' AND $id_division == '15' AND $id_office == '2') {
      $guide_email .= ',nelepa@tfnopt.ru,bazanov@tfnopt.ru';
    }
    if ($id_company == '1' AND $id_department == '4' AND $id_division == '0' AND $id_office == '43') {
      $guide_email .= ',nelepa@tfnopt.ru,bazanov@tfnopt.ru';
    }

    $mail = $guide_email . ",salakhova@tfnopt.ru,baryshnikova@tfnopt.ru,stukalo@tfnopt.ru,murzo@tfnopt.ru,kaliganov@tfnopt.ru";
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

// echo 'work';



$query = "SELECT * FROM employee_data WHERE date_probationary_period = '" . $format_date_45days . "' AND dismissed = 'n' AND date_dismissal <> '0000-00-00'";
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

  $query_guide = "SELECT email FROM employee_data WHERE id_company = '" . $id_company . "' AND id_department = '" . $id_department . "' AND id_division = '" . $id_division . "' AND id_office = '" . $id_office . "' AND guide = 1 AND dismissed = 'n'";
  $result_guide = mysqli_query($link, $query_guide);
  $num_rows = mysqli_num_rows($result_guide);
  $row_guide = mysqli_fetch_assoc($result_guide);

  if ($id_office != 0 || $id_division != 0 && $id_office != 0) {
    $query_director = "SELECT email FROM employee_data WHERE id_company = '" . $id_company . "' AND id_department = '" . $id_department . "' AND id_division = '0' AND id_office = '0' AND guide = 1 AND dismissed = 'n'";
    $result_director = mysqli_query($link, $query_director);
    $row_director = mysqli_fetch_assoc($result_director);
    $director_email = $row_director['email'];
  }

  if ($num_rows != 0) {
    $guide_email = $row_guide["email"];
    if (isset($director_email)) {
      $guide_email .= "," . $director_email;
    }

    if ($id_company == '1' AND $id_department == '4' AND $id_division == '15' AND $id_office == '2') {
      $guide_email .= ',nelepa@tfnopt.ru,bazanov@tfnopt.ru';
    }
    if ($id_company == '1' AND $id_department == '4' AND $id_division == '0' AND $id_office == '43') {
      $guide_email .= ',nelepa@tfnopt.ru,bazanov@tfnopt.ru';
    }

    $mail = $guide_email . ",salakhova@tfnopt.ru,baryshnikova@tfnopt.ru,stukalo@tfnopt.ru,murzo@tfnopt.ru,kaliganov@tfnopt.ru";
    $subject = 'Через полтора месяца закончится испытательный срок';
    $message = '<p>Через полтора месяца (' . $format_date_probationary_period . ') у сотрудника закончится испытательный срок.</p>';
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

    if ($id_company == '1' AND $id_department == '4' AND $id_division == '15' AND $id_office == '2') {
      $guide_email .= ',nelepa@tfnopt.ru,bazanov@tfnopt.ru';
    }
    if ($id_company == '1' AND $id_department == '4' AND $id_division == '0' AND $id_office == '43') {
      $guide_email .= ',nelepa@tfnopt.ru,bazanov@tfnopt.ru';
    }

    $mail = $guide_email . ",salakhova@tfnopt.ru,baryshnikova@tfnopt.ru,stukalo@tfnopt.ru,murzo@tfnopt.ru,kaliganov@tfnopt.ru";
    $subject = 'Через полтора месяца закончится испытательный срок';
    $message = '<p>Через полтора месяца (' . $format_date_probationary_period . ') у сотрудника закончится испытательный срок.</p>';
    $message .= '<p>ФИО: ' . $full_name_employee . '<br>Должность: ' . $post_employee . '</p>';
    $headers='';
    $headers.="Content-Type: text/html; charset=utf-8\r\n";
    $headers.="From: <staff@tfnopt.ru>\r\n";
    $headers.="X-Mailer: PHP/" . phpversion() . "\r\n";
    mail($mail, $subject, $message, $headers);
  }
}

// echo 'work 2';



$plus_1day = $today_timestamp + (24 * 60 * 60);
$format_date_1day = date('Y-m-d', $plus_1day);

$query_sklad = "SELECT * FROM employee_data WHERE date_probationary_period = '" . $format_date_1day . "' AND id_company = 1 AND id_department = 4 AND id_division = 15 AND id_office = 2 AND dismissed = 'n' AND date_dismissal = '0000-00-00'";
$result_sklad = mysqli_query($link, $query_sklad);

/*echo '<pre>';
var_dump($query_sklad);
echo '</pre>';*/

while ($row_sklad = mysqli_fetch_assoc($result_sklad)){

  echo '<pre>';
  var_dump($row_sklad);
  echo '</pre>';

  // Узнаем почту того, кто добавлял сотрудника
  $userDn = $row_sklad["way_in_ad"]; // Путь в AD
  $filter = '(&(objectClass=user)(objectCategory=PERSON))';
  $attributes = array("extensionAttribute7");
  $sr = ldap_search($connect, $userDn, $filter, $attributes);
  $userInfo = ldap_get_entries($connect, $sr);

  $admin_mail = $userInfo[0]["extensionattribute7"][0];
  $baseDn = 'OU=Сотрудники,OU=MEGA-F,DC=mega-f,DC=ru';

  // Получаем данные того, кто добавлял сотрудника
  $filter = '(&(objectClass=user)(objectCategory=PERSON)(|(proxyAddresses=*:' . $admin_mail . ')(mail=' . $admin_mail . ')))';
  $sr = ldap_search($connect, $baseDn, $filter);
  $userInfo = ldap_get_entries($connect, $sr);

  // Данные для отправки POST запроса на создание заявки
  $token = 'BV4BwR!pX3%dW#nue3@O1';

  $cn = $userInfo[0]["cn"][0]; // От кого создаётся заявка
  $mail = $userInfo[0]["mail"][0]; // Почта создателя заявки

  $dn_ad = explode(",", $userInfo[0]["dn"]);
  $dn = '"' . str_replace("OU=", "", $dn_ad[2]) . '","' . str_replace("OU=", "", $dn_ad[1]) . '"';
  $additionalUserInfo = '{"l":"'.$userInfo[0]["l"][0].'","title":"'.$userInfo[0]["title"][0].'","telephonenumber":"'.$userInfo[0]["telephonenumber"][0].'","samaccountname":"'.$userInfo[0]["samaccountname"][0].'","mail":"'.$userInfo[0]["mail"][0].'","dn":['.$dn.']}';

  $dep_name = 1; // Департамент IT
  $categori = 1; // Техника

  $subject = "Заканчивается испытательный срок (Склад Саларьево)"; // Тема заявки

  $message = "Сотрудник прошел испытательный срок.
Необходимо сделать постоянный пропуск на склад Саларьево.
ФИО: " . $row_sklad["full_name"] . "
Должность: " . $row_sklad["post"] . "
Ссылка на фото: http://portal.tfnopt.ru/images/people/" . $row_sklad["login"] . ".jpg";

  // POST данные с данными о сотруднике
  $postData = array('token' => $token, 'dep_name' => $dep_name, 'categori' => $categori, 'subject' => $subject, 'message' => $message, 'cn' => $cn, 'mail' => $mail, 'additionalUserInfo' => $additionalUserInfo);

  // POST запрос в систему заявок для создания заявки
  $Curl = curl_init();
  curl_setopt_array($Curl, array(
    CURLOPT_SSL_VERIFYPEER => FALSE,
    CURLOPT_URL => 'http://hesk.tfnopt.ru/submit_ticket.php',
    CURLOPT_RETURNTRANSFER => false,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $postData));
  $response = curl_exec($Curl);
  curl_close($Curl);
}
	
?>