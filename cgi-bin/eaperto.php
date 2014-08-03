<?php
/* Questo e' un descrittore per il progetto di tecweb
Descrittore
Nome:E'aperto
input:ORARIO1,ORARIO2
outuput:bulean
crediti:3
Descrittore che prende in input un multiintervallo di orario (ad es:lun,mar,mer: 1000-1300,1530-2000. giov,ven,sab:1000-1230, 
1630-2000.) ee un intervallo semplice(es.:mar:0930:1100) e verifica se la posizione del secondo orario nel primo e parzialmente interna(apero-vero) o no (chiuso-falso)
Autore:Orgest shehaj
*/
//importo le configurazioni generiche
/*$key="mar:0930-1100";
$valore="Mon,Tue,Wed,Thu,Fri,Sun:1230-1930.Mar,TGio,asd,sulcamello,sucamelo,Sun:1430-1630.";*/
include ('./config/metaMarket.conf');
include ('./config/miahttp.conf');
include ('./config/funzioneora.php');
//$mimeType= "text/plain";
$head="Content-type: text/plain; charset=UTF-8";
$query=count($_GET);
if($query != 2){
	$error="errore i parametri devono essere 2";
	sendHTTPResponse($HTTP_errors[406],$error);
}
else {
	

	$orario1= $_GET['orario1'];
	$orario2= $_GET['orario2'];
	if (empty($orario1)|| empty($orario2)){
		$error="i parametri sono vuoti, inserisci dei valori";
		sendHTTPResponse($HTTP_errors[406],$error);
	}

	$value=valutazione($orario1,$orario2);
	//valuto se la stringa in output dal narratore e ben formata
	if(!preg_match ("/^((([a-z][a-z][a-z],){0,6})([a-z]{0,3}):([01][0-9]|2[0-3])([0-5][0-9])-([01][0-9]|2[0-3])([0-5][0-9]),([01][0-9]|2[0-3])([0-5][0-9])-([01][0-9]|2[0-3])([0-5][0-9]).){0,2}$/",$orario1)){
		$error="l'orario 1 non e ben formatto";
		sendHTTPResponse($HTTP_errors[406],$error);
	}
	//valuto se la stringa in input dal utente e ben formatta
	else if (!preg_match("/^(([a-z]){0,3}):([01][0-9]|2[0-3])([0-5][0-9])-([01][0-9]|2[0-3])([0-5][0-9])$/",$orario2)){
		$error="l'orario2 non e ben formatto riprova porca madonna";
		sendHTTPResponse($HTTP_errors[406],$error);
	}
	//dopo il controllo il risultato che potra tornare sara' 1 ovvero 'e' aperto' oppure 2 'e chiuso'	
	else if($value==1){
		$body="e aperto";
	}
		
	else {
		$body="e chiuso";
	}
	sendHTTPResponse($head,$body);
}

//orario1=tue, mon, fri: 1000-1300, 1500-1900. sat, sun: 0900-1400, 1600-20.&orario2=sat: 1300-1700

?>

