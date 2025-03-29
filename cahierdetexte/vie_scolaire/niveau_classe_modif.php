<?php 

include "../authentification/authcheck.php" ;
if (($_SESSION['droits']<>1)&&($_SESSION['droits']<>3)&&($_SESSION['droits']<>4)) { header("Location: ../index.php");exit;};

require_once('../Connections/conn_cahier_de_texte.php');
require_once('../inc/functions_inc.php');



$editFormAction = '#';
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}
$datetoday=date('y-m-d');

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1") && (isset($_GET['ID_niv']))) {


  $updateSQL = sprintf(" UPDATE `cdt_niveau` SET  nom_niv=%s , commentaire_niv =%s WHERE ID_niv=%u ",
  GetSQLValueString($_POST['nom_niv'], "text"),
  GetSQLValueString($_POST['commentaire_niv'],"text"),
  GetSQLValueString($_GET['ID_niv'],"text")
  );
  mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
  $Result1 = mysqli_query($conn_cahier_de_texte, $updateSQL) or die(mysqli_error($conn_cahier_de_texte));

 
  //on efface
  if ((isset($_GET['ID_niv'])) && ($_GET['ID_niv'] != "")) {
    $deleteSQL = sprintf("DELETE FROM cdt_niveau_classe WHERE niv_ID=%u",
                         GetSQLValueString($_GET['ID_niv'], "int"));
  
    mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
    $Result1 = mysqli_query($conn_cahier_de_texte, $deleteSQL) or die(mysqli_error($conn_cahier_de_texte));
  
  }



  $nblign=$_POST['nb_classes'];
    
  //for ($i=0; $i<=$nblign; $i++) { 
  for ($i=0; $i<=255; $i++) { 
    $refclasse='classe'.$i;
    $refgroupe='groupe'.$i;
    if (isset($_POST[$refclasse])&&(isset($_POST[$refgroupe])) &&($_POST[$refclasse]=='on')){
    $insertSQL2= sprintf("INSERT INTO `cdt_niveau_classe` ( `niv_ID` , `classe_ID`, `groupe_ID`)  VALUES ('%u', '%u', '%u');",$_GET['ID_niv'],$i, $_POST[$refgroupe]);
    
    $Result2 = mysqli_query($conn_cahier_de_texte, $insertSQL2) or die(mysqli_error($conn_cahier_de_texte));
    }//du if
  }//du for


 
 

$insertGoTo = "niveau_classe_ajout.php";
header(sprintf("Location: %s", $insertGoTo));
}

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsModifniv =sprintf("SELECT * FROM cdt_niveau WHERE ID_niv=%u",GetSQLValueString($_GET['ID_niv'], "int") );
$RsModifniv = mysqli_query($conn_cahier_de_texte, $query_RsModifniv) or die(mysqli_error($conn_cahier_de_texte));
$row_RsModifniv = mysqli_fetch_assoc($RsModifniv);
$totalRows_RsModifniv = mysqli_num_rows($RsModifniv);

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_Rsniv ="SELECT * FROM cdt_niveau";
$Rsniv = mysqli_query($conn_cahier_de_texte, $query_Rsniv) or die(mysqli_error($conn_cahier_de_texte));
$row_Rsniv = mysqli_fetch_assoc($Rsniv);
$totalRows_Rsniv = mysqli_num_rows($Rsniv);

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_Rsdest =sprintf("SELECT * FROM cdt_niveau_classe WHERE niv_ID=%u",$_GET['ID_niv'] );
$Rsdest = mysqli_query($conn_cahier_de_texte, $query_Rsdest) or die(mysqli_error($conn_cahier_de_texte));
$row_Rsdest = mysqli_fetch_assoc($Rsdest);
$totalRows_Rsdest = mysqli_num_rows($Rsdest);


mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsClasse = "SELECT * FROM cdt_classe ORDER BY nom_classe ASC";
$RsClasse = mysqli_query($conn_cahier_de_texte, $query_RsClasse) or die(mysqli_error($conn_cahier_de_texte));
$row_RsClasse = mysqli_fetch_assoc($RsClasse);
$totalRows_RsClasse = mysqli_num_rows($RsClasse);


mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_Rsgroupe = "SELECT * FROM cdt_groupe ORDER BY ID_groupe ASC";
$Rsgroupe = mysqli_query($conn_cahier_de_texte, $query_Rsgroupe) or die(mysqli_error($conn_cahier_de_texte));
$row_Rsgroupe = mysqli_fetch_assoc($Rsgroupe);
$totalRows_Rsgroupe = mysqli_num_rows($Rsgroupe);


?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Cahier de textes - <?php echo $_SESSION['identite']?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="../styles/style_default.css" rel="stylesheet" type="text/css">
<style>
form{
   margin:5;
   padding:0;
}
.bordure_grise {
	border: 1px solid #CCCCCC;
}
.Style70 {font-size: 16px}
</style>
<script type="text/javascript" src="../jscripts/jquery-1.6.2.js"> </script>
<script type="text/javascript" src="../jscripts/utils.js"></script>
<script type="text/JavaScript">
function verifier() {
  var cases = document.getElementsByTagName('input');   // on recupere tous les INPUT
   for (var i=0; i<cases.length; i++)  {     // on les parcourt
       if (cases[i].type == 'checkbox')    // si on a une checkbox...
	     { //alert(cases[i].checked);
		 if (cases[i].checked==true) {  	//si la case est cochee, envoi du formulaire		
		        if(cases[i].name != 'online') {return true}
				}; 
		 }
		 
   };
   alert("Il faut indiquer un destinataire. Cocher au moins une classe");
   return false;
   }

function MM_goToURL() { //v3.0
  var i, args=MM_goToURL.arguments; document.MM_returnValue = false;
  for (i=0; i<(args.length-1); i+=2) eval(args[i]+".location='"+args[i+1]+"'");
}

function confirmation(sup_nom_niv,ref)
{
  if (confirm("Voulez-vous supprimer r\351ellement ce groupe-classe "+" N\260"+ref+" nomm\351"+sup_nom_niv)) { // Clic sur OK
    MM_goToURL('window','groupe_interclasses_supprime.php?ID_niv='+ref);
       }
}



</script>
</head>
<body >
<div id="">
<!--<div id="container3"> en remplacement du div ci-dessus pour un tableau moins large -->
<p><table class="lire_bordure" width="90%" border="0" align="center" cellpadding="0" cellspacing="0">

  <tr class="lire_cellule_4">
    <td >Gestion des niveaux de classes </td>
    <td ><div align="right"><a href="<?php if (($_SESSION['droits']==3)||($_SESSION['droits']==4)) {echo 'niveau_classe_ajout.php';};?>"><img src="../images/home-menu.gif" alt="Accueil" width="26" height="20" border="0" /></a></div></td>
  </tr>  <tr>
    <td colspan="2" valign="top" class="lire_cellule_2" ><br /><br />
<form onLoad= "formfocus()" method="post"  name="form1" enctype="multipart/form-data" action="<?php echo $editFormAction; ?>" onsubmit="return verifier()">

             

      <table width="100%" border="0" cellspacing="5" cellpadding="0">
        <tr>
          <td valign="top"><table border="0" align="center" class="bordure">
            <tr>
              <td class="Style6"><div align="center">Classes</div></td>
              <td class="Style6">
<SCRIPT>
function cocherTout(etat)
{
   var cases = document.getElementsByTagName('input');   // on recupere tous les INPUT
   for(var i=0; i<cases.length; i++)     // on les parcourt
      if(cases[i].type == 'checkbox')     // si on a une checkbox...
         cases[i].checked = etat;     // ... on la coche ou non
}

function decocherTout()
{
   var cases = document.getElementsByTagName('input');   // on recupere tous les INPUT
   cases[0].checked = false;     // ... on decoche la premiere, le TOUS
}
</SCRIPT>              
Tout<input type="checkbox" name="checkbox" id="tousaucun" onclick=cocherTout(this.checked) value="ok" ></td>

              <td class="Style6">Groupes</td>
            </tr>
            <?php 
			do { ?>
            <tr>
              <td class="tab_detail"><div align="left"><?php echo $row_RsClasse['nom_classe']; ?></div></td>
              <td class="tab_detail"><div align="center">
                                  
<input type="checkbox" name="<?php echo 'classe'.$row_RsClasse['ID_classe']; ?>"   id="<?php echo 'classe'.$row_RsClasse['ID_classe']; ?>" 
                                  <?php
                  
                    if ( (isset($_SESSION['module_absence'])) && ($_SESSION['module_absence']=='Oui'))  {
					echo 'onclick="majElevesListe();"';
				  }
                  
                  do {
                  //  print $row_RsClasse['ID_classe'] . " <==> " . $row_Rsdest['classe_ID'] . "<br />";
                    
			  if ($row_RsClasse['ID_classe']==$row_Rsdest['classe_ID']){echo 'checked';$groupe_sel=$row_Rsdest['groupe_ID'];};
			     } while ($row_Rsdest = mysqli_fetch_assoc($Rsdest));
			
				 mysqli_data_seek($Rsdest, 0);                
                 
				
                  
                  ?> 
                 
                 
				  >
				  
				  
				  
              </div></td>
              <td class="tab_detail"><select name="<?php echo 'groupe'.$row_RsClasse['ID_classe']; ?>" size="1" class="menu_deroulant" id="<?php echo 'groupe'.$row_RsClasse['ID_classe']; ?>">
                  <?php do {  ?>
                  <option value="<?php echo $row_Rsgroupe['ID_groupe']?>"
				  
				  
				  <?php if ((isset($groupe_sel))&&($groupe_sel==$row_Rsgroupe['ID_groupe'] )) {echo ' selected';};?>
				  
				  
				  ><?php echo $row_Rsgroupe['groupe']?></option>
                  <?php
                } while ($row_Rsgroupe = mysqli_fetch_assoc($Rsgroupe));
            $rows = mysqli_num_rows($Rsgroupe);
            if($rows > 0) {
            mysqli_data_seek($Rsgroupe, 0);
	        $row_Rsgroupe = mysqli_fetch_assoc($Rsgroupe);
            }?>
              </select></td>
            </tr>
            <?php } while ($row_RsClasse = mysqli_fetch_assoc($RsClasse)); ?>
          </table>          </td>
          <td valign="top">
		  <?php if($totalRows_Rsniv<>0){ ?>
		  <table width="700" border="1" align="center" cellpadding="0" cellspacing="0" class="bordure">
            <tr>
              <td class="Style6">Ref</td>
              <td class="Style6"><div align="center">Nom du groupe classe</div></td>
              <td class="Style6">Commentaire</td>
			  </tr>
            <?php 
			do { ?>
            <tr>
              <td class="tab_detail"><?php echo $row_Rsniv['ID_niv']; ?></td>
			  <td class="tab_detail"><?php echo $row_Rsniv['nom_niv']; ?></td>
              <td class="tab_detail"><?php echo $row_Rsniv['commentaire_niv']; ?></td>
              </tr>
            <?php } while ($row_Rsniv = mysqli_fetch_assoc($Rsniv)); ?>
          </table>
	<?php ;};?>
            <p>&nbsp;</p>
            <p>&nbsp;</p>
				<div align="center">
                <fieldset style="width : 90%">
                  <legend align="top"><strong>Modification d'un niveau d'&eacute;l&egrave;ves issus de diff&eacute;rentes classes</strong></legend>
                  <table align="center" cellspacing="5">
                    <tr valign="baseline">
                      <td valign="top"><p>Libell&eacute; du niveau <br>
                          <input name="nom_niv" type="text" size="50" value="<?php echo $row_RsModifniv['nom_niv']; ?>">
                  </p>
                        <p>Commentaire (facultatif) <br />
                          <textarea name="commentaire_niv" cols="70" rows="2" id="message" width="200" height= "80" ><?php echo $row_RsModifniv['commentaire_niv']; ?></textarea>
                  </p>
  <p align="center"> <br />
                          <input name="submit" type="submit" value="Enregistrer les modifications">
                  </p>
                  <p align="center"><a href="niveau_classe_ajout.php">Annuler</a></p></td>
                  </tr>
                  </table>
                                      
            </fieldset></div>
            <p>
              <input type="hidden" name="nb_classes" value="<?php echo $totalRows_RsClasse;?>">
              <input type="hidden" name="MM_update" value="form1">
                      </p>
          </form></td>
    </tr>

</table>
</p>
</div>




</DIV>
</body>
</html>
<?php

mysqli_free_result($RsClasse);
mysqli_free_result($Rsgroupe);
mysqli_free_result($Rsdest);
?>

