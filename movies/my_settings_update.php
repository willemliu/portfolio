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
  $template_file = "my_settings_update.tmpl.xml";
  $tmpl->readTemplatesFromFile($template_file);
  $tmpl->addGlobalVar("FILE_NAME", $template_file);

  //Check login
  DBConnect();
  $res = DBQuery("SELECT id, nick_name, user_name, password, security_level FROM wmc_users WHERE active='1' AND user_name='" . mysql_real_escape_string($_SESSION['username']) . "' AND password='" . mysql_real_escape_string($_SESSION['password']) . "'");
  
  $security_level = 0;

  //Fade Information box in immediatly
  FadeIn($tmpl, "INFORMATION", 0);

  //Disable the Update button immediatly
  $tmpl->addVar("setTimeout", "COMMAND", "document.getElementById('my_settings_update').disabled = true");
  $tmpl->addVar("setTimeout", "MILLISECONDS", "0");
  $tmpl->parseTemplate("setTimeout", "a");

  //If logged in
  if($_POST && mysql_num_rows($res) > 0){
    $security_level = mysql_result($res, 0, "security_level");
    $menu_result = DBQuery("SELECT security_level FROM wmc_menus_security_levels WHERE menu_item='My Settings' ORDER BY position ASC");
    //Check security level
    if(mysql_num_rows($menu_result) > 0 && $security_level >= mysql_result($menu_result, 0, "security_level")){
      $user_id = mysql_result($res, 0, "id");

      //If mandatory fields aren't empty
      $first_name = mysql_real_escape_string(utf8_decode(strip_tags(urlDecode($_POST['my_settings_first_name']))));
      $last_name = mysql_real_escape_string(utf8_decode(strip_tags(urlDecode($_POST['my_settings_last_name']))));
      $email = mysql_real_escape_string(utf8_decode(strip_tags(urlDecode($_POST['my_settings_email']))));
      $gender_id = mysql_real_escape_string(utf8_decode(strip_tags(urlDecode($_POST['my_settings_gender_id']))));
      $address = mysql_real_escape_string(utf8_decode(strip_tags(urlDecode($_POST['my_settings_address']))));
      $city = mysql_real_escape_string(utf8_decode(strip_tags(urlDecode($_POST['my_settings_city']))));
      $country_code = mysql_real_escape_string(utf8_decode(strip_tags(urlDecode($_POST['my_settings_country_code']))));
      $postal = mysql_real_escape_string(utf8_decode(strip_tags(urlDecode($_POST['my_settings_postal']))));
      $phone = mysql_real_escape_string(utf8_decode(strip_tags(urlDecode($_POST['my_settings_phone']))));
      $mobile = mysql_real_escape_string(utf8_decode(strip_tags(urlDecode($_POST['my_settings_mobile']))));
      $nick_name = mysql_real_escape_string(utf8_decode(strip_tags(urlDecode($_POST['my_settings_nick_name']))));
      $user_name = mysql_real_escape_string(utf8_decode(strip_tags(urlDecode($_POST['my_settings_user_name']))));
      $old_password = mysql_real_escape_string(utf8_decode(strip_tags(urlDecode($_POST['my_settings_old_password']))));
      $new_password = mysql_real_escape_string(utf8_decode(strip_tags(urlDecode($_POST['my_settings_new_password']))));
      $new_password2 = mysql_real_escape_string(utf8_decode(strip_tags(urlDecode($_POST['my_settings_new_password2']))));
      if($first_name != "" &&
      $last_name != "" &&
      $email != "" &&
      $user_name != "" &&
      $nick_name != ""){
        $check_result = DBQuery("SELECT nick_name, email, user_name FROM `wmc_users` WHERE (" . 
        "nick_name='" . $nick_name . "' OR " .
        "email='" . $email . "' OR " .
        "user_name='" . $user_name . "') AND id!=" . $user_id);
        if(mysql_num_rows($check_result) == 0){
          if(md5($old_password) == mysql_result($res, 0, "password")){
            if($new_password != "" && $new_password == $new_password2){
              DBQuery("UPDATE wmc_users SET " . 
              "first_name='" . $first_name . "', " .
              "last_name='" . $last_name . "', " .
              "email='" . $email . "', " .
              "gender_id='" . $gender_id . "', " .
              "address='" . $address . "', " .
              "city='" . $city . "', " .
              "country_code='" . $country_code . "', " .
              "postal='" . $postal . "', " .
              "phone='" . $phone . "', " .
              "mobile='" . $mobile . "', " .
              "nick_name='" . $nick_name . "', " .
              "user_name='" . $user_name . "', " .
              "password='" . md5($new_password) . "' WHERE id=" . $user_id);
              $_SESSION['username'] = $user_name;
              $_SESSION['password'] = md5($new_password);
              setcookie("username", $_SESSION['username'], time()+604800, "/");
              setcookie("password", $_SESSION['password'], time()+604800, "/");
              $tmpl->addGlobalVar("INFORMATION", "Your information has been updated.");
              //Fade My Settings window out immediatly
              FadeOut($tmpl, "MY_SETTINGS", 0);
              $tmpl->addVar("setTimeout", "COMMAND", "postData('window_close.php', '', 'window=MY_SETTINGS')");
              $tmpl->addVar("setTimeout", "MILLISECONDS", "0");
              $tmpl->parseTemplate("setTimeout", "a");
            } else{
              //Focus on user name text field
              $tmpl->addVar("setTimeout", "COMMAND", "document.getElementById('my_settings_new_password').focus();");
              $tmpl->addVar("setTimeout", "MILLISECONDS", "0");
              $tmpl->parseTemplate("setTimeout", "a");
              $tmpl->addGlobalVar("INFORMATION", "Your new passwords doesn't match.");
              //Enable the Update button after 1 second
              $tmpl->addVar("setTimeout", "COMMAND", "document.getElementById('my_settings_update').disabled = false");
              $tmpl->addVar("setTimeout", "MILLISECONDS", "1000");
              $tmpl->parseTemplate("setTimeout", "a");
            }
          } else if($old_password == ""){
            DBQuery("UPDATE wmc_users SET " . 
            "first_name='" . $first_name . "', " .
            "last_name='" . $last_name . "', " .
            "email='" . $email . "', " .
            "gender_id='" . $gender_id . "', " .
            "address='" . $address . "', " .
            "city='" . $city . "', " .
            "country_code='" . $country_code . "', " .
            "postal='" . $postal . "', " .
            "phone='" . $phone . "', " .
            "nick_name='" . $nick_name . "', " .
            "user_name='" . $user_name . "', " .
            "mobile='" . $mobile . "' WHERE id=" . $user_id);
            $_SESSION['username'] = $user_name;
            setcookie("username", $_SESSION['username'], time()+604800, "/");
            setcookie("password", $_SESSION['password'], time()+604800, "/");
            $tmpl->addGlobalVar("INFORMATION", "Your information has been updated.");
            //Fade My Settings window out immediatly
            FadeOut($tmpl, "MY_SETTINGS", 0);
            $tmpl->addVar("setTimeout", "COMMAND", "postData('window_close.php', '', 'window=MY_SETTINGS')");
            $tmpl->addVar("setTimeout", "MILLISECONDS", "0");
            $tmpl->parseTemplate("setTimeout", "a");
          } else{
            //Focus on password text field
            $tmpl->addVar("setTimeout", "COMMAND", "document.getElementById('my_settings_old_password').focus();");
            $tmpl->addVar("setTimeout", "MILLISECONDS", "0");
            $tmpl->parseTemplate("setTimeout", "a");
            $tmpl->addGlobalVar("INFORMATION", "Your old password does not match.");
            //Enable the Update button after 1 second
            $tmpl->addVar("setTimeout", "COMMAND", "document.getElementById('my_settings_update').disabled = false");
            $tmpl->addVar("setTimeout", "MILLISECONDS", "1000");
            $tmpl->parseTemplate("setTimeout", "a");
          }
        } else{
          if(mysql_result($check_result, 0, "nick_name") == $nick_name){
            //Focus on nick name text field
            $tmpl->addVar("setTimeout", "COMMAND", "document.getElementById('my_settings_nick_name').focus();");
            $tmpl->addVar("setTimeout", "MILLISECONDS", "0");
            $tmpl->parseTemplate("setTimeout", "a");
            $tmpl->addGlobalVar("INFORMATION", "Your nick name is not unique.");
          } else if(mysql_result($check_result, 0, "email") == $email){
            //Focus on e-mail text field
            $tmpl->addVar("setTimeout", "COMMAND", "document.getElementById('my_settings_email').focus();");
            $tmpl->addVar("setTimeout", "MILLISECONDS", "0");
            $tmpl->parseTemplate("setTimeout", "a");
            $tmpl->addGlobalVar("INFORMATION", "Your e-mail is not unique.");
          } else if(mysql_result($check_result, 0, "user_name") == $user_name){
            //Focus on user name text field
            $tmpl->addVar("setTimeout", "COMMAND", "document.getElementById('my_settings_user_name').focus();");
            $tmpl->addVar("setTimeout", "MILLISECONDS", "0");
            $tmpl->parseTemplate("setTimeout", "a");
            $tmpl->addGlobalVar("INFORMATION", "Your user name is not unique.");
          }
          //Enable the Update button after 1 second
          $tmpl->addVar("setTimeout", "COMMAND", "document.getElementById('my_settings_update').disabled = false");
          $tmpl->addVar("setTimeout", "MILLISECONDS", "1000");
          $tmpl->parseTemplate("setTimeout", "a");
        }
      } else{
        //Focus on first name text field
        $tmpl->addVar("setTimeout", "COMMAND", "document.getElementById('my_settings_first_name').focus();");
        $tmpl->addVar("setTimeout", "MILLISECONDS", "0");
        $tmpl->parseTemplate("setTimeout", "a");
        $tmpl->addGlobalVar("INFORMATION", "Fields marked with (*) are mandatory.");
        //Enable the Update button after 1 second
        $tmpl->addVar("setTimeout", "COMMAND", "document.getElementById('my_settings_update').disabled = false");
        $tmpl->addVar("setTimeout", "MILLISECONDS", "1000");
        $tmpl->parseTemplate("setTimeout", "a");
      }
    } else{
      //Load standard information into the Information box immediatly
      $tmpl->addVar("setTimeout", "COMMAND", "getData('information.php?filename=information_security_level', 'INFORMATION')");
      $tmpl->addVar("setTimeout", "MILLISECONDS", "0");
      $tmpl->parseTemplate("setTimeout", "a");
    }
  } else{
    //Load standard information into the Information box immediatly
    $tmpl->addVar("setTimeout", "COMMAND", "getData('information.php?filename=information_login_failed', 'INFORMATION')");
    $tmpl->addVar("setTimeout", "MILLISECONDS", "0");
    $tmpl->parseTemplate("setTimeout", "a");
  }
  //Fade Information box out after 3 seconds
  FadeOut($tmpl, "INFORMATION", 3000);

  if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')){
    $content = gzencode($tmpl->getParsedTemplate("body"), 9);
    echo $content;
  } else{
    $tmpl->displayParsedTemplate("body");
  }
?>
