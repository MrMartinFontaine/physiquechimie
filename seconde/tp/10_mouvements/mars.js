// JavaScript Document
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

var stop1 = 1;
var stop2 = 1;
var vis = 0;
var vis2 = 0;
var txte
//
var traj=1;
var p=1;var pp=1;var ppp=1;
var pi = Math.PI;   // pi
var t=0;
var a1= 100 //demi grand axe
var a2= 152 //demi grand axe
var angle2 = 270;
var angle1 = 270;
var e1= 0.017//excentricité
var e2= 0.093//excentricité
var q;
var c1=a1*e1
var c2=a2*e2

var dt = 1;
var rad1=angle1*pi/180;
var raad1=angle1*pi/180;
 var xoff = 570;     // position de la Terre
var yoff = 350;     // position y
var xoff2 = 570;     // position du soleil
var yoff2 = 250;     // position y
var rad2=angle2*Math.PI/180;
var raad2=angle2*Math.PI/180;
function creer(){
for (i=0;i<350;i++) {
document.write("<div id='p"+i+"' class='p'><img border=0 src=point2.gif></div>")
}
for (i=0;i<350;i++) {
document.write("<div id='pp"+i+"' class='p'><img border=0 src=point.gif></div>")
}
}
function debut(){
	if(q==0){	
	cref1("ox2","oy2","ref2")
		cref1("ox","oy","ref1")  
t=0; p=1;dt=1;x=0;da1=0;da2=0;
cacher();
stop1=1;stop2=0
x=0;
eval(layerRef+cro1+"soleil"+cro2+styleRef+".left = 570");
eval(layerRef+cro1+"soleil"+cro2+styleRef+".top = 250");
eval(layerRef+cro1+"terre"+cro2+styleRef+".left = 570");
eval(layerRef+cro1+"terre"+cro2+styleRef+".top = 350");
eval(layerRef+cro1+"mars"+cro2+styleRef+".left = 570");
eval(layerRef+cro1+"mars"+cro2+styleRef+".top = 98");
}}
function depart()
	{
		if(q==0){	
		stop1=1;stop2=0 ;debut();t=0
		if (stop1==0) return;
		stop1=0;
		startmove();
}	}
function startmove(){
	if(q==0){	
if (stop1==1) return;
rotationObjets()
setTimeout("startmove()", 50);
//x2 += 20;
}}

function rotationObjets() {
	if(q==0){	
 var r1=(1-e1*e1)/(1-e1*Math.cos(rad1));
 var r2=(1-e2*e2)/(1-e2*Math.cos(rad2));
var da1 = 2*pi*Math.sqrt(1-e1*e1)/(r1*r1)*dt;
var da2 = 0.53*2*pi*Math.sqrt(1-e2*e2)/(r2*r2)*dt;
t+=dt;
 angle1+=da1;
 angle2+=da2;
rad2=angle2*pi/180;      
rad1=angle1*pi/180;
//soleil
z1= Math.round(a1*r1*Math.cos(rad1)) + xoff;
eval(layerRef+cro1+"soleil"+cro2+styleRef+".left = " + z1 );
z2= Math.round(a1*r1*Math.sin(rad1)) + yoff;
eval(layerRef+cro1+"soleil"+cro2+styleRef+".top = " + z2 );  
 if(vis==0){  
    p+=1;
       if(p>349){p=1}
	point="p"+p;
	eval(layerRef+cro1+point+cro2+styleRef+".left = " + z1 );
eval(layerRef+cro1+point+cro2+styleRef+".top = " + z2 );
eval(layerRef+cro1+point+cro2+styleRef+".zIndex = 10");
eval(layerRef+cro1+point+cro2+styleRef+".visibility = 'visible'");
	}
	
	z3= Math.round(a2*r2*Math.cos(rad2)) + Math.round(a1*r1*Math.cos(rad1)) + xoff;
eval(layerRef+cro1+"mars"+cro2+styleRef+".left = " + z3 );
z4=Math.round(a2*r2*Math.sin(rad2)) + Math.round(a1*r1*Math.sin(rad1)) + yoff;
eval(layerRef+cro1+"mars"+cro2+styleRef+".top = " + z4 );  

if(vis==0){
  p+=1;
if(p>349){p=1}
point2="pp"+p;
eval(layerRef+cro1+point2+cro2+styleRef+".left = " + z3 );
eval(layerRef+cro1+point2+cro2+styleRef+".top = " + z4 );
eval(layerRef+cro1+point2+cro2+styleRef+".zIndex = 10");
eval(layerRef+cro1+point2+cro2+styleRef+".visibility = 'visible'");
}
	}

}

function cacher(){
for (var i = 1; i < 350;i++) {
pt="p"+i;	
eval(layerRef+cro1+pt+cro2+styleRef+".visibility = 'hidden'");
 }
 for (var i = 1; i < 350;i++) {
pt="pp"+i;	
eval(layerRef+cro1+pt+cro2+styleRef+".visibility = 'hidden'");
 }
 }
  function vref1(x,y,z,w){
	  if(w==1){cref1("ox2","oy2","ref2")}
		if(w==2){cref1("ox","oy","ref1")}  
  eval(layerRef+cro1+x+cro2+styleRef+".visibility = 'visible'");
   eval(layerRef+cro1+y+cro2+styleRef+".visibility = 'visible'");
  eval(layerRef+cro1+z+cro2+styleRef+".visibility = 'visible'");
 }
  function cref1(x,y,z){
   eval(layerRef+cro1+x+cro2+styleRef+".visibility = 'hidden'");
   eval(layerRef+cro1+y+cro2+styleRef+".visibility = 'hidden'");
  eval(layerRef+cro1+z+cro2+styleRef+".visibility = 'hidden'");
 }
 //heliocentrique

 
 function debut2(){ 
 if(q==1){	
 cref1("ox2","oy2","ref2")
		cref1("ox","oy","ref1")  
tt=0; pp=1;dtt=1;x=0;daa1=0;daa2=0;

 cacher()
stop1=0;stop2=1;
x=0;
eval(layerRef+cro1+"soleil"+cro2+styleRef+".left = 570");
eval(layerRef+cro1+"soleil"+cro2+styleRef+".top = 250");
eval(layerRef+cro1+"terre"+cro2+styleRef+".left = 570");
eval(layerRef+cro1+"terre"+cro2+styleRef+".top = 350");
eval(layerRef+cro1+"mars"+cro2+styleRef+".left = 570");
eval(layerRef+cro1+"mars"+cro2+styleRef+".top = 98");
}}
function depart2()
	{
if(q==1){			
		stop2=1; stop1=0;debut2();t=0
		if (stop2==0) return;
		stop2=0;
		startmove2();
}	}
function startmove2(){
	if(q==1){	
if (stop2==1) return;
rotationObjets2()
setTimeout("startmove2()", 50);
//x2 += 20;
}}

function rotationObjets2() {
if(q==1){	
 var rr1=(1-e1*e1)/(1-e1*Math.cos(raad1));
 var rr2=(1-e2*e2)/(1-e2*Math.cos(raad2));
var daa1 = 2*pi*Math.sqrt(1-e1*e1)/(rr1*rr1)*dtt;
var daa2 = 0.53*2*pi*Math.sqrt(1-e2*e2)/(rr2*rr2)*dtt;
tt+=dtt;
 angle1+=daa1;
 angle2+=daa2;
raad2=angle2*pi/180;      
raad1=angle1*pi/180;
//terre
zz1= Math.round(a1*rr1*Math.cos(raad1)) + xoff2;
eval(layerRef+cro1+"terre"+cro2+styleRef+".left = " + zz1 );
zz2= Math.round(a1*rr1*Math.sin(raad1)) + yoff2;
eval(layerRef+cro1+"terre"+cro2+styleRef+".top = " + zz2 );  
 if(vis2==0){  
    ppp+=1;
       if(ppp>349){ppp=1}
	point="p"+ppp;
	eval(layerRef+cro1+point+cro2+styleRef+".left = " + zz1 );
eval(layerRef+cro1+point+cro2+styleRef+".top = " + zz2 );
eval(layerRef+cro1+point+cro2+styleRef+".zIndex = 10");
eval(layerRef+cro1+point+cro2+styleRef+".visibility = 'visible'");
	}
		zz3= Math.round(a2*rr2*Math.cos(raad2)) + xoff2
eval(layerRef+cro1+"mars"+cro2+styleRef+".left = " + zz3 );
zz4=Math.round(a2*rr2*Math.sin(raad2)) + yoff2;
eval(layerRef+cro1+"mars"+cro2+styleRef+".top = " + zz4 );  

if(vis2==0){
  ppp+=1;
if(ppp>349){ppp=1}
point2="pp"+ppp;
eval(layerRef+cro1+point2+cro2+styleRef+".left = " + zz3 );
eval(layerRef+cro1+point2+cro2+styleRef+".top = " + zz4 );
eval(layerRef+cro1+point2+cro2+styleRef+".zIndex = 10");
eval(layerRef+cro1+point2+cro2+styleRef+".visibility = 'visible'");
}
}

}