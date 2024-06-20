<?

require_once 'connect.php';
require_once ("../functions.php");
$link = db_connect();
$id_company = $_POST['id_company'];


if($id_company == 5){
  $query_off = "SELECT * from office where id_company = 5";
  $result_off   = mysqli_query($link, $query_off);

  $out = '
    <h3>Выберите Департамент</h3>
    <form class="uk-form uk-form-stacked" method="post">
      <fieldset>
        <div class="uk-form-row">
          <select name="department" onchange="employees_list()">
            <option value="none">---</option>';

  while ($row_off = mysqli_fetch_assoc($result_off)) {
    $out .= '<option value="' . $id_company . ', ' . $row_off['id_department'] . ', ' . $row_off['id_division'] . ', ' . $row_off['id'] . '">' . $row_off['off_name'] . '</option>';
  }

  $out .= '
          </select>
        </div>
      </fieldset>
     </form>';

  echo $out;

  exit;
}


$query = "SELECT * FROM department WHERE id_company = '" . $id_company . "' ORDER BY de_name";
$result = mysqli_query($link, $query);

$out = '
<h3>Выберите Департамент</h3>
<form class="uk-form uk-form-stacked" method="post">
  <fieldset>
    <div class="uk-form-row">
      <select name="department" onchange="employees_list()">
        <option value="none">---</option>';

while ($row = mysqli_fetch_assoc($result)) {
  if($row["active"] == 1){
    $out .= '<option value="' . $id_company . ', ' . $row['id'] . ', 0, 0">' . $row['de_name'] . '</option>';
  }
  if($row["active"] == 0){
    $out .= '<option value="' . $id_company . ', ' . $row['id'] . ', 0, 0">***' . $row['de_name'] . '***</option>';
  }

  $query_div = "SELECT * from division where id_company = '" . $id_company . "' AND id_department = '" . $row['id'] . "'";
  $result_div = mysqli_query($link, $query_div);

  if($result_div->num_rows != 0){
    while ($row_div = mysqli_fetch_assoc($result_div)) {
      $out .= '<option value="' . $id_company . ', ' . $row_div['id_department'] . ', ' . $row_div['id'] . ', 0">&#8212;  ' . $row_div['div_name'] . '</option>';

      $query_off = "SELECT * from office where id_company = '" . $id_company . "' AND id_department = '" . $row['id'] . "' AND id_division = '" . $row_div['id'] . "'";
      $result_off = mysqli_query($link, $query_off);

      if($result_off->num_rows != 0){
        while ($row_off = mysqli_fetch_assoc($result_off)) {
          $out .= '<option value="' . $id_company . ', ' . $row_off['id_department'] . ', ' . $row_off['id_division'] . ', ' . $row_off['id'] . '">&#8212; &#8212;  ' . $row_off['off_name'] . '</option>';
        }
      }
    }
  }
  $query_off = "SELECT * from office where id_company = '" . $id_company . "' AND id_department = '" . $row['id'] . "' AND id_division = '0'";
  $result_off = mysqli_query($link, $query_off);

  if($result_off->num_rows != 0){
    while ($row_off = mysqli_fetch_assoc($result_off)) {
      if($row_off["active"] == 1){
        $out .= '<option value="' . $id_company . ', ' . $row_off['id_department'] . ', ' . $row_off['id_division'] . ', ' . $row_off['id'] . '">&#8212; ' . $row_off['off_name'] . '</option>';
      }
      if($row_off["active"] == 0){
        $out .= '<option value="' . $id_company . ', ' . $row_off['id_department'] . ', ' . $row_off['id_division'] . ', ' . $row_off['id'] . '">&#8212; ***' . $row_off['off_name'] . '***</option>';
      }
    }
  }
}

$out .= '</select><!--<br><br><input class="uk-button" type="submit" value="Вывести">--></div></fieldset></form>';

echo $out;


?>