<?php
if (isset($_SESSION['identite'])) {
	?>
	<div id="sessionTimeoutBlock" style="position:absolute; right:5px;top:27px; background: #FFF;padding: 1px 5px;-moz-border-radius: 8px;
	-webkit-border-radius:8px;border-radius:8px; border:solid 1px #CACACA;width:95px; text-align:center"></div>
	<script type="text/javascript">
    var sessionTimeout = <?php echo $_SESSION['last_access']+$_SESSION['session_timeout']-time() ; ?>;//Initialisation de la variable gérant le temps restant calculé côté serveur.
	var sessionTimeoutBlock = document.getElementById("sessionTimeoutBlock");
	var xmlhttp = new getXMLObject();
	
	var affichSessionTimeout = "" ;
	var x = 1 ;
	function displaySessionTimeout() {
		var h = Math.floor (sessionTimeout / 3600);
		var mn = Math.floor ((sessionTimeout - ( h * 3600)) / 60);
		var sec = Math.floor (sessionTimeout - (h * 3600 + mn * 60));
		affichSessionTimeout = "Temps restant<br />" ;
		if (h > 0)
			affichSessionTimeout += h + "h" ;
		if (mn > 0 || h > 0)
			affichSessionTimeout += (mn < 10 ? "0":"") + mn ;
		if (sessionTimeout < 120)
			affichSessionTimeout += (sessionTimeout > 60 ?":":"") + (sec < 10 ? "0":"") + sec ;
		else if (sessionTimeout < 3600)
			affichSessionTimeout += " minute" + (mn > 1 ? "s":"");
		if (mn == 0 && h == 0) {
			sessionTimeoutBlock.style.backgroundColor = "red" ;
			sessionTimeoutBlock.style.color = "#FFF";
			affichSessionTimeout += " seconde" + (sec > 1 ? "s":"") + " !" ;
			if (sessionTimeout <= 0)
				affichSessionTimeout = "Session<br />expir&eacute;e" ;
		}
		sessionTimeoutBlock.innerHTML =  affichSessionTimeout;
		sessionTimeout = sessionTimeout - (1 * x);
		if (sec == 0) // Contrôle AJAX de connexion toutes les minutes
			sessionAjaxVerif();
		if (sessionTimeout >= 0) { // Rappel de la fonction toutes les secondes
			window.setTimeout("displaySessionTimeout()", 1000);
		}
	}
	function alertTimeOut(mode) {
		var msg = "" ;
		if (mode == "PB") {
			sessionTimeoutBlock.innerHTML="Session<br />perdue";
			msg += "Connexion au serveur impossible !!!\n\n";
		}
		else {
			sessionTimeoutBlock.innerHTML="Session<br />expir&eacute;e";
			msg += "Votre session a expir\351 !!!\n\n";
			sessionTimeout = 0 ;
		}
		msg += "Attention \340 ne valider aucun formulaire : \n";
		msg += "les donn\351es seraient perdues...\n";
		msg += "Si n\351cessaire, copiez votre travail en cours\n";
		msg += "pour le r\351utiliser ult\351rieurement.";
		alert(msg);
	}
	function sessionAjaxVerif() {
		if(xmlhttp) { 
			xmlhttp.open("POST","../authentification/sessionAjax.php",true);
			xmlhttp.onreadystatechange = handleServerResponse;
			xmlhttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
			xmlhttp.send("verifSession=active"); 
		}
	}
	function getXMLObject() { //XML OBJECT
		var xmlHttp = false;
		try {
			xmlHttp = new ActiveXObject("Msxml2.XMLHTTP")  // For Old Microsoft Browsers
		}
		catch (e) {
			try {
				xmlHttp = new ActiveXObject("Microsoft.XMLHTTP")  // For Microsoft IE 6.0+
			}
			catch (e2) {
				xmlHttp = false   // No Browser accepts the XMLHTTP Object then false
			}
		}
		if (!xmlHttp && typeof XMLHttpRequest != 'undefined') {
			xmlHttp = new XMLHttpRequest();        //For Mozilla, Opera Browsers
		}
		return xmlHttp;  // Mandatory Statement returning the ajax object created
	}
	function handleServerResponse() {
		if (xmlhttp.readyState == 4) {
			if(xmlhttp.status == 200) {
			  if (parseInt(xmlhttp.responseText) > 0){//Récupération de la réponse, conversion en Int et test SI il reste du temps.
			    sessionTimeout = parseInt(xmlhttp.responseText) ;//Réaffectation de la variable de temps restant.
			    sessionTimeoutBlock.innerHTML="Session<br />active";
			  }
			  else {
			    alertTimeOut("");
			  }
			} 
			else {
			  alertTimeOut("PB");
			}
		}
	}
	
	displaySessionTimeout();
	</script>
	<?php
}
else {
	echo "<!-- PAS DE VERIFICATION DE SESSION -->" ;
}
?>
