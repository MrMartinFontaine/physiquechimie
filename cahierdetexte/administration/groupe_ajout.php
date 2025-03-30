<?php 
include "../authentification/authcheck.php" ;
if ($_SESSION['droits']<>1) { header("Location: ../index.php");exit;};
require_once('../Connections/conn_cahier_de_texte.php');
require_once('../inc/functions_inc.php');

$editFormAction = '#';
if ((isset($_SERVER['QUERY_STRING']))and($_SERVER['QUERY_STRING']!='')) {
	$editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

$ajout=FALSE;
if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1") && (isset($_POST['groupe']))) {
	$groupe= str_replace(array("/", "&", "\'"), "-",GetSQLValueString($_POST['groupe'], "text") );
	$groupe = strtr($groupe,'@ÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÒÓÔÕÖÙÚÛÜÝàáâãäåçèéêëìíîïðòóôõöùúûüýÿ','aAAAAAACEEEEIIIIOOOOOUUUUYaaaaaaceeeeiiiioooooouuuuyy');
	$code_groupe=$groupe; //par defaut
	$insertSQL = sprintf("INSERT IGNORE INTO cdt_groupe (groupe,code_groupe) VALUES (%s,%s)",$groupe,$code_groupe);
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$Result1 = mysqli_query($conn_cahier_de_texte, $insertSQL) or die(mysqli_error($conn_cahier_de_texte));
$ajout=TRUE;
}

$indicegpe=array(array("A","1"),array("B","2"),array("C","3"),array("1","4"),array("2","5"),array("3","6"));
$err=0;
if ((isset($_POST["massgpe"])) && ($_POST["massgpe"] == "massgpe")) {
	$commandeSQL=" groupe!='Classe entière' AND groupe!='Groupe Réduit'";
	foreach($indicegpe as $ind) {
		if ((isset($_POST["case".$ind[1]]))&&($_POST["case".$ind[1]]=="Gpe".$ind[0])) {
			$commandeSQL=$commandeSQL." AND groupe!='Groupe ".$ind[0]."'";
				mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
				$query = sprintf("INSERT IGNORE INTO `cdt_groupe` (`groupe`) VALUES (%s)",
					GetSQLValueString("Groupe ".$ind[0], "text"));
				$Result3bis = mysqli_query($conn_cahier_de_texte, $query);if ($_POST["selectgpe".$ind[0]]!='0') {
				$updateSQL1 = sprintf("UPDATE cdt_edt SET groupe=%s WHERE groupe=%s",
					GetSQLValueString("Groupe ".$ind[0], "text"),
					GetSQLValueString($_POST["selectgpe".$ind[0]], "text"));
				mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
				$Result1 = mysqli_query($conn_cahier_de_texte, $updateSQL1) or die(mysqli_error($conn_cahier_de_texte));
				$updateSQL2 = sprintf("UPDATE cdt_emploi_du_temps SET groupe=%s WHERE groupe=%s",
					GetSQLValueString("Groupe ".$ind[0], "text"),
					GetSQLValueString($_POST["selectgpe".$ind[0]], "text"));
				mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
				$Result2 = mysqli_query($conn_cahier_de_texte, $updateSQL2) or die(mysqli_error($conn_cahier_de_texte));
			}
		}
	}
		
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$Result3 = mysqli_query($conn_cahier_de_texte, "DELETE FROM cdt_groupe WHERE".$commandeSQL) or die(mysqli_error($conn_cahier_de_texte));
	
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$query = "INSERT IGNORE INTO `cdt_groupe` (`groupe`,`code_groupe`) VALUES ('Groupe Réduit','groupe_reduit')";
    $Result3bis = mysqli_query($conn_cahier_de_texte, $query);	
	
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$Result4 = mysqli_query($conn_cahier_de_texte, "UPDATE cdt_edt SET groupe='Groupe Réduit' WHERE".$commandeSQL) or die(mysqli_error($conn_cahier_de_texte));
	
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$Result5 = mysqli_query($conn_cahier_de_texte, "UPDATE cdt_emploi_du_temps SET groupe='Groupe Réduit' WHERE".$commandeSQL) or die(mysqli_error($conn_cahier_de_texte));
	
	header("Location:groupe_ajout.php");
}


mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_Rsgroupe = "SELECT * FROM cdt_groupe ORDER BY ID_groupe ASC";
$Rsgroupe = mysqli_query($conn_cahier_de_texte, $query_Rsgroupe) or die(mysqli_error($conn_cahier_de_texte));
$row_Rsgroupe = mysqli_fetch_assoc($Rsgroupe);
$totalRows_Rsgroupe = mysqli_num_rows($Rsgroupe);

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_NomImport =sprintf("SELECT param_val FROM cdt_params WHERE param_val='UDT' LIMIT 1");
$NomImport = mysqli_query($conn_cahier_de_texte, $query_NomImport) or die(mysqli_error($conn_cahier_de_texte));
$totalrows_NomImport=mysqli_num_rows($NomImport);
mysqli_free_result($NomImport);

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_Rsmoinsgpe ="SELECT DISTINCT groupe,code_groupe FROM cdt_groupe WHERE 
		groupe!='Classe entière' AND
		groupe!='Groupe A' AND
		groupe!='Groupe B' AND
		groupe!='Groupe C' AND
		groupe!='Groupe 1' AND
		groupe!='Groupe 2' AND
		groupe!='Groupe 3' AND
		groupe!='Groupe Réduit'
		ORDER BY groupe asc";
$Rsmoinsgpe = mysqli_query($conn_cahier_de_texte, $query_Rsmoinsgpe) or die(mysqli_error($conn_cahier_de_texte));
$totalrows_Rsmoinsgpe=mysqli_num_rows($Rsmoinsgpe);

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Cahier de textes - <?php echo $_SESSION['identite']?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<LINK media=screen href="../styles/style_default.css" type=text/css rel=stylesheet>
<LINK media=screen href="../templates/default/header_footer.css" type=text/css rel=stylesheet>
<script type="text/javascript" src="../jscripts/jquery-1.6.2.js"> </script>
<script language="JavaScript" type="text/JavaScript">

function MM_goToURL() { //v3.0
	var i, args=MM_goToURL.arguments; document.MM_returnValue = false;
	for (i=0; i<(args.length-1); i+=2) eval(args[i]+".location='"+args[i+1]+"'");
}

function ajoutgpe()
{
	var divedt = document.getElementById("cachenouvogpe");
	
	if(divedt.style.display=="none") {
		$("#cachenouvogpe").show("slow");
	} else {
		$("#cachenouvogpe").hide("slow");
	}
}

function modifmassegpe()
{
	var divedt = document.getElementById("modifgroupe");
	
	if(divedt.style.display=="none") {
		$("#modifgroupe").show("slow");
	} else {
		$("#modifgroupe").hide("slow");
	}
}

function selectchkbox(symbole1,symbole2){
	var gpeAvalue = document.getElementById('selectgpe'+symbole1).selectedIndex;
	if(gpeAvalue=='0') {document.getElementById('case'+symbole2).checked = false;} else {document.getElementById('case'+symbole2).checked = true;};
}

function decochebox(symbole1,symbole2){
	var boxvalue = document.getElementById('case'+symbole2).checked;
	if(!(boxvalue)) {document.getElementById('selectgpe'+symbole1).selectedIndex = '0';}
}

function verification(){
	var indicegpe=[["A","1"],["B","2"],["C","3"],["1","4"],["2","5"],["3","6"]];
	var gpecoche=false;
	var selecgp ="";
	var ind=0;
	while ((ind<6)&&(!(gpecoche))) {
		gpecoche=(gpecoche||(document.getElementById('case'+indicegpe[ind][1]).checked));
		ind++;
	}
	if (gpecoche) {
		var message="";
		var gpeidentique=false;
		var i=0;
		while ((i<6)&&(!(gpeidentique))) {
			var j=i+1;
			var selecgpi = document.getElementById('selectgpe'+indicegpe[i][0]);
			var selecgpival = selecgpi.options[selecgpi.selectedIndex].value;
			while ((j<6)&&(!(gpeidentique)))  {
				var selecgpj = document.getElementById('selectgpe'+indicegpe[j][0]);
				var selecgpjval = selecgpj.options[selecgpj.selectedIndex].value;
				gpeidentique=(gpeidentique||((selecgpjval==selecgpival)&&(selecgpjval!='0')));
				j++;
			}
			i++;
		}
		if (gpeidentique) {
			return 'Modification en masse impossible car un m\352me groupe est associ\351 \340 deux groupes diff\351rents.';
		} else {
			var message='';
			for (i=0;i<6;i++) {
				if (document.getElementById('case'+indicegpe[i][1]).checked) {
					var selecgpi = document.getElementById('selectgpe'+indicegpe[i][0]);
					var selecgpival = selecgpi.options[selecgpi.selectedIndex].value;
					if (selecgpival!='0') {
						message=message+'Le groupe '+indicegpe[i][0]+' va \352tre associ\351 au groupe '+selecgpival+'.\n';
					} else {
						message=message+'Le groupe '+indicegpe[i][0]+' va \352tre conserv\351 sans association \340 un autre groupe.\n';        
					}  
				} else {
					message=message+'Le groupe '+indicegpe[i][0]+' va \352tre supprim\351.\n';        
				}  
			}
			message=message+"Tous les groupes supprim\351s le seront au profit d'un groupe appel\351 \"Groupe R\351duit\".";
			message='OK'+message;
			return message;
		}
	} else {
		return 'Aucune modification en masse n\'est possible car aucun groupe n\'est s\351lectionn\351.';
	}
}

function formfocus() {
	document.form1.groupe.focus()
	document.form1.groupe.select()
}

</script>
</HEAD>
<BODY onLoad= "formfocus()">
<DIV id=page>
<?php 
$header_description="Gestion des groupes";
require_once "../templates/default/header.php";
?>


<HR>
<p>

Attention : Dans cette page, sont g&eacute;r&eacute;s uniquement les Groupes et non les Regroupements</p>
<p><em><strong>Groupe</strong> : Ensemble d'&eacute;l&egrave;ves d'une m&ecirc;me classe (Ex : la classe est divis&eacute;e en 2 groupes)<br>
<strong>Regroupement</strong> :  Ensemble d'&eacute;l&egrave;ves provenant de plusieurs classes (Ex : Option Chinois)<br>
Les regroupements seront g&eacute;r&eacute;s dans le menu de chaque enseignant. <br>
Enfin, cas particulier, des groupes peuvent &eacute;galement exister au sein d'un regroupement.</em> </p>
<p>&nbsp;</p>
<?php
if (($totalrows_NomImport==1)and($totalrows_Rsmoinsgpe!=0)) {// Import UDT effectue
?>
<button onclick="JavaScript:ajoutgpe();">Ajouter ou modifier un groupe</button>
<div id="cachenouvogpe" style="display:<?php if ($ajout) {echo "block";} else {echo "none";}?>" align="center">
<?php } else {?>
<div align="center">
<?php }?>
<fieldset style="width : 90%">
<legend align="top">Ajouter ou modifier un groupe</legend>
<blockquote>

<form onLoad= "formfocus()" method="post"  name="form1" action="<?php echo $editFormAction; ?>">
<table align="center">
<tr valign="baseline"> 
<td><label><strong>Nom du nouveau groupe : </strong></label><input name="groupe" type="text"  value="" size="32"></td>
</tr>
<tr valign="baseline"> 
<td><div align="center"> 
<input type="submit" value="Ajouter ce groupe">
</div></td>
</tr>
</table>
<input type="hidden" name="MM_insert" value="form1">
</form>  
<BR></BR><BR></BR>
<table border="0" align="center">
<tr> 
<td class="Style6"><div align="center">R&eacute;f&nbsp;&nbsp;</div></td>
<td class="Style6"><div align="center">Groupe&nbsp;&nbsp;</div></td>
<td class="Style6"><div align="center">Editer&nbsp;&nbsp;</div></td>
<td class="Style6"><div align="center">Supprimer&nbsp;&nbsp;</div></td>
</tr>
<?php do { ?>
	<tr> 
	<td class="tab_detail_gris"><?php echo $row_Rsgroupe['ID_groupe']; ?></td>
	<td class="tab_detail_gris"><?php echo $row_Rsgroupe['groupe']; ?></td>
	<td class="tab_detail_gris">
	<?php if($row_Rsgroupe['ID_groupe']<>1){ ?><div align="center"><img src="../images/button_edit.png" alt="Modifier" title="Modifier" width="12" height="13" onClick="MM_goToURL('window','groupe_modif.php?ID_groupe=<?php echo $row_Rsgroupe['ID_groupe']; ?>');return document.MM_returnValue"></div> <?php } else {echo '&nbsp;';}?>	</td>    
	<td class="tab_detail_gris">
	<?php if($row_Rsgroupe['ID_groupe']<>1){ ?><div align="center"><img src="../images/ed_delete.gif" alt="Supprimer" title="Supprimer" width="11" height="13" onClick="if(confirm('Voulez-vous r\351ellement supprimer le groupe <?php echo $row_Rsgroupe['groupe']; ?> ?')){MM_goToURL('window','groupe_supprime.php?ID_groupe=<?php echo $row_Rsgroupe['ID_groupe']; ?>');return document.MM_returnValue}"></div> <?php } else {echo '&nbsp;';}?>	  </td>
	
	</tr>
<?php } while ($row_Rsgroupe = mysqli_fetch_assoc($Rsgroupe)); ?>
</table>
</blockquote>
</fieldset>
</div>
<?php
if (($totalrows_NomImport==1)and($totalrows_Rsmoinsgpe!=0)) {// Import UDT effectue
	?>
	<BR></BR> <BR></BR>
	<button onclick="JavaScript:modifmassegpe();">Modifier en masse des groupes</button>
	<div id="modifgroupe" style="display:<?php if ($err==0) {echo "none";} else {echo "block";}?>" align="center">
		<fieldset style="width : 90%">
		<legend align="top">Modifier en masse des groupes</legend>
		<blockquote>
		<p>
		Suite &agrave; un import d'emplois du temps effectu&eacute; avec UDT, des groupes ont &eacute;t&eacute; ins&eacute;r&eacute;s dans votre Cahier de Textes. Ceux-ci
		peuvent &ecirc;tre nombreux. De ce fait, la liste exhaustive de choix des groupes, lors de la cr&eacute;ation de plages horaires dans 
		la partie emploi du temps, peut &ecirc;tre trop longue et compliqu&eacute;e &agrave; utiliser. Actuellement, le choix est comme ci-dessous.</p>
		
		
		<?php
		
		mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
		$query_Rstousgpes ="SELECT DISTINCT groupe FROM cdt_groupe ORDER BY groupe asc";
		$Rstousgpes = mysqli_query($conn_cahier_de_texte, $query_Rstousgpes) or die(mysqli_error($conn_cahier_de_texte));
		
		?>
		<form method="post" name="form_gpe" action="groupe_ajout.php#modifgroupe">
		<select name='touslesgroupes'>
		<option value="0" selected> Liste de tous les groupes du Cahier de Textes</option>
		<?php
		while ($row_Rstousgpes = mysqli_fetch_assoc($Rstousgpes)) {
			echo "<option value='".$row_Rstousgpes['groupe']."'>".$row_Rstousgpes['groupe']."</option>";	
		}
		mysqli_free_result($Rstousgpes);
		?>
		</select>
		<p>Mais malgr&eacute; cet import, vous ne souhaitez &eacute;ventuellement retrouver qu'un nombre limit&eacute; de groupes comme par exemple, 
		les listes ci-dessous.</p>
		<select name='qquesgroupes1'>
		<option selected>Exemple 1</option>
		<option>Classe enti&egrave;re</option>
		<option>Groupe A</option>
		<option>Groupe B</option>
		<option>Groupe r&eacute;duit</option>
		</select>
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		<select name='qquesgroupes2'>
		<option selected>Exemple 2</option>
		<option>Classe enti&egrave;re</option>
		<option>Groupe 1</option>
		<option>Groupe 2</option>
		<option>Groupe 3</option>
		<option>Groupe r&eacute;duit</option>
		</select>
		
		<p>C'est ce qui vous est propos&eacute; ici. Vous allez pouvoir ne garder que quelques groupes existants parmi votre liste actuelle
		et les associer &agrave; un groupe pr&eacute;d&eacute;fini.
		
		Dans les emplois du temps import&eacute;s, les groupes que vous aurez associ&eacute;s &agrave; un nouveau groupe se verront renomm&eacute;s ainsi.
		Les autres groupes seront tous renomm&eacute;s <i>"Groupe r&eacute;duit"</i>.</p>
		<p>Si vous vouliez garder plus de groupes que ceux pr&eacute;d&eacute;finis, deux solutions s'offrent &agrave; vous :</p>
		<ul>
		<li>Ne pas utiliser la modification en masse des groupes mais supprimer un &agrave; un les groupes non d&eacute;sir&eacute;s.</li>
		<li>Utiliser cette modification en masse puis ajouter un &agrave; un les groupes que vous souhaitez voir r&eacute;apparaitre.</li>
		</ul>
		<table>
		<?php
		foreach($indicegpe as $ind) {
			?>
			<tr valign=middle>
			<td width=30%> 
			
			<input type="checkbox" name="case<?php echo $ind[1]; ?>" id="case<?php echo $ind[1]; ?>" value="Gpe<?php echo $ind[0]; ?>" onChange="decochebox('<?php echo $ind[0]; ?>','<?php echo $ind[1]; ?>');" style="vertical-align:middle" <?php if ((isset($_POST["case$ind[1]"])) && ($_POST["case$ind[1]"] == "Gpe$ind[0]")) {echo 'checked="checked"';} ?>>Groupe <?php echo $ind[0]; ?></input></td>
			
			<td width=70%>
			<select name='selectgpe<?php echo $ind[0]; ?>' id='selectgpe<?php echo $ind[0]; ?>' onChange="selectchkbox('<?php echo $ind[0]; ?>','<?php echo $ind[1]; ?>');">
			<option value="0"> S&eacute;lectionner un groupe du Cahier de Textes</option>
			<?php
			mysqli_data_seek($Rsmoinsgpe,0);
			while ($row_Rsmoinsgpe = mysqli_fetch_assoc($Rsmoinsgpe)) {
				$select='';
				if (($_POST["selectgpe".$ind[0]])&&($_POST["selectgpe".$ind[0]]==$row_Rsmoinsgpe['groupe'])) {
					$select='selected="selected"';
				}
				echo "<option value='".$row_Rsmoinsgpe['groupe']."' $select>".$row_Rsmoinsgpe['groupe']."</option>";	
			}
			
			?>
			</select></td>
			</tr>
			<?php
		}
		?>
		
		</table>
		
		<input type="hidden" name="massgpe" value="massgpe">
		<BR></BR>
		<input type='button' value="Modifier en masse les groupes" name="modifgpe" onClick='if(verification().substring(0,2)=="OK") {if (confirm(verification().substring(2,verification().length))) {submit();}} else {alert(verification());};'>
		</form>
		</blockquote>
		</fieldset>
		</div>
		<?php

	}
	?>
	
  <p align="center"><a href="index.php">Retour au Menu Administrateur</a></p>
  <p>&nbsp; </p>
<DIV id=footer></DIV>
        </DIV>
        </body>
        </html>
        <?php
        mysqli_free_result($Rsgroupe);
        mysqli_free_result($Rsmoinsgpe);
        ?>
