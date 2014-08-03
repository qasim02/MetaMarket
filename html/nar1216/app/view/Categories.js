/* Grid contenente le categorie selezionate per il descrittore cosa-faccio-oggi */
Ext.define('NR.view.Categories', {
	extend : 'Ext.grid.Panel',
	
	alias : 'widget.categories',
	
	id : 'categories',
	store : 'Categories',
	title : 'Cerca_',  //bottone cerca da configurare
	
	height : '45%',
	
	//css : "text-align : left;",
	
	
	columns : [{
		 title: '<center>Ricerca</center>',
		 id: 'strumenti',	
		 region: 'center',
		 width: '100%',
		 height: 250,
		 html: '<br><center><b>Ricerca per Nome</b><br><br>Selezionare una Categoria per visualizzare le locazioni<br><br><FORM NAME="myform" action="javascript:updateStore(document.myform.s.value);"><SELECT NAME="mylist" onChange="javascript:updateStore();"><OPTION VALUE="ltw1210-cinema">Cinema<OPTION VALUE="ltw1209-ristoranti">Ristoranti<OPTION VALUE="ltw1209-bar">Bar<input type="text" name="s" value="" /></FORM><br>Per ricercare un luogo nella categoria selezionata digitare un Nome e premere Invio</center>',
		 cmargins: '5 0 0 0'
	}]
	
	
	
	
	
	/*columns : [{
		header : 'Categorie',
		sortable : true,
		dataIndex : 'category',
		flex : true
	}]*/
	
	
});
