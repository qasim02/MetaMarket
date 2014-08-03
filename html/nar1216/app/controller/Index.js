/* **************************************************************************** */
/* 							controller/index.js									*/				
/* 					Controller per le interazioni con l'Index 					*/	
Ext.define('NR.controller.Index', {
	extend : 'Ext.app.Controller',
	id : 'indexController',
	//Funzione di inizializzazione per gli handler di eventi 
	init : function() {
		this.control({
			'index' : {
				itemclick : this.fillThisCategory
			},
			'categories' : {
				itemdblclick : this.removeCategory
			}
		})	
	},
	
/* **************************************************************************** *
 * Metodo per aggiungere la categoria selezionata al descrittore 
 * 							cosa-faccio-oggi 									*/
	addCategory : function(grid, record) {
		/* Prima uniformo la stringa della categoria richiesta */
		var selected = record.get('category');
		selected.toLowerCase();
		selected.replace('/', '%2F');
		/* "Catturo" la view Categories */
		var categories = Ext.getCmp('categories');
		var catStore = categories.getStore();
		/* Aggiungo la categoria SSE non è già presente */
		if (catStore.indexOf(record) == -1)
			catStore.add(record);
	},
	/* Rimuovere una categoria se si fa doppio click */
	removeCategory : function(grid, record) {
		var catStore = Ext.getCmp('categories').getStore();
		catStore.remove(record);		
	},
	

/* **************************************************************************** *
 * Metodo che "riempie" la tabella centrale con le location della categoria 	*
 * selezionata e aggiunge la categoria selezionata a cosa-faccio-oggi 			*
 * Innanzitutto aggiungo la categoria selezionata alle categorie di 			*
 * 								cosa-faccio-oggi 								*/
	fillThisCategory : function(grid, record){
		this.addCategory(grid,record);
		/* Poi uniformo la stringa della categoria richiesta */
		var selected = record.get('category');
		selected.toLowerCase();
		selected.replace('/', '%2F');
		/* Salvo la View tabellare e ne modifico lo store in modo da 
		 * 				immagazzinare le location 								*/
		var locationView = Ext.getCmp('locationView');
		var locationStore = locationView.getStore();
/* Pulisco lo store altrimenti sovrappongo le operazioni eseguite dal listener 
 * 						dichiarato in fondo (onLoad) 							*/
		locationStore.removeAll(false);
		/* Configuro il proxy per la richiesta asincrona 						*/
		locationStore.setProxy({
			/* Le location sono richieste dinamicamente al descrittore 	
			 * 						"tipologia" 								*/
			type : 'ajax',
			url : 'http://ltw1140.web.cs.unibo.it/tipologia/params/or/'+selected,

			/* XML Reader */
			reader : {
				type : 'xml',
				record : 'location'
			},
					
			failure : function(){
				Ext.MessageBox.alert('ERROR','Riprovare piu tardi');
			}
		});
/* Devo mostrare i marker relativi alla categoria selezionata SOLO se sono 
 * 				nella prima tab (che ha xtype location)! 						*/
		locationStore.on('load', function() {
			/* Appena lo store si carica mostra tutte le location sulla mappa */
			var selectedTab = Ext.getCmp('centralContent').getActiveTab().xtype; //ordine delle tab
			if (selectedTab == 'location'){
				var descrBox = Ext.getCmp('descrBox');
				/* Abilito e mostro il pannello di descrizione se 				*
				 * precedentemente nascosto o disabilitato 						*/
				descrBox.expand();
				descrBox.setDisabled(false);
				var mymap = Ext.getCmp('rightMap');
				var gmap = mymap.getMap();
				mymap.clearMap();
				this.each( 
					function(record) {
						window.log('adding : '+record.get('lat')+', '+record.get('long')+' from: '+record.get('aggregatore'));
						mymap.addAMarker(null, record, true);
					}
				);
				/* Centro la mappa sulla mia posizione */
				gmap.panTo(new google.maps.LatLng(mymap.lat, mymap.lng));
				gmap.setZoom(15);
			}
		});
		locationStore.load();	
	}
});
