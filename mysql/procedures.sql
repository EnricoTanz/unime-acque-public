





##user: myapplication
##pass: phpmysql



DELIMITER $$
CREATE OR REPLACE PROCEDURE applica_sovrapprezzo_fatture_scadute()
BEGIN
    UPDATE FATTURA
    SET 
        sovrapprezzo = 10.00,
        importo_fattura = importo_fattura * 1.10
    WHERE 
        data_pagamento IS NULL
        AND sovrapprezzo = 0.00
        AND DATE_ADD(data_scadenza, INTERVAL 30 DAY) <= CURRENT_DATE;
END$$
DELIMITER ;
##idealmente schedulata ogni giorno, la procedura applica un sovrapprezzo del 10% alle fatture scadute da piu' di 30gg



DELIMITER $$
CREATE OR REPLACE PROCEDURE applica_sconto_fattura(
    IN p_IdFattura INT,
    IN p_percentuale_sconto DECIMAL(5,2)
)
BEGIN
    DECLARE v_importo_attuale DECIMAL(10,2);
    DECLARE v_data_pagamento DATE;
    DECLARE v_sconto_esistente DECIMAL(5,2);
    DECLARE v_nuovo_importo DECIMAL(10,2);
    DECLARE v_importo_sconto DECIMAL(10,2);
    
    IF p_percentuale_sconto < 0 OR p_percentuale_sconto > 100 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Errore: La percentuale di sconto deve essere tra 0 e 100';
    END IF;
    SELECT importo_fattura, data_pagamento, sconto
    INTO v_importo_attuale, v_data_pagamento, v_sconto_esistente
    FROM FATTURA
    WHERE IdFattura = p_IdFattura;
    IF v_importo_attuale IS NULL THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Errore: Fattura non trovata';
    END IF;
    IF v_data_pagamento IS NOT NULL THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Errore: Impossibile applicare sconto a fattura già pagata';
    END IF;
    IF v_sconto_esistente > 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Errore: La fattura ha già uno sconto applicato';
    END IF;
    SET v_nuovo_importo = v_importo_attuale * (1 - p_percentuale_sconto / 100);
    SET v_importo_sconto = v_importo_attuale - v_nuovo_importo;
    UPDATE FATTURA
    SET 
        importo_fattura = v_nuovo_importo,
        sconto = p_percentuale_sconto
    WHERE IdFattura = p_IdFattura;
    SELECT 
        p_IdFattura AS 'ID Fattura',
        v_importo_attuale AS 'Importo originale (€)',
        p_percentuale_sconto AS 'Sconto applicato (%)',
        v_importo_sconto AS 'Importo scontato (€)',
        v_nuovo_importo AS 'Nuovo importo (€)',
        'Sconto applicato con successo' AS 'Stato';
END$$
DELIMITER ;
##a questa procedura si passa una fattura specifica e una percentuale di sconto, e la procedura aggiornera' l'importo con lo sconto











##la procedura che segue marca una fattura come pagata. Gli si passa idCarta usata e l'id fattura che l'utente vedra' nel menu a tendina delle fatture non pagate e delle carte attive. gestito da php. testata

DELIMITER $$


CREATE OR REPLACE PROCEDURE registra_pagamento_fattura(
    IN p_IdFattura INT,
    IN p_IdCarta INT
)
BEGIN
    ## Variabile per verificare l'esistenza della fattura
    DECLARE v_fattura_exists INT DEFAULT 0;
    DECLARE v_gia_pagata INT DEFAULT 0;
    DECLARE v_carta_valida INT DEFAULT 0;
    
    ## Verifica che la fattura esista
    SELECT COUNT(*) INTO v_fattura_exists
    FROM FATTURA
    WHERE IdFattura = p_IdFattura;
    
    IF v_fattura_exists = 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Fattura non trovata';
    END IF;
    
    ## Verifica che la fattura non sia già stata pagata
    SELECT COUNT(*) INTO v_gia_pagata
    FROM FATTURA
    WHERE IdFattura = p_IdFattura
      AND data_pagamento IS NOT NULL;
    
    IF v_gia_pagata > 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Fattura già pagata';
    END IF;
    
    ## Verifica che la carta di credito esista e sia valida
    SELECT COUNT(*) INTO v_carta_valida
    FROM CARTA_DI_CREDITO
    WHERE IdCarta = p_IdCarta
      AND data_scadenza >= CURRENT_DATE;
    
    IF v_carta_valida = 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Carta di credito non valida o scaduta';
    END IF;
    
    ## Aggiorna la fattura con i dati del pagamento
    UPDATE FATTURA
    SET data_pagamento = CURRENT_DATE,
        IdCarta_pagamento = p_IdCarta
    WHERE IdFattura = p_IdFattura;
    
END$$

DELIMITER ;





--procedura per sostituire vecchio contatore testata
DELIMITER $$

CREATE OR REPLACE PROCEDURE imposta_contatore_sostituito(
    IN p_IdContatore INT
)
BEGIN
    -- Aggiorna il contatore: marca come SOSTITUITO e imposta data_sostituzione
    -- Solo se il contatore è attualmente ATTIVO
    UPDATE CONTATORE
    SET 
        stato_contatore = 'SOSTITUITO',
        data_sostituzione = CURRENT_DATE
    WHERE 
        IdContatore = p_IdContatore
        AND stato_contatore = 'ATTIVO';
        
END$$

DELIMITER ;







##secondo test per procedura fatture
DELIMITER $$

CREATE OR REPLACE PROCEDURE genera_fatture_mensili()
BEGIN
    DECLARE v_periodo_inizio DATE;
    DECLARE v_periodo_fine DATE;
    DECLARE v_data_emissione DATE;
    DECLARE v_data_scadenza DATE;
    
    SET v_data_emissione = CURRENT_DATE;
    SET v_data_scadenza = DATE_ADD(CURRENT_DATE, INTERVAL 30 DAY);
    SET v_periodo_inizio = DATE_FORMAT(DATE_SUB(CURRENT_DATE, INTERVAL 1 MONTH), '%Y-%m-01');
    SET v_periodo_fine = LAST_DAY(DATE_SUB(CURRENT_DATE, INTERVAL 1 MONTH));
    
    INSERT INTO FATTURA (
        IdContratto,
        importo_fattura,
        sovrapprezzo,
        sconto,
        data_emissione,
        data_scadenza,
        periodo_rif_inizio,
        periodo_rif_fine
    )
    WITH 
    contratti_da_fatturare AS (
        SELECT DISTINCT 
            c.IdContratto,
            c.stato_contratto,
            f.IdFornitura,
            f.IdArea_fornitura
        FROM CONTRATTO c
        INNER JOIN FORNITURA f ON c.IdContratto = f.IdContratto
        WHERE 
            (
                c.stato_contratto = 'ATTIVO'
                AND f.stato_fornitura = 'ATTIVA'
                AND c.data_inizio_validita <= v_periodo_fine
                AND (c.data_fine_validita IS NULL OR c.data_fine_validita >= v_periodo_fine)
            )
            OR
            (
                c.stato_contratto = 'CESSATO'
                AND c.data_fine_validita >= v_periodo_inizio
                AND c.data_fine_validita <= v_periodo_fine
            )
    ),
    contatori_attivi AS (
        SELECT 
            cdf.IdContratto,
            cdf.IdFornitura,
            co.IdContatore
        FROM contratti_da_fatturare cdf
        INNER JOIN CONTATORE co ON cdf.IdFornitura = co.IdFornitura
        WHERE co.data_installazione <= v_periodo_fine
          AND (co.data_dismissione IS NULL OR co.data_dismissione >= v_periodo_inizio)
        ORDER BY co.data_installazione DESC
    ),
    tariffe_minime AS (
        SELECT 
            cdf.IdContratto,
            MIN(t.tariffa_applicata) AS tariffa_applicata
        FROM contratti_da_fatturare cdf
        INNER JOIN ABBINAMENTO_TARIFFA at ON cdf.IdContratto = at.IdContratto
        INNER JOIN TARIFFA t ON at.IdTariffa = t.IdTariffa
        WHERE at.data_inizio <= CASE 
                WHEN cdf.stato_contratto = 'ATTIVO' THEN v_data_emissione
                ELSE v_periodo_fine
            END
          AND (at.data_fine IS NULL OR at.data_fine >= CASE
                WHEN cdf.stato_contratto = 'ATTIVO' THEN v_data_emissione
                ELSE v_periodo_inizio
            END)
        GROUP BY cdf.IdContratto
    ),
    consumi_periodo AS (
        SELECT 
            ca.IdContratto,
            ca.IdContatore,
            COALESCE(SUM(COALESCE(lc.vol_rettificato, lc.vol_consumato)), 0) AS consumo_totale
        FROM contatori_attivi ca
        INNER JOIN LETTURA_CONSUMI lc ON ca.IdContatore = lc.IdContatore
        WHERE lc.data_rif >= v_periodo_inizio
          AND lc.data_rif <= v_periodo_fine
        GROUP BY ca.IdContratto, ca.IdContatore
    ),
    costi_area AS (
        SELECT 
            cdf.IdContratto,
            ag.costo_acqua
        FROM contratti_da_fatturare cdf
        INNER JOIN AREA_GEOGRAFICA ag ON cdf.IdArea_fornitura = ag.IdArea
    )
    SELECT 
        cdf.IdContratto,
        ROUND(
            COALESCE(cp.consumo_totale, 0) * tm.tariffa_applicata * ca.costo_acqua,
            2
        ) AS importo_fattura,
        0.00 AS sovrapprezzo,
        0.00 AS sconto,
        v_data_emissione,
        v_data_scadenza,
        v_periodo_inizio,
        v_periodo_fine
    FROM contratti_da_fatturare cdf
    INNER JOIN tariffe_minime tm ON cdf.IdContratto = tm.IdContratto
    INNER JOIN costi_area ca ON cdf.IdContratto = ca.IdContratto
    LEFT JOIN consumi_periodo cp ON cdf.IdContratto = cp.IdContratto
    WHERE tm.tariffa_applicata IS NOT NULL
      AND ca.costo_acqua IS NOT NULL;
        
END$$

DELIMITER ;