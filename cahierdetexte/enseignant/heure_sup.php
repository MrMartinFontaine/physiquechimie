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
$mes_erreurs=0;$erreur_matiere=0;$erreur_indice_plage=0;$erreur_classe=0;
if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {
	
	if ((!isset($_POST['classe_ID']))||($_POST['classe_ID']=='')||($_POST['classe_ID']=='value')){ $mes_erreurs=1;$erreur_classe=1;};
	if ((!isset($_POST['matiere_ID']))||($_POST['matiere_ID']=='')||($_POST['matiere_ID']=='value2')){ $mes_erreurs=1;$erreur_matiere=1;};
	if ((!isset($_POST['h1']))||($_POST['h1']=='')){ $mes_erreurs=1;$erreur_indice_plage=1;}; 
	
	if ($mes_erreurs==0) {
                
                mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
                
                $query_RsClasse = sprintf("SELECT * FROM cdt_classe WHERE ID_classe=%u",GetSQLValueString($_POST['classe_ID'], "int"));
                $RsClasse = mysqli_query($conn_cahier_de_texte, $query_RsClasse) or die(mysqli_error($conn_cahier_de_texte));
                $row_RsClasse = mysqli_fetch_assoc($RsClasse);
                
                
                $query_RsMatiere = sprintf("SELECT * FROM cdt_matiere WHERE ID_matiere=%u",GetSQLValueString($_POST['matiere_ID'], "int"));
                $RsMatiere = mysqli_query($conn_cahier_de_texte, $query_RsMatiere) or die(mysqli_error($conn_cahier_de_texte));
                $row_RsMatiere = mysqli_fetch_assoc($RsMatiere);
                
		
		if (isset($_SESSION['semdate'])){$sem=$_SESSION['semdate'];} else {$sem='A et B';};
		if (isset($_POST['edt_exist_debut'])){$date1=substr($_POST['edt_exist_debut'],6,4).'-'.substr($_POST['edt_exist_debut'],3,2).'-'.substr($_POST['edt_exist_debut'],0,2);} 
		if (isset($_POST['edt_exist_fin'])){$date2=substr($_POST['edt_exist_fin'],6,4).'-'.substr($_POST['edt_exist_fin'],3,2).'-'.substr($_POST['edt_exist_fin'],0,2);} 
		
		$heure_debut=$_POST['h1'].'h'.$_POST['mn1'];
		$heure_fin=$_POST['h2'].'h'.$_POST['mn2'];
		
		if (substr($_POST["classe_ID"],0,1)==0){ // c'est un regroupement
		$gic_ID=substr($_POST["classe_ID"],1,strlen($_POST["classe_ID"]));$classe_ID=0; }
		else
		{$gic_ID=0;$classe_ID=$_POST["classe_ID"];};
		
		$GoTo = 'ecrire.php?saisie=1&nom_classe='.$row_RsClasse['nom_classe']
		.'&classe_ID='.$classe_ID
		.'&gic_ID='.$gic_ID
		.'&nom_matiere='.$row_RsMatiere['nom_matiere']
		.'&groupe='.$_POST['groupe'].'&matiere_ID='.$_POST['matiere_ID']
		.'&semaine='.$sem.'&jour_pointe='.$_GET['current_day_name']
		.'&heure='.$_POST['heure'].'&heure_debut='.$heure_debut.'&heure_fin='.$heure_fin;
		if ((isset($_POST['duree']))&&($_POST['duree']!='')){$GoTo .='&duree='.$_POST['duree'];} else {$GoTo .='&duree';};
		
		if (isset($_SERVER['QUERY_STRING'])) {    $GoTo .= (strpos($GoTo, '?')) ? "&" : "?";    $GoTo .= $_SERVER['QUERY_STRING'];  }
		mysqli_free_result($RsClasse);
		mysqli_free_result($RsMatiere);
		header(sprintf("Location: %s", $GoTo));
	};
};

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_Rsplage_all ="SELECT * FROM cdt_plages_horaires ORDER BY ID_plage";
$Rsplage_all = mysqli_query($conn_cahier_de_texte, $query_Rsplage_all) or die(mysqli_error($conn_cahier_de_texte));
$row_Rsplage_all= mysqli_fetch_assoc($Rsplage_all);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Cahier de textes - <?php echo $_SESSION['identite']?></title>
<?php
$nb_HS=0;
$madate=substr($_GET['code_date'],0,8);
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsHS = sprintf("SELECT * FROM cdt_agenda WHERE substring(code_date,9,1)=0 AND substring(code_date,1,8)=%s AND type_activ<>'%s'",$madate,'ds_prog');

$RsHS = mysqli_query($conn_cahier_de_texte, $query_RsHS) or die(mysqli_error($conn_cahier_de_texte));
$row_RsHS = mysqli_fetch_assoc($RsHS);
$totalRows_RsHS = mysqli_num_rows($RsHS);
mysqli_free_result($RsHS);
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


mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_Rsgroupe = "SELECT * FROM cdt_groupe ORDER BY ID_groupe ASC";
$Rsgroupe = mysqli_query($conn_cahier_de_texte, $query_Rsgroupe) or die(mysqli_error($conn_cahier_de_texte));
$row_Rsgroupe = mysqli_fetch_assoc($Rsgroupe);
$totalRows_Rsgroupe = mysqli_num_rows($Rsgroupe);
?>
<script language="JavaScript" type="text/javascript">

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


function ShowPlages(){
        getXhr();
        xhr.onreadystatechange = function(){
                if(xhr.readyState == 4 && xhr.status == 200){
                        leselect = xhr.responseText;
			document.getElementById('plages').innerHTML = leselect;
		}
	}
        xhr.open("POST","ajax_plages.php",true);
        xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');

	sel = document.getElementById('heure');
	ID_plage = sel.options[sel.selectedIndex].value;
	xhr.send("ID_plage="+ID_plage);
}

function go(){
        getXhr();
        xhr.onreadystatechange = function(){
                if(xhr.readyState == 4 && xhr.status == 200){
                        leselect = xhr.responseText;
			document.getElementById('matiere').innerHTML = leselect;
		}
	}
        
        xhr.open("POST","ajax_matiere.php",true);
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

</script>
<style type="text/css">
.Style70 {
	color: #FFFFFF;
	font-family: Arial, Helvetica, sans-serif;
}
</style>
<link media="screen" href="../styles/style_default.css" type="text/css" rel="stylesheet" />
<link media="screen" href="../templates/default/header_footer.css" type="text/css" rel="stylesheet" />
<link href="../styles/info_bulles.css" rel="stylesheet" type="text/css" />
</head>
<body>
<div id="page">
  <?php 
$header_description="<br /><b>Heure suppl&eacute;mentaire du ".$_GET['jour_pointe']."<b />";
require_once "../templates/default/header.php";       
?>
  <form action="heure_sup.php?hs&amp;jour_pointe=<?php if (isset($_GET['jour_pointe'])) {echo $_GET['jour_pointe'];} else {echo $jour_pointe;}?>&amp;current_day_name=<?php echo $_GET['current_day_name']?>&amp;code_date=<?php echo $_GET['code_date'] ?> " method="post" name="form1" id="form1">
    <table width="91%"  border="0" align="center" >
      <tr>
        <td nowrap="nowrap" align="right">&nbsp;</td>
        <td align="left">&nbsp;</td>
      </tr>
      <tr>
        <td nowrap="nowrap" align="right"><select name='classe_ID' id='classe' onchange='go()'>
            <option value='value'>S&eacute;lectionner une classe</option>
            <?php
$totalRows_res=0;
if ($_SESSION['droits']==2){ //enseignant avec un EDT
        
        $res = mysqli_query($conn_cahier_de_texte, sprintf("SELECT DISTINCT nom_classe,ID_classe FROM cdt_classe,cdt_emploi_du_temps WHERE cdt_classe.ID_classe=cdt_emploi_du_temps.classe_ID AND prof_ID=%u ORDER BY nom_classe ASC",$_SESSION['ID_prof']));
        $totalRows_res = mysqli_num_rows($res);
        while($row = mysqli_fetch_assoc($res)){
                echo "<option value='".$row["ID_classe"]."'";
                if ($totalRows_res==1) { echo " selected='selected'";};
                echo ">".$row["nom_classe"]."</option>";
        }
        mysqli_free_result($res);
}
else { //documentaliste
        do {
        	echo "<option value='".$row_RsClasse["ID_classe"]."'>".$row_RsClasse["nom_classe"]."</option>";
        } while($row_RsClasse = mysqli_fetch_assoc($RsClasse));
};

if ($totalRows_res==1) {
        ?>
            <script language="JavaScript" type="text/javascript">
        go();
        </script>
            <?php
        
};
//regroupements                                         
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_Rsgic =sprintf("SELECT * FROM cdt_groupe_interclasses WHERE prof_ID=%u",$_SESSION['ID_prof']);
$Rsgic = mysqli_query($conn_cahier_de_texte, $query_Rsgic) or die(mysqli_error($conn_cahier_de_texte));

while ($row_Rsgic = mysqli_fetch_assoc($Rsgic)) { 
        echo "<option value='0".$row_Rsgic['ID_gic']."'>".$row_Rsgic['nom_gic']."</option>";
};
mysqli_free_result($Rsgic);

?>
          </select></td>
        <td align="left"><div  id='matiere' style='display:inline' align="left"></div>
          <?php if ($erreur_matiere==1){echo '<br /><span style="color: #FF0000">Vous devez s&eacute;lectionner la classe puis la mati&egrave;re</span>';};?>
        </td>
      </tr>
      <tr valign="baseline">
        <td nowrap="nowrap" align="right"><?php if (isset($_SESSION['semdate'])){echo 'Semaine :';};?>        </td>
        <td><div align="left">
            <?php if (isset($_SESSION['semdate'])){echo $_SESSION['semdate'];};?>
          </div></td>
      </tr>
      <tr valign="baseline">
        <td nowrap="nowrap" align="right">Groupe : </td>
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
        <td align="right" nowrap="nowrap" >&nbsp;</td>
        <td>&nbsp;</td>
      </tr>
      <tr valign="baseline">
        <td align="right" nowrap="nowrap" >Indice de plage 
          de cours <a href="#" class="tooltip">Aide <em>Ce nombre permettra d'ordonner les cours de la journ&eacute;e<br/>
          Vous pouvez d&eacute;finir jusqu'&agrave; 12 plages horaires sur une journ&eacute;e.<br/>
          Vous pouvez modifier les horaires propos&eacute;s par d&eacute;faut<br />
          par votre administrateur.</em></a>:</td>
        <td><div align="left">
            <select name="select" id="heure"  onchange='ShowPlages()'>
              <option value="" selected="selected">S&eacute;lectionnez la plage</option>
              <?php $j=1;
do {
	echo '<option value="'.$j.'">';
	if ($j<10){echo '0';};
        echo $j.'&nbsp;&nbsp;&nbsp;&nbsp;(&nbsp;'.$row_Rsplage_all['h1'].'h'.$row_Rsplage_all['mn1'].' - '.$row_Rsplage_all['h2'].'h'.$row_Rsplage_all['mn2'].'&nbsp;)</option>';
        $j=$j+1;
}
while ($row_Rsplage_all = mysqli_fetch_assoc($Rsplage_all));
mysqli_free_result($Rsplage_all);
?>
            </select>
            <?php
if ($erreur_indice_plage ==1){echo '<br /><span style="color: #FF0000">Vous devez s&eacute;lectionner la plage</span>';};?>
          </div>
          </td>
      </tr>
      <tr valign="baseline">
        <td align="right" nowrap="nowrap" >&nbsp;</td>
        <td>&nbsp;</td>
      </tr>
      <tr valign="baseline">
        <td colspan="2"  nowrap="nowrap" ><div  id="plages"  align="center">        </td>
      </tr>
      <tr valign="baseline">
        <td nowrap="nowrap" align="right">&nbsp;</td>
        <td>&nbsp;</td>
      </tr>
      <tr valign="baseline">
        <td colspan="2" align="right" nowrap="nowrap">&nbsp;</td>
      </tr>
      <tr valign="baseline">
        <td colspan="2" align="right" nowrap="nowrap"><div align="center">
            <input type="submit" name="Submit" value="Valider " />
          </div></td>
      </tr>
    </table>
    <p align="center">
      <input name="ordre" type="hidden" value="up" />
    </p>
    <p>&nbsp;</p>
    <p><a href="ecrire.php?date=<?php echo date('Ymd');?>">Annuler</a>

    </p>      <input type="hidden" name="heure" value="<?php echo $nb_HS;?>" />
      <input type="hidden" name="MM_insert" value="form1" />
  </form>
  <?php
mysqli_free_result($RsClasse);
mysqli_free_result($RsMatiere);
mysqli_free_result($Rsgroupe);
?>
<div id=footer></div>
</div>
</body>
</html>
