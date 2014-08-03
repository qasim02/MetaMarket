/* Definisco il pannello con informazioni riguardo la location selezionata */
Ext.define('NR.view.Infobox', {
	extend : 'Ext.panel.Panel',
	alias : 'widget.infobox',
	
	id: 'infoBox',
	
	title : 'Google Maps ©',
	
	requires : ['NR.view.Geolocal', 'NR.view.Textbox'],
	
	layout: 'border',
	
	collapsible : true,
	
	width : '65%',		//largezza google maps
	
	items : [{
		region : 'center',
		xtype : 'geolocal'
	},{
		region : 'south',
		xtype : 'textbox'
	}]
	
	
});
