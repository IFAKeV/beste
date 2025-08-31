<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $department = $_POST['Department'] ?? '';
    $sortedLong = $_POST['SortedLong'] ?? '';
    $color = $_POST['color'] ?? '';

    if (empty($department) || empty($sortedLong) || empty($color)) {
        $error = "Bitte füllen Sie alle Pflichtfelder aus.";
    } else {
        try {
            $stmt = $db->prepare("INSERT INTO Departments (Department, SortedLong, color) VALUES (?, ?, ?)");
            $stmt->execute([$department, $sortedLong, $color]);
            header('Location: departments.php');
            exit;
        } catch (PDOException $e) {
            $error = "Fehler beim Hinzufügen des Fachbereichs: " . $e->getMessage();
        }
    }
} else {
    ?>
    <!DOCTYPE html>
    <html>
<head>
    <meta charset="UTF-8">
        <title>Neuen Fachbereich hinzufügen</title>
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
        <h1>Neuen Fachbereich hinzufügen</h1>
        <p class="menu">
            <a href="employees.php">Mitarbeitende verwalten</a> |
            <a href="facilities.php">Einrichtungen verwalten</a> |
            <a href="locations.php">Standorte verwalten</a> |
            <a href="departments.php">Fachbereiche verwalten</a> |
            <a href="languages.php">Sprachen verwalten</a> |
            <a href="roles.php">Rollen verwalten</a>
        </p>
        <?php if (!empty($error)): ?>
            <p style="color: red;"> <?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
        <form method="post" action="add_department.php">
            <label for="Department">Bezeichnung:</label><br>
            <input type="text" name="Department" id="Department" required><br><br>

            <label for="SortedLong">Sortierung:</label><br>
            <input type="text" name="SortedLong" id="SortedLong" required><br><br>

            <label for="color">Farbe:</label><br>
            <input type="text" name="color" id="color" required><br><br>

            <input type="submit" value="Speichern">
        </form>
    </body>
    </html>
    <?php
}
?>
