<?php
date_default_timezone_set('Europe/Berlin');
define('FPDF_FONTPATH','font/');
define('EURO',chr(128));
require('tfpdf.php');
require('tfpdf_eps.php');
$pwcount=1;
class PDF extends EPSPDF {
	// Page footer
	function Footer() {
	    // Position at 1.5 cm from bottom
	    $this->SetY(-15);
	    // Arial italic 8
	    $this->SetFont('DejaVu','',8);
	    // Page number
	    $this->Cell(80,5,'Zugangsdaten / IFAK e.V. / '.$_POST['Name'],'T',0,'L');		
// 	    $this->Cell(20,5,'Seite '.$this->PageNo(),'T',0,'C');
        $this->Cell(20,5,'Seite '.$this->PageNo().'/{nb}','T',0,'C');
	    $this->Cell(80,5,'generiert: '.date("d.m.Y - H:i"),'T',0,'R');
	}
}

$pdf = new PDF();
$pdf->AddFont('DejaVu','','DejaVuSans.ttf',true);
$pdf->AddFont('DejaVu','B','DejaVuSans-Bold.ttf',true);
$pdf->AddFont('DejaVu','I','DejaVuSansCondensed.ttf',true);
$pdf->AliasNbPages();
$pdf->AddPage('P','A4');
$pdf->ImageEps('img/ifak-logo.ai', 160, 30, 40);
$pdf->SetFont('DejaVu','B',15);
$pdf->Cell(0,10,'Zugangsdaten & (Kurz)Anleitung',1,1,'C');
$pdf->Ln(5);

$pdf->SetFont('DejaVu','B',12);
$pdf->Write(10,'EMail-Adresse');
$pdf->Ln();
$pdf->SetFont('DejaVu','',10);
$breite=$pdf->GetStringWidth($_POST['Mail']);
$pdf->Cell($breite+5,6,$_POST['Mail'],0,0);
$pdf->Cell(70,6,$_POST['mailpassword'],0,1);
$pdf->Ln(5);

if($_POST['MicrosoftKonto']!="" && $_POST['microsoftpassword']!="") {
	$pdf->SetFont('DejaVu','',10);
	if($pdf->GetStringWidth($_POST['MicrosoftKonto'])>$breite) $breite=$pdf->GetStringWidth($_POST['MicrosoftKonto']);
	$pdf->SetFont('DejaVu','B',12);
	$pdf->Write(10,'Microsoft-Konto');
	$pdf->Ln();
	$pdf->SetFont('DejaVu','',10);
	$pdf->Cell($breite+5,6,$_POST['MicrosoftKonto'],0,0);
	$pdf->Cell(70,6,$_POST['microsoftpassword'],0,1);
	$pdf->Ln(5);
	$pwcount++;
}

if($_POST['GoogleAccount']!="" && $_POST['googlepassword']!="") {
	$pdf->SetFont('DejaVu','',10);
	if($pdf->GetStringWidth($_POST['GoogleAccount'])>$breite) $breite=$pdf->GetStringWidth($_POST['GoogleAccount']);
	$pdf->SetFont('DejaVu','B',12);
	$pdf->Write(10,'Google-Konto');
	$pdf->Ln();
	$pdf->SetFont('DejaVu','',10);
	$pdf->Cell($breite+5,6,$_POST['GoogleAccount'],0,0);
	$pdf->Cell(70,6,$_POST['googlepassword'],0,1);
	$pdf->Ln(5);
	$pwcount++;
}

if($_POST['SecondMail']!="" && $_POST['secondmailpassword']!="") {
	$pdf->SetFont('DejaVu','',10);
	if($pdf->GetStringWidth($_POST['SecondMail'])>$breite) $breite=$pdf->GetStringWidth($_POST['SecondMail']);
	$pdf->SetFont('DejaVu','B',12);
	$pdf->Write(10,'Weitere EMail-Adresse');
	$pdf->Ln();
	$pdf->SetFont('DejaVu','',10);
	$pdf->Cell($breite+5,6,$_POST['SecondMail'],0,0);
	$pdf->Cell(70,6,$_POST['secondmailpassword'],0,1);
	$pdf->Ln(5);
	$pwcount++;
}
if($pwcount>1) $pdf->MultiCell(0,4,"Diese Passwörter sind als Vorschläge zu verstehen. Sie können geändert werden, müssen aber nicht.",0,'J');
else $pdf->MultiCell(0,4,"Dieses Passwort ist als Vorschlag zu verstehen. Es kann geändert werden, muss aber nicht.",0,'J');
$pdf->Ln();

if($_POST['GoogleAccount']!="" && $_POST['googlepassword']!="") {
	$pdf->SetFont('DejaVu','B',12);
	$pdf->Write(10,'Smartphone-Einrichtung');
	$pdf->Ln();
	$pdf->SetFont('DejaVu','',10);
	$pdf->MultiCell(0,4,"Die Einrichtung des Telefons erfolgt mit oben stehendem Google-(Workplace)-Konto. Das Konto ermöglicht den Zugriff auf weitere Google Apps wie Drive, Docs, Tabellen, Präsentationen, Notizen, Kalender, Meet und mehr. Auch eine Freigabe von Dateien und Terminen ist auf diesem Wege (sehr einfach) möglich.\nFür die Nutzung der Dienst-Emails (meist name@ifak-bochum.de) ist die „1&1 Mail“-App aus dem Playstore zu installieren. Nach Einrichten des Mail-Kontos dort bitte über die Einstellungen der App den eigenen Namen korrekt setzen und die (Werbe)Signatur personalisieren oder zumindest löschen. Wer mehr als eine Mailadresse lesen muss, kann diese alle in der 1&1 Mail-App einrichten.",0,'J');
	$pdf->SetFont('DejaVu','B',10);
	$pdf->MultiCell(0,4,"Es müssen keine weiteren Konten eingerichtet werden!",0,'J');	
	$pdf->SetFont('DejaVu','',10);
	$pdf->MultiCell(0,4,"Insbesondere Samsung-Telefone drängen bei der Einrichtung dazu ein Samsung-Konto anzulegen. Dieses ist nicht erfoderlich und daher zu unterlassen.",0,'J');
	$pdf->Ln();

	$pdf->SetFont('DejaVu','B',12);
	$pdf->Write(10,'Hinweise zur Rückgabe des Telefons');
	$pdf->Ln();
	$pdf->SetFont('DejaVu','',10);
	$pdf->MultiCell(0,4,"Wenn das Telefon zurückgegeben wird, bitte in jedem Fall Sicherheitssperren wie Wischgesten oder Gerätepins entfernen! Idealerweise ist das Google-Konto zu entfernen und das Handy auf Werkseinstellungen zurückzusetzen.",0,'J');
	$pdf->Ln();

	$pdf->SetFont('DejaVu','B',12);
	$pdf->Write(10,'Hinweise zu einem Wechsel des Telefons');
	$pdf->Ln();
	$pdf->SetFont('DejaVu','',10);
	$pdf->MultiCell(0,4,"Wird das Telefon, z.B. aufgrund eines Defektes, durch ein anderes ersetzt, synchronisieren sich z.B. die Kontakte, Photos und je nach Telefon auch die SMS über das Google-Konto, sofern dort durch entsprechende Sync/Backup-Einstellungen zuvor gesetzt wurden. Werden jegliche Sync- und Backup-Funktionen des Google-Kontos deaktiviert, sind entsprechende Daten nach einem Geräteverlust oder Totaldefekt ggf. ebenfalls verloren. EMails gehen keine Verloren. Das Mailkonto muss nur neu eingerichtet werden.\nDie Übertragung eines WhatsApp-Kontos ist hier beschrieben:\nhttps://faq.whatsapp.com/android/chats/how-to-restore-your-chat-history\nDie Übertragung eines Signal-Kontos ist hier beschrieben:\nhttps://support.signal.org/hc/de/articles/360007059752-Nachrichten-sichern-und-wiederherstellen\nDie Nutzung dieser (oder anderer) Messenger ist verbreitet und zum Teil notwendig, jedoch nicht offiziell.",0,'J');
	$pdf->Ln();
}

if($_POST['MicrosoftKonto']!="" && $_POST['microsoftpassword']!="") {
	$pdf->SetFont('DejaVu','B',12);
	$pdf->Write(10,'Willkommen in der 365-Welt');
	$pdf->Ln();
	$pdf->SetFont('DejaVu','',10);
	$pdf->MultiCell(0,4,"Das MicrosoftKonto ist deine Mailadresse und die Eintrittskarte in die komplette 365 Welt. Microsoft verspricht es sei eine wunderbare Erfahrung mit Teams, Shaprepoint und den klassischen Office-Produkten, die auf diese Weise Kollaboratives Arbeiten ermöglichen.",0,'J');
	$pdf->Ln();
}

$pdf->SetFont('DejaVu','B',12);
$pdf->Write(10,'WLAN');
$pdf->Ln();
$pdf->SetFont('DejaVu','',10);
$pdf->MultiCell(0,4,"In vielen Einrichtungen gibt es ein (Gäste)WLAN mit dem Namen „bonvena“.\nDas Passwort lautet „bepoliteandrespectful“.\nDer Zugang zum Dienst-WLAN „IFAK“ ist auf Laptops und anderen Geräten, die ihn benötigen, bereits hinterlegt.\nWer unterwegs mit seinem Laptop Internetzugriff benötigt, kann dazu den WLAN-Hotspot des Handys nutzen. Einmal eingerichtet, ist dieser mit nur einer Wischgeste und einem Fingertap einfach und schnell zu aktivieren.",0,'J');
$pdf->Ln();

$pdf->SetFont('DejaVu','B',12);
$pdf->Write(10,'EMail-Zugriff (@ifak-bochum.de)');
$pdf->Ln();
$pdf->SetFont('DejaVu','',10);
$pdf->MultiCell(0,4,"Es gibt drei übliche Zugriffswege auf das dienstliche Mailkonto bzw. die dienstlichen Mailkonten.\n1. Das Webmailinterface: https://mail.ionos.de/\nHier wird auch die Abwesenheitsnotiz (z.B: bei Urlaub) gesetzt, es können Einstellungen am Spamfilter vorgenommen werden und die Änderung des Passworts passiert hier.\n2. Per „1&1 Mail“-App auf dem Smartphone\n3. Über den Thunderbird-EMail Client (für MitarbeiterInnen mit persönlichem PC Login oder eigenem Laptop)\nEMail-Signaturen müssen leider jeweils eingerichtet werden.",0,'J');
$pdf->Ln();

$pdf->SetFont('DejaVu','B',12);
$pdf->Write(10,'EMail-Zugriff (@ifak-sozial.de)');
$pdf->Ln();
$pdf->SetFont('DejaVu','',10);
$pdf->MultiCell(0,4,"@ifak-sozial.de Mails werden in Outlook gelesen. Auf dem Desktop, per gleichnamiger App auf dem Telefon oder in der Web-Variante im Browser auf https://outlook.office.com/ .",0,'J');
$pdf->Ln();

$pdf->SetFont('DejaVu','B',12);
$pdf->Write(10,'Software');
$pdf->Ln();
$pdf->SetFont('DejaVu','',10);
$pdf->MultiCell(0,4,"Auf Desktop-Rechnern und Laptops ist üblicherweise ein Office-Paket (Word, Excel, Powerpoint) installiert. Als Browser zum surfen im Internet verwenden wir noch Firefox in der ESR Version. Mit dem Wechsel auf 365 wird es Edge werden. EMails lesen wir in noch Thunderbird und schon in Outlook, je nachdem ob du @ifak-bochum.de oder @ifak-sozial.de bist und PDF-Dateien betrachten wir mit dem Foxit-Reader. 7Zip ist installiert um alle Arten von Archiven öffnen zu können und auf dem Desktop findet sich eine Verknüpfung zum TeamViewerQS für einen Fernwartungszugriff. Sollte davon etwas auf einem älteren Bestandrechner nicht vorhanden sein, könnt ihr das gern kurz melden. Software, die sich bewusst im Userspace installiert, wie z.B. zoom oder MS Teams, könnt ihr selbsttätig installieren. Die Systemweite Installation von zusätzlicher Software erfolgt in Absprache und durch die IT.",0,'J');
$pdf->Ln();

$pdf->SetFont('DejaVu','B',12);
$pdf->Write(10,'Adressbuch');
$pdf->Ln();
$pdf->SetFont('DejaVu','',10);
$pdf->MultiCell(0,4,"Die IFAK beschäftigt (Stand Sommer 2025) in allen Projekten, Einrichtungen und Kindergärten bald 500 MitarbeiterInnen an über 40 Betriebsstätten. Es gibt zwei Adressbücher, die dabei helfen sollen, dass ihr euch doch gegenseitig findet, obwohl inzwischen nicht mehr jede jede kennen kann.\n1. Die Zentralen Dienste - EDV pflegen ein Thunderbird Adressbuch, welches per Sync-Plugin über das Internet bei jedem Start eures Thunderbird Clients lokal aktualisiert wird. Das funktioniert überwiegend Zufriedenstellend und auch Problemlos. Leider hat das Plugin in der Vergangenheit hier und da schon mal ein Update des Clients nicht überlebt und war danach nicht mehr aktiv. Das lässt sich aber schnell ggf. per Fernwartung lösen bzw. erneut aktivieren.\n2. Es gibt ein Online-Adressbuch, welches technisch gesehen eine Webseite ist. Diese ist per https://beste.ifak-bochum.de zu erreichen.\nFür PapierliebhaberInnen generiert es auch Telefonlisten in Form von PDF-Dateien, welche sich zum Ausdruck eignen.",0,'J');
$pdf->Ln();

$pdf->SetFont('DejaVu','B',12);
$pdf->Write(10,'Fragen, Anregungen, Hinweise und gefundene Fehler');
$pdf->Ln();
$pdf->SetFont('DejaVu','',10);
$pdf->MultiCell(0,4,"Jederzeit und gerne an Rafael: 0157/76216984 oder edv@ifak-bochum.de",0,'J');
$pdf->Ln();

$dateiname = date("Ymd")."-Zugangsdaten-".preg_replace('/[^a-zA-Z0-9]/','_',strtolower(umlauteumwandeln($_POST['Name']))).'.pdf';
$pdf->Output('I',$dateiname);
?>