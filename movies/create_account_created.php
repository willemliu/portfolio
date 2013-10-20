<?php
	session_start();
	
	include_once("./includes/load_cookies.php");
	
  if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')){
    header('Content-Encoding: gzip');
  }
	header("Content-type: text/xml");
	header('Content-Description: XML');
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	header("Pragma: no-cache");
	header("Expires: 0");
  
	include_once("./includes/dbconnection.php");
	include_once("./includes/patTemplate.php");
	include_once("./includes/fade_functions.php");
	
	// Initialize the patTemplate-class, and create an object.
	$tmpl = new patTemplate();

	// Set which directory contains the template-files.
	$tmpl->setBasedir("tmpl");
	// Set template
  $template_file = "create_account_created.tmpl.xml";
  $tmpl->readTemplatesFromFile($template_file);
  $tmpl->addGlobalVar("FILE_NAME", $template_file);

  //Check login
  DBConnect();
  
  $security_level = 0;

  //Fade Information box in immediatly
  FadeIn($tmpl, "INFORMATION", 0);

  //Disable the Update button immediatly
  $tmpl->addVar("setTimeout", "COMMAND", "document.getElementById('create_account_create').disabled = true");
  $tmpl->addVar("setTimeout", "MILLISECONDS", "0");
  $tmpl->parseTemplate("setTimeout", "a");

  //If mandatory fields aren't empty
  $first_name = mysql_real_escape_string(utf8_decode(strip_tags(urlDecode($_POST['create_account_first_name']))));
  $last_name = mysql_real_escape_string(utf8_decode(strip_tags(urlDecode($_POST['create_account_last_name']))));
  $email = mysql_real_escape_string(utf8_decode(strip_tags(urlDecode($_POST['create_account_email']))));
  $gender_id = mysql_real_escape_string(utf8_decode(strip_tags(urlDecode($_POST['create_account_gender_id']))));
  $address = mysql_real_escape_string(utf8_decode(strip_tags(urlDecode($_POST['create_account_address']))));
  $city = mysql_real_escape_string(utf8_decode(strip_tags(urlDecode($_POST['create_account_city']))));
  $country_code = mysql_real_escape_string(utf8_decode(strip_tags(urlDecode($_POST['create_account_country_code']))));
  $postal = mysql_real_escape_string(utf8_decode(strip_tags(urlDecode($_POST['create_account_postal']))));
  $phone = mysql_real_escape_string(utf8_decode(strip_tags(urlDecode($_POST['create_account_phone']))));
  $mobile = mysql_real_escape_string(utf8_decode(strip_tags(urlDecode($_POST['create_account_mobile']))));
  $nick_name = mysql_real_escape_string(utf8_decode(strip_tags(urlDecode($_POST['create_account_nick_name']))));
  $user_name = mysql_real_escape_string(utf8_decode(strip_tags(urlDecode($_POST['create_account_user_name']))));
  $new_password = mysql_real_escape_string(utf8_decode(strip_tags(urlDecode($_POST['create_account_new_password']))));
  $new_password2 = mysql_real_escape_string(utf8_decode(strip_tags(urlDecode($_POST['create_account_new_password2']))));
  
  if($first_name != "" &&
  $last_name != "" &&
  $email != "" &&
  $user_name != "" &&
  $nick_name != ""){
    $check_result = DBQuery("SELECT nick_name, email, user_name FROM `wmc_users` WHERE " . 
    "nick_name='" . $nick_name . "' OR " .
    "email='" . $email . "' OR " .
    "user_name='" . $user_name . "'");
    if(mysql_num_rows($check_result) == 0){
        if($new_password != "" && $new_password == $new_password2){
          DBQuery("INSERT INTO wmc_users (first_name, last_name, email, gender_id, address, city, country_code, postal, phone, mobile, nick_name, user_name, password, security_level) VALUES (" .
          "'" . $first_name . "', " .
          "'" . $last_name . "', " .
          "'" . $email . "', " .
          "'" . $gender_id . "', " .
          "'" . $address . "', " .
          "'" . $city . "', " .
          "'" . $country_code . "', " .
          "'" . $postal . "', " .
          "'" . $phone . "', " .
          "'" . $mobile . "', " .
          "'" . $nick_name . "', " .
          "'" . $user_name . "', " .
          "'" . md5($new_password) . "', " .
          "'1')");
          
          $check_account = DBQuery("SELECT id FROM wmc_users WHERE nick_name='" . $nick_name . "'");
          if(mysql_num_rows($check_account) > 0){
            $user_id = mysql_result($check_account, 0, "id");
            DBQuery("INSERT INTO wmc_users_films_columns (user_id) VALUES ('" . $user_id . "')");
          }

          //Fade My Settings window out immediatly
          FadeOut($tmpl, "CREATE_ACCOUNT", 0);
          $tmpl->addVar("setTimeout", "COMMAND", "postData('window_close.php', '', 'window=CREATE_ACCOUNT')");
          $tmpl->addVar("setTimeout", "MILLISECONDS", "0");
          $tmpl->parseTemplate("setTimeout", "a");

          $tmpl->addGlobalVar("INFORMATION", "An account activation e-mail has been sent to you. " .
          "&lt;br&gt;" . 
          "Follow the instruction in that e-mail to activate your account." .
          "&lt;br&gt;" . 
          "Some spamfilters might block this e-mail.");

          $headers = "From: My Movie Collection <no-reply@willemliu.nl>";
          $subject = "My Movie Collection Account Activation";
          $message = "Welcome " . $first_name . " " . $last_name .
          "\n\n" . 
          "By visiting the next URL your account will be activated. " .
          "You can then sign in with your username and password." .
          "\n\n" . 
          "http://" . $_SERVER['HTTP_HOST'] . "/activate_account.php?activation_code=" . md5($new_password) .
          "\n\n" . 
          "Your username is: " . $user_name .
          "\n" . 
          "Your password is: " . $new_password .
          "\n\n" . 
          "We hope that our services will please you." .
          "\n\n" . 
          "Regards," .
          "\n\n" . 
          "My Movie Collection Team";
          mail($email, $subject, $message, $headers);
        } else{
          $tmpl->addGlobalVar("INFORMATION", "Your new passwords don't match.");
          //Enable the Update button after 1 second
          $tmpl->addVar("setTimeout", "COMMAND", "document.getElementById('create_account_create').disabled = false");
          $tmpl->addVar("setTimeout", "MILLISECONDS", "1000");
          $tmpl->parseTemplate("setTimeout", "a");
          //Fade Information box in immediatly
          FadeOut($tmpl, "INFORMATION", 5000);
        }
    } else{
      if(mysql_result($check_result, 0, "nick_name") == $nick_name){
        //Focus on nick name text field
        $tmpl->addVar("setTimeout", "COMMAND", "document.getElementById('create_account_nick_name').focus();");
        $tmpl->addVar("setTimeout", "MILLISECONDS", "0");
        $tmpl->parseTemplate("setTimeout", "a");
        $tmpl->addGlobalVar("INFORMATION", "Your nick name is not unique.");
      } else if(mysql_result($check_result, 0, "email") == $email){
        //Focus on e-mail text field
        $tmpl->addVar("setTimeout", "COMMAND", "document.getElementById('create_account_email').focus();");
        $tmpl->addVar("setTimeout", "MILLISECONDS", "0");
        $tmpl->parseTemplate("setTimeout", "a");
        $tmpl->addGlobalVar("INFORMATION", "Your e-mail is not unique.");
      } else if(mysql_result($check_result, 0, "user_name") == $user_name){
        //Focus on user name text field
        $tmpl->addVar("setTimeout", "COMMAND", "document.getElementById('create_account_user_name').focus();");
        $tmpl->addVar("setTimeout", "MILLISECONDS", "0");
        $tmpl->parseTemplate("setTimeout", "a");
        $tmpl->addGlobalVar("INFORMATION", "Your user name is not unique.");
      }
      //Enable the Update button after 1 second
      $tmpl->addVar("setTimeout", "COMMAND", "document.getElementById('create_account_create').disabled = false");
      $tmpl->addVar("setTimeout", "MILLISECONDS", "1000");
      $tmpl->parseTemplate("setTimeout", "a");
      //Fade Information box in immediatly
      FadeOut($tmpl, "INFORMATION", 5000);
    }
  } else{
    //Focus on first name text field
    $tmpl->addVar("setTimeout", "COMMAND", "document.getElementById('create_account_first_name').focus();");
    $tmpl->addVar("setTimeout", "MILLISECONDS", "0");
    $tmpl->parseTemplate("setTimeout", "a");
    $tmpl->addGlobalVar("INFORMATION", "Fields marked with (*) are mandatory.");
    //Enable the Update button after 1 second
    $tmpl->addVar("setTimeout", "COMMAND", "document.getElementById('create_account_create').disabled = false");
    $tmpl->addVar("setTimeout", "MILLISECONDS", "1000");
    $tmpl->parseTemplate("setTimeout", "a");
    //Fade Information box in immediatly
    FadeOut($tmpl, "INFORMATION", 5000);
  }

  if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')){
    $content = gzencode($tmpl->getParsedTemplate("body"), 9);
    echo $content;
  } else{
    $tmpl->displayParsedTemplate("body");
  }
?>
