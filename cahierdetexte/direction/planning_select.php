<?php 
include "../authentification/authcheck.php" ;
if ($_SESSION['droits']<>4) { header("Location: ../index.php");exit;};
require_once('../Connections/conn_cahier_de_texte.php');

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Cahier de textes - <?php echo $_SESSION['identite']?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<LINK media=screen href="../styles/style_default.css" type=text/css rel=stylesheet>
<LINK media=screen href="../templates/default/header_footer.css" type=text/css rel=stylesheet>
</HEAD>
<BODY>
<DIV id=page>
  <?php 
$header_description="<br /><b>Affichage du planning mensuel relatif au travail donn&eacute;</b>";
require_once "../templates/default/header.php";

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_res = "SELECT DISTINCT nom_classe,ID_classe FROM cdt_classe,cdt_emploi_du_temps WHERE cdt_classe.ID_classe=cdt_emploi_du_temps.classe_ID  ORDER BY nom_classe ASC";
$res = mysqli_query($conn_cahier_de_texte, $query_res) or die(mysqli_error($conn_cahier_de_texte));

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_res2 ="SELECT DISTINCT nom_classe,ID_classe FROM cdt_classe,cdt_emploi_du_temps,cdt_groupe_interclasses,cdt_groupe_interclasses_classe WHERE 
cdt_emploi_du_temps.classe_ID=0
AND
cdt_emploi_du_temps.gic_ID=cdt_groupe_interclasses.ID_gic
AND
cdt_groupe_interclasses.ID_gic=cdt_groupe_interclasses_classe.gic_ID
AND
cdt_groupe_interclasses_classe.classe_ID=cdt_classe.ID_classe  ORDER BY nom_classe ASC";
$res2 = mysqli_query($conn_cahier_de_texte, $query_res2) or die(mysqli_error($conn_cahier_de_texte));
$totalRows_res2=mysqli_num_rows($res2);

?>
  <p>&nbsp;</p>
  <form action="../planning.php" method="get">
    <table width="70%" border="0" align="center" cellpadding="0" cellspacing="0">
      <tr>
        <td ><div align="center">	
	
          <select name='classe_ID' id='classe' >
            <option style='font-weight: bold;' value='value'>S&eacute;lectionner une classe</option>
            <?php	while($row = mysqli_fetch_assoc($res)){
							echo "<option style='text-indent: 30px;' value='".$row["ID_classe"]."'>".$row["nom_classe"]."</option>";
						};
				
					?>
					
			            <?php	
						if ($totalRows_res2>0){
						echo "<option style='font-weight: bold;' value='0'>Classes int&eacute;grant un regroupement</option>";
						while($row = mysqli_fetch_assoc($res2)){
						echo "<option style='text-indent: 30px;' value='".$row["ID_classe"]."'>".$row["nom_classe"]."</option>";
						};
				        };
					?>		
          </select>
          <input type="submit" name="Submit" value="Valider" />
        </div></td>
      </tr>
    </table>
  </form>
  <p align="center">&nbsp;</p>
  <p><a href="direction.php">Retour au Menu Responsable Etablissement</a></p>
  <DIV id=footer>
    <p class="auteur"><a href="contribution.php" class="auteur">Pierre Lemaitre 
      - St L&ocirc; (France)  <br />
    </a></p>
  </DIV>
</DIV>
</body>
</html>


