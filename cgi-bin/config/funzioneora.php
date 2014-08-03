<?php
//per punto per dividere l'orario che mi arriva dal narratore in due stringhe diverse
	  function perpunto ($string){
		  $explode=explode(".",$string);
		  return $explode;
	  }
	  //uso la funzione explode sulla stringa per : per dividere gli orari dai giorni
	  function perduepunti ($string){
		  $explode = explode(":",$string);
		  return $explode;
		  }
	//per virgola per dividere tutti i giorni	  
	function pervirgola($string){
		$explode=explode(",",$string);
		return $explode;
	}
	//per - per dividere l'orario d'apertura dal orario di chiusura
	function perbar($string){
		$explode=explode("-",$string);
		return $explode;
	}
	//perspazio mi dividei giorni dagli orari
	function perspazio ($string){
		$explode=explode(" ",$string);
		return $explode;
	}


/*

                                
   lun,mar,mer: 10:00-13:00, 15:30-20:00. gio,ven,sab: 10:00-12:30, 16:30-20:00.
   * Mon, Tue, Wed, Thu, Fri, Sun: 0830-1930 , 1600-2000. Mar, TGio, asd, sulcamello, sucamelo, Sun: 0230-1630.
*/
//$prova1= 'tue, mon, fri: 1000-1300, 1500-2000. sat, sun: 0900-1700, 1901-2000.';
//$prova2= 'sat: 1740-1600';

/* la funzione valutazione si occupa di dividere ogni stringa che il descrittore riceve in input , dopo aver creato due array
nel primo inserisco la stringa che mi arriva in input dal narratore, nel primo array quindi avro' 5 campi dove nei primi due ci sarano le due sottostringa mentere negli altri tre ci sarano gli orari di apertura e di chiusura
Mentre nel secondo array avro la stringa in input del utente composto da tre campi , nel primo il giorno negli altri due l'orario di apertura e di chiusura*/
function valutazione($orario1,$orario2){
	
        $stringa1=Array();      //array contenente stringa 1
        $stringa2=Array();      //array contenente stringa 2
        $legitOrario2=FALSE;

        $orario1Array=perpunto($orario1);    //divido per punto la strionga che mi arriva dal narratore da averne due
		//print_r ($orario1Array);
		//die();
        $orario2Array=perduepunti($orario2);    //divido il giorno dall'orario della key
		
		//print_r ($orario2Array);
		//die();
        foreach($orario1Array as $pattern)      //cerco in quale pattern si trova orario2
        {
                $pos = stripos($pattern,$orario2Array[0]);//stripos trova la posizione di una sottostringa in una stringa
                if ($pos === false)
                {
                        continue;
                }
                else
                {
                        $orario1Array=perduepunti($pattern);    //divido i giorni dagli orari di orario1 della seconda stringa
						
                        $truePattern=$orario1Array[1]; //ho l'orario
                        //print_r($truePattern);
                        //die();
                        $legitOrario2=1;
                        break;
                }
        }
 if(!$legitOrario2 || !$truePattern)     //se non è stato trovato l'orario
        {
                //exit(http_code_error(500)  "Second parameter " . $orario2 . " not legit ");
                return FALSE;
        }

        $nturniArray=pervirgola($orario1Array[1]);     //ottengo le diverse fasce orarie del apertura e della chiusura della seconda stringa
        foreach($nturniArray as $secturni => $turno)
        {
                $openCloseArray=perbar($turno);    //ottengo gli orari di apertura e chiusura della mattina
                //print_r ($openCloseArray);
                //die();
                $stringa1[$secturni]['open']=intval($openCloseArray[0]);  //riempio gli array di confronto
                //$print_r ($stringa1);
                //die();
                $stringa1[$secturni]['close']=intval($openCloseArray[1]);
        }

        $openCloseArray2=perbar($orario2Array[1]); //ottengo gli orari di apertura e chiusura del pomeriggio
      
        $stringa2['open']=intval($openCloseArray2[0]);
        $stringa2['close']=intval($openCloseArray2[1]);

        
       return valutazione2($stringa1,$stringa2);
}


//o1 contiene l'orario di apertura e chiusura della stringa del narratore
//o2 contiene l'orario della key
function valutazione2($o1,$o2){
	//print_r( $o1);
	//die();
	
        foreach($o1 as $secturno) //per ogni turno di orario1
        {
                if($o2['open'] < $secturno['open'])
                {
                        if($o2['close'] <= $secturno['close'])
                        {
                                return 2;
                        }
                        else return FALSE;
                }
                else   
                {
                        if($o2['open'] <= $secturno['close'])
                        {
                                if($o2['close'] <= $secturno['close'])
                                {
                                        return 1;
                                }
                                else return 1;
                        }
                        else continue;
                }
        }

        return FALSE;
}
/*$prova = valutazione ($prova1, $prova2);
print_r ($prova);
die();*/

?>

