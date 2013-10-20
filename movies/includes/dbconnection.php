<?php
	$_SESSION['dbUsername'] = "willemliu_games";
	$_SESSION['dbPassword'] = "games";
	$_SESSION['number_of_database_queries'] = 0;
	
	function DBConnect()
	{
		$_SESSION['dbConnection'] = mysql_connect ("localhost", $_SESSION['dbUsername'], $_SESSION['dbPassword']) or die ('Unable to connect to the database because: ' . mysql_error());
		mysql_select_db("willemliu_movie_collection", $_SESSION['dbConnection']) or die("Cannot select database: " . mysql_error());
		return $_SESSION['dbConnection'];
	}
	
	function DBDisconnect()
	{
		mysql_close($_SESSION['dbConnection']);
	}

	function DBQuery($sQuery){
	  if(!@mysql_query('show tables', $_SESSION['dbConnection'])){
      DBConnect();
    }
	  $_SESSION['number_of_database_queries']++;
		return mysql_query($sQuery, $_SESSION['dbConnection']);
	}
?>
