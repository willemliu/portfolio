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
	$template_file = "view_trailer.tmpl.xml";
  $tmpl->readTemplatesFromFile($template_file);
  $tmpl->addGlobalVar("FILE_NAME", $template_file);
  
  //Check login
  DBConnect();
  $res = DBQuery("SELECT id, security_level FROM wmc_users WHERE active='1' AND user_name='" . mysql_real_escape_string($_SESSION['username']) . "' AND password='" . mysql_real_escape_string($_SESSION['password']) . "'");

  $security_level = 0;

  //If logged in
  if(mysql_num_rows($res) > 0){
    $security_level = mysql_result($res, 0, "security_level");
    $section_result = DBQuery("SELECT security_level FROM wmc_sections_security_levels WHERE section='View Trailer'");
    //Check if security level is high enough for this action
    if(mysql_num_rows($section_result) > 0 && $security_level >= mysql_result($section_result, 0, "security_level")){
      FadeInExtra($tmpl, "VIEW_TRAILER", 0, 0, 99);
      $user_id = mysql_result($res, 0, "id");
      $trailer_id = htmlspecialchars(urlDecode($_POST['trailer_id']));

      //Set window as opened
      $window_check = DBQuery("SELECT id FROM wmc_users_windows WHERE user_id=" . $user_id . " AND window='VIEW_TRAILER'");
      if(mysql_num_rows($window_check) > 0){
        DBQuery("UPDATE wmc_users_windows SET opened='1', page='view_trailer.php', parameter='trailer_id=" . $trailer_id . "' WHERE user_id=" . $user_id . " AND window='VIEW_TRAILER'");
      } else{
        DBQuery("INSERT INTO wmc_users_windows (x, y, opened, page, parameter, window, user_id) VALUES ('80', '110', '1', 'view_trailer.php', 'trailer_id=" . $trailer_id . "', 'VIEW_TRAILER', '" . $user_id . "')");
      }
      
      $trailer_result = DBQuery("SELECT films.title, films_trailers.id, films_trailers.title, films_trailers.trailer, users.nick_name FROM wmc_films_trailers AS films_trailers, wmc_users AS users, wmc_films AS films WHERE films.id=films_trailers.film_id AND films_trailers.user_id=users.id AND films_trailers.id='" . $trailer_id . "'");
      if(mysql_num_rows($trailer_result) > 0){
        $tmpl->addGlobalVar("NICK_NAME", htmlspecialchars(mysql_result($trailer_result, 0, "users.nick_name")));
        $tmpl->addGlobalVar("ID", htmlspecialchars(mysql_result($trailer_result, 0, "films_trailers.id")));
        $tmpl->addGlobalVar("FILM", stripslashes(htmlspecialchars(mysql_result($trailer_result, 0, "films.title"))));
        $tmpl->addGlobalVar("TITLE", htmlspecialchars(mysql_result($trailer_result, 0, "films_trailers.title")));
        $tmpl->addGlobalVar("TRAILER", htmlspecialchars(mysql_result($trailer_result, 0, "films_trailers.trailer")));
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
