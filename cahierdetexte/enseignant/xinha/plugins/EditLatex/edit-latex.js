// Character Map plugin for HTMLArea
// Sponsored by http://www.systemconcept.de
// Implementation by Holger Hees based on HTMLArea XTD 1.5 (http://mosforge.net/projects/htmlarea3xtd/)
// Original Author - Bernhard Pfeifer novocaine@gmx.net 
//
// (c) systemconcept.de 2004
// Distributed under the same terms as HTMLArea itself.
// This notice MUST stay intact for use (see license.txt).
// Adaptation de EditTag en editeur Latex _ Pierre Lemaitre
// Modification Philippe SIMIER

function EditLatex(editor) {
  this.editor = editor;
	var cfg = editor.config;
	var self = this;
        
	cfg.registerButton({
                id       : "editlatex",
                tooltip  : this._lc("Editer une expression mathématiques sous LATEX"),
                image    : editor.imgURL("ed_edit_latex.png", "EditLatex"),
                textMode : false,
                action   : function(editor) {
                             self.buttonPress(editor);
                           }
            });

	cfg.addToolbarElement("editlatex", "htmlmode",1);

}

EditLatex._pluginInfo = {
	name          : "EditLatex",
	version       : "1.0",
	developer     : "Pierre Lemaitre & Philippe SIMIER",
	developer_url : "",
	c_owner       : "",
	sponsor       : "Lindt Poulain",
	sponsor_url   : "",
	license       : "htmlArea"
};

EditLatex.prototype._lc = function(string) {
    return HTMLArea._lc(string, 'EditLatex');
};

EditLatex.prototype.buttonPress = function(editor) {
	outparam = {
		content : editor.getSelectedHTML()
	};
	editor._popupDialog( "plugin://EditLatex/edit_latex", function( html ) {
		if ( !html ) {  
			return false;
		}
		editor.insertHTML( html );
	}, outparam);
};
