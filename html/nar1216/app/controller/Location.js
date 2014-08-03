// Controller per la view Location
Ext.define('NR.controller.Location', {
	extend : 'Ext.app.Controller',
	
	id : 'locationController',
	init : function (){									// Method launched during first loading
		this.control({									// Method to control events
			'location' : {
				itemclick : this.showLocation,			// Click x le info 
				itemdblclick : this.removeLocation		// Doppio click per eliminare i marker
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
		if (!record.get('address')) 					// Workaround per la stringa iniziale
			return;	
		if (record.get('aggregatore')){					// Aggiungo la descrizione 
			descrBox.setDescription(record);
			descrBox.show();
			descrBox.expand();
			descrBox.setDisabled(false);
		}
		else {
			descrBox.collapse();
			descrBox.hide();
		}
			rightMap.addAMarker(grid, record);			// add il marker
	},
	
	/* Metodo per rimuovere una location dalla mappa */
	removeLocation : function(grid, record){
		if (!record.get('address')) 					// Workaround per la stringa iniziale
			return;	
		var rightMap = Ext.getCmp('rightMap');
		rightMap.removeAMarker(grid, record);
		Ext.MessageBox.alert('Info','Hai eliminato la location: '+record.get('name')+' dalla mappa.');
	}
});
			
