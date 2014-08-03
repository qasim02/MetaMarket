// Controller per la view Location
Ext.define('NR.controller.Location', {
	extend : 'Ext.app.Controller',
	
	id : 'locationController',
	
	
		
	// Method launched during first loading
	init : function (){
		// Method to control events
		this.control({
			'location' : {
				// Quando clicco su un elemento della tabella mostro le relative info
				itemclick : this.showLocation,
				// Se faccio doppio click elimino il marker di quella location dalla mappa
				itemdblclick : this.removeLocation
			},
			'whatnext' : {
				// Quando clicco su un elemento della tabella mostro le relative info
				itemclick : this.showLocation,
				// Se faccio doppio click elimino il marker di quella location dalla mappa
				itemdblclick : this.removeLocation
			}
		})
	},  
	
		
	
	/* Metodo che espande la location selezionata mostrando marker e descrizione */
	showLocation : function(grid, record){
		var rightMap = Ext.getCmp('rightMap');
		var descrBox = Ext.getCmp('descrBox');
		
		/* Workaround per la stringa iniziale */
		if (!record.get('address')) return;
		
		// Aggiungo la descrizione 
		if (record.get('aggregatore')){
			descrBox.setDescription(record);
			descrBox.show();
			descrBox.expand();
			descrBox.setDisabled(false);
		}
		else {
			descrBox.collapse();
			descrBox.hide();
		}
		
		// Aggiungo il marker
		rightMap.addAMarker(grid, record);
	},
	
	/* Metodo per rimuovere una location dalla mappa */
	removeLocation : function(grid, record){
		/* Workaround per la stringa iniziale */
		if (!record.get('address')) return;
		var rightMap = Ext.getCmp('rightMap');
		rightMap.removeAMarker(grid, record);
		Ext.MessageBox.alert('Avviso','Hai eliminato la location: '+record.get('name')+' dalla mappa.');
	}
	
		
	
});
			
