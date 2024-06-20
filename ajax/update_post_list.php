<?php
ob_start();
require_once 'connect.php';

session_start();
error_reporting(NULL);

require_once ("../functions.php");

$link = db_connect();

$old_dep_dn = $_POST['old_dep_dn'];
$empl_email = $_POST['empl_email'];
$full_name = $_POST['full_name'];
$id_sotrudnika = $_POST['id_sotrudnika'];
$way_in_ad = $_POST['way_in_ad'];
$post = $_POST['post'];
$login = $_POST['login'];
$dep_dn = explode(",", $_POST['dep_dn']);

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

$proverka = '';
$final_array_dolgnosti = array();
foreach ($inter_array_sotrudniki as $key => $value) {
  foreach ($array_dolgnosti as $key_2 => $value_2){
    if($value == $key_2) {
      $array_dolgnosti[$key_2] = (int) $array_dolgnosti[$key_2] - 1;
    }
  }
}


$flag = false;

foreach ($array_dolgnosti as $key => $value) {
  if($value >= 1) {
    //Работаем
    $flag = true;
  }
}

$query_email = "SELECT email FROM employee_data 
WHERE id_company = '" . $id_company . "' 
AND id_department = '" . $id_department . "' 
AND guide = 1";
$result_email = mysqli_query($link, $query_email);

while ($row = mysqli_fetch_assoc($result_email)) {
  $email_plus = $row['email'];
}

if($flag){
  $out .= '
  <h5>Выберите должность</h5>
	<form id="update_post" class="uk-form" data-uk-form-select>
	  <input name="dep_dn" value="'.$_POST['dep_dn'].'" type="hidden">
		<input name="dep_direct_email" value="'.$email_plus.'" type="hidden">
    <input name="department" value="'.$office_name.'" type="hidden">
    <p><select id="position_select" name="post">
          <option value="none" required>---</option>';

  foreach ($array_dolgnosti as $key => $value) {
    if ($value >= 1) {
      $query_mvz = "SELECT cfo FROM position_parallel 
                    WHERE id_company = '" . $id_company . "' 
                    AND id_department = '" . $id_department . "' 
                    AND id_division = '" . $id_division . "' 
                    AND id_office = '" . $id_office . "'
                    AND pos_name = '" . $key . "'";
      $result_mvz = mysqli_query($link, $query_mvz);

      while ($row = mysqli_fetch_assoc($result_mvz)) {
        $cfo = $row['cfo'];
      }
      $out .= '<option value="' . $key .', ' . $cfo . '">' . $key . ' - ' . $value . 'шт.</option>';
    }
  }

  $out .= '
    </select>
  </p>
  <h5>Введите дату смены должности</h5>
  <p>
    <input id="input_update" type="text" name="update_date_change_position" value="' . $release_date_dismissal . '" size="40" data-uk-datepicker="{format:\'DD.MM.YYYY\'}"/>
  </p>
  <input name="empl_email" type="hidden" value="' . $empl_email . '">
  <input name="old_dep_dn" type="hidden" value="' . $old_dep_dn . '">
  <input name="full_name" type="hidden" value="' . $full_name . '">
  <input name="id_sotrudnika" type="hidden" value="' . $id_sotrudnika . '">
  <input name="office_in_ad" type="hidden" value="' . $way_in_ad . '">
  <input name="post" type="hidden" value="' . $post . '">
  <input name="login" type="hidden" value="' . $login . '">
  <div class="uk-form-row">
		 <label style="cursor:pointer;"><input name="dontSeePortal" value="1" size="1" type="checkbox"> - Не отображать на портале</label>
	</div>
  <div class="uk-form-row">
		 <label style="cursor:pointer;"><input name="needPCTransfer" value="1" size="1" type="checkbox"> - Новый ПК (уведомление в письме рассылки)</label>
	</div>
  <h5>Создать заявки</h5>
	<div class="uk-form-row uk-margin-small-top">
		 <label style="cursor:pointer;"><input name="placeApplicationPassITTransfer" value="1" size="1" type="checkbox"> - создать заявку на пропуск в ИТ</label>
	</div>
	<div class="uk-form-row uk-margin-small-top">
		 <label style="cursor:pointer;"><input name="placeApplicationITTransfer" value="1" size="1" type="checkbox"> - создать заявку на место в ИТ</label>
	</div>
	<div class="uk-form-row uk-margin-small-top">
		 <label style="cursor:pointer;"><input name="placeApplicationAHOTransfer" value="1" size="1" type="checkbox"> - создать заявку на место в АХО</label>
	</div>
</div>
<br>

<input id="update_departments_post" class="uk-button uk-margin-small-top" type="submit" value="Перевести">
	<script>
		$("#combi").change(function() {
      //console.log("working");
      $("#office_select").toggleClass("dn");
    });
		$("#vneShtataInput").change(function() {
      //console.log("working");
      $("#handwritten_post_p").toggleClass("db");
      $(".skritie_post").toggleClass("dn");
    });
		$("#dogovor").change(function() {
		  let selectEl = $( "#dogovor" ).val();
		  //console.log(selectEl);
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
            <p>Для добавления должности создайте заявку в отдел Web-разработки</p>';

  echo $out;
}



?>