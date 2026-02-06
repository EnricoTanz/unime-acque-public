# UNIME-ACQUE

Sistema di gestione delle forniture idriche per la città di Messina.

Progetto d'esame per il corso di **Basi di Dati e Web** — Università degli Studi di Messina, A.A. 2025/2026.

**Studente:** Enrico Celesti (Matricola 460896)

---

## Descrizione

UNIME-ACQUE è un'applicazione web a tre livelli (presentazione, logica applicativa, dati) che simula il portale di un'azienda idrica. Il sistema permette a cittadini e aziende di gestire i propri contratti di fornitura idrica, consultare i consumi, visualizzare e pagare le fatture, e aprire segnalazioni.

Il database è dimensionato per gestire circa 95.000 utenze, 100.000 contratti, 36,5 milioni di letture annuali e 1,2 milioni di fatture su 10 anni.

## Tecnologie utilizzate

- **Frontend:** HTML5, CSS3, JavaScript
- **Backend:** PHP (mysqli)
- **Database:** MySQL / MariaDB
- **Ambiente di sviluppo:** XAMPP (Apache + PHP + MySQL)

## Ruoli utente

Il sistema prevede quattro ruoli con funzionalità distinte:

- **Cliente** — Visualizza contratti e consumi, paga le fatture, apre segnalazioni, gestisce le proprie carte di credito.
- **Amministratore** — Registra nuovi clienti, crea contratti, gestisce forniture e tariffe, genera fatture, gestisce segnalazioni.
- **Tecnico** — Installa e sostituisce contatori, registra e rettifica le letture dei consumi.
- **SysAdmin** — Crea utenze per amministratori e tecnici, monitora il database.

## Installazione

### Prerequisiti

- XAMPP installato e funzionante (Apache + MySQL)

5. Accedi all'applicazione da browser: `http://localhost/unime-acque`

## Struttura del progetto

```
unime-acque/
├── auth/           → Login, logout, reset password
├── config/         → Configurazione connessione al database
├── includes/       → Funzioni comuni e gestione sessioni
├── dashboard/      → Dashboard e moduli per ogni ruolo
├── pages/          → Pagine pubbliche del sito (chi siamo, contatti, ecc.)
├── css/            → Fogli di stile
├── mysql/          → Script SQL (tabelle, trigger, procedure, dati di test)
└── index.php       → Homepage
```

## Database

Lo schema è composto da 12 tabelle con vincoli di integrità referenziale (foreign key con CASCADE/RESTRICT), CHECK constraint, indici sulle colonne più utilizzate, trigger e stored procedure per automatizzare parte della logica di business.

Le tabelle principali sono: UTENTE, CONTRATTO, FORNITURA, CONTATORE, LETTURA_CONSUMI, FATTURA, PAGAMENTO, SEGNALAZIONE, TARIFFA, AREA_GEOGRAFICA, LOCALITA, CARTA_CREDITO.

## Sicurezza

- Prepared statement (mysqli) per tutte le query — prevenzione SQL injection
- Password hashate con `password_hash()` (bcrypt)
- Token CSRF su tutti i form
- Validazione e sanitizzazione input con `filter_var()`, `checkdate()`, `htmlspecialchars()`
- Sessioni configurate con `httponly`, `use_only_cookies`, `cookie_samesite`
- Controllo del ruolo su ogni pagina protetta

## Licenza

Progetto accademico — tutti i diritti riservati.

© 2026 Enrico Celesti
