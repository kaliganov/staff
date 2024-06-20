<?php

ob_start();
require_once 'connect.php';
require_once ("../functions.php");
$link = db_connect();
$auth = new AuthClass();
session_start();

$empl_dn = $_POST['empl_dn'];


$dn = $empl_dn;
$newRdn = current(explode(',', $empl_dn));
$newParent = 'OU=Декрет,OU=MEGA-F,DC=mega-f,DC=ru';

// Получаем имя учётной записи сотрудника
$filter = '(&(objectClass=user)(objectCategory=PERSON))';
$sr = ldap_search($connect, $empl_dn, $filter);
$info = ldap_get_entries($connect, $sr);
$samaccountname = $info[0]['samaccountname'][0];

// Блокируем пользователя
$update_arr['userAccountControl'] = 0x0202; // 514
$result = ldap_modify($connect, $empl_dn, $update_arr);

// Переносим в папку Декрет
$result = ldap_rename($connect, $dn, $newRdn, $newParent, true);
if($result) {
$out = '<h2>Данные сотрудника изменены!</h2><p>Пользователь заблокирован и перенесён в скрытый разел Декрет!</p>';

$text = "Дата: " . date("Y-m-d H:i:s") . "\nПеревод сотрудника в декрет: " . str_replace('CN=', '', $newRdn) . "\nИз отдела: " . dep($dn) . "\nАдминистратор: " . $auth->getLogin() . "\nСотрудник успешно переведен\n\n\n";
file_put_contents("../logs/employees_dekret.txt", $text, FILE_APPEND);

} else {
$out = '<h2>При переводе сотрудника в декрет возникла ошибка!</h2><p>Свяжитесь с отделом Web-разработки</p>';

$text = "Дата: " . date("Y-m-d H:i:s") . "\nПопытка перевести сотрудника в декрет: " . str_replace('CN=', '', $newRdn) . "\nИз отдела: " . dep($dn) . "\nАдминистратор: " . $auth->getLogin() . "\nОшибка:" . ldap_error($connect) . "\n\n\n";
file_put_contents("../logs/employees_dekret.txt", $text, FILE_APPEND);
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


$mail = 'ad_mail@tfnopt.ru,kaliganov@tfnopt.ru';
$subject = 'Сотрудник ушёл в декрет в AD '.str_replace('CN=', '', $newRdn);
$message = '<p>Администратор: '.$auth->getLogin().'</p>';
$message .= 'Сотрудник ушёл в декрет: '.str_replace('CN=', '', $newRdn).' ('.$samaccountname.')<br>';
$message .= 'Из: '.dep($dn).'<br>';
$headers='';
$headers.="Content-Type: text/html; charset=utf-8\r\n";
$headers.="From: <staff@tfnopt.ru>\r\n";
$headers.="X-Mailer: PHP/".phpversion()."\r\n";
mail($mail, $subject, $message, $headers);


echo $out;

?>