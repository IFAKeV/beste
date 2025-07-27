<?php
// add_employee.php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Formularverarbeitung
    $firstName = $_POST['FirstName'] ?? '';
    $lastName = $_POST['LastName'] ?? '';
    $sortedLastName = $_POST['SortedLastName'] ?? '';
    $phone = $_POST['Phone'] ?? '';
    $mobile = $_POST['Mobile'] ?? '';
    $mail = $_POST['Mail'] ?? '';
    $facilityData = $_POST['Facilities'] ?? [];
    $languagesData = $_POST['Languages'] ?? [];

    // Eingabedaten validieren
    if (empty($firstName) || empty($lastName) || empty($sortedLastName)) {
        $error = "Bitte füllen Sie alle Pflichtfelder aus.";
    } else {
        try {
            // Beginne Transaktion
            $db->beginTransaction();

            // Mitarbeiter in Employees-Tabelle einfügen
            $stmt = $db->prepare("
                INSERT INTO Employees (FirstName, LastName, SortedLastName, Phone, Mobile, Mail)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$firstName, $lastName, $sortedLastName, $phone, $mobile, $mail]);
            $employeeID = $db->lastInsertId();

            // Zuordnungen zu Einrichtungen hinzufügen
            foreach ($facilityData as $facilityID => $facilityInfo) {
                if (isset($facilityInfo['selected'])) {
                    $RoleID = isset($facilityInfo['RoleID']) ? (int)$facilityInfo['RoleID'] : 1; // Standardwert 1 für 'Teammitglied'
                    $stmt = $db->prepare("
                        INSERT INTO FacilityLinks (EmployeeID, FacilityID, RoleID)
                        VALUES (?, ?, ?)
                    ");
                    $stmt->execute([$employeeID, $facilityID, $RoleID]);
                }
            }

            // Zuordnungen zu Sprachen hinzufügen
            foreach ($languagesData as $languageID => $languageInfo) {
                if (isset($languageInfo['selected'])) {
                    $skillLevel = $languageInfo['SkillLevel'] ?? '';
                    $zertifiziert = isset($languageInfo['zertifiziert']) ? 1 : 0;
                    $stmt = $db->prepare("
                        INSERT INTO LanguageLinks (EmployeeID, LanguageID, SkillLevel, zertifiziert)
                        VALUES (?, ?, ?, ?)
                    ");
                    $stmt->execute([$employeeID, $languageID, $skillLevel, $zertifiziert]);
                }
            }

            // Transaktion abschließen
            $db->commit();

            header('Location: employees.php');
            exit;
        } catch (PDOException $e) {
            // Bei Fehler Transaktion zurückrollen
            $db->rollBack();
            $error = "Fehler beim Hinzufügen des Mitarbeiters: " . $e->getMessage();
        }
    }
} else {
    // Formular anzeigen
    try {
        // Einrichtungen abrufen
        $stmt = $db->query("SELECT FacilityID, Facility FROM Facilities ORDER BY SortedLong ASC");
        $facilities = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Rollen abrufen
        $stmt = $db->query("SELECT RoleID, RoleName FROM Roles");
        $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Sprachen abrufen
        $stmt = $db->query("SELECT LanguageID, LanguageName FROM Languages ORDER BY LanguageName ASC");
        $languages = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Mögliche Skill Levels
        $skillLevels = ['A1', 'A2', 'B1', 'B2', 'C1', 'C2'];
    } catch (PDOException $e) {
        echo "Fehler beim Abrufen der Daten: " . $e->getMessage();
        exit;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Neue:n Mitarbeiter:in hinzufügen</title>
</head>
<body>
    <h1>Neue:n Mitarbeiter:in hinzufügen</h1>
    <?php if (!empty($error)): ?>
        <p style="color: red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <form method="post" action="add_employee.php">
        <label for="FirstName">Vorname:</label><br>
        <input type="text" name="FirstName" id="FirstName" required><br><br>

        <label for="LastName">Nachname:</label><br>
        <input type="text" name="LastName" id="LastName" required><br><br>

        <label for="SortedLastName">Sortierter Nachname:</label><br>
        <input type="text" name="SortedLastName" id="SortedLastName" required><br><br>

        <label for="Phone">Telefon:</label><br>
        <input type="text" name="Phone" id="Phone"><br><br>

        <label for="Mobile">Mobil:</label><br>
        <input type="text" name="Mobile" id="Mobile"><br><br>

        <label for="Mail">E-Mail:</label><br>
        <input type="email" name="Mail" id="Mail"><br><br>

        <!-- Einrichtungen und Rollen auswählen -->
        <label>Einrichtungen und Rollen:</label><br>
        <table>
            <tr>
                <th>Auswählen</th>
                <th>Einrichtung</th>
                <th>Rolle</th>
            </tr>
            <?php foreach ($facilities as $facility): ?>
            <tr>
                <td>
                    <input type="checkbox" name="Facilities[<?= htmlspecialchars($facility['FacilityID']) ?>][selected]" value="1">
                </td>
                <td>
                    <?= htmlspecialchars($facility['Facility']) ?>
                </td>
                <td>
                    <select name="Facilities[<?= htmlspecialchars($facility['FacilityID']) ?>][RoleID]">
                        <?php foreach ($roles as $role): ?>
                            <option value="<?= htmlspecialchars($role['RoleID']) ?>"><?= htmlspecialchars($role['RoleName']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
        <br>

        <!-- Sprachen hinzufügen -->
        <label>Sprachen:</label><br>
        <?php foreach ($languages as $language): ?>
            <input type="checkbox" name="Languages[<?= htmlspecialchars($language['LanguageID']) ?>][selected]" value="1">
            <?= htmlspecialchars($language['LanguageName']) ?>
            <!-- Skill Level auswählen -->
            <select name="Languages[<?= htmlspecialchars($language['LanguageID']) ?>][SkillLevel]">
                <?php foreach ($skillLevels as $level): ?>
                    <option value="<?= htmlspecialchars($level) ?>"><?= htmlspecialchars($level) ?></option>
                <?php endforeach; ?>
            </select>
            Zertifiziert:
            <input type="checkbox" name="Languages[<?= htmlspecialchars($language['LanguageID']) ?>][zertifiziert]" value="1">
            <br>
        <?php endforeach; ?>
        <br>

        <input type="submit" value="Speichern">
    </form>
    <p><a href="employees.php">Zurück zur Mitarbeiterliste</a></p>
</body>
</html>