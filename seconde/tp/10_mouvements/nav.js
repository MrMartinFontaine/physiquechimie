// JavaScript Document
///jf noblet novembre 2001
var	ie4 = (document.all) && (!document.getElementById)
var	ns4 = document.layers
var dom = document.getElementById; //ie5 ou ns6
var net = (navigator.appName.substring(0,3) == "Net")
var ns6 = ((navigator.appName.indexOf("Netscape") >= 0) && (parseFloat(navigator.appVersion)) >=5 )? 1 : 0;

if (ns4)
		{layerRef = "document.layers";v="pageY = '";h=".pageX= '"
	 	styleRef = "";ctn=".document.write('";ctn2="')";wi=".clip.width= '" ;he=".clip.height= '";
	 	cro1="['";cro2="']";bgd=".bgColor";eg="";cro3="";gl="px'";
		}
	if (ie4)
		{layerRef = "document.all";cro3="";h= ".pixelLeft= '";v= ".pixelTop= '"
	 	styleRef = ".style";ctn=".innerHTML";ctn2="";wi=".width= '";he=".height= '";
		cro1="['";cro2="']";bgd=".backgroundColor";eg="=";gl="px'";
		}
	if (dom)
		{layerRef ="document.getElementById";
		styleRef = ".style";ctn2="";eg="="
		cro1='("';h=".left= '";v=".top= '";wi=".width= '";he=".height= '";
		cro2='")';bgd=".backgroundColor";ctn=".innerHTML";gl="px'";
		cro3='"';//nécessaire pour encadrer une variable texte 
		}
	