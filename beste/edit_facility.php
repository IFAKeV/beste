<?php
// edit_facility.php
include 'db.php';

$facilityID = $_GET['id'] ?? null;

if (!$facilityID) {
    echo "Keine Einrichtung angegeben.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $locationID = $_POST['LocationID'] ?? '';
    $short = $_POST['Short'] ?? '';
    $facilityName = $_POST['Facility'] ?? '';
    $long = $_POST['Long'] ?? '';
    $sortedLong = $_POST['SortedLong'] ?? '';
    $phone = $_POST['Phone'] ?? '';
    $mobile = $_POST['Mobile'] ?? '';
    $fax = $_POST['Fax'] ?? '';
    $mail = $_POST['Mail'] ?? '';
    $url = $_POST['URL'] ?? '';
    $departmentID = $_POST['DepartmentID'] ?? '';

    // Eingabedaten validieren
    if (empty($locationID) || empty($short) || empty($facilityName) || empty($long) || empty($sortedLong)) {
        $error = "Bitte füllen Sie alle Pflichtfelder aus.";
    } else {
        try {
            $stmt = $db->prepare("
                UPDATE Facilities 
                SET LocationID = ?, Short = ?, Facility = ?, Long = ?, SortedLong = ?, Phone = ?, Mobile = ?, Fax = ?, Mail = ?, URL = ?, DepartmentID = ?
                WHERE FacilityID = ?
            ");
            $stmt->execute([$locationID, $short, $facilityName, $long, $sortedLong, $phone, $mobile, $fax, $mail, $url, $departmentID, $facilityID]);
            header('Location: facilities.php');
            exit;
        } catch (PDOException $e) {
            $error = "Fehler beim Aktualisieren der Einrichtung: " . $e->getMessage();
        }
    }
} else {
    // Einrichtung und Standorte abrufen
    try {
        // Einrichtung abrufen
        $stmt = $db->prepare("SELECT * FROM Facilities WHERE FacilityID = ?");
        $stmt->execute([$facilityID]);
        $facility = $stmt->fetch();

        if (!$facility) {
            echo "Einrichtung nicht gefunden.";
            exit;
        }

        // Standorte abrufen
        $stmt = $db->query('SELECT LocationID, Location FROM Locations');
        $locations = $stmt->fetchAll();
        // Fachbereiche abrufen
        $stmt = $db->query('SELECT DepartmentID, Department FROM Departments ORDER BY SortedLong COLLATE NOCASE ASC');
        $departments = $stmt->fetchAll();
    } catch (PDOException $e) {
        echo "Fehler beim Abrufen der Einrichtung oder Standorte: " . $e->getMessage();
        exit;
    }
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Einrichtung bearbeiten</title>
    </head>
    <body>
        <h1>Einrichtung bearbeiten</h1>
        <?php if (!empty($error)): ?>
            <p style="color: red;"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
        <form method="post" action="edit_facility.php?id=<?= urlencode($facilityID) ?>">
            <label for="LocationID">Standort:</label><br>
            <select name="LocationID" id="LocationID" required>
                <option value="">-- Standort auswählen --</option>
                <?php foreach ($locations as $location): ?>
                    <option value="<?= htmlspecialchars($location['LocationID']) ?>" <?= $location['LocationID'] == $facility['LocationID'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($location['Location']) ?>
                    </option>
                <?php endforeach; ?>
            </select><br><br>

            <!-- Restliche Felder -->
            <label for="Short">Kurzname:</label><br>
            <input type="text" name="Short" id="Short" value="<?= htmlspecialchars($facility['Short']) ?>" required><br><br>

            <label for="Facility">Einrichtungsname:</label><br>
            <input type="text" name="Facility" id="Facility" value="<?= htmlspecialchars($facility['Facility']) ?>" required><br><br>

            <label for="Long">Langname:</label><br>
            <input type="text" name="Long" id="Long" value="<?= htmlspecialchars($facility['Long']) ?>" required><br><br>

            <label for="SortedLong">Sortierung:</label><br>
            <input type="text" name="SortedLong" id="SortedLong" value="<?= htmlspecialchars($facility['SortedLong']) ?>" required><br><br>

            <label for="DepartmentID">Fachbereich:</label><br>
            <select name="DepartmentID" id="DepartmentID" required>
                <option value="">-- Fachbereich auswählen --</option>
                <?php foreach ($departments as $department): ?>
                    <option value="<?= htmlspecialchars($department['DepartmentID']) ?>" <?= $department['DepartmentID'] == $facility['DepartmentID'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($department['Department']) ?>
                    </option>
                <?php endforeach; ?>
            </select><br><br>

            <label for="Phone">Telefon:</label><br>
            <input type="text" name="Phone" id="Phone" value="<?= htmlspecialchars($facility['Phone']) ?>" ><br><br>

            <label for="Mobile">Mobil:</label><br>
            <input type="text" name="Mobile" id="Mobile" value="<?= htmlspecialchars($facility['Mobile']) ?>" ><br><br>

            <label for="Fax">Fax:</label><br>
            <input type="text" name="Fax" id="Fax" value="<?= htmlspecialchars($facility['Fax']) ?>" ><br><br>

            <label for="Mail">E-Mail:</label><br>
            <input type="email" name="Mail" id="Mail" value="<?= htmlspecialchars($facility['Mail']) ?>" ><br><br>

            <label for="URL">URL:</label><br>
            <input type="url" name="URL" id="URL" value="<?= htmlspecialchars($facility['URL']) ?>" ><br><br>

            <input type="submit" value="Aktualisieren">
        </form>
        <p><a href="facilities.php">Zurück zur Einrichtungsübersicht</a></p>
    </body>
    </html>
    <?php
}
?>
