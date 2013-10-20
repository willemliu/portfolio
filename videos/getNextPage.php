<?php 
  $offset = intval($_REQUEST["offset"]);
  $newOffset = $offset;
  $hasMore = false;
  $maxItemsPerPage = 1;
  
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
  $itemCount = $x->channel->item->count();
  for($i = $offset; $i < $itemCount; $i++)
  {
    $item = $x->channel->item[$i];
    if($count >= ($maxItemsPerPage))
    {
      $hasMore = true;
      break;
    }
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
    $guid = (string) $item->guid;
    
    // Create summary as a shortened body and remove images, extraneous line breaks, etc.
    $summary = $text;
    $summary = preg_replace("/<img[^>]*>/i", "", $summary);
    $summary = preg_replace("/^(<br[ ]?\/>)*/i", "", $summary);
    $summary = preg_replace("/(<br[ ]?\/>)*$/i", "", $summary);

    // Truncate summary line to 100 characters
    $max_len = 100;
    if(strlen($summary) > $max_len)
        $summary = substr($summary, 0, $max_len) . '...';

echo <<< EOT
      <div class="row textColor paddingTop">
        <div class="large-12 columns">
          <div class="panel lifted">
            <a href="$link"><h4>$title</h4></a>
            <a href="$link"><small class="textColor">$dateTime</small></a>
            <a href="$link#comments"><small class="textColor" id="$guid"></small></a>
            <span class="right">
              <a href="https://www.facebook.com/sharer/sharer.php?u=$urlLink" target="_BLANK" rel="nofollow"><img src="img/fb.png" alt="Share on Facebook"/></a>
              <a href="https://twitter.com/intent/tweet?url=$urlLink&text=$urlTitle&hashtags=willemliu&via=willemliu" target="_BLANK" rel="nofollow"><img src="img/tw.png" alt="Share on Twitter"/></a>
              <a href="http://www.linkedin.com/shareArticle?mini=true&url=$urlLink&title=$urlTitle&source=Willem Liu" target="_BLANK" rel="nofollow"><img src="img/in.png" alt="Share on LinkedIn"/></a>
              <a href="https://plus.google.com/share?url=$urlLink" onclick="javascript:window.open(this.href, '', 'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=600,width=600');return false;"><img src="img/gplus.png" alt="Share on Google+"/></a>
            </span>
            <hr/>
              <a href="$link"><p class="textColor">$text</p></a>
              <div style="text-align: center;" >
EOT;
    foreach($item->enclosure as $enclosure){
      if($enclosure->attributes()->type == "image/jpeg")
      {
        $imageUrl = $enclosure->attributes()->url;
echo <<< EOT
        <a href="$link"><img src="$imageUrl"/></a>
EOT;
      }
      else if($enclosure->attributes()->type == "html/youtube-url" || $enclosure->attributes()->type == "html/vimeo-url")
      {
        $videoUrl = $enclosure->attributes()->url;
echo <<< EOT
        <div class="video-container">
          <iframe width="420" height="315" src="$videoUrl" frameborder="0" allowfullscreen></iframe>
        </div>
EOT;
      }
    }
echo <<< EOT
          </div>
          <a href="$link"><div class="textColor">$encodedText</div></a>
          <hr />
          <span class="left">
            <a href="https://www.facebook.com/sharer/sharer.php?u=$urlLink" target="_BLANK" rel="nofollow"><img src="img/fb.png" alt="Share on Facebook"/></a>
            <a href="https://twitter.com/intent/tweet?url=$urlLink&text=$urlTitle&hashtags=willemliu&via=willemliu" target="_BLANK" rel="nofollow"><img src="img/tw.png" alt="Share on Twitter"/></a>
            <a href="http://www.linkedin.com/shareArticle?mini=true&url=$urlLink&title=$urlTitle&source=Willem Liu" target="_BLANK" rel="nofollow"><img src="img/in.png" alt="Share on LinkedIn"/></a>
            <a href="https://plus.google.com/share?url=$urlLink" onclick="javascript:window.open(this.href, '', 'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=600,width=600');return false;"><img src="img/gplus.png" alt="Share on Google+"/></a>
          </span>
          <small class="right">&copy;<a style="color: #222222" href="https://plus.google.com/100501774777586741763/?rel=author" target="_BLANK" rel="author">Willem Liu</a></small>
          <div style="clear: both;"></div>
          <script>
            $(document).ready(function(){
              setFbCommentCount($("#$guid"), "$link");
            });
          </script>
        </div>
      </div>
    </div>
EOT;
    $count++;
    $newOffset++;
  }
  
  if($hasMore)
  {
echo <<< EOT
  <script>
    $(".infiniteScroll").appear();
    
    $(document.body).on('appear', '.infiniteScroll', function(e) {
      console.log("load next page");
      $(document.body).off('appear', '.infiniteScroll');
      showMoreResults("getNextPage.php?offset=$newOffset");
    });
  </script>
EOT;
  }
  else
  {
echo <<< EOT
    <script>
      $(".loader").hide();
    </script>
EOT;
  }

?>