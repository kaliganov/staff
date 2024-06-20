<?php

	exit;
	
	require_once dirname(__DIR__).'/ajax/connect.php';
	
	// Запускаем рекурсию для получения всех пользователей в AD
	$emplListArr = get_array($connect, '', 'OU=Сотрудники,OU=MEGA-F,DC=mega-f,DC=ru');

	
	// ФУНКЦИИ
	
	// Получаем всех пользователей в AD
	function get_array($connect, $ou, $base_dn)
	{
		
		$specific_dn = $base_dn;
		if ('' != $ou) {$specific_dn = 'OU='.$ou.','.$base_dn;}
		$filter = '(|(cn=*)(givenname=*)(ou=*))';
		$justthese = array("ou", "cn", "samaccountname", "mail", "telephonenumber", "mobile", "title", "extensionAttribute12", "l");
		$sr = ldap_list($connect, $specific_dn, $filter, $justthese);
		$info = ldap_get_entries($connect, $sr);

		for ($i=0; $i < $info["count"]; $i++)
		{
			$specific_ou = $info[$i]["ou"][0];

			if ($specific_ou  != ''){ 
			
				$res[] = get_array($connect, $specific_ou, $specific_dn);
				
			}else{
				
				$res[] = str_replace(' ', ';', $info[$i]["cn"][0]).';'.$info[$i]["samaccountname"][0].';'.$info[$i]["mail"][0].';'.str_replace('&nbsp;', '', $info[$i]["telephonenumber"][0]).';'.$info[$i]["mobile"][0].';'.$info[$i]["title"][0].';'.department_for_sap($info[$i]["dn"]);
				
				unset($empl_dn);
				$update_arr = array();
				
				echo $empl_dn = $info[$i]["dn"];
				echo '<br>';
				
				$department = department_for_sap($info[$i]["dn"]);
				
				if($department == 'Департамент Внутренней логистики и производства') {
					$department = 'ДВЛ и производства';
				}
				
				echo $update_arr['extensionAttribute12'] = $department;
				echo '<br>';
				
				$result = ldap_modify($connect, $empl_dn, $update_arr);
				if($result) {$out = 'Данные сотрудника изменены!';} else {$out = 'Ошибка!';}
				echo $out;
				
				echo '<br><br>';
				
			}
			
		}
		return $res ;
	}

	// Получаем департамент из DN
	function department_for_sap($cn) {
		$arr_cn = array_reverse(explode(',', $cn));
		$departmentOut = str_replace('OU=', '', $arr_cn[4]);
		if($departmentOut == 'Департамент Внутренней логистики и производства') {
			$departmentOut = 'ДВЛ и производства';
		}
		return $departmentOut;
	}

?>