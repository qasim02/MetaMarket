/* Definisco il pannello con informazioni riguardo la location selezionata */
Ext.define('NR.view.Infobox', {
	extend : 'Ext.panel.Panel',
	alias : 'widget.infobox',
	
	id: 'infoBox',
	
	title : 'Google Maps ©',
	
	requires : ['NR.view.Geolocal', 'NR.view.Textbox'],
	
	layout: 'border',
	
	collapsible : true,
	
	width : '49%',
	
	items : [{
		region : 'center',
		xtype : 'geolocal'
	},{
		xtype : 'panel',
		layout : 'border',
		height : "5%",
		items : [{
			xtype : 'button',
			bodyStyle : {
				"text-align" : "center"
			},
			text : '<b>Rileva Posizione</b>',
			listeners : {
				click : function(){
					var mymap = Ext.getCmp('rightMap');
					mymap.geoLocCenter();
				}
			},
			region : 'center'
		}],
		region : 'north'
	},{
		region : 'south',
		xtype : 'textbox'
	}]
	
	
});
