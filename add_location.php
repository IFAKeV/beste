<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
// add_location.php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $location = $_POST['Location'] ?? '';
    $sortedLong = $_POST['SortedLong'] ?? '';
    $street = $_POST['Street'] ?? '';
    $zip = $_POST['ZIP'] ?? '';
    $town = $_POST['Town'] ?? '';
echo 'Hallo';
    // Eingabedaten validieren
    if (empty($location) || empty($sortedLong) || empty($street) || empty($zip) || empty($town)) {
        $error = "Bitte füllen Sie alle Pflichtfelder aus.";
    } else {
        try {
            $stmt = $db->prepare("INSERT INTO Locations (Location, SortedLong, Street, ZIP, Town) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$location, $sortedLong, $street, $zip, $town]);
            header('Location: locations.php');
            exit;
        } catch (PDOException $e) {
            $error = "Fehler beim Hinzufügen des Standorts: " . $e->getMessage();
            echo $error;
        }
    }
} else {
    // Formular anzeigen
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Neuen Standort hinzufügen</title>
    </head>
    <body>
        <h1>Neuen Standort hinzufügen</h1>
        <?php if (!empty($error)): ?>
            <p style="color: red;"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
        <form method="post" action="add_location.php">
            <label for="Location">Bezeichnung:</label><br>
            <input type="text" name="Location" id="Location" required><br><br>

            <label for="SortedLong">Sortierung:</label><br>
            <input type="text" name="SortedLong" id="SortedLong" required><br><br>

            <label for="Street">Straße:</label><br>
            <input type="text" name="Street" id="Street" required><br><br>

            <label for="ZIP">PLZ:</label><br>
            <input type="text" name="ZIP" id="ZIP" required><br><br>

            <label for="Town">Stadt:</label><br>
            <input type="text" name="Town" id="Town" required><br><br>

            <input type="submit" value="Speichern">
        </form>
        <p><a href="locations.php">Zurück zur Standortliste</a></p>
    </body>
    </html>
    <?php
}
?>