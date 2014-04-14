var locationOnAnimationEnd = null;

// Start Zurb Foundation.
$(document).foundation();

// Do stuff when page ready.
$(document).ready(function(){
  $('.backToTop').click(function () {
    $('body,html').animate({
      scrollTop: 0
    }, 800);
    return false;
  });

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
  
  // Force jquery appear.
  $.force_appear();
});

/**
 * Function for infinite scroll.
 */
function showMoreResults(url){
  $.ajax({
    url: url,
    cache: false
  })
  .done(function(data) {
    $(data).insertBefore('.infiniteScroll');
  })
  .fail(function(data) { alert(data); })
  .always(function(data) { /*DONE*/ });
}
