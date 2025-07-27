<?php
try {
    $db = new PDO('sqlite:ifak.db');
    // Fehlerberichterstattung aktivieren
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Stellen Sie sicher, dass die Ergebnismengen als assoziative Arrays zurückgegeben werden
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Datenbankverbindung fehlgeschlagen: " . $e->getMessage();
    exit;
}
?>