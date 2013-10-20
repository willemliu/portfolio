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
	$template_file = "member_films.tmpl.xml";
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
    $section_result = DBQuery("SELECT security_level FROM wmc_sections_security_levels WHERE section='Member Films'");
    //Check security level
    if(mysql_num_rows($section_result) > 0 && $security_level >= mysql_result($section_result, 0, "security_level")){
      $user_id = mysql_result($res, 0, "id");
      FadeIn($tmpl, "MEMBER_FILMS", 0);

      $member_id = mysql_real_escape_string(htmlspecialchars(urlDecode($_POST['member_id'])));
	  $tmpl->addGlobalVar("ID", $member_id);
      $nick_name_result = DBQuery("SELECT nick_name FROM wmc_users WHERE id=" . $member_id);
      if(mysql_num_rows($nick_name_result) > 0){
        $nick_name = mysql_result($nick_name_result, 0, "nick_name");
        $tmpl->addGlobalVar("NICK_NAME", $nick_name);
      }

      //Set window as opened
      $window_check = DBQuery("SELECT id FROM wmc_users_windows WHERE user_id=" . $user_id . " AND window='MEMBER_FILMS'");
      if(mysql_num_rows($window_check) > 0){
        DBQuery("UPDATE wmc_users_windows SET opened='1', page='member_films.php', parameter='member_id=" . $member_id . "' WHERE user_id=" . $user_id . " AND window='MEMBER_FILMS'");
      } else{
        DBQuery("INSERT INTO wmc_users_windows (x, y, opened, page, parameter, window, user_id) VALUES ('80', '110', '1', 'member_films.php', 'member_id=" . $member_id . "', 'MEMBER_FILMS', '" . $user_id . "')");
      }

      //Check which columns to show or hide
      $column_result = DBQuery("SELECT * FROM wmc_users_films_columns WHERE user_id=" . $user_id);
      $column_num = mysql_num_rows($column_result);
      if($column_num > 0){
        for($i = 0; $i < $column_num; $i++){
          $tmpl->addGlobalVar("SHOW_TITLE", "none");
          $tmpl->addGlobalVar("SHOW_DURATION", "none");
          $tmpl->addGlobalVar("SHOW_SOUND_MIX", "none");
          $tmpl->addGlobalVar("SHOW_SUBTITLE", "none");
          $tmpl->addGlobalVar("SHOW_GENRE", "none");
          $tmpl->addGlobalVar("SHOW_MEDIUM", "none");
          $tmpl->addGlobalVar("SHOW_REGION", "none");
          $tmpl->addGlobalVar("SHOW_ASPECT_RATIO", "none");
          $tmpl->addGlobalVar("SHOW_COLOR", "none");
          $tmpl->addGlobalVar("SHOW_CAST", "none");
          $tmpl->addGlobalVar("SHOW_TRAILER", "none");
          
          if(mysql_result($column_result, $i, "title")){
            $show_title = true;
            $tmpl->addGlobalVar("SHOW_TITLE", "table-cell");
          }
          if(mysql_result($column_result, $i, "duration")){
            $show_duration = true;
            $tmpl->addGlobalVar("SHOW_DURATION", "table-cell");
          }
          if(mysql_result($column_result, $i, "sound_mix")){
            $show_sound_mix = true;
            $tmpl->addGlobalVar("SHOW_SOUND_MIX", "table-cell");
          }
          if(mysql_result($column_result, $i, "subtitle")){
            $show_subtitle = true;
            $tmpl->addGlobalVar("SHOW_SUBTITLE", "table-cell");
          }
          if(mysql_result($column_result, $i, "genre")){
            $show_genre = true;
            $tmpl->addGlobalVar("SHOW_GENRE", "table-cell");
          }
          if(mysql_result($column_result, $i, "medium")){
            $show_medium = true;
            $tmpl->addGlobalVar("SHOW_MEDIUM", "table-cell");
          }
          if(mysql_result($column_result, $i, "region")){
            $show_region = true;
            $tmpl->addGlobalVar("SHOW_REGION", "table-cell");
          }
          if(mysql_result($column_result, $i, "aspect_ratio")){
            $show_aspect_ratio = true;
            $tmpl->addGlobalVar("SHOW_ASPECT_RATIO", "table-cell");
          }
          if(mysql_result($column_result, $i, "color")){
            $show_color = true;
            $tmpl->addGlobalVar("SHOW_COLOR", "table-cell");
          }
          if(mysql_result($column_result, $i, "cast")){
            $show_cast = true;
            $tmpl->addGlobalVar("SHOW_CAST", "table-cell");
          }
          if(mysql_result($column_result, $i, "trailer")){
            $show_trailer = true;
            $tmpl->addGlobalVar("SHOW_TRAILER", "table-cell");
          }
        }
      }
      
      //Show films
      $film_result = DBQuery("SELECT films.id, users_films.id, films.title, films.duration, colors.color FROM wmc_films AS films, wmc_users_films AS users_films, wmc_colors AS colors WHERE films.title REGEXP '^a' AND films.id=users_films.film_id AND films.color_id=colors.id AND users_films.user_id=" . $member_id . " ORDER BY films.title ASC");
      $film_num = mysql_num_rows($film_result);
      if($film_num > 0){
        $tmpl->addVar("show_rows", "CONDITION", "true");
        for($i = 0; $i < $film_num; $i++){
          //Default values
          $tmpl->addVar("rows", "ID", "&amp;nbsp;");
          $tmpl->addVar("rows", "TITLE", "&amp;nbsp;");
          $tmpl->addVar("rows", "DURATION", "&amp;nbsp;");
          $tmpl->addVar("rows", "MEDIUM", "&amp;nbsp;");
          $tmpl->addVar("rows", "SOUNDMIX", "&amp;nbsp;");
          $tmpl->addVar("rows", "SUBTITLE", "&amp;nbsp;");
          $tmpl->addVar("rows", "GENRE", "&amp;nbsp;");
          $tmpl->addVar("rows", "REGION", "&amp;nbsp;");
          $tmpl->addVar("rows", "COLOR", "&amp;nbsp;");
          $tmpl->addVar("rows", "ASPECT_RATIO", "&amp;nbsp;");
          $tmpl->addVar("rows", "CAST", "&amp;nbsp;");
          $tmpl->addVar("rows", "TRAILER", "&amp;nbsp;");
          $tmpl->addVar("rows", "HAS_CAST", "");

          //Film title, duration and color
          $film_id = mysql_result($film_result, $i, "films.id");
          $user_film_id = mysql_result($film_result, $i, "users_films.id");
          $tmpl->addVar("rows", "ID", mysql_result($film_result, $i, "films.id"));
          if($show_title){
            $tmpl->addVar("rows", "TITLE", stripslashes(htmlspecialchars(mysql_result($film_result, $i, "films.title"))));
          }
          if($show_duration){
            $tmpl->addVar("rows", "DURATION", mysql_result($film_result, $i, "films.duration"));
          }
          if($show_color){
            $tmpl->addVar("rows", "COLOR", mysql_result($film_result, $i, "colors.color"));
          }

          //Mediums
          if($show_medium){
            $medium_result = DBQuery("SELECT mediums.medium FROM wmc_mediums AS mediums, wmc_users_films_mediums AS users_films_mediums WHERE users_films_mediums.medium_id=mediums.id AND users_films_mediums.user_film_id=" . $user_film_id . " ORDER BY mediums.medium ASC");
            $medium_num = mysql_num_rows($medium_result);
            $val = "";
            for($j = 0; $j < $medium_num; $j++){
              $val .= mysql_result($medium_result, $j, "mediums.medium") . "&lt;br&gt;";
            }
            $tmpl->addVar("rows", "MEDIUM", $val);
          }

          //Sound mixes
          if($show_sound_mix){
            $sound_mix_result = DBQuery("SELECT sound_mixes.sound_mix FROM wmc_sound_mixes AS sound_mixes, wmc_users_films_sound_mixes AS users_films_sound_mixes WHERE users_films_sound_mixes.sound_mix_id=sound_mixes.id AND users_films_sound_mixes.user_film_id=" . $user_film_id . " ORDER BY sound_mixes.sound_mix ASC");
            $sound_mix_num = mysql_num_rows($sound_mix_result);
            $val = "";
            for($j = 0; $j < $sound_mix_num; $j++){
              $val .= mysql_result($sound_mix_result, $j, "sound_mixes.sound_mix") . "&lt;br&gt;";
            }
            $tmpl->addVar("rows", "SOUND_MIX", $val);
          }

          //Subtitles
          if($show_subtitle){
            $subtitle_result = DBQuery("SELECT subtitles.subtitle FROM wmc_subtitles AS subtitles, wmc_users_films_subtitles AS users_films_subtitles WHERE users_films_subtitles.subtitle_id=subtitles.id AND users_films_subtitles.user_film_id=" . $user_film_id . " ORDER BY subtitles.subtitle ASC");
            $subtitle_num = mysql_num_rows($subtitle_result);
            $val = "";
            for($j = 0; $j < $subtitle_num; $j++){
              $val .= mysql_result($subtitle_result, $j, "subtitles.subtitle") . "&lt;br&gt;";
            }
            $tmpl->addVar("rows", "SUBTITLE", $val);
          }

          //Genres
          if($show_genre){
            $genre_result = DBQuery("SELECT genres.genre FROM wmc_genres AS genres, wmc_films_genres AS films_genres WHERE genres.id=films_genres.genre_id AND films_genres.film_id='" . $film_id . "' ORDER BY genres.genre ASC");
            $genre_num = mysql_num_rows($genre_result);
            $val = "";
            for($j = 0; $j < $genre_num; $j++){
              $val .= mysql_result($genre_result, $j, "genres.genre") . "&lt;br&gt;";
            }
            $tmpl->addVar("rows", "GENRE", $val);
          }

          //Aspect Ratio
          if($show_aspect_ratio){
            $aspect_ratio_result = DBQuery("SELECT aspect_ratios.aspect_ratio FROM wmc_aspect_ratios AS aspect_ratios, wmc_users_films_aspect_ratios AS users_films_aspect_ratios WHERE users_films_aspect_ratios.aspect_ratio_id=aspect_ratios.id AND users_films_aspect_ratios.user_film_id=" . $user_film_id . " ORDER BY aspect_ratios.aspect_ratio ASC");
            $aspect_ratio_num = mysql_num_rows($aspect_ratio_result);
            $val = "";
            for($j = 0; $j < $aspect_ratio_num; $j++){
              $val .= mysql_result($aspect_ratio_result, $j, "aspect_ratios.aspect_ratio") . "&lt;br&gt;";
            }
            $tmpl->addVar("rows", "ASPECT_RATIO", $val);
          }

          //Sound mixes
          if($show_region){
            $region_result = DBQuery("SELECT regions.region FROM wmc_regions AS regions, wmc_users_films_regions AS users_films_regions WHERE users_films_regions.region_id=regions.id AND users_films_regions.user_film_id=" . $user_film_id . " ORDER BY regions.region ASC");
            $region_num = mysql_num_rows($region_result);
            $val = "";
            for($j = 0; $j < $region_num; $j++){
              $val .= mysql_result($region_result, $j, "regions.region") . "&lt;br&gt;";
            }
            $tmpl->addVar("rows", "REGION", $val);
          }

          //Cast
          if($show_cast){
            $cast_result = DBQuery("SELECT casts.name FROM wmc_cast AS casts, wmc_films_cast AS films_cast WHERE casts.id=films_cast.cast_id AND films_cast.film_id='" . $film_id . "' ORDER BY casts.name ASC");
            $cast_num = mysql_num_rows($cast_result);
            if($cast_num > 0){
              $tmpl->addVar("rows", "HAS_CAST", "[Show Cast]");
            }
            $val = "";
            for($j = 0; $j < $cast_num; $j++){
              $val .= stripslashes(mysql_result($cast_result, $j, "casts.name")) . "&lt;br&gt;";
            }
            $tmpl->addVar("rows", "CAST", $val);
          }

          //Trailer
          if($show_trailer){
            $trailer_result = DBQuery("SELECT id, title, trailer FROM wmc_films_trailers WHERE film_id='" . $film_id . "' ORDER BY id ASC");
            $trailer_num = mysql_num_rows($trailer_result);
            $val = "";
            for($j = 0; $j < $trailer_num; $j++){
              $val .= "&lt;a onClick=\"postData('view_trailer.php', 'VIEW_TRAILER', 'trailer_id=' + escape(encodeURI(" . mysql_result($trailer_result, $j, "id") . ")));\"&gt;" . mysql_result($trailer_result, $j, "title") . "&lt;/a&gt;&lt;br&gt;";
            }
            $tmpl->addVar("rows", "TRAILER", $val);
          }
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
