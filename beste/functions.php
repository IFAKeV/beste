<?php
function date_german2mysql($datum) {
  list($tag, $monat, $jahr) = explode(".", $datum);
  return sprintf("%04d-%02d-%02d", $jahr, $monat, $tag);
}
function spokenlanguages($employee_id,$sprachen) {
// 	$dbfile="db/ifak.db";
	global $dbfile;
	$languagequery = "SELECT * FROM LanguageLinks WHERE EmployeeID =".$employee_id;
	$db = new SQLite3($dbfile);
	$languageresult = $db->query($languagequery);
	$nrows = 0;
	$languageresult->reset();
	while ($languageresult->fetchArray(SQLITE3_ASSOC))
	    $nrows++;
	$languageresult->reset();
	if($nrows>0) { // Falls es hinterlegte Sprachen gibt -> Anzeigen
		if($_GET['main']=="employee") $sl = '<hr>';	
		$sl .= '<ul class="LanguageSkills">';
		$languagequery = "SELECT * FROM LanguageLinks WHERE EmployeeID = ".$employee_id;
		$languageresult = $db->query($languagequery);
		while ($languages = $languageresult->fetchArray(SQLITE3_ASSOC)) {
				if ($languages['SkillLevel'] != "-") {
					if($languages['zertifiziert']=="1") $level.='*';
					else unset($level);
				}
			$sl .= '<li><a class="skill_'.$languages['SkillLevel'].'" href="?main=language&LanguageID='.$languages['LanguageID'].'">'.$sprachen[$languages['LanguageID']].'</a>';
			if($languages['SkillLevel'] != "-") $sl .= ' <a class="skill_'.$languages['SkillLevel'].'" href="?main=language&LanguageID='.$languages['LanguageID'].'&SkillLevel='.$languages['SkillLevel'].'">'.$languages['SkillLevel'].$level.'</a>';
			$sl .= '</li>';
		}
		$sl .= '</ul>';
		return $sl;
	}
}
function facilities($employee_id,$einrichtungen) {
	if($_GET['main']=="employee") $f = '<hr>';
	$f .= '<ul class="Facilities">';
	global $dbfile;
	$facilityquery = "SELECT * FROM FacilityLinks WHERE EmployeeID = ".$employee_id;
	$db = new SQLite3($dbfile);
	$facilityresult = $db->query($facilityquery);
	while ($facilities = $facilityresult->fetchArray(SQLITE3_ASSOC)) {
		$f .= '<li><a href="?main=facility&FacilityID='.$facilities['FacilityID'].'">'.$einrichtungen[$facilities['FacilityID']].'</a></li>';
	}
	$f .= '</ul>';
	return $f;
}
?>