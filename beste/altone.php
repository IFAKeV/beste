<?php
// Verbindung zur SQLite-Datenbank herstellen (Datenbankdatei: 'ifak.db')
try {
    $db = new PDO('sqlite:ifak.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    die('Verbindung zur Datenbank fehlgeschlagen: ' . $e->getMessage());
}

// Aktuellen Tab ermitteln und Suchbegriff ermitteln
$tab = $_GET['tab'] ?? null;
$search = trim($_GET['search'] ?? '');

// Alle vorhandenen Anfangsbuchstaben der Nachnamen von Mitarbeitern abrufen
$letters_stmt = $db->query('
    SELECT DISTINCT UPPER(SUBSTR(LastName, 1, 1)) AS Initial 
    FROM Employees 
    WHERE LastName <> "" 
    ORDER BY Initial COLLATE NOCASE
');
$available_letters = $letters_stmt->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Karteikartensystem</title>
    <style>
        /* Einfache CSS-Stile */
        body {
            font-family: Arial, sans-serif;
        }
        nav a {
            margin-right: 5px;
            text-decoration: none;
        }
        nav a.active,
        nav a:hover {
            font-weight: bold;
            text-decoration: underline;
        }
        ul {
            list-style-type: none;
        }
        .search-form {
            margin-bottom: 20px;
        }
        .employee, .facility, .location {
            margin-bottom: 20px;
        }
        .employee h3,
        .facility h3,
        .location h3 {
            margin: 0;
        }
        .details {
            margin-left: 20px;
        }
    </style>
</head>
<body>

<h1>Karteikartensystem</h1>

<!-- Suchformular -->
<form method="get" class="search-form">
    <input type="text" name="search" placeholder="Suche..." value="<?= htmlspecialchars($search) ?>">
    <button type="submit">Suchen</button>
</form>

<!-- Navigation -->
<nav>
    <?php foreach ($available_letters as $c): ?>
        <a href="?tab=<?= $c ?>" class="<?= ($tab === $c) ? 'active' : '' ?>"><?= $c ?></a>
    <?php endforeach; ?>
    | <a href="?tab=Facilities" class="<?= ($tab === 'Facilities') ? 'active' : '' ?>">Facilities</a>
    | <a href="?tab=Locations" class="<?= ($tab === 'Locations') ? 'active' : '' ?>">Locations</a>
</nav>

<?php
// Datenanzeige basierend auf Tab oder Suchbegriff
if (!empty($search)) {
    // Suchergebnisse anzeigen
    echo '<h2>Suchergebnisse für "' . htmlspecialchars($search) . '"</h2>';

    // Funktion zum Suchen in der Datenbank
    function searchInTable($db, $table, $columns, $search) {
        $likeSearch = '%' . $search . '%';
        $sql = "SELECT * FROM $table WHERE ";
        $conditions = [];
        foreach ($columns as $column) {
            $conditions[] = "$column LIKE :search";
        }
        $sql .= implode(' OR ', $conditions);
        $sql .= " ORDER BY " . implode(', ', $columns) . " COLLATE NOCASE";
        $stmt = $db->prepare($sql);
        $stmt->execute([':search' => $likeSearch]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Mitarbeiter suchen
    $employees = searchInTable($db, 'Employees', ['FirstName', 'LastName'], $search);

    if (!empty($employees)) {
        echo '<h3>Mitarbeiter</h3><ul>';
        foreach ($employees as $row) {
            $employeeID = $row['EmployeeID'];
            echo '<li class="employee">';
            echo '<a href="?tab=EmployeeDetail&id=' . $employeeID . '">'
                . htmlspecialchars($row['FirstName'] . ' ' . $row['LastName']) . '</a>';
            echo '</li>';
        }
        echo '</ul>';
    }

    // Facilities suchen
    $facilities = searchInTable($db, 'Facilities', ['Facility'], $search);

    if (!empty($facilities)) {
        echo '<h3>Facilities</h3><ul>';
        foreach ($facilities as $row) {
            $facilityID = $row['FacilityID'];
            echo '<li class="facility">';
            echo '<a href="?tab=FacilityDetail&id=' . $facilityID . '">'
                . htmlspecialchars($row['Facility']) . '</a>';
            echo '</li>';
        }
        echo '</ul>';
    }

    // Locations suchen
    $locations = searchInTable($db, 'Locations', ['Location'], $search);

    if (!empty($locations)) {
        echo '<h3>Locations</h3><ul>';
        foreach ($locations as $row) {
            echo '<li class="location">';
            echo htmlspecialchars($row['Location']);
            echo '</li>';
        }
        echo '</ul>';
    }

    if (empty($employees) && empty($facilities) && empty($locations)) {
        echo '<p>Keine Ergebnisse gefunden.</p>';
    }

} elseif ($tab == 'Facilities') {
    // Facilities anzeigen
    $stmt = $db->query('SELECT * FROM Facilities ORDER BY Facility COLLATE NOCASE');
    echo '<h2>Facilities</h2>';
    echo '<ul>';
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $facilityID = $row['FacilityID'];
        echo '<li class="facility">';
        echo '<a href="?tab=FacilityDetail&id=' . $facilityID . '">'
            . htmlspecialchars($row['Facility']) . '</a>';
        echo '</li>';
    }
    echo '</ul>';
} elseif ($tab == 'Locations') {
    // Locations anzeigen
    $stmt = $db->query('SELECT * FROM Locations ORDER BY Location COLLATE NOCASE');
    echo '<h2>Locations</h2>';
    echo '<ul>';
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // Adresse zusammenstellen
        $address = trim($row['Street'] . ', ' . $row['ZIP'] . ' ' . $row['Town'], ', ');
        // URL zu Google Maps generieren
        $maps_url = 'https://www.google.com/maps/search/?api=1&query=' . urlencode($address);
        echo '<li class="location">';
        echo '<strong>' . htmlspecialchars($row['Location']) . '</strong><br>';
        echo '<a href="' . htmlspecialchars($maps_url) . '" target="_blank">'
            . htmlspecialchars($address) . '</a><br>';

        // Optionale zusätzliche Informationen anzeigen
        foreach (['Phone' => 'Telefon', 'Mobile' => 'Mobil', 'Fax' => 'Fax'] as $field => $label) {
            if (!empty($row[$field])) {
                echo $label . ': ' . htmlspecialchars($row[$field]) . '<br>';
            }
        }
        if (!empty($row['Mail'])) {
            echo 'E-Mail: <a href="mailto:' . htmlspecialchars($row['Mail']) . '">'
                . htmlspecialchars($row['Mail']) . '</a><br>';
        }

        echo '</li>';
    }
    echo '</ul>';
} elseif ($tab == 'EmployeeDetail' && isset($_GET['id'])) {
    // Detailansicht für Mitarbeiter
    $employeeID = intval($_GET['id']);
    $stmt = $db->prepare('SELECT * FROM Employees WHERE EmployeeID = :employeeID');
    $stmt->execute([':employeeID' => $employeeID]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        echo '<h2>Mitarbeiterdetails</h2>';
        echo '<div class="employee">';
        echo '<h3>' . htmlspecialchars($row['FirstName'] . ' ' . $row['LastName']) . '</h3>';

        // Kontaktdaten
        echo '<div class="details">';
        foreach (['Phone' => 'Telefon', 'Mobile' => 'Mobil'] as $field => $label) {
            if (!empty($row[$field])) {
                echo $label . ': ' . htmlspecialchars($row[$field]) . '<br>';
            }
        }
        if (!empty($row['Mail'])) {
            echo 'E-Mail: <a href="mailto:' . htmlspecialchars($row['Mail']) . '">'
                . htmlspecialchars($row['Mail']) . '</a><br>';
        }

        // Facilities und Rollen abrufen
        $facilitiesStmt = $db->prepare('
            SELECT Facilities.Facility, Roles.RoleName
            FROM FacilityLinks
            JOIN Facilities ON FacilityLinks.FacilityID = Facilities.FacilityID
            JOIN Roles ON FacilityLinks.RoleID = Roles.RoleID
            WHERE FacilityLinks.EmployeeID = :employeeID
            ORDER BY Facilities.Facility COLLATE NOCASE
        ');
        $facilitiesStmt->execute([':employeeID' => $employeeID]);
        $facilities = $facilitiesStmt->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($facilities)) {
            echo '<strong>Facilities und Rollen:</strong><br>';
            echo '<ul>';
            foreach ($facilities as $facility) {
                echo '<li>' . htmlspecialchars($facility['Facility'] . ' - ' . $facility['RoleName']) . '</li>';
            }
            echo '</ul>';
        }

        // Sprachen abrufen
        $languagesStmt = $db->prepare('
            SELECT Languages.LanguageName, LanguageLinks.SkillLevel, LanguageLinks.zertifiziert
            FROM LanguageLinks
            JOIN Languages ON LanguageLinks.LanguageID = Languages.LanguageID
            WHERE LanguageLinks.EmployeeID = :employeeID
            ORDER BY Languages.LanguageName COLLATE NOCASE
        ');
        $languagesStmt->execute([':employeeID' => $employeeID]);
        $languages = $languagesStmt->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($languages)) {
            echo '<strong>Sprachen:</strong><br>';
            echo '<ul>';
            foreach ($languages as $language) {
                echo '<li>' . htmlspecialchars($language['LanguageName']) . ' - Niveau: '
                    . htmlspecialchars($language['SkillLevel']);
                if ($language['zertifiziert']) {
                    echo ' (zertifiziert)';
                }
                echo '</li>';
            }
            echo '</ul>';
        }

        echo '</div>'; // Ende details
        echo '</div>'; // Ende employee
    } else {
        echo '<p>Mitarbeiter nicht gefunden.</p>';
    }
} elseif ($tab == 'FacilityDetail' && isset($_GET['id'])) {
    // Detailansicht für Facility
    $facilityID = intval($_GET['id']);
    $stmt = $db->prepare('SELECT * FROM Facilities WHERE FacilityID = :facilityID');
    $stmt->execute([':facilityID' => $facilityID]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        echo '<h2>Facility Details</h2>';
        echo '<div class="facility">';
        echo '<h3>' . htmlspecialchars($row['Facility']) . '</h3>';

        // Kontaktdaten
        echo '<div class="details">';
        foreach (['Phone' => 'Telefon', 'Mobile' => 'Mobil', 'Fax' => 'Fax'] as $field => $label) {
            if (!empty($row[$field])) {
                echo $label . ': ' . htmlspecialchars($row[$field]) . '<br>';
            }
        }
        if (!empty($row['Mail'])) {
            echo 'E-Mail: <a href="mailto:' . htmlspecialchars($row['Mail']) . '">'
                . htmlspecialchars($row['Mail']) . '</a><br>';
        }
        if (!empty($row['URL'])) {
            echo 'Webseite: <a href="' . htmlspecialchars($row['URL']) . '" target="_blank">'
                . htmlspecialchars($row['URL']) . '</a><br>';
        }

        // Mitarbeiter in dieser Facility abrufen
        $employeesStmt = $db->prepare('
            SELECT Employees.EmployeeID, Employees.FirstName, Employees.LastName, Roles.RoleName
            FROM FacilityLinks
            JOIN Employees ON FacilityLinks.EmployeeID = Employees.EmployeeID
            JOIN Roles ON FacilityLinks.RoleID = Roles.RoleID
            WHERE FacilityLinks.FacilityID = :facilityID
            ORDER BY Employees.LastName COLLATE NOCASE, Employees.FirstName COLLATE NOCASE
        ');
        $employeesStmt->execute([':facilityID' => $facilityID]);
        $employees = $employeesStmt->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($employees)) {
            echo '<strong>Mitarbeiter:</strong><br>';
            echo '<ul>';
            foreach ($employees as $employee) {
                echo '<li>';
                echo '<a href="?tab=EmployeeDetail&id=' . $employee['EmployeeID'] . '">'
                    . htmlspecialchars($employee['FirstName'] . ' ' . $employee['LastName']) . '</a>';
                echo ' - ' . htmlspecialchars($employee['RoleName']);
                echo '</li>';
            }
            echo '</ul>';
        } else {
            echo 'Keine Mitarbeiter in dieser Facility.<br>';
        }

        echo '</div>'; // Ende details
        echo '</div>'; // Ende facility
    } else {
        echo '<p>Facility nicht gefunden.</p>';
    }
} elseif (in_array($tab, $available_letters)) {
    // Mitarbeiter anzeigen, deren Nachname mit dem ausgewählten Buchstaben beginnt
    $stmt = $db->prepare('
        SELECT * 
        FROM Employees 
        WHERE LastName LIKE :letter 
        ORDER BY LastName COLLATE NOCASE, FirstName COLLATE NOCASE
    ');
    $stmt->execute([':letter' => $tab . '%']);
    echo '<h2>Mitarbeiter - ' . $tab . '</h2>';
    echo '<ul>';
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $employeeID = $row['EmployeeID'];
        echo '<li class="employee">';
        echo '<a href="?tab=EmployeeDetail&id=' . $employeeID . '">'
            . htmlspecialchars($row['FirstName'] . ' ' . $row['LastName']) . '</a>';
        echo '</li>';
    }
    echo '</ul>';
} else {
    echo '<p>Bitte wählen Sie einen Tab aus oder nutzen Sie die Suche.</p>';
}
?>

</body>
</html>