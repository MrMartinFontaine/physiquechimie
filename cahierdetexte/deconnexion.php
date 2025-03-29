<?php session_start();

require_once('Connections/conn_cahier_de_texte.php');
if (isset($_SESSION['url_deconnecte_prof'])){$url_deconnecte_prof=$_SESSION['url_deconnecte_prof'];} else {$url_deconnecte_prof='index.php';};

unset($_SESSION['identite']);
unset($_SESSION['nom_prof']);
unset($_SESSION['ID_prof']);
unset($_SESSION['email']);
unset($_SESSION['droits']);
unset($_SESSION['publier_cdt']);
unset($_SESSION['publier_travail']);
unset($_SESSION['date_visa']);
unset($_SESSION['copie']);
unset($_SESSION['coller']);
unset($_SESSION['semdate']);
unset($_SESSION['consultation']);
unset($_SESSION['last_access']);
unset($_SESSION['ipaddr']);
unset($_SESSION['path_fichier_perso']);
unset($_SESSION['xinha_editlatex']);
unset($_SESSION['xinha_equation']);
unset($_SESSION['xinha_stylist']);


unset($_SESSION['nom_etab']);
unset($_SESSION['url_etab']);
unset($_SESSION['url_etab']);
unset($_SESSION['url_logo_etab']);

unset($_SESSION['url_deconnecte_eleve']);
unset($_SESSION['url_deconnecte_prof']);
unset($_SESSION['module_absence']);

unset($_SESSION['libelle_devoir']);
unset($_SESSION['visa_stop_edition']);
unset($_SESSION['session_timeout']);

unset($_SESSION['edt_modif_mat']);
unset($_SESSION['affichage_compteur']);

unset($_SESSION['acces_rapide']);
unset($_SESSION['afficher_messages']);
unset($_SESSION['masque_edt_cloture']);
unset($_SESSION['libelle_semaine']);

unset($_SESSION['mobile_browser']);
unset($_SESSION['site_ferme']);
unset($_SESSION['devoir_planif']);

unset($_SESSION['prof_mess_pp']);
unset($_SESSION['prof_mess_all']);
unset($_SESSION['id_etat']);
unset($_SESSION['ipad']);
unset($_SESSION['affiche_xinha']);

if (isset($_SESSION['ecart_realise'])){unset($_SESSION['ecart_realise']);};

unset($_SESSION['type_affich']);
unset($_SESSION['acces_inspection_all_cdt']);

if (isset($_SESSION['archivID'])){ unset($_SESSION['archivID']);};
if (isset($_SESSION['URL_Piwik'])){ unset($_SESSION['URL_Piwik']);};
if (isset($_SESSION['ID_Piwik'])){ unset($_SESSION['ID_Piwik']);};

header(sprintf("Location: %s", $url_deconnecte_prof));
?>
