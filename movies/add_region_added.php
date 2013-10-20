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
  $template_file = "add_region_added.tmpl.xml";
  $tmpl->readTemplatesFromFile($template_file);
  $tmpl->addGlobalVar("FILE_NAME", $template_file);

  //Check login
  DBConnect();
  $res = DBQuery("SELECT id, security_level FROM wmc_users WHERE active='1' AND user_name='" . mysql_real_escape_string($_SESSION['username']) . "' AND password='" . mysql_real_escape_string($_SESSION['password']) . "'");
  
  $security_level = 0;

  //Fade Information box in immediatly
  FadeIn($tmpl, "INFORMATION", 0);

  //Disable the Update button immediatly
  $tmpl->addVar("setTimeout", "COMMAND", "document.getElementById('add_region_add').disabled = true");
  $tmpl->addVar("setTimeout", "MILLISECONDS", "0");
  $tmpl->parseTemplate("setTimeout", "a");

  //If logged in
  if($_POST && mysql_num_rows($res) > 0){
    $security_level = mysql_result($res, 0, "security_level");
    $section_result = DBQuery("SELECT security_level FROM wmc_sections_security_levels WHERE section='Add Region'");
    //Check security level
    if(mysql_num_rows($section_result) > 0 && $security_level >= mysql_result($section_result, 0, "security_level")){
      $user_id = mysql_result($res, 0, "id");

      $film_id = mysql_real_escape_string(htmlspecialchars(urlDecode($_POST['film_id'])));

      $user_film_result = DBQuery("SELECT id FROM wmc_users_films WHERE user_id='" . $user_id . "' AND film_id='" . $film_id . "'");
      $user_film_num = mysql_num_rows($user_film_result);
      if(mysql_num_rows($user_film_result) > 0){
        $user_film_id = mysql_result($user_film_result, 0, "id");
        DBQuery("DELETE FROM wmc_users_films_regions WHERE user_film_id='" . $user_film_id . "'");
        DBQuery("DELETE FROM wmc_films_regions WHERE film_id='" . $film_id . "' AND user_id=" . $user_id);
        if(count($_POST['add_region_id']) > 0){
          foreach($_POST['add_region_id'] as $key => $val){
            $region_id = mysql_real_escape_string(utf8_decode(htmlspecialchars(urlDecode($val))));
            DBQuery("INSERT INTO wmc_users_films_regions (user_film_id, region_id) VALUES ('" . $user_film_id . "', '" . $region_id . "')");
            DBQuery("INSERT INTO wmc_films_regions (film_id, region_id, user_id) VALUES ('" . $film_id . "', '" . $region_id . "', '" . $user_id . "')");
          }
        }
      }
      
      //Fade out
      FadeOut($tmpl, "ADD_REGION", 0);
      $tmpl->addVar("setTimeout", "COMMAND", "postData('window_close.php', '', 'window=ADD_REGION')");
      $tmpl->addVar("setTimeout", "MILLISECONDS", "0");
      $tmpl->parseTemplate("setTimeout", "a");
      
      $tmpl->addGlobalVar("INFORMATION", "Regions added to your film.");

      //Reload the My Films content
      $parameter_result = DBQuery("SELECT page, parameter FROM wmc_users_windows WHERE opened='1' AND window='MY_FILMS' AND user_id='" . $user_id . "'");
      if(mysql_num_rows($parameter_result) > 0){
        $tmpl->addVar("setTimeout", "COMMAND", "postData('" . mysql_result($parameter_result, 0, "page") . "', 'MY_FILMS', '" . htmlspecialchars(mysql_result($parameter_result, 0, "parameter")) . "')");
        $tmpl->addVar("setTimeout", "MILLISECONDS", "0");
        $tmpl->parseTemplate("setTimeout", "a");
      }

      //Enable the Update button after 1 second
      $tmpl->addVar("setTimeout", "COMMAND", "document.getElementById('add_region_add').disabled = false");
      $tmpl->addVar("setTimeout", "MILLISECONDS", "1000");
      $tmpl->parseTemplate("setTimeout", "a");
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

  $content = gzencode($tmpl->getParsedTemplate("body"), 9);
  echo $content;
?>
