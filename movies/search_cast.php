<?php
  //Start timer
	$mtime = microtime();
	$mtime = explode(" ",$mtime);
	$mtime = $mtime[1] + $mtime[0];
	$tstart = $mtime;
	
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
	$template_file = "search_cast.tmpl.xml";
  $tmpl->readTemplatesFromFile($template_file);
  $tmpl->addGlobalVar("FILE_NAME", $template_file);
  
  //Check login
  DBConnect();
  $res = DBQuery("SELECT id, security_level FROM wmc_users WHERE active='1' AND user_name='" . mysql_real_escape_string($_SESSION['username']) . "' AND password='" . mysql_real_escape_string($_SESSION['password']) . "'");

  //Set default security_level of the user to 0
  $security_level = 0;

  //If logged in
  if($_POST && mysql_num_rows($res) > 0){
    //Set security_level
    $security_level = mysql_result($res, 0, "security_level");
    $section_result = DBQuery("SELECT security_level FROM wmc_sections_security_levels WHERE section='Search Cast'");
    //Check security level
    if(mysql_num_rows($section_result) > 0 && $security_level >= mysql_result($section_result, 0, "security_level")){
      $user_id = mysql_result($res, 0, "id");

      //Replace all Mysql REGEXP signs
      $replace_array = Array("\\", "^", "(", ")", "$", "[", "]", "?", "*");
      $replace_by_array = Array("", "", "", "", "", "", "", "", "");
      $film_id = mysql_real_escape_string(str_replace($replace_array, $replace_by_array, htmlspecialchars(urlDecode($_POST['film_id']))));
      $search_filter = mysql_real_escape_string(str_replace($replace_array, $replace_by_array, utf8_decode(htmlspecialchars(strip_tags(urlDecode($_POST['search_filter']))))));
      $replace_array = Array("\\");
      $replace_by_array = Array("");
      $tmpl->addGlobalVar("SEARCH_FILTER", str_replace($replace_array, $replace_by_array, $search_filter));
      $search_filter = addslashes(addslashes($search_filter));

      //Show members
      if($search_filter != ""){
        $cast_result = DBQuery("SELECT id, name FROM wmc_cast WHERE name REGEXP '" . $search_filter . "'");
        $cast_num = mysql_num_rows($cast_result);
        if($cast_num > 0){
          $tmpl->addVar("show_rows", "CONDITION", "true");
          //Disable the add cast button for the search text
          $tmpl->addVar("setTimeout", "COMMAND", "document.getElementById('add_cast_add').disabled = true;");
          $tmpl->addVar("setTimeout", "MILLISECONDS", "0");
          $tmpl->parseTemplate("setTimeout", "a");
        }
        for($i = 0; $i < $cast_num; $i++){
          $tmpl->addVar("rows", "FILM_ID", $film_id);
          $tmpl->addVar("rows", "ID", mysql_result($cast_result, $i, "id"));
          $tmpl->addVar("rows", "NAME", stripslashes(mysql_result($cast_result, $i, "name")));
          $tmpl->parseTemplate("rows", "a");
        }
        if($cast_num == 0){
          //Enable the add cast button for the search text
          $tmpl->addVar("setTimeout", "COMMAND", "document.getElementById('add_cast_add').disabled = false;");
          $tmpl->addVar("setTimeout", "MILLISECONDS", "0");
          $tmpl->parseTemplate("setTimeout", "a");
        }
      } else{
        //Disable the add cast button for the search text
        $tmpl->addVar("setTimeout", "COMMAND", "document.getElementById('add_cast_add').disabled = true;");
        $tmpl->addVar("setTimeout", "MILLISECONDS", "0");
        $tmpl->parseTemplate("setTimeout", "a");
      }
    } else{
      //Fade Information box in immediatly
      FadeIn($tmpl, "INFORMATION", 0);
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
  //Focus on user name text field
  $tmpl->addVar("setTimeout", "COMMAND", "document.getElementById('cast_search_filter').focus();");
  $tmpl->addVar("setTimeout", "MILLISECONDS", "0");
  $tmpl->parseTemplate("setTimeout", "a");

  //Fade Information box out after 3 seconds
  FadeOut($tmpl, "INFORMATION", 3000);

  //End timer
	$mtime = microtime();
	$mtime = explode(" ",$mtime);
	$mtime = $mtime[1] + $mtime[0];
	$tend = $mtime;
	$tpassed = ($tend - $tstart);
	$tmpl->addGlobalVar("PARSE_TIME", number_format($tpassed, 3, ".", ""));
	$tmpl->addGlobalVar("DB_QUERIES", $_SESSION['number_of_database_queries']);

  if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')){
    $content = gzencode($tmpl->getParsedTemplate("body"), 9);
    echo $content;
  } else{
    $tmpl->displayParsedTemplate("body");
  }
?>
