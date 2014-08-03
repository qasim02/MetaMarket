<?php
require_once("http.conf");
//FUNZIONE DA INCLUDERE DI MICHELE  si trova su xml2ttl_lib.conf
function fondi_array ($array) { //funzione nata per normal_day che fonde l'array di day 
    $string = null;
    if (count ($array) == 1) { return $array; exit(); }
    foreach ($array as $key => $value) {
            $string=$string.$value;
            if($key != count($array)-1) {
              $string=$string.' ';
              }
            else { $string = $string; }
    }
    return $string;
}
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
?>


