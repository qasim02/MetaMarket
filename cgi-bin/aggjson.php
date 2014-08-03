<?php

/* Funzione per il controllo chiave-comparazione */
function checkComp($key,$comp){
	$nComp = array (        "id" => array("eq","ne","contains"),
                        "name" => array("eq","ne","contains","ncontains"),
                        "lat" => array("lt","gt","le","ge","eq","ne"),
                        "long" => array("lt","gt","le","ge","eq","ne","contains"),
                        "address" => array("contains"),
                        "category" => array("eq","ne","contains","ncontains"),
                        "opening" => array("contains","ncontains"),
                        "closing" => array("contains","ncontains"),
                              );
global $data;
                              
    $validComps = $nComp[$key];
    if ($validComps == null)
    {
    header("Content-type: application/json; charset=UTF-8");
    print_r($data); /* Chiave non esistente */
   	exit();
   	}
    foreach ($validComps as $newComp) {
         if ($newComp == $comp) return true;
    }    
    return false;
}

/*CheckComp(strtolower ($_GET["key"]), strtolower ($_GET["comp"]));*/


/* Funzione per salvare i parametri Key, Value, Comp*/
function saveURLparams($params){
	foreach ($_GET as $param){
		$param = trim($param);
		$param = strtolower($param);
	}
	$URL["key"] = $_GET["key"];
	$URL["value"] = $_GET["value"];
	$URL["comp"] = $_GET["comp"];
	return $URL;
}


function JsonFilter($json,$key,$comp,$value)
{
	/*inizializzo l'array vuoto dove andrò ad inserire le location che rispondono alla richiesta*/
	$arrayout = array();
	/*foreach per ogni location del file json*/
	foreach ($json['locations'] as $id=>$location)
	{
		/*assegno la location ad una variabile temporanea*/
		$temp = $location;
		/*inserisco il valore della key nel valore da confrontare con value*/
		$valoreconf = $temp[$key];
		/*controllo il comparatore e metto nell'array di output le location che mi servono*/
		switch ($comp)
		{
			case 'eq': 
			
				if ($valoreconf == $value)
				{   
					$arrayout[$id] = $temp;
				} break;	
			
			case 'lt': 
			
				if ($valoreconf < $value)
				{
					$arrayout[$id] = $temp;
				} break;
			
			case 'le': 
			
				if ($valoreconf <= $value)
				{
					$arrayout[$id] = $temp;
				} break;
		
			case 'gt': 
			
				if ($valoreconf > $value)
				{
					$arrayout[$id] = $temp;
				}break;
			
			case 'ge': 
			
				if ($valoreconf >= $value)
				{
					$arrayout[$id] = $temp;
				}break;
			
			case 'ne': 
			
				if ($valoreconf != $value)
				{
					$arrayout[$id] = $temp;
				}break;
			
			case 'contains': 
			
				if ((stripos($valoreconf, $value) !== false))
				{		
					$arrayout[$id]=$temp;
				}
			break;
			case 'ncontains': 
			
				if ((stripos($valoreconf, $value) === false))
				{
					$arrayout[$id] = $temp;
				}
			break;
		} //fine switch
		
	} //fine foreach
	return $arrayout;
} //fine funzione
	


//* Funzione Header Body *//
function sendHTTPResponse($header, $body){
        header($header);
        header("Content-type: application/json; charset=UTF-8", false);
        echo $body;
        exit();
        }
        
/*MAIN!!!*/

$data = file_get_contents('http://vitali.web.cs.unibo.it/twiki/pub/TechWeb12/DataSource2/supermarketBO2011.json'); 
$json = json_decode($data, TRUE);

//controllo se i paramentri passati sono accettabili
$check = checkComp(strtolower ($_GET["key"]), strtolower ($_GET["comp"]));
//se non sono accettabili ritorno errore
if (!$check){
sendHTTPResponse("HTTP/1.1 406 Not Acceptable","Comparazione non valida");
}

//salvo i parametri di $_GET in $URL
$URL=saveURLparams($_GET);

//filtro i dati tramite la funzione JsonFilter
$locationsfiltered = JsonFilter ($json, $URL["key"],$URL["comp"],$URL["value"]);
/*metto i metadata dentro una varibile $metadata*/
$metadata = $json['metadata'];

/*creo l'array di output comprensivo di metadata e location*/
$jsonoutput = array ("locations"=>$locationsfiltered,"metadata"=>$metadata);

/*restitusco in output la stringa in json*/
header("Content-type: application/json; charset=UTF-8");
echo json_encode($jsonoutput);
  
?>
