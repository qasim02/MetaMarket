/* Tabella contenente le location da mostrare all'utente */
Ext.define('NR.view.Location', {
	extend : 'Ext.grid.Panel',
	
	alias : 'widget.location',
	
	id : 'locationView',
	
	/* Inserisco lo specifico store */
	store : 'Location',
	
	columns : [{
		header : 'Nome',
		hideable : false,
		dataIndex : 'name',
		flex : true
	},{
		header : 'Indirizzo',
		hideable : false,
		dataIndex : 'address',
		flex : true
	}]
	
	/* L'indirizzo è mostrato nelle infoWindow associate ai Marker */
	
});
