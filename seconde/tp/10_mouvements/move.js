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
stop2=1;
if (stop1==1) return;
evalMove();
if (x<950) setTimeout("startmove()",10);
x += 4;
}

function evalMove(){    
eval(layerRef+cro1+"velo"+cro2+styleRef+".left = " + x );
  y0 = 200+ 35*Math.sin(t);//140
  x0 = (35+x)+35*Math.cos(t);
  t+=0.07507;
 point="p"+p;
 val="valve";
 p+=1
	 if(p==249){p=1}
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
cacher();
stop1=1;
x=0;
eval(layerRef+cro1+"velo"+cro2+styleRef+".left = -73");
eval(layerRef+cro1+"valve"+cro2+styleRef+".left = 0");
eval(layerRef+cro1+"valve"+cro2+styleRef+".top = 195");

}
function cacher(){
for (var i = 1; i < 250;i++) {
pt="p"+i;	
eval(layerRef+cro1+pt+cro2+styleRef+".visibility = 'hidden'");
 }
 }

function cacher2(){
for (var i = 1; i < 250;i++) {
pt2="pp"+i;	
eval(layerRef+cro1+pt2+cro2+styleRef+".visibility = 'hidden'");
 }
 }

function depart2(){
//stop1=1;
	if (stop2==0) return;
stop2=0;
startmove2();
}	

function startmove2(){
if (stop2==1) return;
evalMove2();
if (x2<700) setTimeout("startmove2()",10);
x2 += 4;
}
 function evalMove2(){
eval(layerRef+cro1+"arb3"+cro2+styleRef+".left = " + (200-x2) );
eval(layerRef+cro1+"arb4"+cro2+styleRef+".left = " + (500-x2) ); 
eval(layerRef+cro1+"arb6"+cro2+styleRef+".left = " + (815-x2) );
eval(layerRef+cro1+"arb7"+cro2+styleRef+".left = " + (1000-x2) );  
  y0 = 488+ 35*Math.sin(t);
  x0 = (233)+35*Math.cos(t);
  t+=0.07507;
 point2="pp"+pp;
 val2="valve2";
 pp+=1
 if(pp==249){pp=1}
eval(layerRef+cro1+"valve2"+cro2+styleRef+".left = " + x0 );
 eval(layerRef+cro1+"valve2"+cro2+styleRef+".top = " + y0); 
if(vis2==0){
	 eval(layerRef+cro1+point2+cro2+styleRef+".left = " + x0 );	
		 eval(layerRef+cro1+point2+cro2+styleRef+".top = " + y0 );
		eval(layerRef+cro1+point2+cro2+styleRef+".visibility = 'visible'");
		eval(layerRef+cro1+point2+cro2+styleRef+".zIndex = 10");
} }
function debut2(){
cacher2();
stop2=1;
x2=0;
eval(layerRef+cro1+"arb3"+cro2+styleRef+".left = +200");
eval(layerRef+cro1+"arb4"+cro2+styleRef+".left = +500");
eval(layerRef+cro1+"arb6"+cro2+styleRef+".left = +815");
eval(layerRef+cro1+"arb7"+cro2+styleRef+".left = +1000");
eval(layerRef+cro1+"valve2"+cro2+styleRef+".top = 494");
eval(layerRef+cro1+"valve2"+cro2+styleRef+".left = +264");
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