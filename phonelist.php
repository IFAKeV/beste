<?php
date_default_timezone_set('Europe/Berlin');
define('FPDF_FONTPATH','font/');
define('EURO',chr(128));
$fiveDigitAreaCodes = ['02323', '02325', '02327', '02302'];

function formatPhoneNumber($number, $fiveDigitAreaCodes) {
    // Entferne alle nicht-numerischen Zeichen
    $number = preg_replace('/\D+/', '', $number);
    
    // Stelle sicher, dass die Nummer lang genug ist
    if (strlen($number) < 4) {
        // Rückgabe der ursprünglichen Nummer, wenn sie zu kurz ist
        return $number;
    }
    
    // Erhalte die ersten fünf Ziffern
    $areaCodeFiveDigits = substr($number, 0, 5);

    // Überprüfe auf fünfstellige Vorwahl
    if (in_array($areaCodeFiveDigits, $fiveDigitAreaCodes)) {
        $areaCode = $areaCodeFiveDigits;
        $restNumber = substr($number, 5);
    } else {
        // Vorwahl ist vierstellig
        $areaCode = substr($number, 0, 4);
        $restNumber = substr($number, 4);
    }
    
    // Setze die formatierte Nummer zusammen
    $formattedNumber = $areaCode . '-' . $restNumber;
    
    return $formattedNumber;
}

require('fpdf.php');

class PDF extends FPDF {
	// Page header
	function Header() {	    
	    if($this->PageNo()==1) {
		    // Logos
		    $this->Image('img/IFAK-Logo.png',5,3,0,14); // IFAK e.V. Logo
		    $this->Image('img/IFAK-Kindergarten-Logo.png',33,3,0,14); // IFAK Kindergarten e.V. Logo
		    $this->Image('img/DINX-Logo.png',140,3,0,14); // Institut DINX gGmbH Logo
		    $this->Image('img/Frauenzentrum-Logo.png',60,3,0,13); // Frauenzentrum in der IFAK Logo Logo
		    $this->Image('img/InterCare-Logo.png',180,3,0,14); // InterCare Pflege GmbH Logo

		    // Arial bold 15
		    $this->SetFont('Arial','B',28);
		    // Title
		    $this->Cell(200,14,'Telefonliste',0,1,'C');
		    $this->SetFont('Arial','',12);		    
	    }
	    else
	     {
		     $this->Ln(0);
	     }
	}
	
	// Page footer
	function Footer() {
	    // Position at 1.5 cm from bottom
	    $this->SetY(-10);
	    // Arial italic 8
	    $this->SetFont('Arial','I',8);
	    // Page number
	    $this->Cell(80,10,'Telefonliste IFAK-Intern',0,0,'L');
	    $this->Cell(40,10,'Seite '.$this->PageNo(),0,0,'C');
	    $this->Cell(80,10,'generiert: '.date("d.m.Y - H:i"),0,0,'R');
	}

	// Umlaute gewünscht
	function Cell($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=false, $link='') {
	   	if ($txt != ''){
	    //$txt = utf8_decode($txt);
	    $txt = iconv("UTF-8", "Windows-1252//TRANSLIT", $txt); // Soll noch besser sein
	    }
		parent::Cell($w, $h, $txt, $border, $ln, $align, $fill, $link);
	}
}

$pdf = new PDF();
$pdf->SetMargins(5,4,5);
$pdf->SetAutoPageBreak(1,6);
$pdf->SetTitle('Mitarbeitendenliste der IFAK Familie',1);
$pdf->AddPage('P','A4');
$pdf->SetFont('Arial','B',10);
$pdf->SetFillColor(220,220,225);
$pdf->Cell(100,5,"Standort/Einrichtung/Projekt/Name",'TBL',0,'L',true);
/*
$pdf->Cell(30,5,"Adresse/Einrichtung",1,0,'L',true);
$pdf->Cell(28,5,"Telefon",1,0,'L',true);
$pdf->Cell(28,5,"Mobil",1,0,'L',true);
$pdf->Cell(28,5,"Fax",1,0,'L',true);
*/
$pdf->Cell(100,5,"Telefon, Mobil, ggf. Fax, Mail",'TBR',1,'R',true);
$pdf->SetFont('Arial','',10);

// Standorte
global $db;
global $einrichtungenlang;
$order = "ORDER BY SortedLong COLLATE NOCASE ASC";
$locationquery = "SELECT * FROM Locations ".$order;
$lresults = $db->query($locationquery);
while ($lrow = $lresults->fetchArray(SQLITE3_ASSOC)) {
	$pdf->SetFont('Arial','B',10);
	$pdf->SetFillColor(170,190,225);
	//Standortname zu lang? Schriftgröße reduzieren!
	$size='10';
	$breite=$pdf->GetStringWidth($lrow['Long']);
	while($breite>'100') {
		$size=$size-0.5;
		$pdf->SetFont('Arial','B',$size);
		$breite=$pdf->GetStringWidth($lrow['Long']);
	}
	$pdf->Cell(100,5,$lrow['Long'],'LBT',0,'L',true);
	$pdf->SetFont('Arial','B',10);
	$pdf->Cell(100,5,$lrow['Street'].', '.$lrow['ZIP'].' '.$lrow['Town'],'RBT',1,'R',true);
	$pdf->SetFont('Arial','',10);
	// Einrichtungen
	$fresults = $db->query("SELECT * FROM Facilities WHERE LocationID=".$lrow['LocationID']." ORDER BY SortedLong COLLATE NOCASE ASC");

	$rows = 0;
	$fresults->reset();
	while ($fresults->fetchArray()) $rows++;
	$fresults->reset();
	// $rows="";
	// return $rows;

	while ($frow = $fresults->fetchArray()) {
// 		if($rows>1) { // Das war ein Hack, der die Einrichtungsdaten nur ausgegeben hat, wenn es mehr als eine Einrichtung am Standort gab. Ich fand die doppelte Titelzeile doof. Im Hochformat geht sich das aber so nicht aus.
			$pdf->SetFillColor(200,220,255);
			$pdf->Cell(65,5,$frow['Long'],1,0,'L',true);
// 			$pdf->Cell(70,5,$frow['Street'].', '.$frow['ZIP'].' '.$frow['Town'],1,0,'L',true);
			$phoneNumber = $frow['Phone'] ?? '';
			$formattedPhone = formatPhoneNumber($phoneNumber, $fiveDigitAreaCodes);
			$pdf->Cell(28,5,$formattedPhone,1,0,'L',true);
			// Holt die Mobilnummer, falls sie vorhanden ist, ansonsten ein leerer String
			$mobile = $frow['Mobile'] ?? '';
			// Überprüft ob die Mobilnummer mindestens 4 Zeichen lang ist
			if (strlen($mobile) >= 4) {
			    // Füget nach der vierten Ziffer ein "/" ein
			    $formattedMobile = substr($mobile, 0, 4) . '-' . substr($mobile, 4);
			} else {
			    // Falls die Nummer kürzer ist, verwenden wir sie unverändert
			    $formattedMobile = $mobile;
			}
			
			// Ausgabe der formatierten Mobilnummer in der PDF-Zelle
			$pdf->Cell(28, 5, $formattedMobile, 1, 0, 'L',true);
// 			$pdf->Cell(28,5,$frow['Mobile'],1,0,'L',true);
			$faxNumber = $frow['Fax'] ?? '';
			$formattedFax = formatPhoneNumber($faxNumber, $fiveDigitAreaCodes);
			$pdf->Cell(28,5,$formattedFax,1,0,'L',true);
// 			$pdf->Cell(28,5,$frow['Fax'],1,0,'L',true);	
			//Mailadresse zu lang? Schriftgröße reduzieren!
			$size='10';
			$breite=$pdf->GetStringWidth($frow['Mail']);
			while($breite>'50') {
				$size=$size-0.5;
				$pdf->SetFont('Arial','',$size);
				$breite=$pdf->GetStringWidth($frow['Mail']);
			}	
			$pdf->Cell(51,5,$frow['Mail'],1,1,'R',true);
			$pdf->SetFont('Arial','',10);
// 		}
		$order = "ORDER by Employees.SortedLastName asc";
		$employeequery = 'SELECT * FROM Employees INNER JOIN FacilityLinks ON Employees.EmployeeID = FacilityLinks.EmployeeID WHERE FacilityLinks.FacilityID = '.$frow['FacilityID'].' '.$order;
// 		echo $employeequery.'<hr>';
		$results = $db->query($employeequery);
		while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
			$bool = false;
			if (isset($_GET['show']) && $_GET['show'] == "all") {
			    $bool = true;
			}
			if($row['Phone'] or $row['Mobile'] or $row['Mail'] or $bool) {
				unset($f);
				$employeeid = $row['EmployeeID'];
				// Farbe für Leitungen und andere wichtige Personen				
				// Farbdefinitionen zentral in einem Array
				$roleColors = [
				    1 => [255, 200, 100],   // Leitung / Standard-Orange
				    3 => [255, 200, 100],    // Vorsitzende / Standard-Orange
				    4 => [255, 200, 100],    // SGeschäftsführerin / tandard-Orange
				    2 => [255, 230, 150],  // Stellvertretende Leitung / Helleres Orange
				    // Zukünftige Werte einfach hier ergänzen
				];
				// Farbe auswählen (falls nicht vorhanden: Standardweiß)
				$color = $roleColors[$row['RoleID']] ?? [255, 255, 255]; 				
				// Farbe setzen (mit "..." werden die Array-Werte als Parameter übergeben)
				$pdf->SetFillColor(...$color);

				
				
				
				
// 				$pdf->SetFillColor(255,165,0);

				
				$pdf->Cell(65,5,$row['FirstName'].' '.$row['LastName'],1,0,'L','true');
				
				$pdf->SetFillColor(255,255,255);

				/*$facilityquery = "SELECT * FROM FacilityLinks WHERE EmployeeID = ".$employeeid;
				$facilityresult = $db->query($facilityquery);
				while ($facilities = $facilityresult->fetchArray(SQLITE3_ASSOC)) {
					$f[] = $einrichtungenlang[$facilities['FacilityID']];
				}
				if($f) $f2 = implode(" / ", $f);
				else $f2 = "";
				/* Einrichtungsauflistung zu lang? Schriftgröße reduzieren!
				$size='10';
				$breite=$pdf->GetStringWidth($f2);
				while($breite>'70') {
					$size=$size-0.5;
					$pdf->SetFont('Arial','',$size);
					$breite=$pdf->GetStringWidth($f2);
				}
				$pdf->Cell(70,5,$f2,1,0,'L');
				*/
				$pdf->SetFont('Arial','',10);
				$phoneNumber = $row['Phone'] ?? '';
				$formattedPhone = formatPhoneNumber($phoneNumber, $fiveDigitAreaCodes);
				$pdf->Cell(28,5,$formattedPhone,1,0,'L');
// 				$pdf->Cell(28,5,$row['Phone'],1,0,'L');
				// Holt die Mobilnummer, falls sie vorhanden ist, ansonsten ein leerer String
				$mobile = $row['Mobile'] ?? '';
				
				// Überprüft ob die Mobilnummer mindestens 4 Zeichen lang ist
				if (strlen($mobile) >= 4) {
				    // Füget nach der vierten Ziffer ein "/" ein
				    $formattedMobile = substr($mobile, 0, 4) . '-' . substr($mobile, 4);
				} else {
				    // Falls die Nummer kürzer ist, verwenden wir sie unverändert
				    $formattedMobile = $mobile;
				}
				
				// Ausgabe der formatierten Mobilnummer in der PDF-Zelle
				$pdf->Cell(28, 5, $formattedMobile, 1, 0, 'L');
// 				$pdf->Cell(28,5,$row['Mobile'],1,0,'L');
				// Keine Person hat eine Faxnummer
				// $pdf->Cell(28,5,$row['Fax'] ?? '',1,0,'L');
				// Mailadresse zu lang? Schriftgröße reduzieren!
				$size='10';
				$breite=$pdf->GetStringWidth($row['Mail']);
				while($breite>'78') {
					$size=$size-0.5;
					$pdf->SetFont('Arial','',$size);
					$breite=$pdf->GetStringWidth($row['Mail']);
				}	
				$pdf->Cell(79,5,$row['Mail'],1,1,'R');
				$pdf->SetFont('Arial','',10);				
			}
		}
	}
	$pdf->Ln(5);
}
if(isset($_GET['show']) && $_GET['show']=="all") $dateiname="IFAK-Telefonliste(lang)";
else $dateiname="IFAK-Telefonliste";
$pdf->Output('I',$dateiname."-".date("Ymd").".pdf");
?>