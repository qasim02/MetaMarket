// Store delle location provenienti dal descrittore cosa-faccio-oggi
Ext.define ('NR.store.WhatNext', {
	extend : 'Ext.data.Store',
	
	// Richiede che sia definito il modello per la lettura
	requires : ['NR.model.Location'],
	
	// Path assoluto della classe: NS.package.Class richiesto!
	model : 'NR.model.Location'

	/* ATTENZIONE! Il sorters NON C'È! L'ordine è quello dato dal descrittore e tale deve rimanere */
	
	/* Il contenuto viene caricato solo quando l'utente lo richiede */
});

