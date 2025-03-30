<script type="text/javascript">
var _editor_url  = "xinha/";
var _editor_lang = "fr";

</script>
<!-- Load up the actual editor core -->
<script type="text/javascript" src="xinha/XinhaCore.js"></script>


<script type="text/javascript">

var xinha_plugins =
[
<?php if($_SESSION['xinha_editlatex']=="O"){echo "'EditLatex',";};?>
<?php if($_SESSION['xinha_equation']=="O"){echo "'Equation',";};?>
<?php if($_SESSION['xinha_stylist']=="O"){echo "'Stylist',";};?>
<?php if(isset($_SESSION['path_fichier_perso'])){echo "'EditLink',";};?>
'CharacterMap',
'InsertWords'

];
var xinha_editors =
[
'activite','a_faire_1'<?php if(!isset($_GET['ds_prog'])){echo ",'a_faire_2','a_faire_3'";};?>
];


function xinha_init()
{
	if(!Xinha.loadPlugins(xinha_plugins, xinha_init)) return;
	
	var xinha_config = new Xinha.Config();
        
	xinha_config.pageStyle = 'body { font-family: verdana,sans-serif; font-size: 11px; color: #000066 }';
	<?php if($_SESSION['xinha_stylist']=="O"){echo "xinha_config.stylistLoadStylesheet('../templates/default/perso.css');";};?>
	if(typeof InsertWords != 'undefined') {
		// Register the keyword/replacement list
		var keywrds1 = new Object();
		keywrds1['Ins\351rer'] = '';
		keywrds1['Objectifs'] = '<strong><ul><li>Objectifs : </li></ul></strong>';
		keywrds1['Activit\351s'] = '<strong><ul><li>Activit&eacute;s : </li></ul></strong>';
		keywrds1['Contenu'] = '<strong><ul><li>Contenu de la s&eacute;ance : </li></ul></strong>';
		keywrds1['Corr. Exos'] = '<ul><li>Correction des exercices </li></ul>';
		keywrds1['Corr. Dev'] = '<ul><li>Correction du devoir </li></ul> ';
		keywrds1['Fichier joint'] = '(Voir fichier joint &agrave; cette s&eacute;ance) ';
		keywrds1['Interro'] = '<strong><ul><li>Interrogation &eacute;crite : </li></ul></strong>';
		keywrds1['Vie class'] = '<strong><ul><li>Vie de classe : </li></ul></strong>';
		keywrds1['Dev. Mais.'] = '<strong><ul><li>Devoir &agrave; la maison &agrave; r&eacute;diger sur copie double : </li></ul></strong>';
		keywrds1['Prep. exerc.'] = '<strong><ul><li>Pr&eacute;paration &agrave; r&eacute;diger sur le cahier d\'exercices : </li></ul></strong>';
		
		xinha_config.InsertWords = {
			combos : [ { options: keywrds1, context: "body" },
			]
		}
		xinha_editors = Xinha.makeEditors(xinha_editors, xinha_config, xinha_plugins);
		
		xinha_editors.a_faire_1.config.height = '200px';
		<?php if (!isset($_GET['ds_prog'])){
		echo "xinha_editors.a_faire_2.config.height = '200px';";
		echo "xinha_editors.a_faire_3.config.height = '200px';";
		}
		else {
		echo "xinha_editors.a_faire_1.config.width = '680px';";
		}
		?>
		
		Xinha.startEditors(xinha_editors);
		
	}
}
Xinha.addOnloadHandler(xinha_init);
</script>
