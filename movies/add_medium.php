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
	$template_file = "add_medium.tmpl.xml";
  $tmpl->readTemplatesFromFile($template_file);
  $tmpl->addGlobalVar("FILE_NAME", $template_file);
  
  //Check login
  DBConnect();
  $res = DBQuery("SELECT id, security_level FROM wmc_users WHERE active='1' AND user_name='" . mysql_real_escape_string($_SESSION['username']) . "' AND password='" . mysql_real_escape_string($_SESSION['password']) . "'");

  $security_level = 0;

  //If logged in
  if($_POST && mysql_num_rows($res) > 0){
    $security_level = mysql_result($res, 0, "security_level");
    $section_result = DBQuery("SELECT security_level FROM wmc_sections_security_levels WHERE section='Add Medium'");
    //Check if security level is high enough for this action
    if(mysql_num_rows($section_result) > 0 && $security_level >= mysql_result($section_result, 0, "security_level")){
      $user_id = mysql_result($res, 0, "id");
      FadeIn($tmpl, "ADD_MEDIUM", 0);
      
      $film_id = htmlspecialchars(urlDecode($_POST['film_id']));
      $film_result = DBQuery("SELECT title FROM wmc_films WHERE id=" . $film_id);
      
      //Set window as opened
      $window_check = DBQuery("SELECT id FROM wmc_users_windows WHERE user_id=" . $user_id . " AND window='ADD_MEDIUM'");
      if(mysql_num_rows($window_check) > 0){
        DBQuery("UPDATE wmc_users_windows SET opened='1', page='add_medium.php', parameter='film_id=" . $film_id . "' WHERE user_id=" . $user_id . " AND window='ADD_MEDIUM'");
      } else{
        DBQuery("INSERT INTO wmc_users_windows (x, y, opened, page, parameter, window, user_id) VALUES ('80', '110', '1', 'add_medium.php', 'film_id=" . $film_id . "', 'ADD_MEDIUM', '" . $user_id . "')");
      }

      if(mysql_num_rows($film_result) > 0){
        $tmpl->addGlobalVar("FILM_TITLE", stripslashes(htmlspecialchars(mysql_result($film_result, 0, "title"))));
      }
      
      $medium_result = DBQuery("SELECT id, medium FROM wmc_mediums ORDER BY medium ASC");
      $medium_num = mysql_num_rows($medium_result);
      for($i = 0; $i < $medium_num; $i++){
        $medium_id = mysql_result($medium_result, $i, "id");
        $check_medium_result = DBQuery("SELECT users_films_mediums.medium_id FROM wmc_users_films AS users_films, wmc_users_films_mediums AS users_films_mediums WHERE users_films.film_id='" . $film_id . "' AND users_films.user_id='" . $user_id . "' AND users_films_mediums.user_film_id=users_films.id AND users_films_mediums.medium_id='" . $medium_id . "'");
        if(mysql_num_rows($check_medium_result) > 0){
          $tmpl->addVar("show_selected_medium", "CONDITION", "true");
          $tmpl->addVar("selected_medium", "MEDIUM_ID", $medium_id);
          $tmpl->addVar("selected_medium", "MEDIUM", mysql_result($medium_result, $i, "medium"));
          $tmpl->parseTemplate("selected_medium", "a");
        } else{
          $tmpl->addVar("show_medium", "CONDITION", "true");
          $tmpl->addVar("medium", "MEDIUM_ID", $medium_id);
          $tmpl->addVar("medium", "MEDIUM", mysql_result($medium_result, $i, "medium"));
          $tmpl->parseTemplate("medium", "a");
        }
      }
      
      //Javascript code to be called by the Update command
      //Only reason to keep this bit in the PHP file is for the overview
      //writing this on one line in the xml template is unreadable and not maintainable
      $update_command = "postData('add_medium_added.php', 'INFORMATION', 
      'film_id=" . $film_id . "' + " . 
      "ListToUrl('add_medium_id[]'))";

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
