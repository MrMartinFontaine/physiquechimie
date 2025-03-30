<?php 
include "../authentification/authcheck.php";
if (($_SESSION['droits']<>2)&&($_SESSION['droits']<>8)){ header("Location: ../index.php");exit;};
require_once('../Connections/conn_cahier_de_texte.php'); 
require_once('../inc/functions_inc.php');


if (isset($_GET['ID_supprime'])) {
	$deleteSQL1 = sprintf("DELETE FROM cdt_archive_association WHERE ID_assoc=%u",
                GetSQLValueString($_GET['ID_supprime'], "int"));
        mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
        $Result1 = mysqli_query($conn_cahier_de_texte, $deleteSQL1) or die(mysqli_error($conn_cahier_de_texte));
}
$profchoix_RsImprime = "0";
if (isset($_SESSION['ID_prof'])) {
	$profchoix_RsImprime = (get_magic_quotes_gpc()) ? $_SESSION['ID_prof'] : addslashes($_SESSION['ID_prof']);
	
}


mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsImprime = sprintf("SELECT DISTINCT gic_ID, nom_classe, nom_matiere, cdt_classe.ID_classe,cdt_matiere.ID_matiere FROM cdt_agenda, cdt_classe, cdt_matiere WHERE prof_ID=%u AND cdt_classe.ID_classe = cdt_agenda.classe_ID AND cdt_matiere.ID_matiere = cdt_agenda.matiere_ID ORDER BY gic_ID,nom_matiere,nom_classe", $profchoix_RsImprime);
$RsImprime = mysqli_query($conn_cahier_de_texte, $query_RsImprime) or die(mysqli_error($conn_cahier_de_texte));
$row_RsImprime = mysqli_fetch_assoc($RsImprime);

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Cahier de textes - <?php echo $_SESSION['identite']?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<script language="JavaScript" type="text/JavaScript">

function MM_goToURL() { //v3.0
        var i, args=MM_goToURL.arguments; document.MM_returnValue = false;
        for (i=0; i<(args.length-1); i+=2) eval(args[i]+".location='"+args[i+1]+"'");
}

var xhr = null;

function getXhr(){
	if(window.XMLHttpRequest) // Firefox et autres
		xhr = new XMLHttpRequest();
	else if(window.ActiveXObject){ // Internet Explorer
		try {
			xhr = new ActiveXObject("Msxml2.XMLHTTP");
		} catch (e) {
                        xhr = new ActiveXObject("Microsoft.XMLHTTP");
                }
        }
        else { // XMLHttpRequest non supporte par le navigateur
                alert("Votre navigateur ne supporte pas les objets XMLHTTPRequest...");
                xhr = false;
        }
}

function go(){
        getXhr();
        // On definit ce qu'on va faire quand on aura la reponse
        xhr.onreadystatechange = function(){
                // On ne fait quelque chose que si on a tout recu et que le serveur est ok
                if(xhr.readyState == 4 && xhr.status == 200){
                        leselect = xhr.responseText;
                        // On se sert de innerHTML pour rajouter les options a la liste
			document.getElementById('menu_archive_classe_matiere').innerHTML = leselect;
		}
	}
        xhr.open("POST","ajax_archive_association.php",true);
        
        xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
        
	sel = document.getElementById('archive');
	NumArch = sel.options[sel.selectedIndex].value;
	xhr.send("NumArch="+NumArch);
}

function MM_reloadPage(init) {  //reloads the window if Nav4 resized
	if (init==true) with (navigator) {if ((appName=="Netscape")&&(parseInt(appVersion)==4)) {
	document.MM_pgW=innerWidth; document.MM_pgH=innerHeight; onresize=MM_reloadPage; }}
	else if (innerWidth!=document.MM_pgW || innerHeight!=document.MM_pgH) location.reload();
}

MM_reloadPage(true);

</script>
<style type="text/css">
a img {	border: none;}
</style>
<LINK media=screen href="../styles/style_default.css" type=text/css rel=stylesheet>
<LINK media=screen href="../templates/default/header_footer.css" type=text/css rel=stylesheet>
</HEAD>
<BODY>
<DIV id=page>
<?php 
$header_description="Consultation de mes cahiers de textes";
require_once "../templates/default/header.php";

$editFormAction = '#';
if (isset($_SERVER['QUERY_STRING'])) {
	$editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}?>
<?php
$mes_erreurs=0;$erreur_cdt=0;$erreur_archive=0;$erreur_archive_detail=0;
if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {
	if ((!isset($_POST['cdt_actuel']))||($_POST['cdt_actuel']=='')||($_POST['cdt_actuel']=='-1')){ $mes_erreurs=1;$erreur_cdt=1;};
	if ((!isset($_POST['archive']))||($_POST['archive']=='')||($_POST['archive']=='-1')){ $mes_erreurs=1;$erreur_archive=1;};
	if ((!isset($_POST['archive_detail']))||($_POST['archive_detail']=='')||($_POST['archive_detail']=='-1')){ $mes_erreurs=1;$erreur_archive_detail=1;};
	
	
	if ($mes_erreurs==0) {
		//on enregistre
		
		$exp1= explode("-",$_POST['cdt_actuel']);
		$exp2= explode("-",$_POST['archive_detail']);
		
		$deleteSQL1 = sprintf("DELETE FROM cdt_archive_association WHERE prof_ID=%u AND classe_ID=%u AND gic_ID=%u AND matiere_ID=%u",
			GetSQLValueString($_SESSION['ID_prof'], "int"),
			GetSQLValueString($exp1[0], "int"),
			GetSQLValueString($exp1[1], "int"),
			GetSQLValueString($exp1[2], "int")
			);
		mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
		$Result1 = mysqli_query($conn_cahier_de_texte, $deleteSQL1) or die(mysqli_error($conn_cahier_de_texte));
		
		$insertSQL = sprintf(" INSERT INTO `cdt_archive_association` (prof_ID, classe_ID , gic_ID , matiere_ID, NumArchive, classe_ID_archive, gic_ID_archive, matiere_ID_archive) VALUES (%u,%u,%u,%u,%u,%u,%u,%u)",
			GetSQLValueString($_SESSION['ID_prof'], "int"),
			GetSQLValueString($exp1[0], "int"),
			GetSQLValueString($exp1[1], "int"),
			GetSQLValueString($exp1[2], "int"),
			GetSQLValueString($_POST['archive'], "int"),
			GetSQLValueString($exp2[0], "int"),
			GetSQLValueString($exp2[1], "int"),
			GetSQLValueString($exp2[2], "int")
			);
		mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
		$Result1 = mysqli_query($conn_cahier_de_texte, $insertSQL) or die(mysqli_error($conn_cahier_de_texte));  
	}
	
}
?>
<br />
<p align="center"> <a href="../index.php">Me d&eacute;connecter</a> - <a href="enseignant.php">Retour au Menu Enseignant</a> - <a href="ecrire.php?date=<?php echo date('Ymd');?>">Retour en saisie de s&eacute;ance</a> </p>
<p>&nbsp;</p>
<p>Mes cahiers de l'ann&eacute;e en cours </p>
<table  width="80%" align="center" cellpadding="5" cellspacing="1" class="Style555">
<?php 
$last_gic_ID=0;
$last_matiere_ID=0;
$i=1;
do { 
	?>
	<tr>
        <?php 
        // pas de regroupements
        if ($row_RsImprime['gic_ID']==0){?>
        	<td valign="bottom" bgcolor="#FFFFFF"><a href="../lire.php?classe_ID=<?php echo $row_RsImprime['ID_classe'];?>&matiere_ID=<?php echo $row_RsImprime['ID_matiere'];?>&gic_ID=<?php echo $row_RsImprime['gic_ID'];?>&ordre=down" ><?php echo $row_RsImprime['nom_classe']; ?>&nbsp;</a></td>
        	<td valign="bottom" bgcolor="#FFFFFF"><a href="../lire.php?classe_ID=<?php echo $row_RsImprime['ID_classe'];?>&matiere_ID=<?php echo $row_RsImprime['ID_matiere'];?>&gic_ID=<?php echo $row_RsImprime['gic_ID'];?>&ordre=down" ><?php echo $row_RsImprime['nom_matiere']; ?></a></td>
        	<td valign="bottom" bgcolor="#FFFFFF">
                <div align="left">
                <?php
                
                include('../inc/archive_association_inc.php');
                ?>
                </div></td>
                </tr>
                <?php
                
        }
        else
        {
        	//presence de regroupement dans la matiere et la classe
        	
        	if (($row_RsImprime['gic_ID']<>$last_gic_ID)||($row_RsImprime['ID_matiere']<>$last_matiere_ID)){ 
                        // Rechercher le nom du regroupement
                        mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
                        $query_RsG =sprintf("SELECT nom_gic FROM cdt_groupe_interclasses WHERE ID_gic = %u",$row_RsImprime['gic_ID']);
                        $RsG = mysqli_query($conn_cahier_de_texte, $query_RsG) or die(mysqli_error($conn_cahier_de_texte));
                        $row_RsG = mysqli_fetch_assoc($RsG);?>
                        <tr>
                        <td valign="bottom" bgcolor="#FFFFFF"><a href="../lire.php?classe_ID=<?php echo $row_RsImprime['ID_classe'];?>&matiere_ID=<?php echo $row_RsImprime['ID_matiere'];?>&gic_ID=<?php echo $row_RsImprime['gic_ID'];?>&regroupement=<?php echo $row_RsG['nom_gic'];?>&ordre=down" ><?php echo '(R) '.$row_RsG['nom_gic'];
                        $last_gic_ID=$row_RsImprime['gic_ID'];
                        $last_matiere_ID=$row_RsImprime['ID_matiere'];
                        ?></a></td>
                        <td valign="bottom" bgcolor="#FFFFFF"><a href="../lire.php?classe_ID=<?php echo $row_RsImprime['ID_classe'];?>&matiere_ID=<?php echo $row_RsImprime['ID_matiere'];?>&gic_ID=<?php echo $row_RsImprime['gic_ID'];?>&regroupement=<?php echo $row_RsG['nom_gic'];?>&ordre=down" ><?php echo $row_RsImprime['nom_matiere']; ?></a></td>
                        <td valign="bottom" bgcolor="#FFFFFF"><div align="left">
                        <?php
                        include('../inc/archive_association_inc.php');
                        ?>
                        </div></td>
                        </tr>
                        <?php
                        
                        mysqli_free_result($RsG);
                }
        }
        if ($row_RsImprime['gic_ID']==0 ){
        	$ch_nom[$i]=$row_RsImprime['nom_classe'] . ' - '.$row_RsImprime['nom_matiere'];
        	$ch_ID_classe[$i]=$row_RsImprime['ID_classe'];
        } else {
        	$ch_nom[$i]=$row_RsG['nom_gic'] .' - '.$row_RsImprime['nom_matiere'];$ch_ID_classe[$i]=0;
        };
        $ch_ID_matiere[$i]=$row_RsImprime['ID_matiere'];
        $ch_gic_ID[$i]=$row_RsImprime['gic_ID'];
        $i=$i+1;
        
} while ($row_RsImprime = mysqli_fetch_assoc($RsImprime)); 


?>
</table>
<?php
mysqli_free_result($RsImprime);


/// Remplacement - cdt du titulaire
if ($_SESSION['id_etat']==2){
	
	//recuperer le cdt du titulaire
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$query_RsImprimeTitu = sprintf("
SELECT DISTINCT gic_ID, nom_classe, nom_matiere, ID_classe,ID_matiere FROM cdt_agenda, cdt_classe, cdt_matiere 
WHERE gic_ID=0 AND prof_ID=%u 
AND cdt_classe.ID_classe = cdt_agenda.classe_ID 
AND cdt_matiere.ID_matiere = cdt_agenda.matiere_ID 
UNION
SELECT DISTINCT gic_ID, nom_classe, nom_matiere, ID_classe, ID_matiere FROM cdt_agenda, cdt_classe, cdt_matiere
WHERE gic_ID >0
AND prof_ID =%u
AND cdt_classe.ID_classe = cdt_agenda.classe_ID
AND cdt_matiere.ID_matiere = cdt_agenda.matiere_ID
GROUP BY ID_matiere
ORDER BY gic_ID,nom_classe,nom_matiere ", $_SESSION['id_remplace']);
	$RsImprimeTitu = mysqli_query($conn_cahier_de_texte, $query_RsImprimeTitu) or die(mysqli_error($conn_cahier_de_texte));
	$row_RsImprimeTitu = mysqli_fetch_assoc($RsImprimeTitu);
	?>
	<br />
	<br />
	<div align="center"><em> Cahier de textes du professeur titulaire </em></div>
	<table  width="80%" align="center" cellpadding="5" cellspacing="1" class="Style555">
	<?php 
	$last_gic_ID=0;
	do { 
		?>
		<tr>
		<?php 
		//Regroupements
		if ($row_RsImprimeTitu['gic_ID']==0){?>
			<td valign="bottom" bgcolor="#FFFFFF"><a href="../lire.php?classe_ID=<?php echo $row_RsImprimeTitu['ID_classe'];?>&matiere_ID=<?php echo $row_RsImprimeTitu['ID_matiere'];?>&gic_ID=<?php echo $row_RsImprimeTitu['gic_ID'];?>&ordre=down&afficher_titulaire" ><?php echo $row_RsImprimeTitu['nom_classe']; ?>&nbsp;</a></td>
			<td valign="bottom" bgcolor="#FFFFFF"><a href="../lire.php?classe_ID=<?php echo $row_RsImprimeTitu['ID_classe'];?>&matiere_ID=<?php echo $row_RsImprimeTitu['ID_matiere'];?>&gic_ID=<?php echo $row_RsImprimeTitu['gic_ID'];?>&ordre=down&afficher_titulaire" ><?php echo $row_RsImprimeTitu['nom_matiere']; ?></a></td>
			<td valign="bottom" bgcolor="#FFFFFF"><div align="left">
			<?php
			include('../inc/archive_association_inc.php');
			?>
			</div></td>
			</tr>
			<?php
		}
		else
		{
                        //presence de regroupement dans la matiere et la classe
                        
                        if ($row_RsImprimeTitu['gic_ID']<>$last_gic_ID){ 
                        	// Rechercher le nom du regroupement
                        	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
                        	$query_RsG =sprintf("SELECT nom_gic FROM cdt_groupe_interclasses WHERE ID_gic=%u",$row_RsImprimeTitu['gic_ID']);
                        	$RsG = mysqli_query($conn_cahier_de_texte, $query_RsG) or die(mysqli_error($conn_cahier_de_texte));
                        	$row_RsG = mysqli_fetch_assoc($RsG);?>
                        	<tr>
                        	<td valign="bottom" bgcolor="#FFFFFF"><?php echo '(R) '.$row_RsG['nom_gic'];
                        	$last_gic_ID=$row_RsImprimeTitu['gic_ID'];
                        	?>&nbsp;</td>
                        	<td valign="bottom" bgcolor="#FFFFFF"><a href="../lire.php?classe_ID=<?php echo $row_RsImprimeTitu['ID_classe'];?>&matiere_ID=<?php echo $row_RsImprimeTitu['ID_matiere'];?>&gic_ID=<?php echo $row_RsImprimeTitu['gic_ID'];?>&regroupement=<?php echo $row_RsG['nom_gic'];?>&ordre=down&afficher_titulaire" ><?php echo $row_RsImprimeTitu['nom_matiere']; ?></a></td>
                        	<td valign="bottom" bgcolor="#FFFFFF"><div align="left">
                        	<?php
                        	include('../inc/archive_association_inc.php');
                        	?>
                        	</div></td>
                        	</tr>
                        	<?php
                        	mysqli_free_result($RsG);
			}
		}
	} while ($row_RsImprimeTitu = mysqli_fetch_assoc($RsImprimeTitu));
	mysqli_free_result($RsImprimeTitu);
	?>
	</table>
	<p>
	<?php
}


// Archives
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsArchiv = "SELECT * FROM cdt_archive ORDER BY NumArchive DESC";
$RsArchiv = mysqli_query($conn_cahier_de_texte, $query_RsArchiv) or die(mysqli_error($conn_cahier_de_texte));
$row_RsArchiv = mysqli_fetch_assoc($RsArchiv);
$totalRows_RsArchiv = mysqli_num_rows($RsArchiv);

if ($totalRows_RsArchiv!=0) {  
	
	if ($erreur_cdt ==1){echo '<br /><span style="color: #FF0000">Vous devez s&eacute;lectionner un cahier</span>';}; 
	if ($erreur_archive ==1){echo '<br /><span style="color: #FF0000">Vous devez s&eacute;lectionner une archive</span>';}; 
	if ($erreur_archive_detail ==1){echo '<br /><span style="color: #FF0000">Vous devez s&eacute;lectionner la classe et la mati&egrave;re pour cette archive</span>';}; ?>
	</p>
	<p>&nbsp; </p>
	<form name="form1" method="post" action="imprimer_menu.php">
	<div  style="background:#F0EDE5;margin:20px;padding:0px;" >
	<table width="95%" border="0">
        <tr>
        <td><div align="center">
        <p align="center"><img src="../images/lightbulb.png" width="16" height="16" /> Cr&eacute;er un raccourci permettant  en saisie de s&eacute;ance d'acc&eacute;der directement &agrave; une archive sp&eacute;cifique.</p>
        </div></td>
        </tr>
        <tr>
        <td><div style="float:left">
        <select name="cdt_actuel" id="cdt_actuel" >
        <option value='-1'>S&eacute;lectionner un cahier</option>
        <?php    
        $last_gic_ID=0;
        for ($x=1;$x<$i;$x++) {
        	if ($ch_gic_ID[$x]==0){
        		echo "<option  value='".$ch_ID_classe[$x]."-".$ch_gic_ID[$x]."-".$ch_ID_matiere[$x]."'>".$ch_nom[$x]."</option>";
        	} 
        	else { //regroupement
        		if ($ch_gic_ID[$x]<>$last_gic_ID){ 
        			echo "<option value='".$ch_ID_classe[$x]."-".$ch_gic_ID[$x]."-".$ch_ID_matiere[$x]."'> (R) ".$ch_nom[$x]."</option>";
        			$last_gic_ID=$ch_gic_ID[$x];
        		}
        	};
        } ;?>
        </select>
        </div>
        <div style="float:left"> &nbsp;<img src="../images/link_add.png" width="16" height="16"></div>
        <div style="float:left">
        <?php 
        if ($totalRows_RsArchiv>1){
        	?>
        	<select name="archive" id="archive" onchange='go()'>
                <option value='-1'>S&eacute;lectionner l'archive</option>
                <?php    
                do { 
                	echo "<option value='".$row_RsArchiv['NumArchive']."'>".$row_RsArchiv['NomArchive']."</option>";
                	
                	
                } while ($row_RsArchiv = mysqli_fetch_assoc($RsArchiv)); ;?>
                </select>

                <br>
                <?php
                $rows = mysqli_num_rows($RsArchiv);
                if($rows > 0) {
                	mysqli_data_seek($RsArchiv, 0);
                	$row_RsArchiv = mysqli_fetch_assoc($RsArchiv);
                };
        } 
        else    {
        	//une seule archive pas de menu deroulant
        	?>
        	<input type="hidden" name="archive" value="<?php echo $row_RsArchiv['NomArchive'];?>">
        	<?php
        }
        ?>
        </div>
        <div  id='menu_archive_classe_matiere' style='display:inline' align="left"></div> </td>
        </tr>
        </table>
        <input type="hidden" name="MM_insert" value="form1">
        </div>
        </form>
        
        <?php
};
mysqli_free_result($RsArchiv);
?>
<br>
<p><a href="cahiers_archives_liste.php">Consulter l'ensemble de mes archives</a></p><br>
<p align="center"> <a href="enseignant.php">Retour au Menu Enseignant</a> - <a href="ecrire.php?date=<?php echo date('Ymd');?>">Retour en saisie de s&eacute;ance</a> </p>
<DIV id=footer></DIV>
</DIV>
</body>
</html>
