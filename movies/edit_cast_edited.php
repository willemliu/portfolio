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
  $template_file = "add_cast_added.tmpl.xml";
  $tmpl->readTemplatesFromFile($template_file);
  $tmpl->addGlobalVar("FILE_NAME", $template_file);

  //Check login
  DBConnect();
  $res = DBQuery("SELECT id, security_level FROM wmc_users WHERE active='1' AND user_name='" . mysql_real_escape_string($_SESSION['username']) . "' AND password='" . mysql_real_escape_string($_SESSION['password']) . "'");
  
  $security_level = 0;

  //Fade Information box in immediatly
  FadeIn($tmpl, "INFORMATION", 0);

  //Disable the Update button immediatly
  $tmpl->addVar("setTimeout", "COMMAND", "document.getElementById('edit_cast_edit').disabled = true");
  $tmpl->addVar("setTimeout", "MILLISECONDS", "0");
  $tmpl->parseTemplate("setTimeout", "a");

  //If logged in
  if($_POST && mysql_num_rows($res) > 0){
    $security_level = mysql_result($res, 0, "security_level");
    $section_result = DBQuery("SELECT security_level FROM wmc_sections_security_levels WHERE section='Edit Cast'");
    //Check security level
    if(mysql_num_rows($section_result) > 0 && $security_level >= mysql_result($section_result, 0, "security_level")){
      $user_id = mysql_result($res, 0, "id");

      $cast_id = mysql_real_escape_string(htmlspecialchars(strip_tags(urlDecode($_POST['view_cast_cast_id']))));
      $cast_name = mysql_real_escape_string(utf8_decode(htmlspecialchars(strip_tags(urlDecode($_POST['view_cast_name'])))));
      $cast_birth_date = mysql_real_escape_string(htmlspecialchars(strip_tags(urlDecode($_POST['view_cast_birth_date']))));
      $cast_gender_id = mysql_real_escape_string(htmlspecialchars(strip_tags(urlDecode($_POST['view_cast_gender_id']))));
      $cast_description = mysql_real_escape_string(utf8_decode(htmlspecialchars(strip_tags(urlDecode($_POST['view_cast_description'])))));

      if($cast_name != ""){
        if($cast_id != ""){
          DBQuery("UPDATE wmc_cast SET name='" . $cast_name . "', birth_date='" . $cast_birth_date . "', gender_id='" . $cast_gender_id . "', description='" . $cast_description . "' WHERE id='" . $cast_id . "'");
          FadeOut($tmpl, "VIEW_CAST", 0);
        }
        $tmpl->addGlobalVar("INFORMATION", "Cast saved to your database.");
      } else{
        $tmpl->addGlobalVar("INFORMATION", "Name cannot be empty.");
        //Disable the Update button immediatly
        $tmpl->addVar("setTimeout", "COMMAND", "document.getElementById('edit_cast_edit').disabled = false");
        $tmpl->addVar("setTimeout", "MILLISECONDS", "1000");
        $tmpl->parseTemplate("setTimeout", "a");
      }

      //Reload the My Films content
      $parameter_result = DBQuery("SELECT page, parameter FROM wmc_users_windows WHERE window='MY_FILMS' AND user_id='" . $user_id . "'");
      if(mysql_num_rows($parameter_result) > 0){
        $tmpl->addVar("setTimeout", "COMMAND", "postData('" . mysql_result($parameter_result, 0, "page") . "', 'MY_FILMS', '" . htmlspecialchars(mysql_result($parameter_result, 0, "parameter")) . "')");
        $tmpl->addVar("setTimeout", "MILLISECONDS", "0");
        $tmpl->parseTemplate("setTimeout", "a");
      }
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
