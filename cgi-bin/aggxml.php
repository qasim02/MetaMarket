<?php

/* 
Questo e' un aggregatore per il progetto Meta Market del corso di Tecnologie Web.

Formato dati: XML
Sorgente dati: farmacieBO2011.xml

Questo semplice modulo si occupa di filtrare e ordinare il documento in input, secondo i parametri passati tramite l'URL di richiesta,
seguendo queste fasi:

1) Interpretazione della richiesta HTTP (con annessi i parametri key, comp e value nell'URL)
  1.1) Check del formato supportato in "Accept", se non presente return HTTP 406
2) Salvataggio dei parametri in variabili
3) Utilizzo di XPath per l'applicazione di un filtro sul documento
  3.1) Conversione della stringa con parametri in valori di comparazione utili per XPath
    3.1.1) Conversione del campo "comp" nell'equivalente utilizzabile in XPath
  3.2) Costruzione della stringa di query XPath
  3.3) Applicazione della query XPath
4) Costruzione e invio della risposta HTTP

*/


/* ============================== GLOBAL BEGIN =========================*/

/* VARIABILI E COSTANTI GLOBALI */

/* Sto richiedendo tutti i dati, senza applicare alcun filtro? */
$reqAll = false;

/* Definisco il tipo di dati rilasciato */				
$mimeType = 'application/xml';
$header = "Content-type: $mimeType; charset=UTF-8";

/* HTTP Tools */
require_once "./config/http.conf";

/* Comparazioni valide */
require_once "./config/metaMarket.conf";

/* Id dell'aggregatore */
$id = $_GET['id'];

/* Configurazione specifica rispetto all'id e controllo su quest'ultimo */
if (empty($id)){
	$error = "ERRORE AGGREGATORE: id dell'aggregatore non specificato! URL malformato!";
	sendHTTPResponse($HTTP_errors[406], $error);
}
if ($handler = @fopen("./config/$id.conf",'r')){
	@fclose($handler);
	include "./config/$id.conf";
} else {
	$error = "ERRORE AGGREGATORE: aggregatore inesistente!";
	sendHTTPResponse($HTTP_errors[404], $error);
}
				


/* Caricamento parametri della richiesta HTTP */
$URL = array("key"=>null,"comp"=>null,"value"=>null);
$DOM_doc = DOMDocument::load($filePath."/".$fileName);
$XPath_doc = new DOMXPath($DOM_doc);



/* ============================== GLOBAL END =========================*/


/* Funzione che verifica la correttezza della chiave e dei confronti come da protocollo */
function checkComp($key, $comp){
	/* Riferimento all'array di chiavi e comparazioni valide */
	global $validComps;
	$comps = $validComps[$key];
	if ($validComps == null) {
		$error = "ERRORE AGGREGATORE: Richiesta non valida! La chiave non è valida!";
		sendHTTPResponse($HTTP_errors[406], $error);
	}
	foreach ($comps as $newComp) {
		if ($newComp == $comp) return true;
	}    
	$error = "ERRORE AGGREGATORE: Richiesta non valida! Il confronto $key $comp <\$value> non e' semanticamente corretto!";
	sendHTTPResponse($HTTP_errors[406], $error);
}



function loadGETParameters(){
/* Funzione che carica i parametri dell'URL all'interno di un array associativo globale uniformandone il casing */
	global $URL;
	global $HTTP_errors;
	if (isset($_GET["key"], $_GET["comp"], $_GET["value"]) and (($_GET["key"] != '') and ($_GET["comp"] != '') and ($_GET["value"] != ''))) {
		$URL["key"] = strtolower($_GET["key"]);
		$URL["comp"] = strtolower($_GET["comp"]);
		$URL["value"] = strtolower($_GET["value"]);
		checkComp($URL["key"], $URL["comp"]);
	}
	else if (isset($_GET["key"]) or isset($_GET["comp"]) or isset($_GET["value"])){
		if (!isset($_GET["key"]) or ($_GET["key"] == '')){
			$error = "ERRORE AGGREGATORE: Richiesta non valida! Chiave mancante! Si prega di seguire le specifiche qui descritte: 
			<a href=\"http://vitali.web.cs.unibo.it/TechWeb12/WebHome\">MetaMarket Protocol</a>\n";
			sendHTTPResponse($HTTP_errors[406], $error); 
		}
		if (!isset($_GET["comp"]) or ($_GET["comp"] == '')){
			$error = "ERRORE AGGREGATORE: Richiesta non valida! Comparazione mancante! Si prega di seguire le specifiche qui descritte: 
			<a href=\"http://vitali.web.cs.unibo.it/TechWeb12/WebHome\">MetaMarket Protocol</a>\n";
			sendHTTPResponse($HTTP_errors[406], $error);
		}		
		if (!isset($_GET["value"]) or ($_GET["value"] == '')){
			$error = "ERRORE AGGREGATORE: Richiesta non valida! Valore mancante! Si prega di seguire le specifiche qui descritte: 
			<a href=\"http://vitali.web.cs.unibo.it/TechWeb12/WebHome\">MetaMarket Protocol</a>\n";
			sendHTTPResponse($HTTP_errors[406], $error);
		}
	}	
	/* Sto richiedendo tutti i dati senza filtri */
	else {
		global $reqAll;
		$reqAll = true;	
	}			 
}


function checkHeaders($mimeType){
/* Funzione che controlla che all'interno dell'header Accept, nella richiesta HTTP pervenuta, sia presente il formato dei dati gestiti */
	global $HTTP_errors;
	$accepted = $_SERVER["HTTP_ACCEPT"];
	$method = $_SERVER["REQUEST_METHOD"];
	if ($method != 'GET') {
		$error = "ERRORE_AGGREGATORE: Il metodo con cui e' stata effettuata la richiesta non e' GET";
		sendHTTPResponse($HTTP_errors[405], $error);
	}
	if (!stristr($accepted, $mimeType) and ($accepted != '*/*')){	
		$error = "ERRORE AGGREGATORE: Tra i formati accettati: {".$accepted."} non è presente $mimeType\n";
		sendHTTPResponse($HTTP_errors[406], $error);
	}
}



function buildXPathQuery($URL){
/* Funzione che, a partire dai parametri passati via URL, tramite una sottofunzione costruisce una stringa valida per una query XPath */
	global $HTTP_errors;
	
	function str2cmp($key, $comp, $value){
	/* Funzione che converte una tripla key,comp,value in una stringa valida per una query XPath differenziando per casi */
 		global $HTTP_errors;
		
		/* Se il comp e' CONTAINS utilizzo l'omonima funzione nella query XPath */
		if ($comp == "contains"){
			$condition = 'contains('.$key_lower.',"'.$value.'")';			
			return $condition; 
		}
		else if ($comp == "ncontains"){
			$condition = 'not(contains('.$key_lower.',"'.$value.'"))';
			return $condition;
		}
		else {			
			$new_comp = null;
			switch ($comp){
			/* Traduci la comparazione da stringa a simbolo/i */
				case 'lt': $new_comp = '<'; break;
				case 'gt': $new_comp = '>'; break; 
				case 'le': $new_comp = '<='; break; 
				case 'ge': $new_comp = '>='; break; 
				/* Se uso EQ o NE e' possibile che vengano confrontate due stringhe quindi devo assicurarmi che il confronto sia case-insensitive */
				case 'eq': $new_comp = '='; break; 
				case 'ne': $new_comp = '!='; break; 
				default: {	
						$error =  "ERRORE AGGREGATORE: Comparatore non valido!\n";
						sendHTTPResponse($HTTP_errors[406], $error);
				}
			}
			/* se la chiave corrisponde ad un attributo specificalo nella query XPath con @ */
			if ($key == "lat" or $key == "id" or $key == "long") $key = "@".$key;
			/* pongo in lowercase il contenuto dell'elemento per evitare il confronto case-sensitive */
			$key_lower = "translate($key , 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz')";		
			/* "" necessarie in caso di confronto tra stringhe, il comportamento non cambia per tipi numerici */
			$condition = $key_lower.' '.$new_comp.' "'.$value.'"';
			return $condition; 
		}
	}

	/* Dopo aver convertito i parametri in una stringa valida per la query la costruisco */
	global $reqAll;
	if (!$reqAll){ 
		$condition = str2cmp($URL["key"],$URL["comp"],$URL["value"]);
		$XPath_query = "/locations/location[".$condition."]";
	}
	else { $XPath_query = "/locations/location";}
	return $XPath_query;
}



function buildFilteredDOM($query, $DOMDoc){
/* Funzione che applica la query XPath al documento XPath preso in input lavorando sul DOMDocument e DOMXPath globali passati per riferimento */
	global $HTTP_errors;
	$XPathDoc = new DOMXPath($DOMDoc);
	$filtered = $XPathDoc->query($query);
	// Preservo DOCTYPE ed eventuali P.I.
	$rootNodes = $XPathDoc->query("/"); //NodeList
	// Salvo i metadati (elemento unico)
	$metadata = $XPathDoc->query("/locations/metadata")->item(0); //DOMNode
	// Rimuovo la root locations
	$locations = $XPathDoc->query("/locations")->item(0);
	$DOMDoc->removeChild($locations);
	// Creo un elemento 'locations' vuoto e lo appendo come figlio
	$emptyLocations = new DOMElement('locations');
	$DOMDoc->appendChild($emptyLocations);
	// Appendo i metadati
	$emptyLocations = $DOMDoc->getElementsByTagName('locations')->item(0);
	if (!empty($metadata)){
		$emptyLocations->appendChild($metadata);
	}	
	// Appendo le location filtrate
	foreach ($filtered as $location) {
		$emptyLocations->appendChild($location);
	}
	
	return $DOMDoc;	
}



/* =========================== MAIN ============================*/


// Controllo che tra i formati accettati ci sia quello rilasciato
checkHeaders($mimeType);

// Controllo che il documento caricato sia valido
/*			DEBUG
 * 
 * if (!$DOM_doc->validate()){
	header($HTTP_errors[500]);
	exit("ERRORE AGGREGATORE: Il documento XML non e' valido!\n");
}
* 
* 
*/

// Carico i parametri della richiesta in variabili globali
loadGETParameters();	

// Costruisco la query XPath
$XPath_query = buildXPathQuery($URL);

// Applico il filtro tramite XPath
$DOM_doc = buildFilteredDOM($XPath_query, $DOM_doc);

// Salvo il body della risposta HTTP
$DOM_body = $DOM_doc->saveXML($DOM_doc);

// Invio la risposta
sendHTTPResponse($header, $DOM_body); 

/* =========================== END ============================*/

?>
