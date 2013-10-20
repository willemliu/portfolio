<?php
	session_start();
  if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')){
    header('Content-Encoding: gzip');
  }
  
	include_once("./includes/dbconnection.php");
	include_once("./includes/patTemplate.php");
	
	// Initialize the patTemplate-class, and create an object.
	$tmpl = new patTemplate();

	// Set which directory contains the template-files.
	$tmpl->setBasedir("tmpl");

	// Set template
	$template_file = "index.tmpl.html";
  $tmpl->readTemplatesFromFile($template_file);
  $tmpl->addGlobalVar("FILE_NAME", $template_file);

  DBConnect();
  $container_result = DBQuery("SELECT container FROM wmc_web_containers ORDER BY id ASC");
  $container_num = mysql_num_rows($container_result);
  for($i = 0; $i < $container_num; $i++){
    $tmpl->addVar("containers", "CONTAINER", mysql_result($container_result, $i, "container"));
    $tmpl->parseTemplate("containers", "a");
  }

  if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')){
    $content = gzencode($tmpl->getParsedTemplate("body"), 9);
    echo $content;
  } else{
    $tmpl->displayParsedTemplate("body");
  }
?>
