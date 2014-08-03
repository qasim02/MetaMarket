<?php



/* 
Questo e' un aggregatore per il progetto Meta Market del corso di Tecnologie Web.

Formato dati: CSV
Sorgente dati: scuolematerneBO2011.csv

Questo semplice modulo si occupa di filtrare e ordinare il documento in input, secondo i parametri passati tramite l'URL di richiesta,
seguendo queste fasi:

1) Interpretazione della richiesta HTTP (con annessi i parametri key, comp e value nell'URL)
  1.1) Check del formato supportato in "Accept", se non presente return HTTP 406
2) Salvataggio dei parametri in variabili
3) Conversione in array associativo e filtraggio
	3.1) Conversione del documento CSV in un array associativo
	3.2) Filtraggio in base alla tripla key, comp, value
	   3.2.1) Analisi per casi per i diversi tipi di comparazione (contains a parte)
	   3.2.2) Analisi della correttezza semantica dei confronti (
4) Costruzione e invio della risposta HTTP
*/


/* ============================== GLOBAL BEGIN =========================*/
/* VARIABILI E COSTANTI GLOBALI */
/* Creazione di un array associativo con gli status code da gestire */
/*$HTTP_errors = array(
				403 => 'HTTP/1.1 403 Forbidden',
				404 => 'HTTP/1.1 404 Not found',
				405 => 'HTTP/1.1 405 Method not Allowed',
				406 => 'HTTP/1.1 406 Not acceptable',
				500 => 'HTTP/1.1 500 Internal server error',
				501 => 'HTTP/1.1 501 Not implemented'
				);

/*			
/* Includo le configurazioni globali */
include './config/miahttp.conf';
include './config/metaMarket.conf';
//Sto richiedendo tutti i dati senza applicare alcun filtro
$reqAll = false;			
//un URL e' corretto se contiene 3 o 0 parametri, altrimenti do errore
$numparametri = count($_GET);
if  ($numparametri != 3 && $numparametri != 0){
  $error="ERRORE i numero dei parametri e errato! Si prega di seguire le specifiche qui descritte: 
                        <a href=\"http://vitali.web.cs.unibo.it/TechWeb12/WebHome\">MetaMarket Protocol</a>\n";
	sendHTTPResponse($HTTP_errors[406],$error);
}	

/* Caricamento parametri della richiesta HTTP */
$URL = array("key"=>null,"comp"=>null,"value"=>null);
$mimeType = 'text/csv';
$header = "Content-Type:$mimeType; charset=UTF-8";
//$filePath ='../data/';
$fileName = "scuolematerneBO2011.csv";



/* ============================== GLOBAL END =========================*/


function loadGETParameters(){
/* Funzione che carica i parametri dell'URL all'interno di un array associativo globale uniformandone il casing */
	global $URL;
	global $HTTP_errors;
	if (isset($_GET["key"], $_GET["comp"], $_GET["value"]) and (($_GET["key"] != '') and ($_GET["comp"] != '') and ($_GET["value"] != ''))) {
		$URL["key"] = strtolower($_GET["key"]);
		$URL["comp"] = strtolower($_GET["comp"]);
		$URL["value"] = strtolower($_GET["value"]);
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


function csv2array($filename, $delimiter)
//transforma tutto in array
{
	if(!file_exists($filename) || !is_readable($filename))
		return FALSE;
	$header = NULL;
	$data = array();
	if (($handle = fopen($filename, 'r')) != FALSE)
	{
		while (($row = fgetcsv($handle, 1000, $delimiter)) != FALSE)
		{
			if($header == null){
				foreach (array_keys($row) as $key){
					$value = strtolower($row[$key]); //i campi possono essere sia maiuscolii che minuscoli
					$row[$key] = $value;
				}
				$header = $row ;
				$data[] = array_combine ($row, $row);
				}
			else
				$data[] = array_combine($header, $row);
		}
		fclose($handle);
		
	}
	return $data;
}

function filter($key, $comp, $value, $data){
/* Funzione di filtro: questa funzione interpreta la tripla key, comp, value 
   e filtra le location in base alla validità della comparazione */
    $j = 0;
	$value = strtolower($value);
	$key = strtolower($key);
    global $reqAll;
    if ($reqAll) return $data;
    
	function contains($key, $value, $data){
		$j = 0;
		$dataNew = null;
		$value = strtolower($value);
		$key = strtolower($key);
		for ($i=0 ;$i<count($data); $i++){
			$current = $data[$i][$key];
 			if (stristr($current, $value)){
				$dataNew[$j] = $data[$i];
				$j++;
			}
		}
		return $dataNew;
	}
	
	if ($comp == 'contains') $filtered = contains($key, $value, $data);
	else{
		switch ($comp){
			case 'lt': { 
				if ($key == "id" or $key =="name" or $key =="address" or $key =="category" or $key =="opening" or $key == "closing")
					sendHTTPResponse($HTTP_errors[406], "ERRORE Aggregatore : Confronto semanticamente invalido");
				else for ($i=0 ;$i<count($data); $i++){
					$current = strtolower($data[$i][$key]);
 					if ($current < $value){
						$filtered[$j] = $data[$i];
						$j++;
					}
				}	
				break;
			}
			case 'ncontains': { 
				if ($key == "id" or $key =="lat" or $key =="long" or $key =="address")
					sendHTTPResponse($HTTP_errors[406], "ERRORE Aggregatore : Confronto semanticamente invalido");
				else for ($i=0 ;$i<count($data); $i++){
					$current = strtolower($data[$i][$key]);
 					if ($current != $value){
						$filtered[$j] = $data[$i];
						$j++;
					}
				}	
				break;
			}
			case 'gt': {
				if ($key == "id" or $key =="name" or $key =="address" or $key =="category" or $key =="opening" or $key == "closing")
					sendHTTPResponse($HTTP_errors[406], "ERRORE Aggregatore : Confronto semanticamente invalido");
				else for ($i=0 ;$i<count($data); $i++){
					$current = strtolower($data[$i][$key]);
 					if ($current > $value){
						$filtered[$j] = $data[$i];
						$j++;
					}
				}	
				break;
			}
			case 'le': {
				if ($key == "id" or $key =="name" or $key =="address" or $key =="category" or $key =="opening" or $key == "closing")
					sendHTTPResponse($HTTP_errors[406], "ERRORE Aggregatore : Confronto semanticamente invalido");
				else for ($i=0 ;$i<count($data); $i++){

					$current = strtolower($data[$i][$key]);
 					if ($current <= $value){
						$filtered[$j] = $data[$i];
						$j++;
					}
				}	
				break;
			}
			case 'ge': {
				if ($key == "id" or $key =="name" or $key =="address" or $key =="category" or $key =="opening" or $key == "closing")
					sendHTTPResponse($HTTP_errors[406], "ERRORE Aggregatore : Confronto semanticamente invalido");
				else for ($i=0 ;$i<count($data); $i++){
					$current = strtolower($data[$i][$key]);
 					if ($current >= $value){
						$filtered[$j] = $data[$i];
						$j++;
					}
				}	
				break;
			}
			case 'eq': {
				if ($key =="address" or $key =="category" or $key =="opening" or $key == "closing")
					sendHTTPResponse($HTTP_errors[406], "ERRORE Aggregatore : Confronto semanticamente invalido");
				else for ($i=0 ;$i<count($data); $i++){
					$current = strtolower($data[$i][$key]);
 					if ($current == $value){
						$filtered[$j] = $data[$i];
						$j++;
					}
				}	
				break;
			}
			case 'ne': {
				if ($key =="address" or $key =="category" or $key =="opening" or $key == "closing")
					sendHTTPResponse($HTTP_errors[406], "ERRORE Aggregatore : Confronto semanticamente invalido");
				else for ($i=0 ;$i<count($data); $i++){
					$current = strtolower($data[$i][$key]);
 					if ($current != $value){
						$filtered[$j] = $data[$i];
						$j++;
					}
				}
				break;	
			}
			default: $error="ERRORE AGGREGATORE: Richiesta non valida! Caso non previsto! Si prega di seguire le specifiche qui descritte: 
                        <a href=\"http://vitali.web.cs.unibo.it/TechWeb12/WebHome\">MetaMarket Protocol</a>";
				sendHTTPResponse($HTTP_errors[406],$error);
				 break;
		}
	}	
 	return $filtered;		
}






function array2csv($data){
/* Questa funzione converte un array associativo in un documento csv sottoforma di stringa
	PRECONDITION: $data deve essere un array associativo ottenuto dalla conversione di un CSV */
	function csv2string ( $riga, $sep ){
	/* Converte una riga di un array associativo, ottenuto da una riga CSV, in una stringa CSV */
		$i=0;
		$keys= array_keys($riga);
		$riga_csv;
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


/*================= MAIN =================*/

loadGETParameters();
$data = csv2array($fileName,',');
$data = filter($URL["key"],$URL["comp"],$URL["value"],$data);
$body = array2csv($data);
sendHTTPResponse(header, $body);
//contains($URL["key"], $URL["value"]);
//print_r(csv_to_array('scuole.csv',','));
/*
1) funzione per il comp e value

*/
//http://localhost/agg.php?key=Name&value=Viaview-source:http://localhost/agg.php?key=name&comp=le&value=mazzini
?>

