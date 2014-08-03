/* Bottone che invia la richiesta per il descrittore cosa-faccio-oggi */
Ext.define('NR.view.Buttons', {
	extend : 'Ext.panel.Panel',
	
	/* URL di base: http://ltw1130.web.cs.unibo.it/cosa-faccio-oggi/$AGGS/params/$LAT/$lng/$CATS , aggs e cats separati da /*/
	
	alias : 'widget.buttons',
	
	layout : 'border',
	
	items : [{
		xtype : 'button',
		text : '<b>Trova Per Nome</b>',
		listeners : {
			click : function updateStore(url){
					//Leggo la categoria selezionata nella listview
					var w = document.myform.mylist.selectedIndex;
					var cat = document.myform.mylist.options[w].value;
					//Aggiorno il proxy dello store facendo richiesta al descrittore Ricerca per nome
					if(url)
					{				
						store.proxy.url = 'http://ltw1210.web.cs.unibo.it/Trova-per-nome/'+cat+'/params/'+url;
						store.read();        
					}
					else
					{
						store.proxy.url = 'http://ltw1210.web.cs.unibo.it/Trova-per-nome/'+cat+'/params/*';
						store.read();
					}
					loadAll(store.proxy.url);
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


