<?php 
mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "SET @@global.sql_mode= '' "; //desactivation mode strict sur version recente mysql
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_nom_etab_db = "SELECT param_val FROM cdt_params WHERE param_nom='nom_etab'";
$nom_etab_db = mysqli_query($conn_cahier_de_texte, $query_nom_etab_db) or die(mysqli_error($conn_cahier_de_texte));
$row_nom_etab_db = mysqli_fetch_assoc($nom_etab_db);
$totalRows_nom_etab_db=mysqli_num_rows($nom_etab_db);
if ($totalRows_nom_etab_db==0){ //mise a jour 420x pas encore faite
 $_SESSION['nom_etab']=stripslashes($nom_etab);
} else {$_SESSION['nom_etab']=stripslashes($row_nom_etab_db['param_val']);}


mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_url_logo_db = "SELECT param_val FROM cdt_params WHERE param_nom='url_logo_etab'";
$url_logo_db = mysqli_query($conn_cahier_de_texte, $query_url_logo_db) or die(mysqli_error($conn_cahier_de_texte));
$row_url_logo_db = mysqli_fetch_assoc($url_logo_db);
$totalRows_url_logo_db=mysqli_num_rows($url_logo_db);
if ($totalRows_url_logo_db==0){ //mise a jour 420x pas encore faite
 $_SESSION['url_logo_etab']==$url_logo_etab;
} else {$_SESSION['url_logo_etab']=$row_url_logo_db['param_val'];}


mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_url_etab_db = "SELECT param_val FROM cdt_params WHERE param_nom='url_etab'";
$url_etab_db = mysqli_query($conn_cahier_de_texte, $query_url_etab_db) or die(mysqli_error($conn_cahier_de_texte));
$row_url_etab_db = mysqli_fetch_assoc($url_etab_db);
$totalRows_url_etab_db=mysqli_num_rows($url_etab_db);
if ($totalRows_url_etab_db==0){ //mise a jour 420x pas encore faite
 $_SESSION['url_etab']=$url_etab;
} else {$_SESSION['url_etab']=$row_url_etab_db['param_val'];}


mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_session_timeout_db = "SELECT param_val FROM cdt_params WHERE param_nom='session_timeout'";
$session_timeout_db = mysqli_query($conn_cahier_de_texte, $query_session_timeout_db) or die(mysqli_error($conn_cahier_de_texte));
$row_session_timeout_db = mysqli_fetch_assoc($session_timeout_db);
$totalRows_session_timeout_db=mysqli_num_rows($session_timeout_db);
if ($totalRows_session_timeout_db==0){ //mise ajour 420x pas encore faite
    if (isset($session_timeout)&&($session_timeout<>'')){$_SESSION['session_timeout']=$session_timeout;}
    else {$_SESSION['session_timeout']=3600;};
} else {$_SESSION['session_timeout']=$row_session_timeout_db['param_val'];}


mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_Rslibelle_devoir = "SELECT param_val FROM cdt_params WHERE param_nom='libelle_devoir'";
$Rslibelle_devoir = mysqli_query($conn_cahier_de_texte, $query_Rslibelle_devoir) or die(mysqli_error($conn_cahier_de_texte));
$row_Rslibelle_devoir = mysqli_fetch_assoc($Rslibelle_devoir);
$totalRows_Rslibelle_devoir = mysqli_num_rows($Rslibelle_devoir);
if ($totalRows_Rslibelle_devoir>0) {$_SESSION['libelle_devoir']=$row_Rslibelle_devoir['param_val']; } else {$_SESSION['libelle_devoir']='DEVOIR';};


mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_Rsvisa_stop_edition = "SELECT param_val FROM cdt_params WHERE param_nom='visa_stop_edition'";
$Rsvisa_stop_edition = mysqli_query($conn_cahier_de_texte, $query_Rsvisa_stop_edition) or die(mysqli_error($conn_cahier_de_texte));
$row_Rsvisa_stop_edition = mysqli_fetch_assoc($Rsvisa_stop_edition);
$totalRows_Rsvisa_stop_edition = mysqli_num_rows($Rsvisa_stop_edition);
if ($totalRows_Rsvisa_stop_edition>0) {$_SESSION['visa_stop_edition']=$row_Rsvisa_stop_edition['param_val']; } else {$_SESSION['visa_stop_edition']='Non';};


mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_Rsurl_deconnecte_eleve = "SELECT param_val FROM cdt_params WHERE param_nom='url_deconnecte_eleve'";
$Rsurl_deconnecte_eleve = mysqli_query($conn_cahier_de_texte, $query_Rsurl_deconnecte_eleve) or die(mysqli_error($conn_cahier_de_texte));
$row_Rsurl_deconnecte_eleve = mysqli_fetch_assoc($Rsurl_deconnecte_eleve);
$totalRows_Rsurl_deconnecte_eleve = mysqli_num_rows($Rsurl_deconnecte_eleve);
if ($totalRows_Rsurl_deconnecte_eleve>0) {$_SESSION['url_deconnecte_eleve']=$row_Rsurl_deconnecte_eleve['param_val']; } else {$_SESSION['url_deconnecte_eleve']='index.php';};

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_Rsurl_deconnecte_prof = "SELECT param_val FROM cdt_params WHERE param_nom='url_deconnecte_prof'";
$Rsurl_deconnecte_prof = mysqli_query($conn_cahier_de_texte, $query_Rsurl_deconnecte_prof) or die(mysqli_error($conn_cahier_de_texte));
$row_Rsurl_deconnecte_prof = mysqli_fetch_assoc($Rsurl_deconnecte_prof);
$totalRows_Rsurl_deconnecte_prof = mysqli_num_rows($Rsurl_deconnecte_prof);
if ($totalRows_Rsurl_deconnecte_prof>0) {$_SESSION['url_deconnecte_prof']=$row_Rsurl_deconnecte_prof['param_val']; } else {$_SESSION['url_deconnecte_prof']='index.php';};

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_Rsmodule_absence = "SELECT param_val FROM cdt_params WHERE param_nom='module_absence'";
$Rsmodule_absence = mysqli_query($conn_cahier_de_texte, $query_Rsmodule_absence) or die(mysqli_error($conn_cahier_de_texte));
$row_Rsmodule_absence = mysqli_fetch_assoc($Rsmodule_absence);
$totalRows_Rsmodule_absence = mysqli_num_rows($Rsmodule_absence);
if ($totalRows_Rsmodule_absence>0) {$_SESSION['module_absence']=$row_Rsmodule_absence['param_val']; } else {$_SESSION['module_absence']='Non';};

if ((isset($_SESSION['module_absence']))&&($_SESSION['module_absence']=='Oui'))	{	
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_Rschoix_module_absence = "SELECT param_val FROM cdt_params WHERE param_nom='choix_module_absence'";
$Rschoix_module_absence = mysqli_query($conn_cahier_de_texte,$query_Rschoix_module_absence) or die(mysqli_error($conn_cahier_de_texte));
$row_Rschoix_module_absence = mysqli_fetch_assoc($Rschoix_module_absence);
$totalRows_Rschoix_module_absence = mysqli_num_rows($Rschoix_module_absence);
if ($totalRows_Rschoix_module_absence>0) {$_SESSION['choix_module_absence']=$row_Rschoix_module_absence['param_val']; } else {$_SESSION['module_absence']='1';};
};

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_Rs_affichage_compteur = "SELECT param_val FROM cdt_params WHERE param_nom='affichage_compteur'";
$Rs_affichage_compteur = mysqli_query($conn_cahier_de_texte, $query_Rs_affichage_compteur) or die(mysqli_error($conn_cahier_de_texte));
$row_Rs_affichage_compteur = mysqli_fetch_assoc($Rs_affichage_compteur);
$totalRows_Rs_affichage_compteur = mysqli_num_rows($Rs_affichage_compteur);
if ($totalRows_Rs_affichage_compteur>0) {$_SESSION['affichage_compteur']=$row_Rs_affichage_compteur['param_val']; } else {$_SESSION['affichage_compteur']='Non';};

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_Rsparams3 = "SELECT param_val FROM cdt_params WHERE param_nom='ind_maj_base'";
$Rsparams3 = mysqli_query($conn_cahier_de_texte, $query_Rsparams3) or die(mysqli_error($conn_cahier_de_texte));
$row_Rsparams3 = mysqli_fetch_assoc($Rsparams3);

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_Rs_libelle_semaine = "SELECT param_val FROM cdt_params WHERE param_nom='libelle_semaine'";
$Rs_libelle_semaine = mysqli_query($conn_cahier_de_texte, $query_Rs_libelle_semaine) or die(mysqli_error($conn_cahier_de_texte));
$row_Rs_libelle_semaine = mysqli_fetch_assoc($Rs_libelle_semaine);
$totalRows_Rs_libelle_semaine = mysqli_num_rows($Rs_libelle_semaine);
if ($totalRows_Rs_libelle_semaine>0) {$_SESSION['libelle_semaine']=$row_Rs_libelle_semaine['param_val']; } else {$_SESSION['libelle_semaine']=0;};

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_Rs_site_ferme = "SELECT param_val FROM cdt_params WHERE param_nom='site_ferme'";
$Rs_site_ferme = mysqli_query($conn_cahier_de_texte, $query_Rs_site_ferme) or die(mysqli_error($conn_cahier_de_texte));
$row_Rs_site_ferme = mysqli_fetch_assoc($Rs_site_ferme);
$totalRows_Rs_site_ferme = mysqli_num_rows($Rs_site_ferme);
if ($totalRows_Rs_site_ferme>0) {$_SESSION['site_ferme']=$row_Rs_site_ferme['param_val']; } else {$_SESSION['site_ferme']='Non';};

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_Rs_devoir_planif = "SELECT param_val FROM cdt_params WHERE param_nom='devoir_planif'";
$Rs_devoir_planif = mysqli_query($conn_cahier_de_texte, $query_Rs_devoir_planif) or die(mysqli_error($conn_cahier_de_texte));
$row_Rs_devoir_planif = mysqli_fetch_assoc($Rs_devoir_planif);
$totalRows_Rs_devoir_planif = mysqli_num_rows($Rs_devoir_planif);
if ($totalRows_Rs_devoir_planif>0) {$_SESSION['devoir_planif']=$row_Rs_devoir_planif['param_val']; } else {$_SESSION['devoir_planif']='Oui';};

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_Rs_prof_mess_pp = "SELECT param_val FROM cdt_params WHERE param_nom='prof_mess_pp'";
$Rs_prof_mess_pp = mysqli_query($conn_cahier_de_texte, $query_Rs_prof_mess_pp) or die(mysqli_error($conn_cahier_de_texte));
$row_Rs_prof_mess_pp = mysqli_fetch_assoc($Rs_prof_mess_pp);
$totalRows_Rs_prof_mess_pp = mysqli_num_rows($Rs_prof_mess_pp);
if ($totalRows_Rs_prof_mess_pp>0) {$_SESSION['prof_mess_pp']=$row_Rs_prof_mess_pp['param_val']; } else {$_SESSION['prof_mess_pp']='Non';};

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_Rs_prof_mess_all = "SELECT param_val FROM cdt_params WHERE param_nom='prof_mess_all'";
$Rs_prof_mess_all = mysqli_query($conn_cahier_de_texte, $query_Rs_prof_mess_all) or die(mysqli_error($conn_cahier_de_texte));
$row_Rs_prof_mess_all = mysqli_fetch_assoc($Rs_prof_mess_all);
$totalRows_Rs_prof_mess_all = mysqli_num_rows($Rs_prof_mess_all);
if ($totalRows_Rs_prof_mess_all>0) {$_SESSION['prof_mess_all']=$row_Rs_prof_mess_all['param_val']; } else {$_SESSION['prof_mess_all']='Non';};

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_Rs_acces_inspection = "SELECT param_val FROM cdt_params WHERE param_nom='acces_inspection_all_cdt'";
$Rs_acces_inspection = mysqli_query($conn_cahier_de_texte, $query_Rs_acces_inspection) or die(mysqli_error($conn_cahier_de_texte));
$row_Rs_acces_inspection = mysqli_fetch_assoc($Rs_acces_inspection);
$totalRows_Rs_acces_inspection = mysqli_num_rows($Rs_acces_inspection);
if ($totalRows_Rs_acces_inspection>0) {$_SESSION['acces_inspection_all_cdt']=$row_Rs_acces_inspection['param_val']; } else {$_SESSION['acces_inspection_all_cdt']='Non';};


mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_Rs_URL_Piwik = "SELECT param_val FROM cdt_params WHERE param_nom='URL_Piwik'";
$Rs_URL_Piwik = mysqli_query($conn_cahier_de_texte, $query_Rs_URL_Piwik) or die(mysqli_error($conn_cahier_de_texte));
$row_Rs_URL_Piwik = mysqli_fetch_assoc($Rs_URL_Piwik);
$totalRows_Rs_URL_Piwik = mysqli_num_rows($Rs_URL_Piwik);
if ($totalRows_Rs_URL_Piwik>0) {$_SESSION['URL_Piwik']=$row_Rs_URL_Piwik['param_val']; };


mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_Rs_ID_Piwik = "SELECT param_val FROM cdt_params WHERE param_nom='ID_Piwik'";
$Rs_ID_Piwik = mysqli_query($conn_cahier_de_texte, $query_Rs_ID_Piwik) or die(mysqli_error($conn_cahier_de_texte));
$row_Rs_ID_Piwik = mysqli_fetch_assoc($Rs_ID_Piwik);
$totalRows_Rs_ID_Piwik = mysqli_num_rows($Rs_ID_Piwik);
if ($totalRows_Rs_ID_Piwik>0) {$_SESSION['ID_Piwik']=$row_Rs_ID_Piwik['param_val']; };


if (isset($nom_etab_db)){mysqli_free_result($nom_etab_db);};
if (isset($url_logo_db)){mysqli_free_result($url_logo_db);};
if (isset($url_etab_db)){mysqli_free_result($url_etab_db);};
if (isset($session_timeout_db)){mysqli_free_result($session_timeout_db);};
if (isset($Rslibelle_devoir)){mysqli_free_result($Rslibelle_devoir);};
if (isset($Rsvisa_stop_edition)){mysqli_free_result($Rsvisa_stop_edition);};
if (isset($Rsurl_deconnecte_eleve)){mysqli_free_result($Rsurl_deconnecte_eleve);};
if (isset($Rsurl_deconnecte_prof)){mysqli_free_result($Rsurl_deconnecte_prof);};
if (isset($Rsmodule_absence)){mysqli_free_result($Rsmodule_absence);};
if (isset($Rschoix_module_absence)){mysqli_free_result($Rschoix_module_absence);};
if (isset($Rs_affichage_compteur)){mysqli_free_result($Rs_affichage_compteur);};

if (isset($Rs_libelle_semaine)){mysqli_free_result($Rs_libelle_semaine);};
if (isset($Rs_site_ferme)){mysqli_free_result($Rs_site_ferme);};
if (isset($Rs_devoir_planif)){mysqli_free_result($Rs_devoir_planif);};
if (isset($Rs_prof_mess_pp)){mysqli_free_result($Rs_prof_mess_pp);};
if (isset($Rs_prof_mess_all)){mysqli_free_result($Rs_prof_mess_all);};
if (isset($Rs_acces_inspection)){mysqli_free_result($Rs_acces_inspection);};
?>
