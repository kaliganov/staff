<?php
ob_start();
require_once 'connect.php';
require_once ("../functions.php");
$link = db_connect();
$auth = new AuthClass();
session_start();

$empl_email = $_POST['form_data'][5]["value"];
$old_dep_dn = $_POST['form_data'][6]["value"];
$full_name = $_POST['form_data'][7]["value"];
$id_sotrudnika = $_POST['form_data'][8]["value"];
$way_in_ad = $_POST['form_data'][9]["value"];
$old_post = $_POST['form_data'][10]["value"];
$login = $_POST['form_data'][11]["value"];

if ($_POST['form_data'][12]["name"] == "needPCTransfer" || $_POST['form_data'][13]["name"] == "needPCTransfer") {
  $need_pc = "да";
} else {
  $need_pc = "нет";
}

$flag = true;
if ($_POST['form_data'][12]["name"] == "dontSeePortal") {
  $flag = false;
}


$date_change_position = date("Y-m-d", strtotime($_POST["form_data"][4]["value"]));

$post = $_POST["form_data"][3]["value"];
$post = explode(",", $post);
$cfo = $post[1];
$post = $post[0];

$guide = "";
if($post == "Руководитель отдела" OR $post == "Директор департамента"){
  $guide = ", `guide` = '1'";
}else{
  $guide = ", `guide` = '0'";
}

//$date_change_position = date("Y-m-d", time());


// Меняем должность у сотрудника в AD
$update_ext["title"] = $post;
$result_ad = ldap_modify($connect, $way_in_ad, $update_ext);


$dep_dn = explode(",", $_POST["form_data"][0]["value"]);

$count = 1;
foreach ($dep_dn as $key => $value) {
  switch ($count++) {
    case 1:
      $id_company = (int) $value;
      break;
    case 2:
      $id_department = (int) $value;
      break;
    case 3:
      $id_division = (int) $value;
      break;
    case 4:
      $id_office = (int) $value;
      break;
  }
}

$query_department_name = "SELECT de_name from department where id = '" . $id_department . "'";
$result_department_name = mysqli_query($link, $query_department_name);
while ($row_department_name = mysqli_fetch_assoc($result_department_name)) {
  $department_name = $row_department_name["de_name"];
}

$query_division_name = "SELECT div_name from division where id = '" . $id_division . "'";
$result_division_name = mysqli_query($link, $query_division_name);
while ($row_division_name = mysqli_fetch_assoc($result_division_name)) {
  $division_name = $row_division_name["div_name"];
}

$query_office_name = "SELECT off_name from office where id = '" . $id_office . "'";
$result_office_name = mysqli_query($link, $query_office_name);
while ($row_office_name = mysqli_fetch_assoc($result_office_name)) {
  $office_name = $row_office_name["off_name"];
}

$new_dep_dn = $department_name;
if($id_division > 0){
  $new_dep_dn .= " → " . $division_name;
}
if($id_office > 0) {
  $new_dep_dn .= " → " . $office_name;
}

$query = "UPDATE `employee_data`
SET `id_company` = '" . $id_company . "', 
`id_department` = '" . $id_department . "', 
`id_division` = '" . $id_division . "', 
`id_office` = '" . $id_office . "', 
`date_change_position` = '" . $date_change_position . "', 
`cfo` = '" . $cfo .  "', 
`post` = '" . $post .  "'" . $guide . "
WHERE `employee_data`.`id` = '" . (int)$id_sotrudnika . "'";
$result = mysqli_query($link, $query);

if ($flag) {
  $query_transfer = "INSERT INTO employee_transfer 
    (login, full_name, email, previous_position, date_transfer, post, otkuda, kuda) 
    VALUE 
    ('$login', '$full_name', '$empl_email', '$old_post', '$date_change_position', '$post', '$old_dep_dn', '$new_dep_dn')";
  $result_transfer = mysqli_query($link, $query_transfer);
}


//if($result) {$out = 'Пользователь перенесён в другой отдел!';}

if($result) {
  $mail = 'ad_mail@tfnopt.ru,kaliganov@tfnopt.ru';
  $subject = 'Перевод сотрудника ' . $full_name;
  $message = '<p>Администратор: '.$auth->getLogin().'</p>';
  $message .= 'Сотрудник: ' . $full_name . '<br>';
  $message .= 'Должность: ' . $post . '<br>';
  $message .= 'Из: ' . $old_dep_dn . '<br>';
  $message .= 'В: ' . $new_dep_dn . '<br>';
  $message .= 'Новый ПК: ' . $need_pc;
  $headers='';
  $headers.="Content-Type: text/html; charset=utf-8\r\n";
  $headers.="From: <staff@tfnopt.ru>\r\n";
  $headers.="X-Mailer: PHP/".phpversion()."\r\n";
  mail($mail, $subject, $message, $headers);

  // Очистка буфера
  ob_end_clean();

  echo '{"success":"1","emplEmail":"'.$empl_email.'","emplTitle":"'.$post.'"}';

  $text = "Дата: " . date("Y-m-d H:i:s") . "\nПеревод сотрудника в ШР: " . $full_name . "\nДолжность: " . $post . "\nИз отдела: " . $old_dep_dn . "\nВ отдел: " . $new_dep_dn . "\nАдминистратор: " . $auth->getLogin() . "\nСотрудник успешно переведен\n\n\n";
  file_put_contents("../logs/employees_change_department_mysql.txt", $text, FILE_APPEND);
}else{
  $text = "Дата: " . date("Y-m-d H:i:s") . "\nПопытка перевода сотрудника в ШР: " . $full_name . "\nДолжность: " . $post . "\nИз отдела: " . $old_dep_dn . "\nВ отдел: " . $new_dep_dn . "\nАдминистратор: " . $auth->getLogin() . "\nОшибка: " . $result . "\n\n\n";
  file_put_contents("../logs/employees_change_department_mysql.txt", $text, FILE_APPEND);
}

?>