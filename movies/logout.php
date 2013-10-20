<?php
	session_start();
	
	include_once("./includes/load_cookies.php");

  $_COOKIE['username'] = "";
  $_COOKIE['password'] = "";
  setcookie("username", "", time()-3600, "");
  setcookie("password", "", time()-3600, "");

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
	$template_file = "logged_out.tmpl.xml";
  $tmpl->readTemplatesFromFile($template_file);
  $tmpl->addGlobalVar("FILE_NAME", $template_file);

  //Check login
  DBConnect();
  $res = DBQuery("SELECT id, security_level FROM wmc_users WHERE active='1' AND user_name='" . mysql_real_escape_string($_SESSION['username']) . "' AND password='" . mysql_real_escape_string($_SESSION['password']) . "'");

  $security_level = 0;

  // Content
  FadeIn($tmpl, "LOGIN", 0);

  //If logged in
  if(mysql_num_rows($res) > 0){
    $user_id = mysql_result($res, 0, "id");
    //Close all windows
    $window_check = DBQuery("SELECT id, page, window FROM wmc_users_windows WHERE opened='1' AND user_id='" . $user_id . "'");
    $window_num = mysql_num_rows($window_check);
    for($i = 0; $i < $window_num; $i++){
      FadeOut($tmpl, mysql_result($window_check, $i, "window"), 0);
    }
  }

  $_SESSION['username'] = "";
  $_SESSION['password'] = "";

  //Focus on user name text field
  $tmpl->addVar("setTimeout", "COMMAND", "document.getElementById('username').focus();");
  $tmpl->addVar("setTimeout", "MILLISECONDS", "0");
  $tmpl->parseTemplate("setTimeout", "a");

  //Disable Login Button
  $tmpl->addVar("setTimeout", "COMMAND", "document.getElementById('login_button').disabled = true");
  $tmpl->addVar("setTimeout", "MILLISECONDS", "0");
  $tmpl->parseTemplate("setTimeout", "a");

  //Enable Login Button
  $tmpl->addVar("setTimeout", "COMMAND", "document.getElementById('login_button').disabled = false");
  $tmpl->addVar("setTimeout", "MILLISECONDS", "1000");
  $tmpl->parseTemplate("setTimeout", "a");

  //Parse Menu
  $tmpl->addVar("setTimeout", "COMMAND", "getData('menu.php', 'MENU')");
  $tmpl->addVar("setTimeout", "MILLISECONDS", "0");
  $tmpl->parseTemplate("setTimeout", "a");

  //Set Menu position
  $tmpl->addVar("setTimeout", "COMMAND", "document.getElementById('MENU').style.top = '80px'");
  $tmpl->addVar("setTimeout", "MILLISECONDS", "0");
  $tmpl->parseTemplate("setTimeout", "a");
  $tmpl->addVar("setTimeout", "COMMAND", "document.getElementById('MENU').style.left = '0px'");
  $tmpl->addVar("setTimeout", "MILLISECONDS", "0");
  $tmpl->parseTemplate("setTimeout", "a");

  //Set Information position
  $tmpl->addVar("setTimeout", "COMMAND", "document.getElementById('INFORMATION').style.top = '80px'");
  $tmpl->addVar("setTimeout", "MILLISECONDS", "0");
  $tmpl->parseTemplate("setTimeout", "a");
  $tmpl->addVar("setTimeout", "COMMAND", "document.getElementById('INFORMATION').style.left = '110px'");
  $tmpl->addVar("setTimeout", "MILLISECONDS", "0");
  $tmpl->parseTemplate("setTimeout", "a");

  //Set Members List position
  $tmpl->addVar("setTimeout", "COMMAND", "document.getElementById('MEMBERS_LIST').style.top = '80px'");
  $tmpl->addVar("setTimeout", "MILLISECONDS", "0");
  $tmpl->parseTemplate("setTimeout", "a");
  $tmpl->addVar("setTimeout", "COMMAND", "document.getElementById('MEMBERS_LIST').style.left = '110px'");
  $tmpl->addVar("setTimeout", "MILLISECONDS", "0");
  $tmpl->parseTemplate("setTimeout", "a");

  //Write result successful message in Information box
  $tmpl->addVar("setTimeout", "COMMAND", "getData('information.php?filename=information_logout_success', 'INFORMATION')");
  $tmpl->addVar("setTimeout", "MILLISECONDS", "0");
  $tmpl->parseTemplate("setTimeout", "a");
  //Fade Information in immediatly
  FadeIn($tmpl, "INFORMATION", 0);
  //Fade it out after 3 seconds
  FadeOut($tmpl, "INFORMATION", 3000);

  //Write standard information in Information box
  $tmpl->addVar("setTimeout", "COMMAND", "getData('information.php?filename=information', 'INFORMATION')");
  $tmpl->addVar("setTimeout", "MILLISECONDS", "4000");
  $tmpl->parseTemplate("setTimeout", "a");
  //Fade Information in after 4 seconds
  FadeIn($tmpl, "INFORMATION", 4000);

  if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')){
    $content = gzencode($tmpl->getParsedTemplate("body"), 9);
    echo $content;
  } else{
    $tmpl->displayParsedTemplate("body");
  }
?>
