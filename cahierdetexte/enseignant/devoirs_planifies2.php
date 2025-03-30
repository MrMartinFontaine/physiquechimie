<?php 
include "../authentification/authcheck.php";
if (($_SESSION['droits']<>2)&&($_SESSION['droits']<>8)) { header("Location: ../index.php");exit;};
require_once('../Connections/conn_cahier_de_texte.php'); 
require_once('../inc/functions_inc.php');

$editFormAction = '#';
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}?>
<?php
if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {


mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);

$query_RsClasse = sprintf("SELECT * FROM cdt_classe WHERE ID_classe=%u",GetSQLValueString($_POST['classe_ID'], "int"));
$RsClasse = mysqli_query($conn_cahier_de_texte, $query_RsClasse) or die(mysqli_error($conn_cahier_de_texte));
$row_RsClasse = mysqli_fetch_assoc($RsClasse);


$query_RsMatiere = sprintf("SELECT * FROM cdt_matiere WHERE ID_matiere=%u",GetSQLValueString($_POST['matiere_ID'], "int"));
$RsMatiere = mysqli_query($conn_cahier_de_texte, $query_RsMatiere) or die(mysqli_error($conn_cahier_de_texte));
$row_RsMatiere = mysqli_fetch_assoc($RsMatiere);


if (isset($_SESSION['semdate'])){$sem=$_SESSION['semdate'];} else {$sem='A et B';};


$heure_debut=$_POST['heure_debut_h'].'h'.$_POST['heure_debut_min'];
if ($_POST['heure_debut_h']<10){$heure_debut='0'.$_POST['heure_debut_h'];}else {$heure_debut=$_POST['heure_debut_h'];};
if ($_POST['heure_debut_min']<10){$heure_debut=$heure_debut.'h'.'0'.$_POST['heure_debut_min'];}else{$heure_debut=$heure_debut.'h'.$_POST['heure_debut_min'];};
//calcul de heure_fin
$heure_t=intval(($_POST['heure_debut_min'] + $_POST['duree_min'])/60) + $_POST['heure_debut_h'] + $_POST['duree_h'];
$minute_t=fmod(($_POST['heure_debut_min']+$_POST['duree_min']), 60);
if ($minute_t<10){$minute_t='0'.$minute_t;};
if ($heure_t<10){$heure_t='0'.$heure_t;};
$heure_fin= $heure_t.'h';
$heure_fin=$heure_fin.$minute_t;
if($_POST['duree_min']<10){$duree='0'.$_POST['duree_min'];}else{$duree=$_POST['duree_min'];};
if ($_POST['duree_h']==0){$duree=$duree.'min';}else{
$duree=$_POST['duree_h'].'h'.$duree;
;};


if (substr($_POST["classe_ID"],0,1)==0){ // c'est un regroupement
$gic_ID=substr($_POST["classe_ID"],1,strlen($_POST["classe_ID"]));$classe_ID=0; }
else
{$gic_ID=0;$classe_ID=$_POST["classe_ID"];};


$GoTo = 'ecrire.php?saisie=1&ds_prog&nom_classe='.$row_RsClasse['nom_classe']
.'&classe_ID='.$classe_ID
.'&gic_ID='.$gic_ID
.'&nom_matiere='.$row_RsMatiere['nom_matiere']
.'&groupe='.$_POST['groupe'].'&matiere_ID='.$_POST['matiere_ID']
.'&semaine='.$sem.'&heure='.$_POST['heure'].'&duree='.$duree.'&heure_debut='.$heure_debut.'&heure_fin='.$heure_fin;


  if (isset($_SERVER['QUERY_STRING'])) {    $GoTo .= (strpos($GoTo, '?')) ? "&" : "?";    $GoTo .= $_SERVER['QUERY_STRING'];  }
  mysqli_free_result($RsClasse);
  mysqli_free_result($RsMatiere);
  header(sprintf("Location: %s", $GoTo));
};


 ?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Cahier de textes - <?php echo $_SESSION['identite']?></title>
<?php
$nb_HS=0;
$madate=substr($_GET['code_date'],0,8);
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsHS = sprintf("SELECT * FROM cdt_agenda WHERE substring(code_date,9,1)=0 AND substring(code_date,1,8)=%s AND type_activ='%s'",$madate,'ds_prog');
$RsHS = mysqli_query($conn_cahier_de_texte, $query_RsHS) or die(mysqli_error($conn_cahier_de_texte));
$row_RsHS = mysqli_fetch_assoc($RsHS);
$totalRows_RsHS = mysqli_num_rows($RsHS);
if ($nb_HS<9){$nb_HS=$totalRows_RsHS+1;}else{$nb_HS=9;};

//La gestion semaine ab definie par l'administrateur est-elle prise en compte ?
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsSem = sprintf("SELECT * FROM cdt_prof WHERE ID_prof=%u", $_SESSION['ID_prof']);
$RsSem = mysqli_query($conn_cahier_de_texte, $query_RsSem) or die(mysqli_error($conn_cahier_de_texte));
$row_RsSem = mysqli_fetch_assoc($RsSem);

if ($row_RsSem['gestion_sem_ab']=='O'){

//recup de la semaine
        if (isset($_GET['code_date'])){ //chercher la semaine
                mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
                $query_RsSemdate =  sprintf("SELECT * FROM cdt_semaine_ab WHERE s_code_date<=%s ORDER BY `s_code_date` DESC LIMIT 1 ",substr($_GET['code_date'],0,8));
                $RsSemdate = mysqli_query($conn_cahier_de_texte, $query_RsSemdate) or die(mysqli_error($conn_cahier_de_texte));
                $row_RsSemdate= mysqli_fetch_assoc($RsSemdate);
                $_SESSION['semdate']=$row_RsSemdate['semaine'];
    };
	};

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsClasse = sprintf("SELECT * FROM cdt_classe WHERE ID_classe=%u ORDER BY nom_classe ASC",$_GET['classe_ID']) ;
$RsClasse = mysqli_query($conn_cahier_de_texte, $query_RsClasse) or die(mysqli_error($conn_cahier_de_texte));
$row_RsClasse = mysqli_fetch_assoc($RsClasse);
$totalRows_RsClasse = mysqli_num_rows($RsClasse);

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsMatiere = "SELECT * FROM cdt_matiere ORDER BY nom_matiere ASC";
$RsMatiere = mysqli_query($conn_cahier_de_texte, $query_RsMatiere) or die(mysqli_error($conn_cahier_de_texte));
$row_RsMatiere = mysqli_fetch_assoc($RsMatiere);
$totalRows_RsMatiere = mysqli_num_rows($RsMatiere);

if(isset($_GET["classe_ID"])){
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$rq=mysqli_query($conn_cahier_de_texte, "SELECT DISTINCT cdt_matiere.nom_matiere,cdt_emploi_du_temps.matiere_ID FROM cdt_matiere LEFT JOIN cdt_emploi_du_temps ON cdt_matiere.ID_matiere=cdt_emploi_du_temps.matiere_ID WHERE cdt_emploi_du_temps.classe_ID=".$_GET["classe_ID"]." AND prof_ID= ".$_SESSION['ID_prof']. " ORDER BY matiere_ID");
};

$refprof_Rs_emploi = "0";
if (isset($_SESSION['ID_prof'])) {
  $refprof_Rs_emploi = (get_magic_quotes_gpc()) ? $_SESSION['ID_prof'] : addslashes($_SESSION['ID_prof']);
}
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_Rs_emploi = sprintf("SELECT * FROM cdt_emploi_du_temps,cdt_classe,cdt_matiere WHERE cdt_emploi_du_temps.matiere_ID=cdt_matiere.ID_matiere AND cdt_emploi_du_temps.classe_ID=cdt_classe.ID_classe AND cdt_emploi_du_temps.prof_ID=%u ORDER BY cdt_emploi_du_temps.jour_semaine, cdt_emploi_du_temps.heure, cdt_emploi_du_temps.semaine", $refprof_Rs_emploi);
$Rs_emploi = mysqli_query($conn_cahier_de_texte, $query_Rs_emploi) or die(mysqli_error($conn_cahier_de_texte));
$row_Rs_emploi = mysqli_fetch_assoc($Rs_emploi);
$totalRows_Rs_emploi = mysqli_num_rows($Rs_emploi);


mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_Rsgroupe = "SELECT * FROM cdt_groupe ORDER BY ID_groupe ASC";
$Rsgroupe = mysqli_query($conn_cahier_de_texte, $query_Rsgroupe) or die(mysqli_error($conn_cahier_de_texte));
$row_Rsgroupe = mysqli_fetch_assoc($Rsgroupe);
$totalRows_Rsgroupe = mysqli_num_rows($Rsgroupe);
?>

<style type="text/css">
<!--
.Style70 {
	color: #FFFFFF;
	font-family: Arial, Helvetica, sans-serif;
}
-->
</style>
<LINK media=screen href="../styles/style_default.css" type=text/css rel=stylesheet>
<LINK media=screen href="../templates/default/header_footer.css" type=text/css rel=stylesheet>
</HEAD>
<BODY>
<DIV id=page>
<?php 
if (isset($_SESSION['semdate'])){$sema= ' - Semaine '.$_SESSION['semdate'];} else {$sema='';};

$header_description="<br /><b>Planification d'un devoir en ".$row_RsClasse['nom_classe'];
require_once "../templates/default/header.php";
?>
<form name="form1" method="post" action="devoirs_planifies2.php?jour_pointe=<?php if (isset($_GET['jour_pointe'])) {echo $_GET['jour_pointe'];};?>&current_day_name=<?php echo $_GET['current_day_name']?>&code_date=<?php echo $_GET['code_date'] ?> ">
    
	<table width="91%"  border="0" align="center" >
              <td align="right" valign="middle" nowrap="nowrap"><span class="Style15">Devoir le <?php echo '<strong>'.$_GET['jour_pointe'].'</strong>'.$sema;?>&nbsp;</span><span class="Style14">:</span></td>
      <td align="left"><div  id='matiere' style='display:inline' align="left">
<?php		
		echo "<select name='matiere_ID'>";
		echo "<option value='value2'>S&eacute;lectionner la mati&egrave;re</option>";
		while($row = mysqli_fetch_row($rq)){
			echo "<option value='".$row[1]."'>".$row[0]."</option>";
		};
    	echo "</select>";
?>
		
		</div>        </td>
      </tr>
      </tr>
      
      <tr valign="baseline">
        <td align="right" nowrap class="Style15">&nbsp;</td>
        <td>&nbsp;</td>
      </tr>
      <tr valign="baseline">
        <td align="right" nowrap class="Style15">Groupe : </td>
        <td><div align="left">
            <select name="groupe" size="1" id="select">
              <?php
do {  
?>
              <option value="<?php echo $row_Rsgroupe['groupe']?>"><?php echo $row_Rsgroupe['groupe']?></option>
              <?php
} while ($row_Rsgroupe = mysqli_fetch_assoc($Rsgroupe));
  $rows = mysqli_num_rows($Rsgroupe);
  if($rows > 0) {
      mysqli_data_seek($Rsgroupe, 0);
	  $row_Rsgroupe = mysqli_fetch_assoc($Rsgroupe);
  }
?>
            </select>
          </div></td>
      </tr>
      <tr valign="baseline">
        <td nowrap align="right">&nbsp;</td>
        <td>&nbsp;</td>
      </tr>
      <tr valign="baseline">
        <td nowrap align="right"><div align="right" class="Style15">Heure d&eacute;but (facultatif, mais conseill&eacute;):</div></td>
        <td><div align="left"><select name="heure_debut_h">
      <option value="7" selected="selected">07 h</option>
      <option value="8">08 h</option>
      <option value="9">09 h</option>
      <option value="10">10 h</option>
      <option value="11">11 h</option>
      <option value="12">12 h</option>
      <option value="13">13 h</option>
      <option value="14">14 h</option>
      <option value="15">15 h</option>
      <option value="16">16 h</option>
      <option value="17">17 h</option>
      <option value="18">18 h</option>
      <option value="19">19 h</option>
      <option value="20">20 h</option>
      <option value="21">21 h</option>
      <option value="22">22 h</option>
    </select>
    <select name="heure_debut_min" size="1">
      <option value="0" selected="selected">00 min</option>
      <option value="5">05 min</option>
      <option value="10">10 min</option>
      <option value="15">15 min</option>
      <option value="20">20 min</option>
      <option value="25">25 min</option>
      <option value="30">30 min</option>
      <option value="35">35 min</option>
      <option value="40">40 min</option>
      <option value="45">45 min</option>
      <option value="50">50 min</option>
      <option value="55">55 min</option>
    </select></div></td>
      </tr>
      <tr valign="baseline">
        <td nowrap align="right"><div align="right" class="Style15">Dur&eacute;e (facultatif):</div></td>
        <td><div align="left">
            <select name="duree_h" size="1">
      <option value="0" selected="selected">0 h</option>
	  <option value="1">1 h</option>
      <option value="2">2 h</option>
      <option value="3">3 h</option>
      <option value="4">4 h</option>
      <option value="5">5 h</option>
      <option value="6">6 h</option>
      <option value="7">7 h</option>
      <option value="8">8 h</option>
    </select>
    <select name="duree_min" size="1">
      <option value="0" selected="selected">00 min</option>
      <option value="5">05 min</option>
      <option value="10">10 min</option>
      <option value="15">15 min</option>
      <option value="20">20 min</option>
      <option value="25">25 min</option>
      <option value="30">30 min</option>
      <option value="35">35 min</option>
      <option value="40">40 min</option>
      <option value="45">45 min</option>
      <option value="50">50 min</option>
      <option value="55">55 min</option>
    </select>
          </div></td>
      </tr>
      <tr valign="baseline">
        <td nowrap align="right">&nbsp;</td>
        <td>&nbsp;</td>
      </tr>
      <tr valign="baseline">
        <td nowrap align="right">&nbsp;</td>
        <td><input type="submit" name="Submit" value="Valider" /></td>
      </tr>
    </table>
    <p align="center">
      <input name="ordre" type="hidden" value="up">
    <a href="../planning.php?classe_ID=<?php echo $_GET['classe_ID'];?>">Annuler</a>
    <input type="hidden" name="heure" value="<?php echo $nb_HS;?>">
	<input type="hidden" name="classe_ID" value="<?php echo $_GET['classe_ID'];?>">
    <input type="hidden" name="type_activ" value="ds_prog">
    <input type="hidden" name="MM_insert" value="form1">
  </p>
    </form>
  <?php
mysqli_free_result($RsClasse);
mysqli_free_result($RsMatiere);
mysqli_free_result($Rsgroupe);
?>
  <DIV id=footer></DIV>
</DIV>
</BODY>
</HTML>
