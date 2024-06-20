<?php
ob_start();
require_once 'connect.php';

session_start();
error_reporting(NULL);

function build_tree($connect, $ou = '', $base_dn = 'OU=Сотрудники,OU=MEGA-F,DC=mega-f,DC=ru') {
  $specific_dn = $base_dn;
  if ('' != $ou) {$specific_dn = 'OU='.$ou.','.$base_dn;}
  $filter = '(|(cn=*)(givenname=*)(ou=*))';
  $justthese = array("ou", "cn", "givenname", "mail");
  $sr = ldap_list($connect, $specific_dn, $filter, $justthese);
  $info = ldap_get_entries($connect, $sr);

  for ($i=0; $i < $info["count"]; $i++) {
    $specific_ou = $info[$i]["ou"][0];
    if ($specific_ou  != '') {
      $result .= '<option '.$selected.' value="OU='.$specific_ou.','.$specific_dn.'">'.depth($specific_dn).' '.$specific_ou.'</option>';
      $result .= build_tree($connect, $specific_ou, $specific_dn);
    }
  }
  return $result;
}

function depth($specific_dn) {
  $specific_dn_array = explode(',', $specific_dn);
  $qty = count($specific_dn_array);

  for($i=0; $i<$qty-4; $i++) {
    $depth .= '—';
  }

  return $depth;
}

require_once ("../functions.php");
$link = db_connect();

// Записываем айдишники в массив
$dep_dn = explode(",", $_POST['dep_dn']);

// Из масива пишем переменные
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

// Переписываем сервер почты в зависимости от департамента или отдела
$company_email = 'tfnopt.ru';
if($id_company == 5){
  $company_email = 'tfnopt.by';
}
if($id_company == 6){
  $company_email = 'tfn-trading.com';
}
if($id_company == 4){
  $company_email = 'softworks.ru';
}
if($id_company == 7){
  $company_email = 'tfnopt.kz';
}
if($id_office == 36){
  $company_email = 'smartlifesystem.ru';
}


// Пишем переменные с запросами в базу
$company_name = '';
$query_cn = "SELECT company_name FROM company WHERE id = '" . $id_company . "'";
$result_cn = mysqli_query($link, $query_cn);
while ($row_cn = mysqli_fetch_assoc($result_cn)) {
  $company_name = $row_cn['company_name'];
}

$office_name = '';
if($id_office == 0){
  if($id_division == 0){
    $query_on = "SELECT de_name FROM department WHERE id = '" . $id_department . "'";
    $result_on = mysqli_query($link, $query_on);
    while ($row_on = mysqli_fetch_assoc($result_on)) {
      $office_name = $row_on['de_name'];
    }
  }else{
    $query_on = "SELECT div_name FROM division WHERE id = '" . $id_division . "'";
    $result_on = mysqli_query($link, $query_on);
    while ($row_on = mysqli_fetch_assoc($result_on)) {
      $office_name = $row_on['div_name'];
    }
  }
}else{
  $query_on = "SELECT off_name FROM office WHERE id = '" . $id_office . "'";
  $result_on = mysqli_query($link, $query_on);
  while ($row_on = mysqli_fetch_assoc($result_on)) {
    $office_name = $row_on['off_name'];
  }
}

// Запрашиваем названия должностей у работающих сотрудников
$query_so = "SELECT post FROM employee_data 
WHERE id_company = '" . $id_company . "' 
AND id_department = '" . $id_department . "' 
AND id_division = '" . $id_division . "' 
AND id_office = '" . $id_office . "'
AND dismissed = 'n'";
$result_so = mysqli_query($link, $query_so);

$inter_array_sotrudniki = array();
while ($row_so = mysqli_fetch_assoc($result_so)) {
  $inter_array_sotrudniki[] = $row_so['post'];
}

// Запрашиваем названия должностей и их количество
$query_pp = "SELECT pos_name, quantity FROM position_parallel 
WHERE id_company = '" . $id_company . "' 
AND id_department = '" . $id_department . "' 
AND id_division = '" . $id_division . "' 
AND id_office = '" . $id_office . "'";
$result_pp = mysqli_query($link, $query_pp);

$array_dolgnosti = array();
while ($row = mysqli_fetch_assoc($result_pp)) {
  $array_dolgnosti[$row['pos_name']] = $row['quantity'];
}

// Вычисляем свободные должности для добавления
$proverka = '';
foreach ($inter_array_sotrudniki as $key => $value) {
  foreach ($array_dolgnosti as $key_2 => $value_2){
    if($value == $key_2) {
      $array_dolgnosti[$key_2] = (int) $array_dolgnosti[$key_2] - 1;
    }
  }
}

// Список городов
$city = array();
$query_city = "SELECT city FROM city";
$result_city = mysqli_query($link, $query_city);
while ($row_city = mysqli_fetch_assoc($result_city)) {
  $city[] = $row_city['city'];
}

$flag = false;
foreach ($array_dolgnosti as $key => $value) {
  if($value >= 1) {
    //Работаем
    $flag = true;
  }
}

// Узнаем почту руководителя департамента 
$email_plus = "";
$query_email = "SELECT email FROM employee_data 
WHERE id_company = '" . $id_company . "' 
AND id_department = '" . $id_department . "' 
AND id_division = 0 
AND id_office = 0 
AND guide = 1
AND dismissed = 'n'";
$result_email = mysqli_query($link, $query_email);

while ($row = mysqli_fetch_assoc($result_email)) {
  $email_plus .= "," . $row['email'];
}


// Узнаем почту руководителя отдела (если выбран отдел)
if($id_office > 0){
  $query_email = "SELECT email FROM employee_data 
  WHERE id_company = '" . $id_company . "' 
  AND id_department = '" . $id_department . "' 
  AND id_division = '" . $id_division . "' 
  AND id_office = '" . $id_office . "' 
  AND guide = 1
  AND dismissed = 'n'";
  $result_email = mysqli_query($link, $query_email);

  while ($row = mysqli_fetch_assoc($result_email)) {
    $email_plus .= "," . $row['email'];
  }
}

if($id_department == 16 || $id_department == 17 || $id_department == 31 || $id_department == 33){
  $email_plus .= ",salishev@tfnopt.ru,saftenko@tfnopt.ru";
}


if($flag){
  $out .= '
  <h3>Введите данные сотрудника</h3>
	<form class="uk-form" data-uk-form-select>
	  <input name="dep_dn" value="'.$_POST['dep_dn'].'" type="hidden">
		<input name="dep_direct_email" value="'.$email_plus.'" type="hidden">
    <input name="department" value="'.$office_name.'" type="hidden">';

  $out .= '
    <p><span class="red">Тип трудового договора:</span></p>
		<p class="tip_trudov">
		  <select id="dogovor" name="ttp">
			  <option value="none">---</option>
			  <option value="td">Трудовой договор</option>
			  <option value="gph">ГПХ</option>
			  <option value="du">Договор об оказании услуг</option>
		  </select>&nbsp;&nbsp;<span class="red">*</span>
		</p>
		<p id="vneShtata">Вне штата&nbsp;&nbsp;<input id="vneShtataInput" name="vneShtataGalka" value="1" size="40" type="checkbox"></p>
		<div class="skritie_post">
		  <p><span class="red">Выберите должность:</span></p>
      <p><select id="position_select" name="post">
      <option value="none" required>---</option>';

  foreach ($array_dolgnosti as $key => $value) {
    if ($value >= 1) {
      $query_cfo = "SELECT cfo FROM position_parallel 
                    WHERE id_company = '" . $id_company . "' 
                    AND id_department = '" . $id_department . "' 
                    AND id_division = '" . $id_division . "' 
                    AND id_office = '" . $id_office . "' 
                    AND pos_name = '" . $key . "'";
      $result_cfo = mysqli_query($link, $query_cfo);
      while ($row = mysqli_fetch_assoc($result_cfo)) {
        $cfo = $row["cfo"];
      }
      $out .= '<option value="' . $cfo . ',' . $key .'">' . $key . ' - ' . $value . 'шт.</option>';
    }
  }

  $out .= '</select>&nbsp;&nbsp;<span class="red">*</span></p></div>';


  $out .= '
    <div id="handwritten_post_p">
      <p><span class="red">Введите название должности:</span></p>
      <p>
        <input name="handwritten_post" placeholder="Введите название должности" value="" size="40" type="text">&nbsp;&nbsp;<span class="red">*</span>
      </p>
      <p>
        <input name="handwritten_cfo" placeholder="Введите ФС" value="" size="40" type="text">&nbsp;&nbsp;<span class="red">*</span>
      </p>
    </div>
    <p><input name="family_name" placeholder="Фамилия" value="" size="40" type="text">&nbsp;&nbsp;<span class="red">*</span></p>
    <p><input name="first_name" placeholder="Имя" value="" size="40" type="text">&nbsp;&nbsp;<span class="red">*</span></p>
		<p><input name="patronymic" placeholder="Отчество" value="" size="40" type="text"></p>';

  $out .= '
		<p id="stavka_fte">Ставка (FTE)&nbsp;&nbsp;<input id="change_fte" name="fte" placeholder="Ставка (FTE)" value="1" size="3" type="text">&nbsp;&nbsp;<span class="red">*</span></p>
		<p>Выберите город:</p>
		<p>
		  <select id="city" name="l">
        <option value="none" required>---</option>';

  foreach ($city as $key => $value) {
    $out .= '<option value="' . $value .'">' . $value . '</option>';
  }

  $out .= '</select>&nbsp;&nbsp;<span class="red">*</span></p>
		<p><input id="city_number" name="telephoneCityNumber" placeholder="Номер городского телефона" value="" size="40" type="text"></p>
		<p><input name="telephoneNumber" placeholder="Номер внутреннего телефона" value="" size="40" type="text"></p>
		<p><input id="mobile" name="mobile" placeholder="Номер мобильного телефона" value="" size="40" type="text"></p>
		<p><input name="physicalDeliveryOfficeName" placeholder="Кабинет" value="" size="40" type="text"></p>
	  <p><input 
	              id="date_employment" 
	              name="extensionAttribute1" 
	              placeholder="Дата выхода" 
	              value="" size="40" 
	              type="text" 
	              data-uk-datepicker="{format:\'DD.MM.YYYY\'}" 
	              required>&nbsp;&nbsp;<span class="red">*</span></p>
    <p id="dataIsp"><input 
                id="date_probationary_period" 
                name="extensionAttribute10" 
                placeholder="Дата окончания испытательного срока" 
                value="" 
                size="40" 
                type="text" 
                data-uk-datepicker="{format:\'DD.MM.YYYY\'}" 
                required>&nbsp;&nbsp;<span class="red">*</span></p>
		<p><input 
		            id="date_birth" 
		            name="extensionAttribute2" 
		            placeholder="Дата рождения" 
		            value="" 
		            size="40" 
		            type="text" 
		            data-uk-datepicker="{format:\'DD.MM.YYYY\'}">&nbsp;&nbsp;<span class="red">*</span></p>
		<p><input name="snils" placeholder="СНИЛС" value="" size="40" type="text"></p>
    <p>Скрыть на портале: <input id="combi" name="hide_in_ad" value="1" size="40" type="checkbox"></p>';

  $out .= '
    <div id="office_select">
      <p><span class="red">Выберите отдел для отображения на портале:</span></p>
      <p><select name="office_in_ad">
        <option value="none">---</option>';

  $out .= build_tree($connect);

  $out .= '</select>&nbsp;&nbsp;<span class="red">*</span></p></div>';

  $out .= '
    <div id="vibor_rukovoditelya">
      <p><span class="red">Выберите руководителя:</span></p>
      <p><select name="v_r">
        <option value="none">---</option>';

  $query_ruk = "SELECT full_name, email FROM employee_data WHERE date_dismissal = '0000-00-00' AND dismissed = 'n' ORDER BY full_name";
  $result_ruk = mysqli_query($link, $query_ruk);
  while ($row = mysqli_fetch_assoc($result_ruk)) {
    $out .= '<option value="' . $row['email'] . '">' . $row['full_name'] . '</option>';
  }

  $out .= '</select>&nbsp;&nbsp;<span class="red">*</span></p></div>';

  $out .= '
		<p id="rukovoditel">Руководитель: <input name="extensionAttribute3" value="1" size="40" type="checkbox"></p>
		<div class="uk-form-row" style="margin-top: 25px;">
		  <label style="cursor:pointer;">Наличие ДМС: <input name="contractDMS" value="да" size="1" type="checkbox"></label>
		</div>
		<div class="uk-form-row uk-margin-small-top">
		  <label style="cursor:pointer;">Новый ПК: <input name="needPC" value="1" size="1" type="checkbox"></label>
		</div>
		<p>Фото сотрудника: <input name="file" value="" type="file"></p>
		<h4>Создать заявки</h4>
		<div class="uk-form-row">
		  <label style="cursor:pointer;"><input name="placeApplicationPassIT" value="1" size="1" type="checkbox"> - создать заявку на пропуск в ИТ</label>
		</div>
		<div class="uk-form-row uk-margin-small-top">
		  <label style="cursor:pointer;"><input name="placeApplicationIT" value="1" size="1" type="checkbox"> - создать заявку на место в ИТ</label>
		</div>
		<div class="uk-form-row uk-margin-small-top">
		  <label style="cursor:pointer;"><input name="placeApplicationAHO" value="1" size="1" type="checkbox"> - создать заявку на место в АХО</label>
		</div>
		<p class="uk-text-muted uk-margin-small-top">Заявка на Выход в HR - создается по умолчанию.</p>
		<p style="margin-top: 30px;"><input class="uk-button" id="employees_add" value="Создать сотрудника" type="submit"></p>
		<input name="company" value="' . $company_name . '" type="hidden">
		<input name="company_email" value="' . $company_email . '" type="hidden">
		<input name="head_office" value="' . $head_office . '" type="hidden">
	</form>
	<p class="zvezda"><span class="red">* - поля обязательные для заполнения.</span></p>
	<script>
		$(function(){
			$("#employees_data #mobile").mask("+79999999999");
			$("#employees_data #date_employment").mask("99.99.9999");
			$("#employees_data #date_probationary_period").mask("99.99.9999");
			$("#employees_data #date_birth").mask("99.99.9999");
		});
		$("#vneShtataInput").change(function() {
      $("#handwritten_post_p").toggleClass("db");
      $(".skritie_post").toggleClass("dn");
    });
		$("#dogovor").change(function() {
		  let selectEl = $( "#dogovor" ).val();

		  if(selectEl == "gph" || selectEl == "du"){
		    $("#dataIsp").hide();
		    $("#stavka_fte").hide();
		    $("#vneShtata").show();
		  }
		  if(selectEl == "td" || selectEl == "none"){
		    $("#dataIsp").show();
		    $("#stavka_fte").show();
		    $("#vneShtata").hide();
		  }
    });
	</script>';

  echo $out;
}else{
  $out .= '<h3>Свободных должностей нет</h3>
            <p>Для добавления должности создайте заявку в отдел Web-разработки</p><br><br><br>';

  $out .= '
  <h3>Введите данные сотрудника</h3>
	<form class="uk-form" data-uk-form-select>
	  <input name="dep_dn" value="'.$_POST['dep_dn'].'" type="hidden">
		<input name="dep_direct_email" value="'.$email_plus.'" type="hidden">
    <input name="department" value="'.$office_name.'" type="hidden">';

  $out .= '
    <p><span class="red">Тип трудового договора:</span></p>
		<p class="tip_trudov">
		  <select id="dogovor" name="ttp">
			  <option value="none">---</option>
			  <option value="gph">ГПХ</option>
			  <option value="du">Договор об оказании услуг</option>
		  </select>&nbsp;&nbsp;<span class="red">*</span>
		</p>
		<p id="vneShtata">Вне штата&nbsp;&nbsp;<input id="vneShtataInput" name="vneShtataGalka" value="1" size="40" type="checkbox" checked disabled></p>
		<div class="skritie_post" style="display:none">
		  <p><span class="red">Выберите должность:</span></p>
      <p><select id="position_select" name="post">
      <option value="none" required>---</option>';

  $out .= '</select>&nbsp;&nbsp;<span class="red">*</span></p></div>';

  $out .= '
    <div id="handwritten_post_p_double">
      <p><span class="red">Введите название должности:</span></p>
      <p>
        <input name="handwritten_post" placeholder="Введите название должности" value="" size="40" type="text">&nbsp;&nbsp;<span class="red">*</span>
      </p>
      <p>
        <input name="handwritten_cfo" placeholder="Введите ФС" value="" size="40" type="text">&nbsp;&nbsp;<span class="red">*</span>
      </p>
    </div>
    <p><input name="family_name" placeholder="Фамилия" value="" size="40" type="text">&nbsp;&nbsp;<span class="red">*</span></p>
    <p><input name="first_name" placeholder="Имя" value="" size="40" type="text">&nbsp;&nbsp;<span class="red">*</span></p>
		<p><input name="patronymic" placeholder="Отчество" value="" size="40" type="text"></p>';

  $out .= '
		<p id="stavka_fte">Ставка (FTE)&nbsp;&nbsp;<input id="change_fte" name="fte" placeholder="Ставка (FTE)" value="1" size="3" type="text">&nbsp;&nbsp;<span class="red">*</span></p>
		<p>Выберите город:</p>
		<p>
		  <select id="city" name="l">
        <option value="none" required>---</option>';

  foreach ($city as $key => $value) {
    $out .= '<option value="' . $value .'">' . $value . '</option>';
  }

  $out .= '</select>&nbsp;&nbsp;<span class="red">*</span></p>
		<p><input id="city_number" name="telephoneCityNumber" placeholder="Номер городского телефона" value="" size="40" type="text"></p>
		<p><input name="telephoneNumber" placeholder="Номер внутреннего телефона" value="" size="40" type="text"></p>
		<p><input id="mobile" name="mobile" placeholder="Номер мобильного телефона" value="" size="40" type="text"></p>
		<p><input name="physicalDeliveryOfficeName" placeholder="Кабинет" value="" size="40" type="text"></p>
	  <p><input 
	              id="date_employment" 
	              name="extensionAttribute1" 
	              placeholder="Дата выхода" 
	              value="" size="40" 
	              type="text" 
	              data-uk-datepicker="{format:\'DD.MM.YYYY\'}" 
	              required>&nbsp;&nbsp;<span class="red">*</span></p>
    <p id="dataIsp"><input 
                id="date_probationary_period" 
                name="extensionAttribute10" 
                placeholder="Дата окончания испытательного срока" 
                value="" 
                size="40" 
                type="text" 
                data-uk-datepicker="{format:\'DD.MM.YYYY\'}" 
                required>&nbsp;&nbsp;<span class="red">*</span></p>
		<p><input 
		            id="date_birth" 
		            name="extensionAttribute2" 
		            placeholder="Дата рождения" 
		            value="" 
		            size="40" 
		            type="text" 
		            data-uk-datepicker="{format:\'DD.MM.YYYY\'}">&nbsp;&nbsp;<span class="red">*</span></p>
		<p><input name="snils" placeholder="СНИЛС" value="" size="40" type="text"></p>
    <p>Скрыть на портале: <input id="combi" name="hide_in_ad" value="1" size="40" type="checkbox"></p>';

  $out .= '
    <div id="office_select">
      <p><span class="red">Выберите отдел для отображения на портале:</span></p>
      <p><select name="office_in_ad">
        <option value="none">---</option>';

  $out .= build_tree($connect);

  $out .= '</select>&nbsp;&nbsp;<span class="red">*</span></p></div>';

  $out .= '
    <div id="vibor_rukovoditelya">
      <p><span class="red">Выберите руководителя:</span></p>
      <p><select name="v_r">
        <option value="none">---</option>';

  $query_ruk = "SELECT full_name FROM employee_data WHERE date_dismissal = '0000-00-00' AND dismissed = 'n' ORDER BY full_name";
  $result_ruk = mysqli_query($link, $query_ruk);
  while ($row = mysqli_fetch_assoc($result_ruk)) {
    $out .= '<option value="' . $row['full_name'] . '">' . $row['full_name'] . '</option>';
  }

  $out .= '</select>&nbsp;&nbsp;<span class="red">*</span></p></div>';

  $out .= '
		<p id="rukovoditel">Руководитель: <input name="extensionAttribute3" value="1" size="40" type="checkbox"></p>
		<div class="uk-form-row" style="margin-top: 25px;">
		  <label style="cursor:pointer;">Наличие ДМС: <input name="contractDMS" value="да" size="1" type="checkbox"></label>
		</div>
		<div class="uk-form-row uk-margin-small-top">
		  <label style="cursor:pointer;">Новый ПК: <input name="needPC" value="1" size="1" type="checkbox"></label>
		</div>
		<p>Фото сотрудника: <input name="file" value="" type="file"></p>
		<h4>Создать заявки</h4>
		<div class="uk-form-row">
		  <label style="cursor:pointer;"><input name="placeApplicationPassIT" value="1" size="1" type="checkbox"> - создать заявку на пропуск в ИТ</label>
		</div>
		<div class="uk-form-row uk-margin-small-top">
		  <label style="cursor:pointer;"><input name="placeApplicationIT" value="1" size="1" type="checkbox"> - создать заявку на место в ИТ</label>
		</div>
		<div class="uk-form-row uk-margin-small-top">
		  <label style="cursor:pointer;"><input name="placeApplicationAHO" value="1" size="1" type="checkbox"> - создать заявку на место в АХО</label>
		</div>
		<p class="uk-text-muted uk-margin-small-top">Заявка на Выход в HR - создается по умолчанию.</p>
		<p style="margin-top: 30px;"><input class="uk-button" id="employees_add" value="Создать сотрудника" type="submit"></p>
		<input name="company" value="' . $company_name . '" type="hidden">
		<input name="company_email" value="' . $company_email . '" type="hidden">
		<input name="head_office" value="' . $head_office . '" type="hidden">
	</form>
	<p class="zvezda"><span class="red">* - поля обязательные для заполнения.</span></p>
	<script>
		$(function(){
			$("#employees_data #mobile").mask("+79999999999");
			$("#employees_data #date_employment").mask("99.99.9999");
			$("#employees_data #date_probationary_period").mask("99.99.9999");
			$("#employees_data #date_birth").mask("99.99.9999");
		});
		$("#vneShtataInput").change(function() {
      $("#handwritten_post_p").toggleClass("db");
      $(".skritie_post").toggleClass("dn");
    });
		$("#dogovor").change(function() {
		  let selectEl = $( "#dogovor" ).val();

		  if(selectEl == "gph" || selectEl == "du"){
		    $("#dataIsp").hide();
		    $("#stavka_fte").hide();
		    $("#vneShtata").show();
		  }
		  if(selectEl == "td" || selectEl == "none"){
		    $("#dataIsp").show();
		    $("#stavka_fte").show();
		    $("#vneShtata").hide();
		  }
    });
	</script>';

  echo $out;
}



?>