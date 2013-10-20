MyFade.prototype = new Object;

MyFade.prototype.opacity;
MyFade.prototype.cancel_fade_out;

function MyFade(){
  this.opacity = 0;
  this.cancel_fade_in = false;
}

//==============================================================================
MyFade.prototype.FadeIn = function(id, startValue, endValue, speed, objName){
  this.cancel_fade_out = true;
	if(document.all){
		if((startValue + speed+10) < endValue){
			startValue += speed+10;
		} else{
			startValue = endValue;
		}
	} else{
		if((startValue + speed) < endValue){
			startValue += speed;
		} else{
			startValue = endValue;
		}
	}
  var temp = "";
	if(startValue < 10){
		temp = "0";
	} else{
		temp = "";
	}
	if(document.all){
		document.getElementById(id).style.filter = "Alpha(Opacity=" + startValue + ", Style=0)";
	} else{
		document.getElementById(id).style.opacity = "0." + temp + startValue;
	}
	document.getElementById(id).style.display = "block";
	if(startValue != endValue){
	    setTimeout(objName + ".FadeIn('" + id + "', " + startValue + ", " + endValue + ", " + speed + ", '" + objName + "')", 0);
	} else{
		this.cancel_fade_out = false;
  }
	this.opacity = startValue;
}

MyFade.prototype.FadeInInline = function(id, startValue, endValue, speed, objName){
  this.cancel_fade_out = true;
	if(document.all){
		if((startValue + speed+10) < endValue){
			startValue += speed+10;
		} else{
			startValue = endValue;
		}
	} else{
		if((startValue + speed) < endValue){
			startValue += speed;
		} else{
			startValue = endValue;
		}
	}
  var temp = "";
	if(startValue < 10){
		temp = "0";
	} else{
		temp = "";
	}
	if(document.all){
		document.getElementById(id).style.filter = "Alpha(Opacity=" + startValue + ", Style=0)";
	} else{
		document.getElementById(id).style.opacity = "0." + temp + startValue;
	}
	document.getElementById(id).style.display = "inline";
	if(startValue != endValue){
    setTimeout(objName + ".FadeInInline('" + id + "', " + startValue + ", " + endValue + ", " + speed + ", '" + objName + "')", 0);
	} else{
		this.cancel_fade_out = false;
  }
	this.opacity = startValue;
}
    
//Parameter objname is the name of the instance of this class
//this instance has to be public so that it is reachable from
//this function. This is necessary for the iteration through
//this function.
MyFade.prototype.FadeOut = function(id, endValue, speed, objName){
  if(!this.cancel_fade_out){
  	if(document.all){
  		if((this.opacity - (speed+10)) > endValue){
  			this.opacity -= (speed+10);
  		} else{
  			this.opacity = endValue;
  		}
  	} else{
  		if((this.opacity - speed) > endValue){
  			this.opacity -= speed;
  		} else{
  			this.opacity = endValue;
  		}
  	}
    var temp = "";
  	if(this.opacity < 10){
  		temp = "0";
  	} else{
  		temp = "";
  	}
  	if(document.all){
  		document.getElementById(id).style.filter = "Alpha(Opacity=" + this.opacity + ", Style=0)";
  	} else{
  		document.getElementById(id).style.opacity = "0." + temp + this.opacity;
  	}
  	if(this.opacity != endValue){
  	    setTimeout(objName + ".FadeOut('" + id + "', " + endValue + ", " + speed + ", '" + objName + "')", 0);
  	} else if(this.opacity == 0){
    		document.getElementById(id).style.display = "none";
  	}
  }
}
    
MyFade.prototype.getOpacity = function(){
  return this.opacity;
}
    