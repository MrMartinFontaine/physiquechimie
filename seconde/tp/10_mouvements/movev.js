// JavaScript Document
x=0;
x2=0;
var t=0;
var p=1;
var pp=1;
var stop1 = 1;

var vis = 1;

var txte

function creer(){
for (i=0;i<250;i++) {
document.write("<div id='p"+i+"' class='p'><img border=0 src=point.gif></div>")
}
for (i=0;i<250;i++) {
document.write("<div id='pp"+i+"' class='p'><img border=0 src=point.gif></div>")
}
}


function depart()
{
	if (stop1==0) return;
	stop1=0;
	startmove();
}	

function startmove(){
//stop2=1;
if (stop1==1) return;
evalMove();
if (x<830) setTimeout("startmove()",10);
x += 4;

}

function evalMove(){  
if (stop1==1) return;
eval(layerRef+cro1+"velo"+cro2+styleRef+".left = " + x );
  y0 = (271+12)+ 35*Math.sin(t);//140
  x0 = (361+35)+35*Math.cos(t);
  y1 = (389+12)+ 35*Math.sin(t);//140
  x1 = (-x-40)+35*Math.cos(t);
  xp =(124+361+35)+35*Math.cos(t);
  yp =(271+12)+ 35*Math.sin(t);
   xc =(390+481-x)+35*Math.cos(t);
  yc =(260+123)+ 35*Math.sin(t);
  t+=0.07507;
point="p"+p;
point2="pp"+p;
// val="valve";
 p+=1
	 if(p==249){p=1}
	// x3=x1-x2;
 eval(layerRef+cro1+"velo"+cro2+styleRef+".left = " + x0 );
 eval(layerRef+cro1+"velo"+cro2+styleRef+".top = " + y0);
 eval(layerRef+cro1+"fond"+cro2+styleRef+".left = "+ x1 );
 eval(layerRef+cro1+"fond"+cro2+styleRef+".top = " + y1);
		if(vis==0){
			eval(layerRef+cro1+"v2"+cro2+styleRef+".visibility = 'visible'");
			eval(layerRef+cro1+"v3"+cro2+styleRef+".visibility = 'visible'");
			eval(layerRef+cro1+"v2"+cro2+styleRef+".left = " + xp );	
		 eval(layerRef+cro1+"v2"+cro2+styleRef+".top = " + yp );
		 eval(layerRef+cro1+point+cro2+styleRef+".left = " + xp );	
		 eval(layerRef+cro1+point+cro2+styleRef+".top = " + yp );
		eval(layerRef+cro1+point+cro2+styleRef+".visibility = 'visible'");
		eval(layerRef+cro1+point+cro2+styleRef+".zIndex = 25");
		eval(layerRef+cro1+"v3"+cro2+styleRef+".left = " + xc );	
		 eval(layerRef+cro1+"v3"+cro2+styleRef+".top = " + yc );
		 eval(layerRef+cro1+point2+cro2+styleRef+".left = " + xc );	
		 eval(layerRef+cro1+point2+cro2+styleRef+".top = " + yc );
		eval(layerRef+cro1+point2+cro2+styleRef+".visibility = 'visible'");
		eval(layerRef+cro1+point2+cro2+styleRef+".zIndex = 25");
		}
}

function debut(){
cacher();
stop1=1;t=0;
x=0;
eval(layerRef+cro1+"v2"+cro2+styleRef+".visibility = 'hidden'");
			eval(layerRef+cro1+"v3"+cro2+styleRef+".visibility = 'hidden'");
eval(layerRef+cro1+"velo"+cro2+styleRef+".left = 361");
eval(layerRef+cro1+"velo"+cro2+styleRef+".top = 271");
eval(layerRef+cro1+"fond"+cro2+styleRef+".left = -40");
eval(layerRef+cro1+"fond"+cro2+styleRef+".top = 389");
eval(layerRef+cro1+"v2"+cro2+styleRef+".left = 484");
eval(layerRef+cro1+"v2"+cro2+styleRef+".top = 269");
eval(layerRef+cro1+"v3"+cro2+styleRef+".left = 865");
eval(layerRef+cro1+"v3"+cro2+styleRef+".top = 379");
}
function cacher(){
for (var i = 1; i < 250;i++) {
pt="p"+i;	
eval(layerRef+cro1+pt+cro2+styleRef+".visibility = 'hidden'");
 }
 for (var i = 1; i < 250;i++) {
pt2="pp"+i;	
eval(layerRef+cro1+pt2+cro2+styleRef+".visibility = 'hidden'");
 }
 }




 function vref1(x,y,z){
  eval(layerRef+cro1+x+cro2+styleRef+".visibility = 'visible'");
   eval(layerRef+cro1+y+cro2+styleRef+".visibility = 'visible'");
  eval(layerRef+cro1+z+cro2+styleRef+".visibility = 'visible'");
 }
  function cref1(x,y,z){
   eval(layerRef+cro1+x+cro2+styleRef+".visibility = 'hidden'");
   eval(layerRef+cro1+y+cro2+styleRef+".visibility = 'hidden'");
  eval(layerRef+cro1+z+cro2+styleRef+".visibility = 'hidden'");
 }
 
 
 function bul(x,p){
	 if(x==0){txte=""} 
	if(x==1){txte="LANCER"} 
	if(x==2){txte="ARRETER"} 
	if(x==3){txte="RECOMMENCER"} 
	if(x==4){txte="VOIR"} 
	if(x==5){txte="MASQUER"}
	if(p==1){k1="txt1";}
	if(p==2){k1="txt2";}
	eval(layerRef+cro1+k1+cro2+ctn+eg+cro3+txte+cro3+ctn2); 
 }