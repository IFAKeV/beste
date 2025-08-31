<?php
// facilities.php
include 'db.php';

try {
    // Alle Einrichtungen abrufen, inkl. Standortbezeichnung
    $stmt = $db->query('
        SELECT Facilities.*, Locations.Location AS LocationName
        FROM Facilities
        JOIN Locations ON Facilities.LocationID = Locations.LocationID
    ');
    $facilities = $stmt->fetchAll();
} catch (PDOException $e) {
    echo "Fehler beim Abrufen der Einrichtungen: " . $e->getMessage();
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Einrichtungen verwalten</title>
    <style>
	    body {
/* 		    font-size: 0.8em; */
	    }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 2px;
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
    <h1>Einrichtungen verwalten</h1>
	<p class="menu">
        <a href="add_facility.php">Neue Einrichtung hinzufügen</a> | 
        <a href="employees.php">Mitarbeitende verwalten</a> | 
        <a href="locations.php">Standorte verwalten</a> | 
        <a href="languages.php">Sprachen verwalten</a> | 
        <a href="roles.php">Rollen verwalten</a>
    </p>

    <table>
        <tr>
            <th>ID</th>
            <th>Standort</th>
            <th>Kurzname</th>
            <th>Einrichtungsname</th>
            <th>Langname</th>
            <th>Sortierung</th>
            <th>Telefon</th>
            <th>Mobil</th>
            <th>Fax</th>
            <th>E-Mail</th>
            <th>URL</th>
            <th>Aktionen</th>
        </tr>
        <?php foreach ($facilities as $facility): ?>
        <tr>
            <td><?= htmlspecialchars($facility['FacilityID']) ?></td>
            <td><?= htmlspecialchars($facility['LocationName']) ?></td>
            <td><?= htmlspecialchars($facility['Short']) ?></td>
            <td><?= htmlspecialchars($facility['Facility']) ?></td>
            <td><?= htmlspecialchars($facility['Long']) ?></td>
            <td><?= htmlspecialchars($facility['SortedLong']) ?></td>
            <td><?= htmlspecialchars($facility['Phone']) ?></td>
            <td><?= htmlspecialchars($facility['Mobile']) ?></td>
            <td><?= htmlspecialchars($facility['Fax']) ?></td>
            <td><?= htmlspecialchars($facility['Mail']) ?></td>
            <td><?= htmlspecialchars($facility['URL']) ?></td>
            <td>
                <a href="edit_facility.php?id=<?= urlencode($facility['FacilityID']) ?>">Bearbeiten</a> |
                <a href="delete_facility.php?id=<?= urlencode($facility['FacilityID']) ?>" onclick="return confirm('Möchten Sie diese Einrichtung wirklich löschen?');">Löschen</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>