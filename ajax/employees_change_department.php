<?php

ob_start();
require_once 'connect.php';
require_once ("../functions.php");
$link = db_connect();
$auth = new AuthClass();
session_start();

foreach($_POST['form_data'] as $row) {
	$update_arr[$row['name']] = $row['value'];
}

// Создаём необходимые переменные

$dn = $update_arr['dn'];
$newRdn = 'CN='.$update_arr['cn'];
$newParent = $update_arr['new_department'];
$new_way_in_ad = $newRdn . "," . $newParent;
$id_sotrudnika = $update_arr['id'];

$query = "UPDATE `employee_data` SET `way_in_ad` = '" . $new_way_in_ad . "' WHERE `employee_data`.`id` = '" . $id_sotrudnika . "'";
$result = mysqli_query($link, $query);


// Ищем максимальное значиние атрибута сортировки для отдела в который переносим нового сотрудника. И присваиваем новому сотруднику сортировочный номер на 1 больше максимально существующего в данном отделе.

$specific_dn = $newParent;
if ('' != $ou) {$specific_dn = 'OU='.$ou.','.$base_dn;}
$filter = '(|(cn=*)(givenname=*)(ou=*))';
$justthese = array("ou", "cn", "givenname", "mail", "extensionAttribute6");
$sr = ldap_list($connect, $specific_dn, $filter, $justthese);
$info = ldap_get_entries($connect, $sr);
$sort_number = 1;
foreach($info as $key => $row) {
	if($row["extensionattribute6"][0] > $sort_number) {
		$sort_number = $row["extensionattribute6"][0];
	}
}
$sort_number++;


// Получае название нового департамента и передаём его для изменения.

function department_for_sap($cn) {
	$arr_cn = array_reverse(explode(',', $cn));
	$departmentOut = str_replace('OU=', '', $arr_cn[4]);
	if($departmentOut == 'Департамент Внутренней логистики и производства') {
		$departmentOut = 'ДВЛ и производства';
	}
	return $departmentOut;
}
$update_ext['extensionAttribute12'] = department_for_sap($newParent);


// Переносим сотрудника в другой отдел.

$result = ldap_rename($connect, $dn, $newRdn, $newParent, true);

if($result) {
$out = '<h2 style="text-align:center">Пользователь перенесён в другой отдел!</h2>';

$text = "Дата: " . date("Y-m-d H:i:s") . "\nПеревод сотрудника в AD: " . str_replace('CN=', '', $newRdn) . "\nИз отдела: " . dep($dn) . "\nВ отдел: " . dep($newParent) . "\nАдминистратор: " . $auth->getLogin() . "\nСотрудник успешно переведен\n\n\n";
file_put_contents("../logs/employees_change_department.txt", $text, FILE_APPEND);
} else {
	$out = "<h2>При переводе сотрудника возникла ошибка!</h2><p>Свяжитесь с отделом Web-разработки</p>";

$text = "Дата: " . date("Y-m-d H:i:s") . "\nПри перевод сотрудника в AD: " . str_replace('CN=', '', $newRdn) . "\nИз отдела: " . dep($dn) . "\nВ отдел: " . dep($newParent) . "\nАдминистратор: " . $auth->getLogin() . "\nВозникла ошибка: " . ldap_error($connect) . "\n\n\n";
file_put_contents("../logs/employees_change_department.txt", $text, FILE_APPEND);
}

	
// Присваиваем перенесённому сотруднику сортировочный номер на 1 больше максимального в новом отделе. И меняем департамент в дополнительном аттрибуте.

$new_dn = $newRdn.','.$newParent;
$update_ext['extensionAttribute6'] = $sort_number;
$result_new = ldap_modify($connect, $new_dn, $update_ext);


// Отправка оповещения

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

echo $out;
exit;




//$mail = 'nisnevich@tfnopt.ru,dolgiy@tfnopt.ru,gorshkov@tfnopt.ru';
$mail = 'ad_mail@tfnopt.ru';
$subject = 'В AD сотрудник '.str_replace('CN=', '', $newRdn).' перенесён в другой отдел';
$message = '<p>Администратор: '.$auth->getLogin().'</p>';
$message .= 'Сотрудник: '.str_replace('CN=', '', $newRdn).'<br>';
$message .= 'Из: '.dep($dn).'<br>';
$message .= 'В: '.dep($newParent);
$headers='';
$headers.="Content-Type: text/html; charset=utf-8\r\n";
$headers.="From: <staff@tfnopt.ru>\r\n";
$headers.="X-Mailer: PHP/".phpversion()."\r\n";
mail($mail, $subject, $message, $headers);

echo $out;

?>