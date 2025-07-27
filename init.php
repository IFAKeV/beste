<?php
@error_reporting(E_ALL ^ E_NOTICE);
setlocale (LC_ALL, 'de_DE');
$sprachlevel=array('-','A1','A2','B1','B2','C1','C2');
date_default_timezone_set('Europe/Berlin');
$dbfile="ifak.db";
$umlautefueralphabetischesortierung=Array("Ä" => "AE", "Ö" => "OE", "Ü" => "UE", "ä" => "ae", "ö" => "oe", "ü" => "ue", "ß" => "ss"); 
$db = new SQLite3($dbfile);
$results = $db->query("SELECT * FROM Languages ORDER BY LanguageName ASC");
while ($row = $results->fetchArray()) {
	$sprachen[$row["LanguageID"]] = $row["LanguageName"];
}
// Einrichtungen
$results = $db->query("SELECT * FROM Facilities ORDER BY Facility COLLATE NOCASE ASC");
while ($row = $results->fetchArray()) {
	$einrichtungen[$row["FacilityID"]] = $row["Facility"];
	$einrichtungenlang[$row["FacilityID"]] = $row["Long"];
	$url[$row["FacilityID"]] = $row["URL"];
	if($row["URL"]) $einrichtungenlangggfmiturl[$row["FacilityID"]] = '<a target="_blank" href="'.$row["URL"].'">'.$row["Long"].'</a>';
	else $einrichtungenlangggfmiturl[$row["FacilityID"]] = $row["Long"];
}
// Locations
$results = $db->query("SELECT * FROM Locations ORDER BY Location COLLATE NOCASE ASC");
while ($row = $results->fetchArray()) {
	$locations[$row["LocationID"]] = $row["Location"];
	$locationslang[$row["LocationID"]] = $row["Long"];
}
// Sprachen
$results = $db->query("SELECT * FROM Languages ORDER BY LanguageName COLLATE NOCASE ASC");
while ($row = $results->fetchArray()) {
	$sprachen[$row["LanguageID"]] = $row["LanguageName"];
}
// Wieviele Sprachen? 
$result = $db->query("SELECT count(*) FROM Languages");
while ($number = $result->fetchArray(SQLITE3_ASSOC)) {
	$sprachanzahl=$number['count(*)'];
}
// Wieviele Kollegen insgesamt?
$result = $db->query("SELECT count(*) FROM Employees");
while ($number = $result->fetchArray(SQLITE3_ASSOC)) {
// 	$overview = $number['count(*)'].' KollegInnen und '.$sprachanzahl.' Sprachen';
	$overview = $sprachanzahl.' Sprachen';
}
?>