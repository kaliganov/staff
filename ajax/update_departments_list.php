<?

require_once 'connect.php';

require_once ("../functions.php");
$link = db_connect();

$id_company = $_POST['id_company'];
$id_deparment = 0;
$id_division = 0;
$id_office = 0;

$dep_array = array();
$query = "SELECT * from department where id_company = '" . $id_company . "'";
$result = mysqli_query($link, $query);

$out = '
<h5>Выберите Департамент</h5>
<form id="update_departments" class="uk-form uk-form-stacked" method="post">
  <fieldset>
    <div class="uk-form-row">
      <select name="department" onchange="update_post_list()">
        <option value="none">---</option>';

while ($row = mysqli_fetch_assoc($result)) {
  $id_deparment = $row['id'];
  $id_division = 0;
  $id_office = 0;
  $out .= '<option value="'.$id_company.', '.$id_deparment.', '.$id_division.', '.$id_office.'">'.$row['de_name'].'</option>';

  $query_div = "SELECT * from division where id_department = '" . $row['id'] . "'";
  $result_div = mysqli_query($link, $query_div);
  while ($row_div = mysqli_fetch_assoc($result_div)) {

    if($row_div['id_department'] == $row['id']){
      $id_division = $row_div['id'];
      $out .= '<option value="'.$id_company.', '.$id_deparment.', '.$id_division.', '.$id_office.'">&#8212; '.$row_div['div_name'].'</option>';
      $query_off = "SELECT * from office where id_division = '" . $row_div['id'] . "'";
      $result_off = mysqli_query($link, $query_off);

      while ($row_off = mysqli_fetch_assoc($result_off)) {
        if($row_off['id_division'] == $row_div['id']) {
          $id_office = $row_off['id'];
          $out .= '<option value="'.$id_company.', '.$id_deparment.', '.$id_division.', '.$id_office.'">&#8212;&#8212; '.$row_off['off_name'].'</option>';
          $id_office = 0;
        }
      }
      $id_division = 0;
    }
  }


  $query_off_2 = "SELECT * from office where id_department = '" . $row['id'] . "'";
  $result_off_2 = mysqli_query($link, $query_off_2);
  while ($row_off_2 = mysqli_fetch_assoc($result_off_2)) {
    if($row_off_2['id_department'] == $row['id']) {
      $id_office = $row_off_2['id'];
      $out .= '<option value="'.$id_company.', '.$id_deparment.', '.$id_division.', '.$id_office.'">&#8212; '.$row_off_2['off_name'].'</option>';
      $id_office = 0;
    }
  }
}
$out .= '</select><!--<br><br><input class="uk-button" type="submit" value="Вывести">--></div></fieldset></form>
<div id="update_post"></div>';

echo $out;
	
?>