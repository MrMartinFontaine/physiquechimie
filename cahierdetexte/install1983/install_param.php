<html>
<head>
<title>Cahier de textes - Installation</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
 
<LINK media=screen href="../styles/style_default.css" type=text/css rel=stylesheet>
<LINK media=screen href="../templates/default/header_footer.css" type=text/css rel=stylesheet>
</HEAD>
<BODY>
<DIV id=page>
<?php 
$header_description="INSTALLATION - Premi&egrave;re partie - Etape 1";
require_once "../templates/default/header.php";
?>

<HR>
<form name="parametres" method="post" action="install_param2.php">
<br>
<table width="95%" border="0" align="center" cellspacing="0" class="lire_cellule_22">

    <tr bgcolor="#CCCCCC"> 
      <td width="50%" class="lire_cellule_22"> <p><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><b>Serveur 
          MySQL:</b><br>
          La valeur localhost conviendra dans le cas d'un Intranet.</font></p>
          <p><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Chez 
        Free par exemple, ce sera sql.free.fr</font></p></td>
      <td width="50%" class="lire_cellule_22"> <input type="texte" name="serveur" id="serveur" size="25" maxlength="80" value="localhost"></td>
    </tr>
    <tr bgcolor="#CCCCCC"> 
      <td width="50%" class="lire_cellule_22"> <p><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><b>Nom 
          d'utilisateur MySQL :</b></font></p>
        <p><font size="2" face="Verdana, Arial, Helvetica, sans-serif">La valeur 
          root conviendra dans le cas d'un Intranet.</font></p>
          <p><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Chez Free 
        par exemple, ce sera votre login Free</font></p></td>
      <td width="50%" class="lire_cellule_22"> <input type="text" name="login" id="login" size="25" maxlength="80" value="root"></td>
    </tr>
    <tr bgcolor="#CCCCCC"> 
      <td width="50%" class="lire_cellule_22"> <p><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><b>Mot 
          de passe MySQL :</b></font></p>
        <p><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Pas de 
          mot de passe par d&eacute;faut dans le cas d'un intranet</font> (sauf Easyphp version 3 - mot de passe : mysql) </p>
        <p><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Chez Free, ce sera  votre mot de passe Free</font></p></td>
      <td width="50%" class="lire_cellule_22"> <input type="password" name="password" id="password" size="25" maxlength="80" value=""></td>
    </tr>
   
    <tr bgcolor="#CCCCCC"> 
      <td width="50%" class="lire_cellule_22"> <p><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><b>Nom 
          de la base de donn&eacute;es :</b></font></p>
        <p><font size="2" face="Verdana, Arial, Helvetica, sans-serif">En intranet, soit par exemple cahier_de_textes. </font></p>
        <p><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> Chez Free, la base portera le nom de votre login Free</font></p></td>
      <td width="50%" class="lire_cellule_22"> <input name="base" type="text" id="base"  size="25" maxlength="80" value="cahier_de_textes"></td>
    </tr>
    <tr bgcolor="#CCCCCC"> 
      <td width="50%" class="lire_cellule_22"> <p><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><b>Nom 
          de votre &eacute;tablissement:</b></font></p>
        <p><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Facultatif, ce nom est report&eacute; sur la page d'accueil. </font></p>
      </td>
      <td width="50%" class="lire_cellule_22"> <input name="nom_etab" type="text" id="nom_etab"  size="50" maxlength="80"></td>
    </tr>
	<tr bgcolor="#CCCCCC"> 
      <td width="50%" class="lire_cellule_22"> <p><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><b>URL 
          de votre site &eacute;tablissement:</b></font></p>
        <p><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Facultatif, permet de cr&eacute;er des liens de retour vers votre site &eacute;tablissement. </font></p>
      </td>
      <td width="50%" class="lire_cellule_22"> <input name="url_etab" type="text" id="url_etab" value="http://"  size="50" maxlength="80"></td>
    </tr>
	<tr bgcolor="#CCCCCC"> 
      <td width="50%" class="lire_cellule_22"> <p><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><b>URL du logo 
          de votre &eacute;tablissement:</b></font></p>
        <p><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Facultatif, ce logo sera  report&eacute; sur la page d'accueil, et sur les fils RSS. </font></p>
      </td>
      <td width="50%" class="lire_cellule_22"><input name="url_logo_etab" type="text" id="url_logo_etab" value="http://"  size="50" maxlength="80"></td>
    </tr>
    <tr> 
      <td colspan="2" align="center">
        <p>&nbsp;</p>
        <p class="erreur">Tous ces param&egrave;tres seront enregistr&eacute;s dans le fichier <strong>Connections/conn_cahier_de_texte.php</strong><br>
        Vous devez ponctuellement poss&eacute;der les droits en &eacute;criture sur ce fichier </p>
        <p> <br>
          <input type="submit" name="verif" value="Enregistrer ces param&egrave;tres " >
        </p>
        <p>&nbsp; </p>        </td>
    </tr>

  </table>
</form>
<DIV id=footer></DIV>
</DIV>
</body>
</html>
