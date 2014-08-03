/* Definisco il pannello contenente la descrizione della location selezionata */
Ext.define('NR.view.Textbox', {
	extend : 'Ext.panel.Panel',
	alias : 'widget.textbox',
	
	title : 'Informazioni',
	
	id : 'descrBox',
	
	collapsible : true,
	
	autoScroll : true,
	
	hideable : true,
	
	/* Messaggio di benvenuto iniziale */
	html : '<h1>Benvenuto!</h1><br /> \
		<p>Benvenuto nel progetto <b>MetaMarket</b>.</p> \
		<p>Grazie a questa applicazione è possibile consultare un database di attività commerciali filtrandole per categoria e visualizzandone \
		la posizione su Google Maps arricchita da una descrizione del luogo.</p><br /> \
		<p>Per maggiori informazioni (e per conoscere tutte le feature) leggi la <i><a target="_blank" href="../doc">Guida all\'utilizzo</a></i></p> \
		<p>In qualunque momento puoi consultare una versione <i>mobile</i> della guida nella tab "Help"</p> ',
		
	
	/* ATTENZIONE! La percentuale è in base all'altezza della finestra, non del box! */
	bodyStyle : {
		"text-align" : "center",
		"padding-top" : "1%",
		"padding-right" : "3px",
		"padding-left" : "3px",
		"font-size" : "16px",
		"text-shadow" : '0px 0px 2px rgba(200,200,200,0.7)',
		"color" : "rgba(20,20,20,0.9)",
		"background-color" : "rgb(230,230,230)"
	},
	
	height : '40%',
		
	// private
	setDescription : function(record){
		var locId = record.get('id');
		var aggId = record.get('aggregatore');
		
		var descrBox = Ext.getCmp('descrBox');
		var h = this.getHeight();
		var w = this.getWidth();
		descrBox.update('<h1>Attendi...</h1><img style="text-align: center; margin-top: 7%" src="app/resources/loading.gif" height="'+(h-210)+'" width="'+(h-210)+'" />');
		var url = "http://ltw1140.web.cs.unibo.it/cgi-bin/ajaxCompliant.php?url=http://ltw1134.web.cs.unibo.it/descrittore-descrizione/"+aggId+"/params/"+locId;
		url.replace(' ','%20');
		Ext.Ajax.request({
			// Evito cross domain error chiamando uno script locale
			url: url,
			success: function (response) {
                if (response)
                	descrBox.update(response.responseText+"<br/>");
                else 
                	descrBox.update("<h1>Errore</h1><p>Si e' verificato un errore temporaneo con il descrittore 'Descrizione'. Ti preghiamo di riprovare piu' tardi.</p><br/>");
			},
			error : function(msg) {
				descrBox.update('<h1 style="font-color:red;">Errore!</h1>'+response.responseText+"<br/>");
			}
		});
	}
	
	
});
