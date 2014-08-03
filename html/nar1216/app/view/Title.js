/* ***************************************************************************  */
/*							 Titolo e Header									*/
Ext.define('NR.view.Title', {
	extend : 'Ext.panel.Panel',
	
	alias : 'widget.title',
	layout : 'fit',
	height : function() { this.getHeight(); },
	width : function() { this.getWidth(); },
	html : '',
	bodyStyle : { //immafine del header
		"background-image" : "url('http://img46.imageshack.us/img46/9929/headervjl.png')",
		"background-repeat" : "repeat-x",
		"background-position" : "top",
		"padding-top" : '0.3%',
		"padding-left" : '0.5%',
	},
	
	height : '12%',
	border : false
		
});


