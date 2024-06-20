<?
	exit;

	require_once 'connect.php';
	
	$empl_dn = 'CN=Гусева Анна,OU=Отдел Управленческого учета,OU=Финансовое управление,OU=Сотрудники,OU=MEGA-F,DC=mega-f,DC=ru';
	
	
	$update_arr['title'] = 'Специалист';


	//$result = ldap_modify($connect, $empl_dn, $update_arr);
	if($result) {$out = 'Данные сотрудника изменены!';} else {$out = ldap_error($connect);}

?>