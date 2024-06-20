<?php

ob_start();
require_once ("../functions.php");
$auth = new AuthClass();
session_start();

if ($auth->isAuth()) {
	echo '1';
}
else {
	echo '0';
}

?>