<?php

require_once dirname(__DIR__).'/ajax/connect.php';
require_once (dirname(__DIR__)."/functions.php");
$link = db_connect();

function kama_create_csv_file( $create_data, $file = null, $col_delimiter = ';', $row_delimiter = "\r\n" ){
  if( ! is_array($create_data) )
    return false;
  if( $file && ! is_dir( dirname($file) ) )
    return false;
  // строка, которая будет записана в csv файл
  $CSV_str = '';
  // перебираем все данные
  foreach( $create_data as $row ){
    $cols = array();
    foreach( $row as $col_val ){
      // строки должны быть в кавычках ""
      // кавычки " внутри строк нужно предварить такой же кавычкой "
      if( $col_val && preg_match('/[",;\r\n]/', $col_val) ){
        // поправим перенос строки
        if( $row_delimiter === "\r\n" ){
          $col_val = str_replace( "\r\n", '\n', $col_val );
          $col_val = str_replace( "\r", '', $col_val );
        }elseif( $row_delimiter === "\n" ){
          $col_val = str_replace( "\n", '\r', $col_val );
          $col_val = str_replace( "\r\r", '\r', $col_val );
        }
        $col_val = str_replace( '"', '""', $col_val ); // предваряем "
        $col_val = '"'. $col_val .'"'; // обрамляем в "
      }
      $cols[] = $col_val; // добавляем колонку в данные
    }
    $CSV_str .= implode( $col_delimiter, $cols ) . $row_delimiter; // добавляем строку в данные
  }
  $CSV_str = rtrim( $CSV_str, $row_delimiter );
  // задаем кодировку windows-1251 для строки
  if( $file ){
    $CSV_str = iconv( "UTF-8", "cp1251",  $CSV_str );
    // создаем csv файл и записываем в него строку
    $done = file_put_contents( $file, $CSV_str );
    return $done ? $CSV_str : false;
  }
  return $CSV_str;
}

$query = "SELECT 
`data`.`login` as `Логин`, 
`data`.`full_name` as `ФИО`, 
`data`.`surname` as `Фамилия`, 
`data`.`name` as `Имя`, 
`data`.`patronymic` as `Отчество`, 
`com`.`company_name` as `Компания`, 
`dep`.`de_name` as `Департамент`, 
`div`.`div_name` as `Подразделение`, 
`off`.`off_name` as `Отдел`, 
`data`.`post` as `Должность`, 
`data`.`date_employment` as `Дата выхода на работу`, 
`data`.`date_probationary_period` as `Дата окончания испытательного срока  `, 
`data`.`date_change_position` as `Дата смены должности`, 
`data`.`date_release_decree` as `Дата начала декрета`, 
`data`.`date_withdrawal_decree` as `Дата окончания декрета`, 
`data`.`date_dismissal` as `Дата увольнения`, 
`data`.`type_employment_contract` as `Тип тругового договора`, 
`data`.`dmc` as `ДМС`, 
`data`.`cfo` as `ФС`, 
`data`.`fte` as `Ставка (FTE)`, 
`data`.`snils` as `СНИЛС`, 
`data`.`date_birth` as `Дата рождения`, 
`data`.`city_phone` as `Городской телефон`, 
`data`.`phone` as `Внутренний телефон`, 
`data`.`mobile_phone` as `Мобильный`, 
`data`.`city` as `Город`, 
`data`.`office_num` as `Кабинет`, 
`data`.`email` as `Рабочая почта` 
FROM `employee_data` as `data` 
LEFT JOIN `company` as `com` ON `data`.`id_company` = `com`.`id` 
LEFT JOIN `department` as `dep` ON `data`.`id_department` = `dep`.`id` 
LEFT JOIN `division` as `div` ON `data`.`id_division` = `div`.`id`
LEFT JOIN `office` as `off` ON `data`.`id_office` = `off`.`id`
WHERE DATE(`data`.`date_employment`) BETWEEN '1995-01-01 00:00:00' AND '2012-06-25 00:00:00'";

$result = mysqli_query($link, $query);

$array_first_line = array();
$array_rest_line = array();
$count = 1;
while ($row = mysqli_fetch_assoc($result)) {
  $array_inter_line = array();
  if($count == 1){
    foreach ($row as $key => $value){
      $array_first_line[] = $key;
      $array_inter_line[] = $value;
    }
  }else{
    foreach ($row as $key => $value){
      $array_inter_line[] = $value;
    }
  }
  $count++;
  $array_rest_line[] = $array_inter_line;
  unset($array_inter_line);
}

$total_array = array();
$total_array[] = $array_first_line;
foreach ($array_rest_line as $key => $value){
  $total_array[] = $value;
}

$today_date = date("d_m_Y", time());
$reportFileName = "golden_fond_$today_date.csv";
$total_result = kama_create_csv_file($total_array, "$reportFileName");

//$mail = 'kaliganov@tfnopt.ru'; // murzo@tfnopt.ru, karaseva@tfnopt.ru
$mail = 'murzo@tfnopt.ru, karaseva@tfnopt.ru';
$subject = 'Отчет по сотрудникам';
$message = '<p>Отчет по сотрудникам сформирован, расположен по адресу:  file:///\\atlant.mega-f.ru\o\HR\Employee_report</p>';
$message .= "<p>Имя файла: $reportFileName</p>";
$headers='';
$headers.="Content-Type: text/html; charset=utf-8\r\n";
$headers.="From: <staff@tfnopt.ru>\r\n";
$headers.="X-Mailer: PHP/" . phpversion() . "\r\n";
mail($mail, $subject, $message, $headers);
mail('vjatkin@tfnopt.ru', $subject, $message, $headers);

?>