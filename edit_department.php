<?php
include 'db.php';

$departmentID = $_GET['id'] ?? null;

if (!$departmentID) {
    echo "Kein Fachbereich angegeben.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $department = $_POST['Department'] ?? '';
    $sortedLong = $_POST['SortedLong'] ?? '';
    $color = $_POST['color'] ?? '';

    if (empty($department) || empty($sortedLong) || empty($color)) {
        $error = "Bitte füllen Sie alle Pflichtfelder aus.";
    } else {
        try {
            $stmt = $db->prepare("UPDATE Departments SET Department = ?, SortedLong = ?, color = ? WHERE DepartmentID = ?");
            $stmt->execute([$department, $sortedLong, $color, $departmentID]);
            header('Location: departments.php');
            exit;
        } catch (PDOException $e) {
            $error = "Fehler beim Aktualisieren des Fachbereichs: " . $e->getMessage();
        }
    }
} else {
    try {
        $stmt = $db->prepare("SELECT * FROM Departments WHERE DepartmentID = ?");
        $stmt->execute([$departmentID]);
        $departmentData = $stmt->fetch();
        if (!$departmentData) {
            echo "Fachbereich nicht gefunden.";
            exit;
        }
    } catch (PDOException $e) {
        echo "Fehler beim Abrufen des Fachbereichs: " . $e->getMessage();
        exit;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Fachbereich bearbeiten</title>
    <style>
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
    <h1>Fachbereich bearbeiten</h1>
    <p class="menu">
        <a href="employees.php">Mitarbeitende verwalten</a> |
        <a href="facilities.php">Einrichtungen verwalten</a> |
        <a href="locations.php">Standorte verwalten</a> |
        <a href="departments.php">Fachbereiche verwalten</a> |
        <a href="languages.php">Sprachen verwalten</a> |
        <a href="roles.php">Rollen verwalten</a>
    </p>
    <?php if (!empty($error)): ?>
        <p style="color: red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <form method="post" action="edit_department.php?id=<?= urlencode($departmentID) ?>">
        <label for="Department">Bezeichnung:</label><br>
        <input type="text" name="Department" id="Department" value="<?= htmlspecialchars($departmentData['Department']) ?>" required><br><br>

        <label for="SortedLong">Sortierung:</label><br>
        <input type="text" name="SortedLong" id="SortedLong" value="<?= htmlspecialchars($departmentData['SortedLong']) ?>" required><br><br>

        <label for="color">Farbe:</label><br>
        <input type="text" name="color" id="color" value="<?= htmlspecialchars($departmentData['color']) ?>" required><br><br>

        <input type="submit" value="Aktualisieren">
    </form>
</body>
</html>
