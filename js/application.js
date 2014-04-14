var locationOnAnimationEnd = null;

// Do stuff when page ready.
$(document).ready(function(){

  if(Modernizr.cssanimations)
  {
    /**
     * On all inner <a> elements without a target we do an exit animation.
     */
    $(".hasTransitionOut").click(function(event){
      event.preventDefault();
      locationOnAnimationEnd = $(this).attr("href");
      if(locationOnAnimationEnd != null && 
        locationOnAnimationEnd.length > 0 && 
        locationOnAnimationEnd != "#"){
        $("body").addClass("rotateOutTop");
      }
    });
  }
});

/**
 * When animation on the body has ended we redirect to the locationOnAnimationEnd.
 */
$("body").on('oanimationend animationend webkitAnimationEnd MSAnimationEnd',
function (){
  if(locationOnAnimationEnd != null && locationOnAnimationEnd.length > 0){
    window.location = locationOnAnimationEnd;
  }
});

// Start Zurb Foundation.
$(document).foundation();
