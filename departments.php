<?php
include 'db.php';

try {
    $stmt = $db->query('SELECT * FROM Departments');
    $departments = $stmt->fetchAll();
} catch (PDOException $e) {
    echo "Fehler beim Abrufen der Fachbereiche: " . $e->getMessage();
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Fachbereiche verwalten</title>
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
            background-color: #FFD580;
        }
        a {
            text-decoration: none;
            color: #469cda;
        }
        .menu a {
            color: darkorange;
        }
    </style>
</head>
<body>
    <img src="img/IFAK-Logo.svg" style="float: right" width="200">
    <h1>Fachbereiche verwalten</h1>
    <p class="menu">
        <a href="add_department.php">Neuen Fachbereich hinzufügen</a> |
        <a href="employees.php">Mitarbeitende verwalten</a> |
        <a href="facilities.php">Einrichtungen verwalten</a> |
        <a href="locations.php">Standorte verwalten</a> |
        <a href="languages.php">Sprachen verwalten</a> |
        <a href="roles.php">Rollen verwalten</a>
    </p>

    <table>
        <tr>
            <th>ID</th>
            <th>Bezeichnung</th>
            <th>Sortierung</th>
            <th>Farbe</th>
            <th>Aktionen</th>
        </tr>
        <?php foreach ($departments as $department): ?>
        <tr>
            <td><?= htmlspecialchars($department['DepartmentID']) ?></td>
            <td><?= htmlspecialchars($department['Department']) ?></td>
            <td><?= htmlspecialchars($department['SortedLong']) ?></td>
            <td><?= htmlspecialchars($department['color']) ?></td>
            <td>
                <a href="edit_department.php?id=<?= urlencode($department['DepartmentID']) ?>">Bearbeiten</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
