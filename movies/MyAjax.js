//MyAjax class

//==============================================================================
//Private variables
//==============================================================================
MyAjax.prototype = new Object;
MyAjax.prototype.req;
MyAjax.prototype.this_class;
MyAjax.prototype.isIE;
MyAjax.prototype.id;
MyAjax.prototype.synchronous;


//==============================================================================
//Constructor
//==============================================================================
function MyAjax (bool) {
  this.setSynchronous(bool);
}

//==============================================================================
//loadXMLDoc function
//==============================================================================
//Used for GET.
//==============================================================================

MyAjax.prototype.getXMLDoc = function(urlink, process){
  var this_class = this;
  // branch for native XMLHttpRequest object
  if (window.XMLHttpRequest) {
    this.req = new XMLHttpRequest();
    this.req.onreadystatechange = function(){process(this_class)};
    this.req.open("GET", urlink, this.synchronous);
    this.req.send(null);
  // branch for IE/Windows ActiveX version
  } else if (window.ActiveXObject) {
    this.isIE = true;
    this.req = new ActiveXObject("Microsoft.XMLHTTP");
    if (this.req) {
      this.req.onreadystatechange = function(){process(this_class)};
      this.req.open("GET", urlink, this.synchronous);
      this.req.send();
    }
  }
}

//==============================================================================
//postXMLDoc function
//==============================================================================
//Used for POST.
//==============================================================================

MyAjax.prototype.postXMLDoc = function(urlink, params, process){
  var this_class = this;
  // branch for native XMLHttpRequest object
  if (window.XMLHttpRequest) {
    this.req = new XMLHttpRequest();
    this.req.onreadystatechange = function(){process(this_class)};
    this.req.open("POST", urlink, this.synchronous);
    //Send the proper header information along with the request
    this.req.setRequestHeader("Content-type", "application/x-www-form-urlencoded; charset=ISO-8859-1");
    this.req.setRequestHeader("Content-length", params.length);
    this.req.setRequestHeader("Connection", "close");
    this.req.send(params);
  // branch for IE/Windows ActiveX version
  } else if (window.ActiveXObject) {
    this.isIE = true;
    this.req = new ActiveXObject("Microsoft.XMLHTTP");
    if (this.req) {
      this.req.onreadystatechange = process;
      this.req.open("POST", urlink, this.synchronous);
      //Send the proper header information along with the request
      this.req.setRequestHeader("Content-type", "application/x-www-form-urlencoded; charset=ISO-8859-1");
      this.req.setRequestHeader("Content-length", params.length);
      this.req.setRequestHeader("Connection", "close");
      this.req.send(params);
    }
  }
}

//==============================================================================
//getElementTextNS function
//==============================================================================
//Retrieve text of an XML document element, including
//elements using namespaces
//==============================================================================

MyAjax.prototype.getElementTextNS = function(prefix, local, parentElem, index){
  var result = "";
  if (prefix && this.isIE) {
    // IE/Windows way of handling namespaces
    result = parentElem.getElementsByTagName(prefix + ":" + local)[index];
  } else {
    // the namespace versions of this method
    // (getElementsByTagNameNS()) operate
    // differently in Safari and Mozilla, but both
    // return value with just local name, provided
    // there aren't conflicts with non-namespace element
    // names
    result = parentElem.getElementsByTagName(local)[index];
  }
  if (result) {
    // get text, accounting for possible
    // whitespace (carriage return) text nodes
    if (result.childNodes.length > 1) {
      return result.childNodes[1].nodeValue;
    } else {
      if(result.firstChild == null){
        return "";
      } else{
        return result.firstChild.nodeValue;
      }
    }
  } else {
    return "";
  }
}

//==============================================================================
//setSynchronous function
//==============================================================================
//Set to true to set XmlHttpRequest to be asynchronous.
//==============================================================================

MyAjax.prototype.setSynchronous = function(bool){
  this.synchronous = bool;
}

//==============================================================================
//getReq function
//==============================================================================
//Get the XmlHttpRequest object
//==============================================================================

MyAjax.prototype.getReq = function(){
  return this.req;
}
    
//==============================================================================
//setID function
//==============================================================================
//Set the HTML object id.
//==============================================================================

MyAjax.prototype.setID = function(value){
  this.id = value;
}

//==============================================================================
//getID function
//==============================================================================
//Get the HTML object id.
//==============================================================================

MyAjax.prototype.getID = function(){
  return this.id;
}
