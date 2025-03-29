// JavaScript Document
var jg = new jsGraphics("manege");	
var jg2 = new jsGraphics("lignes");	
var R= 250;
xoff=250;yoff=250 ;// decallage centre cercle;
var t=0;
var pi2 = 0.5*Math.PI;   // pi/2
var x=0;
var ref; var v

function t_manege(){
	var jg = new jsGraphics("manege");	
	jg.setColor("#ccaaaa");
 jg.fillEllipse(0,0,500,500); 
 jg.setColor("#FFFFaa");
 jg.setStroke(3);
 jg.drawEllipse(0,0,500,500);
 jg.paint();
 
 var jg5 = new jsGraphics("texte");	
  jg5.setColor("#ffffff");
 jg5.setFont("arial","10px",Font.ITALIC_BOLD); 
jg5.drawString("MANEGE",250,480);
jg5.paint();

 var jg4 = new jsGraphics("arbre");	
jg4.setColor("#227722");
 jg4.fillEllipse(530,230,40,40);
 jg4.fillEllipse(530,221,40,40); 
 jg4.setColor("#ffffff");
 jg4.setFont("arial","10px",Font.ITALIC_BOLD); 
jg4.drawString("ARBRE",530,225); 
  jg4.paint();
  
  var jg2 = new jsGraphics("lignes");	
  jg2.setColor("#FFFFAA");
  jg2.setStroke(3);
jg2.drawLine(250,0,250,500);
jg2.setColor("#2222FF");
jg2.drawLine(0,250,500,250);
 jg2.paint();
 eval(layerRef+cro1+"enfant"+cro2+styleRef+".visibility = 'visible'");
}



//ref terrestre
function ref1(){
	if (document.f1.r1[0].checked) {v=500/200}
	if (document.f1.r1[1].checked) {v=500/100}
	if (document.f1.r1[2].checked) {v=500/50}
	contenu="";
	eval(layerRef+cro1+"arbre"+cro2+ctn+eg+cro3+contenu+cro3+ctn2); 
	eval(layerRef+cro1+"point"+cro2+ctn+eg+cro3+contenu+cro3+ctn2); 
	eval(layerRef+cro1+"texte"+cro2+ctn+eg+cro3+contenu+cro3+ctn2); 
	eval(layerRef+cro1+"enfant"+cro2+styleRef+".visibility = 'hidden'");
	ref=1;
	x=0;t=0;
	t_manege();
	
	move_m();
}


function move_m(){
	if (document.f1.r1[0].checked) {v=500/200}
	if (document.f1.r1[1].checked) {v=500/100}
	if (document.f1.r1[2].checked) {v=500/50}
	if(ref==2){return;}
	contenu="";
	eval(layerRef+cro1+"lignes"+cro2+ctn+eg+cro3+contenu+cro3+ctn2); 
	eval(layerRef+cro1+"texte"+cro2+ctn+eg+cro3+contenu+cro3+ctn2); 
	//axe1
   y0 = yoff+ R*Math.sin(t);
   x0 = xoff+R*Math.cos(t);
    y1 = yoff- R*Math.sin(t);
   x1 = xoff-R*Math.cos(t);
   //axe2 perpendiculaire axe1
   y2 = yoff+ R*Math.sin(t+pi2);
   x2 = xoff+R*Math.cos(t+pi2);
    y3 = yoff- R*Math.sin(t+pi2);
   x3 = xoff-R*Math.cos(t+pi2);
  //tracé axes
   var jg2 = new jsGraphics("lignes");	
  jg2.setColor("#2222FF");
   jg2.setStroke(3);
jg2.drawLine(x0,y0,x1,y1);//horiz à t=0
 jg2.setColor("#FFFFaa");
jg2.drawLine(x2,y2,x3,y3);//vert. à t=0
 jg2.paint();
 var jg5 = new jsGraphics("texte");	
  jg5.setColor("#ffffff");
 jg5.setFont("arial","10px",Font.ITALIC_BOLD); 
jg5.drawString("MANEGE",x2,y2-20);
jg5.paint();
 //pointqui avance sur l'axe 1
  x4=xoff+(R-x)*Math.cos(t);
 // x5=xoff+(R-x+3)*Math.cos(t);
  y4=yoff+ (R-x)*Math.sin(t);
  //y5=yoff+ (R-x+3)*Math.sin(t)
   var jg3 = new jsGraphics("point");	
   jg3.setColor("#FF0000");
   jg3.setStroke(3);
jg3.drawRect(x4,y4,3,3); jg3.paint();
if(x4==x1){return;}
 x+=v;t+=0.05;
	if (t<20) setTimeout("move_m()",500);
}
//ref manege
function ref2(){
	if (document.f2.r2[0].checked) {v=500/100}
	if (document.f2.r2[1].checked) {v=500/50}
	if (document.f2.r2[2].checked) {v=500/20}
	contenu="";
	eval(layerRef+cro1+"point"+cro2+ctn+eg+cro3+contenu+cro3+ctn2); 
	eval(layerRef+cro1+"lignes"+cro2+ctn+eg+cro3+contenu+cro3+ctn2); 
	eval(layerRef+cro1+"texte"+cro2+ctn+eg+cro3+contenu+cro3+ctn2); 
	eval(layerRef+cro1+"enfant"+cro2+styleRef+".visibility = 'hidden'");
	ref=2;
	x=0;t=0;
	t_manege();
	move_m2();
}

function move_m2(){
	if (document.f2.r2[0].checked) {v=500/100}
	if (document.f2.r2[1].checked) {v=500/50}
	if (document.f2.r2[2].checked) {v=500/20}
	if(ref==1){return;}
contenu="";
eval(layerRef+cro1+"arbre"+cro2+ctn+eg+cro3+contenu+cro3+ctn2); 
	//arbre
	var jg4 = new jsGraphics("arbre");
	x10 = xoff+(R+50)*Math.cos(t)-20; 
	y10 = yoff- (R+50)*Math.sin(t)-20;
	x12 = xoff+(R+50)*Math.cos(t+0.03)-20; 
	y12 = yoff- (R+50)*Math.sin(t+0.03)-20;
jg4.setColor("#227722");
 jg4.fillEllipse(x10,y10,40,40); 
 jg4.fillEllipse(x12,y12,40,40); 
 jg4.setColor("#ffffff");
 jg4.setFont("arial","10px",Font.ITALIC_BOLD); 
jg4.drawString("ARBRE",x10,y10); 
  jg4.paint();
  t+=(0.05);
  //point
  x11=2*xoff-x;
   var jg3 = new jsGraphics("point");	
   jg3.setColor("#FF0000");
   jg3.setStroke(3);
jg3.drawRect(x11,yoff,3,3); 
jg3.paint();
 x+=v;
 if(x==2*xoff){return;}
  if (t<20) setTimeout("move_m2()",500);
}

function bul(x,p){
	 if(x==0){txte=""} 
	if(x==1){txte="LANCER"} 
	if(x==2){txte="ARRETER"} 
	if(x==3){txte="REPRENDRE"} 
	if(x==4){txte="VOIR"} 
	if(x==5){txte="MASQUER"}
	if(p==1){k1="txt1";}
	if(p==2){k1="txt2";}
	eval(layerRef+cro1+k1+cro2+ctn+eg+cro3+txte+cro3+ctn2); 
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