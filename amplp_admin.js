// JS utility class, includes a number of PHP clones and useful functions
function wpPluginBuilderClass(pass){}
if (typeof wpPluginBuilderClass != 'undefined') {
	wpPluginBuilderClass.prototype.get_element  = function(id){
		if (document.getElementById) {
			if (document.getElementById( id ) != null) {
				return document.getElementById( id );
			}
		} else {
			if (document.layers) {
				return document[id];
			} else {
				return document.all[id];
			}
		}
		return null;
	}
	wpPluginBuilderClass.prototype.get_content  = function(id){
		var elm = this.get_element( id );
		if (elm != null) {
			if (elm.id != undefined) {
				var tagname = elm.tagName;
				var tag     = tagname.toLowerCase();
				if (tag == 'input') {
					var type = elm.type;
					var typ  = type.toLowerCase();
					if (typ == 'checkbox' || typ == 'radio') {
						if (elm.checked) {
							return " checked";
						}
					} else {
						return elm.value;
					}
				} else if (tag == 'select' || tag == 'button') {
					return elm.value;
				} else {
					return elm.innerHTML;
				}
			}
		}
		return '';
	}
	wpPluginBuilderClass.prototype.set_content  = function(id,content){
		var elm = this.get_element( id );
		if (elm != null) {
			if (elm.id != undefined) {
				var tagname = elm.tagName;
				var tag     = tagname.toLowerCase();
				if (tag == 'input') {
					var type = elm.type;
					var typ  = type.toLowerCase();
					if (typ == 'checkbox' || typ == 'radio') {
						elm.checked = content;
					} else {
						elm.value = content;
					}
				} else if (tag == 'select' || tag == 'button') {
					elm.value = content;
				} else {
					elm.innerHTML = content;
				}
			}
		}
	}
	wpPluginBuilderClass.prototype.getdiv       = function(divname,what,which){
		if (this.get_element( divname ) != null) {
			if (which == null) {
				return this.get_element( divname )[what];
			} else {
				return this.get_element( divname )[what][which];
			}
		}
		return null;
	}
	wpPluginBuilderClass.prototype.commanddiv   = function(divname,what,which,how){
		if (this.get_element( divname ) != null) {
			if (how == null) {
				this.get_element( divname )[what] = which;
			} else {
				this.get_element( divname )[what][which] = how;
			}
		}
	}
	wpPluginBuilderClass.prototype.showdiv      = function(divname){
		this.commanddiv( divname,'style','display','block' );
	}
	wpPluginBuilderClass.prototype.hidediv      = function(divname){
		this.commanddiv( divname,'style','display','none' );
	}
	wpPluginBuilderClass.prototype.togglediv    = function(divname){
		var isit = this.getdiv( divname,'style','display' );
		if (isit != 'none') {
			this.hidediv( divname );
		} else {
			this.showdiv( divname );
		}
	}
	wpPluginBuilderClass.prototype.verify_clear = function(pass){
		if (pass != null) {
			var answer = confirm( pass );
		} else {
			var answer = confirm( "Are you sure you wish to proceed?" );
		}
		if (answer) {
			return true;
		}
		return false;
	}
	wpPluginBuilderClass.prototype.in_array     = function(arr, obj){
		var i = arr.length;
		while (i--) {
			if (arr[i] === obj) {
				return true;
			}
		}
		return false;
	}
	wpPluginBuilderClass.prototype.is_numeric   = function(n){
		return ! isNaN( parseFloat( n ) ) && isFinite( n );
	}
	wpPluginBuilderClass.prototype.get_value    = function(arr,dex,def){
		if (arr[dex] != null) {
			return arr[dex];
		}
		if (def == null) {
			var def = '';
		}
		return def;
	}
	wpPluginBuilderClass.prototype.post_to_url  = function(path, params, target){
		if (params == null) {
			var params = new Array();}
		if (target == null) {
			var target = "_self";}
		var method   = "post";
		var tempform = document.createElement( "form" );
		tempform.setAttribute( "method", "post" );
		tempform.setAttribute( "action", path );
		tempform.setAttribute( "target", target );

		for (var key in params) {
			var hiddenField = document.createElement( "input" );
			hiddenField.setAttribute( "type", "hidden" );
			hiddenField.setAttribute( "name", key );
			hiddenField.setAttribute( "value", params[key] );

			tempform.appendChild( hiddenField );
		}
		document.body.appendChild( tempform );
		tempform.submit();
	}
	wpPluginBuilderClass.prototype.count        = function(obj){
		// var size = 0, key;
		// for (key in obj){if(obj.hasOwnProperty(key)) size++;}
		// return size;
		if (typeof obj === 'array') {
			return obj.length;
		} else {
			var size = 0, key;
			for (key in obj) {
				if (obj.hasOwnProperty( key )) {
					size++;
				}
			}
			return size;
		}
	}
	wpPluginBuilderClass.prototype.array_move   = function(arr, fromIndex, toIndex){
		var element = arr[fromIndex];
		arr.splice( fromIndex, 1 );
		arr.splice( toIndex, 0, element );
		return arr;
	}
	wpPluginBuilderClass.prototype.get_get      = function(param){
		if (param == null) {
			var param = 'all';}
		var sURL = window.document.URL.toString();
		if (sURL.indexOf( "?" ) > 0) {
			var arrParams = sURL.split( "?" );
			if (param == 'base') {
				return arrParams[0];
			}
			var RET            = new Object();
			var arrURLParams   = arrParams[1].split( "&" );
			var arrParamNames  = new Array( arrURLParams.length );
			var arrParamValues = new Array( arrURLParams.length );
			var i              = 0;
			for (i = 0;i < arrURLParams.length;i++) {
				var sParam       = arrURLParams[i].split( "=" );
				arrParamNames[i] = sParam[0];
				if (sParam[1] != "") {
					arrParamValues[i] = unescape( sParam[1] );
				} else {
					arrParamValues[i] = "No Value";
				}
				var nam  = arrParamNames[i];
				RET[nam] = arrParamValues[i];
			}
			if (param == 'all') {
				return RET;
			}

			for (i = 0;i < arrURLParams.length;i++) {
				if (arrParamNames[i] == param) {
					return arrParamValues[i];
				}
			}
			return "No Parameters Found";
		}
		if (param == 'base') {
			return sURL;
		}
		return '';
	}
	wpPluginBuilderClass.prototype.div_pos      = function(div,x_pos, y_pos){
		var d            = document.getElementById( div );
		d.style.position = "absolute";
		d.style.left     = x_pos + 'px';
		d.style.top      = y_pos + 'px';
	}
	wpPluginBuilderClass.prototype.canonize     = function(txt){
		txt = txt.toLowerCase();
		txt = txt.replace( '.-','-' );
		txt = txt.replace( '.','' );
		txt = txt.replace( ',','' );
		txt = txt.replace( /[^\w ]+/g,'' );
		txt = txt.replace( / +/g,'-' );
		return txt;
		// return txt.toLowerCase().replace(/[^\w ]+/g,'').replace(/ +/g,'-');
	}
	wpPluginBuilderClass.prototype.which_mouse_button = function(evt){
		if ( ! evt) {
			var evt = window.event;
		}
		var mouseEvt  = (evt).which;
		var mMouseEvt = evt.button;
		var btn       = '';
		if (mouseEvt == 3) {
			btn = 'right';
		} else if (mouseEvt == 1) {
			btn = 'left';
		} else if (mMouseEvt == 2) {
			btn = 'right';
		} else if (mMouseEvt == 0) {
			btn = 'left';
		}
		return btn;
	}
	wpPluginBuilderClass.prototype.is_array           = function(someVar){
		if ( typeof someVar === 'array' ) {
			return true;
		}
		return false;
	}
	wpPluginBuilderClass.prototype.is_string          = function(someVar){
		if ( typeof someVar === 'string' ) {
			return true;
		}
		return false;
	}
	wpPluginBuilderClass.prototype.get_input_value    = function(id,def){
		var obj = document.getElementById( id );
		if (obj != null) {
			var val = obj.value;
			if (val == null || val == '') {
				return def;
			}
			return val;
		}
		return def;
	}
	wpPluginBuilderClass.prototype.is_number          = function(n){
		return ! isNaN( parseFloat( n ) ) && isFinite( n );
	}
	wpPluginBuilderClass.prototype.money              = function(price,params){
		var dec = 2;
		var pre = '$';
		var com = true;
		var num = false;
		if (params != null) {
			if (params == '') {
				pre = params;
			} else {
				dec = this.get_value( params,'decimal',2 );
				pre = this.get_value( params,'symbol','$' );
				com = this.get_value( params,'commas',true );
				num = this.get_value( params,'number',false );
			}
		}
		if (dec > 4) {
			dec = 4;}
		var val = price.replace( /[^\d]/g, '' );
		if (num == true) {
			return val;}
		var str  = '' + val + '';
		var decs = '';
		if (this.strstr( str,'.' )) {
			var splode = str.split( '.' );
			str        = splode[0];
			decs       = splode[1];
		}
		if (com == true) {
			var cnt  = str.length;
			var digg = 0;
			price    = '';
			for (i = 1;i <= cnt;i++) {
				var dig = str.substr( i * -1,1 );
				if (digg == 3) {
					digg  = 0;
					price = ',' + price;
				}
				price = dig + price;
				digg ++;
			}
			str = price;
		}
		if (dec > 0) {
			len      = decs.length;
			var newt = decs;
			if (len > dec) {
				newt = decs.substr( 0,dec );
			} else if (len < dec) {
				for (i = len;i < dec;i++) {
					newt += '0';}
			}
			decs = '.' + newt;
		} else {
			decs = '';
		}
		return pre + str + decs;
	}
	wpPluginBuilderClass.prototype.strip_to_number    = function(source){var sout = new String( source );return sout.replace( /[^0-9]/g,'' );}
	wpPluginBuilderClass.prototype.strip_to_canonized = function(source){var val = new String( source );val = val.replace( /[^0-9a-z ]/g, '' );return val;}
	wpPluginBuilderClass.prototype.strstr             = function(haystack,needle){if (haystack.indexOf( needle ) >= 0) {
			return true;} else {
			return false;}}

	wpPluginBuilderClass.prototype.get_input_character = function(ev){
		var keyv    = window.event ? ev.keyCode : ev.which;
		var keychar = String.fromCharCode( keyv );
		return keychar;
	}
	wpPluginBuilderClass.prototype.str_replace         = function(find, replace, str){
		return str.replace( new RegExp( /find/, 'g' ), replace );
	}
	wpPluginBuilderClass.prototype.delimitit           = function(input,delim,append){
		if (input != '') {
			input += delim;}
		input += append;
		return input;
	}


	wpPluginBuilderClass.prototype.img_encrypt     = function(string){
		var cc;
		for (cc = 0;cc < this.count( BAD_CHARS_64 );cc++) {
			var narr = string.split( BAD_CHARS_64[cc] );
			string   = narr.join( NEW_CHARS_64[cc] );
		}
		return string;
	}
	wpPluginBuilderClass.prototype.img_decrypt     = function(string){
		var cc;
		for (cc = 0;cc < this.count( NEW_CHARS_64 );cc++) {
			var narr = string.split( NEW_CHARS_64[cc] );
			string   = narr.join( BAD_CHARS_64[cc] );
		}
		return string;
	}
	wpPluginBuilderClass.prototype.htmlin          = function(string){
		var cc;
		for (cc = 0;cc < this.count( BAD_CHARS );cc++) {
			var narr = string.split( BAD_CHARS[cc] );
			string   = narr.join( NEW_CHARS[cc] );
		}
		return string;
	}
	wpPluginBuilderClass.prototype.htmlout         = function(string){
		var cc;
		for (cc = 0;cc < this.count( NEW_CHARS );cc++) {
			var narr = string.split( NEW_CHARS[cc] );
			string   = narr.join( BAD_CHARS[cc] );
		}
		return string;
	}
	wpPluginBuilderClass.prototype.has_bad_chars   = function(string){
		var cc;
		var isthere = false;
		for (cc = 0;cc < this.count( BAD_CHARS );cc++) {
			if (strpos( string,BAD_CHARS[cc] )) {
				isthere = true;
			}
		}
		return isthere;
	}
	wpPluginBuilderClass.prototype.has_new_chars   = function(string){
		var cc;
		var isthere = false;
		for (cc = 0;cc < this.count( NEW_CHARS );cc++) {
			if (strpos( string,NEW_CHARS[cc] )) {
				isthere = true;
			}
		}
		return isthere;
	}
	wpPluginBuilderClass.prototype.strip_bad_chars = function(string){
		var cc;
		for (cc = 0;cc < this.count( BAD_CHARS );cc++) {
			string = this.str_replace( NEW_CHARS[cc],"",string );
		}
		return string;
	}


	wpPluginBuilderClass.prototype.ucwords        = function(string){
		return string.replace( /\w\S*/g, function(txt){return txt.charAt( 0 ).toUpperCase() + txt.substr( 1 ).toLowerCase();} );
	}
	wpPluginBuilderClass.prototype.ucwords_undash = function(string){
		return this.ucwords( string.replace( '-',' ' ) );
	}


	// jQuery is required from here on
	wpPluginBuilderClass.prototype.isset      = function(elm){return jQuery( elm ).length > 0;}
	wpPluginBuilderClass.prototype.swap_class = function(tgt,find,replace){
		if (jQuery( tgt ).hasClass( find )) {
			jQuery( tgt ).removeClass( find );
			jQuery( tgt ).addClass( replace );
		}
	}

	wpPluginBuilderClass.prototype.loading_ball  = function(txt){
		if (txt == null || txt == 'undefined') {
			var txt = '';}
		var html = '<div class="wppb-loadingball"></div>';
		html    += '<div class="wppb-loadingnotice">' + txt + '</div>';
		html    += '<div style="clear:both;"></div>';
		return html;
	}
	wpPluginBuilderClass.prototype.loader        = function(id,mess){
		if ( ! jQuery( '#LOADER_' + id ).length > 0) {
			return '<div id="LOADER_' + id + '" class="wppb-loader">' + wppb.loading_ball( mess ) + '</div>';
		}
	}
	wpPluginBuilderClass.prototype.loader_remove = function(id){
		if (id == null) {
			jQuery( '.wppb-loader' ).remove();
		} else {
			jQuery( '#LOADER_' + id ).remove();
		}
	}

	wpPluginBuilderClass.prototype.is_browser = function(bro){
		if (this.get_value( jQuery.browser,bro ) == true) {
			return true;}
		return false;
	}

	// [NOTE] initiate an instance of the class
	var wppb = new wpPluginBuilderClass( 'init' );
}


// lists of charcater swaps used in wppb translation functions
var BAD_CHARS    = new Array( '&#149;','<br />','<hr />','<','>','&amp;','&','\\','"',"'",';' );
var NEW_CHARS    = new Array( '(_BL_)','(_BR_)','(_HR_)','(_LT_)','(_GT_)','(_AM_)','(_AM_)','(_BS_)','(_DQ_)','(_SQ_)','(_SC_)' );
var BAD_CHARS_64 = new Array( '&#149;','<br />','<hr />','<','>','&','\\','"',"'",';','/','+' );
var NEW_CHARS_64 = new Array( '(_BL_)','(_BR_)','(_HR_)','(_LT_)','(_GT_)','(_AM_)','(_BS_)','(_DQ_)','(_SQ_)','(_SC_)','(_FS_)','(_PL_)' );
function htmlin(nstr){alert( 'htmlin moved to wppb' );}
function htmlout(nstr){alert( 'htmlout moved to wppb' );}


// AJAX functions and handlers
var xmlhttp;
var wppb_arctype;
var wppb_slug;
var wppb_type;
var wppb_postid;
var wppb_termid;
if (window.XMLHttpRequest) {
	AJAXcommunicationsupported = true;} else {
	AJAXcommunicationsupported = false;alert( "Live form data saving is not fully supported in this browser. Some features of this website may not work as intended." );}
	function getXMLHttpRequest(){if (window.XMLHttpRequest) {
			return new window.XMLHttpRequest();} else {
		try {
			return new ActiveXObject( "MSXML2.XMLHTTP.3.0" );} catch (ex) {
			try {
				return new ActiveXObject( "Microsoft.XMLHTTP" );} catch (ex) {
				return null;}}}}
	function ajax_communication(command,fct,params,type,sync){
		if (type == null) {
			var type = "text";
			var file = ajaxurl;
			var pram = "";
			xmlhttp  = getXMLHttpRequest();
			if (xmlhttp != null) {
				if (typeof params === "string") {
					pram = "&" + params;
				} else {
					for (p in params) {
						pram += "&" + p + "=" + params[p];
					}
				}
				pram     += '&arctype=' + wppb_arctype;
				pram     += '&slug=' + wppb_slug;
				pram     += '&pagetype=' + wppb_type;
				pram     += '&postid=' + wppb_postid;
				pram     += '&termid=' + wppb_termid;
				var async = true;
				if (sync == false) {
					var async = false;}
				var parameters = "action=wppb_ajax_communication&command=" + command + pram;
				xmlhttp.open( "POST",file,async );
				xmlhttp.commandtype        = type;
				xmlhttp.commandfunction    = fct;
				xmlhttp.onreadystatechange = ajax_communication_handler;
				xmlhttp.setRequestHeader( "Content-type", "application/x-www-form-urlencoded" );
				xmlhttp.setRequestHeader( "Content-length", parameters.length );
				xmlhttp.setRequestHeader( "Connection","close" );
				xmlhttp.send( parameters );
			}
		}
	}
	function ajax_parse_communication(icom){var RAT = new Array();if (icom != "" && icom.substr( 0,1 ) == "[") {
			var com = icom.substr( 1 );var coms = com.split( "[" );for (cc in coms) {
				var dcom = coms[cc];var dcoms = dcom.split( "]" );var dex = dcoms[0];var data = dcoms[1];var dats = data.split( "&" );RAT[dex] = new Array();for (dd in dats) {
					var fat = dats[dd];var fats = fat.split( "=" );var far = fats[0];var fal = fats[1];RAT[dex][far] = fal;}}}return RAT;}
	function ajax_communication_handler(incoming){
		if (xmlhttp.readyState == 4) {
			if (xmlhttp.status == 200) {
				var type = xmlhttp.commandtype;
				if (type == "xml") {
					var response = xmlhttp.responseXML;
				} else if (type == "text") {
					var response = xmlhttp.responseText;
					var rep      = response.substr( response.length - 1 );
					if (rep == '0' || rep == 0) {
						response = response.substring( 0, response.length - 1 );}
				} else {
					var response = ajax_parse_communication( xmlhttp.responseText );
				}
				xmlhttp.commandfunction( response );
			}
		}
	}

	// FILTERING
	// FORM INPUT CHARACTER FILTERS
	function input_character_filter(ev,str,how){
		var keyv = window.event ? ev.keyCode : ev.which;
		if (wppb.strstr( str,'0-9' )) {
			if (keyv >= 96 && keyv <= 105) {
				return true;}}
		var ok = new Array( 8,13,16,37,38,39,40,46 );
		// backspace, enter, shift, arrows(37-40), delete
		if (wppb.in_array( ok,keyv )) {
			return true;}
		var keychar = String.fromCharCode( keyv );
		if (how == 1) {
			var reg = RegExp( "[^" + str + "]" );} else {
				var reg = RegExp( "[" + str + "]" );}
			return ! reg.test( keychar );
	}
	function inputfilter_number(ev){return input_character_filter( ev,"0-9.",1 );}
	function inputfilter_integer(ev){return input_character_filter( ev,"0-9",1 );}
	function inputfilter_letters(ev){return input_character_filter( ev," A-Za-z",1 );}
	function inputfilter_slug(ev){return input_character_filter( ev,"a-z",1 );}
	function inputfilter_nopunctuation(ev){return input_character_filter( ev," 0-9A-Za-z",1 );}
	function inputfilter_canonized(ev){return input_character_filter( ev," 0-9a-z",1 );}
	function inputfilter_address(ev){return input_character_filter( ev," 0-9A-Za-z,.'\"_()#",1 );}
	function inputfilter_phone(ev){return input_character_filter( ev,"0-9()",1 );}
	function inputfilter_validatedphone(ev){return input_character_filter( ev,"0-9",1 );}
	function inputfilter_safe(ev){return input_character_filter( ev," 0-9A-Za-z',._()#~!@$%*=+{}/<>",1 );}
	function inputfilter_url(ev){return input_character_filter( ev,"0-9a-z:/._#~!@%=+?",1 );}
	function inputfilter_email(ev){return input_character_filter( ev,"0-9A-Za-z._@",1 );}
	function inputfilter_basic(ev){return input_character_filter( ev," 0-9A-Za-z._@!,?'=+#$%",1 );}
	function inputfilter_login(ev){return input_character_filter( ev,"0-9A-Za-z._@!,?=+#$%",1 );}
	function inputfilter_date(ev){return input_character_filter( ev,"0-9/ ",1 );}
	function inputfilter_social(ev){return input_character_filter( ev,"0-9",1 );}
	function inputfilter_domain(ev){return input_character_filter( ev,"0-9a-z.",1 );}
	function amplp_import_demo_content(){
		if (confirm( 'Are you sure you wish to IMPORT DEMO CONTENT for your AMP Landing Page? This will replace the featured image and content (if any).' )) {
			var prams    = new Array();
			prams['tid'] = wppb_postid;
			ajax_communication(
				'ajax_import_demo_content',function(com){
					document.location.reload();
				},prams
			);
		}
	}
