<?php

require_once ("../functions.php");
$link = db_connect();

$out = '<h3>Введите данные новой вакансии</h3>
	<form class="uk-form" data-uk-form-select>
	<input name="dep_dn" value="'.$_POST['dep_dn'].'" type="hidden">
	<p><select name="title"><option value="" disabled="" selected="">Должность</option>';
	
$query = "SELECT position, comment from position ORDER BY position";
$result = mysqli_query($link, $query);

while($row = mysqli_fetch_assoc($result)) {
	$comment = $row['comment'] != "1С" ?' ('.$row['comment'].')':'';
	$out .= '<option value="'.$row['position'].'">'.$row['position'].$comment.'</option>';
}

$out .= '</select> * &nbsp; <select name="extensionAttribute5"><option value="" dselected="">---</option><option value="РК">РК</option><option value="ОТП">ОТП</option></select></p>
	<p><input name="quantity" placeholder="Количество вакансий" value="" size="40" type="text"></p>
	<p><input name="cfo" placeholder="ЦФО" value="" size="40" type="text"></p>
	<p><input name="salary" placeholder="Предполагаемый оклад" value="" size="40" type="text"></p>
	<p><select name="l"><option value="" disabled="" selected="">Город</option>';

$query = "SELECT city from city";
$result = mysqli_query($link, $query);

while($row = mysqli_fetch_assoc($result)) {
	$out .= '<option value="'.$row['city'].'">'.$row['city'].'</option>';
}

$out .= '</select></p>
	<p><select name="extensionAttribute8">
		<option value="tfnopt">Выберите компанию</option>
		<option value="tfnopt">ТФНопт</option>
		<option value="tfn-trading">ТФН-трейд</option>
		<option value="tfnopt.by">ТФНопт (Беларусь)</option>
		<option value="smartlifesystem.ru">СмартЛайфСистем</option>
	</select></p>
	<p>Руководитель: <input name="extensionAttribute3" value="1" size="40" type="checkbox"></p>
	<p style="margin-top: 30px;"><input class="uk-button" id="vacancy_add" value="Создать вакансию" type="submit"></p>
	</form>
	<p>* - поля обязательные для заполнения.</p>
	';

echo $out;

?>