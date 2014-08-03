/* Definisco il pannello che contiene, ed estende, la classe GMapPanel */
Ext.define('NR.view.Geolocal', {
	extend : 'Ext.ux.GMapPanel',
	alias : 'widget.geolocal',
		
	id: 'rightMap',
	
	lat : 0,
	
	lng : 0,
		
	/* Inizializzo la configurazione della mappa */
	zoomLevel: 14,
	gmapType: 'map',
	mapConfOpts: ['enableScrollWheelZoom','enableDoubleClickZoom','enableDragging'],
	mapControls: ['GSmallMapControl','GMapTypeControl'],
	
	// Location aggiunte finora
	markers : new Array(),
	
	// Infowindow aggiunte finora
	infowindows : new Array(),
	
	height : '70%',
	
	locNum : 0,
		
	/* Marker personalizzati */
	icons : {
	
		"Attrezzature musicali":"app/resources/markers/music_classical.png",
		"Bar": "app/resources/markers/coffee.png",
		"Batterie":"app/resources/markers/music_hiphop.png",
		"Benzinaio":"app/resources/markers/fillingstation.png",
		"Bordello":"app/resources/markers/stripclub2.png",
		"Cantautore":"app/resources/markers/music_classical.png",
		"Cinema":"app/resources/markers/cinema.png",
		"Concerto":"app/resources/markers/music_live.png",
		"Cup":"app/resources/markers/information.png",
		"Death metal":"app/resources/markers/music_rock.png",
		"Demenziale":"app/resources/markers/fireworks.png",
		"Eletronic rock":"app/resources/markers/music_rock.png",
		"Equitalia":"app/resources/markers/congress.png",
		"Farmacia":"app/resources/markers/treasure-mark.png",
		"Festival":"app/resources/markers/music_live.png",
		"Folk":"app/resources/markers/music_classical.png",
		"Folk metal":"app/resources/markers/music_classical.png",
		"Fumetteria":"app/resources/markers/comics.png",
		"Guardia medica":"http://mapicons.nicolasmollet.com/markers/health-education/health/doctor/",
		"Hardcore metal":"app/resources/markers/music_rock.png",
		"Hardcore punk":"app/resources/markers/music_rock.png",
		"Indie":"app/resources/markers/music_classical.png",
		"Indie pop":"app/resources/markers/music_classical.png",
		"Liutaio":"app/resources/markers/music_classical.png",
		"Museo":"app/resources/markers/art-museum-2.png",
		"Negozio":"app/resources/markers/conveniencestore.png",
		"Negozio di musica":"app/resources/markers/music.png",
		"Pagan metal":"app/resources/markers/music_classical.png",
		"Palestra":"app/resources/markers/weights.png",
		"Posta":"app/resources/markers/postal.png",
		"Poste e telegrafi":"app/resources/markers/postal.png",
		"Pronto soccorso":"app/resources/markers/firstaid.png",
		"Pronto soccorso generale":"app/resources/markers/firstaid.png",
		"Pronto soccorso oculistico":"app/resources/markers/firstaid.png",
		"Pronto soccorso ortopedico":"app/resources/markers/firstaid.png",
		"Pronto soccorso ortopedico traumatologico":"app/resources/markers/firstaid.png",
		"Pronto soccorso ostetrico ginecologico":"app/resources/markers/firstaid.png",
		"Pronto soccorso pediatrico": "app/resources/markers/firstaid.png",
		"Raggamuffin":"app/resources/markers/music_classical.png",
		"Rap":"app/resources/markers/music_classical.png",
		"Ristorante":"app/resources/markers/restaurant.png",
		"Scuola materna":"app/resources/markers/childmuseum01.png",
		"Sexy shop":"app/resources/markers/stripclub2.png",
		"Strumenti musicali":"app/resources/markers/music_classical.png",
		"Supermarket":"app/resources/markers/supermarket.png",
		"Tabacchi":"app/resources/markers/gumball_machine.png",
		"Teatro":"app/resources/markers/theater.png",
		"Ufficio postale":"app/resources/markers/postal.png",
		"Unita operativa di pediatria":"app/resources/markers/firstaid.png",
		"Visita codici a bassa complessita":"app/resources/markers/star-3.png",
		"Position" : "app/resources/markers/position.png",
		"University": "app/resources/markers/university.png",
		"Misc":"" // Default GMaps Marker
		
	},
	
	
	
	/* Metodo che riconfigura il centro della mappa */
	setTheCenter : function(lat, lng){
		this.lat = lat;
		this.lng = lng;
		this.setCenter = {
			lat : lat,
			lng : lng,
			marker : {
				title : 'Computer Science Dep. (University of Bologna)',
				icon : this.icons['University']
			}
		}
	},
	
	
	
	geoLocCenter : function(){
		/* Alias */
		var mymap = this;
		/* Controllo se è disponibile la geolocalizzazione */
		if (navigator.geolocation){
			navigator.geolocation.getCurrentPosition(
			
				function success(position){
					/* Se riesco a rilevare la posizione centro la mappa in quel punto */
					lat = position.coords.latitude;
					lng = position.coords.longitude;
					var posObj = new google.maps.LatLng(lat, lng);
					/* Se ho già centrato la mappa su queste coordinate non devo fare nulla */
					if (mymap.lat == lat && mymap.lng == lng){
						mymap.getMap().panTo(posObj);
						return;
					}
					mymap.lat = lat;
					mymap.lng = lng;
					/* Centra la mappa e aggiungi il marker */
					mymap.getMap().panTo(posObj);
					var centerMarker = new google.maps.Marker({ 
						lat:lat, 
						lng:lng,
						title : 'La tua posizione (approssimativa)',
						icon : mymap.icons['Position'],
						position : posObj,
						map : mymap.getMap(),
						animation : google.maps.Animation.DROP
					});				
				},
				
				function error(msg){
					/* Altrimenti vado sull'indirizzo di fallback */
					// Add marker
					Ext.Msg.alert('Ouch!', 'Purtroppo è stato impossibile riconoscere la tua posizione. <br/> <b>Sicuro di aver acconsentito al suo rilevamento</b>?');
				
				}
			);
			
		} else {
			/* Altrimenti vado sull'indirizzo di fallback */
			// Add marker
			Ext.Msg.alert('Ouch!', 'Purtroppo è stato impossibile rilevare la tua posizione. Non sembra essere presente alcun dispositivo di localizzazione!');
		}
	},
	
	
	/* Metodo che aggiunge il marker relativo alla location selezionata con relativa infowindow */
	addAMarker : function(grid, record, multi, ordered){		
		
		// Alias per "this" da usare dentro funzioni che hanno scope differente 
		var thisObject = this;
		var descrBox = Ext.getCmp('descrBox');
		
		// Draggable di default
		var dragValue = true;
		
		// Catturo la mappa sia come google.maps.Map che come Ext.ux.GMapPanel per avere più flessibilità
		var mymap = this.getMap();
		var extMap = this;
		
		/* Se il marker e' troppo distante dalla posizione, e non sto "organizzando la giornata" 
		(oltre i 1000m di raggio) lo creo solo "ondemand" (cliccando sulla tabella) */
		var thislat = record.get('lat');
		var thislong = record.get('long');
		var mylat = this.lat;
		var mylng = this.lng;
		window.log(mylat);
		var thisdistance = this.getDistance(thislat, thislong, mylat, mylng);
		if (multi && !ordered && thisdistance > 1000) {
			return;
		}

		// Catturo l'id della location selezionata
		var locId = record.get('id');
		
		// Creo degli alias per i marker e le infowindow
		var markers = this.markers;
		var infowindows = this.infowindows;
				
		// Chiudo tutte le infowindow rimaste aperte prima di mostrarne un'altra
		for (id in infowindows){
			infowindows[id].close();
		}
		for (id in markers){
			markers[id].desc=false;
		}
		
		// Create the infowindow linked to the marker OUTSIDE the Listener (prevents overlay of infowindows)
		var infowindow = new google.maps.InfoWindow({
			content: '',
			appendContent : function(content) {
				this.content = this.content+content;
			},
			attachTo : marker,
			showIn : mymap
		});
		
		// Se location già presente rendi marker e infowindow visibili
		if (markers[locId] && !multi) {
			mymap.panTo(markers[locId].getPosition());
			var marker = markers[locId];
			marker.setVisible(true);
			infowindows[locId].open(mymap, marker);
			return;
		}
			
		// Crea e aggiunge (tramite il parametro map) un marker con lat e lng del record
		
		/* Formo l'icona del marker in base alla categoria e/o alla numerazione, se tra quelle suggerite, usando la categoria del record o this.locNum*/
		var iconURL = '';
		if (!ordered) {
			/* Ho trovato una categoria conosciuta? */
			var found = false;
			var thiscat = record.get('category').toLowerCase();
			for (cat in this.icons){
				
				if (thiscat.indexOf(cat.toLowerCase()) != -1){
					
					iconURL = this.icons[cat];
					found = true;
				}
			}
			if (!found){
				/* Se no, inserisco il marker generico */
				iconURL = this.icons['Misc'];
			}
		}
		else {
			/* Se sono ordinati utilizzo dei marker incrementali */
			var num = this.locNum++;
			iconURL = "app/resources/markers/number_"+this.locNum+".png";
			
			/* Non si deve poter rimuovere altrimenti si generano comportamenti inaspettati */
			dragValue = false;
		}
			
		/* Creo il marker */
		var marker =  new google.maps.Marker({
			position : new google.maps.LatLng(record.get('lat'), record.get('long')),
			setCenter : this.position,
			// Conservo la posizione iniziale per futuri utilizzi
			origPos : new google.maps.LatLng(record.get('lat'), record.get('long')),
			map : mymap,
			icon : iconURL,
			visible : true,
			title : record.get('name'),
			draggable : dragValue
		});
		
		// Nonappena clicco sulla location compare anche l'infowindow associata SOLO se non voglio la cosa per location multiple
		if (!multi){
			infowindow.open(mymap, marker);
		}
		
		// Listener per drag = elimina marker
		google.maps.event.addListener(marker, 'dragend', function() {
			marker.setVisible(false); 
			/* Poiché, al ricomparire, il marker deve essere nella sua vecchia posizione... */
			marker.setPosition(marker.origPos);
			infowindow.close();
		});
		
		// Listener per doppio click = entra in modalità Street View
		google.maps.event.addListener(marker, 'rightclick', function() {
			/* Allo StreetViewer passo l'intero record per estrarre le coordinate */
			thisObject.setStreetView(record);
		});
		
		/* Con un solo click apro l'infowindow associata e chiudo le altre */
		google.maps.event.addListener(marker, 'click', function() {
			for (id in infowindows)
				infowindows[id].close();
			infowindow.open(mymap, marker);
			descrBox.setDescription(record);
			mymap.panTo(marker.getPosition());
		});
		// Centro la mappa sul marker SOLO se non sono marker multipli
		if (!multi){
			mymap.panTo(marker.getPosition());
		}
		
		// Salvo il marker e la infowindow associata negli array
		this.markers[locId] = marker;
		this.infowindows[locId] = infowindow;
		/* Inizializzo il contenuto della infowindow della location selezionata */
		infowindow.appendContent('<h1 align="center">'+record.get('name')+'</h1> <br /> <p><em>'+record.get('address')+'</em></p><br/>');
		if (record.get('tel')) // Solo se è presente
			infowindow.appendContent('<p><b>Tel: </b><i>'+record.get('tel')+'</i></p><br/>');
		/* Infine inserisco distanza e stato di apertura */
		this.setDistance(extMap.lat, extMap.lng, record.get('lat'), record.get('long'), infowindow);
		this.setOpening(record.get('opening'), infowindow); 
	},
	
	
	/* Metodo che rimuove il marker della location selezionata */
	removeAMarker : function(grid, record) {
		var marker = this.markers[record.get('id')];
		var infowindow = this.infowindows[record.get('id')];
		infowindow.close();
		marker.setVisible(false);
	},
	
	
	/* Metodo che elimina tutti i marker presenti sulla mappa */
	clearMap : function(){
		if (!this.markers) return;
		for (id in this.markers){
			this.markers[id].setVisible(false);
			this.infowindows[id].close();
		}
		this.locNum = 0;
	},
	
	
	// private
	setStreetView : function(record){
		var textbox = Ext.getCmp('descrBox');
		var locId = record.get('id');
		/* Segno che ho già inizializzato la descrizione per il marker */
		var lat = record.get('lat');
		var lng = record.get('long');
		/* Adatto la streetview alle dimensioni del pannello */
		var panelHeight = textbox.getHeight();
		var panelWidth =  textbox.getWidth();
		Ext.Ajax.request({
			url: 'http://ltw1140.web.cs.unibo.it',
			success: function (response) {
                textbox.update('<iframe height="'+panelHeight+'" width="'+panelWidth+'" frameborder="0" = scrolling="no" marginheight="0" marginwidth="0" src="http://maps.google.com/maps/sv?cbp=12,329.97,,0,11.09&amp;cbll='+lat+','+lng+'&amp;panoid=&amp;v=1&amp;hl=en&amp;gl=us"></iframe>');
			}
		});
	},
    
    // private 
    getDistance : function(lat1, long1, lat2, long2){
		var distance = 0;
		/* Se i parametri sono invalidi ritorno 0 */
		if (!lat1 || !lat2 || !long1 || !long2){
			return 0;
		}
		var r = 6372795.477598; // raggio terrestre
		/* Trasformo le coordinate in radianti */
		var lat1rad = Math.PI*lat1 / 180;
		var long1rad = Math.PI*long1 / 180;
		var lat2rad = Math.PI*lat2 / 180;
		var long2rad = Math.PI*long2 / 180;
		var phi = Math.abs(long1rad - long2rad);
		var p = Math.acos( (Math.sin(lat1rad) * Math.sin(lat2rad)) + (Math.cos(lat1rad) * Math.cos(lat2rad) * Math.cos(phi)) );
		distance = p * r;
		return distance;
	},
    
    // private
	setDistance : function(lat1, long1, lat2, long2, infowindow){
	/* Metodo per ottenere la distanza della location selezionata dalla posizione specificata */		
		Ext.Ajax.request({
			url: 'http://ltw1140.web.cs.unibo.it/distanza/params/'+lat1+'/'+long1+'/'+lat2+'/'+long2+'/',
			success: function (response) {
				infowindow.appendContent('<p><b>Distanza (in linea d\'aria):</b><i> '+response.responseText+' metri</i></p></br>');
			},
			failure : function(msg) {
				infowindow.appendContent('<p><b>Distanza non disponibile</b></p><br/>');
				//
			}
		});
	},
	
	
	// private 
	setOpening : function(itstime, infowindow){
	/* Metodo per ottenere la distanza della location selezionata dalla posizione specificata */
		/* Prima converto l'orario attuale in un intervallo di orari di delay minuti */
		var delay = 60;
		var currentTime = new Date();
		var day = currentTime.getDay();
		/* Converto day da valore numerico a letterale inglese */
		var days = ["sun", "mon", "tue", "wed", "thu", "fri", "sat"];
		day = days[day];
		/* Converto i vari valori in una stringa valida per il descrittore */
		var hours = currentTime.getHours();
		var minutes = currentTime.getMinutes();
		var endingMinutes = minutes+delay;
		var endingHours = hours;
		/* Se incrementando endingMinutes sforo l'ora devo incrementare anche l'ora in maniera adeguata */
		if (endingMinutes >= 60){
			var i = 0;
			while (endingMinutes >= 60) {
				endingMinutes = endingMinutes-60;
				i++;
			}
			endingHours = endingHours+i;
			if (endingHours >= 24) {
				while (endingHours >= 24){
					// Il max valore di cui può incrementare è un'ora
					endingHours = endingHours-24;
				}
			}
		}
		/* Aggiungo uno 0 iniziale se il valore è compreso tra 0 e 9 */
		if (hours < 10) {
			hours = '0'+hours;
		}
		if (minutes < 10) {
			minutes = '0'+minutes;
		}
		if (endingHours < 10) {
			endingHours = '0'+endingHours;
		}
		if (endingMinutes < 10) {
			endingMinutes = '0'+endingMinutes;
		}
		/* Costruisco la stringa da passare al descrittore */
		var mytime = day+': '+hours+minutes+'-'+endingHours+endingMinutes+'.';
		//
		var url = 'http://ltw1140.web.cs.unibo.it/aprira/params/'+itstime+'/'+mytime;
		
		Ext.Ajax.request({
			url: url,
			success: function (response) {
                opening = response.responseText;
                switch (opening) {				
					case '0' :
						opening = '<font color="red"> È chiuso </font>';
						break;
					case '1' :
						opening = '<font color="yellow"> Sta per aprire </font>';
						break;
					case '2' :
						opening = '<font color="green"> È aperto </font>';
						break;
					case '3' :
						opening = '<font color="orange"> Sta per chiudere </font>';
						break;
					case '4' :
						opening = '<font color="yellow"> Stà per chiudere ma riaprirà più tardi </font>';
						break;			
					default : 
						opening = '<font color="red">Non disponibile</font>';
						break;
				}
				infowindow.appendContent('<p><b>È aperto nei prossimi '+delay+' minuti?</b><i> '+opening+'</i></p><br/>');
				infowindow.close();
				infowindow.open(infowindow.showIn, infowindow.attachTo);
			}
		}); 
	}
	
});
