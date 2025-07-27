<?php
// employees.php
date_default_timezone_set('Europe/Berlin'); // Setzt die Zeitzone auf Mitteleuropäische Zeit
include 'db.php';

try {
    // Mitarbeiter:innen abrufen
    $stmt = $db->query('SELECT * FROM Employees ORDER BY LastName, FirstName');
    $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Einrichtungen und Rollen für jede:n Mitarbeiter:in abrufen
    $employeeFacilities = [];
    foreach ($employees as $employee) {
        $employeeID = $employee['EmployeeID'];

        // Einrichtungen und Rollen abrufen
        $stmtFacilities = $db->prepare("
            SELECT Facilities.Facility, Roles.RoleName
            FROM FacilityLinks
            JOIN Facilities ON FacilityLinks.FacilityID = Facilities.FacilityID
            JOIN Roles ON FacilityLinks.RoleID = Roles.RoleID
            WHERE FacilityLinks.EmployeeID = ?
        ");
        $stmtFacilities->execute([$employeeID]);
        $facilities = $stmtFacilities->fetchAll(PDO::FETCH_ASSOC);
        $employeeFacilities[$employeeID] = $facilities;
    }

    // Sprachen für jede:n Mitarbeiter:in abrufen
    $employeeLanguages = [];
    foreach ($employees as $employee) {
        $employeeID = $employee['EmployeeID'];

        // Sprachen abrufen
        $stmtLanguages = $db->prepare("
            SELECT Languages.LanguageName, LanguageLinks.SkillLevel, LanguageLinks.zertifiziert
            FROM LanguageLinks
            JOIN Languages ON LanguageLinks.LanguageID = Languages.LanguageID
            WHERE LanguageLinks.EmployeeID = ?
        ");
        $stmtLanguages->execute([$employeeID]);
        $languages = $stmtLanguages->fetchAll(PDO::FETCH_ASSOC);
        $employeeLanguages[$employeeID] = $languages;
    }
} catch (PDOException $e) {
    echo "Fehler beim Abrufen der Mitarbeiter:innen: " . $e->getMessage();
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Mitarbeiter:innen verwalten</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 8px;
            vertical-align: top;
        }
        th {
            background-color: #ddd;
        }
		tr:nth-child(even) {
			background-color: #efefef;
		}
		tr:hover {
		    background-color: #FFD580; /* Gleiche Farbe wie Slogan */
		} 
        a {
            text-decoration: none;
            color: #469cda;
        }
        .menu a {
	        color:darkorange;
        }
    </style>
</head>
<body>
	<img src="img/IFAK-Logo.svg" style="float: right" width="200">
    <h1>Mitarbeiter:innen verwalten</h1>
    <?php
		$files = [
		    'ifak.db',
		    'ifak.json'
		];
		
		foreach ($files as $path) {
		    $name = basename($path);
		    $date = file_exists($path) ? date("F d Y H:i:s", filemtime($path)) : "Datei nicht gefunden.";
		    echo "<p>Letztes Änderungsdatum von {$name}: {$date}</p>";
		}
	?>
    <p class="menu">
        <a href="add_employee.php">Neue:n Mitarbeiter:in hinzufügen</a> | 
        <a href="facilities.php">Einrichtungen verwalten</a> | 
        <a href="locations.php">Standorte verwalten</a> | 
        <a href="languages.php">Sprachen verwalten</a> | 
        <a href="roles.php">Rollen verwalten</a>
    </p>
    <form method="post" action="run_sqlite2json_script.php">
        <button type="submit">sql2json</button>
    </form>
    
	<form method="post" action="run_upload_script.php">
        <button type="submit">upload</button>
    </form>
    
    <form method="post" action="run_script.php">
        <button type="submit">sql2json & upload</button>
    </form>
    
    <table>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Kontakt</th>
            <th>Einrichtungen (Rollen)</th>
            <th>Sprachen</th>
            <th>Aktionen</th>
        </tr>
        <?php foreach ($employees as $employee): ?>
        <tr>
            <td><?= htmlspecialchars($employee['EmployeeID']) ?></td>
            <td><?= htmlspecialchars($employee['FirstName'] . ' ' . $employee['LastName']) ?></td>
            <td>
                <?php if (!empty($employee['Phone'])): ?>
                    Tel: <?= htmlspecialchars($employee['Phone']) ?><br>
                <?php endif; ?>
                <?php if (!empty($employee['Mobile'])): ?>
                    Mobil: <?= htmlspecialchars($employee['Mobile']) ?><br>
                <?php endif; ?>
                <?php if (!empty($employee['Mail'])): ?>
                    E-Mail: <a href="mailto:<?= htmlspecialchars($employee['Mail']) ?>"><?= htmlspecialchars($employee['Mail']) ?></a>
                <?php endif; ?>
            </td>
            <td>
                <?php if (!empty($employeeFacilities[$employee['EmployeeID']])): ?>
                    <?php foreach ($employeeFacilities[$employee['EmployeeID']] as $facility): ?>
                        <?= htmlspecialchars($facility['Facility']) ?> (<?= htmlspecialchars($facility['RoleName']) ?>)<br>
                    <?php endforeach; ?>
                <?php else: ?>
                    Keine
                <?php endif; ?>
            </td>
            <td>
                <?php if (!empty($employeeLanguages[$employee['EmployeeID']])): ?>
                    <?php foreach ($employeeLanguages[$employee['EmployeeID']] as $language): ?>
                        <?= htmlspecialchars($language['LanguageName']) ?> (<?= htmlspecialchars($language['SkillLevel']) ?><?= $language['zertifiziert'] ? ', zertifiziert' : '' ?>)<br>
                    <?php endforeach; ?>
                <?php else: ?>
                    Keine
                <?php endif; ?>
            </td>
            <td>
                <a href="edit_employee.php?id=<?= urlencode($employee['EmployeeID']) ?>">Bearbeiten</a> |
                <a href="delete_employee.php?id=<?= urlencode($employee['EmployeeID']) ?>" onclick="return confirm('Möchten Sie diese:n Mitarbeiter:in wirklich löschen?');">Löschen</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>