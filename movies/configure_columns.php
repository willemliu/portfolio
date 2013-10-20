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
	$template_file = "configure_columns.tmpl.xml";
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
    $section_result = DBQuery("SELECT security_level FROM wmc_sections_security_levels WHERE section='Configure Columns'");
    //Check if security level is high enough for this action
    if(mysql_num_rows($section_result) > 0 && $security_level >= mysql_result($section_result, 0, "security_level")){
      $user_id = mysql_result($res, 0, "id");
      FadeIn($tmpl, "CONFIGURE_COLUMNS", 0);

      //Set window as opened
      $window_check = DBQuery("SELECT id FROM wmc_users_windows WHERE user_id=" . $user_id . " AND window='CONFIGURE_COLUMNS'");
      if(mysql_num_rows($window_check) > 0){
        DBQuery("UPDATE wmc_users_windows SET opened='1', page='configure_columns.php' WHERE user_id=" . $user_id . " AND window='CONFIGURE_COLUMNS'");
      } else{
        DBQuery("INSERT INTO wmc_users_windows (x, y, opened, page, window, user_id) VALUES ('80', '110', '1', 'configure_columns.php','CONFIGURE_COLUMNS', '" . $user_id . "')");
      }
      
      $column_result = DBQuery("SELECT * FROM wmc_users_films_columns WHERE user_id=" . mysql_result($res, 0, "id"));
      if(mysql_num_rows($column_result) > 0){
        $title = mysql_result($column_result, 0, "title");
        $duration = mysql_result($column_result, 0, "duration");
        $sound_mix = mysql_result($column_result, 0, "sound_mix");
        $subtitle = mysql_result($column_result, 0, "subtitle");
        $genre = mysql_result($column_result, 0, "genre");
        $medium = mysql_result($column_result, 0, "medium");
        $region = mysql_result($column_result, 0, "region");
        $color = mysql_result($column_result, 0, "color");
        $cast = mysql_result($column_result, 0, "cast");
        $keyword = mysql_result($column_result, 0, "keyword");
        $lend_to = mysql_result($column_result, 0, "lend_to");
        $aspect_ratio = mysql_result($column_result, 0, "aspect_ratio");
        $trailer = mysql_result($column_result, 0, "trailer");
        $tmpl->addGlobalVar("TITLE_VALUE", $title);
        $tmpl->addGlobalVar("DURATION_VALUE", $duration);
        $tmpl->addGlobalVar("SOUND_MIX_VALUE", $sound_mix);
        $tmpl->addGlobalVar("SUBTITLE_VALUE", $subtitle);
        $tmpl->addGlobalVar("GENRE_VALUE", $genre);
        $tmpl->addGlobalVar("MEDIUM_VALUE", $medium);
        $tmpl->addGlobalVar("REGION_VALUE", $region);
        $tmpl->addGlobalVar("COLOR_VALUE", $color);
        $tmpl->addGlobalVar("ASPECT_RATIO_VALUE", $aspect_ratio);
        $tmpl->addGlobalVar("CAST_VALUE", $cast);
        $tmpl->addGlobalVar("KEYWORD_VALUE", $keyword);
        $tmpl->addGlobalVar("LEND_TO_VALUE", $lend_to);
        $tmpl->addGlobalVar("TRAILER_VALUE", $trailer);
        if($title){
          $tmpl->addGlobalVar("TITLE_CHECKED", "checked");
        }
        if($duration){
          $tmpl->addGlobalVar("DURATION_CHECKED", "checked");
        }
        if($sound_mix){
          $tmpl->addGlobalVar("SOUND_MIX_CHECKED", "checked");
        }
        if($subtitle){
          $tmpl->addGlobalVar("SUBTITLE_CHECKED", "checked");
        }
        if($genre){
          $tmpl->addGlobalVar("GENRE_CHECKED", "checked");
        }
        if($medium){
          $tmpl->addGlobalVar("MEDIUM_CHECKED", "checked");
        }
        if($region){
          $tmpl->addGlobalVar("REGION_CHECKED", "checked");
        }
        if($color){
          $tmpl->addGlobalVar("COLOR_CHECKED", "checked");
        }
        if($aspect_ratio){
          $tmpl->addGlobalVar("ASPECT_RATIO_CHECKED", "checked");
        }
        if($cast){
          $tmpl->addGlobalVar("CAST_CHECKED", "checked");
        }
        if($lend_to){
          $tmpl->addGlobalVar("LEND_TO_CHECKED", "checked");
        }
        if($keyword){
          $tmpl->addGlobalVar("KEYWORD_CHECKED", "checked");
        }
        if($trailer){
          $tmpl->addGlobalVar("TRAILER_CHECKED", "checked");
        }
      }
      
      //Javascript code to be called by the Update command
      //Only reason to keep this bit in the PHP file is for the overview
      //writing this on one line in the xml template is unreadable and not maintainable
      $update_command = "postData('configure_columns_update.php', 'INFORMATION', 
      'configure_columns_title=' + escape(encodeURI(document.getElementById('configure_columns_title').value)) + " . 
      "'&amp;configure_columns_duration=' + escape(encodeURI(document.getElementById('configure_columns_duration').value)) + " . 
      "'&amp;configure_columns_sound_mix=' + escape(encodeURI(document.getElementById('configure_columns_sound_mix').value)) + " . 
      "'&amp;configure_columns_subtitle=' + escape(encodeURI(document.getElementById('configure_columns_subtitle').value)) + " .
      "'&amp;configure_columns_genre=' + escape(encodeURI(document.getElementById('configure_columns_genre').value)) + ".
      "'&amp;configure_columns_medium=' + escape(encodeURI(document.getElementById('configure_columns_medium').value)) + ".
      "'&amp;configure_columns_region=' + escape(encodeURI(document.getElementById('configure_columns_region').value)) + " .
      "'&amp;configure_columns_color=' + escape(encodeURI(document.getElementById('configure_columns_color').value)) + " .
      "'&amp;configure_columns_aspect_ratio=' + escape(encodeURI(document.getElementById('configure_columns_aspect_ratio').value)) + " .
      "'&amp;configure_columns_cast=' + escape(encodeURI(document.getElementById('configure_columns_cast').value)) + " .
      "'&amp;configure_columns_keyword=' + escape(encodeURI(document.getElementById('configure_columns_keyword').value)) + " .
      "'&amp;configure_columns_lend_to=' + escape(encodeURI(document.getElementById('configure_columns_lend_to').value)) + " .
      "'&amp;configure_columns_trailer=' + escape(encodeURI(document.getElementById('configure_columns_trailer').value)))";
      
      //Add the command to the template
      $tmpl->addGlobalVar("UPDATE_COMMAND", $update_command);
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

  if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')){
    $content = gzencode($tmpl->getParsedTemplate("body"), 9);
    echo $content;
  } else{
    $tmpl->displayParsedTemplate("body");
  }
?>
