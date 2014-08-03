// Menu principale
Ext.define('NR.view.Index', {	
	extend : 'Ext.grid.Panel',
	
	store : 'Category',
	
	alias : 'widget.index',
	
	width : '15%',
	
	columns : [
		{
			header : 'Categorie',
			tooltip : 'Clicca su una categoria per visualizzare luoghi appartenenti solo a quella categoria',
			flex : true,
			dataIndex : 'category',
			resizable : false,
			hideable : false
		}]
	
});
