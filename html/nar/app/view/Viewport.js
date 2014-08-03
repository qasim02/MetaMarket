// Viewport principale
Ext.define('NR.view.Viewport', {
	
	extend : 'Ext.container.Viewport',
	
	requires : ['NR.view.Title', 'NR.view.Index', 'NR.view.Content', 'NR.view.Infobox'],
	
	layout : 'border',
	
	items : [{
		xtype : 'title',
		region : 'north'
	},{
		xtype : 'index',
		region : 'west'
	},{
		xtype : 'infobox',
		region : 'east'
	},{
		xtype : 'content',
		region : 'center'
	}]
});
