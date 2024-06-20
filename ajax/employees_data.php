<?
error_reporting(0);
require_once 'connect.php';
require_once ("../functions.php");
$link = db_connect();


// -------------------------------------------  KSS  ------------------------------------------- //
// Собираем всю инфу по сотруднику
$id_sotrudnika = $_POST['empl_dn'];

$query_all_data = "SELECT * from employee_data where id = '" . $id_sotrudnika . "'";
$result_all_data = mysqli_query($link, $query_all_data);

$all_data_array_sotrudnika = array();
while ($row_all_data = mysqli_fetch_assoc($result_all_data)) {
  $all_data_array_sotrudnika = $row_all_data;
}

$login = $all_data_array_sotrudnika["login"];
$id_company = (int) $all_data_array_sotrudnika["id_company"];
$id_department = (int) $all_data_array_sotrudnika["id_department"];
$id_division = (int) $all_data_array_sotrudnika["id_division"];
$id_office = (int) $all_data_array_sotrudnika["id_office"];
$full_name = $all_data_array_sotrudnika["full_name"];
$date_employment = $all_data_array_sotrudnika["date_employment"];
$date_probationary_period = $all_data_array_sotrudnika["date_probationary_period"];
$date_release_decree = $all_data_array_sotrudnika["date_release_decree"];
$date_withdrawal_decree = $all_data_array_sotrudnika["date_withdrawal_decree"];
$date_dismissal = $all_data_array_sotrudnika["date_dismissal"];
$way_in_ad = $all_data_array_sotrudnika["way_in_ad"];
$fte = $all_data_array_sotrudnika["fte"];
$city_phone = $all_data_array_sotrudnika["city_phone"];
$phone = $all_data_array_sotrudnika["phone"];
$mobile_phone = $all_data_array_sotrudnika["mobile_phone"];
$office_num = $all_data_array_sotrudnika["office_num"];
$email = $all_data_array_sotrudnika["email"];
$post = $all_data_array_sotrudnika["post"];
$city_phone_number = $all_data_array_sotrudnika["city_phone"];
$city = $all_data_array_sotrudnika["city"];
$snils = $all_data_array_sotrudnika["snils"];
$dmc = $all_data_array_sotrudnika["dmc"];
$type_employment_contract = $all_data_array_sotrudnika["type_employment_contract"];
$guide = $all_data_array_sotrudnika["guide"];
$hide_in_ad = $all_data_array_sotrudnika["hide_in_ad"];


$query_company_name = "SELECT company_name from company where id = '" . $id_company . "'";
$result_company_name = mysqli_query($link, $query_company_name);
while ($row_company_name = mysqli_fetch_assoc($result_company_name)) {
  $company_name = $row_company_name["company_name"];
}

$query_department_name = "SELECT de_name from department where id = '" . $id_department . "'";
$result_department_name = mysqli_query($link, $query_department_name);
while ($row_department_name = mysqli_fetch_assoc($result_department_name)) {
  $department_name = $row_department_name["de_name"];
}

$query_division_name = "SELECT div_name from division where id = '" . $id_division . "'";
$result_division_name = mysqli_query($link, $query_division_name);
while ($row_division_name = mysqli_fetch_assoc($result_division_name)) {
  $division_name = $row_division_name["div_name"];
}

$query_office_name = "SELECT off_name from office where id = '" . $id_office . "'";
$result_office_name = mysqli_query($link, $query_office_name);
while ($row_office_name = mysqli_fetch_assoc($result_office_name)) {
  $office_name = $row_office_name["off_name"];
}

$bread_crumbs = $department_name;
if($id_division > 0){
  $bread_crumbs .= " → " . $division_name;
}
if($id_office > 0) {
  $bread_crumbs .= " → " . $office_name;
}
$old_dep_dn = $bread_crumbs;
// -------------------------------------------  END KSS  ------------------------------------------- //

// Список отделов для "Перенести сотрудника в другой департамент на портале"
function build_tree($connect, $ou = '', $base_dn = 'OU=Сотрудники,OU=MEGA-F,DC=mega-f,DC=ru'){
  $specific_dn = $base_dn;
  if ('' != $ou) {$specific_dn = 'OU='.$ou.','.$base_dn;}
  $filter = '(|(cn=*)(givenname=*)(ou=*))';
  $justthese = array("ou", "cn", "givenname", "mail");
  $sr = ldap_list($connect, $specific_dn, $filter, $justthese);
  $info = ldap_get_entries($connect, $sr);

  for ($i=0; $i < $info["count"]; $i++) {
    $specific_ou = $info[$i]["ou"][0];
    if ($specific_ou  != ''){
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

$result = '
<div id="perenos_v_ad">
<h5>Выберите департамент</h5>
<form id="department_change_form" class="uk-form uk-form-stacked" method="post">
  <fieldset>
    <div class="uk-form-row">
      <select name="new_department">
        <option value="none">---</option>';
$result .= build_tree($connect);

// Старая переменная
$empl_dn = $all_data_array_sotrudnika["way_in_ad"];

$filter = '(&(objectClass=user)(objectCategory=PERSON))';
$sr = ldap_search($connect, $empl_dn, $filter);
$info = ldap_get_entries($connect, $sr);

if($info[0]["extensionattribute3"][0] == 1){
  $checked = 'checked';
}


// Руководитель или нет
if($guide == 1){
  $checked = 'checked';
}

$checked_DMS = '';
if($all_data_array_sotrudnika["dmc"] == "да"){
  $checked_DMS = 'checked';
}

$checked_hide_in_ad = '';
if($all_data_array_sotrudnika["hide_in_ad"] == "1"){
  $checked_hide_in_ad = 'checked';
}

$checked_dismissed = '';
if($all_data_array_sotrudnika["dismissed"] == "y"){
  $checked_dismissed = 'checked';
}

// Форматирование нормальной даты
$release_date_employment = '';
if($all_data_array_sotrudnika["date_employment"] != '0000-00-00'){
  $release_date_employment = date('d.m.Y', strtotime($all_data_array_sotrudnika["date_employment"]));
}

$release_date_probationary_period = '';
if($all_data_array_sotrudnika["date_probationary_period"] != '0000-00-00'){
  $release_date_probationary_period = date('d.m.Y', strtotime($all_data_array_sotrudnika["date_probationary_period"]));
}

$release_date_release_decree = '';
$flag_decree = 'add';
if($all_data_array_sotrudnika["date_release_decree"] != '0000-00-00'){
  $release_date_release_decree = date('d.m.Y', strtotime($all_data_array_sotrudnika["date_release_decree"]));
  $flag_decree = "not_ad";
}

$release_date_withdrawal_decree = '';
if($all_data_array_sotrudnika["date_withdrawal_decree"] != '0000-00-00'){
  $release_date_withdrawal_decree = date('d.m.Y', strtotime($all_data_array_sotrudnika["date_withdrawal_decree"]));
}

$release_date_birth = '';
if($all_data_array_sotrudnika["date_birth"] != '0000-00-00'){
  $release_date_birth = date('d.m.Y', strtotime($all_data_array_sotrudnika["date_birth"]));
}

$release_date_dismissal = '';
if($all_data_array_sotrudnika["date_dismissal"] != '0000-00-00'){
  $release_date_dismissal = date('d.m.Y', strtotime($all_data_array_sotrudnika["date_dismissal"]));
}

/* -------------- Хлебные крошки -------------- */
$employees_data = "";

if($date_release_decree != "0000-00-00"){
  $new_class = "";
  $now_date = time();
  if($date_withdrawal_decree != "0000-00-00"){
    $date_withdrawal_decree_time = strtotime($date_withdrawal_decree);
    if($now_date < $date_withdrawal_decree_time){
      $employees_data .= "<p><b>--- Сотрудник в Декрете ---</b></p>";
    }
  }else{
    $employees_data .= "<p><b>--- Сотрудник в Декрете ---</b></p>";
  }
}

$employees_data .= "<p><b>Подразделение</b><br>" . $bread_crumbs . "</p>";
$bread_crumbs = "";

/* -------------- Имя сотрудника -------------- */

//$employees_data .= '<h2>'.$info[0]["cn"][0].' '.$array_all_param["patronymic"].'</h2>';
$employees_data .= "<h2 id='full_name' data-login='" . $login . "'>" . $full_name . "</h2>";

/* -------------- Вывод изображения сотрудника -------------- */
if($info[0]["thumbnailphoto"][0]) {
  $empl_img = imagecreatefromstring ($info[0]["thumbnailphoto"][0]);
  ImageJPEG ($empl_img, 'empl-img.jpg', 100);
  $employees_data .= '<img src="/ajax/empl-img.jpg?'.rand(0, 9999).'"><br><br>';
}
//$employees_data .= "<img src=''><br><br><br>";

$employees_data .= '
<form id="employees_data_update_form" method="POST" class="uk-form uk-form-stacked" data-uk-form-select>
	<fieldset>
		<input type="hidden" name="id_sotrudnika" value="' . $id_sotrudnika . '" />
		<input type="hidden" name="id_company" value="' . $id_company . '" />
		<input type="hidden" name="dn" value="' . $way_in_ad . '" />
		<div class="uk-form-row">
      <label class="uk-form-label">Город:</label>
		  <select id="city" name="l">
        <option value="none" required>---</option>';

$array_city = array();
$query_city = "SELECT city FROM city";
$result_city = mysqli_query($link, $query_city);
while ($row_city = mysqli_fetch_assoc($result_city)) {
  $array_city[] = $row_city['city'];
}

foreach ($array_city as $key => $value) {
  if($value == $city){
    $employees_data .= '
        <option value="' . $value .'" selected>' . $value . '</option>';
  }else{
    $employees_data .= '
        <option value="' . $value .'">' . $value . '</option>';
  }
}

$employees_data .= '
      </select>
    </div>
		<div class="uk-form-row">
		  <label class="uk-form-label">Тип трудового договора</label>
		  <input type="text" value="' . $type_employment_contract . '" size="40" disabled/>
		</div>
		<div class="uk-form-row">
		  <label class="uk-form-label">Должность</label>
		  <input type="text" name="post" value="' . $post . '" size="40" disabled/>
		</div>
		<div class="uk-form-row">
      <label class="uk-form-label">Ставка (FTE)</label>
      <input type="text" value="' . $fte . '" size="40" disabled/>
    </div>
    <div class="uk-form-row">
      <label class="uk-form-label">Номер городского телефона</label>
      <input id="telephonenumber" type="text" name="citytelephonenumber" value="' . $city_phone_number . '" size="40" />
    </div>
    <div class="uk-form-row">
      <label class="uk-form-label">Внутренний номер телефона</label>
      <input type="text" name="telephonenumber" value="' . $phone . '" size="40" />
    </div>
    <div class="uk-form-row">
      <label class="uk-form-label">Номер мобильного телефона</label>
      <input id="mobile" type="text" name="mobile" value="' . $mobile_phone . '" size="40" />
    </div>
    <div class="uk-form-row">
      <label class="uk-form-label">Кабинет</label>
      <input type="text" name="physicaldeliveryofficename" value="' . $office_num . '" size="40" />
    </div>
    <div class="uk-form-row">
      <label class="uk-form-label">Дата выхода</label>
      <input id="date_employment" type="text" name="extensionAttribute1" value="' . $release_date_employment . '" size="40" data-uk-datepicker="{format:\'DD.MM.YYYY\'}"/>
    </div>';

if($type_employment_contract == "Трудовой договор"){
  $employees_data .= '
    <div class="uk-form-row">
      <label class="uk-form-label">Дата окончания испытательного срока</label>
      <input id="date_probationary_period" type="text" name="date_probationary_period" value="' . $release_date_probationary_period . '" size="40" data-uk-datepicker="{format:\'DD.MM.YYYY\'}"/>
    </div>';
}

$employees_data .= '
    <div class="uk-form-row">
      <label class="uk-form-label">Дата начала декрета</label>
      <input id="date_release_decree" type="text" name="date_release_decree" value="' . $release_date_release_decree . '" size="40" data-uk-datepicker="{format:\'DD.MM.YYYY\'}" data-was="' . $flag_decree . '"/>
    </div>
    <div class="uk-form-row">
      <label class="uk-form-label">Дата окончания декрета</label>
      <input id="date_withdrawal_decree" type="text" name="date_withdrawal_decree" value="' . $release_date_withdrawal_decree . '" size="40" data-uk-datepicker="{format:\'DD.MM.YYYY\'}"/>
    </div>
    <div class="uk-form-row">
      <label class="uk-form-label">Дата рождения</label>
      <input id="date_birth" type="text" name="extensionAttribute2" value="' . $release_date_birth . '" size="40" data-uk-datepicker="{format:\'DD.MM.YYYY\'}" />
    </div>
    <div class="uk-form-row">
      <label class="uk-form-label">СНИЛС</label>
      <input type="text" name="snils" value="' . $snils . '" size="40" />
    </div>
		<div class="uk-form-row"><label class="uk-form-label">Электронная почта</label>
		  <a id="empl_email" href="mailto:' . $email . '">' . $email . '</a>
		</div>
		<div class="uk-form-row">
      <label class="uk-form-label">Компания</label>
      <input type="text" value="' . $company_name . '" size="40" disabled/>
    </div>
    <div class="uk-form-row">
      <label class="uk-form-label">Скрыть на портале</label>
      <input id="hide_perenos" name="hide_in_ad" value="1" type="checkbox" ' . $checked_hide_in_ad . '>
    </div>
    <div class="uk-form-row">
      <label class="uk-form-label">Руководитель</label>
      <input name="extensionAttribute3" value="1" type="checkbox" ' . $checked . '>
    </div>
    <div class="uk-form-row">
      <label class="uk-form-label">Наличие ДМС</label>
      <input name="contractDMS" value="да" type="checkbox" ' . $checked_DMS . '>
    </div>
    <div class="uk-form-row">
      <label class="uk-form-label">Увольняется</label>
      <input name="dismissed" value="y" type="checkbox" ' . $checked_dismissed . '>
    </div>
    <div class="uk-form-row">
      <label class="uk-form-label">Заменить фото</label>
      <input name="file" value="" type="file">
    </div>
    <br><br>
    <input id="employees_data_update" class="uk-button" type="submit" value="Обновить данные"/>
  </fieldset>
</form>
    <hr>
    <div class="uk-form uk-form-stacked">
      <div class="uk-form-row">
        <label class="uk-form-label">Дата увольнения</label>
        <input id="input_delete" type="text" name="date_dismissal" value="' . $release_date_dismissal . '" size="40" data-uk-datepicker="{format:\'DD.MM.YYYY\'}"/>
      </div>
      <br>
      <span id="date_delete" class="uk-button uk-margin-bottom" data-id="' . $id_sotrudnika . '">Изменить дату</span>
      <br>
      <span id="delete" class="uk-button" data-dn="' . $way_in_ad . '" data-id="' . $id_sotrudnika . '" data-department="' . $department_name . '">Уволить сотрудника</span>
    </div>
    <hr>
    <h4>Перевести сотрудника на другую должность в ШР</h4>
    <div id="perenos_sotrudnika">
        </fieldset>
      </form>
    </div>
    <div id="old_dep_dn" class="dn">' . $old_dep_dn . '</div>
    <hr>
    <div id="perenos_sotrudnika_in_ad">
      <h4>Перенести сотрудника в другой департамент на портале</h4>
      ' . $result . '   
            </select>
          </fieldset>
          <p>
           <input id="department_change_button" class="uk-button" type="submit" value="Перенести">
           <input name="dn" type="hidden" value="' . $info[0]["dn"] . '">
           <input name="cn" type="hidden" value="' . $info[0]["cn"][0] . '">
           <input name="id" type="hidden" value="' . $id_sotrudnika . '">
          </p>
        </form>
      </div>
    </div>
    <hr>
    <div>
      <h4>Вывод сотрудника из декрета</h4>
      <form id="withdrawal_decree" class="uk-form uk-form-stacked" method="post">
        <fieldset>
          <p><input name="new_family_name" placeholder="Новая фамилия" value="" size="40" type="text"></p>
          ' . $result . '
            </select>&nbsp;&nbsp;<span class="red">*</span>
          </fieldset>
           <input name="login" type="hidden" value="' . $login . '">
           <input name="full_name" type="hidden" value="' . $full_name . '">
	        <h5>Создать заявки</h5>
          <div class="uk-form-row uk-margin-small-top">
             <label style="cursor:pointer;"><input name="placeApplicationPassITDecree" value="1" size="1" type="checkbox"> - создать заявку на пропуск в ИТ</label>
          </div>
          <div class="uk-form-row uk-margin-small-top">
             <label style="cursor:pointer;"><input name="placeApplicationITDecree" value="1" size="1" type="checkbox"> - создать заявку на место в ИТ</label>
          </div>
          <div class="uk-form-row uk-margin-small-top">
             <label style="cursor:pointer;"><input name="placeApplicationAHODecree" value="1" size="1" type="checkbox"> - создать заявку на место в АХО</label>
          </div>
          <br>
          <input id="withdrawal_decree_button" class="uk-button" type="submit" value="Вывести">
        </fieldset>
      </form>
    </div>
    <br>
<script>
  /*$("#hide_perenos").change(function() {
    //console.log("working");
    $("#perenos_sotrudnika_in_ad").toggleClass("dn");
  });*/

  $(function(){
		$("#employees_data #mobile").mask("+79999999999");
		$("#employees_data #date_employment").mask("99.99.9999");
		$("#employees_data #date_probationary_period").mask("99.99.9999");
		$("#employees_data #date_release_decree").mask("99.99.9999");
		$("#employees_data #date_withdrawal_decree").mask("99.99.9999");
		$("#employees_data #date_birth").mask("99.99.9999");
		$("#employees_data #input_update").mask("99.99.9999");
		//$("#employees_data #telephonenumber").mask("+79999999999");
	});
  
  var data = {"id_company":"' . $id_company . '"};
  function update_departments_list() {
    $.post("ajax/update_departments_list.php", data, function(info) {
      $("#perenos_sotrudnika").html(info);
    }, "html");
  }
  update_departments_list();
</script>';


echo $employees_data;

?>