<?php

/* Script to avoid "ajax cross-domain" errors. Not properly REST! :P */

/* Configuro la CURL */
$reqURL = $_GET['url'];
$reqURL = str_replace(' ', '%20', $reqURL);
$curl = curl_init($reqURL);
/* È necessario specificare nell'accept alcuni dei possibili content-type ritornati perché *.* non viene riconosciuto da tutti i descrittori (ed è lo standard Ext per i proxy) */
curl_setopt($curl, CURLOPT_HTTPHEADER, array('Accept: '.$_SERVER["HTTP_ACCEPT"].', application/xml, text/html'));
curl_setopt($curl,CURLOPT_RETURNTRANSFER, true);

/* Log della richiesta */
$log = fopen('../data/new/ajaxCompliant.log', 'a');
$string = "REQUEST URL: ".$reqURL."\n Accept: ".$_SERVER['HTTP_ACCEPT']."\n Method: ".$_SERVER['REQUEST_METHOD'];
fwrite($log, $string);


/* Infine restituisco header e body in maniera quasi trasparente */
$data = curl_exec($curl);
$headers = curl_getinfo($curl, CURLINFO_CONTENT_TYPE);
fwrite($log, "\n Content-type: ".$headers."\n\n\n");
/* Chiudo il log */
fclose($log);

/* Risultato */
header("Content-type: $headers; charset=UTF-8", true);
echo $data;


