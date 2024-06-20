<?php

function hesk_input($in,$error=0) {

    $in = trim($in);

    if (strlen($in))
    {
        $in = htmlspecialchars($in);
        $in = preg_replace('/&amp;(\#[0-9]+;)/','&$1',$in);
    }
    elseif ($error)
    {
        hesk_error($error);
    }

    if (!ini_get('magic_quotes_gpc'))
    {
        if (!is_array($in))
            $in = addslashes($in);
        else
            $in = hesk_slashArray($in);
    }

    return $in;

}



function db_connect(){
	
	define ('MYSQL_SERVER', '');
	define ('MYSQL_USER', '');
	define ('MYSQL_PASSWORD', '');
	define ('MYSQL_DB', '');

	define ('PATHSITE', '');   //Относительный путь до катаога со скриптом

    $link = mysqli_connect(MYSQL_SERVER, MYSQL_USER, MYSQL_PASSWORD, MYSQL_DB) or die ("Error:".mysqli_error($link));
    
    if (!mysqli_set_charset($link, "utf8"))
        {
            printf("Error: ".mysqli_error($link)); 
        }
    return $link;
}



class AuthClass {
	
    public function loginIn($login, $password, $member, $link ) {
            if(eregi("^$SERVER_ROOT",$HTTP_REFERER)){	
	           if(empty($login)){ 
                    $errarr[] = "Вы не выбрали пользователя";
                }elseif(empty($password)){ 
                    $errarr[] = "Вы не ввели пароль"; 
                }else{
					
					$query= "SELECT * FROM admin_users WHERE samaccountname='$login'";
					$result = mysqli_query($link, $query);
					if($result->num_rows != 0) {
						$ldap_host = "0.0.0.0";
						$ldap_port = "389";
						$connect = ldap_connect($ldap_host, $ldap_port);
						$bind = ldap_bind($connect, $login.'@mega-f.ru', $password);
						if($bind){
						   $line = mysqli_fetch_assoc($result);
						   $_SESSION["admin_is_auth"] = true;
						   $_SESSION['admin_session_user'] =  $line['id'];
						   $_SESSION['admin_session_user_name'] = $line['first_name'];
						   $_SESSION['admin_session_user_second_name'] = $line['second_name'];
						   $_SESSION['admin_session_user_email'] = $line['email'];
						   $_SESSION['access_level'] = $line['access_level'];
						   return $errarr;
						}
						else {
							$errarr[] = "Неверный пароль, попробуйте еще раз.";
							return $errarr;
						}
					}
					else {
						return $errarr[] = "Данного пользователя нет в системе.";
					}
					
	           }
            }
        }
	
	/*
	// Авторизация без AD (под удаление)
	
    public function loginIn($login, $password, $member, $link ) {
            if(eregi("^$SERVER_ROOT",$HTTP_REFERER)){	
	           if(empty($login)){ 
                    $errarr[] = "Вы не ввели логин";
                }elseif(!preg_match("/[-a-zA-Z0-9]{3,30}/", $login)){ 
                    $errarr[] = "Вы неправильно ввели логин";
                }elseif(empty($password)){ 
                    $errarr[] = "Вы не ввели пароль"; 
                }elseif(!preg_match("/[-a-zA-Z0-9]{3,30}/", $password)){ 
                   $errarr[] = "Вы неправильно ввели пароль";      
                }else{
					$password = md5(hesk_input($password));
                    $query= "SELECT * FROM admin_users WHERE email='$login' AND pass='$password'";
                     $result = mysqli_query($link, $query);
                    if(!$result) die (mysqli_error($link));  
                    $row = mysqli_num_rows($result);
    	           if($row != 1){ 
	                   $errarr[] = "Неправильный логин или пароль!";
                       $_SESSION["admin_is_auth"] = false;
                       return $errarr; 
		              }else{
                       //echo "Вы успешно авторизовались!";
                       $line = mysqli_fetch_assoc($result);
                       $_SESSION["admin_is_auth"] = true;
                       $_SESSION['admin_session_user'] =  $line['id'];
                       $_SESSION['admin_session_user_name'] = $line['first_name'];
                       $_SESSION['admin_session_user_second_name'] = $line['second_name'];
                       $_SESSION['admin_session_user_email'] = $line['email'];
					   $_SESSION['access_level'] = $line['access_level'];
                       return $errarr;
		          }
	           }
            }
        }
	*/
		
    public function isAuth() {
        if (isset($_SESSION["admin_is_auth"])) { //Если сессия существует
            return $_SESSION["admin_is_auth"]; //Возвращаем значение переменной сессии is_auth (хранит true если авторизован, false если не авторизован)
        }
        else return false; //Пользователь не авторизован, т.к. переменная is_auth не создана
    }

    /* Метод возвращает логин авторизованного пользователя */
    public function getLogin() {
        if ($this->isAuth()) { //Если пользователь авторизован
            return $_SESSION["admin_session_user_second_name"].' '.$_SESSION["admin_session_user_name"]; //Возвращаем логин, который записан в сессию
        }
    }

    public function out() {
		if($_COOKIE['admin_remember']) {
			setcookie("remember", 'remove', time()-1, "/", "", false, true); // Удаление куки "Запомнить меня"
		}
        $_SESSION = array(); //Очищаем сессию
        session_destroy(); //Уничтожаем
		
		header ("location: index.php");
    }
	
}
	
?>