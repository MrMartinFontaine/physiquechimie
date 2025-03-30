<?php 
include "../authentification/authcheck.php" ;
if ($_SESSION['droits']<>1) { header("Location: ../index.php");exit;};
require_once('../Connections/conn_cahier_de_texte.php');
require_once('../inc/functions_inc.php');


mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsAB = "SELECT * FROM cdt_semaine_ab ORDER BY s_code_date ASC";
$RsAB = mysqli_query($conn_cahier_de_texte, $query_RsAB) or die(mysqli_error($conn_cahier_de_texte));
$row_RsAB = mysqli_fetch_assoc($RsAB);
$totalRows_RsAB = mysqli_num_rows($RsAB);
$Non_definition_SAB=$totalRows_RsAB<2;
$semaineABdebut=false;
$semaineABfin=false;
$date_du_jour=date('Ymd');
do {
        if ($row_RsAB['s_code_date'] <= $date_du_jour) {
                $semaineABdebut=true;
        } else if (($row_RsAB['s_code_date'] >= $date_du_jour)&&($semaineABdebut)){
                $semaineABfin=true;
                break;  
        }
} while ($row_RsAB = mysqli_fetch_assoc($RsAB));
mysqli_free_result($RsAB);

if (isset($_POST['pub_import'])) {
        if ($_POST['pub_import']=="Oui") { $publimport="Non";} else { $publimport="Oui";}; 
        
        mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
        $query_RsparamImport = sprintf("UPDATE cdt_params SET param_val=%s WHERE param_nom='Publication_Import'",GetSQLValueString($publimport, "text"));
        $Result = mysqli_query($conn_cahier_de_texte, $query_RsparamImport) or die(mysqli_error($conn_cahier_de_texte));
}

if($Non_definition_SAB) {
	?>
	<script language="JavaScript" type="text/JavaScript">
	function MM_goToURL() { //v3.0
		var i, args=MM_goToURL.arguments; document.MM_returnValue = false;
		for (i=0; i<(args.length-1); i+=2) eval(args[i]+".location='"+args[i+1]+"'");
	}
	alert("Les semaines par alternance ne sont pas d\351finies. Veillez \340 les d\351finir avant de tenter un import des emplois du temps.");
	MM_goToURL('window','index.php'); 
        </script>
        <?php 
}
elseif(!($semaineABfin)) {
        ?>
        <script language="JavaScript" type="text/JavaScript">
        
        alert("Pour information, la date du jour n'est pas incluse dans la p\351riode de d\351finition de l'ann\351e scolaire. Veillez \340 v\351rifier ces dates si ce n'est pas coh\351rent.");
        
        
        </script>
	
	
	<?php 
};
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

function EDT()
{
	var divedt = document.getElementById("cacheedt");
	
	if(divedt.style.display=="none") {
		$("#cacheedt").show("slow");
	} else {
		$("#cacheedt").hide("slow");
	}
}

function UDT()
{
	var divudt = document.getElementById("cacheudt");
	
	if(divudt.style.display=="none") {
		$("#cacheudt").show("slow");
	} else {
		$("#cacheudt").hide("slow");
	}
}

</script>
</HEAD>
<BODY>
<DIV id=page>
<?php 
$header_description="T&eacute;l&eacute;chargement d'emplois du temps";
require_once "../templates/default/header.php";
?>
<BR></BR>
<table  border="0" align="center" cellpadding="0" cellspacing="0">
<tr align=center><td><button onclick="JavaScript:EDT();">Importer les emplois du temps depuis le logiciel EDT</button></td></tr>
<tr align=center><td>
<BR></BR>
<div id="cacheedt" style="display:none" align="center">
<fieldset style="width : 90%">
<legend align="top">Importer le fichier depuis EDT</legend>
<blockquote>
<p align="left">Si votre &eacute;tablissement utilise une version assez r&eacute;cente du logiciel EDT pour &eacute;tablir les emplois du temps, vous pouvez l'utiliser pour implanter dans le Cahier de Textes les emplois de temps des professeurs de votre &eacute;tablissement avec le processus suivant.</p>

<ul><li><p align="left">Dans le logiciel EDT, </p>
<ul><li><p align="left">Version 2009, utilisez la commande Fichier > Import/Export > Exporter les emplois du temps au format Ical depuis les Emplois du temps des Professeurs.</p></li>
<li><p align="left">Versions 2010, 2011 et 2012, l'export Ical est disponible dans l'onglet Gestion par semaine / Professeurs / afficher l'emploi du temps &agrave; la semaine / imprimer. Ne pas oubliez
de s&eacute;lectionner toutes les ressources &agrave; imprimer, de s&eacute;lectionner la sortie Ical et de choisir au moins 3 semaines d'impression cons&eacute;cutives.
Ce dernier choix se fait en bas de l'&eacute;cran d'EDT, sur une barre avec des petits rectangles o&ugrave; chacun indique une semaine et il faut donc en s&eacute;lectionner 3 cons&eacute;cutifs.</p></li>
<li><p align="left"><span class="erreur">Attention</span> ! Un d&eacute;calage de deux heures sur vos emplois du temps peut se produire &agrave; l'export en cas de mauvais param&egrave;trage de votre part. Il faut cocher "Ne pas se r&eacute;f&eacute;r&eacute;r aux fuseaux horaires". <a href="../images/Export-EDT-vers-CDT.png">Voir ce param&egrave;trage.</a></p>
</li>
</ul></li>
<li><p align="left">Exportez tous les emplois du temps (qui vous int&eacute;ressent !).</p></li>
<li><p align="left">Compressez tous ces fichiers (et pas le r&eacute;pertoire contenant ces fichiers) en un seul et m&ecirc;me fichier zip.</p></li>
<li><p align="left">Joignez ce fichier compress&eacute; dans le formulaire ci-dessous.</p></li>
</ul>

</blockquote>
<form method="post" action="importfromedt.php" enctype="multipart/form-data">
<label>Fichier compress&eacute; comprenant tous les emplois du temps : </label>
<input type="file" name="fichiers_edt" id="fichiers_edt" /><br /><br />
<input type="submit" name="submit" value="T&eacute;l&eacute;charger" />
</form>
</fieldset>
<BR></BR><BR></BR>
</div>
</td></tr>
<tr align=center><td><button onclick="JavaScript:UDT();">Importer les emplois du temps depuis le logiciel UDT</button></td></tr>
<tr><td>
<BR></BR>
<div id="cacheudt" style="display:none" align="center">
<fieldset style="width : 90%">
<legend align="top">Importer les fichiers depuis UDT</legend>
<blockquote>
<p align="left">Si votre &eacute;tablissement utilise une version du logiciel UDT pour &eacute;tablir les emplois du temps, vous pouvez l'utiliser pour implanter dans le Cahier de Textes les emplois de temps des professeurs de votre &eacute;tablissement avec le processus suivant (bas&eacute; sur la version V16 d'UDT).</p>

<ul><li><p align="left">Faites, dans le logiciel UDT, une recherche d'emploi du temps.</p></li>
<li><p align="left">Cochez <i>"Je recherche les cours"</i> et faites cette recherche.</p></li>
<li><p align="left">Cliquez alors sur le nouveau bouton qui vient d'apparaitre <i>"Exporter"</i>.</p></li>
<li><p align="left">L'export se fait par d&eacute;faut en txt mais dans Outils -> Pr&eacute;f&eacute;rences, pr&eacute;f&eacute;rez plut&ocirc;t l'export CSV.</p></li>
<li><p align="left">Le traitement des semaines en alternances se fait par analyse du contenu de la colonne Freq de votre fichier csv. Seront traitées les valeurs A, B, A et B, ou sp,si, ou une chaine de type 1234 correspondant au numero de semaine. Un rechercher/remplacer préalable sur votre fichier sera peut-être nécessaire pour respecter ces critères.</p></li>
</ul>

</blockquote>
<form method="post" action="importfromudt.php" enctype="multipart/form-data">
<label>Fichier CSV ou TXT comprenant tous les emplois du temps : </label>
<input type="file" name="fichier_udt" id="fichier_udt" /><br /><br />
<blockquote>
<p align="left">Vous devriez aussi fournir un fichier issu de STS-Web (Sconet) que vous pourrez vous procurer aupr&egrave;s de la Direction ou de son Secr&eacute;tariat. Ce fichier est optionnel mais, sans celui-ci, alors les mati&egrave;res, dans les emplois du temps, apparaitront avec leurs codes et non avec leurs intitul&eacute;s et le cahier de textes sera moins clair pour tous les utilisateurs (enseignants, parents d'&eacute;l&egrave;ves...). Ce fichier peut &ecirc;tre r&eacute;cup&eacute;r&eacute; d&egrave;s le d&eacute;but d'ann&eacute;e scolaire.</p>
<p align="left">Le processus pour se le procurer est le suivant : STS-Web -> Mise &agrave; jour -> Exports -> Emplois du temps</p>

</blockquote><label>Fichier Sconet STS_EMP_rne.xml : 
</label>
<input type="file" name="fichier_sconet" id="fichier_sconet" /><br /><br />
<input type="submit" name="submit" value="T&eacute;l&eacute;charger" />
</form>
</fieldset>
<BR></BR><BR></BR>
</div>
</td></tr><tr><td>
<div id='publication' align="center">
<fieldset style="width : 90%">
<legend align="top">Autoriser ou interdire l'import des emplois du temps</legend>
<blockquote>


<p align="left">Si vous d&eacute;sirez, &agrave; un moment de l'ann&eacute;e, que les enseignants ne puissent plus b&eacute;n&eacute;ficier de l'import d'emplois du temps, vous trouverez ici l'occasion d'y rem&eacute;dier.</p>
<p align="left">Evidemment, vous pourrez alors tout aussi facilement autoriser de nouveau cet import par le m&ecirc;me moyen.</p>       
<?php
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsparamImport1 = "SELECT param_val FROM cdt_params WHERE param_nom='Import' LIMIT 1";
$RsparamImport1 = mysqli_query($conn_cahier_de_texte, $query_RsparamImport1) or die(mysqli_error($conn_cahier_de_texte));
$row_RsparamImport1 = mysqli_fetch_assoc($RsparamImport1);
if (($row_RsparamImport1['param_val']=="EDT")||($row_RsparamImport1['param_val']=="UDT")) {

        mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
        $query_RsparamImport2 = "SELECT param_val FROM cdt_params WHERE param_nom='Publication_Import' LIMIT 1";
        $RsparamImport2 = mysqli_query($conn_cahier_de_texte, $query_RsparamImport2) or die(mysqli_error($conn_cahier_de_texte));
	$row_RsparamImport2 = mysqli_fetch_assoc($RsparamImport2);
	
        if ($row_RsparamImport2['param_val']=="Oui") { echo "Actuellement, la possiblit&eacute; d'importer un emploi du temps est offerte aux enseignants.";}
        else {echo "<font color=red>Actuellement, la possiblit&eacute; d'importer un emploi du temps <b>n'est plus offerte</b> aux enseignants.</font>";}
        ?>        
        </blockquote>
        <form method="post" action="edt.php#publication" enctype="multipart/form-data">
        
        <input type="hidden" name="pub_import" value="<?php echo $row_RsparamImport2['param_val'] ;?>">
        <input type="submit" name="pub_import2" id="pub_import2" value="<?php
        if ($row_RsparamImport2['param_val']=="Oui") {echo "Interdire l'import des emplois du temps" ;}
        else {echo "Autoriser l'import des emplois du temps";}
        ?>"/>
        </form>
	<?php
        
        mysqli_free_result($RsparamImport2);
}
        mysqli_free_result($RsparamImport1);

?>
</fieldset>
</div>
</td></tr>
 
</table>

<p align="center"><a href="index.php">Retour au Menu Administrateur</a></p>
<p>&nbsp; </p>
<DIV id=footer></DIV>
</body>
</html>

