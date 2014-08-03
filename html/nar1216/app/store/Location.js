/* **************************************************************************** */
/* 							Store dei modelli XML								*/
Ext.define ('NR.store.Location', {
	extend : 'Ext.data.Store',
	requires : ['NR.model.Location'],	
	model : 'NR.model.Location',		
	sorters : {
		property : 'name',
		direction : 'ASC'
	},
	data : [{
		name : 'Nessuna categoria selezionata!'
	}]
	
	/* NOTA: Il contenuto viene caricato da richiesta dell'utente per 
	 * evitare troppi carricamenti che possono causare lentezze   				*/
});
