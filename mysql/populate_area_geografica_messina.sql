## ============================================================================
## UNIME-ACQUE - Popolamento AREA_GEOGRAFICA Provincia di Messina
## 
## Aree geografiche con CAP corretti e costi acqua realistici
## Costo acqua: 0.90€ - 2.50€ per m³ (media Italia: 1.37€/m³)
## 
## @author Enrico Celesti (460896)
## ============================================================================

USE unime_acque;

## Pulizia (solo se necessario)
## DELETE FROM AREA_GEOGRAFICA;

## ============================================================================
## MESSINA CITTÀ - Diverse zone (CAP 98100)
## ============================================================================

INSERT INTO AREA_GEOGRAFICA (nome_area, CAP, costo_acqua) VALUES
('Via Garibaldi', '98100', 2.10),
('Piazza Duomo', '98100', 2.15),
('Via Primo Settembre', '98100', 2.10),
('Corso Cavour', '98100', 2.12),
('Via XXIV Maggio', '98100', 2.08),
('Via La Farina', '98100', 2.10),
('Piazza Cairoli', '98100', 2.08),
('Via Ferdinando Stagno d\'Alcontres', '98100', 1.95),
('Piazza Pugliatti', '98100', 1.95),
('Via Concezione', '98100', 1.88),
('Viale Annunziata', '98100', 1.92),
('Via Catania', '98100', 1.75),
('Via Bonino', '98100', 1.72),
('Viale Regina Elena', '98100', 1.80),
('Via Santa Cecilia', '98100', 1.70),
('Tremestieri Centro', '98100', 1.60),
('Via Comunale Santo', '98100', 1.58),
('Mili San Pietro', '98100', 1.55),
('Via Consolare Valeria', '98100', 1.65),
('Via Circuito Torre Faro', '98100', 1.95),
('Ganzirri Centro', '98100', 1.92),
('Capo Peloro', '98100', 1.98),
('Via Lago Piccolo', '98100', 1.90),
('Camaro Superiore', '98100', 1.45),
('Briga Superiore', '98100', 1.42),
('Faro Superiore', '98100', 1.48),
('Castanea delle Furie', '98100', 1.43),
('Spartà', '98100', 1.47),
('Pezzolo', '98100', 1.41);

## ============================================================================
## TAORMINA - Zona Turistica Premium (CAP 98039)
## ============================================================================

INSERT INTO AREA_GEOGRAFICA (nome_area, CAP, costo_acqua) VALUES
('Corso Umberto I', '98039', 2.20),
('Via Teatro Greco', '98039', 2.25),
('Piazza IX Aprile', '98039', 2.22),
('Via Pirandello', '98039', 2.18),
('Via Bagnoli Croci', '98039', 2.15),
('Mazzarò', '98039', 2.12),
('Isola Bella', '98039', 2.10);

## ============================================================================
## MILAZZO - Zona Portuale/Industriale (CAP 98057)
## ============================================================================

INSERT INTO AREA_GEOGRAFICA (nome_area, CAP, costo_acqua) VALUES
('Centro Storico Milazzo', '98057', 1.70),
('Via Lungomare Garibaldi', '98057', 1.75),
('Zona Porto', '98057', 1.65),
('Via Cristoforo Colombo', '98057', 1.68),
('Zona Industriale Milazzo', '98057', 1.60),
('Vaccarella', '98057', 1.62),
('Borgo Antico', '98057', 1.73);

## ============================================================================
## BARCELLONA POZZO DI GOTTO (CAP 98051)
## ============================================================================

INSERT INTO AREA_GEOGRAFICA (nome_area, CAP, costo_acqua) VALUES
('Via Garibaldi Barcellona', '98051', 1.55),
('Piazza San Giovanni', '98051', 1.58),
('Via Roma Barcellona', '98051', 1.56),
('Zona Industriale Barcellona', '98051', 1.50),
('Pozzo di Gotto Centro', '98051', 1.54),
('Via Kennedy', '98051', 1.52);

## ============================================================================
## PATTI (CAP 98058)
## ============================================================================

INSERT INTO AREA_GEOGRAFICA (nome_area, CAP, costo_acqua) VALUES
('Centro Storico Patti', '98058', 1.60),
('Patti Marina', '98058', 1.65),
('Via Nazario Sauro', '98058', 1.62),
('Zona Industriale Patti', '98058', 1.55),
('Tindari', '98058', 1.68);

## ============================================================================
## CAPO D'ORLANDO (CAP 98067)
## ============================================================================

INSERT INTO AREA_GEOGRAFICA (nome_area, CAP, costo_acqua) VALUES
('Via Libertà Capo d\'Orlando', '98067', 1.80),
('Lungomare Andrea Doria', '98067', 1.85),
('Centro Storico Capo d\'Orlando', '98067', 1.78),
('San Gregorio', '98067', 1.75),
('Via Consolare Antica', '98067', 1.72);

## ============================================================================
## LIPARI - Isole Eolie (CAP 98055) - Costo Molto Alto per isolamento
## ============================================================================

INSERT INTO AREA_GEOGRAFICA (nome_area, CAP, costo_acqua) VALUES
('Centro Lipari', '98055', 2.40),
('Marina Corta', '98055', 2.45),
('Marina Lunga', '98055', 2.43),
('Canneto Lipari', '98055', 2.38),
('Acquacalda', '98055', 2.35),
('Quattropani', '98055', 2.30);

## ============================================================================
## GIARDINI NAXOS (CAP 98024) - Zona Turistica Balneare
## ============================================================================

INSERT INTO AREA_GEOGRAFICA (nome_area, CAP, costo_acqua) VALUES
('Via Naxos', '98024', 1.95),
('Lungomare Giardini Naxos', '98024', 2.00),
('Via Tysandros', '98024', 1.92),
('Recanati', '98024', 1.88),
('Schisò', '98024', 1.90);

## ============================================================================
## ALTRI COMUNI COSTIERI - Costo Medio
## ============================================================================

INSERT INTO AREA_GEOGRAFICA (nome_area, CAP, costo_acqua) VALUES
('Centro Santa Teresa di Riva', '98028', 1.65),
('Centro Roccalumera', '98027', 1.60),
('Centro Letojanni', '98037', 1.75),
('Centro Savoca', '98029', 1.45),
('Centro Forza d\'Agrò', '98060', 1.50),
('Centro Alì Terme', '98021', 1.55),
('Centro Nizza di Sicilia', '98026', 1.58),
('Centro Villafranca Tirrena', '98049', 1.52),
('Centro Santa Lucia del Mela', '98074', 1.48),
('Centro Pace del Mela', '98042', 1.50),
('Centro Rometta', '98043', 1.47),
('Centro Spadafora', '98041', 1.49),
('Centro Terme Vigliatore', '98050', 1.54),
('Centro Falcone', '98052', 1.51),
('Centro Oliveri', '98056', 1.53),
('Centro Gioiosa Marea', '98062', 1.63),
('Centro Brolo', '98069', 1.58);

## ============================================================================
## COMUNI NEBRODI - Zone Montane (Costo Basso)
## ============================================================================

INSERT INTO AREA_GEOGRAFICA (nome_area, CAP, costo_acqua) VALUES
('Centro Cesarò', '98033', 1.10),
('Centro Francavilla', '98034', 1.15),
('Centro San Fratello', '98094', 1.08),
('Centro Tortorici', '98121', 1.05),
('Centro Floresta', '98084', 0.95),
('Centro Alcara li Fusi', '98061', 1.12),
('Centro Novara di Sicilia', '98092', 1.18),
('Centro Montalbano Elicona', '98073', 1.20),
('Centro Basicò', '98082', 1.00),
('Centro Tripi', '98054', 1.03),
('Centro Capizzi', '98063', 1.08),
('Centro Caronia', '98072', 1.06),
('Centro Galati Mamertino', '98086', 1.10),
('Centro Longi', '98087', 1.04),
('Centro Malvagna', '98088', 1.07),
('Centro Mirto', '98089', 1.09),
('Centro Reitano', '98093', 1.05),
('Centro San Marco d\'Alunzio', '98095', 1.11),
('Centro San Piero Patti', '98096', 1.13),
('Centro Ucria', '98123', 1.02);

## ============================================================================
## COMUNI PELORITANI E ALTRI (Costo Medio-Basso)
## ============================================================================

INSERT INTO AREA_GEOGRAFICA (nome_area, CAP, costo_acqua) VALUES
('Centro Castelmola', '98032', 1.35),
('Centro Gaggi', '98035', 1.32),
('Centro Graniti', '98036', 1.30),
('Centro Mojo Alcantara', '98038', 1.28),
('Centro Casalvecchio Siculo', '98030', 1.33),
('Centro Antillo', '98031', 1.25),
('Centro Limina', '98127', 1.23),
('Centro Mandanici', '98128', 1.26),
('Centro Roccafiorita', '98129', 1.22),
('Centro Itala', '98126', 1.27),
('Centro Scaletta Zanclea', '98048', 1.31),
('Centro Fiumedinisi', '98022', 1.29),
('Centro Alì', '98020', 1.34),
('Centro Furci Siculo', '98023', 1.36);

## ============================================================================
## ZONE RURALI E PERIFERICHE (Costo Basso)
## ============================================================================

INSERT INTO AREA_GEOGRAFICA (nome_area, CAP, costo_acqua) VALUES
('Zona Rurale Acquedolci', '98070', 1.25),
('Zona Rurale Santo Stefano', '98080', 1.22),
('Zona Rurale Torrenova', '98081', 1.20),
('Zona Rurale Capri Leone', '98078', 1.18),
('Zona Rurale Ficarra', '98075', 1.24),
('Zona Rurale Sinagra', '98077', 1.21);

## ============================================================================
## VERIFICA INSERIMENTI E STATISTICHE
## ============================================================================

SELECT '============================================' as '';
SELECT 'RIEPILOGO POPOLAMENTO AREA_GEOGRAFICA' as '';
SELECT '============================================' as '';
SELECT '';

SELECT COUNT(*) as 'Totale Aree Geografiche' FROM AREA_GEOGRAFICA;
SELECT '';

SELECT 
    MIN(costo_acqua) as 'Costo Min (€/m³)',
    MAX(costo_acqua) as 'Costo Max (€/m³)',
    ROUND(AVG(costo_acqua), 4) as 'Costo Medio (€/m³)'
FROM AREA_GEOGRAFICA;
SELECT '';

SELECT '##- Top 10 Aree Più Care ##-' as '';
SELECT nome_area, l.citta, CONCAT(costo_acqua, ' €/m³') as Costo
FROM AREA_GEOGRAFICA ag
INNER JOIN LOCALITA l ON ag.CAP = l.CAP
ORDER BY costo_acqua DESC
LIMIT 10;
SELECT '';

SELECT '##- Top 10 Aree Più Economiche ##-' as '';
SELECT nome_area, l.citta, CONCAT(costo_acqua, ' €/m³') as Costo
FROM AREA_GEOGRAFICA ag
INNER JOIN LOCALITA l ON ag.CAP = l.CAP
ORDER BY costo_acqua ASC
LIMIT 10;
