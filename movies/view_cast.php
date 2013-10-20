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
	$template_file = "view_cast.tmpl.xml";
  $tmpl->readTemplatesFromFile($template_file);
  $tmpl->addGlobalVar("FILE_NAME", $template_file);
  
  //Check login
  DBConnect();
  $res = DBQuery("SELECT id, security_level FROM wmc_users WHERE active='1' AND user_name='" . mysql_real_escape_string($_SESSION['username']) . "' AND password='" . mysql_real_escape_string($_SESSION['password']) . "'");

  $security_level = 0;

  //If logged in
  if(mysql_num_rows($res) > 0){
    $security_level = mysql_result($res, 0, "security_level");
    $section_result = DBQuery("SELECT security_level FROM wmc_sections_security_levels WHERE section='View Cast'");
    //Check if security level is high enough for this action
    if(mysql_num_rows($section_result) > 0 && $security_level >= mysql_result($section_result, 0, "security_level")){
      FadeInExtra($tmpl, "VIEW_CAST", 0, 0, 99);
      $user_id = mysql_result($res, 0, "id");
      $film_id = htmlspecialchars(urlDecode($_POST['film_id']));
      $cast_id = htmlspecialchars(urlDecode($_POST['cast_id']));

      $tmpl->addGlobalVar("FILM_ID", $film_id);
      $tmpl->addGlobalVar("CAST_ID", $cast_id);

      //Set window as opened
      $window_check = DBQuery("SELECT id FROM wmc_users_windows WHERE user_id=" . $user_id . " AND window='VIEW_CAST'");
      if(mysql_num_rows($window_check) > 0){
        DBQuery("UPDATE wmc_users_windows SET opened='1', page='view_cast.php', parameter='film_id=" . $film_id . "&cast_id=" . $cast_id . "' WHERE user_id=" . $user_id . " AND window='VIEW_CAST'");
      } else{
        DBQuery("INSERT INTO wmc_users_windows (x, y, opened, page, parameter, window, user_id) VALUES ('80', '110', '1', 'view_cast.php', 'film_id" . $film_id . "&cast_id=" . $cast_id . "', 'VIEW_CAST', '" . $user_id . "')");
      }

      $cast_result = DBQuery("SELECT users.nick_name, films.title FROM wmc_users AS users, wmc_films_cast AS films_casts, wmc_films AS films WHERE users.id=films_casts.user_id AND films.id=films_casts.film_id AND films_casts.cast_id='" . $cast_id . "' AND films.id='" . $film_id . "'");
      if(mysql_num_rows($cast_result) > 0){
        $tmpl->addGlobalVar("NICK_NAME", htmlspecialchars(mysql_result($cast_result, 0, "users.nick_name")));
        $tmpl->addGlobalVar("FILM", stripslashes(htmlspecialchars(mysql_result($cast_result, 0, "films.title"))));
      }
      
      $cast_result = DBQuery("SELECT name, birth_date, gender_id, description FROM wmc_cast WHERE id='" . $cast_id . "'");
      if(mysql_num_rows($cast_result)){
        $gender_id = mysql_result($cast_result, 0, "gender_id");
        $tmpl->addGlobalVar("NAME", stripslashes(mysql_result($cast_result, 0, "name")));
        $birth_date = mysql_result($cast_result, 0, "birth_date");
        if($birth_date != "0000-00-00" && $birth_date != ""){
          $tmpl->addGlobalVar("BIRTH_DATE", $birth_date);
        }
        $tmpl->addGlobalVar("DESCRIPTION", stripslashes(mysql_result($cast_result, 0, "description")));

        $gender_result = DBQuery("SELECT id, gender FROM wmc_genders");
        $gender_num = mysql_num_rows($gender_result);
        for($i = 0; $i < $gender_num; $i++){
          if(mysql_result($gender_result, $i, "id") == $gender_id){
            $tmpl->addVar("gender", "SELECTED", "selected");
          } else{
            $tmpl->addVar("gender", "SELECTED", "");
          }
          $tmpl->addVar("gender", "GENDER_ID", mysql_result($gender_result, $i, "id"));
          $tmpl->addVar("gender", "GENDER", mysql_result($gender_result, $i, "gender"));
          $tmpl->parseTemplate("gender", "a");
        }
      }
      
      //Javascript code to be called by the Update command
      //Only reason to keep this bit in the PHP file is for the overview
      //writing this on one line in the xml template is unreadable and not maintainable
      $update_command = "postData('edit_cast_edited.php', 'INFORMATION', 
      'view_cast_name=' + escape(encodeURI(document.getElementById('view_cast_name').value)) + " . 
      "'&amp;view_cast_cast_id=' + escape(encodeURI(document.getElementById('view_cast_cast_id').value)) + " . 
      "'&amp;view_cast_birth_date=' + escape(encodeURI(document.getElementById('view_cast_birth_date').value)) + " . 
      "'&amp;view_cast_description=' + escape(encodeURI(document.getElementById('view_cast_description').value)) + " . 
      "'&amp;view_cast_gender_id=' + escape(encodeURI(document.getElementById('view_cast_gender_id').value)))";
      
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
