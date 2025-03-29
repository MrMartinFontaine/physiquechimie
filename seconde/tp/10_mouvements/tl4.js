
//variables
var t=0;        // temps initial
var attente=40;// temps entre 2 déplacements 
var delta=0.025;// intervalle de déplacement
var k=0;
function ouids(z2,u){
	k1="lune2";
	//document.w2.src='globe12.gif'
	eval("document.w2.width="+z2)
  }
function deplace(x,y,z,z2){
eval(layerRef+cro1+"lune2"+cro2+styleRef+wi+z2+gl);
eval(layerRef+cro1+"lune2"+cro2+styleRef+".left = " + x );
eval(layerRef+cro1+"lune2"+cro2+styleRef+".top = " + y );
eval(layerRef+cro1+"lune2"+cro2+styleRef+".zIndex = " + z);
} 

function tourne() { 
 y0 =60*Math.sin(t)-5;
  x0 =250*Math.cos(t);
z = (y0>-10) ? 41 : 30;//zindex de la lune
z2=(56-y0)/1.5+2;//taille de la lune
//choix(z2,u)
ouids(z2);//affichage des différentes images
//déplacement
  t += delta;
	deplace(x0,y0,z,z2);
  setTimeout("tourne()", attente); 
}
  
function voir(x){
	for(i=1;i<4;i++){
	lay="ref"+i;
	eval(layerRef+cro1+lay+cro2+styleRef+".visibility = 'hidden'");
	}
	if(x==1){var oo=1;
		eval(layerRef+cro1+"globe"+cro2+styleRef+".visibility = 'hidden'");
		eval(layerRef+cro1+"ter"+cro2+styleRef+".visibility = 'visible'");
		eval(layerRef+cro1+"axes"+cro2+styleRef+".visibility = 'hidden'");
		eval(layerRef+cro1+"ox"+cro2+styleRef+".visibility = 'visible'");
		eval(layerRef+cro1+"oy"+cro2+styleRef+".visibility = 'visible'");
		eval(layerRef+cro1+"ref1"+cro2+styleRef+".visibility = 'visible'");
	}
	if(x==2){var oo=0;
		eval(layerRef+cro1+"ter"+cro2+styleRef+".visibility = 'hidden'");
		document.w1.src='globe12.gif';//return false;
		document.w2.src='lune.gif';//return false;
		eval(layerRef+cro1+"ox"+cro2+styleRef+".visibility = 'hidden'");
		eval(layerRef+cro1+"oy"+cro2+styleRef+".visibility = 'hidden'");
		eval(layerRef+cro1+"globe"+cro2+styleRef+".visibility = 'visible'");
		eval(layerRef+cro1+"axes"+cro2+styleRef+".visibility = 'visible'");
		eval(layerRef+cro1+"ref2"+cro2+styleRef+".visibility = 'visible'");
	if(k==0){
		tourne();k=1;}
	}
	if(x==3){var oo=0;
		eval(layerRef+cro1+"ter"+cro2+styleRef+".visibility = 'hidden'");
		document.w1.src='soleil.gif';//return false;
		document.w2.src='globe12.gif';//return false;
		eval(layerRef+cro1+"globe"+cro2+styleRef+".visibility = 'visible'");
		eval(layerRef+cro1+"axes"+cro2+styleRef+".visibility = 'visible'");
		eval(layerRef+cro1+"ref3"+cro2+styleRef+".visibility = 'visible'");
		if(k==0){
		tourne();k=1;}
	}
}