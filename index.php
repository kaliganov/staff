<?php
	ob_start();
	require_once ("functions.php");
	$link = db_connect();
	$auth = new AuthClass();
	session_start();
?>

<!DOCTYPE html>
<html lang="en-gb">
<head>
	<title>Система управления данными сотрудников - Версия 1.0</title>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!--<link rel="apple-touch-icon-precomposed" href="images/apple-touch-icon.png">-->
	<link href="/favicon.ico" type="image/x-icon" rel="shortcut icon">
  <link rel="stylesheet" href="css/animate.min.css">
	<link rel="stylesheet" href="css/uikit.min.css">
	<link rel="stylesheet" href="css/components/datepicker.min.css">
	<link rel="stylesheet" href="css/components/sortable.min.css">
  <link rel="stylesheet" href="css/dop.css">
	<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
	<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8/jquery-ui.min.js"></script>
	<link href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css" rel="stylesheet" type="text/css"/>
	<link rel="stylesheet" href="css/preloader.css">
	<script src="js/anime.min.js"></script>
	<script src="js/uikit.min.js"></script>
	<script src="js/core/modal.min.js"></script>
	<script src="js/components/datepicker.min.js"></script>
	<script src="js/components/sortable.min.js"></script>
	<script src="js/jquery.maskedinput.min.js"></script>
</head>
<body>

<?php
if ($auth->isAuth()) {
	if($_GET['logout'] == 1) {
		require_once ("logout.php");
	}
?>

<div class="preloader" style="width:100%; height:100%; position: fixed; background-color: #000; opacity: 0.3; margin-top: -15px; display: none;">
	<div class="demo">
	  <div class="circle">
		<div class="inner"></div>
	  </div>
	  <div class="circle">
		<div class="inner"></div>
	  </div>
	  <div class="circle">
		<div class="inner"></div>
	  </div>
	  <div class="circle">
		<div class="inner"></div>
	  </div>
	  <div class="circle">
		<div class="inner"></div>
	  </div>
	</div>
</div>

<div id="admin_mail" class="uk-hidden"><?echo $_SESSION["admin_session_user_email"]?></div>

<div class="uk-container uk-container-center uk-margin-top uk-margin-large-bottom">
<div class="uk-grid">
<div class="uk-width-1-1">
<div class="uk-grid">
	<div class="uk-width-1-3"><h2>Система управления данными сотрудников (STAFF)</h2></div>
	<div class="uk-width-1-3 uk-text-right"><h2><?echo $auth->getLogin();?></h2></div>
	<div class="uk-width-1-3 uk-text-right"><h2><a href="?logout=1">Выйти</a></h2></div>
</div>
<hr>

<div id="navigation" class="uk-grid">
  <div class="uk-width-1-3">
    <div id="company"></div>
  </div>
  <div id="editing_company" class="uk-width-1-3" style="opacity:0">
    &#xFEFF
    <h3>Изменение структуры ШР</h3>
    <button id="edit_button" class="uk-button">Редактировать</button>
  </div>
	<div class="uk-width-1-3 uk-text-right">
		﻿<h3>Поиск по фамилии сотрудника</h3>
    <form class="uk-form uk-form-stacked uk-text-right" method="post">
      <input id="employees_search_name" style="margin-right: 20px;">
      <button id="employees_search_button" class="uk-button">Найти</button></form><br>
	</div>
  <div id="department"></div>
</div>

<br><hr><br>

<ul id="list"></ul>
<div class="uk-grid">
	<div id="employees" class="uk-width-1-3"></div>
	<div id="employees_data" class="uk-width-2-3" style="border-left: 1px solid #E5E5E5;"></div>
  <div id="edit"></div>
</div>

</div>
</div>
</div>

<?
} else {
	echo '<div class="uk-vertical-align uk-text-center uk-height-1-1">
	<div class="uk-grid">
	<div class="uk-width-1-1">';
	
	require_once ("login.php");
	require_once ("footer.php");
}
?>

<div id="modal-creation-of-applications" class="uk-modal">
	<div class="uk-modal-dialog">
		<a class="uk-modal-close uk-close"></a>
			<div id="modal-content">Content</div>
	</div>
</div>

</body>
</html>

<script>


// Почта администратора (для отправки в create_application.php)
var admin_mail = $("#admin_mail").html();


/* --------------------------- Проверка на активную сессию. Если сессия неактивна, то выводим сообщение и перезагружаем страницу. --------------------------- */

$('#employees, #employees_data, #navigation').on('click', "button, a, span, select, input", function(e) {
  var data = {"data_list":0};
  $.post("ajax/session_check.php", data, function(info) {
    if(info == 0) {
      alert('Время сессии истекло! Необходима авторизация.');
      location.reload();
    }
  }, "html");
});


/* --------------------------- Редактирование структуры ШР --------------------------- */

$('#edit_button').on('click', function(e) {
  e.preventDefault();
  e.stopPropagation();

  $("#employees").empty();
  $("#employees_data").empty();

  console.log("work");
  return;

  var empl_dn = $(this).attr('data-dn');
  var data = {"empl_dn":empl_dn};

  $.post("ajax/employees_data.php", data, function(info) {
    $('#employees_data').html(info);
    $('html, body').animate({scrollTop:0}, 'slow');
  }, "html");
});


/* --------------------------- Редактирование кол-ва в должности --------------------------- */

$('#employees_data').on('click', '#edit_quantity_button', function(e) {
  e.preventDefault();
  e.stopPropagation();

  var dep_dn = $("#department select[name=department]").prop("value");
  var post_id = $("select[name=edit_quantity]").prop("value");
  var post_name = $("select[name=edit_quantity] option:selected").html();
  var post_quantity = $("#edit_quantity_input").prop("value");
  var data = {"dep_dn":dep_dn, "post_id":post_id, "post_name":post_name, "post_quantity":post_quantity};

  if (post_id == "none") {
    alert("Выберите должность");
    return;
  }

  $.post("ajax/edit_post_quantity.php", data, function(info) {
    if (info == 1) {
      employees_list();
    } else {
      $('#modal-content').html(info);

      var modal = UIkit.modal("#modal-creation-of-applications", {center:true});
      modal.show();
    }
  }, "html");
});


/* --------------------------- Добавление должности --------------------------- */

$('#employees_data').on('click', '#add_post_button', function(e) {
  e.preventDefault();
  e.stopPropagation();

  var dep_dn = $("#department select[name=department]").prop("value");
  var post_name = $("#add_post_input").prop("value").trim();
  var data = {"dep_dn":dep_dn, "post_name":post_name};

  if (post_name == '') {
    alert("Введите должность");
    return;
  }

  $.post("ajax/add_post.php", data, function(info) {
    employees_list();
  }, "html");
});


/* --------------------------- Удаление должности --------------------------- */

$('#employees_data').on('click', '#delete_post_button', function(e) {
  e.preventDefault();
  e.stopPropagation();

  var post_id = $("select[name=delete_post]").prop("value");
  var data = {"post_id":post_id};

  if (post_id == "none") {
    alert("Выберите должность");
    return;
  }

  if (confirm("Вы уверены, что хотите удалить должность?")) {
    $.post("ajax/delete_post.php", data, function(info) {
      employees_list();
    }, "html");
  }
});


/* --------------------------- Выводим список компаний. Автоматически при открытии страницы. --------------------------- */

company_list();
var data = {"1":"1"};
function company_list() {
  $.post("ajax/company_list.php", data, function(info) {
    $('#company').html(info);
  }, "html");
}

function departments_list() {
  $("#editing_company");
  var id_company = $("#company select[name=company]").prop("value");
  var data = {"id_company":id_company};

  $.post("ajax/departments_list.php", data, function(info) {
    $('#department').html(info);
  }, "html");
}


/* --------------------------- Выводим список сотрудников выбранного департамента --------------------------- */

function employees_list() {
  var dep_dn = $("#department select[name=department]").prop("value");
  console.log(dep_dn);
  var data = {"dep_dn":dep_dn};

  $.post("ajax/employees_list.php", data, function(info) {
    $('#employees').html(info);
  }, "html");

  $.post("ajax/post_info.php", data, function(info) {
    $("#employees_data").empty();
    $('#employees_data').html(info);
  }, "html");
}


/* --------------------------- Выводим список должностей выбранного департамента --------------------------- */

function update_post_list() {
  var dep_dn = $("#perenos_sotrudnika select[name=department]").prop("value");
  var empl_email = $("#empl_email").html();
  var old_dep_dn = $("#old_dep_dn").html();
  var full_name = $("#full_name").html();
  var id_sotrudnika = $('#employees_data_update_form input[name=id_sotrudnika]').attr('value');
  var way_in_ad = $('#employees_data_update_form input[name=dn]').attr('value');
  var post = $('#employees_data_update_form input[name=post]').attr('value');
  var login = $('#full_name').attr('data-login');
  var data = {"dep_dn":dep_dn, "empl_email":empl_email, "old_dep_dn":old_dep_dn, "full_name":full_name, "id_sotrudnika":id_sotrudnika, "way_in_ad":way_in_ad, "post":post, "login":login};

  $.post("ajax/update_post_list.php", data, function(info) {
    $('#update_post').html(info);
  }, "html");
}


/* --------------------------- Выводим информацию о сотруднике при клике по имени --------------------------- */

$('#employees').on('click', "span", function(e) {
  e.preventDefault();
  e.stopPropagation();

  var empl_dn = $(this).attr('data-dn');
  var data = {"empl_dn":empl_dn};

  $.post("ajax/employees_data.php", data, function(info) {
    $('#employees_data').html(info);
    $('html, body').animate({scrollTop:0}, 'slow');
  }, "html");
});


/* --------KSS-------- Обновляем информацию о сотруднике при клике по кнопке обновления --------KSS-------- */

$('#employees_data').on('click', "#employees_data_update", function(e){
  e.preventDefault();
  e.stopPropagation();

  if(confirm('Вы уверены, что хотите обновить данные этого пользователя?')) {
    var empl_dn = $('#employees_data_update_form input[name=dn]').attr('value');
    var file = document.querySelector('input[type=file]').files[0];
    //var data_decree = $('#date_release_decree').attr('value');
    var data_decree_array = $('#employees_data form#employees_data_update_form').serializeArray();
    var data_decree = data_decree_array[10]["value"];
    var data_was = $('#date_release_decree').attr('data-was');


    if(file) {
      var reader = new FileReader();
      reader.onloadend = function () {
        var file_img = reader.result;
        var form_data = $('#employees_data form#employees_data_update_form').serializeArray();
        var data = {"form_data":form_data, "file_img":file_img};
        $.post("ajax/employees_update.php", data, function( response ) {
            const resp = JSON.parse( response.slice(1, response.length) );
            const info = resp.info;

            $("#employees_data").empty();
            employees_list();
            $(".preloader").css("display","none");

            $('#modal-content').html(info);

            var modal = UIkit.modal("#modal-creation-of-applications", {center:true});
            modal.show();

        }, "html");
      }

      if (file) {
        reader.readAsDataURL(file);
      } else {
        preview.src = "";
      }

      if(data_decree && data_was == "add"){
        var data = {"empl_dn":empl_dn};
        $.post("ajax/employees_dekret.php", data, function(info) {
          $("#employees_data").empty();
          $(".preloader").css("display","none");

          $('#modal-content').html(info);

          var modal = UIkit.modal("#modal-creation-of-applications", {center:true});
          modal.show();

        }, "html");
      }

    }else{
      var form_data = $('#employees_data form#employees_data_update_form').serializeArray();
      var data = {"form_data":form_data};

      $.post("ajax/employees_update.php", data, function(response) {
          const resp = JSON.parse( response.slice(1, response.length) );
          const info = resp.info;

        employees_list();
        $(".preloader").css("display","none");

        $('#modal-content').html(info);

        var modal = UIkit.modal("#modal-creation-of-applications", {center:true});
        modal.show();

      }, "html");

      if(data_decree && data_was == "add"){
        var data = {"empl_dn":empl_dn};

        $.post("ajax/employees_dekret.php", data, function(info) {
          $("#employees_data").empty();
          $(".preloader").css("display","none");

          $('#modal-content').html(info);

          var modal = UIkit.modal("#modal-creation-of-applications", {center:true});
          modal.show();

        }, "html");
      }
    }
  }
});


/* --------------------------- Увольняем сотрудника --------------------------- */

$('#employees_data').on('click', "span#delete", function(e){
  var date_delete = $("#input_delete").val();

  if(date_delete != ""){
    e.preventDefault();
    e.stopPropagation();

    if(confirm('Вы уверены, что хотите удалить этого пользователя без возможности возврата данных?')) {
      var empl_dn = $(this).attr('data-dn');
      var empl_id = $(this).attr('data-id');
      var empl_department = $(this).attr('data-department');

      var data = {"empl_dn":empl_dn, "empl_id":empl_id, "empl_department":empl_department, "date_delete":date_delete};

      $.post("ajax/employees_delete.php", data, function(info) {
        $("#employees_data").empty();
        employees_list();
        $(".preloader").css("display","none");

        $('#modal-content').html(info);

        var modal = UIkit.modal("#modal-creation-of-applications", {center:true});
        modal.show();

      }, "html");
    }
  }else{
    confirm('Введите дату увольнения');
  }
});


/* --------------------------- Изменяем дату увольнения сотрудника --------------------------- */

$('#employees_data').on('click', "span#date_delete", function(e){
  e.preventDefault();
  e.stopPropagation();

  var empl_date = 1;
  var date_delete = $("#input_delete").val();
  if(date_delete == ""){
    date_delete = "00.00.0000";
  }
  var empl_id = $(this).attr('data-id');
  var data = {"empl_date":empl_date, "empl_id":empl_id, "date_delete":date_delete};

  $.post("ajax/employees_delete.php", data, function(info) {
    $("#employees_data").empty();
    employees_list();
    $(".preloader").css("display","none");

    $('#modal-content').html(info);

    var modal = UIkit.modal("#modal-creation-of-applications", {center:true});
    modal.show();
  }, "html");
});


/* --------------------------- Выводим форму для добавления нового сотрудника при клике на кнопку --------------------------- */

$('#employees').on('click', '#employees_add_form', function(e) {
  e.preventDefault();
  e.stopPropagation();

  var dep_dn = $("#department select[name=department]").prop("value");
  var data = {"dep_dn":dep_dn};

  $.post("ajax/employees_add_form_new.php", data, function(info) {
    $('#employees_data').html(info);
    $('html, body').animate({scrollTop:0}, 'slow');
  }, "html");
});


/* --------------------------- Перенос сотрудника в другой департамент в ШР --------------------------- */

$('#employees_data').on('click', "#update_departments_post", function(e){
  e.preventDefault();
  e.stopPropagation();

  var date_change = $("#input_update").val();

  if(date_change != ""){
    if(confirm('Вы уверены, что хотите перенести этого пользователя в другой департамент?')) {
      var new_post = $("#employees_data form#update_post select[name=post]").prop("value");

      if(new_post == 'none') {
        alert('Необходимо выбрать должность!');
      }else{
        var form_data = $('#employees_data form#update_post').serializeArray();
        var data = {"form_data":form_data};

        // Заявку на пропуск создаём если отмечен чекбокс
        if ($("#employees_data form input[name='placeApplicationPassITTransfer']").is(':checked')){
          var skipApplicationCreate = 1;
          var skipApplicationHTML = '<i class="uk-icon-spinner uk-icon-spin uk-icon-small"></i>'; // Для вывода в таблице со списком создаваемых заявок: прелоадер или "не создавать".
        }else{
          var skipApplicationCreate = 0;
          var skipApplicationHTML = 'не выбрано';
        }

        // Отмечены ли чеки на создание опционных заявок
        if ($("#employees_data form input[name='placeApplicationAHOTransfer']").is(':checked')){ // Если выбрано
          var placeApplicationAHOChecked = 1; // Значение = 1, для понимания отправлять ли ajax запрос на создание заявки
          var placeApplicationAHOHTML = '<i class="uk-icon-spinner uk-icon-spin uk-icon-small"></i>'; // Для вывода в таблице со списком создаваемых заявок: прелоадер или "нет".
        }else{
          var placeApplicationAHOChecked = 0;
          var placeApplicationAHOHTML = 'не выбрано';
        }

        if ($("#employees_data form input[name='placeApplicationITTransfer']").is(':checked')){
          var placeApplicationITChecked = 1;
          var placeApplicationITHTML = '<i class="uk-icon-spinner uk-icon-spin uk-icon-small"></i>';
        }else{
          var placeApplicationITChecked = 0;
          var placeApplicationITHTML = 'не выбрано';
        }

        $.post("ajax/employees_change_department_mysql.php", data, function(dataEmplCreate) {
          employees_list();
          $("#employees_data").empty();
          $(".preloader").css("display","none");

          if(dataEmplCreate.success == 1) { // Если сотрудник успешно переведен

            $('#modal-content').html('<h2>Сотрудник успешно переведен!</h2><p>Дождитесь подтверждения создания заявок в выбранные отделы.</p><table class="uk-table"><thead><tr><th>Отдел</th><th>Статус</th></tr></thead><tbody>\
						<tr><td>Пропуск в ИТ</td><td class="skip-it">'+skipApplicationHTML+'</td></tr>\
						<tr><td>Место в ИТ</td><td class="working-place-it">'+placeApplicationITHTML+'</td></tr>\
						<tr><td>Место в АХО</td><td class="working-place-aho">'+placeApplicationAHOHTML+'</td></tr>\
						</tbody></table>');


            // AJAX запросы на создание заявок

            // Заявка в IT на пропуск
            if(skipApplicationCreate == 1) { // по чекбоксу
              var dataApplicationSkipIT = {"form_data":form_data, "emplEmail":dataEmplCreate.emplEmail, "emplTitle":dataEmplCreate.emplTitle, "emplDepName":"1", "emplCategori":"1", "emplSubject":"Перевод сотрудника", "emplLastText":"Просьба сделать пропуск.", "emplApplicationType":"2", "admin_mail":admin_mail};
              $.post("ajax/applications/create_application.php", dataApplicationSkipIT, function(status) { // Запускаем программу создания заявки на Место в ИТ
                if(status == 1) { // Если заявка успешно создана
                  $('.skip-it').html('<i class="uk-icon-thumbs-up uk-icon-small uk-text-success"></i>');
                }else{ // Ошибка создания заявки
                  $('.skip-it').html('<i class="uk-icon-thumbs-down uk-icon-small uk-text-danger"></i>');
                }
              }, "html");
            }

            // Если нужно создать заявку на Место в ИТ
            if(placeApplicationITChecked == 1) {
              var dataApplicationPlaceIT = {"form_data":form_data, "emplEmail":dataEmplCreate.emplEmail, "emplTitle":dataEmplCreate.emplTitle, "emplDepName":"1", "emplCategori":"1", "emplSubject":"Перевод сотрудника", "emplLastText":"Просьба сделать рабочее место.", "emplApplicationType":"2", "admin_mail":admin_mail};
              $.post("ajax/applications/create_application.php", dataApplicationPlaceIT, function(status) { // Запускаем программу создания заявки на Место в ИТ
                if(status == 1) { // Если заявка успешно создана
                  $('.working-place-it').html('<i class="uk-icon-thumbs-up uk-icon-small uk-text-success"></i>');
                }else{ // Ошибка создания заявки
                  $('.working-place-it').html('<i class="uk-icon-thumbs-down uk-icon-small uk-text-danger"></i>');
                }
              }, "html");
            }

            // Если нужно создать заявку на Место в АХО
            if(placeApplicationAHOChecked == 1) {
              var dataApplicationPlaceAHO = {"form_data":form_data, "emplEmail":dataEmplCreate.emplEmail, "emplTitle":dataEmplCreate.emplTitle, "emplDepName":"2", "emplCategori":"14", "emplSubject":"Перевод сотрудника", "emplLastText":"Просьба сделать рабочее место.", "emplApplicationType":"2", "admin_mail":admin_mail};
              $.post("ajax/applications/create_application.php", dataApplicationPlaceAHO, function(status) { // Запускаем программу создания заявки на Место в ИТ
                if(status == 1) { // Если заявка успешно создана
                  $('.working-place-aho').html('<i class="uk-icon-thumbs-up uk-icon-small uk-text-success"></i>');
                }else{ // Ошибка создания заявки
                  $('.working-place-aho').html('<i class="uk-icon-thumbs-down uk-icon-small uk-text-danger"></i>');
                }
              }, "html");
            }

          }else{
            $('#modal-content').html("<h2>При переводе сотрудника возникла ошибка!</h2><p>Свяжитесь с отделом Web-разработки.</p>");
          }

          var modal = UIkit.modal("#modal-creation-of-applications", {center:true});
          modal.show();
        }, "json");
      }
    }
  }else{
    confirm('Введите дату смены должности');
  }
});


/* --------------------------- Перенос сотрудника в другой департамент --------------------------- */

$('#employees_data').on('click', "#department_change_button", function(e){
  e.preventDefault();
  e.stopPropagation();

  if(confirm('Вы уверены, что хотите перенести этого пользователя в другой департамент?')) {
    var new_department = $("#employees_data form#department_change_form select[name=new_department]").prop("value");
    if(new_department == 'none') {
				alert('Необходимо выбрать новый департамент!');
    }else{
      var form_data = $('#employees_data form#department_change_form').serializeArray();
      var data = {"form_data":form_data};

      $.post("ajax/employees_change_department.php", data, function(info) {
        employees_list();
        $("#employees_data").empty();
        $(".preloader").css("display","none");

        $('#modal-content').html(info);

        var modal = UIkit.modal("#modal-creation-of-applications", {center:true});
        modal.show();

      }, "html");
    }
  }
});


/* --------------------------- Вывод сотрудника из декрета --------------------------- */

$('#employees_data').on('click', "#withdrawal_decree_button", function(e){
  e.preventDefault();
  e.stopPropagation();

  if(confirm('Вы уверены, что хотите вывести сотрудника из декрета?')) {
    var department = $("#employees_data form#withdrawal_decree select[name=new_department]").prop("value");
    if(department == 'none') {
      alert('Необходимо выбрать департамент!');
    }else{
      var form_data = $('#employees_data form#withdrawal_decree').serializeArray();

      // Заявку на пропуск создаём если отмечен чекбокс
      if ($("#employees_data form input[name='placeApplicationPassITDecree']").is(':checked')){
        var skipApplicationCreate = 1;
        var skipApplicationHTML = '<i class="uk-icon-spinner uk-icon-spin uk-icon-small"></i>'; // Для вывода в таблице со списком создаваемых заявок: прелоадер или "не создавать".
      }else{
        var skipApplicationCreate = 0;
        var skipApplicationHTML = 'не выбрано';
      }

      // Отмечены ли чеки на создание опционных заявок
      if ($("#employees_data form input[name='placeApplicationAHODecree']").is(':checked')){ // Если выбрано
        var placeApplicationAHOChecked = 1; // Значение = 1, для понимания отправлять ли ajax запрос на создание заявки
        var placeApplicationAHOHTML = '<i class="uk-icon-spinner uk-icon-spin uk-icon-small"></i>'; // Для вывода в таблице со списком создаваемых заявок: прелоадер или "нет".
      }else{
        var placeApplicationAHOChecked = 0;
        var placeApplicationAHOHTML = 'не выбрано';
      }

      if ($("#employees_data form input[name='placeApplicationITDecree']").is(':checked')){
        var placeApplicationITChecked = 1;
        var placeApplicationITHTML = '<i class="uk-icon-spinner uk-icon-spin uk-icon-small"></i>';
      }else{
        var placeApplicationITChecked = 0;
        var placeApplicationITHTML = 'не выбрано';
      }

      employees_list();
      $("#employees_data").empty();
      $(".preloader").css("display","none");

      $('#modal-content').html('<h2>Вывод сотрудника из декрета</h2><p>Дождитесь подтверждения создания заявок в выбранные отделы.</p><table class="uk-table"><thead><tr><th>Отдел</th><th>Статус</th></tr></thead><tbody>\
						<tr><td>Вывод из декрета</td><td class="employee-exit-hr"><i class="uk-icon-spinner uk-icon-spin uk-icon-small"></i></td></tr>\
						<tr><td>Пропуск в ИТ</td><td class="skip-it">'+skipApplicationHTML+'</td></tr>\
						<tr><td>Место в ИТ</td><td class="working-place-it">'+placeApplicationITHTML+'</td></tr>\
						<tr><td>Место в АХО</td><td class="working-place-aho">'+placeApplicationAHOHTML+'</td></tr>\
						</tbody></table>');

      // AJAX запросы на создание заявок

      // Заявка на вывод из декрета
      var dataApplicationDecree = {"form_data":form_data, "emplDepName":"1", "emplCategori":"1", "emplSubject":"Выход сотрудника из декрета", "emplLastText":" ", "emplApplicationType":"3", "admin_mail":admin_mail};
      $.post("ajax/applications/create_application.php", dataApplicationDecree, function(status) { // Запускаем программу создания заявки на Место в ИТ
        if(status == 1) { // Если заявка успешно создана
          $('.employee-exit-hr').html('<i class="uk-icon-thumbs-up uk-icon-small uk-text-success"></i>');
        }else{ // Ошибка создания заявки
          $('.employee-exit-hr').html('<i class="uk-icon-thumbs-down uk-icon-small uk-text-danger"></i>');
        }
      }, "html");

      // Заявка в IT на пропуск
      if(skipApplicationCreate == 1) { // по чекбоксу
        var dataApplicationSkipIT = {"form_data":form_data, "emplDepName":"1", "emplCategori":"1", "emplSubject":"Выход сотрудника из декрета", "emplLastText":"Просьба сделать пропуск.", "emplApplicationType":"3", "admin_mail":admin_mail};
        $.post("ajax/applications/create_application.php", dataApplicationSkipIT, function(status) { // Запускаем программу создания заявки на Место в ИТ
          if(status == 1) { // Если заявка успешно создана
            $('.skip-it').html('<i class="uk-icon-thumbs-up uk-icon-small uk-text-success"></i>');
          }else{ // Ошибка создания заявки
            $('.skip-it').html('<i class="uk-icon-thumbs-down uk-icon-small uk-text-danger"></i>');
          }
        }, "html");
      }

      // Если нужно создать заявку на Место в ИТ
      if(placeApplicationITChecked == 1) {
        var dataApplicationPlaceIT = {"form_data":form_data, "emplDepName":"1", "emplCategori":"1", "emplSubject":"Выход сотрудника из декрета", "emplLastText":"Просьба сделать рабочее место.", "emplApplicationType":"3", "admin_mail":admin_mail};
        $.post("ajax/applications/create_application.php", dataApplicationPlaceIT, function(status) { // Запускаем программу создания заявки на Место в ИТ
          if(status == 1) { // Если заявка успешно создана
            $('.working-place-it').html('<i class="uk-icon-thumbs-up uk-icon-small uk-text-success"></i>');
          }else{ // Ошибка создания заявки
            $('.working-place-it').html('<i class="uk-icon-thumbs-down uk-icon-small uk-text-danger"></i>');
          }
        }, "html");
      }

      // Если нужно создать заявку на Место в АХО
      if(placeApplicationAHOChecked == 1) {
        var dataApplicationPlaceAHO = {"form_data":form_data, "emplDepName":"2", "emplCategori":"14", "emplSubject":"Выход сотрудника из декрета", "emplLastText":"Просьба сделать рабочее место.", "emplApplicationType":"3", "admin_mail":admin_mail};
        $.post("ajax/applications/create_application.php", dataApplicationPlaceAHO, function(status) { // Запускаем программу создания заявки на Место в ИТ
          if(status == 1) { // Если заявка успешно создана
            $('.working-place-aho').html('<i class="uk-icon-thumbs-up uk-icon-small uk-text-success"></i>');
          }else{ // Ошибка создания заявки
            $('.working-place-aho').html('<i class="uk-icon-thumbs-down uk-icon-small uk-text-danger"></i>');
          }
        }, "html");
      }

      var modal = UIkit.modal("#modal-creation-of-applications", {center:true});
      modal.show();

    }
  }
});


/* --------------------------- Выводим список сотрудников используя в качестве строки поиска введённую строку в форме поиска --------------------------- */

$('#navigation').on('click', "#employees_search_button", function(e) {
  e.preventDefault();
  e.stopPropagation();
  search_employees();
});


/* --------------------------- Отменяем обработку нажатия ENTER для форм --------------------------- */

$("#navigation form").on("submit", function(e) {
  e.preventDefault();
  search_employees();
});


/* --------------------------- Функция инициации поиска сотрудников по строке --------------------------- */

function search_employees() {
  var name = $("#employees_search_name").val();
  var data = {"name":name};

  $.post("ajax/employees_search.php", data, function(info) {
    $('#employees').html(info);
    $("#employees_data").empty();
  }, "html");
}


/* --------------------------- Добавляем сотрудника в AD --------------------------- */

$('#employees_data').on('click', "#employees_add", function(e){
  e.preventDefault();
  e.stopPropagation();

  //$(".preloader").css("display","block");
  var form_data = $('#employees_data form').serializeArray(); // Получаем весь список данных из формы для передачи в программу

  // Заявку на пропуск создаём если отмечен чекбокс
  if ($("#employees_data form input[name='placeApplicationPassIT']").is(':checked')){
    var skipApplicationCreate = 1;
    var skipApplicationHTML = '<i class="uk-icon-spinner uk-icon-spin uk-icon-small"></i>'; // Для вывода в таблице со списком создаваемых заявок: прелоадер или "не создавать".
  }else{
    var skipApplicationCreate = 0;
    var skipApplicationHTML = 'не выбрано';
  }

  // Отмечены ли чеки на создание опционных заявок
  if ($("#employees_data form input[name='placeApplicationAHO']").is(':checked')){ // Если выбрано
    var placeApplicationAHOChecked = 1; // Значение = 1, для понимания отправлять ли ajax запрос на создание заявки
    var placeApplicationAHOHTML = '<i class="uk-icon-spinner uk-icon-spin uk-icon-small"></i>'; // Для вывода в таблице со списком создаваемых заявок: прелоадер или "нет".
  }else{
    var placeApplicationAHOChecked = 0;
    var placeApplicationAHOHTML = 'не выбрано';
  }

  if ($("#employees_data form input[name='placeApplicationIT']").is(':checked')){
    var placeApplicationITChecked = 1;
    var placeApplicationITHTML = '<i class="uk-icon-spinner uk-icon-spin uk-icon-small"></i>';
  }else{
    var placeApplicationITChecked = 0;
    var placeApplicationITHTML = 'не выбрано';
  }

  var flag = false;
  if(form_data[3]['value'] == "td"){
    if(form_data[4]['value'] != "none" && form_data[7]['value'] && form_data[8]['value'] && form_data[11]['value'] != "none" && form_data[16]['value'] && form_data[17]['value'] && form_data[18]['value'] && form_data[20]['value'] != "none" && form_data[21]['value'] != "none"){
      flag = true;
    }
  }
  if(form_data[3]['value'] == "gph" || form_data[3]['value'] == "du"){
    if(form_data[4]['value'] == "1"){
      if(form_data[6]['value'] && form_data[7]['value'] && form_data[8]['value'] && form_data[9]['value'] && form_data[12]['value'] != "none" && form_data[17]['value'] && form_data[19]['value'] && form_data[21]['value'] != "none" && form_data[22]['value'] != "none"){
        flag = true;
      }
    }else{
      if(form_data[4]['value'] != "none" && form_data[7]['value'] && form_data[8]['value'] && form_data[11]['value'] != "none" && form_data[16]['value'] && form_data[18]['value'] && form_data[20]['value'] != "none" && form_data[21]['value'] != "none"){
        flag = true;
      }
    }
    if(form_data[4]['value'] == "none"){
      if(form_data[7]['value'] && form_data[8]['value'] && form_data[11]['value'] != "none" && form_data[16]['value'] && form_data[18]['value'] && form_data[20]['value'] != "none" && form_data[20]['name'] == "office_in_ad" && form_data[21]['value'] != "none"){
        flag = true;
      }
      if(form_data[7]['value'] && form_data[8]['value'] && form_data[11]['value'] != "none" && form_data[16]['value'] && form_data[18]['value'] && form_data[20]['name'] == "hide_in_ad" && form_data[21]['value'] != "none" && form_data[22]['value'] != "none"){
        flag = true;
      }
    }
  }


  if(flag) {
    var file = document.querySelector('input[type=file]').files[0];
    if(file) {
      var reader = new FileReader();
      reader.onloadend = function () {
        var file_img = reader.result;
        var data = {"form_data":form_data, "file_img":file_img};
        EmployeesAdd(data);
      }

      if (file) {
        reader.readAsDataURL(file);
      }else{
        preview.src = "";
      }
    }else{
      var data = {"form_data":form_data};
      EmployeesAdd(data);
    }

    // Функция создания нового сотрудника и заявок
    function EmployeesAdd(data) {
      $.post("ajax/employees_add.php", data, function(dataEmplCreate) {
        employees_list();
        $("#employees_data").empty();
        $(".preloader").css("display","none");

        if(dataEmplCreate.success == 1) { // Если сотрудник успешно добавлен в AD

          $('#modal-content').html('<h2>Сотрудник успешно создан!</h2><p>Дождитесь подтверждения создания заявок в выбранные отделы.</p><table class="uk-table"><thead><tr><th>Отдел</th><th>Статус</th></tr></thead><tbody>\
						<tr><td>Выход в HR</td><td class="employee-exit-hr"><i class="uk-icon-spinner uk-icon-spin uk-icon-small"></i></td></tr>\
						<tr><td>Пропуск в ИТ</td><td class="skip-it">'+skipApplicationHTML+'</td></tr>\
						<tr><td>Место в ИТ</td><td class="working-place-it">'+placeApplicationITHTML+'</td></tr>\
						<tr><td>Место в АХО</td><td class="working-place-aho">'+placeApplicationAHOHTML+'</td></tr>\
						</tbody></table>');


          // AJAX запросы на создание заявок

          // Заявка выход в HR
          var dataApplicationExitHR = {"form_data":form_data, "emplEmail":dataEmplCreate.emplEmail, "emplTitle":dataEmplCreate.emplTitle, "emplDepName":"4", "emplCategori":"23", "emplSubject":"Выход нового сотрудника", "emplLastText":"Просьба принять на работу.", "emplApplicationType":"1", "admin_mail":admin_mail};
          //console.log(dataApplicationExitHR);
          $.post("ajax/applications/create_application.php", dataApplicationExitHR, function(status) { // Запускаем программу создания заявки на Место в ИТ
            if(status == 1) { // Если заявка успешно создана
              $('.employee-exit-hr').html('<i class="uk-icon-thumbs-up uk-icon-small uk-text-success"></i>');
            }else{ // Ошибка создания заявки
              $('.employee-exit-hr').html('<i class="uk-icon-thumbs-down uk-icon-small uk-text-danger"></i>');
            }
          }, "html");

          // Заявка в IT на пропуск
          if(skipApplicationCreate == 1) { // по чекбоксу
            var dataApplicationSkipIT = {"form_data":form_data, "emplEmail":dataEmplCreate.emplEmail, "emplTitle":dataEmplCreate.emplTitle, "emplDepName":"1", "emplCategori":"1", "emplSubject":"Выход нового сотрудника", "emplLastText":"Просьба сделать пропуск.", "emplApplicationType":"1", "admin_mail":admin_mail};
            $.post("ajax/applications/create_application.php", dataApplicationSkipIT, function(status) { // Запускаем программу создания заявки на Место в ИТ
              if(status == 1) { // Если заявка успешно создана
                $('.skip-it').html('<i class="uk-icon-thumbs-up uk-icon-small uk-text-success"></i>');
              }else{ // Ошибка создания заявки
                $('.skip-it').html('<i class="uk-icon-thumbs-down uk-icon-small uk-text-danger"></i>');
              }
            }, "html");
          }

          // Если нужно создать заявку на Место в ИТ
          if(placeApplicationITChecked == 1) {
            var dataApplicationPlaceIT = {"form_data":form_data, "emplEmail":dataEmplCreate.emplEmail, "emplTitle":dataEmplCreate.emplTitle, "emplDepName":"1", "emplCategori":"1", "emplSubject":"Рабочее место нового сотрудника", "emplLastText":"Просьба сделать рабочее место.", "emplApplicationType":"1", "admin_mail":admin_mail};
            $.post("ajax/applications/create_application.php", dataApplicationPlaceIT, function(status) { // Запускаем программу создания заявки на Место в ИТ
              if(status == 1) { // Если заявка успешно создана
                $('.working-place-it').html('<i class="uk-icon-thumbs-up uk-icon-small uk-text-success"></i>');
              }else{ // Ошибка создания заявки
                $('.working-place-it').html('<i class="uk-icon-thumbs-down uk-icon-small uk-text-danger"></i>');
              }
            }, "html");
          }

          // Если нужно создать заявку на Место в АХО
          if(placeApplicationAHOChecked == 1) {
            var dataApplicationPlaceAHO = {"form_data":form_data, "emplEmail":dataEmplCreate.emplEmail, "emplTitle":dataEmplCreate.emplTitle, "emplDepName":"2", "emplCategori":"14", "emplSubject":"Выход нового сотрудника", "emplLastText":"Просьба сделать рабочее место.", "emplApplicationType":"1", "admin_mail":admin_mail};
            $.post("ajax/applications/create_application.php", dataApplicationPlaceAHO, function(status) { // Запускаем программу создания заявки на Место в ИТ
            if(status == 1) { // Если заявка успешно создана
                $('.working-place-aho').html('<i class="uk-icon-thumbs-up uk-icon-small uk-text-success"></i>');
             }else{ // Ошибка создания заявки
                $('.working-place-aho').html('<i class="uk-icon-thumbs-down uk-icon-small uk-text-danger"></i>');
             }
            }, "html");
          }

        }else if(dataEmplCreate.success == 2){
          $('#modal-content').html('<h2>Сотрудник успешно добавлен в STAFF.</h2>');
        }else{
          $('#modal-content').html("<h2>Возникла ошибка при создания нового сотрудника!</h2><p>Свяжитесь с отделом Web-разработки.</p>");
        }

        var modal = UIkit.modal("#modal-creation-of-applications", {center:true});
        modal.show();
      }, "json");
    }
  }else{
    alert('Заполнены не все обязательные поля!')
    $(".preloader").css("display","none");
  }
});

</script>