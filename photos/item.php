<?php

  $guid = $_REQUEST["guid"];
  $file_or_url = "whysofunny.xml";
  if(!preg_match('/^http:/i', $file_or_url))
      $feed_uri = './xml/'. $file_or_url;
  else
      $feed_uri = $file_or_url;

  $xml_source = file_get_contents($feed_uri);
  $xml_source = mb_convert_encoding($xml_source, 'UTF-8', mb_detect_encoding($xml_source, 'UTF-8, ISO-8859-1', true));
  $x = simplexml_load_string($xml_source);
  $x->registerXPathNamespace ("content", "http://purl.org/rss/1.0/modules/content/");

  if(count($x) == 0)
      return;

  $count = 0;
  $items = $x->xpath("channel/item/guid[.='$guid']/parent::*");
  $prev = $x->xpath("channel/item/guid[.='$guid']/../following-sibling::item[position()=1]");
  $next = $x->xpath("channel/item/guid[.='$guid']/../preceding-sibling::item[position()=1]");
  $prevButtonHeader = '<div class="large-6 columns">&nbsp;</div>';
  $nextButtonHeader = '<div class="large-6 columns">&nbsp;</div>';
  if(count($prev) > 0)
  {
    $prevUrl = ((string) $prev[0]->link);
    $prevTitle = (string) $prev[0]->title;
    $prevUrlTitle = str_replace("\"", "&quot;", $prevTitle);
$prevButtonHeader = <<< EOT
    <div class="large-6 columns">
      <a class="button expand secondary" href="$prevUrl" title="$prevUrlTitle">Previous</a>
    </div>
EOT;
$prevButtonFooter = <<< EOT
  <a class="button expand" href="$prevUrl" title="$prevUrlTitle">Previous</a>
EOT;
    $relPrev = "<link rel='prev' href='" . $prevUrl . "' />";

  }
  if(count($next) > 0)
  {
    $nextUrl = ((string) $next[0]->link);
    $nextTitle = (string) $next[0]->title;
    $nextUrlTitle = str_replace("\"", "&quot;", $nextTitle);
$nextButtonHeader = <<< EOT
    <div class="large-6 columns">
      <a class="button expand secondary" href="$nextUrl" title="$nextUrlTitle">Next</a>
    </div>
EOT;
$nextButtonFooter = <<< EOT
  <a class="button expand" href="$nextUrl" title="$nextUrlTitle">Next</a>
EOT;
    $relNext = "<link rel='next' href='" . $nextUrl . "' />";
  }
  
  foreach($items as $item)
  {
    $date = (string) $item->pubDate;
    $ts = strtotime($item->pubDate);
    $dateTime = date('H:i d-M-Y', $ts);
    $link = (string) $item->link;
    $urlLink = urlencode($link);
    $title = (string) $item->title;
    $urlTitle = urlencode($title);
    $text = (string) $item->description;
    $encodedText = "";
    foreach($item->xpath("content:encoded") as $key => $value)
    {
      $encodedText .= $value;
    }
    
    // Create summary as a shortened body and remove images, extraneous line breaks, etc.
    $summary = $text;
    $summary = preg_replace("/<img[^>]*>/i", "", $summary);
    $summary = preg_replace("/^(<br[ ]?\/>)*/i", "", $summary);
    $summary = preg_replace("/(<br[ ]?\/>)*$/i", "", $summary);

    // Truncate summary line to 100 characters
    $max_len = 100;
    if(strlen($summary) > $max_len)
        $summary = substr($summary, 0, $max_len) . '...';

$echo = <<< EOT
      <div class="row textColor paddingTop">
        $prevButtonHeader
        $nextButtonHeader
        <div class="large-12 columns">
          <div class="panel lifted">
            <h4>$title</h4>
            <small>$dateTime</small>
            <span class="right">
              <a href="https://www.facebook.com/sharer/sharer.php?u=$urlLink" target="_BLANK" rel="nofollow"><img src="img/fb.png" alt="Share on Facebook"/></a>
              <a href="https://twitter.com/intent/tweet?url=$urlLink&text=$urlTitle&hashtags=willemliu&via=willemliu" target="_BLANK" rel="nofollow"><img src="img/tw.png" alt="Share on Twitter"/></a>
              <a href="http://www.linkedin.com/shareArticle?mini=true&url=$urlLink&title=$urlTitle&source=Willem Liu" target="_BLANK" rel="nofollow"><img src="img/in.png" alt="Share on LinkedIn"/></a>
              <a href="https://plus.google.com/share?url=$urlLink" onclick="javascript:window.open(this.href, '', 'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=600,width=600');return false;"><img src="img/gplus.png" alt="Share on Google+"/></a>
            </span>
            <hr/>
              <p>$text</p>
              <div style="text-align: center;" >
EOT;
    foreach($item->enclosure as $enclosure){
      if($enclosure->attributes()->type == "image/jpeg")
      {
        $imageUrl = $enclosure->attributes()->url;
$echo .= <<< EOT
        <img src="$imageUrl"/>
EOT;
      }
      else if($enclosure->attributes()->type == "html/youtube-url")
      {
        $videoUrl = $enclosure->attributes()->url;
        $parts = split("/", $videoUrl);
        $videoId = $parts[sizeof($parts)-1];
        $imageUrl = "http://img.youtube.com/vi/$videoId/hqdefault.jpg";
$echo .= <<< EOT
        <div class="video-container">
          <iframe width="420" height="315" src="$videoUrl" frameborder="0" allowfullscreen></iframe>
        </div>
EOT;
      }
      else if($enclosure->attributes()->type == "html/vimeo-url")
      {
        $videoUrl = $enclosure->attributes()->url;
        $parts = split("/", $videoUrl);
        $videoId = $parts[sizeof($parts)-1];
        $hash = unserialize(file_get_contents("http://vimeo.com/api/v2/video/$videoId.php"));
        $imageUrl = $hash[0]['thumbnail_medium']; 
$echo .= <<< EOT
        <div class="video-container">
          <iframe width="420" height="315" src="$videoUrl" frameborder="0" allowfullscreen></iframe>
        </div>
EOT;
      }
    }
    
$echo .= <<< EOT
          </div>
          <div>$encodedText</div>
          <hr />
          <span class="left">
            <a href="https://www.facebook.com/sharer/sharer.php?u=$urlLink" target="_BLANK" rel="nofollow"><img src="img/fb.png" alt="Share on Facebook"/></a>
            <a href="https://twitter.com/intent/tweet?url=$urlLink&text=$urlTitle&hashtags=willemliu&via=willemliu" target="_BLANK" rel="nofollow"><img src="img/tw.png" alt="Share on Twitter"/></a>
            <a href="http://www.linkedin.com/shareArticle?mini=true&url=$urlLink&title=$urlTitle&source=Willem Liu" target="_BLANK" rel="nofollow"><img src="img/in.png" alt="Share on LinkedIn"/></a>
            <a href="https://plus.google.com/share?url=$urlLink" onclick="javascript:window.open(this.href, '', 'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=600,width=600');return false;"><img src="img/gplus.png" alt="Share on Google+"/></a>
          </span>
          <small class="right">&copy;<a style="color: #222222" href="https://plus.google.com/100501774777586741763/?rel=author" target="_BLANK" rel="author">Willem Liu</a></small>
          <div class="paddingTop" style="clear: both;"></div>
          <a id="comments"></a>
          <div class="fb-comments" data-href="$link" data-width="470" data-num-posts="10"></div>
          <style>
            #fbcomments, .fb-comments, .fb-comments iframe[style], .fb-comments span {
              width: 100% !important;
            }
          </style>
        </div>
      </div>
    </div>
EOT;

    $title = "Willem Liu - 廖偉麟 - " . htmlentities(preg_replace('/\s+/', ' ', trim($title)));
    $description = htmlentities(preg_replace('/\s+/', ' ', trim($summary)));
    $summary = $description;
    $max_len = 197;
    if(strlen($description) > $max_len)
      $summary = substr($description, 0, $max_len) . '...';
    $keywords = "willem liu, photos, fotos, " . $title;
    $image = $imageUrl;
    $url = $link;
    
    /**
     * The actual output of the page.
     */
    include_once("tmpl/header.php");
    echo $echo;
    include_once("tmpl/footer.php");
  }

?>