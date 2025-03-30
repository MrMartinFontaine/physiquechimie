<?php 
include "../authentification/authcheck.php";
if ($_SESSION['droits']<>1 && $_SESSION['droits']<>2 && $_SESSION['droits']<>3) { header("Location: ../index.php");exit;};
require_once('../Connections/conn_cahier_de_texte.php'); 
require_once('../inc/functions_inc.php');

$editFormAction = '#';
if (isset($_SERVER['QUERY_STRING'])) {
	$editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

//Test de validite d'un regroupement - Permet de corriger un bug d'une ancienne version
//Suite a une modification d'une plage avec regroupement, un enregistrement de la table cdt_emploi_du_temps
//a pu se retrouver avec un gic_ID=0 et un classe_ID=0 donc sans affectation de classe.
$bug=0;
if ((isset($_GET['regroupement']))&&($_GET['regroupement']==0)){
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$query_Rs_bug = sprintf("SELECT * FROM cdt_emploi_du_temps WHERE ID_emploi=%u", GetSQLValueString($_GET['ID_emploi'],"int"));
	$Rs_bug = mysqli_query($conn_cahier_de_texte, $query_Rs_bug) or die(mysqli_error($conn_cahier_de_texte));
	$row_Rs_bug = mysqli_fetch_assoc($Rs_bug);
	
	if (($row_Rs_bug['classe_ID']==0)&&($row_Rs_bug['gic_ID']==0)&&($row_Rs_bug['ImportEDT']=='NON')){$bug=1;
		echo '<br /><br /><p>Suite &agrave; une modification de cette plage avec regroupement dans une version ant&eacute;rieure, cette plage n\'est affect&eacute;e actuellement &agrave; aucune classe.<br /> Il est fortement conseill&eacute; de la supprimer.</p>';?>
		<p><a href="emploi_supprime_verif.php?ID_emploi=<?php echo $_GET['ID_emploi'].'&affiche=1';?>">Supprimer cette plage</a></p>
		<p><a href="emploi.php?affiche=1">Ne rien faire et retourner &agrave; la gestion de mon emploi du temps</a></p>
		<?php
	}
}
if ($bug==0){
	
	if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
	
$sem_alter='';
	if (isset($_POST['s1'])) {$s1=GetSQLValueString($_POST['s1'], "int");}else{$s1='';};
	if (isset($_POST['s2'])) {$s2=GetSQLValueString($_POST['s2'], "int");}else{$s2='';};
	if (isset($_POST['s3'])) {$s3=GetSQLValueString($_POST['s3'], "int");}else{$s3='';};
	if (isset($_POST['s4'])) {$s4=GetSQLValueString($_POST['s4'], "int");}else{$s4='';};

  

	$sem_alter=$s1.$s2.$s3.$s4;

	if($sem_alter<>''){$choix_semaine=$sem_alter;} else {$choix_semaine=GetSQLValueString($_POST['semaine'], "text");};
		
		$err=0;
		
		if ((isset($_POST['edt_exist_debut']))&&(isset($_POST['edt_exist_fin']))) {
			$date1=substr($_POST['edt_exist_debut'],6,4).'-'.substr($_POST['edt_exist_debut'],3,2).'-'.substr($_POST['edt_exist_debut'],0,2); 
			$date2=substr($_POST['edt_exist_fin'],6,4).'-'.substr($_POST['edt_exist_fin'],3,2).'-'.substr($_POST['edt_exist_fin'],0,2);
			if ($date1>$date2) {
				$message_erreur_date="La date de d&eacute;but doit &ecirc;tre avant la date de fin.<br />";
				$err=1;
			}
		}
		
		if ($err==0) {
			$gic = 0;
			if (isset($_POST['classe_ID']) && $_POST['classe_ID'] == 0) {
				$gic = GetSQLValueString($_POST['gic_ID'], "int");
			}
			
			
			if (isset($_POST['edt_exist_debut'])){$date1=substr($_POST['edt_exist_debut'],6,4).'-'.substr($_POST['edt_exist_debut'],3,2).'-'.substr($_POST['edt_exist_debut'],0,2);} 
			if (isset($_POST['edt_exist_fin'])){$date2=substr($_POST['edt_exist_fin'],6,4).'-'.substr($_POST['edt_exist_fin'],3,2).'-'.substr($_POST['edt_exist_fin'],0,2);} 
			
			if (isset($_POST['update_limite'])&&($_POST['update_limite']>0)){
				
				$updateSQL = sprintf("UPDATE cdt_emploi_du_temps SET couleur_cellule=%s, couleur_police=%s, edt_exist_debut=%s, edt_exist_fin=%s WHERE ID_emploi=%u",
					GetSQLValueString($_POST['couleur_cellule'], "text"),
					GetSQLValueString($_POST['couleur_police'], "text"),
					GetSQLValueString($date1, "text"),
					GetSQLValueString($date2, "text"),
					GetSQLValueString($_POST['ID_emploi'], "int"));
			}
			else if ((isset($_POST['h1']))&&(isset($_POST['mn1']))&&(isset($_POST['h2']))&&(isset($_POST['mn2'])))
			{
                                $heure_debut=$_POST['h1'].'h'.$_POST['mn1'];
                                $heure_fin=$_POST['h2'].'h'.$_POST['mn2'];
                                
                                $updateSQL = sprintf("UPDATE cdt_emploi_du_temps SET jour_semaine=%s, semaine=%s, heure=%u, classe_ID=%u,gic_ID=%u, fusion_gic=%s, groupe=%s, matiere_ID=%u, heure_debut=%s, heure_fin=%s, duree=%s, couleur_cellule=%s, couleur_police=%s, edt_exist_debut=%s, edt_exist_fin=%s WHERE ID_emploi=%u",
                                        GetSQLValueString($_POST['jour_semaine'], "text"),
										$choix_semaine,
                                        GetSQLValueString($_POST['heure'], "int"),
                                        GetSQLValueString($_POST['classe_ID'], "int"),
                                        $gic,
                                        GetSQLValueString(isset($_POST['fusion_gic']) ? 'true' : '', 'defined','"O"','"N"'),
                                        GetSQLValueString($_POST['groupe'], "text"),
                                        GetSQLValueString($_POST['matiere_ID'], "int"),
                                        GetSQLValueString($heure_debut, "text"),
					GetSQLValueString($heure_fin, "text"),
					GetSQLValueString($_POST['duree'], "text"),
					GetSQLValueString($_POST['couleur_cellule'], "text"),
					GetSQLValueString($_POST['couleur_police'], "text"),
					GetSQLValueString($date1, "text"),
					GetSQLValueString($date2, "text"),
					GetSQLValueString($_POST['ID_emploi'], "int"));
                        }
                        else 
                        {
                                $updateSQL = sprintf("UPDATE cdt_emploi_du_temps SET jour_semaine=%s, semaine=%s, heure=%u, classe_ID=%u, gic_ID=%u,fusion_gic=%s, groupe=%s, matiere_ID=%u, couleur_cellule=%s, couleur_police=%s, edt_exist_debut=%s, edt_exist_fin=%s WHERE ID_emploi=%u",
                                        GetSQLValueString($_POST['jour_semaine'], "text"),
										$choix_semaine,
                                        GetSQLValueString($_POST['heure'], "int"),
                                        GetSQLValueString($_POST['classe_ID'], "int"),
                                        $gic,
                                        GetSQLValueString(isset($_POST['fusion_gic']) ? 'true' : '', 'defined','"O"','"N"'),
                                        GetSQLValueString($_POST['groupe'], "text"),
                                        GetSQLValueString($_POST['matiere_ID'], "int"),
                                        GetSQLValueString($_POST['couleur_cellule'], "text"),
					GetSQLValueString($_POST['couleur_police'], "text"),
					GetSQLValueString($date1, "text"),
					GetSQLValueString($date2, "text"),
					GetSQLValueString($_POST['ID_emploi'], "int"));	
			}
			
			
                        mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
                        $Result1 = mysqli_query($conn_cahier_de_texte, $updateSQL) or die(mysqli_error($conn_cahier_de_texte));
                        
			if ($_POST['profID']==$_SESSION['ID_prof']) {
				
				mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
				$Suppression = sprintf("DELETE FROM cdt_emploi_du_temps_partage WHERE ID_emploi=%u", GetSQLValueString($_POST['ID_emploi'], "int"));
				$Rs_Suppression = mysqli_query($conn_cahier_de_texte, $Suppression) or die(mysqli_error($conn_cahier_de_texte));
				
				if(isset($_POST['partage']) && isset($_POST['partage_ID']) && !empty($_POST['partage_ID'])){
					$Col1_Array = $_POST['partage_ID'];
					foreach($Col1_Array as $selectValue){
						$insertSQL = sprintf("INSERT INTO cdt_emploi_du_temps_partage (ID_emploi, profpartage_ID) VALUES (%u, %u)",GetSQLValueString($_POST['ID_emploi'], "int"),$selectValue);
						mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
						$Result1 = mysqli_query($conn_cahier_de_texte, $insertSQL) or die(mysqli_error($conn_cahier_de_texte));
					}
				}
			}
                        
                        
                        $updateGoTo = "../enseignant/emploi.php";
                        if (isset($_SERVER['QUERY_STRING'])) {
                                $updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
				$updateGoTo .= $_SERVER['QUERY_STRING'];
			}
			
		header(sprintf("Location: %s", $updateGoTo));
		}
	}
	
	
	$refprof_Rs_emploi = "0";
	if (isset($_SESSION['ID_prof'])) {
		$refprof_Rs_emploi = (get_magic_quotes_gpc()) ? $_SESSION['ID_prof'] : addslashes($_SESSION['ID_prof']);
	}
	$indice_Rs_emploi = "0";
	if (isset($_GET['ID_emploi'])) {
		$indice_Rs_emploi = (get_magic_quotes_gpc()) ? $_GET['ID_emploi'] : addslashes($_GET['ID_emploi']);
	}
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$query_Rs_partage = sprintf("SELECT * FROM cdt_emploi_du_temps_partage WHERE profpartage_ID=%u AND ID_emploi=%u", $refprof_Rs_emploi,$indice_Rs_emploi);
	$Rs_partage = mysqli_query($conn_cahier_de_texte, $query_Rs_partage) or die(mysqli_error($conn_cahier_de_texte));
	$totalRows_Rs_partage = mysqli_num_rows($Rs_partage);
        mysqli_free_result($Rs_partage);
	if ($_GET['regroupement']==1){
	    if ($totalRows_Rs_partage>0) { //Heure partagee
		$query_Rs_emploi = sprintf("SELECT * FROM cdt_emploi_du_temps,cdt_matiere WHERE cdt_emploi_du_temps.ID_emploi=%u AND cdt_emploi_du_temps.matiere_ID=cdt_matiere.ID_matiere ORDER BY cdt_emploi_du_temps.jour_semaine, cdt_emploi_du_temps.heure, cdt_emploi_du_temps.semaine", $indice_Rs_emploi);
	    } else {
		$query_Rs_emploi = sprintf("SELECT * FROM cdt_emploi_du_temps,cdt_matiere WHERE cdt_emploi_du_temps.ID_emploi=%u AND cdt_emploi_du_temps.matiere_ID=cdt_matiere.ID_matiere AND cdt_emploi_du_temps.prof_ID=%u ORDER BY cdt_emploi_du_temps.jour_semaine, cdt_emploi_du_temps.heure, cdt_emploi_du_temps.semaine", $indice_Rs_emploi,$refprof_Rs_emploi);
	    };
        } else { //regroupement = 0
	    if ($totalRows_Rs_partage>0) { //Heure partagee
		    $query_Rs_emploi = sprintf("SELECT * FROM cdt_emploi_du_temps,cdt_matiere,cdt_classe WHERE cdt_emploi_du_temps.ID_emploi=%u AND cdt_emploi_du_temps.matiere_ID=cdt_matiere.ID_matiere AND (cdt_emploi_du_temps.classe_ID=cdt_classe.ID_classe OR cdt_emploi_du_temps.classe_ID=0) ORDER BY cdt_emploi_du_temps.jour_semaine, cdt_emploi_du_temps.heure, cdt_emploi_du_temps.semaine", $indice_Rs_emploi);
	    } else {  //Heure non partagee
		    $query_Rs_emploi = sprintf("SELECT * FROM cdt_emploi_du_temps,cdt_matiere,cdt_classe WHERE cdt_emploi_du_temps.ID_emploi=%u AND cdt_emploi_du_temps.matiere_ID=cdt_matiere.ID_matiere AND cdt_emploi_du_temps.prof_ID=%u AND (cdt_emploi_du_temps.classe_ID=cdt_classe.ID_classe OR cdt_emploi_du_temps.classe_ID=0) ORDER BY cdt_emploi_du_temps.jour_semaine, cdt_emploi_du_temps.heure, cdt_emploi_du_temps.semaine", $indice_Rs_emploi,$refprof_Rs_emploi);
	    };
        };

	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$Rs_emploi = mysqli_query($conn_cahier_de_texte, $query_Rs_emploi) or die(mysqli_error($conn_cahier_de_texte));
	$row_Rs_emploi = mysqli_fetch_assoc($Rs_emploi);
	$totalRows_Rs_emploi = mysqli_num_rows($Rs_emploi);
	
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$query_RsClasse = "SELECT * FROM cdt_classe ORDER BY nom_classe ASC";
	$RsClasse = mysqli_query($conn_cahier_de_texte, $query_RsClasse) or die(mysqli_error($conn_cahier_de_texte));
	$row_RsClasse = mysqli_fetch_assoc($RsClasse);
	$totalRows_RsClasse = mysqli_num_rows($RsClasse);
	
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$query_RsMatiere = "SELECT * FROM cdt_matiere ORDER BY nom_matiere ASC";
	$RsMatiere = mysqli_query($conn_cahier_de_texte, $query_RsMatiere) or die(mysqli_error($conn_cahier_de_texte));
	$row_RsMatiere = mysqli_fetch_assoc($RsMatiere);
	$totalRows_RsMatiere = mysqli_num_rows($RsMatiere);
	
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$query_Rsgroupe = "SELECT * FROM cdt_groupe ORDER BY groupe ASC";
	$Rsgroupe = mysqli_query($conn_cahier_de_texte, $query_Rsgroupe) or die(mysqli_error($conn_cahier_de_texte));
	$row_Rsgroupe = mysqli_fetch_assoc($Rsgroupe);
	$totalRows_Rsgroupe = mysqli_num_rows($Rsgroupe);
	
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$query_Rsplage_all ="SELECT * FROM cdt_plages_horaires ORDER BY ID_plage";
	$Rsplage_all = mysqli_query($conn_cahier_de_texte, $query_Rsplage_all) or die(mysqli_error($conn_cahier_de_texte));
        $row_Rsplage_all= mysqli_fetch_assoc($Rsplage_all);
        
        mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
        $query_Rsgic =sprintf("SELECT * FROM cdt_groupe_interclasses WHERE cdt_groupe_interclasses.prof_ID = %u ",GetSQLValueString($_SESSION['ID_prof'],"int"));
        $Rsgic = mysqli_query($conn_cahier_de_texte, $query_Rsgic) or die(mysqli_error($conn_cahier_de_texte));
        $row_Rsgic = mysqli_fetch_assoc($Rsgic);
        $totalrows_Rsgic=mysqli_num_rows($Rsgic);
        
        ?>
        <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
        <html>
        <head>
<title>Cahier de textes - <?php echo $_SESSION['identite']?></title>
        <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
        
        <link media=screen href="../styles/style_default.css" type=text/css rel=stylesheet>
        <link rel="stylesheet" type="text/css" href="../styles/colorpicker.css" />
        <link href="../styles/info_bulles.css" rel="stylesheet" type="text/css" />
        <link type="text/css" href="../styles/jquery-ui-1.8.13.custom_perso.css" rel="stylesheet" />
        
        <style type="text/css">
        .curseur_pointe { cursor: pointer; }
        </style>
        
        <script language="JavaScript" type="text/JavaScript">
	<!--
	var xhr = null; 
	function getXhr()
	{
		if(window.XMLHttpRequest)xhr = new XMLHttpRequest(); 
		else if(window.ActiveXObject)
		{ 
			try{
				xhr = new ActiveXObject("Msxml2.XMLHTTP");
			} catch (e) 
			{
				xhr = new ActiveXObject("Microsoft.XMLHTTP");
			}
		}
		else 
		{
			alert("Votre navigateur ne supporte pas les objets XMLHTTPRequest..."); 
			xhr = false; 
		} 
	}
	
	function ShowRegroupement()
	{
		getXhr();
		xhr.onreadystatechange = function()
		{
			if(xhr.readyState == 4 && xhr.status == 200)
			{
                                document.getElementById('gic_ID').innerHTML=xhr.responseText;
                        }
                }
                // Ici on va voir comment faire du post
                xhr.open("POST","ajax_regroupement.php",true);
                // ne pas oublier ca pour le post
                xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
                // ne pas oublier de poster les arguments
                sel = document.getElementById('classe');
                classe = sel.options[sel.selectedIndex].value;
                xhr.send("Classe="+classe);
	}
	
	function ShowPlages(){
		getXhr();
		// On definit ce qu'on va faire quand on aura la reponse
		xhr.onreadystatechange = function(){
			// On ne fait quelque chose que si on a tout recu et que le serveur est ok
			if(xhr.readyState == 4 && xhr.status == 200){
				leselect = xhr.responseText;
				// On se sert de innerHTML pour rajouter les options a la liste
				document.getElementById('plages').innerHTML = leselect;
			}
		}
		
		// Ici on va voir comment faire du post
		xhr.open("POST","ajax_plages.php",true);
		// ne pas oublier ca pour le post
		xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
		// ne pas oublier de poster les arguments
		// ici, la plage horaire
		sel = document.getElementById('heure');
		ID_plage = sel.options[sel.selectedIndex].value;
		xhr.send("ID_plage="+ID_plage);
	}
	
	function ShowPlagesModif(){
		getXhr();
		// On definit ce qu'on va faire quand on aura la reponse
		xhr.onreadystatechange = function(){
			// On ne fait quelque chose que si on a tout recu et que le serveur est ok
			if(xhr.readyState == 4 && xhr.status == 200){
				leselect = xhr.responseText;
				// On se sert de innerHTML pour rajouter les options a la liste
				document.getElementById('plages').innerHTML = leselect;
			}
		}
		
		// Ici on va voir comment faire du post
		xhr.open("POST","ajax_plages.php",true);
		// ne pas oublier ca pour le post
		xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
		// ne pas oublier de poster les arguments
		// ici, la plage horaire et l'ID de l'edt de la plage horaire en cours
		sel = document.getElementById('heure');
		ID_plage = sel.options[sel.selectedIndex].value;
		sel = document.getElementById('num_edt');
		Num_Edt = sel.value;
		xhr.send("ID_plage="+ID_plage+"&Num_Edt="+Num_Edt);
	}
	
	
	
	function MM_reloadPage(init) {  //reloads the window if Nav4 resized
		
		if (init==true) with (navigator) {if ((appName=="Netscape")&&(parseInt(appVersion)==4)) {
			
		document.MM_pgW=innerWidth; document.MM_pgH=innerHeight; onresize=MM_reloadPage; }}
		
		else if (innerWidth!=document.MM_pgW || innerHeight!=document.MM_pgH) location.reload();
		
	}
	
	MM_reloadPage(true);
	
	
	
	function MM_goToURL() { //v3.0
		var i, args=MM_goToURL.arguments; document.MM_returnValue = false;
		for (i=0; i<(args.length-1); i+=2) eval(args[i]+".location='"+args[i+1]+"'");
	}
	
	
	
	function confirmation(ref,jour_del,heure_del)
	{
		if (confirm("Voulez-vous supprimer r\351ellement cette plage horaire du "+jour_del+" "+heure_del+ " ?")) { // Clic sur OK
			MM_goToURL('window','emploi_supprime_verif.php?ID_emploi='+ref+'&affiche=1');
		}
	}
	//-->
	
	function MM_popupMsg(msg) { //v1.0
                alert(msg);
        }
        //-->
        
        window.onload = function()
        {
                fctLoad();
	}
	window.onscroll = function()
	{
		fctShow();
	}
	window.onresize = function()
        {
                fctShow();
        }
        
        function PartageShow()
        {
                var Partage = document.getElementById("partage");
                
                if(Partage.checked==true) {
                        $("#cachepartage").show("slow");
                } else {
                        $("#cachepartage").hide("slow");
                }
        }
        
        $(function() {
                        $('selector').datepicker($.datepicker.regional['fr']);
                        var dates = $( "#edt_exist_debut, #edt_exist_fin" ).datepicker({
                                        defaultDate: "+1w",
                                        changeMonth: true,
                                        numberOfMonths: 1,
                                        firstDay:1,
                                        onSelect: function( selectedDate ) {
                                                var option = this.id == "edt_exist_debut" ? "minDate" : "maxDate",
                                                instance = $( this ).data( "datepicker" ),
                                                date = $.datepicker.parseDate(
                                                        instance.settings.dateFormat ||
                                                        $.datepicker._defaults.dateFormat,
                                                        selectedDate, instance.settings );
                                                dates.not( this ).datepicker( "option", option, date );
                                        }
                        });
        });
        </script>
        <script type="text/javascript" src="../jscripts/jquery-1.6.2.js"> </script>
        <script type="text/javascript" src="../jscripts/jquery-ui-1.8.13.custom.min.js"></script>
        <script type="text/javascript" src="../jscripts/jquery-ui.datepicker-fr.js"></script>
        <script type="text/javascript" src="../jscripts/CP_Class.js"></script>
        
        
        </HEAD>
        <BODY>
        
        <p>&nbsp;</p>
        <table width="90%"  border="0" align="center" cellpadding="0" cellspacing="0" class="lire_bordure" >
        <tr class="lire_cellule_4">
        <td width="29%" class="black_police"><div align="left">
        <?php if (isset($_SESSION['identite'])){echo '<img src="../images/identite.gif" >&nbsp;'.$_SESSION['identite'];
		if ($_SESSION['id_etat']==2){echo '&nbsp (Professeur suppl&eacute;ant)';};
		}?>
      </div></td>
        <td width="29%" class="black_police"> Gestion de mon emploi du temps</td>
        <td width="9%" ><div align="right" > <a href="enseignant.php"><img src="../images/home-menu.gif" alt="Accueil" width="26" height="20" border="0"></a><br>
        </div></td>
        </tr>
        </table>
        <BR />
        <p>
        <?php
        $exp="'%".$row_Rs_emploi['jour_semaine']."%'";
        $query_Rsagenda =sprintf("
                SELECT * FROM cdt_agenda WHERE prof_ID=%u AND heure=%s  AND semaine = '%s' AND classe_ID=%u AND groupe ='%s' AND jour_pointe LIKE %s",
                GetSQLValueString($_SESSION['ID_prof'],"int"),$row_Rs_emploi['heure'],$row_Rs_emploi['semaine'],$row_Rs_emploi['classe_ID'],$row_Rs_emploi['groupe'],$exp);
        $Rsagenda = mysqli_query($conn_cahier_de_texte, $query_Rsagenda) or die(mysqli_error($conn_cahier_de_texte));
        $row_Rsagenda = mysqli_fetch_assoc($Rsagenda);
        $totalRows_Rsagenda = mysqli_num_rows($Rsagenda);        

        // Autoriser la modification d'emploi du temps meme si des fiches sont deja saisies (decommenter la ligne suivante)
        //$totalRows_Rsagenda=0;
        
        if ($totalRows_Rsagenda>0){ ?>
<p class="erreur"> Des fiches de cours ont d&eacute;j&agrave; &eacute;t&eacute; remplies pour cette plage horaire.<br />
Vous ne pouvez modifier que la p&eacute;riode d'existence de cette plage horaire sur l'ann&eacute;e scolaire. <br />
Pour supprimer cette plage, vous devez pr&eacute;alablement supprimer les saisies de ces fiches de cours.  </p>
<p class="erreur">S'il s'agit d'un changement d'emploi du temps en cours d'ann&eacute;e scolaire, il vous faut cl&ocirc;turer cette plage <br />
en modifiant la date de fin d'existence de celle-ci. Vous conserverez alors les saisies d&eacute;j&agrave; r&eacute;alis&eacute;es.<br />
Il ne vous restera plus qu'&agrave; recr&eacute;er une nouvelle plage.<br />  
</p>

                <?php
        };
        
        $message_erreur='Vous devez saisir la position du cours';
        if (isset($_GET['erreur'])){echo $message_erreur;}?>
        </p>
        <p> </p>
        <form method="post" name="form1" action="<?php echo $editFormAction; ?>">
        <div align="center">
        <fieldset style="width : 90%">
        <legend align="top">Modification / Suppression d'une plage horaire</legend>
        <table width="90%" align="center">
        <tr valign="baseline">
        <td nowrap align="right">&nbsp;</td>
        <td>&nbsp;</td>
        </tr>
        <tr valign="baseline">
        <td nowrap align="right">Jour de la semaine :</td>
        <td><div align="left"><strong>
        <?php if ($totalRows_Rsagenda==0){ ?>
        	<select name="jour_semaine" size="1" id="jour_semaine">
        	<option value="Lundi" <?php if (!(strcmp("Lundi", $row_Rs_emploi['jour_semaine']))) {echo "SELECTED";} ?>>Lundi</option>
        	<option value="Mardi" <?php if (!(strcmp("Mardi", $row_Rs_emploi['jour_semaine']))) {echo "SELECTED";} ?>>Mardi</option>
        	<option value="Mercredi" <?php if (!(strcmp("Mercredi", $row_Rs_emploi['jour_semaine']))) {echo "SELECTED";} ?>>Mercredi</option>
        	<option value="Jeudi" <?php if (!(strcmp("Jeudi", $row_Rs_emploi['jour_semaine']))) {echo "SELECTED";} ?>>Jeudi</option>
        	<option value="Vendredi" <?php if (!(strcmp("Vendredi", $row_Rs_emploi['jour_semaine']))) {echo "SELECTED";} ?>>Vendredi</option>
        	<option value="Samedi" <?php if (!(strcmp("Samedi", $row_Rs_emploi['jour_semaine']))) {echo "SELECTED";} ?>>Samedi</option>
        	<option value="Dimanche" <?php if (!(strcmp("Dimanche", $row_Rs_emploi['jour_semaine']))) {echo "SELECTED";} ?>>Dimanche</option>
        	</select>
        <?php }
        else { echo $row_Rs_emploi['jour_semaine'];}
        ?>
        </strong></div></td>
        </tr>
        <tr>
        <td align="right" nowrap >Indice de plage de cours (1 &agrave; 12) <a href="#" class="tooltip">Aide <em><span></span>Ce nombre permettra d'ordonner les cours de la journ&eacute;e<br/>
        Vous pouvez d&eacute;finir jusqu'&agrave; 12 plages horaires sur une journ&eacute;e.<br/>
        Vous pouvez modifier les horaires propos&eacute;s par d&eacute;faut<br>
        par votre administrateur.</em></a>:</td>
        <td><div STYLE="float: left;"><strong>
        <?php if ($totalRows_Rsagenda==0){ ?>
        	<input type="hidden" name="num_edt" id="num_edt" value="<?php echo $_GET['ID_emploi']; ?>">
        	<select name="heure" id="heure" size="1" onchange='ShowPlages()' >
        	<?php $j=1;
        	do {
        		echo '<option value="'.$j.'"';
			if ($row_Rs_emploi['heure']==$j) {
				echo " selected"; $h2 = substr($row_Rs_emploi['heure_fin'],0,2); $mn2 = substr($row_Rs_emploi['heure_fin'],3,2);
			} else {
				$h2 = $row_Rsplage_all['h2']; $mn2 = $row_Rsplage_all['mn2'];
			};
			echo'>';
			if ($j<10){echo '0';};
			echo $j.'&nbsp;&nbsp;&nbsp;&nbsp;(&nbsp;'.$row_Rsplage_all['h1'].'h'.$row_Rsplage_all['mn1'].' - '.$h2.'h'.$mn2.'&nbsp;)</option>';
        		$j=$j+1;
        	}
        	while ($row_Rsplage_all = mysqli_fetch_assoc($Rsplage_all)); ?>
        	</select>
        	<script language="JavaScript" type="text/JavaScript">ShowPlagesModif();//Pour afficher les horaires de cette plage s'ils ont ete modifies</script>
        	&nbsp;&nbsp; </strong></div>
        	<div  align="left" id="plages" STYLE="float: left;"><strong>
        <?php }
        else { echo $row_Rs_emploi['heure'];}
        ?>
        </strong></div></td>
        </tr>
        <tr valign="baseline">
        <td nowrap align="right">Semaine :</td>
        <td><div align="left"><strong>
        <?php if ($totalRows_Rsagenda==0){ ?>
        	<select name="semaine" id="select14">
        	<option value="A et B" <?php if (!(strcmp("A et B", $row_Rs_emploi['semaine']))) {echo "SELECTED";} ?>>
        	<?php if ((isset($_SESSION['libelle_semaine']))&&($_SESSION['libelle_semaine']==1)){echo 'Paire et Impaire';} 
        	else {echo 'A et B';};?>
        	</option>
        	<option value="A" <?php if (!(strcmp("A", $row_Rs_emploi['semaine']))) {echo "SELECTED";} ?>>
        	<?php if ((isset($_SESSION['libelle_semaine']))&&($_SESSION['libelle_semaine']==1)){echo 'Semaine Paire';} 
        	else {echo 'A';};?>
        	</option>
        	<option value="B" <?php if (!(strcmp("B", $row_Rs_emploi['semaine']))) {echo "SELECTED";} ?>>
        	<?php if ((isset($_SESSION['libelle_semaine']))&&($_SESSION['libelle_semaine']==1)){echo 'Semaine Impaire';} 
        	else {echo 'B';};?>
        	</option>

        	</select>
			
				OU 	Autre(s) alternance(s)	: Sem 1<input name="s1" type="checkbox" id="s1" value="1" style="vertical-align:middle;"<?php 
if (strpos($row_Rs_emploi['semaine'],'1') !== FALSE) {echo " checked";};	?> >  
			Sem 2<input name="s2" type="checkbox" id="s2" value="2" style="vertical-align:middle;" <?php 
if (strpos($row_Rs_emploi['semaine'],'2') !== FALSE) {echo " checked";};	?>> 
			Sem 3<input name="s3" type="checkbox" id="s3" value="3" style="vertical-align:middle;" <?php 
if (strpos($row_Rs_emploi['semaine'],'3') !== FALSE) {echo " checked";};	?>> 
			Sem 4<input name="s4" type="checkbox" id="s4" value="4" style="vertical-align:middle;"<?php 
if (strpos($row_Rs_emploi['semaine'],'4') !== FALSE) {echo " checked";};	?> >
        <?php }
        else { echo $row_Rs_emploi['semaine'];}
        ?>
        </strong></div></td>
        </tr>
        <tr valign="baseline">
        <?php
        mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
        $query_RsHeurePartagee = sprintf("SELECT * FROM cdt_emploi_du_temps_partage WHERE ID_emploi=%u AND profpartage_ID=%u",
        	GetSQLValueString($_GET['ID_emploi'],"int"),
        	GetSQLValueString($_SESSION['ID_prof'],"int"));
        $RsHeurePartagee = mysqli_query($conn_cahier_de_texte, $query_RsHeurePartagee) or die(mysqli_error($conn_cahier_de_texte));
        $totalRows_RsHeurePartagee = mysqli_num_rows($RsHeurePartagee);
        mysqli_free_result($RsHeurePartagee);
	if ($totalRows_RsHeurePartagee!=0) { // Dans le cas ou l'heure a modifier est une heure partagee cree par un collegue
		?>
		<input type="hidden" name="classe_ID" value="<?php echo $row_Rs_emploi['classe_ID']; ?>">
		<input type="hidden" name="gic_ID" value="<?php echo $row_Rs_emploi['gic_ID']; ?>">
		<td nowrap align="right">Classe :</td>
		<td>
		<?php
		if ($row_Rs_emploi['classe_ID']!=0) { // C'est un classe
			mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
			$query_RsClassePartagee = sprintf("SELECT nom_classe FROM cdt_classe WHERE ID_classe=%u",$row_Rs_emploi['classe_ID']);
			$RsClassePartagee = mysqli_query($conn_cahier_de_texte, $query_RsClassePartagee) or die(mysqli_error($conn_cahier_de_texte));
			$row_RsClassePartagee = mysqli_fetch_assoc($RsClassePartagee);
			echo " ".$row_RsClassePartagee['nom_classe']; 
		} else { // C'est un regroupement
			mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
			$query_RsGICPartagee = sprintf("SELECT nom_gic,nom_classe FROM cdt_groupe_interclasses,cdt_groupe_interclasses_classe,cdt_classe WHERE ID_gic=%u AND ID_gic=gic_ID AND classe_ID=ID_classe ORDER BY nom_classe",$row_Rs_emploi['gic_ID']);
			$RsGICPartagee = mysqli_query($conn_cahier_de_texte, $query_RsGICPartagee) or die(mysqli_error($conn_cahier_de_texte));
			$row_RsGICPartagee = mysqli_fetch_assoc($RsGICPartagee);
			echo " Regroupement <strong>".$row_RsGICPartagee['nom_gic']."</strong> de plusieurs classes (".$row_RsGICPartagee['nom_classe'];
			while ($row_RsGICPartagee = mysqli_fetch_assoc($RsGICPartagee)) {
				echo " - ".$row_RsGICPartagee['nom_classe'];
                        };
                        echo ")";
                } 
                ?>              </td>
                <?php
        } else {
                if (($row_Rs_emploi['classe_ID']==0)&&($row_Rs_emploi['gic_ID']=='0')){ //Dans le cas d'un import d'EDT sans classe, sans regroupement
			?>
			<td nowrap align="right"><font color=red><b>Classe &agrave; v&eacute;rifier :</b></font></td>
			<?php
		} else {
			?>
			<td nowrap align="right">Classe :</td>
			<?php
		}
		
		?>
		<td><div align="left"><strong>
		<?php if ($totalRows_Rsagenda==0){ ?>
			<select name="classe_ID" id="classe" onchange="ShowRegroupement()">
			<?php
			do {
				?>
				<option value="<?php echo $row_RsClasse['ID_classe']?>"<?php if (!(strcmp($row_RsClasse['ID_classe'], $row_Rs_emploi['classe_ID']))) {echo " selected='selected' ";} ?>><?php echo $row_RsClasse['nom_classe']?></option>
				<?php
			} while ($row_RsClasse = mysqli_fetch_assoc($RsClasse));
			mysqli_free_result($RsClasse);
			
			if ($totalrows_Rsgic>0) {?>
				<option value="0"></option>
				<option value="0" style="font-weight: bold;" <?php if (($row_Rs_emploi['classe_ID']==0)&&($row_Rs_emploi['gic_ID']!=='0')) {echo " selected='selected' ";} ?>>Regroupement de classes</option>
				<option value="0"></option>
			<?php } ?>
			</select>
			<div  id='gic_ID' style='display:inline' align="left">
			<?php
			if (($row_Rs_emploi['classe_ID']==0)&&($row_Rs_emploi['gic_ID']!=='0')){ 
				echo "<select name='gic_ID'>";
				
				mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
				$query_Rsgic =sprintf("SELECT * FROM cdt_groupe_interclasses WHERE cdt_groupe_interclasses.prof_ID = %u ",GetSQLValueString($_SESSION['ID_prof'],"int"));
				$Rsgic = mysqli_query($conn_cahier_de_texte, $query_Rsgic) or die(mysqli_error($conn_cahier_de_texte));
				$row_Rsgic = mysqli_fetch_assoc($Rsgic);
				
				do { 
					echo "<option value='".$row_Rsgic['ID_gic']."'";
					if (!(strcmp($row_Rsgic['ID_gic'], $row_Rs_emploi['gic_ID']))) {echo "SELECTED";} 
					echo ">".$row_Rsgic['nom_gic']."</option>";
                                } while ($row_Rsgic = mysqli_fetch_assoc($Rsgic));
                                
                                echo "</select>";
                                ?>
                                <br />Fusionner &agrave; l'affichage les contenus de ce regroupement avec les autres contenus de la mati&egrave;re&nbsp;<input type="checkbox" name="fusion_gic" id="fusion_gic" value="" <?php if (!(strcmp($row_Rs_emploi['fusion_gic'],'O'))) {echo "checked=checked";} ?>>
                        <?php
                        } 
                        ?>
                        </div>
		<?php }
		else { echo $row_Rs_emploi['nom_classe'];}
		?>
		</strong></div></td>
	<?php } ?>
        </tr>
        <tr valign="baseline">
        <td nowrap align="right">Groupe :</td>
        <td><div align="left"><strong>
        <?php if ($totalRows_Rsagenda==0){ ?>
        	<select name="groupe" id="groupe">
        	<?php
        	do {  
        		?>
        		<option value="<?php echo $row_Rsgroupe['groupe']?>"<?php if (!(strcmp($row_Rsgroupe['groupe'], $row_Rs_emploi['groupe']))) {echo "SELECTED";} ?>><?php echo $row_Rsgroupe['groupe']?></option>
        		<?php
        	} while ($row_Rsgroupe = mysqli_fetch_assoc($Rsgroupe));
        	$rows = mysqli_num_rows($Rsgroupe);
        	if($rows > 0) {
        		mysqli_data_seek($Rsgroupe, 0);
        		$row_Rsgroupe = mysqli_fetch_assoc($Rsgroupe);
        	}
        	?>
        	</select>
        <?php }
        else { echo $row_Rs_emploi['groupe'];}
        ?>
        </strong></div></td>
        </tr>
        <tr valign="baseline">
        <td nowrap align="right">Mati&egrave;re :</td>
        <td><div align="left"><strong>
        <?php if ($totalRows_Rsagenda==0){ ?>
        	<select name="matiere_ID" id="select17">
        	<?php
        	do {  
        		?>
        		<option value="<?php echo $row_RsMatiere['ID_matiere']?>" <?php if (!(strcmp($row_RsMatiere['ID_matiere'], $row_Rs_emploi['matiere_ID']))) {echo "SELECTED";} ?>><?php echo $row_RsMatiere['nom_matiere']?></option>
        		<?php
        	} while ($row_RsMatiere = mysqli_fetch_assoc($RsMatiere));
        	$rows = mysqli_num_rows($RsMatiere);
        	if($rows > 0) {
        		mysqli_data_seek($RsMatiere, 0);
        		$row_RsMatiere = mysqli_fetch_assoc($RsMatiere);
        	}
        	?>
        	</select>
        <?php }
        else { echo $row_Rs_emploi['nom_matiere'];}
        ?>
        </strong></div></td>
        <tr>
	<?php if ($row_Rs_emploi['prof_ID']<>$refprof_Rs_emploi) {  //Le prof modifiant n'est pas le createur de la plage ?>
		<td valign='middle' align="right">
		<img src="../images/partage.gif" width="15" height="18" title="Cette plage est partag&eacute;e avec au moins un autre enseignant.">
		Cette heure est partag&eacute;e avec : </td>
		<td valign='middle'><ul><li>
		<?php
		mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
                $query_RsProfCreateur = sprintf("SELECT identite,nom_prof FROM cdt_prof WHERE ID_prof=%u",$row_Rs_emploi['prof_ID']);
                $RsProfCreateur = mysqli_query($conn_cahier_de_texte, $query_RsProfCreateur) or die(mysqli_error($conn_cahier_de_texte));
                $row_RsProfCreateur = mysqli_fetch_assoc($RsProfCreateur);
		if ($row_RsProfCreateur['identite']=="")  {echo $row_RsProfCreateur['nom_prof'];} else {echo $row_RsProfCreateur['identite'];};
		echo ' (son cr&eacute;ateur)';
		?></li>
		<?php 
		
		mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
                $query_RsPartage = sprintf("SELECT cdt_prof.identite,cdt_prof.nom_prof FROM cdt_prof,cdt_emploi_du_temps_partage WHERE ID_emploi=%u AND cdt_prof.ID_prof=cdt_emploi_du_temps_partage.profpartage_ID AND cdt_prof.ID_prof<>%u ORDER BY cdt_prof.nom_prof ASC",$_GET['ID_emploi'],$refprof_Rs_emploi);
                $RsPartage = mysqli_query($conn_cahier_de_texte, $query_RsPartage) or die(mysqli_error($conn_cahier_de_texte));
                $row_RsPartage = mysqli_fetch_assoc($RsPartage);
		$totalRows_RsPartage = mysqli_num_rows($RsPartage);
		if ($totalRows_RsPartage>0) {
			do {
				echo "<li>";
				if ($row_RsPartage['identite']=="")  {echo $row_RsPartage['nom_prof'];} else {echo $row_RsPartage['identite'];}
				echo "</li>";
			} while ($row_RsPartage = mysqli_fetch_assoc($RsPartage));
			mysqli_free_result($RsPartage);   
                }
                ?>
                
                </ul>          </td>
        <?php } else { ?>
                <td valign='middle' align="right">Heure partag&eacute;e : </td>
                <td valign='middle'>
		<?php
		mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
		$query_RsPartage = sprintf("SELECT * FROM cdt_emploi_du_temps_partage WHERE ID_emploi=%u",GetSQLValueString($_GET['ID_emploi'],"int"));
		$RsPartage = mysqli_query($conn_cahier_de_texte, $query_RsPartage) or die(mysqli_error($conn_cahier_de_texte));
		$totalRows_RsPartage = mysqli_num_rows($RsPartage);
		mysqli_free_result($RsPartage);   
		?>
		<input name="partage" type="checkbox" id="partage" value="partage" <?php if ($totalRows_RsPartage>0) {echo "checked='checked'";}; ?> onClick="PartageShow();">
		<div id="cachepartage"  <?php if ($totalRows_RsPartage==0) {echo 'style="display:none"';}; ?> align="center">
		<select multiple="multiple" name="partage_ID[]" id="partage_ID" size=5>
		<?php
		mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
		$query_RsProf = sprintf("SELECT * FROM cdt_prof WHERE droits='2' AND ID_prof<>%u ORDER BY nom_prof ASC",$_SESSION['ID_prof']);
		$RsProf = mysqli_query($conn_cahier_de_texte, $query_RsProf) or die(mysqli_error($conn_cahier_de_texte));
		$row_RsProf = mysqli_fetch_assoc($RsProf);
		
		do {  
			mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
			$query_RsPartage = sprintf("SELECT * FROM cdt_emploi_du_temps_partage WHERE ID_emploi=%u AND profpartage_ID=%u",$_GET['ID_emploi'],$row_RsProf['ID_prof']);
			$RsPartage = mysqli_query($conn_cahier_de_texte, $query_RsPartage) or die(mysqli_error($conn_cahier_de_texte));
			$totalRows_RsPartage = mysqli_num_rows($RsPartage);
			mysqli_free_result($RsPartage);   
			?>
			<option value="<?php echo $row_RsProf['ID_prof']?>"<?php if ($totalRows_RsPartage>0) {echo "selected='selected'";}; ?>><?php if ($row_RsProf['identite']=="")  {echo $row_RsProf['nom_prof'];} else {echo $row_RsProf['identite'];}?></option>
			<?php
                } while ($row_RsProf = mysqli_fetch_assoc($RsProf));
                ?>
                </select>
                </div>          </td>
        <?php } ?>
        </tr>

        <tr valign="baseline">
        <td nowrap align="right">Couleur de fond de la plage horaire (facultatif) <a href="#" class="tooltip">Aide <em><span></span>Si laiss&eacute; vide, les cellules sont vertes par d&eacute;faut. <br/>
        Il peut &ecirc;tre n&eacute;cessaire cependant de mat&eacute;rialiser <br/>
        une cellule pour la mettre en &eacute;vidence <br/>
        dans son emploi du temps (heure de vie de classe par exemple). <br/>
        Attention &agrave; ne pas utiliser la couleur bleu/gris&eacute; utilis&eacute;e <br>
        dans l'application pour mettre en &eacute;vidence les cellules <br>
        relatives aux s&eacute;ances d&eacute;j&agrave; remplies.</em></em></a>: </td>
        <td><div align="left"><strong>
        <input type="text" size="10" name="couleur_cellule" value="<?php echo $row_Rs_emploi['couleur_cellule']?>" maxlength="7" style="font-family:Tahoma;font-size:x-small;background-color:<?php if($row_Rs_emploi['couleur_cellule']==""){echo '#CAFDBD';} else {echo $row_Rs_emploi['couleur_cellule'];}?>">
        <img src="../images/color.gif" width="21" height="20" border="0" align="absmiddle" onClick="fctShow(document.form1.couleur_cellule);" style="cursor:pointer;"></strong>
        </div></td>
        </tr>
        <tr valign="baseline">
        <td align="right" nowrap>Couleur de la police du nom de la classe (facultatif) <a href="#" class="tooltip">Aide <em><span></span>Il peut &ecirc;tre n&eacute;cessaire de mettre en &eacute;vidence <br/>
        le nom d'une classe dans son emploi du temps <br/>
        (heure de vie de classe par exemple).</em></a>: </td>
        <td><div align="left"><strong>
        <input type="text" size="10" name="couleur_police" value="<?php echo $row_Rs_emploi['couleur_police']?>" maxlength="7" style="font-family:Tahoma;font-size:x-small;background-color:<?php if($row_Rs_emploi['couleur_police']==""){echo '#000000';} else {echo $row_Rs_emploi['couleur_police'];}?>">
        <img src="../images/color.gif" width="21" height="20" border="0" align="absmiddle" onClick="fctShow(document.form1.couleur_police);" style="cursor:pointer;"> </strong></div></td>
        </tr>
        <tr>
        <td nowrap align="right">Existence de la plage horaire (facultatif) <a href="#" class="tooltip">Aide <em><span></span>Si laiss&eacute par d&eacute;faut,<br/>
        la plage horaire existera pendant toute l'ann&eacute;e scolaire.<br/>
        Vous pouvez utiliser ces param&egrave;tres suite &agrave; <br/>
        une modification d'emploi du temps en cours d'ann&eacute;e.<br/>
        .</em></a>: </td>
        <td><div STYLE="float: left" align="left"><strong>
        <script>
        $(function() {
        $('selector').datepicker($.datepicker.regional['fr']);
        var dates = $( "#edt_exist_debut, #edt_exist_fin" ).datepicker({
        defaultDate: "+1w",
        changeMonth: true,
        numberOfMonths: 1,
        onSelect: function( selectedDate ) {
        var option = this.id == "edt_exist_debut" ? "minDate" : "maxDate",
        instance = $( this ).data( "datepicker" ),
        date = $.datepicker.parseDate(
        instance.settings.dateFormat ||
        $.datepicker._defaults.dateFormat,
        selectedDate, instance.settings );
        dates.not( this ).datepicker( "option", option, date );
        }
        });
        });
        </script>
        <?php 
if ($_SESSION['id_etat']==2){
        $datedebut=substr($row_Rs_emploi['edt_exist_debut'],8,2)."/".substr($row_Rs_emploi['edt_exist_debut'],5,2)."/".substr($row_Rs_emploi['edt_exist_debut'],0,4);
        echo $datedebut; ?>
        <input type='hidden' name='edt_exist_debut' type='text' class="curseur_pointe"  id='edt_exist_debut' value="<?php echo $datedebut;?>" size="10"/>
        <?php
        }
        else
	{	
                
                $today_form=date('d/m/Y');
        //$date_fin="01/01/2020" initialisation date debut et date fin annee 01/09 au 30/06
                        //initialisation date de debut
                
                        $query_read = "SELECT `param_val` FROM `cdt_params` WHERE `param_nom`='date_debut_annee' LIMIT 1;";
        $result_read = mysqli_query($conn_cahier_de_texte, $query_read);
        $row = mysqli_fetch_row($result_read);
        $date_deb = $row[0];
        mysqli_free_result($result_read);
        
                //initialisation date fin annee (avant modif, au 13/07)
                        $query_read = "SELECT `param_val` FROM `cdt_params` WHERE `param_nom`='date_fin_annee' LIMIT 1;";
        $result_read = mysqli_query($conn_cahier_de_texte, $query_read);
        $row = mysqli_fetch_row($result_read);
        $date_fin = $row[0];
        mysqli_free_result($result_read);
        
        //Cas ou les dates n'ont pas ete saisies via le menu administrateur
if($date_deb ==''){
// Modifie depuis la version 4.4.0 - On prend la date de debut d'annee plutot que la date du jour
        if(date('n') >= 8 && date('n') <=12){$date_deb="01/09/".date('Y');} else {$date_deb="01/09/".(date('Y')-1);};
};
if($date_fin ==''){
//initialisation date fin annee au 13/07
        if(date('n') >= 8 && date('n') <=12){$date_fin="13/07/".(date('Y')+1);} else {$date_fin="13/07/".date('Y');};
};


        
        if(checkdate(substr($row_Rs_emploi['edt_exist_debut'],5,2), substr($row_Rs_emploi['edt_exist_debut'],8,2), substr($row_Rs_emploi['edt_exist_debut'],0,4))) {
                echo "<input name='edt_exist_debut' type='text' class='curseur_pointe'  id='edt_exist_debut' value='";
        	if(isset($_POST['edt_exist_debut'])){
        		echo $_POST['edt_exist_debut'];
        	} else {
        		echo substr($row_Rs_emploi['edt_exist_debut'],8,2)."/".substr($row_Rs_emploi['edt_exist_debut'],5,2)."/".substr($row_Rs_emploi['edt_exist_debut'],0,4);
        	}
        	echo "' size='10'/>";
        } else {
        	echo "<input name='edt_exist_debut' type='text' class='curseur_pointe'  id='edt_exist_debut' value='";
        	if(isset($_POST['edt_exist_debut'])){
        		echo $_POST['edt_exist_debut'];
        	} else {
        		echo $date_deb;
        	}
        	echo "' size='10'/>";
        }
        
    }   
		?>
        &nbsp;au&nbsp;&nbsp;
        <?php 
        if(checkdate(substr($row_Rs_emploi['edt_exist_fin'],5,2), substr($row_Rs_emploi['edt_exist_fin'],8,2), substr($row_Rs_emploi['edt_exist_fin'],0,4))) {
        	echo "<input name='edt_exist_fin'  type='text' class='curseur_pointe'  id='edt_exist_fin' value='";
        	if(isset($_POST['edt_exist_fin'])){
        		echo $_POST['edt_exist_fin'];
        	} else {
        		echo substr($row_Rs_emploi['edt_exist_fin'],8,2)."/".substr($row_Rs_emploi['edt_exist_fin'],5,2)."/".substr($row_Rs_emploi['edt_exist_fin'],0,4);
        	}
        	echo "' size='10'/>";
        } else {
        	echo "<input name='edt_exist_fin' type='text' class='curseur_pointe'  id='edt_exist_fin' value='";
        	if(isset($_POST['edt_exist_fin'])){
        		echo $_POST['edt_exist_fin'];
        	} else {
        		echo $date_fin;
        	}
        	echo "' size='10'/>";
        }
        ?>
        </strong></div>
        <div id="plages"  align="left"> <span class='erreur'>
        <?php 
        if (isset($message_erreur_date)){echo $message_erreur_date;};
        ?>
        </span></div></td>
        </tr>
        <tr valign="baseline">
        <td nowrap align="right">&nbsp;</td>
        <td><p align="left">&nbsp; </p>
        <p align="left"><strong>
        <input type="submit" value="Mettre &agrave; jour l'emploi du temps">
        </strong></p>
        <p align="left">&nbsp;</p>
        <?php   if ($totalRows_Rsagenda==0){ ?>
        	<div align="left" style="cursor:pointer" onClick= "return confirmation('<?php echo $row_Rs_emploi['ID_emploi'];?>','<?php echo $row_Rs_emploi['jour_semaine'];?>','<?php echo $row_Rs_emploi['heure_debut'];?>');return document.MM_returnValue;
        	"><img src="../images/ed_delete.gif" width="11" height="13" 
        	>&nbsp; <span style="font-size: 8pt; color: #0000FF;">Supprimer cette plage horaire</span> </div>
        <?php };?>
        <p>&nbsp;</p></td>
        </tr>
        </table>
        <p>
        <input type="hidden" name="MM_update" value="form1">
        <input type="hidden" name="profID" value="<?php echo $row_Rs_emploi['prof_ID']; ?>">
        <input type="hidden" name="ID_emploi" value="<?php echo $row_Rs_emploi['ID_emploi']; ?>">
        <input type="hidden" name="update_limite" value="<?php if ($totalRows_Rsagenda==0){echo '0';}else{echo '1';}?>">
        </p>
<p align="center"><a href="emploi.php<?php echo "?ID_prof=".$row_Rs_emploi['prof_ID'];if (isset($_GET['affiche'])){echo '&affiche='.$_GET['affiche'];}?>">Retour &agrave; la gestion de mon emploi du temps </a></p>
        </fieldset>
        </div>
        </form>
        </body>
        </html>
        <?php
        mysqli_free_result($Rs_emploi);
        mysqli_free_result($RsMatiere);
        mysqli_free_result($Rsgroupe);
        mysqli_free_result($Rsgic);
}
?>
