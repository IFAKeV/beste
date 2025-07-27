<?php
// add_facility.php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $locationID = $_POST['LocationID'] ?? '';
    $short = $_POST['Short'] ?? '';
    $facility = $_POST['Facility'] ?? '';
    $long = $_POST['Long'] ?? '';
    $sortedLong = $_POST['SortedLong'] ?? '';
    $phone = $_POST['Phone'] ?? '';
    $mobile = $_POST['Mobile'] ?? '';
    $fax = $_POST['Fax'] ?? '';
    $mail = $_POST['Mail'] ?? '';
    $url = $_POST['URL'] ?? '';

    // Eingabedaten validieren
    if (empty($locationID) || empty($short) || empty($facility) || empty($long) || empty($sortedLong)) {
        $error = "Bitte füllen Sie alle Pflichtfelder aus.";
    } else {
        try {
            $stmt = $db->prepare("
                INSERT INTO Facilities (LocationID, Short, Facility, Long, SortedLong, Phone, Mobile, Fax, Mail, URL) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$locationID, $short, $facility, $long, $sortedLong, $phone, $mobile, $fax, $mail, $url]);
            header('Location: facilities.php');
            exit;
        } catch (PDOException $e) {
            $error = "Fehler beim Hinzufügen der Einrichtung: " . $e->getMessage();
        }
    }
} else {
    // Standorte abrufen, um die Select-Liste zu füllen
    try {
        $stmt = $db->query('SELECT LocationID, Location FROM Locations ORDER BY SortedLong COLLATE NOCASE ASC');
        $locations = $stmt->fetchAll();
    } catch (PDOException $e) {
        echo "Fehler beim Abrufen der Standorte: " . $e->getMessage();
        exit;
    }
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Neue Einrichtung hinzufügen</title>
    </head>
    <body>
        <h1>Neue Einrichtung hinzufügen</h1>
        <form method="post" action="add_facility.php">
            <label for="LocationID">Standort:</label><br>
            <select name="LocationID" id="LocationID" required>
                <option value="">-- Standort auswählen --</option>
                <?php foreach ($locations as $location): ?>
                    <option value="<?= htmlspecialchars($location['LocationID']) ?>"><?= htmlspecialchars($location['Location']) ?></option>
                <?php endforeach; ?>
            </select><br><br>

            <label for="Short">Kurzname:</label><br>
            <input type="text" name="Short" id="Short" required><br><br>

            <label for="Facility">Einrichtungsname:</label><br>
            <input type="text" name="Facility" id="Facility" required><br><br>

            <label for="Long">Langname:</label><br>
            <input type="text" name="Long" id="Long" required><br><br>

            <label for="SortedLong">Sortierung:</label><br>
            <input type="text" name="SortedLong" id="SortedLong" required><br><br>

            <label for="Phone">Telefon:</label><br>
            <input type="text" name="Phone" id="Phone"><br><br>

            <label for="Mobile">Mobil:</label><br>
            <input type="text" name="Mobile" id="Mobile"><br><br>

            <label for="Fax">Fax:</label><br>
            <input type="text" name="Fax" id="Fax"><br><br>

            <label for="Mail">E-Mail:</label><br>
            <input type="email" name="Mail" id="Mail"><br><br>

            <label for="URL">URL:</label><br>
            <input type="url" name="URL" id="URL"><br><br>

            <input type="submit" value="Speichern">
        </form>
        <p><a href="facilities.php">Zurück zur Einrichtungsübersicht</a></p>
    </body>
    </html>
    <?php
}
?>