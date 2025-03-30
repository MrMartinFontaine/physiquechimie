<?php
header("Content-Type:text/plain; charset=iso-8859-1");
session_start();
require_once('../Connections/conn_cahier_de_texte.php');


if( isset($_POST["classes"]) && is_array($_POST["classes"]) > 0   ){
  
  $classes = str_replace("classe" , "", $_POST["classes"] ) ;
  $in_classes = "";
  $in_classes = join(" ,", $classes) ;
  
  mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
  $rq=mysqli_query($conn_cahier_de_texte, "SELECT nom_classe,code_classe FROM `cdt_classe` where `ID_classe` IN (". $in_classes  .");");
  if  ($rq!=NULL){
	
	while (($row_rq = mysqli_fetch_array($rq, MYSQLI_ASSOC) )) {
	  
	  //print_r($row_rq );
	  //echo $row_rq['code_classe'] . "<br />";
	  
	   $rq_eleves=mysqli_query($conn_cahier_de_texte, "SELECT * FROM `ele_liste` where `classe_ele` = '". $row_rq['code_classe']."' ORDER BY nom_ele,prenom_ele;");
	   //print "SELECT * FROM `ele_liste` where `classe_ele` = '". $row_rq['code_classe']."';" ;
	    if  ($rq_eleves!=NULL){
		print  "<optgroup label='" . $row_rq['nom_classe'] . "' >";
		while (($row_rq_eleve = mysqli_fetch_array($rq_eleves, MYSQLI_ASSOC) )) {
		  echo "<option name='".$row_rq_eleve['ID_ele']."'  value='".$row_rq_eleve['ID_ele']."'>".$row_rq_eleve['nom_ele'] . "&nbsp;" . $row_rq_eleve['prenom_ele']."</option>";
		};
		print  "</optgroup  >";
		
		}
	};
	
  }

  
  

 } else {
	print "<option disabled > <- S&eacute;lectionner des classes</option>";
	print "<option disabled ></option>";
	print "<option disabled ></option>";
	print "<option disabled > <- S&eacute;lectionner des classes</option>";
	print "<option disabled ></option>";
	print "<option disabled ></option>";
	print "<option disabled > <- S&eacute;lectionner des classes</option>";
  
 };
?>

