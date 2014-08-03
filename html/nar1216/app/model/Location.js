/* **************************************************************************** */
/*  					Modello delle location									*/
Ext.define('NR.model.Location', {
	extend : 'Ext.data.Model',
	// Campi del record principale (location) che voglio leggere
	fields : [						
		{name: 'id', mapping: '@id'},
		{name: 'name'},
		{name: 'category'},
		{name: 'address'},
		{name: 'lat', mapping: '@lat'},
		{name: 'long', mapping: '@long'},
		{name: 'opening'},
		{name: 'tel'},
		{name: 'note'},
		{name: 'aggregatore', mapping: '@aggregatore'}
	]
	
});
