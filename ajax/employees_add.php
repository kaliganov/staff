<?php

ob_start();
require_once 'connect.php';
session_start();

// Подключаемся к БД для дубляжа
function db_connect(){
  define ('MYSQL_SERVER', 'staff.mega-f.ru');
  define ('MYSQL_USER', 'root');
  define ('MYSQL_PASSWORD', 'Ralmes%%');
  define ('MYSQL_DB', 'staff');
  define ('PATHSITE', '');   //Относительный путь до катаога со скриптом
  $link = mysqli_connect(MYSQL_SERVER, MYSQL_USER, MYSQL_PASSWORD, MYSQL_DB) or die ("Error:".mysqli_error($link));
  if (!mysqli_set_charset($link, "utf8")){
    printf("Error: ".mysqli_error($link));
  }
  return $link;
}

$link = db_connect();


foreach($_POST['form_data'] as $row) {

  if($row['name'] != 'dn') {
    $update_arr[$row['name']] = trim($row['value']);
  }
  else {
    $empl_dn = $row['value'];
  }

}

foreach($update_arr as $key => $value)
{
  $$key = $value;
}

//Узнаем есть ли такой сотрудник в базе
if($extensionAttribute2){
  $date_birth = date("Y-m-d", strtotime($extensionAttribute2));
}
$query = "SELECT * FROM `employee_data` WHERE `surname` = '$family_name' AND `name` = '$first_name' AND `date_birth` = '$date_birth' AND dismissed = 'n'";
$result = mysqli_query($link, $query);
while ($row = mysqli_fetch_assoc($result)) {
  $double = $row;
}


// Записываем почту руководителя (выбор в анкете добавления сотрудника)
$dep_direct_email = ',' . $v_r;


$dep_dn = explode(",", $dep_dn);

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

// Если добавляют сотрудника в Склад Саларьево, добавляем в рассылку почту охраны
$security_mail = "";
if($id_company == 1 && $id_department == 4 && $id_division == 15 && $id_office == 2){
  $security_mail = ",ohrana_sol@tfnopt.ru";
}

$post = explode(",", $post);
$cfo = $post[0];
$post = $post[1];
if($handwritten_post){
  $post = $handwritten_post;
  $cfo = $handwritten_cfo;
}

// Если добавляют "Кладовщика-комплектовщика", добавляем в рассылку почту Филашова
$logistic_managers_mail = "";
if($id_company == 1 && $id_department == 4 && $id_division == 15 && $id_office == 2 ||
  $id_company == 1 && $id_department == 4 && $id_division == 0 && $id_office == 43){
  $logistic_managers_mail = ",nelepa@tfnopt.ru,filashov@tfnopt.ru";
}

$city = "";
if($l) {
  $city = $l;
}else{
  $city = "Не указан";
}


$trans_family_name = translit($family_name);
$trans_first_name = translit($first_name);

// Получаем samaccountname всех пользователей в AD
$account_array1 = get_array($connect, '', 'OU=Сотрудники,OU=MEGA-F,DC=mega-f,DC=ru');
$account_array2 = get_array($connect, '', 'OU=Disabled,OU=MEGA-F,DC=mega-f,DC=ru');
$account_array3 = get_array($connect, '', 'OU=Декрет,OU=MEGA-F,DC=mega-f,DC=ru');

//$account_array4 = get_array($connect, '', 'OU=ГПХ,OU=MEGA-F,DC=mega-f,DC=ru');
$account_array5 = get_array($connect, '', 'OU=Разное,OU=MEGA-F,DC=mega-f,DC=ru');
$account_array6 = get_array($connect, '', 'OU=Contacts,OU=MEGA-F,DC=mega-f,DC=ru');
$account_array7 = get_array($connect, '', 'OU=Outsourсe,OU=MEGA-F,DC=mega-f,DC=ru');
$account_array8 = get_array($connect, '', 'OU=Groups,OU=MEGA-F,DC=mega-f,DC=ru');

//$account_array = array_merge($account_array1, $account_array2, $account_array3, $account_array4, $account_array5, $account_array6, $account_array7, $account_array8);
$account_array = array_merge($account_array1, $account_array2, $account_array3, $account_array5, $account_array6, $account_array7, $account_array8);

// Преобразуем массив в одномерный и удаляем пустые элементы
$oneDimensional = array_diff(makeSingleArray($account_array), array(''));

$nameLenght = strlen($trans_first_name); // Количество букв в имени
$account = $trans_family_name;
for ($i=0; $i <= $nameLenght; $i++) {
  if((!in_array(lcfirst($account), $oneDimensional) and !in_array(ucfirst($account), $oneDimensional))) {
    break;
  }
  else {

    if($i == 0) {
      $account = $trans_first_name[$i].'.'.$account;
    }
    else {
      $account = substr_replace($account, $trans_first_name[$i], $i, 0);
    }

  }
}

$dn = 'CN='.$family_name.' '.$first_name.','.$office_in_ad;


$newuser["objectclass"][0] = "top";
$newuser["objectclass"][1] = "person";
$newuser["objectclass"][2] = "organizationalPerson";
$newuser["objectclass"][3] = "user";
$newuser['userAccountControl'] = 0x0220; // 544
$newuser['cn'] = $family_name.' '.$first_name;
$newuser['sn'] = $family_name;
$newuser['givenname'] = $first_name;
$newuser['displayname'] = $family_name.' '.$first_name;
$newuser['samAccountName'] = $account;
$newuser['extensionAttribute13'] = $account;
if($hide_in_ad){
  $newuser['extensionAttribute8'] = $hide_in_ad;
}else{
  $newuser['extensionAttribute8'] = "0";
}
$newuser['title'] = $post;
$newuser['mail'] = $account.'@'.$company_email;



function department_for_sap($cn) {
  $arr_cn = array_reverse(explode(',', $cn));
  $departmentOut = str_replace('OU=', '', $arr_cn[4]);
  if($departmentOut == 'Департамент Внутренней логистики и производства') {
    $departmentOut = 'ДВЛ и производства';
  }
  return $departmentOut;
}
$newuser['extensionAttribute12'] = department_for_sap($dn);

if($telephoneNumber){$newuser['telephoneNumber'] = $telephoneNumber;}
//if($mobile){$newuser['mobile'] = $mobile;}
if($l){$newuser['l'] = $l; $newuser['st'] = $l;}
if($physicalDeliveryOfficeName){$newuser['physicalDeliveryOfficeName'] = $physicalDeliveryOfficeName;}
/*if($department){$newuser['department'] = $department;}*/
if($company){$newuser['company'] = $company;}
if($account){$newuser['userPrincipalName'] = $account.'@mega-f.ru';}

if($extensionAttribute1){$newuser['extensionAttribute1'] = $extensionAttribute1;}
if($extensionAttribute2){$newuser['extensionAttribute2'] = $extensionAttribute2;}
if($extensionAttribute3 == 1){$newuser['extensionAttribute3'] = 1;} else{$newuser['extensionAttribute3'] = 0;}
//if($extensionAttribute4){$newuser['extensionAttribute4'] = $extensionAttribute4;}
if($extensionAttribute5){$newuser['extensionAttribute5'] = $extensionAttribute5;}

// Ищем максимальное значиние атрибута сортировки для отдела в котором создаём нового сотрудника. И присваиваем новому сотруднику сортировочный номер на 1 больше максимально существующего в данном отделе.
$specific_dn = $office_in_ad;
if ('' != $ou) {$specific_dn = 'OU='.$ou.','.$base_dn;}
$filter = '(|(cn=*)(givenname=*)(ou=*))';
$justthese = array("ou", "cn", "givenname", "mail", "extensionAttribute6", "extensionAttribute3");
$sr = ldap_list($connect, $specific_dn, $filter, $justthese);
$info = ldap_get_entries($connect, $sr);
$sort_number = 1;
foreach($info as $key => $row) {
  if($row["extensionattribute6"][0] > $sort_number) {
    $sort_number = $row["extensionattribute6"][0];
  }
}
$sort_number++;
$newuser['extensionAttribute6'] = $sort_number;

// Записываем e-mail администратора создавшего пользователя в AD
$newuser['extensionAttribute7'] = $_SESSION['admin_session_user_email'];

// ГПХ
if($ttp == "gph") {
  $contractGPHout = 'да';
} else {
  $contractGPHout = 'нет';
}

// ПК
if($needPC == 1) {
  $needPC = 'да';
} else {
  $needPC = 'нет';
}

// ДМС
if(!isset($contractDMS)) {
  $contractDMS = 'нет';
}


if($_POST['file_img']){
  /* Уменьшаем размер картинки */

  //$fileName = $newuser['thumbnailPhoto'];
  $fileName = base64_decode(substr(strstr($_POST['file_img'], ','), 1));

  //Определяем размер фотографии — ширину и высоту
  $size=getimagesizefromstring ($fileName);

  //Создаём новое изображение из «старого»
  //$src=ImageCreateFromJPEG ($fileName);
  $src=imagecreatefromstring ($fileName);

  //Берём числовое значение ширины фотографии, которое мы получили в первой строке и записываем это число в переменную
  $iw=$size[0];

  //Проделываем ту же операцию, что и в предыдущей строке, но только уже с высотой.
  $ih=$size[1];

  //Ширину фотографии делим на 300 т.к. на выходе мы хотим получить фото шириной в 300 пикселей. В результате получаем коэфициент соотношения ширины оригинала с будущей превьюшкой.
  $koe=$iw/300;

  //Делим высоту изображения на коэфициент, полученный в предыдущей строке, и округляем число до целого в большую сторону — в результате получаем высоту нового изображения.
  $new_h=ceil ($ih/$koe);

  //Создаём пустое изображение шириной в 300 пикселей и высотой, которую мы вычислили в предыдущей строке.
  $dst=ImageCreateTrueColor (300, $new_h);

  //Данная функция копирует прямоугольную часть изображения в другое изображение, плавно интерполируя пикселные значения таким образом, что, в частности, уменьшение размера изображения сохранит его чёткость и яркость.
  ImageCopyResampled ($dst, $src, 0, 0, 0, 0, 300, $new_h, $iw, $ih);

  //Сохраняем полученное изображение в формате JPG
  ImageJPEG ($dst, "small_photo", 100);
  imagedestroy($src);


  $newuser['thumbnailPhoto'] = file_get_contents("small_photo");
  unlink('small_photo');
}


//Дублируем данные в MySQL
//list($devision) = explode(",", $dep_dn);
$full_name = "$family_name $first_name $patronymic";
$email = $account . "@" . $company_email;
//$company = $extensionAttribute4;

/*switch($company){
  case "tfnopt.ru": $company = "ООО \"ТФН\""; break;
  case "tfn-trading.ru": break;
  case "tfnopt.by": break;
  case "smartlifesystem.ru": break;
}*/

$td = $ttp;
switch($td){
  case "td": $td = "Трудовой договор"; break;
  case "gph": $td = "ГПХ"; break;
  case "du": $td = "Договор об оказании услуг"; break;
}

$dismissed = "n";

$extensionAttribute1 = date("Y-m-d", strtotime($extensionAttribute1));
if($extensionAttribute2){
  $extensionAttribute2 = date("Y-m-d", strtotime($extensionAttribute2));
}
if($extensionAttribute10){
  $extensionAttribute10 = date("Y-m-d", strtotime($extensionAttribute10));
}


//$devision = str_replace('OU=', '', $devision);

// Если добавляем только в базу данных
if(!empty($double)){
  $query = "INSERT INTO `employee_data` (
                    `id`, 
                    `login`, 
                    `full_name`, 
                    `surname`, 
                    `name`, 
                    `patronymic`, 
                    `id_company`, 
                    `id_department`, 
                    `id_division`, 
                    `id_office`, 
                    `post`, 
                    `date_employment`, 
                    `date_probationary_period`, 
                    `date_change_position`, 
                    `date_release_decree`, 
                    `date_withdrawal_decree`, 
                    `date_dismissal`, 
                    `type_employment_contract`, 
                    `dmc`, 
                    `cfo`, 
                    `fte`, 
                    `snils`, 
                    `date_birth`, 
                    `city_phone`, 
                    `phone`, 
                    `mobile_phone`, 
                    `city`, 
                    `office_num`, 
                    `email`,
                    `way_in_ad`,
                    `guide`,
                    `sorting`,
                    `hide_in_ad`,
                    `dismissed`
      ) VALUES (
			NULL,
			'',
			'".$full_name."',
			'".$family_name."',
			'".$first_name."',
			'".$patronymic."',
			'".$id_company."',
			'".$id_department."',
			'".$id_division."',
			'".$id_office."',
			'".$post."',
			'".$extensionAttribute1."',
			'".$extensionAttribute10."',
			'".$date_change_position."',
			'".$date_release_decree."',
			'".$date_withdrawal_decree."',
			'".$date_dismissal."',
			'".$td."',
			'".$contractDMS."',
			'".$cfo."',
			'".$fte."',
			'".$snils."',
			'".$extensionAttribute2."',
			'".$telephoneCityNumber."',
			'".$telephoneNumber."',
			'".$mobile."',
			'".$city."',
			'".$physicalDeliveryOfficeName."',
			'',
			'',
			'".$extensionAttribute3."',
			'".$sort_number."',
			'".$hide_in_ad."',
			'".$dismissed."'
		)";
  $result = mysqli_query($link, $query);
}else{
  $query = "INSERT INTO `employee_data` (
                    `id`, 
                    `login`, 
                    `full_name`, 
                    `surname`, 
                    `name`, 
                    `patronymic`, 
                    `id_company`, 
                    `id_department`, 
                    `id_division`, 
                    `id_office`, 
                    `post`, 
                    `date_employment`, 
                    `date_probationary_period`, 
                    `date_change_position`, 
                    `date_release_decree`, 
                    `date_withdrawal_decree`, 
                    `date_dismissal`, 
                    `type_employment_contract`, 
                    `dmc`, 
                    `cfo`, 
                    `fte`, 
                    `snils`, 
                    `date_birth`, 
                    `city_phone`, 
                    `phone`, 
                    `mobile_phone`, 
                    `city`, 
                    `office_num`, 
                    `email`,
                    `way_in_ad`,
                    `guide`,
                    `sorting`,
                    `hide_in_ad`,
                    `dismissed`
      ) VALUES (
			NULL,
			'".$account."',
			'".$full_name."',
			'".$family_name."',
			'".$first_name."',
			'".$patronymic."',
			'".$id_company."',
			'".$id_department."',
			'".$id_division."',
			'".$id_office."',
			'".$post."',
			'".$extensionAttribute1."',
			'".$extensionAttribute10."',
			'".$date_change_position."',
			'".$date_release_decree."',
			'".$date_withdrawal_decree."',
			'".$date_dismissal."',
			'".$td."',
			'".$contractDMS."',
			'".$cfo."',
			'".$fte."',
			'".$snils."',
			'".$extensionAttribute2."',
			'".$telephoneCityNumber."',
			'".$telephoneNumber."',
			'".$mobile."',
			'".$city."',
			'".$physicalDeliveryOfficeName."',
			'".$email."',
			'".$dn."',
			'".$extensionAttribute3."',
			'".$sort_number."',
			'".$hide_in_ad."',
			'".$dismissed."'
		)";
  $result = mysqli_query($link, $query);
}


$query_company = "SELECT company_name FROM company WHERE id = '" . $id_company . "'";
$result_company = mysqli_query($link, $query_company);
while ($row_company = mysqli_fetch_assoc($result_company)) {
  $which_department = $row_company["company_name"] . " -> ";
}
if($id_department){
  $query_department = "SELECT de_name FROM department WHERE id = '" . $id_department . "'";
  $result_department = mysqli_query($link, $query_department);
  while ($row_department = mysqli_fetch_assoc($result_department)) {
    $which_department .= $row_department["de_name"] . " -> ";
  }
}
if($id_division){
  $query_division = "SELECT div_name FROM division WHERE id = '" . $id_division . "'";
  $result_division = mysqli_query($link, $query_division);
  while ($row_division = mysqli_fetch_assoc($result_division)) {
    $which_department .= $row_division["div_name"] . " -> ";
  }
}
if($id_office){
  $query_office = "SELECT off_name FROM office WHERE id = '" . $id_office . "'";
  $result_office = mysqli_query($link, $query_office);
  while ($row_office = mysqli_fetch_assoc($result_office)) {
    $which_department .= $row_office["off_name"];
  }
}

if($result){
  $text = "Дата: " . date("Y-m-d H:i:s") . "\nДобавление сотрудника: " . $full_name . "\nДолжность: " . $post . "\nВ какой отдел: " . $which_department . "\nКто добавляет: " . $_SESSION['admin_session_user_second_name'] . " " . $_SESSION['admin_session_user_name'] . "\nСотрудник успешно добавлен в БД\n\n";
  file_put_contents("../logs/employees_add.txt", $text, FILE_APPEND);
}else{
  $text = "Дата: " . date("Y-m-d H:i:s") . "\nНе удалось добавить сотрудника: " . $full_name . "\nДолжность: " . $post . "\nВ какой отдел: " . $which_department . "\nКто добавляет: " . $_SESSION['admin_session_user_second_name'] . " " . $_SESSION['admin_session_user_name'] . "\nОшибка: " . var_dump($result) . "\n\n";
  file_put_contents("../logs/employees_add.txt", $text, FILE_APPEND);
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


// Форматирование даты в привычный вид
$release_date = '';
if($newuser['extensionAttribute1']){
  $release_date = date('d-m-Y', strtotime($newuser['extensionAttribute1']));
}
if($extensionAttribute10){
  $date_probationary_period = date('d-m-Y', strtotime($extensionAttribute10));
}


if(!empty($double)) {
  $mail = 'ad_mail@tfnopt.ru, kaliganov@tfnopt.ru' . $dep_direct_email;
  //$mail = 'kaliganov@tfnopt.ru';
  $subject = 'Сотрудник добавлен только в базу данных (в AD не добавляется) ' . $newuser['cn'];
  $message = '<p>Администратор: ' . $_SESSION['admin_session_user_second_name'] . ' ' . $_SESSION['admin_session_user_name'] . '</p>';
  $message .= 'Новый сотрудник: ' . $newuser['cn'] . '<br>';
  $message .= 'Должность: ' . $newuser['title'] . '<br>';
  //$message .= 'ФС: ' . $cfo . '<br>';
  $message .= 'Отдел: ' . $department . '<br>';
  $message .= 'Дата выхода: ' . $release_date . '<br>';
  $message .= 'Дата окончания испытательного срока: ' . $date_probationary_period . '<br>';
  $message .= 'ГПХ: ' . $contractGPHout . '<br>';
  $headers='';
  $headers.="Content-Type: text/html; charset=utf-8\r\n";
  $headers.="From: <staff@tfnopt.ru>\r\n";
  $headers.="X-Mailer: PHP/" . phpversion() . "\r\n";
  mail($mail, $subject, $message, $headers);

  // Очистка буфера
  ob_end_clean();
  echo '{"success":"2"}';

} else { // END if(!empty($double))

  $result_ad = ldap_add($connect, $dn, $newuser);

  if($result_ad) {

    $mail = 'ad_mail@tfnopt.ru,kaliganov@tfnopt.ru' . $dep_direct_email . $security_mail . $logistic_managers_mail;
    //$mail = 'kaliganov@tfnopt.ru';
    $subject = 'В AD добавлен новый сотрудник ' . $newuser['cn'];
    $message = '<p>Администратор: ' . $_SESSION['admin_session_user_second_name'] . ' ' . $_SESSION['admin_session_user_name'] . '</p>';
    $message .= 'Новый сотрудник: ' . $newuser['cn'] . '<br>';
    $message .= 'Должность: ' . $newuser['title'] . '<br>';
    $message .= 'ФС: ' . $cfo . '<br>';
    $message .= 'Отдел: ' . $department . '<br>';
    $message .= 'Логин: ' . $account . '<br>';
    $message .= 'Почта: ' . $newuser['mail'] . '<br>';
    $message .= 'Кабинет: ' . $newuser['physicalDeliveryOfficeName'] . '<br>';
    $message .= 'Дата выхода: ' . $release_date . '<br>';
    $message .= 'Дата окончания испытательного срока: ' . $date_probationary_period . '<br>';
    $message .= 'ГПХ: ' . $contractGPHout . '<br>';
    $message .= 'Новый ПК: ' . $needPC;
    $headers='';
    $headers.="Content-Type: text/html; charset=utf-8\r\n";
    $headers.="From: <staff@tfnopt.ru>\r\n";
    $headers.="X-Mailer: PHP/" . phpversion() . "\r\n";
    mail($mail, $subject, $message, $headers);

    // Очистка буфера
    ob_end_clean();

    echo '{"success":"1","emplEmail":"'.$newuser['mail'].'","emplTitle":"'.$newuser['title'].'"}';

    $text = "В AD добавление сотрудника в отдел: " . $office_in_ad . "\nДобавление сотрудника в AD прошло успешно\n\n\n";
    file_put_contents("../logs/employees_add.txt", $text, FILE_APPEND);

  } else { // END if($result_ad)

    echo '{"success":"3"}';

    $errors = ldap_error($connect);
    $text = "Не удалось добавить сотрудника в AD, отдел: " . $office_in_ad . "\nОшибка: " . $errors . "\n\n\n";
    file_put_contents("../logs/employees_add.txt", $text, FILE_APPEND);

  }

}



























//if($result) {echo '{"success":"1","emplEmail":"'.$newuser['mail'].'","emplTitle":"'.$newuser['title'].'"}';} else {$errors = ldap_error($connect);}

//echo '{"success":"1","emplEmail":"'.$newuser['mail'].'","emplTitle":"'.$newuser['title'].'"}';

//}


/* *************************************       Функции        ************************************ */

/* Функция транслитерации */

function translit($text)
{
  $alphabet_ru = array
  (
    'а','б','в','г','д','е','ё','ж','з','и','й','к','л','м','н','о','п','р','с','т','у','ф','х','ц','ч','ш','щ','ъ','ы','ь','э','ю','я',
    'А','Б','В','Г','Д','Е','Ё','Ж','З','И','Й','К','Л','М','Н','О','П','Р','С','Т','У','Ф','Х','Ц','Ч','Ш','Щ','Ъ','Ы','Ь','Э','Ю','Я',
    ' '
  );

  $alphabet_eng = array
  (
    'a','b','v','g','d','e','e','zh','z','i','y','k','l','m','n','o','p','r','s','t','u','f','kh','ts','ch','sh','shch','','y','','e','yu','ya',
    'a','b','v','g','d','e','e','zh','z','i','y','k','l','m','n','o','p','r','s','t','u','f','kh','ts','ch','sh','shch','','y','','e','yu','ya',
    ''
  );

  return str_replace($alphabet_ru, $alphabet_eng, $text);
}

/* Получаем samaccountname всех пользователей в AD */

function get_array($connect, $ou, $base_dn)
{
  $specific_dn = $base_dn;
  if ('' != $ou) {$specific_dn = 'OU='.$ou.','.$base_dn;}
  $filter = '(|(cn=*)(givenname=*)(ou=*))';
  $justthese = array("ou", "samaccountname");
  $sr = ldap_list($connect, $specific_dn, $filter, $justthese);
  $info = ldap_get_entries($connect, $sr);

  for ($i=0; $i < $info["count"]; $i++)
  {
    $specific_ou = $info[$i]["ou"][0];

    if ($specific_ou  != ''){
      $res[] = get_array($connect, $specific_ou, $specific_dn);
    }else{
      $res[$i]["samaccountname"] = $info[$i]["samaccountname"][0];
    }

  }
  return $res ;
}

/* Преобразуем массив в одномерный */

function makeSingleArray($arr){
  if(!is_array($arr)) return false;
  $tmp = array();
  foreach($arr as $val){
    if(is_array($val)){
      $tmp = array_merge($tmp, makeSingleArray($val));
    } else {
      $tmp[] = $val;
    }
  }
  return $tmp;
}

//echo $errors;

?>