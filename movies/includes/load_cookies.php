<?php
	if($_POST['username'] != "" && $_POST['password']){
	  $_SESSION['username'] = utf8_decode(urlDecode($_POST['username']));
	  $_SESSION['password'] = md5(utf8_decode(urlDecode($_POST['password'])));
    setcookie("username", $_SESSION['username'], time()+604800, "");
    setcookie("password", $_SESSION['password'], time()+604800, "");
	} else if(isset($_COOKIE['username']) && isset($_COOKIE['password'])){
    $_SESSION['username'] = $_COOKIE['username'];
    $_SESSION['password'] = $_COOKIE['password'];
  } else{
	  $_SESSION['username'] = "";
	  $_SESSION['password'] = "";
  }
?>
