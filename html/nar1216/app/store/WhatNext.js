/* **************************************************************************** */
// Store delle location provenienti dal descrittore cosa-faccio-oggi
Ext.define ('NR.store.WhatNext', {
	extend : 'Ext.data.Store',
	requires : ['NR.model.Location'],
	model : 'NR.model.Location'
		/* ATTENZIONE! Il sorters NON C'e! L'ordine e quello dato dal descrittore e tale deve rimanere */
		/* Il contenuto viene caricato solo quando l'utente lo richiede */
});

