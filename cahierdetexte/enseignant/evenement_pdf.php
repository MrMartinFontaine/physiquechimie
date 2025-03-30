<?php 
// pb span margin-right ?
// mmupdate=form1 on enregistre ; sinon on affiche en consultatio: ou   en edition si propriétaire ou   direction si mod=edit  :cf l 144
include "../authentification/authcheck.php" ;
if (($_SESSION['droits']<>1)&& ($_SESSION['droits']<>2)&&($_SESSION['droits']<>3)&&($_SESSION['droits']<>4)&&($_SESSION['droits']<>8)){ header("Location: ../../profs/portail.php");};

require_once('../Connections/conn_cahier_de_texte.php');
require_once('../inc/functions_inc.php');
include 'evenement_select.php' ; // listes jours, listes classes, routines dates


$editFormAction = '#';
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}
$datetoday=date('y-m-d');



// lecture et affichage de la fiche
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsModifMessage =sprintf("SELECT * FROM cdt_evenement_contenu,cdt_prof WHERE ID_even=%u AND cdt_evenement_contenu.prof_ID = cdt_prof.ID_prof ",$_GET['ID_even'] );
$RsModifMessage = mysqli_query($conn_cahier_de_texte, $query_RsModifMessage) or die(mysqli_error($conn_cahier_de_texte));
$row_RsModifMessage = mysqli_fetch_assoc($RsModifMessage);
$totalRows_RsModifMessage = mysqli_num_rows($RsModifMessage);


//Dissociation heure et date
$hhd=substr($row_RsModifMessage['heure_debut'],0,2);
$mmd=substr($row_RsModifMessage['heure_debut'],3,2);
$hhf=substr($row_RsModifMessage['heure_fin'],0,2);
$mmf=substr($row_RsModifMessage['heure_fin'],3,2);
$date_debut=substr($row_RsModifMessage['date_debut'],8,2).'/'.substr($row_RsModifMessage['date_debut'],5,2).'/'.substr($row_RsModifMessage['date_debut'],0,4);
$date_fin=substr($row_RsModifMessage['date_fin'],8,2).'/'.substr($row_RsModifMessage['date_fin'],5,2).'/'.substr($row_RsModifMessage['date_fin'],0,4);
$mois_planning=substr($row_RsModifMessage['date_debut'],0,4).substr($row_RsModifMessage['date_debut'],5,2).'01' ; //pour pointage planning


$classes_conc = explode( "#dates",$row_RsModifMessage['classes_conc'],2); 
$tc=7.1 ;  // taille horizontale de caractere !
ob_start();

?>
<?php {?>

<table  border="0" align="center" cellpadding="0" cellspacing="0" style=" font-size:11px;  padding:12px; "  >
  <tr class="lire_cellule_4">
    <td colspan="2" align="center"  style="border: 1px ; border-color:#000000 ; solid; padding:20px ; font-family:Comic Sans MS; font-size:16px; font-weight:bold; " } ><img src="../images/even_planning.png" width="16" height="16" border="0" /> &nbsp; &nbsp; &nbsp; &nbsp; <?php echo $row_RsModifMessage['titre_even'].'<i>&nbsp; &nbsp;[ '.$classes_conc[0].'] &nbsp;  &nbsp; ('.jour_dmy($date_debut).$date_debut.')</i>&nbsp; &nbsp; ' ; ?> </td>
  </tr>
  <tr>
    <td  valign="top" class="lire_cellule_2" style="font-size:11px; border-top:0px border-right:0px ; border-left:0px  border-bottom:1px ; border-color:#E5E5E5	  ; padding:5px 12px ;" >&nbsp;</td>
    <td  valign="top" class="lire_cellule_2" style="font-size:11px; border-top:0px border-right:0px ; border-left:0px  border-bottom:1px ; border-color:#E5E5E5	  ; padding:5px 12px ;" ><br />
      <br />
      <b>Rédacteur  : </b> <?php echo $row_RsModifMessage['identite'] ; ?> <span style="font-size:11px;margin-left:380px"> <b> Etat du projet </b> : <?php echo $row_RsModifMessage['etat']; 
						if (substr($row_RsModifMessage['date_valid'],0,4) <> '0000' )
						{ echo '<i> le  '.ymd_dmy($row_RsModifMessage['date_crea']).'</i> '; }; ?> </span><br />
      <br />
      <br /></td>
  </tr>
  <tr>
    <td  valign="top" class="lire_cellule_2" style="font-size:12px;  border: 1px solid;border-color:#E5E5E5 ; padding:5px 12px ; background-color:#E5E5E5 ; " ><b>Classes concernées : </b></td>
    <td  valign="top" class="lire_cellule_2" style="font-size:12px;  border: 1px solid;border-color:#E5E5E5 ; padding:5px 12px ; background-color:#E5E5E5 ; " ><b>Titre de l'&eacute;v&egrave;nement : </b> <?php echo $row_RsModifMessage['titre_even'].
	'<span  style="font-size:11px ; margin-left:'.(150-(int)strlen($row_RsModifMessage['titre_even']*$tc)).
	'px ;"> <b> Th&eacute;matique : </b>'.$mode_resume[$row_RsModifMessage['code_mode']].$theme[$row_RsModifMessage['code_theme']].'</span>' ; ?> </td>
  </tr>
  <tr>
    <td rowspan="14"  valign="top" class="lire_cellule_2" style="font-size:11px; border: 1px solid;border-color:#E5E5E5 ; padding:12px ; " >
	
	

      <?php 
	
		
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsActeur="SELECT * FROM cdt_evenement_acteur,cdt_classe,cdt_groupe
			WHERE 	cdt_evenement_acteur.even_ID = ".$_GET['ID_even']."
					AND cdt_evenement_acteur.classe_ID = cdt_classe.ID_classe 
					AND cdt_evenement_acteur.groupe_ID = cdt_groupe.ID_groupe 
						
				ORDER BY nom_classe" ; 		
				
$RsActeur = mysqli_query($conn_cahier_de_texte, $query_RsActeur) or die(mysqli_error($conn_cahier_de_texte));
$row_RsActeur = mysqli_fetch_assoc($RsActeur);
$totalRows_RsActeur = mysqli_num_rows($RsActeur);
do {
			echo $row_RsActeur['nom_classe'].' - '.	$row_RsActeur['groupe'].'<br>';
	}
while ($row_RsActeur = mysqli_fetch_assoc($RsActeur));

 

			

    
	
?>	
	
	
	</td>
    <td  valign="top" class="lire_cellule_2" style="font-size:11px; border: 1px solid;border-color:#E5E5E5 ; padding:12px ; " >
      <p style="margin-top:20px; font-size:11px ;">
        <?php 
	
	if  ($date_fin==$date_debut) {
				echo '<b> Date : </b> '.jour_dmy($date_debut).' '.$date_debut .'<span style="margin-left:50px ;"> <b>' ;
				if ( $row_RsModifMessage['code_mode']<= $surplace ) { echo 'D&eacute;but : ';} else { echo 'Départ : ';} ;
				echo '</b>'.$row_RsModifMessage['heure_debut'].' </span> &nbsp; <span style="margin-left:50px ;"> <b> ';
				if ($row_RsModifMessage['code_mode']<= $surplace) { echo 'Fin :';} else {echo 'Retour :' ;} ;
				echo '</b></span> '.$row_RsModifMessage['heure_fin'] ;	}
			
		else { echo '<b> Départ : </b> '.jour_dmy($date_debut).' '.$date_debut .'&nbsp; &agrave; &nbsp;'.$row_RsModifMessage['heure_debut'].'<span style="margin-left:80px; "> <b>Retour : </b> '.jour_dmy($date_fin).' '.$date_fin .'&nbsp; &agrave; &nbsp;'.$row_RsModifMessage['heure_fin'].'</span>'	; } ;?>
        <?php if ($row_RsModifMessage['pb_dates']=="1"){ echo ' <span style=" text-border: 2px ;border-color:#E5E5E5 ; padding : 4px ;" > <b> <i> &nbsp;  ATTENTION : dates incertaines &agrave; confirmer ! </i></b> </span> ' ;} ; ?>
      </p></td>
  </tr>
  <tr>
    <td  valign="top" class="lire_cellule_2" style="font-size:11px; background-color:#FFFFFF ; padding:12px" ></td>
  </tr>
  <tr>
    <td  valign="top" class="lire_cellule_2" style="font-size:11px; border: 1px solid; border-color:#E5E5E5 ; background-color:#E5E5E5 ; padding:5px 12px; font-size:12px ;" ><b>Description de l'&eacute;v&egrave;nement </b> <span style="font-size:10px; margin-left:160px;"></span> </td>
  </tr>
  <tr>
    <td  valign="top" class="lire_cellule_2" style="font-size:11px; border: 1px solid;border-color:#E5E5E5 ; padding:12px ; " ><span style="margin-left:10px;line-height:16pt;"><?php echo  wordwrap(str_replace("\n","<br>",$row_RsModifMessage['detail']),120, "<br>\n"); ?> </span> </td>
  </tr>
  <tr>
    <td ></td>
  </tr>
  <tr>
    <td  valign="top" class="lire_cellule_2" style="font-size:11px; border: 1px solid; border-color:#E5E5E5 ; padding:12px; style="margin-top:20px;="margin-top:20px;" font-size:12px="font-size:12px" ;><b>Participation par &eacute;l&egrave;ve : </b> <?php echo $row_RsModifMessage['cout_elv']; ?> &euro;  &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; &nbsp; <b>Co&ucirc;t global : </b> <?php echo $row_RsModifMessage['cout_glob']; ?>&euro; <b> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; Effectif : &nbsp;</b><?php echo 	$row_RsModifMessage['classes_eff']; ?>
	
	</td>
  </tr>
  <tr>
    <td  valign="top" class="lire_cellule_2" style="font-size:11px; background-color:#FFFFFF ; padding:12px" ></td>
  </tr>
  <tr>
    <td  valign="top" class="lire_cellule_2" style="font-size:11px;  border:1px ;  border-color: #E5E5E5 ; background-color:#E5E5E5 ;padding:5px 12px ; font-size:12px ;" ><b>Accompagnateurs : </b> <span style="font-size:10px; margin-left:260px;"><i> (non affich&eacute;  sur le cdt eleves)</i></span> </td>
  </tr>
  <tr>
    <td  valign="top" class="lire_cellule_2" style="font-size:11px; border: 1px  solid;border-color:#E5E5E5 ; padding:12px ; " ><?php $text = explode( ";",trim($row_RsModifMessage['accompagnateurs']," \t\n\r\0\x0B")); 
		for ($l=1; $l<4; $l++)	{ $n= 3*($l -1) ; 
			echo '&nbsp; - '.$text[$n].'<span  style="font-size:11px ; margin-left:'.(int)(230-strlen($text[$n])*$tc).'px ;"> - '.$text[$n+1].'</span>'.
			'<span style="font-size:11px ; margin-left:'.( 230-(int)strlen($text[$n+1])*$tc) .'px ;"> - '.$text[$n+2].'</span> <br>' ;
			if ($l<3) {echo '<br>';	}  ; }
			?>
    </td>
  </tr>
  <tr>
    <td  valign="top" class="lire_cellule_2" style="font-size:11px; background-color:#FFFFFF ; padding:12px" ></td>
  </tr>
  <tr>
    <td  valign="top" class="lire_cellule_2" style="font-size:11px; border: 1px solid;border-color:#E5E5E5 ;background-color:#E5E5E5 ; padding:5px 12px ; " ><b>Détails d'organisation : </b><span  style="font-size:10px ; margin-left:260px;"><i> (non affich&eacute;  sur le cdt eleves)</i></span> </td>
  </tr>
  <tr>
    <td  valign="top" class="lire_cellule_2" style="font-size:11px; border: 1px solid;border-color:#E5E5E5 ; padding:12px ; " ><span style="line-height:16pt;"><?php echo  wordwrap(str_replace("\n","<br>",$row_RsModifMessage['details_sup']),120, "<br>\n"); ?> </span> </td>
  </tr>
  <tr>
    <td  valign="top" class="lire_cellule_2" style="font-size:11px; background-color:#FFFFFF ; padding:12px" ></td>
  </tr>
  <tr>
    <td  style="margin-top:20px; font-size:11px ; padding-left:10px ;"  ><?php echo 'Fiche cr&eacute;&eacute;e le '.ymd_dmy($row_RsModifMessage['date_crea']).', &nbsp; &nbsp; &nbsp; derni&egrave;re modification le '.ymd_dmy($row_RsModifMessage['date_envoi']) ; ?> <span style="margin-left: 160px ;font-size:11px ;padding:16px ;"> <?php echo ' <i>&eacute;dit&eacute;e le '.date('d-m-Y').' à '.date('H').'h'.date('i').' par '.$_SESSION['identite'].'</i>' ; ?> </span> </td>
  </tr>
</table>

<?php } 
mysqli_free_result($RsModifMessage);

//$liste_classes=$classes[0];
//for ($i=1; $i<6; $i++) { if ( $classes[$i]<>'') {$liste_classes = $liste_classes.'.'.$classes[$i] ; } } ; 
//$nom_pdf= $row_RsModifMessage['date_debut'].'.['.str_replace( "&quot;", "-",$row_RsModifMessage['titre_even']).'].'.$liste_classes.'.pdf';
$nom_pdf= $row_RsModifMessage['date_debut'].'.['.str_replace( "&quot;", "-",$row_RsModifMessage['titre_even']).'].pdf';

$content2 = ob_get_clean();
require_once('../html2pdf/html2pdf.class.php');
$html2pdf = new HTML2PDF('L','A4', 'fr');
$html2pdf->setDefaultFont('Helvetica');
$html2pdf->WriteHTML($content2);
$html2pdf->Output($nom_pdf);

?>
