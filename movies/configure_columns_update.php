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
  $template_file = "configure_columns_update.tmpl.xml";
  $tmpl->readTemplatesFromFile($template_file);
  $tmpl->addGlobalVar("FILE_NAME", $template_file);

  //Check login
  DBConnect();
  $res = DBQuery("SELECT id, security_level FROM wmc_users WHERE active='1' AND user_name='" . mysql_real_escape_string($_SESSION['username']) . "' AND password='" . mysql_real_escape_string($_SESSION['password']) . "'");
  
  $security_level = 0;

  //Fade Information box in immediatly
  FadeIn($tmpl, "INFORMATION", 0);

  //Disable the Update button immediatly
  $tmpl->addVar("setTimeout", "COMMAND", "document.getElementById('configure_columns_update').disabled = true");
  $tmpl->addVar("setTimeout", "MILLISECONDS", "0");
  $tmpl->parseTemplate("setTimeout", "a");

  //If logged in
  if($_POST && mysql_num_rows($res) > 0){
    $security_level = mysql_result($res, 0, "security_level");
    $section_result = DBQuery("SELECT security_level FROM wmc_sections_security_levels WHERE section='Configure Columns'");
    //Check security level
    if(mysql_num_rows($section_result) > 0 && $security_level >= mysql_result($section_result, 0, "security_level")){
      $user_id = mysql_result($res, 0, "id");

      $configure_columns_title = mysql_real_escape_string(htmlspecialchars(urlDecode($_POST['configure_columns_title'])));
      $configure_columns_duration = mysql_real_escape_string(htmlspecialchars(urlDecode($_POST['configure_columns_duration'])));
      $configure_columns_sound_mix = mysql_real_escape_string(htmlspecialchars(urlDecode($_POST['configure_columns_sound_mix'])));
      $configure_columns_subtitle = mysql_real_escape_string(htmlspecialchars(urlDecode($_POST['configure_columns_subtitle'])));
      $configure_columns_genre = mysql_real_escape_string(htmlspecialchars(urlDecode($_POST['configure_columns_genre'])));
      $configure_columns_medium = mysql_real_escape_string(htmlspecialchars(urlDecode($_POST['configure_columns_medium'])));
      $configure_columns_region = mysql_real_escape_string(htmlspecialchars(urlDecode($_POST['configure_columns_region'])));
      $configure_columns_color = mysql_real_escape_string(htmlspecialchars(urlDecode($_POST['configure_columns_color'])));
      $configure_columns_aspect_ratio = mysql_real_escape_string(htmlspecialchars(urlDecode($_POST['configure_columns_aspect_ratio'])));
      $configure_columns_cast = mysql_real_escape_string(htmlspecialchars(urlDecode($_POST['configure_columns_cast'])));
      $configure_columns_lend_to = mysql_real_escape_string(htmlspecialchars(urlDecode($_POST['configure_columns_lend_to'])));
      $configure_columns_keyword = mysql_real_escape_string(htmlspecialchars(urlDecode($_POST['configure_columns_keyword'])));
      $configure_columns_trailer = mysql_real_escape_string(htmlspecialchars(urlDecode($_POST['configure_columns_trailer'])));
      
      DBQuery("UPDATE wmc_users_films_columns SET " .
      "title='" . $configure_columns_title . "', " .
      "duration='" . $configure_columns_duration . "', " .
      "sound_mix='" . $configure_columns_sound_mix . "', " .
      "subtitle='" . $configure_columns_subtitle . "', " .
      "genre='" . $configure_columns_genre . "', " .
      "medium='" . $configure_columns_medium . "', " .
      "region='" . $configure_columns_region . "', " .
      "color='" . $configure_columns_color . "', " .
      "cast='" . $configure_columns_cast . "', " .
      "keyword='" . $configure_columns_keyword . "', " .
      "lend_to='" . $configure_columns_lend_to . "', " .
      "trailer='" . $configure_columns_trailer . "', " .
      "aspect_ratio='" . $configure_columns_aspect_ratio . "' WHERE user_id=" . $user_id);

      $tmpl->addGlobalVar("INFORMATION", "Columns configured.");

      //Fade Configure Columns out
      FadeOut($tmpl, "CONFIGURE_COLUMNS", 0);
      $tmpl->addVar("setTimeout", "COMMAND", "postData('window_close.php', '', 'window=CONFIGURE_COLUMNS')");
      $tmpl->addVar("setTimeout", "MILLISECONDS", "0");
      $tmpl->parseTemplate("setTimeout", "a");

      //Reload the My Films content
      $parameter_result = DBQuery("SELECT page, parameter FROM wmc_users_windows WHERE opened='1' AND window='MY_FILMS' AND user_id='" . $user_id . "'");
      if(mysql_num_rows($parameter_result) > 0){
        $tmpl->addVar("setTimeout", "COMMAND", "postData('" . mysql_result($parameter_result, 0, "page") . "', 'MY_FILMS', '" . htmlspecialchars(mysql_result($parameter_result, 0, "parameter")) . "')");
        $tmpl->addVar("setTimeout", "MILLISECONDS", "0");
        $tmpl->parseTemplate("setTimeout", "a");
      }

      //Enable the Update button after 1 second
      $tmpl->addVar("setTimeout", "COMMAND", "document.getElementById('configure_columns_update').disabled = false");
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

  if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')){
    $content = gzencode($tmpl->getParsedTemplate("body"), 9);
    echo $content;
  } else{
    $tmpl->displayParsedTemplate("body");
  }
?>
