<?php
header("Content-Type: text/html; charset=iso-8859-1");
include("config.inc");

$pdo = new PDO("mysql:host=$mysqlhost;dbname=$mysqldbvito", "$mysqluser", "$mysqlpwd");
$sql = "SELECT * FROM temperaturen ORDER BY timestamp DESC LIMIT 1";
foreach ($pdo->query($sql) as $tempwerte_aktuell) {}

$sql = "SELECT * FROM brenner ORDER BY timestamp DESC LIMIT 1";
foreach ($pdo->query($sql) as $brennerwerte_aktuell) {}

//letzter Brennerstart
$tmp_wert="";
$sql = "SELECT DATE_FORMAT(timestamp,'%k:%i'),brennerstarts	FROM brenner WHERE DATE(timestamp) = DATE(NOW())";
foreach ($pdo->query($sql) as $brenner) {
	if ($brenner[1]<>$tmp_wert) {
		$max_brenner_datum=$brenner[0];
		$tmp_wert=$brenner[1];
	}
}

//Min Max Temperaturen
$sql = "SELECT MIN(aussentemperatur), MAX(aussentemperatur) FROM temperaturen WHERE timestamp >= DATE(NOW()) ORDER BY timestamp";
foreach ($pdo->query($sql) as $minmax) {}

?>

<html>
<head>
	<title>Heizungsüberwachung</title>
	<meta http-equiv="refresh" content="300">
	<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
	<link href="metro_ui/css/metro-bootstrap.css" rel="stylesheet">
	<link href="metro_ui/css/iconFont.css" rel="stylesheet">
</head>

<body class="metro">
<div class="tile-area tile-area-grayed">
	<h1 class="tile-area-title fg-white">
		<a href="index.php"><i class="icon-arrow-left-3 fg-white smaller"></i></a>
		Heizung<small class="on-right">Übersicht</small>
	</h1>
	
	<!-- Allgemeine Einstellungen der Heizung anzeigen -->
	<div class="tile-group three">
		<div class="tile-group-title fg-white">Allgemeines</div>
		<div class="tile bg-steel">
			<div class="tile-status">
				<span class="name">
					<span style="font-size:15px;"><?php print $brennerwerte_aktuell['betriebsart']; ?></span>
					<br><br>Betriebsart
				</span>
			</div>
		</div>
		<div class="tile bg-lime">
			<div class="tile-status">
				<span class="name">
					<span style="font-size:18px;">
						<?php print $tempwerte_aktuell['raum_nor_soll']."°C<br>".$tempwerte_aktuell['raum_red_soll']."°C red."?>
					</span>
					<br><br>Raumsolltemp.
				</span>
			</div>
		</div>
		<div class="tile bg-olive">
			<div class="tile-status">
				<span class="name">
					<span style="font-size:18px;">
						<?php print $tempwerte_aktuell['vorlauf_ist']; ?>°C<br>
						<?php print $tempwerte_aktuell['vorlauf_soll']; ?>°C red.
					</span>
					<br><br>Vorlauftemp.
				</span>
			</div>
		</div>
		<div class="tile bg-olive">
			<div class="tile-status">
				<span class="name">
					<span style="font-size:40px;">
						<?php print $tempwerte_aktuell['warmwasser']; ?>°C
					</span>
					<br><br>Warmwasser (Ist)
				</span>
			</div>
		</div>
		<div class="tile bg-olive">
			<div class="tile-status">
				<span class="name">
					<span style="font-size:40px;">
						<?php print $tempwerte_aktuell['ruecklauf']; ?>°C
					</span>
					<br><br>Rücklauf
				</span>
			</div>
		</div>
		
		<?php
		if ($brennerwerte_aktuell['brennerstoerung_status'] == "0") {
		//if (strpos($brennerwerte_aktuell['error0'], "Wartung") || strpos($brennerwerte_aktuell['error0'], "UNKNOWN")) {
			$hintergrund_fehlerspeicher="bg-green";
			$status_fehler="";
		} else {
			$hintergrund_fehlerspeicher="bg-crimson";
			$status_fehler="attention";
		}
		
		if ($brennerwerte_aktuell['brennerstatus'] == "0") {
			$brenner_status = "paused";
		} else {
			$brenner_status = "playing";
		}
		?>
		

		<div class="tile <?php print $hintergrund_fehlerspeicher; ?> double">
			<div class="tile-status">
				<span class="name">
					<span style="font-size:15px;">
						<?php print $brennerwerte_aktuell['error0']; ?>
					</span>
					<br><br><br>Fehlerspeicher
					<div class="brand">
						<div class="badge <?php print $status_fehler; ?>"></div>
					</div>
				</span>
			</div>
		</div>
	</div>
	<!-- Daten des Brenners anzeigen -->
	<div class="tile-group double">
		<div class="tile-group-title fg-white">Brenner</div>
		<div class="tile double bg-teal">
			<div class="tile-status">
				<span class="name">
					<span style="font-size:50px;"><?php print round($brennerwerte_aktuell['brennerstarts'],4); ?></span>
					<br><br><br>Brennerstarts / Status
				</span>
				<div class="brand">
					<div class="badge <?php print $brenner_status; ?>"></div>
				</div>
			</div>
		</div>
		<div class="tile bg-amber">
			<div class="tile-status">
				<span class="name">
					<span style="font-size:50px;"><?php print round($brennerwerte_aktuell['brennerstunden'],0); ?></span>
					<br><br><br>Brennerstunden
				</span>
			</div>
		</div>
		<div class="tile bg-taupe">
			<div class="tile-status">
				<span class="name">
					<span style="font-size:45px;"><?php print $max_brenner_datum; ?></span>
					<br><br>Letzter Brennerstart
				</span>
			</div>
		</div>
		<div class="tile bg-cyan">
			<div class="tile-status">
				<span class="name">
					<span style="font-size:50px;"><?php print $tempwerte_aktuell['kessel']; ?></span>
					<br><br>Kesseltemperatur in °C
				</span>
			</div>
		</div>
	</div>
	<!-- Temperaturen -->
	<div class="tile-group double">
		<div class="tile-group-title fg-white">Temperaturen</div>
		<div class="tile bg-teal double">
			<div class="tile-status">
				<span class="name">
					<span style="font-size:50px;"><?php print $tempwerte_aktuell['aussentemperatur']; ?></span>
					<br><br>Aussentemperatur aktuell in °C
				</span>
			</div>
		</div>
		<div class="tile bg-cyan">
			<div class="tile-status">
				<span class="name">
					<span style="font-size:50px;"><?php print round($minmax['0'],1); ?></span>
					<br><br>Aussentemperatur min. heute
				</span>
			</div>
		</div>
		<div class="tile bg-cyan">
			<div class="tile-status">
				<span class="name">
					<span style="font-size:50px;"><?php print round($minmax['1'],1); ?></span>
					<br><br>Aussentemperatur max. heute
				</span>
			</div>
		</div>
		<a class="tile bg-taupe" href="min_max.php">
			<div class="tile-status">
				<span class="name">
					<span style="font-size:35px;">Min<br>&nbsp;Max</span>
				</span>
			</div>
		</a>
	</div>
	
</div>

</body>
</html>
