/* Modello per l'interpretazione di file XML contenenti liste di categorie */

Ext.define('NR.model.Category', {
	extend : 'Ext.data.Model',
	
	/* Campi del record principale (list) */
	fields : [
		{name : 'category', mapping : '@id'},
		{name : 'aggregatore', mapping : '@aggregatore'}
	]
	
});
			
		
		
		
	
