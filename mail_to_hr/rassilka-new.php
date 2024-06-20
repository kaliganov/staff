<?php

	//exit;
	
	require_once __DIR__.'/ajax/connect.php';
	
	// Запускаем рекурсию для получения всех пользователей в AD
	$emplListArr = get_array($connect, '', 'OU=Сотрудники,OU=MEGA-F,DC=mega-f,DC=ru');
	
	// Преобразуем массив в одномерный
	$oneArr = makeSingleArray($emplListArr);
	
	// Сортируем массив по значениям
	sort($oneArr);
	
	// Собираем строку из элементов массива
	foreach($oneArr as $row) {
		$out .= iconv( "UTF-8","cp1251", $row);
	}
	
	// Добавляем в строку названия полей
	$firstLine = iconv( "UTF-8","cp1251", 'фамилия;имя;login;e-mail;телефон;моб. телефон;должность;департамент;город;extensionAttribute13');
	$out = $firstLine.PHP_EOL.$out;
	
	// Сохраняем данные о сотрудниках в файл
	$fd = fopen(__DIR__ ."/user.csv", 'w') or die("Не удалось создать файл");
	fwrite($fd, $out);
	fclose($fd);
	
	// Отправляем письмо с вложением
	$file = __DIR__ ."/user.csv"; // файл
	//$mailTo = "nisnevich@tfnopt.ru,prokudina@tfnopt.ru,vyazmina@tfnopt.ru,murzo@tfnopt.ru"; // кому
	$mailTo = "kaliganov@tfnopt.ru"; // кому
	$from = "staff@tfnopt.ru"; // от кого
	$subject = "Выгрузка пользователей из AD"; // тема письма
	$message = ""; // текст письма
	$r = sendMailAttachment($mailTo, $from, $subject, $message, $file); // отправка письма c вложением
	//echo ($r)?'Письмо отправлено':'Ошибка. Письмо не отправлено!';
	
	//sendMailAttachment("vjatkin@tfnopt.ru", $from, $subject, $message, $file);
	
	// ФУНКЦИИ
	
	// Отправляем письмо с вложением
	function sendMailAttachment($mailTo, $from, $subject, $message, $file = false){
		$separator = "---"; // разделитель в письме
		// Заголовки для письма
		$headers = "MIME-Version: 1.0\r\n";
		$headers .= "From: <staff@tfnopt.ru>\r\n"; // задаем от кого письмо
		$headers .= "Content-Type: multipart/mixed; boundary=\"$separator\""; // в заголовке указываем разделитель
		// если письмо с вложением
		if($file){
			$bodyMail = "--$separator\n"; // начало тела письма, выводим разделитель
			$bodyMail .= "Content-Type: text/html; charset=utf-8\r\n"; // кодировка письма
			$bodyMail .= "Content-Transfer-Encoding: quoted-printable"; // задаем конвертацию письма
			$bodyMail .= "Content-Disposition: attachment; filename==?utf-8?B?".base64_encode(basename($file))."?=\n\n"; // задаем название файла
			$bodyMail .= $message."\n"; // добавляем текст письма
			$bodyMail .= "--$separator\n";
			$fileRead = fopen($file, "r"); // открываем файл
			$contentFile = fread($fileRead, filesize($file)); // считываем его до конца
			fclose($fileRead); // закрываем файл
			$bodyMail .= "Content-Type: application/octet-stream; name==?utf-8?B?".base64_encode(basename($file))."?=\n"; 
			$bodyMail .= "Content-Transfer-Encoding: base64\n"; // кодировка файла
			$bodyMail .= "Content-Disposition: attachment; filename==?utf-8?B?".base64_encode(basename($file))."?=\n\n";
			$bodyMail .= chunk_split(base64_encode($contentFile))."\n"; // кодируем и прикрепляем файл
			$bodyMail .= "--".$separator ."--\n";
		// письмо без вложения
		}else{
			$bodyMail = $message;
		}
		$result = mail($mailTo, $subject, $bodyMail, $headers); // отправка письма
		return $result;
	}
	
	// Получаем всех пользователей в AD
	function get_array($connect, $ou, $base_dn)
	{
		
		$specific_dn = $base_dn;
		if ('' != $ou) {$specific_dn = 'OU='.$ou.','.$base_dn;}
		$filter = '(|(cn=*)(givenname=*)(ou=*))';
		$justthese = array("ou", "cn", "samaccountname", "mail", "telephonenumber", "mobile", "title", "extensionAttribute12", "l", "extensionAttribute13");
		$sr = ldap_list($connect, $specific_dn, $filter, $justthese);
		$info = ldap_get_entries($connect, $sr);

		for ($i=0; $i < $info["count"]; $i++)
		{
			$specific_ou = $info[$i]["ou"][0];

			if ($specific_ou  != ''){ 
			
				//$res .= get_array($connect, $specific_ou, $specific_dn);
				$res[] = get_array($connect, $specific_ou, $specific_dn);
				
			}else{
				
				//$res .= str_replace(' ', ';', $info[$i]["cn"][0]).';'.$info[$i]["samaccountname"][0].';'.$info[$i]["mail"][0].';'.$info[$i]["telephonenumber"][0].';'.$info[$i]["mobile"][0].';'.$info[$i]["title"][0].';'.department_for_sap($info[$i]["dn"]).';'.$info[$i]["l"][0].PHP_EOL;
				
				$famNameArr = explode(' ', $info[$i]["cn"][0]);
				if($famNameArr[1] == '') {$famNameArr[1] = $famNameArr[2];} // Исключение для Воробиной (лишний проблем между фамилией и именем)
				
				$res[] = $famNameArr[0].';'.$famNameArr[1].';'.$info[$i]["samaccountname"][0].';'.$info[$i]["mail"][0].';'.str_replace('&nbsp;', '', $info[$i]["telephonenumber"][0]).';'.$info[$i]["mobile"][0].';'.$info[$i]["title"][0].';'.department_for_sap($info[$i]["dn"]).';'.$info[$i]["l"][0].';'.$info[$i]["extensionattribute13"][0].PHP_EOL;

				/*echo "<pre>";
				var_dump($info[$i]["extensionattribute13"][0]);
        echo "</pre>";*/
			}
			
		}
    echo "<pre>";
    var_dump($res);
    echo "</pre>";
		return $res;
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
	
	// Преобразуем многомерный массив в одномерный
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

?>