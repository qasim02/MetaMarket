<?php


/* ==============GLOBAL BEGIN=====================*/
/* MimeType (default: application/xml) */
$mimeType = 'application/xml';

/* Header di default */
$defaultHeader = "Content-type: $mimeType; charset=UTF-8"; 

/* importo le configurazioni generiche */
require_once './config/metaMarket.conf';
require_once './config/http.conf';
require_once './config/conversioni.php';

/* importo le classi necessarie */
require_once './config/MMCrawler.php';

/* importo le configurazioni specifiche */
require_once './config/tipologia.conf';


/* ==============GLOBAL END=======================*/

function loadHTTPparams($paramArray){
/* Carica i parametri della richiesta in una variabile globale controllandone la sintassi */
	if (!empty($paramArray["list"])){
		global $listOnly;
		/* Sto richiedendo solo la lista di qualcosa */
		$listOnly = $paramArray["list"];
		return;
	}
	/* E' un operatore booleano? */
	$op = strtolower($paramArray['op']);
	switch ($op){
		case 'and': break;
		case 'or' : break;
		case 'not': break;
		case 'xor': break;
		default : {
			$error = "ERRORE DESCRITTORE: L'operatore $op non è un operatore booleano valido";
			sendHTTPResponse($HTTP_error[406], $error);
		}
	}
	/* Carico la lista delle categorie */
	$categories = strtolower($paramArray['categories']);
	if (empty($categories)){
		$error = "ERRORE DESCRITTORE: L'insieme delle categorie è vuoto";
		sendHTTPResponse($HTTP_error[406], $error);
	}
	/* empty(x) e' true anche se x e' NULL == !isset() */
	if (empty($paramArray['aggId'])){
		$URL['aggId'] = '';
	} else {
		/* ATTENZIONE al casing dell'id! Il confronto E' case sensitive */
		$URL['aggId'] = $paramArray['aggId'];
	}

	/* Salvo i parametri come array */
	$URL['op'] = $op;
	/* Le categorie sono, a loro volta, un array */
	$categories = explode(',', $categories);
	foreach ($categories as $key=>$category) {
		/* Elimino gli spazi iniziali e finali ma non i centrali */
		$category = trim($category);
		$categories[$key] = $category;
	}
	$URL['categories'] = $categories;
	return $URL;
}



function opCategory($DOMDoc, $categories, $op){
/*  input: DOM, array di stringhe, operatore booleano (stringa) */
/*  return: nodeSet */
/* descr: effettua una query XPath sul DOM dato in input restituendo  
 * un DOM con tutti e soli quei nodi che rispettano la condizione */
	
	function queryGen($strings, $op, $rec = false){
	/* Funzione che genera una query XPath valida a partire da un array
	 * di stringhe e un operatore booleano */
		/*  Necessario per il confronto case-insensitive */
		$keytolower = "translate(category, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 
		'abcdefghijklmnopqrstuvwxyz')";
		
		$condition; /*  Node Test XPath booleano */
		switch($op){
			case 'and': {
				foreach ($strings as $string){
						$condition = $condition."contains($keytolower, '$string') and ";
				}
				$condition = "$condition-";
				/*  Elimino l'ultimo or che darebbe errore */
				$condition = str_replace('and -', '', $condition);
				break;
			}
			case 'or' : {
				foreach ($strings as $string){
					$condition = $condition."contains($keytolower, '$string') or ";
				}
				$condition = "$condition-";
				/*  Elimino l'ultimo or che darebbe query invalida */
				$condition = str_replace('or -', '', $condition);
				break;
			}
			case 'xor' : {
				/* A XOR B = (A and notB) or (notA and B) */
				if (count($strings) < 2){
					$error = "ERRORE DESCRITTORE: Per utilizzare $op è necessario specificare almeno due categorie";
					sendHTTPResponse($HTTP_errors[406], $error);
				} else if (count($strings) == 2){
					/* Caso base */
					$condition = "(contains($keytolower, '$strings[0]') and not(contains($keytolower, '$strings[1]'))) or
								  (not(contains($keytolower, '$strings[0]')) and contains($keytolower, '$strings[1]'))";
				} else {
					$rec = true; /* Effettuo lo xor tra più di 2 categorie */
					$last = count($strings)-1;
					$lastString = $strings[$last];
					/* Richiamo ricorsivamente lo xor sulle stringhe rimanenti */
					unset($strings[$last]);
					$condRec = queryGen($strings, $op, true);
					$condition = "(not($condRec) and contains($keytolower, '$lastString')) or
									(($condRec) and not(contains($keytolower, '$lastString')))";									
				}
				break;
			}
			case 'not' : {
				foreach ($strings as $string){
						$condition = $condition."not(contains($keytolower, '$string')) and ";
				}
				$condition = "$condition-";
				/*  Elimino l'ultimo and che darebbe query invalida */
				$condition = str_replace('and -', '', $condition);
				break;
			}
			default: {
				$error = "ERRORE DESCRITTORE: la stringa $op non è un operatore booleano valido";
				sendHTTPResponse($HTTP_errors[406], $error);
			}
		}
		return $condition;
	 
	}
	
	/*  Trasformo il DOM in un XPathDOM */
	$XPathDoc = new DOMXPath($DOMDoc);
	
	/*  Check dell'operatore booleano */
	
		
	/*  Query di filtro per le location */
	$condition = queryGen($categories, $op);
	$query = "/locations/location[$condition]";

	$filtered = $XPathDoc->query($query);
	/*  Preservo DOCTYPE ed eventuali P.I. */
	$rootNodes = $XPathDoc->query("/"); /* NodeList */
	/*  Salvo i metadati (elemento unico) */
	$metadata = $XPathDoc->query("/locations/metadata")->item(0); /* DOMNode */
	/*  Rimuovo la root locations */
	$locations = $XPathDoc->query("/locations")->item(0);
	$DOMDoc->removeChild($locations);
	/*  Creo un elemento 'locations' vuoto e lo appendo come figlio */
	$emptyLocations = new DOMElement('locations');
	$DOMDoc->appendChild($emptyLocations);
	/*  Appendo i metadati */
	$emptyLocations = $DOMDoc->getElementsByTagName('locations')->item(0);
	if ($metadata != null)
		$emptyLocations->appendChild($metadata);	
	/*  Appendo le location filtrate */
	foreach ($filtered as $location) {
		$emptyLocations->appendChild($location);
	}
	return $DOMDoc;	
}

/* ===============MAIN================*/

/* Carico i parametri della richiesta */
$URL = loadHTTPparams($_GET);
$aggId = $URL['aggId'];

/* Creo il crawler */
$mmCrawler = new MMCrawler($mCURL);

/* Imposto un percorso per il salvataggio dei dati */
$mmCrawler->setSaveURL($saveURL);

/* Gli dico di caricare il metaCatalogo */
if (!$mmCrawler->loadMetaCatalogo()){
	$error = "ERRORE DESCRITTORE: Impossibile caricare il metacatalogo. L'errore è : ".$mmCrawler->getError();
	sendHTTPResponse($HTTP_errors[500], $error);
}
/* GLi dico di caricare gli aggregatori */
if (!$mmCrawler->loadAggregatori()){
	$error = "ERRORE DESCRITTORE: Impossibile caricare gli aggregatori. L'errore è : ".$mmCrawler->getError();
	sendHTTPResponse($HTTP_errors[500], $error);
}

/* Scarico tutti i dati da tutti gli aggregatori, o dall'aggregatore di cui ho specificato l'id, e li salvo nel file */
$data = $mmCrawler->loadData($aggId);



/* Se ho richiesto solo la lista di qualcosa ritorno questa lista */
if (!empty($listOnly)){
	if ($listOnly == 'aggregatori')
		/* Se chiedo l'elenco di tutti gli aggregatori restituisco un file XML che li contiene */
		$body = $mmCrawler->getAggregatoriXML();
	else	
		/* Altrimenti restituisco una lista degli elementi richiesti presi da ogni location */
		$body = $mmCrawler->getElements($listOnly);
	if (!$body) {
		$error = "ERRORE DESCRITTORE: Impossibile restituire la lista richiesta - ".$mmCrawler->getError();
		sendHTTPResponse($HTTP_errors[406], $error);
	}
	sendHTTPResponse($defaultHeader, $body);
}

/* Se i dati sono ancora vuoti significa che c'è stato un errore con il Crawler */
if (empty($data)) {
	$error = 'ERRORE DESCRITTORE: Impossibile soddisfare la richiesta per il seguente errore: '.$mmCrawler->getError();
	sendHTTPResponse($HTTP_errors[500], $error);
}


/* A questo punto è stato richiesto un normale filtraggio per categoria */
$DOMDoc = DOMDocument::loadXML($data);

/* Applico il filtraggio */
$newDOM = opCategory($DOMDoc, $URL["categories"], $URL["op"]);

/* Costruisco il body XML */
$body = $newDOM->saveXML();

/* Verifico di poter rilasciare il formato richiesto, converto e invio la risposta */
$accepted = $_SERVER["HTTP_ACCEPT"];

if (stristr($accepted, '*/*') or stristr($accepted, 'application/xml') or stristr($accepted, 'text/html') ) {
		/* Di default mando application/xml */
		sendHTTPResponse($defaultHeader, $body);
		break;
	}
else if (stristr($accepted, 'application/json')){
		$header = 'Content-type: application/json; charset=UTF-8';
		$body = xml2json($body);
		sendHTTPResponse($header, $body);
		break;
	}
else if (stristr($accepted, 'text/csv')){
		$header = 'Content-type: text/csv; charset=UTF-8';
		$body = xml2csv($body);
		sendHTTPResponse($header, $body);
		break;
	}
else if (stristr($accepted, 'text/ttl')){
		$header = 'Content-type: text/turtle; charset=UTF-8';
		$body = xml2ttl($body);
		sendHTTPResponse($header, $body);	
		break;
	}
else {
		$error = 'ERRORE DESCRITTORE: Il formato richiesto non è tra quelli rilasciati!';
		/* Scrivo un semplice log del formato richiesto DEBUG */
		$reqData = @fopen('../data/new/request_data.txt','a');
		@fwrite($reqData, "Got this Accept: $accepted  on  ".date('r')."\n");
		@fclose($reqData);
		sendHTTPResponse($HTTP_errors[406], $error);
}






	
	
	
	


