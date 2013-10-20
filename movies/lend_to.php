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
	$template_file = "lend_to.tmpl.xml";
  $tmpl->readTemplatesFromFile($template_file);
  $tmpl->addGlobalVar("FILE_NAME", $template_file);
  
  //Check login
  DBConnect();
  $res = DBQuery("SELECT id, security_level FROM wmc_users WHERE active='1' AND user_name='" . mysql_real_escape_string($_SESSION['username']) . "' AND password='" . mysql_real_escape_string($_SESSION['password']) . "'");

  //Set default security_level of the user to 0
  $security_level = 0;

  //If logged in
  if(mysql_num_rows($res) > 0){
    //Set security_level
    $security_level = mysql_result($res, 0, "security_level");
    $menu_result = DBQuery("SELECT security_level FROM wmc_menus_security_levels WHERE menu_item='Lend To' ORDER BY position ASC");
    //Check if security level is high enough for this action
    if(mysql_num_rows($menu_result) > 0 && $security_level >= mysql_result($menu_result, 0, "security_level")){
      $user_id = mysql_result($res, 0, "id");
      FadeIn($tmpl, "LEND_TO", 0);
      
      //Replace all Mysql GENEXP signs
      $replace_array = Array("\\", "^", "(", ")", "$", "[", "]", "?", "*", "&amp;");
      $replace_by_array = Array("", "", "", "", "", "", "", "", "", "&");
      $char_filter = mysql_real_escape_string(htmlspecialchars(urlDecode($_POST['char'])));
      $search_filter = mysql_real_escape_string(str_replace($replace_array, $replace_by_array, utf8_decode(htmlspecialchars(urlDecode($_POST['search_filter'])))));
      $replace_array = Array("\\");
      $replace_by_array = Array("");
      $tmpl->addGlobalVar("SEARCH_FILTER", htmlspecialchars(str_replace($replace_array, $replace_by_array, $search_filter)));
      $search_filter = addslashes(addslashes($search_filter));
      
      //Set window as opened
      $window_check = DBQuery("SELECT id FROM wmc_users_windows WHERE user_id=" . $user_id . " AND window='LEND_TO'");
      if(mysql_num_rows($window_check) > 0){
        DBQuery("UPDATE wmc_users_windows SET opened='1', page='lend_to.php', parameter='search_filter=" . urlencode($search_filter) . "&char=" . $char_filter . "' WHERE user_id=" . $user_id . " AND window='LEND_TO'");
      } else{
        DBQuery("INSERT INTO wmc_users_windows (x, y, opened, page, parameter, window, user_id) VALUES ('80', '110', '1', 'lend_to.php', 'search_filter=" . urlencode($search_filter) . "&char=" . $char_filter . "', 'LEND_TO', '" . $user_id . "')");
      }

      //Show films
      if($search_filter != ""){
        //Search films ids
        $film_ids = "";

        //Search Lend To
        $lend_to_result = DBQuery("SELECT film_id FROM wmc_users_films_lend_to  WHERE lend_to REGEXP '" . $search_filter . "' ORDER BY lend_to ASC");
        $lend_to_num = mysql_num_rows($lend_to_result);
        for($j = 0; $j < $lend_to_num; $j++){
				  if($film_ids != ""){
  					$film_ids .= "|";
					}
          $film_ids .= mysql_result($lend_to_result, $j, "film_id");
        }

				if($film_ids != ""){
          $film_result = DBQuery("SELECT films.id, films.title FROM wmc_films AS films, wmc_users_films AS users_films, wmc_colors AS colors, wmc_users_films_lend_to AS lend_to WHERE films.id=lend_to.film_id AND lend_to.user_id='" . $user_id . "' AND films.id=users_films.film_id AND users_films.user_id=" . $user_id . " AND (films.title REGEXP '" . $search_filter . "+' OR films.duration REGEXP '" . $search_filter . "+' OR colors.color REGEXP '" . $search_filter . "+' OR films.id REGEXP '^(" . $film_ids . ")$') GROUP BY films.id ORDER BY films.title ASC");
				} else{
          $film_result = DBQuery("SELECT films.id, films.title FROM wmc_films AS films, wmc_users_films AS users_films, wmc_colors AS colors, wmc_users_films_lend_to AS lend_to WHERE films.id=lend_to.film_id AND lend_to.user_id='" . $user_id . "' AND films.id=users_films.film_id AND users_films.user_id=" . $user_id . " AND (films.title REGEXP '" . $search_filter . "+' OR films.duration REGEXP '" . $search_filter . "+' OR colors.color REGEXP '" . $search_filter . "+') GROUP BY films.id ORDER BY films.title ASC");
				}
        $film_num = mysql_num_rows($film_result);
        if($film_num == 0){
          $film_result = DBQuery("SELECT films.id, films.title FROM wmc_films AS films, wmc_users_films AS users_films, wmc_users_films_lend_to AS lend_to WHERE films.id=lend_to.film_id AND lend_to.user_id='" . $user_id . "' AND films.id=users_films.film_id AND users_films.user_id=" . $user_id . " ORDER BY films.title ASC");
        }
      } else{
        $film_result = DBQuery("SELECT films.id, films.title FROM wmc_films AS films, wmc_users_films AS users_films, wmc_users_films_lend_to AS lend_to WHERE films.id=lend_to.film_id AND lend_to.user_id='" . $user_id . "' AND films.id=users_films.film_id AND users_films.user_id=" . $user_id . " ORDER BY films.title ASC");
      }
      $film_num = mysql_num_rows($film_result);
      if($film_num > 0){
        $tmpl->addVar("show_rows", "CONDITION", "true");
        for($i = 0; $i < $film_num; $i++){
          //Default values
          $tmpl->addVar("rows", "ID", "&amp;nbsp;");
          $tmpl->addVar("rows", "TITLE", "&amp;nbsp;");
          $tmpl->addVar("rows", "LEND_TO", "&amp;nbsp;");

          //Film title
          $film_id = mysql_result($film_result, $i, "films.id");
          $tmpl->addVar("rows", "ID", mysql_result($film_result, $i, "films.id"));
          $tmpl->addVar("rows", "TITLE", stripslashes(htmlspecialchars(mysql_result($film_result, $i, "films.title"))));

          //Lend To
          $lend_to_result = DBQuery("SELECT id, lend_to FROM wmc_users_films_lend_to WHERE film_id='" . $film_id . "' ORDER BY lend_to ASC");
          $lend_to_num = mysql_num_rows($lend_to_result);
          if($cast_num > 0){
            $tmpl->addVar("rows", "HAS_LEND_TO", "[Show Lend To]");
          }
          $val = "";
          for($j = 0; $j < $lend_to_num; $j++){
            $val .= stripslashes(mysql_result($lend_to_result, $j, "LEND_TO")) . "&lt;br&gt;";
          }
          $tmpl->addVar("rows", "LEND_TO", $val);
          $tmpl->parseTemplate("rows", "a");
        }
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
    //Fade Information box in immediatly
    FadeIn($tmpl, "INFORMATION", 0);
    //Load standard information into the Information box immediatly
    $tmpl->addVar("setTimeout", "COMMAND", "getData('information.php?filename=information_login_failed', 'INFORMATION')");
    $tmpl->addVar("setTimeout", "MILLISECONDS", "0");
    $tmpl->parseTemplate("setTimeout", "a");
  }
  //Fade Information box out after 3 seconds
  FadeOut($tmpl, "INFORMATION", 3000);

  //Focus on user name text field
  $tmpl->addVar("setTimeout", "COMMAND", "document.getElementById('my_films_search_filter').focus();");
  $tmpl->addVar("setTimeout", "MILLISECONDS", "0");
  $tmpl->parseTemplate("setTimeout", "a");

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
