/* *************************************************************************** */
/* Grid contenente le location selezionate per il descrittore cosa-faccio-oggi */
Ext.define('NR.view.WhatNext', {
	extend : 'Ext.grid.Panel',
	
	id : 'whatnext',
	alias : 'widget.whatnext',
	height : '45%',
	store : 'WhatNext',
	
	title : 'Risultato',
	
	columns : [{
		header : 'Nome',
		dataIndex : 'name',
		sortable : false,
		flex : true
	},{
		header : 'Categoria',
		dataIndex : 'category',
		sortable : false,
		flex : true	
	}]
	
	
});

