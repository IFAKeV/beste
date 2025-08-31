<?php
include 'db.php';

try {
    $stmt = $db->query('SELECT * FROM Locations');
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
    <title>Standorte verwalten</title>
    <style>
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
    <h1>Standorte verwalten</h1>
    <p class="menu">
        <a href="add_location.php">Neuen Standort hinzufügen</a> | 
		<a href="employees.php">Mitarbeitende verwalten</a> | 
        <a href="facilities.php">Einrichtungen verwalten</a> | 
        <a href="languages.php">Sprachen verwalten</a> | 
        <a href="roles.php">Rollen verwalten</a>
    </p>

    <table>
        <tr>
            <th>ID</th>
            <th>Bezeichnung</th>
            <th>Sortierung</th>
            <th>Straße</th>
            <th>PLZ</th>
            <th>Stadt</th>
            <th>Aktionen</th>
        </tr>
        <?php foreach ($locations as $location): ?>
        <tr>
            <td><?= htmlspecialchars($location['LocationID']) ?></td>
            <td><?= htmlspecialchars($location['Location']) ?></td>
            <td><?= htmlspecialchars($location['SortedLong']) ?></td>
            <td><?= htmlspecialchars($location['Street']) ?></td>
            <td><?= htmlspecialchars($location['ZIP']) ?></td>
            <td><?= htmlspecialchars($location['Town']) ?></td>
            <td>
                <a href="edit_location.php?id=<?= urlencode($location['LocationID']) ?>">Bearbeiten</a> |
                <a href="delete_location.php?id=<?= urlencode($location['LocationID']) ?>" onclick="return confirm('Möchten Sie diesen Standort wirklich löschen?');">Löschen</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>