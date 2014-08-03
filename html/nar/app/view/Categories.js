/* Grid contenente le categorie selezionate per il descrittore cosa-faccio-oggi */
Ext.define('NR.view.Categories', {
	extend : 'Ext.grid.Panel',
	
	alias : 'widget.categories',
	
	id : 'categories',
	
	store : 'Categories',
	
	title : 'Categorie selezionate',
	
	height : '45%',
	
	css : "text-align : center;",
	
	columns : [{
		header : 'Categorie',
		sortable : true,
		dataIndex : 'category',
		flex : true
	}]
	
	
});
