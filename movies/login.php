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
	$template_file = "logged_out.tmpl.xml";
  $tmpl->readTemplatesFromFile($template_file);
  $tmpl->addGlobalVar("FILE_NAME", $template_file);

  //Check login  
  DBConnect();
  $res = DBQuery("SELECT id, nick_name FROM wmc_users WHERE active='1' AND user_name='" . mysql_real_escape_string($_SESSION['username']) . "' AND password='" . mysql_real_escape_string($_SESSION['password']) . "'");

  //Fade Login box immediatly
  FadeIn($tmpl, "LOGIN", 0);
  //Parse menu immediatly
  $tmpl->addVar("setTimeout", "COMMAND", "getData('menu.php', 'MENU')");
  $tmpl->addVar("setTimeout", "MILLISECONDS", "100");
  $tmpl->parseTemplate("setTimeout", "a");

  //If login successful
  if(mysql_num_rows($res) > 0){
    $user_id = mysql_result($res, 0, "id");
    //Load logged_in template
  	$template_file = "logged_in.tmpl.xml";
    $tmpl->readTemplatesFromFile($template_file);
    $tmpl->addGlobalVar("FILE_NAME", $template_file);
    //Put nick name as global variable
    $tmpl->addGlobalVar("NICK_NAME", mysql_result($res, 0, "nick_name"));
    //Write successful information in Information box
    $tmpl->addVar("setTimeout", "COMMAND", "getData('information.php?filename=information_login_success', 'INFORMATION')");
    $tmpl->addVar("setTimeout", "MILLISECONDS", "0");
    $tmpl->parseTemplate("setTimeout", "a");
    //Fade Information box in immediatly
    FadeIn($tmpl, "INFORMATION", 0);
    //Fade Information box out after 3 seconds
    FadeOut($tmpl, "INFORMATION", 3000);
    //Set Window positions
    $window_result = DBQuery("SELECT window, x, y, opened, page, parameter FROM wmc_users_windows WHERE user_id=" . $user_id);
    $window_num = mysql_num_rows($window_result);
    for($i = 0; $i < $window_num; $i++){
      $window = mysql_result($window_result, $i, "window");
      $x = mysql_result($window_result, $i, "x");
      $y = mysql_result($window_result, $i, "y");
      $page = mysql_result($window_result, $i, "page");
      $parameter = mysql_result($window_result, $i, "parameter");
      $tmpl->addVar("setTimeout", "COMMAND", "document.getElementById('" . $window . "').style.left='" . $x . "px';");
      $tmpl->addVar("setTimeout", "MILLISECONDS", "0");
      $tmpl->parseTemplate("setTimeout", "a");
      $tmpl->addVar("setTimeout", "COMMAND", "document.getElementById('" . $window . "').style.top='" . $y . "px';");
      $tmpl->addVar("setTimeout", "MILLISECONDS", "0");
      $tmpl->parseTemplate("setTimeout", "a");
      if(mysql_result($window_result, $i, "opened") == 1){
        $tmpl->addVar("setTimeout", "COMMAND", "postData('" . $page . "', '" . $window . "', '" . utf8_decode(htmlspecialchars($parameter)) . "');");
        $tmpl->addVar("setTimeout", "MILLISECONDS", "0");
        $tmpl->parseTemplate("setTimeout", "a");
      }
    }
  } else{
    //Disable Login Button
    $tmpl->addVar("setTimeout", "COMMAND", "document.getElementById('login_button').disabled = true");
    $tmpl->addVar("setTimeout", "MILLISECONDS", "0");
    $tmpl->parseTemplate("setTimeout", "a");
  
    //Enable Login Button
    $tmpl->addVar("setTimeout", "COMMAND", "document.getElementById('login_button').disabled = false");
    $tmpl->addVar("setTimeout", "MILLISECONDS", "1000");
    $tmpl->parseTemplate("setTimeout", "a");
  
    //Load logged_out template
  	$template_file = "logged_out.tmpl.xml";
    $tmpl->readTemplatesFromFile($template_file);
    $tmpl->addGlobalVar("FILE_NAME", $template_file);
    //If not a blank login
    if(htmlspecialchars(urlDecode($_POST['username'])) != "" || htmlspecialchars(urlDecode($_POST['password'])) != ""){
      //Show message of failed login in the Information box
      $tmpl->addVar("setTimeout", "COMMAND", "getData('information.php?filename=information_login_failed', 'INFORMATION')");
      $tmpl->addVar("setTimeout", "MILLISECONDS", "0");
      $tmpl->parseTemplate("setTimeout", "a");
      //Fade the Information box out after 3 seconds
      FadeOut($tmpl, "INFORMATION", 3000);
      //Load standard information into the Information box after 4 seconds
      $tmpl->addVar("setTimeout", "COMMAND", "getData('information.php?filename=information', 'INFORMATION')");
      $tmpl->addVar("setTimeout", "MILLISECONDS", "4000");
      $tmpl->parseTemplate("setTimeout", "a");
      //Fade Information box in after 4 seconds
      FadeIn($tmpl, "INFORMATION", 4000);
    } else{
      //Load standard information into the Information box immediatly
      $tmpl->addVar("setTimeout", "COMMAND", "getData('information.php?filename=information', 'INFORMATION')");
      $tmpl->addVar("setTimeout", "MILLISECONDS", "0");
      $tmpl->parseTemplate("setTimeout", "a");
    }
    //Focus on user name text field
    $tmpl->addVar("setTimeout", "COMMAND", "document.getElementById('username').focus();");
    $tmpl->addVar("setTimeout", "MILLISECONDS", "0");
    $tmpl->parseTemplate("setTimeout", "a");

    //Fade Information box in immediatly
    FadeIn($tmpl, "INFORMATION", 0);
  }

  if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')){
    $content = gzencode($tmpl->getParsedTemplate("body"), 9);
    echo $content;
  } else{
    $tmpl->displayParsedTemplate("body");
  }
?>
