<?php
header("Content-Type:text/plain; charset=iso-8859-1");
session_start();
require_once('../Connections/conn_cahier_de_texte.php');

$totalrow_rq=0;
if(isset($_SESSION["ID_prof"])){
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$rq=mysqli_query($conn_cahier_de_texte, "SELECT ID_progression,titre_progression FROM cdt_progression WHERE prof_ID =".$_SESSION["ID_prof"]." ORDER BY titre_progression");
if  ($rq!=NULL){$totalrow_rq = mysqli_num_rows($rq);};

if  ($totalrow_rq>0){

echo "<FORM  NAME='Choix' style='display:inline'><select style='color: #000066;font-family:Arial, Helvetica, sans-serif;font-size:11px' name='Liste' onClick='Lien()'>";
echo "<option  value='value' >S&eacute;lectionner votre fiche du carnet de bord&nbsp;</option>";
while (($row_rq = mysqli_fetch_array($rq, MYSQLI_ASSOC) )) {
echo "<option  style='color: #000066;font-weight: bold;font-size:11px' value='progression_affiche.php?ID_progression=".$row_rq['ID_progression']."'>".$row_rq['titre_progression']."</option>";

};
echo "</select></FORM>";}
 else {echo "Aucune fiche disponible ";};};
?>
  
  
