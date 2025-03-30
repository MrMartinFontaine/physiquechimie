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

$mes_erreurs=0;$erreur_matiere=0;$erreur_classe=0;
if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {

	if ((!isset($_POST['classe_ID']))||($_POST['classe_ID']=='')||($_POST['classe_ID']=='value')){ $mes_erreurs=1;$erreur_classe=1;};
	if ((!isset($_POST['matiere_ID']))||($_POST['matiere_ID']=='')||($_POST['matiere_ID']=='value2')){ $mes_erreurs=1;$erreur_matiere=1;};

if ($mes_erreurs==0) {

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
}
}
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


mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsClasse = "SELECT * FROM cdt_classe ORDER BY nom_classe ASC" ;
$RsClasse = mysqli_query($conn_cahier_de_texte, $query_RsClasse) or die(mysqli_error($conn_cahier_de_texte));
$row_RsClasse = mysqli_fetch_assoc($RsClasse);
$totalRows_RsClasse = mysqli_num_rows($RsClasse);

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsMatiere = "SELECT * FROM cdt_matiere ORDER BY nom_matiere ASC";
$RsMatiere = mysqli_query($conn_cahier_de_texte, $query_RsMatiere) or die(mysqli_error($conn_cahier_de_texte));
$row_RsMatiere = mysqli_fetch_assoc($RsMatiere);
$totalRows_RsMatiere = mysqli_num_rows($RsMatiere);

$refprof_Rs_emploi = "0";
if (isset($_SESSION['ID_prof'])) {
  $refprof_Rs_emploi = (get_magic_quotes_gpc()) ? $_SESSION['ID_prof'] : addslashes($_SESSION['ID_prof']);
}
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_Rs_emploi = sprintf("SELECT * FROM cdt_emploi_du_temps,cdt_classe,cdt_matiere WHERE  cdt_emploi_du_temps.matiere_ID=cdt_matiere.ID_matiere AND cdt_emploi_du_temps.classe_ID=cdt_classe.ID_classe AND cdt_emploi_du_temps.prof_ID=%u ORDER BY cdt_emploi_du_temps.jour_semaine, cdt_emploi_du_temps.heure, cdt_emploi_du_temps.semaine", $refprof_Rs_emploi);
$Rs_emploi = mysqli_query($conn_cahier_de_texte, $query_Rs_emploi) or die(mysqli_error($conn_cahier_de_texte));
$row_Rs_emploi = mysqli_fetch_assoc($Rs_emploi);
$totalRows_Rs_emploi = mysqli_num_rows($Rs_emploi);


mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_Rsgroupe = "SELECT * FROM cdt_groupe ORDER BY ID_groupe ASC";
$Rsgroupe = mysqli_query($conn_cahier_de_texte, $query_Rsgroupe) or die(mysqli_error($conn_cahier_de_texte));
$row_Rsgroupe = mysqli_fetch_assoc($Rsgroupe);
$totalRows_Rsgroupe = mysqli_num_rows($Rsgroupe);
?>
<script language="JavaScript" type="text/javascript">

<!--

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

			/**
			* Methode qui sera appelee sur le click du bouton
			*/
			function go(){
				getXhr();
				// On definit ce qu'on va faire quand on aura la reponse
				xhr.onreadystatechange = function(){
					// On ne fait quelque chose que si on a tout recu et que le serveur est ok
					if(xhr.readyState == 4 && xhr.status == 200){
						leselect = xhr.responseText;
						// On se sert de innerHTML pour rajouter les options a la liste
						document.getElementById('matiere').innerHTML = leselect;
					}
				}

				
				xhr.open("POST","ajax_matiere_devoirs.php?code_date=<?php echo $_GET['code_date']; ?>&jour_pointe=<?php echo $_GET['jour_pointe']; ?>&current_day_name=<?php echo $_GET['current_day_name']; ?>",true);

				xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');

				sel = document.getElementById('classe');
				classe = sel.options[sel.selectedIndex].value;
				xhr.send("Classe="+classe);
			}
function MM_reloadPage(init) {  //reloads the window if Nav4 resized

  if (init==true) with (navigator) {if ((appName=="Netscape")&&(parseInt(appVersion)==4)) {

    document.MM_pgW=innerWidth; document.MM_pgH=innerHeight; onresize=MM_reloadPage; }}

  else if (innerWidth!=document.MM_pgW || innerHeight!=document.MM_pgH) location.reload();

}

MM_reloadPage(true);



function MM_goToURL() { //v3.0

  var i, args=MM_goToURL.arguments; document.MM_returnValue = false;

  for (i=0; i<(args.length-1); i+=2) eval(args[i]+".location='"+args[i+1]+"'");

}

//-->

</script>
<style type="text/css">
<!--
.Style70 {
	color: #FFFFFF;
	font-family: Arial, Helvetica, sans-serif;
}
-->
</style>
<link media="screen" href="../styles/style_default.css" type="text/css" rel="stylesheet" />
<link media="screen" href="../templates/default/header_footer.css" type="text/css" rel="stylesheet" />
</head>
<body>
<DIV id=page><?php if (isset($_SESSION['semdate'])){$sema= ' - Semaine '.$_SESSION['semdate'];} else {$sema='';};
$header_description="<br />Planification d'un devoir";
require_once "../templates/default/header.php";	
?>

  <p>&nbsp;</p>
  <form action="devoirs_planifies.php?jour_pointe=<?php if (isset($_GET['jour_pointe'])) {echo $_GET['jour_pointe'];} else {echo $jour_pointe;}?>&amp;current_day_name=<?php echo $_GET['current_day_name']?>&amp;code_date=<?php echo $_GET['code_date'] ?> " method="post" name="form1" id="form1">
    <table width="91%"  border="0" align="center" >
      <tr>
        <td align="right" valign="middle" nowrap="nowrap"><span class="Style15">Devoir le <?php echo '<strong>'.$_GET['jour_pointe'].'</strong>'.$sema;?>&nbsp;</span><span class="Style14">:</span></td>
        <td align="left"><select name='classe_ID' id='classe' onchange='go()'>
            <option value='value'>S&eacute;lectionner une classe</option>
            <?php

	
						$res = mysqli_query($conn_cahier_de_texte, sprintf("SELECT DISTINCT nom_classe,ID_classe FROM cdt_classe,cdt_emploi_du_temps WHERE cdt_classe.ID_classe=cdt_emploi_du_temps.classe_ID AND prof_ID=%u AND cdt_emploi_du_temps.edt_exist_fin >='%s' ORDER BY nom_classe ASC",$_SESSION['ID_prof'],date('Y-m-j')));
						while($row = mysqli_fetch_assoc($res)){
							echo "<option value='".$row["ID_classe"]."'>".$row["nom_classe"]."</option>";
						}

//regroupements                                         

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_Rsgic =sprintf("SELECT DISTINCT nom_gic,ID_gic FROM cdt_groupe_interclasses,cdt_emploi_du_temps WHERE  cdt_groupe_interclasses.ID_gic=cdt_emploi_du_temps.gic_ID AND cdt_groupe_interclasses.prof_ID=%u AND cdt_emploi_du_temps.edt_exist_fin >='%s' ",$_SESSION['ID_prof'],date('Y-m-j'));
$Rsgic = mysqli_query($conn_cahier_de_texte, $query_Rsgic) or die(mysqli_error($conn_cahier_de_texte));
$row_Rsgic = mysqli_fetch_assoc($Rsgic);
$totalrow_Rsgic = mysqli_num_rows($Rsgic);
if ($totalrow_Rsgic>0){
		do { 
		echo "<option value='0".$row_Rsgic['ID_gic']."'>".$row_Rsgic['nom_gic']."</option>";
		} while ($row_Rsgic = mysqli_fetch_assoc($Rsgic));
};
						

						
					?>
          </select>
<?php
if ($erreur_matiere ==1){echo '<br /><span style="color: #FF0000">Vous devez s&eacute;lectionner la classe puis la mati&egrave;re</span>';};	?>
		  </td>
      </tr>
      <tr>
        <td align="right" valign="top" nowrap="nowrap">&nbsp;</td>
        <td align="left"><div  id='matiere' style='display:inline' align="left"></div>        </td>
      </tr>
      <tr valign="baseline">
        <td align="right" nowrap="nowrap" class="Style15">&nbsp;</td>
        <td>&nbsp;</td>
      </tr>
      <tr valign="baseline">
        <td align="right" nowrap="nowrap" class="Style15">Groupe : </td>
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
        <td align="right" nowrap="nowrap" class="Style14">&nbsp;</td>
        <td>&nbsp;</td>
      </tr>
      <tr valign="baseline">
        <td align="right" nowrap="nowrap" class="Style15"><div align="right">Heure d&eacute;but (facultatif, mais conseill&eacute;):</div></td>
        <td><div align="left">
            <select name="heure_debut_h">
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
            </select>
          </div></td>
      </tr>
      <tr valign="baseline">
        <td nowrap="nowrap" align="right"><div align="right" class="Style15">Dur&eacute;e (facultatif):</div></td>
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
        <td nowrap="nowrap" align="right">&nbsp;</td>
        <td>&nbsp;</td>
      </tr>
      <tr valign="baseline">
        <td nowrap="nowrap" align="right">&nbsp;</td>
        <td><div align="left">
            <input type="submit" name="Submit" value="Valider" />
          </div></td>
      </tr>
    </table>
    <p align="center">
      <input name="ordre" type="hidden" value="up" />
    </p>
    <a href="ecrire.php?date=<?php echo substr($_GET['code_date'],0,8);?>">Annuler</a>
    <input type="hidden" name="heure" value="<?php echo $nb_HS;?>" />
    <input type="hidden" name="type_activ" value="ds_prog" />
    <input type="hidden" name="MM_insert" value="form1" />
  </form>

<DIV id=footer></DIV>
</DIV>
</body>
</html>
  <?php
mysqli_free_result($RsClasse);
mysqli_free_result($RsMatiere);
mysqli_free_result($Rsgroupe);
?>
