<?php
  $title = "Willem Liu - 廖偉麟 - Photos";
  $description = "Willem Liu: Photos I made.";
  $summary = $description;
  $max_len = 197;
  if(strlen($description) > $max_len)
    $summary = substr($description, 0, $max_len) . '...';

  $keywords = "willem liu, news, rss, feed, photos, fotos";
  $image = "http://www.willemliu.nl/img/me.jpg";
  $url = "http://www.willemliu.nl/photos/";
  include_once("tmpl/header.php");
  include_once("tmpl/home.php");
  include_once("tmpl/footer.php");
?>