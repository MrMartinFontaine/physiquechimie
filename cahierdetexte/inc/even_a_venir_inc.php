<?php 
//=============================================evenements à venir==============================================================messa_evene


//      Att:date en yyyymmjj pour sql    WHERE  (( date_fin <= {date('Ymd')+7} ) AND ( date_debut >= {date('Ymd')} )) 	

			$datemini=date('Ymd', strtotime('+7 day')); 
			$datemaxi=date('Ymd', strtotime('+14 day')) ;
			$query_even_dans_1sem="SELECT COUNT(*) FROM cdt_evenement_contenu WHERE ( date_debut >= {$datemini} AND date_debut <= {$datemaxi} ) ";
			$dans_1sem = mysqli_query($conn_cahier_de_texte, $query_even_dans_1sem) or die(mysqli_error($conn_cahier_de_texte));
			$datemini=date('Ymd', strtotime('+14 day')); 
			$datemaxi=date('Ymd', strtotime('+21 day')) ;
			$query_even_dans_2sem="SELECT COUNT(*) FROM cdt_evenement_contenu WHERE ( date_debut >= {$datemini} AND date_debut <= {$datemaxi} ) ";
			$dans_2sem = mysqli_query($conn_cahier_de_texte, $query_even_dans_2sem) or die(mysqli_error($conn_cahier_de_texte));	
			$nb_dans_1sem= mysqli_data_seek($dans_1sem,0);  $nb_dans_2sem= mysqli_data_seek($dans_2sem,0);  
			if (($nb_dans_1sem==0) || ($nb_dans_2sem==0)) // on affiche les even apres les vac
			{ $datemaxi=date('Ymd', strtotime('+27 day')); } else { $datemaxi=date('Ymd', strtotime('+13 day')); };
			$datemini=date('Ymd'); // format $date...  "20131230"

?>			