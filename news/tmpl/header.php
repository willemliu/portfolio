<?php
if(isset($relNext) === false)
{
  $relNext = "";
}

if(isset($relPrev) === false)
{
  $relPrev = "";
}

<<<<<<< HEAD

echo <<< EOF

=======
$protocol = strtolower(array_shift(explode("/",$_SERVER['SERVER_PROTOCOL'])));

echo <<< EOF
>>>>>>> Cleaned up a bit.
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
<<<<<<< HEAD
          <a href="./" class="hasTransitionOut"><span class="liu breathing">廖</span></a>
=======
          <a href="{$protocol}://{$_SERVER["SERVER_NAME"]}" class="hasTransitionOut"><span class="liu breathing">廖</span></a>
>>>>>>> Cleaned up a bit.
        </li>
        <!-- Remove the class "menu-icon" to get rid of menu icon. Take out "Menu" to just have icon alone -->
        <li class="toggle-topbar menu-icon"><a href="#"><span>Menu</span></a></li>
      </ul>

      <section class="top-bar-section">
        <!-- Left Nav Section -->
        <ul class="left">
          <li class="divider"></li>
<<<<<<< HEAD
          <li><a href="http://www.willemliu.nl">willemliu.nl</a></li>
=======
          <li><a href="{$protocol}://{$_SERVER["SERVER_NAME"]}/news">News</a></li>
          <li class="divider"></li>
          <li class="has-dropdown"><a href="#">Websites</a>

            <ul class="dropdown">
              <li class="divider"></li>
              <li class="has-dropdown"><a href="#">External projects</a>
                <ul class="dropdown">
                  <li class="divider"></li>
                  <li><a href="http://fd.nl" target="_BLANK" rel="nofollow">FD.nl</a></li>
                  <li><a href="http://bnr.nl" target="_BLANK" rel="nofollow">BNR.nl</a></li>
                  <li><a href="http://fdmg.nl" target="_BLANK" rel="nofollow">FDMG.nl</a></li>
                  <li><a href="http://www.misterbubbletea.nl" target="_BLANK" rel="nofollow">MisterBubbleTea.nl</a></li>
                  <li><a href="http://www.karlijnscholten.nl" target="_BLANK" rel="nofollow">Karlijnscholten.nl</a></li>
                </ul>
              </li>
              <li class="divider"></li>
              <li class="has-dropdown"><a href="#">Personal projects</a>
                <ul class="dropdown">
                  <li class="divider"></li>
                  <li><a href="http://willemliu.nl/easylist" target="_BLANK">EasyList</a></li>
                  <li><a href="http://willemliu.nl/movies" target="_BLANK">My Movie Database</a></li>
                  <li><a href="http://willemliu.nl/games" target="_BLANK">Games Website</a></li>
                  <li><a href="http://willemliu.nl/ibood" target="_BLANK">iBood Hunt Checker</a></li>
                </ul>
              </li>
            </ul>
          </li>
          <li class="divider"></li>
          <li class="has-dropdown"><a href="#" class="hasTransitionOut">Software</a>
            <ul class="dropdown">
              <li class="divider"></li>
              <li class="has-dropdown"><a href="#">EasyList</a>
                <ul class="dropdown">
                  <li class="divider"></li>
                  <li class="has-dropdown"><a href="#">Nokia Symbian Belle+</a>
                    <ul class="dropdown">
                      <li class="divider"></li>
                      <li><a href="http://store.ovi.com/content/296668" target="_BLANK" rel="nofollow">Nokia Store</a></li>
                      <li><a href="https://github.com/willemliu/EasyList-Harmattan" target="_BLANK" rel="nofollow">Source repository</a></li>
                      <li><a href="http://talk.maemo.org/showthread.php?t=75894&amp;highlight=easylist" target="_BLANK" rel="nofollow">Community maemo.org</a></li>
                    </ul>
                  </li>
                  <li class="has-dropdown"><a href="#">Nokia N9 (Meego-Harmattan)</a>
                    <ul class="dropdown">
                      <li class="divider"></li>
                      <li><a href="http://store.ovi.com/content/178837" target="_BLANK" rel="nofollow">Nokia Store</a></li>
                      <li><a href="https://github.com/willemliu/EasyList-Harmattan" target="_BLANK" rel="nofollow">Source repository</a></li>
                      <li><a href="http://talk.maemo.org/showthread.php?t=75894&amp;highlight=easylist" target="_BLANK" rel="nofollow">Community maemo.org</a></li>
                    </ul>
                  </li>
                  <li class="has-dropdown"><a href="#">Nokia N900 (Maemo)</a>
                    <ul class="dropdown">
                      <li class="divider"></li>
                      <li><a href="http://maemo.org/downloads/product/Maemo5/easylist/" target="_BLANK" rel="nofollow">Nokia Store</a></li>
                      <li><a href="https://gitorious.org/easylist" target="_BLANK" rel="nofollow">Source repository</a></li>
                      <li><a href="http://talk.maemo.org/showthread.php?t=62280&amp;highlight=easylist" target="_BLANK" rel="nofollow">Community maemo.org</a></li>
                    </ul>
                  </li>
                </ul>
              </li>
              <li class="has-dropdown"><a href="#">EasyNote</a>
                <ul class="dropdown">
                  <li class="divider"></li>
                  <li><a href="http://store.ovi.com/content/224492" target="_BLANK" rel="nofollow">Nokia Store</a></li>
                  <li><a href="https://github.com/willemliu/EasyNote-Harmattan" target="_BLANK" rel="nofollow">Source repository</a></li>
                  <li><a href="http://talk.maemo.org/showthread.php?t=80370&amp;highlight=easynote" target="_BLANK" rel="nofollow">Community maemo.org</a></li>
                </ul>
              </li>
              <li><a href="http://store.ovi.com/publisher/Willem%20Liu/" target="_BLANK" rel="nofollow">Nokia Store Publisher page</a></li>
            </ul>
          </li>
          <li class="divider"></li>
          <li><a href="{$protocol}://{$_SERVER["SERVER_NAME"]}/photos" class="hasTransitionOut">Photography</a></li>
          <li class="divider"></li>
          <li><a href="{$protocol}://{$_SERVER["SERVER_NAME"]}/videos" class="hasTransitionOut">Videos</a></li>
          <li class="divider"></li>
        </ul>

        <!-- Right Nav Section -->
        <ul class="right">
          <li class="divider hide-for-small"></li>
          <li class="has-dropdown"><a href="#">Social</a>

            <ul class="dropdown">
              <li><a href="https://linkedin.com/pub/willem-liu/21/383/796" target="_BLANK" rel="nofollow">LinkedIn</a></li>
              <li><a href="https://twitter.com/willemliu" target="_BLANK" rel="nofollow">Twitter</a></li>
              <li><a href="https://facebook.com/willemliu" target="_BLANK" rel="nofollow">Facebook</a></li>
              <li><a href="https://plus.google.com/100501774777586741763/" target="_BLANK" rel="nofollow">Google Plus</a></li>
            </ul>
          </li>
>>>>>>> Cleaned up a bit.
        </ul>
      </section>
    </nav>
  </div>

  <div class="top-bar-dummy">&nbsp;</div>
EOF;
?>