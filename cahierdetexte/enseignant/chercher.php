<?php 
include "../authentification/authcheck.php"; 
if (($_SESSION['droits']<>2)&&($_SESSION['droits']<>8)) { header("Location: ../index.php");exit;};
require_once('../Connections/conn_cahier_de_texte.php');
require_once('../inc/functions_inc.php');
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Cahier de textes - <?php echo $_SESSION['identite']?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link media=screen href="../styles/style_default.css" type=text/css rel=stylesheet>
</head>
<body>
<div class="Style6">Recherche de s&eacute;ance par mot cl&eacute; </div>
<br />
<?php

if(isset($_POST['type']))
{
        if($_POST['type']=='un')//Un des mots
        {
                $type = 1;
        }
        elseif($_POST['type']=='tout')//Tout les mots
        {
                $type = 2;
        }
        else//L'expression exacte
        {
                $type = 3;
        }
}
else
{
        $type = 1;//type par defaut: L'expression exacte
};

if ((isset($_POST['sur_titre']))&&($_POST['sur_titre']=='O')){$sur_titre=1;} else {$sur_titre=0;}
if ((isset($_POST['sur_activ']))&&($_POST['sur_activ']=='O')){$sur_activ=1;} else {$sur_activ=0;};
if ((!isset($_POST['sur_activ']))&&(!isset($_POST['sur_titre']))){$sur_activ=1;$sur_titre=1;};
?>
<div align="left" style=";float: left ;margin-left: 50px ;">
  <form id="form1" name="form1" method="post" action="chercher.php">
    <strong><img src="../images/puce_jaune.gif">&nbsp;Type de recherche :</strong>
    <input type="radio" name="type" value="un"<?php if($type==1){echo ' checked="checked"';} ?> />
    Un des mots
    <input type="radio" name="type" value="tout"<?php if($type==2){echo ' checked="checked"';} ?> />
    Tout les mots
    <input type="radio" name="type" value="exacte"<?php if($type==3){echo ' checked="checked"';} ?> />
    Expression exacte &nbsp;&nbsp;&nbsp;&nbsp; <strong><img src="../images/puce_jaune.gif">&nbsp;Cible</strong> :
	
	<input type="checkbox" name="sur_titre" value="O" <?php if($sur_titre==1){echo ' checked="checked"';} ?>>
	Titre
	
	<input type="checkbox" name="sur_activ" value="O" <?php if($sur_activ==1){echo ' checked="checked"';} ?>/>
	Contenu 
	
	&nbsp;&nbsp;&nbsp;&nbsp;<img src="../images/puce_jaune.gif">&nbsp;<strong>Mots cl&eacute;s</strong> :
    
	<input type="text" name="search" />
    <input type="submit" name="Submit" value="Envoyer" />
  </form>
</div>
<div align="right"><a href="ecrire.php?date=<?php echo date('Ymd');?>"><img src="../images/home-menu.gif" width="26" height="20" border="0"></a></div>
<br />
<?php

//$rec = htmlentities($_POST['search']);
$rec = $_POST['search'];
$mots = explode(' ',$rec);


$searchSQL="SELECT * FROM cdt_agenda WHERE  prof_ID=".GetSQLValueString($_SESSION['ID_prof'], "int")." AND  (";
if($type==1)
{//ayant un des mots dans leurs informations

		foreach($mots as $mot)
				{
						if (($sur_activ==1)&&($sur_titre==0)){$searchSQL .= ' activite LIKE "%'.$mot.'%" OR';};
						if (($sur_titre==1)&&($sur_activ==0)){$searchSQL .= ' theme_activ LIKE "%'.$mot.'%" OR';};
						if (($sur_titre==1)&&($sur_activ==1)){$searchSQL .= ' theme_activ LIKE "%'.$mot.'%" OR activite LIKE "%'.$mot.'%" OR ';};
				}
				$searchSQL .= ' 1=0 )';

		
}
elseif($type==2)
{//ayant tout des mots dans leurs informations
foreach($mots as $mot)
		{
						if (($sur_activ==1)&&($sur_titre==0)){$searchSQL .= ' activite LIKE "%'.$mot.'%" AND';};
						if (($sur_titre==1)&&($sur_activ==0)){$searchSQL .= ' theme_activ LIKE "%'.$mot.'%" AND';};
						if (($sur_titre==1)&&($sur_activ==1)){$searchSQL .= ' theme_activ LIKE "%'.$mot.'%" OR activite LIKE "%'.$mot.'%" AND ';};
        }
        $searchSQL .= ' 1=1 )';
}
else
{//ayant l'expression exacte dans leurs informations

						if (($sur_activ==1)&&($sur_titre==0)){$searchSQL .= ' activite LIKE "%'.$rec.'%" )';};
						if (($sur_titre==1)&&($sur_activ==0)){$searchSQL .= ' theme_activ LIKE "%'.$rec.'%" )';};
						if (($sur_titre==1)&&($sur_activ==1)){$searchSQL .= ' theme_activ LIKE "%'.$rec.'%" OR activite LIKE "%'.$rec.'%" )';};
};
$searchSQL .=" ORDER BY code_date";

//echo $searchSQL.'<br>';
echo '<br />';
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$Rssearch = mysqli_query($conn_cahier_de_texte, $searchSQL) or die(mysqli_error($conn_cahier_de_texte));
$row_Rssearch = mysqli_fetch_assoc($Rssearch);
$totalRows_Rssearch = mysqli_num_rows($Rssearch);

echo $totalRows_Rssearch. ' occurence(s)  trouv&eacute;e(s)<br>';
if ($totalRows_Rssearch>0){
do {
echo '<br />';
	
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$query_RsClasse = "SELECT nom_classe FROM cdt_classe WHERE ID_classe=".$row_Rssearch['classe_ID'];
	//echo $query_RsClasse;
	$RsClasse = mysqli_query($conn_cahier_de_texte, $query_RsClasse) or die(mysqli_error($conn_cahier_de_texte));
	$row_RsClasse = mysqli_fetch_assoc($RsClasse);
	$totalRows_RsClasse = mysqli_num_rows($RsClasse);


mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsMatiere = "SELECT * FROM cdt_matiere  WHERE ID_matiere=".$row_Rssearch['matiere_ID'];
$RsMatiere = mysqli_query($conn_cahier_de_texte, $query_RsMatiere) or die(mysqli_error($conn_cahier_de_texte));
$row_RsMatiere = mysqli_fetch_assoc($RsMatiere);
$totalRows_RsMatiere = mysqli_num_rows($RsMatiere);

$jour_sem=explode(" ",$row_Rssearch['jour_pointe']);

?>
<a href="ecrire.php?nom_classe=<?php echo $row_RsClasse['nom_classe'];?>
&classe_ID=<?php echo $row_Rssearch['classe_ID'];?>
&gic_ID=<?php echo $row_Rssearch['gic_ID'];?>
&nom_matiere=<?php echo $row_RsMatiere['nom_matiere'];?>
&groupe=<?php echo $row_Rssearch['groupe'];?>
&matiere_ID=<?php echo $row_Rssearch['matiere_ID'];?>
&semaine=<?php echo $row_Rssearch['semaine'];?>
&heure=<?php echo $row_Rssearch['heure'];?>
&duree=<?php echo $row_Rssearch['duree'];?>
&heure_debut=<?php echo $row_Rssearch['heure_debut'];?>
&heure_fin=<?php $row_Rssearch['heure_fin'];?>
&current_day_name= <?php echo $jour_sem[0];?>
&code_date=<?php echo $row_Rssearch['code_date'];?>
&jour_pointe=<?php echo $row_Rssearch['jour_pointe'];?>
">
  <div class="tab_detail_gris_clair">
    <blockquote><?php echo $row_RsClasse['nom_classe']. '  -  '.$row_Rssearch['jour_pointe'].  '  -  '.$row_Rssearch['heure_debut'];?></a>
<?php
echo '  -  ';
echo preg_replace('#('.str_replace(' ','|',preg_quote($rec)).')#i', '<span style="color:#F00"><strong>$1</strong></span>', $row_Rssearch['theme_activ']);
echo '<br />';
echo '<blockquote>';
echo preg_replace('#('.str_replace(' ','|',preg_quote($rec)).')#i', '<span style="color:#F00"><strong>$1</strong></span>', $row_Rssearch['activite']);
echo '</blockquote>';
?>
    </blockquote>
  </div>
  <?php
} while ($row_Rssearch = mysqli_fetch_assoc($Rssearch)); 

}


?>
<p>&nbsp; </p>
<p align="center"><a href="ecrire.php?date=<?php echo date('Ymd');?>">Retour en saisie de s&eacute;ance</a></p>
<p>&nbsp; </p>
</body>
</html>
