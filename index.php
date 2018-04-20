<?php
header("Content-Type: text/html; charset=iso-8859-1");
include("config.inc");

$pdo = new PDO("mysql:host=$mysqlhost;dbname=$mysqldbvito", "$mysqluser", "$mysqlpwd");
//Temperaturen der Heizung auslesen
$sql = "SELECT * FROM temperaturen ORDER BY timestamp DESC LIMIT 1";
foreach ($pdo->query($sql) as $row) {
//	   print $row['aussentemperatur']." ".$row['warmwasser']."<br />";
}

//letzter Brennerstart
$tmp_wert="";
$sql = "SELECT DATE_FORMAT(timestamp,'%k:%i'),brennerstarts FROM brenner WHERE DATE(timestamp) = DATE(NOW())";
foreach ($pdo->query($sql) as $brenner) {
	if ($brenner[1]<>$tmp_wert) {
		$max_brenner_datum=$brenner[0];
		$tmp_wert=$brenner[1];
	}
}

?>

<html>
<head>
	<title>Hausüberwachung</title>
	<meta http-equiv="refresh" content="180">
	<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
	<link href="metro_ui/css/metro-bootstrap.css" rel="stylesheet">
	<link href="metro_ui/css/iconFont.css" rel="stylesheet">
</head>

<body class="metro">
<div class="tile-area tile-area-grayed">
	<h1 class="tile-area-title fg-white">Übersicht</h1>

	<div class="tile-group one">
		<div class="tile-group-title fg-white">Temperatur</div>
		<a class="tile bg-teal" href="temperatur.php">
			<div class="tile-status">
				<span class="name">
					<span style="font-size:56px;"><?php print $row['aussentemperatur']; ?></span>
					<br><br>Aussentemperatur in °C
				</span>
			</div>
		</a>
	</div>

	<div class="tile-group double">
		<div class="tile-group-title fg-white">Heizung</div>
		<a class="tile bg-darkOrange" href="heizung.php" title="Details anzeigen">
			<div class="tile-content image">
				<img src="heizung.jpg">
			</div>
		</a>
		<div class="tile bg-darkCyan">
			<div class="tile-status">
				<span class="name">
					<span style="font-size:46px;"><?php print $max_brenner_datum; ?></span>
					<br><br>Letzter Brennerstart
				</span>
			</div>
		</div>
		<div class="tile bg-teal">
			<div class="tile-status">
				<span class="name">
					<span style="font-size:56px;"><?php print $row['warmwasser']; ?></span>
					<br><br>Warmwasser in °C
				</span>
			</div>
		</div>
		<div class="tile bg-teal">
			<div class="tile-status">
				<span class="name">
					<span style="font-size:56px;"><?php print $row['kessel']; ?></span>
					<br><br>Kesseltemperatur in °C
				</span>
			</div>
		</div>
	</div>

<!--
	<div class="tile-group double">
		<div class="tile-group-title fg-white">Gas / Strom / Wasser</div>
		<a class="tile bg-steel" href="gaszaehler.php">
			<div class="tile-status">
				<span class="name">
					<span style="font-size:56px;"><? print $gas_kwh_aktuell; ?></span>
					<br><br>Gasverbrauch heute in kWh
				</span>
			</div>
		</a>
		<a class="tile bg-steel" href="gaszaehler.php">
			<div class="tile-status">
				<span class="name">
					<span style="font-size:56px;"><? print $gas_aktuell; ?></span>
					<br><br>Gasverbrauch heute in m&sup3;
				</span>
			</div>
		</a>

		<div class="tile-group-title fg-white"></div>
		<a class="tile bg-cyan" href="wasserzaehler.php">
			<div class="tile-status">
				<span class="name">
					<span style="font-size:56px;"><? print $wasserverbrauch_aktuell; ?></span>
					<br><br>Wasserverbrauch heute in l
				</span>
			</div>
		</a>
		<a class="tile bg-orange" href="stromzaehler.php">
			<div class="tile-status">
				<span class="name">
					<span style="font-size:56px;"><? print runden($strom_tag_aktuell,1); ?></span>
					<br><br>Stromverbrauch heute in kWh
				</span>
			</div>
		</a>
	</div>
-->
</div>
</body>
</html>
