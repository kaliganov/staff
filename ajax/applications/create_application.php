<?php

ob_start();
require_once '../connect.php';
require_once ("../../functions.php");
$auth = new AuthClass();
session_start();

// Переменная для понятия выход сотрудника или перевод
$emplApplicationType = $_POST['emplApplicationType'];
// Почта администратора
$admin_mail = $_POST['admin_mail'];


// Создаём ассоциативный массив с данными из ajax запроса
foreach($_POST['form_data'] as $row) {
	$update_arr[$row['name']] = trim($row['value']);
}


// Из адреса отдела формата AD делаем формат для письма
$department = dep($update_arr['office_in_ad']);
function dep($cn) {
	$arr_cn = array_reverse(explode(',', $cn));
	foreach($arr_cn as $row) {
		if(strpos($row, 'OU=') !== false) {
			if($row != 'OU=Сотрудники' and $row != 'OU=MEGA-F') {
			$arr[] = str_replace('OU=', '', $row);
			}
		}
	}
	return implode(", ", $arr);
}

$headOfDepartment = $update_arr['v_r'];

// Получаем данные администратора
$baseDn = 'OU=Сотрудники,OU=MEGA-F,DC=mega-f,DC=ru';
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

$dep_name = $_POST['emplDepName']; // Департамент IT
$categori = $_POST['emplCategori']; // Веб-сайты

$subject = $_POST['emplSubject'] . " " . $update_arr['family_name'] . " " . $update_arr['first_name']; // Тема заявки

// Преобразовываем тип договора
$tip_dogovora = '';
if ($update_arr['ttp'] == 'td') {
	$tip_dogovora = 'Трудовой договор';
} elseif ($update_arr['ttp'] == 'gph') {
	$tip_dogovora = 'ГПХ';
} elseif ($update_arr['ttp'] == 'du') {
	$tip_dogovora = 'Договор об оказании услуг';
}

if($emplApplicationType == "1"){
$message = 'Коллеги, добрый день!
К нам '.date("d.m.Y", strtotime($update_arr['extensionAttribute1'])).' выходит сотрудник:
1) Фамилия/Имя: '.$update_arr['family_name'].' '.$update_arr['first_name'].'
2) Отдел: '.$department.'
3) Должность: '.$_POST['emplTitle'].'
4) Электронная почта: '.$_POST['emplEmail'].'
5) Руководитель: '.$headOfDepartment.'
6) Тип трудового договора: ' . $tip_dogovora . '
'.$_POST['emplLastText'];
}

if($emplApplicationType == "2"){
$message = 'Коллеги, добрый день!
'.$update_arr['update_date_change_position'].' переводят сотрудника:
1) ФИО: '.$update_arr['full_name'].'
2) Отдел: '.$update_arr['department'].'
3) Должность: '.$_POST['emplTitle'].'
4) Электронная почта: '.$_POST['emplEmail'].'
'.$_POST['emplLastText'];
}

if($emplApplicationType == "3"){
$message = 'Коллеги, добрый день!
Вывод сотрудника из декрета:
1) ФИО: '.$update_arr['full_name'].'
2) Новая фамилия: '.$update_arr['new_family_name'].'
3) Логин: '.$update_arr['login'].'
4) Выходит в отдел: '.$update_arr['new_department'].'
'.$_POST['emplLastText'];
}


// POST данные с данными о сотруднике
$postData = array('token' => $token, 'dep_name' => $dep_name, 'categori' => $categori, 'subject' => $subject, 'message' => $message, 'cn' => $cn, 'mail' => $mail, 'additionalUserInfo' => $additionalUserInfo);


// POST запрос в систему заявок для создания заявки
$Curl = curl_init();
curl_setopt_array($Curl, array(
	CURLOPT_SSL_VERIFYPEER => FALSE,
	CURLOPT_URL => 'https://hesk.tfnopt.ru/submit_ticket.php',
	CURLOPT_RETURNTRANSFER => true,
	CURLOPT_POST => true,
	CURLOPT_POSTFIELDS => $postData));
$response = curl_exec($Curl);
curl_close($Curl);

if(strpos($response, 'К сожалению время сессии истекло')) { // Ошибка создания заявки
	echo '0';
}
else { // Заявка успешно создана
	echo '1';
}
	
?>