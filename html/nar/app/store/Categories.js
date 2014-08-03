/* Store delle categorie selezionate */
Ext.define('NR.store.Categories', {
	extend : 'Ext.data.Store',

	/* Richiede che sia definito il modello per la lettura dei dati */
	requires : ['NR.model.Category'],
	/* Path assoluto della classe: NS.package.Class richiesto! */
	model : 'NR.model.Category'

	
});
