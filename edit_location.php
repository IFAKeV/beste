<?php
include 'db.php';

$locationID = $_GET['id'] ?? null;

if (!$locationID) {
    echo "Kein Standort angegeben.";
    exit;
}


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $location = $_POST['Location'] ?? '';
    $sortedLong = $_POST['SortedLong'] ?? '';
    $street = $_POST['Street'] ?? '';
    $zip = $_POST['ZIP'] ?? '';
    $town = $_POST['Town'] ?? '';

    // Eingabedaten validieren
    if (empty($location) || empty($street) || empty($zip) || empty($town)) {
        $error = "Bitte füllen Sie alle Pflichtfelder aus.";
    } else {
        try {
            $stmt = $db->prepare("UPDATE Locations SET Location = ?, SortedLong = ?, Street = ?, ZIP = ?, Town = ? WHERE LocationID = ?");
            $stmt->execute([$location, $sortedLong, $street, $zip, $town, $locationID]);
            header('Location: locations.php');
            exit;
        } catch (PDOException $e) {
            $error = "Fehler beim Aktualisieren des Standorts: " . $e->getMessage();
        }
    }
} else {
    // Standortdaten abrufen
    try {
        $stmt = $db->prepare("SELECT * FROM Locations WHERE LocationID = ?");
        $stmt->execute([$locationID]);
        $location = $stmt->fetch();

        if (!$location) {
            echo "Standort nicht gefunden.";
            exit;
        }
    } catch (PDOException $e) {
        echo "Fehler beim Abrufen des Standorts: " . $e->getMessage();
        exit;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Standort bearbeiten</title>
</head>
<body>
	<?php if (!empty($error)): ?>
    <p style="color: red;"><?= htmlspecialchars($error) ?></p>
	<?php endif; ?>
    <h1>Standort bearbeiten</h1>
    <form method="post" action="edit_location.php?id=<?= urlencode($locationID) ?>">
        <label for="Location">Bezeichnung:</label><br>
        <input type="text" name="Location" id="Location" value="<?= htmlspecialchars($location['Location']) ?>" required><br><br>

        <label for="SortedLong">Sortierung:</label><br>
        <input type="text" name="SortedLong" id="SortedLong" value="<?= htmlspecialchars($location['SortedLong']) ?>"><br><br>

        <label for="Street">Straße:</label><br>
        <input type="text" name="Street" id="Street" value="<?= htmlspecialchars($location['Street']) ?>" required><br><br>

        <label for="ZIP">PLZ:</label><br>
        <input type="text" name="ZIP" id="ZIP" value="<?= htmlspecialchars($location['ZIP']) ?>" required><br><br>

        <label for="Town">Stadt:</label><br>
        <input type="text" name="Town" id="Town" value="<?= htmlspecialchars($location['Town']) ?>" required><br><br>

        <input type="submit" value="Aktualisieren">
    </form>
    <p><a href="locations.php">Zurück zur Standortliste</a></p>
</body>
</html>