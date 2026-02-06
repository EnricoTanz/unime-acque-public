
##sysadmin@email.it Password12345
##enrico.celesti@studenti.unime.it z0!4y%aADpyy amministratore
##ciccio.pasticcio@email.it O5hFoDx!yxox tecnico
##enrico.rossi@email.it 0x7zt8fFsP6* cliente
##enrico.verdi@email.it  k3V#Anavctu$ cliente
##vincenzo.vincenzi@email.it MByw!SQia0v5 amministrativo
##enrichetto@email.it Test1234 tecnico
##disabilitare@email.it cf 09983745213 Disabilitami123 cliente


##pulire il codice e verificare i vari filtri di validazione email
##creare un nuovo repo pubblico con la versione finale


##enrico verdi dovrebbe pagare 0.96*200 * 2.1 (isola bella) -> 403€ ma col volume rettificato è 203
##enrico rossi dovrebbe pagare 0.995 * 200 * 1.54(Centro terme vigliatore) -> 306,46€
##da cancellare fatture e rilanciare il pulsante, ricorda che tutto cio che viene creato deve essere precedente nella data inizio validita delle letture

SELECT 
    f.IdFattura,
    f.IdContratto,
    c.IdUtente,
    u.nome,
    u.cognome,
    f.importo_fattura,
    f.data_emissione,
    f.data_scadenza,
    f.periodo_rif_inizio,
    f.periodo_rif_fine
FROM FATTURA f
INNER JOIN CONTRATTO c ON f.IdContratto = c.IdContratto
INNER JOIN UTENTE u ON c.IdUtente = u.IdUtente
WHERE f.data_emissione = CURRENT_DATE
ORDER BY f.IdFattura DESC;


CALL genera_fatture_mensili();