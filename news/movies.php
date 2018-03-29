<?php
  /**
   * Turn on all error reporting.
   */
  error_reporting(E_ALL ^ (E_STRICT | E_DEPRECATED | E_NOTICE));
  ini_set('display_errors', 1);

  $title = "News";
  $description = "Here you can find the news which interests me. Most of the news is Dutch. I'm sorry for that.";
  $twitterCard = $description;
  $max_len = 197;
  if(strlen($description) > $max_len)
    $twitterCard = substr($description, 0, $max_len) . '...';

  $keywords = "willem liu, news, rss, feed";
  $image = "http://www.willemliu.nl/img/me.jpg";
  $url = "http://www.willemliu.nl";
  $xmlUrl = "https://www.reddit.com/r/movies/.rss";
  include_once("tmpl/header.php");
  include_once("tmpl/home.php");
  include_once("tmpl/footer.php");
?>
