<?php 
include "../authentification/authcheck.php" ;
if ($_SESSION['droits']<>4) { header("Location: ../index.php");exit;};
require_once('../Connections/conn_cahier_de_texte.php');
require_once('../inc/functions_inc.php');

$profchoix_RsImprime = "0";
if (isset($_SESSION['ID_prof'])) {

if ($_SESSION['droits']==4){ $profchoix_RsImprime=$_GET['ID_consult'];} 
else
{
  $profchoix_RsImprime = (get_magic_quotes_gpc()) ? $_SESSION['ID_prof'] : addslashes($_SESSION['ID_prof']);
};
};

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsImprime = sprintf("SELECT DISTINCT gic_ID, nom_classe, nom_matiere, cdt_classe.ID_classe,cdt_matiere.ID_matiere FROM cdt_agenda, cdt_classe, cdt_matiere WHERE prof_ID=%u AND cdt_classe.ID_classe = cdt_agenda.classe_ID AND cdt_matiere.ID_matiere = cdt_agenda.matiere_ID ORDER BY gic_ID, cdt_classe.nom_classe, cdt_matiere.nom_matiere ", $profchoix_RsImprime);
$RsImprime = mysqli_query($conn_cahier_de_texte, $query_RsImprime) or die(mysqli_error($conn_cahier_de_texte));
$row_RsImprime = mysqli_fetch_assoc($RsImprime);
$totalRows_RsImprime = mysqli_num_rows($RsImprime);

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_Rsgic =sprintf("SELECT * FROM cdt_groupe_interclasses WHERE  cdt_groupe_interclasses.prof_ID = %s ",$_SESSION['ID_prof']);
$Rsgic = mysqli_query($conn_cahier_de_texte, $query_Rsgic) or die(mysqli_error($conn_cahier_de_texte));
$row_Rsgic = mysqli_fetch_assoc($Rsgic);
$totalRows_Rsgic = mysqli_num_rows($Rsgic);

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsProfinvite = sprintf("SELECT passe,lien_invite_dir,datefin_invite_dir FROM cdt_prof WHERE ID_prof=%u",$_GET['ID_consult']);
$RsProfinvite = mysqli_query($conn_cahier_de_texte, $query_RsProfinvite) or die(mysqli_error($conn_cahier_de_texte));
$row_RsProfinvite = mysqli_fetch_assoc($RsProfinvite);


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
if(isset($_POST['reinit']) || $row_RsProfinvite['lien_invite_dir']=='')
	{
	$lien_invite_dir = md5("direction-".$row_RsProfinvite['passe']."-".genere_pwd());
	$updateSQL = sprintf("UPDATE cdt_prof SET lien_invite_dir=%s WHERE ID_prof=%u",
				GetSQLValueString($lien_invite_dir,"text"),
				GetSQLValueString($_GET['ID_consult'],"int"));
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$Result1 = mysqli_query($conn_cahier_de_texte, $updateSQL) or die(mysqli_error($conn_cahier_de_texte));	
	}
else $lien_invite_dir = $row_RsProfinvite['lien_invite_dir'];

//traitement de la date de validite du lien d'acces "invite"	
if(isset($_POST['date1'])  || $row_RsProfinvite['lien_invite_dir']=='' || !preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/",$row_RsProfinvite['datefin_invite_dir'])) 
	{
	$datefin_invite_dir = isset($_POST['date1']) ? substr($_POST['date1'],6,4).'-'.substr($_POST['date1'],3,2).'-'.substr($_POST['date1'],0,2) : date('Y-m-d',time()-3600*24);	
	if($datefin_invite_dir!=$row_RsProfinvite['datefin_invite_dir'])
		{
		$updateSQL = sprintf("UPDATE cdt_prof SET datefin_invite_dir=%s WHERE ID_prof=%u",
					GetSQLValueString($datefin_invite_dir,"text"),
					GetSQLValueString($_GET['ID_consult'],"int"));
		mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
		$Result1 = mysqli_query($conn_cahier_de_texte, $updateSQL) or die(mysqli_error($conn_cahier_de_texte));
		}
	}
else $datefin_invite_dir = $row_RsProfinvite['datefin_invite_dir'];

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
$header_description="Cahiers de textes de ".$_GET['ens_consult'];
require_once "../templates/default/header.php";
?>
  <HR>
  <p>&nbsp;</p>
  <table  width="90%" align="center" cellpadding="0" cellspacing="0">
    <tr>
      <td ><div align="left" class="tab_detail" ><div align="center">
        <p align="left"><img src="../images/puce_jaune.gif">&nbsp; <strong>Lien &agrave; communiquer aux corps d'inspection 
          dans le cas d'une mise &agrave; disposition des cahiers de textes de cet enseignant : </strong> </br>
            </p>
			<p>
            <?php

$ch='direction/cdt_enseignant.php?ID_consult='.$_GET['ID_consult'];
$url1="http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
$url2="invite/invite.php?ID_prof=".$_GET['ID_consult'].'&all&ident='.$lien_invite_dir;
$ch2='<a href="'. str_replace($ch,$url2,$url1).'">'.str_replace($ch,$url2,$url1).'</a>';
$array = explode('&',$ch2,-1);
$lien = '';
foreach ( $array as $contenu )
{
	$lien=$lien.$contenu.'&';
} 
$lien=substr($lien,0,-1);
echo $lien;
echo '</a>';

?>
            </br>

      </div>
        <form name="frm" method="POST" action="cdt_enseignant.php?ID_consult=<?php echo $_GET['ID_consult'];?>&ens_consult=<?php echo $_GET['ens_consult'];?>">
            <div align="center">
              <p><em>Ce lien est valide jusqu'au &nbsp;

                  <script>
         $(function() {
        	$.datepicker.regional['fr'] = { dateFormat: 'dd-mm-yy'};
			$.datepicker.setDefaults($.datepicker.regional['fr']);
        	$('#date1').datepicker({firstDay:1});
        });
                  </script>
                <input name='date1' type='text' id='date1' value="<?php 
	  $date1_form=substr($datefin_invite_dir,8,2).'-'.substr($datefin_invite_dir,5,2).'-'.substr($datefin_invite_dir,0,4);
	  echo $date1_form;?>" size="10"/>
              </em>
              &nbsp;<br/>
              <input name="reinit" type="checkbox" value="1"/>
              g&eacute;n&eacute;rer un nouveau lien (plus aucun acc&egrave;s ne pourra alors se faire avec le lien actuel)</p>
              <p>              
                <input name="submit" type="submit" value="Actualiser"/></p>
            </div>
          </form>
          </div>
        <p>&nbsp;</p></td>
    </tr>
  </table>
  <table  width="90%" align="center" cellpadding="0" cellspacing="1" class="bordure">
  <tr>
      <td  class="Style6">Classe&nbsp;</td>
      <td  class="Style6">Mati&egrave;re&nbsp;</td>
      <td  class="Style6">Dernier ajout&nbsp;</td>
    </tr>
    <?php 
         $last_gic_ID=0;
         do { 
         ?>
      <tr>
        <?php

//MODIF Date derniere saisie
$datetoday=date('Ymd').'9';
//$classencours = $row_RsImprime['ID_classe'];
//$matiereencours = $row_RsImprime['ID_matiere'];
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsPublier = sprintf("SELECT MAX(code_date) FROM cdt_agenda
        WHERE code_date<=%s AND prof_ID=%u AND classe_ID=%u AND matiere_ID=%u",$datetoday,$profchoix_RsImprime,$row_RsImprime['ID_classe'],$row_RsImprime['ID_matiere']);
$RsPublier = mysqli_query($conn_cahier_de_texte, $query_RsPublier) or die(mysqli_error($conn_cahier_de_texte));
$row_RsPublier = mysqli_fetch_assoc($RsPublier);
$totalRows_RsPublier = mysqli_num_rows($RsPublier);
$date_lastajout=substr($row_RsPublier['MAX(code_date)'],6,2).'/'.substr($row_RsPublier['MAX(code_date)'],4,2).'/'.substr($row_RsPublier['MAX(code_date)'],0,4);
//Fin Ajout Date derniere saisie
 
          //Regroupements
                        if ($row_RsImprime['gic_ID']==0){?>
        <td valign="bottom" bgcolor="#FFFFFF" class="tab_detail"><?php echo $row_RsImprime['nom_classe']; ?>&nbsp;</td>
        <td valign="bottom" bgcolor="#FFFFFF" class="tab_detail"><a href="../lire.php?classe_ID=<?php echo $row_RsImprime['ID_classe'];?>&matiere_ID=<?php echo $row_RsImprime['ID_matiere'];?>&gic_ID=<?php echo $row_RsImprime['gic_ID'];?>&ID_consult=<?php echo $profchoix_RsImprime;?>&ens_consult=<?php echo $_GET['ens_consult'];?>&ordre=down" ><?php echo $row_RsImprime['nom_matiere']; ?></a></td>
<!-- ajout Date derniere saisie -->
        <td valign="bottom" bgcolor="#FFFFFF" class="tab_detail">
        <?php echo $date_lastajout; ?>&nbsp;</td>
<!-- Fin ajout Date derniere saisie -->        
      </tr>
      <?php
                                
				}
				else
				{
                        //presence de regroupement dans la matiere et la classe
                        
if ($row_RsImprime['gic_ID']<>$last_gic_ID){
//MODIF Date derniere saisie
$datetoday=date('Ymd').'9';
//$classencours = $row_RsImprime['ID_classe'];
//$matiereencours = $row_RsImprime['ID_matiere'];
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsPublier = sprintf("SELECT MAX(code_date) FROM cdt_agenda
        WHERE code_date<=%s AND prof_ID=%u AND classe_ID=%u AND matiere_ID=%u AND gic_ID=%u",$datetoday,$profchoix_RsImprime,$row_RsImprime['ID_classe'],$row_RsImprime['ID_matiere'],$row_RsImprime['gic_ID']);
$RsPublier = mysqli_query($conn_cahier_de_texte, $query_RsPublier) or die(mysqli_error($conn_cahier_de_texte));
$row_RsPublier = mysqli_fetch_assoc($RsPublier);
$totalRows_RsPublier = mysqli_num_rows($RsPublier);
$date_lastajout=substr($row_RsPublier['MAX(code_date)'],6,2).'/'.substr($row_RsPublier['MAX(code_date)'],4,2).'/'.substr($row_RsPublier['MAX(code_date)'],0,4);
//Fin Ajout Date derniere saisie 
                        // Rechercher le nom du regroupement
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsG =sprintf("SELECT nom_gic FROM cdt_groupe_interclasses WHERE  cdt_groupe_interclasses.ID_gic = %u ",$row_RsImprime['gic_ID']);
$RsG = mysqli_query($conn_cahier_de_texte, $query_RsG) or die(mysqli_error($conn_cahier_de_texte));
$row_RsG = mysqli_fetch_assoc($RsG);?>
      <td valign="bottom" bgcolor="#FFFFFF" class="tab_detail"><?php echo '(R) '.$row_RsG['nom_gic'];
$last_gic_ID=$row_RsImprime['gic_ID'];
 ?>&nbsp;</td>
        <td valign="bottom" bgcolor="#FFFFFF" class="tab_detail"><a href="../lire.php?classe_ID=<?php echo $row_RsImprime['ID_classe'];?>&matiere_ID=<?php echo $row_RsImprime['ID_matiere'];?>&gic_ID=<?php echo $row_RsImprime['gic_ID'];?>&ID_consult=<?php echo $profchoix_RsImprime;?>&ens_consult=<?php echo $_GET['ens_consult'];?>&regroupement=<?php echo $row_RsG['nom_gic'];?>&ordre=down" ><?php echo $row_RsImprime['nom_matiere']; ?></a></td>
<!-- ajout Date derniere saisie -->
        <td valign="bottom" bgcolor="#FFFFFF" class="tab_detail">
        <?php echo $date_lastajout; ?>&nbsp;</td>
<!-- Fin ajout Date derniere saisie -->      
      </tr>
      <?php
                
			}
                        }
} while ($row_RsImprime = mysqli_fetch_assoc($RsImprime)); ?>
  </table>
  <p align="center">&nbsp; </p>
  <p align="center"><a href="../index.php">Me d&eacute;connecter</a> - <a href="direction.php">Retour au Menu Responsable Etablissement</a></p>
  <DIV id=footer>
    <p class="auteur"><a href="contribution.php" class="auteur">Pierre Lemaitre 
      - St L&ocirc; (France) <br />
      </a></p>
  </DIV>
</DIV>
</body>
</html>
<?php
mysqli_free_result($RsImprime);
mysqli_free_result($Rsgic);
mysqli_free_result($RsProfinvite);
mysqli_free_result($RsPublier);
?>
