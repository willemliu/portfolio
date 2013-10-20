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
	$template_file = "new_film.tmpl.xml";
  $tmpl->readTemplatesFromFile($template_file);
  $tmpl->addGlobalVar("FILE_NAME", $template_file);
  
  //Check login
  DBConnect();
  $res = DBQuery("SELECT id, security_level FROM wmc_users WHERE active='1' AND user_name='" . mysql_real_escape_string($_SESSION['username']) . "' AND password='" . mysql_real_escape_string($_SESSION['password']) . "'");

  //Set default security_level of the user to 0
  $security_level = 0;

  //If logged in
  if(mysql_num_rows($res) > 0){
    //Set security_level
    $security_level = mysql_result($res, 0, "security_level");
    $section_result = DBQuery("SELECT security_level FROM wmc_sections_security_levels WHERE section='New Film'");
    //Check if security level is high enough for this action
    if(mysql_num_rows($section_result) > 0 && $security_level >= mysql_result($section_result, 0, "security_level")){
      FadeOut($tmpl, "INFORMATION", 0);
      $user_id = mysql_result($res, 0, "id");
      FadeIn($tmpl, "NEW_FILM", 0);

      //Set window as opened
      $window_check = DBQuery("SELECT id FROM wmc_users_windows WHERE user_id=" . $user_id . " AND window='NEW_FILM'");
      if(mysql_num_rows($window_check) > 0){
        DBQuery("UPDATE wmc_users_windows SET opened='1', page='new_film.php' WHERE user_id=" . $user_id . " AND window='NEW_FILM'");
      } else{
        DBQuery("INSERT INTO wmc_users_windows (x, y, opened, page, window, user_id) VALUES ('80', '110', '1', 'new_film.php', 'NEW_FILM', '" . $user_id . "')");
      }
      
      $color_result = DBQuery("SELECT id, color FROM wmc_colors ORDER BY id ASC");
      $color_num = mysql_num_rows($color_result);
      for($i = 0; $i < $color_num; $i++){
        $tmpl->addVar("color", "COLOR_ID", mysql_result($color_result, $i, "id"));
        $tmpl->addVar("color", "COLOR_NAME", mysql_result($color_result, $i, "color"));
        $tmpl->parseTemplate("color", "a");
      }

      //Focus on user name text field
      $tmpl->addVar("setTimeout", "COMMAND", "document.getElementById('new_film_title').focus();");
      $tmpl->addVar("setTimeout", "MILLISECONDS", "0");
      $tmpl->parseTemplate("setTimeout", "a");

      //Javascript code to be called by the Update command
      //Only reason to keep this bit in the PHP file is for the overview
      //writing this on one line in the xml template is unreadable and not maintainable
      $update_command = "postData('new_film_saved.php', 'INFORMATION', 
      'new_film_title=' + escape(encodeURI(document.getElementById('new_film_title').value)) + " . 
      "'&amp;new_film_duration=' + escape(encodeURI(document.getElementById('new_film_duration').value)) + " . 
      "'&amp;new_film_color_id=' + escape(encodeURI(document.getElementById('new_film_color_id').value)))";
      
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
