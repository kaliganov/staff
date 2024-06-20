<?

require_once 'connect.php';
require_once ("../functions.php");
$link = db_connect();

$post_id = $_POST['post_id'];
$post_name = $_POST['post_name'];
$post_quantity = $_POST['post_quantity'];
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

$query = "SELECT * FROM employee_data 
WHERE id_company = '" . $id_company . "' 
AND id_department = '" . $id_department . "' 
AND id_division = '" . $id_division . "' 
AND id_office = '" . $id_office . "' 
AND post = '" . $post_name . "' 
AND dismissed = 'n'";
$result = mysqli_query($link, $query);

if ($post_quantity >= mysqli_num_rows($result)) {
  $query = "UPDATE position_parallel SET quantity = '" . $post_quantity . "' WHERE id = '" . $post_id . "'";
  $result = mysqli_query($link, $query);
  echo 1;
} else {
  echo "<h2>Введеное кол-во меньше чем работающих сотрудников. Введите корректное значение.</h2>";
}

?>