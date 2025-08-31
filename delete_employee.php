<?php
include 'db.php';

$employeeID = $_GET['id'] ?? null;

if (!$employeeID) {
    echo "Keine Mitarbeiter:in angegeben.";
    exit;
}

try {
    // Optional: Prüfen, ob die Mitarbeiter:in existiert
    $stmt = $db->prepare("SELECT * FROM Employees WHERE EmployeeID = ?");
    $stmt->execute([$employeeID]);
    $employee = $stmt->fetch();

    if (!$employee) {
        echo "Mitarbeiter:in nicht gefunden.";
        exit;
    }

    // Zuordnungen löschen
    $stmt = $db->prepare("DELETE FROM FacilityLinks WHERE EmployeeID = ?");
    $stmt->execute([$employeeID]);
    $stmt = $db->prepare("DELETE FROM LanguageLinks WHERE EmployeeID = ?");
    $stmt->execute([$employeeID]);

    // Mitarbeiter:in löschen
    $stmt = $db->prepare("DELETE FROM Employees WHERE EmployeeID = ?");
    $stmt->execute([$employeeID]);

    header('Location: employees.php');
    exit;
} catch (PDOException $e) {
    echo "Fehler beim Löschen der Mitarbeiter:in: " . $e->getMessage();
    exit;
}
?>