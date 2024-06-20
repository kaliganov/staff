<?

/* Connection parameters */
	$ldap_host = "0.0.0.0"; # domain controller or ip-address
	$ldap_port = "389"; # port for connection
	$ldap_user ="adadmin@mega-f.ru"; # AD-user
	$ldap_pass = "FbtoJgtRfnYsf666"; # AD-user's password
   
/* Open connection */
	$connect = ldap_connect($ldap_host, $ldap_port);
	ldap_set_option($connect, LDAP_OPT_PROTOCOL_VERSION, 3);
	ldap_set_option($connect, LDAP_OPT_REFERRALS, 0);
	$bind = ldap_bind($connect, $ldap_user, $ldap_pass);
	
?>
