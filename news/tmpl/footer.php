<?php
if(isset($footer) === false)
{
$footer = <<< EOT
    <script>
      $(".loader").hide();
    </script>
EOT;
}

if(isset($nextUrl) === false)
{
  $nextButtonFooter = "&nbsp;";
}

if(isset($prevUrl) === false)
{
  $prevButtonFooter = "&nbsp;";
}


echo <<< EOT
    <div class="infiniteScroll"></div>
    <div class="loader row">
      <div class="large-12 columns">
        <img src="img/loading.gif" style="display: block; margin-left: auto; margin-right: auto;">
      </div>
    </div>
    <div class="row">
      <div class="small-4 columns">
        $prevButtonFooter
      </div>
      <div class="small-4 columns paddingBottom">
        <a class="backToTop button expand secondary" href="javascript: void(0);">Back to top</a>
      </div>
      <div class="small-4 columns">
        $nextButtonFooter
      </div>
    </div>

  <script src="js/willemliu.min.js"></script>
  $footer
</body>
</html>
EOT;
?>
