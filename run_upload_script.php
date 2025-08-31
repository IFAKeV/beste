<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Python-Skript ausführen
/*
    $output = shell_exec('python3 /var/www/html/beste/sqlite2json.py');
    echo "<p>Python-Skript ausgeführt: " . $output.'</p>';
*/

    // Shell-Kommando ausführen (z.B. scp mit sshpass, weil ich nicht wüsste wie anders)
    $password = 'd#ePh>a-9kL2:8J';
    $remote_user = 'acc814279125';
    $remote_host = 'home22266140.1and1-data.host';
    $remote_path = '';

    // Dateien, die übertragen werden sollen
    $files = ['/var/www/html/beste/ifak.db', '/var/www/html/beste/ifak.json'];

    foreach ($files as $file) {
        $command = "sshpass -p '$password' scp -o StrictHostKeyChecking=no $file $remote_user@$remote_host:$remote_path";
        $output = shell_exec($command);
        echo $command;
        echo "<p>Datei $file übertragen: " . $output."</p>";
    }
}
?>
<p><a href="employees.php">Zurück</a> | <a href="https:/beste.ifak-bochum.de">Beste</a></p>