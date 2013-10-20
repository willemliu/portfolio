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
	$template_file = "menu.tmpl.xml";
  $tmpl->readTemplatesFromFile($template_file);
  $tmpl->addGlobalVar("FILE_NAME", $template_file);
  
  //Check login
  DBConnect();
  $res = DBQuery("SELECT id, security_level FROM wmc_users WHERE active='1' AND user_name='" . mysql_real_escape_string($_SESSION['username']) . "' AND password='" . mysql_real_escape_string($_SESSION['password']) . "'");

  $security_level = 0;

  FadeIn($tmpl, "MENU", 0);

  //If logged in
  if(mysql_num_rows($res) > 0){
    $security_level = mysql_result($res, 0, "security_level");
  }
  $menu_result = DBQuery("SELECT menu_item, menu_command, security_level FROM wmc_menus_security_levels WHERE security_level<=" . $security_level . " ORDER BY position ASC");
  $menu_items = mysql_num_rows($menu_result);
  for($i = 0; $i < $menu_items; $i++){
    $tmpl->addVar("link", "LINK_TEXT", mysql_result($menu_result, $i, "menu_item"));
    $tmpl->addVar("link", "LINK_COMMAND", mysql_result($menu_result, $i, "menu_command"));
    $tmpl->parseTemplate("link", "a");
  }

  if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')){
    $content = gzencode($tmpl->getParsedTemplate("body"), 9);
    echo $content;
  } else{
    $tmpl->displayParsedTemplate("body");
  }
?>
