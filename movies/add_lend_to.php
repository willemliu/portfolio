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
	$template_file = "add_lend_to.tmpl.xml";
  $tmpl->readTemplatesFromFile($template_file);
  $tmpl->addGlobalVar("FILE_NAME", $template_file);
  
  //Check login
  DBConnect();
  $res = DBQuery("SELECT id, security_level FROM wmc_users WHERE active='1' AND user_name='" . mysql_real_escape_string($_SESSION['username']) . "' AND password='" . mysql_real_escape_string($_SESSION['password']) . "'");

  $security_level = 0;

  //If logged in
  if($_POST && mysql_num_rows($res) > 0){
    $security_level = mysql_result($res, 0, "security_level");
    $section_result = DBQuery("SELECT security_level FROM wmc_sections_security_levels WHERE section='Add Lend To'");
    //Check if security level is high enough for this action
    if(mysql_num_rows($section_result) > 0 && $security_level >= mysql_result($section_result, 0, "security_level")){
      $user_id = mysql_result($res, 0, "id");
      FadeIn($tmpl, "ADD_LEND_TO", 0);
      
      $film_id = htmlspecialchars(urlDecode($_POST['film_id']));
      $film_result = DBQuery("SELECT title FROM wmc_films WHERE id=" . $film_id);
      
      //Set window as opened
      $window_check = DBQuery("SELECT id FROM wmc_users_windows WHERE user_id=" . $user_id . " AND window='ADD_LEND_TO'");
      if(mysql_num_rows($window_check) > 0){
        DBQuery("UPDATE wmc_users_windows SET opened='1', page='add_lend_to.php', parameter='film_id=" . $film_id . "' WHERE user_id=" . $user_id . " AND window='ADD_LEND_TO'");
      } else{
        DBQuery("INSERT INTO wmc_users_windows (x, y, opened, page, parameter, window, user_id) VALUES ('80', '110', '1', 'add_lend_to.php', 'film_id=" . $film_id . "', 'ADD_LEND_TO', '" . $user_id . "')");
      }

      //Focus on text field
      $tmpl->addVar("setTimeout", "COMMAND", "document.getElementById('add_lend_to_text').focus();");
      $tmpl->addVar("setTimeout", "MILLISECONDS", "0");
      $tmpl->parseTemplate("setTimeout", "a");      

      if(mysql_num_rows($film_result) > 0){
        $tmpl->addGlobalVar("FILM_TITLE", stripslashes(htmlspecialchars(mysql_result($film_result, 0, "title"))));
      }
      
      $check_lend_to_result = DBQuery("SELECT id, lend_to FROM wmc_users_films_lend_to WHERE film_id='" . $film_id . "' AND user_id='" . $user_id . "'");
      $check_lend_to_num = mysql_num_rows($check_lend_to_result);
      for($i = 0; $i < $check_lend_to_num; $i++){
        $tmpl->addVar("show_selected_lend_to", "CONDITION", "true");
        $tmpl->addVar("selected_lend_to", "LEND_TO_ID", mysql_result($check_lend_to_result, $i, "lend_to"));
        $tmpl->addVar("selected_lend_to", "LEND_TO", mysql_result($check_lend_to_result, $i, "lend_to"));
        $tmpl->parseTemplate("selected_lend_to", "a");
      }
              
      //Javascript code to be called by the Update command
      //Only reason to keep this bit in the PHP file is for the overview
      //writing this on one line in the xml template is unreadable and not maintainable
      $update_command = "postData('add_lend_to_added.php', 'INFORMATION', 
      'film_id=" . $film_id . "' + " . 
      "ListToUrl('add_lend_to_id[]'))";

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
