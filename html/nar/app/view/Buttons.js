/* Bottone che invia la richiesta per il descrittore cosa-faccio-oggi */
Ext.define('NR.view.Buttons', {
	extend : 'Ext.panel.Panel',
	
	/* URL di base: http://ltw1130.web.cs.unibo.it/cosa-faccio-oggi/$AGGS/params/$LAT/$lng/$CATS , aggs e cats separati da /*/
	
	alias : 'widget.buttons',
	
	layout : 'border',
	
	items : [{
		xtype : 'button',
		text : '<b>Proponi Giornata</b>',
		listeners : {
			click : function(){
				/* Salvo posizione dello user agent e... */
				var rightMap = Ext.getCmp('rightMap');
				var gRightMap = rightMap.getMap();
				/* Prima di iniziare una nuova ricerca pulisco la mappa */
				rightMap.clearMap();
				rightMap.getMap().panTo(new google.maps.LatLng(rightMap.lat, rightMap.lng));
				/* In più disabilito il pannello di descrizione perché non può essere usato */
				var descrBox = Ext.getCmp('descrBox');
				/* Usa il sessionStorage (dove possibile) per vedere se l'utente ha già avuto modo di vedere il popup */
				if (!sessionStorage.getItem('nodescr'))
					Ext.MessageBox.alert('Avviso', 'Il pannello di descrizione è attivo solo se si selezionano luoghi e categorie dalla prima tab!');
				/* Procedo alla disabilitazione */
				descrBox.collapse();
				descrBox.setDisabled(true);
				/* Centro la mappa sulla posizione */
				gRightMap.panTo(new google.maps.LatLng(rightMap.lat, rightMap.lng));
				var lat = rightMap.getCenterLatLng().lat;
				var lng = rightMap.getCenterLatLng().lng;
				/* ...Data di oggi da essere usati con il descrittore */
				var today = new Date();
				var day = today.getDate();
				if (day < 10) day = '0'+day;
				var month = today.getMonth()+1;
				if (month < 10) month = '0'+month;
				var year = today.getFullYear();
				var fullDate = year+'-'+month+'-'+day;
				
				/* Prendo tutti gli aggregatori dallo store "Categories" e li concateno */
				var categories = Ext.getCmp('categories');
				var catStore = categories.getStore();
				/* Se non è stata selezionata alcuna categoria non fare nulla */
				if (catStore.count() == 0){
					Ext.MessageBox.alert('Avviso','Devi selezionare almeno una categoria!');
					return;
				}
				var aggStr = '';
				catStore.each(function(record) {
					var agg = record.get('aggregatore');
					// Non aggiungo duplicati di aggregatori
					if (aggStr.indexOf(agg) == -1){
						aggStr = aggStr+agg+'/';
					}
				});
				aggStr = aggStr.replace(/\s/g, '');
				aggStr = aggStr.replace(/,/g, '/');
				
				/* Prendo tutte le categorie dallo store "Categories" e le concateno */
				var catStr = '';
				catStore.each(function(record) {
					var cat = record.get('category');
					catStr = catStr+cat+'/';
				});
				/* Sostituisco gli spazi con l'escape URI %20 ed eventuali , con / per separare gli aggregatori */
				catStr.replace(' ', '%20');
				/* Effettuo la richiesta al descrittore e carico lo store WhatNext con ciò che mi arriva */
				var url = 'http://ltw1140.web.cs.unibo.it/cgi-bin/ajaxCompliant.php?url=http://ltw1130.web.cs.unibo.it/cosa-faccio-oggi/';
				url = url+aggStr+'params/'+lat+'/'+lng+'/'+fullDate+'/'+catStr;
				url = url.replace(' ', '%20');
				window.log("requesting: "+url);
				 // DEBUG
				/* Carico lo store */
				var whatNextStore = Ext.getCmp('whatnext').getStore();
				whatNextStore.setProxy({
				/* ATTENZIONE: L'accept di Ext è *.* ma alcuni descrittori NON LO CAPISCONO! */
					url : url,
					type : 'ajax',
					timeout : 30000,
					// Se l'evento è generato dal click su un bottone è POST di default!
					reader : {
						type : 'xml',
						root : 'locations',
						record : 'location'
					}
				});
				/* Assegno un listener allo store: appena carica i dati li visualizza sulla mappa */
				whatNextStore.on('load', function() {
					var mymap = Ext.getCmp('rightMap');
					var gmap = mymap.getMap();
					mymap.clearMap();
					this.each( 
						function(record) {
							mymap.addAMarker(null, record, true, true);
						}
					);
					/* Centro e dezoomo la mappa sulla mia posizione */
					gmap.panTo(new google.maps.LatLng(mymap.lat, mymap.lng));
					/* Aumento lo zoom dato che, in teoria, desidero le location nelle vicinanze */
					gmap.setZoom(14);
				});
				sessionStorage.setItem('nodescr', 'true');
				whatNextStore.load();	
			}	
		},
		region : 'center'
	},{
		xtype : 'button',
		text : '<b>Reset</b>',
		height : '50%',
		listeners : {
			click : function(){
				/* Rimuovi tutte le categorie e le location */
				var categories = Ext.getCmp('categories');
				var whatnext = Ext.getCmp('whatnext');
				/* Pulisco gli store sia delle categorie selezionate sia delle location suggerite */
				var catStore = categories.getStore();
				var locStore = whatnext.getStore();
				catStore.removeAll();
				locStore.removeAll();
				/* Rimuovi tutti i marker dalla mappa */
				var rightMap = Ext.getCmp('rightMap'); // <- Bruttissimo, ma ormai!
				rightMap.clearMap();
				rightMap.getMap().panTo(new google.maps.LatLng(rightMap.lat, rightMap.lng));
				/* Azzero il contatore delle location suggerite */
			}
		},
		
		region : 'south'
	}]
	
	
});


