/* Including core.js */	
var script = document.createElement('script');
script.type = 'text/javascript';
script.src = _editor_url + '/plugins/xinha_wiris/core/core.js';
document.getElementsByTagName('head')[0].appendChild(script);

/* Configuration */
var _wrs_conf_editorEnabled = true;
var _wrs_conf_CASEnabled = true;

var _wrs_conf_imageMathmlAttribute = 'alt';			// Tag where save mathml code on formula editor. <img [the tag]="..." />
var _wrs_conf_CASMathmlAttribute = 'alt';			// Tag where save mathml code on CAS editor. <img [the tag]="..."  />

var _wrs_conf_editorPath = _editor_url + '/plugins/xinha_wiris/integration/editor.php';		// Editor window.
var _wrs_conf_editorAttributes = 'width=500, height=400, scroll=no, resizable=yes';			// Editor window attributes
var _wrs_conf_CASPath = _editor_url + '/plugins/xinha_wiris/integration/cas.php';			// CAS window
var _wrs_conf_CASAttributes = 'width=640, height=480, scroll=no, resizable=yes';			// CAS window attributes.

var _wrs_conf_createimagePath = _editor_url + '/plugins/xinha_wiris/integration/createimage.php';		// Script to create images.
var _wrs_conf_createcasimagePath = _editor_url + '/plugins/xinha_wiris/integration/createcasimage.php';	// Script to create CAS images.

var _wrs_conf_saveMode = 'tags';		// this value can be 'tags', 'xml' or 'safeXml'.

/* Vars */
var _wrs_int_editorIcon = _editor_url + '/plugins/xinha_wiris/core/wiris-formula.gif';
var _wrs_int_CASIcon = _editor_url + '/plugins/xinha_wiris/core/wiris-cas.gif';
var _wrs_int_temporalIframe;
var _wrs_int_window;
var _wrs_int_window_opened = false;
var _wrs_int_temporalImageResizing;
var _wrs_int_language = _editor_lang;

/* Plugin integration */
function xinha_wiris(editor) {
	this.editor = editor;
	var thisInstance = this;
	
	Xinha.addDom0Event(editor._textArea.form, 'submit', function () {
		editor._textArea.value = wrs_endParse(editor._textArea.value);
	});
	
	function whenDocReady() {
		if (window.wrs_initParse) {
			editor.setEditorContent(wrs_initParse(editor._textArea.value));
			wrs_addIframeEvents(editor._iframe, wrs_int_doubleClickHandler, wrs_int_mousedownHandler, wrs_int_mouseupHandler);
		}
		else {
			setTimeout(whenDocReady, 50);
		}
	}
	
	editor.whenDocReady(whenDocReady);
	
	if (_wrs_conf_CASEnabled) {
		editor.config.registerButton({
			id: 'xinha_wiris-CAS',
			tooltip: 'WIRIS CAS',
			image: _wrs_int_CASIcon,
			textMode: false,
			action: function (editor) {
				thisInstance.CASButtonPress(editor);
			}
		});
		
		editor.config.addToolbarElement('xinha_wiris-CAS', 'superscript', 1);
	}
	
	if (_wrs_conf_editorEnabled) {
		editor.config.registerButton({
			id: 'xinha_wiris-formula',
			tooltip: 'Formula editor',
			image: _wrs_int_editorIcon,
			textMode: false,
			action: function (editor) {
				thisInstance.formulaEditorButtonPress(editor);
			}
		});
		
		editor.config.addToolbarElement('xinha_wiris-formula', 'superscript', 1);
	}
}

xinha_wiris._pluginInfo = {
	name: 'xinha_wiris',
	version: '1.0',
	developer: 'Juan Lao Tebar',
	developer_url: 'http://www.wiris.com',
	sponsor: 'Maths for More',
	sponsor_url: 'http://www.mathsformore.com',
	license: 'privative'
};

xinha_wiris.prototype.formulaEditorButtonPress = function (editor) {
	wrs_int_openNewFormulaEditor(editor._iframe);
};

xinha_wiris.prototype.CASButtonPress = function (editor) {
	wrs_int_openNewCAS(editor._iframe);
};

/* Functions */

/**
 * Opens formula editor.
 * @param object iframe Destination iframe
 */
function wrs_int_openNewFormulaEditor(iframe) {
	if (_wrs_int_window_opened) {
		_wrs_int_window.focus();
	}
	else {
		_wrs_int_window_opened = _wrs_isNewElement = true;
		_wrs_int_temporalIframe = iframe;		
		_wrs_int_window = window.open(_wrs_conf_editorPath, 'WIRISFormulaEditor', _wrs_conf_editorAttributes);
	}
}

/**
 * Opens CAS.
 * @param object iframe Destination iframe
 */
function wrs_int_openNewCAS(iframe) {
	if (_wrs_int_window_opened) {
		_wrs_int_window.focus();
	}
	else {
		_wrs_int_window_opened = _wrs_isNewElement = true;
		_wrs_int_temporalIframe = iframe;
		_wrs_int_window = window.open(_wrs_conf_CASPath, 'WIRISCAS', _wrs_conf_CASAttributes);
	}
}

/**
 * Handles a double click on the iframe.
 * This function provides the editing formulas feature.
 * @param object iframe Target
 * @param object element Element double clicked
 */
function wrs_int_doubleClickHandler(iframe, element) {
	if (element.nodeName.toLowerCase() == 'img') {
		if (wrs_containsClass(element, 'Wirisformula')) {
			if (!_wrs_int_window_opened) {
				_wrs_temporalImage = element;
				wrs_int_openExistingFormulaEditor(iframe);
			}
		}
		else if (wrs_containsClass(element, 'Wiriscas')) {
			if (!_wrs_int_window_opened) {
				_wrs_temporalImage = element;
				wrs_int_openExistingCAS(iframe);
			}
		}
	}
}

/**
 * Opens formula editor to edit an existing formula.
 * @param object iframe Destination iframe
 */
function wrs_int_openExistingFormulaEditor(iframe) {
	_wrs_int_window_opened = true;
	_wrs_isNewElement = false;
	_wrs_int_temporalIframe = iframe;
	_wrs_int_window = window.open(_wrs_conf_editorPath, 'WIRISFormulaEditor', _wrs_conf_editorAttributes);
}

/**
 * Opens CAS to edit an existing formula.
 * @param object iframe Destination iframe
 */
function wrs_int_openExistingCAS(iframe) {
	_wrs_int_window_opened = true;
	_wrs_isNewElement = false;
	_wrs_int_temporalIframe = iframe;
	_wrs_int_window = window.open(_wrs_conf_CASPath, 'WIRISCAS', _wrs_conf_CASAttributes);
}

/**
 * Handles a mouse down event on the iframe.
 * This function saves the clicked image for future uses (for example, prohibits resizing or takes its formula code).
 * @param object iframe Target
 * @param object element Element mouse downed
 */
function wrs_int_mousedownHandler(iframe, element) {
	if (element.nodeName.toLowerCase() == 'img') {
		if (wrs_containsClass(element, 'Wirisformula') || wrs_containsClass(element, 'Wiriscas')) {
			_wrs_int_temporalImageResizing = element;
		}
	}
}

/**
 * Handles a mouse up event on the iframe.
 * This function prohibits formula resizing.
 * @param object iframe Target
 * @param object element Element mouse downed
 */
function wrs_int_mouseupHandler(iframe, element) {
	if (_wrs_int_temporalImageResizing) {
		setTimeout(function () {
			with (_wrs_int_temporalImageResizing) {
				removeAttribute('style');
				removeAttribute('width');
				removeAttribute('height');
			}
		}, 10);
	}
}

/**
 * Calls wrs_updateFormula with well params.
 * This function is called when you click on "Ok" button in editor window.
 * This function must call wrs_updateFormula with iframe param and mathml param.
 * @param string mathml
 */
function wrs_int_updateFormula(mathml) {
	wrs_updateFormula(_wrs_int_temporalIframe, mathml);
}

/**
 * Calls wrs_updateCAS with well params. This function must call wrs_updateCAS with iframe param, mathml param, image param, width param and height param.
 * @param string appletCode
 * @param string image
 * @param int width
 * @param int height
 */
function wrs_int_updateCAS(appletCode, image, width, height) {
	wrs_updateCAS(_wrs_int_temporalIframe, appletCode, image, width, height);
}

/**
 * Handles window closing.
 * This function is called when you closes editor or CAS window.
 */
function wrs_int_notifyWindowClosed() {
	_wrs_int_window_opened = false;
}
