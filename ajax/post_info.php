<?

require_once 'connect.php';
require_once ("../functions.php");
$link = db_connect();

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
$query_pp = "SELECT id, pos_name, quantity FROM position_parallel 
WHERE id_company = '" . $id_company . "' 
AND id_department = '" . $id_department . "' 
AND id_division = '" . $id_division . "' 
AND id_office = '" . $id_office . "'";
$result_pp = mysqli_query($link, $query_pp);

$array_dolgnosti = array();
$count = 0;
while ($row = mysqli_fetch_assoc($result_pp)) {
  //$array_dolgnosti[$row['pos_name']] = $row['quantity'];
  $array_dolgnosti[$count]["pos_name"] = $row['pos_name'];
  $array_dolgnosti[$count]["quantity"] = $row['quantity'];
  $array_dolgnosti[$count]["works"] = 0;
  $array_dolgnosti[$count]["free"] = $row['quantity'];
  $array_dolgnosti[$count]["id"] = $row['id'];
  $count++;
}


// Вычисляем свободные должности для добавления
foreach ($inter_array_sotrudniki as $key => $value) {
  $count = 0;
  foreach ($array_dolgnosti as $key_2 => $value_2){
    //echo $value_2["pos_name"] . "<br>";
    if($value == $value_2["pos_name"]) {
      //$array_dolgnosti[$key_2] = (int) $array_dolgnosti[$key_2] - 1;
      $array_dolgnosti[$count]["works"] = (int) $array_dolgnosti[$count]["works"] + 1;
      $array_dolgnosti[$count]["free"] = (int) $array_dolgnosti[$count]["free"] - 1;
    }
    $count++;
  }
}

$out = "
<table class='uk-table'>
  <tr>
    <th>Должности</th>
    <th>Общее кол-во</th>
    <th>Работающих</th>
    <th>Свободно</th>
  </tr>";

foreach ($array_dolgnosti as $key => $value){
  $out .= "<tr>";
  $out .= "<td>" . $value["pos_name"] . "</td><td>" . $value["quantity"] . "</td><td>" . $value["works"] . "</td><td>" . $value["free"] . "</td>";
  $out .= "</tr>";
}

$out .= "</table>";

$out .= "
<h4>Редактирование кол-ва в должности</h4>
<p>Выберите должность:</p>
<div class='uk-form'>
  <select id='edit_quantity_select' name='edit_quantity'>
    <option value='none'>---</option>
";

foreach ($array_dolgnosti as $key => $value){
  $out .= "<option value='" . $value["id"] . "'>" . $value["pos_name"] . "</option></td>";
}

$out .= "
  </select>
  <p>Введите кол-во:</p>
  <input id='edit_quantity_input'>
  <button id='edit_quantity_button' class='uk-button'>Изменить</button>
</div>
<h4>Добавление должности</h4>
<p>Введите должность:</p>
<div class='uk-form'>
  <input id='add_post_input'>
  <button id='add_post_button' class='uk-button'>Добавить</button>
</div>
<h4>Удаление должности</h4>
<p>Выберите должность:</p>
<div class='uk-form'>
  <select id='delete_post_select' name='delete_post'>
    <option value='none'>---</option>
";

foreach ($array_dolgnosti as $key => $value){
  if ($value["free"] <= 0) continue;
  $out .= "<option value='" . $value["id"] . "'>" . $value["pos_name"] . "</option></td>";
}

$out .= "
  </select>
  <button id='delete_post_button' class='uk-button'>Удалить</button>
</div>";

echo $out;
	
?>