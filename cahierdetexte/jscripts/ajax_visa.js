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

function go_visa(taVariable,taVariable2){
getXhr();
xhr.onreadystatechange = function(){
 
if(xhr.readyState == 4 && xhr.status == 200){
monretour = xhr.responseText;
document.getElementById('image'+taVariable).innerHTML = '<img onclick ="go_visa_supprime('+taVariable+','+taVariable2+')" src="images/visa.gif">';
}
}

xhr.open("POST","direction/ajax_visa.php",true);
xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
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
xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
xhr.send("ID_agenda="+taVariable+"& numprof="+taVariable2);

}



