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
	$template_file = "retrieve_account.tmpl.xml";
  $tmpl->readTemplatesFromFile($template_file);
  $tmpl->addGlobalVar("FILE_NAME", $template_file);
  
  DBConnect();

  //Set default security_level of the user to 0
  $security_level = 0;

  FadeIn($tmpl, "RETRIEVE_ACCOUNT", 0);
  
  //Focus on user name text field
  $tmpl->addVar("setTimeout", "COMMAND", "document.getElementById('retrieve_account_email').focus();");
  $tmpl->addVar("setTimeout", "MILLISECONDS", "0");
  $tmpl->parseTemplate("setTimeout", "a");

  //Javascript code to be called by the Update command
  //Only reason to keep this bit in the PHP file is for the overview
  //writing this on one line in the xml template is unreadable and not maintainable
  $update_command = "postData('retrieve_account_retrieved.php', 'INFORMATION', 
  'retrieve_account_email=' + escape(encodeURI(document.getElementById('retrieve_account_email').value)))";
  
  //Add the command to the template
  $tmpl->addGlobalVar("UPDATE_COMMAND", $update_command);

  //Fade Information box out after 3 seconds
  FadeOut($tmpl, "INFORMATION", 0);

  if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')){
    $content = gzencode($tmpl->getParsedTemplate("body"), 9);
    echo $content;
  } else{
    $tmpl->displayParsedTemplate("body");
  }
?>
