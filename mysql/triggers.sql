

DELIMITER $$
CREATE OR REPLACE TRIGGER after_fornitura_cessazione
AFTER UPDATE ON FORNITURA
FOR EACH ROW
BEGIN
    IF NEW.stato_fornitura = 'DISATTIVATA PER CESSAZIONE' 
       AND OLD.stato_fornitura != 'DISATTIVATA PER CESSAZIONE' THEN
        UPDATE CONTRATTO
        SET 
            stato_contratto = 'CESSATO',
            data_fine_validita = CURRENT_DATE
        WHERE 
            IdContratto = NEW.IdContratto
            AND stato_contratto = 'ATTIVO'; 
    END IF;
END$$
DELIMITER ;
--trigger che disattiva contratto se fornitura 'disattiva_cessata' testato



DELIMITER $$
CREATE OR REPLACE TRIGGER after_contatore_insert
AFTER INSERT ON CONTATORE
FOR EACH ROW
BEGIN
    IF NEW.stato_contatore = 'ATTIVO' THEN
        UPDATE FORNITURA
        SET 
            stato_fornitura = 'ATTIVA',
            data_attivazione = CURRENT_DATE
        WHERE 
            IdFornitura = NEW.IdFornitura
            AND stato_fornitura = 'IN ATTESA DI ATTIVAZIONE';
    END IF;
END$$
DELIMITER ;
--trigger su contatore che quando viene inserito e attivato attiva anche la fornitura, testato 


DELIMITER $$
CREATE OR REPLACE TRIGGER after_fornitura_cessazione_contatore
AFTER UPDATE ON FORNITURA
FOR EACH ROW
BEGIN
    IF NEW.stato_fornitura = 'DISATTIVATA PER CESSAZIONE' 
       AND OLD.stato_fornitura != 'DISATTIVATA PER CESSAZIONE' THEN
        UPDATE CONTATORE
        SET 
            stato_contatore = 'CESSATO PER DISATTIVAZIONE FORNITURA',
            data_dismissione = CURRENT_DATE
        WHERE 
            IdFornitura = NEW.IdFornitura
            AND stato_contatore = 'ATTIVO'; 
    END IF;
END$$
DELIMITER ;
--trigger che disattiva contatore se fornitura 'disattiva_cessata' testato


