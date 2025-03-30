<?php 
include "../authentification/authcheck.php"; 
if (($_SESSION['droits']<>2)&&($_SESSION['droits']<>8)) { header("Location: ../index.php");exit;};
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
  <p>
    <?php 
$header_description="<br /><b>Diffuser un message aux enseignants en tant que professeur principal</b>";
require_once "../templates/default/header.php";

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_Rspp =sprintf("SELECT * FROM cdt_prof_principal,cdt_classe,cdt_groupe WHERE pp_prof_ID=%u AND pp_classe_ID=ID_classe AND pp_groupe_ID=ID_groupe",$_SESSION['ID_prof']);
$Rspp = mysqli_query($conn_cahier_de_texte, $query_Rspp) or die(mysqli_error($conn_cahier_de_texte));
$row_Rspp = mysqli_fetch_assoc($Rspp);
$totalRows_Rspp = mysqli_num_rows($Rspp);

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_Rsgroupe = "SELECT * FROM cdt_groupe ORDER BY ID_groupe ASC";
$Rsgroupe = mysqli_query($conn_cahier_de_texte, $query_Rsgroupe) or die(mysqli_error($conn_cahier_de_texte));
$row_Rsgroupe = mysqli_fetch_assoc($Rsgroupe);
$totalRows_Rsgroupe = mysqli_num_rows($Rsgroupe);?>

  </p>
  <br />
  <p>Votre message sera diffus&eacute; &agrave; vos coll&egrave;gues enseignants intervenant dans la  classe dont vous &ecirc;tes professeur principal. </p>
  <p><br />
  </p>
  <p>Diffuser un message <strong>aux enseignants</strong> de </p>
  <form action="message_ajout.php" method="GET">
    <table width="70%" border="0" align="center" cellpadding="0" cellspacing="0">
      <tr>
        <td ><div align="center">
          <select name='classe_ID' id='classe_ID' >
            <option value='value'>S&eacute;lectionner la classe</option>
            <?php	do {
							echo "<option value='".$row_Rspp["ID_classe"]."'>".$row_Rspp["nom_classe"]."</option>";
						}while($row_Rspp = mysqli_fetch_assoc($Rspp));	?>

						
          </select>
<select name="groupe_ID" size="1" id="groupe_ID">
            <?php do {  ?>
            <option value="<?php echo $row_Rsgroupe['ID_groupe']?>"><?php echo $row_Rsgroupe['groupe']?></option>
            <?php
                } while ($row_Rsgroupe = mysqli_fetch_assoc($Rsgroupe));
            $rows = mysqli_num_rows($Rsgroupe);
            if($rows > 0) {
            mysqli_data_seek($Rsgroupe, 0);
	        $row_Rsgroupe = mysqli_fetch_assoc($Rsgroupe);
            };
			?>
          </select>
		  <input name="dest_profs" type="hidden" value="">
          <input type="submit" name="Submit" value="Valider" />
        </div></td>
      </tr>
    </table>
  </form>
  <p align="center">&nbsp;</p>
  <p><a href="enseignant.php">Retour au Menu Enseignant </a></p>
  <DIV id=footer></DIV>
</DIV>
</body>
</html>
