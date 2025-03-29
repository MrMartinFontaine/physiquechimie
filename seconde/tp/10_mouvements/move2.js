// JavaScript Document
x=0;
x2=0;
var t=0;
var p=1;
var pp=1;
var stop1 = 1;
var stop2 = 1;
var vis = 0;
var vis2 = 0;
var txte

function creer(){
for (i=0;i<21;i++) {
document.write("<div id='p"+i+"' class='p'><img border=0 src=point3.gif></div>")
}
for (i=0;i<21;i++) {
document.write("<div id='pp"+i+"' class='p'><img border=0 src=point3.gif></div>")
}
}


function depart()
	{debut();
		if (stop1==0) return;
		stop1=0;
		startmove();
}	
function startmove(){
stop2=1;
if (stop1==1) return;
evalMove();
if (t<18) setTimeout("startmove()",50);
x += 20;
}

function evalMove(){    
eval(layerRef+cro1+"velo"+cro2+styleRef+".left = "+ (157+ x) );
 y0 = 90+ 0.5*(t*t);
  x0 = 310+x;
  t+=1;
 point="p"+p;
 val="valve";
 p+=1
 if(p==21){p=1}
 eval(layerRef+cro1+"valve"+cro2+styleRef+".left = " + x0 );
 eval(layerRef+cro1+"valve"+cro2+styleRef+".top = " + y0);
		if(vis==0){
		 eval(layerRef+cro1+point+cro2+styleRef+".left = " + x0 );	
		 eval(layerRef+cro1+point+cro2+styleRef+".top = " + y0 );
		eval(layerRef+cro1+point+cro2+styleRef+".visibility = 'visible'");
		eval(layerRef+cro1+point+cro2+styleRef+".zIndex = 4");
		}
}

function debut(){
t=0; p=1;
cacher();
stop1=1;
x=0;
eval(layerRef+cro1+"velo"+cro2+styleRef+".left = 173");
eval(layerRef+cro1+"valve"+cro2+styleRef+".left = 310");
eval(layerRef+cro1+"valve"+cro2+styleRef+".top = 91");
}
function cacher(){
for (var i = 1; i < 21;i++) {
pt="p"+i;	
eval(layerRef+cro1+pt+cro2+styleRef+".visibility = 'hidden'");
 }
 }

function cacher2(){
for (var i = 1; i < 21;i++) {
pt2="pp"+i;	
eval(layerRef+cro1+pt2+cro2+styleRef+".visibility = 'hidden'");
 }
 }

function depart2()
	{stop1=1; debut2();t=0
		if (stop2==0) return;
		stop2=0;
		startmove2();
}	
function startmove2(){
if (stop2==1) return;
evalMove2();
if (t<18) setTimeout("startmove2()",50);
x2 += 20;
}
 function evalMove2(){
eval(layerRef+cro1+"fond3"+cro2+styleRef+".left = " + (-x2) );

  y0 = 370+ 0.5*(t*t);
  x0 = 315;
  t+=1;
 point2="pp"+p;
 val2="valve2";
 p+=1
 if(p==21){p=1}
eval(layerRef+cro1+"valve2"+cro2+styleRef+".left = " + x0 );
 eval(layerRef+cro1+"valve2"+cro2+styleRef+".top = " + y0); 
if(vis2==0){
	 eval(layerRef+cro1+point2+cro2+styleRef+".left = " + (x0+7) );	
		 eval(layerRef+cro1+point2+cro2+styleRef+".top = " + y0 );
		eval(layerRef+cro1+point2+cro2+styleRef+".visibility = 'visible'");
		eval(layerRef+cro1+point2+cro2+styleRef+".zIndex = 10");
} }
function debut2(){
cacher2();
stop2=1;
x2=0;
eval(layerRef+cro1+"fond3"+cro2+styleRef+".left = 0");
eval(layerRef+cro1+"valve2"+cro2+styleRef+".top = +370");
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
 function vref2(x,y,z){
  eval(layerRef+cro1+x+cro2+styleRef+".visibility = 'visible'");
   eval(layerRef+cro1+y+cro2+styleRef+".visibility = 'visible'");
   eval(layerRef+cro1+z+cro2+styleRef+".visibility = 'visible'");
  }
   function cref2(x,y,z){
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