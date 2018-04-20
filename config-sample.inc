<?php
//MySQL Verbindungsdaten
$mysqlhost="<hostname>";
$mysqluser="<username>";
$mysqlpwd="<password>";
$mysqldbvito="<dbname>";

//Startwert Gaszaehler
$gas_startwert="251650";
$brennwert_gas=10.097;
$zustandszahl_gas=0.9309;
$anz_tage_gas=7;

//Wasserzähler
$anz_tage_wasser=7;

//Stromzähler
$anz_tage_strom=7;

//Runden
function runden($wert) {
	if (strlen(round($wert,0))<=2) {
		$wert=round($wert,1);
	} else {
		$wert=round($wert,0);
	}
	return $wert;
}
?>
