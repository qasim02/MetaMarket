<?php

/*****
questo file contiene l'aggregatore turtle che preso un file turtle a seconda della richiesta filtra il file e restituisce un novo turtle filtrato  
*****/
/*%%%%%% CREATORE MICHEL GOITOM TUCCU %%%%%%%%*/
$headers = "Content-Type: text/turtle; charset=UTF-8";

include_once("./config/aggttl/arc/ARC2.php");           //api per turtle
require_once("./config/aggttl_lib.conf");             	//libreria mia
require_once("./config/aggttl_poste.conf");				//url di posteBO2011
require_once("./config/http.conf");

    /*====inizializzazione del api ARC2 per il parsing e serializing del TURTLE====*/
$serializer = ARC2::getTurtleSerializer();
$parser = ARC2::getTurtleParser();
$parser -> parse($turtle);
$triples = $parser->getTriples();


    /*====input di dati====*/
$da_get = $_GET;          //variabile globale preleva dati da uri

$key = $da_get['key'];      //campo [id] contenuto nel uri
$comp = $da_get['comp'];    //campo [comp] contenuto nel uri
$value = $da_get['value'];  //campo [value] contenuto nel uri


    /*====mette aposto la forma e l'aspetto dei dati in input====*/
$new_key = aggiusta_key ($key); 	// key trimmata, minuscolo, nome giusto
$new_comp = aggiusta ($comp); 		// trimm e minuscolo
$new_value = aggiusta ($value); 	// trimm e minuscolo


    /*====assegnazione del soggetto del metadata a una variabile====*/
$metadata = $triples [0]['s']; // il soggetto del primo elemento del array contiene per forza il soggetto del metadata


    /*====crea un array contenente tutti i soggetti====*/
$array_provv = array (); // array che conterra' i soggetti delle triple
foreach ($triples as $tripla) {
    if ($tripla['s'] != $metadata) {
        array_push ($array_provv, $tripla['s']);
    }
}


    /*====filtra l'array di soggetti eliminando doppioni====*/
$array_provv = array_unique($array_provv);//elimina i doppioni lasciando solo un unica copia del elemento
$array_sogg = array (); //array contenete i 40 soggetti delle poste
foreach ($array_provv as $sogg) {
    array_push ($array_sogg, $sogg);
}


    /*====a seconda della key risponde con un uri adatto====*/
$url_key = seleziona_uri($new_key);           //genera l'uri inbase alla key
$propriety = $url_key.$new_key;               //fonde l'uri adatta alla key


//array contenente il risultato finale
$risultato = array ();


    /*====carica in anteprima il metadata====*/
foreach ($triples as $tripla) { //metadata presenti sempre, carica nel array i metadati
    if ($tripla['s'] == $metadata) {
        array_push ($risultato, $tripla);
    }
}


    /*====chiama la funzione main che fa tutto il lavoro====*/
$aggrditriple = main ($triples, $array_sogg, $propriety, $new_key, $new_comp, $new_value, $risultato);


    /*====converte l'array di triple in turtle====*/
$prefixes = array('ns' => $namespace);
$serializer = ARC2::getTurtleSerializer($prefixes);
$document = $serializer->getSerializedTriples($aggrditriple); //documento finito in convertito in turtle

sendHTTPResponse ($headers,$document);

?>
