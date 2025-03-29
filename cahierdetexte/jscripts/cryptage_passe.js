// JavaScript Document
function submit_modifpass2()
{ if (document.form1.email.value!=""){
mail=/^[a-zA-Z0-9\-_]+[a-zA-Z0-9\.\-_]*@[a-zA-Z0-9\-_]+\.[a-zA-Z\.\-_]{1,}[a-zA-Z\-_]+/;
if (!mail.test(document.form1.email.value)) {
         alert ("Adresse e-mail invalide !");
         document.form1.email.focus();
         return false;
      }
  }

  p1=document.form1.passe.value;
  p2=document.form1.passe2.value;
		if (p1<>p2) { 

				return true;
 				 }
  				else
  				{ 
  				alert("Vos deux saisies de mot de passe ne sont pas identiques");
  				document.form1.passe.value="";
 				document.form1.passe2.value="";
 				document.form1.passe.focus();
  				return false;
				};
	
}
