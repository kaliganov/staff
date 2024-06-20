<?php 

if(isset($_POST['login'])){
	
    $errarr = Array();
    $errarr = $auth->loginIn(hesk_input($_POST['login']), hesk_input($_POST['password']), hesk_input($_POST['remember']), $link );
    if(empty($errarr))
	{		
		echo '<script>parent.location.href="";</script>';
    }
	else
	{
        foreach ($errarr as $er)
		{
            echo "<h2 class='text-center'><small>".$er."</small></h2>";
        }   
    }
}   

   $authstring = '
<div class="uk-vertical-align-middle uk-margin-top" style="width: 300px;">
<h2 class="form-signin-heading">Авторизация</h2>
<form class="uk-panel uk-panel-box uk-form" role="form" method="post" action="">
<div class="uk-form-row">
<select name="login" class="uk-width-1-1 uk-form-large">
<option value="" disabled selected>Пользователь</option>';


$query= "SELECT * FROM admin_users ORDER BY sort, second_name";
$result = mysqli_query($link, $query);

while($row = mysqli_fetch_assoc($result)) {
	$authstring .= '<option value="'.$row['samaccountname'].'">'.$row['second_name'].' '.$row['first_name'].'</option>';
}

$authstring .= '</select>
</div>
<div class="uk-form-row">
<input name="password" type="password" class="uk-width-1-1 uk-form-large" placeholder="Пароль" required>
</div>
<div class="uk-form-row">
<button class="uk-width-1-1 uk-button uk-button-primary uk-button-large" type="submit" name="submit" >Войти</button>
</div>
</form>
</div>';

echo $authstring;

?>