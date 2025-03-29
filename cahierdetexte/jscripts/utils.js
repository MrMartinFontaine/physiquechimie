/************************************************************
Cette librairie centralise les fonctions javascripts
utilises par les differentes pages sur projet

************************************************************/


function showDiv(div){

	  var div_elem = document.getElementById(div);

	  if ( div_elem.style.display == "none" )
	    {
	      div_elem.style.display = "block";	    
	    }
	  else
	    {
	      div_elem.style.display = "none";	     
            }       
}




function ouvre_popup(page) {
       window.open(page,"_blank","toolbar=no,location=no,directories=no,status=no,scrollbars=yes,resizable=yes,copyhistory=no, fullsceen=yes");
}

function ouvre_popup_size(page,width,height) {
       window.open(page,"_blank","toolbar=no,location=no,directories=no,status=no,scrollbars=yes,resizable=yes,copyhistory=no, width="+ width +", height="+ height);
}


function acquite_absent(ele_absent_id, ele_absent_statut) {


	   	var xhr_object = null; 
		var _response = null;
		var _ele_absent_statut = null;

		if ( ele_absent_statut == true ) {
			_ele_absent_statut = 1;
		} else {
			_ele_absent_statut = 0;
		}
	     
		if (window.XMLHttpRequest) // Firefox 
	      	xhr_object = new XMLHttpRequest(); 
	   	else if (window.ActiveXObject) // Internet Explorer 
	      	xhr_object = new ActiveXObject("Microsoft.XMLHTTP"); 
	   	else { 
	   		// XMLHttpRequest non supporte par le navigateur 
	      	alert("Votre navigateur ne supporte pas les objets XMLHTTPRequest..."); 
	      	return; 
	   	} 
	   	xhr_object.open("POST", "./ajax_absence.php", true);
	   	xhr_object.onreadystatechange = function() { 
	    	if (xhr_object.readyState == 4) {
	          	_response = xhr_object.responseText; 
			$("#acquittement").html(_response );
			$("#acquittement").fadeIn("slow").delay(800).fadeOut("slow");
	        }
	   	}	 
	 	xhr_object.setRequestHeader("Content-type", "application/x-www-form-urlencoded"); 
	   	//xhr_object.send("ele_absent_id=" + ele_absent_id + "&id_viesco_prof=" + id_viesco_prof + "&ele_absent_statut=" + _ele_absent_statut  ); 	
		xhr_object.send("ele_absent_id=" + ele_absent_id + "&ele_absent_statut=" + _ele_absent_statut  ); 	 
		 	
 
}


function majElevesListe()
{
	
   var classes = new Array();
   var current_selected_ele = new Array();

   var cases = document.getElementsByTagName('input');   // on recupere tous les INPUT
   for(var i=0; i<cases.length; i++)     // on les parcourt
      if(cases[i].type == 'checkbox') {    // si on a une checkbox...
        if ( cases[i].checked ) {
			  classes.push(cases[i].id);
		}     // ... on la coche ou non		
	  }
  // if (classes.length > 0) {
        // On recupere les eleves deja selectionnes
         $("#gic_eleves option:selected").each(
                function(j){
                  current_selected_ele.push( $(this).val());
                });
    
          // On recupere la liste des eleves des classes selectionnees
		 $.post('ajax_groupe_interclasses.php', {  
			 classes:classes  
              }, function(data){
				$("#gic_eleves").html( data );
                 // On remet les eleves deja selectionnes
                $("#gic_eleves option").each(
                function(j){
                  var ele = $(this).val();
                  if (jQuery.inArray( ele,current_selected_ele) > -1 ) {
                    $(this).attr("selected","selected");
                  }
                 }
                );           
                
		     }); 
}


function findPos(obj) {
   var curtop = 0;
     if (obj.offsetParent) {
       do {
         curtop += obj.offsetTop;
       } while (obj = obj.offsetParent);
     }
     return [curtop];
}

function findLeft(obj) {
   var curleft = 0;
     if (obj.offsetParent) {
       do {
         curleft += obj.offsetLeft;
       } while (obj = obj.offsetParent);
     }
     return [curleft];
}


function getVar(name)   //from http://scripts.franciscocharrua.com/javascript-get-variables.php
         {
         get_string = document.location.search;         
         return_value = '';
         
         do { //This loop is made to catch all instances of any get variable.
            name_index = get_string.indexOf(name + '=');
            
            if(name_index != -1)
              {
              get_string = get_string.substr(name_index + name.length + 1, get_string.length - name_index);
              
              end_of_value = get_string.indexOf('&');
              if(end_of_value != -1)                
                value = get_string.substr(0, end_of_value);                
              else                
                value = get_string;                
                
              if(return_value == '' || value == '')
                 return_value += value;
              else
                 return_value += ', ' + value;
              }
            } while(name_index != -1)
            
         //Restores all the blank spaces.
         space = return_value.indexOf('+');
         while(space != -1)
              { 
              return_value = return_value.substr(0, space) + ' ' + 
              return_value.substr(space + 1, return_value.length);
							 
              space = return_value.indexOf('+');
              }
          
         return(return_value);        
         }


$(document).ready(function() {
    $("#tabs").tabs().tabs('select', getVar('option'));
});