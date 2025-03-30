<?php 
include "../authentification/authcheck.php" ;
if (($_SESSION['droits']<>2)&&($_SESSION['droits']<>8)) { header("Location: ../index.php");}
else {
	header('Content-type: text/html; charset=iso-8859-1');
};

require_once('../Connections/conn_cahier_de_texte.php');
require_once('../inc/functions_inc.php');
require_once('../inc/module_absence_couleur.php');

//nb de colonne maxi en affichage standard 
$maxcol=6;
$sql_affiche='';

$editFormAction = '#';
if (isset($_SERVER['QUERY_STRING'])) {
	$editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}
$datetoday=date('y-m-d');
$madate=substr($_GET['code_date'],0,8);
$jourtoday= jour_semaine(date('d/m/Y'));



if ((isset($_GET['type_affiche']))&&($_GET['type_affiche']==1)){
	//uniquement la colonne de saisie
	$sql_affiche = 'AND heure_debut="'.$_GET['heure_debut']. '"' ;
};
if ((!isset($_GET['type_affiche']))||($_GET['type_affiche']==2)){
	//standard
	$sql_affiche = 'AND heure_debut<="'.$_GET['heure_debut']. '"' ;
};

if ((isset($_GET['type_affiche']))&&($_GET['type_affiche']==3)){
	//afficher tout
	$maxcol=40;
}



//Envoi en vie scolaire
if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1") ) {

	if (isset($_POST['createur_heure_part_ID'])){$prof_declarant=$_POST['createur_heure_part_ID'];} else {$prof_declarant=$_SESSION['ID_prof'];};
	
	// on vide les absents
	$deleteSQL = sprintf("DELETE FROM ele_absent WHERE classe_ID=%u AND groupe=%s AND prof_ID=%u AND heure=%s AND heure_debut=%s AND code_date=%s ",
		GetSQLValueString($_GET['classe_ID'], "int"),
		GetSQLValueString($_GET['groupe'], "text"),
		GetSQLValueString($prof_declarant, "int"),
		GetSQLValueString($_GET['heure'], "int"),
		GetSQLValueString($_GET['heure_debut'], "text"),
		GetSQLValueString($_GET['code_date'], "text")
		);
	
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$Result1 = mysqli_query($conn_cahier_de_texte, $deleteSQL) or die(mysqli_error($conn_cahier_de_texte));
	
	$req = "SELECT min(ID_ele) AS min, max(ID_ele) AS max FROM ele_liste;"; 
	$res = mysqli_query($conn_cahier_de_texte, $req) or die(mysqli_error($conn_cahier_de_texte)); 
	$row = mysqli_fetch_assoc($res); 
	$nblign=(int) $row['max'];
	$nb_insert=0;
	
	
	for ($i=(int) $row['min']; $i<=$nblign; $i++) { 
		
		
		$p=$i.'motif';
		$retard='R_'.$i;
		$i_statut=$i.'statut';
		
		$perso1='A_'.$i;
		$perso2='B_'.$i;
		$perso3='C_'.$i;
		
		if (isset($_POST[$i]) || isset($_POST[$p]) || isset($_POST[$retard]) || isset($_POST[$perso1]) || isset($_POST[$perso2])|| isset($_POST[$perso3])){
			
			if ((isset($_POST[$retard]))||(isset($_POST[$perso1]))||(isset($_POST[$perso2]))||(isset($_POST[$perso3]))){$_POST[$i]='off';};
			
			if ( !empty($_POST[$i] )) {
				$nom_classe = "";
				if ( $_GET['gic_ID'] != "0" ) {
					
					$nameClasseSQL = sprintf("SELECT ID_classe, nom_classe FROM ele_liste,cdt_classe WHERE ele_liste.classe_ele COLLATE latin1_swedish_ci=cdt_classe.code_classe COLLATE latin1_swedish_ci AND ele_liste.ID_ele=%u LIMIT 1;", 
						GetSQLValueString($i, "int") 
						);
					mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
					$nameClasseResult = mysqli_query($conn_cahier_de_texte, $nameClasseSQL) or die(mysqli_error($conn_cahier_de_texte));
					$row_nameClasseResult = mysqli_fetch_assoc($nameClasseResult);
					$nom_classe = "Regroupement";
				} else {
					$nom_classe = $_GET['nom_classe'] ;
				}
				
				$retard='R_'.$i;
				$perso1='A_'.$i;
				$perso2='B_'.$i;
				$perso3='C_'.$i;
				
				if ((isset($_POST[$retard]))&&($_POST[$retard]=='on')){$retard_val='O';} else {$retard_val='N';};
				if ((isset($_POST[$perso1]))&&($_POST[$perso1]=='on')){$perso1_val='O';} else {$perso1_val='N';};
				if ((isset($_POST[$perso2]))&&($_POST[$perso2]=='on')){$perso2_val='O';} else {$perso2_val='N';};
				if ((isset($_POST[$perso3]))&&($_POST[$perso3]=='on')){$perso3_val='O';} else {$perso3_val='N';};
				
			
				
				
				$insertSQL = sprintf("INSERT INTO `ele_absent` (classe_ID,classe,groupe,heure,heure_debut,heure_fin,code_date,jour_pointe,eleve_ID,prof_ID,salle,motif,vie_sco_statut,retard,perso1,perso2,perso3) VALUES (%u,%s,%s,%u,%s,%s,%s,%s,%u,%u,%s,%s,%u,%s,%s,%s,%s)", 
					
					GetSQLValueString($_GET['classe_ID'], "int"),
					GetSQLValueString($nom_classe, "text"),
					GetSQLValueString($_GET['groupe'], "text"),
					GetSQLValueString($_GET['heure'], "int"),
					GetSQLValueString($_GET['heure_debut'], "text"),
					GetSQLValueString($_GET['heure_fin'], "text"),
					GetSQLValueString($_GET['code_date'], "text"),
					GetSQLValueString(urlencode($_GET['jour_pointe']), "text"),
					GetSQLValueString($i, "int"),
					GetSQLValueString($prof_declarant, "int"),
					GetSQLValueString($_POST['salle'], "text"),
					GetSQLValueString($_POST[$p], "text"),
					GetSQLValueString($_POST[$i_statut], "int"),
					GetSQLValueString($retard_val, "text"),
					GetSQLValueString($perso1_val, "text"),
					GetSQLValueString($perso2_val, "text"),
					GetSQLValueString($perso3_val, "text")
					);
				mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
				$Result = mysqli_query($conn_cahier_de_texte, $insertSQL) or die(mysqli_error($conn_cahier_de_texte));
				if (($perso1_val!='N')&&($perso2_val=='N')&&($perso3_val=='N')) {$nb_insert=$nb_insert+1;};
				
			}
		}
	}
	mysqli_free_result($res);
	
	//pas d'absent - on cree un enregistrement pour un eleve fictif avec elev_id=0
	
	if ((isset($_POST['pasdabsent']))&&($_POST['pasdabsent']=='on')){
		if ($_GET['classe_ID']==0){$nom_classe = "Regroupement"; } else { $nom_classe = $_GET['nom_classe'] ; }
		$insertSQL = sprintf("INSERT INTO `ele_absent` (classe_ID,classe,groupe,heure,heure_debut,heure_fin,code_date,jour_pointe,eleve_ID,prof_ID,salle) VALUES (%u,%s,%s,%u,%s,%s,%s,%s,%u,%u,%s)",
			
			GetSQLValueString($_GET['classe_ID'], "int"),
			GetSQLValueString($nom_classe, "text"),
			GetSQLValueString($_GET['groupe'], "text"),
			GetSQLValueString($_GET['heure'], "int"),
			GetSQLValueString($_GET['heure_debut'], "text"),
			GetSQLValueString($_GET['heure_fin'], "text"),
			GetSQLValueString($_GET['code_date'], "text"),
			GetSQLValueString(urlencode($_GET['jour_pointe']), "text"),
			0,
			GetSQLValueString($prof_declarant, "int"),
			GetSQLValueString($_POST['salle'], "text")
			);
		
		mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
		$Result = mysqli_query($conn_cahier_de_texte, $insertSQL) or die(mysqli_error($conn_cahier_de_texte));
		
	};
	
	//sms vie scolaire
	
	if ((isset($_POST['sms_vie_sco']))&&($_POST['sms_vie_sco']<>'')&&($_POST['sms_vie_sco']<>'Ecrire un SMS pour la vie scolaire...')){
		$classe_ID=$_GET['classe_ID'];
		$groupe_ID=$_POST['gic_ID'];
		$insertSQL = sprintf("INSERT INTO cdt_message_contenu (message, prof_ID, date_envoi, date_fin_publier,online,dest_ID,pp_classe_ID,pp_groupe_ID)
			VALUES (%s,%u,NOW(),'0000-00-00','O',3,%u,%u)",
			GetSQLValueString($_POST['sms_vie_sco'], "text"),
			GetSQLValueString($_SESSION['ID_prof'], "int"),
			GetSQLValueString($classe_ID, "int"),
			GetSQLValueString($groupe_ID, "int")
			);
		mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
		$Result = mysqli_query($conn_cahier_de_texte, $insertSQL) or die(mysqli_error($conn_cahier_de_texte));
	}
	
	
	?>
	<SCRIPT language=javascript>
	alert('La liste des absents et autres consignes ont \351t\351 envoy\351es en vie scolaire');
	function fermer(){
		var obj_window = window.open('', '_self');
		obj_window.opener = window;
		obj_window.focus();
		opener=self;
		self.close();
	}
	fermer();
	</SCRIPT>
	<?php
}

// Gestion des semaines A et B
if ( $_SESSION['semdate'] == "A" ) {
	$semdate_exclusion = "B";
} else if ( $_SESSION['semdate'] == "B" ) {
	$semdate_exclusion = "A";
} else {
	$semdate_exclusion = NULL;
}


if ($_GET['classe_ID'] != "0" ) { 
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$query_Rslibelle = sprintf("SELECT code_classe FROM cdt_classe WHERE ID_Classe = %u", GetSQLValueString($_GET['classe_ID'], "int") );
	$Rslibelle = mysqli_query($conn_cahier_de_texte, $query_Rslibelle) or die(mysqli_error($conn_cahier_de_texte));
	$row_libelle = mysqli_fetch_assoc($Rslibelle);
	
	if ($_GET['groupe']=='Classe entière'){ 
		$query_Rseleves = sprintf("SELECT * FROM ele_liste WHERE classe_ele COLLATE latin1_swedish_ci='%s' COLLATE latin1_swedish_ci ORDER BY nom_ele",$row_libelle['code_classe']);
	} else {
		//avec la gestion de groupes
		// selection des eleves du groupe dans ele liste
		$query_Rseleves = sprintf("SELECT * FROM ele_liste WHERE groupe_ele COLLATE latin1_swedish_ci='%s' COLLATE latin1_swedish_ci AND classe_ele COLLATE latin1_swedish_ci='%s' COLLATE latin1_swedish_ci ORDER BY nom_ele",$_GET['groupe'],$row_libelle['code_classe']);
		mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
		$Rseleves = mysqli_query($conn_cahier_de_texte, $query_Rseleves) or die(mysqli_error($conn_cahier_de_texte));
		$row_Rseleves = mysqli_fetch_assoc($Rseleves);
		$totalRows_Rseleves = mysqli_num_rows($Rseleves);
		
		if ((isset($totalRows_Rseleves))&&($totalRows_Rseleves==0)){  //pas de groupe defini - on prend la classe entiere - 
		$query_Rseleves = sprintf("SELECT * FROM ele_liste WHERE classe_ele COLLATE latin1_swedish_ci='%s' COLLATE latin1_swedish_ci ORDER BY nom_ele",$row_libelle['code_classe']);}
		else {
			$query_Rseleves = sprintf("SELECT * FROM ele_liste WHERE groupe_ele COLLATE latin1_swedish_ci='%s' COLLATE latin1_swedish_ci AND classe_ele COLLATE latin1_swedish_ci='%s' COLLATE latin1_swedish_ci ORDER BY nom_ele",$_GET['groupe'],$row_libelle['code_classe']);
		};
		
	};
	mysqli_free_result($Rslibelle);
	
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$Rseleves = mysqli_query($conn_cahier_de_texte, $query_Rseleves) or die(mysqli_error($conn_cahier_de_texte));
	$row_Rseleves = mysqli_fetch_assoc($Rseleves);
	$totalRows_Rseleves = mysqli_num_rows($Rseleves);
	
	if (!is_null($semdate_exclusion) ) {
		$query_Rs_emploi = sprintf("SELECT * FROM cdt_emploi_du_temps,cdt_prof WHERE ID_prof=prof_ID AND cdt_emploi_du_temps.classe_ID=%u AND cdt_emploi_du_temps.jour_semaine='%s' AND '%s'<= edt_exist_fin AND semaine!='%s' %s ORDER BY cdt_emploi_du_temps.heure_debut", $_GET['classe_ID'],$_GET['current_day_name'],date('Y-m-d') ,$semdate_exclusion,$sql_affiche );
	} else {
		$query_Rs_emploi = sprintf("SELECT * FROM cdt_emploi_du_temps,cdt_prof WHERE ID_prof=prof_ID AND cdt_emploi_du_temps.classe_ID=%u AND cdt_emploi_du_temps.jour_semaine='%s' AND '%s'<= edt_exist_fin %s ORDER BY cdt_emploi_du_temps.heure_debut", $_GET['classe_ID'],$_GET['current_day_name'],date('Y-m-d'),$sql_affiche);
	}
	
	
} elseif ( $_GET['gic_ID'] != "0" ) {// Il s'agit d'un REGROUPEMENT
	
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$query_Rsclasses_gic = sprintf("SELECT * FROM cdt_groupe_interclasses_classe WHERE gic_ID='%u'", GetSQLValueString($_GET['gic_ID'], "int"));
	$Rsclasses_gic = mysqli_query($conn_cahier_de_texte, $query_Rsclasses_gic) or die(mysqli_error($conn_cahier_de_texte));
	$row_Rsclasses_gic = mysqli_fetch_assoc($Rsclasses_gic);
	
	
	$classe_gic = array();
	array_push( $classe_gic , 0 );
	@mysqli_data_seek($Rsclasses_gic,0) ;
	while (($row_rq = mysqli_fetch_array($Rsclasses_gic , MYSQLI_ASSOC) )) {  
		array_push( $classe_gic , $row_rq['classe_ID'] );
	}
	$in_classe_gic = join(" ,", $classe_gic) ;
	mysqli_free_result($Rsclasses_gic);
	
	//supprimer le "0,"
	$in_classe_gic=substr($in_classe_gic,3,strlen($in_classe_gic));
	
	
	$query_Rseleves_gic = sprintf("SELECT * FROM ele_gic WHERE ele_gic.ID_gic='%u'", GetSQLValueString($_GET['gic_ID'], "int"));
	$Rseleves_gic = mysqli_query($conn_cahier_de_texte, $query_Rseleves_gic) or die(mysqli_error($conn_cahier_de_texte));
	$row_Rseleves_gic = mysqli_fetch_assoc($Rseleves_gic);
	
	
	$ele_gic = array();
	if ( @mysqli_data_seek($Rseleves_gic,0) ) { 
		while (($row_rq = mysqli_fetch_array($Rseleves_gic , MYSQLI_ASSOC) )) {  
			array_push( $ele_gic , $row_rq['ID_ele'] );
		}
		$in_ele_gic = join(" ,", $ele_gic) ;
	} else {
		die('D&eacute;finissez les &eacute;l&egrave;ves du regroupement. <a href="groupe_interclasses_ajout.php">ici</a>');
	};
	
	mysqli_free_result($Rseleves_gic);
	
	//listes des eleves
	$query_Rseleves = sprintf("SELECT * FROM ele_liste WHERE ele_liste.ID_ele IN ( %s ) ORDER BY nom_ele",$in_ele_gic);
	$Rseleves = mysqli_query($conn_cahier_de_texte, $query_Rseleves) or die(mysqli_error($conn_cahier_de_texte));
	$row_Rseleves = mysqli_fetch_assoc($Rseleves);
	$totalRows_Rseleves = mysqli_num_rows($Rseleves);
	
	
	
	if (!is_null($semdate_exclusion) ) {
		
		$query_Rs_emploi = sprintf("SELECT * FROM cdt_emploi_du_temps,cdt_prof WHERE ID_prof=prof_ID AND (cdt_emploi_du_temps.classe_ID IN (%s) OR (cdt_emploi_du_temps.classe_ID =0 AND cdt_emploi_du_temps.gic_ID=%u)) AND cdt_emploi_du_temps.jour_semaine='%s' AND '%s'<= edt_exist_fin AND semaine!='%s' %s ORDER BY cdt_emploi_du_temps.heure_debut", $in_classe_gic, GetSQLValueString($_GET['gic_ID'], "int") ,$_GET['current_day_name'] ,date('Y-m-d'),$semdate_exclusion,$sql_affiche );
	} else {
		
		$query_Rs_emploi = sprintf("SELECT * FROM cdt_emploi_du_temps,cdt_prof WHERE ID_prof=prof_ID AND (cdt_emploi_du_temps.classe_ID IN (%s) OR (cdt_emploi_du_temps.classe_ID =0 AND cdt_emploi_du_temps.gic_ID=%u)) AND 
			cdt_emploi_du_temps.jour_semaine='%s' AND '%s'<= edt_exist_fin %s ORDER BY cdt_emploi_du_temps.heure_debut", $in_classe_gic, GetSQLValueString($_GET['gic_ID'], "int"),$_GET['current_day_name'],date('Y-m-d'),$sql_affiche);
	}
}

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$Rs_emploi = mysqli_query($conn_cahier_de_texte, $query_Rs_emploi) or die(mysqli_error($conn_cahier_de_texte));
$row_Rs_emploi = mysqli_fetch_assoc($Rs_emploi);
$totalRows_Rs_emploi = mysqli_num_rows($Rs_emploi);
$a=1;$pos=0;

//Affectations
if ($totalRows_Rs_emploi>0){
	do {

	
		if (($_GET['heure']==$row_Rs_emploi['heure'])&&($_GET['heure_debut']==$row_Rs_emploi['heure_debut'])&&($_SESSION['ID_prof']==$row_Rs_emploi['ID_prof'])){$pos=$a;};
		
		
		$hdeb[$a]=$row_Rs_emploi['heure_debut'];
		$hfin[$a]=$row_Rs_emploi['heure_fin'];
		$gr[$a]=$row_Rs_emploi['groupe'];
		
		

				
				// cette heure est-elle partagee ?
				$colpart[$a]=0;
				mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
				$query_RsPart=sprintf("SELECT cdt_emploi_du_temps_partage.ID_emploi,cdt_emploi_du_temps_partage.profpartage_ID,cdt_emploi_du_temps.prof_ID FROM cdt_emploi_du_temps_partage,cdt_emploi_du_temps WHERE cdt_emploi_du_temps.ID_emploi=%u AND cdt_emploi_du_temps_partage.ID_emploi=%u",$row_Rs_emploi['ID_emploi'],$row_Rs_emploi['ID_emploi']);
				$RsPart = mysqli_query($conn_cahier_de_texte, $query_RsPart) or die(mysqli_error($conn_cahier_de_texte));
				$row_RsPart = mysqli_fetch_assoc($RsPart);
				$totalRows_RsPart = mysqli_num_rows($RsPart);

				
				if ($totalRows_RsPart==0){	//heure non partagee
					$idpc[$a]=$row_Rs_emploi['ID_prof'];; //$idpc est le createur de l'heure paratagee
				}
				else {
				//heure partagee
				$idpc[$a]=$row_RsPart['prof_ID'];
				$colpart[$a]=1;
				};
		
		
		//on determine pos : le numero de la colonne de pointage
		if (($_GET['heure']==$row_Rs_emploi['heure'])&&($_GET['heure_debut']==$row_Rs_emploi['heure_debut'])&&($_GET['groupe']==$row_Rs_emploi['groupe'])&&($idpc[$a]==$row_Rs_emploi['ID_prof'])){
		

			
		$pos=$a;$ident[$a]=$_SESSION['identite'];
		} 
		else { 
		$ident[$a]=$row_Rs_emploi['identite'];
		};

		$numcol[$a]=$a;
		$a=$a+1;
	}while ($row_Rs_emploi = mysqli_fetch_assoc($Rs_emploi)) ;
};




//heure modifiee ponctuellement
if((isset($_GET['edt_modif']))&&($_GET['edt_modif']=='O')){
	$sql_edt_modif=sprintf(" UNION 
		SELECT *
		FROM cdt_agenda, cdt_prof
		WHERE substring( code_date, 1, 9 )=%s
		AND cdt_agenda.classe_ID=%u
		AND cdt_agenda.edt_modif='O'
		AND cdt_prof.ID_prof = cdt_agenda.prof_ID"
		,$_GET['code_date'],$_GET['classe_ID']);
} else {$sql_edt_modif='';};

//recherche de Devoirs planifies ou Heures supplementaires 
$query_RsDs = sprintf(" 
	SELECT *
	FROM cdt_agenda, cdt_prof
	WHERE substring( code_date, 9, 1 )=0
	AND substring( code_date, 1, 8 )=%s
	
	AND cdt_agenda.classe_ID =%u
	AND cdt_prof.ID_prof = cdt_agenda.prof_ID
	%s
	GROUP BY cdt_agenda.heure
	ORDER BY heure_debut"
	,substr($_GET['code_date'],0,8),$_GET['classe_ID'],$sql_edt_modif);

$RsDs = mysqli_query($conn_cahier_de_texte, $query_RsDs) or die(mysqli_error($conn_cahier_de_texte));
$row_RsDs = mysqli_fetch_assoc($RsDs);
$totalRows_RsDs = mysqli_num_rows($RsDs);

if ($totalRows_RsDs>0){
	do {
		$hdeb[$a]=$row_RsDs['heure_debut'];
		$hfin[$a]=$row_RsDs['heure_fin'];
		$gr[$a]=$row_RsDs['groupe'];
		$ident[$a]=$row_RsDs['identite'];
		$idpc[$a]=$row_RsDs['ID_prof'];
		$gicid[$a]=$row_RsDs['gic_ID'];
		$typac[$a]=$row_RsDs['type_activ'];
		$numcol[$a]=$a;
	
if (($_GET['heure']==$row_RsDs['heure'])&&($_GET['heure_debut']==$row_RsDs['heure_debut'])&&($_GET['groupe']==$row_RsDs['groupe'])&&($idpc[$a]==$row_RsDs['ID_prof'])){	$pos=$a;};	
		$a=$a+1;
	} while ($row_RsDs = mysqli_fetch_assoc($RsDs));
}
mysqli_free_result($RsDs);




?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Cahier de textes -<?php echo $_SESSION['identite']?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="../styles/style_default.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="../jscripts/utils.js"></script>
<script type="text/javascript" src="../jscripts/jquery-1.6.2.js"> </script>
<script type="text/javascript" src="../jscripts/jquery-ui-1.8.13.custom.min.js"></script>
<style>
form{
	margin:5;
	padding:0;
}
.rouge {color:#FF0000}
.selected td {
	background-color: #48507B;
	color: #FFFFFF;
}
</style>
</head>
<body >
<script type="text/JavaScript">
function MM_goToURL() { //v3.0
	var i, args=MM_goToURL.arguments; document.MM_returnValue = false;
	for (i=0; i<(args.length-1); i+=2) eval(args[i]+".location='"+args[i+1]+"'");
};

function absent(IDabsent) {
	document.getElementById('R_'+IDabsent).checked=false;
	document.getElementById('A_'+IDabsent).checked=false;
	document.getElementById('B_'+IDabsent).checked=false;
	document.getElementById('C_'+IDabsent).checked=false;
	document.getElementById('pasdabsent').checked=false;
};


$(document).ready(function() {
		$('#rowclick5 tr ')			// Selection des lignes des absents...
		.filter(':has(:checkbox:checked)')	// .. contenant des checkbox cochees...
		.addClass('selected')			// .. et coloration de ces lignes...
		.end()					//  ----- break -----
		
		.click(function(event) {
				var Ligne=$(this);
				if ((event.target.type !== 'checkbox')&&(event.target.type !== 'text')){
					var BoxAbsent=$(':checkbox:first', this);
					BoxAbsent.attr('checked', function() {
							BoxAbsent.attr('checked',!(BoxAbsent.attr('checked')));
							absent(BoxAbsent.attr('id'));
							if (BoxAbsent.attr('checked')) {Ligne.addClass("selected")} else {Ligne.removeClass("selected")};
					}
					);
				} else if ((event.target.type == 'checkbox')) {
					var cases =$(':checkbox:checked', this);
					if(cases.attr('id')==undefined) {Ligne.removeClass("selected")} else {Ligne.addClass("selected")};
				};
		});
		
		$('#pasdabsent').click(function() { 
				var cases = $("#rowclick5").find(':checkbox');
				var ligne = $("#rowclick5");
				
				if(this.checked){
					for(var i=0;i<cases.length/5;i++) {
						if (!(isNaN(cases[5*i].id))) {
							cases[5*i].checked=false;
							var CasesChecked=false;
							for (var j=1;j<5;j++){
								CasesChecked = CasesChecked || cases[5*i+j].checked;
							};
							if (!CasesChecked) {$('#ligne'+i).removeClass('selected');};
						}
					}
				}
		});
		
});



</script>
<table class="lire_bordure" border="0" align="center" cellpadding="0" cellspacing="0" width="100%">
<tr class="lire_cellule_4">
<td><?php echo $_SESSION['identite']. ' - '?> D&eacute;claration des absences pour la classe de
<?php 
if (isset($_GET['gic_ID'])&&(isset($_GET['classe_ID']))&&($_GET['classe_ID']==0)) {
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$query_Rsnom_gic = sprintf("SELECT * FROM cdt_groupe_interclasses WHERE ID_gic='%u' LIMIT 1", GetSQLValueString($_GET['gic_ID'], "int"));
	$Rsnom_gic = mysqli_query($conn_cahier_de_texte, $query_Rsnom_gic) or die(mysqli_error($conn_cahier_de_texte));
	$row_Rsnom_gic = mysqli_fetch_assoc($Rsnom_gic);
	echo $row_Rsnom_gic['nom_gic'];
	mysqli_free_result($Rsnom_gic);
} else {
echo $_GET['nom_classe'];};
echo ' - '.$_GET['groupe'].' pour le '.$_GET['jour_pointe'];
?></td>
<td ><div align="right"><img onClick="window.close()" src="../images/home-menu.gif" alt="Fermer la page" title="Fermer la page" width="26" height="20" border="0" /></div></td>
</tr>
</table>
<?php if ($totalRows_Rseleves==0){?>
	<p><strong>
	
	<br />
	<br />
	La gestion des absences n'a pas &eacute;t&eacute; encore mise en place pour cette classe.<br />
	<br />
	</strong></p>
<?php } 
else 

{ // Il y a des eleves
	?>
	<br />
	<?php 
	$lien_post='absence_ajout.php?nom_classe='.$_GET['nom_classe'].'&classe_ID='.$_GET['classe_ID'].'&gic_ID='.$_GET['gic_ID'].'&nom_matiere='.$_GET['nom_matiere'].'&groupe='.$_GET['groupe'].'&matiere_ID='.$_GET['matiere_ID'].'&semaine='.$_GET['semaine'].'&jour_pointe=';
	if (isset($_GET['jour_pointe'])) {$lien_post.=urlencode($_GET['jour_pointe']);} else {$lien_post.=urlencode($jour_pointe);};
	$lien_post.='&heure='.$_GET['heure'].'&duree='.$_GET['duree'].'&heure_debut='.$_GET['heure_debut'].'&heure_fin='.$_GET['heure_fin'].'&code_date='.$_GET['code_date'].'&current_day_name='.$_GET['current_day_name'];
	if (isset($_GET['edt_modif'])){$lien_post.='&edt_modif='.$_GET['edt_modif'];}else{$lien_post.='&edt_modif=N';}?>
	<form method="POST" name="form1" id="form1" action="<?php echo $lien_post;?>" >
	<table width="817" border="0" align="center">
	<tr>
	<td width="189" rowspan="2" align="right"><div align="center"><span>
	<textarea name="sms_vie_sco" cols="34" rows="2" class="tab_detail_gris_clair" style="border: 2px solid #cccccc;" id="sms_vie_sco" type="text" onFocus="if(this.value=='Ecrire un SMS pour la vie scolaire...') this.value='';" onBlur="if(this.value=='') this.value='Ecrire un SMS pour la vie scolaire...';" >Ecrire un SMS pour la vie scolaire...</textarea>
	</span></div></td>
	<td width="601"><div align="center">La pr&eacute;sence de cette ic&ocirc;ne <img src="../images/flag_green.png" width="16" height="16"> indique que l'absence de l'&eacute;l&egrave;ve a &eacute;t&eacute; prise en compte en vie scolaire.</div></td>
	</tr>
	<tr>
	<td width="601"><div align="center">
	<input type="button" value="Afficher une seule colonne" onclick="MM_goToURL('parent','<?php echo $lien_post.'&type_affiche=1';?>');return document.MM_returnValue">
	<input type="button" value="Affichage standard" onclick="MM_goToURL('parent','<?php echo $lien_post.'&type_affiche=2&pos='.$pos;?>');return document.MM_returnValue">
	<input type="button" value="Afficher tout" onclick="MM_goToURL('parent','<?php echo $lien_post.'&type_affiche=3';?>');return document.MM_returnValue">
	<input name="submit" type="submit" onClick="document.form1.submit()>" value="Envoyer en vie scolaire">
	</div></td>
	</tr>
	</table>
	<table class="lire_bordure" border="0" align="center" cellpadding="0" cellspacing="0"  >
	<tbody >
	<tr>
	<?php 
	echo '<td class="tab_detail_bleu" >'.$totalRows_Rseleves .' &eacute;l&egrave;ves </td>';
	echo '<td class="tab_detail_bleu" >Abs.<br>jour<br>pr&eacute;c.</td>';
	
	$a=1;
	if ($totalRows_Rs_emploi>0) {
		for($a=1;$a<=$totalRows_Rs_emploi;$a++){
			if (($pos<=$maxcol)||($numcol[$a]>$pos-$maxcol)){
				echo '<td class="tab_detail_bleu" >'.$hdeb[$a]. '-'.$hfin[$a].' <br> ';
				if ($colpart[$a]==1){echo '<img src="../images/partage.gif" width="12" height="10" />&nbsp;';}
				echo $ident[$a].'<br>' .$gr[$a].'</td>';
			}
		};
	} // du if $totalRows_Rs_emploi
	
	
	
	$b=$a;
	$tot=$totalRows_RsDs+$totalRows_Rs_emploi;
	
	//affichage Devoir et heure sup dans cellule du bout du bandeau
	if ($totalRows_RsDs>0){
		for($a=$b;$a<=$tot;$a++){
			
			echo '<td class="tab_detail_bleu" >'.$hdeb[$a]. '-'.$hfin[$a].' <br> '.$ident[$a].'<br>';
			if ($gicid[$a]>0) {echo "Regroupement";}else {echo $gr[$a];};
			if ($typac[$a]=='ds_prog'){echo '<br /><span class="rouge">Devoir</span>';} else if 
			(
				(isset($_GET['edt_modif']))&&($_GET['edt_modif']=='O')
				){echo '<br /><span class="rouge">Edt modif.</span>';} else {
			
				echo '<br /><span class="rouge">Heure sup.</span>';};
				echo'</td>';
				
		} ;
	} //du if $totalRows_RsDs
	// fin du bandeau
	?>
	</tr>
	<tr>
	<td class="tab_detail_gris_clair" ><div align="left"></div></td>
	<td class="tab_detail_gris_clair" ><div align="left"></div></td>
	<?php 
	
	$t=$totalRows_Rs_emploi+$totalRows_RsDs;
	
	for($j=1;$j<=$t;$j++){
		
		if (($pos<=$maxcol)||($numcol[$j]>$pos-$maxcol)){
			
			mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
			if ( $_GET['gic_ID'] != "0" ) {
				
				$query_Rsabsent = sprintf("SELECT * FROM ele_absent WHERE ele_absent.eleve_ID=0 AND SUBSTR(code_date,1,8)='%s' AND classe_ID IN (%s) AND heure_debut='%s' AND prof_ID=%u ",$madate,$in_classe_gic,$hdeb[$j],$idpc[$j]);
			} else {
				$query_Rsabsent = sprintf("SELECT * FROM ele_absent WHERE ele_absent.eleve_ID=0 AND SUBSTR(code_date,1,8)='%s' AND classe_ID=%u AND heure_debut='%s' AND prof_ID=%u ",$madate,$_GET['classe_ID'],$hdeb[$j],$idpc[$j]);
			}
			
			$Rsabsent = mysqli_query($conn_cahier_de_texte,  $query_Rsabsent) or die(mysqli_error($conn_cahier_de_texte));
			$row_Rsabsent= mysqli_fetch_assoc($Rsabsent);
			$totalRows_Rsabsent = mysqli_num_rows($Rsabsent);
			
		
			
			// On recupere le nombre d'eleves absents
			mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
			$sql_ajout=" AND retard='N' AND perso1='N' AND perso2='N' AND perso3='N'";
			if ( $_GET['gic_ID'] != "0" ) {
				$query_Rsabsentavant = sprintf("SELECT * FROM ele_absent WHERE SUBSTR(code_date,1,8)= '%s' AND classe_ID IN (%s) AND eleve_ID IN (%s) AND heure_debut='%s' AND prof_ID=%u %s",$madate,$in_classe_gic,$in_ele_gic ,$hdeb[$j],$idpc[$j],$sql_ajout);
			} else {
				$query_Rsabsentavant = sprintf("SELECT * FROM ele_absent WHERE SUBSTR(code_date,1,8)= '%s' AND classe_ID=%u AND eleve_ID!=0 AND heure_debut='%s' AND prof_ID=%u %s",$madate,$_GET['classe_ID'],$hdeb[$j],$idpc[$j],$sql_ajout); 
			}
			$Rsabsentavant = mysqli_query($conn_cahier_de_texte,  $query_Rsabsentavant) or die(mysqli_error($conn_cahier_de_texte));
			$totalRows_Rsabsentavant = mysqli_num_rows($Rsabsentavant);
			
			
			//entete tableau ( pas d'appel /nb d'absents / coche pas d'absents  Car Mat Autre
			if ($pos==$j) {
				
				?>
				<br />
				<td class="tab_detail_gris_clair" >
				
				<div style="float:left;display:inline;">
				
				&nbsp;Abs&nbsp;Ret
				<input type="hidden" name="nombre_absent_select" id="nombre_absent_select" value="'.$totalRows_Rsabsentavant.'" > 
				&nbsp;Pas d'absent
				
				<input type="checkbox" name="pasdabsent" id="pasdabsent"
				<?php
				
				if ( $totalRows_Rsabsent > 0 ) {echo ' checked';};?>>
				
				</div>
				<div style="width:110px;float:right;">
				<p align="center">&nbsp;Car&nbsp;&nbsp;&nbsp;Mat&nbsp;&nbsp;&nbsp;Autre</p>
				</div>
				</div>
				</td>
				<?php
			} else {
				if ( $totalRows_Rsabsentavant == 0 ) {
					if ($totalRows_Rsabsent==0){
						?>
						<td class="tab_detail_gris_clair" style="color:'.$couleur_pas_appel.'"><div align="center">
						Pas d'appel
						</div> </td>
						<?php
					}else{ ?>
						<td class="tab_detail_gris_clair" style="color:'.$couleur_pas_absent.'" ><div align="center">
						Pas d'absents
						</div></td>
						<?php
					};
					
				} else { ?>
					<td class="tab_detail_gris_clair" style="color:'.$couleur_absent.'" >
					<div align="center" style="color: #FF1063;font-weight: bold;">
					<?php
					echo $totalRows_Rsabsentavant . ' absent';if ($totalRows_Rsabsentavant>1){echo's';}
					?>
					</div></td>
					<?php
				}
			};
			
			mysqli_free_result($Rsabsent);
			mysqli_free_result($Rsabsentavant );
			
		}
		
	} //du for colonne
	
	?>
	</tr>
	</tbody >
	<tbody id="rowclick5">
	<?php
	
	$alterne=1; 
	$ind_ligne=0;
	do { 
		$alterne=$alterne*(-1);
		if ($alterne==1){$style_tab= 'tab_detail_gris_clair';} else {$style_tab='tab_detail_gris_fonce';};?>
		<tr id="ligne<?php echo $ind_ligne; $ind_ligne+=1; ?>" class="<?php echo $style_tab;?>">
		<td class="<?php echo $style_tab;?>" nowrap="nowrap" id="<?php echo 'div'.$row_Rseleves['ID_ele'];?>"><div align="left" onmouseover="this.style.cursor='pointer';" >
		<?php echo $row_Rseleves['nom_ele'].' '.$row_Rseleves['prenom_ele'];?>
		</div></td>
		<td class="<?php echo $style_tab; ?>" ><div align="center">
		<?php  
		//Recherche des absents de la veille
		//si lundi, on pointe sur le vendredi precedent et non la veille
		if (date('w')==1){$decal=3;}else{$decal=1;};
		$date_prec = mktime(0, 0, 0, date("m") , date("d") - $decal, date("Y"));
		$code_date_prec=date('Ymd',$date_prec);
		
		mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
		$query_Rsabsent_hier = sprintf("SELECT eleve_ID,retard FROM ele_absent WHERE eleve_ID = %u AND SUBSTRING(code_date,1,8)='%s' AND retard='N' AND perso1='N' AND perso2='N' AND perso3='N'",$row_Rseleves['ID_ele'],$code_date_prec);
								
								
		$Rsabsent_hier = mysqli_query($conn_cahier_de_texte, $query_Rsabsent_hier) or die(mysqli_error($conn_cahier_de_texte));
		$row_Rsabsent_hier = mysqli_fetch_assoc($Rsabsent_hier);
		$totalRows_Rsabsent_hier = mysqli_num_rows($Rsabsent_hier);
		if($totalRows_Rsabsent_hier>0){
			?>
			<img src="../images/coche.gif">
			<?php 
		}; 
		mysqli_free_result($Rsabsent_hier);
		?>
		</div></td>
		<?php
		
		for($j=1;$j<=$t;$j++){
			if (($pos<=$maxcol)||($numcol[$j]>$pos-$maxcol)){
				?>
				<td class="<?php echo $style_tab; ?>" nowrap="nowrap">
				<div style="float:left;display:inline;">
				<?php
				
				// On recupere les absences existantes, avec validation viesco
				mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
				if ( $_GET['gic_ID'] != "0" ) {
					$query_Rsele = sprintf("SELECT * FROM ele_absent WHERE eleve_ID=%u AND SUBSTR(code_date,1,8)='%s' AND classe_ID=0 AND heure_debut='%s' AND prof_ID=%u",$row_Rseleves['ID_ele'],$madate,$hdeb[$j],$idpc[$j]);
				} else {
					$query_Rsele = sprintf("SELECT * FROM ele_absent WHERE eleve_ID=%u AND SUBSTR(code_date,1,8)='%s' AND classe_ID=%u AND heure_debut='%s' AND prof_ID=%u",$row_Rseleves['ID_ele'],$madate,$_GET['classe_ID'],$hdeb[$j],$idpc[$j]); 
				}
				$Rsele = mysqli_query($conn_cahier_de_texte, $query_Rsele) or die(mysqli_error($conn_cahier_de_texte));
				$row_Rsele= mysqli_fetch_assoc($Rsele);
				$totalRows_Rsele = mysqli_num_rows($Rsele);
				
				if ($totalRows_Rsele <= 0 ) {
					// si aucune absence existante validee, on recupere les absences sans info vie sco
					mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
					if ( $_GET['gic_ID'] != "0" ) {
						$query_Rsele = sprintf("SELECT * FROM ele_absent WHERE eleve_ID=%u AND SUBSTR(ele_absent.code_date,1,8)= '%s' AND classe_ID IN (%s) AND heure_debut='%s' AND prof_ID=%u",$row_Rseleves['ID_ele'],$madate,$in_classe_gic,$hdeb[$j],$idpc[$j]);
					} else {
						$query_Rsele = sprintf("SELECT * FROM ele_absent WHERE eleve_ID=%u AND SUBSTR(ele_absent.code_date,1,8)= '%s' AND classe_ID=%u AND heure_debut='%s' AND prof_ID=%u",$row_Rseleves['ID_ele'],$madate,$_GET['classe_ID'],$hdeb[$j],$idpc[$j]); 
					}
					$Rsele = mysqli_query($conn_cahier_de_texte, $query_Rsele) or die(mysqli_error($conn_cahier_de_texte));
					$row_Rsele= mysqli_fetch_assoc($Rsele);
					$totalRows_Rsele = mysqli_num_rows($Rsele);
					
				}


				if (($totalRows_Rsele>0) && ($pos!=$j) ){ 
						if ($row_Rsele['retard']=='O'){
					            echo '<div style="color: #339966;font-weight: bold;">&nbsp;';
								if ($row_Rsele['motif']<>''){echo $row_Rsele['motif'];} else {echo 'Retard';};
								echo '</div>';
						};
						  
						if (($row_Rsele['retard']=='N')&&($row_Rsele['perso1']=='N')&&($row_Rsele['perso2']=='N')&&($row_Rsele['perso3']=='N')){	//absent
							echo '<div style="color: #FF1063;font-weight: bold;">&nbsp;Absent</div>';
						}; 
						if ($row_Rsele['perso1']=='O'){
							echo '<div style="color: #339966;font-weight: bold;display:inline;">&nbsp;';
							echo 'Oubli Carnet</div>';
						};
						if ($row_Rsele['perso2']=='O'){
							echo '<div style="color: #339966;font-weight: bold;display:inline;">&nbsp;';
							echo 'Oubli Mat&eacute;riel</div>';
						};		  
						  
				
				};
				
				
				
				$gr_url=$_GET['groupe']; 
				
				isset($row_Rsele['vie_sco_statut']) ? $vie_sco_statut = $row_Rsele['vie_sco_statut'] : $vie_sco_statut = 0;
				

				if($j==$pos){ //affichage de la colonne des colonnes de pointage
					 $createur_heure_part_ID=$idpc[$j];
					 if ($row_Rsele['vie_sco_statut'] == 0 ) {?>
						
						<input onclick="absent('<?php echo $row_Rseleves['ID_ele'];?>');" type="checkbox" name="<?php echo $row_Rseleves['ID_ele'];?>"  id="<?php echo $row_Rseleves['ID_ele'];?>" value="on"  
						
						<?php 
						if (($row_Rsele['heure_debut']==$hdeb[$j])&&
							
							($row_Rsele['retard']=='N')&&( $row_Rsele['perso1']=='N')&&( $row_Rsele['perso2']=='N')&&( $row_Rsele['perso3']=='N') 
						)
						{echo ' checked';};?>>
						
						<input onclick="document.getElementById('<?php echo $row_Rseleves['ID_ele'];?>').checked=false;" type="checkbox" name="<?php echo 'R_'.$row_Rseleves['ID_ele'];?>"  id="<?php echo 'R_'.$row_Rseleves['ID_ele'];?>" value="on"  <?php if ($row_Rsele['retard']=='O'){echo ' checked';};?>>
						
						<input onclick="" type="text" name="<?php echo $row_Rseleves['ID_ele'].'motif';?>" id="<?php echo $row_Rseleves['ID_ele'].'motif';?>" maxlength="255" size="11" style="height:10px;" value="<?php echo $row_Rsele['motif']; ?>">
						
						</div>
						
						<div style="width:110px;float:right;">
						&nbsp;
						<input onclick="document.getElementById('<?php echo $row_Rseleves['ID_ele'];?>').checked=false;" type="checkbox" name="<?php echo 'A_'.$row_Rseleves['ID_ele'];?>"  id="<?php echo 'A_'.$row_Rseleves['ID_ele'];?>" value="on"  <?php if ($row_Rsele['perso1']=='O'){echo ' checked';};?>>
						&nbsp;&nbsp;
						
						<input onclick="document.getElementById('<?php echo $row_Rseleves['ID_ele'];?>').checked=false;" type="checkbox" name="<?php echo 'B_'.$row_Rseleves['ID_ele'];?>"  id="<?php echo 'B_'.$row_Rseleves['ID_ele'];?>" value="on"  <?php if ($row_Rsele['perso2']=='O'){echo ' checked';};?>>
						&nbsp;&nbsp;
						
						<input onclick="document.getElementById('<?php echo $row_Rseleves['ID_ele'];?>').checked=false;" type="checkbox" name="<?php echo 'C_'.$row_Rseleves['ID_ele'];?>"  id="<?php echo 'C_'.$row_Rseleves['ID_ele'];?>" value="on"  <?php if ($row_Rsele['perso3']=='O'){echo ' checked';};?>>
						
					<?php }
					
					else { //acquite vie scolaire
						
						?>
						<img src="../images/flag_green.png" title="Acquit&eacute; par la vie scolaire" alt="Acquit&eacute; par la vie scolaire" >
						<?php
						if ($row_Rsele['retard']=='O'){echo 'Retard ';};
						echo $row_Rsele['motif'];
						?>
						<input type="hidden" name="<?php echo $row_Rseleves['ID_ele'];?>"  id="<?php echo $row_Rseleves['ID_ele'];?>" value="on" >
						<input type="hidden" name="<?php echo $row_Rseleves['ID_ele'].'motif';?>" id="<?php echo $row_Rseleves['ID_ele'].'motif';?>" value="<?php echo $row_Rsele['motif']; ?>">
						<?php 
						
					};?>
					<input onclick="document.getElementById('<?php echo $row_Rseleves['ID_ele'];?>').checked=false;" name="<?php echo $row_Rseleves['ID_ele'].'statut';?>" id="<?php echo $row_Rseleves['ID_ele'].'statut';?>" type="hidden" value="<?php 
					if($row_Rsele['vie_sco_statut']==NULL){echo '0';}else {echo $row_Rsele['vie_sco_statut'];};?>">
				<?php };
				?>
				</div>
				
				
				</td>
				<?php 
			}
		} ?>
		</tr>
		<?php 
	} while ($row_Rseleves = mysqli_fetch_assoc($Rseleves)); 
	?>
	</tbody >
	</table>
	<p>Salle (facultatif)
	<input name="salle" type="text" size="5">
	&nbsp;&nbsp;
	<input name="submit" type="submit" value="Envoyer en vie scolaire">
	</p>
	<?php

	if (isset($createur_heure_part_ID)){ 
	// heure partagee -  $row_RsPart['profpartage_ID'] est createur du partage
	// les absents seront enregistres dans la table sous son ID
	?>
	<input type="hidden" name="createur_heure_part_ID" value="<?php echo $createur_heure_part_ID;?>">	
	<?php };	?>
	<input type="hidden" name="MM_insert" value="form1">
	<input type="hidden" name="nb_eleves" value="<?php echo $totalRows_Rseleves;?>">
	<input type="hidden" name="gic_ID" value="<?php echo $_GET['gic_ID'];?>">
	</form>
	<?php
};
mysqli_free_result($Rs_emploi);
mysqli_free_result($Rseleves);
?>
</body>
</html>
