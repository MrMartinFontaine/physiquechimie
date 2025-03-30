<?php include "../authentification/authcheck.php";
if ($_SESSION['droits']<>1) { header("Location: ../index.php");exit;}; ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Cahier de textes - <?php echo $_SESSION['identite']?></title>
<link media=screen href="../styles/style_default.css" type=text/css rel=stylesheet>
<link media=screen href="../templates/default/header_footer.css" type=text/css rel=stylesheet>
<script type="text/javascript">
<!--
  function cleartext()
	 { document.expression.data.value = "";
	   document.expression.data.focus(); 
	 }
	 
  // fonction pour tester le serveur CGI latex	 
  function afficher()
     {  var i= document.expression.serveur_latex.options.selectedIndex;
        var serveur_url= document.expression.serveur_latex.options[i].value; 
     	image = document.getElementById("rendu");
        image.src= serveur_url + encodeURI(document.expression.data.value);
     }
 -->
 </script>

</head>
<body >
<div id=page>
     <?php
     $header_description="Choix du service LaTEX";
     require_once "../templates/default/header.php";
     ?>
<blockquote>
    <blockquote>
      <blockquote>
<p style="text-align: left;"> Cahier de textes introduit une puissante fonctionnalit&eacute; permettant d'ins&eacute;rer 
dans le texte des formules de math&eacute;matiques en utilisant la syntaxe Latex.</p>
<p style="text-align: left;">Les &eacute;quations sont trait&eacute;es par un service Web : autrement dit, les formules sont 
envoy&eacute;es &agrave; un serveur qui retourne les fichiers graphiques de ces &eacute;quations <a href="../enseignant/xinha/plugins/EditLatex/lisez moi v2.pdf" target="_blank">
(en savoir plus)</a>.  </p>
</blockquote>
    </blockquote>
  </blockquote>

<form method="POST" action="editLatex.php" name="expression">
	<p><b>Choisir un service web pour Latex dans la liste suivante</b><br/><br/> 
        <select size="1" name="serveur_latex">
	        <option selected value="http://math.spip.org/tex.php?">math.spip.org </option>
	        <option value="http://www.forum-maths-express.net/cgi-bin/latex.cgi?">forum-math-express.net </option>
	        <option value="http://www.forkosh.com/mimetex.cgi?">forkosh.com mimetex </option>
	        <option value="http://www.forkosh.com/mathtex.cgi?">forkosh.com  mathtex  </option>
	        <option value="http://www.problem-solving.be/cgi-bin/mathtex.cgi?">problem-solving.be  mathtex </option>
	</select><br/><br/>
	<b>Entrer une expression Latex...</b><br/><br/>
	<textarea cols="72" rows="5" name="data">\Large f(x)=\int_{-\infty}^x e^{-t^2}dt</textarea><br/><br/>
		<input type="button" value="Afficher" onclick="afficher()"/>
	   <input type="button" value="Effacer l'expression" onclick="cleartext()"/><br/><br/>
	<b>puis cliquer sur afficher pour observer le rendu...</b><br/><br/>
	<div style="background-color: buttonFace ; border: 1px solid #808080 ; width: 400px ; padding: 10px ; margin: 0 auto ;">
			<img id="rendu">
	</div>
	</p>
	<p>
		<input type="submit" value="Enregistrer le service web choisi" name="B1">
	   
	<br/>
	<br/>

        </p>
</form>
  <p align="center"><a href="index.php">Retour au Menu Administrateur</a></p>
  <p>&nbsp; </p>
<DIV id=footer></DIV>
</div>
</body>

</html>
