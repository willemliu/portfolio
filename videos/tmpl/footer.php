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

  <script>
    document.write('<script src=' +
    ('__proto__' in {} ? 'js/vendor/zepto' : 'js/vendor/jquery') +
    '.js><\/script>')
  </script>
  <script>
    $(document).ready(function(){
      $('.backToTop').click(function () {
        $('body,html').animate({
          scrollTop: 0
        }, 800);
        return false;
      });
    });
    function setFbCommentCount(obj, url){
      $.ajax({
        url: "http://graph.facebook.com/?callback=?",
        dataType: 'json',
        data: {
          ids: url
        },
        success: function(json){
          $(obj).html(" | 0 comments");
          for(link in json)
          {
            var commentsNum = json[link].comments;
            if(commentsNum == null || commentsNum == undefined)
            {
              commentsNum = 0;
            }
            if(parseInt(commentsNum))
            {
              var comments = " comments";
              if(parseInt(commentsNum) == 1)
              {
                comments = " comment";
              }
              $(obj).html("| " + commentsNum.toString() + comments);
            }
            break;
          }
        },
        error: function(){
          $(obj).html(" | 0 comments");
        }
      });
    }
  </script>
  <script src="js/willemliu.min.js"></script>
  <script src="js/ga.js"></script>
  $footer
</body>
</html>
EOT;
?>
