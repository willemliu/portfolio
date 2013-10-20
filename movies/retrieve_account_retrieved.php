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
  $template_file = "retrieve_account_retrieved.tmpl.xml";
  $tmpl->readTemplatesFromFile($template_file);
  $tmpl->addGlobalVar("FILE_NAME", $template_file);

  //Check login
  DBConnect();
  
  $security_level = 0;

  //Fade Information box in immediatly
  FadeIn($tmpl, "INFORMATION", 0);

  //Disable the Update button immediatly
  $tmpl->addVar("setTimeout", "COMMAND", "document.getElementById('retrieve_account_retrieve').disabled = true");
  $tmpl->addVar("setTimeout", "MILLISECONDS", "0");
  $tmpl->parseTemplate("setTimeout", "a");

  //If mandatory fields aren't empty
  $email = mysql_real_escape_string(strip_tags(urlDecode($_POST['retrieve_account_email'])));
  
  if($email != ""){
    $check_result = DBQuery("SELECT first_name, last_name, email, user_name FROM `wmc_users` WHERE email='" . $email . "'");
    if(mysql_num_rows($check_result) > 0){
      $first_name = mysql_result($check_result, 0, "first_name");
      $last_name = mysql_result($check_result, 0, "last_name");
      $user_name = mysql_result($check_result, 0, "user_name");
      
      $length = 8;
      // start with a blank password
      $password = "";
      // define possible characters
      $possible = "0123456789bcdfghjkmnpqrstvwxyz"; 
      // set up a counter
      $i = 0; 
      // add random characters to $password until $length is reached
      while ($i < $length) { 
        // pick a random character from the possible ones
        $char = substr($possible, mt_rand(0, strlen($possible)-1), 1);
            
        // we don't want this character if it's already in the password
        if (!strstr($password, $char)) { 
          $password .= $char;
          $i++;
        }
      }

      $tmpl->addGlobalVar("INFORMATION", "Account information retrieved. An e-mail with this information has been mailed to you.");
      //Fade Information box in immediatly
      FadeOut($tmpl, "RETRIEVE_ACCOUNT", 0);
      
      DBQuery("UPDATE wmc_users SET password='" . md5($password) . "' WHERE email='" . $email . "'");
      
      $headers = "From: My Movie Collection <no-reply@willemliu.nl>\nBCC: willemliu@willemliu.nl";
      $subject = "My Movie Collection Retrieve Account";
      $message = "Welcome " . $first_name . " " . $last_name .
      "\n\n" . 
      "You've requested an account retrieval on our website. " .
      "Because passwords are encrypted we can't retrieve that from our database." .
      "\n" . 
      "We've generated a new password instead which you can change at any time on our website." .
      "\n\n" . 
      "Your username is: " . $user_name .
      "\n" . 
      "Your password is: " . $password .
      "\n\n" . 
      "We hope that our services will please you." .
      "\n\n" . 
      "Regards," .
      "\n\n" . 
      "My Movie Collection Team";
      mail($email, $subject, $message, $headers);
    } else{
      $tmpl->addGlobalVar("INFORMATION", "E-mail address not found in our database. You can create an account with this e-mail address.");
      //Enable the Update button after 1 second
      $tmpl->addVar("setTimeout", "COMMAND", "document.getElementById('retrieve_account_retrieve').disabled = false");
      $tmpl->addVar("setTimeout", "MILLISECONDS", "1000");
      $tmpl->parseTemplate("setTimeout", "a");
    }
  } else{
    $tmpl->addGlobalVar("INFORMATION", "Fields marked with (*) are mandatory.");
    //Enable the Update button after 1 second
    $tmpl->addVar("setTimeout", "COMMAND", "document.getElementById('retrieve_account_retrieve').disabled = false");
    $tmpl->addVar("setTimeout", "MILLISECONDS", "1000");
    $tmpl->parseTemplate("setTimeout", "a");
  }
  //Fade Information box in immediatly
  FadeOut($tmpl, "INFORMATION", 5000);

  if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')){
    $content = gzencode($tmpl->getParsedTemplate("body"), 9);
    echo $content;
  } else{
    $tmpl->displayParsedTemplate("body");
  }
?>
