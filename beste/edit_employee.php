<?php
// edit_employee.php
include 'db.php';

$employeeID = $_GET['id'] ?? null;

if (!$employeeID) {
    echo "Keine Mitarbeiter:in angegeben.";
    exit;
}

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

            // Mitarbeiterdaten aktualisieren
            $stmt = $db->prepare("
                UPDATE Employees
                SET FirstName = ?, LastName = ?, SortedLastName = ?, Phone = ?, Mobile = ?, Mail = ?
                WHERE EmployeeID = ?
            ");
            $stmt->execute([$firstName, $lastName, $sortedLastName, $phone, $mobile, $mail, $employeeID]);

            // Bestehende Zuordnungen zu Einrichtungen löschen
            $stmt = $db->prepare("DELETE FROM FacilityLinks WHERE EmployeeID = ?");
            $stmt->execute([$employeeID]);

            // Neue Zuordnungen zu Einrichtungen hinzufügen
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

            // Bestehende Zuordnungen zu Sprachen löschen
            $stmt = $db->prepare("DELETE FROM LanguageLinks WHERE EmployeeID = ?");
            $stmt->execute([$employeeID]);

            // Neue Zuordnungen zu Sprachen hinzufügen
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
            $error = "Fehler beim Aktualisieren des Mitarbeiters: " . $e->getMessage();
        }
    }
} else {
    // Formulardaten abrufen
    try {
        // Mitarbeiterdaten abrufen
        $stmt = $db->prepare("SELECT * FROM Employees WHERE EmployeeID = ?");
        $stmt->execute([$employeeID]);
        $employee = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$employee) {
            echo "Mitarbeiter:in nicht gefunden.";
            exit;
        }

        // Einrichtungen abrufen
        $stmt = $db->query("SELECT FacilityID, Facility FROM Facilities ORDER BY SortedLong ASC");
        $facilities = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Rollen abrufen
        $stmt = $db->query("SELECT RoleID, RoleName FROM Roles");
        $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Zuweisungen des Mitarbeiters zu Einrichtungen und Rollen abrufen
        $stmt = $db->prepare("SELECT FacilityID, RoleID FROM FacilityLinks WHERE EmployeeID = ?");
        $stmt->execute([$employeeID]);
        $assignedFacilities = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Umwandeln in ein assoziatives Array für leichteren Zugriff
        $facilityAssignments = [];
        foreach ($assignedFacilities as $facility) {
            $facilityID = $facility['FacilityID'];
            $facilityAssignments[$facilityID] = $facility['RoleID'];
        }

        // Sprachen abrufen
        $stmt = $db->query("SELECT LanguageID, LanguageName FROM Languages ORDER BY LanguageName ASC");
        $languages = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Zuweisungen des Mitarbeiters zu Sprachen abrufen
        $stmt = $db->prepare("SELECT LanguageID, SkillLevel, zertifiziert FROM LanguageLinks WHERE EmployeeID = ?");
        $stmt->execute([$employeeID]);
        $assignedLanguages = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Umwandeln in ein assoziatives Array für leichteren Zugriff
        $languageAssignments = [];
        foreach ($assignedLanguages as $lang) {
            $languageID = $lang['LanguageID'];
            $languageAssignments[$languageID] = [
                'SkillLevel' => $lang['SkillLevel'],
                'zertifiziert' => $lang['zertifiziert']
            ];
        }

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
    <title>Mitarbeiter:in bearbeiten</title>
</head>
<body>
    <h1>Mitarbeiter:in bearbeiten</h1>
    <?php if (!empty($error)): ?>
        <p style="color: red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <form method="post" action="edit_employee.php?id=<?= urlencode($employeeID) ?>">
        <label for="FirstName">Vorname:</label><br>
        <input type="text" name="FirstName" id="FirstName" value="<?= htmlspecialchars($employee['FirstName']) ?>" required><br><br>

        <label for="LastName">Nachname:</label><br>
        <input type="text" name="LastName" id="LastName" value="<?= htmlspecialchars($employee['LastName']) ?>" required><br><br>

        <label for="SortedLastName">Sortierter Nachname:</label><br>
        <input type="text" name="SortedLastName" id="SortedLastName" value="<?= htmlspecialchars($employee['SortedLastName']) ?>" required><br><br>

        <label for="Phone">Telefon:</label><br>
        <input type="text" name="Phone" id="Phone" value="<?= htmlspecialchars($employee['Phone'] ?? '') ?>"><br><br>

        <label for="Mobile">Mobil:</label><br>
        <input type="text" name="Mobile" id="Mobile" value="<?= htmlspecialchars($employee['Mobile'] ?? '') ?>"><br><br>

        <label for="Mail">E-Mail:</label><br>
        <input type="email" name="Mail" id="Mail" value="<?= htmlspecialchars($employee['Mail'] ?? '') ?>"><br><br>

        <!-- Einrichtungen und Rollen auswählen -->
        <label>Einrichtungen und Rollen:</label><br>
        <table>
            <tr>
                <th>Auswählen</th>
                <th>Einrichtung</th>
                <th>Rolle</th>
            </tr>
            <?php foreach ($facilities as $facility): ?>
                <?php
                $facilityID = $facility['FacilityID'];
                $isSelected = isset($facilityAssignments[$facilityID]);
                $selectedRoleID = $isSelected ? $facilityAssignments[$facilityID] : 0; // Standardwert 0 für 'Teammitglied'
                ?>
                <tr>
                    <td>
                        <input type="checkbox" name="Facilities[<?= htmlspecialchars($facilityID) ?>][selected]" value="1" <?= $isSelected ? 'checked' : '' ?>>
                    </td>
                    <td>
                        <?= htmlspecialchars($facility['Facility']) ?>
                    </td>
                    <td>
                        <select name="Facilities[<?= htmlspecialchars($facilityID) ?>][RoleID]">
                            <?php foreach ($roles as $role): ?>
                                <option value="<?= htmlspecialchars($role['RoleID']) ?>" <?= $role['RoleID'] == $selectedRoleID ? 'selected' : '' ?>><?= htmlspecialchars($role['RoleName']) ?></option>
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
            <?php
            $languageID = $language['LanguageID'];
            $isSelected = isset($languageAssignments[$languageID]);
            $skillLevel = $isSelected ? $languageAssignments[$languageID]['SkillLevel'] : '';
            $zertifiziert = $isSelected ? $languageAssignments[$languageID]['zertifiziert'] : 0;
            ?>
            <input type="checkbox" name="Languages[<?= htmlspecialchars($languageID) ?>][selected]" value="1" <?= $isSelected ? 'checked' : '' ?>>
            <?= htmlspecialchars($language['LanguageName']) ?>
            <!-- Skill Level auswählen -->
            <select name="Languages[<?= htmlspecialchars($languageID) ?>][SkillLevel]">
                <?php foreach ($skillLevels as $level): ?>
                    <option value="<?= htmlspecialchars($level) ?>" <?= $level == $skillLevel ? 'selected' : '' ?>><?= htmlspecialchars($level) ?></option>
                <?php endforeach; ?>
            </select>
            Zertifiziert:
            <input type="checkbox" name="Languages[<?= htmlspecialchars($languageID) ?>][zertifiziert]" value="1" <?= $zertifiziert ? 'checked' : '' ?>>
            <br>
        <?php endforeach; ?>
        <br>

        <input type="submit" value="Aktualisieren">
    </form>
    <p><a href="employees.php">Zurück zur Mitarbeiterliste</a></p>
</body>
</html>