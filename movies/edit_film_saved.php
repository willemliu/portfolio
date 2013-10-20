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
  $template_file = "edit_film_saved.tmpl.xml";
  $tmpl->readTemplatesFromFile($template_file);
  $tmpl->addGlobalVar("FILE_NAME", $template_file);

  //Check login
  DBConnect();
  $res = DBQuery("SELECT id, security_level FROM wmc_users WHERE active='1' AND user_name='" . mysql_real_escape_string($_SESSION['username']) . "' AND password='" . mysql_real_escape_string($_SESSION['password']) . "'");

  $security_level = 0;

  //Fade Information box in immediatly
  FadeIn($tmpl, "INFORMATION", 0);

  //Disable the Update button immediatly
  $tmpl->addVar("setTimeout", "COMMAND", "document.getElementById('edit_film_save').disabled = true");
  $tmpl->addVar("setTimeout", "MILLISECONDS", "0");
  $tmpl->parseTemplate("setTimeout", "a");

  $edit_film_id = mysql_real_escape_string(utf8_decode(strip_tags(urlDecode($_POST['edit_film_id']))));
  $edit_film_title = mysql_real_escape_string(utf8_decode(strip_tags(urlDecode($_POST['edit_film_title']))));
  $edit_film_duration = mysql_real_escape_string(utf8_decode(strip_tags(urlDecode($_POST['edit_film_duration']))));
  $edit_film_color_id = mysql_real_escape_string(htmlspecialchars(urlDecode($_POST['edit_film_color_id'])));

  //If logged in
  if($_POST && mysql_num_rows($res) > 0){
    $user_id = mysql_result($res, 0, "id");
    $security_level = mysql_result($res, 0, "security_level");
    $section_result = DBQuery("SELECT security_level FROM wmc_sections_security_levels WHERE section='Edit Film'");
    $check_film = DBQuery("SELECT id, user_id FROM wmc_films WHERE id='" . $edit_film_id . "'");
    if(mysql_num_rows($check_film) == 0){
      $tmpl->addGlobalVar("INFORMATION", "Film does not exists.");
      //Fade Edit Films
      FadeOut($tmpl, "EDIT_FILM", 0);
      $tmpl->addVar("setTimeout", "COMMAND", "postData('window_close.php', '', 'window=EDIT_FILM')");
      $tmpl->addVar("setTimeout", "MILLISECONDS", "0");
      $tmpl->parseTemplate("setTimeout", "a");
    } else{
      //Check security level
      if((mysql_num_rows($section_result) > 0 && $security_level >= mysql_result($section_result, 0, "security_level")) || $user_id == mysql_result($check_film, 0, "user_id")){
        
        if($edit_film_title == ""){
          //Focus on user name text field
          $tmpl->addVar("setTimeout", "COMMAND", "document.getElementById('edit_film_title').focus();");
          $tmpl->addVar("setTimeout", "MILLISECONDS", "0");
          $tmpl->parseTemplate("setTimeout", "a");
          $tmpl->addGlobalVar("INFORMATION", "Film title cannot be empty.");
        } else if($edit_film_duration == ""){
          //Focus on user name text field
          $tmpl->addVar("setTimeout", "COMMAND", "document.getElementById('edit_film_duration').focus();");
          $tmpl->addVar("setTimeout", "MILLISECONDS", "0");
          $tmpl->parseTemplate("setTimeout", "a");
          $tmpl->addGlobalVar("INFORMATION", "Film duration cannot be empty.");
        } else{
          $check_film = DBQuery("SELECT id FROM wmc_films WHERE id='" . $edit_film_id . "'");
          $check_film = DBQuery("SELECT id FROM wmc_films WHERE title='" . $edit_film_title . "' AND duration='" . $edit_film_duration . "' AND color_id='" . $edit_film_color_id . "'");
          if(mysql_num_rows($check_film) == 0){
            DBQuery("UPDATE wmc_films SET title='" . $edit_film_title . "', duration='" . $edit_film_duration . "', color_id='" . $edit_film_color_id . "', user_id='" . $user_id . "' WHERE id='" . $edit_film_id . "'");
            //Reload the My Films content
            $parameter_result = DBQuery("SELECT page, parameter FROM wmc_users_windows WHERE opened='1' AND window='MY_FILMS' AND user_id='" . $user_id . "'");
            if(mysql_num_rows($parameter_result) > 0){
              $tmpl->addVar("setTimeout", "COMMAND", "postData('" . mysql_result($parameter_result, 0, "page") . "', 'MY_FILMS', '" . htmlspecialchars(mysql_result($parameter_result, 0, "parameter")) . "')");
              $tmpl->addVar("setTimeout", "MILLISECONDS", "0");
              $tmpl->parseTemplate("setTimeout", "a");
            }
      
            $tmpl->addGlobalVar("INFORMATION", "Film saved.");
            //Fade Edit Films
            FadeOut($tmpl, "EDIT_FILM", 0);
            $tmpl->addVar("setTimeout", "COMMAND", "postData('window_close.php', '', 'window=EDIT_FILM')");
            $tmpl->addVar("setTimeout", "MILLISECONDS", "0");
            $tmpl->parseTemplate("setTimeout", "a");
          } else{
            //Focus on user name text field
            $tmpl->addVar("setTimeout", "COMMAND", "document.getElementById('edit_film_title').focus();");
            $tmpl->addVar("setTimeout", "MILLISECONDS", "0");
            $tmpl->parseTemplate("setTimeout", "a");
            $tmpl->addGlobalVar("INFORMATION", "Film already exists.");
          }
        }
  
        //Enable the Update button after 1 second
        $tmpl->addVar("setTimeout", "COMMAND", "document.getElementById('edit_film_save').disabled = false");
        $tmpl->addVar("setTimeout", "MILLISECONDS", "1000");
        $tmpl->parseTemplate("setTimeout", "a");
      } else{
        //Load standard information into the Information box immediatly
        $tmpl->addVar("setTimeout", "COMMAND", "getData('information.php?filename=information_security_level', 'INFORMATION')");
        $tmpl->addVar("setTimeout", "MILLISECONDS", "0");
        $tmpl->parseTemplate("setTimeout", "a");
      }
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
