<?php
/**
 * UNIME-ACQUE - Configurazione Database
 * 
 * File contenente le credenziali di connessione al database MySQL.
 * ATTENZIONE: In produzione, questo file NON deve essere accessibile via web
 * e le credenziali dovrebbero essere gestite tramite variabili d'ambiente.
 * 
 * @author Enrico Celesti (460896)
 * @project UNIME-ACQUE
 */

// Impedisce l'accesso diretto al file
if (!defined('UNIME_ACQUE')) {
    die('Accesso non consentito.');
}

// Configurazione connessione database
define('DB_HOST', 'localhost');
define('DB_PORT', 3306);
define('DB_NAME', 'unime-acque');
define('DB_USER', 'myapplication');
define('DB_PASS', 'phpmysql');
define('DB_CHARSET', 'utf8mb4');

date_default_timezone_set('Europe/Rome');

/**
 * Crea e restituisce una connessione MySQLi al database.
 * Utilizza prepared statements per prevenire SQL injection.
 * 
 * @return mysqli|null Oggetto connessione o null in caso di errore
 */
function getDbConnection() {
    static $conn = null;
    
    // Riutilizza la connessione esistente (singleton pattern)
    if ($conn !== null && $conn->ping()) {
        return $conn;
    }
    
    // Crea nuova connessione
    $conn = mysqli_connect(
        DB_HOST,
        DB_USER,
        DB_PASS,
        DB_NAME,
        DB_PORT
    );
    
    // Verifica errori di connessione
    if (mysqli_connect_errno()) {
        error_log("Errore connessione DB: " . mysqli_connect_error());
        return null;
    }
    
    // Imposta charset per supporto caratteri speciali
    mysqli_set_charset($conn, DB_CHARSET);
    
    return $conn;
}

/**
 * Chiude la connessione al database.
 * 
 * @param mysqli $conn Oggetto connessione da chiudere
 * @return bool True se chiusura riuscita
 */
function closeDbConnection($conn) {
    if ($conn !== null) {
        return mysqli_close($conn);
    }
    return false;
}

/**
 * Esegue una query preparata con parametri.
 * Previene SQL injection utilizzando prepared statements.
 * 
 * @param string $query Query SQL con placeholder ?
 * @param string $types Tipi dei parametri (i=int, d=double, s=string, b=blob)
 * @param array $params Array di parametri da associare
 * @return mysqli_result|bool Risultato della query o false in caso di errore
 */
function executeQuery($query, $types = '', $params = []) {
    $conn = getDbConnection();
    
    if ($conn === null) {
        return false;
    }
    
    // Prepara lo statement
    $stmt = mysqli_prepare($conn, $query);
    
    if ($stmt === false) {
        error_log("Errore preparazione query: " . mysqli_error($conn));
        return false;
    }
    
    // Associa i parametri se presenti
    if (!empty($types) && !empty($params)) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    
    // Esegue la query
    if (!mysqli_stmt_execute($stmt)) {
        error_log("Errore esecuzione query: " . mysqli_stmt_error($stmt));
        mysqli_stmt_close($stmt);
        return false;
    }
    
    // Recupera il risultato per query SELECT
    $result = mysqli_stmt_get_result($stmt);
    
    // Se non è una SELECT, restituisce true per successo
    if ($result === false && mysqli_stmt_errno($stmt) === 0) {
        $affectedRows = mysqli_stmt_affected_rows($stmt);
        mysqli_stmt_close($stmt);
        return $affectedRows;
    }
    
    mysqli_stmt_close($stmt);
    return $result;
}
?>