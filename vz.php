<?php
$EmployeeID = $_GET['EmployeeID'] ?? '';

if ($EmployeeID !== '' && !ctype_digit($EmployeeID)) {
    die('Ungültige EmployeeID');
}

$line = ['FirstName' => '', 'LastName' => '', 'Mail' => ''];

if ($EmployeeID !== '') {
    $stmt = $db->prepare('SELECT * FROM Employees WHERE EmployeeID = :id LIMIT 1');
    $stmt->bindValue(':id', intval($EmployeeID), SQLITE3_INTEGER);
    $userresult = $stmt->execute();

    if (!$userresult) {
        die('DB-Fehler: '.$db->lastErrorMsg());
    }

    $row = $userresult->fetchArray(SQLITE3_ASSOC);
    if ($row) {
        $line = $row;
    }
}

?>
<div class="ifak-formular-container">	
	<form id="zugangsdatenformular" class="ifak-formular" action="<?php echo $_SERVER['PHP_SELF']; ?>?main=zugangsdaten_pdf" method="post">
	  <fieldset>
	    <h1>Zugangsdaten</h1>
	
	    <input type="hidden" name="EmployeeID" value="<?php echo $EmployeeID; ?>">
	
	    <div class="form-block">
	    <div class="form-row">
	      <label for="name">Name:</label>
	      <input type="text" id="name" name="Name" value="<?php echo $line['FirstName'].' '.$line['LastName']; ?>">
	    </div>
	    </div>
		
		<div class="form-block">
	    <div class="form-row">
	      <label for="mail">E-Mail:</label>
	      <input type="text" id="mail" name="Mail" value="<?php echo $line['Mail']; ?>">
	    </div>

	    <div class="form-row">
	      <label for="mailpassword">Passwort (Mail):</label>
	      <input type="text" id="mailpassword" name="mailpassword">
	    </div>
		</div>
		
		<div class="form-block">
	    <div class="form-row">
	      <label for="microsoft">Microsoft-Konto:</label>
	      <input type="text" id="microsoft" name="MicrosoftKonto" value="<?php echo str_replace('@ifak-bochum.de', '@ifak-sozial.de', $line['Mail']); ?>">
	    </div>
	
	    <div class="form-row">
	      <label for="microsoftpassword">Passwort (Microsoft):</label>
	      <input type="text" id="microsoftpassword" name="microsoftpassword">
	    </div>
		</div>
	
		<div class="form-block">
	    <div class="form-row">
	      <label for="google">Google-Account:</label>
	      <input type="text" id="google" name="GoogleAccount" value="<?php echo str_replace('@ifak-bochum.de', '@bildungsnetz-nrw.de', $line['Mail']); ?>">
	    </div>
	
	    <div class="form-row">
	      <label for="googlepassword">Passwort (Google):</label>
	      <input type="text" id="googlepassword" name="googlepassword">
	    </div>
		</div>
	
		<div class="form-block">
	    <div class="form-row">
	      <label for="secondmail">Weitere E-Mail:</label>
	      <input type="text" id="secondmail" name="SecondMail">
	    </div>
	
	    <div class="form-row">
	      <label for="secondmailpassword">Passwort (weitere E-Mail):</label>
	      <input type="text" id="secondmailpassword" name="secondmailpassword">
	    </div>
		</div>
	
<!-- 	    <button type="submit">Zugangsdaten drucken</button> -->
	    
	    
	    <div style="text-align: right;">
  <button type="submit">Zugangsdaten drucken</button>
</div>
	  </fieldset>
	</form>
	

	<form id="vereinbarungsformular" class="ifak-formular" action="<?php echo $_SERVER['PHP_SELF']; ?>?main=vereinbarung_pdf" method="post">
		<fieldset id="Vereinbarung">
			<h1>Vereinbarung</h1>
			<h3>zur Überlassung von Arbeitsmitteln</h3>
			
			<div class="form-block">
				<div class="form-row">
			      <label for="name">Name:</label>
			      <input type="text" id="name" name="Name" value="<?php echo $line['FirstName'].' '.$line['LastName']; ?>">
				</div>
		    </div>
			
		    <div id="arbeitsmittel-container">
			
			    <div class="arbeitsmittel-gruppe form-block">
					<div class="form-row">
						<label for="arbeitsmittel">Arbeitsmittel:</label>
						<input type="text" name="arbeitsmittel[]" placeholder="Arbeitsmittel">
		    		</div>
		    		<div class="form-row">
						<label for="hersteller">Hersteller/Typ:</label>
						 <input type="text" name="hersteller[]" placeholder="Hersteller/Typ">
		    		</div>
					<div class="form-row">
						<label for="seriennummer">Seriennummer:</label>
						<input type="text" name="seriennummer[]" placeholder="Seriennummer">
		    		</div>
					<div class="form-row">
						<label for="kennzeichnung">Kennzeichnung:</label>
						<input type="text" name="kennzeichnung[]" placeholder="Kennzeichnung">
		    		</div>
	            </div>
            </div>
            <div class="form-block">
                <div class="form-row">
                        <input type="checkbox" id="sim_check" name="sim_check" onclick="toggleSimFields()">
                        <label for="sim_check">mit SIM-Karte</label>
                </div>
                <div id="sim_fields" style="display:none;">
					<div class="form-block">
	                    <div class="form-row">
	                            <label for="sim_phone">Telefonnummer:</label>
	                            <input type="text" id="sim_phone" name="sim_phone">
	                    </div>
	                    <div class="form-row">
	                            <label for="sim_pin">PIN:</label>
	                            <input type="text" id="sim_pin" name="sim_pin">
	                    </div>
					</div>
                    <div class="form-row">
                            <input type="checkbox" id="sim_export" name="sim_export" value="1">
                            <label for="sim_export">In Adressbuch übernehmen</label>
                    </div>
                </div>
            </div>
			<div style="display: flex; justify-content: space-between; gap: 1rem; margin-top: 1rem;">
			    <button type="button" onclick="addArbeitsmittel()">+ weiteres Arbeitsmittel</button>
			    <button type="submit">Vereinbarung drucken</button>
			</div>
        </fieldset>
    </form>
</div>

<script>
        function addArbeitsmittel() {
  const tmpl = document.querySelector('.arbeitsmittel-gruppe');
  const clone = tmpl.cloneNode(true);
  // Leere Felder
  clone.querySelectorAll('input').forEach(i => i.value = '');
  document.getElementById('arbeitsmittel-container').appendChild(clone);
}

function toggleSimFields() {
  const check = document.getElementById('sim_check');
  const fields = document.getElementById('sim_fields');
  fields.style.display = check.checked ? 'block' : 'none';
}
</script>