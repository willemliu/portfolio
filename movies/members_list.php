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
	$template_file = "members_list.tmpl.xml";
  $tmpl->readTemplatesFromFile($template_file);
  $tmpl->addGlobalVar("FILE_NAME", $template_file);
  
  //Check login
  DBConnect();
  $res = DBQuery("SELECT id, security_level FROM wmc_users WHERE active='1' AND user_name='" . mysql_real_escape_string($_SESSION['username']) . "' AND password='" . mysql_real_escape_string($_SESSION['password']) . "'");

  //Set default security_level of the user to 0
  $security_level = 0;

  //If not logged in then don't show comparison because there isn't one
  $tmpl->addGlobalVar("SHOW_NICK_NAME", "table-cell");
  $tmpl->addGlobalVar("SHOW_FILMS_TOTAL", "table-cell");
  $tmpl->addGlobalVar("SHOW_COMPARISON", "none");

  //If logged in
  if(mysql_num_rows($res) > 0){
    //Show all columns
    $tmpl->addGlobalVar("SHOW_NICK_NAME", "table-cell");
    $tmpl->addGlobalVar("SHOW_FILMS_TOTAL", "table-cell");
    $tmpl->addGlobalVar("SHOW_COMPARISON", "table-cell");
    
    //Set security_level
    $security_level = mysql_result($res, 0, "security_level");
    $menu_result = DBQuery("SELECT security_level FROM wmc_menus_security_levels WHERE menu_item='Members List' ORDER BY position ASC");
    //Check if security level is high enough for this action
    if(mysql_num_rows($menu_result) > 0 && $security_level >= mysql_result($menu_result, 0, "security_level")){
      $user_id = mysql_result($res, 0, "id");
      FadeIn($tmpl, "MEMBERS_LIST", 0);
      
      //Set window as opened
      $window_check = DBQuery("SELECT id FROM wmc_users_windows WHERE user_id=" . $user_id . " AND window='MEMBERS_LIST'");
      if(mysql_num_rows($window_check) > 0){
        DBQuery("UPDATE wmc_users_windows SET opened='1', page='members_list.php' WHERE user_id=" . $user_id . " AND window='MEMBERS_LIST'");
      } else{
        DBQuery("INSERT INTO wmc_users_windows (x, y, opened, page, window, user_id) VALUES ('80', '110', '1', 'members_list.php', 'MEMBERS_LIST', '" . $user_id . "')");
      }

      //Focus on search field
      $tmpl->addVar("setTimeout", "COMMAND", "document.getElementById('members_list_search_filter').focus();");
      $tmpl->addVar("setTimeout", "MILLISECONDS", "0");
      $tmpl->parseTemplate("setTimeout", "a");      

      //Show members
      $members_result = DBQuery("SELECT wmc_users.id AS id, wmc_users.nick_name AS nick_name, count(wmc_users_films.id) AS films_total FROM wmc_users, wmc_users_films WHERE wmc_users.id=wmc_users_films.user_id GROUP BY wmc_users.id");
      $members_num = mysql_num_rows($members_result);
      if($members_num > 0){
        $tmpl->addVar("show_rows", "CONDITION", "true");
      }
      for($i = 0; $i < $members_num; $i++){
        $tmpl->addVar("rows", "ID", mysql_result($members_result, $i, "id"));
        $tmpl->addVar("rows", "NICK_NAME", htmlspecialchars(mysql_result($members_result, $i, "nick_name")));
        $tmpl->addVar("rows", "FILMS_TOTAL", mysql_result($members_result, $i, "films_total"));
        $tmpl->parseTemplate("rows", "a");
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
    $menu_result = DBQuery("SELECT security_level FROM wmc_menus_security_levels WHERE menu_item='Members List' ORDER BY position ASC");
    //Check if security level is high enough for this action
    if($security_level >= mysql_result($menu_result, 0, "security_level")){
      FadeOut($tmpl, "INFORMATION", 0);
      FadeIn($tmpl, "MEMBERS_LIST", 0);

      //Show members
      $members_result = DBQuery("SELECT wmc_users.id AS id, wmc_users.nick_name AS nick_name, count(wmc_users_films.id) AS films_total FROM wmc_users, wmc_users_films WHERE wmc_users.id=wmc_users_films.user_id GROUP BY wmc_users.id");
      $members_num = mysql_num_rows($members_result);
      if($members_num > 0){
        $tmpl->addVar("show_rows", "CONDITION", "true");
      }
      for($i = 0; $i < $members_num; $i++){
        $tmpl->addVar("rows", "ID", mysql_result($members_result, $i, "id"));
        $tmpl->addVar("rows", "NICK_NAME", mysql_result($members_result, $i, "nick_name"));
        $tmpl->addVar("rows", "FILMS_TOTAL", mysql_result($members_result, $i, "films_total"));
        $tmpl->parseTemplate("rows", "a");
      }
    } else{
      //Fade Information box in immediatly
      FadeIn($tmpl, "INFORMATION", 0);
      //Load standard information into the Information box immediatly
      $tmpl->addVar("setTimeout", "COMMAND", "getData('information.php?filename=information_security_level', 'INFORMATION')");
      $tmpl->addVar("setTimeout", "MILLISECONDS", "0");
      $tmpl->parseTemplate("setTimeout", "a");
    }
  }
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
