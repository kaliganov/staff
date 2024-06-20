<?php

require_once '../ajax/connect.php';
require_once ("../functions.php");
$link = db_connect();



$ou = 'HR-департамент';
$base_dn = 'OU=Сотрудники,OU=MEGA-F,DC=mega-f,DC=ru';
$specific_dn = $base_dn;
if ('' != $ou) {$specific_dn = 'OU='.$ou.','.$base_dn;}
$filter = '(|(cn=*)(givenname=*)(ou=*))';
$justthese = array("ou", "cn", "givenname", "mail");
$sr = ldap_list($connect, $specific_dn, $filter, $justthese);
$info = ldap_get_entries($connect, $sr);

echo '<h2>$info</h2>';
echo '<pre>';
var_dump($info);
echo '</pre>';
echo '<br><br><br>';




?>