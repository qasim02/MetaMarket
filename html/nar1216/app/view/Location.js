/* **************************************************************************** */
/* 		Tabella contenente le location da mostrare all'utente 					*/
Ext.define('NR.view.Location', {
	extend : 'Ext.grid.Panel',
	alias : 'widget.location',
	id : 'locationView',
	store : 'Location',				//Inserisco lo specifico store
		
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
	
	/* L'indirizzo e mostrato nelle infoWindow associate ai Marker */
	
});
