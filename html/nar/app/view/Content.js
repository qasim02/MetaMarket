/* Contenuto centrale diviso in tab */
Ext.define('NR.view.Content', {
	extend : 'Ext.tab.Panel',
	
	id : 'centralContent',
	
	alias : 'widget.content',
	
	requires : ['NR.view.Location', 'NR.view.What'],
	
	items : [{
		xtype : 'location',
		title : 'Elenco attività commerciali'
	},{
		xtype : 'what',
		title : 'Percorso convenienza™'
	},{
		xtype : 'panel',
		title : 'Help!',
		id : 'help',
		autoScroll : true
	}]
	
	
});
