<?php 
include "../authentification/authcheck.php";
if (($_SESSION['droits']<>2)&&($_SESSION['droits']<>8)) { header("Location: ../index.php");exit;};
require_once('../Connections/conn_cahier_de_texte.php'); 
require_once('../inc/functions_inc.php');

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {
        mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
        $query_RsInviteDel = sprintf("DELETE FROM cdt_invite WHERE prof_ID=%u",GetSQLValueString($_SESSION['ID_prof'],"int"));
        $RsInviteDel = mysqli_query($conn_cahier_de_texte, $query_RsInviteDel) or die(mysqli_error($conn_cahier_de_texte));

//traitement annee en cours     
	for ($i=1; $i<$_POST['n']; $i++) { 
		$refcahier='cahier'.$i;

	if (isset($_POST[$refcahier])){
		$refclasse='classe'.$i;
		$refgic='gic'.$i;
                $refprof='prof'.$i;
                $refmatiere='matiere'.$i;
                mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
                $insertSQL= sprintf("INSERT INTO `cdt_invite` ( `prof_ID` , `classe_ID` ,`gic_ID` , `matiere_ID`, NumArchive )  VALUES (%u, '%s', '%s','%s','0')",GetSQLValueString($_SESSION['ID_prof'],"int"),$_POST[$refclasse],$_POST[$refgic],$_POST[$refmatiere]);
                $Result = mysqli_query($conn_cahier_de_texte, $insertSQL) or die(mysqli_error($conn_cahier_de_texte));
        };
        };


//traitement archives

for ($j=1; $j<=$_POST['nb_archives']; $j++) { 
$refarchive='NumArchive'.$j;
	for ($i=1; $i<$_POST['n_archiv']; $i++) { 
	$refcahier='cahier'.$i.'archiv'.$j;
	if (isset($_POST[$refcahier])){
		$refclasse='classe'.$i.'archiv'.$j;
		$refgic='gic'.$i.'archiv'.$j;
                $refprof='prof'.$i.'archiv'.$j;
                $refmatiere='matiere'.$i.'archiv'.$j;
                mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
                $insertSQL= sprintf("INSERT INTO `cdt_invite` ( `prof_ID` , `classe_ID` ,`gic_ID` , `matiere_ID`, NumArchive )  VALUES (%u, '%s', '%s','%s','%s')",GetSQLValueString($_SESSION['ID_prof'],"int"),$_POST[$refclasse],$_POST[$refgic],$_POST[$refmatiere],$_POST[$refarchive]);
                $Result = mysqli_query($conn_cahier_de_texte, $insertSQL) or die(mysqli_error($conn_cahier_de_texte));
        };
        };
}

//Autorisation de publication de messages par les invites
        mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
        $query_RsProf_Update = sprintf("UPDATE cdt_prof SET `message_invite`=%s WHERE ID_prof=%u", GetSQLValueString(isset($_POST['message_invite']) ? 'true' : '', 'defined','"O"','"N"'),GetSQLValueString($_SESSION['ID_prof'],"int"));
        $RsProf_Update = mysqli_query($conn_cahier_de_texte, $query_RsProf_Update) or die(mysqli_error($conn_cahier_de_texte));
		$insertGoTo = "enseignant.php";
		header(sprintf("Location: %s", $insertGoTo));
	
};


if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form2")) {
//Tous les cahiers sont accessibles via un compte invite
//Autorisation de publication de messages par les invites
        mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
        $query_RsProf_Update = sprintf("UPDATE cdt_prof SET `message_invite`=%s WHERE ID_prof=%u", GetSQLValueString(isset($_POST['message_invite2']) ? 'true' : '', 'defined','"O"','"N"'),GetSQLValueString($_SESSION['ID_prof'],"int"));
        $RsProf_Update = mysqli_query($conn_cahier_de_texte, $query_RsProf_Update) or die(mysqli_error($conn_cahier_de_texte));
		$insertGoTo = "enseignant.php";
		header(sprintf("Location: %s", $insertGoTo));
}


$profchoix_RsInvite = "0";
if (isset($_SESSION['ID_prof'])) {
  $profchoix_RsInvite = (get_magic_quotes_gpc()) ? $_SESSION['ID_prof'] : addslashes($_SESSION['ID_prof']);

}
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsInvite = sprintf("SELECT DISTINCT gic_ID, nom_classe, nom_matiere, cdt_classe.ID_classe,cdt_matiere.ID_matiere FROM cdt_agenda, cdt_classe, cdt_matiere WHERE prof_ID=%u AND cdt_classe.ID_classe = cdt_agenda.classe_ID AND cdt_matiere.ID_matiere = cdt_agenda.matiere_ID ORDER BY gic_ID, cdt_classe.nom_classe, cdt_matiere.nom_matiere ", $profchoix_RsInvite);
$RsInvite = mysqli_query($conn_cahier_de_texte, $query_RsInvite) or die(mysqli_error($conn_cahier_de_texte));
$row_RsInvite = mysqli_fetch_assoc($RsInvite);
$totalRows_RsInvite = mysqli_num_rows($RsInvite);


mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_Rsgic =sprintf("SELECT * FROM cdt_groupe_interclasses WHERE prof_ID=%u",GetSQLValueString($_SESSION['ID_prof'],"int"));
$Rsgic = mysqli_query($conn_cahier_de_texte, $query_Rsgic) or die(mysqli_error($conn_cahier_de_texte));
$row_Rsgic = mysqli_fetch_assoc($Rsgic);
$totalRows_Rsgic = mysqli_num_rows($Rsgic);

// Archives
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsArchiv = "SELECT * FROM cdt_archive ORDER BY NumArchive ASC";
$RsArchiv = mysqli_query($conn_cahier_de_texte, $query_RsArchiv) or die(mysqli_error($conn_cahier_de_texte));
$row_RsArchiv = mysqli_fetch_assoc($RsArchiv);
$totalRows_RsArchiv = mysqli_num_rows($RsArchiv);

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsProf = sprintf("SELECT * FROM cdt_prof WHERE ID_prof=%u ",GetSQLValueString($_SESSION['ID_prof'],"int"));;
$RsProf = mysqli_query($conn_cahier_de_texte, $query_RsProf) or die(mysqli_error($conn_cahier_de_texte));
$row_RsProf = mysqli_fetch_assoc($RsProf);


//gestion du lien "invite"
function genere_pwd($pass_length=12)
        {
        $chaine = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
	$pass = "";
	$chaine_length = strlen($chaine);
	for($i=1; $i<=$pass_length; $i++)
		{
		$char = mt_rand(0,$chaine_length-1);
		$pass .= $chaine[$char];
		}
        return $pass;
        }

//traitement de la cle d'acces "invite"
if(isset($_POST['reinit']) || $row_RsProf['lien_invite_prof']=='')
        {
        $lien_invite_prof = md5("enseignant-".$row_RsProf['passe']."-".genere_pwd());
	$updateSQL = sprintf("UPDATE cdt_prof SET lien_invite_prof=%s WHERE ID_prof=%u",
				GetSQLValueString($lien_invite_prof,"text"),
				GetSQLValueString($_SESSION['ID_prof'],"int"));
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$Result1 = mysqli_query($conn_cahier_de_texte, $updateSQL) or die(mysqli_error($conn_cahier_de_texte));	
        }
else $lien_invite_prof = $row_RsProf['lien_invite_prof'];

//traitement de la date de validite du lien d'acces "invite"    
if(isset($_POST['date1'])  || $row_RsProf['lien_invite_prof']=='' || !preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/",$row_RsProf['datefin_invite_prof'])) 
        {
        $datefin_invite_prof = isset($_POST['date1']) ? substr($_POST['date1'],6,4).'-'.substr($_POST['date1'],3,2).'-'.substr($_POST['date1'],0,2) : date('Y-m-d',time()-3600*24);     
	if($datefin_invite_prof!=$row_RsProf['datefin_invite_prof'])
		{
		$updateSQL = sprintf("UPDATE cdt_prof SET datefin_invite_prof=%s WHERE ID_prof=%u",
					GetSQLValueString($datefin_invite_prof,"text"),
					GetSQLValueString($_SESSION['ID_prof'],"int"));
		mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
		$Result1 = mysqli_query($conn_cahier_de_texte, $updateSQL) or die(mysqli_error($conn_cahier_de_texte));
		}
	}
else $datefin_invite_prof = $row_RsProf['datefin_invite_prof'];

	?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Cahier de textes - <?php echo $_SESSION['identite']?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link media=screen href="../styles/style_default.css" type=text/css rel=stylesheet>
<link media=screen href="../templates/default/header_footer.css" type=text/css rel=stylesheet>
<link type="text/css" href="../styles/jquery-ui-1.8.13.custom_perso.css" rel="stylesheet" />
<style type="text/css">
a img {	border: none;}
</style>
<script type="text/javascript" src="../jscripts/jquery-1.6.2.js"> </script>
<script type="text/javascript" src="../jscripts/jquery-ui-1.8.13.custom.min.js"></script>
<script type="text/javascript" src="../jscripts/jquery-ui.datepicker-fr.js"></script>
</head>
<body>
<DIV id=page>
  <?php 
$header_description="Publication des cahiers de textes vers un compte invit&eacute;";
require_once "../templates/default/header.php";

if ((isset($_SESSION['acces_inspection_all_cdt']))&&($_SESSION['acces_inspection_all_cdt']=='Non')){
?>
  <HR>
  <blockquote>
    <p align="left"><em>Cochez ci-dessous les cahiers de textes que vous d&eacute;sirez ponctuellement mettre &agrave; disposition  de vos invit&eacute;s <br>
      (Coll&egrave;gues, enseignants ext&eacute;rieurs &agrave; l'&eacute;tablissement, conseiller p&eacute;dagogique, corps d'inspection...).<br>
      <br>
      Deux solutions s'offrent &agrave; vous pour permettre &agrave; votre invit&eacute; cet acc&eacute;s : </em></p>
    <blockquote>
      <p align="left"><em>1) Demander &agrave; votre administrateur le <strong>mot de passe</strong> &agrave; fournir &agrave; votre &quot;invit&eacute;&quot; 
        pour qu'il se connecte depuis la page d'accueil. 
        Il aura acc&egrave;s &agrave; l'ensemble des cahiers de l'&eacute;tablissement mis ponctuellement &agrave; disposition.</em></p>
      <p align="left"><em>2) Pour un acc&egrave;s sans mot de passe et <strong>uniquement &agrave; vos cahiers</strong> mis &agrave; disposition, fournissez &agrave; votre invit&eacute; le lien ci-dessous.
        <!-- ce n'est plus vrai -->
        <!-- (Attention, ce lien sera modifi&eacute; en cas de modification de votre  mot de passe personnel. Dans ce cas, pensez &agrave; fournir &agrave; vos invit&eacute;s le nouveau lien g&eacute;n&eacute;r&eacute;).-->
        </em></p>
    </blockquote>
    <p align="left">&nbsp;</p>
    <div align="left" class="tab_detail" > </br>
      <blockquote>
        <p><strong>Lien &agrave; communiquer &agrave; votre invit&eacute; :</strong> </p>
      </blockquote>
      <p align="center">
        <?php
$url1="http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
$url2="invite/invite.php?ID_prof=".$_SESSION['ID_prof'].'&ident='.$lien_invite_prof;
echo '<a href="'. str_replace('enseignant/publier_invite.php',$url2,$url1).'">'.str_replace('enseignant/publier_invite.php',$url2,$url1).'</a>';

?>
        </br>
        </br>
      </p>
      <form name="frm" method="POST" action="publier_invite.php">
        <script>
         $(function() {
        	$.datepicker.regional['fr'] = { dateFormat: 'dd-mm-yy'};
			$.datepicker.setDefaults($.datepicker.regional['fr']);
        	$('#date1').datepicker({firstDay:1});
        });
        </script>
        <div align="center">
          <p><em>Ce lien est valide jusqu'au &nbsp;
            <input name='date1' type='text' id='date1' value="<?php 
	  $date1_form=substr($datefin_invite_prof,8,2).'-'.substr($datefin_invite_prof,5,2).'-'.substr($datefin_invite_prof,0,4);
	  echo $date1_form;?>" size="10"/>
            </em> &nbsp; <br/>
            G&eacute;n&eacute;rer un nouveau lien (plus aucun acc&egrave;s ne pourra alors se faire avec le lien ci-dessus)
            <input name="reinit" type="checkbox" value="1"/>
          </p>
          <p>
            <input name="submit" type="submit" value="Actualiser"/>
          </p>
        </div>
      </form>
      </br>
      </br>
    </div>
  </blockquote>
  <blockquote>
    <div align="left" class="tab_detail" >
      <form name="form1" method="post" action="publier_invite.php">
        <blockquote>
          <p>&nbsp;</p>
          <p><strong>Messages de la part de vos invit&eacute;s </strong></p>
          <p><em>En cochant <strong>l'option ci-dessous</strong>, vos invit&eacute;s auront la possibilit&eacute; de vous contacter sans utiliser le m&eacute;l, en vous envoyant un message 
            via votre cahier de textes. Cet envoi accompagn&eacute; d'&eacute;ventuelles pi&egrave;ces jointes, appara&icirc;tra en page de saisie de votre cahier de textes en bonne place au-dessus de vos autres messages issus du Responsable Etablissement ou de la Vie Scolaire. </em><br>
            <br>
            Autoriser vos invit&eacute;s &agrave; vous envoyer un message via l'interface du cahier de textes &nbsp;
            <input type="checkbox" name="message_invite" id="message_invite" value="" <?php if (!(strcmp($row_RsProf['message_invite'],'O'))) {echo "checked=checked";} ?>>
          </p>
          </p>
        </blockquote>
        <p>&nbsp;</p>
        <blockquote>
          <p><strong> Ann&eacute;e en cours </strong></p>
        </blockquote>
        <table  width="70%" align="center" cellpadding="5" cellspacing="1" class="Style555">
          <?php 
	 $last_gic_ID=0;$n=1;
	 do { 
	 
	 ?>
            <tr>
              <?php 
	  //Regroupements
			if ($row_RsInvite['gic_ID']==0){?>
              <td valign="bottom" bgcolor="#FFFFFF"><?php echo $row_RsInvite['nom_classe']; ?>&nbsp;</td>
              <td valign="bottom" bgcolor="#FFFFFF"><a href="../lire.php?classe_ID=<?php echo $row_RsInvite['ID_classe'];?>&matiere_ID=<?php echo $row_RsInvite['ID_matiere'];?>&gic_ID=<?php echo $row_RsInvite['gic_ID'];?>&ordre=down" class="Style15" ><?php echo $row_RsInvite['nom_matiere']; ?></a></td>
              <td valign="bottom" bgcolor="#FFFFFF"><div align="center">
                  <input type="checkbox" name="<?php echo 'cahier'.$n; ?>"   id="<?php echo 'cahier'.$n; ?>" value="on"
                          <?php
                                mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
                                $query_RsI = sprintf("SELECT ID_invite FROM cdt_invite WHERE prof_ID=%u AND classe_ID=%u AND gic_ID=%u AND matiere_ID=%u AND NumArchive=0",GetSQLValueString($_SESSION['ID_prof'],"int"),$row_RsInvite['ID_classe'],$row_RsInvite['gic_ID'],$row_RsInvite['ID_matiere']);
                                $RsI = mysqli_query($conn_cahier_de_texte, $query_RsI) or die(mysqli_error($conn_cahier_de_texte));
                                $row_RsI = mysqli_fetch_assoc($RsI);
                                $totalRows_RsI = mysqli_num_rows($RsI);
				if ($totalRows_RsI==1){echo " checked";};
			  ?>
			  >
                </div></td>
            </tr>
            <?php
				
                                }
                                else
                                {
                        //presence de regroupement dans la matiere et la classe
                        
if ($row_RsInvite['gic_ID']<>$last_gic_ID){ 
                        // Rechercher le nom du regroupement
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsG =sprintf("SELECT nom_gic FROM cdt_groupe_interclasses WHERE ID_gic=%u",$row_RsInvite['gic_ID']);
$RsG = mysqli_query($conn_cahier_de_texte, $query_RsG) or die(mysqli_error($conn_cahier_de_texte));
$row_RsG = mysqli_fetch_assoc($RsG);?>
            <tr>
              <td valign="bottom" bgcolor="#FFFFFF"><?php echo '(R) '.$row_RsG['nom_gic'];
$last_gic_ID=$row_RsInvite['gic_ID'];
 ?>&nbsp;</td>
              <td valign="bottom" bgcolor="#FFFFFF"><a href="../lire.php?classe_ID=<?php echo $row_RsInvite['ID_classe'];?>&matiere_ID=<?php echo $row_RsInvite['ID_matiere'];?>&gic_ID=<?php echo $row_RsInvite['gic_ID'];?>&regroupement=<?php echo $row_RsG['nom_gic'];?>&ordre=down" class="Style15" ><?php echo $row_RsInvite['nom_matiere']; ?></a></td>
              <td valign="bottom" bgcolor="#FFFFFF"><div align="center">
                  <input type="checkbox" name="<?php echo 'cahier'.$n; ?>"   id="<?php echo 'cahier'.$n; ?>" value="on" 
                          
                          <?php
                                mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
                                $query_RsI = sprintf("SELECT ID_invite FROM cdt_invite WHERE prof_ID=%u AND classe_ID=%u AND gic_ID=%u AND matiere_ID=%u AND NumArchive=0",GetSQLValueString($_SESSION['ID_prof'],"int"),$row_RsInvite['ID_classe'],$row_RsInvite['gic_ID'],$row_RsInvite['ID_matiere']);
                                $RsI = mysqli_query($conn_cahier_de_texte, $query_RsI) or die(mysqli_error($conn_cahier_de_texte));
                                $row_RsI = mysqli_fetch_assoc($RsI);
                                $totalRows_RsI = mysqli_num_rows($RsI);
				if ($totalRows_RsI==1){echo " checked";};
			  ?>
			  >
                </div></td>
            </tr>
            <?php
		
mysqli_free_result($RsG);
			}
}?>
            <input name="classe<?php echo $n; ?>" type="hidden" value="<?php echo $row_RsInvite['ID_classe'];?>">
            <input name="prof<?php echo $n; ?>" type="hidden" value="<?php echo $_SESSION['ID_prof'];?>">
            <input name="gic<?php echo $n; ?>" type="hidden" value="<?php echo $row_RsInvite['gic_ID'];?>">
            <input name="matiere<?php echo $n; ?>" type="hidden" value="<?php echo $row_RsInvite['ID_matiere'];?>">
            <input name="num_archives<?php echo $n; ?>" type="hidden" value="0">
            <?php	$n=$n+1;
} while ($row_RsInvite = mysqli_fetch_assoc($RsInvite)); ?>
        </table>
        <blockquote>
          <p>
            <!-- nombre de cahiers annee en cours -->
            <input type="hidden" name="n" value="<?php echo $n?>">
          </p>
          <p>
            <?php
mysqli_free_result($RsInvite);


  
////// Archives  
  
  if ($totalRows_RsArchiv!=0) {
    $x=1;
	do {
		$num_archive="_save".$row_RsArchiv['NumArchive'];
				
//***********************************************************************************************************************************		
		mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
		$query_RsInvite = sprintf("SELECT DISTINCT gic_ID, nom_classe, nom_matiere, cdt_classe$num_archive.ID_classe,cdt_matiere$num_archive.ID_matiere FROM cdt_agenda$num_archive, cdt_classe$num_archive, cdt_matiere$num_archive WHERE prof_ID=%u AND cdt_classe$num_archive.ID_classe = cdt_agenda$num_archive.classe_ID AND cdt_matiere$num_archive.ID_matiere = cdt_agenda$num_archive.matiere_ID ORDER BY gic_ID, cdt_classe$num_archive.nom_classe, cdt_matiere$num_archive.nom_matiere ", $profchoix_RsInvite);
		$RsInvite = mysqli_query($conn_cahier_de_texte, $query_RsInvite) or die(mysqli_error($conn_cahier_de_texte));
		$row_RsInvite = mysqli_fetch_assoc($RsInvite);
		$totalRows_RsInvite = mysqli_num_rows($RsInvite);
		
		  if ($totalRows_RsInvite!=0) {?>
          </p>
          <p>&nbsp;</p>
          <p><strong>Mes archives - <?php echo $row_RsArchiv['NomArchive']; ?></strong></p>
        </blockquote>
        <table  width="70%" align="center" cellpadding="5" cellspacing="1" class="Style555">
          <?php 
	 $last_gic_ID=0;$n_archiv=1;
	 do { 
	 ?>
            <tr>
              <?php 
	  //Regroupements
			if ($row_RsInvite['gic_ID']==0){?>
              <td valign="bottom" bgcolor="#FFFFFF"><?php echo $row_RsInvite['nom_classe']; ?>&nbsp;</td>
              <td valign="bottom" bgcolor="#FFFFFF"><a href="../lire.php?classe_ID=<?php echo $row_RsInvite['ID_classe'];?>&matiere_ID=<?php echo $row_RsInvite['ID_matiere'];?>&gic_ID=<?php echo $row_RsInvite['gic_ID'];?>&ordre=down&archivID=<?php echo $row_RsArchiv['NumArchive'];?>" class="Style15" ><?php echo $row_RsInvite['nom_matiere']; ?></a></td>
              <td valign="bottom" bgcolor="#FFFFFF"><div align="center">
                  <input type="checkbox" name="<?php echo 'cahier'.$n_archiv.'archiv'.$x; ?>"   id="<?php echo 'cahier'.$n.'archiv'.$x; ?>" value="on"
                          <?php
                                mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
                                $query_RsI = sprintf("SELECT ID_invite FROM cdt_invite WHERE prof_ID=%u AND classe_ID=%u AND gic_ID=%u AND matiere_ID=%u AND NumArchive=%s",GetSQLValueString($_SESSION['ID_prof'],"int"),$row_RsInvite['ID_classe'],$row_RsInvite['gic_ID'],$row_RsInvite['ID_matiere'],$row_RsArchiv['NumArchive']);
                                $RsI = mysqli_query($conn_cahier_de_texte, $query_RsI) or die(mysqli_error($conn_cahier_de_texte));
                                $row_RsI = mysqli_fetch_assoc($RsI);
                                $totalRows_RsI = mysqli_num_rows($RsI);
				if ($totalRows_RsI==1){echo " checked";};
				
			  ?>
			  >
                </div></td>
            </tr>
            <?php

				}
				else
				{
			//presence de regroupement dans la matiere et la classe
			
if ($row_RsInvite['gic_ID']<>$last_gic_ID){ 
			// Rechercher le nom du regroupement
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsG =sprintf("SELECT nom_gic FROM cdt_groupe_interclasses$num_archive WHERE ID_gic=%u ",$row_RsInvite['gic_ID']);
$RsG = mysqli_query($conn_cahier_de_texte, $query_RsG) or die(mysqli_error($conn_cahier_de_texte));
$row_RsG = mysqli_fetch_assoc($RsG);?>
            <tr>
              <td valign="bottom" bgcolor="#FFFFFF"><?php echo '(R) '.$row_RsG['nom_gic'];
$last_gic_ID=$row_RsInvite['gic_ID'];
 ?>&nbsp;</td>
              <td valign="bottom" bgcolor="#FFFFFF"><a href="../lire.php?classe_ID=<?php echo $row_RsInvite['ID_classe'];?>&matiere_ID=<?php echo $row_RsInvite['ID_matiere'];?>&gic_ID=<?php echo $row_RsInvite['gic_ID'];?>&regroupement=<?php echo $row_RsG['nom_gic'];?>&ordre=down&archivID=<?php echo $row_RsArchiv['NumArchive'];?>" class="Style15" ><?php echo $row_RsInvite['nom_matiere']; ?></a></td>
              <td valign="bottom" bgcolor="#FFFFFF"><div align="center">
                  <input type="checkbox" name="<?php echo 'cahier'.$n_archiv.'archiv'.$x ?>"   id="<?php echo 'cahier'.$n.'archiv'.$x; ?>" value="on" 
                          
                          <?php
                                mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
                                $query_RsI = sprintf("SELECT ID_invite FROM cdt_invite WHERE prof_ID=%u AND classe_ID=%u AND gic_ID=%u AND matiere_ID=%u AND NumArchive=%s",GetSQLValueString($_SESSION['ID_prof'],"int"),$row_RsInvite['ID_classe'],$row_RsInvite['gic_ID'],$row_RsInvite['ID_matiere'],$row_RsArchiv['NumArchive']);
                                $RsI = mysqli_query($conn_cahier_de_texte, $query_RsI) or die(mysqli_error($conn_cahier_de_texte));
                                $row_RsI = mysqli_fetch_assoc($RsI);
                                $totalRows_RsI = mysqli_num_rows($RsI);
				if ($totalRows_RsI==1){echo " checked";};
				
			  ?>
			  >
                </div></td>
            </tr>
            <?php

mysqli_free_result($RsG);
			}
			}?>
            <input name="classe<?php echo $n_archiv.'archiv'.$x; ?>" type="hidden" value="<?php echo $row_RsInvite['ID_classe'];?>">
            <input name="prof<?php echo $n_archiv.'archiv'.$x; ?>" type="hidden" value="<?php echo $_SESSION['ID_prof'];?>">
            <input name="gic<?php echo $n_archiv.'archiv'.$x; ?>" type="hidden" value="<?php echo $row_RsInvite['gic_ID'];?>">
            <input name="matiere<?php echo $n_archiv.'archiv'.$x; ?>" type="hidden" value="<?php echo $row_RsInvite['ID_matiere'];?>">
            <?php 

$n_archiv=$n_archiv+1;
} while ($row_RsInvite = mysqli_fetch_assoc($RsInvite)); ?>
        </table>
        <blockquote>
        <p>
          <?php		
//***********************************************************************************************************************************	
};
mysqli_free_result($RsInvite);
?>
          <input type="hidden" name="NumArchive<?php echo $x;?>" value="<?php echo $row_RsArchiv['NumArchive'];?>">
          <?php

$x=$x+1;        
        }       while ($row_RsArchiv = mysqli_fetch_assoc($RsArchiv)); 
        
} else {$n_archiv=0;};
?>
        </p>
        <!-- nombre de cahiers en archive -->
        <input type="hidden" name="n_archiv" value="<?php echo $n_archiv?>">
        <!-- nombre darchives -->
        <input type="hidden" name="nb_archives" value="<?php echo $totalRows_RsArchiv?>">
        <input type="hidden" name="MM_insert" value="form1">
        </p>
        <p>&nbsp;</p>
        <p align="center">
          <input name="submit" type="submit" value="Enregistrer vos modifications">
        </p>
      </form>
    </div>
  </blockquote>
  <?php
  }
  else
  {
  ?>
    <blockquote>
  <div align="left" class="tab_detail" > 
   
      <form name="form2" method="post" action="publier_invite.php">
        <p><strong>Acc&egrave;s invit&eacute;</strong></p>
        <blockquote>
          <p> <em>Le param&eacute;trage actuel de l'application d&eacute;fini par l'administrateur autorise l'acc&egrave;s de l'ensemble des cahiers de textes de l'&eacute;tablissement &agrave; toute personne disposant d'un compte invit&eacute; (Coll&egrave;gues, enseignants ext&eacute;rieurs &agrave; l'&eacute;tablissement, conseiller p&eacute;dagogique, corps d'inspection...).<br>
            </em></p>
          <p><em>Demander &agrave; votre administrateur le <strong>mot de passe</strong> de ce compte de fa&ccedil;on &agrave; le fournir &agrave; votre &quot;invit&eacute;&quot; afin qu'il se connecte depuis la page d'accueil. </em> </p>
          <p>&nbsp;</p>
        </blockquote>
        <p><strong>Messages de la part de vos invit&eacute;s </strong></p>
        <blockquote>
          <p><em>En cochant <strong>l'option ci-dessous</strong>, vos invit&eacute;s auront la possibilit&eacute; de vous contacter sans utiliser le m&eacute;l, mais en vous envoyant un message 
            via votre cahier de textes. Cet envoi accompagn&eacute; d'&eacute;ventuelles pi&egrave;ces jointes, appara&icirc;tra en page de saisie de votre cahier de textes en bonne place au-dessus de vos autres messages issus du Responsable Etablissement ou de la Vie Scolaire. </em><br>
            <br>
            Autoriser vos invit&eacute;s &agrave; vous envoyer un message via l'interface du cahier de textes &nbsp;
            <input type="checkbox" name="message_invite2" id="message_invite2" value="" <?php if (!(strcmp($row_RsProf['message_invite'],'O'))) {echo "checked=checked";} ?>>
          </p>
        </blockquote>
      

      <p>&nbsp;</p>
      <p align="center">
    <input type="hidden" name="MM_insert" value="form2">
    <input name="submit2" type="submit" value="Enregistrer votre modification">
  </p>
  </form>
  <p align="left">&nbsp;</p></div>
  </blockquote> 
  <?php 
  };
  ?>
  <p>&nbsp;</p>
  <p align="center"><a href="enseignant.php">Retour au Menu Enseignant</a> </p>
<DIV id=footer></DIV>
</DIV>
</body>
</html>
<?php

mysqli_free_result($Rsgic);
mysqli_free_result($RsArchiv);
mysqli_free_result($RsProf);
?>
