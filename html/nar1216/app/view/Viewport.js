/* ***************************************************************************  */
/*							Viewport principale									*/
Ext.define('NR.view.Viewport', {
	extend : 'Ext.container.Viewport',
	requires : ['NR.view.Title', 'NR.view.Index', 'NR.view.Content', 'NR.view.Infobox'],
	
	layout : 'border',
	
	items : [{
		xtype : 'title',
		region : 'north' // Header del Naratore 
	},{
		xtype : 'index',
		region : 'east'   //elenco categorie 
	},{
		xtype : 'infobox',
		region : 'west'		//mapa con infobox	
	},{
		xtype : 'content',
		region : 'center'
	}]
});
