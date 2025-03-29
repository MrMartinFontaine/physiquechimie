var xhr = null;
function getXhr(){
				if(window.XMLHttpRequest) // Firefox et autres
				   xhr = new XMLHttpRequest();
				else if(window.ActiveXObject){ // Internet Explorer
				   try {
			                xhr = new ActiveXObject("Msxml2.XMLHTTP");
			            } catch (e) {
			                xhr = new ActiveXObject("Microsoft.XMLHTTP");
			            }
				}
				else { // XMLHttpRequest non supporté par le navigateur
				   alert("Votre navigateur ne supporte pas les objets XMLHTTPRequest...");
				   xhr = false;
				}
			}
			/**
			* Méthode qui sera appelée sur le click du bouton
			*/

function go_visa(taVariable,taVariable2){
getXhr();
xhr.onreadystatechange = function(){
 
if(xhr.readyState == 4 && xhr.status == 200){
monretour = xhr.responseText;
document.getElementById('image'+taVariable).innerHTML = '<img onclick ="go_visa_supprime('+taVariable+','+taVariable2+')" src="images/visa.gif">';
}
}

xhr.open("POST","direction/ajax_visa.php",true);
xhr.setRequestHeader ('Content-Type','application/x-www-form-urlencoded','charset=iso-8859-1');
xhr.send("ID_agenda="+taVariable+"& numprof="+taVariable2);

}
 
 
function go_visa_supprime(taVariable,taVariable2){
getXhr();
xhr.onreadystatechange = function(){
 
if(xhr.readyState == 4 && xhr.status == 200){
monretour = xhr.responseText;
document.getElementById('image'+taVariable).innerHTML = '<img  onclick ="go_visa('+taVariable+','+taVariable2+')" src="images/tampon3.gif">';
}
}

xhr.open("POST","direction/ajax_visa_supprime.php",true);
xhr.setRequestHeader ('Content-Type','application/x-www-form-urlencoded','charset=iso-8859-1');
xhr.send("ID_agenda="+taVariable+"& numprof="+taVariable2);

}

/*
function go_absents_du_jour(classe_ID,groupe,gic_ID,matiere_ID,heure,code_date){
				getXhr();
				xhr.onreadystatechange = function(){
					if(xhr.readyState == 4 && xhr.status == 200){
						leselect = xhr.responseText;
						document.getElementById('d4').innerHTML = leselect;
					}
				}
				xhr.open("POST","ajax_absents_du_jour.php",true);
xhr.setRequestHeader ('Content-Type','application/x-www-form-urlencoded','charset=iso-8859-1');

				xhr.send("classe_ID="+classe_ID+"&gic_ID="+gic_ID+"&groupe="+groupe+"&matiere_ID="+matiere_ID+"&heure="+heure+"&code_date="+code_date);
			}
*/

function go_progression(){
				getXhr();
				xhr.onreadystatechange = function(){
					if(xhr.readyState == 4 && xhr.status == 200){
						leselect = xhr.responseText;
						document.getElementById('liste_progression').innerHTML = leselect;
					}
				}
				xhr.open("POST","ajax_progression.php",true);
xhr.setRequestHeader ('Content-Type','application/x-www-form-urlencoded','charset=iso-8859-1');

				xhr.send(null);
			}

function go_devoirs(date,classe){
				getXhr();
				xhr.onreadystatechange = function(){
					if(xhr.readyState == 4 && xhr.status == 200){
						leselect = xhr.responseText;
						alert(leselect);
					}
				}
				xhr.open("POST","ajax_devoirs.php",true);
xhr.setRequestHeader ('Content-Type','application/x-www-form-urlencoded','charset=iso-8859-1');

				xhr.send("date="+date+"&classe="+classe);
			}