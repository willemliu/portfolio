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
	$template_file = "create_account.tmpl.xml";
  $tmpl->readTemplatesFromFile($template_file);
  $tmpl->addGlobalVar("FILE_NAME", $template_file);
  
  DBConnect();

  //Set default security_level of the user to 0
  $security_level = 0;

  FadeIn($tmpl, "CREATE_ACCOUNT", 0);
  
  //Get available genders and select the one applicable to the current user
  $gender_result = DBQuery("SELECT id, gender FROM wmc_genders ORDER BY id ASC");
  $gender_num = mysql_num_rows($gender_result);
  for($i = 0; $i < $gender_num; $i++){
    $tmpl->addVar("gender", "GENDER", mysql_result($gender_result, $i, "gender"));
    $tmpl->addVar("gender", "GENDER_ID", mysql_result($gender_result, $i, "id"));
    $tmpl->parseTemplate("gender", "a");
  }

  //Get available countries and select the one applicable to the current user
  $country_result = DBQuery("SELECT Code, Name FROM country ORDER BY Name ASC");
  $country_num = mysql_num_rows($country_result);
  for($i = 0; $i < $country_num; $i++){
    $tmpl->addVar("country", "COUNTRY", mysql_result($country_result, $i, "Name"));
    $tmpl->addVar("country", "COUNTRY_CODE", mysql_result($country_result, $i, "Code"));
    $tmpl->parseTemplate("country", "a");
  }

  //Focus on user name text field
  $tmpl->addVar("setTimeout", "COMMAND", "document.getElementById('create_account_first_name').focus();");
  $tmpl->addVar("setTimeout", "MILLISECONDS", "0");
  $tmpl->parseTemplate("setTimeout", "a");

  //Javascript code to be called by the Update command
  //Only reason to keep this bit in the PHP file is for the overview
  //writing this on one line in the xml template is unreadable and not maintainable
  $update_command = "postData('create_account_created.php', 'INFORMATION', 
  'create_account_first_name=' + escape(encodeURI(document.getElementById('create_account_first_name').value)) + " . 
  "'&amp;create_account_last_name=' + escape(encodeURI(document.getElementById('create_account_last_name').value)) + " . 
  "'&amp;create_account_email=' + escape(encodeURI(document.getElementById('create_account_email').value)) + " . 
  "'&amp;create_account_gender_id=' + escape(encodeURI(document.getElementById('create_account_gender_id').value)) + " .
  "'&amp;create_account_address=' + escape(encodeURI(document.getElementById('create_account_address').value)) + ".
  "'&amp;create_account_city=' + escape(encodeURI(document.getElementById('create_account_city').value)) + " .
  "'&amp;create_account_country_code=' + escape(encodeURI(document.getElementById('create_account_country_code').value)) + " .
  "'&amp;create_account_postal=' + escape(encodeURI(document.getElementById('create_account_postal').value)) + " .
  "'&amp;create_account_phone=' + escape(encodeURI(document.getElementById('create_account_phone').value)) + " .
  "'&amp;create_account_mobile=' + escape(encodeURI(document.getElementById('create_account_mobile').value)) + " .
  "'&amp;create_account_nick_name=' + escape(encodeURI(document.getElementById('create_account_nick_name').value)) + " .
  "'&amp;create_account_user_name=' + escape(encodeURI(document.getElementById('create_account_user_name').value)) + " .
  "'&amp;create_account_new_password=' + escape(encodeURI(document.getElementById('create_account_new_password').value)) + " .
  "'&amp;create_account_new_password2=' + escape(encodeURI(document.getElementById('create_account_new_password2').value)))";
  
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
