<?php	
// Fehleranzeige aktivieren
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include the database connection
include 'db.php';

// Fetch employees, groups, and permissions
$employees = $db->query("SELECT * FROM Employees")->fetchAll();
$groups = $db->query("SELECT * FROM Groups")->fetchAll();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employeeId = $_POST['employee_id'];
    $groupId = $_POST['group_id'];

    // Check if the employee already has a permission entry
    $stmt = $db->prepare("SELECT * FROM Permissions WHERE EmployeeID = ? AND GroupID = ?");
    $stmt->execute([$employeeId, $groupId]);
    $existingPermission = $stmt->fetch();

    if ($existingPermission) {
        // Update existing permission (if needed)
        echo "<p>Employee already has this permission!</p>";
    } else {
        // Insert new permission
        $stmt = $db->prepare("INSERT INTO Permissions (EmployeeID, GroupID) VALUES (?, ?)");
        $stmt->execute([$employeeId, $groupId]);
        echo "<p>Permissions updated successfully!</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Permissions</title>
</head>
<body>
    <h1>Manage Permissions</h1>
    <form method="POST">
        <label for="employee_id">Select Employee:</label>
        <select name="employee_id" id="employee_id" required>
            <?php foreach ($employees as $employee): ?>
                <option value="<?= $employee['EmployeeID'] ?>"><?= $employee['FirstName'] . ' ' . $employee['LastName'] ?></option>
            <?php endforeach; ?>
        </select>

        <label for="group_id">Select Group:</label>
        <select name="group_id" id="group_id" required>
            <?php foreach ($groups as $group): ?>
                <option value="<?= $group['GroupID'] ?>"><?= $group['GroupName'] ?></option>
            <?php endforeach; ?>
        </select>

        <button type="submit">Update Permissions</button>
    </form>

    <h2>Current Permissions</h2>
    <table border="1">
        <tr>
            <th>Employee</th>
            <th>Group</th>
        </tr>
        <?php
        $permissions = $db->query("
            SELECT e.FirstName, e.LastName, g.GroupName
            FROM Permissions p
            JOIN Employees e ON p.EmployeeID = e.EmployeeID
            JOIN Groups g ON p.GroupID = g.GroupID
        ")->fetchAll();

        foreach ($permissions as $permission): ?>
            <tr>
                <td><?= $permission['FirstName'] . ' ' . $permission['LastName'] ?></td>
                <td><?= $permission['GroupName'] ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
