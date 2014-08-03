/* **************************************************************************** */
/* Interpretazione di file XML contenenti liste di categorie 					*/

Ext.define('NR.model.Category', {
	extend : 'Ext.data.Model',
	fields : [											//Campi del record principale (list)
		{name : 'category', mapping : '@id'},
		{name : 'aggregatore', mapping : '@aggregatore'}
	]
	
});
			
		
		
		
	
