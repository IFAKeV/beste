<?php
date_default_timezone_set('Europe/Berlin');
define('FPDF_FONTPATH','font/');
define('EURO',chr(128));
require('tfpdf.php');
require('tfpdf_eps.php');

class PDF extends EPSPDF {
    protected $col = 0; // Current column
    protected $y0;      // Ordinate of column start

    // Page footer
    function Footer() {
        $this->SetY(-15);
        $this->SetFont('DejaVu','',8);
        $this->Cell(80, 5, 'Vereinbarung Arbeitsmittel / IFAK e.V. / ' . ($_POST['Name'] ?? ''), 'T', 0, 'L');
        $this->Cell(20,5,'Seite '.$this->PageNo().'/{nb}','T',0,'C');
        $this->Cell(80,5,'generiert: '.date("d.m.Y - H:i"),'T',0,'R');
    }
    
    function SetCol($col) {
        $this->col = $col;
        // Breitere Spalten: 70px statt 65px
        $x = 10 + $col * 61;
        $this->SetLeftMargin($x);
        $this->SetX($x);
    }
    
    function AcceptPageBreak() {
        if($this->col < 2) {
            $this->SetCol($this->col+1);
            $this->SetY($this->y0);
            return false;
        } else {
            $this->SetCol(0);
            return true;
        }
    }
    
    function ChapterBody($fichier) {
        $f=fopen($fichier,'r');
        $txt=fread($f,filesize($fichier));
        fclose($f);
        
        // Text in Paragraphen aufteilen
        $paragraphs = explode("\n\n", $txt);
        
        foreach($paragraphs as $paragraph) {
            $paragraph = trim($paragraph);
            if(empty($paragraph)) continue;
            
            // Prüfen ob Paragraph beginnt
//             if(preg_match('/^(§\d+)\s+(.*)/', $paragraph, $matches)) {
            if (preg_match('/^(§\d+|Anhang:)\s+(.*)/', $paragraph, $matches)) {
                // §-Überschrift FETT
                $this->SetFont('DejaVu','B',7);
                $this->MultiCell(57.5, 4, $matches[1] . " " . $matches[2]);
                $this->Ln(1);
                $this->SetFont('DejaVu','',7);
            } else {
                // Normaler Text
                $this->SetFont('DejaVu','',7);
                $this->MultiCell(57.5, 3, $paragraph);
                $this->Ln(3);
            }
        }
        
        $this->SetCol(0);
    }
    
    function PrintChapter($file) {
	    $this->SetCol(0);
        // WICHTIG: y0 explizit setzen!
        $this->y0 = $this->GetY();
        $this->ChapterBody($file);
    }
}

$pdf = new PDF();
$pdf->AddFont('DejaVu','','DejaVuSans.ttf',true);
$pdf->AddFont('DejaVu','B','DejaVuSans-Bold.ttf',true);
$pdf->AddFont('DejaVu','I','DejaVuSansCondensed.ttf',true);
$pdf->AliasNbPages();
$pdf->AddPage('P','A4');
$pdf->SetLeftMargin(20);
$pdf->ImageEps('img/ifak-logo.ai', 160, 30, 40);
$pdf->SetFont('DejaVu','B',15);
$pdf->Cell(0,10,'Vereinbarung zur Überlassung von Arbeitsmitteln',1,1,'C');
$pdf->Ln(5);
$pdf->SetFont('DejaVu','B',10);
$pdf->Write(4,'Zwischen');
$pdf->Ln(8);
$pdf->SetFont('DejaVu','',10);
$pdf->Write(4,"IFAK e.V.\nVerein für multikulturelle Kinder- u. Jugendhilfe – Migrationsarbeit\nEngelsburger Str. 168\n44793 Bochum");
$pdf->Ln();
$pdf->Cell(90,5,' - im folgenden Arbeitgeber -','T',1);
$pdf->SetFont('DejaVu','B',10);
$pdf->Ln(4);
$pdf->Write(4,'und');
$pdf->Ln(8);
$pdf->SetFont('DejaVu','B',10);
$pdf->Write(4, $_POST['Name'] ?? '');
$pdf->SetFont('DejaVu','',10);
$pdf->Ln();
$pdf->Cell(90,5,' - im folgenden Arbeitnehmerin -','T',1);
$pdf->SetFont('DejaVu','B',10);
$pdf->Ln(4);
$pdf->Write(4,'werden nachfolgende Vereinbarungen getroffen:');
$pdf->Ln(4);
$pdf->SetFont('DejaVu','B',12);
$pdf->Write(10,'§ 1 Überlassene Arbeitsmittel');
$pdf->Ln();
$pdf->SetFont('DejaVu','',10);
$pdf->MultiCell(180,4,'Der Arbeitgeber stellt der Arbeitnehmerin das im Folgenden näher bezeichnete Arbeitsmittel (Handy, Computer, Laptop, Internetzugang usw.) zur Verfügung.',0,'J');
$pdf->Ln();

for ($i = 0; $i <= 4; $i++) {
    $am = $_POST['arbeitsmittel'][$i] ?? '';
    $hs = $_POST['hersteller'][$i] ?? '';
    $sn = $_POST['seriennummer'][$i] ?? '';
    $kn = $_POST['kennzeichnung'][$i] ?? '';
    if($am !== '' || $hs !== '' || $sn !== '' || $kn !== '') {
        $pdf->SetFont('DejaVu','B',10);
        $pdf->Cell(46,6,'Arbeitsmittel:',1,0);
        $pdf->SetFont('DejaVu','',10);
        $pdf->Cell(0,6,$_POST['arbeitsmittel'][$i],1,1);
        $pdf->Cell(46,6,'Hersteller/Typbezeichnung:',1,0);
        $pdf->Cell(0,6,$_POST['hersteller'][$i],1,1);
        $pdf->Cell(46,6,'Geräte/Seriennummer:',1,0);
        $pdf->Cell(0,6,$_POST['seriennummer'][$i],1,1);
        $pdf->Cell(46,6,'Sonstige Kennzeichnung:',1,0);
        $pdf->Cell(0,6,$_POST['kennzeichnung'][$i],1,1);
        if($i<3) $pdf->Ln();
    }
}

if (!empty($_POST['sim_phone'])) {
    $pdf->Ln(5);
    $pdf->SetFont('DejaVu','B',10);
    $pdf->Cell(0,6,'SIM-Karte:',0,1);
    $pdf->SetFont('DejaVu','',10);
    $pdf->Cell(46,6,'Rufnummer:',0,0);
    $pdf->Cell(0,6,$_POST['sim_phone'],0,1);
    $pdf->Cell(46,6,'PIN:',0,0);
    $pdf->Cell(0,6,$_POST['sim_pin'] ?? '',0,1);
}

$pdf->Ln(20);
$pdf->Cell(100,4,'Bochum, den '.date("d.m.Y"),0,0);
$pdf->Cell(60,4,'',0,1);
$pdf->Cell(100,6,'',0,0);
$pdf->Cell(60,6,'Arbeitnehmerin','T',1,'C');
$pdf->Cell(90,3,'',0,0);
$pdf->SetFont('DejaVu','',7);
$pdf->Cell(80,3,'Mit meiner Unterschrift bestätige ich die Kenntnisnahme der umseitigen Regelungen',0,1,'C');

$pdf->AddPage();
$pdf->SetLeftMargin(10);

$pdf->PrintChapter('vereinbarung.txt');

$simExport = !empty($_POST['sim_export']);
if ($simExport && !empty($_POST['sim_phone'])) {
    $entry = [
        'name' => $_POST['Name'] ?? '',
        'phone' => $_POST['sim_phone'],
        'pin' => $_POST['sim_pin'] ?? '',
        'timestamp' => date('c')
    ];
    $jsonFile = 'simcards.json';
    $jsonData = [];
    if (file_exists($jsonFile)) {
        $content = file_get_contents($jsonFile);
        $jsonData = json_decode($content, true);
        if (!is_array($jsonData)) {
            $jsonData = [];
        }
    }
    $jsonData[] = $entry;
    file_put_contents($jsonFile, json_encode($jsonData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
}

$name = $_POST['Name'] ?? '';
$dateiname = date("Ymd")."-Vereinbarung_Arbeitsmittel-".preg_replace('/[^a-zA-Z0-9]/','_', strtolower(umlauteumwandeln($name))).'.pdf';
$pdf->Output('I',$dateiname);
?>