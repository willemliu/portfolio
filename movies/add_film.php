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
	$template_file = "add_film.tmpl.xml";
  $tmpl->readTemplatesFromFile($template_file);
  $tmpl->addGlobalVar("FILE_NAME", $template_file);
  
  //Check login
  DBConnect();
  $res = DBQuery("SELECT id, security_level FROM wmc_users WHERE active='1' AND user_name='" . mysql_real_escape_string($_SESSION['username']) . "' AND password='" . mysql_real_escape_string($_SESSION['password']) . "'");

  $security_level = 0;

  //If logged in
  if(mysql_num_rows($res) > 0){
    $security_level = mysql_result($res, 0, "security_level");
    $section_result = DBQuery("SELECT security_level FROM wmc_sections_security_levels WHERE section='Add Film'");
    //Check if security level is high enough for this action
    if(mysql_num_rows($section_result) > 0 && $security_level >= mysql_result($section_result, 0, "security_level")){
      $user_id = mysql_result($res, 0, "id");
      FadeIn($tmpl, "ADD_FILM", 0);
  
      //Set window as opened
      $window_check = DBQuery("SELECT id FROM wmc_users_windows WHERE user_id=" . $user_id . " AND window='ADD_FILM'");
      if(mysql_num_rows($window_check) > 0){
        DBQuery("UPDATE wmc_users_windows SET opened='1', page='add_film.php' WHERE user_id=" . $user_id . " AND window='ADD_FILM'");
      } else{
        DBQuery("INSERT INTO wmc_users_windows (x, y, opened, page, window, user_id) VALUES ('80', '110', '1', 'add_film.php', 'ADD_FILM', '" . $user_id . "')");
      }
  
      $films_result = DBQuery("SELECT wmc_films.id, wmc_films.title, wmc_films.duration, wmc_colors.color FROM wmc_films, wmc_colors WHERE wmc_films.color_id=wmc_colors.id AND (SELECT COUNT(wmc_users_films.film_id) FROM wmc_users_films WHERE wmc_users_films.user_id='" . $user_id . "' AND wmc_users_films.film_id=wmc_films.id)=0 ORDER BY wmc_films.title ASC");
      $films_num = mysql_num_rows($films_result);
      for($i = 0; $i < $films_num; $i++){
        $tmpl->addVar("film", "FILM_ID", mysql_result($films_result, $i, "wmc_films.id"));
        $tmpl->addVar("film", "FILM_TITLE", stripslashes(htmlspecialchars(mysql_result($films_result, $i, "wmc_films.title"))));
        $tmpl->addVar("film", "FILM_DURATION", mysql_result($films_result, $i, "wmc_films.duration"));
        $tmpl->addVar("film", "FILM_COLOR", mysql_result($films_result, $i, "wmc_colors.color"));
        $tmpl->parseTemplate("film", "a");
      }
      
      if($films_num == 0){
        $tmpl->addVar("setTimeout", "COMMAND", "document.getElementById('add_film_add').disabled = true;");
        $tmpl->addVar("setTimeout", "MILLISECONDS", "0");
        $tmpl->parseTemplate("setTimeout", "a");
      }
      
      $medium_result = DBQuery("SELECT id, medium FROM wmc_mediums ORDER BY id ASC");
      $medium_num = mysql_num_rows($medium_result);
      for($i = 0; $i < $medium_num; $i++){
        $tmpl->addVar("medium", "MEDIUM_ID", mysql_result($medium_result, $i, "id"));
        $tmpl->addVar("medium", "MEDIUM", mysql_result($medium_result, $i, "medium"));
        $tmpl->parseTemplate("medium", "a");
      }
      
      if($films_num > 0){
        $tmpl->addVar("show_films", "CONDITION", "true");
        $tmpl->addVar("show_mediums", "CONDITION", "true");
        $tmpl->addVar("show_film_details", "CONDITION", "true");
      }
      
      //Javascript code to be called by the Update command
      //Only reason to keep this bit in the PHP file is for the overview
      //writing this on one line in the xml template is unreadable and not maintainable
      $update_command = "postData('add_film_added.php', 'INFORMATION', 
      'add_film_film_id=' + escape(encodeURI(document.getElementById('add_film_film_id').value)) + " . 
      "ArrayToUrl('add_film_medium_id[]'))";

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
