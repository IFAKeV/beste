<?php
header('Content-Type: application/json');

$db = new SQLite3('ifak.db');

$employeeId = $_GET['id'] ?? null;

if ($employeeId === null) {
    echo json_encode(['error' => 'No employee ID provided']);
    exit;
}

$stmt = $db->prepare('SELECT * FROM employee_permissions WHERE employee_id = :id');
$stmt->bindValue(':id', $employeeId, SQLITE3_INTEGER);
$result = $stmt->execute();

$permissions = [];
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $permissions[] = $row;
}

echo json_encode($permissions);
