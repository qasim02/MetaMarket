// Store dei modelli XML
Ext.define ('NR.store.Location', {
	extend : 'Ext.data.Store',
	
	// Richiede che sia definito il modello per la lettura
	requires : ['NR.model.Location'],
	
	// Path assoluto della classe: NS.package.Class richiesto!
	model : 'NR.model.Location',
	
	sorters : {
		property : 'name',
		direction : 'ASC'
	},
	
	data : [{
		name : 'Nessuna categoria selezionata!'
	}]
	
	/* Il contenuto viene caricato solo quando l'utente lo richiede */
});
