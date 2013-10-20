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
	$template_file = "my_settings.tmpl.xml";
  $tmpl->readTemplatesFromFile($template_file);
  $tmpl->addGlobalVar("FILE_NAME", $template_file);
  
  //Check login
  DBConnect();
  $res = DBQuery("SELECT id, first_name, last_name, address, city, country_code, postal, phone, mobile, email, nick_name, user_name, gender_id, security_level FROM wmc_users WHERE active='1' AND user_name='" . mysql_real_escape_string($_SESSION['username']) . "' AND password='" . mysql_real_escape_string($_SESSION['password']) . "'");

  //Set default security_level of the user to 0
  $security_level = 0;

  //If logged in
  if(mysql_num_rows($res) > 0){
    //Set security_level
    $security_level = mysql_result($res, 0, "security_level");
    $menu_result = DBQuery("SELECT security_level FROM wmc_menus_security_levels WHERE menu_item='My Settings' ORDER BY position ASC");
    //Check if security level is high enough for this action
    if(mysql_num_rows($menu_result) > 0 && $security_level >= mysql_result($menu_result, 0, "security_level")){
      $user_id = mysql_result($res, 0, "id");
      FadeIn($tmpl, "MY_SETTINGS", 0);

      //Set window as opened
      $window_check = DBQuery("SELECT id FROM wmc_users_windows WHERE user_id=" . $user_id . " AND window='MY_SETTINGS'");
      if(mysql_num_rows($window_check) > 0){
        DBQuery("UPDATE wmc_users_windows SET opened='1', page='my_settings.php' WHERE user_id=" . $user_id . " AND window='MY_SETTINGS'");
      } else{
        DBQuery("INSERT INTO wmc_users_windows (x, y, opened, page, window, user_id) VALUES ('80', '110', '1', 'my_settings.php', 'MY_SETTINGS', '" . $user_id . "')");
      }
      
      //Add data to the template applicable to the current user
      $tmpl->addGlobalVar("FIRST_NAME", mysql_result($res, 0, "first_name"));
      $tmpl->addGlobalVar("LAST_NAME", mysql_result($res, 0, "last_name"));
      $tmpl->addGlobalVar("ADDRESS", mysql_result($res, 0, "address"));
      $tmpl->addGlobalVar("CITY", mysql_result($res, 0, "city"));
      $tmpl->addGlobalVar("POSTAL", mysql_result($res, 0, "postal"));
      $tmpl->addGlobalVar("PHONE", mysql_result($res, 0, "phone"));
      $tmpl->addGlobalVar("MOBILE", mysql_result($res, 0, "mobile"));
      $tmpl->addGlobalVar("EMAIL", mysql_result($res, 0, "email"));
      $tmpl->addGlobalVar("NICK_NAME", mysql_result($res, 0, "nick_name"));
      $tmpl->addGlobalVar("USER_NAME", mysql_result($res, 0, "user_name"));
      
      //Get available genders and select the one applicable to the current user
      $gender_result = DBQuery("SELECT id, gender FROM wmc_genders ORDER BY id ASC");
      $gender_num = mysql_num_rows($gender_result);
      for($i = 0; $i < $gender_num; $i++){
        if(mysql_result($gender_result, $i, "id") == mysql_result($res, 0, "gender_id")){
          $tmpl->addVar("gender", "SELECTED", "selected");
        } else{
          $tmpl->addVar("gender", "SELECTED", "");
        }
        $tmpl->addVar("gender", "GENDER", mysql_result($gender_result, $i, "gender"));
        $tmpl->addVar("gender", "GENDER_ID", mysql_result($gender_result, $i, "id"));
        $tmpl->parseTemplate("gender", "a");
      }

      //Get available countries and select the one applicable to the current user
      $country_result = DBQuery("SELECT Code, Name FROM country ORDER BY Name ASC");
      $country_num = mysql_num_rows($country_result);
      for($i = 0; $i < $country_num; $i++){
        if(mysql_result($country_result, $i, "Code") == mysql_result($res, 0, "country_code")){
          $tmpl->addVar("country", "SELECTED", "selected");
        } else{
          $tmpl->addVar("country", "SELECTED", "");
        }
        $tmpl->addVar("country", "COUNTRY", mysql_result($country_result, $i, "Name"));
        $tmpl->addVar("country", "COUNTRY_CODE", mysql_result($country_result, $i, "Code"));
        $tmpl->parseTemplate("country", "a");
      }

      //Focus on user name text field
      $tmpl->addVar("setTimeout", "COMMAND", "document.getElementById('my_settings_first_name').focus();");
      $tmpl->addVar("setTimeout", "MILLISECONDS", "0");
      $tmpl->parseTemplate("setTimeout", "a");

      //Javascript code to be called by the Update command
      //Only reason to keep this bit in the PHP file is for the overview
      //writing this on one line in the xml template is unreadable and not maintainable
      $update_command = "postData('my_settings_update.php', 'INFORMATION', 
      'my_settings_first_name=' + escape(encodeURI(document.getElementById('my_settings_first_name').value)) + " . 
      "'&amp;my_settings_last_name=' + escape(encodeURI(document.getElementById('my_settings_last_name').value)) + " . 
      "'&amp;my_settings_email=' + escape(encodeURI(document.getElementById('my_settings_email').value)) + " . 
      "'&amp;my_settings_gender_id=' + escape(encodeURI(document.getElementById('my_settings_gender_id').value)) + " .
      "'&amp;my_settings_address=' + escape(encodeURI(document.getElementById('my_settings_address').value)) + ".
      "'&amp;my_settings_city=' + escape(encodeURI(document.getElementById('my_settings_city').value)) + " .
      "'&amp;my_settings_country_code=' + escape(encodeURI(document.getElementById('my_settings_country_code').value)) + " .
      "'&amp;my_settings_postal=' + escape(encodeURI(document.getElementById('my_settings_postal').value)) + " .
      "'&amp;my_settings_phone=' + escape(encodeURI(document.getElementById('my_settings_phone').value)) + " .
      "'&amp;my_settings_mobile=' + escape(encodeURI(document.getElementById('my_settings_mobile').value)) + " .
      "'&amp;my_settings_nick_name=' + escape(encodeURI(document.getElementById('my_settings_nick_name').value)) + " .
      "'&amp;my_settings_user_name=' + escape(encodeURI(document.getElementById('my_settings_user_name').value)) + " .
      "'&amp;my_settings_old_password=' + escape(encodeURI(document.getElementById('my_settings_old_password').value)) + " .
      "'&amp;my_settings_new_password=' + escape(encodeURI(document.getElementById('my_settings_new_password').value)) + " .
      "'&amp;my_settings_new_password2=' + escape(encodeURI(document.getElementById('my_settings_new_password2').value)));";
      
      //Add the command to the template
      $tmpl->addGlobalVar("UPDATE_COMMAND", $update_command);
    } else{
      //Fade Information box in immediatly
      FadeIn($tmpl, "INFORMATION", 0);
      //Load standard information into the Information box immediatly
      $tmpl->addVar("setTimeout", "COMMAND", "getData('information.php?filename=information_security_level', 'INFORMATION')");
      $tmpl->addVar("setTimeout", "MILLISECONDS", "0");
      $tmpl->parseTemplate("setTimeout", "a");
    }
  } else{
    //Fade Information box in immediatly
    FadeIn($tmpl, "INFORMATION", 0);
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
