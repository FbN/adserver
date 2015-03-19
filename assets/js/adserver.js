var me = document.currentScript;
var w = me.dataset.width;
var h = me.dataset.height;
var domain = me.getAttribute('src').replace("/assets/js/adserver.js","")
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
js.src = domain+"/deliver?w="+w+"&h="+h+"&jsonp=publish";
document.body.appendChild(js);
