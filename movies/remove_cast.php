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
  $template_file = "remove_cast.tmpl.xml";
  $tmpl->readTemplatesFromFile($template_file);
  $tmpl->addGlobalVar("FILE_NAME", $template_file);

  //Check login
  DBConnect();
  $res = DBQuery("SELECT id, security_level FROM wmc_users WHERE active='1' AND user_name='" . mysql_real_escape_string($_SESSION['username']) . "' AND password='" . mysql_real_escape_string($_SESSION['password']) . "'");
  
  $security_level = 0;

  //If logged in
  if($_POST && mysql_num_rows($res) > 0){
    $security_level = mysql_result($res, 0, "security_level");
    $section_result = DBQuery("SELECT security_level FROM wmc_sections_security_levels WHERE section='Remove Cast'");
    $security_check = false;
    $user_id = mysql_result($res, 0, "id");
    
    //Check security level
    if(mysql_num_rows($section_result) > 0 && $security_level >= mysql_result($section_result, 0, "security_level")){
      $security_check = true;
    }
    
    $film_id = mysql_real_escape_string(htmlspecialchars(urlDecode($_POST['film_id'])));
    $cast_id = mysql_real_escape_string(htmlspecialchars(urlDecode($_POST['cast_id'])));
    $users_films_result = DBQuery("SELECT id, user_id FROM wmc_films_cast WHERE film_id='" . $film_id . "' AND cast_id='" . $cast_id . "'");
    if(mysql_num_rows($users_films_result) > 0){
      if(mysql_result($users_films_result, 0, "user_id") == $user_id || $security_check){
        $user_id = mysql_result($res, 0, "id");
  
        DBQuery("DELETE FROM wmc_films_cast WHERE film_id='" . $film_id . "' AND cast_id='" . $cast_id . "'");
        
        //Fade Information box in immediatly
        FadeIn($tmpl, "INFORMATION", 0);
        $tmpl->addGlobalVar("INFORMATION", "Cast removed from movie.");
  
        //Fade View Trailer
        FadeOut($tmpl, "VIEW_CAST", 0);
        $tmpl->addVar("setTimeout", "COMMAND", "postData('window_close.php', '', 'window=VIEW_CAST')");
        $tmpl->addVar("setTimeout", "MILLISECONDS", "0");
        $tmpl->parseTemplate("setTimeout", "a");

        //Reload the My Films content
        $parameter_result = DBQuery("SELECT page, parameter FROM wmc_users_windows WHERE opened='1' AND window='MY_FILMS' AND user_id='" . $user_id . "'");
        if(mysql_num_rows($parameter_result) > 0){
          $tmpl->addVar("setTimeout", "COMMAND", "postData('" . mysql_result($parameter_result, 0, "page") . "', 'MY_FILMS', '" . htmlspecialchars(mysql_result($parameter_result, 0, "parameter")) . "')");
          $tmpl->addVar("setTimeout", "MILLISECONDS", "0");
          $tmpl->parseTemplate("setTimeout", "a");
        }
      } else{
        FadeIn($tmpl, "INFORMATION", 0);
        $tmpl->addGlobalVar("INFORMATION", "You can only delete the cast you've submitted.");
      }
    } else{
      FadeIn($tmpl, "INFORMATION", 0);
      $tmpl->addGlobalVar("INFORMATION", "Cast not found in the database.");
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
