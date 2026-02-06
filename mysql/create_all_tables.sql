USE unime_acque;

--UTENTE
CREATE OR REPLACE TABLE UTENTE (
    IdUtente INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(50) NOT NULL,
    cognome VARCHAR(50) NOT NULL,
    codice_fiscale VARCHAR(16) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    telefono VARCHAR(20),
    ragione_sociale VARCHAR(100),
    data_nascita DATE NOT NULL,
    ruolo VARCHAR(30) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    data_creazione TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT chk_cf CHECK (LENGTH(codice_fiscale) = 16) OR LENGTH(codice_fiscale) = 11),
    CONSTRAINT chk_email CHECK (email REGEXP '^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\\.[A-Za-z]{2,}$'),
    CONSTRAINT chk_ruolo CHECK (ruolo IN ('TECNICO', 'AMMINISTRATORE', 'CLIENTE', 'SYSADMIN'))
);

CREATE INDEX idx_utente_ruolo ON UTENTE(ruolo);
--aggiungo questo indice perchè il check sul ruolo verrà fatto ad ogni login. I campi UNIQUE e PK sono già indicizzati


--TARIFFA
CREATE OR REPLACE TABLE TARIFFA (
    IdTariffa INT AUTO_INCREMENT PRIMARY KEY,
    tariffa_applicata DECIMAL(6,4) NOT NULL,
    nome_tariffa VARCHAR(50) NOT NULL UNIQUE,
    descrizione VARCHAR(200));
    
--LOCALITA
CREATE OR REPLACE TABLE LOCALITA (
    CAP VARCHAR(5) PRIMARY KEY,
    citta VARCHAR(50) NOT NULL,
    provincia VARCHAR(30) NOT NULL,
    CONSTRAINT chk_cap CHECK (CAP REGEXP '^[0-9]{5}$')
);

--AREA_GEOGRAFICA
CREATE TABLE AREA_GEOGRAFICA (
    IdArea INT AUTO_INCREMENT PRIMARY KEY,
    nome_area VARCHAR(100) NOT NULL UNIQUE,
    CAP VARCHAR(5) NOT NULL,
    costo_acqua DECIMAL(6,4) NOT NULL,
    CONSTRAINT fk_area_localita FOREIGN KEY (CAP) 
        REFERENCES LOCALITA(CAP) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT chk_costo_acqua CHECK (costo_acqua > 0)
);
--chiave esterna sul CAP della tabella localita, ON UPDATE CASCADE significa che se modifico il CAP in località, si propagherà anche qui.
--ON DELETE RESTRICT invece significa che impedisce la cancellazione di record che hanno chiavi esterne presenti in altre tabelle


--CONTRATTO
CREATE OR REPLACE TABLE CONTRATTO (
    IdContratto INT AUTO_INCREMENT PRIMARY KEY,
    IdUtente INT NOT NULL,
    data_stipula DATE NOT NULL,
    data_inizio_validita DATE NOT NULL,
    data_fine_validita DATE,
    stato_contratto varchar(10) NOT NULL DEFAULT 'ATTIVO',
    tipo_contratto varchar(10) NOT NULL,
    CONSTRAINT fk_contratto_utente FOREIGN KEY (IdUtente) 
        REFERENCES UTENTE(IdUtente) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT chk_date_contratto CHECK (data_inizio_validita >= data_stipula),
    CONSTRAINT chk_fine_validita CHECK (data_fine_validita IS NULL OR data_fine_validita >= data_inizio_validita)
);

CREATE INDEX idx_contratto_utente ON CONTRATTO(IdUtente);
CREATE INDEX idx_contratto_stato ON CONTRATTO(stato_contratto);
CREATE INDEX idx_contratto_tipo ON CONTRATTO(tipo_contratto);
CREATE INDEX idx_contratto_date ON CONTRATTO(data_inizio_validita, data_fine_validita);



--FORNITURA
CREATE OR REPLACE TABLE FORNITURA (
    IdFornitura INT AUTO_INCREMENT PRIMARY KEY,
    stato_fornitura varchar(30) NOT NULL DEFAULT 'IN ATTESA DI ATTIVAZIONE',
    IdContratto INT NOT NULL,
    IdArea_fornitura INT NOT NULL,
    data_attivazione DATE, 
    data_disattivazione DATE,
    indirizzo_fornitura VARCHAR(200) NOT NULL,
    CONSTRAINT fk_fornitura_contratto FOREIGN KEY (IdContratto) 
        REFERENCES CONTRATTO(IdContratto) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_fornitura_area FOREIGN KEY (IdArea_fornitura) 
        REFERENCES AREA_GEOGRAFICA(IdArea) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT chk_date_fornitura CHECK (data_disattivazione IS NULL OR data_disattivazione >= data_attivazione)
);


CREATE INDEX idx_fornitura_stato ON FORNITURA(stato_fornitura);
--data_attivazione nullable perchè attivata col trigger


--ABBINAMENTO_TARIFFA
CREATE OR REPLACE TABLE ABBINAMENTO_TARIFFA (
    IdContratto INT NOT NULL,
    IdTariffa INT NOT NULL,
    data_inizio DATE NOT NULL,
    data_fine DATE,
    PRIMARY KEY (IdContratto, IdTariffa),
    CONSTRAINT fk_abbinamento_contratto FOREIGN KEY (IdContratto) 
        REFERENCES CONTRATTO(IdContratto) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_abbinamento_tariffa FOREIGN KEY (IdTariffa) 
        REFERENCES TARIFFA(IdTariffa) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT chk_date_abbinamento CHECK (data_fine IS NULL OR data_fine >= data_inizio)
);




--CARTA_DI_CREDITO
CREATE OR REPLACE TABLE CARTA_DI_CREDITO (
    IdCarta INT AUTO_INCREMENT PRIMARY KEY,
    IdUtente INT NOT NULL,
    numero_carta VARCHAR(16) NOT NULL UNIQUE,
    intestatario VARCHAR(30) NOT NULL,
    CVV VARCHAR(3) NOT NULL,
    data_scadenza DATE NOT NULL,
    data_registrazione TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_carta_utente FOREIGN KEY (IdUtente) 
        REFERENCES UTENTE(IdUtente) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT chk_numero_carta CHECK (numero_carta REGEXP '^[0-9]{16}$'),
    CONSTRAINT chk_cvv CHECK (CVV REGEXP '^[0-9]{3}$'),
    CONSTRAINT chk_data_scadenza CHECK (data_scadenza > data_registrazione)
);


--CONTATORE
CREATE OR REPLACE TABLE CONTATORE (
    IdContatore INT AUTO_INCREMENT PRIMARY KEY,
    matricola_contatore VARCHAR(50) NOT NULL UNIQUE,
    stato_contatore VARCHAR(30) NOT NULL DEFAULT 'ATTIVO',
    marca_contatore VARCHAR(50) NOT NULL,
    IdFornitura INT NOT NULL,
    IdUtente_installatore INT,
    IdUtente_removitore INT,
    data_installazione TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    data_sostituzione DATE,
    data_dismissione DATE,
    note VARCHAR(250),
    sostituisce_IdContatore INT,
    CONSTRAINT fk_contatore_fornitura FOREIGN KEY (IdFornitura) 
        REFERENCES FORNITURA(IdFornitura) ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT fk_contatore_installatore FOREIGN KEY (IdUtente_installatore) 
        REFERENCES UTENTE(IdUtente) ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT fk_contatore_removitore FOREIGN KEY (IdUtente_removitore) 
        REFERENCES UTENTE(IdUtente) ON UPDATE CASCADE ON DELETE SET NULL
);

CREATE INDEX idx_contatore_stato ON CONTATORE(stato_contatore);
--ALTER TABLE CONTATORE ADD COLUMN 'sostituisce_IdContatore' INT;

--LETTURA_CONSUMI
CREATE OR REPLACE TABLE LETTURA_CONSUMI (
    IdContatore INT NOT NULL,
    data_rif DATE NOT NULL,
    vol_consumato INT(5) NOT NULL,
    vol_rettificato INT(5),
    data_rettifica TIMESTAMP,
    PRIMARY KEY (IdContatore, data_rif),
    CONSTRAINT fk_lettura_contatore FOREIGN KEY (IdContatore) 
        REFERENCES CONTATORE(IdContatore) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT chk_vol_consumato CHECK (vol_consumato >= 0),
    CONSTRAINT chk_vol_rettificato CHECK (vol_rettificato IS NULL OR vol_rettificato >= 0)
);


CREATE INDEX idx_lettura_data ON LETTURA_CONSUMI(data_rif);


--FATTURA
CREATE OR REPLACE TABLE FATTURA (
    IdFattura INT AUTO_INCREMENT PRIMARY KEY,
    IdContratto INT NOT NULL,
    importo_fattura DECIMAL(10,2) NOT NULL,
    sovrapprezzo DECIMAL(5,2) DEFAULT 0.00,
    sconto DECIMAL(5,2) DEFAULT 0.00,
    data_emissione DATE NOT NULL,
    data_scadenza DATE NOT NULL,
    data_pagamento DATE,
    IdCarta_pagamento INT,
    periodo_rif_inizio DATE NOT NULL,
    periodo_rif_fine DATE NOT NULL,
    CONSTRAINT fk_fattura_contratto FOREIGN KEY (IdContratto) 
        REFERENCES CONTRATTO(IdContratto) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_fattura_carta FOREIGN KEY (IdCarta_pagamento) 
        REFERENCES CARTA_DI_CREDITO(IdCarta) ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT chk_importo CHECK (importo_fattura >= 0),
    CONSTRAINT chk_sovrapprezzo CHECK (sovrapprezzo >= 0 AND sovrapprezzo <= 100),
    CONSTRAINT chk_sconto CHECK (sconto >= 0 AND sconto <= 100),
    CONSTRAINT chk_date_fattura CHECK (data_scadenza > data_emissione),
    CONSTRAINT chk_periodo CHECK (periodo_rif_fine >= periodo_rif_inizio)
) ;



CREATE INDEX idx_fattura_date ON FATTURA(data_emissione, data_scadenza);
CREATE INDEX idx_fattura_periodo ON FATTURA(periodo_rif_inizio, periodo_rif_fine);


--SEGNALAZIONE
CREATE OR REPLACE TABLE SEGNALAZIONE (
    IdSegnalazione INT AUTO_INCREMENT PRIMARY KEY,
    IdUtente_segnalante INT NOT NULL,
    IdUtente_presa_in_carico INT,
    motivo_richiesta VARCHAR(30) NOT NULL,
    contenuto_richiesta VARCHAR(1000) NOT NULL,
    data_apertura DATE DEFAULT CURRENT_DATE,
    data_chiusura DATE,
    CONSTRAINT fk_segnalazione_segnalante FOREIGN KEY (IdUtente_segnalante) 
        REFERENCES UTENTE(IdUtente) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_segnalazione_operatore FOREIGN KEY (IdUtente_presa_in_carico) 
        REFERENCES UTENTE(IdUtente) ON UPDATE CASCADE ON DELETE SET NULL
);

CREATE INDEX idx_fattura_periodo ON SEGNALAZIONE(data_apertura, data_chiusura);








