var me = document.currentScript;
var w = me.dataset.width;
var h = me.dataset.height;
var domain = me.getAttribute('src').replace("/assets/js/adserver.js","")
var cookie = function(name) {
  var value = "; " + document.cookie;
  var parts = value.split("; " + name + "=");
  if (parts.length == 2) return parts.pop().split(";").shift();
}('adtrak');
function publish(arr) {
	var a = document.createElement("a");
	a.href=arr.url;
	a.title = arr.caption;
	var img = document.createElement("img");
	img.src = arr.banner;
	img.width = w;
	img.height = h;	
	a.appendChild(img);
	me.parentNode.insertBefore(a, me.nextSibling);
}
var js = document.createElement("script");
js.type = "text/javascript";
js.src = domain+"/deliver?w="+w+"&h="+h+"&jsonp=publish&referer="+encodeURIComponent(document.referrer)+"&cookie="+encodeURIComponent(cookie);
document.body.appendChild(js);
