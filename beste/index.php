<?php
	if (isset($_GET['main'])) $main=$_GET['main'];
	else $main = "home";
	if($main=="phonelist" || $main=="vcf" || $main=="vcf21" || $main=="list" || $main=="fritzboxxml") {
		include_once "init.php";
		include_once "functions.php";
		include $main.'.php';
	}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bestes IFAK Adressbuch</title>
    <link rel="shortcut icon" href="favicon.ico">
    <link rel="stylesheet" href="styles.css?v=12">
</head>
<body>
    <header>
        <div class="container">
            <div class="header">
                <!-- IFAKONTAKT Text -->
                <div class="logo">
                    <h1 id="logoText" class="logo-text">
                        <span id="word1" class="word1-color">FRIENDLY</span>
                        <a href="index.php"><span class="ifak-color">IFAK</span></a>
                        <span id="word2" class="word2-color">FAMILY</span>
                    </h1>
                </div>
                <!-- Suchfeld und Filter -->
                <input type="text" class="search-bar" placeholder="Suche...">
                <select class="filter-options" id="filterType">
                    <option value="all">Alle anzeigen</option>
                    <option value="person">Personen</option>
                    <option value="facility">Einrichtungen</option>
                    <option value="location">Standorte</option>
                </select>
            </div>
        </div>
    </header>

    <!-- Hauptinhalt -->
    <div class="content">
        <div class="container">
            <div id="currentFilter" style="display: none;"></div>
            <div class="mosaic" id="dataMosaic"></div>
        </div>
    </div>

    <!-- Modal -->
    <div id="detailModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <div id="modalContent"></div>
        </div>
    </div>

    <footer>
	    <div class="top-row">
	        <div class="left">
	            <?php
	                $dbfile = "ifak.json";
	                if (file_exists($dbfile)) {
	                    echo "Stand: " . date("d. F Y H:i:s.", filemtime($dbfile));
	                }
	            ?>
	        </div>
	        <div class="right">
	            Export: <a href="<?php echo $_SERVER['PHP_SELF']; ?>?main=phonelist">Telefonliste</a> / <a href="<?php echo $_SERVER['PHP_SELF']; ?>?main=phonelist&amp;show=all">Telefonliste (lang)</a> / <a href="<?php echo $_SERVER['PHP_SELF']; ?>?main=list">Mitarbeitendenliste</a>
	        </div>
	    </div>
        <div class="logo-container">
	        <a target="_blank" href="https://ifak-bochum.de"><img src="img/IFAK-Logo.svg" alt="IFAK e.V." class="company-logo"></a>
	        <a target="_blank" href="https://ifak-bochum.de/fachbereich-fruehkindliche-bildung/"><img src="img/IFAK-Kindergarten-Logo.png" alt="IFAK Kindergarten e.V." class="company-logo"></a>
	        <a target="_blank" href="https://frauenzentrum-dortmund.de"><img src="img/Frauenzentrum-Logo.png" alt="Frauenzentrum Dortmund" class="company-logo"></a>
	        <a target="_blank" href="https://institut-dinx.de"><img src="img/DINX-Logo.png" alt="Institut DINX" class="company-logo"></a>
	        <a target="_blank" href="https://intercare-pflege.de"><img src="img/InterCare-Logo.png" alt="Intercare GmbH" class="company-logo"></a>
		</div>
    </footer>
    <script src="script.js?v=9"></script>
    <script>
        // Fokussiert das Suchfeld, sobald die Seite geladen ist
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelector('.search-bar').focus();
        });
    </script>
</body>
</html>