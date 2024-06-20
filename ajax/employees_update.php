<?

error_reporting(0);

require_once ("../functions.php");
$link = db_connect();

foreach($_POST['form_data'] as $row) {
  $array_update_data[$row["name"]] = $row["value"];
}

$dop_query = "";
$id_sotrudnika = $array_update_data["id_sotrudnika"];
$id_company = $array_update_data["id_company"];
$dop_query = "`id_company` = '" . (int)$id_company . "'";

if($array_update_data["l"]){
  $city = $array_update_data["l"];
  $dop_query .= ", `city` = '$city'";
}

if($array_update_data["citytelephonenumber"]){
  $city_phone = $array_update_data["citytelephonenumber"];
  $dop_query .= ", `city_phone` = '$city_phone'";
}

if($array_update_data["telephonenumber"]){
  $phone = $array_update_data["telephonenumber"];
  $dop_query .= ", `phone` = '$phone'";
}

if($array_update_data["mobile"]){
  $mobile = $array_update_data["mobile"];
  $dop_query .= ", `mobile_phone` = '$mobile'";
}

if($array_update_data["physicaldeliveryofficename"]){
  $office_num = $array_update_data["physicaldeliveryofficename"];
  $dop_query .= ", `office_num` = '$office_num'";
}

$date_employment = date("Y-m-d", strtotime($array_update_data["extensionAttribute1"]));
$dop_query .= ", `date_employment` = '$date_employment'";

$needNotifProb = false;
if ($array_update_data["date_probationary_period"] != ""){
    $date_probationary_period = date("Y-m-d", strtotime($array_update_data["date_probationary_period"]));
    $dop_query .= ", `date_probationary_period` = '$date_probationary_period'";

    $query_prob_per_origin = "SELECT * from employee_data where id = '" . $id_sotrudnika . "'";
    $result_prob_per_origin = mysqli_query($link, $query_prob_per_origin);
    $row_prob_per_origin = mysqli_fetch_assoc($result_prob_per_origin);
    $date_prob_per_origin = $row_prob_per_origin['date_probationary_period'];
  if ($date_prob_per_origin !== $date_probationary_period) {
    $needNotifProb = true;

    // Подключаемся к AD и тянем оттуда емэйл руководителя
    require_once (dirname(__DIR__)."/ajax/connect.php");
    $original_dn = $array_update_data["dn"];
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

    $id_company = $row_prob_per_origin['id_company'];
    $id_department = $row_prob_per_origin['id_department'];
    $id_division = $row_prob_per_origin['id_division'];
    $id_office = $row_prob_per_origin['id_office'];

    if ($id_company == '1' AND $id_department == '4' AND $id_division == '15' AND $id_office == '2') {
      $guide_email = 'nelepa@tfnopt.ru,bazanov@tfnopt.ru';
    }
    if ($id_company == '1' AND $id_department == '4' AND $id_division == '0' AND $id_office == '43') {
      $guide_email = 'nelepa@tfnopt.ru,bazanov@tfnopt.ru';
    }
  }

} else {
  $dop_query .= ", `date_probationary_period` = '0000-00-00'";
}

if($array_update_data["date_release_decree"] != ""){
  $date_release_decree = date("Y-m-d", strtotime($array_update_data["date_release_decree"]));
  $dop_query .= ", `date_release_decree` = '$date_release_decree'";
} else {
  $dop_query .= ", `date_release_decree` = '0000-00-00'";
}

if($array_update_data["date_withdrawal_decree"] != ""){
  $date_withdrawal_decree = date("Y-m-d", strtotime($array_update_data["date_withdrawal_decree"]));
  $dop_query .= ", `date_withdrawal_decree` = '$date_withdrawal_decree'";
} else {
  $dop_query .= ", `date_withdrawal_decree` = '0000-00-00'";
}

if($array_update_data["extensionAttribute2"] != ""){
  $date_birth = date("Y-m-d", strtotime($array_update_data["extensionAttribute2"]));
  $dop_query .= ", `date_birth` = '$date_birth'";
} else {
  $dop_query .= ", `date_birth` = '0000-00-00'";
}

if($array_update_data["snils"]){
  $snils = $array_update_data["snils"];
  $dop_query .= ", `snils` = '$snils'";
}

if($array_update_data["hide_in_ad"]){
  $hide_in_ad = $array_update_data["hide_in_ad"];
  $dop_query .= ", `hide_in_ad` = '$hide_in_ad'";
  $update_arr["extensionAttribute8"] = 1;
} else {
  $dop_query .= ", `hide_in_ad` = '0'";
  $update_arr["extensionAttribute8"] = 0;
}

if($array_update_data["extensionAttribute3"]){
  $guide = $array_update_data["extensionAttribute3"];
  $dop_query .= ", `guide` = '$guide'";
} else {
  $dop_query .= ", `guide` = '0'";
}

if($array_update_data["contractDMS"]){
  $dmc = $array_update_data["contractDMS"];
  $dop_query .= ", `dmc` = '$dmc'";
} else {
  $dop_query .= ", `dmc` = 'нет'";
}

if($array_update_data["dismissed"]){
  $dismissed = $array_update_data["dismissed"];
  $dop_query .= ", `dismissed` = '$dismissed'";
} else {
  $dop_query .= ", `dismissed` = 'n'";
}

$query_update = "UPDATE `employee_data` SET " . $dop_query . " WHERE `employee_data`.`id` = " . (int)$id_sotrudnika;
$result_update = mysqli_query($link, $query_update);

require_once 'connect.php';

foreach($_POST['form_data'] as $row) {
  if($row['name'] == "id_sotrudnika"){continue;}
  if($row['name'] == "id_company"){continue;}
  if($row['name'] == "citytelephonenumber"){continue;}
  if($row['name'] == "date_release_decree"){continue;}
  if($row['name'] == "date_withdrawal_decree"){continue;}
  if($row['name'] == "hide_in_ad"){continue;}
  if($row['name'] == "snils"){continue;}
  if($row['name'] == "contractDMS"){continue;}
  if($row['name'] == "dismissed"){continue;}
  if($row['name'] != 'dn') {
    if($row['value']) {
      $update_arr[$row['name']] = trim($row['value']);
    }
  } else {
    $empl_dn = $row['value'];
  }

  /* Если меняется город, то переписываем "область, край, регион" */
  if($row['name'] == 'l' and $row['value']) {
    $update_arr['st'] = trim($row['value']);
  }

  /* Так как нельзя задать атрибуту пустое значение, при удалении номера телефона ставим вмсето номера пробел */
  if($row['name'] == 'telephonenumber' and $row['value'] == '') {
    $update_arr[$row['name']] = '&nbsp;';
  }
}
	
/* Руководитель или нет */
if(!$update_arr['extensionAttribute3'] == 1) {
  $update_arr['extensionAttribute3'] = 0;
}
	
/* Дата выхода приводится к формату d.m.Y */
if($update_arr['extensionAttribute1']) {
  $update_arr['extensionAttribute1'] = date('d.m.Y', strtotime($update_arr['extensionAttribute1']));
}

	
/* Обновляем фото сотрудника */
if($_POST['file_img']){
  /* Уменьшаем размер картинки */
  $fileName = base64_decode(substr(strstr($_POST['file_img'], ','), 1));
		
  //Определяем размер фотографии — ширину и высоту
  $size=getimagesizefromstring ($fileName);
		
  //Создаём новое изображение из «старого»
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
		
  // Создаём и сохраняем фото на портал в папку с фотографиями сотрудников
  $filter = '(&(objectClass=user)(objectCategory=PERSON))';
  $justthese = array("samaccountname");
  $sr = ldap_search($connect, $empl_dn, $filter, $justthese);
  $info = ldap_get_entries($connect, $sr);
  $samaccountname = $info[0]['samaccountname'][0];
  // ImageJPEG ($dst, "C:/OSPanel/domains/portal.mega-f.ru/public_html/images/people/".$samaccountname.".jpg", 100);
  ImageJPEG ($dst, "C:/OSPanel/domains/staff.mega-f.ru/public_html/images/people/".$samaccountname.".jpg", 100);
		
  imagedestroy($src);
		
  $update_arr['thumbnailPhoto'] = file_get_contents("small_photo");
  unlink('small_photo');
}

foreach ($update_arr as $key => $value){
  if($key['id_sotrudnika'] != "0"){unset($update_arr["id_sotrudnika"]);}
  if($key['id_company'] != "0"){unset($update_arr["id_company"]);}
  if($key['citytelephonenumber'] != "пусто"){unset($update_arr["citytelephonenumber"]);}
  if($key['date_probationary_period'] != "пусто"){unset($update_arr["date_probationary_period"]);}
  if($key['date_release_decree'] != "пусто"){unset($update_arr["date_release_decree"]);}
  if($key['date_withdrawal_decree'] != "пусто"){unset($update_arr["date_withdrawal_decree"]);}
  if($key['snils'] != "пусто"){unset($update_arr["snils"]);}
  if($key['hide_in_ad'] != "пусто"){unset($update_arr["hide_in_ad"]);}
  if($key['contractDMS'] != "пусто"){unset($update_arr["contractDMS"]);}
  if($key['dismissed'] != "пусто"){unset($update_arr["dismissed"]);}
  unset($update_arr["mobile"]);
}

$result = ldap_modify($connect, $empl_dn, $update_arr);
$out = array();
if($result && $result_update) {
  if( $needNotifProb ){
      $out['users'] = include('employees_end_probation.php');

      // Отправляем оповещение о смене даты окончания испытательного срока
      $mail = $guide_email . ",salakhova@tfnopt.ru,baryshnikova@tfnopt.ru,stukalo@tfnopt.ru,murzo@tfnopt.ru,kaliganov@softworks.ru";
      $subject = 'Новая дата окончания испытательного срока';
      $message = '<p>ФИО: ' . $row_prob_per_origin['full_name'] . '<br>Должность: ' . $row_prob_per_origin['post'] . '</p>';
      $message .= '<p>Старая дата: ' . date("d.m.Y", strtotime($date_prob_per_origin)) . '</p>';
      $message .= '<p>Новая дата: ' . $array_update_data['date_probationary_period'] . '</p>';
      $headers = '';
      $headers .= "Content-Type: text/html; charset=utf-8\r\n";
      $headers .= "From: <staff@tfnopt.ru>\r\n";
      $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
      @mail($mail, $subject, $message, $headers);
  }else{
      $out['users'] = null;
  }
  $out['info'] = '<h2 style="text-align:center">Данные сотрудника изменены!</h2>';
} else {
  $out['users'] = null;
  $out['info'] = '<h2>При обновлении информации о сотруднике возникла ошибка!</h2><p>Свяжитесь с отделом Web-разработки</p>';
}

header('Content-Type: application/json');
echo json_encode(array(
    "info" => $out['info'],
    "users" => $out['users']
));

?>