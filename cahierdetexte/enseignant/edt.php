<?php 
include "../authentification/authcheck.php";
require_once('../inc/functions_inc.php');
if (($_SESSION['droits']<>2) && ($_SESSION['droits']<>1)) { header("Location: ../index.php");exit;};
require_once('../Connections/conn_cahier_de_texte.php'); 
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Emploi du temps de <?php echo $_SESSION['identite']?></title>
<link media=screen href="../styles/style_default.css" type=text/css rel=stylesheet>
<link media=screen href="../templates/default/header_footer.css" type=text/css rel=stylesheet>
<link type="text/css" href="../styles/jquery-ui-1.8.13.custom_perso.css" rel="stylesheet" />

<style type="text/css">
.detail {
	font-size: 9px;
	background-color: #FFFFFF;
	text-align:left;
	color: #000066;
	border: 1px solid #CCCCCC;
	border-collapse:collapse;
	font-family: Verdana, Arial, Helvetica, sans-serif;
	vertical-align: top;
}
</style>

<script type="text/javascript" src="../jscripts/jquery-1.6.2.js"> </script>
<script type="text/javascript" src="../jscripts/jquery-ui-1.8.13.custom.min.js"></script>
<script type="text/javascript" src="../jscripts/jquery-ui.datepicker-fr.js"></script>

<script language="JavaScript" type="text/JavaScript">
<!--
// This is the function that will open the
// new window when the mouse is moved over the link
function open_new_window() 
{
	new_window = open("","hoverwindow","width=300,height=200,left=10,top=10");
	
	// open new document 
	new_window.document.open();
	
	// Text of the new document
	// Replace your " with ' or \" or your document.write statements will fail
	new_window.document.write("<html><title>JavaScript New Window</title>");
	new_window.document.write("<body bgcolor=\"#FFFFFF\">");
	new_window.document.write("This is a new html document created by JavaScript ");
	new_window.document.write("statements contained in the previous document.");
	new_window.document.write("<br>");
	new_window.document.write("</body></html>");
	
	// close the document
	new_window.document.close(); 
}

// This is the function that will close the
// new window when the mouse is moved off the link
function close_window() 
{
	new_window.close();
}

</SCRIPT>
<link rel="stylesheet" type="text/css" href="../styles/colorpicker.css" />
<LINK media=screen href="../styles/style_default.css" type=text/css rel=stylesheet>
<link href="../styles/arrondis.css" rel="stylesheet" type="text/css" />
<link href="../styles/info_bulles.css" rel="stylesheet" type="text/css" />


</HEAD>
<BODY>

<?php
if(isset($_GET['idp'])){
		
	if ($_SESSION['identite']=='') {
			$nomduprof=$_SESSION['nom_prof'];
			} else {
				$nomduprof=$_SESSION['identite'];
			}	
                
        if (!(is_numeric($_GET['idp']))) {
                $nprof=ucwords(str_replace("_"," ",$_GET['idp']));
                
        }
        else {
                        $nprof=$nomduprof;
			}
	
	
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$query_Rs_emploi_imp = sprintf("SELECT * FROM cdt_edt,cdt_matiere WHERE cdt_edt.matiere_ID=cdt_matiere.ID_matiere AND cdt_edt.prof_ref=%s ORDER BY cdt_edt.jour_semaine, cdt_edt.heure, cdt_edt.semaine", GetSQLValueString($_GET['idp'],"text"));
	$Rs_emploi_imp = mysqli_query($conn_cahier_de_texte, $query_Rs_emploi_imp) or die(mysqli_error($conn_cahier_de_texte));
	$row_Rs_emploi_imp = mysqli_fetch_assoc($Rs_emploi_imp);
	
	if ($row_Rs_emploi_imp == 0) { 
		echo "<BR></BR><BR></BR><BR></BR><p class='erreur'>Finalement, aucun emploi du temps pour $nprof n'a &eacute;t&eacute; trouv&eacute;.</p>";
	} else {
		?>
		<p align='center'>Emploi du temps pour <?php echo $nomduprof;?> import&eacute; de l'emploi du temps depuis EDT de <?php echo $nprof;?></p>
		
		
		<table width="90%" align="center" cellpadding="0" cellspacing="0" class="lire_bordure">
		<tr class="lire_cellule_4">
		<td ><div align="center"></div></td>
		<td >Lundi</td>
		<td >Mardi</td>
		<td >Mercredi</td>
		<td >Jeudi</td>
		<td >Vendredi</td>
		<td >Samedi</td>
		</tr>
		<?php $tab[1]='Lundi';$tab[2]='Mardi';$tab[3]='Mercredi';$tab[4]='Jeudi';$tab[5]='Vendredi';$tab[6]='Samedi';
		
		
		for($x=1;$x < 13;$x++) {?>
			<tr>
			<td bgcolor="#FFFFFF" class="detail"><div align="center"><?php echo $x ;?></div></td>
			<?php
			for($i=1;$i < 7;$i++) { ?>
				<td bgcolor="#FFFFFF" class="detail" ><?php 		
				$nb_cell=0;
				do { 
					if (($row_Rs_emploi_imp['jour_semaine']==$tab[$i])&&($row_Rs_emploi_imp['heure']==$x )){
						$nb_cell+=1;
						if ($row_Rs_emploi_imp['couleur_cellule']==''){$row_Rs_emploi_imp['couleur_cellule']='#CAFDBD';}
						$couleur_police='#000000';
						?>
						<div>
						<?php
						echo '<style>.raised'. $x.$i.' .top, .raised'. $x.$i.' .bottom {display:block; background:transparent; font-size:1px;}
						.raised'. $x.$i.' .b1, .raised'. $x.$i.' .b2, .raised'. $x.$i.' .b3, .raised'. $x.$i.' .b4, .raised'. $x.$i.' .b1b, .raised'. $x.$i.' .b2b, .raised'. $x.$i.' .b3b, .raised'. $x.$i.' .b4b {display:block; overflow:hidden;}
						.raised'. $x.$i.' .b1, .raised'. $x.$i.' .b2, .raised'. $x.$i.' .b3, .raised'. $x.$i.' .b1b, .raised'. $x.$i.' .b2b, .raised'. $x.$i.' .b3b {height:1px;}
						.raised'. $x.$i.' .b2 {background:'.$row_Rs_emploi_imp['couleur_cellule'].'; border-left:1px solid #fff; border-right:1px solid #eee;} 
						.raised'. $x.$i.' .b3 {background:'.$row_Rs_emploi_imp['couleur_cellule'].'; border-left:1px solid #fff; border-right:1px solid #ddd;} 
						.raised'. $x.$i.' .b4 {background:'.$row_Rs_emploi_imp['couleur_cellule'].'; border-left:1px solid #fff; border-right:1px solid #aaa;} 
						.raised'. $x.$i.' .b4b {background:'.$row_Rs_emploi_imp['couleur_cellule'].'; border-left:1px solid #eee; border-right:1px solid #999;} 
						.raised'. $x.$i.' .b3b {background:'.$row_Rs_emploi_imp['couleur_cellule'].'; border-left:1px solid #ddd; border-right:1px solid #999;} 
						.raised'. $x.$i.' .b2b {background:'.$row_Rs_emploi_imp['couleur_cellule'].'; border-left:1px solid #aaa; border-right:1px solid #999;} 
						.raised'. $x.$i.' .boxcontent {background:'.$row_Rs_emploi_imp['couleur_cellule'].';}
						.raised'. $x.$i.' .b1 {margin:0 5px; background:#fff;}
						.raised'. $x.$i.' .b2, .raised'. $x.$i.' .b2b {margin:0 3px; border-width:0 2px;}
						.raised'. $x.$i.' .b3, .raised'. $x.$i.' .b3b {margin:0 2px;}
						.raised'. $x.$i.' .b4, .raised'. $x.$i.' .b4b {height:2px; margin:0 1px;}
						.raised'. $x.$i.' .b1b {margin:0 5px; background:#999;}
						.raised'. $x.$i.' .boxcontent {display:block;  background:'.$row_Rs_emploi_imp['couleur_cellule'].'; border-left:1px solid #fff; border-right:1px solid #999;}
						
						</style>';
						echo '<div class="raised'. $x.$i.'" ><b class="top"><b class="b1"></b><b class="b2"></b><b class="b3"></b><b class="b4"></b></b><div class="boxcontent">';
						echo '<strong>&nbsp;'.$row_Rs_emploi_imp['heure_debut'] .'</strong> - '.$row_Rs_emploi_imp['heure_fin'].' -  ';
						if ($row_Rs_emploi_imp['semaine']!="A et B") {
							echo "<font color=red><b>";
							echo 'Sem. '.$row_Rs_emploi_imp['semaine'];
							echo "</b></font>";
						} else {
							echo 'Sem. '.$row_Rs_emploi_imp['semaine'];
						}
						
						echo '<br />&nbsp;<span style="color:'.$couleur_police.'">';
						
						if ($row_Rs_emploi_imp['classe_ID']==0){
							echo "<font color=red><b>Classe inconnue - A modifier</b></font>";
						} else { 
							$query_RsClasse = sprintf("SELECT nom_classe FROM cdt_classe WHERE ID_classe=%u",$row_Rs_emploi_imp['classe_ID']);
							$RsClasse = mysqli_query($conn_cahier_de_texte, $query_RsClasse) or die(mysqli_error($conn_cahier_de_texte));
							$row_RsClasse = mysqli_fetch_assoc($RsClasse);
							echo 	$row_RsClasse['nom_classe'];
							if (strlen($row_RsClasse['nom_classe'])>20){echo'<br />';};
						};
						
						echo '<br />';
						echo '&nbsp;'.$row_Rs_emploi_imp['nom_matiere'].'<br /></div><b class="bottom"><b class="b4b"></b><b class="b3b"></b><b class="b2b"></b><b class="b1b"></b></b></div>';
					?></div><?php	 } ; ; 
				} while ($row_Rs_emploi_imp = mysqli_fetch_assoc($Rs_emploi_imp));
				mysqli_data_seek($Rs_emploi_imp, 0);
				?>    </td>
				<?php
			}
			?>
			</tr>
			<?php 
		}
		mysqli_free_result($Rs_emploi_imp); 
		?>
		</table>
		<?php 
	} 
} 
?>
</body>
</html>
