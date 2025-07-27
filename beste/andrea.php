<?php
// Verbindung zur SQLite-Datenbank herstellen
try {
    $db = new PDO('sqlite:ifak.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    die('Verbindung zur Datenbank fehlgeschlagen: ' . $e->getMessage());
}

// Aktuelles Tab ermitteln
$tab = isset($_GET['tab']) ? $_GET['tab'] : null;

// Suchbegriff ermitteln
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

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
    <!-- Verlinkung des CSS-Stylesheets -->
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<header>
    <h1>Karteikartensystem</h1>
    <!-- Suchformular -->
    <form method="get" class="search-form">
        <input type="text" name="search" placeholder="Suche..." value="<?php echo htmlspecialchars($search); ?>">
        <button type="submit">Suchen</button>
    </form>
</header>

<!-- Navigation -->
<nav>
    <?php
    foreach ($available_letters as $c) {
        $class = ($tab === $c) ? 'active' : '';
        echo '<a href="?tab=' . $c . '" class="' . $class . '">' . $c . '</a>';
    }
    $facilities_class = ($tab === 'Facilities') ? 'active' : '';
    $locations_class = ($tab === 'Locations') ? 'active' : '';
    echo '<a href="?tab=Facilities" class="' . $facilities_class . '">Facilities</a>';
    echo '<a href="?tab=Locations" class="' . $locations_class . '">Locations</a>';
    ?>
</nav>

<main>
<?php
// Datenanzeige basierend auf Tab oder Suchbegriff
if (!empty($search)) {
    // Suchergebnisse anzeigen
    echo '<h2>Suchergebnisse für "' . htmlspecialchars($search) . '"</h2>';

    // Mitarbeiter suchen
    $stmt = $db->prepare('
        SELECT * 
        FROM Employees 
        WHERE FirstName LIKE :search OR LastName LIKE :search
        ORDER BY LastName COLLATE NOCASE, FirstName COLLATE NOCASE
    ');
    $stmt->execute([':search' => '%' . $search . '%']);
    $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($employees)) {
        echo '<h3>Mitarbeiter</h3><ul>';
        foreach ($employees as $row) {
            $employeeID = $row['EmployeeID'];
            echo '<li>';
            echo '<a href="?tab=EmployeeDetail&id=' . $employeeID . '">' . htmlspecialchars($row['FirstName']) . ' ' . htmlspecialchars($row['LastName']) . '</a>';
            echo '</li>';
        }
        echo '</ul>';
    }

    // Facilities suchen
    $stmt = $db->prepare('
        SELECT * 
        FROM Facilities 
        WHERE Facility LIKE :search
        ORDER BY Facility COLLATE NOCASE
    ');
    $stmt->execute([':search' => '%' . $search . '%']);
    $facilities = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($facilities)) {
        echo '<h3>Facilities</h3><ul>';
        foreach ($facilities as $row) {
            $facilityID = $row['FacilityID'];
            echo '<li>';
            echo '<a href="?tab=FacilityDetail&id=' . $facilityID . '">' . htmlspecialchars($row['Facility']) . '</a>';
            echo '</li>';
        }
        echo '</ul>';
    }

    // Locations suchen
    $stmt = $db->prepare('
        SELECT * 
        FROM Locations 
        WHERE Location LIKE :search
        ORDER BY Location COLLATE NOCASE
    ');
    $stmt->execute([':search' => '%' . $search . '%']);
    $locations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($locations)) {
        echo '<h3>Locations</h3><ul>';
        foreach ($locations as $row) {
            $locationID = $row['LocationID'];
            echo '<li>';
            echo '<a href="?tab=LocationDetail&id=' . $locationID . '">' . htmlspecialchars($row['Location']) . '</a>';
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
        echo '<li>';
        echo '<a href="?tab=FacilityDetail&id=' . $facilityID . '">' . htmlspecialchars($row['Facility']) . '</a>';
        echo '</li>';
    }
    echo '</ul>';

} elseif ($tab == 'Locations') {
    // Locations anzeigen
    $stmt = $db->query('SELECT * FROM Locations ORDER BY Location COLLATE NOCASE');
    echo '<h2>Locations</h2>';
    echo '<ul>';
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $locationID = $row['LocationID'];
        echo '<li>';
        echo '<a href="?tab=LocationDetail&id=' . $locationID . '">' . htmlspecialchars($row['Location']) . '</a>';
        echo '</li>';
    }
    echo '</ul>';

} elseif ($tab == 'EmployeeDetail' && isset($_GET['id'])) {
    // Detailansicht für Mitarbeiter
    // [Code für die Mitarbeiterdetailansicht hier einfügen]
    // Detailansicht für Mitarbeiter
$employeeID = intval($_GET['id']);
$stmt = $db->prepare('SELECT * FROM Employees WHERE EmployeeID = :employeeID');
$stmt->execute([':employeeID' => $employeeID]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if ($row) {
    echo '<h2>' . htmlspecialchars($row['FirstName']) . ' ' . htmlspecialchars($row['LastName']) . '</h2>';
    echo '<div class="details">';
    
    // Kontaktdaten
    if (!empty($row['Phone'])) {
        echo '<p><strong>Telefon:</strong> ' . htmlspecialchars($row['Phone']) . '</p>';
    }
    if (!empty($row['Mobile'])) {
        echo '<p><strong>Mobil:</strong> ' . htmlspecialchars($row['Mobile']) . '</p>';
    }
    if (!empty($row['Mail'])) {
        echo '<p><strong>E-Mail:</strong> <a href="mailto:' . htmlspecialchars($row['Mail']) . '">' . htmlspecialchars($row['Mail']) . '</a></p>';
    }

    // Facilities und Rollen
    $facilitiesStmt = $db->prepare('
        SELECT Facilities.FacilityID, Facilities.Facility, Roles.RoleName
        FROM FacilityLinks
        JOIN Facilities ON FacilityLinks.FacilityID = Facilities.FacilityID
        JOIN Roles ON FacilityLinks.RoleID = Roles.RoleID
        WHERE FacilityLinks.EmployeeID = :employeeID
        ORDER BY Facilities.Facility COLLATE NOCASE
    ');
    $facilitiesStmt->execute([':employeeID' => $employeeID]);
    $facilities = $facilitiesStmt->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($facilities)) {
        echo '<h3>Facilities und Rollen</h3>';
        echo '<ul>';
        foreach ($facilities as $facility) {
            echo '<li>';
            echo '<a href="?tab=FacilityDetail&id=' . $facility['FacilityID'] . '">' . htmlspecialchars($facility['Facility']) . '</a>';
            echo ' - ' . htmlspecialchars($facility['RoleName']);
            echo '</li>';
        }
        echo '</ul>';
    }

    // Sprachen
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
        echo '<h3>Sprachen</h3>';
        echo '<ul>';
        foreach ($languages as $language) {
            echo '<li>';
            echo htmlspecialchars($language['LanguageName']) . ' - ' . htmlspecialchars($language['SkillLevel']);
            if ($language['zertifiziert']) {
                echo ' (zertifiziert)';
            }
            echo '</li>';
        }
        echo '</ul>';
    }

    echo '</div>';
} else {
    echo '<p>Mitarbeiter nicht gefunden.</p>';
}

} elseif ($tab == 'FacilityDetail' && isset($_GET['id'])) {
    // Detailansicht für Facility
    // [Code für die Facilitydetailansicht hier einfügen]

} elseif ($tab == 'LocationDetail' && isset($_GET['id'])) {
    // Detailansicht für Location
    // [Code für die Locationdetailansicht hier einfügen]

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
        echo '<li>';
        echo '<a href="?tab=EmployeeDetail&id=' . $employeeID . '">' . htmlspecialchars($row['FirstName']) . ' ' . htmlspecialchars($row['LastName']) . '</a>';
        echo '</li>';
    }
    echo '</ul>';
} else {
    echo '<p>Bitte wählen Sie einen Tab aus oder nutzen Sie die Suche.</p>';
}
?>
</main>

<footer>
    &copy; <?php echo date('Y'); ?> Ihr Unternehmen
</footer>

</body>
</html>