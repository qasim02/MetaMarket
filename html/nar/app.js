/* Esecuzione iniziale (MAIN) */
Ext.application({
	
	/* Namespace */
	name : 'NR',
	
	/* Contenuti di primo livello dell'applicazione */
	models : ['Location', 'Category'],
	stores : ['Location', 'Category', 'Categories', 'WhatNext'],
	controllers : ['Index', 'Location'],
	views : ['Title', 'Index', 'Content', 'Infobox'],
	
	
	/* Funzione di inizializzazione */
	launch : function() {		
		// Istanzio il Viewport
		Ext.create('NR.view.Viewport');
		/* Indirizzo di fallback */
		var lat = 44.49706;
		var lng = 11.356277;
		var mymap = Ext.getCmp('rightMap');
		mymap.setTheCenter(lat,lng);
			
		/* Carico la documentazione nella tab Help */
		var helpPanel = Ext.getCmp('help');
		Ext.Ajax.request({
			// Carico l'html della documentazione
			url: '../doc',
			success: function (response) {
                if (response)
                	helpPanel.update(response.responseText);
                else 
                	helpPanel.update("<h1>Errore</h1><p>Si e' verificato un errore temporaneo. Ti preghiamo di riprovare piu' tardi.</p><br/>");
			},
			error : function(msg) {
				helpPanel.update('<h1 style="font-color:red;">Errore!</h1>'+response.responseText+"<br/>");
			}
		});
		
		/* Infine se ho geolocalizzazione centro opportunamente la mappa */
	}
});

