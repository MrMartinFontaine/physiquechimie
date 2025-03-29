<?php

if (($_SESSION['droits']<>2)&&($_SESSION['droits']<>3)&&($_SESSION['droits']<>4)&&($_SESSION['droits']<>7)){ header("Location: ../index.php");exit;};
require_once('../Connections/conn_cahier_de_texte.php');
require_once('../inc/functions_inc.php');

$plages=array("##","M1","M2","M3","M4","S1","S2","S3");


// liste des motifs d'incidents_profs, puis les sanctions, puis les motifs de sanctions  puis couleurs associées

// idx =0-5 retards  idx= 6 evac idx=7-11  pb travail; idx=12-15 gestion carnet & signatures   idx= 16-18  attitude  idx=19 autres 20,21,22 Rdv ,  23-26 retenues   idx=27,28 sanctions   idx= 29-34 motifs sanctions 
$motifs = array("","retard 1ere h.&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; ","retard casier","retard toilettes","retard vie sco","ret. infirmerie","&eacute;vac. infirmerie",//		idx =0-5 retards  idx= 6 evac
			"défaut cahier","défaut livre","défaut de mat.", "travail non fait", "trav. non rendu", // idx=7-11  pb travail
			"doc. non signé","carnet non signé","doc non rapporté","carnet non repris", // idx=12-15 gestion carnet & signatures
			"pb contestation","pb comportement","exclu de cours","autre motif", // idx= 16-18  attitude  idx=19 autres
			"Convocation elv","Appel parents","Rdv parents","retenue 1h","retenue 2h","retenue 3h","retenue 4h","inclusion","exclusion",  // 20,21,22 Rdv ,23-26 retenues   idx=27,28 sanctions
			"3 d&eacute;fauts de carnet","3 retards non valables","3 d&eacute;fauts de mat&eacute;riel","3 d&eacute;fauts de travail","3 pb de signature ","3 pb de comportement"); // idx= 29-34 motifs sanctions 

$nbmotifs_profs= 19; // pour cibler seulement les rdv ou retenues au_dela de cette borne
$IndexRetards=5;
$IndexHorsRetards=7;$indexMotifsMax=19; // plage des non_retards proposés
$indexMotifsViesco=12;
$nbRdv=3;$nbRetenues=8;$nbSanctions=2;$nbMotifs=6;;

// liste des couleurs utilisées dans les bilans pour les differents types d'incidents, & associations incident-couleur
$clr=array("white","yellow","gold","khaki","LightSalmon","aquamarine","red","LightSkyBlue","#ECCEF5","#7FC6BC","#F2F2F2"); //10 teintes
$color=array($clr[0] ,$clr[8] ,$clr[1],$clr[1],$clr[1],$clr[1],$clr[5],// idx 0 à 6
			$clr[2],$clr[2],$clr[2],$clr[2],$clr[2],// idx=7-11  pb travail
			$clr[3],$clr[3],$clr[3],$clr[3], //idx=12-15 gestion carnet & signatures
			$clr[4],$clr[4],$clr[4],$clr[0],// idx= 16-18  attitude & idx=19 autres
			$clr[9] ,$clr[9], $clr[9],$clr[1] ,$clr[1] ,$clr[1] ,$clr[1] ,$clr[2] ,$clr[4] ,// 20,21,22 Rdv ,23-26 retenues   idx=27,28 sanctions
			$clr[6] ,$clr[1] ,$clr[2] ,$clr[2] ,$clr[3],$clr[4]);// idx= 29-34 motifs sanctions 
			
$choix_heures =array ('07','08','09','10','11','12','13','14','15','16','17','18','19','20','21','22','23','00','01','02','03','04','05','06' ) ; $nb_heures = 24 ; 
$choix_min =array ('00','05','10','15','20','25','30','35','40','45','50','55') ; $nb_min = 12 ;


 ?>