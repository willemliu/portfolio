<?php
if(isset($relNext) === false)
{
  $relNext = "";
}

if(isset($relPrev) === false)
{
  $relPrev = "";
}


echo <<< EOF

<!DOCTYPE html>
<!--[if IE 8]> 				 <html class="no-js lt-ie9" lang="en" > <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" lang="en" > <!--<![endif]-->

<head>
  <!--[if IE]><meta http-equiv="X-UA-Compatible" content="IE=Edge"><![endif]-->
	<meta charset="utf-8" />
  <meta name="viewport" content="width=device-width" />
  <meta name="description" content="$description"/>
  <meta name="keywords" content="$keywords">
  <meta property="og:title" content="$title"/>
  <meta property="og:image" content="$image"/>
  <meta property="og:site_name" content="$title"/>
  <meta property="og:description" content="$description"/>
  <meta property="og:url" content="$url"/>
  <meta name="twitter:card" content="$twitterCard">
  <meta name="twitter:url" content="$url">
  <meta name="twitter:title" content="$title">
  <meta name="twitter:description" content="$description">
  <meta name="twitter:image" content="$image">
  <meta name="twitter:site" content="@willemliu">
  <meta name="twitter:creator" content="@willemliu">
  <link rel="image_src" href="$image" />
  $relPrev
  $relNext
  
  <title>$title</title>

  <link rel="stylesheet" href="css/willemliu.min.css" />

  <script src="js/vendor/custom.modernizr.js"></script>

</head>
<body>
  <div id="fb-root"></div>
  <script src="js/facebook.js"></script>
  
  <div class="contain-to-grid fixed">
    <nav class="top-bar">
      <ul class="title-area">
        <!-- Title Area -->
        <li class="name">
          <a href="./" class="hasTransitionOut"><span class="liu breathing">廖</span></a>
        </li>
        <!-- Remove the class "menu-icon" to get rid of menu icon. Take out "Menu" to just have icon alone -->
        <li class="toggle-topbar menu-icon"><a href="#"><span>Menu</span></a></li>
      </ul>

      <section class="top-bar-section">
        <!-- Left Nav Section -->
        <ul class="left">
          <li class="divider"></li>
          <li><a href="http://www.willemliu.nl">willemliu.nl</a></li>
        </ul>
      </section>
    </nav>
  </div>

  <div class="top-bar-dummy">&nbsp;</div>
EOF;
?>