// Adaptation du plugin EditLink pour le Cahier de Textes
// Character Map plugin for HTMLArea
// Sponsored by http://www.systemconcept.de
// Implementation by Holger Hees based on HTMLArea XTD 1.5 (http://mosforge.net/projects/htmlarea3xtd/)
// Original Author - Bernhard Pfeifer novocaine@gmx.net 
//
// (c) systemconcept.de 2004
// Distributed under the same terms as HTMLArea itself.
// This notice MUST stay intact for use (see license.txt).

function EditLink(editor) {
  this.editor = editor;
	var cfg = editor.config;
	var self = this;
        
	cfg.registerButton({
                id       : "editlink",
                tooltip  : this._lc("Insérer un lien vers un de mes fichiers"),
                image    : editor.imgURL("ed_edit_link.gif", "EditLink"),
                textMode : false,
                action   : function(editor) {
                             self.buttonPress(editor);
                           }
            });

	cfg.addToolbarElement("editlink", "htmlmode",2);

}


EditLink._pluginInfo = {
	name          : "EditLink",
	version       : "1.0",
	developer     : "Pierre Lemaitre ",
	developer_url : "",
	c_owner       : "",
	sponsor       : "Lindt Poulain",
	sponsor_url   : "",
	license       : "htmlArea"
};

EditLink.prototype._lc = function(string) {
    return HTMLArea._lc(string, 'EditLink');
};

EditLink.prototype.buttonPress = function(editor) {
//   if (typeof link == "undefined") {
        link = editor.getParentElement();
        if (link) {
           while (link && !/^a$/i.test(link.tagName))
           link = link.parentNode;
        }
//   }
   if (!link) {
    var sel = editor._getSelection();
    var range = editor._createRange(sel);
    var compare = 0;
    if (HTMLArea.is_ie) {
      if(sel.type == "Control")
      {
        compare = range.length;
      }
      else
      {
        compare = range.compareEndPoints("StartToEnd", range);
      }
    } else {
      compare = range.compareBoundaryPoints(range.START_TO_END, range);
    }
    if (compare == 0) {
      alert("Vous devez sélectionner du texte avant de créer un lien");
      return;
    }
    outparam = {
      f_href : '',
      f_title : '',
      f_target : '',
      f_usetarget : editor.config.makeLinkShowsTarget
    };
  } else
    outparam = {
      f_href   : HTMLArea.is_ie ? editor.stripBaseURL(link.href) : link.getAttribute("href"),
      f_title  : link.title,
      f_target : link.target,
      f_usetarget : editor.config.makeLinkShowsTarget
    };



	editor._popupDialog( "../plugins/EditLink/popups/edit_link.php", function( param ) {
	  if (!param)
            return false;
          var a = link;
          if (!a) try {
             editor._doc.execCommand("createlink", false, param.f_href);
             a = editor.getParentElement();
             var sel = editor._getSelection();
             var range = editor._createRange(sel);
             if (!HTMLArea.is_ie) {
                a = range.startContainer;
                  if (!/^a$/i.test(a.tagName)) {
                     a = a.nextSibling;
                       if (a == null)
                          a = range.startContainer.parentNode;
                  }
             }
             } catch(e) {}
          else {
               var href = param.f_href.trim();
               editor.selectNodeContents(a);
               if (href == "") {
                  editor._doc.execCommand("unlink", false, null);
                  editor.updateToolbar();
                  return false;
                }
                else {
                     a.href = href;
                }
        }
        if (!(a && /^a$/i.test(a.tagName)))
           return false;
        a.target = param.f_target.trim();
        a.title = param.f_title.trim();
        editor.selectNodeContents(a);
        editor.updateToolbar();


	}, outparam);
};
