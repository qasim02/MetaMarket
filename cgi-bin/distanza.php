<?php
/*Descrittore Distanza:
input:lat1,long1,lat2,long2
output:distanza(mt)
crediti:3
Descrittore che prende in input due luogghi e ne calcola la distanza in metri. 
*/
/* importo le configurazioni generiche */
include( 'config/metaMarket.conf');
include( 'config/http.conf');
$head = "Content-type: text/plain; charset=UTF-8";
$query = count($_GET);
/*Se rappresento il luogo A con la coppia (a1,b1) 
 * in cui a1 e' la longitudine e b1 e la latitudine
 * ed il luogo B con la coppia (a2, b2) dove a2 e' la longiutdine del luogo 2
 * e b2 e' la latitudine del luogo 2 la distanza tra A e B e' data dalla formula :
 *  d(A,B)= arcos (cos(a1-a2)cos(b1)cos(b2)+ sin (b1) sin(b2) */
 /* la latitudine varia da 0-90 gradi nord e da 0-90 gradi sud dove 0 equivale al equatore
  *  mentre la longitudine varia da 0-180 gradi est e da 0-180 gradi ovest
*/
if ($query != 4) { 					// devono essere presenti almeno 4 parametri
                $error="errore i parametri richiesti devono essere 4";
                sendHTTPResponse($HTTP_errors[406], $error);
 }
        else {
               if(!is_numeric($_GET['lat1'])){
                         $error3="errore il valore di lat1 e' sbagliato";
                        sendHTTPResponse($HTTP_errors[406] , $error3);
		}
               else if (!is_numeric ($_GET['long1'])){
                        $error4="errore il valore di long1 non e stato imposto corretamente";
                        sendHTTPResponse($HTTP_errors[406],$error4);}
               else  if (!is_numeric ($_GET['lat2'])){
                        $error5="errore il valore di lat2 non e stato imposto corretamente";
                        sendHTTPResponse($HTTP_errors[406], $error5);}
               else  if (!is_numeric ($_GET['long2'])){
                        $error6="errore il valore di long2 non e stato imposto corretamente";
                        sendHTTPResponse($HTTP_errors[406],$error6);}
                $lat1 = floatval (trim($_GET['lat1']));
                $long1 = floatval(trim($_GET['long1']));
                $lat2 = floatval(trim($_GET['lat2']));
                $long2 = floatval(trim($_GET['long2']));

                if ($lat1 < -90 || $lat1 > 90){
                        $errore1="406 , Not Acceptable, la latitudine e' errata";
                                sendHTTPResponse($HTTP_errors[406],$errore1);
		}

                else if ($lat2 < -90 || $lat2 > 90){
                        $errore7="406 , Not Acceptable , la latitudine e' errata";
                                sendHTTPResponse($HTTP_errors[406],$errore7);
		}

                else if ($long1 < -180 || $long1 > 180){
                        $errore8="406 , Not Acceptable , la longitudine e' errata";
                        sendHTTPResponse($HTTP_errors[406],$errore8);
		}

 	       else if ($long2 < -180 || $long2 > 180){
        	        $errore9="406 , Not Acceptable , la longitudine e' errata";
        	        sendHTTPResponse($HTTP_errors[406],$errore9);
		}
		else if (empty($lat1) || empty($lat2) || empty( $long1) || empty( $long2)){
			$errore="406, Not Acceptable, i campi non possono essere vuoti";
			sendHTTPResponse($HTTP_errors[406],$errore);
		}     
   		
		else {
                //$dist = acos(cos($long1-$long2)cos($lat1)cos($lat2)+sin($lat1)sin($lat2));    
                  	$Raggterr = 6371000; //è il raggio della terra in metri
                        $latinrad = deg2rad($lat2-$lat1);//deg2rad converte i gradi in radianti
                        $loninrad = deg2rad($long2-$long1);
                        $lat1 = deg2rad($lat1);
                        $lat2 = deg2rad($lat2);
                     	$a = sin($latinrad/2) * sin($latinrad/2) + sin($loninrad/2) * sin($loninrad/2) * cos($lat1) * cos($lat2);
                        $c = 2 * atan2(sqrt(abs($a)), sqrt(1-abs($a)));
			//la funzione floor serve per arrotondare un numero decimale
                        $body = floor(($Raggterr * $c) * 100) * .01;
                       sendHTTPResponse($head,$body);
        	}
}

?>
