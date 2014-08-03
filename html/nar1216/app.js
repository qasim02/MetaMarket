/* **************************************************************************** */
/*										MAIN									*/
Ext.application({
	
	/* Namespace */
	name : 'NR',
/* **************************************************************************** */	
/* 						1°  livello dell'applicazione 							*/
	models : ['Location', 'Category'],
	stores : ['Location', 'Category', 'Categories', 'WhatNext'],
	controllers : ['Index', 'Location'],
	views : ['Title', 'Index', 'Content', 'Infobox'],
	
/* **************************************************************************** */		
/* 						Inizializzazione 										*/
	launch : function() {
		// Istanzio il Viewport
		Ext.create('NR.view.Viewport');
		var mymap = Ext.getCmp('rightMap');
		//Coordinate di Inizializzazione 
		var lat = 44.491362;
		var lng = 11.359134;
		//Controllo se sosso trovare la posizione in automatico 
		if (navigator.geolocation){
			navigator.geolocation.getCurrentPosition(
				function success(position){
					//Se trovo la posizione punto la mappa nella posizione lat , long 
					var mylat = position.coords.latitude;
					var mylng = position.coords.longitude;
					mymap.setCenter(mylat, mylng);
				},
				function error(msg){
					//Altrimenti indirizzo di default 
					mymap.setCenter(lat, lng);
				}
			);
			
		} else {
			//Altrimenti indirizzo di default 
			mymap.setCenter(lat, lng);
		}
		//Carico la documentazione nella tab Help
		var helpPanel = Ext.getCmp('help');
		Ext.Ajax.request({
			// Carico l'html della documentazione
			url: '../relazione',
			success: function (response) {
                if (response)
                	helpPanel.update(response.responseText);
                else 
                	helpPanel.update("<h1>ERROR</h1><p>Si e' verificato un errore . Riprova tra qualche istante.</p><br/>");
			},
			error : function(msg) {
				helpPanel.update('<h1 style="font-color:red;">ERROR!</h1>'+response.responseText+"<br/>");
			}
		});
	}
});

