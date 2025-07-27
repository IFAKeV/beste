<?php
date_default_timezone_set('Europe/Berlin');
define('FPDF_FONTPATH','font/');
define('EURO',chr(128));
require('fpdf.php');

class PDF extends FPDF {
	// Page header
	function Header() {
	    // Logo
	    $this->Image('img/IFAK-Logo.png',5,4,0,14); // IFAK e.V. Logo
	    $this->Image('img/IFAK-Kindergarten-Logo.png',33,4,0,14); // IFAK Kindergarten e.V. Logo
	    $this->Image('img/DINX-Logo.png',140,4,0,14); // Institut DINX gGmbH Logo
	    $this->Image('img/Frauenzentrum-Logo.png',60,4,0,13); // Frauenzentrum in der IFAK Logo Logo
	    $this->Image('img/InterCare-Logo.png',180,4,0,14); // InterCare Pflege GmbH Logo
	    
	    if($this->PageNo()==1) {
		    // Arial bold 15
		    $this->SetFont('Arial','B',25);
		    // Title
		    $this->Cell(200,14,'Alles IFAK',0,1,'C');
		    $this->SetFont('Arial','',12);		    
	    }
	    else
	     {
		     $this->Ln(15);
	     }	    
	}
	
	// Page footer
	function Footer() {
	    // Position at 1.5 cm from bottom
	    $this->SetY(-10);
	    // Arial italic 8
	    $this->SetFont('Arial','I',8);
	    // Page number
	    $this->Cell(80,10,'Mitarbeitendenliste IFAK-Intern',0,0,'L');
	    $this->Cell(40,10,'Seite '.$this->PageNo(),0,0,'C');
	    $this->Cell(80,10,'generiert: '.date("d.m.Y - H:i").' Uhr',0,0,'R');
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
$pdf->SetFont('Arial','',10);
global $db;
global $einrichtungenlang;
$order = "ORDER by SortedLastName COLLATE NOCASE asc";
$employeequery = "SELECT * FROM Employees ".$order;
$results = $db->query($employeequery);
while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
	unset($f);
	$employeeid = $row['EmployeeID'];
	

	//Mailadresse zu lang? Schriftgröße reduzieren!
	$size='10';
	$breite=$pdf->GetStringWidth($row['FirstName'].' '.$row['LastName']);
	while($breite>'40') {
		$size=$size-0.5;
		$pdf->SetFont('Arial','',$size);
		$breite=$pdf->GetStringWidth($row['FirstName'].' '.$row['LastName']);
	}
	$pdf->Cell(40,5,$row['FirstName'].' '.$row['LastName'],1,0,'L');
	$pdf->SetFont('Arial','',10);


	$facilityquery = "SELECT * FROM FacilityLinks WHERE EmployeeID = ".$employeeid;
	$facilityresult = $db->query($facilityquery);
	$f = []; // Array vor der Schleife initialisieren
	while ($facilities = $facilityresult->fetchArray(SQLITE3_ASSOC)) {
		$f[] = $einrichtungenlang[$facilities['FacilityID']];
	}
	if($f) $f2 = implode(" / ", $f);
	else $f2 = "";


	//Mailadresse zu lang? Schriftgröße reduzieren!
	$size='10';
	$breite=$pdf->GetStringWidth($f2);
	while($breite>'55') {
		$size=$size-0.5;
		$pdf->SetFont('Arial','',$size);
		$breite=$pdf->GetStringWidth($f2);
	}
	$pdf->Cell(55,5,$f2,1,0,'L');
	$pdf->SetFont('Arial','',10);



	$pdf->Cell(28,5,$row['Mobile'],1,0,'L');
	$pdf->Cell(28,5,$row['Phone'],1,0,'L');
	//Mailadresse zu lang? Schriftgröße reduzieren!
	$size='10';
	$breite=$pdf->GetStringWidth($row['Mail']);
	while($breite>'50') {
		$size=$size-0.5;
		$pdf->SetFont('Arial','',$size);
		$breite=$pdf->GetStringWidth($row['Mail']);
	}
	$pdf->Cell(50,5,$row['Mail'],1,1,'L');
	$pdf->SetFont('Arial','',10);
}
$pdf->Output('I',"IFAK-Mitarbeitendenliste-".date("Ymd").".pdf");
?>