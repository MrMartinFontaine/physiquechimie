// JavaScript Document


function pos_nextcours1() //affichage du DIV prochains cours a la meme position verticale que le calendrier
{document.getElementById("nextcours1").style.top  = 
parseInt(findPos(document.getElementById("ui-datepicker-div")));} 

function pos_nextcours2() //affichage du DIV prochains cours a la meme position verticale que le calendrier
{document.getElementById("nextcours2").style.top  = 
parseInt(findPos(document.getElementById("ui-datepicker-div")));} 

function pos_nextcours3() //affichage du DIV prochains cours a la meme position verticale que le calendrier
{document.getElementById("nextcours3").style.top  = 
parseInt(findPos(document.getElementById("ui-datepicker-div")));} 

function MM_reloadPage(init)
{  //reloads the window if Nav4 resized
  if (init==true) with (navigator) {if ((appName=="Netscape")&&(parseInt(appVersion)==4)) {
    document.MM_pgW=innerWidth; document.MM_pgH=innerHeight; onresize=MM_reloadPage; }}
  else if (innerWidth!=document.MM_pgW || innerHeight!=document.MM_pgH) location.reload();
}

MM_reloadPage(true);

function MM_findObj(n, d) { //v4.01
  var p,i,x;  if(!d) d=document; if((p=n.indexOf("?"))>0&&parent.frames.length) {
    d=parent.frames[n.substring(p+1)].document; n=n.substring(0,p);}
  if(!(x=d[n])&&d.all) x=d.all[n]; for (i=0;!x&&i<d.forms.length;i++) x=d.forms[i][n];
  for(i=0;!x&&d.layers&&i<d.layers.length;i++) x=MM_findObj(n,d.layers[i].document);
  if(!x && d.getElementById) x=d.getElementById(n); return x;
}

function MM_showHideLayers() { //v6.0
  var i,p,v,obj,args=MM_showHideLayers.arguments;
  for (i=0; i<(args.length-2); i+=3) if ((obj=MM_findObj(args[i]))!=null) { v=args[i+2];
    if (obj.style) { obj=obj.style; v=(v=='show')?'visible':(v=='hide')?'hidden':v; }
    obj.visibility=v; }
}

function ds_getel(id) {
	return document.getElementById(id);
}

var ds_oe = ds_getel('ds_calclass');

var ds_ce = ds_getel('ds_conclass');

var ds_ob = ''; 



function ds_sh(t) {

	ds_element = t;
	clic=0;
}
