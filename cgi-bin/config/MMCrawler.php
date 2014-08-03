<?php


/* Crawler per il metaCatalogo e il catalogo del progetto MetaMarket
 * 
 * 1. Estrae gli URL dei catalogi dal MC salvandoli un array id=>URL
 * 2. Estrae gli URL degli aggregatori dal C salvandoli in un array id=>URL
 * 3. Estrae le categorie dai dati ottenuti dagli aggregatori
 * 4. Ritorna un XML wellformed con le categorie (senza duplicati)
 * 
 * */

/* Includo le funzioni di conversione in modo da poter essere chiamate
 * senza essere metodi */
require_once('./config/conversioni.php');

class MMCrawler {
	
	/* PROPERTIES */
	
	/* URL del metaCatalogo */
	public $mcURL = '';
	
	/* [id] => url */
	public $metaCatalogo = array();
	
	/* [id] => url */
	public $aggregatori = array();
	
	/* I dati catturati convertiti in XML */
	private $data;
	
	/* Parametri di salvataggio dei dati */
	private $cacheURL;
	private $dataSize;
	
	private $error;
	
	/* CONSTRUCTORS */
	
	/* NOTA: tutti i metodi che manipolano o leggono proprietà 
	ritornano * falso in caso * di errore e la descrizione 
	dell'errore è presente nella proprietà * (privata) $error. */
	
	/* Base: solo l'url del metaCatalogo */
	public function __construct($mcURL){
		$this->mcURL = $mcURL;
	}
	
	/* METHODS */
	
	public function setSaveURL($URL){
		if ($URL == '') return false;
		$this->cacheURL = $URL;
	}
	
	public function getError(){
		return $this->error;
	}
	
	public function setMCURL($mcURL){
		if ($mcURL == null){
			return false;
		}
		$this->mcURL = $mcURL;
	}
	
	public function setAggregatori($aggs){
		if (count($aggs) <= 0){
			return false;
		}
		$this->aggregatori = $aggs;
	}
	
	public function setMetaCatalogo($metaCatalogo){
		//DEBUG
		/* if (!$metaCatalogo->validate()) { return false; }; */
		$this->metaCatalogo = $metaCatalogo;
	}
	
	public function loadMetaCatalogo($URL = ''){
	/* Carica nell'apposita proprietà il file XML del metaCatalogo come array */
		if ($URL == '')
			$URL = $this->mcURL;
		if ($URL == '') {
			$this->error = 'URL assente';
			return false;
		}
		$curl = curl_init($URL);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		$metaC = curl_exec($curl);
		/* Caricamento dell'URL tramite XPath */
		$DOMDoc = DOMDocument::loadXML($metaC);
		$nodesCatalogo = $DOMDoc->getElementsByTagName('catalogo');
		/* Per ogni catalogo trovato */
		foreach ($nodesCatalogo as $catalog){
			/* Prendo gli attributi id e url */
			$attributes = $catalog->attributes;
			/* Prendo gli oggetti valore di 'id' e 'url' */
			$idObj = $attributes->getNamedItem('id');
			$urlObj = $attributes->getNamedItem('url');
			/* Li converto in stringhe da poter usare con un array */
			$id = $idObj->value;
			$url = $urlObj->value;
			/* Infine carico l'array del metacatalogo con i nuovi dati */
			$this->metaCatalogo[$id] = $url;
		}
		return $this->metaCatalogo;
	}		
	
	
	public function loadAggregatori(){
	/* Carica, nell'apposita proprietà, tutte le voci dei vari cataloghi */
		if (empty($this->metaCatalogo)) {
			$this->error = 'ERRORE CRAWLER: URL del MetaCatalogo non definito';
			return false;
		}
		/* Per ogni catalogo prendo gli oggetti aggregatore e ne salvo id (come chiave), titolo e url */
		foreach ($this->metaCatalogo as $catURL) {
			/* I valori dell'array sono già URL di cataloghi */
			$curl = curl_init($catURL);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			/* Ottengo il catalogo in XML */
			$catalogo = curl_exec($curl);
			/* Salta i cataloghi inesistenti o invalidi (non xml ad esempio) */
			if (curl_getinfo($curl, CURLINFO_HTTP_CODE) != 200 or curl_getinfo($curl, CURLINFO_CONTENT_TYPE) != 'application/xml'){
				continue;
			}
			//echo $catURL; // DEBUG 
			@$DOMCat = DOMDocument::loadXML($catalogo);
			/* Analizza il catalogo solo se è valido (loadXML torna false
			 * se il caricamento è fallito (ad es. XML mal formato) */
			if ($DOMCat && !empty($DOMCat)) {
				$aggs = $DOMCat->getElementsByTagName('aggregatore');
				foreach ($aggs as $aggregatore) {
					$attributes = $aggregatore->attributes;
					$idObj = $attributes->getNamedItem('id');
					$urlObj = $attributes->getNamedItem('url'); 
					$titleObj = $attributes->getNamedItem('title');
					$id = $idObj->value;
					$url = $urlObj->value;
					$title = $titleObj->value;
					$this->aggregatori[$id] = array('title' => $title, 'url' => $url);
				}
			}
		}
		return $this->aggregatori;
	}
		
		
	public function getData($type = 'application/xml'){
	/* Funzione che restituisce i dati memorizzati convertendoli nel formato richiesto 
	 * NOTA: Il formato DEVE essere un mime-type valido tra quelli presenti in MetaMarket */
	/* DEFAULT = application/xml */
		switch ($type) {
			case 'application/xml': {
				return $this->data;
				break;
			}
			/* TODO ALTRI FORMATI */
		}
	}
		
		
	public function convert($in, $out){
	/* Conversione da e verso formati */
	/* NOTA: $in e $out DEVONO essere mime-type validi di MetaMarket
	 * La  */
		
		
	}
	
	public function loadData($aggid = ''){
		/* Se il tempo trascorso è meno di $expire secondi, o se l'id non e' specificato, non scaricare di nuovo i dati */
		$expire = 86400;
		$lastTime = @filemtime($this->cacheURL);
		if ( true || !empty($lastTime) and ($lastTime > ( time() - $expire)) and (empty($aggid))){
			$this->data = DOMDocument::load($this->cacheURL)->saveXML();
			return $this->data;
		}
		/* Se e' stato precisato l'id di un aggregatore impongo che l'interrogazione sia unica e che ancora quell'aggregatore deve essere trovato */
		$unique = false;
		$found = false;
		if (!empty($aggid)){
			$unique = true;
		}
		/* Ho già creato la root per ospitare le location (DTD, PI, locations ecc.)? */
		$rootReady = false;
		/* Dichiaro il DOMDocument che utilizzero' come contenitore per i dati */
		$DOMDoc;
		/* Scorro la lista degli aggregatori */
		if (empty($this->aggregatori)){
			$this->error = 'ERRORE CRAWLER: nessun aggregatore caricato!';
			return false;
		}
		foreach ($this->aggregatori as $aggkey => $aggregatore){
			$aggregatore = $aggregatore['url'];
			/* Se l'id e' precisato e trovo un aggregatore con quell'id restituisco solo i suoi dati */
			if (($aggkey == $aggid) and $unique) {
				$found = true;
			} else if ($unique){
				/* Se unique e' true ma l'aggregatore analizzato non e' quello cercato lo salto */
				continue;
			}
			/* Scarico i dati */
			$curl = curl_init($aggregatore);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			/* Ottengo i dati in un particolare formato */
			$data = curl_exec($curl);
			/* Se l'aggregatore non risponde lo salto */
			if (curl_getinfo($curl, CURLINFO_HTTP_CODE) != 200){
				/* Se l'aggregatore che cercavo non e' raggiungibile interrompo l'interrogazione */
				if ($found) break;
				/* Se invece non era specificato alcun aggregatore mi limito a saltare quello non raggiungibile */
				else continue;
			}
			/* Converto qualunque formato arrivi in XML e se e' invalido lo salto */
			$contentType = curl_getinfo($curl, CURLINFO_CONTENT_TYPE);
			if (strstr($contentType, 'application/xml')){
				/* Se e' gia' XML non faccio nulla */
			}
			else if (strstr($contentType, 'application/json')){
				$data = json2xml($data);
			}
			else if (strstr($contentType, 'text/csv')){
				$data = csv2xml($data);
			}
			else if (strstr($contentType, 'text/turtle')){
				$data = ttl2xml($data);
			}
			/* Il formato non e' tra quelli riconosciuti quindi salto l'aggregatore perche' invalido o interrompo se era quello che stavo cercando */
			else {
				if ($found) {
					$this->error = 'ERRORE CRAWLER: Formato dei dati invalido!';
					return false;
				}
				else continue;
			}
			/* Controllo la validita' del file */
			/*
			$tobeval = DOMDocument::loadXML($data); // DA AGGIUNGERE ! ALL'IF. La validazione rimuoverebbe troppi dati "ben formati" ma invalidi solo per l'ordine. 
			if (!@$tobeval->validate()){
				if ($found) {
					$this->error = 'ERRORE CRAWLER: Dati non validi!';
					return false;
				}
				else continue;
			}	
			*/	
			
			
			/* Se non e' ancora stata definita inizializzo la root del documento (DTD incluso) */
			if (!$rootReady){
				$DOMImp = new DOMImplementation();
				$dtd = $DOMImp->createDocumentType('locations', '', 'http://vitali.web.cs.unibo.it/twiki/pub/TechWeb12/DTDs/locations.dtd');
				$DOMDoc = $DOMImp->createDocument('', 'locations', $dtd);
				$DOMDoc->encoding = 'UTF-8';
				$DOMDoc->formatOutput = true;
				$rootReady = true;
			}
			/* Controllo che non sia gia' stato aggiunto un elemento con lo stesso id */
			$locations = $DOMDoc->getElementsByTagName('locations')->item(0);
			if (!empty($data)){
				$newDOM = DOMDocument::loadXML($data);
				/* Creo una lista delle nuove location da aggiungere previa verifica */
				$locationList = $newDOM->getElementsByTagName('location');
				foreach ($locationList as $location) {
					/* Verifico che non ci sia una location con lo stesso id di quella presa in esame */
					$id = $location->attributes;
					$id = $id->getNamedItem('id');
					$id = $id->value;
					$XPathDoc = new DOMXPath($DOMDoc);
					/* Il metodo più veloce è una query XPath on-the-fly sugli attributi id */
					$query = "/locations/location[@id = '$id']";
					$idNodes = $XPathDoc->query($query);
					if ($idNodes->length <= 0){
						/* È necessario definire la proprietà del nuovo nodo
						* rispetto al vecchio $DOMDoc altrimenti eccezione:
						* 'Wrong Document Error' */
						$location = $DOMDoc->importNode($location, true);
						/* In più appendo un attributo supplementare che riporta l'aggregatore in cui la location è stata trovata */
						$location->setAttribute('aggregatore', $aggkey);
						/* Infine inserisco la location nel nuovo DOM */
						$locations->appendChild($location);
					}
				}
			}
			/* Brutto ma efficace: se l'interrogazione e' unica, e ho trovato l'aggregatore, mi fermo */
			if ($found) break;
		}	
		/* Infine scrivo l'XML come stringa nella proprietà $data */
		if (empty($DOMDoc)){
			$this->error = 'ERRORE CRAWLER:  raggiungibile!';
			return false;
		}
		$this->data = $DOMDoc->saveXML();
		/* E ne salvo una copia come file SSE l'interrogazione non e' unica (con caching primitivo) */
		if (($this->cacheURL) and (!$unique))
			$this->dataSize = $DOMDoc->save($this->cacheURL);
		return $this->data;
	}
	
	
	
	public function getAggregatoriXml(){
	/* Funzione che restituisce la lista degli aggregatori crawlati come XML */
		/* Parto da una struttura vuota */
		$DOMImpl = new DOMImplementation();
		$DOMDoc = $DOMImpl->createDocument('', 'aggregatori');
		$DOMDoc->formatOutput = true;
		$DOMDoc->encoding = 'UTF-8';
		$aggregators = $DOMDoc->getElementsByTagName('aggregatori')->item(0);
		/* Controllo che gli aggregatori siano stati caricati */
		if (empty($this->aggregatori)) {
			$this->error = "ERRORE CRAWLER: array degli aggregatori vuoto!";
			return false;
		}
		/* Aggiungo gli aggregatori uno ad uno inserendo id e url come attributi e title come text-node */
		foreach ($this->aggregatori as $aggKey => $aggData){
			$aggregatore = $DOMDoc->createElement('aggregatore', $aggData['title']);
			$aggregatore->setAttribute('id', $aggKey);
			$aggregatore->setAttribute('url', $aggData['url']);
			$aggregators->appendChild($aggregatore);
		}
		return $DOMDoc->saveXML();
	}
	
	
	
	public function getElements($what = 'category'){
	/* Restituisce un array di stringhe con all'interno tutti gli elementi
	 * 'what' (senza duplicati) presenti all'interno dei dati */
		if (empty($this->data)){
			if (empty($this->cacheURL)){
				$this->error = "Impossibile leggere o scaricare i dati";
				return false;
			}
			$this->data = $this->loadData();
		}
		/* Dichiaro l'array che conterrà gli elementi richiesti */
		$elArray = array();
		/* Costruisco un nuovo documento XML che conterrà gli elementi richiesti */
		$elements = DOMImplementation::createDocument();
		/* Creo l'elemento root 'list' */
		$elDRoot = $elements->appendChild($elements->createElement('list'));
		
		/* Creo un array di elementi senza duplicati */
		$DOMDoc = DOMDocument::load($this->cacheURL, true);
		/* Per ogni location salvo elemento e aggregatore insieme */
		$locations = $DOMDoc->getElementsByTagName('location');
		foreach ($locations as $location) {
			$elNodes = $location->getElementsByTagName($what);
			$agg = $location->getAttribute('aggregatore');
			foreach ($elNodes as $element) {
				/* "esplodo" eventuali elementi separati da virgole in un array*/
				$elValue = $element->nodeValue;
				$elValue = explode(',', $elValue);
				foreach ($elValue as $elstr){
					/* Se la stringa è vuota non posso usarla come chiave quindi salto */	
					if (empty($elstr)) continue;
					/* Rimuovo eventuali spazi iniziali */
					$elstr = trim($elstr);
					/* Converto tutto in Camel case */
					$elstr = (strtolower($elstr));
					$elstr[0] = strtoupper($elstr[0]);
					/* Uso le chiavi per evitare duplicati! Sto attento a non sovrascrivere gli aggregatori tra loro! */
					if (empty($elArray[$elstr]))
						$elArray[$elstr] = $agg;
					else if (!strstr($elArray[$elstr], $agg)) 
						/* Appendo l'aggregatore SOLO se non contiene uno di quelli visti finora */
						$elArray[$elstr] = $elArray[$elstr].', '.$agg;
				}
			}
		}

		/* Appendo le varie categorie al documento */
		foreach ($elArray as $el=>$value){
			$already = $elements->getElementById($el);
			/* Se non esiste alcuna categoria con questo id la creo */
			if (empty($already)){
				$element = $elements->createElement($what);
				/* Non ci sono sicuramente duplicati quindi posso usare id come attributo */
				$element->setAttribute('id', $el);
				/* Inserisco l'aggregatore come attributo */
				$element->setAttribute('aggregatore', $value);
				$elDRoot->appendChild($element);
			} else { /* Se c'è già una categoria con questo id la uso come principale e appendo sottocategorie */
				$element = $elDRoot->getElementById($el);
				$subElement = $elements->createElement('sub'.$what, $el);
				$element->appendChild($subElement);
			}
		}
		$elements->formatOutput = true;
		$elements->encoding = 'UTF-8';
		return $elements->saveXML();
	}

}






