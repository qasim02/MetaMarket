/* **************************************************************************** */
/* Definisco il pannello contenente la descrizione della location selezionata 	*/
Ext.define('NR.view.Textbox', {
	extend : 'Ext.panel.Panel',
	alias : 'widget.textbox',
	title : 'Informazioni',
	
	id : 'descrBox',
	collapsible : true,
	autoScroll : true,
	hideable : true,
	/* Messaggio di benvenuto iniziale */
	html : '<h1><mark>Gentile Utente</mark></h1><br /> \
		<p>Benvenuto nel portale <b>MetaMarket di ZABBIX 2.0 </b>.</p> \
		<p>In questo portalle e possibile scegliere  varie attivita commerciali per tipologia, selezionandole tramite la  \
		 posizione su Google Maps seguita da una descrizione del luogo.</p><br /> \
		<p>Per maggiori info consultate la documentazione ufficiale del progetto <i><a target="_blank" href="../relazione/index.html">Guida\ </a></i></p> \
		<p>Versione <i>mobile</i> della guida nella tab "Help"</p> ',
		
	
	/* ATTENZIONE! La percentuale e in base all'altezza della finestra, non del box! */
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
	
	height : '30%',
		
	// private
	setDescription : function(record){
		var locId = record.get('id');
		var aggId = record.get('aggregatore');
		
		var descrBox = Ext.getCmp('descrBox');
		var h = this.getHeight();
		var w = this.getWidth();
		descrBox.update('<h1>Attendi...</h1><img style="text-align: center; margin-top: 7%" src="app/resources/loading.gif" height="'+(h-210)+'" width="'+(h-210)+'" />');
		var url = "http://ltw1216.web.cs.unibo.it/cgi-bin/ajaxCurl.php?url=http://ltw1134.web.cs.unibo.it/descrittore-descrizione/"+aggId+"/params/"+locId;
		
                //http://ltw1134.web.cs.unibo.it/descrittore-descrizione/ltw1134-aggregatore-guardie-mediche/ltw1134-aggregatore-pronto-soccorsi/ltw1134-aggregatore-sportelli-cup/params/ps0016
                
                url.replace(' ','%20');
		Ext.Ajax.request({
			// Evito cross domain error chiamando uno script locale
			url: url,
			success: function (response) {
                if (response)
                	descrBox.update(response.responseText+"<br/>");
                else 
                	descrBox.update("<h1>Errore</h1><p>Si e' verificato un errore temporaneo con il descrittore 'Descrizione'. Eiprovare piu' tardi.</p><br/>");
			},
			error : function(msg) {
				descrBox.update('<h1 style="font-color:red;">Errore!</h1>'+response.responseText+"<br/>");
			}
		});
	}
	
	
});
