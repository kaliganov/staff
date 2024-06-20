<?

require_once 'connect.php';
require_once ("../functions.php");

$link = db_connect();
$name = $_POST['name'];
$today = date("Y-m-d");

$query = "SELECT `id`, `full_name` FROM `employee_data` WHERE `full_name` LIKE '%$name%' AND (date_dismissal = '0000-00-00' OR date_dismissal >= '$today')";
$result = mysqli_query($link, $query);

$array_id = array();
while ($row = mysqli_fetch_assoc($result)) {
  $array_id[$row["id"]] = $row["full_name"];
}

$out = "";
foreach ($array_id as $key => $value) {
  $out .= '
  <li class="uk-grid-margin" data-dn="' . $key . '">
    <div class="uk-panel uk-panel-box">
      <span data-dn="' . $key . '">' . $value . '</span>
    </div>
  </li>
  <br>';
}

if($out) {
  echo '
<h3>Результаты поиска</h3>
<ul class="uk-sortable uk-grid uk-grid-small uk-grid-width-1-1" data-uk-sortable="" style="margin-right: 20px;">
' . $out . '
</ul>';
}

?>