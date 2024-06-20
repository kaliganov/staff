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

$employees_list = '<h3>Список сотрудников</h3>';

$employees_list .= '<!--<button class="uk-button" id="employees_sortable">Сохранить сортировку</button><br><br>-->
<button class="uk-button" id="employees_add_form">Добавить сотрудника</button>
<ul class="uk-sortable uk-grid uk-grid-small uk-grid-width-1-1" data-uk-sortable="" style="margin-right: 20px;">';


$query = "SELECT id, full_name, guide, sorting, date_release_decree, date_withdrawal_decree, date_dismissal, hide_in_ad from employee_data where id_company = '" . $id_company . "' and id_department = '" . $id_department . "' and id_division = '" . $id_division . "' and id_office = '" . $id_office . "' ORDER BY guide DESC";
$result = mysqli_query($link, $query);

//$sorting_array = array();
while ($row = mysqli_fetch_assoc($result)) {
  if($row["date_dismissal"] == "0000-00-00"){
    $new_class = "";
    if($row["hide_in_ad"] == "1"){
      $new_class = "hide_in_ad";
    }

    if($row["date_release_decree"] != "0000-00-00"){
      $now_date = time();
      $date_withdrawal_decree = $row["date_withdrawal_decree"];
      if($date_withdrawal_decree != "0000-00-00"){
        $date_withdrawal_decree_time = strtotime($date_withdrawal_decree);
        if($now_date < $date_withdrawal_decree_time){
          $new_class = "decree";
        }
      }else{
        $new_class = "decree";
      }
    }

    $employees_list .= '<li class="uk-grid-margin" data-dn="'.$row["id"].'"><div class="uk-panel uk-panel-box ' . $new_class . '"><span data-dn="'.$row["id"].'">' . $row["full_name"] . '</span></div></li>';
    //$sorting_array[] = $row;
  } //END if($row["date_dismissal"] == "0000-00-00")

  if($row["date_dismissal"] != "0000-00-00"){
    $now_date = time();
    $date_dismissal = $row["date_dismissal"];
    $date_dismissal_time = strtotime($date_dismissal);

    if($date_dismissal_time > $now_date){
      $employees_list .= '<li class="uk-grid-margin" data-dn="'.$row["id"].'"><div class="uk-panel uk-panel-box"><span data-dn="'.$row["id"].'">' . $row["full_name"] . '</span></div></li>';
    }
  }
}

$employees_list .= '</ul>';


echo $employees_list;

	
?>