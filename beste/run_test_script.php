<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Python-Skript ausführen
   $output = shell_exec('python3 /var/www/html/beste/test.py 2>&1');
   echo "<pre>Python-Skript ausgeführt: " . htmlspecialchars($output) . "</pre>";
}
?>
<p><a href="employees.php">Zurück</a> | <a href="https:/beste.ifak-bochum.de">Beste</a></p>