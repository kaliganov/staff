<?

require_once 'connect.php';
require_once ("../functions.php");
$link = db_connect();

$post_name = $_POST['post_name'];
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

$query = "INSERT INTO position_parallel (pos_name, id_company, id_department, id_division, id_office, quantity) 
VALUES ('".$post_name."','".$id_company."','".$id_department."','".$id_division."','".$id_office."','1')";
$result = mysqli_query($link, $query);

?>