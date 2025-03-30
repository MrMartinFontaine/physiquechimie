<?php 
include "../authentification/authcheck.php" ;
if ($_SESSION['droits']<>1) { header("Location: ../index.php");exit;};
require_once('../Connections/conn_cahier_de_texte.php');
require_once('../inc/functions_inc.php');

if ((isset($_GET['ID_classe'])) && ($_GET['ID_classe'] != "")) {
	
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$query_RsNomClasse = sprintf("SELECT nom_classe FROM cdt_classe WHERE ID_classe=%u",GetSQLValueString($_GET['ID_classe'], "int"));
	$RsNomClasse = mysqli_query($conn_cahier_de_texte, $query_RsNomClasse) or die(mysqli_error($conn_cahier_de_texte));
	$row_RsNomClasse = mysqli_fetch_assoc($RsNomClasse);
	$NomClasse=$row_RsNomClasse['nom_classe'];
	mysqli_free_result($RsNomClasse);
			
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$query_RsClasse = sprintf("SELECT classe_ID FROM cdt_agenda WHERE classe_ID=%u",GetSQLValueString($_GET['ID_classe'], "int"));
	$RsClasse = mysqli_query($conn_cahier_de_texte, $query_RsClasse) or die(mysqli_error($conn_cahier_de_texte));
	$totalRows_RsClasse= mysqli_num_rows($RsClasse);
	mysqli_free_result($RsClasse);
	
	if ($totalRows_RsClasse!=0) { ?>
		<script language="JavaScript" type="text/JavaScript">
		function MM_goToURL() { //v3.0
			var i, args=MM_goToURL.arguments; document.MM_returnValue = false;
			for (i=0; i<(args.length-1); i+=2) eval(args[i]+".location='"+args[i+1]+"'");
		};
		alert("La suppression de la classe de <?php echo $NomClasse; ?> est impossible car son cahier de textes n'est pas vide.");
		MM_goToURL('window','classe_ajout.php');
		</script>
		<?php
	} else {
		
		mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
		$query_RsClasse = sprintf("SELECT classe_ID FROM cdt_emploi_du_temps WHERE classe_ID=%u",GetSQLValueString($_GET['ID_classe'], "int"));
		$RsClasse = mysqli_query($conn_cahier_de_texte, $query_RsClasse) or die(mysqli_error($conn_cahier_de_texte));
		$totalRows_RsClasse= mysqli_num_rows($RsClasse);
		mysqli_free_result($RsClasse);
		
		if ($totalRows_RsClasse!=0) { ?>
			<script language="JavaScript" type="text/JavaScript">
			function MM_goToURL() { //v3.0
				var i, args=MM_goToURL.arguments; document.MM_returnValue = false;
				for (i=0; i<(args.length-1); i+=2) eval(args[i]+".location='"+args[i+1]+"'");
			};
			alert("La suppression de la classe de <?php echo $NomClasse; ?> est impossible car cette classe est d\351j\340 utilis\351e dans au moins un emploi du temps.");
			MM_goToURL('window','classe_ajout.php');
			</script>
			<?php
		} else {
			
			mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
			$query_RsIDClasse = sprintf("SELECT gic_ID FROM cdt_groupe_interclasses_classe WHERE classe_ID=%u",GetSQLValueString($_GET['ID_classe'], "int"));
			$RsIDClasse = mysqli_query($conn_cahier_de_texte, $query_RsIDClasse) or die(mysqli_error($conn_cahier_de_texte));
			$totalRows_RsIDClasse= mysqli_num_rows($RsIDClasse);
			
			if ($totalRows_RsIDClasse>0) {
				
				while (($row_RsIDClasse = mysqli_fetch_assoc($RsIDClasse)) && ($totalRows_RsClasse==0)) {
					
					mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
					$query_RsClasse = sprintf("SELECT classe_ID FROM cdt_agenda WHERE gic_ID=%u",$row_RsIDClasse['gic_ID']);
					$RsClasse = mysqli_query($conn_cahier_de_texte, $query_RsClasse) or die(mysqli_error($conn_cahier_de_texte));
					$totalRows_RsClasse= mysqli_num_rows($RsClasse);
					mysqli_free_result($RsClasse);
					
					mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
					$query_RsClasse = sprintf("SELECT classe_ID FROM cdt_emploi_du_temps WHERE gic_ID=%u",$row_RsIDClasse['gic_ID']);
					$RsClasse = mysqli_query($conn_cahier_de_texte, $query_RsClasse) or die(mysqli_error($conn_cahier_de_texte));
					$totalRows_RsClasse+= mysqli_num_rows($RsClasse);
					mysqli_free_result($RsClasse);
				};
			};
			
			mysqli_free_result($RsIDClasse);
			
			if ($totalRows_RsClasse!=0) { ?>
				<script language="JavaScript" type="text/JavaScript">
				function MM_goToURL() { //v3.0
					var i, args=MM_goToURL.arguments; document.MM_returnValue = false;
					for (i=0; i<(args.length-1); i+=2) eval(args[i]+".location='"+args[i+1]+"'");
				};
				alert("La suppression de la classe de <?php echo $NomClasse; ?> est impossible car cette classe est utilis\351e au sein d'un regroupement dans un cahier de textes ou un emploi du temps.");
				MM_goToURL('window','classe_ajout.php');
				</script>
				<?php
			} else {
				
				$deleteSQL = sprintf("DELETE FROM cdt_classe WHERE ID_classe=%u",GetSQLValueString($_GET['ID_classe'], "int"));
				
				mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
				$Result1 = mysqli_query($conn_cahier_de_texte, $deleteSQL) ;
				
				// supprimer sa presence dans un niveau
				
				$deleteSQL2 = sprintf("DELETE FROM cdt_niveau_classe WHERE classe_ID=%u",GetSQLValueString($_GET['ID_classe'], "int"));
				mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
				$Result2 = mysqli_query($conn_cahier_de_texte, $deleteSQL2);
				
				// supprimer sa presence dans un regroupement
				
				mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
				$query_RsIDClasse = sprintf("SELECT gic_ID FROM cdt_groupe_interclasses_classe WHERE classe_ID=%u",GetSQLValueString($_GET['ID_classe'], "int"));
				$RsIDClasse = mysqli_query($conn_cahier_de_texte, $query_RsIDClasse) or die(mysqli_error($conn_cahier_de_texte));
				$totalRows_RsIDClasse= mysqli_num_rows($RsIDClasse);
				
				if ($totalRows_RsIDClasse!=0) {
					$deleteSQL2 = sprintf("DELETE FROM cdt_groupe_interclasses_classe WHERE classe_ID=%u",GetSQLValueString($_GET['ID_classe'], "int"));
					mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
					$Result2 = mysqli_query($conn_cahier_de_texte, $deleteSQL2);
				};
				
				// Verification de la presence d'un regroupement sans classes et suppression le cas echeant
				
				while ($row_RsIDClasse = mysqli_fetch_assoc($RsIDClasse)) {
					$query_RsIDClasse2 = sprintf("SELECT gic_ID FROM cdt_groupe_interclasses_classe WHERE gic_ID=%u",$row_RsIDClasse['gic_ID']);
					$RsIDClasse2 = mysqli_query($conn_cahier_de_texte, $query_RsIDClasse2) or die(mysqli_error($conn_cahier_de_texte));
					if (mysqli_num_rows($RsIDClasse2)==0) {
						$deleteSQL2 = sprintf("DELETE FROM cdt_groupe_interclasses WHERE ID_GIC=%u",$row_RsIDClasse['gic_ID']);
						mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
						$Result2 = mysqli_query($conn_cahier_de_texte, $deleteSQL2);
					};
					mysqli_free_result($RsIDClasse2);					
				};
				
				mysqli_free_result($RsIDClasse);
				
				// mise a jour dans l'import des EDT
				
				$deleteSQL3 = sprintf("UPDATE cdt_edt SET classe_ID=0 WHERE classe_ID=%u",GetSQLValueString($_GET['ID_classe'], "int"));
				mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
				$Result3 = mysqli_query($conn_cahier_de_texte, $deleteSQL3);
				
				$deleteGoTo = "classe_ajout.php";
				if (isset($_SERVER['QUERY_STRING'])) {
					$deleteGoTo .= (strpos($deleteGoTo, '?')) ? "&" : "?";
					$deleteGoTo .= $_SERVER['QUERY_STRING'];
				}
				header(sprintf("Location: %s", $deleteGoTo));
			}
		}
	};
}; 
?>
