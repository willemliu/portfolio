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
	$template_file = "edit_film.tmpl.xml";
  $tmpl->readTemplatesFromFile($template_file);
  $tmpl->addGlobalVar("FILE_NAME", $template_file);
  
  //Check login
  DBConnect();
  $res = DBQuery("SELECT id, security_level FROM wmc_users WHERE active='1' AND user_name='" . mysql_real_escape_string($_SESSION['username']) . "' AND password='" . mysql_real_escape_string($_SESSION['password']) . "'");

  //Set default security_level of the user to 0
  $security_level = 0;

  //If logged in
  if(mysql_num_rows($res) > 0){
    $user_id = mysql_result($res, 0, "id");
    $film_id = mysql_real_escape_string(utf8_decode(strip_tags(urlDecode($_POST['film_id']))));
    if($film_id != ""){
      $film_result = DBQuery("SELECT title, duration, color_id, user_id FROM wmc_films WHERE id='" . $film_id . "'");
      $film_num = mysql_num_rows($film_result);
      if($film_num > 0){
        //Set security_level
        $security_level = mysql_result($res, 0, "security_level");
        $section_result = DBQuery("SELECT security_level FROM wmc_sections_security_levels WHERE section='Edit Film'");
        //Check if security level is high enough for this action
        if((mysql_num_rows($section_result) > 0 && $security_level >= mysql_result($section_result, 0, "security_level")) || $user_id == mysql_result($film_result, 0, "user_id")){
          FadeOut($tmpl, "INFORMATION", 0);
          FadeIn($tmpl, "EDIT_FILM", 0);
          //Set window as opened
          $window_check = DBQuery("SELECT id FROM wmc_users_windows WHERE user_id=" . $user_id . " AND window='EDIT_FILM'");
          if(mysql_num_rows($window_check) > 0){
            DBQuery("UPDATE wmc_users_windows SET opened='1', page='edit_film.php', parameter='film_id=" . $film_id . "' WHERE user_id=" . $user_id . " AND window='EDIT_FILM'");
          } else{
            DBQuery("INSERT INTO wmc_users_windows (x, y, opened, page, parameter, window, user_id) VALUES ('80', '110', '1', 'edit_film.php', 'film_id=" . $film_id . "', 'EDIT_FILM', '" . $user_id . "')");
          }

          $tmpl->addGlobalVar("FILM_ID", $film_id);
          $tmpl->addGlobalVar("TITLE", stripslashes(mysql_result($film_result, 0, "title")));
          $tmpl->addGlobalVar("DURATION", mysql_result($film_result, 0, "duration"));
          $film_color_id = mysql_result($film_result, 0, "color_id");
          
          $color_result = DBQuery("SELECT id, color FROM wmc_colors ORDER BY id ASC");
          $color_num = mysql_num_rows($color_result);
          for($i = 0; $i < $color_num; $i++){
            $color_id = mysql_result($color_result, $i, "id");
            if($color_id == $film_color_id){
              $tmpl->addVar("color", "COLOR_ID", mysql_result($color_result, $i, "id"));
              $tmpl->addVar("color", "SELECTED", "SELECTED");
              $tmpl->addVar("color", "COLOR_NAME", mysql_result($color_result, $i, "color"));
              $tmpl->parseTemplate("color", "a");
            } else{
              $tmpl->addVar("color", "COLOR_ID", mysql_result($color_result, $i, "id"));
              $tmpl->addVar("color", "SELECTED", "");
              $tmpl->addVar("color", "COLOR_NAME", mysql_result($color_result, $i, "color"));
              $tmpl->parseTemplate("color", "a");
            }
          }
    
          //Focus on user name text field
          $tmpl->addVar("setTimeout", "COMMAND", "document.getElementById('edit_film_title').focus();");
          $tmpl->addVar("setTimeout", "MILLISECONDS", "0");
          $tmpl->parseTemplate("setTimeout", "a");
    
          //Javascript code to be called by the Update command
          //Only reason to keep this bit in the PHP file is for the overview
          //writing this on one line in the xml template is unreadable and not maintainable
          $update_command = "postData('edit_film_saved.php', 'INFORMATION', 
          'edit_film_title=' + escape(encodeURI(document.getElementById('edit_film_title').value)) + " . 
          "'&amp;edit_film_id=' + escape(encodeURI(document.getElementById('edit_film_id').value)) + " . 
          "'&amp;edit_film_duration=' + escape(encodeURI(document.getElementById('edit_film_duration').value)) + " . 
          "'&amp;edit_film_color_id=' + escape(encodeURI(document.getElementById('edit_film_color_id').value)))";
          
          //Add the command to the template
          $tmpl->addGlobalVar("UPDATE_COMMAND", $update_command);
        } else{
          //Fade Information box in immediatly
          FadeIn($tmpl, "INFORMATION", 0);
          $tmpl->addGlobalVar("INFORMATION", "Film does not exist.");
        }
      } else{
        //Fade Information box in immediatly
        FadeIn($tmpl, "INFORMATION", 0);
        $tmpl->addGlobalVar("INFORMATION", "No film selected.");
      }
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
