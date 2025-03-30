<?php
// affichage des plages 
require_once('../Connections/conn_cahier_de_texte.php');
$modif=false;
$ind_plage=0;
if (isset($_POST['ID_plage'])){ //Modif d'une plage horaire ou modification du choix de l'ID de la plage horaire.
	$ind_plage=$_POST['ID_plage'];
	if (isset($_POST['Num_Edt'])) { //Modif d'une plage horaire
		$modif=true;
		mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
		$query_Rshoraire =sprintf("SELECT heure_debut,heure_fin,duree FROM cdt_emploi_du_temps WHERE ID_emploi=%u",$_POST['Num_Edt']);
		$Rshoraire = mysqli_query($conn_cahier_de_texte, $query_Rshoraire) or die(mysqli_error($conn_cahier_de_texte));
		$row_Rshoraire= mysqli_fetch_assoc($Rshoraire);
        }
};

if (isset($_GET['ind_plage'])){$ind_plage=$_GET['ind_plage'];}; //Saisie par clic
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_Rsplage =sprintf("SELECT * FROM cdt_plages_horaires WHERE ID_plage = %s ",$ind_plage);
$Rsplage = mysqli_query($conn_cahier_de_texte, $query_Rsplage) or die(mysqli_error($conn_cahier_de_texte));
$row_Rsplage= mysqli_fetch_assoc($Rsplage);
?> &nbsp;&nbsp;&nbsp;de
<select name="h1">
<option value="07"<?php if ($modif) {
	if (substr($row_Rshoraire['heure_debut'],0,2)=='07') {
		echo "SELECTED";
	}
                } else if ($row_Rsplage['h1']=='07') {echo "SELECTED";} ?>>07</option>
                
                <option value="08"<?php if ($modif) {
                        if (substr($row_Rshoraire['heure_debut'],0,2)=='08') {
                                echo "SELECTED";
                        }
               } else if ($row_Rsplage['h1']=='08') {echo "SELECTED";} ?>>08</option>
               <option value="09"<?php if ($modif) {
               	       if (substr($row_Rshoraire['heure_debut'],0,2)=='09') {
               	       	       echo "SELECTED";
               	       }
               } else if ($row_Rsplage['h1']=='09') {echo "SELECTED";} ?>>09</option>
               <option value="10"<?php if ($modif) {
               	       if (substr($row_Rshoraire['heure_debut'],0,2)=='10') {
               	       	       echo "SELECTED";
               	       }
               } else if ($row_Rsplage['h1']=='10') {echo "SELECTED";} ?>>10</option>
               <option value="11"<?php if ($modif) {
               	       if (substr($row_Rshoraire['heure_debut'],0,2)=='11') {
               	       	       echo "SELECTED";
               	       }
               } else if ($row_Rsplage['h1']=='11') {echo "SELECTED";} ?>>11</option>
               <option value="12"<?php if ($modif) {
               	       if (substr($row_Rshoraire['heure_debut'],0,2)=='12') {
               	       	       echo "SELECTED";
               	       }
               } else if ($row_Rsplage['h1']=='12') {echo "SELECTED";} ?>>12</option>
               <option value="13"<?php if ($modif) {
               	       if (substr($row_Rshoraire['heure_debut'],0,2)=='13') {
               	       	       echo "SELECTED";
               	       }
               } else if ($row_Rsplage['h1']=='13') {echo "SELECTED";} ?>>13</option>			
               <option value="14"<?php if ($modif) {
               	       if (substr($row_Rshoraire['heure_debut'],0,2)=='14') {
               	       	       echo "SELECTED";
               	       }
               } else if ($row_Rsplage['h1']=='14') {echo "SELECTED";} ?>>14</option>
               <option value="15"<?php if ($modif) {
               	       if (substr($row_Rshoraire['heure_debut'],0,2)=='15') {
               	       	       echo "SELECTED";
               	       }
               } else if ($row_Rsplage['h1']=='15') {echo "SELECTED";} ?>>15</option>
               <option value="16"<?php if ($modif) {
               	       if (substr($row_Rshoraire['heure_debut'],0,2)=='16') {
               	       	       echo "SELECTED";
               	       }
               } else if ($row_Rsplage['h1']=='16') {echo "SELECTED";} ?>>16</option>
               <option value="17"<?php if ($modif) {
               	       if (substr($row_Rshoraire['heure_debut'],0,2)=='17') {
               	       	       echo "SELECTED";
               	       }
               } else if ($row_Rsplage['h1']=='17') {echo "SELECTED";} ?>>17</option>
               <option value="18"<?php if ($modif) {
               	       if (substr($row_Rshoraire['heure_debut'],0,2)=='18') {
               	       	       echo "SELECTED";
               	       }
               } else if ($row_Rsplage['h1']=='18') {echo "SELECTED";} ?>>18</option>
               <option value="19"<?php if ($modif) {
               	       if (substr($row_Rshoraire['heure_debut'],0,2)=='19') {
               	       	       echo "SELECTED";
               	       }
               } else if ($row_Rsplage['h1']=='19') {echo "SELECTED";} ?>>19</option>
               <option value="20"<?php if ($modif) {
               	       if (substr($row_Rshoraire['heure_debut'],0,2)=='20') {
               	       	       echo "SELECTED";
               	       }
               } else if ($row_Rsplage['h1']=='20') {echo "SELECTED";} ?>>20</option>
               <option value="21"<?php if ($modif) {
               	       if (substr($row_Rshoraire['heure_debut'],0,2)=='21') {
               	       	       echo "SELECTED";
               	       }
               } else if ($row_Rsplage['h1']=='21') {echo "SELECTED";} ?>>21</option>	   
               </select> 
               h 
               
               <select name="mn1">
               <option value="00"<?php if ($modif) {
               	       if (substr($row_Rshoraire['heure_debut'],3,2)=='00') {
               	       	       echo "SELECTED";
               	       }
               } else if ($row_Rsplage['mn1']=='00') {echo "SELECTED";} ?>>00</option>
               <option value="05"<?php if ($modif) {
               	       if (substr($row_Rshoraire['heure_debut'],3,2)=='05') {
               	       	       echo "SELECTED";
               	       }
               } else if ($row_Rsplage['mn1']=='05') {echo "SELECTED";} ?>>05</option>
               <option value="10"<?php if ($modif) {
               	       if (substr($row_Rshoraire['heure_debut'],3,2)=='10') {
               	       	       echo "SELECTED";
               	       }
               } else if ($row_Rsplage['mn1']=='10') {echo "SELECTED";} ?>>10</option>
               <option value="15"<?php if ($modif) {
               	       if (substr($row_Rshoraire['heure_debut'],3,2)=='15') {
               	       	       echo "SELECTED";
               	       }
               } else if ($row_Rsplage['mn1']=='15') {echo "SELECTED";} ?>>15</option>
               <option value="20"<?php if ($modif) {
               	       if (substr($row_Rshoraire['heure_debut'],3,2)=='20') {
               	       	       echo "SELECTED";
               	       }
               } else if ($row_Rsplage['mn1']=='20') {echo "SELECTED";} ?>>20</option>
               <option value="25"<?php if ($modif) {
               	       if (substr($row_Rshoraire['heure_debut'],3,2)=='25') {
               	       	       echo "SELECTED";
               	       }
               } else if ($row_Rsplage['mn1']=='25') {echo "SELECTED";} ?>>25</option>
               <option value="30"<?php if ($modif) {
               	       if (substr($row_Rshoraire['heure_debut'],3,2)=='30') {
               	       	       echo "SELECTED";
               	       }
               } else if ($row_Rsplage['mn1']=='30') {echo "SELECTED";} ?>>30</option>			
               <option value="35"<?php if ($modif) {
               	       if (substr($row_Rshoraire['heure_debut'],3,2)=='35') {
               	       	       echo "SELECTED";
               	       }
               } else if ($row_Rsplage['mn1']=='35') {echo "SELECTED";} ?>>35</option>
               <option value="40"<?php if ($modif) {
               	       if (substr($row_Rshoraire['heure_debut'],3,2)=='40') {
               	       	       echo "SELECTED";
               	       }
               } else if ($row_Rsplage['mn1']=='40') {echo "SELECTED";} ?>>40</option>
               <option value="45"<?php if ($modif) {
               	       if (substr($row_Rshoraire['heure_debut'],3,2)=='45') {
               	       	       echo "SELECTED";
               	       }
               } else if ($row_Rsplage['mn1']=='45') {echo "SELECTED";} ?>>45</option>
               <option value="50"<?php if ($modif) {
               	       if (substr($row_Rshoraire['heure_debut'],3,2)=='50') {
               	       	       echo "SELECTED";
               	       }
               } else if ($row_Rsplage['mn1']=='50') {echo "SELECTED";} ?>>50</option>
               <option value="55"<?php if ($modif) {
               	       if (substr($row_Rshoraire['heure_debut'],3,2)=='55') {
               	       	       echo "SELECTED";
               	       }
               } else if ($row_Rsplage['mn1']=='55') {echo "SELECTED";} ?>>55</option>
               </select> 
               &nbsp;&nbsp;&agrave;        
               
               
               <select name="h2">
               <option value="07"<?php if ($modif) {
               	       if (substr($row_Rshoraire['heure_fin'],0,2)=='07') {
               	       	       echo "SELECTED";
               	       }
                } else if ($row_Rsplage['h2']=='07') {echo "SELECTED";} ?>>07</option>
                
                <option value="08"<?php if ($modif) {
                        if (substr($row_Rshoraire['heure_fin'],0,2)=='08') {
                                echo "SELECTED";
                        }
               } else if ($row_Rsplage['h2']=='08') {echo "SELECTED";} ?>>08</option>
               <option value="09"<?php if ($modif) {
               	       if (substr($row_Rshoraire['heure_fin'],0,2)=='09') {
               	       	       echo "SELECTED";
               	       }
               } else if ($row_Rsplage['h2']=='09') {echo "SELECTED";} ?>>09</option>
               <option value="10"<?php if ($modif) {
               	       if (substr($row_Rshoraire['heure_fin'],0,2)=='10') {
               	       	       echo "SELECTED";
               	       }
               } else if ($row_Rsplage['h2']=='10') {echo "SELECTED";} ?>>10</option>
               <option value="11"<?php if ($modif) {
               	       if (substr($row_Rshoraire['heure_fin'],0,2)=='11') {
               	       	       echo "SELECTED";
               	       }
               } else if ($row_Rsplage['h2']=='11') {echo "SELECTED";} ?>>11</option>
               <option value="12"<?php if ($modif) {
               	       if (substr($row_Rshoraire['heure_fin'],0,2)=='12') {
               	       	       echo "SELECTED";
               	       }
               } else if ($row_Rsplage['h2']=='12') {echo "SELECTED";} ?>>12</option>
               <option value="13"<?php if ($modif) {
               	       if (substr($row_Rshoraire['heure_fin'],0,2)=='13') {
               	       	       echo "SELECTED";
               	       }
               } else if ($row_Rsplage['h2']=='13') {echo "SELECTED";} ?>>13</option>			
               <option value="14"<?php if ($modif) {
               	       if (substr($row_Rshoraire['heure_fin'],0,2)=='14') {
               	       	       echo "SELECTED";
               	       }
               } else if ($row_Rsplage['h2']=='14') {echo "SELECTED";} ?>>14</option>
               <option value="15"<?php if ($modif) {
               	       if (substr($row_Rshoraire['heure_fin'],0,2)=='15') {
               	       	       echo "SELECTED";
               	       }
               } else if ($row_Rsplage['h2']=='15') {echo "SELECTED";} ?>>15</option>
               <option value="16"<?php if ($modif) {
               	       if (substr($row_Rshoraire['heure_fin'],0,2)=='16') {
               	       	       echo "SELECTED";
               	       }
               } else if ($row_Rsplage['h2']=='16') {echo "SELECTED";} ?>>16</option>
               <option value="17"<?php if ($modif) {
               	       if (substr($row_Rshoraire['heure_fin'],0,2)=='17') {
               	       	       echo "SELECTED";
               	       }
               } else if ($row_Rsplage['h2']=='17') {echo "SELECTED";} ?>>17</option>
               <option value="18"<?php if ($modif) {
               	       if (substr($row_Rshoraire['heure_fin'],0,2)=='18') {
               	       	       echo "SELECTED";
               	       }
               } else if ($row_Rsplage['h2']=='18') {echo "SELECTED";} ?>>18</option>
               <option value="19"<?php if ($modif) {
               	       if (substr($row_Rshoraire['heure_fin'],0,2)=='19') {
               	       	       echo "SELECTED";
               	       }
               } else if ($row_Rsplage['h2']=='19') {echo "SELECTED";} ?>>19</option>
               <option value="20"<?php if ($modif) {
               	       if (substr($row_Rshoraire['heure_fin'],0,2)=='20') {
               	       	       echo "SELECTED";
               	       }
               } else if ($row_Rsplage['h2']=='20') {echo "SELECTED";} ?>>20</option>
               <option value="21"<?php if ($modif) {
               	       if (substr($row_Rshoraire['heure_fin'],0,2)=='21') {
               	       	       echo "SELECTED";
               	       }
               } else if ($row_Rsplage['h2']=='21') {echo "SELECTED";} ?>>21</option>	  
               </select> 
               h
               <select name="mn2">
               <option value="00"<?php if ($modif) {
               	       if (substr($row_Rshoraire['heure_fin'],3,2)=='00') {
               	       	       echo "SELECTED";
               	       }
               } else if ($row_Rsplage['mn2']=='00') {echo "SELECTED";} ?>>00</option>
               <option value="05"<?php if ($modif) {
               	       if (substr($row_Rshoraire['heure_fin'],3,2)=='05') {
               	       	       echo "SELECTED";
               	       }
               } else if ($row_Rsplage['mn2']=='05') {echo "SELECTED";} ?>>05</option>
               <option value="10"<?php if ($modif) {
               	       if (substr($row_Rshoraire['heure_fin'],3,2)=='10') {
               	       	       echo "SELECTED";
               	       }
               } else if ($row_Rsplage['mn2']=='10') {echo "SELECTED";} ?>>10</option>
               <option value="15"<?php if ($modif) {
               	       if (substr($row_Rshoraire['heure_fin'],3,2)=='15') {
               	       	       echo "SELECTED";
               	       }
               } else if ($row_Rsplage['mn2']=='15') {echo "SELECTED";} ?>>15</option>
               <option value="20"<?php if ($modif) {
               	       if (substr($row_Rshoraire['heure_fin'],3,2)=='20') {
               	       	       echo "SELECTED";
               	       }
               } else if ($row_Rsplage['mn2']=='20') {echo "SELECTED";} ?>>20</option>
               <option value="25"<?php if ($modif) {
               	       if (substr($row_Rshoraire['heure_fin'],3,2)=='25') {
               	       	       echo "SELECTED";
               	       }
               } else if ($row_Rsplage['mn2']=='25') {echo "SELECTED";} ?>>25</option>
               <option value="30"<?php if ($modif) {
               	       if (substr($row_Rshoraire['heure_fin'],3,2)=='30') {
               	       	       echo "SELECTED";
               	       }
               } else if ($row_Rsplage['mn2']=='30') {echo "SELECTED";} ?>>30</option>			
               <option value="35"<?php if ($modif) {
               	       if (substr($row_Rshoraire['heure_fin'],3,2)=='35') {
               	       	       echo "SELECTED";
               	       }
               } else if ($row_Rsplage['mn2']=='35') {echo "SELECTED";} ?>>35</option>
               <option value="40"<?php if ($modif) {
               	       if (substr($row_Rshoraire['heure_fin'],3,2)=='40') {
               	       	       echo "SELECTED";
               	       }
               } else if ($row_Rsplage['mn2']=='40') {echo "SELECTED";} ?>>40</option>
               <option value="45"<?php if ($modif) {
               	       if (substr($row_Rshoraire['heure_fin'],3,2)=='45') {
               	       	       echo "SELECTED";
               	       }
               } else if ($row_Rsplage['mn2']=='45') {echo "SELECTED";} ?>>45</option>
               <option value="50"<?php if ($modif) {
               	       if (substr($row_Rshoraire['heure_fin'],3,2)=='50') {
               	       	       echo "SELECTED";
               	       }
               } else if ($row_Rsplage['mn2']=='50') {echo "SELECTED";} ?>>50</option>
               <option value="55"<?php if ($modif) {
               	       if (substr($row_Rshoraire['heure_fin'],3,2)=='55') {
               	       	       echo "SELECTED";
               	       }
               } else if ($row_Rsplage['mn2']=='55') {echo "SELECTED";} ?>>55</option>
               </select>
               &nbsp;&nbsp;dur&eacute;e (facultatif)&nbsp;
               <input type="text" name="duree" value="" size="7">&nbsp;
               

