<?php

//exit;
	
require_once dirname(__DIR__).'/ajax/connect.php';
require_once '../functions.php';
$link = db_connect();
	
	
	// Запускаем рекурсию для получения всех пользователей в AD
	$emplListArr = get_array($connect, '', 'OU=Сотрудники,OU=MEGA-F,DC=mega-f,DC=ru', $newTitelsArr);

	// ФУНКЦИИ

	// Получаем всех пользователей в AD
	function get_array($connect, $ou, $base_dn, $newTitelsArr){
	  global $link;

		$specific_dn = $base_dn;
		if ('' != $ou) {$specific_dn = 'OU='.$ou.','.$base_dn;}
		$filter = '(|(cn=*)(givenname=*)(ou=*))';
		$justthese = array("ou", "cn", "physicaldeliveryofficename", "samaccountname", "distinguishedname", "mail", "telephonenumber", "mobile", "title", "extensionAttribute1", "extensionAttribute2", "extensionAttribute3", "extensionAttribute6", "l"); // Сюда нужно добавлять имена полей в AD которые нужно получить в ответе.
		$sr = ldap_list($connect, $specific_dn, $filter, $justthese);
		$info = ldap_get_entries($connect, $sr);

		for ($i=0; $i < $info["count"]; $i++){
			$specific_ou = $info[$i]["ou"][0];

			if ($specific_ou  != ''){

				$res[] = get_array($connect, $specific_ou, $specific_dn, $newTitelsArr);

			}else{

				//echo '<pre>'; print_r($info[$i]); echo '</pre>';

				// Для наглядности  вывожу имя и фамилию сотрудника в данной итерации
        //echo "<pre>";
				//var_dump($info[$i]);
        //echo "<pre>";
				//echo '<br>';
				//echo $info[$i]["physicaldeliveryofficename"][0];
        //echo '<br><br><br>';
        /*echo '<h2>$info</h2>';
        echo '<pre>';
        print_r($info);
        echo '</pre>';
        echo '<br><br><br>';*/

				// Фамилия и имя
				$famNameArr = explode(' ', $info[$i]["cn"][0]); // Разбиваем по пробелу фамилию и имя
				if($famNameArr[1] == '') {$famNameArr[1] = $famNameArr[2];} // Исключение для Воробиной (лишний проблем между фамилией и именем)

        // Login
        $login = $info[$i]["samaccountname"][0];

				// Внутренний номер телефона
        if($info[$i]["telephonenumber"][0] == '&nbsp;'){
          $info[$i]["telephonenumber"][0] = '';
        }
        $phone = $info[$i]["telephonenumber"][0];

        // Мобильный
        $mobile_phone = $info[$i]["mobile"][0];

        // Город
        $city = $info[$i]["l"][0];

        // Номер кабинета
        $office_num = $info[$i]["physicaldeliveryofficename"][0];

        // Рабочая почта
        $email = $info[$i]["mail"][0];

        // Путь в AD
        $way_in_ad = $info[$i]["distinguishedname"][0];

        // Руководитель или нет
        $guide = $info[$i]["extensionAttribute3"][0];

        // Сортировка в AD
        $sorting = $info[$i]["extensionAttribute6"][0];

        $query = "UPDATE `employee_data`
        SET `login` = '". $login . "', 
            `phone` = '". $phone . "', 
            `mobile_phone` = '". $mobile_phone . "', 
            `city` = '". $city . "', 
            `office_num` = '". $office_num . "', 
            `email` = '". $email . "', 
            `way_in_ad` = '". $way_in_ad . "', 
            `guide` = '". $guide . "', 
            `sorting` = '". $sorting . "'
				WHERE `surname` = '" . $famNameArr[0] . "' AND `name` = '" . $famNameArr[1] . "'";
        $result = mysqli_query($link, $query);
        if($result){
          echo "обновил: " . $famNameArr[0] . "<br><br><br>";
        }else{
          echo "не получилось обновить: " . $famNameArr[0] . "<br><br><br>";
        }

				// Дата выхода на работу
				//$currentDateTime = new DateTime;
				//echo $date_employment = $currentDateTime->modify($info[$i]['extensionattribute1'][0])->format('Y-m-d');
        //$date_elements = explode('.', $info[$i]['extensionattribute2'][0]);
				//$timestamp = mktime(0,0,0,$date_elements[1],$date_elements[0],$date_elements[2]);
        //$date_birth = date('Y-m-d', $timestamp);
        //echo '<br><br><br>';
				//$date_birth = $currentDateTime->modify($info[$i]['extensionattribute2'][0])->format('Y-m-d');

				// Запрос в БД на изменение данных
				//echo "'UPDATE `employee_data` SET `date_employment` = '$date_employment' WHERE `surname` = '$famNameArr[0]' AND `name` = '$famNameArr[1]'";
				/*echo $info[$i]["samaccountname"][0] .
          ', ' . $info[$i]["extensionattribute1"][0] .
          ', ' . $info[$i]["extensionattribute2"][0] .
          ', ' . $info[$i]["extensionattribute3"][0] .
          ', ' . $info[$i]["distinguishedname"][0];
				echo '<br><br><br>';*/


				/*$query = "UPDATE `employee_data`
        SET `sorting` = '". $info[$i]["extensionattribute6"][0] . "' 
				WHERE `surname` = '" . $famNameArr[0] . "' AND `name` = '" . $famNameArr[1] . "'";
        $result = mysqli_query($link, $query);
        if($result){echo "обновил: " . $famNameArr[0] . "<br><br><br>";}*/
			}

		}
		return $res ;
	}

?>