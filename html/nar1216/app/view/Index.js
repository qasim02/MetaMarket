// Menu principale
Ext.define('NR.view.Index', {	
	extend : 'Ext.grid.Panel',
	
	store : 'Category',
	
	alias : 'widget.index',
	
	width : '13%', //dimensione collona categorie
	
	columns : [
		{
			header : 'Categorie',
			tooltip : 'Clicca su una categoria per visualizzare luoghi della categoria',
			flex : true,
			dataIndex : 'category',
			resizable : false,
			hideable : false
		}]
	
});
