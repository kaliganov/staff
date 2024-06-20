<?

require_once 'connect.php';
require_once ("../functions.php");
$link = db_connect();
$query = "SELECT * from company";
$res = mysqli_query($link, $query);
	

$result = '
<h3>Выберите компанию</h3>
<form class="uk-form uk-form-stacked" method="post">
  <fieldset>
    <div class="uk-form-row">
      <select name="company" onchange="departments_list()">
        <option value="none">---</option>';


while($row = mysqli_fetch_assoc($res)) {
	$result .= '<option value="'.$row['id'].'">'.$row['company_name'].'</option>';
}
$result .= '</select><!--<br><br><input class="uk-button" type="submit" value="Вывести">--></div></fieldset></form><br>';

echo $result;

function build_tree($connect, $ou = '', $base_dn = 'OU=Сотрудники,OU=MEGA-F,DC=mega-f,DC=ru')
{
	$specific_dn = $base_dn;
	if ('' != $ou) {$specific_dn = 'OU='.$ou.','.$base_dn;}
	$filter = '(|(cn=*)(givenname=*)(ou=*))';
	$justthese = array("ou", "cn", "givenname", "mail");
	$sr = ldap_list($connect, $specific_dn, $filter, $justthese);
	$info = ldap_get_entries($connect, $sr);
	
	for ($i=0; $i < $info["count"]; $i++) 
	{
		
		$specific_ou = $info[$i]["ou"][0];

		if ($specific_ou  != '')
		{
			$result .= '<option '.$selected.' value="OU='.$specific_ou.','.$specific_dn.'">'.depth($specific_dn).' '.$specific_ou.'</option>';
			$result .= build_tree($connect, $specific_ou, $specific_dn);
		}
	}
		
	return $result;
}


function depth($specific_dn) {
	$specific_dn_array = explode(',', $specific_dn);
	$qty = count($specific_dn_array);
	
	for($i=0; $i<$qty-3; $i++) {
		$depth .= '—';
	}
	
	return $depth;
}
	
?>