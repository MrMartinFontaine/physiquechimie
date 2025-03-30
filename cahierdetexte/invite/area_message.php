<script type="text/javascript">
var _editor_url  = "../enseignant/xinha/";
var _editor_lang = "fr";
</script>
<!-- Load up the actual editor core -->
<script type="text/javascript" src="../enseignant/xinha/XinhaCore.js"></script>


<script type="text/javascript">

var xinha_plugins =
      [
      ];
var xinha_editors =
[
  'message'
];

function xinha_init()
{
  if(!Xinha.loadPlugins(xinha_plugins, xinha_init)) return;

  var xinha_config = new Xinha.Config();
   xinha_config.toolbar =
	  [
   ["popupeditor","formatblock","fontname","fontsize","bold","italic","underline","forecolor","justifyleft","justifycenter","undo","redo","createlink","insertimage"]

	  ];
  xinha_config.pageStyle = 'body { font-family: verdana,sans-serif; font-size: 11px; color: #000066 }';
  xinha_editors = Xinha.makeEditors(xinha_editors, xinha_config, xinha_plugins);

  Xinha.startEditors(xinha_editors);
}
Xinha.addOnloadHandler(xinha_init);
</script>

