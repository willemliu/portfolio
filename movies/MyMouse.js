MyMousePosition.prototype = new Object;

//==============================================================================
//Private variables
//==============================================================================
MyMousePosition.prototype.xMousePos = 0; // Horizontal position of the mouse on the screen
MyMousePosition.prototype.yMousePos = 0; // Vertical position of the mouse on the screen
MyMousePosition.prototype.xMousePosOffset = 0; // Horizontal position of the mouse on the screen without offset
MyMousePosition.prototype.yMousePosOffset = 0; // Vertical position of the mouse on the screen without offset
MyMousePosition.prototype.xMousePosMax = 0; // Width of the page
MyMousePosition.prototype.yMousePosMax = 0; // Height of the page
MyMousePosition.prototype.xScrollOffset = 0; // Horizontal scroll offset of the page
MyMousePosition.prototype.yScrollOffset = 0; // Vertical scroll offset of the page
MyMousePosition.prototype.BrowserWidth = 0; // Width of the browser
MyMousePosition.prototype.BrowserHeight = 0; // Height of the browser
MyMousePosition.prototype.debug_mode = false;

MyMousePosition.prototype.single_click_move = false;
MyMousePosition.prototype.leftmdown = false;
MyMousePosition.prototype.dontmove = false;
MyMousePosition.prototype.moveid = null;
MyMousePosition.prototype.tempx = 0;
MyMousePosition.prototype.tempy = 0;
MyMousePosition.prototype.yMoved = 0;
MyMousePosition.prototype.xMoved = 0;
MyMousePosition.prototype.objectX = 0;
MyMousePosition.prototype.objectY = 0;
MyMousePosition.prototype.move_increment = 1;

//==============================================================================
//Constructor
//==============================================================================
function MyMousePosition(){
  var this_class = this;
  // Set Netscape up to run the "captureMousePosition" function whenever
  // the mouse is moved. For Internet Explorer and Netscape 6, you can capture
  // the movement a little easier.
  if (document.layers) { // Netscape
  	document.captureEvents(Event.MOUSEMOVE);
  	document.captureEvents(Event.KEYPRESS);
  	document.onmousemove = function(e){this_class.captureMousePosition(e)};
  } else if (document.all) { // Internet Explorer
  	document.onmousemove = function(e){this_class.captureMousePosition(e)};
  } else if (document.getElementById) { // Netcsape 6
  	document.onmousemove = function(e){this_class.captureMousePosition(e)};
  }
  document.onmouseup = this.leftup;
}
    
MyMousePosition.prototype.getXMousePosMax = function(){
  return this.xMousePosMax;
}
MyMousePosition.prototype.getYMousePosMax = function(){
  return this.yMousePosMax;
}
MyMousePosition.prototype.getXMousePosOffset = function(){
  return this.xMousePosOffset;
}
MyMousePosition.prototype.getYMousePosOffset = function(){
  return this.yMousePosOffset;
}
MyMousePosition.prototype.getXMousePos = function(){
  return this.xMousePos;
}
MyMousePosition.prototype.getYMousePos = function(){
  return this.yMousePos;
}
MyMousePosition.prototype.getBrowserWidth = function(){
  return this.BrowserWidth;
}
MyMousePosition.prototype.getBrowserHeight = function(){
  return this.BrowserHeight;
}
MyMousePosition.prototype.getXScrollOffset = function(){
  return this.xScrollOffset;
}
MyMousePosition.prototype.getYScrollOffset = function(){
  return this.yScrollOffset;
}

MyMousePosition.prototype.debugOn = function(value){
  this.debug_mode = value;
}

MyMousePosition.prototype.setMoveIncrement = function(value){
  this.move_increment = value;
}

MyMousePosition.prototype.setSingleClickMove = function(value){
  this.single_click_move = value;
}

//Move objects
MyMousePosition.prototype.move = function(){
	if(this.leftmdown && this.moveid != null && !this.dontmove){
		this.objectX = this.objectX + (this.xMousePosOffset - this.xMoved);
		this.objectY = this.objectY + (this.yMousePosOffset - this.yMoved);

    if((this.objectX%this.move_increment) == 0){
      document.getElementById(this.moveid).style.left = this.objectX + "px";
      this.xMoved = this.xMousePosOffset;
    }
    if((this.objectY%this.move_increment) == 0){
      document.getElementById(this.moveid).style.top = this.objectY + "px";
      this.yMoved = this.yMousePosOffset;
    }
	}
}
    
MyMousePosition.prototype.dispensemove = function(){
	this.dontmove = true;
}
    
MyMousePosition.prototype.leftdown = function(id){
  if(this.single_click_move){
    if(this.leftmdown){
    	this.leftmdown = false;
    	this.moveid = null;
    	this.dontmove = false;
    } else{
    	this.leftmdown = true;
    	this.moveid = id;
    }
  } else{
  	this.leftmdown = true;
  	this.moveid = id;
  }
	var x = document.getElementById(this.moveid).style.left;
	var xArray = x.split("px");
	this.objectX = parseInt(xArray[0], 10);
	var y = document.getElementById(this.moveid).style.top;
	var yArray = y.split("px");
	this.objectY = parseInt(yArray[0], 10);
  this.xMoved = this.xMousePosOffset;
  this.yMoved = this.yMousePosOffset;
}
    
MyMousePosition.prototype.leftup = function(){
  if(!this.single_click_move){
  	this.leftmdown = false;
  	this.moveid = null;
  	this.dontmove = false;
  }
}

MyMousePosition.prototype.captureMousePosition = function(e){
  if (document.layers) {
    // When the page scrolls in Netscape, the event's mouse position
    // reflects the absolute position on the screen. innerHight/Width
    // is the position from the top/left of the screen that the user is
    // looking at. pageX/YOffset is the amount that the user has
    // scrolled into the page. So the values will be in relation to
    // each other as the total offsets into the page, no matter if
    // the user has scrolled or not.
    this.xMousePos = e.pageX;
    this.yMousePos = e.pageY;
    this.xMousePosOffset = e.pageX;
    this.yMousePosOffset = e.pageY;
    this.xMousePosMax = window.innerWidth+window.pageXOffset;
    this.yMousePosMax = window.innerHeight+window.pageYOffset;
    this.BrowserWidth = window.innerWidth;
    this.BrowserHeight = window.innerHeight;
    this.xScrollOffset = window.pageXOffset;
    this.yScrollOffset = window.pageYOffset;
  } else if (document.all) {
    // When the page scrolls in IE, the event's mouse position
    // reflects the position from the top/left of the screen the
    // user is looking at. scrollLeft/Top is the amount the user
    // has scrolled into the page. clientWidth/Height is the height/
    // width of the current page the user is looking at. So, to be
    // consistent with Netscape (above), add the scroll offsets to
    // both so we end up with an absolute value on the page, no
    // matter if the user has scrolled or not.
    this.xMousePos = window.event.x+document.body.scrollLeft;
    this.yMousePos = window.event.y+document.body.scrollTop;
    this.xMousePosOffset = window.event.x;
    this.yMousePosOffset = window.event.y;
    this.xMousePosMax = document.body.clientWidth+document.body.scrollLeft;
    this.yMousePosMax = document.body.clientHeight+document.body.scrollTop;
    this.BrowserWidth = document.body.clientWidth;
    this.BrowserHeight = document.body.clientHeight;
    this.xScrollOffset = document.body.scrollLeft;
    this.yScrollOffset = document.body.scrollTop;
  } else if (document.getElementById) {
    // Netscape 6 behaves the same as Netscape 4 in this regard
    this.xMousePos = e.pageX;
    this.yMousePos = e.pageY;
    this.xMousePosOffset = e.pageX-document.body.scrollLeft;
    this.yMousePosOffset = e.pageY-document.body.scrollTop;
    this.xMousePosMax = window.innerWidth+window.pageXOffset;
    this.yMousePosMax = window.innerHeight+window.pageYOffset;
    this.BrowserWidth = window.innerWidth;
    this.BrowserHeight = window.innerHeight;
    this.xScrollOffset = window.pageXOffset;
    this.yScrollOffset = window.pageYOffset;
  }
  if(this.debug_mode){
    window.status = "leftmousedown=" + this.leftmdown + ", xMousePos=" + this.xMousePos + ", yMousePos=" + this.yMousePos + ", xMousePosMax=" + this.xMousePosMax + ", yMousePosMax=" + this.yMousePosMax + ", xMousePosOffset=" + this.xMousePosOffset + ", yMousePosOffset=" + this.yMousePosOffset + ", xScrollOffset=" + this.xScrollOffset + ", yScrollOffset=" + this.yScrollOffset;
  }
  this.move();
}
