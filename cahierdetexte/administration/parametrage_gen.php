<?php include "../authentification/authcheck.php"; ?>
<?php if ($_SESSION['droits']<>1) { header("Location: ../index.php");exit;};?>
<?php require_once('../Connections/conn_cahier_de_texte.php'); ?>
<?php

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_url_logo_db = "SELECT param_val FROM cdt_params WHERE param_nom='url_logo_etab'";
$url_logo_db = mysqli_query($conn_cahier_de_texte, $query_url_logo_db) or die(mysqli_error($conn_cahier_de_texte));
$row_url_logo_db = mysqli_fetch_assoc($url_logo_db);

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_url_etab_db = "SELECT param_val FROM cdt_params WHERE param_nom='url_etab'";
$url_etab_db = mysqli_query($conn_cahier_de_texte, $query_url_etab_db) or die(mysqli_error($conn_cahier_de_texte));
$row_url_etab_db = mysqli_fetch_assoc($url_etab_db);

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_nom_etab_db = "SELECT param_val FROM cdt_params WHERE param_nom='nom_etab'";
$nom_etab_db = mysqli_query($conn_cahier_de_texte, $query_nom_etab_db) or die(mysqli_error($conn_cahier_de_texte));
$row_nom_etab_db = mysqli_fetch_assoc($nom_etab_db);

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_session_timeout_db = "SELECT param_val FROM cdt_params WHERE param_nom='session_timeout'";
$session_timeout_db = mysqli_query($conn_cahier_de_texte, $query_session_timeout_db) or die(mysqli_error($conn_cahier_de_texte));
$row_session_timeout_db = mysqli_fetch_assoc($session_timeout_db);

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_time_zone_db = "SELECT param_val FROM cdt_params WHERE param_nom='time_zone'";
$time_zone_db = mysqli_query($conn_cahier_de_texte, $query_time_zone_db) or die(mysqli_error($conn_cahier_de_texte));
$row_time_zone_db = mysqli_fetch_assoc($time_zone_db);

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_url_deconnecte_eleve_db = "SELECT param_val FROM cdt_params WHERE param_nom='url_deconnecte_eleve'";
$url_deconnecte_eleve_db = mysqli_query($conn_cahier_de_texte, $query_url_deconnecte_eleve_db) or die(mysqli_error($conn_cahier_de_texte));
$row_url_deconnecte_eleve_db = mysqli_fetch_assoc($url_deconnecte_eleve_db);

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_url_deconnecte_prof_db = "SELECT param_val FROM cdt_params WHERE param_nom='url_deconnecte_prof'";
$url_deconnecte_prof_db = mysqli_query($conn_cahier_de_texte, $query_url_deconnecte_prof_db) or die(mysqli_error($conn_cahier_de_texte));
$row_url_deconnecte_prof_db = mysqli_fetch_assoc($url_deconnecte_prof_db);

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_URL_Piwik_db = "SELECT param_val FROM cdt_params WHERE param_nom='URL_Piwik'";
$URL_Piwik_db = mysqli_query($conn_cahier_de_texte, $query_URL_Piwik_db) or die(mysqli_error($conn_cahier_de_texte));
$row_URL_Piwik_db = mysqli_fetch_assoc($URL_Piwik_db);

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_ID_Piwik_db = "SELECT param_val FROM cdt_params WHERE param_nom='ID_Piwik'";
$ID_Piwik_db = mysqli_query($conn_cahier_de_texte, $query_ID_Piwik_db) or die(mysqli_error($conn_cahier_de_texte));
$row_ID_Piwik_db = mysqli_fetch_assoc($ID_Piwik_db);






?>
<html>
<head>
<title>Param&egrave;tres g&eacute;n&eacute;raux de l'installation</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<LINK media=screen href="../styles/style_default.css" type=text/css rel=stylesheet>
<LINK media=screen href="../templates/default/header_footer.css" type=text/css rel=stylesheet>
<style type="text/css">
<!--
.Style70 {font-size: small;color: #000066;}
-->
</style>
</HEAD>
<BODY>
<DIV id=page>
  <?php 
$header_description="Param&egrave;tres g&eacute;n&eacute;raux";
require_once "../templates/default/header.php";
?>
  <HR>
  <form name="parametres" method="post" action="parametrage_gen2.php">
    <br>
    <table width="95%" align="center" class="lire_cellule_22">
      <tr>
        <td width="50%" class="lire_cellule_22" ><p><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><b>Serveur 
            
            MySQL:</b><br>
            La valeur localhost conviendra dans le cas d'un Intranet.</font> <font size="2" face="Verdana, Arial, Helvetica, sans-serif"><br>
            </font><font face="Verdana, Arial, Helvetica, sans-serif"><span class="Style44"><em>Chez Free par exemple, ce sera sql.free.fr</em></span></font><br>
          </p>
          <br>        </td>
        <td width="45%" class="lire_cellule_22" ><input type="texte" name="serveur" id="serveur" size="25" maxlength="80" value="<?php echo $hostname_conn_cahier_de_texte;?>"></td>
      </tr>
      <tr >
        <td width="50%" class="lire_cellule_22" ><p><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><b>Nom d'utilisateur MySQL :</b></font></p>
          <p><font size="2" face="Verdana, Arial, Helvetica, sans-serif">La valeur 
            
            root conviendra dans le cas d'un Intranet.<br>
            </font><font face="Verdana, Arial, Helvetica, sans-serif"><span class="Style44 Style44"><em>Chez Free par exemple, ce sera votre login Free</em></span></font><span class="Style44 Style44"><em>.</em> </span></p>
        <br></td>
        <td width="45%" class="lire_cellule_22" ><input type="text" name="login" id="login" size="25" maxlength="80" value="<?php echo $username_conn_cahier_de_texte;?>"></td>
      </tr>
      <tr >
        <td width="50%" class="lire_cellule_22" ><p><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><b>Mot 
            
            de passe MySQL :</b></font></p>
          <p><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Pas de mot de passe par d&eacute;faut dans le cas d'un intranet <br>
            </font><font face="Verdana, Arial, Helvetica, sans-serif">  <span class="Style44 Style44"><em>Chez Free, ce sera  votre mot de passe Free</em></span></font><span class="Style44 Style44"><em>. </em></span></p>
          <br></td>
        <td width="45%" class="lire_cellule_22" ><input type="password" name="password" id="password" size="25" maxlength="80" value="<?php echo $password_conn_cahier_de_texte;?>"></td>
      </tr>
      <tr >
        <td width="50%" class="lire_cellule_22" ><p><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><b>Nom de la base de donn&eacute;es :</b></font></p>
          <p><font size="2" face="Verdana, Arial, Helvetica, sans-serif">En intranet, soit par exemple cahier_de_textes. </font><br>
            <em>Chez Free, la base portera le nom de votre login Free</font></em></p>
          <br></td>
        <td width="45%" class="lire_cellule_22" ><input name="base" type="text" id="base"  size="25" maxlength="80" value="<?php echo $database_conn_cahier_de_texte;?>"></td>
      </tr>
      <tr >
        <td width="50%" class="lire_cellule_22"><p><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><b>Nom 
            de votre &eacute;tablissement :</b></font></p>
          <p><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Facultatif, ce nom est report&eacute; sur la page d'accueil.</font></p>
          <br></td>
        <td width="45%" class="lire_cellule_22" ><input name="nom_etab" type="text" id="nom_etab"  size="50" maxlength="80" value="<?php echo stripslashes($row_nom_etab_db['param_val']);?>"></td>
        <?php mysqli_free_result($nom_etab_db);?>
      </tr>
      <tr>
        <td width="50%" class="lire_cellule_22"><p><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><b>URL 
            de votre site &eacute;tablissement :</b></font></p>
          <p><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Facultatif, cette adresse permet de cr&eacute;er des liens de retour vers votre site &eacute;tablissement.</font></p>
          <br></td>
        <td width="45%" class="lire_cellule_22"><p>
            <input name="url_etab" type="text" id="url_etab" value="<?php echo $row_url_etab_db['param_val'];?>"  size="50" maxlength="80">
            <?php mysqli_free_result($url_etab_db);?>
            <br>
            <em>ex : http://www.monsite.com</em></p></td>
      </tr>
      <tr>
        <td width="50%" class="lire_cellule_22"><p><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><b>URL du logo 
            de votre &eacute;tablissement :</b></font></p>
          <p><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Facultatif, ce logo sera  report&eacute; sur la page d'accueil, et sur les fils RSS.</font><br>
            <br>
          </p></td>
        <td width="45%" class="lire_cellule_22"><br>
          <input name="url_logo_etab" type="text" id="url_logo_etab" value="<?php echo $row_url_logo_db['param_val'];?>"  size="50" maxlength="80">
          <?php mysqli_free_result($url_logo_db);?>
          <em>ex : http://www.monsite.com/logo.jpg<br>
          (Dimension maxi de l'image : 200 pixels X 150 pixels) </em></td>
      </tr>
      <tr >
        <td width="50%" class="lire_cellule_22" ><p><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><b>URL de sortie des &eacute;l&egrave;ves apr&egrave;s d&eacute;connexion </b></font></p>
          <p><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Par d&eacute;faut, index.php<br>
            (page d'accueil du cahier de textes) </font> </p>
          <br></td>
        <td width="45%" class="lire_cellule_22" ><input name="url_deconnecte_eleve" type="text" id="url_deconnecte_eleve" value="<?php echo $row_url_deconnecte_eleve_db['param_val'];?>"  size="50" maxlength="80">
          <em>ex : index.php ou http://mon_site_etablissement.fr</em></td>
      </tr>
      <tr >
        <td width="50%" class="lire_cellule_22" ><p><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><b>URL de sortie des enseignants apr&egrave;s d&eacute;connexion </b></font></p>
          <p><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Par d&eacute;faut, index.php<br>
          (page d'accueil du cahier de textes) </font>          </p><br></td>
        <td width="45%" class="lire_cellule_22" ><input name="url_deconnecte_prof" type="text" id="url_deconnecte_prof" value="<?php echo $row_url_deconnecte_prof_db['param_val'];?>"  size="50" maxlength="80">
          <em>ex : index.php ou http://mon_site_etablissement.fr</em></td>
      </tr>
      <tr >
        <td width="50%" class="lire_cellule_22" ><p><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><b>Time Out de session </b></font></p>
          <p><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Temps en secondes au bout duquel il y aura perte de session et d&eacute;connexion. </font></p>
          <p>&nbsp;</p></td>
        <td width="45%" class="lire_cellule_22" ><input name="session_timeout" type="text" id="session_timeout" value="<?php echo $row_session_timeout_db['param_val']?>"  size="5" maxlength="5">
          <br>
          Mesure de s&eacute;curit&eacute; -
          
          Timeout pour 60 min : 60x60=3600 <br>
          Un temps plus court peut cependant exister par d&eacute;faut dans vos param&egrave;tres Serveur (php.ini)</td>
      </tr>
      <tr >
        <td width="50%" class="lire_cellule_22"><p><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><b>D&eacute;calage Horaire</b></font></p>
          <p><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Voici l'heure du serveur de votre h&eacute;bergeur : <strong>
            <?php 
        $heure_actuelle=date('H:m',time());
        echo $heure_actuelle;
        ?>
            </strong></font></p>
          <p><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Si &quot;heure de la localisation g&eacute;ographique de votre &eacute;tablissement ne correspond pas &agrave; celle de votre h&eacute;bergeur, indiquer ici votre zone de temps.<br/>
            Exemple : Europe/Paris ou Asia/Singapore <a target="_blank" href="http://www.php.net/manual/fr/timezones.europe.php"><br/>
            Vous pouvez la trouver ici.</a></font><br>
          </p>
          <br>        </td>
        <td width="45%" class="lire_cellule_22"><input name="time_zone" type="text" id="time_zone" value="<?php echo $row_time_zone_db['param_val']?>"  size="25" maxlength="25"></td>
        <?php mysqli_free_result($time_zone_db);?>
      </tr>
      <tr >
        <td width="50%" class="lire_cellule_22"><p><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><b>Adresse Piwik - Statistiques de consultation </b></font></p>
          <p><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Facultatif, cette adresse est celle de l'adresse Internet de votre site Piwik. Elle est toutefois n&eacute;cessaire si vous voulez des recensements statistiques sur Piwik.</font><br>
            <br>
            <br>
          </p></td>
        <td width="45%" class="lire_cellule_22"><input name="URL_Piwik" type="text" id="URL_Piwik" value="<?php echo $row_URL_Piwik_db['param_val']?>"  size="50" maxlength="80">
          <?php mysqli_free_result($URL_Piwik_db);?>
          <br />
          <em>ex : w3.ac-XXXX.fr/piwik/<br>
          Il est important de ne pas noter http:// dans l'adresse</em></td>
      </tr>
      <tr >
        <td width="50%" class="lire_cellule_22"><p><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><b>ID Piwik</b></font></p>
          <p><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Facultatif, ce num&eacute;ro est celui associ&eacute; &agrave; Piwik pour r&eacute;f&eacute;rencer votre cahier de textes. Il est toutefois n&eacute;cessaire si vous voulez des recensements statistiques sur Piwik.</font><br>
            <br>
          </p></td>
        <td width="45%" class="lire_cellule_22"><input name="ID_Piwik" type="text" id="ID_Piwik" value="<?php echo $row_ID_Piwik_db['param_val']?>"  size="25" maxlength="25">
          <?php mysqli_free_result($ID_Piwik_db);?>        <br>
          <em>Si vous avez un seul compte piwik,
          le num&eacute;ro sera &eacute;gal &agrave; 1</em></td>
      </tr>
      <tr>
        <td colspan="2" class="lire_cellule_22"><p>&nbsp;</p>
          <p class="erreur">Les param&egrave;tres de connexion seront enregistr&eacute;s dans le fichier <strong>Connections/conn_cahier_de_texte.php</strong>. <br />
            <br />
            Vous devez ponctuellement poss&eacute;der les droits en &eacute;criture sur ce fichier <br>
            pour que vos modifications 
            
            soient prises en compte. <br />
          </p>
          <p align="center">
            <input type="submit" name="verif" value="Enregistrer ces nouveaux param&egrave;tres " >
          </p>
          <p class="erreur Style70"><a href="index.php">Annuler</a></p>
          <p>&nbsp;</p></td>
      </tr>
    </table>
  </form>
  </DIV>
</body>
</html>
