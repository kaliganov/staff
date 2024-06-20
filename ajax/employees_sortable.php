<?

require_once 'connect.php';

foreach($_POST['data_list'] as $key=>$row) {
	
	$update_arr['extensionAttribute6'] = $key+1;
	$empl_dn = $row;
	
	$result = ldap_modify($connect, $empl_dn, $update_arr);
	
}
	
?>