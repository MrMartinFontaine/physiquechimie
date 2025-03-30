<?php 
include "../authentification/authcheck.php" ;
if ($_SESSION['droits']<>1) { header("Location: ../index.php");exit;};
require_once('../Connections/conn_cahier_de_texte.php');
require_once('../inc/functions_inc.php');

$editFormAction = '#';
if (isset($_SERVER['QUERY_STRING'])) {
	$editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

$insertSQL='TRUNCATE `cdt_semaine_ab`';
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$Result1 = mysqli_query($conn_cahier_de_texte, $insertSQL) or die(mysqli_error($conn_cahier_de_texte));

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte) or die(mysqli_error($conn_cahier_de_texte));
$query_read = "SELECT `param_val` FROM `cdt_params` WHERE `param_nom`='libelle_semaine' LIMIT 1;";
$result_read = mysqli_query($conn_cahier_de_texte, $query_read);
$row = mysqli_fetch_row($result_read);
if ($row[0]==1){$sema="Sem. Paire";$semb="Sem. Impaire";} else {$sema="Sem. A";$semb="Sem. B";};


if (isset($_POST['sem_rentree']))
{
	function get_semaine($semaine,$annee)
	{
		Global $dateSemaine;
		$date_depart = 4 ;
		while (date("w",mktime(0,0,0,01,($date_depart+($semaine-1)*7),$annee)) != 1)
			$date_depart-- ;
		
		for ($a=0;$a<7;$a++)
			$dateSemaine[$a] = date("d-m-Y",mktime(0,0,0,01,($date_depart+$a+($semaine-1)*7),$annee));
		
		return $dateSemaine;
	}



        $sem_rentree=$_POST['sem_rentree'];
        $annee2=$_POST['annee_rentree'];
        $annee3=$_POST['annee_sortie'];
        $smax = $annee2==$annee3?$_POST['sem_sortie']:53;
        $x=1;$y=1;
		$sem_rentree=intval($sem_rentree);
        for ($s=$sem_rentree;$s<$smax;$s++)
        {
                get_semaine($s,$annee2);
               		
		        if($x>0){$sem_choix='A';} else {$sem_choix='B';};
		        
				if ($y==1) {$sem_alter_choix='Sem 1';};
				if ($y==2) {$sem_alter_choix='Sem 2';};
				if ($y==3) {$sem_alter_choix='Sem 3';};
				if ($y==4) {$sem_alter_choix='Sem 4';};
				
        		$s_code_date=substr($dateSemaine[0],6,4).substr($dateSemaine[0],3,2).substr($dateSemaine[0],0,2);
        		$insertSQL = sprintf("INSERT INTO cdt_semaine_ab (semaine,semaine_alter,num_semaine,s_code_date,date_lundi,date_dimanche) VALUES (%s,%s,%u,%s,%s,%s)",
        			GetSQLValueString($sem_choix, "text"),
					GetSQLValueString($sem_alter_choix, "text"),					
        			GetSQLValueString($s, "int"),
        			GetSQLValueString($s_code_date, "text"),
        			GetSQLValueString($dateSemaine[0], "text"),
        			GetSQLValueString($dateSemaine[6], "text")
        			);
        		
        		mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
        		$Result1 = mysqli_query($conn_cahier_de_texte, $insertSQL) or die(mysqli_error($conn_cahier_de_texte));
		
		
		
		
		
                $x=$x*(-1);
				$y=$y+1; if ($y==5){$y=1;}; 
        }
        
        if ($annee2!=$annee3) {
        	for ($s=1;$s<=$_POST['sem_sortie'];$s++)
        	{
        		get_semaine($s,$annee2+1);
	           	if($x>0){$sem_choix='A';} else {$sem_choix='B';}; 

				if ($y==1) {$sem_alter_choix='Sem 1';};
				if ($y==2) {$sem_alter_choix='Sem 2';};
				if ($y==3) {$sem_alter_choix='Sem 3';};
				if ($y==4) {$sem_alter_choix='Sem 4';};
				       		
        						
				$s_code_date=substr($dateSemaine[0],6,4).substr($dateSemaine[0],3,2).substr($dateSemaine[0],0,2);
        		$insertSQL = sprintf("INSERT INTO cdt_semaine_ab (semaine,semaine_alter,num_semaine,s_code_date,date_lundi,date_dimanche) VALUES (%s,%s,%u,%s,%s,%s)",
        			GetSQLValueString($sem_choix, "text"),
					GetSQLValueString($sem_alter_choix, "text"),
        			GetSQLValueString($s, "int"),
        			GetSQLValueString($s_code_date, "text"),
        			GetSQLValueString($dateSemaine[0], "text"),
        			GetSQLValueString($dateSemaine[6], "text")
        			);
        		
        		mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
        		$Result1 = mysqli_query($conn_cahier_de_texte, $insertSQL) or die(mysqli_error($conn_cahier_de_texte));
				
        		$x=$x*(-1);
				$y=$y+1; if ($y==5){$y=1;}; 
        		
        	}
        }
		
		
	}?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Cahier de textes - <?php echo $_SESSION['identite']?></title>
 
<LINK media=screen href="../styles/style_default.css" type=text/css rel=stylesheet>
<LINK media=screen href="../templates/default/header_footer.css" type=text/css rel=stylesheet>
</HEAD>
<BODY>
<DIV id=page>
<?php 
$header_description="Programmation des semaines en alternance";
require_once "../templates/default/header.php";
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte) or die(mysqli_error($conn_cahier_de_texte));
$query_read = "SELECT `param_val` FROM `cdt_params` WHERE `param_nom`='libelle_semaine' LIMIT 1;";
$result_read = mysqli_query($conn_cahier_de_texte, $query_read);
$row = mysqli_fetch_row($result_read);
if ($row[0]==1){$libelle = 'Semaine Paire et Impaire';} else {$libelle = 'Semaine A et B';};
?>

<HR> 
<p align="center">Une programmation par d&eacute;faut vient d'&ecirc;tre d&eacute;finie. <br />
  Veuillez la consulter ou la red&eacute;finir en utilisant les liens ci-dessous.</p>
<p align="center">&nbsp;</p>
<p align="center"><a href="libelle_semaine.php"><span class="Style13">Gestion des semaines A et B (Paire ou Impaire) </span></a></p>
<p align="center"><a href="semaine_ab_modif.php">Consulter / Modifier  la programmation des semaines A et B de l'ann&eacute;e scolaire en cours</a></p>
<p align="center">&nbsp;</p>
<p align="center">&nbsp;</p>
<HR> 
<p align="center"><span class="Style13">Gestion des alternances sur 4 semaines (Sem 1, Sem 2, Sem 3,Sem4) </span></p>

<p align="center"><a href="semaine_alter4_modif.php">Consulter / Modifier la programmation des alternances 4 semaines de l'ann&eacute;e scolaire en cours</a></p>
<p align="center">&nbsp;</p>
<body>
  <p align="center"><a href="index.php">Retour au Menu Administrateur</a></p>
  <p>&nbsp; </p>
<DIV id=footer></DIV>
</DIV>

</body>
</html>

