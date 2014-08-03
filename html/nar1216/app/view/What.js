/* *************************************************************************** */
/* Descrittore cosa-faccio-oggi: 
 * un pannello "border" separato in due sezioni. Una per le categorie 
 * selezionate, una per le location da visitare, in ordine 						*/
Ext.define('NR.view.What', {
	extend : 'Ext.panel.Panel',
	
	layout: 'border',
	alias : 'widget.what',
	/* La classe Categories per la lista di categorie "on the fly" mentre 
	 * WhatNext e la lista di location prese dal descrittore 					*/
	requires : ['NR.view.Buttons', 'NR.view.Categories', 'NR.view.WhatNext'],
	
	
	items : [{
		xtype : 'buttons',
		region : 'center'
	},{
		xtype : 'categories',
		region : 'north'
	},{
		xtype : 'whatnext',
		region : 'south'
	}]


});
