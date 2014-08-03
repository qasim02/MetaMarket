<?php

/*****
questo e' il descrittore aprira, prende in input due orari:

orario1) opening del posto. l'opening puo essere passato in due forme diverse :
		 I) nel formato day1, day2: hhmm-hhmm, hhmm-hhmm. day e' scritto con le prime 3 lettere del nome del giorno in inglese es. mon, tue: 1200-1600.
		II) nel formato yyyy-mm-dd, yyyy-mm-dd: hhmm-hhmm, hhmm-hhmm.  es. 2012-06-12, 2012-09-23: 1900-2300.
		 N.B. queste due forme possono essere date anche promisquamente, il descrittore li elaborera tranquillamente

orario2) intervallo del orario che si vuole confrontare. anche questo intervallo puo ricevere due forme diverse di orario:
		 I) nel formato day: hhmm-hhmm. dove day e' nel formato descritto in precedenza per orario1
		II) nel formato yyyy-mm-dd: hhmm-hhmm.
		 N.B. qua la forma e' una dei due perche' riceve solo un giorno e un intervallo.

restituisce un numero a seconda del confronto:
	I) restituisce [-1] se manca uno dei due parametri.
   II) restituisce [ 0] se nel orario2 l'ora iniziale e finale risultano chiusi nel giorno specificato. CHIUSO.
  III) restituisce [ 1] se nel orario2 l'ora iniziale risulta chiusa e l'ora finale risulta aperta nel giorno specificato. APRIRA. 
   IV) restituisce [ 2] se nel orario2 l'ora iniziale e finale risultano aperti nel giorno specificato. APERTO.
    V) restotuisce [ 3] se nel orario2 l'ora iniziale risulta aperta e l'ora finale risulta chiusa nel giorno specificato. CHIUDERA. 
   VI) restituisce [ 4] questa e' diversa dalle altre e viene restituita solo nel caso che l'orario1, nel giorno specificato contenga un orario nel formato day: intervallo1, intervallo2. se nel orario2 l'intervallo dato risulta 3 nel intervallo1 e 1 nel intervallo2 del orario1 risponde con questo numero.  E' APERTO MA LO SI POTREBBE TROVARE CHIUSO.
*****/
/*%%%%%% CREATORE MICHEL GOITOM TUCCU %%%%%%%%*/
$headers = "Content-Type: text/plain; charset=UTF-8";
require_once("./config/aprira_lib.conf");
require_once("./config/http.conf");

$da_get = $_GET;
$orario1 = $da_get['orario1'];
$orario2 = $da_get['orario2'];

function main ($orario1, $orario2) {

    if (($orario1 == null) || ($orario2 == null)) {return $risultato = -1; } //si attiva solo nel caso in cui manca qualche parametro [orarioX]

    $orario1 = aggiusta ($orario1); //mette aposto l'orario togliendo spazi al inizio e alla fine della stringa e lo rendende tutto in minuscolo
    $orario2 = aggiusta ($orario2); // stesso di sopra

    $new_orario2 = value_expl ($orario2); //orario2 viene trasformato in 1 array dove 0 => giorno, 1 => apertura, 2 => chiusura
    $new_orario1 = seleziona ($orario1, $new_orario2[0]); //orario1 conterra in 0 => giorni, 1 => primo range [ 0 => apertura, 1 => chiusura], lo stesso per 2, 
    
    $inizio = null; //variabile contenete il l'orario iniziale del giorno preso dal intervallo dato dal utente
    $fine = null;	//uguale a sopra

    if (!(is_hour ($new_orario2[1])) || !(is_hour ($new_orario2[2]))) { // controlla se gli orari dati dal utente sono orari o numeri a caso.
        sendHTTPResponse ($http_errors[406], "error: uno dei due orari non è corretto"); 
    }
    else  { $inizio = $new_orario2[1]; $fine = $new_orario2[2]; } //assegna i valori di inizio e fine
    
    $risultato = calcola ($inizio, $fine, $new_orario1); //chiama la funzione che fara' tutto il calcolo
    return $risultato;

}

$results = main ($orario1, $orario2); //variabile che chiama la main con i due dati in input 
sendHTTPResponse ($headers, $results); //restituzione del dato via sendHTTPResponse.

?>
