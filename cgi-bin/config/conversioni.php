<?php

include_once("aggttl/arc/ARC2.php"); 
require_once("ttl2xml_lib.conf");
require_once ("xml2ttl_lib.conf");
/* NOTA: Questo file contiene tutte le funzioni di conversione necessarie al progetto: 
* da e verso application/xml da e verso i formati text/csv, text/turtle e application/json */


function json2xml($jsonData){
/* Funzione che converte una stringa json a stringa xml */
	if (empty($jsonData)) return null;
	/* Converto eventuali caratteri proibiti in entità */
	$jsonData = str_replace('&', '&amp;', $jsonData);
	$jsonData = str_replace('>', '&gt;', $jsonData);
	$jsonData = str_replace('<', '&lt;', $jsonData);
	$jsonArray = json_decode($jsonData);
	/* Creo la struttura DOM di base*/
	$DOMImp = new DOMImplementation();
	$dtd = $DOMImp->createDocumentType('locations', '', 'http://vitali.web.cs.unibo.it/twiki/pub/TechWeb12/DTDs/locations.dtd');
	$DOMDoc = $DOMImp->createDocument('', 'locations', $dtd);
	/*Prendo in considerazione la root del documento, posizione 0*/
	$locations = $DOMDoc -> getElementsByTagName('locations')->item(0);
	$DOMDoc->encoding = 'UTF-8';
	
	/* Scandisco l'array delle location e i metadati json e le schiaffo nel DOM */
	foreach ($jsonArray as $el=>$location){
		if ($el != 'metadata')
		/*Faccio un foreach per scorrere tutte le chiavi (sup00**)*/
		  foreach ($location as $key=>$data){
			$location = $DOMDoc->createElement('location');
			$location->setAttribute('id', $key);
			/*Utilizzo questo foreach per scorrere tutti gli elementi/attributi e prendere il loro valore (innerData)*/
			foreach ($data as $element=>$innerData){
				/* 3 casi: se lat/long come attributo, se category dare un implode */
				switch ($element){
					case 'lat': {
						$location->setAttribute('lat', $innerData);
						break;
					}
					case 'long': {
						$location->setAttribute('long', $innerData);
						break;
					}
					case 'category': {
						@$catStr = implode(',', $innerData);
						/* Se c'è un maledetto errore nel definire le categorie in JSON come letterale e non come ARRAY di letterali correggo l'errore */
						if (empty($catStr)){
							$catStr = $innerData;
						}
						/* Infine creo e appendo l'elemento come figlio */
						$child = $DOMDoc->createElement($element, $catStr);
						$location->appendChild($child);
						break;
					}
					default : {
						@$catStr = implode(',', $innerData);
						/* Se c'è un maledetto errore nel definire le categorie in JSON come letterale e non come ARRAY di letterali correggo l'errore */
						if (empty($catStr)){
							$catStr = $innerData;
						}
						$child = $DOMDoc->createElement($element, $catStr);
						$location->appendChild($child);
					}
				}
			}
			//Aggiungo la singola location, alle locations
			$locations -> appendChild($location);
		}
		else {		
			$metadata = $DOMDoc->createElement('metadata');
			foreach ($location as $element=>$innerData){
				$child = $DOMDoc->createElement($element, $innerData);
				$metadata->appendChild($child);
			}
			$locations -> appendChild($metadata);
		}
	}	
	
	
	/* Infine restituisco in output la stringa XML formattata */
	$DOMDoc -> formatOutput = true;
	return $DOMDoc->saveXML();

}//fine funzione json2xml

function csv2array ($csvString, $saveURL = '../data/tmp/csv2array_temp.csv') {
/* Funzione che converte una stringa CSV in un array associativo */
		/* Dichiaro l'array in cui verrà convertito il CSV */
		$array = array();
		/* Poiché str_getcsv() è presente solo in PHP >= 5.3.0 utilizzo un workaround */
		$tmpFile = fopen($saveURL, 'w');
		fwrite($tmpFile, $csvString);
		fclose($tmpFile);
		$tmpFile = fopen($saveURL, 'r');
		//parso il file con la funzione fgetcsv
		while (($data = fgetcsv($tmpFile, 2048, ",")) != FALSE)
		{
			//inserisco  un array nel array di prima
			// alla fine avro' come risultato un array di array
			array_push ($array, $data);
		}
		//chiudo il file
		fclose ($tmpFile);
		return $array;
}



function csv2xml($csvString){
/* Funzione che converte una stringa CSV in una stringa XML */
	/* Trasformo eventuali caratteri riservati in xml in entità */
	$csvString = str_replace('&', '&amp;', $csvString); 
	$csvString = str_replace('<', '&lt;', $csvString);
	$csvString = str_replace('>', '&gt;', $csvString);
	/* Converto la stringa in un array */
	$arrayCsv = csv2array($csvString);
	/* Dichiaro un'array di "colonne" che rappresentano elementi dei metadati.
	 * Queste colonne NON saranno incluse nelle location ma soltanto nei metadata
	 * che nel CSV si ripetono per ogni riga */
	$mtArray = array('creator', 'created', 'version', 'source', 'valid');
	/* Carico la struttura di base: PI, DTD e DocumentRoot + Metadata */
	$DOMImp = new DOMImplementation();
	$DOMDtd = $DOMImp->createDocumentType('locations', '', 'http://vitali.web.cs.unibo.it/twiki/pub/TechWeb12/DTDs/locations.dtd');
	$DOMDoc = $DOMImp->createDocument('', 'locations', $DOMDtd);
	$DOMDoc->encoding = 'UTF-8';
	$locations = $DOMDoc->getElementsByTagName('locations')->item(0);
	$metadata = $DOMDoc->createElement('metadata');
	$locations->appendChild($metadata);
	/* Carico un array che ha come chiavi i nomi dei campi e come valori le relative posizioni */
	$keyArray = array();
	foreach ($arrayCsv[0] as $pos => $key){ /* Posizione 0 = header */
		$keyArray[strtolower($key)] = $pos;
	}
	/* Riempio i metadati prendendoli dalla prima riga/location */
	if (empty($arrayCsv[1])) return null;
	$firstLoc = $arrayCsv[1];
	foreach ($keyArray as $field=>$pos){
		if (in_array($field, $mtArray)){
			$mtElem = $DOMDoc->createElement($field, $firstLoc[$pos]);
			$metadata->appendChild($mtElem);	
		}
	}
	/* Creo le nuove location */
	foreach ($arrayCsv as $pos=>$row){
		/* Salto gli header */
		if ($pos == 0) continue;
		$location = $DOMDoc->createElement('location');
		/* Per ogni campo della riga creo il rispettivo componente nella location (attributo o elemento) */
		foreach ($row as $element=>$data){
			switch ($element){
				case $keyArray['id'] : {
					$location->setAttribute('id', $data);
					break;
				}
				case $keyArray['lat'] : {
					$location->setAttribute('lat', $data);
					break;
				}
				case $keyArray['long'] : {
					$location->setAttribute('long', $data);
					break;
				}
				default : {
					foreach ($keyArray as $str=>$value){
						/* Lo aggiungo, con il nome della chiave corrispondente, solo se non è un metadato */
						if ($element == $value && !in_array($str, $mtArray)){
							/* Trim per evitare "Invalid Character error" */
							$str = trim($str);
							$data = trim($data);
							/* Infine creo l'elemento da appendere come figlio della location */
							$elNode = $DOMDoc->createElement($str, $data);
							$location->appendChild($elNode);
						}
					}
				}
				
			}
			$locations->appendChild($location);
		}
	}
	$DOMDoc->formatOutput = true;
	return $DOMDoc->saveXML();
}
//ø→ø→ø→ø→ø→ø→ø→ø→ø→ø→ø→ø→ø→ø→ø→ø→ø→ø→øxml2csvø→ø→ø→ø→ø→ø→ø→ø→ø→ø→ø→ø→ø→ø→ø→ø→ø→ø→ø→ø→ø→ø→ø→ø→ø→ø→ø→ø→ø→ø→ø→ø


function xml2csv ($string){
/* Questa funzione converte un array associativo in un documento csv sottoforma di stringa
    PRECONDITION: $data deve essere un array associativo ottenuto dalla conversione di un CSV */
    $risultato = array ();
    //conveto il file xml in un array asociativo
    $data = file_get_contents($string);
    $encoded = simplexml_load_string ($data);
    $data = json_decode((json_encode ((array) $encoded)), 1);
    $attributes = array ('id','category', 'name', 'address', 'lat', 'long', 'subcategory', 'note', 'opening', 'closing','tel', 'creator', 'created', 'valid', 'version', 'source');
    array_push ($risultato, $attributes);
    
    $metadata = $data['metadata']; //array di metadata.

    for ($i = 0, $n = count ($data['location']); $i < $n; $i++) {
        $location = $data['location'][$i]; //variabile che contiene il primo elemento di location ovvero la prima location
        $location['opening'] = fondi_array ($location['opening']); //opening => array di opening, fondo l'array cosi' non si ha problemi con la conversione
	//fondi_array fonde gli elementi di un array in un unico elemento di un array (funzione di michele si trova in :require_once ("xml2ttl_lib.conf");
        //pulizia di attributes
        $temp_key = array_keys ($location['@attributes']); //ricava da @attributes tutte le key e le mette in array
        for ($f = 0, $m = count ($location['@attributes']); $f < $m; $f++) { //per ogni elemento di attributes preleva l'elemento e lo carica in temp_loc
            $location[$temp_key[$f]] = array_shift ($location['@attributes']); //temp_key[$i] = id,lat o long.. array_shift preleva elemento in testa al array
        }
        unset ($location['@attributes']); //elimina la key @attributes perche e' vuota

        //aggiunta di metadata a location
        $metadata_provv = $metadata;
        $key_metadata = array_keys ($metadata_provv);
        for ($f = 0, $m = count ($metadata_provv); $f < $m; $f++) { //per ogni elemento di attributes preleva l'elemento e lo carica in temp_loc
            $location[$key_metadata[$f]] = array_shift ($metadata_provv); //temp_key[$i] = id,lat o long.. array_shift preleva elemento in testa al array
        }
        
        $array_provv = array ();

        foreach ($attributes as $attributo) {
              array_push ($array_provv, $location[$attributo]);                     //$csvDoc.csv2string($attributo ,",");
            }
        array_push ($risultato, $array_provv);
        }
        return array2csv ($risultato);
}
function array2csv($data){
/* Questa funzione converte un array associativo in un documento csv sottoforma di stringa
        PRECONDITION: $data deve essere un array associativo ottenuto dalla conversione di un CSV */
        function csv2string ( $riga, $sep ){
        /* Converte una riga di un array associativo, ottenuto da una riga CSV, in una stringa CSV */
                $i=0;
                $keys= array_keys($riga);
                $riga_csv=null;
                foreach ($keys as $field) {
                        if($i == count($keys)-1)
                                $sep = "\n";
                        // else: $sep rimane quello ricevuto in input
                        $riga_csv = $riga_csv."\"".$riga[$field]."\"".$sep;
                        $i++;           
                }
                return $riga_csv;
        }       
        $csvDoc = '';
        for ($j = 0; $j < count($data); $j++){  
                $csvDoc = $csvDoc.csv2string($data[$j],",");
        }
        return $csvDoc;
}
//$file= "museiBO2011.xml";
//echo xml2csv ($file);


//æææææææææææææææææææææææææææææææææææææææææææææfinełłłłłłłłłłłłłłłłłłłłłłłłłłłłłłłłłłłłłłłłłłłłłłłłłłłłłłłłłł



				/*%%%%%%%%%%%%%%% CONVERTITORE TURTLE => XML %%%%%%%%%%%%%%%%%*/

function ttl2xml ($turtle) {

	$name =  random_string(8); //genra 8 lettere

	$pos_file="./config/temp_ttl/$name"; // directory file + nome file
	$file=fopen($pos_file,"w"); //crea file e lo apre 
	fwrite($file,$turtle); //scrive il contenuto nel file
	fclose($file); // chiude il file aperto 

    $parser = ARC2::getTurtleParser(); //inizializza il parser
    $parser -> parse($pos_file); // legge il dato turtle
    $triples = $parser->getTriples(); //genera arraay di triple dal dato letto

    $sogg_metadata = $triples [0]['s']; // il soggetto del primo elemento del array contiene per forza il soggetto del metadata
    /*====crea un array contenente tutti i soggetti====*/

    $prop_metacat = array ('creator', 'created', 'description', 'source', 'valid','note'); // tag metadata da cercare
    $prop_location = array ('id', 'lat', 'long', 'category', 'name', 'address', 'tel', 'opening', 'closing'); //tag location da cercare

    /*====crea un array contenente tutti i soggetti====*/
    $array_sogg = array ();
    foreach ($triples as $tripla) {
        if ($tripla['s'] != $sogg_metadata) {
            array_push ($array_sogg, $tripla['s']);
        }
    }
    $array_sogg = array_unique($array_sogg);//elimina i doppioni lasciando solo un unica copia del elemento

/*========================= DOM ===========================*/

    $doc = new DOMDocument(); //inizializza il dom
    $doc->formatOutput = true;

    $locations = $doc -> appendChild ($doc -> createElement ('locations')); //crea il tag location come figlio di doc
    $metadata = $locations -> appendChild ($doc -> createElement ('metadata')); //crea il tag metadata come figlio di location
                /* METADATA */
    foreach ($triples as $tripla) { // per ogni tripla 
        if ($sogg_metadata == $tripla['s']) { // se il soggetto del metadata prelevato in precedenza e' uguale al soggetto preso dalla tripla

           foreach ($prop_metacat as $propriety) { //per ogni elemento del array prp metacat 
                $prop = seleziona_uri ($propriety).$propriety; //genera l'uri datto alla propriety

                if ( $prop == $tripla['p']) { // se propriety generato e' uguale al propriety della tripla
                     $proprieta = $metadata -> appendChild ($doc -> createElement( $propriety )); //crea l'elemeto propiety e appendilo come figlio di metadata
                     $proprieta -> appendChild ( $doc -> createTextNode ($tripla['o'])); //preleva il testo e appendilo come figlio di proprieta
                }
           }
        }
    }

                /* LOCATION */
    foreach ($array_sogg as $sogg) { // per ogni soggetto
        $location = $locations -> appendChild ($doc -> createElement ('location')); // crea il tag location e appendilo al locations
        foreach ($triples as $tripla) { // per ogni tripla

            if ($sogg == $tripla['s']) { //se il soggetto e' uguale al soggetto della tripla

                foreach ($prop_location as $propriety) { // per ogni propriey
                    $propr = aggiusta_key ($propriety);  // aggiusta propriey
                    $prop = seleziona_uri ($propr).$propr; // selezona l'uri adatto alla propriety

                    if ($propriety == 'id') { // se propiety e' uguale a id 
                        $location -> setAttribute ('id', subject ($sogg)); // metti come attributo id
                        }

                    if ($prop == $tripla['p']) { // se prop e' uguale a propriety della tripla

                        if (($propriety == 'lat') || ($propriety == 'long')) { // se propriety e' uguale a lat o long 
                            $location -> setAttribute ($propriety, $tripla['o']); //aggiungilo come attributo di location
                        }

                        else { // altrimenti crea l'elemento propriety e appendilo a location
                            $proprieta = $location -> appendChild ($doc -> createElement ($propriety));
                            $proprieta -> appendChild ($doc -> createTextNode ($tripla['o'])); //aggiungi l'oggetto della tripla a proprieta
                        }
                    }
                }
            }
        }
    }
	unlink("./config/temp_ttl/$name"); //elimina file
    return $doc -> saveXML(); //chiudi e salva.
}


		/*%%%%%%%%%%%%%%% CONVERSIONE DA XML => TURTLE %%%%%%%%%%%%%%%%%*/

function xml2ttl ($xmlfile) {
//grazie a json ecode converto il file xml in un array associaivo.
    $data = file_get_contents($xmlfile);
    $encoded = simplexml_load_string ($data);
    $data = json_decode((json_encode ((array) $encoded)), 1); //data contiene un array associativo

    $triples = array(); //array finale contenente tutti gli array di triple che gli verrano caricate

//scheletro array associativo da dare in pasto a turtle serializer
    $tripla = array ('type' => 'triple', 
                    's' => null,
                    'p' => null,
                    'o' => null,
               's_type' => 'uri',
               'p_type' => 'uri',
               'o_type' => 'literal',
           'o_datatype' => null,
               'o_lang' => null,);

    $prop_meta = array ('creator', 'created', 'version', 'source', 'valid','note'); // tag metadata da cercare
    $prop_location = array ('lat', 'long', 'category', 'subcategory', 'name', 'address', 'tel', 'opening', 'closing'); //tag location da cercare

    $xsd_date = 'http://www.w3.org/2001/XMLSchema#date'; //modificatore di tipo xsd:date per le date contenute in metadata

//array di namespace da mettere nel file finale di turtle
    $namespace = array(
    'dcterms' => 'http://purl.org/dc/terms/',
    'xsd' => 'http://www.w3.org/2001/XMLSchema#',
    'vcard' => 'http://www.w3.org/2006/vcard/ns#',
    'cs' => 'http://cs.unibo.it/ontology/',
    '' => 'http://www.essepuntato.it/resource/' );

//metadata
    foreach ( $prop_meta as $prop ) {

       $temp_trip = $tripla; //questa e' la variabile che contiene lo scheletro della tripla
       $soggetto = $xmlfile;//e' il soggetto che va a popolare il metadata
       $proprieta = seleziona_uri($prop).$prop;//l' url giusto a seconda della proprieta
       $oggetto = $data ['metadata'][$prop];    //l' oggetto della tripla

       $temp_trip['s'] = $soggetto;    //assegnazione
       $temp_trip['p'] = $proprieta;   //assegnazione
       $temp_trip['o'] = $oggetto;     //assegnazione

       if (is_date ($oggetto)) { //attivo solo nel caso in cui il soggetto e' una data, aggiunge xsd come tipo
          $temp_trip['o_datatype'] = $xsd_date;
       }
       array_push ($triples, $temp_trip); //carica sul array finale le triple
    }

// location
    for ($i = 0, $n = count ($data['location']); $i < $n; $i++) {

        $temp_location = $data['location'][$i]; //variabile che contiene il primo elemento di location ovvero la prima location
        $temp_location['opening'] = fondi_array ($temp_location['opening']); //opening => array di opening, fondo l'array cosi' non si ha problemi con la conversione
        $temp_key = array_keys ($temp_location['@attributes']); //ricava da @attributes tutte le key e le mette in array

        for ($f = 0, $m = count ($temp_location['@attributes']); $f < $m; $f++) { //per ogni elemento di attributes preleva l'elemento e lo carica in temp_loc
            $temp_location[$temp_key[$f]] = array_shift ($temp_location['@attributes']); //temp_key[$i] = id,lat o long.. array_shift preleva elemento in testa al array
        }
        unset ($temp_location['@attributes']); //elimina la key @attributes perche e' vuota

// creazione array di triple
        foreach ( $prop_location as $prop ) { // per ogni elemento di prop loc
            $temp_trip = $tripla; //crea lo scheletro della tripla 
            $soggetto = seleziona_uri('id').$temp_location['id'];// seleziona l'uri adatta al id
            
            $new_prop = aggiusta_propriety($prop); //trasforma i nomi in nomi definiti in turtle

            $proprieta = seleziona_uri($new_prop).$new_prop; //seleziona l'uri adatto per la proprieta

            $oggetto = $temp_location[$prop]; //preleva l'oggetto

            $temp_trip['s'] = $soggetto;    //assegnazione
            $temp_trip['p'] = $proprieta;   //assegnazione
            $temp_trip['o'] = $oggetto;     //assegnazione

            array_push ($triples, $temp_trip); //carica sul array finale i dati
        }
    }

    $serializer = ARC2::getTurtleSerializer(); //inizializza il serializzatore
    $prefixes = array('ns' => $namespace); //prepara i namespace da mettere come prefisso
    $serializer = ARC2::getTurtleSerializer($prefixes); //carica il serializer turtle che fara' la conversione da array associativo a turtle
    $document = $serializer->getSerializedTriples($triples); //documento finito, convertito in turtle
    
    return $document;
}


/*funzione che trasforma un file XML in JSON*/
function xml2json ($xmlData){
	if (empty($xmlData)) return null;
	$DOMDoc = new DOMDocument();
	$DOMDoc->preserveWhiteSpace = false;	 
	$DOMDoc->loadXML($xmlData);
	
	
	/* assegno ad una variabile tutti i contenuti di metadata*/
	$metadata = $DOMDoc->getElementsByTagName('metadata')->item(0);
	
	/*creo l'array vuoto dove ci schiafferò il mio nuovo file JSON*/
	$JSONArray = array();
	if (!empty($metadata)){	
	
		$childNodes = $metadata->childNodes;
		foreach ($childNodes as $child)
		{
				// aggiunge l'elemento all'array
			$JSONArray['metadata'][$child->nodeName] = $child->nodeValue;
			
		} //fine foreach metadata
	}
	
	/* assegno a una variabile tutti i contenuti di locations*/
	$locations = $DOMDoc -> getElementsByTagName('location');
	
	foreach($locations as $location)
		{
		$id = null;
		//dichiaro un array vuoto che userò temporaneamente per metterci i dati di ogni location
		$temp = array();
	
			//ride the attributes! controlliamo gli attributi
			foreach($location->attributes as $attribut)
			{
				// se il nome del mio attributo è uguale a id lo salvo in una variabile
				if($attribut->nodeName == "id")
				$id = $attribut->nodeValue;
				else
				// altrimenti metto l'attributo
				$temp[$attribut->nodeName] = $attribut->nodeValue;
			} //fine foreach attributi
	
	
		// scorre ogni elemento delle location
		foreach($location->childNodes as $child) 
		{
		// aggiunge l'elemento all'array
		$temp[$child->nodeName] = $child->nodeValue;
		}
	
	$JSONArray['locations'][$id] = $temp;
	} //fine foreach che scorre le location
		
return json_encode($JSONArray);

} //fine xml2json

/*%%%%%%%%%%%%%%% FINE XML & TURTLE %%%%%%%%%%%%%%%%%*/
