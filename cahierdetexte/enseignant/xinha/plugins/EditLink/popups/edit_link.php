<?php include "../../../../../authentification/authcheck.php" ;
// on récupère les variables de session pour scanner le répertoire de l'utilisateur
$url_abs = 'http://'.$_SERVER['HTTP_HOST'].$_SESSION['path_fichier_perso'];
$dir     = $_SERVER['DOCUMENT_ROOT'].$_SESSION['path_fichier_perso'];

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
  <title>Insertion d'un lien vers un de mes fichiers</title>
  <link rel="stylesheet" type="text/css" href="../../../popups/popup.css" />
  <script type="text/javascript" src="../../../popups/popup.js"></script>
  <link rel="StyleSheet" href="dtree.css" type="text/css" />
  <script type="text/javascript" src="dtree.js"></script>

  <script language="javascript">
window.resizeTo(620, 430);
var texte;

function fab_arbre(){
files = <?php include "scan.php" ?> ;                       // retourne un tableau d'objets au format Json
nxtid = 0;
   dTree = new dTree('dTree');                              // création d'un nouvel objet arbre
   var info_racine = "Dossier :(<?php echo $url_abs ?> )";
   dTree.add(nxtid++, -1,info_racine, null, 'essai');      //création de la racine
   fab_noeuds(files, 0);                                   //création des noeuds avec le tableau files
   document.getElementById("contenu").innerHTML = dTree;   //copie de l'arbre dans le conteneur contenu
}
   
function fab_noeuds(files, parent){
 for(var i = 0; i < files.length; i++)
  {
     if(typeof files[i] == 'object')
    {   id = nxtid++;
        if(files[i].url)
           var item = files[i].url.replace(/^.*\//, '');
        else
           var item = "sans titre";

       if(files[i].url)
           var lien = "javascript:recopie_lien('"+files[i].url+"');"
       else
           var lien = '#';

      if(files[i].children) {
       dTree.add(id, parent, item, null);
       fab_noeuds(files[i].children, id);
       }
      else
       dTree.add(id, parent, item, lien);
    }
  }
}

function Init() {

  __dlg_translate("HTMLArea");
  __dlg_init();
  fab_arbre();

  var param = window.dialogArguments;
  if (param) {
    if ( typeof param["f_href"] != "undefined" ) {
      document.getElementById("f_href").value = param["f_href"];
      document.getElementById("f_title").value = param["f_title"];
    }
  }
}



function onCancel() {
  __dlg_close( null );
  return false;
}

function onOK() {
         // retour est l'objet retourné à la fénêtre appelante
         var retour = new Object();
         retour.f_href =  document.getElementById("f_href").value;
         retour.f_title = document.getElementById("f_title").value;
         retour.f_target = "_blank";
         __dlg_close( retour );
}

function recopie_lien(url){
       document.getElementById("f_href").value = url;
}
</script>
</head>

<body class="dialog" onLoad="Init();" >

<div class="title">Lien vers un fichier de mon dossier personnel</div>

<table  style="width: 100%;">
  <tr>
    <td class="label">URL:</td>
    <td><input id="f_href"  type="text" style="width: 100%; height: 20px; margin-top: 8px; margin-bottom: 4px; font-family: monospace; font-size: 11px;"/></td>
  </tr>
  <tr>
    <td class="label">Texte alternatif:</td>
    <td><input type="text" id="f_title" style="width: 100%" /></td>
  </tr>
</table>
<br />


<div id="contenu" style="width: 100%; height: 300px; border: 1px solid #7F9DB9; background: White; overflow: auto;"></div>

<div id="buttons">
    <button type="submit" name="ok" onClick="return onOK();"> Ins&eacute;rer</button>
    <button type="button" name="cancel" onClick="return onCancel();">Annuler</button>
</div>

</body>
</html>
