// Titolo e Header
Ext.define('NR.view.Title', {
	extend : 'Ext.panel.Panel',
	
	alias : 'widget.title',
	
	layout : 'fit',
	
	height : function() { this.getHeight(); },
	
	width : function() { this.getWidth(); },
	
	html : '<h1>MetaMarket Project 2.0 - OM3GA-TWG</h1>',
	bodyStyle : {
		"background-image" : "url('http://unblogen.files.wordpress.com/2010/02/88034-ubuntu-rain.jpg')",
		"background-repeat" : "repeat-x",
		"background-position" : "center",
		"padding-top" : '0.3%',
		"padding-left" : '0.5%',
		"text-align" : 'left',
		"font-size" : '35px',
		"font-family" : "Liberation Sans",
		"color" : 'white',
		"text-shadow" : '0px 0px 15px rgba(0,0,0,1)'
	},
	
	height : '8%',
	border : false
		
});


