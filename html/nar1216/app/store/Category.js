/* **************************************************************************** */
/* 								Store delle categorie 							*/
Ext.define('NR.store.Category', {
	extend : 'Ext.data.Store',
	requires : ['NR.model.Category'],
	model : 'NR.model.Category',
	
	sorters : {					//Ordine alfabetico 
		property : 'category',
		direction : 'ASC'
	},

/* **************************************************************************** */
/* 			Request Ajax per il download della lista di categorie 				*/
/*    Si rivolge all'apposito descrittore comprato alla fira dall'gruppo OMEGA  */
	proxy : {
		type : 'ajax',
		url : 'http://ltw1140.web.cs.unibo.it/tipologia/params/list/category/',
		timeout : 60000,
		/* Reader XML */
		reader : {
			type : 'xml',
			root : 'list',
			record : 'category'
		}
	},
	autoLoad : true		//Carico i dati
});
