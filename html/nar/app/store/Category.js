/* Store delle categorie */
Ext.define('NR.store.Category', {
	extend : 'Ext.data.Store',
	
	/* Richiede che sia definito il modello per la lettura dei dati */
	requires : ['NR.model.Category'],
	/* Path assoluto della classe: NS.package.Class richiesto! */
	model : 'NR.model.Category',
	
	/* Ordino, di default, per ordine alfabetico */
	sorters : {
		property : 'category',
		direction : 'ASC'
	},
	
	/* Richiesta Ajax per il download della lista di categorie */
	// NOTA: Si rivolge all'apposito descrittore
	proxy : {
		type : 'ajax',
		url : 'http://ltw1140.web.cs.unibo.it/tipologia/params/list/category',
		timeout : 60000,
		
		/* Reader XML */
		reader : {
			type : 'xml',
			root : 'list',
			record : 'category'
		}
	},
	
	/* Carico i dati durante l'istanziazione */
	autoLoad : true
	
});
