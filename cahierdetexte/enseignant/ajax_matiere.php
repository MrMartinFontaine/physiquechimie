<?php	
// affichage des matieres du prof pour la classe postee
header("Content-Type:text/plain; charset=iso-8859-1");
session_start();
require_once('../Connections/conn_cahier_de_texte.php');	
if(isset($_POST["Classe"])){
        if (substr($_POST["Classe"],0,1)==0){ // c'est un regroupement
$gic_ID=substr($_POST["Classe"],1,strlen($_POST["Classe"]));}else{$gic_ID=0;};

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
if ( $gic_ID==0){
        
        if ($_SESSION['droits']==2){
        $rq=mysqli_query($conn_cahier_de_texte, "SELECT DISTINCT cdt_matiere.nom_matiere,cdt_emploi_du_temps.matiere_ID FROM cdt_matiere LEFT JOIN cdt_emploi_du_temps ON cdt_matiere.ID_matiere=cdt_emploi_du_temps.matiere_ID WHERE cdt_emploi_du_temps.classe_ID=".$_POST["Classe"]." AND prof_ID= ".$_SESSION['ID_prof']. " ORDER BY matiere_ID");
        } 
        else { //documentaliste
        $rq=mysqli_query($conn_cahier_de_texte, "SELECT nom_matiere,ID_matiere FROM cdt_matiere ORDER BY nom_matiere");
        };
}
else //regroupement
{
        
        if ($_SESSION['droits']==2){
        $rq=mysqli_query($conn_cahier_de_texte, "SELECT DISTINCT cdt_matiere.nom_matiere,cdt_emploi_du_temps.matiere_ID FROM cdt_matiere LEFT JOIN cdt_emploi_du_temps ON cdt_matiere.ID_matiere=cdt_emploi_du_temps.matiere_ID WHERE cdt_emploi_du_temps.classe_ID=0 AND  cdt_emploi_du_temps.gic_ID=".$gic_ID." AND prof_ID= ".$_SESSION['ID_prof']. " ORDER BY matiere_ID");
        } 
        else { //documentaliste
        $rq=mysqli_query($conn_cahier_de_texte, "SELECT ID_matiere,nom_matiere FROM cdt_matiere ORDER BY nom_matiere");
        };      
};      

echo "<select name='matiere_ID'>";
        echo "<option value='value2'>maintenant choisir la mati&egrave;re</option>";
        while($row = mysqli_fetch_row($rq)){
		echo "<option value='".$row[1]."'";							    
		if (((isset($_GET['matiere_ID']))&&(!(strcmp($row[1], $_GET['matiere_ID']))))||(mysqli_num_rows($rq)==1)) {echo " selected='selected'";}
                echo ">".$row[0]."</option>";
        };
        echo "</select>";

}       


?>

