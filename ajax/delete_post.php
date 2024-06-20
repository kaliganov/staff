<?

require_once 'connect.php';
require_once ("../functions.php");
$link = db_connect();

$post_id = $_POST['post_id'];

$query = "DELETE FROM position_parallel WHERE id = '" . $post_id . "'";
$result = mysqli_query($link, $query);

?>