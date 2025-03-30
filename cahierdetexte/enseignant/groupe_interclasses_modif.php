<?php 

include "../authentification/authcheck.php" ;
if (($_SESSION['droits']<>1)&&($_SESSION['droits']<>2)&&($_SESSION['droits']<>3)&&($_SESSION['droits']<>8)) { header("Location: ../index.php");exit;};

require_once('../Connections/conn_cahier_de_texte.php');
require_once('../inc/functions_inc.php');


$editFormAction = '#';
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}
$datetoday=date('y-m-d');

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsClasse = "SELECT * FROM cdt_classe ORDER BY nom_classe ASC";
$RsClasse = mysqli_query($conn_cahier_de_texte, $query_RsClasse) or die(mysqli_error($conn_cahier_de_texte));
$row_RsClasse = mysqli_fetch_assoc($RsClasse);
$totalRows_RsClasse = mysqli_num_rows($RsClasse);

$i=1;
do
{
$indcl_id[$i]=$row_RsClasse['ID_classe'];
$i=$i+1;
}while ($row_RsClasse = mysqli_fetch_assoc($RsClasse)) ;

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_Rsgroupe = "SELECT * FROM cdt_groupe ORDER BY ID_groupe ASC";
$Rsgroupe = mysqli_query($conn_cahier_de_texte, $query_Rsgroupe) or die(mysqli_error($conn_cahier_de_texte));
$row_Rsgroupe = mysqli_fetch_assoc($Rsgroupe);
$totalRows_Rsgroupe = mysqli_num_rows($Rsgroupe);


if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1") && (isset($_GET['ID_gic']))) {
  
  if ($_SESSION['droits']==1){$num_prof=$_POST['num_prof'];} else {$num_prof=$_SESSION['ID_prof'];};
  
  $nom_gic= str_replace(array("/", "&", "\'"), "-",$_POST['nom_gic']);
  $nom_gic= trim(str_replace('"',' ',$nom_gic));
  $nom_gic= trim(str_replace("'","-",$nom_gic));
  $updateSQL = sprintf(" UPDATE `cdt_groupe_interclasses` SET prof_ID=%u , nom_gic=%s , commentaire_gic =%s WHERE ID_gic=%u ",
  
  GetSQLValueString($num_prof, "int"),
  GetSQLValueString($nom_gic, "text"),
  GetSQLValueString($_POST['commentaire_gic'],"text"),
  GetSQLValueString($_GET['ID_gic'],"int")
  
  );
  mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
  $Result1 = mysqli_query($conn_cahier_de_texte, $updateSQL) or die(mysqli_error($conn_cahier_de_texte));

 
  //on efface
  if ((isset($_GET['ID_gic'])) && ($_GET['ID_gic'] != "")) {
    $deleteSQL = sprintf("DELETE FROM cdt_groupe_interclasses_classe WHERE gic_ID=%u",
                         GetSQLValueString($_GET['ID_gic'], "int"));
  
    mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
    $Result1 = mysqli_query($conn_cahier_de_texte, $deleteSQL) or die(mysqli_error($conn_cahier_de_texte));
  
  }


$UID=mysqli_insert_id($conn_cahier_de_texte); 
	
$nblign=$_POST['nb_classes'];
        
for ($i=1; $i<=$totalRows_RsClasse; $i++) { 

    $refclasse='classe'.$i;
    $refgroupe='groupe'.$i;
    if (isset($_POST[$refclasse])&&(isset($_POST[$refgroupe])) &&($_POST[$refclasse]=='on')){
	
    $insertSQL2= sprintf("INSERT INTO `cdt_groupe_interclasses_classe` ( `gic_ID` , `classe_ID`, `groupe_ID`)  VALUES ('%s', '%s', '%s');",$_GET['ID_gic'],$indcl_id[$i], $_POST[$refgroupe]);
   
    $Result2 = mysqli_query($conn_cahier_de_texte, $insertSQL2) or die(mysqli_error($conn_cahier_de_texte));
    }//du if
  }//du for

   if (isset($_POST['gic_eleves']) ){
    //on efface les references ele_gic
     $deleteSQL_ele_gic = sprintf("DELETE FROM `ele_gic` WHERE ID_gic=%u",
                            GetSQLValueString($_GET['ID_gic'], "int"));
     
       mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
       $Result_del_ele_gic = mysqli_query($conn_cahier_de_texte, $deleteSQL_ele_gic) or die(mysqli_error($conn_cahier_de_texte));
     
  	  foreach( $_POST['gic_eleves'] as $gic_eleve ) {
		mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
		$insertSQL_ele_gic = sprintf("INSERT INTO `ele_gic` (  `ID_ele`, `ID_gic`)  VALUES ( '%s', '%s');", GetSQLValueString($gic_eleve, "int") , GetSQLValueString($_GET['ID_gic'], "int")  );
		//print $insertSQL_ele_gic  ."<br />";
		$Result_ele_gic = mysqli_query($conn_cahier_de_texte, $insertSQL_ele_gic) or die(mysqli_error($conn_cahier_de_texte));
	  }
      
   }
 
	if (($_SESSION['droits']==1)||($_SESSION['droits']==3)){
	$insertGoTo="../inc/regroupement_liste.php";} 
	else {
	$insertGoTo = "groupe_interclasses_ajout.php";
	};
header(sprintf("Location: %s", $insertGoTo));
}

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsModifgic =sprintf("SELECT * FROM cdt_groupe_interclasses,cdt_prof WHERE ID_gic=%u AND cdt_groupe_interclasses.prof_ID = cdt_prof.ID_prof ",GetSQLValueString($_GET['ID_gic'], "int") );
$RsModifgic = mysqli_query($conn_cahier_de_texte, $query_RsModifgic) or die(mysqli_error($conn_cahier_de_texte));
$row_RsModifgic = mysqli_fetch_assoc($RsModifgic);
$totalRows_RsModifgic = mysqli_num_rows($RsModifgic);

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_Rsgic =sprintf("SELECT * FROM cdt_groupe_interclasses WHERE prof_ID=%u ",$row_RsModifgic['ID_prof']);
$Rsgic = mysqli_query($conn_cahier_de_texte, $query_Rsgic) or die(mysqli_error($conn_cahier_de_texte));
$row_Rsgic = mysqli_fetch_assoc($Rsgic);
$totalRows_Rsgic = mysqli_num_rows($Rsgic);

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_Rsdest =sprintf("SELECT * FROM cdt_groupe_interclasses_classe WHERE gic_ID=%u ",$_GET['ID_gic'] );
//print $query_Rsdest ;
$Rsdest = mysqli_query($conn_cahier_de_texte, $query_Rsdest) or die(mysqli_error($conn_cahier_de_texte));
$row_Rsdest = mysqli_fetch_assoc($Rsdest);
$totalRows_Rsdest = mysqli_num_rows($Rsdest);
//print $totalRows_Rsdest . "<br />";



// Gestion des gic avec le module d'absence
if ( (isset($_SESSION['module_absence'])) && ($_SESSION['module_absence']=='Oui')) {
  // On recupere les eleves selectionnes
  $selected_eles = array();
  mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
  $query_Rsele_select_classe = sprintf("SELECT gic.ID_ele FROM `ele_gic` as gic, `ele_liste` as ele WHERE gic.ID_ele = ele.ID_ele AND ID_gic='%s'", GetSQLValueString($_GET['ID_gic'], "int")   );
  $Rsele_select_classe = mysqli_query($conn_cahier_de_texte, $query_Rsele_select_classe) or die(mysqli_error($conn_cahier_de_texte));
  //$row_Rsele_select_classe = mysqli_fetch_assoc($Rsele_select_classe);
  //$totalRows_Rsele_select_classe = mysqli_num_rows($Rsele_select_classe);
  while (($row_rq = mysqli_fetch_array($Rsele_select_classe , MYSQLI_ASSOC) )) {    
    array_push(  $selected_eles , $row_rq['ID_ele'] );
  }
  
  $classes = array();
  mysqli_data_seek($Rsdest,0) ;
  while (($row_rq = mysqli_fetch_array($Rsdest , MYSQLI_ASSOC) )) {    
    array_push( $classes , $row_rq['classe_ID'] );
  }
 // print_r($classes );
   $in_classes = join(" ,", $classes) ;
   mysqli_data_seek($Rsdest, 0);
//  echo $in_classes ;
}


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

function confirmation(sup_nom_gic,ref)
{
  if (confirm("Voulez-vous supprimer r\351ellement ce groupe-classe "+" N\260"+ref+" nomm\351"+sup_nom_gic)) { // Clic sur OK
    MM_goToURL('window','groupe_interclasses_supprime.php?ID_gic='+ref);
       }
}



</script>
</head>
<body >
<div id="">
<!--<div id="container3"> en remplacement du div ci-dessus pour un tableau moins large -->
<p><table class="lire_bordure" width="90%" border="0" align="center" cellpadding="0" cellspacing="0">

  <tr class="lire_cellule_4">
    <td ><?php echo $_SESSION['identite']; ?> - Gestion des regroupements d'&eacute;l&egrave;ves issus de plusieurs classes <?php if ($_SESSION['droits']==1){echo ' pour l\'enseignant '.$row_RsModifgic['identite'];};?> </td>
    <td ><div align="right"><a href="<?php if (($_SESSION['droits']==2)||($_SESSION['droits']==8))  {echo 'enseignant.php';};
	if (($_SESSION['droits']==1)||($_SESSION['droits']==3)){echo '../inc/regroupement_liste.php';};?>">
	<img src="../images/home-menu.gif" alt="Accueil" width="26" height="20" border="0" /></a></div></td>
  </tr>  <tr>
    <td colspan="2" valign="top" class="lire_cellule_2" ><br /><br />
<form onLoad= "formfocus()" method="post"  name="form1" enctype="multipart/form-data" action="<?php echo $editFormAction; ?>" onsubmit="return verifier()">

             

      <table width="100%" border="0" cellspacing="5" cellpadding="0">
        <tr>
          <td valign="top"><table border="0" align="center" class="bordure">
            <tr>
              <td class="Style6"><div align="center">El&egrave;ves venant de</div></td>
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
mysqli_data_seek($RsClasse, 0);

$i=1;

while ($row_RsClasse = mysqli_fetch_assoc($RsClasse)){  ?>
            <tr>
              <td class="tab_detail"><div align="left"><?php echo $row_RsClasse['nom_classe']; ?></div></td>
              <td class="tab_detail"><div align="center">
                                  
<input type="checkbox" name="<?php echo 'classe'.$i; ?>"   id="<?php echo 'classe'.$row_RsClasse['ID_classe']; ?>"  
                                  <?php
            if ( (isset($_SESSION['module_absence'])) && ($_SESSION['module_absence']=='Oui'))  {
					echo 'onclick="majElevesListe();"';
			}
                $groupe_sel='';  
             do {
			  if ($row_RsClasse['ID_classe']==$row_Rsdest['classe_ID']){echo 'checked';$groupe_sel=$row_Rsdest['groupe_ID'];};
			     } while ($row_Rsdest = mysqli_fetch_assoc($Rsdest));
				 mysqli_data_seek($Rsdest, 0);                
  ?>>
				  
				  
              </div></td>
              <td class="tab_detail"><select name="<?php echo 'groupe'.$i; ?>" size="1" class="menu_deroulant" id="<?php echo 'groupe'.$row_RsClasse['ID_classe']; ?>">
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
            <?php 
			$i=$i+1;
			} ; ?>
          </table>          </td>
          <td valign="top">
		  <?php if($totalRows_Rsgic<>0){ ?>
		  <table width="700" border="1" align="center" cellpadding="0" cellspacing="0" class="bordure">
            <tr>
              <td class="Style6">Ref</td>
              <td class="Style6"><div align="center">Nom du groupe classe</div></td>
              <td class="Style6">Commentaire</td>
			  </tr>
            <?php 
			do { ?>
            <tr>
              <td class="tab_detail"><?php echo $row_Rsgic['ID_gic']; ?></td>
			  <td class="tab_detail"><?php echo $row_Rsgic['nom_gic']; ?></td>
              <td class="tab_detail"><?php echo $row_Rsgic['commentaire_gic']; ?></td>
              </tr>
            <?php } while ($row_Rsgic = mysqli_fetch_assoc($Rsgic)); ?>
          </table>
	<?php ;};?>
            <p>&nbsp;</p>
            <p>&nbsp;</p>
				<div align="center">
                <fieldset style="width : 90%">
                  <legend align="top"><strong>Modification d'un regroupement d'&eacute;l&egrave;ves issus de diff&eacute;rentes classes</strong></legend>
                  <table align="center" cellspacing="5">
                    <tr valign="baseline">
                      <td valign="top"><p>Libell&eacute; du regroupement <br>
                          <input name="nom_gic" type="text" size="50" value="<?php echo $row_RsModifgic['nom_gic']; ?>">
                  </p>
                        <p>Commentaire (facultatif) <br />
                          <textarea name="commentaire_gic" cols="70" rows="2" id="message" width="200" height= "80" ><?php echo $row_RsModifgic['commentaire_gic']; ?></textarea>
                  </p>
                   <?php
				if ( (isset($_SESSION['module_absence'])) && ($_SESSION['module_absence']=='Oui')) {
				  ?>
				 <p>S&eacute;lection des &eacute;l&egrave;ves <br />
					<select id="gic_eleves" name="gic_eleves[]" multiple size="10">
					
                   <?php
                    mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
                        $rq=mysqli_query($conn_cahier_de_texte, "SELECT nom_classe,code_classe FROM `cdt_classe` where `ID_classe` IN (". $in_classes  .");");
                        if  ($rq!=NULL){
                          
                          while (($row_rq = mysqli_fetch_array($rq, MYSQLI_ASSOC) )) {
                            
                            //print_r($row_rq );
                            //echo $row_rq['code_classe'] . "<br />";
                            
                             $rq_eleves=mysqli_query($conn_cahier_de_texte, "SELECT * FROM `ele_liste` where `classe_ele` = '". $row_rq['code_classe']."';");
                             //print "SELECT * FROM `ele_liste` where `classe_ele` = '". $row_rq['code_classe']."';" ;
                              if  ($rq_eleves!=NULL){
                              print  "<optgroup label='" . $row_rq['nom_classe'] . "' >";
                              while (($row_rq_eleve = mysqli_fetch_array($rq_eleves, MYSQLI_ASSOC) )) {
                                echo "<option name='".$row_rq_eleve['ID_ele']."'  value='".$row_rq_eleve['ID_ele'] ."'" ;
                                if (in_array($row_rq_eleve['ID_ele'], $selected_eles ) ) {
                                  echo " selected ";
                                }
                                echo ">".$row_rq_eleve['nom_ele'] . "&nbsp;" . $row_rq_eleve['prenom_ele']."</option>";
                              };
                              print  "</optgroup  >";
                              
                              }
                          };
                          
                        }
                    
                   ?> 
                    
					</select>  
               </p> 
				  
				 <?php
                 mysqli_free_result($rq);
                 
				}
				
				?>   
				
				
 
                        <p align="center"> <br />
                          <input name="submit" type="submit" value="Enregistrer les modifications">
                  </p>
                  <p align="center"><a href="<?php	if (($_SESSION['droits']==1)||($_SESSION['droits']==3)){echo '../inc/regroupement_liste.php';} else { echo 'groupe_interclasses_ajout.php';};?>">Annuler</a></p></td>
                  </tr>
                  </table>
                                      
            </fieldset></div>
            <p>
              <input type="hidden" name="nb_classes" value="<?php echo $totalRows_RsClasse;?>">
              <input type="hidden" name="num_prof" value="<?php echo $row_RsModifgic['prof_ID'];?>">			  
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
